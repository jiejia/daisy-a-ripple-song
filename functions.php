<?php

defined('ABSPATH') || exit;

/** @var string $autoload Composer autoload file path. */
$autoload = __DIR__ . '/vendor/autoload.php';

/** @var string $scoperAutoload PHP-Scoper autoload bridge path. */
$scoperAutoload = __DIR__ . '/vendor/scoper-autoload.php';

if (!file_exists($autoload) && !file_exists($scoperAutoload)) {
    add_action('admin_notices', static function (): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Theme dependencies are missing. Run "composer install" in wp-content/themes/daisy-a-ripple-song.', 'daisy-a-ripple-song');
        echo '</p></div>';
    });

    return;
}

if (!defined('DAISY_A_RIPPLE_SONG_THEME_DIR')) {
    define('DAISY_A_RIPPLE_SONG_THEME_DIR', plugin_dir_path(__FILE__));
}

if (file_exists($scoperAutoload)) {
    require_once $scoperAutoload;
} else {
    require_once $autoload;
}

new \Jiejia\DaisyARippleSong\Theme();
