<?php

namespace App\Core;

/**
 * Carbon Fields compatibility layer for scoped and unscoped vendor builds.
 */
class CarbonCompat
{
    /** @var string $scopedPrefix Namespace prefix used by PHP-Scoper in release builds. */
    protected const SCOPED_PREFIX = '\\A_Ripple_Song_Theme\\Vendor\\';

    /**
     * Boot Carbon Fields when either the scoped or unscoped class is available.
     *
     * @return void
     */
    public static function bootCarbonFields(): void
    {
        /** @var null|string $carbonClass Bootable Carbon Fields root class. */
        $carbonClass = static::resolveClass('\\Carbon_Fields\\Carbon_Fields', static::SCOPED_PREFIX . 'Carbon_Fields\\Carbon_Fields');

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
        return static::resolveClass('\\Carbon_Fields\\Container', static::SCOPED_PREFIX . 'Carbon_Fields\\Container');
    }

    /**
     * Resolve the Field proxy class for scoped or unscoped builds.
     *
     * @return null|string
     */
    public static function getFieldClass(): ?string
    {
        return static::resolveClass('\\Carbon_Fields\\Field', static::SCOPED_PREFIX . 'Carbon_Fields\\Field');
    }

    /**
     * Resolve the Helper proxy class for scoped or unscoped builds.
     *
     * @return null|string
     */
    protected static function getHelperClass(): ?string
    {
        return static::resolveClass('\\Carbon_Fields\\Helper\\Helper', static::SCOPED_PREFIX . 'Carbon_Fields\\Helper\\Helper');
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
     * Resolve a class that may exist in scoped or unscoped form.
     *
     * @param string $unscoped Unscoped class name.
     * @param string $scoped Scoped class name.
     * @return null|string
     */
    protected static function resolveClass(string $unscoped, string $scoped): ?string
    {
        if ($scoped !== '' && class_exists($scoped)) {
            return $scoped;
        }

        if ($unscoped !== '' && class_exists($unscoped)) {
            return $unscoped;
        }

        return null;
    }
}
