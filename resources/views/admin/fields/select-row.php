<tr>
    <th scope="row">
        <label for="<?php echo esc_attr($optionKey); ?>"><?php echo esc_html($label); ?></label>
    </th>
    <td>
        <select id="<?php echo esc_attr($optionKey); ?>" name="<?php echo esc_attr($optionName); ?>[<?php echo esc_attr($optionKey); ?>]">
            <?php foreach ($options as $optionValue => $optionLabel): ?>
                <option value="<?php echo esc_attr($optionValue); ?>"<?php selected($value, $optionValue); ?>><?php echo esc_html($optionLabel); ?></option>
            <?php endforeach; ?>
        </select>

        <p class="description"><?php echo esc_html($description); ?></p>
    </td>
</tr>
