<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Settings\General;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Registers and loads theme frontend and editor assets.
 */
class AssetServiceProvider extends AbstractServiceProvider
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

    /** @var string $widgetPreviewScriptEntry Entry point file path for legacy widget preview runtime. */
    private string $widgetPreviewScriptEntry = 'resources/js/widget-preview.js';

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
        string $handlePrefix = Theme::PREFIX
    ) {
        $this->devServerUrl = $devServerUrl;
        $this->scriptEntry = $scriptEntry;
        $this->styleEntry = $styleEntry;
        $this->handlePrefix = $handlePrefix;
    }

    /**
     * Register asset loading hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Load the main theme bundle on public requests.
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);

        // Load theme styles in widget editors and block previews.
        add_action('admin_enqueue_scripts', [$this, 'enqueueWidgetEditorAssets']);
        add_action('enqueue_block_assets', [$this, 'enqueueWidgetEditorAssets']);
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
        if ($this->isLegacyWidgetPreviewRequest()) {
            $this->enqueuePreviewAssets();

            return;
        }

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
        $this->enqueueNativeWidgetFormScript();
    }

    /**
     * Enqueue the minimal asset set used by legacy widget preview iframes.
     *
     * @return void
     */
    private function enqueuePreviewAssets(): void
    {
        if ($this->isDev()) {
            $this->enqueueDevStyle();
            $this->enqueueDevPreviewScript();

            return;
        }

        $this->enqueueProdStyle();
        $this->enqueueProdPreviewScript();
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

        $this->injectFrontendData($this->handlePrefix . '-main');
    }

    /**
     * Enqueue the lightweight widget preview runtime directly from the dev server.
     *
     * @return void
     */
    private function enqueueDevPreviewScript(): void
    {
        $this->markScriptAsModule($this->handlePrefix . '-widget-preview-vite-client');
        $this->markScriptAsModule($this->handlePrefix . '-widget-preview');

        wp_enqueue_script(
            $this->handlePrefix . '-widget-preview-vite-client',
            $this->devServerUrl . '/@vite/client',
            [],
            null,
            false
        );

        wp_enqueue_script(
            $this->handlePrefix . '-widget-preview',
            $this->devServerUrl . '/' . $this->widgetPreviewScriptEntry,
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

        $this->injectFrontendData($this->handlePrefix . '-main');
    }

    /**
     * Attach frontend runtime data before the main theme script executes.
     *
     * @param string $handle The registered WordPress script handle.
     * @return void
     */
    private function injectFrontendData(string $handle): void
    {
        /** @var string $script Inline bootstrap script for the theme runtime. */
        $script = 'window.aripplesongData = Object.assign({}, window.aripplesongData || {}, ' . wp_json_encode($this->getFrontendData()) . ');';

        wp_add_inline_script($handle, $script, 'before');
    }

    /**
     * Build the frontend runtime payload used by REST API-driven theme features.
     *
     * @return array<string, array<string, int|string>>
     */
    private function getFrontendData(): array
    {
        /** @var int $postId Current singular post ID when available. */
        $postId = is_singular() ? (int) get_queried_object_id() : 0;

        /** @var string $postType Current singular post type when available. */
        $postType = $postId ? (string) get_post_type($postId) : '';

        return [
            'ajax' => [
                'restUrl'         => (defined('IS_PODCAST_PLUGIN_ACTIVATED') && IS_PODCAST_PLUGIN_ACTIVATED) ? rest_url(\Jiejia\ARippleSong\Plugin::PREFIX . '/v1/') : '',
                'nonce'           => wp_create_nonce('wp_rest'),
                'postId'          => $postId,
                'postType'        => $postType,
                'podcastPostType' => (defined('IS_PODCAST_PLUGIN_ACTIVATED') && IS_PODCAST_PLUGIN_ACTIVATED) ? \Jiejia\ARippleSong\CPTs\Episode::slug() : '',
            ],
            'theme' => General::getThemeModeConfig(),
        ];
    }

    /**
     * Enqueue the lightweight widget preview runtime from the build manifest.
     *
     * @return void
     */
    private function enqueueProdPreviewScript(): void
    {
        /** @var array<string, mixed>|null $script Manifest entry for the preview JS file. */
        $script = $this->getManifestEntry($this->widgetPreviewScriptEntry);

        if (!$script) {
            return;
        }

        $this->markScriptAsModule($this->handlePrefix . '-widget-preview');

        wp_enqueue_script(
            $this->handlePrefix . '-widget-preview',
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
     * Return whether the current request is rendering a legacy widget preview iframe.
     *
     * @return bool
     */
    private function isLegacyWidgetPreviewRequest(): bool
    {
        /** @var string $requestUri Raw request URI used to identify preview REST routes. */
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';

        /** @var string $restRoute Explicit REST route value when the request uses the query-string format. */
        $restRoute = isset($_GET['rest_route']) ? (string) wp_unslash($_GET['rest_route']) : '';

        /** @var bool $isLegacyWidgetRenderRoute Whether the current REST request targets the legacy widget preview renderer. */
        $isLegacyWidgetRenderRoute = (defined('REST_REQUEST') && REST_REQUEST)
            && (
                (str_contains($requestUri, '/wp/v2/widget-types/') && str_contains($requestUri, '/render'))
                || (str_contains($restRoute, '/wp/v2/widget-types/') && str_contains($restRoute, '/render'))
            );

        return $isLegacyWidgetRenderRoute || !empty($_GET['legacy-widget-preview']);
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
     * Enqueue the native widget form helper runtime.
     *
     * @return void
     */
    private function enqueueNativeWidgetFormScript(): void
    {
        wp_enqueue_media();
        wp_enqueue_script('media-image-widget');
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', $this->getNativeWidgetFormScript());
    }

    /**
     * Build the native repeatable widget form helper script.
     *
     * @return string
     */
    private function getNativeWidgetFormScript(): string
    {
        return <<<'JS'
(() => {
    'use strict';

    /**
     * Return a unique string suitable for a repeatable row index.
     *
     * @return {string}
     */
    function getRowIndex() {
        return String(Date.now()) + String(Math.floor(Math.random() * 100000));
    }

    /**
     * Add a new row to a native repeatable widget field.
     *
     * @param {HTMLElement} repeater Repeatable field wrapper.
     * @return {void}
     */
    function addRepeaterRow(repeater) {
        const rows = repeater.querySelector('[data-ars-widget-repeater-rows]');
        const template = repeater.querySelector('template[data-ars-widget-repeater-template]');

        if (!(rows instanceof HTMLElement) || !(template instanceof HTMLTemplateElement)) {
            return;
        }

        const wrapper = document.createElement('div');

        wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', getRowIndex());

        Array.from(wrapper.children).forEach((child) => rows.appendChild(child));
    }

    /**
     * Return the image field wrapper for a clicked media button.
     *
     * @param {HTMLElement} button Clicked image field button.
     * @return {?HTMLElement}
     */
    function getImageField(button) {
        const field = button.closest('[data-ars-widget-image-field]');

        return field instanceof HTMLElement ? field : null;
    }

    /**
     * Update one native image field from a selected media attachment.
     *
     * @param {HTMLElement} field Image field wrapper.
     * @param {Object} attachment Selected media attachment.
     * @return {void}
     */
    function setImageFieldValue(field, attachment) {
        const input = field.querySelector('[data-ars-widget-image-input]');
        const removeButton = field.querySelector('[data-ars-widget-image-remove]');
        const selectButtons = field.querySelectorAll('[data-ars-widget-image-select]');

        if (input instanceof HTMLInputElement) {
            input.value = attachment && attachment.id ? String(attachment.id) : '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        renderImageFieldPreview(field, attachment ? getPreviewTemplateProps(attachment) : null);

        if (removeButton instanceof HTMLElement) {
            removeButton.style.display = attachment && attachment.id ? '' : 'none';
        }

        selectButtons.forEach((button) => {
            if (button instanceof HTMLElement) {
                button.classList.toggle('selected', Boolean(attachment && attachment.id));
            }
        });
    }

    /**
     * Return props expected by WordPress core's media image widget preview template.
     *
     * @param {?Object} attachment Media attachment object.
     * @return {?Object}
     */
    function getPreviewTemplateProps(attachment) {
        if (!attachment) {
            return null;
        }

        const attachmentUrl = attachment.sizes && attachment.sizes.medium
            ? attachment.sizes.medium.url
            : (attachment.url || '');

        if (!attachmentUrl) {
            return null;
        }

        return {
            attachment_id: attachment.id || 0,
            alt: attachment.alt || '',
            currentFilename: attachment.filename || attachmentUrl.replace(/\?.*$/, '').replace(/^.+\//, ''),
            error: false,
            link_url: '',
            url: attachmentUrl,
        };
    }

    /**
     * Render one image preview through WordPress core's media image widget template.
     *
     * @param {HTMLElement} field Image field wrapper.
     * @param {?Object} templateProps Preview template data.
     * @return {void}
     */
    function renderImageFieldPreview(field, templateProps) {
        const preview = field.querySelector('[data-ars-widget-image-preview]');

        if (!(preview instanceof HTMLElement)) {
            return;
        }

        if (!templateProps || !window.wp || !window.wp.template || !document.getElementById('tmpl-wp-media-widget-image-preview')) {
            const selectButton = field.querySelector('.media-widget-buttons [data-ars-widget-image-select]');
            const buttonText = selectButton instanceof HTMLElement
                ? selectButton.textContent || preview.getAttribute('data-select-label') || ''
                : preview.getAttribute('data-select-label') || '';

            preview.classList.remove('populated');
            preview.innerHTML = '<div class="attachment-media-view"><button type="button" class="select-media button-add-media not-selected" data-ars-widget-image-select></button></div>';

            const placeholderButton = preview.querySelector('[data-ars-widget-image-select]');

            if (placeholderButton instanceof HTMLElement) {
                placeholderButton.textContent = buttonText;
            }

            return;
        }

        preview.innerHTML = window.wp.template('wp-media-widget-image-preview')(templateProps);
        preview.classList.add('populated');
    }

    /**
     * Refresh previews for existing image field values after WordPress media scripts load.
     *
     * @return {void}
     */
    function hydrateImageFieldPreviews() {
        document.querySelectorAll('[data-ars-widget-image-field]').forEach((field) => {
            const input = field.querySelector('[data-ars-widget-image-input]');

            if (!(field instanceof HTMLElement) || !(input instanceof HTMLInputElement) || !input.value) {
                return;
            }

            if (/^\d+$/.test(input.value) && window.wp && window.wp.media && window.wp.media.model) {
                const attachment = window.wp.media.model.Attachment.get(Number(input.value));

                attachment.fetch().done(() => {
                    renderImageFieldPreview(field, getPreviewTemplateProps(attachment.toJSON()));
                });
                return;
            }

            renderImageFieldPreview(field, {
                attachment_id: 0,
                alt: '',
                currentFilename: input.value.replace(/\?.*$/, '').replace(/^.+\//, ''),
                error: false,
                link_url: '',
                url: input.value,
            });
        });
    }

    /**
     * Open the WordPress media library for one native image field.
     *
     * @param {HTMLElement} button Clicked select button.
     * @return {void}
     */
    function openImageFrame(button) {
        const field = getImageField(button);

        if (!field || !window.wp || !window.wp.media) {
            return;
        }

        const frame = window.wp.media({
            title: button.getAttribute('data-frame-title') || 'Select Image',
            button: {
                text: button.getAttribute('data-button-label') || 'Use This Image',
            },
            library: {
                type: 'image',
            },
            multiple: false,
        });

        frame.on('select', () => {
            const selectedAttachment = frame.state().get('selection').first();

            if (selectedAttachment) {
                setImageFieldValue(field, selectedAttachment.toJSON());
            }
        });

        frame.open();
    }

    /**
     * Remove the selected image from one native image field.
     *
     * @param {HTMLElement} button Clicked remove button.
     * @return {void}
     */
    function removeImageFieldValue(button) {
        const field = getImageField(button);

        if (!field) {
            return;
        }

        const input = field.querySelector('[data-ars-widget-image-input]');

        if (input instanceof HTMLInputElement) {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        renderImageFieldPreview(field, null);

        button.style.display = 'none';
    }

    hydrateImageFieldPreviews();

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof HTMLElement)) {
            return;
        }

        const imageSelectButton = target.closest('[data-ars-widget-image-select]');

        if (imageSelectButton instanceof HTMLElement) {
            event.preventDefault();
            openImageFrame(imageSelectButton);
            return;
        }

        const imageRemoveButton = target.closest('[data-ars-widget-image-remove]');

        if (imageRemoveButton instanceof HTMLElement) {
            event.preventDefault();
            removeImageFieldValue(imageRemoveButton);
            return;
        }

        const addButton = target.closest('[data-ars-widget-repeater-add]');

        if (addButton instanceof HTMLElement) {
            const repeater = addButton.closest('[data-ars-widget-repeater]');

            if (repeater instanceof HTMLElement) {
                event.preventDefault();
                addRepeaterRow(repeater);
            }

            return;
        }

        const removeButton = target.closest('[data-ars-widget-repeater-remove]');

        if (removeButton instanceof HTMLElement) {
            const row = removeButton.closest('[data-ars-widget-repeater-row]');

            if (row instanceof HTMLElement) {
                event.preventDefault();
                row.remove();
            }
        }
    });
})();
JS;
    }

    /**
     * Build the widget editor theme bridge script.
     *
     * @return string
     */
    private function getWidgetEditorThemeScript(): string
    {
        /** @var string $lightTheme Default light theme configured for the site. */
        $lightTheme = class_exists(\Jiejia\DaisyARippleSong\Settings\General::class)
            ? \Jiejia\DaisyARippleSong\Settings\General::getLightTheme()
            : 'retro';

        /** @var string $darkTheme Default dark theme configured for the site. */
        $darkTheme = class_exists(\Jiejia\DaisyARippleSong\Settings\General::class)
            ? \Jiejia\DaisyARippleSong\Settings\General::getDarkTheme()
            : 'dim';

        /** @var string $lightThemeJson JSON-safe light theme slug for the inline script. */
        $lightThemeJson = wp_json_encode($lightTheme);

        /** @var string $darkThemeJson JSON-safe dark theme slug for the inline script. */
        $darkThemeJson = wp_json_encode($darkTheme);

        return <<<JS
(() => {
    'use strict';

    const storageKey = 'theme-mode';
    const lightTheme = {$lightThemeJson};
    const darkTheme = {$darkThemeJson};
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
     * Force the preview document wrappers to size strictly to their content.
     *
     * @param {Document} targetDocument The preview iframe document.
     * @return {void}
     */
    function normalizePreviewDocumentLayout(targetDocument) {
        if (!targetDocument) {
            return;
        }

        if (!targetDocument.getElementById('ars-widget-preview-style')) {
            const styleElement = targetDocument.createElement('style');

            styleElement.id = 'ars-widget-preview-style';
            styleElement.textContent = `
                html,
                body,
                #page,
                #content,
                .widget,
                .widget > * {
                    min-height: 0 !important;
                    max-height: none !important;
                    height: auto !important;
                }

                html,
                body {
                    margin: 0 !important;
                    padding: 0 !important;
                    overflow-x: hidden !important;
                    overflow-y: hidden !important;
                    scrollbar-width: none !important;
                }

                html::-webkit-scrollbar,
                body::-webkit-scrollbar {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                }

                .widget {
                    overflow: hidden !important;
                    border-radius: 0.75rem !important;
                }
            `;

            targetDocument.head.appendChild(styleElement);
        }

        const wrapperNodes = [
            targetDocument.documentElement,
            targetDocument.body,
            targetDocument.querySelector('#page'),
            targetDocument.querySelector('#content'),
            targetDocument.querySelector('.widget'),
            targetDocument.querySelector('.widget > *'),
        ].filter(Boolean);

        wrapperNodes.forEach((node) => {
            node.style.setProperty('height', 'auto', 'important');
            node.style.setProperty('min-height', '0', 'important');
            node.style.setProperty('max-height', 'none', 'important');
            node.style.setProperty('overflow', 'hidden', 'important');
        });

        if (targetDocument.body) {
            targetDocument.body.style.setProperty('margin', '0', 'important');
            targetDocument.body.style.setProperty('padding', '0', 'important');
        }

        const widgetElement = targetDocument.querySelector('.widget');

        if (widgetElement) {
            widgetElement.style.setProperty('border-radius', '0.75rem', 'important');
            widgetElement.style.setProperty('overflow', 'hidden', 'important');
        }
    }

    /**
     * Return the element that represents the widget's real rendered content box.
     *
     * @param {Document} targetDocument The preview iframe document.
     * @return {?HTMLElement}
     */
    function getPreviewContentElement(targetDocument) {
        if (!targetDocument) {
            return null;
        }

        const widgetElement = targetDocument.querySelector('.widget');

        if (!widgetElement) {
            return null;
        }

        const contentElement = widgetElement.firstElementChild;

        return contentElement instanceof HTMLElement ? contentElement : widgetElement;
    }

    /**
     * Measure the rendered height of a specific element without inheriting the iframe viewport height.
     *
     * @param {?HTMLElement} targetElement Element that should drive the iframe height.
     * @return {number}
     */
    function getElementRenderedHeight(targetElement) {
        if (!targetElement) {
            return 0;
        }

        return Math.max(
            Math.ceil(targetElement.getBoundingClientRect().height),
            targetElement.scrollHeight || 0,
            targetElement.offsetHeight || 0
        );
    }

    /**
     * Return the best-fit pixel height for a preview iframe.
     *
     * @param {HTMLIFrameElement} frameElement The preview iframe element.
     * @return {?number}
     */
    function getFrameContentHeight(frameElement) {
        try {
            const targetDocument = frameElement.contentDocument;

            if (!targetDocument) {
                return null;
            }

            const contentElement = getPreviewContentElement(targetDocument);
            const widgetElement = targetDocument.querySelector('.widget');
            const height = Math.max(
                getElementRenderedHeight(contentElement),
                getElementRenderedHeight(widgetElement),
                1
            );

            return Number.isFinite(height) ? Math.ceil(height) : null;
        } catch (error) {
            return null;
        }
    }

    /**
     * Apply the computed layout styles to a preview iframe.
     *
     * @param {HTMLIFrameElement} frameElement The preview iframe element.
     * @param {number} contentHeight The resolved preview height in pixels.
     * @return {void}
     */
    function applyFrameHeight(frameElement, contentHeight) {
        if (!Number.isFinite(contentHeight) || contentHeight <= 0) {
            return;
        }

        frameElement.style.height = contentHeight + 'px';
        frameElement.height = String(contentHeight);
        frameElement.setAttribute('scrolling', 'no');
        frameElement.style.borderRadius = '0.75rem';
        frameElement.style.overflow = 'hidden';
        frameElement.style.display = 'block';
        frameElement.style.width = '100%';
        frameElement.style.maxHeight = 'none';
    }

    /**
     * Sync the iframe height with the rendered widget preview height.
     *
     * @param {HTMLIFrameElement} frameElement The preview iframe element.
     * @return {void}
     */
    function syncFrameHeight(frameElement) {
        const contentHeight = getFrameContentHeight(frameElement);

        if (!contentHeight) {
            return;
        }

        applyFrameHeight(frameElement, contentHeight);
    }

    /**
     * Run a few delayed height sync passes so collapsed previews can recover when they become visible.
     *
     * @param {HTMLIFrameElement} frameElement The preview iframe element.
     * @return {void}
     */
    function syncFrameHeightWithDelay(frameElement) {
        syncFrameHeight(frameElement);
        window.requestAnimationFrame(() => {
            syncFrameHeight(frameElement);
        });
        window.setTimeout(() => {
            syncFrameHeight(frameElement);
        }, 150);
        window.setTimeout(() => {
            syncFrameHeight(frameElement);
        }, 500);
    }

    /**
     * Observe iframe mutations so late content changes keep the preview height accurate.
     *
     * @param {HTMLIFrameElement} frameElement The preview iframe element.
     * @return {void}
     */
    function bindFrameHeightObserver(frameElement) {
        if (!frameElement || frameElement.dataset.arsHeightObserverBound === '1') {
            return;
        }

        try {
            const targetDocument = frameElement.contentDocument;
            const targetBody = targetDocument && targetDocument.body;

            if (!targetDocument || !targetBody || typeof MutationObserver === 'undefined') {
                return;
            }

            const resizeFrame = () => {
                syncFrameHeightWithDelay(frameElement);
            };

            const mutationObserver = new MutationObserver(resizeFrame);
            mutationObserver.observe(targetBody, {
                childList: true,
                subtree: true,
                attributes: true,
                characterData: true,
            });

            if (typeof ResizeObserver !== 'undefined') {
                const resizeObserver = new ResizeObserver(resizeFrame);
                const widgetElement = targetDocument.querySelector('.widget');

                resizeObserver.observe(targetBody);
                resizeObserver.observe(targetDocument.documentElement);

                if (widgetElement) {
                    resizeObserver.observe(widgetElement);
                }
            }

            Array.from(targetDocument.images || []).forEach((imageElement) => {
                if (imageElement.complete) {
                    return;
                }

                imageElement.addEventListener('load', resizeFrame, { once: true });
                imageElement.addEventListener('error', resizeFrame, { once: true });
            });

            if (targetDocument.fonts && typeof targetDocument.fonts.addEventListener === 'function') {
                targetDocument.fonts.addEventListener('loadingdone', resizeFrame);
            }

            frameElement.dataset.arsHeightObserverBound = '1';
            resizeFrame();
        } catch (error) {
            // Ignore inaccessible preview frames and continue processing others.
        }
    }

    /**
     * Re-sync iframe height whenever the preview enters the viewport after being collapsed.
     *
     * @param {HTMLIFrameElement} frameElement The preview iframe element.
     * @return {void}
     */
    function bindFrameVisibilityObserver(frameElement) {
        if (!frameElement || frameElement.dataset.arsVisibilityObserverBound === '1' || typeof IntersectionObserver === 'undefined') {
            return;
        }

        const visibilityObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    syncFrameHeightWithDelay(frameElement);
                }
            });
        }, {
            threshold: 0,
        });

        visibilityObserver.observe(frameElement);
        frameElement.dataset.arsVisibilityObserverBound = '1';
    }

    /**
     * Bind a window message listener so preview iframes can report their own height.
     *
     * @return {void}
     */
    function bindPreviewMessageListener() {
        if (window.__arsWidgetPreviewMessageBound) {
            return;
        }

        window.__arsWidgetPreviewMessageBound = true;
        window.addEventListener('message', (event) => {
            const message = event.data;

            if (!message || message.type !== 'ars-widget-preview:height') {
                return;
            }

            const matchingFrame = getLegacyWidgetPreviewFrames().find((frameElement) => {
                try {
                    return frameElement.contentWindow === event.source;
                } catch (error) {
                    return false;
                }
            });

            if (!matchingFrame) {
                return;
            }

            applyFrameHeight(matchingFrame, Number(message.height || 0));
        });
    }

    /**
     * Keep the widget editor shell on the default admin background so only preview iframes use the theme surface colors.
     *
     * @return {void}
     */
    function ensureWidgetEditorShellStyles() {
        if (document.getElementById('ars-widget-editor-shell-style')) {
            return;
        }

        const styleElement = document.createElement('style');

        styleElement.id = 'ars-widget-editor-shell-style';
        styleElement.textContent = `
            .edit-widgets-block-editor .wp-block-widget-area__inner-blocks,
            .edit-widgets-block-editor .wp-block-widget-area__inner-blocks.editor-styles-wrapper,
            .edit-widgets-block-editor .wp-block-widget-area__inner-blocks > .block-editor-block-list__layout,
            .edit-widgets-block-editor .block-editor-block-list__layout {
                background: #ffffff !important;
            }
        `;

        document.head.appendChild(styleElement);
    }

    /**
     * Return whether an iframe looks like a legacy widget preview frame.
     *
     * This must not depend on localized attributes such as the iframe title.
     *
     * @param {HTMLIFrameElement} frameElement The iframe candidate.
     * @return {boolean}
     */
    function isLegacyWidgetPreviewFrame(frameElement) {
        if (!(frameElement instanceof HTMLIFrameElement)) {
            return false;
        }

        const frameClassName = String(frameElement.className || '');
        const frameSource = String(frameElement.getAttribute('src') || '');

        return frameClassName.includes('wp-block-legacy-widget__edit-preview-iframe')
            || frameSource.includes('legacy-widget-preview')
            || (frameSource.includes('/wp/v2/widget-types/') && frameSource.includes('/render'));
    }

    /**
     * Return all legacy widget preview iframes currently rendered in the editor.
     *
     * @return {HTMLIFrameElement[]}
     */
    function getLegacyWidgetPreviewFrames() {
        return Array.from(document.querySelectorAll('iframe')).filter((frameElement) => isLegacyWidgetPreviewFrame(frameElement));
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
                normalizePreviewDocumentLayout(frameElement.contentDocument);
                syncFrameHeightWithDelay(frameElement);
                bindFrameHeightObserver(frameElement);
                bindFrameVisibilityObserver(frameElement);
            }
        } catch (error) {
            // Ignore inaccessible preview frames and continue processing others.
        }

        if (!frameElement.dataset.arsThemeBound) {
            frameElement.dataset.arsThemeBound = '1';
            frameElement.addEventListener('load', () => {
                applyThemeToFrame(frameElement);
                syncFrameHeightWithDelay(frameElement);
            });
        }
    }

    /**
     * Apply the active theme to the parent editor document and all preview iframes.
     *
     * @return {void}
     */
    function applyThemeEverywhere() {
        ensureWidgetEditorShellStyles();

        getLegacyWidgetPreviewFrames().forEach((frameElement) => {
            applyThemeToFrame(frameElement);
        });
    }

    /**
     * Re-run the preview sync loop for a short period after page load so restored widget areas settle correctly.
     *
     * @return {void}
     */
    function bootstrapPreviewSync() {
        let runCount = 0;
        const maxRuns = 24;
        const intervalId = window.setInterval(() => {
            applyThemeEverywhere();
            runCount += 1;

            if (runCount >= maxRuns) {
                window.clearInterval(intervalId);
            }
        }, 250);
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
            bindPreviewMessageListener();
            observePreviewFrames();
            bootstrapPreviewSync();
        }, { once: true });
    } else {
        applyThemeEverywhere();
        bindPreviewMessageListener();
        observePreviewFrames();
        bootstrapPreviewSync();
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

            /** @var string|null $updatedTag Preserve inline scripts and only upgrade the external tag to a module. */
            $updatedTag = preg_replace_callback(
                '/<script\b(?=[^>]*\ssrc=)([^>]*)>/',
                static function (array $matches): string {
                    /** @var string $attributes Existing script tag attributes. */
                    $attributes = preg_replace('/\s+type=(["\']).*?\1/', '', $matches[1], 1);

                    return '<script type="module"' . $attributes . '>';
                },
                $tag,
                1
            );

            return is_string($updatedTag) ? $updatedTag : $tag;
        }, 10, 3);
    }
}
