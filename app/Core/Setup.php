<?php

namespace App\Core;

use App\Constants\PodcastPluginConstant;

define('IS_PODCAST_PLUGIN_INSTALLED', Helper::isPluginInstalled(PodcastPluginConstant::PLUGIN_SLUG));

/**
 * Register theme supports, text domain, menus, sidebars, and widget helpers.
 *
 * @return void
 */
add_action('after_setup_theme', function (): void {
    /**
     * Load translations from the custom theme language directory.
     */
    load_theme_textdomain('a-ripple-song', get_template_directory() . '/resources/lang');

    /**
     * Register navigation menus.
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'a-ripple-song'),
    ]);
});

/**
 * Register the theme sidebars and custom widgets.
 *
 * @return void
 */
add_action('widgets_init', function (): void {

    /** Register the custom theme widgets for use in widget areas. */
    register_widget(\App\Widgets\BannerCarouselWidget::class);
    register_widget(\App\Widgets\BlogListWidget::class);
    register_widget(\App\Widgets\AuthorsWidget::class);
    register_widget(\App\Widgets\FooterLinksWidget::class);
    register_widget(\App\Widgets\TagsCloudWidget::class);

    if (IS_PODCAST_PLUGIN_INSTALLED) {
        register_widget(\App\Widgets\PodcastListWidget::class);
        register_widget(\App\Widgets\SubscribeLinksWidget::class);
    }


    register_sidebar([
        'name' => __('Footer Links', 'a-ripple-song'),
        'id' => 'footer-links',
        'description' => __('Footer links area for displaying link columns', 'a-ripple-song'),
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => '',
    ]);

    register_sidebar([
        'name' => __('Home Main', 'a-ripple-song'),
        'id' => 'home-main',
        'description' => __('Main area of the homepage for displaying various content modules', 'a-ripple-song'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);

    register_sidebar([
        'name' => __('Rightbar Primary', 'a-ripple-song'),
        'id' => 'rightbar-primary',
        'description' => __('Primary right sidebar area for displaying various content modules', 'a-ripple-song'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);

    register_sidebar([
        'name' => __('Leftbar Primary', 'a-ripple-song'),
        'id' => 'leftbar-primary',
        'description' => __('Primary left sidebar area for displaying various content modules', 'a-ripple-song'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);
});

/**
 * Load repeatable widget admin assets on widget management screens.
 *
 * @return void
 */
$widget = new Widget();
add_action('admin_enqueue_scripts', [$widget, 'enqueueAssets']);



/**
 * Modify tag archive query to include both post and podcast types.
 *
 * By default, WordPress tag archives only query 'post' type.
 * This filter ensures that both 'post' and 'podcast' types are included.
 *
 * @param WP_Query $query The WordPress query object.
 * @return void
 */

if (IS_PODCAST_PLUGIN_INSTALLED) {
    add_action('pre_get_posts', function ($query) {
        // Only modify the main query on tag archive pages
        if (!is_admin() && $query->is_main_query() && $query->is_tag()) {
            $query->set('post_type', ['post', PodcastPluginConstant::PODCAST_POST_TYPE]);
        }
    });
}

if (IS_PODCAST_PLUGIN_INSTALLED) {
    add_action('pre_get_posts', [Helper::class, 'modifyAuthorArchiveQuery']);
}
