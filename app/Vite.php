<?php
namespace App;

/**
 * Handles Vite asset integration for both development and production environments.
 */
class Vite {

    /** Base URL of the Vite dev server. */
    private string $devServerUrl;

    /** Entry point file path relative to the project root. */
    private string $scriptEntry;

    /** CSS entry file path relative to the project root. */
    private string $styleEntry;

    /** Prefix used for WordPress script/style handles. */
    private string $handlePrefix;

    /**
     * @param string $devServerUrl  Vite dev server base URL.
     * @param string $scriptEntry   JS entry point file (e.g. resources/js/main.js).
     * @param string $styleEntry    CSS entry point file (e.g. resources/css/main.css).
     * @param string $handlePrefix  Prefix for wp_enqueue handles.
     */
    public function __construct(
        string $devServerUrl = 'http://127.0.0.1:5173',
        string $scriptEntry = 'resources/js/main.js',
        string $styleEntry = 'resources/css/main.css',
        string $handlePrefix = 'a-ripple-song'
    ) {
        $this->devServerUrl = $devServerUrl;
        $this->scriptEntry  = $scriptEntry;
        $this->styleEntry   = $styleEntry;
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
     * Returns true when the Vite dev server is running.
     */
    public function isDev(): bool
    {
        return $this->isDevServerRunning();
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
        wp_enqueue_style(
            $this->handlePrefix . '-style',
            $this->devServerUrl . '/' . $this->styleEntry,
            [],
            null
        );

        $this->markScriptAsModule($this->handlePrefix . '-vite-client');
        $this->markScriptAsModule($this->handlePrefix . '-main');

        // Enqueue the Vite HMR client script.
        wp_enqueue_script(
            $this->handlePrefix . '-vite-client',
            $this->devServerUrl . '/@vite/client',
            [],
            null,
            false
        );

        // Enqueue the main entry module from the dev server.
        wp_enqueue_script(
            $this->handlePrefix . '-main',
            $this->devServerUrl . '/' . $this->scriptEntry,
            [],
            null,
            false
        );
    }

    /**
     * Enqueues hashed CSS and JS assets from the Vite build manifest.
     */
    private function enqueueProdAssets(): void
    {
        $themeDir     = get_template_directory();
        $themeUri     = get_template_directory_uri();
        $manifestPath = $themeDir . '/public/dist/.vite/manifest.json';

        // Bail early if the manifest file does not exist.
        if (! file_exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $script   = $manifest[$this->scriptEntry] ?? null;
        $style    = $manifest[$this->styleEntry] ?? null;

        // Bail early if the script entry is not found in the manifest.
        if (! $script) {
            return;
        }

        if ($style) {
            wp_enqueue_style(
                $this->handlePrefix . '-style',
                $themeUri . '/public/dist/' . $style['file'],
                [],
                null
            );
        } elseif (! empty($script['css'])) {
            foreach ($script['css'] as $index => $cssFile) {
                wp_enqueue_style(
                    $this->handlePrefix . '-style-' . $index,
                    $themeUri . '/public/dist/' . $cssFile,
                    [],
                    null
                );
            }
        }

        $this->markScriptAsModule($this->handlePrefix . '-main');

        // Enqueue the compiled JS entry module.
        wp_enqueue_script(
            $this->handlePrefix . '-main',
            $themeUri . '/public/dist/' . $script['file'],
            [],
            null,
            true
        );
    }

    private function markScriptAsModule(string $handle): void
    {
        add_filter('script_loader_tag', static function (string $tag, string $currentHandle, string $src) use ($handle): string {
            if ($currentHandle !== $handle) {
                return $tag;
            }

            return sprintf(
                '<script type="module" src="%s" id="%s-js"></script>',
                esc_url($src),
                esc_attr($currentHandle)
            );
        }, 10, 3);
    }
}
