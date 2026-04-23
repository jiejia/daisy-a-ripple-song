<?php

namespace ARippleSong\Themes\Daisy\ThemeOptions;

use ARippleSong\Themes\Daisy\Constants\BaseConstant;
use ARippleSong\Themes\Daisy\Constants\ThemeConstant;

/**
 * Theme general options powered by native WordPress settings pages.
 */
class General
{
    /** @var string $optionsPageFile Theme options top-level menu slug. */
    protected const OPTIONS_PAGE_FILE = BaseConstant::PREFIX . '_theme_options';

    /** @var string $generalPageFile Theme general settings page slug. */
    protected const GENERAL_PAGE_FILE = BaseConstant::PREFIX . '_theme_general';

    /** @var string $socialPageFile Social links settings page slug. */
    protected const SOCIAL_PAGE_FILE = BaseConstant::PREFIX . '_theme_social_links';

    /** @var string $generalOptionGroup Settings group used by the general settings page. */
    protected const GENERAL_OPTION_GROUP = BaseConstant::PREFIX . '_general_options_group';

    /** @var string $socialOptionGroup Settings group used by the social links settings page. */
    protected const SOCIAL_OPTION_GROUP = BaseConstant::PREFIX . '_social_links_group';

    /** @var string $generalOptionName Serialized option name for general settings. */
    public const GENERAL_OPTION_NAME = BaseConstant::PREFIX . '_general_options';

    /** @var string $socialOptionName Serialized option name for social links. */
    public const SOCIAL_OPTION_NAME = BaseConstant::PREFIX . '_social_links';

    /** @var int $logoCropWidth Preferred site logo width. */
    protected const LOGO_CROP_WIDTH = 220;

    /** @var int $logoCropHeight Preferred site logo height. */
    protected const LOGO_CROP_HEIGHT = 32;

    /**
     * Register all option-related hooks.
     *
     * @return void
     */
    public static function boot(): void
    {
        add_action('admin_menu', [static::class, 'registerPages']);
        add_action('admin_init', [static::class, 'registerSettings']);
        add_action('admin_enqueue_scripts', [static::class, 'enqueueAdminAssets']);
        add_action('admin_head', [static::class, 'outputPickerAssets']);
        add_action('admin_head', [static::class, 'outputLogoAssets']);
        add_action('wp_head', [static::class, 'outputThemePaletteStyles'], 1);
        add_action('wp_head', [static::class, 'outputHeaderScripts'], 99);
        add_action('wp_footer', [static::class, 'outputFooterScripts'], 99);
    }

    /**
     * Register the theme settings pages in the admin menu.
     *
     * @return void
     */
    public static function registerPages(): void
    {
        add_menu_page(
            __('Theme Options', 'daisy-a-ripple-song'),
            __('Theme Options', 'daisy-a-ripple-song'),
            'edit_theme_options',
            static::OPTIONS_PAGE_FILE,
            [static::class, 'renderGeneralPage'],
            'dashicons-admin-settings',
            61
        );

        add_submenu_page(
            static::OPTIONS_PAGE_FILE,
            __('General', 'daisy-a-ripple-song'),
            __('General', 'daisy-a-ripple-song'),
            'edit_theme_options',
            static::GENERAL_PAGE_FILE,
            [static::class, 'renderGeneralPage']
        );

        // Remove the auto-generated duplicate submenu so "General" becomes the first item.
        remove_submenu_page(static::OPTIONS_PAGE_FILE, static::OPTIONS_PAGE_FILE);

        add_submenu_page(
            static::OPTIONS_PAGE_FILE,
            __('Social Links', 'daisy-a-ripple-song'),
            __('Social Links', 'daisy-a-ripple-song'),
            'edit_theme_options',
            static::SOCIAL_PAGE_FILE,
            [static::class, 'renderSocialPage']
        );
    }

    /**
     * Register native settings and sanitizers.
     *
     * @return void
     */
    public static function registerSettings(): void
    {
        register_setting(
            static::GENERAL_OPTION_GROUP,
            static::GENERAL_OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [static::class, 'sanitizeGeneralOptions'],
                'default' => static::getDefaultGeneralOptions(),
            ]
        );

        register_setting(
            static::SOCIAL_OPTION_GROUP,
            static::SOCIAL_OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [static::class, 'sanitizeSocialLinksOptions'],
                'default' => [],
            ]
        );
    }

    /**
     * Render the general settings page.
     *
     * @return void
     */
    public static function renderGeneralPage(): void
    {
        static::renderSettingsPage(
            __('General', 'daisy-a-ripple-song'),
            static::GENERAL_OPTION_GROUP,
            static::getGeneralFields()
        );
    }

    /**
     * Render the social links settings page.
     *
     * @return void
     */
    public static function renderSocialPage(): void
    {
        static::renderSettingsPage(
            __('Social Links', 'daisy-a-ripple-song'),
            static::SOCIAL_OPTION_GROUP,
            SocialLinks::getSettingsFields(),
            __('Only filled links will be used by the theme.', 'daisy-a-ripple-song')
        );
    }

    /**
     * Render a native WordPress settings page.
     *
     * @param string $title Page title.
     * @param string $optionGroup Settings API option group.
     * @param array<int, array<string, mixed>> $fields Field definitions.
     * @param string $description Optional page description.
     * @return void
     */
    protected static function renderSettingsPage(string $title, string $optionGroup, array $fields, string $description = ''): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($title) . '</h1>';

        if ($description !== '') {
            echo '<p class="description">' . esc_html($description) . '</p>';
        }

        settings_errors();
        echo '<form method="post" action="options.php">';
        settings_fields($optionGroup);
        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';
        echo static::renderSettingsFields($fields);
        echo '</tbody>';
        echo '</table>';
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    /**
     * Return field definitions for the general settings page.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function getGeneralFields(): array
    {
        return [
            [
                'type' => 'logo',
            ],
            [
                'type' => 'theme_picker',
                'key' => 'light_theme',
                'label' => __('Light Theme', 'daisy-a-ripple-song'),
                'options' => static::getLightThemeOptions(),
                'value' => static::getLightTheme(),
                'mode' => 'light',
                'description' => __('This is the default theme used when the site is in light mode.', 'daisy-a-ripple-song'),
            ],
            [
                'type' => 'theme_picker',
                'key' => 'dark_theme',
                'label' => __('Dark Theme', 'daisy-a-ripple-song'),
                'options' => static::getDarkThemeOptions(),
                'value' => static::getDarkTheme(),
                'mode' => 'dark',
                'description' => __('This is the default theme used when the site is in dark mode.', 'daisy-a-ripple-song'),
            ],
            [
                'type' => 'textarea',
                'key' => 'footer_copyright',
                'label' => __('Footer Copyright', 'daisy-a-ripple-song'),
                'value' => static::getThemeOption('footer_copyright'),
                'description' => __('Overrides the footer copyright line. Leave empty to use the default.', 'daisy-a-ripple-song'),
                'optionName' => static::GENERAL_OPTION_NAME,
            ],
            [
                'type' => 'textarea',
                'key' => 'header_scripts',
                'label' => __('Header Scripts', 'daisy-a-ripple-song'),
                'value' => static::getThemeOption('header_scripts'),
                'description' => __('Scripts to be added in the <head> section. You can include complete <script> tags for services like Google Analytics.', 'daisy-a-ripple-song'),
                'optionName' => static::GENERAL_OPTION_NAME,
            ],
            [
                'type' => 'textarea',
                'key' => 'footer_scripts',
                'label' => __('Footer Scripts', 'daisy-a-ripple-song'),
                'value' => static::getThemeOption('footer_scripts'),
                'description' => __('Scripts to be added before </body>. You can include complete <script> tags.', 'daisy-a-ripple-song'),
                'optionName' => static::GENERAL_OPTION_NAME,
            ],
        ];
    }

    /**
     * Enqueue the native media library on the general settings page.
     *
     * @return void
     */
    public static function enqueueAdminAssets(): void
    {
        if (!static::isGeneralSettingsPage()) {
            return;
        }

        wp_enqueue_media();
    }

    /**
     * Return whether the current admin request targets the theme general settings page.
     *
     * @return bool
     */
    protected static function isGeneralSettingsPage(): bool
    {
        if (!is_admin()) {
            return false;
        }

        /** @var string $page Current admin page slug. */
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash((string) $_GET['page'])) : '';

        return $page === static::OPTIONS_PAGE_FILE || $page === static::GENERAL_PAGE_FILE;
    }

    /**
     * Return default values for the serialized general option.
     *
     * @return array<string, string>
     */
    public static function getDefaultGeneralOptions(): array
    {
        return [
            'site_logo' => '',
            'light_theme' => 'retro',
            'dark_theme' => 'dim',
            'footer_copyright' => '',
            'header_scripts' => '',
            'footer_scripts' => '',
        ];
    }

    /**
     * Return all saved general settings merged with defaults.
     *
     * @return array<string, string>
     */
    public static function getGeneralOptions(): array
    {
        /** @var mixed $savedOptions Raw serialized option from WordPress. */
        $savedOptions = get_option(static::GENERAL_OPTION_NAME, []);

        if (!is_array($savedOptions)) {
            $savedOptions = [];
        }

        /** @var array<string, string> $normalizedOptions Normalized option values. */
        $normalizedOptions = [];

        foreach ($savedOptions as $optionKey => $optionValue) {
            if (!is_string($optionKey) || !is_scalar($optionValue)) {
                continue;
            }

            $normalizedOptions[$optionKey] = (string) $optionValue;
        }

        return array_merge(static::getDefaultGeneralOptions(), $normalizedOptions);
    }

    /**
     * Return all saved social links.
     *
     * @return array<string, string>
     */
    public static function getSocialLinksOptions(): array
    {
        /** @var mixed $savedOptions Raw serialized option from WordPress. */
        $savedOptions = get_option(static::SOCIAL_OPTION_NAME, []);

        if (!is_array($savedOptions)) {
            return [];
        }

        /** @var array<string, string> $normalizedOptions Normalized social link values. */
        $normalizedOptions = [];

        foreach ($savedOptions as $platformKey => $platformUrl) {
            if (!is_string($platformKey) || !is_scalar($platformUrl)) {
                continue;
            }

            $normalizedOptions[$platformKey] = (string) $platformUrl;
        }

        return $normalizedOptions;
    }

    /**
     * Return the saved general option value.
     *
     * @param string $key General option key.
     * @param string $default Fallback value.
     * @return string
     */
    public static function getThemeOption(string $key, string $default = ''): string
    {
        /** @var array<string, string> $options Saved general settings. */
        $options = static::getGeneralOptions();

        return array_key_exists($key, $options) ? $options[$key] : $default;
    }

    /**
     * Return the saved URL for a social platform.
     *
     * @param string $platformKey Social platform key.
     * @return string
     */
    public static function getSocialLinkOption(string $platformKey): string
    {
        /** @var array<string, string> $options Saved social link settings. */
        $options = static::getSocialLinksOptions();

        return trim($options[$platformKey] ?? '');
    }

    /**
     * Return the configured site logo URL.
     *
     * @return string
     */
    public static function getSiteLogoUrl(): string
    {
        return esc_url(static::getThemeOption('site_logo'));
    }

    /**
     * Return the default light theme slug.
     *
     * @return string
     */
    public static function getLightTheme(): string
    {
        /** @var string $themeSlug Saved light theme slug. */
        $themeSlug = static::getThemeOption('light_theme', 'retro');

        return array_key_exists($themeSlug, static::getLightThemeOptions()) ? $themeSlug : 'retro';
    }

    /**
     * Return the default dark theme slug.
     *
     * @return string
     */
    public static function getDarkTheme(): string
    {
        /** @var string $themeSlug Saved dark theme slug. */
        $themeSlug = static::getThemeOption('dark_theme', 'dim');

        return array_key_exists($themeSlug, static::getDarkThemeOptions()) ? $themeSlug : 'dim';
    }

    /**
     * Return theme data needed by the front-end theme store.
     *
     * @return array<string, mixed>
     */
    public static function getThemeModeConfig(): array
    {
        return [
            'lightTheme' => static::getLightTheme(),
            'darkTheme' => static::getDarkTheme(),
            'lightThemes' => array_keys(static::getLightThemeOptions()),
            'darkThemes' => array_keys(static::getDarkThemeOptions()),
            'palette' => static::getThemePalette(),
        ];
    }

    /**
     * Output CSS custom properties for the legacy theme palettes on the front end.
     *
     * @return void
     */
    public static function outputThemePaletteStyles(): void
    {
        if (is_admin()) {
            return;
        }

        /** @var array<string, array<string, string>> $themePalette Full theme palette map. */
        $themePalette = static::getThemePalette();

        if ($themePalette === []) {
            return;
        }

        /** @var array<string, bool> $darkThemeLookup Dark theme lookup table. */
        $darkThemeLookup = array_fill_keys(array_keys(static::getDarkThemeOptions()), true);

        /** @var array<int, string> $cssRules Generated CSS rules. */
        $cssRules = [];

        foreach ($themePalette as $themeSlug => $themeColors) {
            /** @var string $colorScheme Active CSS color-scheme for the theme. */
            $colorScheme = isset($darkThemeLookup[$themeSlug]) ? 'dark' : 'light';

            $cssRules[] = sprintf(
                ':root[data-theme="%1$s"]{color-scheme:%2$s;--color-base-100:%3$s;--color-base-200:%4$s;--color-base-300:%5$s;--color-base-content:%6$s;--color-primary:%7$s;--color-primary-content:%8$s;--color-secondary:%9$s;--color-secondary-content:%10$s;--color-accent:%11$s;--color-accent-content:%12$s;--color-neutral:%13$s;--color-neutral-content:%14$s;}',
                esc_attr($themeSlug),
                esc_attr($colorScheme),
                esc_attr($themeColors['base100'] ?? '#f3f4f6'),
                esc_attr($themeColors['base200'] ?? '#e5e7eb'),
                esc_attr($themeColors['base300'] ?? '#d1d5db'),
                esc_attr($themeColors['baseContent'] ?? '#111827'),
                esc_attr($themeColors['primary'] ?? '#570df8'),
                esc_attr($themeColors['primaryContent'] ?? '#ffffff'),
                esc_attr($themeColors['secondary'] ?? '#f000b8'),
                esc_attr($themeColors['secondaryContent'] ?? '#ffffff'),
                esc_attr($themeColors['accent'] ?? '#37cdbe'),
                esc_attr($themeColors['accentContent'] ?? '#ffffff'),
                esc_attr($themeColors['neutral'] ?? '#3d4451'),
                esc_attr($themeColors['neutralContent'] ?? '#f3f4f6')
            );
        }

        echo '<style id="ars-theme-palette-styles">' . implode('', $cssRules) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Return the configured footer copyright content.
     *
     * @return string
     */
    public static function getFooterCopyright(): string
    {
        return trim(static::getThemeOption('footer_copyright'));
    }

    /**
     * Output saved custom header scripts on the front end.
     *
     * @return void
     */
    public static function outputHeaderScripts(): void
    {
        if (is_admin()) {
            return;
        }

        /** @var string $headerScripts Saved header scripts. */
        $headerScripts = static::getThemeOption('header_scripts');

        if ($headerScripts === '') {
            return;
        }

        echo $headerScripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Output saved custom footer scripts on the front end.
     *
     * @return void
     */
    public static function outputFooterScripts(): void
    {
        if (is_admin()) {
            return;
        }

        /** @var string $footerScripts Saved footer scripts. */
        $footerScripts = static::getThemeOption('footer_scripts');

        if ($footerScripts === '') {
            return;
        }

        echo $footerScripts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Output inline assets for the native logo uploader.
     *
     * @return void
     */
    public static function outputLogoAssets(): void
    {
        if (!static::isGeneralSettingsPage()) {
            return;
        }

        ?>
        <script>
            (() => {
                /** @const {number} logoWidth Target logo width in pixels. */
                const logoWidth = <?php echo (int) static::LOGO_CROP_WIDTH; ?>;
                /** @const {number} logoHeight Target logo height in pixels. */
                const logoHeight = <?php echo (int) static::LOGO_CROP_HEIGHT; ?>;

                /**
                 * Decide whether the attachment must be cropped to the target size.
                 *
                 * @param {number} imgWidth Original attachment width.
                 * @param {number} imgHeight Original attachment height.
                 * @returns {boolean}
                 */
                const mustBeCropped = (imgWidth, imgHeight) => {
                    if (imgWidth === logoWidth && imgHeight === logoHeight) {
                        return false;
                    }

                    if (imgWidth <= logoWidth || imgHeight <= logoHeight) {
                        return false;
                    }

                    return true;
                };

                /**
                 * Build imgSelect options that lock the crop box to the target aspect ratio.
                 *
                 * @param {Object} attachment Media library attachment model.
                 * @param {Object} controller Cropper state controller.
                 * @returns {Object}
                 */
                const calculateImageSelectOptions = (attachment, controller) => {
                    const realWidth = parseInt(attachment.get('width'), 10) || 0;
                    const realHeight = parseInt(attachment.get('height'), 10) || 0;
                    const ratio = logoWidth / logoHeight;

                    let xInit = logoWidth;
                    let yInit = logoHeight;

                    if (realWidth / realHeight > ratio) {
                        yInit = realHeight;
                        xInit = yInit * ratio;
                    } else {
                        xInit = realWidth;
                        yInit = xInit / ratio;
                    }

                    const x1 = (realWidth - xInit) / 2;
                    const y1 = (realHeight - yInit) / 2;

                    controller.set('canSkipCrop', !mustBeCropped(realWidth, realHeight));

                    return {
                        handles: true,
                        keys: true,
                        instance: true,
                        persistent: true,
                        imageWidth: realWidth,
                        imageHeight: realHeight,
                        minWidth: logoWidth > xInit ? xInit : logoWidth,
                        minHeight: logoHeight > yInit ? yInit : logoHeight,
                        x1: x1,
                        y1: y1,
                        x2: xInit + x1,
                        y2: yInit + y1,
                        aspectRatio: logoWidth + ':' + logoHeight
                    };
                };

                /** @type {?Function} Cached cropper state constructor. */
                let ArsLogoCropper = null;

                /**
                 * Lazily build the cropper state constructor, forcing a fixed output size.
                 *
                 * @returns {Function}
                 */
                const getLogoCropperCtor = () => {
                    if (ArsLogoCropper) {
                        return ArsLogoCropper;
                    }

                    ArsLogoCropper = wp.media.controller.Cropper.extend({
                        doCrop: function(attachment) {
                            const cropDetails = attachment.get('cropDetails');
                            cropDetails.dst_width = logoWidth;
                            cropDetails.dst_height = logoHeight;
                            attachment.set('cropDetails', cropDetails);

                            return wp.ajax.post('crop-image', {
                                nonce: attachment.get('nonces').edit,
                                id: attachment.get('id'),
                                context: 'ars-site-logo',
                                cropDetails: cropDetails
                            });
                        }
                    });

                    return ArsLogoCropper;
                };

                const bindLogoUploader = () => {
                    const wrapper = document.querySelector('[data-ars-logo-uploader]');

                    if (!wrapper || wrapper.dataset.ready === 'true') {
                        return;
                    }

                    const input = wrapper.querySelector('[data-ars-logo-input]');
                    const selectButton = wrapper.querySelector('[data-ars-logo-select]');
                    const removeButton = wrapper.querySelector('[data-ars-logo-remove]');
                    const preview = wrapper.querySelector('[data-ars-logo-preview]');

                    if (!input || !selectButton || !removeButton || !preview) {
                        return;
                    }

                    wrapper.dataset.ready = 'true';

                    const renderPreview = (url) => {
                        preview.innerHTML = url
                            ? '<img src="' + url + '" alt="<?php echo esc_js(__('Site Logo', 'daisy-a-ripple-song')); ?>" style="display:block;width:' + logoWidth + 'px;height:' + logoHeight + 'px;margin-top:12px;border:1px solid #dcdcde;padding:8px;background:#fff;object-fit:contain;">'
                            : '';
                        removeButton.disabled = url === '';
                    };

                    const syncValue = (url) => {
                        input.value = url;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        renderPreview(url);
                    };

                    selectButton.addEventListener('click', (event) => {
                        event.preventDefault();

                        if (!window.wp || !wp.media || !wp.media.controller || !wp.media.controller.Cropper) {
                            return;
                        }

                        const CropperCtor = getLogoCropperCtor();
                        const cropperState = new CropperCtor({
                            imgSelectOptions: calculateImageSelectOptions
                        });

                        const frame = wp.media({
                            button: {
                                text: <?php echo wp_json_encode(__('Select and Crop', 'daisy-a-ripple-song')); ?>,
                                close: false
                            },
                            states: [
                                new wp.media.controller.Library({
                                    title: <?php echo wp_json_encode(__('Select Site Logo', 'daisy-a-ripple-song')); ?>,
                                    library: wp.media.query({ type: 'image' }),
                                    multiple: false,
                                    date: false,
                                    priority: 20,
                                    suggestedWidth: logoWidth,
                                    suggestedHeight: logoHeight
                                }),
                                cropperState
                            ]
                        });

                        const handleSkippedCrop = (selection) => {
                            const url = String(selection.get('url') || '').trim();

                            if (url !== '') {
                                syncValue(url);
                            }
                        };

                        frame.on('select', () => {
                            const selection = frame.state().get('selection');

                            if (!selection) {
                                return;
                            }

                            const attachment = selection.first();
                            const mime = String(attachment.get('mime') || '');
                            const realWidth = parseInt(attachment.get('width'), 10) || 0;
                            const realHeight = parseInt(attachment.get('height'), 10) || 0;

                            if (mime === 'image/svg+xml' || realWidth === 0 || realHeight === 0) {
                                const url = String(attachment.get('url') || '').trim();

                                if (url !== '') {
                                    syncValue(url);
                                }

                                frame.close();
                                return;
                            }

                            frame.setState('cropper');
                        });

                        frame.on('cropped', (croppedImage) => {
                            const url = String(croppedImage.url || '').trim();

                            if (url !== '') {
                                syncValue(url);
                            }
                        });

                        frame.on('skippedcrop', handleSkippedCrop);
                        cropperState.on('skippedcrop', handleSkippedCrop);

                        frame.open();
                    });

                    removeButton.addEventListener('click', (event) => {
                        event.preventDefault();
                        syncValue('');
                    });

                    renderPreview(String(input.value || '').trim());
                };

                document.addEventListener('DOMContentLoaded', bindLogoUploader);
            })();
        </script>
        <?php
    }

    /**
     * Output inline assets for native theme picker cards.
     *
     * @return void
     */
    public static function outputPickerAssets(): void
    {
        if (!static::isGeneralSettingsPage()) {
            return;
        }

        ?>
        <style>
            .ars-logo-uploader [data-ars-logo-input] {
                display: none;
            }

            .ars-theme-picker {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));
                gap: 10px;
                max-width: 920px;
                margin-bottom: 10px;
            }

            .ars-theme-card {
                position: relative;
                display: block;
                width: 100%;
                padding: 0;
                cursor: pointer;
                text-align: left;
                border: 1px solid #c3c4c7;
                border-radius: 10px;
                background: var(--ars-base-100);
                color: var(--ars-base-content);
                overflow: hidden;
                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
                transition: border-color 0.2s, box-shadow 0.2s;
            }

            .ars-theme-card:hover,
            .ars-theme-card:focus {
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
                outline: none;
            }

            .ars-theme-card.is-active {
                border-color: #2271b1;
                box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.22);
            }

            .ars-theme-card.is-active::after {
                position: absolute;
                top: 10px;
                right: 10px;
                width: 10px;
                height: 10px;
                content: "";
                border-radius: 999px;
                background: #2271b1;
            }

            .ars-theme-card__preview {
                display: grid;
                grid-template-columns: 1fr 4fr;
                grid-template-rows: 1fr;
                height: 100%;
                min-height: 72px;
                background: var(--ars-base-100);
            }

            .ars-theme-card__sidebar {
                display: flex;
                flex-direction: column;
            }

            .ars-theme-card__sidebar-top {
                flex: 2;
                background: var(--ars-base-200);
            }

            .ars-theme-card__sidebar-bottom {
                flex: 1;
                background: var(--ars-base-300);
            }

            .ars-theme-card__content {
                display: flex;
                flex-direction: column;
                gap: 6px;
                padding: 12px;
            }

            .ars-theme-card__name {
                display: block;
                font-weight: 600;
                font-size: 13px;
                line-height: 1;
            }

            .ars-theme-card__swatches {
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
            }

            .ars-theme-card__swatch {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
                border-radius: 4px;
                background: var(--ars-swatch);
                color: var(--ars-swatch-content);
                font-size: 11px;
                font-weight: 700;
                line-height: 1;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            }

            .ars-theme-select {
                display: none;
            }
        </style>
        <script>
            (() => {
                const bindThemePickers = () => {
                    document.querySelectorAll('[data-ars-theme-picker]').forEach((picker) => {
                        if (picker.dataset.ready === 'true') {
                            return;
                        }

                        const target = picker.dataset.themeTarget || '';
                        const select = document.querySelector('select[data-theme-target="' + target + '"]');
                        const cards = Array.from(picker.querySelectorAll('[data-theme-value]'));

                        if (!select || cards.length === 0) {
                            return;
                        }

                        picker.dataset.ready = 'true';

                        const syncCards = () => {
                            cards.forEach((card) => {
                                card.classList.toggle('is-active', card.dataset.themeValue === select.value);
                            });
                        };

                        cards.forEach((card) => {
                            card.addEventListener('click', (event) => {
                                event.preventDefault();
                                select.value = card.dataset.themeValue || '';
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                                syncCards();
                            });
                        });

                        select.addEventListener('change', syncCards);
                        syncCards();
                    });
                };

                document.addEventListener('DOMContentLoaded', bindThemePickers);
            })();
        </script>
        <?php
    }

    /**
     * Render the native logo field row.
     *
     * @param string $rowClass Optional extra CSS classes for the table row.
     * @return string
     */
    protected static function renderLogoRow(string $rowClass = ''): string
    {
        /** @var string $currentLogo Saved logo URL. */
        $currentLogo = static::getThemeOption('site_logo');

        return sprintf(
            '<tr class="%1$s"><th scope="row"><label for="site_logo">%2$s</label></th><td><div class="ars-logo-uploader" data-ars-logo-uploader><input type="url" class="regular-text" id="site_logo" name="%8$s[site_logo]" value="%3$s" placeholder="https://example.com/logo.svg" data-ars-logo-input><p class="description">%4$s</p><p><button type="button" class="button button-primary" data-ars-logo-select>%5$s</button> <button type="button" class="button" data-ars-logo-remove>%6$s</button></p><div class="ars-logo-preview" data-ars-logo-preview>%7$s</div></div></td></tr>',
            esc_attr($rowClass),
            esc_html__('Site Logo', 'daisy-a-ripple-song'),
            esc_attr($currentLogo),
            esc_html__('Upload a logo image (220px × 32px). You will be able to crop the image after upload.', 'daisy-a-ripple-song'),
            esc_html__('Upload / Change Logo', 'daisy-a-ripple-song'),
            esc_html__('Remove Logo', 'daisy-a-ripple-song'),
            static::renderLogoPreview($currentLogo),
            esc_attr(static::GENERAL_OPTION_NAME)
        );
    }

    /**
     * Render a list of settings fields from definitions.
     *
     * @param array<int, array<string, mixed>> $fields Field definitions.
     * @return string
     */
    protected static function renderSettingsFields(array $fields): string
    {
        /** @var array<int, string> $markup Generated field rows. */
        $markup = [];

        foreach ($fields as $field) {
            $markup[] = static::renderSettingsField($field);
        }

        return implode('', $markup);
    }

    /**
     * Render one settings field from a definition.
     *
     * @param array<string, mixed> $field Field definition.
     * @return string
     */
    protected static function renderSettingsField(array $field): string
    {
        /** @var string $type Field renderer type. */
        $type = is_string($field['type'] ?? null) ? $field['type'] : '';

        if ($type === 'logo') {
            return static::renderLogoRow((string) ($field['rowClass'] ?? ''));
        }

        /** @var string $optionKey Field key inside the serialized option. */
        $optionKey = is_string($field['key'] ?? null) ? $field['key'] : '';
        if ($optionKey === '') {
            return '';
        }

        /** @var string $label Field label. */
        $label = is_string($field['label'] ?? null) ? $field['label'] : '';
        /** @var string $value Current field value. */
        $value = is_scalar($field['value'] ?? null) ? (string) $field['value'] : '';
        /** @var string $description Field help text. */
        $description = is_string($field['description'] ?? null) ? $field['description'] : '';
        /** @var string $optionName Serialized option name. */
        $optionName = is_string($field['optionName'] ?? null) ? $field['optionName'] : static::GENERAL_OPTION_NAME;

        if ($type === 'theme_picker') {
            /** @var array<string, string> $options Theme picker options. */
            $options = is_array($field['options'] ?? null) ? $field['options'] : [];
            /** @var string $mode Theme mode identifier. */
            $mode = is_string($field['mode'] ?? null) ? $field['mode'] : $optionKey;

            return static::renderThemePickerRow($optionKey, $label, $options, $value, $mode, $description);
        }

        if ($type === 'select') {
            /** @var array<string, string> $options Select options. */
            $options = is_array($field['options'] ?? null) ? $field['options'] : [];

            return static::renderSelectRow($optionKey, $label, $options, $value, $description, $optionName);
        }

        if ($type === 'url') {
            return static::renderUrlRow($optionKey, $label, $value, $description, $optionName);
        }

        if ($type === 'textarea') {
            return static::renderTextareaRow($optionKey, $label, $value, $description, $optionName);
        }

        return '';
    }

    /**
     * Render a theme picker row with color swatches and a native select fallback.
     *
     * @param string $optionKey Option key.
     * @param string $label Field label.
     * @param array<string, string> $options Select options.
     * @param string $value Current value.
     * @param string $mode Theme mode identifier.
     * @param string $description Field description.
     * @param string $rowClass Optional extra CSS classes for the table row.
     * @return string
     */
    protected static function renderThemePickerRow(
        string $optionKey,
        string $label,
        array $options,
        string $value,
        string $mode,
        string $description,
        string $rowClass = ''
    ): string {
        return sprintf(
            '<tr class="%1$s"><th scope="row"><label for="%2$s">%3$s</label></th><td>%4$s%5$s<p class="description">%6$s</p></td></tr>',
            esc_attr($rowClass),
            esc_attr($optionKey),
            esc_html($label),
            static::renderDaisyUiThemePicker($mode, $options, $value),
            static::renderThemeSelect($optionKey, $options, $value, $mode),
            esc_html($description)
        );
    }

    /**
     * Render DaisyUI theme swatch cards.
     *
     * @param string $mode Theme mode identifier.
     * @param array<string, string> $options Theme options.
     * @param string $value Current value.
     * @return string
     */
    protected static function renderDaisyUiThemePicker(string $mode, array $options, string $value): string
    {
        /** @var array<string, array<string, string>> $themePalette Full theme palette map. */
        $themePalette = static::getThemePalette();

        /** @var array<int, string> $markup Generated picker fragments. */
        $markup = [];
        $markup[] = '<div class="ars-theme-picker" data-ars-theme-picker data-theme-target="' . esc_attr($mode) . '">';

        foreach ($options as $themeSlug => $themeLabel) {
            /** @var array<string, string> $colors Theme color palette. */
            $colors = $themePalette[$themeSlug] ?? [];

            $markup[] = sprintf(
                '<button type="button" class="ars-theme-card%1$s" data-theme-value="%2$s" style="--ars-base-100:%3$s;--ars-base-200:%4$s;--ars-base-300:%5$s;--ars-base-content:%6$s;"><div class="ars-theme-card__preview"><div class="ars-theme-card__sidebar"><div class="ars-theme-card__sidebar-top"></div><div class="ars-theme-card__sidebar-bottom"></div></div><div class="ars-theme-card__content"><span class="ars-theme-card__name">%7$s</span><span class="ars-theme-card__swatches" aria-hidden="true">%8$s</span></div></div></button>',
                $value === $themeSlug ? ' is-active' : '',
                esc_attr($themeSlug),
                esc_attr($colors['base100'] ?? '#f3f4f6'),
                esc_attr($colors['base200'] ?? '#e5e7eb'),
                esc_attr($colors['base300'] ?? '#d1d5db'),
                esc_attr($colors['baseContent'] ?? '#111827'),
                esc_html($themeLabel),
                static::renderThemeSwatches($colors)
            );
        }

        $markup[] = '</div>';

        return implode('', $markup);
    }

    /**
     * Render color swatches for a theme palette.
     *
     * @param array<string, string> $colors Theme colors.
     * @return string
     */
    protected static function renderThemeSwatches(array $colors): string
    {
        /** @var array<string, string> $swatchKeys Palette keys shown in the card. */
        $swatchKeys = [
            'primary' => 'primaryContent',
            'secondary' => 'secondaryContent',
            'accent' => 'accentContent',
            'neutral' => 'neutralContent',
        ];

        /** @var array<int, string> $markup Generated swatch fragments. */
        $markup = [];

        foreach ($swatchKeys as $swatchKey => $contentKey) {
            $markup[] = sprintf(
                '<span class="ars-theme-card__swatch" style="--ars-swatch:%s;--ars-swatch-content:%s;">A</span>',
                esc_attr($colors[$swatchKey] ?? '#d1d5db'),
                esc_attr($colors[$contentKey] ?? '#ffffff')
            );
        }

        return implode('', $markup);
    }

    /**
     * Render the native select fallback for theme selection.
     *
     * @param string $optionKey Option key.
     * @param array<string, string> $options Select options.
     * @param string $value Current value.
     * @param string $mode Theme mode identifier.
     * @return string
     */
    protected static function renderThemeSelect(string $optionKey, array $options, string $value, string $mode): string
    {
        /** @var array<int, string> $markup Generated select fragments. */
        $markup = [];
        $markup[] = '<select class="ars-theme-select" id="' . esc_attr($optionKey) . '" name="' . esc_attr(static::GENERAL_OPTION_NAME) . '[' . esc_attr($optionKey) . ']" data-theme-target="' . esc_attr($mode) . '">';

        foreach ($options as $optionValue => $optionLabel) {
            $markup[] = sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr($optionValue),
                selected($value, $optionValue, false),
                esc_html($optionLabel)
            );
        }

        $markup[] = '</select>';

        return implode('', $markup);
    }

    /**
     * Render a select field row.
     *
     * @param string $optionKey Option key.
     * @param string $label Field label.
     * @param array<string, string> $options Select options.
     * @param string $value Current value.
     * @param string $description Field description.
     * @return string
     */
    protected static function renderSelectRow(string $optionKey, string $label, array $options, string $value, string $description, string $optionName = self::GENERAL_OPTION_NAME): string
    {
        /** @var array<int, string> $markup Generated row fragments. */
        $markup = [];
        $markup[] = '<tr>';
        $markup[] = '<th scope="row"><label for="' . esc_attr($optionKey) . '">' . esc_html($label) . '</label></th>';
        $markup[] = '<td>';
        $markup[] = '<select id="' . esc_attr($optionKey) . '" name="' . esc_attr($optionName) . '[' . esc_attr($optionKey) . ']">';

        foreach ($options as $optionValue => $optionLabel) {
            $markup[] = sprintf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr($optionValue),
                selected($value, $optionValue, false),
                esc_html($optionLabel)
            );
        }

        $markup[] = '</select>';
        $markup[] = '<p class="description">' . esc_html($description) . '</p>';
        $markup[] = '</td>';
        $markup[] = '</tr>';

        return implode('', $markup);
    }

    /**
     * Render a textarea field row.
     *
     * @param string $optionKey Option key.
     * @param string $label Field label.
     * @param string $value Current value.
     * @param string $description Field description.
     * @return string
     */
    protected static function renderTextareaRow(string $optionKey, string $label, string $value, string $description, string $optionName = self::GENERAL_OPTION_NAME): string
    {
        return sprintf(
            '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><textarea id="%1$s" name="%5$s[%1$s]" class="large-text code" rows="5">%3$s</textarea><p class="description">%4$s</p></td></tr>',
            esc_attr($optionKey),
            esc_html($label),
            esc_textarea($value),
            esc_html($description),
            esc_attr($optionName)
        );
    }

    /**
     * Render a URL field row.
     *
     * @param string $optionKey Option key.
     * @param string $label Field label.
     * @param string $value Current value.
     * @param string $description Field description.
     * @return string
     */
    protected static function renderUrlRow(string $optionKey, string $label, string $value, string $description, string $optionName = self::SOCIAL_OPTION_NAME): string
    {
        return sprintf(
            '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="url" id="%1$s" name="%5$s[%1$s]" class="regular-text" value="%3$s" placeholder="https://example.com"><p class="description">%4$s</p></td></tr>',
            esc_attr($optionKey),
            esc_html($label),
            esc_attr($value),
            esc_html($description),
            esc_attr($optionName)
        );
    }

    /**
     * Sanitize the serialized general options array.
     *
     * @param mixed $value Raw option value.
     * @return array<string, string>
     */
    public static function sanitizeGeneralOptions($value): array
    {
        /** @var array<string, mixed> $input Submitted general settings. */
        $input = is_array($value) ? $value : [];
        /** @var array<string, string> $sanitizedOptions Sanitized general settings. */
        $sanitizedOptions = [];

        foreach (static::getGeneralSanitizers() as $optionKey => $sanitizeCallback) {
            $sanitizedOptions[$optionKey] = (string) call_user_func([static::class, $sanitizeCallback], $input[$optionKey] ?? '');
        }

        return $sanitizedOptions;
    }

    /**
     * Return sanitizers for the serialized general option.
     *
     * @return array<string, string>
     */
    protected static function getGeneralSanitizers(): array
    {
        return [
            'site_logo' => 'sanitizeUrlOption',
            'light_theme' => 'sanitizeLightTheme',
            'dark_theme' => 'sanitizeDarkTheme',
            'footer_copyright' => 'sanitizeHtmlOption',
            'header_scripts' => 'sanitizeRawCode',
            'footer_scripts' => 'sanitizeRawCode',
        ];
    }

    /**
     * Sanitize the serialized social links options array.
     *
     * @param mixed $value Raw option value.
     * @return array<string, string>
     */
    public static function sanitizeSocialLinksOptions($value): array
    {
        /** @var array<string, mixed> $input Submitted social link settings. */
        $input = is_array($value) ? $value : [];

        /** @var array<string, string> $sanitizedOptions Sanitized social link settings. */
        $sanitizedOptions = [];

        foreach (SocialLinks::getPlatforms() as $platformKey => $platformData) {
            /** @var string $platformUrl Sanitized platform URL. */
            $platformUrl = static::sanitizeUrlOption($input[$platformKey] ?? '');

            if ($platformUrl === '') {
                continue;
            }

            $sanitizedOptions[$platformKey] = $platformUrl;
        }

        return $sanitizedOptions;
    }

    /**
     * Render a logo preview image.
     *
     * @param string $logoUrl Logo URL.
     * @return string
     */
    protected static function renderLogoPreview(string $logoUrl): string
    {
        if ($logoUrl === '') {
            return '';
        }

        return sprintf(
            '<img src="%1$s" alt="%2$s" style="display:block;width:%3$dpx;height:%4$dpx;margin-top:12px;border:1px solid #dcdcde;padding:8px;background:#fff;object-fit:contain;">',
            esc_url($logoUrl),
            esc_attr__('Site Logo', 'daisy-a-ripple-song'),
            (int) static::LOGO_CROP_WIDTH,
            (int) static::LOGO_CROP_HEIGHT
        );
    }

    /**
     * Sanitize a URL option.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeUrlOption($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return esc_url_raw(trim((string) wp_unslash($value)));
    }

    /**
     * Sanitize HTML allowed in the footer copyright field.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeHtmlOption($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return wp_kses_post((string) wp_unslash($value));
    }

    /**
     * Sanitize a raw code field.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeRawCode($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return trim((string) wp_unslash($value));
    }

    /**
     * Sanitize the light theme slug.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeLightTheme($value): string
    {
        return static::sanitizeThemeSlug(is_scalar($value) ? (string) $value : '', static::getLightThemeOptions(), 'retro');
    }

    /**
     * Sanitize the dark theme slug.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeDarkTheme($value): string
    {
        return static::sanitizeThemeSlug(is_scalar($value) ? (string) $value : '', static::getDarkThemeOptions(), 'dim');
    }

    /**
     * Sanitize a theme slug against a whitelist.
     *
     * @param string $value Raw theme slug.
     * @param array<string, string> $allowedValues Allowed theme values.
     * @param string $default Default fallback value.
     * @return string
     */
    protected static function sanitizeThemeSlug(string $value, array $allowedValues, string $default): string
    {
        /** @var string $themeSlug Sanitized theme slug. */
        $themeSlug = sanitize_key($value);

        return array_key_exists($themeSlug, $allowedValues) ? $themeSlug : $default;
    }

    /**
     * Return the configured light DaisyUI themes.
     *
     * @return array<string, string>
     */
    public static function getLightThemeOptions(): array
    {
        return ThemeConstant::LIGHT;
    }

    /**
     * Return the configured dark DaisyUI themes.
     *
     * @return array<string, string>
     */
    public static function getDarkThemeOptions(): array
    {
        return ThemeConstant::DARK;
    }

    /**
     * Return the full legacy palette map.
     *
     * @return array<string, array<string, string>>
     */
    public static function getThemePalette(): array
    {
        return ThemeConstant::PALETTE;
    }
}
