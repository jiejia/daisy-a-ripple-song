<?php

namespace ARippleSong\Themes\Daisy\Core;

/**
 * Carbon Fields runtime bridge for scoped/unscoped builds plus bundled PHP translations.
 */
class Carbon
{
    /** @var string $sharedPrefix Shared first-party plugin namespace prefix used by PHP-Scoper in release builds. */
    protected const SHARED_PREFIX = '\\A_Ripple_Song_Podcast\\Vendor\\';

    /** @var string $scopedPrefix Theme namespace prefix used by PHP-Scoper in release builds. */
    protected const SCOPED_PREFIX = '\\A_Ripple_Song_Theme\\Vendor\\';

    /**
     * Boot Carbon Fields when either the scoped or unscoped class is available.
     *
     * @return void
     */
    public static function bootCarbonFields(): void
    {
        /** @var null|string $carbonClass Bootable Carbon Fields root class. */
        $carbonClass = static::resolveClass(
            '\\Carbon_Fields\\Carbon_Fields',
            static::SHARED_PREFIX . 'Carbon_Fields\\Carbon_Fields',
            static::SCOPED_PREFIX . 'Carbon_Fields\\Carbon_Fields'
        );

        if ($carbonClass !== null && method_exists($carbonClass, 'boot')) {
            $carbonClass::boot();
        }
    }

    /**
     * Resolve the Container proxy class for scoped or unscoped builds.
     *
     * @return null|string
     */
    public static function getContainerClass(): ?string
    {
        return static::resolveClass(
            '\\Carbon_Fields\\Container',
            static::SHARED_PREFIX . 'Carbon_Fields\\Container',
            static::SCOPED_PREFIX . 'Carbon_Fields\\Container'
        );
    }

    /**
     * Resolve the Field proxy class for scoped or unscoped builds.
     *
     * @return null|string
     */
    public static function getFieldClass(): ?string
    {
        return static::resolveClass(
            '\\Carbon_Fields\\Field',
            static::SHARED_PREFIX . 'Carbon_Fields\\Field',
            static::SCOPED_PREFIX . 'Carbon_Fields\\Field'
        );
    }

    /**
     * Resolve the Helper proxy class for scoped or unscoped builds.
     *
     * @return null|string
     */
    protected static function getHelperClass(): ?string
    {
        return static::resolveClass(
            '\\Carbon_Fields\\Helper\\Helper',
            static::SHARED_PREFIX . 'Carbon_Fields\\Helper\\Helper',
            static::SCOPED_PREFIX . 'Carbon_Fields\\Helper\\Helper'
        );
    }

    /**
     * Read a Carbon Fields theme option when the helper API is available.
     *
     * @param string $name Theme option key.
     * @param string $containerId Optional container ID.
     * @return mixed|null
     */
    public static function getThemeOption(string $name, string $containerId = '')
    {
        /** @var null|string $helperClass Helper class that can read theme options. */
        $helperClass = static::getHelperClass();

        if ($helperClass !== null && method_exists($helperClass, 'get_theme_option')) {
            return $helperClass::get_theme_option($name, $containerId);
        }

        if (function_exists('carbon_get_theme_option')) {
            return carbon_get_theme_option($name, $containerId);
        }

        return null;
    }

    /**
     * Persist a Carbon Fields theme option when the helper API is available.
     *
     * @param string $name Theme option key.
     * @param mixed $value Theme option value.
     * @param string $containerId Optional container ID.
     * @return bool
     */
    public static function setThemeOption(string $name, $value, string $containerId = ''): bool
    {
        /** @var null|string $helperClass Helper class that can write theme options. */
        $helperClass = static::getHelperClass();

        if ($helperClass !== null && method_exists($helperClass, 'set_theme_option')) {
            $helperClass::set_theme_option($name, $value, $containerId);

            return true;
        }

        if (function_exists('carbon_set_theme_option')) {
            carbon_set_theme_option($name, $value, $containerId);

            return true;
        }

        return false;
    }

    /**
     * Load the Carbon Fields PHP textdomain for the current admin locale.
     *
     * @return void
     */
    public function loadPhpTextdomain(): void
    {
        /** Resolve the active locale for admin and frontend contexts. */
        $locale = is_admin() ? get_user_locale() : get_locale();

        /** Build the bundled Carbon Fields MO file path for the current locale. */
        $moFile = get_template_directory() . '/resources/lang/carbon-fields-' . (string) $locale . '.mo';

        if (file_exists($moFile)) {
            load_textdomain('carbon-fields', $moFile);
        }
    }

    /**
     * Resolve a class that may exist in unscoped or scoped form.
     *
     * First prefer classes that are already loaded, then only autoload known scoped
     * candidates. This avoids probing unscoped Carbon Fields classes in a scoped build,
     * which can include the same file twice through different class names.
     *
     * @param string ...$candidates Class names in descending priority order.
     * @return null|string
     */
    protected static function resolveClass(string ...$candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if ($candidate !== '' && class_exists($candidate, false)) {
                return $candidate;
            }
        }

        $sharedBooted = did_action('carbon_fields_loaded') && class_exists('\\A_Ripple_Song_Podcast\\Vendor\\Carbon_Fields\\Carbon_Fields', false);
        $themeBooted  = did_action('carbon_fields_loaded') && class_exists('\\A_Ripple_Song_Theme\\Vendor\\Carbon_Fields\\Carbon_Fields', false);

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if (strpos($candidate, '\\Carbon_Fields\\') === 0) {
                /**
                 * Allow unscoped Carbon Fields autoloading only when no scoped build
                 * has been booted yet. Source checkouts depend on the Composer-loaded
                 * unscoped classes, while scoped releases must avoid probing them.
                 */
                if (!$sharedBooted && !$themeBooted && class_exists($candidate)) {
                    return $candidate;
                }

                continue;
            }

            if ($sharedBooted && strpos($candidate, static::SHARED_PREFIX) !== 0) {
                continue;
            }

            if ($themeBooted && strpos($candidate, static::SCOPED_PREFIX) !== 0) {
                continue;
            }

            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

class_alias(Carbon::class, CarbonCompat::class);
class_alias(Carbon::class, CarbonFieldsI18n::class);
