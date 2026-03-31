<?php

namespace App\Core;

use App\Constants\PodcastPluginConstant;

/**
 * Helper
 *
 * Provide shared helper methods for theme templates and widgets.
 */
class Helper
{

    /**
     * Get primary navigation menu items with parent-child relationship structure.
     *
     * @param string $location Menu location name, defaults to 'primary_navigation'.
     * @return array<int, array<string, mixed>> Returns top-level menu items with nested children.
     */
    public static function getMenuItems($location = 'primary_navigation')
    {
        /** @var array<string, int> $menuLocations Registered menu locations keyed by location slug. */
        $menuLocations = get_nav_menu_locations();

        /** @var array<int, \WP_Post> $menuItems Flat menu item list from WordPress. */
        $menuItems = [];

        if (isset($menuLocations[$location])) {
            /** @var int $menuId WordPress menu term ID for the current location. */
            $menuId = $menuLocations[$location];
            $menuItems = wp_get_nav_menu_items($menuId);
        }

        if (!$menuItems) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $menuItemsById Menu items keyed by item ID. */
        $menuItemsById = [];

        foreach ($menuItems as $item) {
            $menuItemsById[$item->ID] = [
                'item' => $item,
                'children' => [],
            ];
        }

        foreach ($menuItems as $item) {
            if ($item->menu_item_parent != 0 && isset($menuItemsById[$item->menu_item_parent])) {
                $menuItemsById[$item->menu_item_parent]['children'][] = &$menuItemsById[$item->ID];
            }
        }

        /** @var array<int, array<string, mixed>> $topLevelItems Top-level menu items with nested children. */
        $topLevelItems = [];

        foreach ($menuItems as $item) {
            if ($item->menu_item_parent == 0) {
                $topLevelItems[] = $menuItemsById[$item->ID];
            }
        }

        return $topLevelItems;
    }

    /**
     * Check if a menu item or any of its children matches the current URL.
     *
     * @param object $item        The menu item object.
     * @param array  $children    Array of child menu item data.
     * @param string $currentUrl  The current page URL.
     * @return bool True if the item is active.
     */
    public static function isMenuItemActive($item, $children, $currentUrl)
    {
        if (rtrim($item->url, '/') === rtrim($currentUrl, '/')) {
            return true;
        }

        foreach ($children as $childData) {
            /** @var object $child Child menu item object. */
            $child = $childData['item'];

            /** @var array<int, array<string, mixed>> $grandchildren Nested child menu items. */
            $grandchildren = $childData['children'];

            if (rtrim($child->url, '/') === rtrim($currentUrl, '/')) {
                return true;
            }

            foreach ($grandchildren as $grandchildData) {
                if (rtrim($grandchildData['item']->url, '/') === rtrim($currentUrl, '/')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get published podcast IDs where the user is listed as a member or guest.
     *
     * @param int $userId The user ID to search for.
     * @return int[] Published podcast IDs that reference the user.
     */
    public static function getParticipatedPodcastIds(int $userId): array
    {
        /** @var array<int, int[]> $cache In-request cache keyed by user ID. */
        static $cache = [];

        $userId = absint($userId);

        if (!$userId) {
            return [];
        }

        if (isset($cache[$userId])) {
            return $cache[$userId];
        }

        /** @var int $cacheVersion Cache version stored in the options table. */
        $cacheVersion = (int) get_option('aripplesong_participation_cache_version', 1);

        /** @var string $transientKey Cache key used for the participation lookup. */
        $transientKey = 'aripplesong_participated_podcasts_v' . $cacheVersion . '_' . $userId;

        /** @var mixed $cached Previously cached result from the transient API. */
        $cached = get_transient($transientKey);

        if (is_array($cached)) {
            $cache[$userId] = array_values(array_unique(array_filter(array_map('absint', $cached))));

            return $cache[$userId];
        }

        /** @var string $needleString Serialized string fragment used in meta queries. */
        $needleString = '"' . $userId . '"';

        /** @var string $needleInt Serialized integer fragment used in meta queries. */
        $needleInt = 'i:' . $userId . ';';

        /** @var int[] $ids Matching podcast post IDs. */
        $ids = get_posts([
            'post_type' => 'ars_podcast',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'author__not_in' => [$userId],
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'members',
                    'value' => $needleString,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'guests',
                    'value' => $needleString,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'members',
                    'value' => $needleInt,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'guests',
                    'value' => $needleInt,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        $cache[$userId] = array_values(array_unique(array_filter(array_map('absint', $ids))));
        set_transient($transientKey, $cache[$userId], HOUR_IN_SECONDS);

        return $cache[$userId];
    }


    /**
     * Get all authors/participants for a post.
     *
     * This includes:
     * - The post author
     * - For podcasts: users listed in members and guests fields
     *
     * @param int $post_id Post ID
     * @return array Array of user IDs (unique)
     */
    public static function getPostAllAuthors($post_id)
    {
        $authors = [];

        // Get the post author
        $author_id = get_post_field('post_author', $post_id);
        if ($author_id) {
            $authors[] = (int)$author_id;
        }

        // If it's a podcast, also get members and guests
        $post_type = get_post_type($post_id);
        if ($post_type === PodcastPluginConstant::PODCAST_POST_TYPE) {
            $members = get_post_meta($post_id, 'members', true);
            $guests = get_post_meta($post_id, 'guests', true);

            $authors = array_merge(
                $authors,
                self::extractMulticheckUserIds($members),
                self::extractMulticheckUserIds($guests)
            );
        }

        $authors = array_values(array_unique(array_filter(array_map('absint', $authors))));

        return $authors;
    }

    /**
     * Extract user IDs from a CMB2 multicheck value.
     *
     * CMB2 multicheck typically stores selected values as an associative array of
     * "id" => "on". Some installs may store a simple numeric array instead.
     *
     * @param mixed $value
     * @return int[]
     */
    public static function extractMulticheckUserIds($value): array
    {
        if (!is_array($value) || empty($value)) {
            return [];
        }

        $ids = [];

        foreach ($value as $key => $item) {
            if ($item === 'on' && is_numeric($key)) {
                $ids[] = (int) $key;
                continue;
            }

            if (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }

        return array_values(array_unique(array_filter(array_map('absint', $ids))));
    }

    /**
     * Check whether a plugin is installed by its slug.
     *
     * Supports both directory-based plugins like "akismet" and single-file
     * plugins whose main file name matches the provided slug.
     *
     * @param string $pluginSlug Plugin slug.
     * @return bool True when a matching installed plugin is found.
     */
    public static function isPluginInstalled(string $pluginSlug): bool
    {
        /** @var array<string, bool> $cache In-request cache keyed by plugin slug. */
        static $cache = [];

        /** @var string $normalizedPluginSlug Normalized plugin slug used for matching. */
        $normalizedPluginSlug = sanitize_key($pluginSlug);

        if ($normalizedPluginSlug === '') {
            return false;
        }

        if (isset($cache[$normalizedPluginSlug])) {
            return $cache[$normalizedPluginSlug];
        }

        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        /** @var array<string, array<string, string>> $plugins Installed plugins keyed by plugin file path. */
        $plugins = get_plugins();

        foreach (array_keys($plugins) as $pluginFile) {
            /** @var string $pluginDirectorySlug Directory slug for directory-based plugins. */
            $pluginDirectorySlug = dirname($pluginFile);

            /** @var string $pluginFileSlug File slug for single-file plugins. */
            $pluginFileSlug = basename($pluginFile, '.php');

            if ($pluginDirectorySlug === $normalizedPluginSlug || $pluginFileSlug === $normalizedPluginSlug) {
                $cache[$normalizedPluginSlug] = true;

                return true;
            }
        }

        $cache[$normalizedPluginSlug] = false;

        return false;
    }

    /**
     * Get episode data for a podcast post.
     *
     * @param int|null $post_id Post ID (defaults to current post).
     * @return array<string, mixed> Episode data array with id, audioUrl, title, description, publishDate, featuredImage, and link.
     */
    public static function getEpisodeData($post_id = null)
    {
        if (!$post_id) {
            $post_id = get_the_ID();
        }

        /** @var string $audio_file Episode audio file URL. */
        $audio_file = get_post_meta($post_id, 'audio_file', true);

        /** @var string|false $featured_image Episode featured image URL. */
        $featured_image = get_the_post_thumbnail_url($post_id, 'medium');

        return [
            'id' => $post_id,
            'audioUrl' => $audio_file,
            'title' => get_the_title($post_id),
            'description' => wp_strip_all_tags(get_the_excerpt()),
            'publishDate' => get_post_time('U', false, $post_id),
            'featuredImage' => $featured_image,
            'link' => get_permalink($post_id),
        ];
    }


    /**
     * Get all post IDs for a user including podcasts they participated in.
     *
     * This includes:
     * - All posts published by the user (including podcasts)
     * - Podcasts where the user is listed in members or guests fields (excluding podcasts authored by the user)
     *
     * @param int $user_id User ID
     * @return array Array of post IDs
     */
    public static function getUserAllPostIds($user_id)
    {
        $user_id = absint($user_id);
        if (!$user_id) {
            return [];
        }

        $post_ids = [];

        // Get posts authored by the user (both 'post' and 'podcast' types)
        $authored_posts = get_posts([
            'author' => $user_id,
            'post_type' => ['post', PodcastPluginConstant::PODCAST_POST_TYPE],
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $post_ids = array_merge($post_ids, $authored_posts);

        $post_ids = array_merge($post_ids, self::getParticipatedPodcastIds($user_id));

        return array_values(array_unique(array_filter(array_map('absint', $post_ids))));
    }

    /**
     * Modify author archive query to include posts where user is a member or guest
     *
     * @param WP_Query $query
     * @return void
     */
    public static function modifyAuthorArchiveQuery($query)
    {
        // Only modify the main query on author archive pages
        if (!is_admin() && $query->is_main_query() && $query->is_author()) {
            // Get the author ID - try multiple methods to ensure we get it
            $author_id = $query->get('author');
            if (!$author_id) {
                $author_name = $query->get('author_name');
                if ($author_name) {
                    $user = get_user_by('slug', $author_name);
                    if ($user) {
                        $author_id = $user->ID;
                    }
                }
            }

            // IMPORTANT: Store the author object in the query before we clear the author vars
            // This allows templates to access the author via get_queried_object()
            if ($author_id) {
                $author_object = get_userdata($author_id);
                if ($author_object) {
                    $query->queried_object = $author_object;
                    $query->queried_object_id = $author_id;
                }
            }

            // Debug log
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Author Archive Query - Author ID: " . $author_id);
            }

            // Get all post IDs for this author (including podcasts they participated in)
            $post_ids = Helper::getUserAllPostIds($author_id);

            // Modify query to use our post IDs
            if (!empty($post_ids)) {
                // Reset query vars to prevent conflicts
                $query->set('post__in', $post_ids);
                $query->set('author', 0); // Set to 0 instead of empty string
                $query->set('author_name', ''); // Clear author_name too
                $query->set('post_type', ['post', PodcastPluginConstant::PODCAST_POST_TYPE]); // Include both post types
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');

                // Important: don't let WordPress limit by author
                unset($query->query_vars['author']);
            } else {
                // No posts found
                $query->set('post__in', [0]); // Force no results
            }
        }
    }
}
