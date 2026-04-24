<select
    class="ars-theme-select"
    id="<?php echo esc_attr($optionKey); ?>"
    name="<?php echo esc_attr($optionName); ?>[<?php echo esc_attr($optionKey); ?>]"
    data-theme-target="<?php echo esc_attr($mode); ?>"
>
    <?php foreach ($options as $optionValue => $optionLabel): ?>
        <option value="<?php echo esc_attr($optionValue); ?>"<?php selected($value, $optionValue); ?>><?php echo esc_html($optionLabel); ?></option>
    <?php endforeach; ?>
</select>
