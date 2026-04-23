<?php

namespace ARippleSong\Themes\Daisy\ThemeOptions;

/**
 * Social links option helper.
 */
class SocialLinks
{
    /**
     * Return all supported social platforms.
     *
     * @return array<string, array<string, string>>
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
     * Return field definitions for the social links settings page.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getSettingsFields(): array
    {
        /** @var array<int, array<string, mixed>> $fields Social link field definitions. */
        $fields = [];

        foreach (static::getPlatforms() as $platformKey => $platformData) {
            $fields[] = [
                'type' => 'url',
                'key' => $platformKey,
                'label' => $platformData['label'],
                'value' => General::getSocialLinkOption($platformKey),
                'description' => __('Optional. Enter a full URL.', 'daisy-a-ripple-song'),
                'optionName' => General::SOCIAL_OPTION_NAME,
            ];
        }

        return $fields;
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
