<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines shared metadata for theme widgets.
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
}
