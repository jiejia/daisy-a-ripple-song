<div class="drawer drawer-start z-[101]" id="swup-mobile-menu">
  <input type="checkbox" id="mobile-menu" class="drawer-toggle" />
  <div class="drawer-side">
    <label for="mobile-menu" aria-label="<?php echo esc_attr__('Close sidebar', 'daisy-a-ripple-song'); ?>" class="drawer-overlay"></label>
    <div class="bg-base-100 h-full w-80 max-w-xs">
      <div class="sticky top-0 bg-base-100 p-4 border-b border-base-300 flex items-center justify-between">
        <h3 class="font-bold text-lg"><?php echo esc_html__('Menu', 'daisy-a-ripple-song'); ?></h3>
        <label for="mobile-menu" class="btn btn-sm btn-circle btn-ghost">✕</label>
      </div>
      
      <ul class="menu p-4 w-full">
        <?php
        /** Fetch the structured menu tree used by the mobile navigation. */
        $menuItems = \ARippleSong\Themes\Daisy\Core\Helper::getMenuItems();

        /** Resolve the current absolute URL for active-state comparison. */
        $currentUrl = home_url(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'));
        ?>

        <?php foreach ($menuItems as $menuData): ?>
          <?php
          /** Extract the current top-level menu node and its children. */
          $item = $menuData['item'];
          $children = $menuData['children'];
          $hasChildren = !empty($children);

          /** Determine the active and current-page state for the menu node. */
          $isCurrentPage = $item->url === $currentUrl;
          $isActive = \ARippleSong\Themes\Daisy\Core\Helper::isMenuItemActive($item, $children, $currentUrl);
          $activeClass = $isActive ? 'active font-semibold' : '';
          ?>

          <?php if ($hasChildren): ?>
            <li>
              <details open>
                <summary class="flex justify-between items-center <?php echo esc_attr($isCurrentPage ? 'bg-base-200/50' : ''); ?>">
                  <a href="<?php echo esc_url($item->url); ?>" class="flex-1 <?php echo esc_attr($activeClass); ?>" onclick="event.stopPropagation()">
                    <?php echo esc_html($item->title); ?>
                  </a>
                </summary>
                <ul>
                  <?php foreach ($children as $childData): ?>
                    <?php
                    /** Extract the current child node and its descendants. */
                    $child = $childData['item'];
                    $grandchildren = $childData['children'];
                    $hasGrandchildren = !empty($grandchildren);

                    /** Determine the active and current-page state for the child node. */
                    $childIsCurrentPage = $child->url === $currentUrl;
                    $childIsActive = \ARippleSong\Themes\Daisy\Core\Helper::isMenuItemActive($child, $grandchildren, $currentUrl);
                    $childActiveClass = $childIsActive ? 'active font-semibold' : '';
                    ?>

                    <?php if ($hasGrandchildren): ?>
                      <li>
                        <details open>
                          <summary class="flex justify-between items-center <?php echo esc_attr($childIsCurrentPage ? 'bg-base-200/50' : ''); ?>">
                            <a href="<?php echo esc_url($child->url); ?>" class="flex-1 <?php echo esc_attr($childActiveClass); ?>" onclick="event.stopPropagation()">
                              <?php echo esc_html($child->title); ?>
                            </a>
                          </summary>
                          <ul>
                            <?php foreach ($grandchildren as $grandchildData): ?>
                              <?php
                              /** Determine the active class for the grandchild node. */
                              $grandchild = $grandchildData['item'];
                              $grandchildIsActive = $grandchild->url === $currentUrl;
                              $grandchildActiveClass = $grandchildIsActive ? 'active font-semibold' : '';
                              $grandchildClass = trim($grandchildActiveClass . ' ' . ($grandchildIsActive ? 'bg-base-200/50' : ''));
                              ?>
                              <li><a href="<?php echo esc_url($grandchild->url); ?>" class="<?php echo esc_attr($grandchildClass); ?>"><?php echo esc_html($grandchild->title); ?></a></li>
                            <?php endforeach; ?>
                          </ul>
                        </details>
                      </li>
                    <?php else: ?>
                      <?php $childClass = trim($childActiveClass . ' ' . ($childIsActive ? 'bg-base-200/50' : '')); ?>
                      <li><a href="<?php echo esc_url($child->url); ?>" class="<?php echo esc_attr($childClass); ?>"><?php echo esc_html($child->title); ?></a></li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </ul>
              </details>
            </li>
          <?php else: ?>
            <?php $itemClass = trim($activeClass . ' ' . ($isCurrentPage ? 'bg-base-200/50' : '')); ?>
            <li><a href="<?php echo esc_url($item->url); ?>" class="<?php echo esc_attr($itemClass); ?>"><?php echo esc_html($item->title); ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
