<?php

namespace App\Widgets;

/**
 * Footer Links Widget
 *
 * Display a configurable list of footer links or plain text rows.
 */
class FooterLinksWidget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'footer_links_widget',
            __('aripplesong - Footer Links', 'a-ripple-song'),
            ['description' => __('Display a list of links or text items in the footer', 'a-ripple-song')]
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

        /** @var string $title Footer column title. */
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : '';

        /** @var array<int, array<string, mixed>> $items Sanitized footer items. */
        $items = $this->getSanitizedItems($instance['items'] ?? []);

        echo WidgetView::render('footer-links', [
            'title' => $title,
            'items' => $items,
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
        $title = !empty($instance['title']) ? sanitize_text_field((string) $instance['title']) : '';

        /** @var array<int, array<string, mixed>> $items Current footer item configuration. */
        $items = $this->getSanitizedItems($instance['items'] ?? []);

        if (empty($items)) {
            $items[] = [
                'text' => '',
                'url' => '',
                'new_tab' => false,
            ];
        }

        /** @var string $widgetId DOM-safe field ID prefix. */
        $widgetId = $this->get_field_id('items');

        /** @var string $fieldPrefix Field name prefix used by the repeatable form. */
        $fieldPrefix = $this->get_field_name('items');
        ?>
        <div class="footer-links-widget-form" data-field-prefix="<?php echo esc_attr($fieldPrefix); ?>">
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                    <?php esc_html_e('Title:', 'a-ripple-song'); ?>
                </label>
                <input class="widefat"
                       id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                       name="<?php echo esc_attr($this->get_field_name('title')); ?>"
                       type="text"
                       value="<?php echo esc_attr($title); ?>"
                       placeholder="<?php echo esc_attr__('e.g., Contact, Navigate, Support', 'a-ripple-song'); ?>">
            </p>

            <p style="margin-bottom: 8px;">
                <strong><?php esc_html_e('Items:', 'a-ripple-song'); ?></strong>
            </p>

            <div id="<?php echo esc_attr($widgetId); ?>_container" class="footer-links-container" style="margin-bottom: 10px;">
                <?php foreach ($items as $index => $item): ?>
                    <div class="footer-link-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Text:', 'a-ripple-song'); ?>
                            </label>
                            <input type="text"
                                   class="widefat footer-link-text"
                                   name="<?php echo esc_attr($fieldPrefix); ?>[<?php echo esc_attr((string) $index); ?>][text]"
                                   value="<?php echo esc_attr((string) ($item['text'] ?? '')); ?>"
                                   placeholder="<?php echo esc_attr__('Display text', 'a-ripple-song'); ?>">
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('URL (optional - leave empty for plain text):', 'a-ripple-song'); ?>
                            </label>
                            <input type="url"
                                   class="widefat footer-link-url"
                                   name="<?php echo esc_attr($fieldPrefix); ?>[<?php echo esc_attr((string) $index); ?>][url]"
                                   value="<?php echo esc_attr((string) ($item['url'] ?? '')); ?>"
                                   placeholder="https://example.com">
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label>
                                <input type="checkbox"
                                       class="footer-link-new-tab"
                                       name="<?php echo esc_attr($fieldPrefix); ?>[<?php echo esc_attr((string) $index); ?>][new_tab]"
                                       value="1"
                                       <?php checked(!empty($item['new_tab'])); ?>>
                                <?php esc_html_e('Open in new tab', 'a-ripple-song'); ?>
                            </label>
                        </div>

                        <div style="text-align: right;">
                            <button type="button" class="button button-link button-link-delete footer-remove-link" style="color: #b32d2e;">
                                <?php esc_html_e('Delete', 'a-ripple-song'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" class="footer-links-flag" name="<?php echo esc_attr($this->get_field_name('_flag')); ?>" value="1">

            <p>
                <button type="button" class="button footer-add-link" data-widget-id="<?php echo esc_attr($widgetId); ?>">
                    <?php esc_html_e('+ Add Item', 'a-ripple-song'); ?>
                </button>
            </p>
        </div>
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
        $instance['items'] = $this->getSanitizedItems($newInstance['items'] ?? []);

        return $instance;
    }

    /**
     * Sanitize footer item rows.
     *
     * @param mixed $items Raw footer item configuration.
     * @return array<int, array<string, mixed>> Sanitized footer items.
     */
    protected function getSanitizedItems($items): array
    {
        /** @var array<int, array<string, mixed>> $sanitizedItems Sanitized footer item list. */
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
