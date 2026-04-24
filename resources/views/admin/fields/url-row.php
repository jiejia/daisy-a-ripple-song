<tr>
    <th scope="row">
        <label for="<?php echo esc_attr($optionKey); ?>"><?php echo esc_html($label); ?></label>
    </th>
    <td>
        <input type="url" id="<?php echo esc_attr($optionKey); ?>" name="<?php echo esc_attr($optionName); ?>[<?php echo esc_attr($optionKey); ?>]" class="regular-text" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr__( 'Enter a full URL', 'daisy-a-ripple-song' ); ?>">
        <p class="description"><?php echo esc_html($description); ?></p>
    </td>
</tr>
