<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines an admin menu page registration contract.
 *
 * Implementations register top-level and child pages via
 * add_menu_page() / add_submenu_page() during the admin_menu hook.
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
