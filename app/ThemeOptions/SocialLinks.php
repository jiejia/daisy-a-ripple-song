<?php

namespace App\ThemeOptions;

use App\Core\CarbonCompat;

/**
 * Social links option helper.
 */
class SocialLinks
{
    /** @var string $settingPrefix Carbon Fields option key prefix. */
    public const SETTING_PREFIX = 'crb_social_';

    /**
     * Return all supported social platforms.
     *
     * @return array<string, array<string, string>>
     */
    public static function getPlatforms(): array
    {
        return [
            'facebook' => [
                'label' => __('Facebook', 'a-ripple-song'),
                'icon' => 'thumbs-up',
            ],
            'twitter' => [
                'label' => __('Twitter / X', 'a-ripple-song'),
                'icon' => 'message-circle',
            ],
            'instagram' => [
                'label' => __('Instagram', 'a-ripple-song'),
                'icon' => 'camera',
            ],
            'linkedin' => [
                'label' => __('LinkedIn', 'a-ripple-song'),
                'icon' => 'briefcase',
            ],
            'youtube' => [
                'label' => __('YouTube', 'a-ripple-song'),
                'icon' => 'play-circle',
            ],
            'tiktok' => [
                'label' => __('TikTok', 'a-ripple-song'),
                'icon' => 'music-2',
            ],
            'pinterest' => [
                'label' => __('Pinterest', 'a-ripple-song'),
                'icon' => 'pin',
            ],
            'threads' => [
                'label' => __('Threads', 'a-ripple-song'),
                'icon' => 'at-sign',
            ],
            'weibo' => [
                'label' => __('Weibo', 'a-ripple-song'),
                'icon' => 'message-circle',
            ],
            'wechat' => [
                'label' => __('WeChat', 'a-ripple-song'),
                'icon' => 'message-square',
            ],
            'rss' => [
                'label' => __('RSS Feed', 'a-ripple-song'),
                'icon' => 'rss',
            ],
        ];
    }

    /**
     * Return configured social links only.
     *
     * @return array<string, array<string, string>>
     */
    public static function getConfiguredLinks(): array
    {
        /** @var array<string, array<string, string>> $configuredLinks Prepared configured social links. */
        $configuredLinks = [];

        foreach (static::getPlatforms() as $platformKey => $platformData) {
            /** @var string $platformUrl Raw saved URL for the current platform. */
            $platformUrl = trim((string) CarbonCompat::getThemeOption(static::SETTING_PREFIX . $platformKey));

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
