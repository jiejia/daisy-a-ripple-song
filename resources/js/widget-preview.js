import { createIcons, icons } from 'lucide';
import Alpine from 'alpinejs';

/**
 * Expose Alpine for devtools and third-party integrations.
 */
window.Alpine = Alpine;

/**
 * Create a tiny player store for widget previews so Alpine directives resolve
 * without booting the full front-end audio/player runtime.
 */
if (!Alpine.store('player')) {
    Alpine.store('player', {
        currentEpisode: null,
        isPlaying: false,

        /**
         * Record the selected episode in preview mode without starting audio.
         *
         * @param {Record<string, unknown>} episode Preview episode payload.
         * @return {void}
         */
        addEpisode(episode) {
            this.currentEpisode = episode || null;
            this.isPlaying = false;
        },

        /**
         * Toggle the preview state to paused.
         *
         * @return {void}
         */
        pause() {
            this.isPlaying = false;
        },

        /**
         * Toggle the preview state to playing.
         *
         * @return {void}
         */
        play() {
            this.isPlaying = true;
        },
    });
}

/**
 * Refresh Lucide icons after Alpine mutates the preview DOM.
 *
 * @return {void}
 */
function refreshIcons() {
    createIcons({ icons });
}

/** @type {number} last reported preview height */
let lastReportedHeight = 0;

/**
 * Return the current rendered widget preview height.
 *
 * @return {number}
 */
function getPreviewHeight() {
    const widgetElement = document.querySelector('.widget');
    const targetBody = document.body;
    const targetRoot = document.documentElement;

    return Math.max(
        widgetElement ? Math.ceil(widgetElement.getBoundingClientRect().height) : 0,
        widgetElement ? widgetElement.scrollHeight : 0,
        targetBody ? targetBody.scrollHeight : 0,
        targetBody ? Math.ceil(targetBody.getBoundingClientRect().height) : 0,
        targetRoot ? targetRoot.scrollHeight : 0,
        targetRoot ? Math.ceil(targetRoot.getBoundingClientRect().height) : 0,
        100
    );
}

/**
 * Send the current preview height to the parent widget editor.
 *
 * @param {boolean} [force=false] Whether to report even when the value is unchanged.
 * @return {void}
 */
function reportPreviewHeight(force = false) {
    const previewHeight = getPreviewHeight();

    if (!force && previewHeight === lastReportedHeight) {
        return;
    }

    lastReportedHeight = previewHeight;

    if (window.parent && window.parent !== window) {
        window.parent.postMessage({
            type: 'ars-widget-preview:height',
            height: previewHeight,
        }, '*');
    }
}

/**
 * Queue a near-future preview height report after layout settles.
 *
 * @return {void}
 */
function queuePreviewHeightReport() {
    window.requestAnimationFrame(() => {
        reportPreviewHeight();
    });

    window.setTimeout(() => {
        reportPreviewHeight();
    }, 60);
}

/**
 * Keep the parent iframe height aligned with the preview's real layout.
 *
 * @return {void}
 */
function bootstrapPreviewHeightSync() {
    const targetBody = document.body;
    const widgetElement = document.querySelector('.widget');

    reportPreviewHeight(true);
    queuePreviewHeightReport();

    if (targetBody && typeof MutationObserver !== 'undefined') {
        const mutationObserver = new MutationObserver(queuePreviewHeightReport);

        mutationObserver.observe(targetBody, {
            childList: true,
            subtree: true,
            attributes: true,
            characterData: true,
        });
    }

    if (typeof ResizeObserver !== 'undefined') {
        const resizeObserver = new ResizeObserver(queuePreviewHeightReport);

        [document.documentElement, targetBody, widgetElement].filter(Boolean).forEach((element) => {
            resizeObserver.observe(element);
        });
    }

    Array.from(document.images || []).forEach((imageElement) => {
        if (imageElement.complete) {
            return;
        }

        imageElement.addEventListener('load', queuePreviewHeightReport, { once: true });
        imageElement.addEventListener('error', queuePreviewHeightReport, { once: true });
    });

    if (document.fonts && typeof document.fonts.addEventListener === 'function') {
        document.fonts.addEventListener('loadingdone', queuePreviewHeightReport);
    }

    if (document.readyState !== 'complete') {
        window.addEventListener('load', queuePreviewHeightReport, { once: true });
    }

    let runCount = 0;
    const intervalId = window.setInterval(() => {
        reportPreviewHeight();
        runCount += 1;

        if (runCount >= 48) {
            window.clearInterval(intervalId);
        }
    }, 250);
}

Alpine.start();
refreshIcons();
bootstrapPreviewHeightSync();

document.addEventListener('alpine:initialized', refreshIcons, { once: true });
document.addEventListener('alpine:updated', refreshIcons);
document.addEventListener('alpine:initialized', queuePreviewHeightReport, { once: true });
document.addEventListener('alpine:updated', queuePreviewHeightReport);
