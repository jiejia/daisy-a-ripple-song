<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Carbon_Fields\Widget as CarbonWidget;
use Jiejia\DaisyARippleSong\Contracts\ThemeWidget;
use Jiejia\DaisyARippleSong\Supports\WidgetRenderer;
use Jiejia\DaisyARippleSong\Theme;
use WP_Widget;

/**
 * Base class for Carbon Fields powered theme widgets.
 */
abstract class AbstractWidget extends CarbonWidget implements ThemeWidget
{
    /**
     * Preserve the legacy widget id base for existing widget placements.
     *
     * @var string
     */
    protected $widget_id_prefix = '';

    /**
     * Register the widget with Carbon Fields.
     */
    public function __construct()
    {
        $this->setup(
            $this->widgetId(),
            $this->widgetTitle(),
            $this->widgetDescription(),
            $this->fields(),
            $this->widgetClassName()
        );
    }

    /**
     * Return the Carbon Fields prefix used by this widget.
     *
     * @return string
     */
    public function fieldPrefix(): string
    {
        return Theme::PREFIX . '_' . $this->widgetId() . '_';
    }

    /**
     * Return the Carbon Fields field name for one widget value.
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
     * Save a widget instance after normalizing REST-encoded Carbon Fields storage keys.
     *
     * @param array<string,mixed> $new_instance New widget instance data.
     * @param array<string,mixed> $old_instance Previous widget instance data.
     * @return array<string,mixed>|false Saved widget instance data or false to cancel saving.
     */
    public function update($new_instance, $old_instance)
    {
        if (is_array($new_instance)) {
            if ($this->isEncodedComplexStorageInstance($new_instance)) {
                return $new_instance;
            }

            $new_instance = $this->normalizeProtectedStorageInput($new_instance);
        }

        return parent::update($new_instance, $old_instance);
    }

    /**
     * Return instance values merged with widget defaults.
     *
     * @param array<string,mixed> $instance Widget instance data.
     * @return array<string,mixed>
     */
    protected function mergeInstanceDefaults(array $instance): array
    {
        /** @var array<string,mixed> $normalizedInstance Widget values normalized to raw keys. */
        $normalizedInstance = $this->defaultSettings();

        foreach ($normalizedInstance as $key => $defaultValue) {
            /** @var string $fieldName Carbon Fields storage key for the current widget field. */
            $fieldName = $this->fieldName((string) $key);

            /** @var string $protectedFieldName Carbon Fields protected storage key for the current widget field. */
            $protectedFieldName = '_' . $fieldName;

            if (array_key_exists($fieldName, $instance)) {
                $normalizedInstance[(string) $key] = $instance[$fieldName];
                continue;
            }

            if (array_key_exists($protectedFieldName, $instance)) {
                $normalizedInstance[(string) $key] = $instance[$protectedFieldName];
                continue;
            }

            if (array_key_exists((string) $key, $instance)) {
                $normalizedInstance[(string) $key] = $instance[(string) $key];
                continue;
            }

            $normalizedInstance[(string) $key] = $defaultValue;
        }

        return $normalizedInstance;
    }

    /**
     * Register Carbon Fields containers for all active instances of this widget during REST API initialisation.
     *
     * Carbon Fields Loader only initialises containers for widgets whose ID begins with the
     * "carbon_fields_" prefix. Because AbstractWidget deliberately clears that prefix (to preserve
     * existing option keys), containers are never registered during REST requests, which prevents
     * Carbon Fields from reading/writing complex fields via the Block Editor widget panel.
     *
     * Call this method from a "rest_api_init" hook so the containers are available before any
     * REST request attempts to update a widget instance.
     *
     * @return void
     */
    public function initializeRestContainer(): void
    {
        /** @var array<string,mixed> $sidebarWidgets All sidebar widget placements. */
        $sidebarWidgets = wp_get_sidebars_widgets();

        /** @var array<int,string> $allWidgetIds Flat list of every placed widget ID. */
        $allWidgetIds = array_merge(...array_values(array_filter($sidebarWidgets, 'is_array')));

        /** @var string $idBase The base ID for this widget class (e.g. "banner_carousel_widget"). */
        $idBase = $this->id_base;

        foreach ($allWidgetIds as $widgetId) {
            if (!is_string($widgetId)) {
                continue;
            }

            // Match instances that belong to this widget class (e.g. "banner_carousel_widget-2").
            if (!str_starts_with($widgetId, $idBase . '-')) {
                continue;
            }

            /** @var int $instanceNumber The numeric suffix of the placed widget instance. */
            $instanceNumber = (int) substr($widgetId, strlen($idBase) + 1);

            // _set() is a WP_Widget method that sets the current instance number used by
            // register_container() to load field values from the correct widget option slot.
            $this->_set($instanceNumber);
            $this->register_container();
        }
    }

    /**
     * Mirror protected Carbon Fields storage keys to their public input names for REST widget saves.
     *
     * @param array<string,mixed> $instance Widget instance data.
     * @return array<string,mixed>
     */
    protected function normalizeProtectedStorageInput(array $instance): array
    {
        /** @var string $protectedPrefix Protected Carbon Fields storage prefix for this widget. */
        $protectedPrefix = '_' . $this->fieldPrefix();

        foreach ($instance as $fieldName => $fieldValue) {
            if (!is_string($fieldName) || !str_starts_with($fieldName, $protectedPrefix)) {
                continue;
            }

            /** @var string $publicFieldName Public Carbon Fields input name for this widget value. */
            $publicFieldName = substr($fieldName, 1);

            if (!array_key_exists($publicFieldName, $instance)) {
                $instance[$publicFieldName] = $fieldValue;
            }
        }

        return $instance;
    }

    /**
     * Return whether the instance is already encoded Carbon Fields complex storage.
     *
     * @param array<string,mixed> $instance Widget instance data.
     * @return bool
     */
    protected function isEncodedComplexStorageInstance(array $instance): bool
    {
        /** @var string $protectedPrefix Protected Carbon Fields storage prefix for this widget. */
        $protectedPrefix = '_' . $this->fieldPrefix();

        foreach ($instance as $fieldName => $fieldValue) {
            if (
                is_string($fieldName)
                && str_starts_with($fieldName, $protectedPrefix)
                && str_contains($fieldName, '|')
            ) {
                return true;
            }
        }

        return false;
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
