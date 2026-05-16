<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;

/**
 * Subscribe Links Widget.
 */
class SubscribeLinksWidget extends AbstractWidget
{
    /**
     * Return the WordPress widget ID.
     *
     * @return string
     */
    public function widgetId(): string
    {
        return 'subscribe_links_widget';
    }

    /**
     * Return the translated widget title.
     *
     * @return string
     */
    public function widgetTitle(): string
    {
        return __('aripplesong - Subscribe Links', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated widget description.
     *
     * @return string
     */
    public function widgetDescription(): string
    {
        return __('Display podcast subscription platform links', 'daisy-a-ripple-song');
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
                'placeholder' => __('SUBSCRIBE', 'daisy-a-ripple-song'),
                'default' => (string) $this->defaultSettings()['title'],
            ],
            [
                'type' => 'url',
                'key' => 'apple_podcast_url',
                'label' => __('Apple Podcast Link', 'daisy-a-ripple-song'),
                'placeholder' => 'https://podcasts.apple.com/...',
                'description' => __('Leave blank to hide this button.', 'daisy-a-ripple-song'),
            ],
            [
                'type' => 'url',
                'key' => 'spotify_url',
                'label' => __('Spotify Link', 'daisy-a-ripple-song'),
                'placeholder' => 'https://open.spotify.com/...',
                'description' => __('Leave blank to hide this button.', 'daisy-a-ripple-song'),
            ],
            [
                'type' => 'url',
                'key' => 'youtube_music_url',
                'label' => __('YouTube Music Link', 'daisy-a-ripple-song'),
                'placeholder' => 'https://music.youtube.com/...',
                'description' => __('Leave blank to hide this button.', 'daisy-a-ripple-song'),
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
            'title' => __('SUBSCRIBE', 'daisy-a-ripple-song'),
            'apple_podcast_url' => '',
            'spotify_url' => '',
            'youtube_music_url' => '',
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

        /** @var array<string,string> $links Sanitized subscription platform links. */
        $links = [
            'apple' => !empty($widgetInstance['apple_podcast_url']) ? esc_url((string) $widgetInstance['apple_podcast_url']) : '',
            'spotify' => !empty($widgetInstance['spotify_url']) ? esc_url((string) $widgetInstance['spotify_url']) : '',
            'youtube' => !empty($widgetInstance['youtube_music_url']) ? esc_url((string) $widgetInstance['youtube_music_url']) : '',
        ];

        /** @var bool $isWidgetEditorPreview Whether the widget is rendering inside the admin preview flow. */
        $isWidgetEditorPreview = $this->isWidgetEditorPreviewRequest();

        if ($isWidgetEditorPreview && empty(array_filter($links))) {
            $links = [
                'apple' => '#',
                'spotify' => '#',
                'youtube' => '#',
            ];
        }

        echo $this->renderTemplate('subscribe-links', [
            'title' => $this->textValue($widgetInstance, 'title', __('SUBSCRIBE', 'daisy-a-ripple-song')),
            'links' => $links,
            'isPreview' => $isWidgetEditorPreview,
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Return whether the current request is rendering a WordPress widget editor preview.
     *
     * @return bool
     */
    protected function isWidgetEditorPreviewRequest(): bool
    {
        /** @var string $requestUri Raw request URI used to identify widget REST preview routes. */
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';

        /** @var string $restRoute Explicit REST route value when WordPress uses query-string REST routing. */
        $restRoute = isset($_GET['rest_route']) ? (string) wp_unslash($_GET['rest_route']) : '';

        /** @var bool $isWidgetRestRequest Whether the current REST request belongs to the widget editor. */
        $isWidgetRestRequest = defined('REST_REQUEST')
            && REST_REQUEST
            && (
                str_contains($requestUri, '/wp/v2/widget-types/')
                || str_contains($requestUri, '/wp/v2/widgets/')
                || str_contains($restRoute, '/wp/v2/widget-types/')
                || str_contains($restRoute, '/wp/v2/widgets/')
            );

        return $isWidgetRestRequest || !empty($_GET['legacy-widget-preview']);
    }
}
