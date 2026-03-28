<?php

$autoload = __DIR__ . '/vendor/autoload.php';

if (! file_exists($autoload)) {
    add_action('admin_notices', static function (): void {
        if (! current_user_can('manage_options')) {
            return;
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__('Theme dependencies are missing. Run "composer install" in wp-content/themes/a-ripple-song.', 'a-ripple-song');
        echo '</p></div>';
    });

    return;
}

require_once $autoload;

require_once __DIR__ . '/app/Core/Helper.php';
require_once __DIR__ . '/app/Core/Vite.php';
require_once __DIR__ . '/app/Core/Widget.php';
require_once __DIR__ . '/app/Core/Setup.php';

/** @var App\Core\Vite $vite Theme asset loader shared across frontend and editor preview hooks. */
$vite = new App\Core\Vite();

add_action('wp_enqueue_scripts', [$vite, 'enqueueAssets']);
add_action('admin_enqueue_scripts', [$vite, 'enqueueWidgetEditorAssets']);
add_action('enqueue_block_assets', [$vite, 'enqueueWidgetEditorAssets']);
