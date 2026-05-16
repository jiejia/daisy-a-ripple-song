<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Jiejia\DaisyARippleSong\Contracts\ThemeWidget;
use Jiejia\DaisyARippleSong\Supports\WidgetRenderer;
use Jiejia\DaisyARippleSong\Theme;
use WP_Widget;

/**
 * Base class for native WordPress theme widgets.
 */
abstract class AbstractWidget extends WP_Widget implements ThemeWidget
{
    /**
     * Register the widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            $this->widgetId(),
            $this->widgetTitle(),
            [
                'classname' => $this->widgetClassName(),
                'description' => $this->widgetDescription(),
            ]
        );
    }

    /**
     * Return the legacy prefixed storage key used by this widget.
     *
     * @return string
     */
    public function fieldPrefix(): string
    {
        return Theme::PREFIX . '_' . $this->widgetId() . '_';
    }

    /**
     * Return the legacy prefixed field name for one widget value.
     *
     * @param string $key Widget field key without the widget prefix.
     * @return string
     */
    public function fieldName(string $key): string
    {
        return $this->fieldPrefix() . $key;
    }

    /**
     * Return the CSS class name used by the widget wrapper.
     *
     * @return string
     */
    protected function widgetClassName(): string
    {
        return '';
    }

    /**
     * Render a widget template.
     *
     * @param string $template Template file name without extension.
     * @param array<string,mixed> $data Template data.
     * @return string
     */
    protected function renderTemplate(string $template, array $data = []): string
    {
        return WidgetRenderer::render($template, $data);
    }

    /**
     * Render the WordPress widget output.
     *
     * @param array<string,mixed> $args Sidebar display arguments.
     * @param array<string,mixed> $instance Saved widget instance.
     * @return void
     */
    public function widget($args, $instance): void
    {
        echo wp_kses_post((string) ($args['before_widget'] ?? ''));
        $this->frontEnd($args, $instance);
        echo wp_kses_post((string) ($args['after_widget'] ?? ''));
    }

    /**
     * Render the native widget admin form.
     *
     * @param array<string,mixed> $instance Saved widget instance.
     * @return string
     */
    public function form($instance): string
    {
        /** @var array<string,mixed> $widgetInstance Widget instance merged with defaults. */
        $widgetInstance = $this->mergeInstanceDefaults(is_array($instance) ? $instance : []);

        foreach ($this->fields() as $field) {
            $this->renderField($field, $widgetInstance);
        }

        return '';
    }

    /**
     * Sanitize a native widget form submission.
     *
     * @param array<string,mixed> $new_instance New widget instance data.
     * @param array<string,mixed> $old_instance Previous widget instance data.
     * @return array<string,mixed> Sanitized widget instance data.
     */
    public function update($new_instance, $old_instance): array
    {
        /** @var array<string,mixed> $submittedInstance Submitted instance values. */
        $submittedInstance = is_array($new_instance) ? $new_instance : [];
        /** @var array<string,mixed> $sanitizedInstance Sanitized instance values. */
        $sanitizedInstance = [];
        /** @var bool $hasNativeFieldValue Whether the submitted data uses the current native field keys. */
        $hasNativeFieldValue = false;

        foreach ($this->fields() as $field) {
            /** @var string $fieldKey Field key. */
            $fieldKey = (string) ($field['key'] ?? '');

            if ($fieldKey !== '' && array_key_exists($fieldKey, $submittedInstance)) {
                $hasNativeFieldValue = true;
                break;
            }
        }

        if (!$hasNativeFieldValue && $submittedInstance !== []) {
            $submittedInstance = $this->mergeInstanceDefaults($submittedInstance);
        }

        foreach ($this->fields() as $field) {
            /** @var string $fieldKey Field key. */
            $fieldKey = (string) ($field['key'] ?? '');

            if ($fieldKey === '') {
                continue;
            }

            $sanitizedInstance[$fieldKey] = $this->sanitizeFieldValue($field, $submittedInstance[$fieldKey] ?? null);
        }

        return $sanitizedInstance;
    }

    /**
     * Return instance values merged with widget defaults and legacy keys.
     *
     * @param array<string,mixed> $instance Widget instance data.
     * @return array<string,mixed>
     */
    protected function mergeInstanceDefaults(array $instance): array
    {
        /** @var array<string,mixed> $normalizedInstance Widget values normalized to raw keys. */
        $normalizedInstance = $this->defaultSettings();
        /** @var array<string,array<string,mixed>> $fieldDefinitions Field definitions keyed by raw field key. */
        $fieldDefinitions = [];

        foreach ($this->fields() as $fieldDefinition) {
            /** @var string $fieldDefinitionKey Raw field key from the field definition. */
            $fieldDefinitionKey = (string) ($fieldDefinition['key'] ?? '');

            if ($fieldDefinitionKey === '') {
                continue;
            }

            $fieldDefinitions[$fieldDefinitionKey] = $fieldDefinition;
        }

        foreach ($normalizedInstance as $key => $defaultValue) {
            /** @var string $rawKey Raw widget field key. */
            $rawKey = (string) $key;
            /** @var string $fieldName Legacy prefixed storage key for the current widget field. */
            $fieldName = $this->fieldName($rawKey);
            /** @var string $protectedFieldName Legacy protected storage key. */
            $protectedFieldName = '_' . $fieldName;

            if (array_key_exists($rawKey, $instance)) {
                $normalizedInstance[$rawKey] = $instance[$rawKey];
                continue;
            }

            if (array_key_exists($fieldName, $instance)) {
                $normalizedInstance[$rawKey] = $instance[$fieldName];
                continue;
            }

            if (array_key_exists($protectedFieldName, $instance)) {
                $normalizedInstance[$rawKey] = $instance[$protectedFieldName];
                continue;
            }

            if (($fieldDefinitions[$rawKey]['type'] ?? '') === 'repeater') {
                /** @var array<int,array<string,mixed>> $legacyRepeaterValue Legacy compact repeater rows. */
                $legacyRepeaterValue = $this->legacyRepeaterFieldValue($rawKey, $fieldDefinitions[$rawKey], $instance);

                if ($legacyRepeaterValue !== []) {
                    $normalizedInstance[$rawKey] = $legacyRepeaterValue;
                    continue;
                }
            }

            $normalizedInstance[$rawKey] = $defaultValue;
        }

        return $normalizedInstance;
    }

    /**
     * Return repeatable rows from the previous compact widget field storage.
     *
     * @param string $rawKey Raw repeater field key.
     * @param array<string,mixed> $field Repeater field definition.
     * @param array<string,mixed> $instance Widget instance data.
     * @return array<int,array<string,mixed>>
     */
    protected function legacyRepeaterFieldValue(string $rawKey, array $field, array $instance): array
    {
        /** @var array<int,array<string,mixed>> $rows Rows reconstructed from compact field keys. */
        $rows = [];
        /** @var array<int,array<string,mixed>> $childFields Repeater child field definitions. */
        $childFields = is_array($field['fields'] ?? null) ? $field['fields'] : [];
        /** @var array<string,array<string,mixed>> $childFieldDefinitions Child field definitions keyed by raw field key. */
        $childFieldDefinitions = [];
        /** @var string $legacyFieldName Legacy prefixed repeater field name. */
        $legacyFieldName = $this->fieldName($rawKey);
        /** @var string $legacyFieldNamePattern Regex-safe legacy field name. */
        $legacyFieldNamePattern = preg_quote($legacyFieldName, '/');

        foreach ($childFields as $childField) {
            /** @var string $childKey Repeater child field key. */
            $childKey = (string) ($childField['key'] ?? '');

            if ($childKey === '') {
                continue;
            }

            $childFieldDefinitions[$childKey] = $childField;
        }

        if ($childFieldDefinitions === []) {
            return [];
        }

        foreach ($instance as $storedKey => $storedValue) {
            if (!is_string($storedKey)) {
                continue;
            }

            /** @var array<int,string>|null $matches Compact repeater key matches. */
            $matches = null;

            if (!preg_match('/^_?' . $legacyFieldNamePattern . '\|([^|]+)\|([0-9]+)\|[0-9]+\|value$/', $storedKey, $matches)) {
                continue;
            }

            /** @var string $childKey Child field key from the compact storage key. */
            $childKey = $matches[1];
            /** @var int $rowIndex Repeater row index from the compact storage key. */
            $rowIndex = absint($matches[2]);

            if (!array_key_exists($childKey, $childFieldDefinitions)) {
                continue;
            }

            if (!isset($rows[$rowIndex])) {
                $rows[$rowIndex] = [];
            }

            $rows[$rowIndex][$childKey] = $this->sanitizeFieldValue($childFieldDefinitions[$childKey], $storedValue);
        }

        if ($rows === []) {
            return [];
        }

        ksort($rows);

        /** @var array<int,array<string,mixed>> $normalizedRows Rows with defaults applied and empty rows removed. */
        $normalizedRows = [];

        foreach ($rows as $row) {
            /** @var array<string,mixed> $normalizedRow Row values keyed by child field key. */
            $normalizedRow = [];

            foreach ($childFieldDefinitions as $childKey => $childField) {
                $normalizedRow[$childKey] = array_key_exists($childKey, $row)
                    ? $row[$childKey]
                    : $this->sanitizeFieldValue($childField, $childField['default'] ?? null);
            }

            if ($this->isRepeaterRowEmpty($normalizedRow, $childFields)) {
                continue;
            }

            $normalizedRows[] = $normalizedRow;
        }

        return $normalizedRows;
    }

    /**
     * Render one widget form field from a field definition.
     *
     * @param array<string,mixed> $field Field definition.
     * @param array<string,mixed> $instance Current widget instance.
     * @return void
     */
    protected function renderField(array $field, array $instance): void
    {
        /** @var string $type Field type. */
        $type = (string) ($field['type'] ?? 'text');

        if ($type === 'repeater') {
            $this->renderRepeaterField($field, $instance);
            return;
        }

        /** @var string $key Field key. */
        $key = (string) ($field['key'] ?? '');
        /** @var string $label Field label. */
        $label = (string) ($field['label'] ?? '');

        if ($key === '') {
            return;
        }

        /** @var string $fieldId Form control ID. */
        $fieldId = $this->get_field_id($key);
        /** @var string $fieldName Form control name. */
        $fieldName = $this->get_field_name($key);
        /** @var mixed $fieldValue Current form control value. */
        $fieldValue = $instance[$key] ?? ($field['default'] ?? '');

        echo '<p>';

        if ($type !== 'checkbox') {
            echo '<label for="' . esc_attr($fieldId) . '">' . esc_html($label) . '</label>';
        }

        if ($type === 'image') {
            $this->renderImageControl($field, $fieldId, $fieldName, $fieldValue);
        } elseif ($type === 'select') {
            $this->renderSelectControl($field, $fieldId, $fieldName, $fieldValue);
        } elseif ($type === 'checkbox') {
            $this->renderCheckboxControl($field, $fieldId, $fieldName, $fieldValue);
        } else {
            $this->renderInputControl($field, $fieldId, $fieldName, $fieldValue);
        }

        if (!empty($field['description'])) {
            echo '<span class="description">' . esc_html((string) $field['description']) . '</span>';
        }

        echo '</p>';
    }

    /**
     * Render a text-like widget form control.
     *
     * @param array<string,mixed> $field Field definition.
     * @param string $fieldId Form control ID.
     * @param string $fieldName Form control name.
     * @param mixed $fieldValue Form control value.
     * @return void
     */
    protected function renderInputControl(array $field, string $fieldId, string $fieldName, mixed $fieldValue): void
    {
        /** @var string $inputType HTML input type. */
        $inputType = (string) ($field['input_type'] ?? ($field['type'] ?? 'text'));

        if (($field['type'] ?? '') === 'textarea') {
            echo '<textarea class="widefat" rows="' . esc_attr((string) ($field['rows'] ?? 4)) . '" id="' . esc_attr($fieldId) . '" name="' . esc_attr($fieldName) . '" placeholder="' . esc_attr((string) ($field['placeholder'] ?? '')) . '">' . esc_textarea((string) $fieldValue) . '</textarea>';
            return;
        }

        echo '<input class="widefat" id="' . esc_attr($fieldId) . '" name="' . esc_attr($fieldName) . '" type="' . esc_attr($inputType) . '" value="' . esc_attr((string) $fieldValue) . '" placeholder="' . esc_attr((string) ($field['placeholder'] ?? '')) . '"';

        foreach (['min', 'max', 'step'] as $attributeName) {
            if (array_key_exists($attributeName, $field)) {
                echo ' ' . esc_attr($attributeName) . '="' . esc_attr((string) $field[$attributeName]) . '"';
            }
        }

        echo '>';
    }

    /**
     * Render a media-library image picker control.
     *
     * @param array<string,mixed> $field Field definition.
     * @param string $fieldId Form control ID.
     * @param string $fieldName Form control name.
     * @param mixed $fieldValue Form control value.
     * @return void
     */
    protected function renderImageControl(array $field, string $fieldId, string $fieldName, mixed $fieldValue): void
    {
        /** @var string $imageValue Stored attachment ID or legacy URL. */
        $imageValue = is_scalar($fieldValue) ? (string) $fieldValue : '';
        /** @var string $previewUrl Preview image URL resolved from the stored value. */
        $previewUrl = $this->imagePreviewUrl($imageValue);

        echo '<div class="ars-widget-image-field" data-ars-widget-image-field>';
        echo '<input type="hidden" id="' . esc_attr($fieldId) . '" name="' . esc_attr($fieldName) . '" value="' . esc_attr($imageValue) . '" data-ars-widget-image-input>';
        echo '<div class="media-widget-preview media_image ' . ($previewUrl !== '' ? 'populated' : '') . '" data-ars-widget-image-preview data-preview-url="' . esc_url($previewUrl) . '" data-select-label="' . esc_attr__('Select Image', 'daisy-a-ripple-song') . '" data-frame-title="' . esc_attr((string) ($field['frame_title'] ?? __('Select Image', 'daisy-a-ripple-song'))) . '" data-button-label="' . esc_attr((string) ($field['button_label'] ?? __('Use This Image', 'daisy-a-ripple-song'))) . '">';
        echo '<div class="attachment-media-view">';
        echo '<button type="button" class="select-media button-add-media not-selected" data-ars-widget-image-select data-frame-title="' . esc_attr((string) ($field['frame_title'] ?? __('Select Image', 'daisy-a-ripple-song'))) . '" data-button-label="' . esc_attr((string) ($field['button_label'] ?? __('Use This Image', 'daisy-a-ripple-song'))) . '">' . esc_html__('Select Image', 'daisy-a-ripple-song') . '</button>';
        echo '</div>';
        echo '</div>';
        echo '<p class="media-widget-buttons">';
        echo '<button type="button" class="button select-media ' . ($imageValue === '' ? '' : 'selected') . '" data-ars-widget-image-select data-frame-title="' . esc_attr((string) ($field['frame_title'] ?? __('Select Image', 'daisy-a-ripple-song'))) . '" data-button-label="' . esc_attr((string) ($field['button_label'] ?? __('Use This Image', 'daisy-a-ripple-song'))) . '">' . esc_html($imageValue === '' ? __('Select Image', 'daisy-a-ripple-song') : __('Replace Image')) . '</button> ';
        echo '<button type="button" class="button-link-delete" data-ars-widget-image-remove ' . ($imageValue === '' ? 'style="display:none;"' : '') . '>' . esc_html__('Remove', 'daisy-a-ripple-song') . '</button>';
        echo '</p>';
        echo '</div>';
    }

    /**
     * Render a select widget form control.
     *
     * @param array<string,mixed> $field Field definition.
     * @param string $fieldId Form control ID.
     * @param string $fieldName Form control name.
     * @param mixed $fieldValue Form control value.
     * @return void
     */
    protected function renderSelectControl(array $field, string $fieldId, string $fieldName, mixed $fieldValue): void
    {
        /** @var array<string,string> $options Select options. */
        $options = is_array($field['options'] ?? null) ? $field['options'] : [];

        echo '<select class="widefat" id="' . esc_attr($fieldId) . '" name="' . esc_attr($fieldName) . '">';

        foreach ($options as $optionValue => $optionLabel) {
            echo '<option value="' . esc_attr((string) $optionValue) . '" ' . selected((string) $fieldValue, (string) $optionValue, false) . '>' . esc_html((string) $optionLabel) . '</option>';
        }

        echo '</select>';
    }

    /**
     * Render a checkbox widget form control.
     *
     * @param array<string,mixed> $field Field definition.
     * @param string $fieldId Form control ID.
     * @param string $fieldName Form control name.
     * @param mixed $fieldValue Form control value.
     * @return void
     */
    protected function renderCheckboxControl(array $field, string $fieldId, string $fieldName, mixed $fieldValue): void
    {
        echo '<label for="' . esc_attr($fieldId) . '">';
        echo '<input id="' . esc_attr($fieldId) . '" name="' . esc_attr($fieldName) . '" type="checkbox" value="1" ' . checked((bool) $fieldValue, true, false) . '> ';
        echo esc_html((string) ($field['label'] ?? ''));
        echo '</label>';
    }

    /**
     * Render a repeatable widget form section.
     *
     * @param array<string,mixed> $field Field definition.
     * @param array<string,mixed> $instance Current widget instance.
     * @return void
     */
    protected function renderRepeaterField(array $field, array $instance): void
    {
        /** @var string $key Repeater field key. */
        $key = (string) ($field['key'] ?? '');

        if ($key === '') {
            return;
        }

        /** @var array<int,array<string,mixed>> $rows Repeater rows. */
        $rows = isset($instance[$key]) && is_array($instance[$key]) ? array_values($instance[$key]) : [];
        /** @var array<int,array<string,mixed>> $childFields Repeater child field definitions. */
        $childFields = is_array($field['fields'] ?? null) ? $field['fields'] : [];

        echo '<div class="ars-widget-repeater" data-ars-widget-repeater>';
        echo '<p><strong>' . esc_html((string) ($field['label'] ?? '')) . '</strong></p>';

        if (!empty($field['description'])) {
            echo '<p class="description">' . esc_html((string) $field['description']) . '</p>';
        }

        echo '<div data-ars-widget-repeater-rows>';

        foreach ($rows as $rowIndex => $row) {
            $this->renderRepeaterRow($key, $childFields, is_array($row) ? $row : [], (string) $rowIndex);
        }

        if ($rows === []) {
            $this->renderRepeaterRow($key, $childFields, [], '0');
        }

        echo '</div>';
        echo '<template data-ars-widget-repeater-template>';
        $this->renderRepeaterRow($key, $childFields, [], '__INDEX__');
        echo '</template>';
        echo '<p><button type="button" class="button" data-ars-widget-repeater-add>' . esc_html__('Add Item', 'daisy-a-ripple-song') . '</button></p>';
        echo '</div>';
    }

    /**
     * Render one repeatable widget form row.
     *
     * @param string $parentKey Parent repeater field key.
     * @param array<int,array<string,mixed>> $childFields Repeater child field definitions.
     * @param array<string,mixed> $row Current row values.
     * @param string $rowIndex Current row index or template token.
     * @return void
     */
    protected function renderRepeaterRow(string $parentKey, array $childFields, array $row, string $rowIndex): void
    {
        echo '<div class="ars-widget-repeater__row" data-ars-widget-repeater-row>';

        foreach ($childFields as $childField) {
            /** @var string $childKey Child field key. */
            $childKey = (string) ($childField['key'] ?? '');

            if ($childKey === '') {
                continue;
            }

            /** @var string $fieldName Child form control name. */
            $fieldName = $this->get_field_name($parentKey) . '[' . $rowIndex . '][' . $childKey . ']';
            /** @var string $fieldId Child form control ID. */
            $fieldId = $this->get_field_id($parentKey . '_' . $rowIndex . '_' . $childKey);
            /** @var mixed $fieldValue Child field value. */
            $fieldValue = $row[$childKey] ?? ($childField['default'] ?? '');

            echo '<p>';

            if (($childField['type'] ?? '') === 'checkbox') {
                $this->renderCheckboxControl($childField, $fieldId, $fieldName, $fieldValue);
            } elseif (($childField['type'] ?? '') === 'image') {
                echo '<label for="' . esc_attr($fieldId) . '">' . esc_html((string) ($childField['label'] ?? '')) . '</label>';
                $this->renderImageControl($childField, $fieldId, $fieldName, $fieldValue);
            } elseif (($childField['type'] ?? '') === 'select') {
                echo '<label for="' . esc_attr($fieldId) . '">' . esc_html((string) ($childField['label'] ?? '')) . '</label>';
                $this->renderSelectControl($childField, $fieldId, $fieldName, $fieldValue);
            } else {
                echo '<label for="' . esc_attr($fieldId) . '">' . esc_html((string) ($childField['label'] ?? '')) . '</label>';
                $this->renderInputControl($childField, $fieldId, $fieldName, $fieldValue);
            }

            echo '</p>';
        }

        echo '<p><button type="button" class="button-link-delete" data-ars-widget-repeater-remove>' . esc_html__('Remove', 'daisy-a-ripple-song') . '</button></p>';
        echo '</div>';
    }

    /**
     * Sanitize one submitted field value.
     *
     * @param array<string,mixed> $field Field definition.
     * @param mixed $value Submitted value.
     * @return mixed
     */
    protected function sanitizeFieldValue(array $field, mixed $value): mixed
    {
        /** @var string $type Field type. */
        $type = (string) ($field['type'] ?? 'text');

        if ($type === 'checkbox') {
            return !empty($value);
        }

        if ($type === 'number') {
            return $this->sanitizeNumberValue($field, $value);
        }

        if ($type === 'select') {
            return $this->sanitizeSelectValue($field, $value);
        }

        if ($type === 'image') {
            return $this->sanitizeImageValue($value);
        }

        if ($type === 'url' || ($field['input_type'] ?? '') === 'url') {
            return is_scalar($value) ? esc_url_raw((string) $value) : '';
        }

        if ($type === 'repeater') {
            return $this->sanitizeRepeaterValue($field, $value);
        }

        if ($type === 'textarea') {
            return is_scalar($value) ? sanitize_textarea_field((string) $value) : '';
        }

        return is_scalar($value) ? sanitize_text_field((string) $value) : '';
    }

    /**
     * Sanitize a numeric widget field.
     *
     * @param array<string,mixed> $field Field definition.
     * @param mixed $value Submitted value.
     * @return int
     */
    protected function sanitizeNumberValue(array $field, mixed $value): int
    {
        /** @var int $defaultValue Default integer value. */
        $defaultValue = isset($field['default']) ? (int) $field['default'] : 0;
        /** @var int $minimum Minimum integer value. */
        $minimum = isset($field['min']) ? (int) $field['min'] : 0;
        /** @var int|null $maximum Maximum integer value. */
        $maximum = isset($field['max']) ? (int) $field['max'] : null;
        /** @var int $numberValue Sanitized integer value. */
        $numberValue = is_scalar($value) && (string) $value !== '' ? absint($value) : $defaultValue;

        $numberValue = max($minimum, $numberValue);

        if ($maximum !== null) {
            $numberValue = min($maximum, $numberValue);
        }

        return $numberValue;
    }

    /**
     * Sanitize a select widget field.
     *
     * @param array<string,mixed> $field Field definition.
     * @param mixed $value Submitted value.
     * @return string
     */
    protected function sanitizeSelectValue(array $field, mixed $value): string
    {
        /** @var array<string,string> $options Select options. */
        $options = is_array($field['options'] ?? null) ? $field['options'] : [];
        /** @var string $choiceValue Submitted choice value. */
        $choiceValue = is_scalar($value) ? (string) $value : '';

        if (array_key_exists($choiceValue, $options)) {
            return $choiceValue;
        }

        return (string) ($field['default'] ?? array_key_first($options) ?? '');
    }

    /**
     * Sanitize a media image field value.
     *
     * @param mixed $value Submitted attachment ID or legacy URL.
     * @return int|string
     */
    protected function sanitizeImageValue(mixed $value): int|string
    {
        if (!is_scalar($value)) {
            return '';
        }

        /** @var string $imageValue Submitted image value. */
        $imageValue = trim((string) $value);

        if ($imageValue === '') {
            return '';
        }

        if (ctype_digit($imageValue)) {
            return absint($imageValue);
        }

        return esc_url_raw($imageValue);
    }

    /**
     * Return a preview URL for an attachment ID or legacy URL value.
     *
     * @param string $imageValue Stored image field value.
     * @return string
     */
    protected function imagePreviewUrl(string $imageValue): string
    {
        if ($imageValue === '') {
            return '';
        }

        if (ctype_digit($imageValue)) {
            /** @var string|false $attachmentUrl Attachment URL resolved from the image ID. */
            $attachmentUrl = wp_get_attachment_image_url(absint($imageValue), 'medium');

            return $attachmentUrl !== false ? esc_url_raw($attachmentUrl) : '';
        }

        return esc_url_raw($imageValue);
    }

    /**
     * Sanitize a repeatable widget field.
     *
     * @param array<string,mixed> $field Field definition.
     * @param mixed $value Submitted value.
     * @return array<int,array<string,mixed>>
     */
    protected function sanitizeRepeaterValue(array $field, mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        /** @var array<int,array<string,mixed>> $sanitizedRows Sanitized repeatable rows. */
        $sanitizedRows = [];
        /** @var array<int,array<string,mixed>> $childFields Repeater child field definitions. */
        $childFields = is_array($field['fields'] ?? null) ? $field['fields'] : [];

        foreach ($value as $row) {
            if (!is_array($row)) {
                continue;
            }

            /** @var array<string,mixed> $sanitizedRow Sanitized row values. */
            $sanitizedRow = [];

            foreach ($childFields as $childField) {
                /** @var string $childKey Child field key. */
                $childKey = (string) ($childField['key'] ?? '');

                if ($childKey === '') {
                    continue;
                }

                $sanitizedRow[$childKey] = $this->sanitizeFieldValue($childField, $row[$childKey] ?? null);
            }

            if ($this->isRepeaterRowEmpty($sanitizedRow, $childFields)) {
                continue;
            }

            $sanitizedRows[] = $sanitizedRow;
        }

        return $sanitizedRows;
    }

    /**
     * Return whether a sanitized repeater row has no meaningful content.
     *
     * @param array<string,mixed> $row Sanitized repeater row.
     * @param array<int,array<string,mixed>> $childFields Repeater child field definitions.
     * @return bool
     */
    protected function isRepeaterRowEmpty(array $row, array $childFields): bool
    {
        /** @var array<string,mixed> $defaults Default values keyed by child field key. */
        $defaults = [];

        foreach ($childFields as $childField) {
            /** @var string $childKey Child field key. */
            $childKey = (string) ($childField['key'] ?? '');

            if ($childKey === '') {
                continue;
            }

            $defaults[$childKey] = $childField['default'] ?? '';
        }

        foreach ($row as $rowKey => $value) {
            if (is_bool($value)) {
                continue;
            }

            if (is_array($value) && $value !== []) {
                return false;
            }

            if (is_scalar($value) && trim((string) $value) !== '') {
                if (is_string($rowKey) && array_key_exists($rowKey, $defaults) && (string) $defaults[$rowKey] === (string) $value) {
                    continue;
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Return a scalar instance value as sanitized text.
     *
     * @param array<string,mixed> $instance Widget instance data.
     * @param string $key Instance key.
     * @param string $default Fallback value.
     * @return string
     */
    protected function textValue(array $instance, string $key, string $default = ''): string
    {
        return !empty($instance[$key]) ? sanitize_text_field((string) $instance[$key]) : $default;
    }

    /**
     * Return a scalar instance value as a bounded integer.
     *
     * @param array<string,mixed> $instance Widget instance data.
     * @param string $key Instance key.
     * @param int $default Fallback value.
     * @param int $minimum Smallest allowed value.
     * @param int|null $maximum Largest allowed value.
     * @return int
     */
    protected function intValue(array $instance, string $key, int $default = 1, int $minimum = 1, ?int $maximum = null): int
    {
        /** @var int $value Normalized integer value. */
        $value = !empty($instance[$key]) ? absint($instance[$key]) : $default;

        $value = max($minimum, $value);

        if ($maximum !== null) {
            $value = min($maximum, $value);
        }

        return $value;
    }

    /**
     * Return a checkbox instance value as a boolean.
     *
     * @param array<string,mixed> $instance Widget instance data.
     * @param string $key Instance key.
     * @param bool $default Fallback value.
     * @return bool
     */
    protected function boolValue(array $instance, string $key, bool $default = false): bool
    {
        return array_key_exists($key, $instance) ? (bool) $instance[$key] : $default;
    }

    /**
     * Return a normalized text choice when it matches the allowed values.
     *
     * @param array<string,mixed> $instance Widget instance data.
     * @param string $key Instance key.
     * @param array<int,string> $allowedValues Allowed normalized values.
     * @param string $default Fallback value.
     * @return string
     */
    protected function choiceValue(array $instance, string $key, array $allowedValues, string $default): string
    {
        /** @var string $value Normalized choice value. */
        $value = !empty($instance[$key]) ? sanitize_key((string) $instance[$key]) : $default;

        return in_array($value, $allowedValues, true) ? $value : $default;
    }
}
