<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Contracts\Setting;
use Jiejia\DaisyARippleSong\Settings\General;
use Jiejia\DaisyARippleSong\Settings\SocialLinks;

/**
 * Registers theme settings pages and related hooks.
 */
class SettingServiceProvider extends AbstractServiceProvider
{
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
        // Register each native settings page when WordPress initializes admin settings.
        add_action('admin_init', [$this, 'registerSettings']);

        // Load admin assets only on theme settings pages.
        add_action('admin_enqueue_scripts', [General::class, 'enqueueAdminAssets']);

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
        foreach ($this->settings as $settingClass) {
            // Let each setting page register its own option group and sanitizer.
            $setting = new $settingClass();
            $setting->registerSetting();
        }
    }
}
