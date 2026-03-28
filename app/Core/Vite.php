<?php

namespace App\Core;

/**
 * Vite
 *
 * Handle Vite asset integration for both development and production environments.
 */
class Vite
{
    /** @var bool $widgetEditorAssetsLoaded Track whether widget editor assets were already enqueued. */
    private bool $widgetEditorAssetsLoaded = false;


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
    public function enqueueWidgetEditorAssets(): void
    {
        if (!$this->shouldLoadWidgetEditorStyles()) {
            return;
        }

        if ($this->widgetEditorAssetsLoaded) {
            return;
        }

        $this->widgetEditorAssetsLoaded = true;

        if ($this->isDev()) {
            $this->enqueueDevStyle();
        } else {
            $this->enqueueProdStyle();
        }

        $this->enqueueWidgetEditorThemeScript();
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
     * Enqueue a small runtime that applies the active DaisyUI theme inside widget previews.
     *
     * @return void
     */
    private function enqueueWidgetEditorThemeScript(): void
    {
        /** @var string $handle Existing core script handle that can carry the widget editor inline runtime. */
        $handle = wp_script_is('wp-blocks', 'registered')
            ? 'wp-blocks'
            : (wp_script_is('editor', 'registered')
                ? 'editor'
                : (wp_script_is('jquery', 'registered') ? 'jquery' : ''));

        if ($handle === '') {
            return;
        }

        wp_add_inline_script($handle, $this->getWidgetEditorThemeScript());
    }

    /**
     * Build the widget editor theme bridge script.
     *
     * @return string
     */
    private function getWidgetEditorThemeScript(): string
    {
        return <<<JS
(() => {
    'use strict';

    const storageKey = 'theme-mode';
    const lightTheme = 'retro';
    const darkTheme = 'dim';
    const supportedModes = ['light', 'dark', 'auto'];
    const root = document.documentElement;
    const colorSchemeMedia = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    /**
     * Read the persisted theme mode from local storage when available.
     *
     * @return {string}
     */
    function getStoredMode() {
        try {
            const storedMode = window.localStorage.getItem(storageKey);

            return supportedModes.includes(storedMode) ? storedMode : 'auto';
        } catch (error) {
            return 'auto';
        }
    }

    /**
     * Resolve the DaisyUI theme name that should be applied to the preview document.
     *
     * @return {string}
     */
    function getResolvedTheme() {
        const currentMode = getStoredMode();

        if (currentMode === 'light') {
            return lightTheme;
        }

        if (currentMode === 'dark') {
            return darkTheme;
        }

        return colorSchemeMedia && colorSchemeMedia.matches ? darkTheme : lightTheme;
    }

    /**
     * Apply the resolved theme classes and attributes to a specific document.
     *
     * @param {Document} targetDocument The document that should receive the active theme.
     * @return {void}
     */
    function applyThemeToDocument(targetDocument) {
        if (!targetDocument || !targetDocument.documentElement) {
            return;
        }

        const resolvedTheme = getResolvedTheme();
        const targetRoot = targetDocument.documentElement;
        const targetBody = targetDocument.body;

        targetRoot.setAttribute('data-theme', resolvedTheme);
        targetRoot.classList.add('bg-base-200');

        if (targetBody) {
            targetBody.classList.add('bg-base-200');
        }
    }

    /**
     * Apply the active theme to a legacy widget preview iframe when it is accessible.
     *
     * @param {HTMLIFrameElement} frameElement The preview iframe element.
     * @return {void}
     */
    function applyThemeToFrame(frameElement) {
        if (!frameElement) {
            return;
        }

        try {
            if (frameElement.contentDocument) {
                applyThemeToDocument(frameElement.contentDocument);
            }
        } catch (error) {
            // Ignore inaccessible preview frames and continue processing others.
        }

        if (!frameElement.dataset.arsThemeBound) {
            frameElement.dataset.arsThemeBound = '1';
            frameElement.addEventListener('load', () => {
                applyThemeToFrame(frameElement);
            });
        }
    }

    /**
     * Apply the active theme to the parent editor document and all preview iframes.
     *
     * @return {void}
     */
    function applyThemeEverywhere() {
        applyThemeToDocument(document);

        document.querySelectorAll('iframe[title="Legacy Widget Preview"]').forEach((frameElement) => {
            applyThemeToFrame(frameElement);
        });
    }

    /**
     * Observe the editor canvas so newly created preview iframes also receive the active theme.
     *
     * @return {void}
     */
    function observePreviewFrames() {
        if (!document.body || typeof MutationObserver === 'undefined') {
            return;
        }

        const observer = new MutationObserver(() => {
            applyThemeEverywhere();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            applyThemeEverywhere();
            observePreviewFrames();
        }, { once: true });
    } else {
        applyThemeEverywhere();
        observePreviewFrames();
    }

    if (colorSchemeMedia && typeof colorSchemeMedia.addEventListener === 'function') {
        colorSchemeMedia.addEventListener('change', applyThemeEverywhere);
    }

    window.addEventListener('storage', (event) => {
        if (event.key === storageKey) {
            applyThemeEverywhere();
        }
    });
})();
JS;
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
