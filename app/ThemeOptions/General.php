<?php

namespace App\ThemeOptions;

use App\Constants\ThemeConstant;
use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Theme general options powered by Carbon Fields.
 */
class General
{
    /** @var string $pluginSettingsPageFile Existing plugin settings landing page file. */
    protected const PLUGIN_SETTINGS_PAGE_FILE = 'ars_settings.php';

    /** @var string $generalPageFile Theme general settings page file. */
    protected const GENERAL_PAGE_FILE = 'ars_theme_general.php';

    /** @var string $socialPageFile Social links settings page file. */
    protected const SOCIAL_PAGE_FILE = 'ars_theme_social_links.php';

    /** @var int $logoCropWidth Required site logo crop width. */
    protected const LOGO_CROP_WIDTH = 220;

    /** @var int $logoCropHeight Required site logo crop height. */
    protected const LOGO_CROP_HEIGHT = 32;

    /** @var mixed $themeContainer Main theme settings container instance. */
    protected static $themeContainer = null;

    /**
     * Register all option-related hooks.
     *
     * @return void
     */
    public static function boot(): void
    {
        add_action('after_setup_theme', [static::class, 'bootCarbon'], 5);
        add_action('carbon_fields_register_fields', [static::class, 'registerFields']);
        add_action('admin_menu', [static::class, 'renameGeneralSubmenu'], 1000);
        add_action('admin_enqueue_scripts', [static::class, 'enqueueAdminAssets']);
        add_action('admin_head', [static::class, 'outputPickerAssets']);
        add_action('admin_head', [static::class, 'outputLogoAssets']);
        add_action('carbon_fields_theme_options_container_saved', [static::class, 'syncLogoOption'], 10, 1);
        add_action('wp_head', [static::class, 'outputThemePaletteStyles'], 1);
        add_action('wp_head', [static::class, 'outputHeaderScripts'], 99);
        add_action('wp_footer', [static::class, 'outputFooterScripts'], 99);
    }

    /**
     * Boot Carbon Fields when it has not already been initialized elsewhere.
     *
     * @return void
     */
    public static function bootCarbon(): void
    {
        if (!class_exists(Carbon_Fields::class) || did_action('carbon_fields_loaded')) {
            return;
        }

        Carbon_Fields::boot();
    }

    /**
     * Register the theme settings and social links pages.
     *
     * @return void
     */
    public static function registerFields(): void
    {
        if (!class_exists(Container::class) || !class_exists(Field::class)) {
            return;
        }

        /** @var \Carbon_Fields\Container\Container $themeContainer General settings container. */
        $themeContainer = Container::make('theme_options', __('Theme Settings', 'a-ripple-song'))
            ->set_page_file(static::GENERAL_PAGE_FILE)
            ->set_page_menu_title(__('General', 'a-ripple-song'));

        if (static::hasPluginSettingsMenu()) {
            $themeContainer->set_page_parent(static::PLUGIN_SETTINGS_PAGE_FILE);
        } else {
            $themeContainer
                ->set_page_menu_title(__('A Ripple Song', 'a-ripple-song'))
                ->set_icon('dashicons-admin-settings')
                ->set_page_menu_position(60);
        }

        $themeContainer->add_fields([
            Field::make('html', 'crb_site_logo_uploader', __('Site Logo', 'a-ripple-song'))
                ->set_html(static::renderLogoUploader())
                ->set_help_text(__('Upload a logo image (220px × 32px). You will be able to crop the image after upload.', 'a-ripple-song')),
            Field::make('text', 'crb_site_logo', '')
                ->set_attribute('type', 'hidden')
                ->set_attribute('data-logo-field', 'true')
                ->set_classes('crb-logo-carbon-field'),
            Field::make('html', 'crb_light_theme_picker', __('Light Theme', 'a-ripple-song'))
                ->set_html(
                    sprintf(
                        '<div class="crb-theme-heading">%s</div>%s',
                        esc_html__('Light Theme', 'a-ripple-song'),
                        static::renderDaisyUiThemePicker('light')
                    )
                )
                ->set_help_text(__('Click any card to choose the light theme.', 'a-ripple-song')),
            Field::make('select', 'crb_light_theme', __('Light Theme (fallback)', 'a-ripple-song'))
                ->set_options(static::getLightThemeOptions())
                ->set_default_value('retro')
                ->set_help_text(__('If the card picker is unavailable, use this dropdown (default: retro).', 'a-ripple-song'))
                ->set_classes('crb-theme-select')
                ->set_attribute('data-theme-target', 'light'),
            Field::make('html', 'crb_dark_theme_picker', __('Dark Theme', 'a-ripple-song'))
                ->set_html(
                    sprintf(
                        '<div class="crb-theme-heading">%s</div>%s',
                        esc_html__('Dark Theme', 'a-ripple-song'),
                        static::renderDaisyUiThemePicker('dark')
                    )
                )
                ->set_help_text(__('Click any card to choose the dark theme.', 'a-ripple-song')),
            Field::make('select', 'crb_dark_theme', __('Dark Theme (fallback)', 'a-ripple-song'))
                ->set_options(static::getDarkThemeOptions())
                ->set_default_value('dim')
                ->set_help_text(__('If the card picker is unavailable, use this dropdown (default: dim).', 'a-ripple-song'))
                ->set_classes('crb-theme-select')
                ->set_attribute('data-theme-target', 'dark'),
            Field::make('textarea', 'crb_footer_copyright', __('Footer Copyright', 'a-ripple-song'))
                ->set_rows(2)
                ->set_attribute('placeholder', __('Powered by A Ripple Song Theme', 'a-ripple-song'))
                ->set_help_text(__('Overrides the footer copyright line. Leave empty to use the default.', 'a-ripple-song')),
            Field::make('header_scripts', 'crb_header_scripts', __('Header Scripts', 'a-ripple-song'))
                ->set_help_text(esc_html__('Scripts to be added in the <head> section. You can include complete <script> tags for services like Google Analytics.', 'a-ripple-song')),
            Field::make('footer_scripts', 'crb_footer_scripts', __('Footer Scripts', 'a-ripple-song'))
                ->set_help_text(esc_html__('Scripts to be added before </body>. You can include complete <script> tags.', 'a-ripple-song')),
        ]);

        static::$themeContainer = $themeContainer;

        Container::make('theme_options', __('Social Links', 'a-ripple-song'))
            ->set_page_file(static::SOCIAL_PAGE_FILE)
            ->set_page_parent(static::getSettingsParent())
            ->add_fields(static::getSocialLinkFields());
    }

    /**
     * Rename the duplicated top-level submenu entry to "General" when the theme owns the menu group.
     *
     * @return void
     */
    public static function renameGeneralSubmenu(): void
    {
        if (static::hasPluginSettingsMenu() || !static::$themeContainer) {
            return;
        }

        global $submenu;

        /** @var string $pageFile Current theme settings page file. */
        $pageFile = static::$themeContainer->get_page_file();

        if (empty($submenu[$pageFile]) || !is_array($submenu[$pageFile])) {
            return;
        }

        foreach ($submenu[$pageFile] as &$submenuItem) {
            if (!isset($submenuItem[0], $submenuItem[2])) {
                continue;
            }

            if ($submenuItem[2] === $pageFile) {
                $submenuItem[0] = __('General', 'a-ripple-song');
                break;
            }
        }
    }

    /**
     * Enqueue the theme settings admin assets on the general settings page only.
     *
     * @return void
     */
    public static function enqueueAdminAssets(): void
    {
        if (!static::isGeneralSettingsPage()) {
            return;
        }

        wp_enqueue_media();
    }

    /**
     * Return whether the podcast plugin already provides the shared settings menu.
     *
     * @return bool
     */
    public static function hasPluginSettingsMenu(): bool
    {
        return class_exists('A_Ripple_Song_Podcast_Podcast_Settings');
    }

    /**
     * Return whether the current admin request targets the theme general settings page.
     *
     * @return bool
     */
    protected static function isGeneralSettingsPage(): bool
    {
        if (!is_admin()) {
            return false;
        }

        /** @var string $page Current admin page slug. */
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash((string) $_GET['page'])) : '';

        return $page === static::GENERAL_PAGE_FILE;
    }

    /**
     * Return the effective parent settings page.
     *
     * @return string|\Carbon_Fields\Container\Container
     */
    public static function getSettingsParent()
    {
        if (static::hasPluginSettingsMenu()) {
            return static::PLUGIN_SETTINGS_PAGE_FILE;
        }

        return static::$themeContainer ?: static::GENERAL_PAGE_FILE;
    }

    /**
     * Return the saved theme option value.
     *
     * @param string $key Theme option key.
     * @param string $default Fallback value.
     * @return string
     */
    public static function getThemeOption(string $key, string $default = ''): string
    {
        if (!function_exists('carbon_get_theme_option')) {
            return $default;
        }

        /** @var mixed $optionValue Raw Carbon Fields option value. */
        $optionValue = carbon_get_theme_option($key);

        return is_string($optionValue) && $optionValue !== '' ? $optionValue : $default;
    }

    /**
     * Return the configured site logo URL.
     *
     * @return string
     */
    public static function getSiteLogoUrl(): string
    {
        return esc_url(static::getThemeOption('crb_site_logo'));
    }

    /**
     * Return the default light theme slug.
     *
     * @return string
     */
    public static function getLightTheme(): string
    {
        /** @var string $themeSlug Saved light theme slug. */
        $themeSlug = static::getThemeOption('crb_light_theme', 'retro');

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
        $themeSlug = static::getThemeOption('crb_dark_theme', 'dim');

        return array_key_exists($themeSlug, static::getDarkThemeOptions()) ? $themeSlug : 'dim';
    }

    /**
     * Return theme data needed by the front-end theme store.
     *
     * @return array<string, mixed>
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
     * Output CSS custom properties for the legacy theme palettes on the front end.
     *
     * @return void
     */
    public static function outputThemePaletteStyles(): void
    {
        if (is_admin()) {
            return;
        }

        /** @var array<string, array<string, string>> $themePalette Full theme palette map. */
        $themePalette = static::getThemePalette();

        if ($themePalette === []) {
            return;
        }

        /** @var array<string, bool> $darkThemeLookup Dark theme lookup table. */
        $darkThemeLookup = array_fill_keys(array_keys(static::getDarkThemeOptions()), true);

        /** @var array<int, string> $cssRules Generated CSS rules. */
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
        return trim(static::getThemeOption('crb_footer_copyright'));
    }

    /**
     * Output saved custom header scripts on the front end.
     *
     * @return void
     */
    public static function outputHeaderScripts(): void
    {
        if (is_admin()) {
            return;
        }

        /** @var string $headerScripts Saved header scripts. */
        $headerScripts = static::getThemeOption('crb_header_scripts');

        if ($headerScripts === '') {
            return;
        }

        echo $headerScripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Output saved custom footer scripts on the front end.
     *
     * @return void
     */
    public static function outputFooterScripts(): void
    {
        if (is_admin()) {
            return;
        }

        /** @var string $footerScripts Saved footer scripts. */
        $footerScripts = static::getThemeOption('crb_footer_scripts');

        if ($footerScripts === '') {
            return;
        }

        echo $footerScripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render the legacy logo uploader UI.
     *
     * @return string
     */
    public static function renderLogoUploader(): string
    {
        /** @var string $currentLogo Saved logo URL. */
        $currentLogo = static::getThemeOption('crb_site_logo');

        /** @var string $previewHtml Preview markup for the saved logo. */
        $previewHtml = '';

        if ($currentLogo !== '') {
            $previewHtml = sprintf(
                '<div class="crb-logo-preview" style="margin-top: 12px;"><img src="%1$s" alt="%2$s" style="max-width: 220px; height: auto; border: 1px solid #ddd; padding: 8px; background: #f9f9f9;"></div>',
                esc_url($currentLogo),
                esc_attr__('Site Logo', 'a-ripple-song')
            );
        }

        return sprintf(
            '<div class="crb-logo-uploader-wrapper"><button type="button" class="button button-primary crb-logo-upload-btn" data-logo-width="%1$d" data-logo-height="%2$d">%3$s</button><button type="button" class="button crb-logo-remove-btn" style="margin-left: 8px; %4$s">%5$s</button><input type="hidden" name="_crb_site_logo" class="crb-site-logo-input" id="crb_site_logo_field" value="%6$s" data-current-value="%6$s">%7$s</div>',
            static::LOGO_CROP_WIDTH,
            static::LOGO_CROP_HEIGHT,
            esc_html__('Upload / Change Logo', 'a-ripple-song'),
            $currentLogo === '' ? 'display: none;' : '',
            esc_html__('Remove Logo', 'a-ripple-song'),
            esc_attr($currentLogo),
            $previewHtml
        );
    }

    /**
     * Render the legacy DaisyUI theme card picker.
     *
     * @param string $mode Target mode.
     * @return string
     */
    public static function renderDaisyUiThemePicker(string $mode): string
    {
        /** @var array<string, string> $themeOptions Visible options for the target mode. */
        $themeOptions = $mode === 'dark' ? static::getDarkThemeOptions() : static::getLightThemeOptions();

        /** @var array<string, array<string, string>> $themePalette Full palette data. */
        $themePalette = static::getThemePalette();

        if ($themeOptions === []) {
            return '';
        }

        /** @var array<int, string> $cards Rendered card fragments. */
        $cards = [];

        foreach ($themeOptions as $themeSlug => $themeLabel) {
            /** @var array<string, string> $colors Palette colors for the current theme. */
            $colors = $themePalette[$themeSlug] ?? [];

            /** @var string $inlineStyle CSS variables used by the preview card. */
            $inlineStyle = sprintf(
                '--crb-base-100:%1$s;--crb-base-200:%2$s;--crb-base-300:%3$s;--crb-base-content:%4$s;--crb-primary:%5$s;--crb-primary-content:%6$s;--crb-secondary:%7$s;--crb-secondary-content:%8$s;--crb-accent:%9$s;--crb-accent-content:%10$s;--crb-neutral:%11$s;--crb-neutral-content:%12$s;',
                esc_attr($colors['base100'] ?? '#f3f4f6'),
                esc_attr($colors['base200'] ?? '#e5e7eb'),
                esc_attr($colors['base300'] ?? '#d1d5db'),
                esc_attr($colors['baseContent'] ?? '#111827'),
                esc_attr($colors['primary'] ?? '#570df8'),
                esc_attr($colors['primaryContent'] ?? '#ffffff'),
                esc_attr($colors['secondary'] ?? '#f000b8'),
                esc_attr($colors['secondaryContent'] ?? '#ffffff'),
                esc_attr($colors['accent'] ?? '#37cdbe'),
                esc_attr($colors['accentContent'] ?? '#ffffff'),
                esc_attr($colors['neutral'] ?? '#3d4451'),
                esc_attr($colors['neutralContent'] ?? '#f3f4f6')
            );

            $cards[] = sprintf(
                '<button type="button" class="crb-theme-card" data-value="%1$s" data-theme-target="%2$s" style="%3$s"><span class="crb-theme-card__grid"><span class="crb-theme-card__base crb-theme-card__base--top"></span><span class="crb-theme-card__base crb-theme-card__base--bottom"></span><span class="crb-theme-card__body"><span class="crb-theme-card__name">%4$s</span><span class="crb-theme-card__colors"><span class="crb-theme-card__color is-primary"><span class="crb-theme-card__color-text">A</span></span><span class="crb-theme-card__color is-secondary"><span class="crb-theme-card__color-text">A</span></span><span class="crb-theme-card__color is-accent"><span class="crb-theme-card__color-text">A</span></span><span class="crb-theme-card__color is-neutral"><span class="crb-theme-card__color-text">A</span></span></span></span></span></button>',
                esc_attr($themeSlug),
                esc_attr($mode),
                $inlineStyle,
                esc_html(strtolower($themeLabel))
            );
        }

        return sprintf(
            '<div class="crb-theme-picker" data-theme-target="%1$s">%2$s</div>',
            esc_attr($mode),
            implode('', $cards)
        );
    }

    /**
     * Output inline assets for the legacy theme picker.
     *
     * @return void
     */
    public static function outputPickerAssets(): void
    {
        if (!static::isGeneralSettingsPage()) {
            return;
        }

        ?>
        <style>
            .crb-theme-picker {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 12px;
                margin-top: 8px;
            }

            .crb-theme-heading {
                font-weight: 700;
                font-size: 14px;
                margin: 6px 0 4px;
                color: #111827;
            }

            .crb-theme-card {
                position: relative;
                border: 1px solid #dcdde0;
                border-radius: 12px;
                padding: 0;
                background: var(--crb-base-100, #fff);
                cursor: pointer;
                text-align: left;
                transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.1s ease;
                overflow: hidden;
            }

            .crb-theme-card::before {
                content: '';
                position: absolute;
                inset: 0 auto 0 0;
                width: 20px;
                background: transparent;
            }

            .crb-theme-card:hover {
                border-color: #4f46e5;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
                transform: translateY(-1px);
            }

            .crb-theme-card.is-active {
                border-color: #6366f1;
                box-shadow:
                    0 0 0 2px rgba(99, 102, 241, 0.4),
                    0 10px 24px rgba(0, 0, 0, 0.16);
                transform: translateY(-1px);
            }

            .crb-theme-card.is-active::after {
                content: '✓';
                position: absolute;
                top: 8px;
                right: 8px;
                width: 22px;
                height: 22px;
                border-radius: 50%;
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.9), rgba(14, 165, 233, 0.9));
                color: #fff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: 800;
                box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.9);
                z-index: 2;
            }

            .crb-theme-card__grid {
                position: relative;
                display: grid;
                grid-template-columns: 20px 1fr;
                grid-template-rows: repeat(3, 1fr);
                min-height: 78px;
            }

            .crb-theme-card__base {
                grid-column: 1 / 2;
            }

            .crb-theme-card__base--top {
                grid-row: 1 / 3;
                background: var(--crb-base-200);
            }

            .crb-theme-card__base--bottom {
                grid-row: 3 / 4;
                background: var(--crb-base-300);
            }

            .crb-theme-card__body {
                grid-column: 2 / 3;
                grid-row: 1 / 4;
                background: var(--crb-base-100);
                display: flex;
                flex-direction: column;
                gap: 6px;
                padding: 10px;
                color: var(--crb-base-content, #111827);
            }

            .crb-theme-card__name {
                font-weight: 700;
                font-size: 14px;
                line-height: 1.2;
                text-transform: lowercase;
            }

            .crb-theme-card__colors {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
            }

            .crb-theme-card__color {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 22px;
                height: 22px;
                border-radius: 6px;
                font-weight: 700;
                font-size: 12px;
            }

            .crb-theme-card__color.is-primary { background: var(--crb-primary); color: var(--crb-primary-content, #fff); }
            .crb-theme-card__color.is-secondary { background: var(--crb-secondary); color: var(--crb-secondary-content, #fff); }
            .crb-theme-card__color.is-accent { background: var(--crb-accent); color: var(--crb-accent-content, #fff); }
            .crb-theme-card__color.is-neutral { background: var(--crb-neutral); color: var(--crb-neutral-content, #fff); }

            .crb-theme-select {
                position: absolute;
                left: -9999px;
                height: 1px;
                width: 1px;
                overflow: hidden;
            }
        </style>
        <script>
            (() => {
                const syncSelection = (picker, select) => {
                    const current = select.value;
                    picker.querySelectorAll('.crb-theme-card').forEach((card) => {
                        card.classList.toggle('is-active', card.dataset.value === current);
                    });
                };

                const findSelect = (target) => {
                    let select = document.querySelector(`select.crb-theme-select[data-theme-target="${target}"]`);
                    if (!select) {
                        select = document.querySelector(`select[name*="[crb_${target}_theme]"]`) ||
                            document.querySelector(`select[name*="crb_${target}_theme"]`);
                        if (select) {
                            select.dataset.themeTarget = target;
                            select.classList.add('crb-theme-select');
                        }
                    }
                    return select;
                };

                const bindPicker = (picker) => {
                    const target = picker.dataset.themeTarget;
                    const select = findSelect(target);
                    if (!select || picker.dataset.pickerReady === 'true') {
                        return;
                    }

                    picker.dataset.pickerReady = 'true';

                    picker.querySelectorAll('.crb-theme-card').forEach((card) => {
                        card.addEventListener('click', () => {
                            const value = card.dataset.value;
                            if (!value) {
                                return;
                            }
                            select.value = value;
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                            select.dispatchEvent(new Event('input', { bubbles: true }));
                            syncSelection(picker, select);
                        }, { passive: true });
                    });

                    select.addEventListener('change', () => syncSelection(picker, select));
                    syncSelection(picker, select);
                    select.style.position = 'absolute';
                    select.style.left = '-9999px';
                    select.style.height = '1px';
                    select.style.width = '1px';
                    select.style.overflow = 'hidden';
                };

                const initPicker = () => {
                    document.querySelectorAll('.crb-theme-picker').forEach(bindPicker);
                };

                document.addEventListener('DOMContentLoaded', () => {
                    initPicker();
                    const observer = new MutationObserver(() => initPicker());
                    observer.observe(document.body, { childList: true, subtree: true });
                });
            })();
        </script>
        <?php
    }

    /**
     * Output inline assets for the legacy logo uploader.
     *
     * @return void
     */
    public static function outputLogoAssets(): void
    {
        if (!static::isGeneralSettingsPage()) {
            return;
        }

        ?>
        <script>
            (() => {
                const cropWidth = <?php echo (int) static::LOGO_CROP_WIDTH; ?>;
                const cropHeight = <?php echo (int) static::LOGO_CROP_HEIGHT; ?>;

                const setNativeInputValue = (inputElement, nextValue) => {
                    const descriptor = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value');
                    if (descriptor && typeof descriptor.set === 'function') {
                        descriptor.set.call(inputElement, nextValue);
                    } else {
                        inputElement.value = nextValue;
                    }
                    inputElement.setAttribute('value', nextValue);
                    inputElement.dispatchEvent(new Event('input', { bubbles: true }));
                    inputElement.dispatchEvent(new Event('change', { bubbles: true }));
                };

                const syncLogoValue = (nextValue) => {
                    const uploaderField = document.getElementById('crb_site_logo_field');
                    const carbonField = document.querySelector('input[data-logo-field="true"]') ||
                        document.querySelector('.crb-logo-carbon-field input') ||
                        document.querySelector('input[name*="crb_site_logo"]');

                    if (uploaderField) {
                        setNativeInputValue(uploaderField, nextValue);
                    }

                    if (carbonField) {
                        setNativeInputValue(carbonField, nextValue);
                    }
                };

                const renderPreview = (url) => {
                    const wrapper = document.querySelector('.crb-logo-uploader-wrapper');
                    if (!wrapper) {
                        return;
                    }

                    let preview = wrapper.querySelector('.crb-logo-preview');
                    if (!url) {
                        if (preview) {
                            preview.remove();
                        }
                        return;
                    }

                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'crb-logo-preview';
                        preview.style.marginTop = '12px';
                        wrapper.appendChild(preview);
                    }

                    preview.innerHTML = '<img src="' + url + '" alt="<?php echo esc_js(__('Site Logo', 'a-ripple-song')); ?>" style="max-width: 220px; height: auto; border: 1px solid #ddd; padding: 8px; background: #f9f9f9;">';
                };

                const setRemoveButtonVisibility = (visible) => {
                    const removeButton = document.querySelector('.crb-logo-remove-btn');
                    if (removeButton) {
                        removeButton.style.display = visible ? 'inline-block' : 'none';
                    }
                };

                const openCropper = () => {
                    if (!window.wp || !wp.media || !wp.media.controller || typeof wp.media.controller.CustomizeImageCropper === 'undefined') {
                        return;
                    }

                    const cropControl = {
                        params: {
                            width: cropWidth,
                            height: cropHeight,
                            flex_width: 0,
                            flex_height: 0
                        }
                    };

                    const calculateSelection = (attachment, controller) => {
                        const control = controller.get('control');
                        const imageWidth = Number(attachment.get('width')) || 0;
                        const imageHeight = Number(attachment.get('height')) || 0;
                        const requiredWidth = Number(control.params.width) || cropWidth;
                        const requiredHeight = Number(control.params.height) || cropHeight;
                        const requiredRatio = requiredWidth / requiredHeight;
                        const realRatio = imageWidth / imageHeight;
                        let selectionWidth = requiredWidth;
                        let selectionHeight = requiredHeight;

                        if (realRatio > requiredRatio) {
                            selectionHeight = imageHeight;
                            selectionWidth = selectionHeight * requiredRatio;
                        } else {
                            selectionWidth = imageWidth;
                            selectionHeight = selectionWidth / requiredRatio;
                        }

                        const x1 = (imageWidth - selectionWidth) / 2;
                        const y1 = (imageHeight - selectionHeight) / 2;

                        controller.set('canSkipCrop', imageWidth === requiredWidth && imageHeight === requiredHeight);

                        return {
                            aspectRatio: `${requiredWidth}:${requiredHeight}`,
                            handles: true,
                            keys: true,
                            instance: true,
                            persistent: true,
                            imageWidth,
                            imageHeight,
                            minWidth: requiredWidth,
                            minHeight: requiredHeight,
                            x1,
                            y1,
                            x2: selectionWidth + x1,
                            y2: selectionHeight + y1
                        };
                    };

                    const mediaFrame = wp.media({
                        button: {
                            close: false
                        },
                        states: [
                            new wp.media.controller.Library({
                                library: wp.media.query({ type: 'image' }),
                                multiple: false,
                                date: false,
                                suggestedWidth: cropWidth,
                                suggestedHeight: cropHeight
                            }),
                            new wp.media.controller.CustomizeImageCropper({
                                control: cropControl,
                                imgSelectOptions: calculateSelection
                            })
                        ]
                    });

                    const applyAttachment = (attachment) => {
                        const nextValue = String((attachment && attachment.url) || '');
                        if (!nextValue) {
                            return;
                        }
                        syncLogoValue(nextValue);
                        renderPreview(nextValue);
                        setRemoveButtonVisibility(true);
                    };

                    mediaFrame.on('select', () => {
                        const attachment = mediaFrame.state().get('selection').first().toJSON();
                        if (Number(attachment.width) === cropWidth && Number(attachment.height) === cropHeight) {
                            applyAttachment(attachment);
                            mediaFrame.close();
                            return;
                        }
                        mediaFrame.setState('cropper');
                    });

                    mediaFrame.on('cropped', (attachment) => {
                        applyAttachment(attachment);
                        mediaFrame.close();
                    });

                    mediaFrame.on('skippedcrop', () => {
                        const attachment = mediaFrame.state().get('selection').first().toJSON();
                        applyAttachment(attachment);
                        mediaFrame.close();
                    });

                    mediaFrame.open();
                };

                document.addEventListener('DOMContentLoaded', () => {
                    const uploadButton = document.querySelector('.crb-logo-upload-btn');
                    const removeButton = document.querySelector('.crb-logo-remove-btn');
                    const initialField = document.getElementById('crb_site_logo_field');
                    const initialValue = initialField ? String(initialField.value || initialField.dataset.currentValue || '').trim() : '';

                    if (initialValue) {
                        renderPreview(initialValue);
                        setRemoveButtonVisibility(true);
                    }

                    if (uploadButton) {
                        uploadButton.addEventListener('click', (event) => {
                            event.preventDefault();
                            openCropper();
                        });
                    }

                    if (removeButton) {
                        removeButton.addEventListener('click', (event) => {
                            event.preventDefault();
                            syncLogoValue('');
                            renderPreview('');
                            setRemoveButtonVisibility(false);
                        });
                    }
                });
            })();
        </script>
        <?php
    }

    /**
     * Sync the custom logo field after the container is saved.
     *
     * @param mixed $container Saved container instance.
     * @return void
     */
    public static function syncLogoOption($container): void
    {
        if (!is_object($container) || !method_exists($container, 'get_page_file') || !static::$themeContainer) {
            return;
        }

        if ($container->get_page_file() !== static::$themeContainer->get_page_file()) {
            return;
        }

        if (!isset($_POST['_crb_site_logo'])) {
            return;
        }

        /** @var string $logoUrl Sanitized logo URL from the custom uploader field. */
        $logoUrl = esc_url_raw(wp_unslash((string) $_POST['_crb_site_logo']));

        if (function_exists('carbon_set_theme_option')) {
            carbon_set_theme_option('crb_site_logo', $logoUrl);
        }
    }

    /**
     * Return the configured light DaisyUI themes.
     *
     * @return array<string, string>
     */
    public static function getLightThemeOptions(): array
    {
        return ThemeConstant::LIGHT;
    }

    /**
     * Return the configured dark DaisyUI themes.
     *
     * @return array<string, string>
     */
    public static function getDarkThemeOptions(): array
    {
        return ThemeConstant::DARK;
    }

    /**
     * Return the full legacy palette map.
     *
     * @return array<string, array<string, string>>
     */
    public static function getThemePalette(): array
    {
        return ThemeConstant::PALETTE;
    }

    /**
     * Build Carbon Fields for the social links page.
     *
     * @return array<int, \Carbon_Fields\Field\Field>
     */
    protected static function getSocialLinkFields(): array
    {
        /** @var array<int, \Carbon_Fields\Field\Field> $fields Social links page fields. */
        $fields = [
            Field::make('html', 'crb_social_links_intro', __('Social Links', 'a-ripple-song'))
                ->set_html('<p>' . esc_html__('Only filled links will be used by the theme.', 'a-ripple-song') . '</p>'),
        ];

        foreach (SocialLinks::getPlatforms() as $platformKey => $platformData) {
            $fields[] = Field::make('text', SocialLinks::SETTING_PREFIX . $platformKey, $platformData['label'])
                ->set_help_text(__('Optional. Enter a full URL.', 'a-ripple-song'))
                ->set_attribute('type', 'url');
        }

        return $fields;
    }
}
