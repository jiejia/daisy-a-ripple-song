<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;
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
        BannerCarouselWidget::class,
        BlogListWidget::class,
        AuthorsWidget::class,
        FooterLinksWidget::class,
        TagsCloudWidget::class,
    ];

    /**
     * Podcast widget classes registered only when the podcast plugin is active.
     *
     * @var array<int,class-string<ThemeWidget>>
     */
    private array $podcastWidgets = [
        PodcastListWidget::class,
        SubscribeLinksWidget::class,
    ];

    /**
     * Register widget hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('widgets_init', [$this, 'registerWidgets']);

        // Carbon Fields Loader only registers widget containers for widgets whose IDs start with
        // "carbon_fields_". Because AbstractWidget clears that prefix (to preserve existing option
        // keys), REST API widget updates via the Block Editor would silently discard complex field
        // data. Hooking into rest_api_init ensures the containers are registered in time.
        add_action('rest_api_init', [$this, 'initializeWidgetRestContainers']);
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

    /**
     * Register Carbon Fields containers for all active theme widget instances during REST API init.
     *
     * This compensates for the Carbon Fields Loader skipping widgets without the "carbon_fields_"
     * ID prefix so that the Block Editor can correctly read and write complex field data.
     *
     * @return void
     */
    public function initializeWidgetRestContainers(): void
    {
        global $wp_registered_widgets;

        /** @var array<int,class-string<ThemeWidget>> $allWidgetClasses All theme widget classes to initialise. */
        $allWidgetClasses = $this->widgets;

        if (defined('IS_PODCAST_PLUGIN_ACTIVATED') && IS_PODCAST_PLUGIN_ACTIVATED) {
            $allWidgetClasses = array_merge($allWidgetClasses, $this->podcastWidgets);
        }

        // Collect the concrete widget instance already instantiated by register_widget() so we
        // avoid creating a second instance (which would trigger Carbon Fields static
        // "field name already registered" checks). A single WP_Widget object may appear multiple
        // times in $wp_registered_widgets (once per placed instance), so track processed object
        // IDs to call initializeRestContainer() only once per widget class.
        /** @var array<int,bool> $initializedObjectIds Map of spl_object_id => true for already-initialized instances. */
        $initializedObjectIds = [];

        foreach ($wp_registered_widgets as $registeredWidget) {
            /** @var object|null $callback The widget object from the callback pair. */
            $callback = $registeredWidget['callback'][0] ?? null;

            if (!($callback instanceof AbstractWidget)) {
                continue;
            }

            /** @var int $objectId Unique identity of the current widget object. */
            $objectId = spl_object_id($callback);

            if (isset($initializedObjectIds[$objectId])) {
                continue;
            }

            foreach ($allWidgetClasses as $widgetClass) {
                if ($callback instanceof $widgetClass) {
                    $callback->initializeRestContainer();
                    $initializedObjectIds[$objectId] = true;
                    break;
                }
            }
        }
    }
}
