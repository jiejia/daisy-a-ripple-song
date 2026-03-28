<?php

namespace App\Widgets;

use App\Core\Widget as WidgetCore;

/**
 * Banner Carousel Widget
 *
 * Display a configurable banner carousel with optional slide links.
 */
class BannerCarouselWidget extends \WP_Widget
{

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'banner_carousel_widget',
            __('aripplesong - Banner Carousel', 'a-ripple-song'),
            ['description' => __('Display banner carousel with images', 'a-ripple-song')]
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

        /** @var array<int, array<string, string>> $slides Sanitized banner slide data. */
        $slides = $this->getSanitizedSlides($instance['slides'] ?? []);

        /** @var string $carouselId Unique DOM ID for the current carousel instance. */
        $carouselId = 'banner-carousel-' . $this->id;

        echo WidgetCore::render('banner-carousel', [
            'slides' => $slides,
            'carouselId' => $carouselId,
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
        /** @var array<int, array<string, string>> $slides Current slide configuration. */
        $slides = $this->getSanitizedSlides($instance['slides'] ?? []);

        if (empty($slides)) {
            $slides[] = $this->getEmptySlide();
        }

        /** @var string $widgetId DOM-safe field ID prefix. */
        $widgetId = $this->get_field_id('slides');

        /** @var string $fieldPrefix Field name prefix used by the repeatable form. */
        $fieldPrefix = $this->get_field_name('slides');
        ?>
        <div class="banner-carousel-widget-form"
             data-widget-id="<?php echo esc_attr($widgetId); ?>"
             data-field-prefix="<?php echo esc_attr($fieldPrefix); ?>">
            <p><strong><?php esc_html_e('Banner Slides:', 'a-ripple-song'); ?></strong></p>

            <div class="banner-slides-container" id="<?php echo esc_attr($widgetId); ?>_container">
                <?php foreach ($slides as $index => $slide): ?>
                    <div class="banner-slide-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <div class="banner-image-url-row" style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Image URL:', 'a-ripple-song'); ?>
                            </label>
                            <div style="display: flex; gap: 5px;">
                                <input type="text"
                                       class="widefat banner-image-url"
                                       name="<?php echo esc_attr($this->get_field_name('slides')); ?>[<?php echo esc_attr((string) $index); ?>][image]"
                                       value="<?php echo esc_attr($slide['image']); ?>"
                                       placeholder="<?php echo esc_attr__('Image URL', 'a-ripple-song'); ?>"
                                       style="flex: 1;">
                                <button type="button" class="button banner-select-image" style="flex-shrink: 0;">
                                    <?php esc_html_e('Select Image', 'a-ripple-song'); ?>
                                </button>
                            </div>

                            <?php if (!empty($slide['image'])): ?>
                                <div class="banner-image-preview" style="margin-top: 8px;">
                                    <img src="<?php echo esc_url($slide['image']); ?>" style="max-width: 100%; height: auto; max-height: 150px; border-radius: 4px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Link URL (optional):', 'a-ripple-song'); ?>
                            </label>
                            <input type="url"
                                   class="widefat banner-link-url"
                                   name="<?php echo esc_attr($this->get_field_name('slides')); ?>[<?php echo esc_attr((string) $index); ?>][link]"
                                   value="<?php echo esc_attr($slide['link']); ?>"
                                   placeholder="https://example.com">
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Link Target:', 'a-ripple-song'); ?>
                            </label>
                            <select class="widefat banner-link-target"
                                    name="<?php echo esc_attr($this->get_field_name('slides')); ?>[<?php echo esc_attr((string) $index); ?>][link_target]">
                                <option value="_self" <?php selected($slide['link_target'], '_self'); ?>>
                                    <?php esc_html_e('Current Page', 'a-ripple-song'); ?>
                                </option>
                                <option value="_blank" <?php selected($slide['link_target'], '_blank'); ?>>
                                    <?php esc_html_e('New Tab', 'a-ripple-song'); ?>
                                </option>
                            </select>
                        </div>

                        <div style="margin-bottom: 8px;">
                            <label style="display: block; margin-bottom: 4px; font-weight: 600;">
                                <?php esc_html_e('Description:', 'a-ripple-song'); ?>
                            </label>
                            <input type="text"
                                   class="widefat banner-description"
                                   name="<?php echo esc_attr($this->get_field_name('slides')); ?>[<?php echo esc_attr((string) $index); ?>][description]"
                                   value="<?php echo esc_attr($slide['description']); ?>"
                                   placeholder="<?php echo esc_attr__('Image description', 'a-ripple-song'); ?>">
                        </div>

                        <div style="text-align: right;">
                            <button type="button" class="button button-link button-link-delete banner-remove-slide" style="color: #b32d2e;">
                                <?php esc_html_e('Delete', 'a-ripple-song'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" class="banner-slides-flag" name="<?php echo esc_attr($fieldPrefix); ?>[__flag]" value="1">

            <p>
                <button type="button" class="button banner-add-slide" data-widget-id="<?php echo esc_attr($widgetId); ?>">
                    <?php esc_html_e('+ Add Banner', 'a-ripple-song'); ?>
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

        $instance['slides'] = $this->getSanitizedSlides($newInstance['slides'] ?? []);

        return $instance;
    }

    /**
     * Return the default empty slide structure.
     *
     * @return array<string, string> Empty slide defaults.
     */
    protected function getEmptySlide(): array
    {
        return [
            'image' => '',
            'link' => '',
            'description' => '',
            'link_target' => '_self',
        ];
    }

    /**
     * Sanitize the repeatable slide array.
     *
     * @param mixed $slides Raw slide configuration.
     * @return array<int, array<string, string>> Sanitized slides.
     */
    protected function getSanitizedSlides($slides): array
    {
        /** @var array<int, array<string, string>> $sanitizedSlides Sanitized slide list. */
        $sanitizedSlides = [];

        if (!is_array($slides)) {
            return $sanitizedSlides;
        }

        foreach ($slides as $slideKey => $slide) {
            if ($slideKey === '__flag' || !is_array($slide)) {
                continue;
            }

            /** @var string $imageUrl Slide image URL. */
            $imageUrl = !empty($slide['image']) ? esc_url_raw((string) $slide['image']) : '';

            if ($imageUrl === '') {
                continue;
            }

            /** @var string $linkTarget Slide link target value. */
            $linkTarget = !empty($slide['link_target']) && in_array($slide['link_target'], ['_self', '_blank'], true)
                ? (string) $slide['link_target']
                : '_self';

            $sanitizedSlides[] = [
                'image' => $imageUrl,
                'link' => !empty($slide['link']) ? esc_url_raw((string) $slide['link']) : '',
                'description' => !empty($slide['description']) ? sanitize_text_field((string) $slide['description']) : '',
                'link_target' => $linkTarget,
            ];
        }

        return $sanitizedSlides;
    }
}
