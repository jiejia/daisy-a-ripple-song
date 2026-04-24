<tr class="<?php echo esc_attr($rowClass); ?>">
    <th scope="row">
        <label for="<?php echo esc_attr($optionKey); ?>"><?php echo esc_html($label); ?></label>
    </th>
    <td>
        <?php echo $pickerHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo $selectHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <p class="description"><?php echo esc_html($description); ?></p>
    </td>
</tr>
