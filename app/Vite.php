<?php
namespace App;

/**
 * Handles Vite asset integration for both development and production environments.
 */
class Vite {

    /** Base URL of the Vite dev server. */
    private string $devServerUrl;

    /** Entry point file path relative to the project root. */
    private string $entry;

    /** Prefix used for WordPress script/style handles. */
    private string $handlePrefix;

    /**
     * @param string $devServerUrl  Vite dev server base URL.
     * @param string $entry         Entry point file (e.g. src/main.js).
     * @param string $handlePrefix  Prefix for wp_enqueue handles.
     */
    public function __construct(
        string $devServerUrl = 'http://127.0.0.1:5173',
        string $entry = 'src/main.js',
        string $handlePrefix = 'a-ripple-song'
    ) {
        $this->devServerUrl = $devServerUrl;
        $this->entry        = $entry;
        $this->handlePrefix = $handlePrefix;
    }

    /**
     * Checks whether the Vite dev server is reachable by requesting the Vite client endpoint.
     */
    public function isDevServerRunning(): bool
    {
        $response = wp_remote_get($this->devServerUrl . '/@vite/client', [
            'timeout' => 0.3,
        ]);

        return ! is_wp_error($response)
            && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Returns true when WP_DEBUG is enabled and the Vite dev server is running.
     */
    public function isDev(): bool
    {
        return defined('WP_DEBUG') && WP_DEBUG && $this->isDevServerRunning();
    }

    /**
     * Enqueues dev or production assets depending on the current environment.
     */
    public function enqueueAssets(): void
    {
        if ($this->isDev()) {
            $this->enqueueDevAssets();
        } else {
            $this->enqueueProdAssets();
        }
    }

    /**
     * Enqueues the Vite client and entry module directly from the dev server.
     */
    private function enqueueDevAssets(): void
    {
        // Enqueue the Vite HMR client script.
        wp_enqueue_script(
            $this->handlePrefix . '-vite-client',
            $this->devServerUrl . '/@vite/client',
            [],
            null,
            true
        );
        wp_script_add_data($this->handlePrefix . '-vite-client', 'type', 'module');

        // Enqueue the main entry module from the dev server.
        wp_enqueue_script(
            $this->handlePrefix . '-main',
            $this->devServerUrl . '/' . $this->entry,
            [],
            null,
            true
        );
        wp_script_add_data($this->handlePrefix . '-main', 'type', 'module');
    }

    /**
     * Enqueues hashed CSS and JS assets from the Vite build manifest.
     */
    private function enqueueProdAssets(): void
    {
        $themeDir     = get_template_directory();
        $themeUri     = get_template_directory_uri();
        $manifestPath = $themeDir . '/dist/.vite/manifest.json';

        // Bail early if the manifest file does not exist.
        if (! file_exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $entry    = $manifest[$this->entry] ?? null;

        // Bail early if the entry is not found in the manifest.
        if (! $entry) {
            return;
        }

        // Enqueue all CSS files referenced by the entry.
        if (! empty($entry['css'])) {
            foreach ($entry['css'] as $index => $cssFile) {
                wp_enqueue_style(
                    $this->handlePrefix . '-style-' . $index,
                    $themeUri . '/dist/' . $cssFile,
                    [],
                    null
                );
            }
        }

        // Enqueue the compiled JS entry module.
        wp_enqueue_script(
            $this->handlePrefix . '-main',
            $themeUri . '/dist/' . $entry['file'],
            [],
            null,
            true
        );
        wp_script_add_data($this->handlePrefix . '-main', 'type', 'module');
    }
}
