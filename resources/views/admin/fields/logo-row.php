<tr class="<?php echo esc_attr($rowClass); ?>">
    <th scope="row">
        <label for="site_logo"><?php echo esc_html__('Site Logo', 'daisy-a-ripple-song'); ?></label>
    </th>
    <td>
        <div class="ars-logo-uploader" data-ars-logo-uploader>
            <input
                type="url"
                class="regular-text"
                id="site_logo"
                name="<?php echo esc_attr($optionName); ?>[site_logo]"
                value="<?php echo esc_attr($currentLogo); ?>"
                placeholder="<?php echo esc_attr__( 'Enter the logo image URL', 'daisy-a-ripple-song' ); ?>"
                data-ars-logo-input
            >

            <p class="description"><?php echo esc_html__('Upload a logo image (220px × 32px). You will be able to crop the image after upload.', 'daisy-a-ripple-song'); ?></p>

            <p>
                <button type="button" class="button button-primary" data-ars-logo-select><?php echo esc_html__('Upload / Change Logo', 'daisy-a-ripple-song'); ?></button>
                <button type="button" class="button" data-ars-logo-remove><?php echo esc_html__('Remove Logo', 'daisy-a-ripple-song'); ?></button>
            </p>

            <div class="ars-logo-preview" data-ars-logo-preview>
                <?php echo $previewHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </td>
</tr>
