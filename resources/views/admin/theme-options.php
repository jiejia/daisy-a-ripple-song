<?php
/**
 * Native theme options admin page.
 */
?>
<div class="wrap ars-theme-options-page">
    <h1><?php echo esc_html__('Theme Options', 'daisy-a-ripple-song'); ?></h1>

    <?php settings_errors(); ?>

    <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
        <?php settings_fields((string) $settingsGroup); ?>

        <h2><?php echo esc_html__('General', 'daisy-a-ripple-song'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php echo esc_html__('Light Theme', 'daisy-a-ripple-song'); ?></th>
                <td>
                    <select
                        class="ars-theme-select"
                        data-theme-target="light"
                        name="<?php echo esc_attr((string) $optionName); ?>[<?php echo esc_attr(\Jiejia\DaisyARippleSong\Settings\General::OPTION_SECTION); ?>][light_theme]"
                    >
                        <?php foreach (\Jiejia\DaisyARippleSong\Settings\General::getLightThemeOptions() as $themeSlug => $themeLabel): ?>
                            <option value="<?php echo esc_attr($themeSlug); ?>" <?php selected((string) ($options[\Jiejia\DaisyARippleSong\Settings\General::OPTION_SECTION]['light_theme'] ?? 'retro'), $themeSlug); ?>>
                                <?php echo esc_html($themeLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php echo $generalSetting->renderThemePickerHtml('light', '', \Jiejia\DaisyARippleSong\Settings\General::getLightThemeOptions(), (string) ($options[\Jiejia\DaisyARippleSong\Settings\General::OPTION_SECTION]['light_theme'] ?? 'retro')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <p class="description"><?php echo esc_html__('This is the default theme used when the site is in light mode.', 'daisy-a-ripple-song'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php echo esc_html__('Dark Theme', 'daisy-a-ripple-song'); ?></th>
                <td>
                    <select
                        class="ars-theme-select"
                        data-theme-target="dark"
                        name="<?php echo esc_attr((string) $optionName); ?>[<?php echo esc_attr(\Jiejia\DaisyARippleSong\Settings\General::OPTION_SECTION); ?>][dark_theme]"
                    >
                        <?php foreach (\Jiejia\DaisyARippleSong\Settings\General::getDarkThemeOptions() as $themeSlug => $themeLabel): ?>
                            <option value="<?php echo esc_attr($themeSlug); ?>" <?php selected((string) ($options[\Jiejia\DaisyARippleSong\Settings\General::OPTION_SECTION]['dark_theme'] ?? 'dim'), $themeSlug); ?>>
                                <?php echo esc_html($themeLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php echo $generalSetting->renderThemePickerHtml('dark', '', \Jiejia\DaisyARippleSong\Settings\General::getDarkThemeOptions(), (string) ($options[\Jiejia\DaisyARippleSong\Settings\General::OPTION_SECTION]['dark_theme'] ?? 'dim')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <p class="description"><?php echo esc_html__('This is the default theme used when the site is in dark mode.', 'daisy-a-ripple-song'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="<?php echo esc_attr((string) $optionName); ?>-footer-copyright"><?php echo esc_html__('Footer Copyright', 'daisy-a-ripple-song'); ?></label>
                </th>
                <td>
                    <textarea
                        id="<?php echo esc_attr((string) $optionName); ?>-footer-copyright"
                        class="large-text"
                        rows="4"
                        name="<?php echo esc_attr((string) $optionName); ?>[<?php echo esc_attr(\Jiejia\DaisyARippleSong\Settings\General::OPTION_SECTION); ?>][footer_copyright]"
                        placeholder="<?php echo esc_attr__('Overrides the footer copyright line. Leave empty to use the default.', 'daisy-a-ripple-song'); ?>"
                    ><?php echo esc_textarea((string) ($options[\Jiejia\DaisyARippleSong\Settings\General::OPTION_SECTION]['footer_copyright'] ?? '')); ?></textarea>
                    <p class="description"><?php echo esc_html__('Overrides the footer copyright line. Leave empty to use the default.', 'daisy-a-ripple-song'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php echo esc_html__('Social Links', 'daisy-a-ripple-song'); ?></h2>
        <table class="form-table" role="presentation">
            <?php foreach (\Jiejia\DaisyARippleSong\Settings\SocialLinks::getPlatforms() as $platformKey => $platformData): ?>
                <tr>
                    <th scope="row">
                        <label for="<?php echo esc_attr((string) $optionName . '-social-' . $platformKey); ?>"><?php echo esc_html($platformData['label']); ?></label>
                    </th>
                    <td>
                        <input
                            id="<?php echo esc_attr((string) $optionName . '-social-' . $platformKey); ?>"
                            class="regular-text"
                            type="url"
                            name="<?php echo esc_attr((string) $optionName); ?>[<?php echo esc_attr(\Jiejia\DaisyARippleSong\Settings\SocialLinks::OPTION_SECTION); ?>][<?php echo esc_attr($platformKey); ?>]"
                            value="<?php echo esc_url((string) ($options[\Jiejia\DaisyARippleSong\Settings\SocialLinks::OPTION_SECTION][$platformKey] ?? '')); ?>"
                            placeholder="<?php echo esc_attr__('Enter a full URL', 'daisy-a-ripple-song'); ?>"
                        >
                        <p class="description"><?php echo esc_html__('Optional. Enter a full URL.', 'daisy-a-ripple-song'); ?></p>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
