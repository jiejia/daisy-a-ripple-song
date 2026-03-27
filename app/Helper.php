<?php

namespace App;

class Helper
{

    /**
     * Get primary navigation menu items with parent-child relationship structure
     *
     * @param string $location Menu location name, defaults to 'primary_navigation'
     * @return array Returns an array containing top-level menu items and their child menu items mapping
     */
    public static function getMenuItems($location = 'primary_navigation')
    {
        $menu_locations = get_nav_menu_locations();
        $menu_items = [];

        if (isset($menu_locations[$location])) {
            $menu_id = $menu_locations[$location];
            $menu_items = wp_get_nav_menu_items($menu_id);
        }

        if (!$menu_items) {
            return [];
        }

        // Build hierarchical menu structure (up to 3 levels)
        $menu_items_by_id = [];
        foreach ($menu_items as $item) {
            $menu_items_by_id[$item->ID] = [
                'item' => $item,
                'children' => []
            ];
        }

        // Build parent-child relationships recursively
        foreach ($menu_items as $item) {
            if ($item->menu_item_parent != 0 && isset($menu_items_by_id[$item->menu_item_parent])) {
                $menu_items_by_id[$item->menu_item_parent]['children'][] = &$menu_items_by_id[$item->ID];
            }
        }

        // Return only top-level menu items (with their nested children)
        $top_level_items = [];
        foreach ($menu_items as $item) {
            if ($item->menu_item_parent == 0) {
                $top_level_items[] = $menu_items_by_id[$item->ID];
            }
        }

        return $top_level_items;
    }

    /**
     * Check if a menu item or any of its children matches the current URL
     *
     * @param object $item    The menu item object
     * @param array  $children Array of child menu item data
     * @param string $current_url The current page URL
     * @return bool True if the item is active
     */
    public static function isMenuItemActive($item, $children, $current_url)
    {
        // Direct URL match
        if (rtrim($item->url, '/') === rtrim($current_url, '/')) {
            return true;
        }

        // Check children recursively
        foreach ($children as $child_data) {
            $child = $child_data['item'];
            $grandchildren = $child_data['children'];

            if (rtrim($child->url, '/') === rtrim($current_url, '/')) {
                return true;
            }

            // Check grandchildren
            foreach ($grandchildren as $grandchild_data) {
                if (rtrim($grandchild_data['item']->url, '/') === rtrim($current_url, '/')) {
                    return true;
                }
            }
        }

        return false;
    }
    /**
     * Get published podcast IDs where the user is listed as a member or guest.
     *
     * @param int $user_id
     * @return int[]
     */
    public static function getParticipatedPodcastIds(int $user_id): array
    {
        static $cache = [];

        $user_id = absint($user_id);
        if (!$user_id) {
            return [];
        }

        if (isset($cache[$user_id])) {
            return $cache[$user_id];
        }

        $cache_version = (int) get_option('aripplesong_participation_cache_version', 1);
        $transient_key = 'aripplesong_participated_podcasts_v' . $cache_version . '_' . $user_id;
        $cached = get_transient($transient_key);
        if (is_array($cached)) {
            $cache[$user_id] = array_values(array_unique(array_filter(array_map('absint', $cached))));
            return $cache[$user_id];
        }

        $needle_string = '"' . $user_id . '"';
        $needle_int = 'i:' . $user_id . ';';

        $ids = get_posts([
            'post_type' => 'ars_podcast',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'author__not_in' => [$user_id],
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'members',
                    'value' => $needle_string,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'guests',
                    'value' => $needle_string,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'members',
                    'value' => $needle_int,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => 'guests',
                    'value' => $needle_int,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        $cache[$user_id] = array_values(array_unique(array_filter(array_map('absint', $ids))));
        set_transient($transient_key, $cache[$user_id], HOUR_IN_SECONDS);

        return $cache[$user_id];
    }
}
