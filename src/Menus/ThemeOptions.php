<?php

namespace Jiejia\DaisyARippleSong\Menus;

use Jiejia\DaisyARippleSong\Contracts\Menu;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Describes the theme options admin menu location.
 */
class ThemeOptions implements Menu
{
    /** @var string OPTIONS_PAGE_FILE Theme options page slug under Appearance. */
    public const OPTIONS_PAGE_FILE = Theme::PREFIX . '_theme_options';

    /** @var string PARENT_PAGE_FILE WordPress Appearance menu slug. */
    public const PARENT_PAGE_FILE = 'themes.php';

    /**
     * Return the theme options menu title.
     *
     * @return string
     */
    public function topMenuTitle(): string
    {
        return __('Theme Options', 'daisy-a-ripple-song');
    }

    /**
     * Return the theme options page slug.
     *
     * @return string
     */
    public function topMenuSlug(): string
    {
        return self::OPTIONS_PAGE_FILE;
    }

    /**
     * Keep menu registration delegated to the settings service provider.
     *
     * @return void
     */
    public function topMenu(): void
    {
        // The page is registered under Appearance by the settings service provider.
    }

    /**
     * Keep submenu registration delegated to the settings service provider.
     *
     * @return void
     */
    public function subMenu(): void
    {
        // Theme settings pages are registered from the settings service provider.
    }
}
