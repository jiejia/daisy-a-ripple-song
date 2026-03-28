<?php

namespace App\Widgets;

/**
 * Podcast List Widget
 *
 * Display recent, popular, and random podcast episode cards.
 */
class PodcastListWidget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'podcast_list_widget',
            __('aripplesong - ARS Episode List', 'a-ripple-song'),
            ['description' => __('Display latest ARS Episode list', 'a-ripple-song')]
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

        /** @var string $title Widget title displayed above the episode tabs. */
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : __('ARS EPISODES', 'a-ripple-song');

        /** @var int $postsPerPage Number of episodes to display per tab. */
        $postsPerPage = !empty($instance['posts_per_page']) ? max(1, absint($instance['posts_per_page'])) : 3;

        /** @var bool $showSeeAll Whether to display the archive link. */
        $showSeeAll = isset($instance['show_see_all']) ? (bool) $instance['show_see_all'] : true;

        /** @var string $episodePostType The podcast episode post type slug. */
        $episodePostType = class_exists('A_Ripple_Song_Podcast_Episodes')
            ? \A_Ripple_Song_Podcast_Episodes::POST_TYPE
            : 'ars_episode';

        /** @var array<string, array<int, array<string, mixed>>> $tabs Prepared episode lists for each tab. */
        $tabs = [
            'recent' => $this->getRecentEpisodes($episodePostType, $postsPerPage),
            'popular' => $this->getPopularEpisodes($episodePostType, $postsPerPage),
            'random' => $this->getRandomEpisodes($episodePostType, $postsPerPage),
        ];

        echo WidgetView::render('podcast-list', [
            'title' => $title,
            'showSeeAll' => $showSeeAll,
            'archiveUrl' => get_post_type_archive_link($episodePostType) ?: home_url('/'),
            'tabs' => $tabs,
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
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : __('ARS EPISODES', 'a-ripple-song');

        /** @var int $postsPerPage Current number of episodes per tab. */
        $postsPerPage = !empty($instance['posts_per_page']) ? max(1, absint($instance['posts_per_page'])) : 3;

        /** @var bool $showSeeAll Current archive link toggle state. */
        $showSeeAll = isset($instance['show_see_all']) ? (bool) $instance['show_see_all'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'a-ripple-song'); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                   type="text"
                   value="<?php echo esc_attr($title); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>">
                <?php esc_html_e('Number of episodes:', 'a-ripple-song'); ?>
            </label>
            <input class="tiny-text"
                   id="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('posts_per_page')); ?>"
                   type="number"
                   step="1"
                   min="1"
                   value="<?php echo esc_attr((string) $postsPerPage); ?>"
                   size="3">
        </p>

        <p>
            <input class="checkbox"
                   type="checkbox"
                   <?php checked($showSeeAll); ?>
                   id="<?php echo esc_attr($this->get_field_id('show_see_all')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('show_see_all')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_see_all')); ?>">
                <?php esc_html_e('Show "See all" link', 'a-ripple-song'); ?>
            </label>
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

        $instance['title'] = !empty($newInstance['title']) ? sanitize_text_field((string) $newInstance['title']) : 'ARS EPISODES';
        $instance['posts_per_page'] = !empty($newInstance['posts_per_page']) ? max(1, absint($newInstance['posts_per_page'])) : 3;
        $instance['show_see_all'] = !empty($newInstance['show_see_all']) ? 1 : 0;

        return $instance;
    }

    /**
     * Query the most recent episode cards.
     *
     * @param string $postType     Podcast episode post type slug.
     * @param int    $postsPerPage Number of episodes to fetch.
     * @return array<int, array<string, mixed>> Prepared episode cards.
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
     * @param string $postType     Podcast episode post type slug.
     * @param int    $postsPerPage Number of episodes to return.
     * @return array<int, array<string, mixed>> Prepared episode cards.
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

        /** @var array<int, array{post: \WP_Post, score: int}> $scoredPosts Scored episode candidates. */
        $scoredPosts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                /** @var int $postId Current episode ID. */
                $postId = get_the_ID();

                /** @var int $score Combined popularity score from views and plays. */
                $score = (int) get_post_meta($postId, '_views_count', true)
                    + (int) get_post_meta($postId, '_play_count', true);

                $scoredPosts[] = [
                    'post' => get_post($postId),
                    'score' => $score,
                ];
            }

            wp_reset_postdata();
        }

        usort($scoredPosts, static function (array $left, array $right): int {
            return $right['score'] <=> $left['score'];
        });

        /** @var array<int, array<string, mixed>> $episodes Prepared popular episode cards. */
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
     * @param string $postType     Podcast episode post type slug.
     * @param int    $postsPerPage Number of episodes to fetch.
     * @return array<int, array<string, mixed>> Prepared episode cards.
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
     * @return array<int, array<string, mixed>> Prepared episode cards.
     */
    protected function prepareEpisodesFromQuery(\WP_Query $query): array
    {
        /** @var array<int, array<string, mixed>> $episodes Prepared episode cards. */
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
     * @return array<string, mixed> Prepared episode card data.
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
     * @return string Episode audio file URL.
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
