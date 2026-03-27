<?php
/**
 * Template Name: Home Template
 */
?>

<?php get_header(); ?>

<div class="layout">
    <?php get_template_part('resources/views/sections/leftbar'); ?>
    <div class="">
        <main id="swup-main" class="main transition-fade">
            <div class="">
                <?php if (is_active_sidebar("home-main")) : ?>
                <?php dynamic_sidebar("home-main"); ?>
                <?php else : ?>
                <div class="rounded-lg bg-base-100 p-8 text-center text-base-content/50">
                    <p><?php echo __('Please add widgets to "Home Main" area in Appearance > Widgets in the admin panel.', 'a-ripple-song'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>