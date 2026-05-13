<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Contracts\Menu;
use Jiejia\DaisyARippleSong\Menus\PrimaryNavigation;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;

/**
 * Registers theme menu locations.
 */
class MenuServiceProvider extends AbstractServiceProvider
{
    /**
     * Frontend menu classes registered by this provider.
     *
     * @var array<int,class-string<Menu>>
     */
    private array $frontendMenus = [
        PrimaryNavigation::class,
    ];

    /**
     * Admin menu classes registered by this provider.
     *
     * @var array<int,class-string<Menu>>
     */
    private array $adminMenus = [
        ThemeOptions::class,
    ];

    /**
     * Register menu hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Register frontend menu locations after WordPress initializes theme support.
        add_action('after_setup_theme', [$this, 'registerFrontendMenus']);

        // Register admin menus when WordPress builds the admin menu tree.
        add_action('admin_menu', [$this, 'registerAdminMenus']);
    }

    /**
     * Register all configured frontend menu locations.
     *
     * @return void
     */
    public function registerFrontendMenus(): void
    {
        foreach ($this->frontendMenus as $menuClass) {
            // Let each frontend menu register its location.
            $menu = new $menuClass();
            $menu->topMenu();
            $menu->subMenu();
        }
    }

    /**
     * Register all configured admin menus.
     *
     * @return void
     */
    public function registerAdminMenus(): void
    {
        foreach ($this->adminMenus as $menuClass) {
            // Let each admin menu register its top-level and child entries.
            $menu = new $menuClass();
            $menu->topMenu();
            $menu->subMenu();
        }
    }
}
