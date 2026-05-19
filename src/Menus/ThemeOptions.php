<?php

namespace Jiejia\DaisyARippleSong\Menus;

use Jiejia\DaisyARippleSong\Contracts\Menu;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Describes the theme options admin menu location.
 */
class ThemeOptions implements Menu
{
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
        return Theme::PREFIX . '_theme_options';
    }

    /**
     * Register the theme options page under Appearance.
     *
     * @return void
     */
    public function topMenu(): void
    {
        add_submenu_page(
            'themes.php',
            $this->topMenuTitle(),
            $this->topMenuTitle(),
            'edit_theme_options',
            $this->topMenuSlug(),
            [$this, 'renderPage']
        );
    }

    /**
     * Register child menu entries.
     *
     * @return void
     */
    public function subMenu(): void
    {
        // This menu has no child pages.
    }

    /**
     * Render the theme options admin page.
     *
     * @return void
     */
    public function renderPage(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($this->topMenuTitle()) . '</h1>';
        echo '</div>';
    }
}
