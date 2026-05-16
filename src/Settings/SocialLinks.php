<?php

namespace Jiejia\DaisyARippleSong\Settings;

use Jiejia\DaisyARippleSong\Abstracts\AbstractSetting;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Social links settings stored in the native theme options array.
 */
class SocialLinks extends AbstractSetting
{
    /** @var string OPTION_SECTION Native option section key for social links. */
    public const OPTION_SECTION = 'social_links';

    /**
     * Return the native settings page slug.
     *
     * @return string
     */
    public function pageSlug(): string
    {
        return ThemeOptions::OPTIONS_PAGE_FILE;
    }

    /**
     * Return the translated settings page title.
     *
     * @return string
     */
    public function pageTitle(): string
    {
        return __('Social Links', 'daisy-a-ripple-song');
    }

    /**
     * Return default settings for this page.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array
    {
        /** @var array<string,string> $defaults Default empty social links. */
        $defaults = [];

        foreach (array_keys(static::getPlatforms()) as $platformKey) {
            $defaults[$platformKey] = '';
        }

        return $defaults;
    }

    /**
     * Return the legacy per-field option key prefix for this settings page.
     *
     * @return string
     */
    public function fieldPrefix(): string
    {
        return Theme::PREFIX . '_social_';
    }

    /**
     * Return the parent menu slug for this settings page.
     *
     * @return string
     */
    public function parentPageSlug(): string
    {
        return ThemeOptions::PARENT_PAGE_FILE;
    }

    /**
     * Return one legacy native option value when the single option has not been saved yet.
     *
     * @param string $key Setting key without the page prefix.
     * @return mixed
     */
    protected function legacySettingValue(string $key): mixed
    {
        /** @var mixed $savedOptions Raw serialized option from WordPress. */
        $savedOptions = get_option(General::SOCIAL_OPTION_NAME, []);

        if (!is_array($savedOptions)) {
            return null;
        }

        return isset($savedOptions[$key]) && is_scalar($savedOptions[$key]) ? (string) $savedOptions[$key] : null;
    }

    /**
     * Return the section key inside the native theme options array.
     *
     * @return string
     */
    protected function optionSection(): string
    {
        return self::OPTION_SECTION;
    }

    /**
     * Return all supported social platforms.
     *
     * @return array<string,array<string,string>>
     */
    public static function getPlatforms(): array
    {
        return [
            'facebook' => [
                'label' => __('Facebook', 'daisy-a-ripple-song'),
                'icon' => 'thumbs-up',
            ],
            'twitter' => [
                'label' => __('Twitter / X', 'daisy-a-ripple-song'),
                'icon' => 'message-circle',
            ],
            'instagram' => [
                'label' => __('Instagram', 'daisy-a-ripple-song'),
                'icon' => 'camera',
            ],
            'linkedin' => [
                'label' => __('LinkedIn', 'daisy-a-ripple-song'),
                'icon' => 'briefcase',
            ],
            'youtube' => [
                'label' => __('YouTube', 'daisy-a-ripple-song'),
                'icon' => 'play-circle',
            ],
            'tiktok' => [
                'label' => __('TikTok', 'daisy-a-ripple-song'),
                'icon' => 'music-2',
            ],
            'pinterest' => [
                'label' => __('Pinterest', 'daisy-a-ripple-song'),
                'icon' => 'pin',
            ],
            'threads' => [
                'label' => __('Threads', 'daisy-a-ripple-song'),
                'icon' => 'at-sign',
            ],
            'weibo' => [
                'label' => __('Weibo', 'daisy-a-ripple-song'),
                'icon' => 'message-circle',
            ],
            'wechat' => [
                'label' => __('WeChat', 'daisy-a-ripple-song'),
                'icon' => 'message-square',
            ],
            'rss' => [
                'label' => __('RSS Feed', 'daisy-a-ripple-song'),
                'icon' => 'rss',
            ],
        ];
    }

    /**
     * Return configured social links only.
     *
     * @return array<string,array<string,string>>
     */
    public static function getConfiguredLinks(): array
    {
        /** @var array<string,array<string,string>> $configuredLinks Prepared configured social links. */
        $configuredLinks = [];

        foreach (static::getPlatforms() as $platformKey => $platformData) {
            /** @var string $platformUrl Raw saved URL for the current platform. */
            $platformUrl = General::getSocialLinkOption($platformKey);

            if ($platformUrl === '') {
                continue;
            }

            $configuredLinks[$platformKey] = [
                'url' => esc_url($platformUrl),
                'label' => $platformData['label'],
                'icon' => $platformData['icon'],
            ];
        }

        return $configuredLinks;
    }

    /**
     * Return whether any social links are configured.
     *
     * @return bool
     */
    public static function hasLinks(): bool
    {
        return !empty(static::getConfiguredLinks());
    }
}
