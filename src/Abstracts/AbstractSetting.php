<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Jiejia\DaisyARippleSong\Contracts\Setting;

/**
 * Base class for native WordPress settings pages.
 */
abstract class AbstractSetting implements Setting
{
    /**
     * Register this setting with WordPress.
     *
     * @return void
     */
    public function registerSetting(): void
    {
        register_setting(
            $this->optionGroup(),
            $this->optionName(),
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize'],
                'default' => $this->defaultSettings(),
            ]
        );
    }

    /**
     * Return saved settings merged with defaults.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        /** @var mixed $savedOptions Raw serialized option from WordPress. */
        $savedOptions = get_option($this->optionName(), []);

        if (!is_array($savedOptions)) {
            $savedOptions = [];
        }

        return array_merge($this->defaultSettings(), $savedOptions);
    }

    /**
     * Return one saved setting.
     *
     * @param string $key Setting key.
     * @param mixed  $default Fallback value.
     * @return mixed
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        /** @var array<string, mixed> $settings Saved settings merged with defaults. */
        $settings = $this->getSettings();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }
}
