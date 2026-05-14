<?php

namespace Jiejia\DaisyARippleSong\Menus;

use Jiejia\DaisyARippleSong\Contracts\Menu;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Registers the theme options parent admin menu.
 */
class ThemeOptions implements Menu
{
    /** @var string OPTIONS_PAGE_FILE Theme options top-level menu slug. */
    public const OPTIONS_PAGE_FILE = Theme::PREFIX . '_theme_options';

    /** @var string GENERAL_PAGE_FILE Theme general settings page slug. */
    public const GENERAL_PAGE_FILE = Theme::PREFIX . '_theme_general';

    /** @var string SOCIAL_PAGE_FILE Social links settings page slug. */
    public const SOCIAL_PAGE_FILE = Theme::PREFIX . '_theme_social_links';

    /**
     * Return the theme options top-level menu title.
     *
     * @return string
     */
    public function topMenuTitle(): string
    {
        return __('Theme Options', 'daisy-a-ripple-song');
    }

    /**
     * Return the theme options top-level menu slug.
     *
     * @return string
     */
    public function topMenuSlug(): string
    {
        return self::OPTIONS_PAGE_FILE;
    }

    /**
     * Register the theme options top-level admin menu.
     *
     * @return void
     */
    public function topMenu(): void
    {
        add_menu_page(
            $this->topMenuTitle(),
            $this->topMenuTitle(),
            $this->capability(),
            $this->topMenuSlug(),
            [self::class, 'renderLandingPage'],
            'dashicons-admin-settings',
            61
        );

        add_action('admin_menu', [self::class, 'removeDuplicateLandingPage'], 999);
    }

    /**
     * Keep submenu registration delegated to Carbon Fields containers.
     *
     * @return void
     */
    public function subMenu(): void
    {
        // Theme settings pages are registered from Carbon Fields containers.
    }

    /**
     * Redirect direct parent menu visits to the general settings page.
     *
     * @return void
     */
    public static function renderLandingPage(): void
    {
        wp_safe_redirect(admin_url('admin.php?page=' . self::GENERAL_PAGE_FILE));
        exit;
    }

    /**
     * Remove the duplicate submenu WordPress creates for top-level pages.
     *
     * @return void
     */
    public static function removeDuplicateLandingPage(): void
    {
        // Keep the theme options menu concise.
        remove_submenu_page(self::OPTIONS_PAGE_FILE, self::OPTIONS_PAGE_FILE);
    }

    /**
     * Return the capability required to manage theme options.
     *
     * @return string
     */
    private function capability(): string
    {
        return 'edit_theme_options';
    }
}
