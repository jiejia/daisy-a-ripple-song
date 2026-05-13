<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines a frontend navigation location contract.
 *
 * Implementations provide the slug and label passed to
 * register_nav_menus() during theme setup.
 */
interface Navigation
{
    /**
     * Return the navigation location slug used in register_nav_menus().
     *
     * @return string
     */
    public function location(): string;

    /**
     * Return the human-readable label for this navigation location.
     *
     * @return string
     */
    public function label(): string;
}
