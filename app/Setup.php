<?php
namespace App;

/**
 * Register theme supports, text domain, and menus.
 *
 * @return void
 */
add_action('after_setup_theme', function (): void {

    /**
     * Load translations from the custom theme language directory.
     */
    load_theme_textdomain('a-ripple-song', get_template_directory() . '/resources/lang');

    /**
     * Register navigation menus
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'a-ripple-song'),
    ]);

});


/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function (): void {


});
