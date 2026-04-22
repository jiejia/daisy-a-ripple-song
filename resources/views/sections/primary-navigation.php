<?php
/** @var array $menu_items Top-level navigation menu items with nested children */
$menu_items  = \ARippleSong\Themes\Daisy\Core\Helper::getMenuItems();
$current_url = home_url($_SERVER['REQUEST_URI']);
?>
<ul class="xl:grid hidden grid-flow-col gap-2 text-md justify-center" id="menu-1">
    <?php foreach ($menu_items as $menu_data) : ?>
        <?php
        /** @var object $item     The top-level menu item */
        $item         = $menu_data['item'];
        /** @var array  $children Child menu items array */
        $children     = $menu_data['children'];
        $has_children = !empty($children);

        /** @var bool $is_active Whether this item or a descendant matches the current URL */
        $is_active    = \ARippleSong\Themes\Daisy\Core\Helper::isMenuItemActive($item, $children, $current_url);

        /** @var string $active_class CSS classes applied based on active state */
        $active_class = $is_active
            ? 'text-base-content font-semibold bg-base-200/50'
            : 'text-base-content/80 hover:text-base-content';
        ?>
        <li>
            <?php if ($has_children) : ?>
                <div class="dropdown dropdown-hover dropdown-start h-full w-full">
                    <a class="grid place-items-center h-full w-full text-center px-4 rounded-lg <?php echo esc_attr($active_class); ?>" href="<?php echo esc_url($item->url); ?>" data-pjax>
                        <?php echo esc_html($item->title); ?>
                    </a>
                    <ul tabindex="-1" class="dropdown-content menu bg-base-200/75 rounded-box z-[100] w-52 p-2 shadow-sm">
                        <?php foreach ($children as $child_data) : ?>
                            <?php
                            /** @var object $child        The child menu item */
                            $child            = $child_data['item'];
                            /** @var array  $grandchildren Grandchild menu items array */
                            $grandchildren    = $child_data['children'];
                            $has_grandchildren = !empty($grandchildren);
                            ?>
                            <?php if ($has_grandchildren) : ?>
                                <li class="relative group/submenu">
                                    <a href="<?php echo esc_url($child->url); ?>" class="flex items-center justify-between" data-pjax>
                                        <?php echo esc_html($child->title); ?>
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                    <ul class="absolute left-full top-0 ml-1 hidden group-hover/submenu:block menu bg-base-200/90 rounded-box z-[100] w-52 p-2 shadow-sm">
                                        <?php foreach ($grandchildren as $grandchild_data) : ?>
                                            <li>
                                                <a href="<?php echo esc_url($grandchild_data['item']->url); ?>" data-pjax>
                                                    <?php echo esc_html($grandchild_data['item']->title); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else : ?>
                                <li>
                                    <a href="<?php echo esc_url($child->url); ?>" data-pjax>
                                        <?php echo esc_html($child->title); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else : ?>
                <a href="<?php echo esc_url($item->url); ?>" class="grid place-items-center h-full w-full text-center px-4 rounded-lg <?php echo esc_attr($active_class); ?>" data-pjax>
                    <?php echo esc_html($item->title); ?>
                </a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
