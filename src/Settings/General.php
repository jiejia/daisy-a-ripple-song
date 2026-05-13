<?php

namespace Jiejia\DaisyARippleSong\Settings;

use Jiejia\DaisyARippleSong\Abstracts\AbstractSetting;
use Jiejia\DaisyARippleSong\Constants\ThemeConstant;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Theme general options powered by native WordPress settings pages.
 */
class General extends AbstractSetting
{
    /** @var string $themeOptionsScriptEntry Theme options admin JavaScript entry. */
    protected const THEME_OPTIONS_SCRIPT_ENTRY = 'resources/js/admin.js';

    /** @var string $themeOptionsStyleEntry Theme options admin stylesheet entry. */
    protected const THEME_OPTIONS_STYLE_ENTRY = 'resources/css/admin.css';

    /** @var string $themeOptionsHandlePrefix Prefix used for theme options admin assets. */
    protected const THEME_OPTIONS_HANDLE_PREFIX = Theme::PREFIX . '-theme-options';

    /** @var string $viteDevServerUrl Base URL of the shared Vite development server. */
    protected const VITE_DEV_SERVER_URL = 'http://127.0.0.1:5173';

    /** @var string $generalOptionGroup Settings group used by the general settings page. */
    public const GENERAL_OPTION_GROUP = Theme::PREFIX . '_general_options_group';

    /** @var string $socialOptionGroup Settings group used by the social links settings page. */
    public const SOCIAL_OPTION_GROUP = Theme::PREFIX . '_social_links_group';

    /** @var string $generalOptionName Serialized option name for general settings. */
    public const GENERAL_OPTION_NAME = Theme::PREFIX . '_general_options';

    /** @var string $socialOptionName Serialized option name for social links. */
    public const SOCIAL_OPTION_NAME = Theme::PREFIX . '_social_links';

    /**
     * Return the settings page slug.
     *
     * @return string
     */
    public function pageSlug(): string
    {
        return ThemeOptions::GENERAL_PAGE_FILE;
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
     * Return the WordPress settings group.
     *
     * @return string
     */
    public function optionGroup(): string
    {
        return static::GENERAL_OPTION_GROUP;
    }

    /**
     * Return the serialized WordPress option name.
     *
     * @return string
     */
    public function optionName(): string
    {
        return static::GENERAL_OPTION_NAME;
    }

    /**
     * Return field definitions for this settings page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fields(): array
    {
        return static::getGeneralFields();
    }

    /**
     * Return default settings for this page.
     *
     * @return array<string, mixed>
     */
    public function defaultSettings(): array
    {
        return static::getDefaultGeneralOptions();
    }

    /**
     * Sanitize the submitted settings value.
     *
     * @param mixed $value Raw submitted value.
     * @return array<string, mixed>
     */
    public function sanitize($value): array
    {
        return static::sanitizeGeneralOptions($value);
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function renderPage(): void
    {
        static::renderGeneralPage();
    }

    /**
     * Register all option-related hooks.
     *
     * @return void
     */
    public static function boot(): void
    {
        add_action('admin_init', [static::class, 'registerSettings']);
        add_action('admin_enqueue_scripts', [static::class, 'enqueueAdminAssets']);
        add_action('wp_head', [static::class, 'outputThemePaletteStyles'], 1);
        add_action('wp_head', [static::class, 'outputHeaderScripts'], 99);
        add_action('wp_footer', [static::class, 'outputFooterScripts'], 99);
    }

    /**
     * Register native settings and sanitizers.
     *
     * @return void
     */
    public static function registerSettings(): void
    {
        (new static())->registerSetting();
        (new SocialLinks())->registerSetting();
    }

    /**
     * Render the general settings page.
     *
     * @return void
     */
    public static function renderGeneralPage(): void
    {
        /** @var self $setting General settings page instance. */
        $setting = new self();

        echo static::renderAdminView('general', [
            'title' => $setting->pageTitle(),
            'optionGroup' => $setting->optionGroup(),
            'fieldsMarkup' => static::renderSettingsFields($setting->fields()),
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render the social links settings page.
     *
     * @return void
     */
    public static function renderSocialPage(): void
    {
        (new SocialLinks())->renderPage();
    }

    /**
     * Render one admin view template from the resources directory.
     *
     * @param string $view View name relative to resources/views/admin.
     * @param array<string, mixed> $data Template data.
     * @return string
     */
    public static function renderAdminView(string $view, array $data = []): string
    {
        /** @var string $viewPath Absolute path to the requested admin view file. */
        $viewPath = get_template_directory() . '/resources/views/admin/' . $view . '.php';

        if (!file_exists($viewPath)) {
            return '';
        }

        /** @var string|false $markup Rendered admin view markup captured from the template include. */
        $markup = (static function (string $__viewPath, array $__data): string {
            extract($__data, EXTR_SKIP);

            ob_start();
            include $__viewPath;

            return (string) ob_get_clean();
        })($viewPath, $data);

        return is_string($markup) ? $markup : '';
    }

    /**
     * Return field definitions for the general settings page.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function getGeneralFields(): array
    {
        return [
            [
                'type' => 'theme_picker',
                'key' => 'light_theme',
                'label' => __('Light Theme', 'daisy-a-ripple-song'),
                'options' => static::getLightThemeOptions(),
                'value' => static::getLightTheme(),
                'mode' => 'light',
                'description' => __('This is the default theme used when the site is in light mode.', 'daisy-a-ripple-song'),
            ],
            [
                'type' => 'theme_picker',
                'key' => 'dark_theme',
                'label' => __('Dark Theme', 'daisy-a-ripple-song'),
                'options' => static::getDarkThemeOptions(),
                'value' => static::getDarkTheme(),
                'mode' => 'dark',
                'description' => __('This is the default theme used when the site is in dark mode.', 'daisy-a-ripple-song'),
            ],
            [
                'type' => 'textarea',
                'key' => 'footer_copyright',
                'label' => __('Footer Copyright', 'daisy-a-ripple-song'),
                'value' => static::getThemeOption('footer_copyright'),
                'description' => __('Overrides the footer copyright line. Leave empty to use the default.', 'daisy-a-ripple-song'),
                'optionName' => static::GENERAL_OPTION_NAME,
            ],
            [
                'type' => 'textarea',
                'key' => 'header_scripts',
                'label' => __('Header Scripts', 'daisy-a-ripple-song'),
                'value' => static::getThemeOption('header_scripts'),
                'description' => __('Scripts to be added in the <head> section. You can include complete <script> tags for services like Google Analytics.', 'daisy-a-ripple-song'),
                'optionName' => static::GENERAL_OPTION_NAME,
            ],
            [
                'type' => 'textarea',
                'key' => 'footer_scripts',
                'label' => __('Footer Scripts', 'daisy-a-ripple-song'),
                'value' => static::getThemeOption('footer_scripts'),
                'description' => __('Scripts to be added before </body>. You can include complete <script> tags.', 'daisy-a-ripple-song'),
                'optionName' => static::GENERAL_OPTION_NAME,
            ],
        ];
    }

    /**
     * Enqueue theme options assets on theme settings pages.
     *
     * @return void
     */
    public static function enqueueAdminAssets(): void
    {
        if (!static::isThemeOptionsPage()) {
            return;
        }

        static::enqueueThemeOptionsAssets();
    }

    /**
     * Return whether the current admin request targets a theme options settings page.
     *
     * @return bool
     */
    protected static function isThemeOptionsPage(): bool
    {
        if (!is_admin()) {
            return false;
        }

        /** @var string $page Current admin page slug. */
        $page = static::getCurrentAdminPage();

        return in_array($page, [ThemeOptions::OPTIONS_PAGE_FILE, ThemeOptions::GENERAL_PAGE_FILE, ThemeOptions::SOCIAL_PAGE_FILE], true);
    }

    /**
     * Return whether the current admin request targets the theme general settings page.
     *
     * @return bool
     */
    protected static function isGeneralThemeOptionsPage(): bool
    {
        /** @var string $page Current admin page slug. */
        $page = static::getCurrentAdminPage();

        return $page === ThemeOptions::OPTIONS_PAGE_FILE || $page === ThemeOptions::GENERAL_PAGE_FILE;
    }

    /**
     * Return the current admin page slug from the request.
     *
     * @return string
     */
    protected static function getCurrentAdminPage(): string
    {
        return isset($_GET['page']) ? sanitize_text_field(wp_unslash((string) $_GET['page'])) : '';
    }

    /**
     * Return default values for the serialized general option.
     *
     * @return array<string, string>
     */
    public static function getDefaultGeneralOptions(): array
    {
        return [
            'light_theme' => 'retro',
            'dark_theme' => 'dim',
            'footer_copyright' => '',
            'header_scripts' => '',
            'footer_scripts' => '',
        ];
    }

    /**
     * Return all saved general settings merged with defaults.
     *
     * @return array<string, string>
     */
    public static function getGeneralOptions(): array
    {
        /** @var mixed $savedOptions Raw serialized option from WordPress. */
        $savedOptions = get_option(static::GENERAL_OPTION_NAME, []);

        if (!is_array($savedOptions)) {
            $savedOptions = [];
        }

        /** @var array<string, string> $normalizedOptions Normalized option values. */
        $normalizedOptions = [];

        foreach ($savedOptions as $optionKey => $optionValue) {
            if (!is_string($optionKey) || !is_scalar($optionValue)) {
                continue;
            }

            $normalizedOptions[$optionKey] = (string) $optionValue;
        }

        return array_merge(static::getDefaultGeneralOptions(), $normalizedOptions);
    }

    /**
     * Return all saved social links.
     *
     * @return array<string, string>
     */
    public static function getSocialLinksOptions(): array
    {
        /** @var mixed $savedOptions Raw serialized option from WordPress. */
        $savedOptions = get_option(static::SOCIAL_OPTION_NAME, []);

        if (!is_array($savedOptions)) {
            return [];
        }

        /** @var array<string, string> $normalizedOptions Normalized social link values. */
        $normalizedOptions = [];

        foreach ($savedOptions as $platformKey => $platformUrl) {
            if (!is_string($platformKey) || !is_scalar($platformUrl)) {
                continue;
            }

            $normalizedOptions[$platformKey] = (string) $platformUrl;
        }

        return $normalizedOptions;
    }

    /**
     * Return the saved general option value.
     *
     * @param string $key General option key.
     * @param string $default Fallback value.
     * @return string
     */
    public static function getThemeOption(string $key, string $default = ''): string
    {
        /** @var array<string, string> $options Saved general settings. */
        $options = static::getGeneralOptions();

        return array_key_exists($key, $options) ? $options[$key] : $default;
    }

    /**
     * Return the saved URL for a social platform.
     *
     * @param string $platformKey Social platform key.
     * @return string
     */
    public static function getSocialLinkOption(string $platformKey): string
    {
        /** @var array<string, string> $options Saved social link settings. */
        $options = static::getSocialLinksOptions();

        return trim($options[$platformKey] ?? '');
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
        return trim(static::getThemeOption('footer_copyright'));
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
        $headerScripts = static::getThemeOption('header_scripts');

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
        $footerScripts = static::getThemeOption('footer_scripts');

        if ($footerScripts === '') {
            return;
        }

        echo $footerScripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Enqueue the extracted theme options asset bundle.
     *
     * @return void
     */
    protected static function enqueueThemeOptionsAssets(): void
    {
        /** @var string $scriptHandle Script handle for the admin bundle. */
        $scriptHandle = static::THEME_OPTIONS_HANDLE_PREFIX . '-script';

        /** @var string $styleHandle Style handle for the admin bundle. */
        $styleHandle = static::THEME_OPTIONS_HANDLE_PREFIX . '-style';

        if (static::isThemeOptionsDevServerRunning()) {
            static::markScriptAsModule(static::THEME_OPTIONS_HANDLE_PREFIX . '-vite-client');
            static::markScriptAsModule($scriptHandle);

            wp_enqueue_style(
                $styleHandle,
                static::VITE_DEV_SERVER_URL . '/' . static::THEME_OPTIONS_STYLE_ENTRY,
                [],
                null
            );

            wp_enqueue_script(
                static::THEME_OPTIONS_HANDLE_PREFIX . '-vite-client',
                static::VITE_DEV_SERVER_URL . '/@vite/client',
                [],
                null,
                false
            );

            wp_enqueue_script(
                $scriptHandle,
                static::VITE_DEV_SERVER_URL . '/' . static::THEME_OPTIONS_SCRIPT_ENTRY,
                [],
                null,
                false
            );
        } else {
            /** @var array<string, mixed>|null $style Manifest entry for the admin stylesheet. */
            $style = static::getBuildManifestEntry(static::THEME_OPTIONS_STYLE_ENTRY);

            /** @var array<string, mixed>|null $script Manifest entry for the admin JavaScript. */
            $script = static::getBuildManifestEntry(static::THEME_OPTIONS_SCRIPT_ENTRY);

            /** @var string $themeUri Public theme directory URI. */
            $themeUri = get_template_directory_uri();

            if ($style) {
                wp_enqueue_style(
                    $styleHandle,
                    $themeUri . '/public/dist/' . $style['file'],
                    [],
                    null
                );
            } elseif (!empty($script['css']) && is_array($script['css'])) {
                foreach ($script['css'] as $index => $cssFile) {
                    wp_enqueue_style(
                        $styleHandle . '-' . $index,
                        $themeUri . '/public/dist/' . $cssFile,
                        [],
                        null
                    );
                }
            }

            if ($script) {
                static::markScriptAsModule($scriptHandle);

                wp_enqueue_script(
                    $scriptHandle,
                    $themeUri . '/public/dist/' . $script['file'],
                    [],
                    null,
                    true
                );
            }
        }

        if (!wp_script_is($scriptHandle, 'registered') && !wp_script_is($scriptHandle, 'enqueued')) {
            return;
        }

        /** @var string $bootstrapScript Inline bootstrap data for the admin bundle. */
        $bootstrapScript = 'window.aripplesongThemeOptions = Object.assign({}, window.aripplesongThemeOptions || {}, ' . wp_json_encode(static::getThemeOptionsAssetData()) . ');';

        wp_add_inline_script($scriptHandle, $bootstrapScript, 'before');
    }

    /**
     * Return localized runtime data used by the theme options admin bundle.
     *
     * @return array<string, mixed>
     */
    protected static function getThemeOptionsAssetData(): array
    {
        return [];
    }

    /**
     * Return whether the shared Vite development server is reachable.
     *
     * @return bool
     */
    protected static function isThemeOptionsDevServerRunning(): bool
    {
        /** @var array<string, mixed> $args HTTP client arguments for the Vite health check. */
        $args = [
            'timeout' => 0.3,
        ];

        /** @var array<string, mixed>|\WP_Error $response HTTP response from the Vite client endpoint. */
        $response = wp_remote_get(static::VITE_DEV_SERVER_URL . '/@vite/client', $args);

        return !is_wp_error($response)
            && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Return a Vite manifest entry for a specific admin asset source path.
     *
     * @param string $entry Vite source entry path.
     * @return array<string, mixed>|null
     */
    protected static function getBuildManifestEntry(string $entry): ?array
    {
        /** @var string $manifestPath Absolute path to the Vite manifest. */
        $manifestPath = get_template_directory() . '/public/dist/.vite/manifest.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        /** @var array<string, array<string, mixed>>|null $manifest Parsed Vite manifest data. */
        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        return $manifest[$entry] ?? null;
    }

    /**
     * Mark a specific theme options script handle as an ES module.
     *
     * @param string $handle Script handle to upgrade.
     * @return void
     */
    protected static function markScriptAsModule(string $handle): void
    {
        add_filter('script_loader_tag', static function (string $tag, string $currentHandle, string $src) use ($handle): string {
            if ($currentHandle !== $handle) {
                return $tag;
            }

            /** @var string|null $updatedTag Preserve existing attributes while forcing module mode. */
            $updatedTag = preg_replace_callback(
                '/<script\b(?=[^>]*\ssrc=)([^>]*)>/',
                static function (array $matches): string {
                    /** @var string $attributes Existing script attributes without the original type attribute. */
                    $attributes = preg_replace('/\s+type=(["\']).*?\1/', '', $matches[1], 1);

                    return '<script type="module"' . $attributes . '>';
                },
                $tag,
                1
            );

            return is_string($updatedTag) ? $updatedTag : $tag;
        }, 10, 3);
    }

    /**
     * Render a list of settings fields from definitions.
     *
     * @param array<int, array<string, mixed>> $fields Field definitions.
     * @return string
     */
    public static function renderSettingsFields(array $fields): string
    {
        /** @var array<int, string> $markup Generated field rows. */
        $markup = [];

        foreach ($fields as $field) {
            $markup[] = static::renderSettingsField($field);
        }

        return implode('', $markup);
    }

    /**
     * Render one settings field from a definition.
     *
     * @param array<string, mixed> $field Field definition.
     * @return string
     */
    protected static function renderSettingsField(array $field): string
    {
        /** @var string $type Field renderer type. */
        $type = is_string($field['type'] ?? null) ? $field['type'] : '';

        /** @var string $optionKey Field key inside the serialized option. */
        $optionKey = is_string($field['key'] ?? null) ? $field['key'] : '';
        if ($optionKey === '') {
            return '';
        }

        /** @var string $label Field label. */
        $label = is_string($field['label'] ?? null) ? $field['label'] : '';
        /** @var string $value Current field value. */
        $value = is_scalar($field['value'] ?? null) ? (string) $field['value'] : '';
        /** @var string $description Field help text. */
        $description = is_string($field['description'] ?? null) ? $field['description'] : '';
        /** @var string $optionName Serialized option name. */
        $optionName = is_string($field['optionName'] ?? null) ? $field['optionName'] : static::GENERAL_OPTION_NAME;

        if ($type === 'theme_picker') {
            /** @var array<string, string> $options Theme picker options. */
            $options = is_array($field['options'] ?? null) ? $field['options'] : [];
            /** @var string $mode Theme mode identifier. */
            $mode = is_string($field['mode'] ?? null) ? $field['mode'] : $optionKey;

            return static::renderThemePickerRow($optionKey, $label, $options, $value, $mode, $description);
        }

        if ($type === 'select') {
            /** @var array<string, string> $options Select options. */
            $options = is_array($field['options'] ?? null) ? $field['options'] : [];

            return static::renderSelectRow($optionKey, $label, $options, $value, $description, $optionName);
        }

        if ($type === 'url') {
            return static::renderUrlRow($optionKey, $label, $value, $description, $optionName);
        }

        if ($type === 'textarea') {
            return static::renderTextareaRow($optionKey, $label, $value, $description, $optionName);
        }

        return '';
    }

    /**
     * Render a theme picker row with color swatches and a native select fallback.
     *
     * @param string $optionKey Option key.
     * @param string $label Field label.
     * @param array<string, string> $options Select options.
     * @param string $value Current value.
     * @param string $mode Theme mode identifier.
     * @param string $description Field description.
     * @param string $rowClass Optional extra CSS classes for the table row.
     * @return string
     */
    protected static function renderThemePickerRow(
        string $optionKey,
        string $label,
        array $options,
        string $value,
        string $mode,
        string $description,
        string $rowClass = ''
    ): string {
        return static::renderAdminView('fields/theme-picker-row', [
            'rowClass' => $rowClass,
            'optionKey' => $optionKey,
            'label' => $label,
            'pickerHtml' => static::renderDaisyUiThemePicker($mode, $options, $value),
            'selectHtml' => static::renderThemeSelect($optionKey, $options, $value, $mode),
            'description' => $description,
        ]);
    }

    /**
     * Render DaisyUI theme swatch cards.
     *
     * @param string $mode Theme mode identifier.
     * @param array<string, string> $options Theme options.
     * @param string $value Current value.
     * @return string
     */
    protected static function renderDaisyUiThemePicker(string $mode, array $options, string $value): string
    {
        /** @var array<string, array<string, string>> $themePalette Full theme palette map. */
        $themePalette = static::getThemePalette();

        /** @var array<string, string> $swatches Pre-rendered swatch markup keyed by theme slug. */
        $swatches = [];

        foreach ($options as $themeSlug => $themeLabel) {
            $swatches[$themeSlug] = static::renderThemeSwatches($themePalette[$themeSlug] ?? []);
        }

        return static::renderAdminView('fields/theme-picker', [
            'mode' => $mode,
            'options' => $options,
            'value' => $value,
            'themePalette' => $themePalette,
            'swatches' => $swatches,
        ]);
    }

    /**
     * Render color swatches for a theme palette.
     *
     * @param array<string, string> $colors Theme colors.
     * @return string
     */
    protected static function renderThemeSwatches(array $colors): string
    {
        return static::renderAdminView('fields/theme-swatches', [
            'colors' => $colors,
        ]);
    }

    /**
     * Render the native select fallback for theme selection.
     *
     * @param string $optionKey Option key.
     * @param array<string, string> $options Select options.
     * @param string $value Current value.
     * @param string $mode Theme mode identifier.
     * @return string
     */
    protected static function renderThemeSelect(string $optionKey, array $options, string $value, string $mode): string
    {
        return static::renderAdminView('fields/theme-select', [
            'optionKey' => $optionKey,
            'options' => $options,
            'value' => $value,
            'mode' => $mode,
            'optionName' => static::GENERAL_OPTION_NAME,
        ]);
    }

    /**
     * Render a select field row.
     *
     * @param string $optionKey Option key.
     * @param string $label Field label.
     * @param array<string, string> $options Select options.
     * @param string $value Current value.
     * @param string $description Field description.
     * @return string
     */
    protected static function renderSelectRow(string $optionKey, string $label, array $options, string $value, string $description, string $optionName = self::GENERAL_OPTION_NAME): string
    {
        return static::renderAdminView('fields/select-row', [
            'optionKey' => $optionKey,
            'label' => $label,
            'options' => $options,
            'value' => $value,
            'description' => $description,
            'optionName' => $optionName,
        ]);
    }

    /**
     * Render a textarea field row.
     *
     * @param string $optionKey Option key.
     * @param string $label Field label.
     * @param string $value Current value.
     * @param string $description Field description.
     * @return string
     */
    protected static function renderTextareaRow(string $optionKey, string $label, string $value, string $description, string $optionName = self::GENERAL_OPTION_NAME): string
    {
        return static::renderAdminView('fields/textarea-row', [
            'optionKey' => $optionKey,
            'label' => $label,
            'value' => $value,
            'description' => $description,
            'optionName' => $optionName,
        ]);
    }

    /**
     * Render a URL field row.
     *
     * @param string $optionKey Option key.
     * @param string $label Field label.
     * @param string $value Current value.
     * @param string $description Field description.
     * @return string
     */
    protected static function renderUrlRow(string $optionKey, string $label, string $value, string $description, string $optionName = self::SOCIAL_OPTION_NAME): string
    {
        return static::renderAdminView('fields/url-row', [
            'optionKey' => $optionKey,
            'label' => $label,
            'value' => $value,
            'description' => $description,
            'optionName' => $optionName,
        ]);
    }

    /**
     * Sanitize the serialized general options array.
     *
     * @param mixed $value Raw option value.
     * @return array<string, string>
     */
    public static function sanitizeGeneralOptions($value): array
    {
        /** @var array<string, mixed> $input Submitted general settings. */
        $input = is_array($value) ? $value : [];
        /** @var array<string, string> $sanitizedOptions Sanitized general settings. */
        $sanitizedOptions = [];

        foreach (static::getGeneralSanitizers() as $optionKey => $sanitizeCallback) {
            $sanitizedOptions[$optionKey] = (string) call_user_func([static::class, $sanitizeCallback], $input[$optionKey] ?? '');
        }

        return $sanitizedOptions;
    }

    /**
     * Return sanitizers for the serialized general option.
     *
     * @return array<string, string>
     */
    protected static function getGeneralSanitizers(): array
    {
        return [
            'light_theme' => 'sanitizeLightTheme',
            'dark_theme' => 'sanitizeDarkTheme',
            'footer_copyright' => 'sanitizeHtmlOption',
            'header_scripts' => 'sanitizeRawCode',
            'footer_scripts' => 'sanitizeRawCode',
        ];
    }

    /**
     * Sanitize the serialized social links options array.
     *
     * @param mixed $value Raw option value.
     * @return array<string, string>
     */
    public static function sanitizeSocialLinksOptions($value): array
    {
        /** @var array<string, mixed> $input Submitted social link settings. */
        $input = is_array($value) ? $value : [];

        /** @var array<string, string> $sanitizedOptions Sanitized social link settings. */
        $sanitizedOptions = [];

        foreach (SocialLinks::getPlatforms() as $platformKey => $platformData) {
            /** @var string $platformUrl Sanitized platform URL. */
            $platformUrl = static::sanitizeUrlOption($input[$platformKey] ?? '');

            if ($platformUrl === '') {
                continue;
            }

            $sanitizedOptions[$platformKey] = $platformUrl;
        }

        return $sanitizedOptions;
    }

    /**
     * Sanitize a URL option.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeUrlOption($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return esc_url_raw(trim((string) wp_unslash($value)));
    }

    /**
     * Sanitize HTML allowed in the footer copyright field.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeHtmlOption($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return wp_kses_post((string) wp_unslash($value));
    }

    /**
     * Sanitize a raw code field.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeRawCode($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return trim((string) wp_unslash($value));
    }

    /**
     * Sanitize the light theme slug.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeLightTheme($value): string
    {
        return static::sanitizeThemeSlug(is_scalar($value) ? (string) $value : '', static::getLightThemeOptions(), 'retro');
    }

    /**
     * Sanitize the dark theme slug.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeDarkTheme($value): string
    {
        return static::sanitizeThemeSlug(is_scalar($value) ? (string) $value : '', static::getDarkThemeOptions(), 'dim');
    }

    /**
     * Sanitize a theme slug against a whitelist.
     *
     * @param string $value Raw theme slug.
     * @param array<string, string> $allowedValues Allowed theme values.
     * @param string $default Default fallback value.
     * @return string
     */
    protected static function sanitizeThemeSlug(string $value, array $allowedValues, string $default): string
    {
        /** @var string $themeSlug Sanitized theme slug. */
        $themeSlug = sanitize_key($value);

        return array_key_exists($themeSlug, $allowedValues) ? $themeSlug : $default;
    }

    /**
     * Return the configured light DaisyUI themes.
     *
     * @return array<string, string>
     */
    public static function getLightThemeOptions(): array
    {
        return ThemeConstant::getLightThemeLabels();
    }

    /**
     * Return the configured dark DaisyUI themes.
     *
     * @return array<string, string>
     */
    public static function getDarkThemeOptions(): array
    {
        return ThemeConstant::getDarkThemeLabels();
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
}
