<?php

/**
 * Store translated labels used in the sidebar widgets.
 *
 * @var array<string, string> $sidebar_labels
 */
?>

<aside class="sidebar sticky top-[70px] lg:block md:block">
    <?php
    if (is_active_sidebar('rightbar-primary')) :
        dynamic_sidebar('rightbar-primary');
    else:
    ?>
        <div class="rounded-lg bg-base-100 p-4 text-center text-base-content/50">
            <p><?php _e('Please add widgets to "Sidebar" area in Appearance > Widgets in the admin panel.', 'a-ripple-song'); ?></p>
        </div>
    <?php
    endif;
    ?>
</aside>