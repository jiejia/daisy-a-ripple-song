<?php
namespace Jiejia\DaisyARippleSong;

use Jiejia\DaisyARippleSong\Contracts\ServiceProvider;
use Jiejia\DaisyARippleSong\Providers\AssetServiceProvider;
use Jiejia\DaisyARippleSong\Providers\BlockServiceProvider;
use Jiejia\DaisyARippleSong\Providers\CarbonFieldsServiceProvider;
use Jiejia\DaisyARippleSong\Providers\CommentServiceProvider;
use Jiejia\DaisyARippleSong\Providers\CustomAreaServiceProvider;
use Jiejia\DaisyARippleSong\Providers\MenuServiceProvider;
use Jiejia\DaisyARippleSong\Providers\NavigationServiceProvider;
use Jiejia\DaisyARippleSong\Providers\PodcastIntegrationServiceProvider;
use Jiejia\DaisyARippleSong\Providers\SettingServiceProvider;
use Jiejia\DaisyARippleSong\Providers\ThemeSupportServiceProvider;
use Jiejia\DaisyARippleSong\Providers\TranslationServiceProvider;
use Jiejia\DaisyARippleSong\Providers\WidgetServiceProvider;

/**
 * Main theme application class.
 */
class Theme
{
    public const SLUG = 'daisy-a-ripple-song';

    public const NAME = 'Daisy A Ripple Song';

    public const VERSION = '0.5.2';

    public const DIR = DAISY_A_RIPPLE_SONG_THEME_DIR;

    public const NAMESPACE_PREFIX = 'Jiejia\DaisyARippleSong';

    public const PREFIX = 'daisyaripplesong';

    public const NAME_PREFIX = 'ARS';

    /**
     * Service provider classes registered by this theme.
     *
     * @var array<int,class-string<ServiceProvider>>
     */
    private array $providers = [
        TranslationServiceProvider::class,
        ThemeSupportServiceProvider::class,
        CarbonFieldsServiceProvider::class,
        NavigationServiceProvider::class,
        MenuServiceProvider::class,
        BlockServiceProvider::class,
        AssetServiceProvider::class,
        CustomAreaServiceProvider::class,
        WidgetServiceProvider::class,
        SettingServiceProvider::class,
        CommentServiceProvider::class,
        PodcastIntegrationServiceProvider::class,
    ];

    /**
     * Service provider instances created during theme boot.
     *
     * @var array<int,ServiceProvider>
     */
    private array $providerInstances = [];

    /**
     * Create the theme application and boot its providers.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Return a theme-prefixed field key.
     *
     * @param string $key Raw field key.
     * @return string
     */
    public static function fieldKey(string $key): string
    {
        return self::PREFIX . '_' . $key;
    }

    /**
     * Return whether the companion podcast plugin runtime is loaded.
     *
     * @return bool
     */
    public static function isPodcastPluginActivated(): bool
    {
        return class_exists(\Jiejia\ARippleSong\Plugin::class, false);
    }

    /**
     * Return Carbon Fields registration hooks supported by the current theme runtime.
     *
     * PHP-Scoper prefixes Carbon Fields' internal registration action in scoped
     * packages, so theme providers must listen to the theme-owned hook names.
     *
     * @return array<int,string>
     */
    public static function carbonFieldsRegisterHooks(): array
    {
        /** @var array<int,string> $hooks Carbon Fields registration action names. */
        $hooks = [];

        if (class_exists('Jiejia\\DaisyARippleSong\\Vendor\\Carbon_Fields\\Carbon_Fields', false)) {
            // Scoped release builds isolate Carbon Fields hooks with the theme prefix.
            $hooks[] = self::PREFIX . '_carbon_fields_register_fields';
        } elseif (class_exists('Carbon_Fields\\Carbon_Fields', false)) {
            // Unscoped development installs use Carbon Fields' original hook names.
            $hooks[] = 'carbon_fields_register_fields';
        } else {
            // Keep both hooks available while Composer autoloading is still settling.
            $hooks[] = self::PREFIX . '_carbon_fields_register_fields';
            $hooks[] = 'carbon_fields_register_fields';
        }

        return array_values(array_unique(array_filter($hooks)));
    }

    /**
     * Initialize the theme lifecycle.
     *
     * @return void
     */
    public function init(): void
    {
        $this->defineCompatibilityConstants();
        $this->registerProviders();
        $this->bootProviders();
    }

    /**
     * Define compatibility constants used by existing templates and widgets.
     *
     * @return void
     */
    private function defineCompatibilityConstants(): void
    {
        if (defined('IS_PODCAST_PLUGIN_ACTIVATED')) {
            return;
        }

        define('IS_PODCAST_PLUGIN_ACTIVATED', self::isPodcastPluginActivated());
    }

    /**
     * Register all configured service providers.
     *
     * @return void
     */
    private function registerProviders(): void
    {
        foreach ($this->providers as $providerClass) {
            // Keep provider instances so boot() runs on the same objects.
            $provider = new $providerClass();
            $provider->register();

            $this->providerInstances[] = $provider;
        }
    }

    /**
     * Boot all registered service providers.
     *
     * @return void
     */
    private function bootProviders(): void
    {
        foreach ($this->providerInstances as $provider) {
            // Allow providers to run logic after every provider is registered.
            $provider->boot();
        }
    }
}
