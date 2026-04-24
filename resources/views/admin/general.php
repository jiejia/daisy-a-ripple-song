<div class="wrap">
    <h1><?php echo esc_html($title); ?></h1>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php settings_fields($optionGroup); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <?php foreach ((array) $fields as $field): ?>
                    <?php if (($field['type'] ?? '') === 'logo'): ?>
                        <tr class="<?php echo esc_attr((string) ($field['rowClass'] ?? '')); ?>">
                            <th scope="row">
                                <label for="<?php echo esc_attr((string) ($field['key'] ?? 'site_logo')); ?>"><?php echo esc_html((string) ($field['label'] ?? '')); ?></label>
                            </th>
                            <td>
                                <div class="ars-logo-uploader" data-ars-logo-uploader>
                                    <input
                                        type="url"
                                        class="regular-text"
                                        id="<?php echo esc_attr((string) ($field['key'] ?? 'site_logo')); ?>"
                                        name="<?php echo esc_attr((string) ($field['optionName'] ?? '')); ?>[<?php echo esc_attr((string) ($field['key'] ?? 'site_logo')); ?>]"
                                        value="<?php echo esc_attr((string) ($field['value'] ?? '')); ?>"
                                        placeholder="https://example.com/logo.svg"
                                        data-ars-logo-input
                                    >

                                    <p class="description"><?php echo esc_html((string) ($field['description'] ?? '')); ?></p>

                                    <p>
                                        <button type="button" class="button button-primary" data-ars-logo-select><?php echo esc_html__('Upload / Change Logo', 'daisy-a-ripple-song'); ?></button>
                                        <button type="button" class="button" data-ars-logo-remove><?php echo esc_html__('Remove Logo', 'daisy-a-ripple-song'); ?></button>
                                    </p>

                                    <div class="ars-logo-preview" data-ars-logo-preview>
                                        <?php if ((string) ($field['value'] ?? '') !== ''): ?>
                                            <img class="ars-logo-preview__image" src="<?php echo esc_url((string) ($field['value'] ?? '')); ?>" alt="<?php echo esc_attr__('Site Logo', 'daisy-a-ripple-song'); ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php elseif (($field['type'] ?? '') === 'theme_picker'): ?>
                        <tr class="<?php echo esc_attr((string) ($field['rowClass'] ?? '')); ?>">
                            <th scope="row">
                                <label for="<?php echo esc_attr((string) ($field['key'] ?? '')); ?>"><?php echo esc_html((string) ($field['label'] ?? '')); ?></label>
                            </th>
                            <td>
                                <div class="ars-theme-picker" data-ars-theme-picker data-theme-target="<?php echo esc_attr((string) ($field['mode'] ?? ($field['key'] ?? ''))); ?>">
                                    <?php foreach ((is_array($field['options'] ?? null) ? $field['options'] : []) as $themeSlug => $themeLabel): ?>
                                        <button
                                            type="button"
                                            class="ars-theme-card<?php echo (string) ($field['value'] ?? '') === (string) $themeSlug ? ' is-active' : ''; ?>"
                                            data-theme-value="<?php echo esc_attr((string) $themeSlug); ?>"
                                            style="--ars-base-100:<?php echo esc_attr((string) ($themePalette[$themeSlug]['base100'] ?? '#f3f4f6')); ?>;--ars-base-200:<?php echo esc_attr((string) ($themePalette[$themeSlug]['base200'] ?? '#e5e7eb')); ?>;--ars-base-300:<?php echo esc_attr((string) ($themePalette[$themeSlug]['base300'] ?? '#d1d5db')); ?>;--ars-base-content:<?php echo esc_attr((string) ($themePalette[$themeSlug]['baseContent'] ?? '#111827')); ?>;"
                                        >
                                            <div class="ars-theme-card__preview">
                                                <div class="ars-theme-card__sidebar">
                                                    <div class="ars-theme-card__sidebar-top"></div>
                                                    <div class="ars-theme-card__sidebar-bottom"></div>
                                                </div>

                                                <div class="ars-theme-card__content">
                                                    <span class="ars-theme-card__name"><?php echo esc_html((string) $themeLabel); ?></span>
                                                    <span class="ars-theme-card__swatches" aria-hidden="true">
                                                        <?php foreach (['primary' => 'primaryContent', 'secondary' => 'secondaryContent', 'accent' => 'accentContent', 'neutral' => 'neutralContent'] as $swatchKey => $contentKey): ?>
                                                            <span
                                                                class="ars-theme-card__swatch"
                                                                style="--ars-swatch:<?php echo esc_attr((string) ($themePalette[$themeSlug][$swatchKey] ?? '#d1d5db')); ?>;--ars-swatch-content:<?php echo esc_attr((string) ($themePalette[$themeSlug][$contentKey] ?? '#ffffff')); ?>;"
                                                            >A</span>
                                                        <?php endforeach; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <select
                                    class="ars-theme-select"
                                    id="<?php echo esc_attr((string) ($field['key'] ?? '')); ?>"
                                    name="<?php echo esc_attr((string) ($field['optionName'] ?? '')); ?>[<?php echo esc_attr((string) ($field['key'] ?? '')); ?>]"
                                    data-theme-target="<?php echo esc_attr((string) ($field['mode'] ?? ($field['key'] ?? ''))); ?>"
                                >
                                    <?php foreach ((is_array($field['options'] ?? null) ? $field['options'] : []) as $optionValue => $optionLabel): ?>
                                        <option value="<?php echo esc_attr((string) $optionValue); ?>"<?php selected((string) ($field['value'] ?? ''), (string) $optionValue); ?>><?php echo esc_html((string) $optionLabel); ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <p class="description"><?php echo esc_html((string) ($field['description'] ?? '')); ?></p>
                            </td>
                        </tr>
                    <?php elseif (($field['type'] ?? '') === 'select'): ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr((string) ($field['key'] ?? '')); ?>"><?php echo esc_html((string) ($field['label'] ?? '')); ?></label>
                            </th>
                            <td>
                                <select id="<?php echo esc_attr((string) ($field['key'] ?? '')); ?>" name="<?php echo esc_attr((string) ($field['optionName'] ?? '')); ?>[<?php echo esc_attr((string) ($field['key'] ?? '')); ?>]">
                                    <?php foreach ((is_array($field['options'] ?? null) ? $field['options'] : []) as $optionValue => $optionLabel): ?>
                                        <option value="<?php echo esc_attr((string) $optionValue); ?>"<?php selected((string) ($field['value'] ?? ''), (string) $optionValue); ?>><?php echo esc_html((string) $optionLabel); ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <p class="description"><?php echo esc_html((string) ($field['description'] ?? '')); ?></p>
                            </td>
                        </tr>
                    <?php elseif (($field['type'] ?? '') === 'textarea'): ?>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo esc_attr((string) ($field['key'] ?? '')); ?>"><?php echo esc_html((string) ($field['label'] ?? '')); ?></label>
                            </th>
                            <td>
                                <textarea id="<?php echo esc_attr((string) ($field['key'] ?? '')); ?>" name="<?php echo esc_attr((string) ($field['optionName'] ?? '')); ?>[<?php echo esc_attr((string) ($field['key'] ?? '')); ?>]" class="large-text code" rows="5"><?php echo esc_textarea((string) ($field['value'] ?? '')); ?></textarea>
                                <p class="description"><?php echo esc_html((string) ($field['description'] ?? '')); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
