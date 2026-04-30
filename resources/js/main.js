import Swup from 'swup';
import { createIcons, icons } from 'lucide';
import {
    siApplepodcasts,
    siFacebook,
    siInstagram,
    siPinterest,
    siRss,
    siSinaweibo,
    siSpotify,
    siThreads,
    siTiktok,
    siWechat,
    siX,
    siYoutube,
    siYoutubemusic,
} from 'simple-icons';
import { Howl } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import Alpine from 'alpinejs';
import SwupFormsPlugin from '@swup/forms-plugin';
import SwupScriptsPlugin from '@swup/scripts-plugin';


window.Alpine = Alpine;

/** @type {Record<string, {title: string, path: string, hex: string}>} Footer brand icon lookup. */
const simpleBrandIcons = {
    applepodcasts: siApplepodcasts,
    facebook: siFacebook,
    twitter: siX,
    instagram: siInstagram,
    youtube: siYoutube,
    youtubemusic: siYoutubemusic,
    tiktok: siTiktok,
    pinterest: siPinterest,
    threads: siThreads,
    weibo: siSinaweibo,
    wechat: siWechat,
    rss: siRss,
    spotify: siSpotify,
};

/** @type {{view: string, play: string}} AJAX action names for metric updates. */
const metricActions = {
    view: 'aripplesong_increment_view',
    play: 'aripplesong_increment_play',
};

/** @type {string|null} Deduplicate singular view tracking across Swup page loads. */
let lastViewMetricKey = null;

/**
 * Format a metric count using the current document locale when possible.
 *
 * @param {number|string} value The numeric value to format.
 * @return {string}
 */
function formatMetricCount(value) {
    const safeValue = Math.max(0, Number.parseInt(value, 10) || 0);

    try {
        return new Intl.NumberFormat(document.documentElement.lang || undefined).format(safeValue);
    } catch (error) {
        return String(safeValue);
    }
}

/**
 * Update every metric DOM node that matches the target post ID.
 *
 * @param {string} selector CSS selector for the metric nodes.
 * @param {number} postId The post ID whose nodes should be updated.
 * @param {number} count The latest metric count.
 * @return {void}
 */
function updateMetricCountDom(selector, postId, count) {
    if (!postId || !Number.isFinite(count)) {
        return;
    }

    document.querySelectorAll(`${selector}[data-post-id="${postId}"]`).forEach((element) => {
        element.textContent = formatMetricCount(count);
    });
}

/**
 * Return the primary singular post ID for view tracking.
 *
 * @return {number}
 */
function resolvePrimaryPostId() {
    const swupMain = document.querySelector('#swup-main');
    const mainPostId = Number(swupMain?.dataset.currentPostId || 0);

    if (mainPostId) {
        return mainPostId;
    }

    const ajax = window.aripplesongData?.ajax;

    if (ajax?.postId) {
        return Number(ajax.postId) || 0;
    }

    const viewElements = Array.from(document.querySelectorAll('.js-views-count[data-post-id]'));
    const postIds = [...new Set(viewElements.map((element) => Number(element.dataset.postId)).filter(Boolean))];

    return postIds.length === 1 ? postIds[0] : 0;
}

/**
 * Sync the frontend AJAX context from the currently rendered Swup main container.
 *
 * @return {void}
 */
function syncCurrentPageAjaxContext() {
    const swupMain = document.querySelector('#swup-main');

    if (!swupMain) {
        return;
    }

    if (!window.aripplesongData) {
        window.aripplesongData = {};
    }

    if (!window.aripplesongData.ajax) {
        window.aripplesongData.ajax = {};
    }

    window.aripplesongData.ajax.postId = Number(swupMain.dataset.currentPostId || 0);
    window.aripplesongData.ajax.postType = swupMain.dataset.currentPostType || '';
}

/**
 * Send a metric AJAX request to WordPress.
 *
 * @param {string} action The WordPress AJAX action name.
 * @param {number} postId The target post ID.
 * @param {Record<string, string|number>} extraData Additional form fields to include.
 * @return {Promise<object|null>}
 */
async function sendAjaxMetric(action, postId, extraData = {}) {
    const ajax = window.aripplesongData?.ajax;

    if (!ajax?.url || !ajax?.nonce || !postId) {
        return null;
    }

    const params = new URLSearchParams({
        action,
        post_id: String(postId),
        _ajax_nonce: ajax.nonce,
    });

    Object.entries(extraData).forEach(([key, value]) => {
        params.append(key, String(value));
    });

    try {
        const response = await fetch(ajax.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params,
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return await response.json();
    } catch {
        return null;
    }
}

/**
 * Fetch current metric counts for the posts rendered on the page.
 *
 * @param {number[]} postIds Post IDs to fetch.
 * @return {Promise<Record<string, {views?: number, plays?: number|null}>|null>}
 */
async function fetchMetrics(postIds = []) {
    const ids = [...new Set(postIds.map((id) => Number(id)).filter(Boolean))];

    if (!ids.length) {
        return null;
    }

    const ajax = window.aripplesongData?.ajax;

    if (!ajax?.url || !ajax?.nonce) {
        return null;
    }

    const params = new URLSearchParams({
        action: 'aripplesong_get_metrics',
        _ajax_nonce: ajax.nonce,
    });

    ids.forEach((id) => {
        params.append('post_ids[]', String(id));
    });

    try {
        const fetchResponse = await fetch(ajax.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params,
        });

        if (!fetchResponse.ok) {
            throw new Error(`HTTP ${fetchResponse.status}`);
        }

        const json = await fetchResponse.json();
        return json?.data?.counts || null;
    } catch {
        return null;
    }
}

/**
 * Hydrate rendered metric values with the latest AJAX counts.
 *
 * @return {void}
 */
function hydrateMetricsFromDom() {
    const metricElements = Array.from(document.querySelectorAll('.js-views-count[data-post-id], .js-play-count[data-post-id]'));
    const postIds = [...new Set(metricElements.map((element) => Number(element.dataset.postId)).filter(Boolean))];

    if (!postIds.length) {
        return;
    }

    fetchMetrics(postIds).then((counts) => {
        if (!counts) {
            return;
        }

        Object.entries(counts).forEach(([postId, entry]) => {
            const numericPostId = Number(postId);

            if (Number.isFinite(entry?.views)) {
                updateMetricCountDom('.js-views-count', numericPostId, Number(entry.views));
            }

            if (Number.isFinite(entry?.plays)) {
                updateMetricCountDom('.js-play-count', numericPostId, Number(entry.plays));
            }
        });
    }).catch(() => null);
}

/**
 * Send a single view increment for the current singular page.
 *
 * @return {void}
 */
function maybeSendViewMetric() {
    const postId = resolvePrimaryPostId();

    if (!postId) {
        return;
    }

    const metricKey = `${postId}:${window.location.href}`;

    if (lastViewMetricKey === metricKey) {
        return;
    }

    lastViewMetricKey = metricKey;

    sendAjaxMetric(metricActions.view, postId).then((response) => {
        const count = response?.data?.count;

        if (Number.isFinite(count)) {
            updateMetricCountDom('.js-views-count', postId, Number(count));
        }
    }).catch(() => null);
}

/**
 * Create an in-memory storage fallback when the browser blocks persistent storage.
 *
 * @return {Storage}
 */
function createMemoryStorage() {
    const store = {};

    return {
        getItem(key) {
            return Object.prototype.hasOwnProperty.call(store, key) ? store[key] : null;
        },
        setItem(key, value) {
            store[key] = String(value);
        },
        removeItem(key) {
            delete store[key];
        },
        clear() {
            Object.keys(store).forEach((key) => delete store[key]);
        },
        key(index) {
            return Object.keys(store)[index] || null;
        },
        get length() {
            return Object.keys(store).length;
        },
    };
}

/**
 * Return a storage implementation that is safe to use in restrictive browsers.
 *
 * @param {string} type The storage type to access.
 * @return {Storage}
 */
function createSafeStorage(type = 'localStorage') {
    if (typeof window === 'undefined') {
        return createMemoryStorage();
    }

    try {
        const storage = window[type];
        const testKey = '__aripplesong_storage_test__';

        storage.setItem(testKey, '1');
        storage.removeItem(testKey);

        return storage;
    } catch {
        return createMemoryStorage();
    }
}

/**
 * Strip HTML markup from a string returned by the WordPress REST API.
 *
 * @param {string} value The HTML string to sanitize.
 * @return {string}
 */
function stripHtml(value = '') {
    const div = document.createElement('div');
    div.innerHTML = value;
    return (div.textContent || div.innerText || '').trim();
}

/**
 * Convert a numeric duration into mm:ss format.
 *
 * @param {number} seconds The duration in seconds.
 * @return {string}
 */
function formatTime(seconds) {
    const safeSeconds = Number.isFinite(seconds) ? Math.max(0, seconds) : 0;
    const minutes = Math.floor(safeSeconds / 60);
    const remainingSeconds = Math.floor(safeSeconds % 60);

    return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
}

/**
 * Build a site-aware REST endpoint for fetching latest episodes.
 *
 * @return {string}
 */
function getEpisodesEndpoint() {
    const apiLink = document.querySelector('link[rel="https://api.w.org/"]')?.href;
    const restRoot = window.wpApiSettings?.root || apiLink || `${window.location.origin}/wp-json/`;
    const normalizedRoot = restRoot.endsWith('/') ? restRoot : `${restRoot}/`;
    const podcastPostType = window.aripplesongData?.ajax?.podcastPostType || 'ars_episode';
    const query = 'per_page=5&orderby=date&order=desc&_embed=1';
    const separator = normalizedRoot.includes('?') ? '&' : '?';

    return `${normalizedRoot}wp/v2/${podcastPostType}${separator}${query}`;
}

/**
 * Render footer brand icons using Simple Icons.
 *
 * @param {ParentNode} root The DOM subtree to scan.
 * @return {void}
 */
function renderSimpleIcons(root = document) {
    root.querySelectorAll('[data-simple-icon]').forEach((iconNode) => {
        if (!(iconNode instanceof HTMLElement)) {
            return;
        }

        const iconName = String(iconNode.dataset.simpleIcon || '').trim().toLowerCase();
        const iconLabel = String(iconNode.dataset.simpleIconLabel || '').trim();
        const icon = simpleBrandIcons[iconName];

        if (!icon) {
            iconNode.textContent = iconLabel.slice(0, 1).toUpperCase();
            iconNode.setAttribute('aria-hidden', 'true');
            return;
        }

        iconNode.innerHTML = `
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor" class="block h-full w-full">
                <title>${iconLabel || icon.title}</title>
                <path d="${icon.path}"></path>
            </svg>
        `;
        iconNode.setAttribute('aria-hidden', 'true');
    });
}

/**
 * Build a consistent episode object for the player store.
 *
 * @param {object} post A REST API post object.
 * @return {?object}
 */
function normalizeEpisode(post) {
    const audioUrl = typeof post?.audio_file === 'string' ? post.audio_file.trim() : '';
    if (!audioUrl) {
        return null;
    }

    const featuredImage = post?._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
    const publishDate = Date.parse(post?.date || '');

    return {
        id: Number(post?.id || 0),
        audioUrl,
        title: stripHtml(post?.title?.rendered || ''),
        description: stripHtml(post?.excerpt?.rendered || ''),
        publishDate: Number.isFinite(publishDate) ? Math.floor(publishDate / 1000) : 0,
        featuredImage,
        link: typeof post?.link === 'string' ? post.link : '',
    };
}

/**
 * Format a Unix timestamp using the current document locale.
 *
 * @param {number} timestamp Unix timestamp in seconds.
 * @return {string}
 */
window.formatLocalizedDate = function formatLocalizedDate(timestamp) {
    const safeTimestamp = Number(timestamp);
    if (!Number.isFinite(safeTimestamp) || safeTimestamp <= 0) {
        return '-';
    }

    const locale = document.documentElement.lang || 'en-US';
    const date = new Date(safeTimestamp * 1000);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMinutes = Math.round(diffMs / 60000);
    const diffHours = Math.round(diffMs / 3600000);
    const diffDays = Math.round(diffMs / 86400000);

    if (Intl.RelativeTimeFormat) {
        const formatter = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });

        if (Math.abs(diffMinutes) < 60) {
            return formatter.format(-diffMinutes, 'minute');
        }

        if (Math.abs(diffHours) < 24) {
            return formatter.format(-diffHours, 'hour');
        }

        if (Math.abs(diffDays) < 7) {
            return formatter.format(-diffDays, 'day');
        }
    }

    return new Intl.DateTimeFormat(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};

/**
 * Compute per-window RMS values for the progress heatmap.
 *
 * @param {AudioBuffer} audioBuffer The decoded audio buffer.
 * @param {number} stepSeconds The analysis window size in seconds.
 * @return {number[]}
 */
function computeRmsBySecond(audioBuffer, stepSeconds = 1) {
    const step = Math.max(1, Number(stepSeconds) || 1);
    const seconds = Math.max(1, Math.ceil(audioBuffer.duration / step));
    const sampleRate = audioBuffer.sampleRate;
    const channels = Math.max(1, audioBuffer.numberOfChannels || 1);
    const channelData = Array.from({ length: channels }, (_, index) => audioBuffer.getChannelData(index));
    const values = new Array(seconds).fill(0);

    for (let secondIndex = 0; secondIndex < seconds; secondIndex += 1) {
        const startTime = secondIndex * step;
        const endTime = Math.min((secondIndex + 1) * step, audioBuffer.duration);
        const startSample = Math.max(0, Math.floor(startTime * sampleRate));
        const endSample = Math.min(audioBuffer.length, Math.floor(endTime * sampleRate));
        const windowSamples = Math.max(0, endSample - startSample);

        if (windowSamples <= 0) {
            values[secondIndex] = 0;
            continue;
        }

        const stride = Math.max(1, Math.floor(windowSamples / 2048));
        let sumSquares = 0;
        let count = 0;

        for (let sampleIndex = startSample; sampleIndex < endSample; sampleIndex += stride) {
            let mixed = 0;

            for (let channelIndex = 0; channelIndex < channels; channelIndex += 1) {
                mixed += channelData[channelIndex][sampleIndex] || 0;
            }

            mixed /= channels;
            sumSquares += mixed * mixed;
            count += 1;
        }

        values[secondIndex] = count > 0 ? Math.sqrt(sumSquares / count) : 0;
    }

    return values;
}

/**
 * Smooth per-second values so the heatmap does not look overly noisy.
 *
 * @param {number[]} values The raw values.
 * @param {number} radius The smoothing radius.
 * @return {number[]}
 */
function smoothValues(values, radius = 1) {
    const safeRadius = Math.max(0, Math.floor(radius));
    if (safeRadius === 0) {
        return values.slice();
    }

    const result = new Array(values.length).fill(0);

    for (let index = 0; index < values.length; index += 1) {
        let sum = 0;
        let count = 0;

        for (let offset = index - safeRadius; offset <= index + safeRadius; offset += 1) {
            if (offset < 0 || offset >= values.length) {
                continue;
            }

            sum += values[offset];
            count += 1;
        }

        result[index] = count > 0 ? sum / count : values[index];
    }

    return result;
}

/**
 * Build an orange gradient string for the player progress background.
 *
 * @param {number[]} values The normalized RMS values.
 * @return {string}
 */
function buildOrangeHeatGradient(values) {
    if (!Array.isArray(values) || values.length === 0) {
        return '';
    }

    let min = Infinity;
    let max = -Infinity;

    values.forEach((value) => {
        const safeValue = Number.isFinite(value) ? value : 0;
        min = Math.min(min, safeValue);
        max = Math.max(max, safeValue);
    });

    const range = max - min;
    if (!Number.isFinite(range) || range <= 1e-8) {
        return '';
    }

    const levels = 24;
    const normalized = values.map((value) => {
        const safeValue = Number.isFinite(value) ? value : 0;
        const ratio = Math.min(1, Math.max(0, (safeValue - min) / range));
        return Math.round(Math.pow(ratio, 0.6) * (levels - 1));
    });

    const colorForLevel = (level) => {
        const ratio = level / (levels - 1);
        const lightness = 82 - ratio * 30;
        return `hsl(35, 100%, ${lightness}%)`;
    };

    const stops = [];
    let index = 0;

    while (index < normalized.length) {
        const level = normalized[index];
        let end = index + 1;

        while (end < normalized.length && normalized[end] === level) {
            end += 1;
        }

        const color = colorForLevel(level);
        const startPercent = ((index / normalized.length) * 100).toFixed(3);
        const endPercent = ((end / normalized.length) * 100).toFixed(3);

        stops.push(`${color} ${startPercent}%`, `${color} ${endPercent}%`);
        index = end;
    }

    return `linear-gradient(to right, ${stops.join(', ')})`;
}

const safeLocalStorage = createSafeStorage('localStorage');
const progressHeatmapCache = new Map();
const themeOptions = window.aripplesongData?.theme || {};

/**
 * Normalize a Lucide icon name to the kebab-case format expected by the runtime.
 *
 * @param {string} value The raw icon name from the DOM.
 * @return {string}
 */
function normalizeLucideIconName(value = '') {
    return String(value)
        .trim()
        .replace(/([A-Z]+)([A-Z][a-z])/g, '$1-$2')
        .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
        .replace(/_/g, '-')
        .toLowerCase();
}

const lucideIconNames = new Set(
    Object.keys(icons)
        .map((iconName) => normalizeLucideIconName(iconName))
        .filter(Boolean),
);

/**
 * Render Lucide icons while skipping unsupported icon names.
 *
 * @param {ParentNode} root The DOM subtree to scan.
 * @return {void}
 */
function renderLucideIcons(root = document) {
    if (!root?.querySelectorAll) {
        return;
    }

    root.querySelectorAll('[data-lucide]').forEach((iconNode) => {
        const rawName = iconNode.getAttribute('data-lucide') || '';
        const normalizedName = normalizeLucideIconName(rawName);

        if (!normalizedName || !lucideIconNames.has(normalizedName)) {
            iconNode.removeAttribute('data-lucide');
            iconNode.setAttribute('data-lucide-missing', rawName);
            return;
        }

        if (normalizedName !== rawName) {
            iconNode.setAttribute('data-lucide', normalizedName);
        }
    });

    createIcons({ icons });
}

/**
 * Queue a Lucide refresh after Alpine mutates the DOM.
 *
 * @return {void}
 */
function queueIconRefresh() {
    window.requestAnimationFrame(() => {
        renderLucideIcons(document);
    });
}

// Theme Store
Alpine.store('theme', {
    mode: 'auto',
    modes: ['light', 'dark', 'auto'],
    storageKey: 'theme-mode',
    lightTheme: typeof themeOptions.lightTheme === 'string' && themeOptions.lightTheme !== '' ? themeOptions.lightTheme : 'retro',
    darkTheme: typeof themeOptions.darkTheme === 'string' && themeOptions.darkTheme !== '' ? themeOptions.darkTheme : 'dim',
    current: typeof themeOptions.lightTheme === 'string' && themeOptions.lightTheme !== '' ? themeOptions.lightTheme : 'retro',

    init() {
        const savedMode = safeLocalStorage.getItem(this.storageKey);
        this.mode = this.modes.includes(savedMode) ? savedMode : 'auto';
    },

    toggle() {
        this.mode = this.mode === 'light' ? 'dark' : this.mode === 'dark' ? 'auto' : 'light';
        safeLocalStorage.setItem(this.storageKey, this.mode);
    },

    get current() {
        if (this.mode === 'light') {
            return this.lightTheme;
        }

        if (this.mode === 'dark') {
            return this.darkTheme;
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches ? this.darkTheme : this.lightTheme;
    },

    get isDark() {
        return this.mode === 'dark';
    },

    get isLight() {
        return this.mode === 'light';
    },

    get isAuto() {
        return this.mode === 'auto';
    },
});

// Player Store
Alpine.store('player', {
    currentSound: null,
    audioMotion: null,
    analyzerAudioContext: null,
    analyzerSourceNode: null,
    analyzerRebindTimer: null,
    progressTimer: null,
    soundId: null,
    currentTime: 0,
    duration: 0,
    volume: 0.8,
    lastVolume: 0.8,
    isMuted: false,
    isPlaying: false,
    isLoading: false,
    volumePanelOpen: false,
    playbackRate: 1,
    availableRates: [0.5, 0.75, 1, 1.25, 1.5, 2],
    playbackRatePanelOpen: false,
    showAutoplayConfirm: false,
    pendingAutoplay: false,
    autoplayCountdown: 10,
    autoplayCountdownTimer: null,
    playlist: [],
    currentIndex: 0,
    currentEpisode: null,
    progressHeatmapGradient: '',
    progressHeatmapReady: false,
    progressHeatmapStepSeconds: 10,
    progressHeatmapSmoothingRadius: 1,
    heatmapNonce: 0,
    initialized: false,
    storageKeys: {
        playlist: 'aripplesong-playlist',
        currentIndex: 'aripplesong-current-index',
        currentTime: 'aripplesong-current-time',
        isPlaying: 'aripplesong-is-playing',
        volume: 'aripplesong-volume',
        playbackRate: 'aripplesong-playback-rate',
    },

    get currentTimeText() {
        return formatTime(this.currentTime);
    },

    get durationText() {
        return formatTime(this.duration);
    },

    get playbackRateText() {
        return this.playbackRate === 1 ? '1x' : `${this.playbackRate}x`;
    },

    get currentEpisodePublishDate() {
        return this.currentEpisode?.publishDate ? window.formatLocalizedDate(this.currentEpisode.publishDate) : '-';
    },

    /**
     * Initialize the player from storage and backfill the playlist when needed.
     *
     * @return {Promise<void>}
     */
    async init() {
        if (this.initialized) {
            return;
        }

        this.initialized = true;
        this.loadPlaylist();
        this.loadVolume();
        this.loadPlaybackRate();
        const playbackState = this.loadPlaybackState();

        if (this.playlist.length === 0) {
            await this.fetchLatestEpisodes();
        }

        if (this.playlist.length === 0) {
            return;
        }

        this.currentIndex = Math.min(this.currentIndex, this.playlist.length - 1);
        this.currentEpisode = this.playlist[this.currentIndex] || null;

        if (!this.currentEpisode?.audioUrl) {
            return;
        }

        this.currentTime = playbackState.currentTime;

        this.loadTrack(this.currentEpisode.audioUrl, { autoplay: false, restoreTime: this.currentTime });

        if (playbackState.isPlaying && this.currentSound) {
            this.currentSound.once('load', () => {
                this.showAutoplayConfirmDialog();
            });
        }
    },

    /**
     * Fetch the latest episodes and seed the playlist.
     *
     * @return {Promise<void>}
     */
    async fetchLatestEpisodes() {
        try {
            const response = await fetch(getEpisodesEndpoint(), {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const posts = await response.json();
            const episodes = posts.map(normalizeEpisode).filter(Boolean);

            if (!episodes.length) {
                return;
            }

            this.playlist = episodes;
            this.currentIndex = 0;
            this.currentEpisode = episodes[0];
            this.savePlaylist();
        } catch {
            this.playlist = [];
            this.currentIndex = 0;
            this.currentEpisode = null;
        }
    },

    /**
     * Load an audio track and prepare player state around it.
     *
     * @param {string} audioUrl The audio source URL.
     * @param {{autoplay?: boolean, restoreTime?: number}} options Track loading options.
     * @return {void}
     */
    loadTrack(audioUrl, options = {}) {
        const { autoplay = false, restoreTime = 0 } = options;
        const restoreAt = Number.isFinite(restoreTime) ? Math.max(0, restoreTime) : 0;

        this.stopProgressTimer();
        this.destroyAnalyzer();
        this.clearAutoplayTimers();
        this.showAutoplayConfirm = false;
        this.pendingAutoplay = false;
        this.isPlaying = false;
        this.heatmapNonce += 1;
        this.progressHeatmapGradient = '';
        this.progressHeatmapReady = false;
        this.isLoading = true;
        this.duration = 0;

        if (this.currentSound) {
            this.currentSound.unload();
            this.currentSound = null;
        }

        this.soundId = null;

        this.currentSound = new Howl({
            src: [audioUrl],
            volume: this.isMuted ? 0 : this.volume,
            rate: this.playbackRate,
            html5: false,
            onload: () => {
                if (!this.currentSound) {
                    return;
                }

                this.duration = this.currentSound.duration() || 0;
                this.isLoading = false;

                if (restoreAt > 0) {
                    this.seek(restoreAt);
                }

                if (autoplay) {
                    this.play();
                }

                this.generateProgressHeatmap(audioUrl, this.heatmapNonce);
            },
            onplay: () => {
                this.isPlaying = true;
                this.bindAnalyzer();
                this.startProgressTimer();
            },
            onseek: () => {
                this.currentTime = Number(this.currentSound?.seek(this.soundId)) || 0;
                this.scheduleAnalyzerRebind();
            },
            onpause: () => {
                this.isPlaying = false;
                this.stopProgressTimer();
                this.savePlaybackState();
            },
            onstop: () => {
                this.isPlaying = false;
                this.stopProgressTimer();
                this.savePlaybackState();
            },
            onend: () => {
                this.playNext();
            },
            onloaderror: () => {
                this.isLoading = false;
            },
            onplayerror: () => {
                this.isPlaying = false;
            },
        });
    },

    /**
     * Decode the current audio file and build the progress heatmap background.
     *
     * @param {string} audioUrl The audio URL to analyze.
     * @param {number} nonce The load-generation marker.
     * @return {Promise<void>}
     */
    async generateProgressHeatmap(audioUrl, nonce) {
        if (!audioUrl) {
            return;
        }

        const cacheKey = `${audioUrl}::step=${this.progressHeatmapStepSeconds}`;
        const cached = progressHeatmapCache.get(cacheKey);

        if (cached) {
            this.progressHeatmapGradient = cached;
            this.progressHeatmapReady = true;
            return;
        }

        try {
            const response = await fetch(audioUrl);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const arrayBuffer = await response.arrayBuffer();
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;

            if (!AudioContextClass) {
                return;
            }

            const decodeContext = new AudioContextClass();
            const decoded = await decodeContext.decodeAudioData(arrayBuffer.slice(0));
            const rms = computeRmsBySecond(decoded, this.progressHeatmapStepSeconds);
            const smoothed = smoothValues(rms, this.progressHeatmapSmoothingRadius);
            const gradient = buildOrangeHeatGradient(smoothed);

            await decodeContext.close();

            if (!gradient || nonce !== this.heatmapNonce) {
                return;
            }

            progressHeatmapCache.set(cacheKey, gradient);
            this.progressHeatmapGradient = gradient;
            this.progressHeatmapReady = true;
        } catch {
            this.progressHeatmapGradient = '';
            this.progressHeatmapReady = false;
        }
    },

    /**
     * Attach the current Howler node to AudioMotion for visualization.
     *
     * @return {void}
     */
    bindAnalyzer() {
        if (!this.currentSound) {
            return;
        }

        const container = document.getElementById('wave');
        const sound = this.currentSound?._sounds?.find((item) => item?._id === this.soundId)
            || this.currentSound?._sounds?.[0]
            || null;

        /**
         * Prefer the live buffer source so waveform analysis stays independent
         * from the gain node that Howler uses for volume control.
         */
        const sourceNode = sound?._node || sound?._node?.bufferSource || null;
        const sourceContext = sourceNode?.context || null;

        if (!container || !sourceNode || !sourceContext || typeof sourceNode.connect !== 'function') {
            return;
        }

        const shouldRecreateAnalyzer = !this.audioMotion
            || this.audioMotion.isDestroyed
            || this.audioMotion.canvas?.parentElement !== container
            || this.analyzerAudioContext !== sourceContext;

        if (!shouldRecreateAnalyzer && this.audioMotion && this.analyzerSourceNode === sourceNode) {
            return;
        }

        if (shouldRecreateAnalyzer) {
            this.destroyAnalyzer();
            container.querySelectorAll('canvas').forEach((canvas) => {
                canvas.remove();
            });

            try {
                this.audioMotion = new AudioMotionAnalyzer(container, {
                    audioCtx: sourceContext,
                    connectSpeakers: false,
                    mode: 4,
                    alphaBars: false,
                    ansiBands: false,
                    barSpace: 0.25,
                    channelLayout: 'single',
                    colorMode: 'bar-level',
                    frequencyScale: 'log',
                    gradient: 'prism',
                    linearAmplitude: true,
                    linearBoost: 1.6,
                    maxFreq: 16000,
                    minFreq: 30,
                    reflexRatio: 0.5,
                    reflexAlpha: 1,
                    roundBars: true,
                    showPeaks: false,
                    showScaleX: false,
                    smoothing: 0.7,
                    weightingFilter: 'D',
                    overlay: true,
                    showBgColor: false,
                    maxDecibels: -30,
                });
                this.analyzerAudioContext = sourceContext;
            } catch {
                this.audioMotion = null;
                this.analyzerAudioContext = null;
                this.analyzerSourceNode = null;
                return;
            }
        } else {
            try {
                this.audioMotion.disconnectInput();
            } catch {
                // Ignore disconnect failures and try reconnecting anyway.
            }
        }

        try {
            this.audioMotion.connectInput(sourceNode);
            this.analyzerSourceNode = sourceNode;
        } catch {
            this.destroyAnalyzer();
            container.querySelectorAll('canvas').forEach((canvas) => {
                canvas.remove();
            });
        }
    },

    /**
     * Rebind the waveform analyzer after Howler replaces the active buffer source.
     *
     * Seeking while a track is playing recreates the underlying Web Audio node,
     * so the visualizer must reconnect to the new source after that swap.
     *
     * @return {void}
     */
    scheduleAnalyzerRebind() {
        if (this.analyzerRebindTimer) {
            window.clearTimeout(this.analyzerRebindTimer);
            this.analyzerRebindTimer = null;
        }

        if (!this.currentSound || !this.isPlaying) {
            return;
        }

        this.analyzerRebindTimer = window.setTimeout(() => {
            this.analyzerRebindTimer = null;
            this.bindAnalyzer();
        }, 0);
    },

    /**
     * Destroy the waveform analyzer.
     *
     * @return {void}
     */
    destroyAnalyzer() {
        if (this.analyzerRebindTimer) {
            window.clearTimeout(this.analyzerRebindTimer);
            this.analyzerRebindTimer = null;
        }

        this.analyzerAudioContext = null;
        this.analyzerSourceNode = null;

        if (!this.audioMotion) {
            return;
        }

        try {
            this.audioMotion.disconnectInput();
        } catch (error) {
            // Ignore disconnect failures while tearing down the analyzer.
        }

        try {
            this.audioMotion.destroy();
        } catch (error) {
            // Ignore destroy failures and continue clearing stale references.
        }

        this.audioMotion = null;
    },

    /**
     * Start syncing the playback position to the UI and storage.
     *
     * @return {void}
     */
    startProgressTimer() {
        this.stopProgressTimer();

        let saveCounter = 0;

        this.progressTimer = window.setInterval(() => {
            if (!this.currentSound || !this.isPlaying) {
                return;
            }

            const currentPosition = Number(this.currentSound.seek(this.soundId));
            this.currentTime = Number.isFinite(currentPosition) ? currentPosition : 0;
            saveCounter += 1;

            if (saveCounter >= 4) {
                this.savePlaybackState();
                saveCounter = 0;
            }
        }, 250);
    },

    /**
     * Stop the progress synchronization loop.
     *
     * @return {void}
     */
    stopProgressTimer() {
        if (!this.progressTimer) {
            return;
        }

        window.clearInterval(this.progressTimer);
        this.progressTimer = null;
    },

    /**
     * Start or resume playback.
     *
     * @return {void}
     */
    play() {
        if (!this.currentSound) {
            return;
        }

        if (this.showAutoplayConfirm) {
            this.clearAutoplayTimers();
            this.showAutoplayConfirm = false;
            this.pendingAutoplay = false;
        }

        if (this.soundId === null) {
            if (this.currentEpisode?.id) {
                sendAjaxMetric(metricActions.play, Number(this.currentEpisode.id)).then((response) => {
                    const count = response?.data?.count;

                    if (Number.isFinite(count)) {
                        updateMetricCountDom('.js-play-count', Number(this.currentEpisode.id), Number(count));
                    }
                }).catch(() => null);
            }

            this.soundId = this.currentSound.play();
        } else {
            this.currentSound.play(this.soundId);
        }

        this.currentSound.rate(this.playbackRate, this.soundId);
        this.currentSound.volume(this.isMuted ? 0 : this.volume, this.soundId);
        this.isPlaying = true;
        this.savePlaybackState();
    },

    /**
     * Pause the active track.
     *
     * @return {void}
     */
    pause() {
        if (!this.currentSound) {
            return;
        }

        this.currentSound.pause(this.soundId);
        this.isPlaying = false;
        this.savePlaybackState();
    },

    /**
     * Toggle between play and pause states.
     *
     * @return {void}
     */
    togglePlay() {
        if (this.isPlaying) {
            this.pause();
            return;
        }

        this.play();
    },

    /**
     * Seek to a specific playback position.
     *
     * @param {number|string} position The target position in seconds.
     * @return {void}
     */
    seek(position) {
        if (!this.currentSound) {
            return;
        }

        const nextPosition = Number.parseFloat(position);
        const safePosition = Number.isFinite(nextPosition) ? Math.max(0, nextPosition) : 0;

        this.currentSound.seek(safePosition, this.soundId);
        this.currentTime = safePosition;
        this.savePlaybackState();
    },

    /**
     * Set the output volume.
     *
     * @param {number|string} volume The requested volume between 0 and 1.
     * @return {void}
     */
    setVolume(volume) {
        const nextVolume = Math.min(1, Math.max(0, Number.parseFloat(volume)));
        this.volume = Number.isFinite(nextVolume) ? nextVolume : this.volume;
        this.isMuted = this.volume === 0;

        if (!this.isMuted) {
            this.lastVolume = this.volume;
        }

        if (this.currentSound) {
            this.currentSound.volume(this.isMuted ? 0 : this.volume, this.soundId);
        }

        safeLocalStorage.setItem(this.storageKeys.volume, String(this.volume));
        queueIconRefresh();
    },

    /**
     * Toggle the small volume panel.
     *
     * @return {void}
     */
    toggleVolumePanel() {
        this.volumePanelOpen = !this.volumePanelOpen;
    },

    /**
     * Toggle mute without losing the previous volume value.
     *
     * @return {void}
     */
    toggleMute() {
        if (this.isMuted) {
            this.setVolume(this.lastVolume || 0.8);
            return;
        }

        this.lastVolume = this.volume > 0 ? this.volume : this.lastVolume;
        this.setVolume(0);
    },

    /**
     * Toggle the playback rate selector.
     *
     * @return {void}
     */
    togglePlaybackRatePanel() {
        this.playbackRatePanelOpen = !this.playbackRatePanelOpen;
    },

    /**
     * Apply a new playback rate.
     *
     * @param {number} rate The target playback rate.
     * @return {void}
     */
    setPlaybackRate(rate) {
        if (!this.availableRates.includes(rate)) {
            return;
        }

        this.playbackRate = rate;

        if (this.currentSound && this.soundId !== null) {
            this.currentSound.rate(rate, this.soundId);
        }

        this.playbackRatePanelOpen = false;
        safeLocalStorage.setItem(this.storageKeys.playbackRate, String(rate));
    },

    /**
     * Show the autoplay confirmation bar and start its countdown.
     *
     * @return {void}
     */
    showAutoplayConfirmDialog() {
        this.clearAutoplayTimers();
        this.pendingAutoplay = true;
        this.showAutoplayConfirm = true;
        this.autoplayCountdown = 10;
        queueIconRefresh();

        this.autoplayCountdownTimer = window.setInterval(() => {
            this.autoplayCountdown -= 1;

            if (this.autoplayCountdown <= 0) {
                this.cancelAutoplay();
            }
        }, 1000);
    },

    /**
     * Clear timers used by the autoplay confirmation UI.
     *
     * @return {void}
     */
    clearAutoplayTimers() {
        if (!this.autoplayCountdownTimer) {
            return;
        }

        window.clearInterval(this.autoplayCountdownTimer);
        this.autoplayCountdownTimer = null;
    },

    /**
     * Resume playback after the user accepts the autoplay prompt.
     *
     * @return {void}
     */
    confirmAutoplay() {
        this.clearAutoplayTimers();
        this.showAutoplayConfirm = false;
        this.pendingAutoplay = false;
        this.play();
    },

    /**
     * Dismiss the autoplay prompt and persist the paused state.
     *
     * @return {void}
     */
    cancelAutoplay() {
        this.clearAutoplayTimers();
        this.showAutoplayConfirm = false;
        this.pendingAutoplay = false;
        this.isPlaying = false;
        this.savePlaybackState();
    },

    /**
     * Add an episode and immediately switch playback to it.
     *
     * @param {object} episode The episode payload.
     * @return {void}
     */
    addEpisode(episode) {
        const existingIndex = this.playlist.findIndex((item) => item.id === episode.id);

        if (existingIndex >= 0) {
            this.playByIndex(existingIndex);
            return;
        }

        this.playlist.push(episode);
        this.currentIndex = this.playlist.length - 1;
        this.currentEpisode = episode;
        this.savePlaylist();
        this.loadTrack(episode.audioUrl, { autoplay: true, restoreTime: 0 });
    },

    /**
     * Remove an episode from the playlist and keep the active index valid.
     *
     * @param {number} episodeId The episode ID to remove.
     * @return {void}
     */
    removeEpisode(episodeId) {
        const removedIndex = this.playlist.findIndex((item) => item.id === episodeId);
        if (removedIndex < 0) {
            return;
        }

        const isCurrentEpisode = removedIndex === this.currentIndex;
        this.playlist = this.playlist.filter((item) => item.id !== episodeId);

        if (this.playlist.length === 0) {
            this.clearPlaylist();
            return;
        }

        if (removedIndex < this.currentIndex) {
            this.currentIndex -= 1;
        } else if (isCurrentEpisode) {
            this.currentIndex = Math.min(this.currentIndex, this.playlist.length - 1);
            this.currentEpisode = this.playlist[this.currentIndex];
            this.loadTrack(this.currentEpisode.audioUrl, { autoplay: this.isPlaying, restoreTime: 0 });
        }

        this.currentEpisode = this.playlist[this.currentIndex] || null;
        this.savePlaylist();
    },

    /**
     * Clear the playlist and reset the entire player state.
     *
     * @return {void}
     */
    clearPlaylist() {
        this.stopAndReset();
        this.playlist = [];
        this.currentIndex = 0;
        this.currentEpisode = null;
        this.savePlaylist();
    },

    /**
     * Switch to the next episode in the playlist.
     *
     * @return {void}
     */
    playNext() {
        if (this.playlist.length === 0) {
            return;
        }

        this.currentIndex = (this.currentIndex + 1) % this.playlist.length;
        this.currentEpisode = this.playlist[this.currentIndex];
        this.savePlaylist();
        this.loadTrack(this.currentEpisode.audioUrl, { autoplay: true, restoreTime: 0 });
    },

    /**
     * Switch to the previous episode in the playlist.
     *
     * @return {void}
     */
    playPrevious() {
        if (this.playlist.length === 0) {
            return;
        }

        this.currentIndex = (this.currentIndex - 1 + this.playlist.length) % this.playlist.length;
        this.currentEpisode = this.playlist[this.currentIndex];
        this.savePlaylist();
        this.loadTrack(this.currentEpisode.audioUrl, { autoplay: true, restoreTime: 0 });
    },

    /**
     * Play the episode at the given playlist index.
     *
     * @param {number} index The playlist index to activate.
     * @return {void}
     */
    playByIndex(index) {
        if (index < 0 || index >= this.playlist.length) {
            return;
        }

        this.currentIndex = index;
        this.currentEpisode = this.playlist[index];
        this.savePlaylist();
        this.loadTrack(this.currentEpisode.audioUrl, { autoplay: true, restoreTime: 0 });
    },

    /**
     * Load the stored playlist and current index.
     *
     * @return {void}
     */
    loadPlaylist() {
        try {
            const rawPlaylist = safeLocalStorage.getItem(this.storageKeys.playlist);
            const rawIndex = safeLocalStorage.getItem(this.storageKeys.currentIndex);
            const parsedPlaylist = rawPlaylist ? JSON.parse(rawPlaylist) : [];

            this.playlist = Array.isArray(parsedPlaylist)
                ? parsedPlaylist.filter((episode) => episode?.id && episode?.audioUrl)
                : [];
            this.currentIndex = Math.max(0, Number.parseInt(rawIndex || '0', 10) || 0);
        } catch {
            this.playlist = [];
            this.currentIndex = 0;
        }
    },

    /**
     * Persist the playlist and refresh playlist drawer icons after DOM updates.
     *
     * @return {void}
     */
    savePlaylist() {
        safeLocalStorage.setItem(this.storageKeys.playlist, JSON.stringify(this.playlist));
        safeLocalStorage.setItem(this.storageKeys.currentIndex, String(this.currentIndex));
        queueIconRefresh();
    },

    /**
     * Load the stored volume and muted state.
     *
     * @return {void}
     */
    loadVolume() {
        const rawVolume = Number.parseFloat(safeLocalStorage.getItem(this.storageKeys.volume) || '');

        if (!Number.isFinite(rawVolume)) {
            return;
        }

        this.volume = Math.min(1, Math.max(0, rawVolume));
        this.lastVolume = this.volume > 0 ? this.volume : this.lastVolume;
        this.isMuted = this.volume === 0;
    },

    /**
     * Restore the stored playback rate when it matches an allowed option.
     *
     * @return {void}
     */
    loadPlaybackRate() {
        const rawRate = Number.parseFloat(safeLocalStorage.getItem(this.storageKeys.playbackRate) || '');

        if (this.availableRates.includes(rawRate)) {
            this.playbackRate = rawRate;
        }
    },

    /**
     * Save the current playback position.
     *
     * @return {void}
     */
    savePlaybackState() {
        safeLocalStorage.setItem(this.storageKeys.currentTime, String(this.currentTime));
        safeLocalStorage.setItem(this.storageKeys.isPlaying, this.isPlaying ? 'true' : 'false');
    },

    /**
     * Restore the persisted playback position and play intent.
     *
     * @return {{currentTime: number, isPlaying: boolean}}
     */
    loadPlaybackState() {
        const rawTime = Number.parseFloat(safeLocalStorage.getItem(this.storageKeys.currentTime) || '0');
        const currentTime = Number.isFinite(rawTime) ? Math.max(0, rawTime) : 0;
        const isPlaying = safeLocalStorage.getItem(this.storageKeys.isPlaying) === 'true';

        return { currentTime, isPlaying };
    },

    /**
     * Remove the persisted playback state.
     *
     * @return {void}
     */
    clearPlaybackState() {
        safeLocalStorage.removeItem(this.storageKeys.currentTime);
        safeLocalStorage.removeItem(this.storageKeys.isPlaying);
    },

    /**
     * Fully stop playback and reset transient player state.
     *
     * @return {void}
     */
    stopAndReset() {
        this.stopProgressTimer();
        this.destroyAnalyzer();
        this.clearAutoplayTimers();

        if (this.currentSound) {
            this.currentSound.stop();
            this.currentSound.unload();
            this.currentSound = null;
        }

        this.soundId = null;
        this.currentTime = 0;
        this.duration = 0;
        this.isLoading = false;
        this.isPlaying = false;
        this.showAutoplayConfirm = false;
        this.pendingAutoplay = false;
        this.autoplayCountdown = 10;
        this.progressHeatmapGradient = '';
        this.progressHeatmapReady = false;
        this.clearPlaybackState();
    },
});

/**
 * Return whether the current document is rendering the public theme shell.
 *
 * @return {boolean}
 */
function hasThemeRuntimeRoot() {
    return Boolean(document.querySelector('#swup-main'));
}

/**
 * Return whether the current document exposes the Swup containers used by the theme.
 *
 * @return {boolean}
 */
function hasSwupContainers() {
    return ['#swup-main', '#swup-header', '#swup-mobile-menu']
        .every((selector) => document.querySelector(selector));
}

/**
 * Return whether Swup can safely access the current document history object.
 *
 * @return {boolean}
 */
function canBootSwup() {
    if (!hasSwupContainers()) {
        return false;
    }

    try {
        return window.location.protocol !== 'about:' && window.location.href !== 'about:srcdoc';
    } catch (error) {
        return false;
    }
}

// Initialize Swup navigation for the partial containers used by the theme.
let swup = null;

if (canBootSwup()) {
    swup = new Swup({
        containers: ['#swup-main', '#swup-header', '#swup-mobile-menu'],
        animateHistoryBrowsing: true,
        plugins: [new SwupFormsPlugin(), new SwupScriptsPlugin()],
    });
}

/**
 * Run lightweight UI initializers after the initial load and Swup swaps.
 *
 * @return {void}
 */
function init() {
    renderLucideIcons(document);
    renderSimpleIcons(document);
    Alpine.store('player').init();
    syncCurrentPageAjaxContext();
    hydrateMetricsFromDom();
    maybeSendViewMetric();
}

if (hasThemeRuntimeRoot()) {
    Alpine.start();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
}

if (swup) {
    swup.hooks.on('content:replace', () => {
        renderLucideIcons(document);
        renderSimpleIcons(document);
    });

    swup.hooks.on('page:view', () => {
        syncCurrentPageAjaxContext();
        hydrateMetricsFromDom();
        maybeSendViewMetric();
    });
}
