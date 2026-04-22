<?php

namespace ARippleSong\Themes\Daisy\ThemeOptions;

use ARippleSong\Themes\Daisy\Constants\ThemeConstant;

/**
 * Theme general options powered by native WordPress settings pages.
 */
class General
{
    /** @var string $optionsPageFile Theme options top-level menu slug. */
    protected const OPTIONS_PAGE_FILE = 'ars_theme_options.php';

    /** @var string $generalPageFile Theme general settings page slug. */
    protected const GENERAL_PAGE_FILE = 'ars_theme_general.php';

    /** @var string $socialPageFile Social links settings page slug. */
    protected const SOCIAL_PAGE_FILE = 'ars_theme_social_links.php';

    /** @var string $optionGroup Shared settings group used by both settings pages. */
    protected const OPTION_GROUP = 'aripplesong_theme_options';

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
            __('Theme Options', 'a-ripple-song'),
            __('Theme Options', 'a-ripple-song'),
            'edit_theme_options',
            static::OPTIONS_PAGE_FILE,
            [static::class, 'renderGeneralPage'],
            'dashicons-admin-settings',
            61
        );

        add_submenu_page(
            static::OPTIONS_PAGE_FILE,
            __('General', 'a-ripple-song'),
            __('General', 'a-ripple-song'),
            'edit_theme_options',
            static::GENERAL_PAGE_FILE,
            [static::class, 'renderGeneralPage']
        );

        // Remove the auto-generated duplicate submenu so "General" becomes the first item.
        remove_submenu_page(static::OPTIONS_PAGE_FILE, static::OPTIONS_PAGE_FILE);

        add_submenu_page(
            static::OPTIONS_PAGE_FILE,
            __('Social Links', 'a-ripple-song'),
            __('Social Links', 'a-ripple-song'),
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
            static::OPTION_GROUP,
            'crb_site_logo',
            [
                'type' => 'string',
                'sanitize_callback' => [static::class, 'sanitizeUrlOption'],
                'default' => '',
            ]
        );

        register_setting(
            static::OPTION_GROUP,
            'crb_light_theme',
            [
                'type' => 'string',
                'sanitize_callback' => [static::class, 'sanitizeLightTheme'],
                'default' => 'retro',
            ]
        );

        register_setting(
            static::OPTION_GROUP,
            'crb_dark_theme',
            [
                'type' => 'string',
                'sanitize_callback' => [static::class, 'sanitizeDarkTheme'],
                'default' => 'dim',
            ]
        );

        register_setting(
            static::OPTION_GROUP,
            'crb_footer_copyright',
            [
                'type' => 'string',
                'sanitize_callback' => [static::class, 'sanitizeHtmlOption'],
                'default' => '',
            ]
        );

        register_setting(
            static::OPTION_GROUP,
            'crb_header_scripts',
            [
                'type' => 'string',
                'sanitize_callback' => [static::class, 'sanitizeRawCode'],
                'default' => '',
            ]
        );

        register_setting(
            static::OPTION_GROUP,
            'crb_footer_scripts',
            [
                'type' => 'string',
                'sanitize_callback' => [static::class, 'sanitizeRawCode'],
                'default' => '',
            ]
        );

        foreach (SocialLinks::getPlatforms() as $platformKey => $platformData) {
            register_setting(
                static::OPTION_GROUP,
                SocialLinks::SETTING_PREFIX . $platformKey,
                [
                    'type' => 'string',
                    'sanitize_callback' => [static::class, 'sanitizeUrlOption'],
                    'default' => '',
                ]
            );
        }
    }

    /**
     * Render the general settings page.
     *
     * @return void
     */
    public static function renderGeneralPage(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('General', 'a-ripple-song') . '</h1>';
        settings_errors();
        echo '<form method="post" action="options.php">';
        settings_fields(static::OPTION_GROUP);
        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';
        echo static::renderLogoRow();
        echo static::renderThemePickerRow(
            'crb_light_theme',
            __('Light Theme', 'a-ripple-song'),
            static::getLightThemeOptions(),
            static::getLightTheme(),
            'light',
            __('This is the default theme used when the site is in light mode.', 'a-ripple-song')
        );
        echo static::renderThemePickerRow(
            'crb_dark_theme',
            __('Dark Theme', 'a-ripple-song'),
            static::getDarkThemeOptions(),
            static::getDarkTheme(),
            'dark',
            __('This is the default theme used when the site is in dark mode.', 'a-ripple-song')
        );
        echo static::renderTextareaRow(
            'crb_footer_copyright',
            __('Footer Copyright', 'a-ripple-song'),
            static::getThemeOption('crb_footer_copyright'),
            __('Overrides the footer copyright line. Leave empty to use the default.', 'a-ripple-song')
        );
        echo static::renderTextareaRow(
            'crb_header_scripts',
            __('Header Scripts', 'a-ripple-song'),
            static::getThemeOption('crb_header_scripts'),
            __('Scripts to be added in the <head> section. You can include complete <script> tags for services like Google Analytics.', 'a-ripple-song')
        );
        echo static::renderTextareaRow(
            'crb_footer_scripts',
            __('Footer Scripts', 'a-ripple-song'),
            static::getThemeOption('crb_footer_scripts'),
            __('Scripts to be added before </body>. You can include complete <script> tags.', 'a-ripple-song')
        );
        echo '</tbody>';
        echo '</table>';
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    /**
     * Render the social links settings page.
     *
     * @return void
     */
    public static function renderSocialPage(): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Social Links', 'a-ripple-song') . '</h1>';
        settings_errors();
        echo '<form method="post" action="options.php">';
        settings_fields(static::OPTION_GROUP);
        echo '<table class="form-table" role="presentation">';
        echo '<tbody>';
        echo static::renderSocialIntroRow();

        foreach (SocialLinks::getPlatforms() as $platformKey => $platformData) {
            echo static::renderUrlRow(
                SocialLinks::SETTING_PREFIX . $platformKey,
                $platformData['label'],
                SocialLinks::getConfiguredLinks()[$platformKey]['url'] ?? '',
                __('Optional. Enter a full URL.', 'a-ripple-song')
            );
        }

        echo '</tbody>';
        echo '</table>';
        submit_button();
        echo '</form>';
        echo '</div>';
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
     * Return the saved theme option value.
     *
     * @param string $key Theme option key.
     * @param string $default Fallback value.
     * @return string
     */
    public static function getThemeOption(string $key, string $default = ''): string
    {
        /** @var mixed $optionValue Raw option value from the WordPress options table. */
        $optionValue = get_option($key, $default);

        if (is_string($optionValue) && $optionValue !== '') {
            return $optionValue;
        }

        if ($optionValue === 0 || $optionValue === '0') {
            return '0';
        }

        /** @var mixed $legacyOptionValue Raw value from Carbon Fields prefixed option keys. */
        $legacyOptionValue = get_option('_' . $key, null);

        if (is_string($legacyOptionValue) && $legacyOptionValue !== '') {
            return $legacyOptionValue;
        }

        if ($legacyOptionValue === 0 || $legacyOptionValue === '0') {
            return '0';
        }

        return $default;
    }

    /**
     * Return the configured site logo URL.
     *
     * @return string
     */
    public static function getSiteLogoUrl(): string
    {
        return esc_url(static::getThemeOption('crb_site_logo'));
    }

    /**
     * Return the default light theme slug.
     *
     * @return string
     */
    public static function getLightTheme(): string
    {
        /** @var string $themeSlug Saved light theme slug. */
        $themeSlug = static::getThemeOption('crb_light_theme', 'retro');

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
        $themeSlug = static::getThemeOption('crb_dark_theme', 'dim');

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
        return trim(static::getThemeOption('crb_footer_copyright'));
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
        $headerScripts = static::getThemeOption('crb_header_scripts');

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
        $footerScripts = static::getThemeOption('crb_footer_scripts');

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
                            ? '<img src="' + url + '" alt="<?php echo esc_js(__('Site Logo', 'a-ripple-song')); ?>" style="display:block;width:' + logoWidth + 'px;height:' + logoHeight + 'px;margin-top:12px;border:1px solid #dcdcde;padding:8px;background:#fff;object-fit:contain;">'
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
                                text: <?php echo wp_json_encode(__('Select and Crop', 'a-ripple-song')); ?>,
                                close: false
                            },
                            states: [
                                new wp.media.controller.Library({
                                    title: <?php echo wp_json_encode(__('Select Site Logo', 'a-ripple-song')); ?>,
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
        $currentLogo = static::getThemeOption('crb_site_logo');

        return sprintf(
            '<tr class="%1$s"><th scope="row"><label for="crb_site_logo">%2$s</label></th><td><div class="ars-logo-uploader" data-ars-logo-uploader><input type="url" class="regular-text" id="crb_site_logo" name="crb_site_logo" value="%3$s" placeholder="https://example.com/logo.svg" data-ars-logo-input><p class="description">%4$s</p><p><button type="button" class="button button-primary" data-ars-logo-select>%5$s</button> <button type="button" class="button" data-ars-logo-remove>%6$s</button></p><div class="ars-logo-preview" data-ars-logo-preview>%7$s</div></div></td></tr>',
            esc_attr($rowClass),
            esc_html__('Site Logo', 'a-ripple-song'),
            esc_attr($currentLogo),
            esc_html__('Upload a logo image (220px × 32px). You will be able to crop the image after upload.', 'a-ripple-song'),
            esc_html__('Upload / Change Logo', 'a-ripple-song'),
            esc_html__('Remove Logo', 'a-ripple-song'),
            static::renderLogoPreview($currentLogo)
        );
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
        $markup[] = '<select class="ars-theme-select" id="' . esc_attr($optionKey) . '" name="' . esc_attr($optionKey) . '" data-theme-target="' . esc_attr($mode) . '">';

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
    protected static function renderSelectRow(string $optionKey, string $label, array $options, string $value, string $description): string
    {
        /** @var array<int, string> $markup Generated row fragments. */
        $markup = [];
        $markup[] = '<tr>';
        $markup[] = '<th scope="row"><label for="' . esc_attr($optionKey) . '">' . esc_html($label) . '</label></th>';
        $markup[] = '<td>';
        $markup[] = '<select id="' . esc_attr($optionKey) . '" name="' . esc_attr($optionKey) . '">';

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
    protected static function renderTextareaRow(string $optionKey, string $label, string $value, string $description): string
    {
        return sprintf(
            '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><textarea id="%1$s" name="%1$s" class="large-text code" rows="5">%3$s</textarea><p class="description">%4$s</p></td></tr>',
            esc_attr($optionKey),
            esc_html($label),
            esc_textarea($value),
            esc_html($description)
        );
    }

    /**
     * Render the social links intro row.
     *
     * @return string
     */
    protected static function renderSocialIntroRow(): string
    {
        return '<tr><td colspan="2"><p class="description">' . esc_html__('Only filled links will be used by the theme.', 'a-ripple-song') . '</p></td></tr>';
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
    protected static function renderUrlRow(string $optionKey, string $label, string $value, string $description): string
    {
        return sprintf(
            '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="url" id="%1$s" name="%1$s" class="regular-text" value="%3$s" placeholder="https://example.com"><p class="description">%4$s</p></td></tr>',
            esc_attr($optionKey),
            esc_html($label),
            esc_attr($value),
            esc_html($description)
        );
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
            esc_attr__('Site Logo', 'a-ripple-song'),
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
        return static::sanitizeThemeSlug((string) $value, static::getLightThemeOptions(), 'retro');
    }

    /**
     * Sanitize the dark theme slug.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public static function sanitizeDarkTheme($value): string
    {
        return static::sanitizeThemeSlug((string) $value, static::getDarkThemeOptions(), 'dim');
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
