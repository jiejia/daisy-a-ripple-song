<?php
/**
 * Leftbar drawer section template.
 */
?>
<div class="drawer drawer-start z-[100]" id="leftbar-drawer-container">
    <input type="checkbox" id="leftbar-drawer" class="drawer-toggle" />
    <div class="drawer-side">
        <label for="leftbar-drawer" aria-label="<?php echo esc_attr__('Close sidebar', 'daisy-a-ripple-song'); ?>" class="drawer-overlay"></label>
        <div class="min-h-full w-72 max-w-[85vw] bg-base-100">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-base-300 bg-base-100 p-4">
                <h3 class="text-lg font-bold"><?php esc_html_e('Left Sidebar', 'daisy-a-ripple-song'); ?></h3>
                <button type="button" class="btn btn-circle btn-ghost btn-sm" onclick="document.getElementById('leftbar-drawer').checked = false" aria-label="<?php echo esc_attr__('Close sidebar', 'daisy-a-ripple-song'); ?>">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <div class="p-4">
                <?php if (is_active_sidebar('leftbar-primary')): ?>
                    <?php dynamic_sidebar('leftbar-primary'); ?>
                <?php else: ?>
                    <div class="rounded-lg bg-base-200 p-4 text-center text-base-content/50">
                        <p><?php esc_html_e('Please add widgets to "Leftbar" area in Appearance > Widgets in the admin panel.', 'daisy-a-ripple-song'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
