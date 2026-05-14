<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Carbon_Fields\Field;
use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;

/**
 * Tags Cloud Widget.
 */
class TagsCloudWidget extends AbstractWidget
{
    /**
     * Return the WordPress widget ID.
     *
     * @return string
     */
    public function widgetId(): string
    {
        return 'tags_cloud_widget';
    }

    /**
     * Return the translated widget title.
     *
     * @return string
     */
    public function widgetTitle(): string
    {
        return __('aripplesong - Tags Cloud', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated widget description.
     *
     * @return string
     */
    public function widgetDescription(): string
    {
        return __('Display article tags cloud', 'daisy-a-ripple-song');
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
                ->set_attribute('placeholder', __('TAGS', 'daisy-a-ripple-song'))
                ->set_default_value((string) $this->defaultSettings()['title']),
            Field::make('text', $this->fieldName('number'), __('Number of tags', 'daisy-a-ripple-song'))
                ->set_attribute('type', 'number')
                ->set_attribute('min', '1')
                ->set_attribute('step', '1')
                ->set_attribute('placeholder', '20')
                ->set_default_value((string) $this->defaultSettings()['number'])
                ->set_help_text(__('Maximum number of tags to display.', 'daisy-a-ripple-song')),
            Field::make('select', $this->fieldName('orderby'), __('Order by', 'daisy-a-ripple-song'))
                ->set_options([
                    'count' => __('Post Count', 'daisy-a-ripple-song'),
                    'name' => __('Tag Name', 'daisy-a-ripple-song'),
                    'term_id' => __('Tag ID', 'daisy-a-ripple-song'),
                    'rand' => __('Random', 'daisy-a-ripple-song'),
                ])
                ->set_default_value((string) $this->defaultSettings()['orderby']),
            Field::make('select', $this->fieldName('order'), __('Sort order', 'daisy-a-ripple-song'))
                ->set_options([
                    'DESC' => __('Descending (High to Low/Z to A)', 'daisy-a-ripple-song'),
                    'ASC' => __('Ascending (Low to High/A to Z)', 'daisy-a-ripple-song'),
                ])
                ->set_default_value((string) $this->defaultSettings()['order']),
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
            'title' => __('TAGS', 'daisy-a-ripple-song'),
            'number' => 20,
            'orderby' => 'count',
            'order' => 'DESC',
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
        /** @var string $title Widget heading. */
        $title = $this->textValue($widgetInstance, 'title', __('TAGS', 'daisy-a-ripple-song'));
        /** @var int $number Number of tags to show. */
        $number = $this->intValue($widgetInstance, 'number', 20);
        /** @var string $orderby Current tag order field. */
        $orderby = $this->choiceValue($widgetInstance, 'orderby', ['count', 'name', 'term_id', 'rand'], 'count');
        /** @var string $order Current tag sort direction. */
        $order = strtoupper($this->choiceValue($widgetInstance, 'order', ['asc', 'desc', 'ASC', 'DESC'], 'DESC'));

        /** @var \WP_Term[] $tags Retrieved tag objects. */
        $tags = get_tags([
            'number' => $number,
            'orderby' => $orderby,
            'order' => in_array($order, ['ASC', 'DESC'], true) ? $order : 'DESC',
            'hide_empty' => true,
        ]);

        echo $this->renderTemplate('tags-cloud', [
            'title' => $title,
            'tags' => $tags,
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
