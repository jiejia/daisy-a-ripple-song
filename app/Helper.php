<?php
namespace App;

class Helper {

    /**
 * Get primary navigation menu items with parent-child relationship structure
 *
 * @param string $location Menu location name, defaults to 'primary_navigation'
 * @return array Returns an array containing top-level menu items and their child menu items mapping
 */
public static function getMenuItems($location = 'primary_navigation') {
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
public static function isMenuItemActive($item, $children, $current_url) {
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

}