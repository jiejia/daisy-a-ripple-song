<aside class="sticky top-[70px] hidden lg:block md:hidden">
    <?php
    if (is_active_sidebar('leftbar-primary')) :
        dynamic_sidebar('leftbar-primary');
    else:
    ?>
        <div class="rounded-lg bg-base-100 p-4 text-center text-base-content/50">
            <p><?php _e('Please add widgets to "Leftbar" area in Appearance > Widgets in the admin panel.', 'daisy-a-ripple-song'); ?></p>
        </div>
    <?php
    endif;
    ?>
</aside>