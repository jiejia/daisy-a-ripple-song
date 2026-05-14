<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Carbon_Fields\Field;
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
     * Return all Carbon Fields fields for the widget form.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array
    {
        return [
            Field::make('text', $this->fieldName('title'), __('Title', 'daisy-a-ripple-song'))
                ->set_attribute('placeholder', __('SUBSCRIBE', 'daisy-a-ripple-song'))
                ->set_default_value((string) $this->defaultSettings()['title']),
            Field::make('text', $this->fieldName('apple_podcast_url'), __('Apple Podcast Link', 'daisy-a-ripple-song'))
                ->set_attribute('type', 'url')
                ->set_attribute('placeholder', 'https://podcasts.apple.com/...')
                ->set_help_text(__('Leave blank to hide this button.', 'daisy-a-ripple-song')),
            Field::make('text', $this->fieldName('spotify_url'), __('Spotify Link', 'daisy-a-ripple-song'))
                ->set_attribute('type', 'url')
                ->set_attribute('placeholder', 'https://open.spotify.com/...')
                ->set_help_text(__('Leave blank to hide this button.', 'daisy-a-ripple-song')),
            Field::make('text', $this->fieldName('youtube_music_url'), __('YouTube Music Link', 'daisy-a-ripple-song'))
                ->set_attribute('type', 'url')
                ->set_attribute('placeholder', 'https://music.youtube.com/...')
                ->set_help_text(__('Leave blank to hide this button.', 'daisy-a-ripple-song')),
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
    public function front_end($args, $instance): void
    {
        /** @var array<string,mixed> $widgetInstance Widget instance merged with defaults. */
        $widgetInstance = $this->mergeInstanceDefaults(is_array($instance) ? $instance : []);

        echo $this->renderTemplate('subscribe-links', [
            'title' => $this->textValue($widgetInstance, 'title', __('SUBSCRIBE', 'daisy-a-ripple-song')),
            'links' => [
                'apple' => !empty($widgetInstance['apple_podcast_url']) ? esc_url((string) $widgetInstance['apple_podcast_url']) : '',
                'spotify' => !empty($widgetInstance['spotify_url']) ? esc_url((string) $widgetInstance['spotify_url']) : '',
                'youtube' => !empty($widgetInstance['youtube_music_url']) ? esc_url((string) $widgetInstance['youtube_music_url']) : '',
            ],
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
