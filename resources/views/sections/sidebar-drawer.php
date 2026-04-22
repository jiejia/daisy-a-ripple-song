<?php
/**
 * Sidebar drawer section template.
 */
?>
<div class="drawer drawer-end z-[100]" id="sidebar-drawer-container">
    <input type="checkbox" id="sidebar-drawer" class="drawer-toggle" />
    <div class="drawer-side">
        <label for="sidebar-drawer" aria-label="<?php echo esc_attr__('Close sidebar', 'daisy-a-ripple-song'); ?>" class="drawer-overlay"></label>
        <div class="min-h-full w-80 max-w-[90vw] bg-base-100">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-base-300 bg-base-100 p-4">
                <h3 class="text-lg font-bold"><?php esc_html_e('Right Sidebar', 'daisy-a-ripple-song'); ?></h3>
                <label for="sidebar-drawer" class="btn btn-circle btn-ghost btn-sm">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </label>
            </div>

            <div class="p-4">
                <?php get_search_form(); ?>
                <?php if (is_active_sidebar('rightbar-primary')): ?>
                    <?php dynamic_sidebar('rightbar-primary'); ?>
                <?php else: ?>
                    <div class="rounded-lg bg-base-200 p-4 text-center text-base-content/50">
                        <p><?php esc_html_e('Please add widgets to "Sidebar" area in Appearance > Widgets in the admin panel.', 'daisy-a-ripple-song'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
