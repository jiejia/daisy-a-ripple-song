<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Jiejia\DaisyARippleSong\Contracts\ThemeWidget;
use Jiejia\DaisyARippleSong\Supports\WidgetRenderer;

/**
 * Base class for theme widgets.
 */
abstract class AbstractWidget extends \WP_Widget implements ThemeWidget
{
    /**
     * Register the widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            $this->widgetId(),
            $this->widgetTitle(),
            ['description' => $this->widgetDescription()]
        );
    }

    /**
     * Render a widget template.
     *
     * @param string               $template Template file name without extension.
     * @param array<string, mixed> $data Template data.
     * @return string
     */
    protected function renderTemplate(string $template, array $data = []): string
    {
        return WidgetRenderer::render($template, $data);
    }

    /**
     * Return a scalar instance value as sanitized text.
     *
     * @param array<string, mixed> $instance Widget instance data.
     * @param string               $key Instance key.
     * @param string               $default Fallback value.
     * @return string
     */
    protected function textValue(array $instance, string $key, string $default = ''): string
    {
        return !empty($instance[$key]) ? sanitize_text_field((string) $instance[$key]) : $default;
    }

    /**
     * Return a scalar instance value as a positive integer.
     *
     * @param array<string, mixed> $instance Widget instance data.
     * @param string               $key Instance key.
     * @param int                  $default Fallback value.
     * @return int
     */
    protected function intValue(array $instance, string $key, int $default = 1): int
    {
        return !empty($instance[$key]) ? max(1, absint($instance[$key])) : $default;
    }

    /**
     * Return a checkbox instance value as a boolean.
     *
     * @param array<string, mixed> $instance Widget instance data.
     * @param string               $key Instance key.
     * @param bool                 $default Fallback value.
     * @return bool
     */
    protected function boolValue(array $instance, string $key, bool $default = false): bool
    {
        return isset($instance[$key]) ? (bool) $instance[$key] : $default;
    }
}
