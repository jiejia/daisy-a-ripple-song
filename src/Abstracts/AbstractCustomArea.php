<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Jiejia\DaisyARippleSong\Contracts\CustomArea;

/**
 * Base class for WordPress widget area definitions.
 */
abstract class AbstractCustomArea implements CustomArea
{
    /**
     * Return markup printed before each widget.
     *
     * @return string
     */
    public function beforeWidget(): string
    {
        return '<div class="widget %1$s %2$s mb-4">';
    }

    /**
     * Return markup printed after each widget.
     *
     * @return string
     */
    public function afterWidget(): string
    {
        return '</div>';
    }

    /**
     * Return markup printed before each widget title.
     *
     * @return string
     */
    public function beforeTitle(): string
    {
        return '<h2 class="widget-title text-lg font-bold mb-2">';
    }

    /**
     * Return markup printed after each widget title.
     *
     * @return string
     */
    public function afterTitle(): string
    {
        return '</h2>';
    }

    /**
     * Return the complete WordPress sidebar registration arguments.
     *
     * @return array<string, mixed>
     */
    public function args(): array
    {
        return [
            'name' => $this->name(),
            'id' => $this->id(),
            'description' => $this->description(),
            'before_widget' => $this->beforeWidget(),
            'after_widget' => $this->afterWidget(),
            'before_title' => $this->beforeTitle(),
            'after_title' => $this->afterTitle(),
        ];
    }
}
