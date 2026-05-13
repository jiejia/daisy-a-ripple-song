<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Widgets\AuthorsWidget;
use Jiejia\DaisyARippleSong\Widgets\BannerCarouselWidget;
use Jiejia\DaisyARippleSong\Widgets\BlogListWidget;
use Jiejia\DaisyARippleSong\Widgets\FooterLinksWidget;
use Jiejia\DaisyARippleSong\Widgets\PodcastListWidget;
use Jiejia\DaisyARippleSong\Widgets\SubscribeLinksWidget;
use Jiejia\DaisyARippleSong\Widgets\TagsCloudWidget;

/**
 * Registers theme widgets and widget editor assets.
 */
class WidgetServiceProvider extends AbstractServiceProvider
{
    /**
     * Base widget classes registered by this provider.
     *
     * @var array<int,class-string<\WP_Widget>>
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
     * @var array<int,class-string<\WP_Widget>>
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
        // Register widgets when WordPress initializes widget support.
        add_action('widgets_init', [$this, 'registerWidgets']);

        // Load repeatable widget admin assets on widget management screens.
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Register all configured custom widgets.
     *
     * @return void
     */
    public function registerWidgets(): void
    {
        foreach ($this->widgets as $widgetClass) {
            // Register each base widget class with WordPress.
            register_widget($widgetClass);
        }

        if (!defined('IS_PODCAST_PLUGIN_ACTIVATED') || !IS_PODCAST_PLUGIN_ACTIVATED) {
            return;
        }

        foreach ($this->podcastWidgets as $widgetClass) {
            // Register podcast widgets only when the plugin runtime is available.
            register_widget($widgetClass);
        }
    }

    /**
     * Enqueue media support and inline scripts for widget forms.
     *
     * @param string $hookSuffix Current admin page hook suffix.
     * @return void
     */
    public function enqueueAssets(string $hookSuffix): void
    {
        if (!in_array($hookSuffix, ['widgets.php', 'customize.php'], true)) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $this->getInlineScript());
    }

    /**
     * Build inline JavaScript used by repeatable widget fields.
     *
     * @return string JavaScript source code.
     */
    private function getInlineScript(): string
    {
        /** @var string|false $labelsJson JSON encoded labels consumed by the admin script. */
        $labelsJson = wp_json_encode([
            'selectBannerImage' => __('Select Banner Image', 'daisy-a-ripple-song'),
            'useThisImage' => __('Use This Image', 'daisy-a-ripple-song'),
        ]);

        return <<<JS
(function($) {
    'use strict';

    const labels = {$labelsJson};

    function triggerWidgetChange(element) {
        element.closest('form').find('input, select, textarea').first().trigger('change');
    }

    function cloneRepeatableItem(button, itemSelector, containerSelector) {
        const container = button.closest('.widget-content').find(containerSelector).first();
        const lastItem = container.find(itemSelector).last();
        const newItem = lastItem.clone();
        const nextIndex = container.find(itemSelector).length;

        newItem.find('input, select, textarea').each(function() {
            const field = $(this);
            const fieldName = field.attr('name');

            if (fieldName) {
                field.attr('name', fieldName.replace(/\\[\\d+\\]/, '[' + nextIndex + ']'));
            }

            if (field.is(':checkbox')) {
                field.prop('checked', false);
            } else if (field.is('select')) {
                field.prop('selectedIndex', 0);
            } else {
                field.val('');
            }
        });

        newItem.find('img').remove();
        container.append(newItem);
        triggerWidgetChange(button);
    }

    $(document).on('click', '.banner-add-slide', function(event) {
        event.preventDefault();
        cloneRepeatableItem($(this), '.banner-slide-item', '.banner-slides-container');
    });

    $(document).on('click', '.footer-add-link', function(event) {
        event.preventDefault();
        cloneRepeatableItem($(this), '.footer-link-item', '.footer-links-container');
    });

    $(document).on('click', '.banner-remove-slide, .footer-remove-link', function(event) {
        event.preventDefault();

        const button = $(this);
        const item = button.closest('.banner-slide-item, .footer-link-item');
        const container = item.parent();

        if (container.children().length > 1) {
            item.remove();
        } else {
            item.find('input[type="text"], input[type="url"]').val('');
            item.find('input[type="checkbox"]').prop('checked', false);
            item.find('select').prop('selectedIndex', 0);
            item.find('img').remove();
        }

        triggerWidgetChange(button);
    });

    $(document).on('click', '.banner-select-image', function(event) {
        event.preventDefault();

        const button = $(this);
        const input = button.closest('.banner-slide-item').find('.banner-image-url');
        const frame = wp.media({
            title: labels.selectBannerImage,
            button: { text: labels.useThisImage },
            multiple: false
        });

        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            input.val(attachment.url).trigger('change');
            triggerWidgetChange(button);
        });

        frame.open();
    });
})(jQuery);
JS;
    }
}
