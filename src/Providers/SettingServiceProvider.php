<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;
use Jiejia\DaisyARippleSong\Settings\General;
use Jiejia\DaisyARippleSong\Settings\SocialLinks;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Registers the native theme settings page and related hooks.
 */
class SettingServiceProvider extends AbstractServiceProvider
{
    /** @var string $adminScriptEntry Theme options admin JavaScript entry. */
    private const ADMIN_SCRIPT_ENTRY = 'resources/js/admin.js';

    /** @var string $adminStyleEntry Theme options admin stylesheet entry. */
    private const ADMIN_STYLE_ENTRY = 'resources/css/admin.css';

    /** @var string $devServerUrl Base URL of the shared Vite development server. */
    private const DEV_SERVER_URL = 'http://127.0.0.1:5173';

    /**
     * Register settings hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the Appearance submenu and the native options setting.
        add_action('admin_menu', [$this, 'registerSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);

        // Load the theme options admin UI assets for the native settings page.
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        // Output frontend settings managed by the general settings page.
        add_action('wp_head', [General::class, 'outputThemePaletteStyles'], 1);
    }

    /**
     * Register the native Appearance submenu page.
     *
     * @return void
     */
    public function registerSettingsPage(): void
    {
        /** @var ThemeOptions $themeOptions Theme options menu descriptor. */
        $themeOptions = new ThemeOptions();

        add_theme_page(
            $themeOptions->topMenuTitle(),
            $themeOptions->topMenuTitle(),
            'edit_theme_options',
            $themeOptions->topMenuSlug(),
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Register the single native option used by all theme settings.
     *
     * @return void
     */
    public function registerSettings(): void
    {
        register_setting(ThemeOptions::OPTIONS_PAGE_FILE, General::THEME_OPTIONS_NAME, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitizeThemeOptions'],
            'default' => General::getDefaultThemeOptions(),
        ]);
    }

    /**
     * Render the native theme options page.
     *
     * @return void
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('edit_theme_options')) {
            wp_die(esc_html__('Sorry, you are not allowed to access this page.'));
        }

        $this->renderAdminView('theme-options', [
            'settingsGroup' => ThemeOptions::OPTIONS_PAGE_FILE,
            'optionName' => General::THEME_OPTIONS_NAME,
            'options' => General::getThemeOptions(),
            'generalSetting' => new General(),
        ]);
    }

    /**
     * Sanitize all submitted theme options before saving the single option row.
     *
     * @param mixed $input Raw submitted options.
     * @return array<string,array<string,string>>
     */
    public function sanitizeThemeOptions(mixed $input): array
    {
        /** @var array<string,array<string,string>> $sanitizedOptions Sanitized theme options. */
        $sanitizedOptions = General::getDefaultThemeOptions();
        /** @var array<string,mixed> $rawOptions Submitted option array. */
        $rawOptions = is_array($input) ? $input : [];
        /** @var array<string,mixed> $rawGeneral Submitted general settings. */
        $rawGeneral = isset($rawOptions[General::OPTION_SECTION]) && is_array($rawOptions[General::OPTION_SECTION])
            ? $rawOptions[General::OPTION_SECTION]
            : [];
        /** @var array<string,mixed> $rawSocialLinks Submitted social link settings. */
        $rawSocialLinks = isset($rawOptions[SocialLinks::OPTION_SECTION]) && is_array($rawOptions[SocialLinks::OPTION_SECTION])
            ? $rawOptions[SocialLinks::OPTION_SECTION]
            : [];

        $sanitizedOptions[General::OPTION_SECTION]['light_theme'] = $this->sanitizeThemeChoice(
            $rawGeneral['light_theme'] ?? '',
            General::getLightThemeOptions(),
            'retro'
        );
        $sanitizedOptions[General::OPTION_SECTION]['dark_theme'] = $this->sanitizeThemeChoice(
            $rawGeneral['dark_theme'] ?? '',
            General::getDarkThemeOptions(),
            'dim'
        );
        $sanitizedOptions[General::OPTION_SECTION]['footer_copyright'] = isset($rawGeneral['footer_copyright']) && is_scalar($rawGeneral['footer_copyright'])
            ? wp_kses_post((string) $rawGeneral['footer_copyright'])
            : '';

        foreach (array_keys(SocialLinks::getPlatforms()) as $platformKey) {
            /** @var string $platformUrl Submitted social link URL. */
            $platformUrl = isset($rawSocialLinks[$platformKey]) && is_scalar($rawSocialLinks[$platformKey])
                ? (string) $rawSocialLinks[$platformKey]
                : '';

            $sanitizedOptions[SocialLinks::OPTION_SECTION][$platformKey] = esc_url_raw($platformUrl);
        }

        return $sanitizedOptions;
    }

    /**
     * Sanitize a DaisyUI theme choice against a whitelist.
     *
     * @param mixed $themeSlug Raw submitted theme slug.
     * @param array<string,string> $allowedThemes Allowed theme slugs and labels.
     * @param string $defaultTheme Fallback theme slug.
     * @return string
     */
    private function sanitizeThemeChoice(mixed $themeSlug, array $allowedThemes, string $defaultTheme): string
    {
        /** @var string $sanitizedTheme Submitted theme slug after key sanitization. */
        $sanitizedTheme = is_scalar($themeSlug) ? sanitize_key((string) $themeSlug) : '';

        return array_key_exists($sanitizedTheme, $allowedThemes) ? $sanitizedTheme : $defaultTheme;
    }

    /**
     * Enqueue settings page assets.
     *
     * @return void
     */
    public function enqueueAdminAssets(): void
    {
        if (!$this->isThemeOptionsPage()) {
            return;
        }

        /** @var string $scriptHandle Admin script handle. */
        $scriptHandle = Theme::PREFIX . '-theme-options-script';
        /** @var string $styleHandle Admin style handle. */
        $styleHandle = Theme::PREFIX . '-theme-options-style';

        if ($this->isDevServerRunning()) {
            $this->markScriptAsModule(Theme::PREFIX . '-theme-options-vite-client');
            $this->markScriptAsModule($scriptHandle);

            wp_enqueue_style($styleHandle, self::DEV_SERVER_URL . '/' . self::ADMIN_STYLE_ENTRY, [], null);
            wp_enqueue_script(Theme::PREFIX . '-theme-options-vite-client', self::DEV_SERVER_URL . '/@vite/client', [], null, false);
            wp_enqueue_script($scriptHandle, self::DEV_SERVER_URL . '/' . self::ADMIN_SCRIPT_ENTRY, [], null, false);

            return;
        }

        /** @var array<string,mixed>|null $style Manifest entry for the admin stylesheet. */
        $style = $this->getBuildManifestEntry(self::ADMIN_STYLE_ENTRY);
        /** @var array<string,mixed>|null $script Manifest entry for the admin script. */
        $script = $this->getBuildManifestEntry(self::ADMIN_SCRIPT_ENTRY);

        if ($style) {
            wp_enqueue_style($styleHandle, get_template_directory_uri() . '/public/dist/' . $style['file'], [], null);
        }

        if ($script) {
            $this->markScriptAsModule($scriptHandle);
            wp_enqueue_script($scriptHandle, get_template_directory_uri() . '/public/dist/' . $script['file'], [], null, true);
        }
    }

    /**
     * Return whether the current admin request targets a theme settings page.
     *
     * @return bool
     */
    private function isThemeOptionsPage(): bool
    {
        if (!is_admin()) {
            return false;
        }

        /** @var string $page Current admin page slug. */
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash((string) $_GET['page'])) : '';

        return $page === ThemeOptions::OPTIONS_PAGE_FILE;
    }

    /**
     * Return whether the shared Vite development server is reachable.
     *
     * @return bool
     */
    private function isDevServerRunning(): bool
    {
        /** @var array<string,mixed>|\WP_Error $response HTTP response from the Vite client endpoint. */
        $response = wp_remote_get(self::DEV_SERVER_URL . '/@vite/client', ['timeout' => 0.3]);

        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Return a Vite manifest entry for an admin asset source path.
     *
     * @param string $entry Vite source entry path.
     * @return array<string,mixed>|null
     */
    private function getBuildManifestEntry(string $entry): ?array
    {
        /** @var string $manifestPath Absolute path to the Vite manifest. */
        $manifestPath = get_template_directory() . '/public/dist/.vite/manifest.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        /** @var array<string,array<string,mixed>>|null $manifest Parsed Vite manifest data. */
        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        return is_array($manifest) ? ($manifest[$entry] ?? null) : null;
    }

    /**
     * Render one admin view template from the resources directory.
     *
     * @param string $view View name relative to resources/views/admin.
     * @param array<string,mixed> $data Template data.
     * @return void
     */
    private function renderAdminView(string $view, array $data = []): void
    {
        /** @var string $viewPath Absolute path to the requested admin view file. */
        $viewPath = get_template_directory() . '/resources/views/admin/' . $view . '.php';

        if (!file_exists($viewPath)) {
            return;
        }

        (static function (string $__viewPath, array $__data): void {
            extract($__data, EXTR_SKIP);

            include $__viewPath;
        })($viewPath, $data);
    }

    /**
     * Mark a script handle as an ES module.
     *
     * @param string $handle Script handle to update.
     * @return void
     */
    private function markScriptAsModule(string $handle): void
    {
        add_filter('script_loader_tag', static function (string $tag, string $currentHandle, string $src) use ($handle): string {
            if ($currentHandle !== $handle) {
                return $tag;
            }

            /** @var string|null $updatedTag Updated script tag with module type. */
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
}
