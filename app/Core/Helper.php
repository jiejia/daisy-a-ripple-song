<?php

namespace App\Core;

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
}
