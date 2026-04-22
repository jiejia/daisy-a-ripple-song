<?php

$autoload = __DIR__ . '/vendor/autoload.php';
$scoperAutoload = __DIR__ . '/vendor/scoper-autoload.php';

if (! file_exists($autoload) && ! file_exists($scoperAutoload)) {
    add_action('admin_notices', static function (): void {
        if (! current_user_can('manage_options')) {
            return;
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Theme dependencies are missing. Run "composer install" in wp-content/themes/daisy-a-ripple-song.', 'daisy-a-ripple-song');
        echo '</p></div>';
    });

    return;
}

if (file_exists($scoperAutoload)) {
    require_once $scoperAutoload;
} else {
    require_once $autoload;
}

require_once __DIR__ . '/src/Core/Helper.php';
require_once __DIR__ . '/src/Core/Vite.php';
require_once __DIR__ . '/src/Core/Widget.php';
require_once __DIR__ . '/src/Core/Setup.php';

ARippleSong\Themes\Daisy\ThemeOptions\General::boot();

/** @var ARippleSong\Themes\Daisy\Core\Vite $vite Theme asset loader shared across frontend and editor preview hooks. */
$vite = new ARippleSong\Themes\Daisy\Core\Vite();

add_action('wp_enqueue_scripts', [$vite, 'enqueueAssets']);
add_action('admin_enqueue_scripts', [$vite, 'enqueueWidgetEditorAssets']);
add_action('enqueue_block_assets', [$vite, 'enqueueWidgetEditorAssets']);
