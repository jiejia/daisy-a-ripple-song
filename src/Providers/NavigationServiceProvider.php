<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Contracts\Navigation;
use Jiejia\DaisyARippleSong\Navigations\PrimaryNavigation;

/**
 * Registers frontend navigation menu locations via register_nav_menus().
 */
class NavigationServiceProvider extends AbstractServiceProvider
{
    /**
     * Navigation classes that define frontend menu locations.
     *
     * @var array<int,class-string<Navigation>>
     */
    private array $navigations = [
        PrimaryNavigation::class,
    ];

    /**
     * Hook into after_setup_theme to register navigation locations.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerNavigations']);
    }

    /**
     * Collect all navigation locations and register them with WordPress.
     *
     * @return void
     */
    public function registerNavigations(): void
    {
        /** @var array<string,string> $locations Navigation locations keyed by slug. */
        $locations = [];

        foreach ($this->navigations as $navigationClass) {
            /** @var Navigation $navigation Navigation instance. */
            $navigation = new $navigationClass();
            $locations[$navigation->location()] = $navigation->label();
        }

        register_nav_menus($locations);
    }
}
