<?php

namespace ARippleSong\Themes\Daisy\Core;

use ARippleSong\Themes\Daisy\Constants\PodcastPluginConstant;

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
     * Check whether a plugin is activated by its slug.
     *
     * Supports both directory-based plugins like "akismet" and single-file
     * plugins whose main file name matches the provided slug.
     *
     * @param string $pluginSlug Plugin slug.
     * @return bool True when the matching plugin is active on the current site or network.
     */
    public static function isPluginActivated(string $pluginSlug): bool
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

        if (!function_exists('get_plugins') || !function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        /** @var string $pluginFile Matching plugin file path relative to the plugins directory. */
        $pluginFile = self::resolvePluginFileBySlug($normalizedPluginSlug);

        if ($pluginFile === '') {
            $cache[$normalizedPluginSlug] = false;

            return false;
        }

        $cache[$normalizedPluginSlug] = is_plugin_active($pluginFile);

        return $cache[$normalizedPluginSlug];
    }

    /**
     * Resolve the plugin file path for a plugin slug.
     *
     * @param string $pluginSlug Normalized plugin slug.
     * @return string Relative plugin file path, or an empty string when no match is found.
     */
    private static function resolvePluginFileBySlug(string $pluginSlug): string
    {
        /** @var string $directoryPluginFile Conventional directory plugin bootstrap file. */
        $directoryPluginFile = $pluginSlug . '/' . $pluginSlug . '.php';

        if (file_exists(WP_PLUGIN_DIR . '/' . $directoryPluginFile)) {
            return $directoryPluginFile;
        }

        /** @var string $singleFilePlugin Conventional single-file plugin bootstrap file. */
        $singleFilePlugin = $pluginSlug . '.php';

        if (file_exists(WP_PLUGIN_DIR . '/' . $singleFilePlugin)) {
            return $singleFilePlugin;
        }

        /** @var array<string, array<string, string>> $plugins Installed plugins keyed by plugin file path. */
        $plugins = get_plugins();

        foreach (array_keys($plugins) as $pluginFile) {
            /** @var string $pluginDirectorySlug Directory slug for directory-based plugins. */
            $pluginDirectorySlug = dirname($pluginFile);

            /** @var string $pluginFileSlug File slug for single-file plugins. */
            $pluginFileSlug = basename($pluginFile, '.php');

            if ($pluginDirectorySlug === $pluginSlug || $pluginFileSlug === $pluginSlug) {
                return $pluginFile;
            }
        }

        return '';
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

    /**
     * Format a comment date using the current WordPress locale settings.
     *
     * @param \WP_Comment|object $comment Comment object.
     * @param bool $includeTime Whether to include the time.
     * @return string
     */
    public static function getLocalizedCommentDate($comment, bool $includeTime = true): string
    {
        /** @var int $timestamp Unix timestamp for the comment date. */
        $timestamp = strtotime((string) ($comment->comment_date ?? ''));

        if (!$timestamp) {
            return '';
        }

        /** @var string $format Date format based on the current site settings. */
        $format = $includeTime
            ? trim(get_option('date_format') . ' ' . get_option('time_format'))
            : (string) get_option('date_format');

        return wp_date($format, $timestamp);
    }

    /**
     * Render a single comment item with DaisyUI-friendly markup.
     *
     * @param \WP_Comment $comment Comment object.
     * @param array<string, mixed> $args Comment arguments.
     * @param int $depth Comment depth level.
     * @return void
     */
    public static function renderComment($comment, array $args, int $depth): void
    {
        ?>
        <li id="comment-<?php comment_ID(); ?>" <?php comment_class('comment-item'); ?>>
            <article class="rounded-lg bg-base-200/50 p-4 transition-shadow hover:shadow-sm">
                <div class="flex gap-2">
                    <div class="flex-shrink-0">
                        <?php if ((int) ($args['avatar_size'] ?? 0) !== 0): ?>
                            <div class="avatar">
                                <div class="h-6 w-6 rounded-full ring ring-primary ring-offset-1 ring-offset-base-100">
                                    <?php echo get_avatar($comment, (int) ($args['avatar_size'] ?? 24)); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold">
                                <?php echo wp_kses_post(get_comment_author_link($comment)); ?>
                            </span>

                            <?php if ((int) $comment->user_id === (int) get_post_field('post_author', get_the_ID())): ?>
                                <span class="badge badge-primary badge-sm"><?php esc_html_e('Author', 'daisy-a-ripple-song'); ?></span>
                            <?php endif; ?>

                            <span class="flex items-center gap-1 text-xs text-base-content/60">
                                <i data-lucide="clock" class="h-4 w-4"></i>
                                <?php echo esc_html(self::getLocalizedCommentDate($comment)); ?>
                            </span>

                            <?php if ((string) $comment->comment_approved === '0'): ?>
                                <span class="badge badge-warning badge-sm"><?php esc_html_e('Pending Approval', 'daisy-a-ripple-song'); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3 text-sm leading-relaxed text-base-content/80">
                            <?php comment_text(); ?>
                        </div>

                        <div class="flex items-center gap-3">
                            <?php
                            comment_reply_link(array_merge($args, [
                                'add_below' => 'comment',
                                'depth' => $depth,
                                'max_depth' => $args['max_depth'],
                                'before' => '<button class="btn btn-ghost btn-sm gap-1 text-sm">',
                                'after' => '</button>',
                                'reply_text' => '<i data-lucide="reply" class="h-4 w-4"></i> ' . __('Reply', 'daisy-a-ripple-song'),
                            ]));
                            ?>

                            <?php
                            edit_comment_link(
                                '<i data-lucide="pencil" class="h-4 w-4"></i> ' . __('Edit', 'daisy-a-ripple-song'),
                                '<button class="btn btn-ghost btn-sm gap-1 text-sm">',
                                '</button>'
                            );
                            ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php
    }

    /**
     * Customize comment form defaults with DaisyUI styling.
     *
     * @param array<string, mixed> $defaults Comment form defaults.
     * @return array<string, mixed>
     */
    public static function filterCommentFormDefaults(array $defaults): array
    {
        $defaults['class_form'] = 'space-y-4';
        $defaults['class_submit'] = 'btn btn-primary btn-sm gap-2 text-sm';
        $defaults['submit_button'] = '<button type="submit" id="%2$s" class="%3$s">%4$s <i data-lucide="send" class="h-4 w-4"></i></button>';
        $defaults['title_reply_before'] = '<h3 id="reply-title" class="mb-4 hidden text-md font-bold">';
        $defaults['title_reply_after'] = '</h3>';
        $defaults['cancel_reply_before'] = '<div class="text-sm">';
        $defaults['cancel_reply_after'] = '</div>';
        $defaults['cancel_reply_link'] = '<button type="button" class="btn btn-ghost btn-sm gap-1 text-sm"><i data-lucide="x" class="h-4 w-4"></i> %s</button>';
        $defaults['comment_notes_before'] = '<p class="comment-notes text-sm text-base-content/60">' . esc_html__('Your email address will not be published.', 'daisy-a-ripple-song') . '</p>';
        $defaults['comment_notes_after'] = '';
        $defaults['logged_in_as'] = '<p class="logged-in-as text-sm text-base-content/60">' .
            sprintf(
                wp_kses_post(__('Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s">Log out?</a>', 'daisy-a-ripple-song')),
                esc_url((string) get_edit_user_link()),
                esc_html(wp_get_current_user()->display_name),
                esc_url((string) wp_logout_url(get_permalink()))
            ) .
        '</p>';

        return $defaults;
    }

    /**
     * Customize comment form fields with DaisyUI styling.
     *
     * @param array<string, string> $fields Comment form fields.
     * @return array<string, string>
     */
    public static function filterCommentFormDefaultFields(array $fields): array
    {
        /** @var array<string, string> $commenter Current commenter values. */
        $commenter = wp_get_current_commenter();

        /** @var bool $isNameEmailRequired Whether name and email are required. */
        $isNameEmailRequired = (bool) get_option('require_name_email');

        /** @var string $requiredAttribute HTML required attribute. */
        $requiredAttribute = $isNameEmailRequired ? ' required' : '';

        $fields['author'] = '<div class="form-control"><label class="label" for="author"><span class="label-text text-sm">' . esc_html__('Name', 'daisy-a-ripple-song') . ' <span class="text-error">*</span></span></label><input type="text" id="author" name="author" value="' . esc_attr($commenter['comment_author'] ?? '') . '" class="input input-bordered w-full text-sm"' . $requiredAttribute . ' /></div>';

        $fields['email'] = '<div class="form-control"><label class="label" for="email"><span class="label-text text-sm">' . esc_html__('Email', 'daisy-a-ripple-song') . ' <span class="text-error">*</span></span></label><input type="email" id="email" name="email" value="' . esc_attr($commenter['comment_author_email'] ?? '') . '" class="input input-bordered w-full text-sm"' . $requiredAttribute . ' /></div>';

        $fields['url'] = '<div class="form-control"><label class="label" for="url"><span class="label-text text-sm">' . esc_html__('Website', 'daisy-a-ripple-song') . '</span></label><input type="url" id="url" name="url" value="' . esc_attr($commenter['comment_author_url'] ?? '') . '" class="input input-bordered w-full text-sm" /></div>';

        $fields['cookies'] = '<div class="form-control"><label class="comment-form-cookies-consent flex items-start gap-2"><input type="checkbox" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" value="yes" class="checkbox checkbox-sm mt-1" /><span class="label-text text-sm leading-relaxed">' . esc_html__('Save my name, email, and website in this browser for the next time I comment.', 'daisy-a-ripple-song') . '</span></label></div>';

        return $fields;
    }

    /**
     * Customize the main comment textarea field with DaisyUI styling.
     *
     * @param string $field Comment textarea field HTML.
     * @return string
     */
    public static function filterCommentFormFieldComment(string $field): string
    {
        return '<div class="form-control"><label class="label" for="comment"><span class="label-text text-sm">' . esc_html__('Comment', 'daisy-a-ripple-song') . ' <span class="text-error">*</span></span></label><textarea id="comment" name="comment" rows="6" class="textarea textarea-bordered w-full text-sm" required></textarea></div>';
    }

    /**
     * Check whether a post can be read for metric tracking purposes.
     *
     * @param \WP_Post $post The target post object.
     * @return bool True when the post metrics may be read or updated.
     */
    private static function canReadMetricPost(\WP_Post $post): bool
    {
        if ($post->post_status === 'publish') {
            return true;
        }

        return current_user_can('read_post', $post->ID);
    }

    /**
     * Increment the view count for a readable post.
     *
     * @return void
     */
    public static function incrementViewCount(): void
    {
        check_ajax_referer('aripplesong-ajax');

        /** @var int $postId Requested post ID. */
        $postId = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;

        /** @var \WP_Post|null $post Target post object. */
        $post = $postId ? get_post($postId) : null;

        if (!$post instanceof \WP_Post || !self::canReadMetricPost($post)) {
            wp_send_json_error(['message' => 'Invalid post ID.'], 400);
        }

        /** @var int $count Updated view count. */
        $count = max(0, (int) get_post_meta($postId, '_views_count', true)) + 1;

        update_post_meta($postId, '_views_count', $count);

        wp_send_json_success(['count' => $count]);
    }

    /**
     * Increment the play count for a readable podcast episode.
     *
     * @return void
     */
    public static function incrementPlayCount(): void
    {
        check_ajax_referer('aripplesong-ajax');

        /** @var int $postId Requested post ID. */
        $postId = isset($_POST['post_id']) ? absint(wp_unslash($_POST['post_id'])) : 0;

        /** @var \WP_Post|null $post Target post object. */
        $post = $postId ? get_post($postId) : null;

        if (
            !$post instanceof \WP_Post
            || $post->post_type !== PodcastPluginConstant::PODCAST_POST_TYPE
            || !self::canReadMetricPost($post)
        ) {
            wp_send_json_error(['message' => 'Invalid podcast episode post.'], 400);
        }

        /** @var int $count Updated play count. */
        $count = max(0, (int) get_post_meta($postId, '_play_count', true)) + 1;

        update_post_meta($postId, '_play_count', $count);

        wp_send_json_success(['count' => $count]);
    }

    /**
     * Fetch the current metrics for a list of readable posts.
     *
     * @return void
     */
    public static function getMetrics(): void
    {
        check_ajax_referer('aripplesong-ajax');

        /** @var mixed[] $rawIds Raw post ID list from the AJAX request. */
        $rawIds = isset($_POST['post_ids']) ? (array) $_POST['post_ids'] : [];

        /** @var int[] $postIds Normalized post IDs. */
        $postIds = array_values(array_filter(array_map(static function ($id): int {
            return absint(wp_unslash($id));
        }, $rawIds)));

        if ($postIds === []) {
            wp_send_json_error(['message' => 'No post IDs provided.'], 400);
        }

        /** @var array<int, array<string, int|string|null>> $data Metrics payload keyed by post ID. */
        $data = [];

        foreach ($postIds as $postId) {
            /** @var \WP_Post|null $post Post object for the requested ID. */
            $post = get_post($postId);

            if (!$post instanceof \WP_Post || !self::canReadMetricPost($post)) {
                continue;
            }

            /** @var int $views Current post view count. */
            $views = max(0, (int) get_post_meta($postId, '_views_count', true));

            /** @var int|null $plays Current post play count when the post is a podcast episode. */
            $plays = $post->post_type === PodcastPluginConstant::PODCAST_POST_TYPE
                ? max(0, (int) get_post_meta($postId, '_play_count', true))
                : null;

            $data[$postId] = [
                'views' => $views,
                'plays' => $plays,
                'postType' => $post->post_type,
            ];
        }

        wp_send_json_success(['counts' => $data]);
    }
}
