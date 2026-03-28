<?php

namespace App\Widgets;

/**
 * Widget Admin Assets
 *
 * Load the repeatable field controls used by custom widgets in the admin area.
 */
class WidgetAdminAssets
{

    /**
     * Enqueue media support and inline scripts for widget forms.
     *
     * @param string $hookSuffix Current admin page hook suffix.
     * @return void
     */
    public function enqueueAssets(string $hookSuffix): void
    {
        if (!in_array($hookSuffix, ['widgets.php', 'customize.php'], true)) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $this->getInlineScript());
    }

    /**
     * Build the inline JavaScript used by repeatable widget fields.
     *
     * @return string JavaScript source code.
     */
    protected function getInlineScript(): string
    {
        /** @var string $labelsJson JSON encoded labels consumed by the admin script. */
        $labelsJson = wp_json_encode([
            'imageUrl' => __('Image URL:', 'a-ripple-song'),
            'imageUrlPlaceholder' => __('Image URL', 'a-ripple-song'),
            'selectImage' => __('Select Image', 'a-ripple-song'),
            'selectBannerImage' => __('Select Banner Image', 'a-ripple-song'),
            'useThisImage' => __('Use This Image', 'a-ripple-song'),
            'linkUrlOptional' => __('Link URL (optional):', 'a-ripple-song'),
            'linkTarget' => __('Link Target:', 'a-ripple-song'),
            'currentPage' => __('Current Page', 'a-ripple-song'),
            'newTab' => __('New Tab', 'a-ripple-song'),
            'description' => __('Description:', 'a-ripple-song'),
            'imageDescription' => __('Image description', 'a-ripple-song'),
            'delete' => __('Delete', 'a-ripple-song'),
            'text' => __('Text:', 'a-ripple-song'),
            'displayText' => __('Display text', 'a-ripple-song'),
            'urlOptionalPlainText' => __('URL (optional - leave empty for plain text):', 'a-ripple-song'),
            'openInNewTab' => __('Open in new tab', 'a-ripple-song'),
        ]);

        return <<<JS
(function($) {
    'use strict';

    const labels = {$labelsJson};

    function triggerChangeFlag(widgetForm, selector) {
        const flagInput = widgetForm.find(selector);
        if (flagInput.length) {
            flagInput.trigger('change');
        }
    }

    function updateBannerPreview(slideItem, url) {
        const preview = slideItem.find('.banner-image-preview');
        const safeUrl = (url || '').trim();

        if (!safeUrl) {
            preview.remove();
            return;
        }

        if (preview.length) {
            preview.find('img').attr('src', safeUrl);
            return;
        }

        slideItem.find('.banner-image-url-row').after(
            '<div class="banner-image-preview" style="margin-top: 8px;">' +
                '<img src="' + safeUrl + '" style="max-width: 100%; height: auto; max-height: 150px; border-radius: 4px;">' +
            '</div>'
        );
    }

    $(document).on('click', '.banner-add-slide', function(event) {
        event.preventDefault();

        const button = $(this);
        const widgetForm = button.closest('.banner-carousel-widget-form');
        const fieldNamePrefix = widgetForm.data('field-prefix');
        const container = $('#' + button.data('widget-id') + '_container');
        const slideCount = container.find('.banner-slide-item').length;

        if (!fieldNamePrefix) {
            return;
        }

        const slideHtml = '' +
            '<div class="banner-slide-item" style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">' +
                '<div class="banner-image-url-row" style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.imageUrl + '</label>' +
                    '<div style="display: flex; gap: 5px;">' +
                        '<input type="text" class="widefat banner-image-url" name="' + fieldNamePrefix + '[' + slideCount + '][image]" placeholder="' + labels.imageUrlPlaceholder + '" style="flex: 1;">' +
                        '<button type="button" class="button banner-select-image" style="flex-shrink: 0;">' + labels.selectImage + '</button>' +
                    '</div>' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.linkUrlOptional + '</label>' +
                    '<input type="url" class="widefat banner-link-url" name="' + fieldNamePrefix + '[' + slideCount + '][link]" placeholder="https://example.com">' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.linkTarget + '</label>' +
                    '<select class="widefat banner-link-target" name="' + fieldNamePrefix + '[' + slideCount + '][link_target]">' +
                        '<option value="_self">' + labels.currentPage + '</option>' +
                        '<option value="_blank">' + labels.newTab + '</option>' +
                    '</select>' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.description + '</label>' +
                    '<input type="text" class="widefat banner-description" name="' + fieldNamePrefix + '[' + slideCount + '][description]" placeholder="' + labels.imageDescription + '">' +
                '</div>' +
                '<div style="text-align: right;">' +
                    '<button type="button" class="button button-link button-link-delete banner-remove-slide" style="color: #b32d2e;">' + labels.delete + '</button>' +
                '</div>' +
            '</div>';

        container.append(slideHtml);
        triggerChangeFlag(widgetForm, '.banner-slides-flag');
    });

    $(document).on('click', '.banner-remove-slide', function(event) {
        event.preventDefault();

        const slideItem = $(this).closest('.banner-slide-item');
        const container = slideItem.closest('.banner-slides-container');
        const widgetForm = slideItem.closest('.banner-carousel-widget-form');

        if (container.find('.banner-slide-item').length <= 1) {
            slideItem.find('input').val('');
            slideItem.find('select').val('_self');
            slideItem.find('.banner-image-preview').remove();
        } else {
            slideItem.remove();
        }

        triggerChangeFlag(widgetForm, '.banner-slides-flag');
    });

    $(document).on('click', '.banner-select-image', function(event) {
        event.preventDefault();

        const button = $(this);
        const slideItem = button.closest('.banner-slide-item');
        const imageInput = slideItem.find('.banner-image-url');
        const mediaFrame = wp.media({
            title: labels.selectBannerImage,
            button: {
                text: labels.useThisImage,
            },
            multiple: false,
        });

        mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            imageInput.val(attachment.url).trigger('change');
            updateBannerPreview(slideItem, attachment.url);
        });

        mediaFrame.open();
    });

    $(document).on('input', '.banner-image-url', function() {
        const input = $(this);
        const slideItem = input.closest('.banner-slide-item');
        updateBannerPreview(slideItem, input.val());
    });

    $(document).on('click', '.footer-add-link', function(event) {
        event.preventDefault();

        const button = $(this);
        const widgetForm = button.closest('.footer-links-widget-form');
        const fieldNamePrefix = widgetForm.data('field-prefix');
        const container = $('#' + button.data('widget-id') + '_container');
        const itemCount = container.find('.footer-link-item').length;

        if (!fieldNamePrefix) {
            return;
        }

        const itemHtml = '' +
            '<div class="footer-link-item" style="margin-bottom: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.text + '</label>' +
                    '<input type="text" class="widefat footer-link-text" name="' + fieldNamePrefix + '[' + itemCount + '][text]" placeholder="' + labels.displayText + '">' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label style="display: block; margin-bottom: 4px; font-weight: 600;">' + labels.urlOptionalPlainText + '</label>' +
                    '<input type="url" class="widefat footer-link-url" name="' + fieldNamePrefix + '[' + itemCount + '][url]" placeholder="https://example.com">' +
                '</div>' +
                '<div style="margin-bottom: 8px;">' +
                    '<label><input type="checkbox" class="footer-link-new-tab" name="' + fieldNamePrefix + '[' + itemCount + '][new_tab]" value="1"> ' + labels.openInNewTab + '</label>' +
                '</div>' +
                '<div style="text-align: right;">' +
                    '<button type="button" class="button button-link button-link-delete footer-remove-link" style="color: #b32d2e;">' + labels.delete + '</button>' +
                '</div>' +
            '</div>';

        container.append(itemHtml);
        triggerChangeFlag(widgetForm, '.footer-links-flag');
    });

    $(document).on('click', '.footer-remove-link', function(event) {
        event.preventDefault();

        const item = $(this).closest('.footer-link-item');
        const container = item.closest('.footer-links-container');
        const widgetForm = item.closest('.footer-links-widget-form');

        if (container.find('.footer-link-item').length <= 1) {
            item.find('input[type="text"], input[type="url"]').val('');
            item.find('input[type="checkbox"]').prop('checked', false);
        } else {
            item.remove();
        }

        triggerChangeFlag(widgetForm, '.footer-links-flag');
    });
})(jQuery);
JS;
    }
}
