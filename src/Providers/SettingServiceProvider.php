<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Carbon_Fields\Container;
use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Contracts\Setting;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;
use Jiejia\DaisyARippleSong\Settings\General;
use Jiejia\DaisyARippleSong\Settings\SocialLinks;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Registers Carbon Fields theme settings pages and related hooks.
 */
class SettingServiceProvider extends AbstractServiceProvider
{
    /** @var string $adminScriptEntry Theme options admin JavaScript entry. */
    private const ADMIN_SCRIPT_ENTRY = 'resources/js/admin.js';

    /** @var string $adminStyleEntry Theme options admin stylesheet entry. */
    private const ADMIN_STYLE_ENTRY = 'resources/css/admin.css';

    /** @var string $devServerUrl Base URL of the shared Vite development server. */
    private const DEV_SERVER_URL = 'http://127.0.0.1:5173';

    /** @var bool $settingsRegistered Whether settings containers have already been registered. */
    private bool $settingsRegistered = false;

    /**
     * Setting page classes registered by this provider.
     *
     * @var array<int,class-string<Setting>>
     */
    private array $settings = [
        General::class,
        SocialLinks::class,
    ];

    /**
     * Register settings hooks.
     *
     * @return void
     */
    public function register(): void
    {
        foreach (Theme::carbonFieldsRegisterHooks() as $hookName) {
            // Register settings containers when Carbon Fields asks for fields.
            add_action($hookName, [$this, 'registerSettings']);
        }

        // Load the theme options admin UI assets for Carbon Fields pages.
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        // Output frontend settings managed by the general settings page.
        add_action('wp_head', [General::class, 'outputThemePaletteStyles'], 1);
        add_action('wp_head', [General::class, 'outputHeaderScripts'], 99);
        add_action('wp_footer', [General::class, 'outputFooterScripts'], 99);
    }

    /**
     * Register all configured settings pages.
     *
     * @return void
     */
    public function registerSettings(): void
    {
        if ($this->settingsRegistered) {
            return;
        }

        $this->settingsRegistered = true;

        foreach ($this->settings as $settingClass) {
            // Create one Carbon Fields settings container per configured setting page.
            $setting = new $settingClass();

            Container::make_theme_options($setting->pageTitle())
                ->set_page_parent($setting->parentPageSlug())
                ->set_page_file($setting->pageSlug())
                ->set_page_menu_title($setting->pageTitle())
                ->add_fields($setting->fields());
        }
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

        return in_array($page, [ThemeOptions::OPTIONS_PAGE_FILE, ThemeOptions::GENERAL_PAGE_FILE, ThemeOptions::SOCIAL_PAGE_FILE], true);
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
