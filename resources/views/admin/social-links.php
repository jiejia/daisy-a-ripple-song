<div class="wrap">
    <h1><?php echo esc_html($title); ?></h1>

    <?php if (!empty($description)): ?>
        <p class="description"><?php echo esc_html($description); ?></p>
    <?php endif; ?>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php settings_fields($optionGroup); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <?php foreach ((array) $fields as $field): ?>
                    <?php if (($field['type'] ?? '') !== 'url'): ?>
                        <?php continue; ?>
                    <?php endif; ?>

                    <tr>
                        <th scope="row">
                            <label for="<?php echo esc_attr((string) ($field['key'] ?? '')); ?>"><?php echo esc_html((string) ($field['label'] ?? '')); ?></label>
                        </th>
                        <td>
                            <input
                                type="url"
                                id="<?php echo esc_attr((string) ($field['key'] ?? '')); ?>"
                                name="<?php echo esc_attr((string) ($field['optionName'] ?? '')); ?>[<?php echo esc_attr((string) ($field['key'] ?? '')); ?>]"
                                class="regular-text"
                                value="<?php echo esc_attr((string) ($field['value'] ?? '')); ?>"
                                placeholder="https://example.com"
                            >
                            <p class="description"><?php echo esc_html((string) ($field['description'] ?? '')); ?></p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
