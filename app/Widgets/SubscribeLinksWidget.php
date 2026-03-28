<?php

namespace App\Widgets;

/**
 * Subscribe Links Widget
 *
 * Display podcast subscription links for common listening platforms.
 */
class SubscribeLinksWidget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'subscribe_links_widget',
            __('aripplesong - Subscribe Links', 'a-ripple-song'),
            ['description' => __('Display podcast subscription platform links', 'a-ripple-song')]
        );
    }

    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments from the sidebar registration.
     * @param array $instance Saved widget option values.
     * @return void
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        /** @var string $title Widget heading displayed above the buttons. */
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : __('SUBSCRIBE', 'a-ripple-song');

        /** @var array<string, string> $links Configured platform links keyed by slug. */
        $links = [
            'apple' => !empty($instance['apple_podcast_url']) ? esc_url((string) $instance['apple_podcast_url']) : '',
            'spotify' => !empty($instance['spotify_url']) ? esc_url((string) $instance['spotify_url']) : '',
            'youtube' => !empty($instance['youtube_music_url']) ? esc_url((string) $instance['youtube_music_url']) : '',
        ];

        echo WidgetView::render('subscribe-links', [
            'title' => $title,
            'links' => $links,
        ]);

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form displayed in the WordPress admin.
     *
     * @param array $instance Current widget settings.
     * @return void
     */
    public function form($instance)
    {
        /** @var string $title Current widget title. */
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : __('SUBSCRIBE', 'a-ripple-song');

        /** @var string $applePodcastUrl Apple Podcasts URL. */
        $applePodcastUrl = !empty($instance['apple_podcast_url']) ? esc_url((string) $instance['apple_podcast_url']) : '';

        /** @var string $spotifyUrl Spotify URL. */
        $spotifyUrl = !empty($instance['spotify_url']) ? esc_url((string) $instance['spotify_url']) : '';

        /** @var string $youtubeMusicUrl YouTube Music URL. */
        $youtubeMusicUrl = !empty($instance['youtube_music_url']) ? esc_url((string) $instance['youtube_music_url']) : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>"
                   placeholder="SUBSCRIBE">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('apple_podcast_url')); ?>">
                <?php esc_html_e('Apple Podcast Link:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('apple_podcast_url')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('apple_podcast_url')); ?>"
                   type="url"
                   value="<?php echo esc_attr($applePodcastUrl); ?>"
                   placeholder="https://podcasts.apple.com/...">
            <small class="description"><?php esc_html_e('Leave blank to hide this button', 'a-ripple-song'); ?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('spotify_url')); ?>">
                <?php esc_html_e('Spotify Link:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('spotify_url')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('spotify_url')); ?>"
                   type="url"
                   value="<?php echo esc_attr($spotifyUrl); ?>"
                   placeholder="https://open.spotify.com/...">
            <small class="description"><?php esc_html_e('Leave blank to hide this button', 'a-ripple-song'); ?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('youtube_music_url')); ?>">
                <?php esc_html_e('YouTube Music Link:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('youtube_music_url')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('youtube_music_url')); ?>"
                   type="url"
                   value="<?php echo esc_attr($youtubeMusicUrl); ?>"
                   placeholder="https://music.youtube.com/...">
            <small class="description"><?php esc_html_e('Leave blank to hide this button', 'a-ripple-song'); ?></small>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $newInstance New widget settings submitted from the form.
     * @param array $oldInstance Previous widget settings.
     * @return array Sanitized settings to be saved.
     */
    public function update($newInstance, $oldInstance)
    {
        /** @var array<string, mixed> $instance Sanitized widget settings to persist. */
        $instance = [];

        $instance['title'] = !empty($newInstance['title']) ? sanitize_text_field((string) $newInstance['title']) : 'SUBSCRIBE';
        $instance['apple_podcast_url'] = !empty($newInstance['apple_podcast_url']) ? esc_url_raw((string) $newInstance['apple_podcast_url']) : '';
        $instance['spotify_url'] = !empty($newInstance['spotify_url']) ? esc_url_raw((string) $newInstance['spotify_url']) : '';
        $instance['youtube_music_url'] = !empty($newInstance['youtube_music_url']) ? esc_url_raw((string) $newInstance['youtube_music_url']) : '';

        return $instance;
    }
}
