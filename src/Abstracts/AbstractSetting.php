<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Jiejia\DaisyARippleSong\Contracts\Setting;

/**
 * Base class for Carbon Fields settings pages.
 */
abstract class AbstractSetting implements Setting
{
    /**
     * Return saved settings merged with defaults.
     *
     * @return array<string,mixed>
     */
    public function getSettings(): array
    {
        /** @var array<string,mixed> $settings Runtime settings merged with defaults. */
        $settings = $this->defaultSettings();
        /** @var bool $hasSavedSettings Whether Carbon Fields settings have been saved for this page. */
        $hasSavedSettings = $this->hasSavedSettings();

        foreach (array_keys($settings) as $settingKey) {
            /** @var mixed $storedValue Saved Carbon Fields value for the current setting. */
            $storedValue = function_exists('carbon_get_theme_option')
                ? carbon_get_theme_option($this->fieldName((string) $settingKey))
                : null;

            if ($this->hasStoredValue($storedValue)) {
                $settings[$settingKey] = $storedValue;
                continue;
            }

            if ($hasSavedSettings) {
                continue;
            }

            /** @var mixed $legacyValue Legacy native setting fallback for the current setting. */
            $legacyValue = $this->legacySettingValue((string) $settingKey);

            if ($this->hasStoredValue($legacyValue)) {
                $settings[$settingKey] = $legacyValue;
            }
        }

        return $settings;
    }

    /**
     * Return one saved setting.
     *
     * @param string $key Setting key without the page prefix.
     * @param mixed $default Default value used when the setting does not exist.
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        /** @var array<string,mixed> $settings Saved settings merged with defaults. */
        $settings = $this->getSettings();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * Return the Carbon Fields option key for a setting.
     *
     * @param string $key Setting key without the page prefix.
     * @return string
     */
    public function fieldName(string $key): string
    {
        return $this->fieldPrefix() . $key;
    }

    /**
     * Return one legacy value when the old native options storage still exists.
     *
     * @param string $key Setting key without the page prefix.
     * @return mixed
     */
    protected function legacySettingValue(string $key): mixed
    {
        return null;
    }

    /**
     * Return whether this settings page has already been saved through Carbon Fields.
     *
     * @return bool
     */
    protected function hasSavedSettings(): bool
    {
        return get_option($this->settingsMarkerFieldName(), null) !== null;
    }

    /**
     * Return the hidden marker field name stored with the settings page.
     *
     * @return string
     */
    protected function settingsMarkerFieldName(): string
    {
        return $this->fieldName('_saved');
    }

    /**
     * Return whether the stored value should override the runtime default.
     *
     * @param mixed $value Stored value from Carbon Fields or legacy storage.
     * @return bool
     */
    protected function hasStoredValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return $value !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }
}
