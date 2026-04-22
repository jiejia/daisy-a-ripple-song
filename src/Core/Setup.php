<?php

namespace ARippleSong\Themes\Daisy\Core;

use ARippleSong\Themes\Daisy\Constants\PodcastPluginConstant;

define('IS_PODCAST_PLUGIN_ACTIVATED', Helper::isPluginActivated(PodcastPluginConstant::PLUGIN_SLUG));

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
     * Register classic theme supports required by WordPress theme guidelines.
     */
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
    add_theme_support('post-thumbnails');
    add_theme_support('wp-block-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);
    add_editor_style('editor-style.css');

    /**
     * Register navigation menus.
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'a-ripple-song'),
    ]);
});

/**
 * Register custom block styles and starter block patterns.
 *
 * @return void
 */
add_action('init', function (): void {
    /**
     * Register a reusable panel style for core Group blocks.
     */
    if (function_exists('register_block_style')) {
        register_block_style('core/group', [
            'name' => 'ars-panel',
            'label' => __('Panel', 'a-ripple-song'),
        ]);
    }

    /**
     * Register a lightweight intro pattern for content-first landing sections.
     */
    if (function_exists('register_block_pattern')) {
        register_block_pattern('a-ripple-song/intro-panel', [
            'title' => __('Intro Panel', 'a-ripple-song'),
            'description' => __('A rounded introduction block with heading, text, and call to action.', 'a-ripple-song'),
            'categories' => ['text'],
            'content' => '<!-- wp:group {"align":"wide","className":"is-style-ars-panel"} --><div class="wp-block-group alignwide is-style-ars-panel"><!-- wp:heading --><h2>' . esc_html__('Start your next episode here', 'a-ripple-song') . '</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__('Use this pattern to introduce a featured story, announcement, or podcast episode.', 'a-ripple-song') . '</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">' . esc_html__('Learn more', 'a-ripple-song') . '</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->',
        ]);
    }
});

/**
 * Register the theme sidebars and custom widgets.
 *
 * @return void
 */
add_action('widgets_init', function (): void {

    /** Register the custom theme widgets for use in widget areas. */
    register_widget(\ARippleSong\Themes\Daisy\Widgets\BannerCarouselWidget::class);
    register_widget(\ARippleSong\Themes\Daisy\Widgets\BlogListWidget::class);
    register_widget(\ARippleSong\Themes\Daisy\Widgets\AuthorsWidget::class);
    register_widget(\ARippleSong\Themes\Daisy\Widgets\FooterLinksWidget::class);
    register_widget(\ARippleSong\Themes\Daisy\Widgets\TagsCloudWidget::class);

    if (IS_PODCAST_PLUGIN_ACTIVATED) {
        register_widget(\ARippleSong\Themes\Daisy\Widgets\PodcastListWidget::class);
        register_widget(\ARippleSong\Themes\Daisy\Widgets\SubscribeLinksWidget::class);
    }

    /**
     * Register the footer links sidebar.
     */
    register_sidebar([
        'name' => __('Footer Links', 'a-ripple-song'),
        'id' => 'footer-links',
        'description' => __('Footer links area for displaying link columns', 'a-ripple-song'),
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '',
        'after_title' => '',
    ]);

    /**
     * Register the home main sidebar.
     */
    register_sidebar([
        'name' => __('Home Main', 'a-ripple-song'),
        'id' => 'home-main',
        'description' => __('Main area of the homepage for displaying various content modules', 'a-ripple-song'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);

    /**
     * Register the rightbar primary sidebar.
     */
    register_sidebar([
        'name' => __('Rightbar Primary', 'a-ripple-song'),
        'id' => 'rightbar-primary',
        'description' => __('Primary right sidebar area for displaying various content modules', 'a-ripple-song'),
        'before_widget' => '<div class="widget %1$s %2$s mb-4">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title text-lg font-bold mb-2">',
        'after_title' => '</h2>',
    ]);

    /**
     * Register the leftbar primary sidebar.
     */
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
 * Enqueue the built-in threaded comment reply script on eligible singular screens.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function (): void {
    if (!is_singular() || !comments_open() || !(bool) get_option('thread_comments')) {
        return;
    }

    wp_enqueue_script('comment-reply');
});

/**
 * Modify tag archive query to include both post and podcast types.
 *
 * By default, WordPress tag archives only query 'post' type.
 * This filter ensures that both 'post' and 'podcast' types are included.
 *
 * @param WP_Query $query The WordPress query object.
 * @return void
 */

if (IS_PODCAST_PLUGIN_ACTIVATED) {
    add_action('pre_get_posts', function ($query) {
        // Only modify the main query on tag archive pages
        if (!is_admin() && $query->is_main_query() && $query->is_tag()) {
            $query->set('post_type', ['post', PodcastPluginConstant::PODCAST_POST_TYPE]);
        }
    });
}

if (IS_PODCAST_PLUGIN_ACTIVATED) {
    add_action('pre_get_posts', [Helper::class, 'modifyAuthorArchiveQuery']);
}

/**
 * Apply custom comment list and form styling.
 */
add_filter('comment_form_defaults', [Helper::class, 'filterCommentFormDefaults']);
add_filter('comment_form_default_fields', [Helper::class, 'filterCommentFormDefaultFields']);
add_filter('comment_form_field_comment', [Helper::class, 'filterCommentFormFieldComment']);

/**
 * Register AJAX handlers for post metric tracking.
 */
add_action('wp_ajax_aripplesong_increment_view', [Helper::class, 'incrementViewCount']);
add_action('wp_ajax_nopriv_aripplesong_increment_view', [Helper::class, 'incrementViewCount']);
add_action('wp_ajax_aripplesong_increment_play', [Helper::class, 'incrementPlayCount']);
add_action('wp_ajax_nopriv_aripplesong_increment_play', [Helper::class, 'incrementPlayCount']);
add_action('wp_ajax_aripplesong_get_metrics', [Helper::class, 'getMetrics']);
add_action('wp_ajax_nopriv_aripplesong_get_metrics', [Helper::class, 'getMetrics']);
