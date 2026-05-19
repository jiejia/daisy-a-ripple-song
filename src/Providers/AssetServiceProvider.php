<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Theme;

class AssetServiceProvider extends AbstractServiceProvider
{
    protected const DEV_SERVER_URL = 'http://127.0.0.1:5173';

    protected const DIST_DIR = 'public/dist';

    protected const MAIN_JS_FILE = 'resources/js/main.js';

    protected const MAIN_CSS_FILE = 'resources/css/main.css';

    protected const ADMIN_JS_FILE = 'resources/js/admin.js';

    protected const ADMIN_CSS_FILE = 'resources/css/admin.css';

    protected const VITE_CLIENT_FILE = '@vite/client';

    protected const MAIN_HANDLE = Theme::SLUG . '-main';

    protected const DEV_MODE = true;

    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueMainAssets']);
    }

    /**
     * Read the Vite manifest file.
     *
     * @return array<string,array<string,mixed>>
     */
    private function getManifest(): array
    {
        $manifestPath = get_theme_file_path(self::DIST_DIR . '/.vite/manifest.json');

        if (!file_exists($manifestPath)) {
            return [];
        }

        $manifest = wp_json_file_decode($manifestPath, ['associative' => true]);

        return is_array($manifest) ? $manifest : [];
    }

    /**
     * Check if the dev server is running.
     *
     * @return bool
     */
    private function isDevServerRunning(): bool
    {
        $response = wp_remote_get(self::DEV_SERVER_URL . '/' . self::VITE_CLIENT_FILE, [
            'timeout' => 0.5,
        ]);

        return !is_wp_error($response)
            && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Enqueue the main assets.
     *
     * @return \WP_Error|void
     */
    public function enqueueMainAssets()
    {
        // If in dev mode and the dev server is running, enqueue the assets from the dev server.
        if (self::DEV_MODE && $this->isDevServerRunning()) {
            wp_enqueue_style(
                self::MAIN_HANDLE,
                self::DEV_SERVER_URL . '/' . self::MAIN_CSS_FILE,
                [],
                null
            );

            wp_enqueue_script(
                self::MAIN_HANDLE . '-vite',
                self::DEV_SERVER_URL . '/' . self::VITE_CLIENT_FILE,
                [],
                null,
                true
            );

            wp_enqueue_script(
                self::MAIN_HANDLE,
                self::DEV_SERVER_URL . '/' . self::MAIN_JS_FILE,
                [],
                null,
                true
            );

            add_filter('script_loader_tag', [$this, 'filterScriptLoaderTag'], 10, 3);

            return;
        }

        // If not in dev mode or the dev server is not running, enqueue the assets from the dist directory.
        $manifest = $this->getManifest();

        if (empty($manifest)) {
            wp_die(
                esc_html__('Vite manifest file does not exist.', 'daisy-a-ripple-song')
            );
        }

        $cssFile = $manifest[self::MAIN_CSS_FILE]['file'];
        $jsFile = $manifest[self::MAIN_JS_FILE]['file'];

        if (empty($cssFile) || empty($jsFile)) {
            wp_die(
                esc_html__('CSS or JS file not found in Vite manifest.', 'daisy-a-ripple-song')
            );
        }

        // Enqueue the CSS and JS files.
        wp_enqueue_style(
            self::MAIN_HANDLE,
            get_theme_file_uri(self::DIST_DIR . '/' . $cssFile),
            [],
            $this->getAssetVersion($cssFile)
        );

        wp_enqueue_script(
            self::MAIN_HANDLE,
            get_theme_file_uri(self::DIST_DIR . '/' . $jsFile),
            [],
            $this->getAssetVersion($jsFile),
            true
        );

        add_filter('script_loader_tag', [$this, 'filterScriptLoaderTag'], 10, 3);
    }

    /**
     * Filter the script loader tag.
     *
     * @param string $tag The script loader tag.
     * @param string $handle The script handle.
     * @param string $src The script src.
     * @return string
     */
    public function filterScriptLoaderTag(string $tag, string $handle, string $src): string
    {
        if (! in_array($handle, [self::MAIN_HANDLE . '-vite', self::MAIN_HANDLE], true)) {
            return $tag;
        }
  
        return sprintf(
            '<script type="module" crossorigin src="%s" id="%s-js"></script>' . "\n",
            esc_url($src),
            esc_attr($handle)
        );
    }

    /**
     * Return the asset version for cache busting.
     *
     * @param string $assetFile Asset path relative to self::DIST_DIR.
     * @return string
     */
    private function getAssetVersion(string $assetFile): string
    {
        $assetPath = get_theme_file_path(self::DIST_DIR . '/' . $assetFile);

        if (!file_exists($assetPath)) {
            return Theme::VERSION;
        }

        return (string) filemtime($assetPath);
    }
}
