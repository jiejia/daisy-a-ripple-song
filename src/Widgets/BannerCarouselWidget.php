<?php

namespace Jiejia\DaisyARippleSong\Widgets;

use Jiejia\DaisyARippleSong\Abstracts\AbstractWidget;

/**
 * Banner Carousel Widget.
 */
class BannerCarouselWidget extends AbstractWidget
{
    /**
     * Return the WordPress widget ID.
     *
     * @return string
     */
    public function widgetId(): string
    {
        return 'banner_carousel_widget';
    }

    /**
     * Return the translated widget title.
     *
     * @return string
     */
    public function widgetTitle(): string
    {
        return __('aripplesong - Banner Carousel', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated widget description.
     *
     * @return string
     */
    public function widgetDescription(): string
    {
        return __('Display banner carousel with images', 'daisy-a-ripple-song');
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
                'type' => 'repeater',
                'key' => 'slides',
                'label' => __('Banner Slides', 'daisy-a-ripple-song'),
                'description' => __('Add one or more banner slides. Empty image rows will not be rendered.', 'daisy-a-ripple-song'),
                'fields' => [
                    [
                        'type' => 'image',
                        'key' => 'image',
                        'label' => __('Image', 'daisy-a-ripple-song'),
                        'frame_title' => __('Select Banner Image', 'daisy-a-ripple-song'),
                        'button_label' => __('Use This Image', 'daisy-a-ripple-song'),
                    ],
                    [
                        'type' => 'url',
                        'key' => 'link',
                        'label' => __('Link URL', 'daisy-a-ripple-song'),
                        'placeholder' => 'https://example.com',
                    ],
                    [
                        'type' => 'select',
                        'key' => 'link_target',
                        'label' => __('Link Target', 'daisy-a-ripple-song'),
                        'options' => [
                            '_self' => __('Current Page', 'daisy-a-ripple-song'),
                            '_blank' => __('New Tab', 'daisy-a-ripple-song'),
                        ],
                        'default' => '_self',
                    ],
                    [
                        'type' => 'text',
                        'key' => 'description',
                        'label' => __('Description', 'daisy-a-ripple-song'),
                        'placeholder' => __('Image description', 'daisy-a-ripple-song'),
                    ],
                ],
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
            'slides' => [],
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
        /** @var array<int,array<string,string>> $slides Sanitized banner slide data. */
        $slides = $this->getSanitizedSlides($widgetInstance['slides'] ?? []);
        /** @var string $carouselId Unique DOM ID for the current carousel instance. */
        $carouselId = 'banner-carousel-' . $this->id;

        echo $this->renderTemplate('banner-carousel', [
            'slides' => $slides,
            'carouselId' => $carouselId,
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Sanitize the repeatable slide array.
     *
     * @param mixed $slides Raw slide configuration.
     * @return array<int,array<string,string>>
     */
    protected function getSanitizedSlides(mixed $slides): array
    {
        /** @var array<int,array<string,string>> $sanitizedSlides Sanitized slide list. */
        $sanitizedSlides = [];

        if (!is_array($slides)) {
            return $sanitizedSlides;
        }

        foreach ($slides as $slide) {
            if (!is_array($slide)) {
                continue;
            }

            /** @var string $imageUrl Slide image URL. */
            $imageUrl = $this->getSlideImageUrl($slide['image'] ?? '');

            if ($imageUrl === '') {
                continue;
            }

            /** @var string $linkTarget Slide link target value. */
            $linkTarget = !empty($slide['link_target']) && in_array((string) $slide['link_target'], ['_self', '_blank'], true)
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

    /**
     * Return a normalized image URL from a widget image value.
     *
     * @param mixed $image Raw image value from the widget instance.
     * @return string
     */
    protected function getSlideImageUrl(mixed $image): string
    {
        if (empty($image)) {
            return '';
        }

        if (is_numeric($image)) {
            /** @var string|false $attachmentUrl Attachment URL resolved from the image ID. */
            $attachmentUrl = wp_get_attachment_image_url(absint($image), 'full');

            return $attachmentUrl !== false ? esc_url_raw($attachmentUrl) : '';
        }

        return esc_url_raw((string) $image);
    }
}
