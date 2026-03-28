<?php

namespace App\Widgets;

use App\Core\Widget as WidgetCore;

/**
 * Tags Cloud Widget
 *
 * Display a configurable cloud of post tags.
 */
class TagsCloudWidget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'tags_cloud_widget',
            __('aripplesong - Tags Cloud', 'a-ripple-song'),
            ['description' => __('Display article tags cloud', 'a-ripple-song')]
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

        /** @var string $title Widget heading. */
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : __('TAGS', 'a-ripple-song');

        /** @var int $number Number of tags to show. */
        $number = !empty($instance['number']) ? max(1, absint($instance['number'])) : 20;

        /** @var string $orderby Current tag order field. */
        $orderby = !empty($instance['orderby']) ? sanitize_key((string) $instance['orderby']) : 'count';

        /** @var string $order Current tag sort direction. */
        $order = !empty($instance['order']) ? strtoupper(sanitize_key((string) $instance['order'])) : 'DESC';

        /** @var \WP_Term[] $tags Retrieved tag objects. */
        $tags = get_tags([
            'number' => $number,
            'orderby' => in_array($orderby, ['count', 'name', 'term_id', 'rand'], true) ? $orderby : 'count',
            'order' => in_array($order, ['ASC', 'DESC'], true) ? $order : 'DESC',
            'hide_empty' => true,
        ]);

        echo WidgetCore::render('tags-cloud', [
            'title' => $title,
            'tags' => $tags,
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
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : __('TAGS', 'a-ripple-song');

        /** @var int $number Current number of tags setting. */
        $number = !empty($instance['number']) ? max(1, absint($instance['number'])) : 20;

        /** @var string $orderby Current tag order field. */
        $orderby = !empty($instance['orderby']) ? sanitize_key((string) $instance['orderby']) : 'count';

        /** @var string $order Current tag sort direction. */
        $order = !empty($instance['order']) ? strtoupper(sanitize_key((string) $instance['order'])) : 'DESC';
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
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>">
                <?php esc_html_e('Number of tags:', 'a-ripple-song'); ?>
            </label>
            <input class="tiny-text"
                   id="<?php echo esc_attr($this->get_field_id('number')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('number')); ?>"
                   type="number"
                   step="1"
                   min="1"
                   value="<?php echo esc_attr((string) $number); ?>"
                   size="3">
            <small class="description"><?php esc_html_e('Maximum number of tags to display', 'a-ripple-song'); ?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('orderby')); ?>">
                <?php esc_html_e('Order by:', 'a-ripple-song'); ?>
            </label>
            <select class="widefat"
                    id="<?php echo esc_attr($this->get_field_id('orderby')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('orderby')); ?>">
                <option value="count" <?php selected($orderby, 'count'); ?>><?php esc_html_e('Post Count', 'a-ripple-song'); ?></option>
                <option value="name" <?php selected($orderby, 'name'); ?>><?php esc_html_e('Tag Name', 'a-ripple-song'); ?></option>
                <option value="term_id" <?php selected($orderby, 'term_id'); ?>><?php esc_html_e('Tag ID', 'a-ripple-song'); ?></option>
                <option value="rand" <?php selected($orderby, 'rand'); ?>><?php esc_html_e('Random', 'a-ripple-song'); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('order')); ?>">
                <?php esc_html_e('Sort order:', 'a-ripple-song'); ?>
            </label>
            <select class="widefat"
                    id="<?php echo esc_attr($this->get_field_id('order')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('order')); ?>">
                <option value="DESC" <?php selected($order, 'DESC'); ?>><?php esc_html_e('Descending (High to Low/Z to A)', 'a-ripple-song'); ?></option>
                <option value="ASC" <?php selected($order, 'ASC'); ?>><?php esc_html_e('Ascending (Low to High/A to Z)', 'a-ripple-song'); ?></option>
            </select>
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

        $instance['title'] = !empty($newInstance['title']) ? sanitize_text_field((string) $newInstance['title']) : 'TAGS';
        $instance['number'] = !empty($newInstance['number']) ? max(1, absint($newInstance['number'])) : 20;
        $instance['orderby'] = !empty($newInstance['orderby']) && in_array($newInstance['orderby'], ['count', 'name', 'term_id', 'rand'], true)
            ? (string) $newInstance['orderby']
            : 'count';
        $instance['order'] = !empty($newInstance['order']) && in_array($newInstance['order'], ['ASC', 'DESC'], true)
            ? (string) $newInstance['order']
            : 'DESC';

        return $instance;
    }
}
