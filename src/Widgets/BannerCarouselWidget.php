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
                'type' => 'hidden',
                'key' => 'gallery_ids',
                'default' => '',
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
            'gallery_ids' => '',
        ];
    }

    /**
     * Render the native widget admin form with WP Media Gallery.
     *
     * @param array<string,mixed> $instance Saved widget instance.
     * @return string
     */
    public function form($instance): string
    {
        /** @var array<string,mixed> $widgetInstance Widget instance merged with defaults. */
        $widgetInstance = $this->mergeInstanceDefaults(is_array($instance) ? $instance : []);
        $galleryIds = $widgetInstance['gallery_ids'] ?? '';
        
        $fieldId = $this->get_field_id('gallery_ids');
        $fieldName = $this->get_field_name('gallery_ids');

        ?>
        <div class="ars-gallery-widget-wrapper" style="padding: 10px; background: #f9f9f9; border: 1px solid #e2e4e7; border-radius: 4px; margin-bottom: 15px;">
            <p><strong><?php _e('Banner Images', 'daisy-a-ripple-song'); ?></strong></p>
            <p class="description"><?php _e('Manage banner images using WordPress built-in gallery manager.', 'daisy-a-ripple-song'); ?></p>
            
            <input type="hidden" id="<?php echo esc_attr($fieldId); ?>" name="<?php echo esc_attr($fieldName); ?>" value="<?php echo esc_attr($galleryIds); ?>" class="ars-gallery-ids-input" />
            
            <div class="ars-gallery-preview" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; min-height: 60px; align-items: center;">
                <?php
                if (!empty($galleryIds)) {
                    $ids = explode(',', $galleryIds);
                    foreach ($ids as $id) {
                        $url = wp_get_attachment_image_url(absint($id), 'thumbnail');
                        if ($url) {
                            echo '<img src="' . esc_url($url) . '" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #c3c4c7; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" />';
                        }
                    }
                } else {
                    echo '<span style="color: #646970; font-style: italic;">' . __('No images selected.', 'daisy-a-ripple-song') . '</span>';
                }
                ?>
            </div>
            
            <button type="button" class="button button-primary ars-manage-gallery-btn" style="width: 100%; text-align: center;">
                <span class="dashicons dashicons-images-alt2" style="line-height: 1.3; margin-right: 5px;"></span>
                <?php _e('Manage Gallery', 'daisy-a-ripple-song'); ?>
            </button>
        </div>

        <script>
            if (typeof window.arsGalleryWidgetInit === 'undefined') {
                window.arsGalleryWidgetInit = true;
                
                jQuery(document).on('click', '.ars-manage-gallery-btn', function(e) {
                    e.preventDefault();
                    
                    var btn = jQuery(this);
                    var wrapper = btn.closest('.ars-gallery-widget-wrapper');
                    var input = wrapper.find('.ars-gallery-ids-input');
                    var preview = wrapper.find('.ars-gallery-preview');
                    var ids = input.val();
                    
                    if (typeof wp === 'undefined' || !wp.media) {
                        alert('WordPress media library is not available.');
                        return;
                    }

                    var frame;
                    if (wp.media.gallery) {
                        frame = wp.media.gallery.edit('[gallery ids="' + ids + '"]');
                        frame.state('gallery-edit').on('update', function(selection) {
                            updatePreview(selection.models, input, preview, true);
                        });
                    } else {
                        frame = wp.media({
                            title: '<?php echo esc_js(__("Select Banner Images", "daisy-a-ripple-song")); ?>',
                            button: { text: '<?php echo esc_js(__("Use these images", "daisy-a-ripple-song")); ?>' },
                            multiple: true,
                            library: { type: 'image' }
                        });
                        frame.on('open', function() {
                            var selection = frame.state().get('selection');
                            if (ids) {
                                ids.split(',').forEach(function(id) {
                                    var attachment = wp.media.attachment(id);
                                    attachment.fetch();
                                    selection.add(attachment ? [attachment] : []);
                                });
                            }
                        });
                        frame.on('select', function() {
                            var selection = frame.state().get('selection');
                            updatePreview(selection.models, input, preview, false);
                        });
                        frame.open();
                    }

                    function updatePreview(models, inputElement, previewElement, isGallery) {
                        var selectedIds = [];
                        var html = '';
                        
                        if (models.length > 0) {
                            models.forEach(function(attachment) {
                                var id = isGallery ? attachment.id : attachment.get('id');
                                selectedIds.push(id);
                                var url = attachment.get('sizes') && attachment.get('sizes').thumbnail 
                                    ? attachment.get('sizes').thumbnail.url 
                                    : attachment.get('url');
                                html += '<img src="' + url + '" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #c3c4c7; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" />';
                            });
                        } else {
                            html = '<span style="color: #646970; font-style: italic;"><?php echo esc_js(__("No images selected.", "daisy-a-ripple-song")); ?></span>';
                        }
                        
                        inputElement.val(selectedIds.join(',')).trigger('change');
                        previewElement.html(html);
                    }
                });
            }
        </script>
        <?php
        
        return '';
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
        $slides = $this->getSanitizedSlides($widgetInstance['gallery_ids'] ?? '');
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
     * @param mixed $galleryIds Raw slide configuration (comma-separated IDs).
     * @return array<int,array<string,string>>
     */
    protected function getSanitizedSlides(mixed $galleryIds): array
    {
        /** @var array<int,array<string,string>> $sanitizedSlides Sanitized slide list. */
        $sanitizedSlides = [];

        if (!is_string($galleryIds) || empty($galleryIds)) {
            return $sanitizedSlides;
        }

        $ids = explode(',', $galleryIds);
        foreach ($ids as $id) {
            $id = absint($id);
            if (!$id) {
                continue;
            }

            /** @var string|false $imageUrl Slide image URL. */
            $imageUrl = wp_get_attachment_image_url($id, 'full');

            if (!$imageUrl) {
                continue;
            }

            // Retrieve attachment for caption and URL
            $attachment = get_post($id);
            $description = $attachment ? $attachment->post_excerpt : '';
            
            // Allow retrieving custom link if set on attachment meta, otherwise default to empty
            $customLink = get_post_meta($id, '_custom_link', true);

            $sanitizedSlides[] = [
                'image' => esc_url_raw($imageUrl),
                'link' => !empty($customLink) ? esc_url_raw((string) $customLink) : '',
                'description' => sanitize_text_field($description),
                'link_target' => '_self',
            ];
        }

        return $sanitizedSlides;
    }
}
