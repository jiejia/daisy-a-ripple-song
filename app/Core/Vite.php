<?php

namespace App\Core;

/**
 * Vite
 *
 * Handle Vite asset integration for both development and production environments.
 */
class Vite
{

    /** @var string $devServerUrl Base URL of the Vite dev server. */
    private string $devServerUrl;

    /** @var string $scriptEntry Entry point file path relative to the project root. */
    private string $scriptEntry;

    /** @var string $styleEntry CSS entry file path relative to the project root. */
    private string $styleEntry;

    /** @var string $handlePrefix Prefix used for WordPress script/style handles. */
    private string $handlePrefix;

    /**
     * @param string $devServerUrl Vite dev server base URL.
     * @param string $scriptEntry JS entry point file.
     * @param string $styleEntry CSS entry point file.
     * @param string $handlePrefix Prefix for wp_enqueue handles.
     */
    public function __construct(
        string $devServerUrl = 'http://127.0.0.1:5173',
        string $scriptEntry = 'resources/js/main.js',
        string $styleEntry = 'resources/css/main.css',
        string $handlePrefix = 'a-ripple-song'
    ) {
        $this->devServerUrl = $devServerUrl;
        $this->scriptEntry = $scriptEntry;
        $this->styleEntry = $styleEntry;
        $this->handlePrefix = $handlePrefix;
    }

    /**
     * Check whether the Vite dev server is reachable by requesting the Vite client endpoint.
     *
     * @return bool True when the dev server responds successfully.
     */
    public function isDevServerRunning(): bool
    {
        /** @var array<string, mixed> $args HTTP client arguments for the health check request. */
        $args = [
            'timeout' => 0.3,
        ];

        /** @var array<string, mixed>|\WP_Error $response HTTP response from the Vite dev server. */
        $response = wp_remote_get($this->devServerUrl . '/@vite/client', $args);

        return !is_wp_error($response)
            && wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Return true when the Vite dev server is running.
     *
     * @return bool True when development mode is available.
     */
    public function isDev(): bool
    {
        return $this->isDevServerRunning();
    }

    /**
     * Enqueue dev or production assets depending on the current environment.
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        if ($this->isDev()) {
            $this->enqueueDevAssets();

            return;
        }

        $this->enqueueProdAssets();
    }

    /**
     * Enqueue only the theme stylesheet for widget editor previews in admin.
     *
     * @return void
     */
    public function enqueueWidgetEditorStyles(): void
    {
        if (!$this->shouldLoadWidgetEditorStyles()) {
            return;
        }

        if ($this->isDev()) {
            $this->enqueueDevStyle();

            return;
        }

        $this->enqueueProdStyle();
    }

    /**
     * Enqueue the Vite client and entry module directly from the dev server.
     *
     * @return void
     */
    private function enqueueDevAssets(): void
    {
        $this->enqueueDevStyle();

        $this->markScriptAsModule($this->handlePrefix . '-vite-client');
        $this->markScriptAsModule($this->handlePrefix . '-main');

        wp_enqueue_script(
            $this->handlePrefix . '-vite-client',
            $this->devServerUrl . '/@vite/client',
            [],
            null,
            false
        );

        wp_enqueue_script(
            $this->handlePrefix . '-main',
            $this->devServerUrl . '/' . $this->scriptEntry,
            [],
            null,
            false
        );
    }

    /**
     * Enqueue hashed CSS and JS assets from the Vite build manifest.
     *
     * @return void
     */
    private function enqueueProdAssets(): void
    {
        $this->enqueueProdStyle();

        /** @var array<string, mixed>|null $script Manifest entry for the main JS file. */
        $script = $this->getManifestEntry($this->scriptEntry);

        if (!$script) {
            return;
        }

        $this->markScriptAsModule($this->handlePrefix . '-main');

        wp_enqueue_script(
            $this->handlePrefix . '-main',
            get_template_directory_uri() . '/public/dist/' . $script['file'],
            [],
            null,
            true
        );
    }

    /**
     * Enqueue the stylesheet from the Vite dev server.
     *
     * @return void
     */
    private function enqueueDevStyle(): void
    {
        wp_enqueue_style(
            $this->handlePrefix . '-style',
            $this->devServerUrl . '/' . $this->styleEntry,
            [],
            null
        );
    }

    /**
     * Enqueue the stylesheet from the Vite build output.
     *
     * @return void
     */
    private function enqueueProdStyle(): void
    {
        /** @var array<string, mixed>|null $style Manifest entry for the main CSS file. */
        $style = $this->getManifestEntry($this->styleEntry);

        /** @var array<string, mixed>|null $script Manifest entry for the main JS file. */
        $script = $this->getManifestEntry($this->scriptEntry);

        /** @var string $themeUri Theme directory URL. */
        $themeUri = get_template_directory_uri();

        if ($style) {
            wp_enqueue_style(
                $this->handlePrefix . '-style',
                $themeUri . '/public/dist/' . $style['file'],
                [],
                null
            );

            return;
        }

        if (empty($script['css'])) {
            return;
        }

        foreach ($script['css'] as $index => $cssFile) {
            wp_enqueue_style(
                $this->handlePrefix . '-style-' . $index,
                $themeUri . '/public/dist/' . $cssFile,
                [],
                null
            );
        }
    }

    /**
     * Return the manifest entry for a given Vite source file.
     *
     * @param string $entry The Vite source entry path.
     * @return array<string, mixed>|null
     */
    private function getManifestEntry(string $entry): ?array
    {
        /** @var string $themeDir Absolute theme directory path. */
        $themeDir = get_template_directory();

        /** @var string $manifestPath Absolute build manifest path. */
        $manifestPath = $themeDir . '/public/dist/.vite/manifest.json';

        if (!file_exists($manifestPath)) {
            return null;
        }

        /** @var array<string, array<string, mixed>>|null $manifest Parsed Vite manifest content. */
        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        return $manifest[$entry] ?? null;
    }

    /**
     * Return whether the current admin request is rendering widget editor content.
     *
     * @return bool
     */
    private function shouldLoadWidgetEditorStyles(): bool
    {
        if (!is_admin()) {
            return false;
        }

        if (function_exists('get_current_screen')) {
            /** @var \WP_Screen|null $screen Current admin screen object. */
            $screen = get_current_screen();

            if ($screen && (
                in_array($screen->id, ['widgets', 'customize'], true)
                || in_array($screen->base, ['widgets', 'customize'], true)
            )) {
                return true;
            }
        }

        /** @var string $requestUri Raw request URI used as a fallback when screen data is not available. */
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';

        return str_contains($requestUri, 'widgets.php')
            || str_contains($requestUri, 'customize.php');
    }

    /**
     * Mark a specific enqueued script as a JavaScript module.
     *
     * @param string $handle Script handle to rewrite.
     * @return void
     */
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
