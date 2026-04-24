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
                <?php echo $fieldsMarkup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
