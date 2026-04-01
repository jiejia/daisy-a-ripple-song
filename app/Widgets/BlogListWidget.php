<?php

namespace App\Widgets;

use App\Core\Widget as WidgetCore;

/**
 * Blog List Widget
 *
 * Display a configurable list of the latest blog posts.
 */
class BlogListWidget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'blog_list_widget',
            __('aripplesong - Blog List', 'a-ripple-song'),
            ['description' => __('Display latest blog posts list', 'a-ripple-song')]
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

        /** @var string $title Widget title displayed above the post grid. */
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : __('BLOG', 'a-ripple-song');

        /** @var int $postsPerPage Number of posts to display. */
        $postsPerPage = !empty($instance['posts_per_page']) ? max(1, absint($instance['posts_per_page'])) : 6;

        /** @var bool $showSeeAll Whether to display the archive link. */
        $showSeeAll = isset($instance['show_see_all']) ? (bool) $instance['show_see_all'] : true;

        /** @var int $columns Number of visual columns in the widget grid. */
        $columns = !empty($instance['columns']) ? min(3, max(1, absint($instance['columns']))) : 3;

        /** @var \WP_Query $query Query object for the latest posts. */
        $query = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $postsPerPage,
            'post_status' => 'publish',
            'no_found_rows' => true,
            'ignore_sticky_posts' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => true,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        /** @var array<int, array<string, int|string>> $posts Prepared post cards for the template. */
        $posts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                /** @var int $postId Current post ID. */
                $postId = get_the_ID();

                $posts[] = [
                    'id' => $postId,
                    'title' => get_the_title($postId),
                    'permalink' => get_permalink($postId),
                ];
            }

            wp_reset_postdata();
        }

        echo WidgetCore::render('blog-list', [
            'title' => $title,
            'posts' => $posts,
            'columns' => $columns,
            'showSeeAll' => $showSeeAll,
            'archiveUrl' => $this->getBlogArchiveUrl(),
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
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : __('BLOG', 'a-ripple-song');

        /** @var int $postsPerPage Current number of posts setting. */
        $postsPerPage = !empty($instance['posts_per_page']) ? max(1, absint($instance['posts_per_page'])) : 6;

        /** @var bool $showSeeAll Current archive link toggle state. */
        $showSeeAll = isset($instance['show_see_all']) ? (bool) $instance['show_see_all'] : true;

        /** @var int $columns Current number of grid columns. */
        $columns = !empty($instance['columns']) ? min(3, max(1, absint($instance['columns']))) : 3;
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
                <?php esc_html_e('Number of posts:', 'a-ripple-song'); ?>
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
            <label for="<?php echo esc_attr($this->get_field_id('columns')); ?>">
                <?php esc_html_e('Number of columns:', 'a-ripple-song'); ?>
            </label>
            <input class="tiny-text"
                   id="<?php echo esc_attr($this->get_field_id('columns')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('columns')); ?>"
                   type="number"
                   step="1"
                   min="1"
                   max="3"
                   value="<?php echo esc_attr((string) $columns); ?>"
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

        $instance['title'] = !empty($newInstance['title']) ? sanitize_text_field((string) $newInstance['title']) : '';
        $instance['posts_per_page'] = !empty($newInstance['posts_per_page']) ? max(1, absint($newInstance['posts_per_page'])) : 6;
        $instance['columns'] = !empty($newInstance['columns']) ? min(3, max(1, absint($newInstance['columns']))) : 3;
        $instance['show_see_all'] = !empty($newInstance['show_see_all']) ? 1 : 0;

        return $instance;
    }

    /**
     * Resolve the preferred blog archive URL.
     *
     * @return string Archive URL for the site blog listing.
     */
    protected function getBlogArchiveUrl(): string
    {
        /** @var int $postsPageId The configured posts page ID. */
        $postsPageId = (int) get_option('page_for_posts');

        if ($postsPageId > 0) {
            return (string) get_permalink($postsPageId);
        }

        /** @var string $showOnFront The current front page display mode. */
        $showOnFront = (string) get_option('show_on_front');

        if ($showOnFront === 'page') {
            return (string) add_query_arg('post_type', 'post', home_url('/'));
        }

        return home_url('/');
    }
}
