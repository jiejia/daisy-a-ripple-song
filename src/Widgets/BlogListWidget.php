<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;

/**
 * Blog List Widget.
 */
class BlogListWidget extends AbstractWidget
{
    /**
     * Return the WordPress widget ID.
     *
     * @return string
     */
    public function widgetId(): string
    {
        return 'blog_list_widget';
    }

    /**
     * Return the translated widget title.
     *
     * @return string
     */
    public function widgetTitle(): string
    {
        return __('aripplesong - Blog List', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated widget description.
     *
     * @return string
     */
    public function widgetDescription(): string
    {
        return __('Display latest blog posts list', 'daisy-a-ripple-song');
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
                'placeholder' => __('BLOG', 'daisy-a-ripple-song'),
                'default' => (string) $this->defaultSettings()['title'],
            ],
            [
                'type' => 'number',
                'key' => 'posts_per_page',
                'label' => __('Number of posts', 'daisy-a-ripple-song'),
                'min' => 1,
                'step' => 1,
                'placeholder' => '6',
                'default' => (int) $this->defaultSettings()['posts_per_page'],
            ],
            [
                'type' => 'select',
                'key' => 'columns',
                'label' => __('Number of columns', 'daisy-a-ripple-song'),
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                ],
                'default' => (string) $this->defaultSettings()['columns'],
            ],
            [
                'type' => 'checkbox',
                'key' => 'show_see_all',
                'label' => __('Show "See all" link', 'daisy-a-ripple-song'),
                'default' => (bool) $this->defaultSettings()['show_see_all'],
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
            'title' => __('BLOG', 'daisy-a-ripple-song'),
            'posts_per_page' => 6,
            'columns' => 3,
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
    public function frontEnd($args, $instance): void
    {
        /** @var array<string,mixed> $widgetInstance Widget instance merged with defaults. */
        $widgetInstance = $this->mergeInstanceDefaults(is_array($instance) ? $instance : []);
        /** @var string $title Widget title displayed above the post grid. */
        $title = $this->textValue($widgetInstance, 'title', __('BLOG', 'daisy-a-ripple-song'));
        /** @var int $postsPerPage Number of posts to display. */
        $postsPerPage = $this->intValue($widgetInstance, 'posts_per_page', 6);
        /** @var bool $showSeeAll Whether to display the archive link. */
        $showSeeAll = $this->boolValue($widgetInstance, 'show_see_all', true);
        /** @var int $columns Number of visual columns in the widget grid. */
        $columns = $this->intValue($widgetInstance, 'columns', 3, 1, 3);

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

        /** @var array<int,array<string,int|string>> $posts Prepared post cards for the template. */
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

        echo $this->renderTemplate('blog-list', [
            'title' => $title,
            'posts' => $posts,
            'columns' => $columns,
            'showSeeAll' => $showSeeAll,
            'archiveUrl' => $this->getBlogArchiveUrl(),
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Resolve the preferred blog archive URL.
     *
     * @return string
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
