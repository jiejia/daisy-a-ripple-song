<?php foreach ([
    'primary' => 'primaryContent',
    'secondary' => 'secondaryContent',
    'accent' => 'accentContent',
    'neutral' => 'neutralContent',
] as $swatchKey => $contentKey): ?>
    <span
        class="ars-theme-card__swatch"
        style="--ars-swatch:<?php echo esc_attr($colors[$swatchKey] ?? '#d1d5db'); ?>;--ars-swatch-content:<?php echo esc_attr($colors[$contentKey] ?? '#ffffff'); ?>;"
    >A</span>
<?php endforeach; ?>
