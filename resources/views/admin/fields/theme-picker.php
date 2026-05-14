<div class="ars-theme-picker-field">
    <h2 class="ars-theme-picker-field__title"><?php echo esc_html((string) ($title ?? '')); ?></h2>
    <div class="ars-theme-picker" data-ars-theme-picker data-theme-target="<?php echo esc_attr($mode); ?>">
        <?php foreach ($options as $themeSlug => $themeLabel): ?>
            <button
                type="button"
                class="ars-theme-card<?php echo $value === $themeSlug ? ' is-active' : ''; ?>"
                data-theme-value="<?php echo esc_attr($themeSlug); ?>"
                style="--ars-base-100:<?php echo esc_attr($themePalette[$themeSlug]['base100'] ?? '#f3f4f6'); ?>;--ars-base-200:<?php echo esc_attr($themePalette[$themeSlug]['base200'] ?? '#e5e7eb'); ?>;--ars-base-300:<?php echo esc_attr($themePalette[$themeSlug]['base300'] ?? '#d1d5db'); ?>;--ars-base-content:<?php echo esc_attr($themePalette[$themeSlug]['baseContent'] ?? '#111827'); ?>;"
            >
                <div class="ars-theme-card__preview">
                    <div class="ars-theme-card__sidebar">
                        <div class="ars-theme-card__sidebar-top"></div>
                        <div class="ars-theme-card__sidebar-bottom"></div>
                    </div>

                    <div class="ars-theme-card__content">
                        <span class="ars-theme-card__name"><?php echo esc_html($themeLabel); ?></span>
                        <span class="ars-theme-card__swatches" aria-hidden="true">
                            <?php echo $swatches[$themeSlug] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </span>
                    </div>
                </div>
            </button>
        <?php endforeach; ?>
    </div>
</div>
