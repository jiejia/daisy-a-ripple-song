<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;

/**
 * Footer Links Widget.
 */
class FooterLinksWidget extends AbstractWidget
{
    /**
     * Return the WordPress widget ID.
     *
     * @return string
     */
    public function widgetId(): string
    {
        return 'footer_links_widget';
    }

    /**
     * Return the translated widget title.
     *
     * @return string
     */
    public function widgetTitle(): string
    {
        return __('aripplesong - Footer Links', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated widget description.
     *
     * @return string
     */
    public function widgetDescription(): string
    {
        return __('Display a list of links or text items in the footer', 'daisy-a-ripple-song');
    }

    /**
     * Return all native field definitions for the widget form.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array
    {
        return [
            [
                'type' => 'text',
                'key' => 'title',
                'label' => __('Title', 'daisy-a-ripple-song'),
                'placeholder' => __('e.g., Contact, Navigate, Support', 'daisy-a-ripple-song'),
                'description' => __('For example: Contact, Navigate, Support.', 'daisy-a-ripple-song'),
            ],
            [
                'type' => 'repeater',
                'key' => 'items',
                'label' => __('Items', 'daisy-a-ripple-song'),
                'description' => __('Add text-only rows or links. Empty text rows will not be rendered.', 'daisy-a-ripple-song'),
                'fields' => [
                    [
                        'type' => 'text',
                        'key' => 'text',
                        'label' => __('Text', 'daisy-a-ripple-song'),
                        'placeholder' => __('Display text', 'daisy-a-ripple-song'),
                    ],
                    [
                        'type' => 'url',
                        'key' => 'url',
                        'label' => __('URL', 'daisy-a-ripple-song'),
                        'placeholder' => 'https://example.com',
                    ],
                    [
                        'type' => 'checkbox',
                        'key' => 'new_tab',
                        'label' => __('Open in new tab', 'daisy-a-ripple-song'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Return default values for the widget instance.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array
    {
        return [
            'title' => '',
            'items' => [],
        ];
    }

    /**
     * Render the widget output.
     *
     * @param array $args Widget arguments from the sidebar registration.
     * @param array $instance Saved widget option values.
     * @return void
     */
    public function frontEnd($args, $instance): void
    {
        /** @var array<string,mixed> $widgetInstance Widget instance merged with defaults. */
        $widgetInstance = $this->mergeInstanceDefaults(is_array($instance) ? $instance : []);

        echo $this->renderTemplate('footer-links', [
            'title' => $this->textValue($widgetInstance, 'title'),
            'items' => $this->getSanitizedItems($widgetInstance['items'] ?? []),
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Sanitize footer item rows.
     *
     * @param mixed $items Raw footer item configuration.
     * @return array<int,array<string,mixed>>
     */
    protected function getSanitizedItems(mixed $items): array
    {
        /** @var array<int,array<string,mixed>> $sanitizedItems Sanitized footer item list. */
        $sanitizedItems = [];

        if (!is_array($items)) {
            return $sanitizedItems;
        }

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            /** @var string $text Footer row text label. */
            $text = !empty($item['text']) ? sanitize_text_field((string) $item['text']) : '';

            if ($text === '') {
                continue;
            }

            $sanitizedItems[] = [
                'text' => $text,
                'url' => !empty($item['url']) ? esc_url_raw((string) $item['url']) : '',
                'new_tab' => !empty($item['new_tab']),
            ];
        }

        return $sanitizedItems;
    }
}
