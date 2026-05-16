<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Jiejia\DaisyARippleSong\Contracts\Setting;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Base class for native theme settings sections.
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
        /** @var array<string,mixed> $sectionSettings Stored native option section values. */
        $sectionSettings = $this->getStoredOptionSection();

        foreach (array_keys($settings) as $settingKey) {
            if (array_key_exists((string) $settingKey, $sectionSettings)) {
                /** @var mixed $storedValue Saved native option value for the current setting. */
                $storedValue = $sectionSettings[(string) $settingKey];

                if (is_scalar($storedValue) || is_array($storedValue)) {
                    $settings[$settingKey] = $storedValue;
                }

                continue;
            }

            /** @var mixed $carbonValue Legacy Carbon Fields value for the current setting. */
            $carbonValue = $this->carbonSettingValue((string) $settingKey);

            if ($this->hasStoredValue($carbonValue)) {
                $settings[$settingKey] = $carbonValue;
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
     * Return the section key inside the native theme options array.
     *
     * @return string
     */
    protected function optionSection(): string
    {
        return '';
    }

    /**
     * Return the native option name used by the theme settings page.
     *
     * @return string
     */
    protected function settingsOptionName(): string
    {
        return Theme::PREFIX . '_theme_options';
    }

    /**
     * Return stored values for this setting section from the single native option.
     *
     * @return array<string,mixed>
     */
    protected function getStoredOptionSection(): array
    {
        /** @var string $sectionKey Section key inside the native option array. */
        $sectionKey = $this->optionSection();

        if ($sectionKey === '') {
            return [];
        }

        /** @var mixed $storedOptions Raw native option value. */
        $storedOptions = get_option($this->settingsOptionName(), []);

        if (!is_array($storedOptions) || !isset($storedOptions[$sectionKey]) || !is_array($storedOptions[$sectionKey])) {
            return [];
        }

        /** @var array<string,mixed> $sectionSettings Normalized section settings. */
        $sectionSettings = [];

        foreach ($storedOptions[$sectionKey] as $settingKey => $settingValue) {
            if (!is_string($settingKey)) {
                continue;
            }

            $sectionSettings[$settingKey] = $settingValue;
        }

        return $sectionSettings;
    }

    /**
     * Return one saved Carbon Fields value from the previous settings storage.
     *
     * @param string $key Setting key without the page prefix.
     * @return mixed
     */
    protected function carbonSettingValue(string $key): mixed
    {
        if (function_exists('carbon_get_theme_option')) {
            /** @var mixed $carbonValue Saved Carbon Fields theme option value. */
            $carbonValue = carbon_get_theme_option($this->fieldName($key));

            if ($carbonValue !== null) {
                return $carbonValue;
            }
        }

        return get_option($this->fieldName($key), null);
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
