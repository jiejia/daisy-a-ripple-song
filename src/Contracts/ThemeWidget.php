<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines shared metadata and native form fields for theme widgets.
 */
interface ThemeWidget
{
    /**
     * Return the WordPress widget ID.
     *
     * @return string
     */
    public function widgetId(): string;

    /**
     * Return the translated widget title.
     *
     * @return string
     */
    public function widgetTitle(): string;

    /**
     * Return the translated widget description.
     *
     * @return string
     */
    public function widgetDescription(): string;

    /**
     * Return all native field definitions for the widget form.
     *
     * @return array<int,array<string,mixed>>
     */
    public function fields(): array;

    /**
     * Return default values for the widget instance.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array;

    /**
     * Return the legacy field prefix used by this widget.
     *
     * @return string
     */
    public function fieldPrefix(): string;
}
