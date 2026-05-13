<?php

namespace Jiejia\DaisyARippleSong\Menus;

use Jiejia\DaisyARippleSong\Contracts\Menu;

/**
 * Registers the primary frontend navigation location.
 */
class PrimaryNavigation implements Menu
{
    /**
     * Register the primary navigation menu location.
     *
     * @return void
     */
    public function topMenu(): void
    {
        register_nav_menus([
            $this->topMenuSlug() => $this->topMenuTitle(),
        ]);
    }

    /**
     * Keep submenu registration empty for frontend menu locations.
     *
     * @return void
     */
    public function subMenu(): void
    {
        // Frontend navigation locations do not register admin submenus.
    }

    /**
     * Return the translated menu location label.
     *
     * @return string
     */
    public function topMenuTitle(): string
    {
        return __('Primary Navigation', 'daisy-a-ripple-song');
    }

    /**
     * Return the WordPress menu location slug.
     *
     * @return string
     */
    public function topMenuSlug(): string
    {
        return 'primary_navigation';
    }
}
