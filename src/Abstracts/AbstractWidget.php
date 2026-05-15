<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Carbon_Fields\Widget as CarbonWidget;
use Jiejia\DaisyARippleSong\Contracts\ThemeWidget;
use Jiejia\DaisyARippleSong\Supports\WidgetRenderer;
use Jiejia\DaisyARippleSong\Theme;

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
