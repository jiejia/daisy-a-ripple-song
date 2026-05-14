<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Carbon_Fields\Field;
use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;

/**
 * Podcast List Widget.
 */
class PodcastListWidget extends AbstractWidget
{
    /**
     * Return the WordPress widget ID.
     *
     * @return string
     */
    public function widgetId(): string
    {
        return 'podcast_list_widget';
    }

    /**
     * Return the translated widget title.
     *
     * @return string
     */
    public function widgetTitle(): string
    {
        return __('aripplesong - Podcast List', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated widget description.
     *
     * @return string
     */
    public function widgetDescription(): string
    {
        return __('Display latest podcast list', 'daisy-a-ripple-song');
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
                ->set_attribute('placeholder', __('PODCASTS', 'daisy-a-ripple-song'))
                ->set_default_value((string) $this->defaultSettings()['title']),
            Field::make('text', $this->fieldName('posts_per_page'), __('Number of episodes', 'daisy-a-ripple-song'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', '1')
                ->set_attribute('step', '1')
                ->set_attribute('placeholder', '3')
                ->set_default_value((string) $this->defaultSettings()['posts_per_page']),
            Field::make('checkbox', $this->fieldName('show_see_all'), __('Show "See all" link', 'daisy-a-ripple-song'))
                ->set_option_value('1')
                ->set_default_value((bool) $this->defaultSettings()['show_see_all']),
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
            'title' => __('PODCASTS', 'daisy-a-ripple-song'),
            'posts_per_page' => 3,
            'show_see_all' => true,
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
        /** @var string $title Widget title displayed above the episode tabs. */
        $title = $this->getWidgetTitle($widgetInstance);
        /** @var int $postsPerPage Number of episodes to display per tab. */
        $postsPerPage = $this->intValue($widgetInstance, 'posts_per_page', 3);
        /** @var bool $showSeeAll Whether to display the archive link. */
        $showSeeAll = $this->boolValue($widgetInstance, 'show_see_all', true);
        /** @var string $episodePostType The podcast episode post type slug. */
        $episodePostType = \Jiejia\ARippleSong\CPTs\Episode::slug();

        echo $this->renderTemplate('podcast-list', [
            'title' => $title,
            'showSeeAll' => $showSeeAll,
            'archiveUrl' => get_post_type_archive_link($episodePostType) ?: home_url('/'),
            'tabs' => [
                'recent' => $this->getRecentEpisodes($episodePostType, $postsPerPage),
                'popular' => $this->getPopularEpisodes($episodePostType, $postsPerPage),
                'random' => $this->getRandomEpisodes($episodePostType, $postsPerPage),
            ],
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Return the normalized widget title while upgrading legacy defaults.
     *
     * @param array<string,mixed> $instance Saved widget options.
     * @return string
     */
    protected function getWidgetTitle(array $instance): string
    {
        /** @var string $savedTitle Raw saved title from the widget instance. */
        $savedTitle = $this->textValue($instance, 'title', __('PODCASTS', 'daisy-a-ripple-song'));

        if ($savedTitle === '' || $savedTitle === 'ARS EPISODES') {
            return __('PODCASTS', 'daisy-a-ripple-song');
        }

        return $savedTitle;
    }

    /**
     * Query the most recent episode cards.
     *
     * @param string $postType Podcast episode post type slug.
     * @param int $postsPerPage Number of episodes to fetch.
     * @return array<int,array<string,mixed>>
     */
    protected function getRecentEpisodes(string $postType, int $postsPerPage): array
    {
        /** @var \WP_Query $query Query for recent episodes. */
        $query = new \WP_Query([
            'post_type' => $postType,
            'posts_per_page' => $postsPerPage,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        return $this->prepareEpisodesFromQuery($query);
    }

    /**
     * Query and sort the most popular episode cards.
     *
     * @param string $postType Podcast episode post type slug.
     * @param int $postsPerPage Number of episodes to return.
     * @return array<int,array<string,mixed>>
     */
    protected function getPopularEpisodes(string $postType, int $postsPerPage): array
    {
        /** @var \WP_Query $query Query used to source popularity candidates. */
        $query = new \WP_Query([
            'post_type' => $postType,
            'posts_per_page' => max($postsPerPage * 3, 20),
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        /** @var array<int,array{post:\WP_Post|null,score:int}> $scoredPosts Scored episode candidates. */
        $scoredPosts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                /** @var int $postId Current episode ID. */
                $postId = get_the_ID();

                $scoredPosts[] = [
                    'post' => get_post($postId),
                    'score' => (int) get_post_meta($postId, '_views_count', true) + (int) get_post_meta($postId, '_play_count', true),
                ];
            }

            wp_reset_postdata();
        }

        usort($scoredPosts, static function (array $left, array $right): int {
            return $right['score'] <=> $left['score'];
        });

        /** @var array<int,array<string,mixed>> $episodes Prepared popular episode cards. */
        $episodes = [];

        foreach (array_slice($scoredPosts, 0, $postsPerPage) as $item) {
            if (!$item['post'] instanceof \WP_Post) {
                continue;
            }

            $episodes[] = $this->buildEpisodeCard($item['post']);
        }

        return array_values(array_filter($episodes));
    }

    /**
     * Query a random subset of episode cards.
     *
     * @param string $postType Podcast episode post type slug.
     * @param int $postsPerPage Number of episodes to fetch.
     * @return array<int,array<string,mixed>>
     */
    protected function getRandomEpisodes(string $postType, int $postsPerPage): array
    {
        /** @var \WP_Query $query Query for random episodes. */
        $query = new \WP_Query([
            'post_type' => $postType,
            'posts_per_page' => $postsPerPage,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
            'orderby' => 'rand',
        ]);

        return $this->prepareEpisodesFromQuery($query);
    }

    /**
     * Convert a query result into a list of prepared episode cards.
     *
     * @param \WP_Query $query Query object containing episode posts.
     * @return array<int,array<string,mixed>>
     */
    protected function prepareEpisodesFromQuery(\WP_Query $query): array
    {
        /** @var array<int,array<string,mixed>> $episodes Prepared episode cards. */
        $episodes = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                /** @var \WP_Post|null $post Current post object. */
                $post = get_post(get_the_ID());

                if ($post instanceof \WP_Post) {
                    $episodes[] = $this->buildEpisodeCard($post);
                }
            }

            wp_reset_postdata();
        }

        return array_values(array_filter($episodes));
    }

    /**
     * Build the card and player payload used by the widget template.
     *
     * @param \WP_Post $post Episode post object.
     * @return array<string,mixed>
     */
    protected function buildEpisodeCard(\WP_Post $post): array
    {
        /** @var int $postId Current episode ID. */
        $postId = (int) $post->ID;
        /** @var string $audioUrl Episode audio file URL. */
        $audioUrl = $this->getEpisodeAudioUrl($postId);
        /** @var string $description Plain text excerpt used by the player store. */
        $description = wp_strip_all_tags(has_excerpt($postId) ? get_the_excerpt($postId) : wp_trim_words($post->post_content, 24, ''));
        /** @var string $featuredImage Episode card image URL. */
        $featuredImage = get_the_post_thumbnail_url($postId, 'medium') ?: '';

        return [
            'id' => $postId,
            'title' => get_the_title($postId),
            'permalink' => get_permalink($postId),
            'date' => get_the_date('', $postId),
            'description' => $description,
            'featured_image' => $featuredImage,
            'player_payload' => [
                'id' => $postId,
                'audioUrl' => $audioUrl,
                'title' => get_the_title($postId),
                'description' => $description,
                'publishDate' => (int) get_post_time('U', true, $postId),
                'featuredImage' => $featuredImage,
                'link' => get_permalink($postId),
            ],
        ];
    }

    /**
     * Resolve the episode audio file URL using both public and underscored meta keys.
     *
     * @param int $postId Episode post ID.
     * @return string
     */
    protected function getEpisodeAudioUrl(int $postId): string
    {
        /** @var string $audioUrl Public meta key value. */
        $audioUrl = (string) get_post_meta($postId, 'audio_file', true);

        if ($audioUrl !== '') {
            return $audioUrl;
        }

        return (string) get_post_meta($postId, '_audio_file', true);
    }
}
