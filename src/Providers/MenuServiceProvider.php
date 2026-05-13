<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Contracts\Menu;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;

/**
 * Registers admin menu pages via add_menu_page() / add_submenu_page().
 */
class MenuServiceProvider extends AbstractServiceProvider
{
    /**
     * Admin menu classes registered by this provider.
     *
     * @var array<int,class-string<Menu>>
     */
    private array $menus = [
        ThemeOptions::class,
    ];

    /**
     * Hook into admin_menu to register admin menu pages.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerAdminMenus']);
    }

    /**
     * Register all configured admin menu pages.
     *
     * @return void
     */
    public function registerAdminMenus(): void
    {
        foreach ($this->menus as $menuClass) {
            /** @var Menu $menu Admin menu instance. */
            $menu = new $menuClass();
            $menu->topMenu();
            $menu->subMenu();
        }
    }
}
