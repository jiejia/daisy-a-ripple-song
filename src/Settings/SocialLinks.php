<?php

namespace Jiejia\DaisyARippleSong\Settings;

use Carbon_Fields\Field;
use Jiejia\DaisyARippleSong\Abstracts\AbstractSetting;
use Jiejia\DaisyARippleSong\Menus\ThemeOptions;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Social links settings powered by Carbon Fields.
 */
class SocialLinks extends AbstractSetting
{
    /**
     * Return the Carbon Fields page slug.
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
     * Return all Carbon Fields fields for this settings page.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array
    {
        /** @var array<int,\Carbon_Fields\Field\Field> $fields Social link fields. */
        $fields = [];

        foreach (static::getPlatforms() as $platformKey => $platformData) {
            $fields[] = Field::make('text', $this->fieldName($platformKey), $platformData['label'])
                ->set_attribute('type', 'url')
                ->set_attribute('placeholder', __('Enter a full URL', 'daisy-a-ripple-song'))
                ->set_help_text(__('Optional. Enter a full URL.', 'daisy-a-ripple-song'));
        }

        return $fields;
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
     * Return the Carbon Fields key prefix for this settings page.
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
        return ThemeOptions::OPTIONS_PAGE_FILE;
    }

    /**
     * Return one legacy native option value when Carbon Fields has not been saved yet.
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
