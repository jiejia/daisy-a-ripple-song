<?php

namespace Jiejia\DaisyARippleSong\Settings;

use Jiejia\DaisyARippleSong\Abstracts\AbstractSetting;
use Jiejia\DaisyARippleSong\Constants\ThemeConstant;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Theme general options stored in the native theme options array.
 */
class General extends AbstractSetting
{
    /** @var string THEME_OPTIONS_NAME Native single option name for all theme settings. */
    public const THEME_OPTIONS_NAME = Theme::PREFIX . '_theme_options';

    /** @var string OPTION_SECTION Native option section key for general settings. */
    public const OPTION_SECTION = 'general';

    /** @var string $generalOptionName Legacy native option name kept for backward compatibility. */
    public const GENERAL_OPTION_NAME = Theme::PREFIX . '_general_options';

    /** @var string $socialOptionName Legacy native option name kept for backward compatibility. */
    public const SOCIAL_OPTION_NAME = Theme::PREFIX . '_social_links';

    /**
     * Return the native settings page slug.
     *
     * @return string
     */
    public function pageSlug(): string
    {
        return ThemeOptions::OPTIONS_PAGE_FILE;
    }

    /**
     * Return the translated settings page title.
     *
     * @return string
     */
    public function pageTitle(): string
    {
        return __('General', 'daisy-a-ripple-song');
    }

    /**
     * Return default settings for this page.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array
    {
        return [
            'light_theme' => 'retro',
            'dark_theme' => 'dim',
            'footer_copyright' => '',
        ];
    }

    /**
     * Return the legacy Carbon Fields key prefix for this settings page.
     *
     * @return string
     */
    public function fieldPrefix(): string
    {
        return Theme::PREFIX . '_general_';
    }

    /**
     * Return the parent menu slug for this settings page.
     *
     * @return string
     */
    public function parentPageSlug(): string
    {
        return ThemeOptions::PARENT_PAGE_FILE;
    }

    /**
     * Return one legacy native option value when Carbon Fields has not been saved yet.
     *
     * @param string $key Setting key without the page prefix.
     * @return mixed
     */
    protected function legacySettingValue(string $key): mixed
    {
        /** @var array<string,string> $legacyOptions Legacy general settings. */
        $legacyOptions = static::getLegacyGeneralOptions();

        return $legacyOptions[$key] ?? null;
    }

    /**
     * Return the section key inside the native theme options array.
     *
     * @return string
     */
    protected function optionSection(): string
    {
        return self::OPTION_SECTION;
    }

    /**
     * Return all saved general settings merged with defaults.
     *
     * @return array<string,mixed>
     */
    public static function getGeneralOptions(): array
    {
        return (new self())->getSettings();
    }

    /**
     * Return all saved theme settings grouped by option section.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function getThemeOptions(): array
    {
        return [
            self::OPTION_SECTION => static::getGeneralOptions(),
            SocialLinks::OPTION_SECTION => static::getSocialLinksOptions(),
        ];
    }

    /**
     * Return all default theme settings grouped by option section.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function getDefaultThemeOptions(): array
    {
        return [
            self::OPTION_SECTION => (new self())->defaultSettings(),
            SocialLinks::OPTION_SECTION => (new SocialLinks())->defaultSettings(),
        ];
    }

    /**
     * Return all saved social links merged with defaults.
     *
     * @return array<string,mixed>
     */
    public static function getSocialLinksOptions(): array
    {
        return (new SocialLinks())->getSettings();
    }

    /**
     * Return one saved general option value.
     *
     * @param string $key General option key.
     * @param string $default Fallback value.
     * @return string
     */
    public static function getThemeOption(string $key, string $default = ''): string
    {
        /** @var array<string,mixed> $options Saved general settings. */
        $options = static::getGeneralOptions();

        return array_key_exists($key, $options) && is_scalar($options[$key]) ? (string) $options[$key] : $default;
    }

    /**
     * Return the saved URL for a social platform.
     *
     * @param string $platformKey Social platform key.
     * @return string
     */
    public static function getSocialLinkOption(string $platformKey): string
    {
        /** @var array<string,mixed> $options Saved social link settings. */
        $options = static::getSocialLinksOptions();

        return array_key_exists($platformKey, $options) && is_scalar($options[$platformKey])
            ? trim((string) $options[$platformKey])
            : '';
    }

    /**
     * Return the default light theme slug.
     *
     * @return string
     */
    public static function getLightTheme(): string
    {
        /** @var string $themeSlug Saved light theme slug. */
        $themeSlug = static::getThemeOption('light_theme', 'retro');

        return array_key_exists($themeSlug, static::getLightThemeOptions()) ? $themeSlug : 'retro';
    }

    /**
     * Return the default dark theme slug.
     *
     * @return string
     */
    public static function getDarkTheme(): string
    {
        /** @var string $themeSlug Saved dark theme slug. */
        $themeSlug = static::getThemeOption('dark_theme', 'dim');

        return array_key_exists($themeSlug, static::getDarkThemeOptions()) ? $themeSlug : 'dim';
    }

    /**
     * Return theme data needed by the front-end theme store.
     *
     * @return array<string,mixed>
     */
    public static function getThemeModeConfig(): array
    {
        return [
            'lightTheme' => static::getLightTheme(),
            'darkTheme' => static::getDarkTheme(),
            'lightThemes' => array_keys(static::getLightThemeOptions()),
            'darkThemes' => array_keys(static::getDarkThemeOptions()),
            'palette' => static::getThemePalette(),
        ];
    }

    /**
     * Output CSS custom properties for the configured theme palettes.
     *
     * @return void
     */
    public static function outputThemePaletteStyles(): void
    {
        if (is_admin()) {
            return;
        }

        /** @var array<string,array<string,string>> $themePalette Full theme palette map. */
        $themePalette = static::getThemePalette();

        if ($themePalette === []) {
            return;
        }

        /** @var array<string,bool> $darkThemeLookup Dark theme lookup table. */
        $darkThemeLookup = array_fill_keys(array_keys(static::getDarkThemeOptions()), true);
        /** @var array<int,string> $cssRules Generated CSS rules. */
        $cssRules = [];

        foreach ($themePalette as $themeSlug => $themeColors) {
            /** @var string $colorScheme Active CSS color-scheme for the theme. */
            $colorScheme = isset($darkThemeLookup[$themeSlug]) ? 'dark' : 'light';

            $cssRules[] = sprintf(
                ':root[data-theme="%1$s"]{color-scheme:%2$s;--color-base-100:%3$s;--color-base-200:%4$s;--color-base-300:%5$s;--color-base-content:%6$s;--color-primary:%7$s;--color-primary-content:%8$s;--color-secondary:%9$s;--color-secondary-content:%10$s;--color-accent:%11$s;--color-accent-content:%12$s;--color-neutral:%13$s;--color-neutral-content:%14$s;}',
                esc_attr($themeSlug),
                esc_attr($colorScheme),
                esc_attr($themeColors['base100'] ?? '#f3f4f6'),
                esc_attr($themeColors['base200'] ?? '#e5e7eb'),
                esc_attr($themeColors['base300'] ?? '#d1d5db'),
                esc_attr($themeColors['baseContent'] ?? '#111827'),
                esc_attr($themeColors['primary'] ?? '#570df8'),
                esc_attr($themeColors['primaryContent'] ?? '#ffffff'),
                esc_attr($themeColors['secondary'] ?? '#f000b8'),
                esc_attr($themeColors['secondaryContent'] ?? '#ffffff'),
                esc_attr($themeColors['accent'] ?? '#37cdbe'),
                esc_attr($themeColors['accentContent'] ?? '#ffffff'),
                esc_attr($themeColors['neutral'] ?? '#3d4451'),
                esc_attr($themeColors['neutralContent'] ?? '#f3f4f6')
            );
        }

        echo '<style id="ars-theme-palette-styles">' . implode('', $cssRules) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Return the configured footer copyright content.
     *
     * @return string
     */
    public static function getFooterCopyright(): string
    {
        return trim(static::getThemeOption('footer_copyright'));
    }

    /**
     * Return the configured light DaisyUI themes.
     *
     * @return array<string,string>
     */
    public static function getLightThemeOptions(): array
    {
        return ThemeConstant::getLightThemeLabels();
    }

    /**
     * Return the configured dark DaisyUI themes.
     *
     * @return array<string,string>
     */
    public static function getDarkThemeOptions(): array
    {
        return ThemeConstant::getDarkThemeLabels();
    }

    /**
     * Return the full theme palette map.
     *
     * @return array<string,array<string,string>>
     */
    public static function getThemePalette(): array
    {
        return ThemeConstant::PALETTE;
    }

    /**
     * Render DaisyUI theme picker cards for the native settings page.
     *
     * @param string $mode Theme mode identifier.
     * @param string $title Theme picker title.
     * @param array<string,string> $options Select options keyed by theme slug.
     * @param string $value Current selected theme slug.
     * @return string
     */
    public function renderThemePickerHtml(string $mode, string $title, array $options, string $value): string
    {
        /** @var array<string,array<string,string>> $themePalette Full theme palette map. */
        $themePalette = static::getThemePalette();
        /** @var array<string,string> $swatches Pre-rendered swatch markup keyed by theme slug. */
        $swatches = [];

        foreach ($options as $themeSlug => $themeLabel) {
            $swatches[$themeSlug] = $this->renderAdminView('fields/theme-swatches', [
                'colors' => $themePalette[$themeSlug] ?? [],
            ]);
        }

        return $this->renderAdminView('fields/theme-picker', [
            'mode' => $mode,
            'title' => $title,
            'options' => $options,
            'value' => $value,
            'themePalette' => $themePalette,
            'swatches' => $swatches,
        ]);
    }

    /**
     * Render one admin view template from the resources directory.
     *
     * @param string $view View name relative to resources/views/admin.
     * @param array<string,mixed> $data Template data.
     * @return string
     */
    protected function renderAdminView(string $view, array $data = []): string
    {
        /** @var string $viewPath Absolute path to the requested admin view file. */
        $viewPath = get_template_directory() . '/resources/views/admin/' . $view . '.php';

        if (!file_exists($viewPath)) {
            return '';
        }

        return (static function (string $__viewPath, array $__data): string {
            extract($__data, EXTR_SKIP);

            ob_start();
            include $__viewPath;

            return (string) ob_get_clean();
        })($viewPath, $data);
    }

    /**
     * Return the legacy native settings array stored before the Carbon Fields migration.
     *
     * @return array<string,string>
     */
    protected static function getLegacyGeneralOptions(): array
    {
        /** @var mixed $savedOptions Raw serialized option from WordPress. */
        $savedOptions = get_option(static::GENERAL_OPTION_NAME, []);

        if (!is_array($savedOptions)) {
            $savedOptions = [];
        }

        /** @var array<string,string> $normalizedOptions Normalized option values. */
        $normalizedOptions = [];

        foreach ($savedOptions as $optionKey => $optionValue) {
            if (!is_string($optionKey) || !is_scalar($optionValue)) {
                continue;
            }

            $normalizedOptions[$optionKey] = (string) $optionValue;
        }

        return $normalizedOptions;
    }
}
