<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;

/**
 * Registers WordPress theme supports.
 */
class ThemeSupportServiceProvider extends AbstractServiceProvider
{
    /**
     * Register theme support hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Add classic theme supports during the standard theme setup phase.
        add_action('after_setup_theme', [$this, 'registerThemeSupports']);
    }

    /**
     * Register classic theme supports required by the theme.
     *
     * @return void
     */
    public function registerThemeSupports(): void
    {
        add_theme_support('title-tag');
        add_theme_support('automatic-feed-links');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo', [
            'height' => 32,
            'width' => 220,
            'flex-height' => true,
            'flex-width' => true,
        ]);
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
    }
}
