<tr>
    <th scope="row">
        <label for="<?php echo esc_attr($optionKey); ?>"><?php echo esc_html($label); ?></label>
    </th>
    <td>
        <textarea id="<?php echo esc_attr($optionKey); ?>" name="<?php echo esc_attr($optionName); ?>[<?php echo esc_attr($optionKey); ?>]" class="large-text code" rows="5"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php echo esc_html($description); ?></p>
    </td>
</tr>
