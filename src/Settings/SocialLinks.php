<?php

namespace Jiejia\DaisyARippleSong\Settings;

use Jiejia\DaisyARippleSong\Abstracts\AbstractSetting;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;

/**
 * Social links option helper.
 */
class SocialLinks extends AbstractSetting
{
    /**
     * Return the settings page slug.
     *
     * @return string
     */
    public function pageSlug(): string
    {
        return ThemeOptions::SOCIAL_PAGE_FILE;
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
     * Return the WordPress settings group.
     *
     * @return string
     */
    public function optionGroup(): string
    {
        return General::SOCIAL_OPTION_GROUP;
    }

    /**
     * Return the serialized WordPress option name.
     *
     * @return string
     */
    public function optionName(): string
    {
        return General::SOCIAL_OPTION_NAME;
    }

    /**
     * Return field definitions for this settings page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fields(): array
    {
        return static::getSettingsFields();
    }

    /**
     * Return default settings for this page.
     *
     * @return array<string, mixed>
     */
    public function defaultSettings(): array
    {
        return [];
    }

    /**
     * Sanitize the submitted settings value.
     *
     * @param mixed $value Raw submitted value.
     * @return array<string, mixed>
     */
    public function sanitize($value): array
    {
        return General::sanitizeSocialLinksOptions($value);
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function renderPage(): void
    {
        echo General::renderAdminView('social-links', [
            'title' => $this->pageTitle(),
            'optionGroup' => $this->optionGroup(),
            'fieldsMarkup' => General::renderSettingsFields($this->fields()),
            'description' => __('Only filled links will be used by the theme.', 'daisy-a-ripple-song'),
        ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

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
