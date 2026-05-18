<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Contracts\ThemeWidget;
use Jiejia\DaisyARippleSong\Widgets\AuthorsWidget;
use Jiejia\DaisyARippleSong\Widgets\BannerCarouselWidget;
use Jiejia\DaisyARippleSong\Widgets\BlogListWidget;
use Jiejia\DaisyARippleSong\Widgets\FooterLinksWidget;
use Jiejia\DaisyARippleSong\Widgets\PodcastListWidget;
use Jiejia\DaisyARippleSong\Widgets\SubscribeLinksWidget;
use Jiejia\DaisyARippleSong\Widgets\TagsCloudWidget;

/**
 * Registers theme widgets.
 */
class WidgetServiceProvider extends AbstractServiceProvider
{
    /**
     * Base widget classes registered by this provider.
     *
     * @var array<int,class-string<ThemeWidget>>
     */
    private array $widgets = [

    ];

    /**
     * Podcast widget classes registered only when the podcast plugin is active.
     *
     * @var array<int,class-string<ThemeWidget>>
     */
    private array $podcastWidgets = [

    ];

    /**
     * Register widget hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('widgets_init', [$this, 'registerWidgets']);
    }

    /**
     * Register all configured custom widgets.
     *
     * @return void
     */
    public function registerWidgets(): void
    {
        foreach ($this->widgets as $widgetClass) {
            register_widget($widgetClass);
        }

        if (!defined('IS_PODCAST_PLUGIN_ACTIVATED') || !IS_PODCAST_PLUGIN_ACTIVATED) {
            return;
        }

        foreach ($this->podcastWidgets as $widgetClass) {
            register_widget($widgetClass);
        }
    }

}
