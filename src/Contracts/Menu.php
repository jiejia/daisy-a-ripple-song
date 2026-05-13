<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines a theme menu registration contract.
 */
interface Menu
{
    /**
     * Register the primary menu entry or location.
     *
     * @return void
     */
    public function topMenu(): void;

    /**
     * Register child menu entries when the menu supports them.
     *
     * @return void
     */
    public function subMenu(): void;

    /**
     * Return the top menu title or label.
     *
     * @return string
     */
    public function topMenuTitle(): string;

    /**
     * Return the top menu slug or location.
     *
     * @return string
     */
    public function topMenuSlug(): string;
}
