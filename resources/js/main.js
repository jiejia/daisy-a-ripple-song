import Swup from 'swup';
import { createIcons, icons } from 'lucide';
import { Howl } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import Alpine from 'alpinejs';
import SwupFormsPlugin from '@swup/forms-plugin';
import SwupScriptsPlugin from '@swup/scripts-plugin';

window.Alpine = Alpine;

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
    } catch (error) {
        console.warn(`[aripplesong] ${type} is unavailable, falling back to memory storage.`, error);
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
    const query = 'per_page=5&orderby=date&order=desc&_embed=1';
    const separator = normalizedRoot.includes('?') ? '&' : '?';

    return `${normalizedRoot}wp/v2/ars_episode${separator}${query}`;
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

/**
 * Queue a Lucide refresh after Alpine mutates the DOM.
 *
 * @return {void}
 */
function queueIconRefresh() {
    window.requestAnimationFrame(() => {
        createIcons({ icons });
    });
}

// Theme Store
Alpine.store('theme', {
    mode: 'auto',
    modes: ['light', 'dark', 'auto'],
    storageKey: 'theme-mode',
    lightTheme: 'retro',
    darkTheme: 'dim',
    current: 'retro',

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

        const savedTime = Number.parseFloat(safeLocalStorage.getItem(this.storageKeys.currentTime) || '0');
        this.currentTime = Number.isFinite(savedTime) ? Math.max(0, savedTime) : 0;

        this.loadTrack(this.currentEpisode.audioUrl, { autoplay: false, restoreTime: this.currentTime });
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
        } catch (error) {
            console.warn('[aripplesong] Failed to fetch latest episodes.', error);
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
            onpause: () => {
                this.isPlaying = false;
                this.stopProgressTimer();
                this.saveCurrentTime();
            },
            onstop: () => {
                this.isPlaying = false;
                this.stopProgressTimer();
            },
            onend: () => {
                this.playNext();
            },
            onloaderror: (_, error) => {
                this.isLoading = false;
                console.warn('[aripplesong] Failed to load audio.', error);
            },
            onplayerror: (_, error) => {
                this.isPlaying = false;
                console.warn('[aripplesong] Failed to start playback.', error);
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
        } catch (error) {
            console.warn('[aripplesong] Failed to generate progress heatmap.', error);
        }
    },

    /**
     * Attach the current Howler node to AudioMotion for visualization.
     *
     * @return {void}
     */
    bindAnalyzer() {
        if (this.audioMotion || !this.currentSound) {
            return;
        }

        const container = document.getElementById('wave');
        const sourceNode = this.currentSound?._sounds?.[0]?._node;

        if (!container || !sourceNode) {
            return;
        }

        this.audioMotion = new AudioMotionAnalyzer(container, {
            source: sourceNode,
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
    },

    /**
     * Destroy the waveform analyzer.
     *
     * @return {void}
     */
    destroyAnalyzer() {
        if (!this.audioMotion) {
            return;
        }

        this.audioMotion.destroy();
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
                this.saveCurrentTime();
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

        if (this.soundId === null) {
            this.soundId = this.currentSound.play();
        } else {
            this.currentSound.play(this.soundId);
        }

        this.currentSound.rate(this.playbackRate, this.soundId);
        this.currentSound.volume(this.isMuted ? 0 : this.volume, this.soundId);
        this.isPlaying = true;
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
        this.saveCurrentTime();
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
        } catch (error) {
            console.warn('[aripplesong] Failed to restore playlist from storage.', error);
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
    saveCurrentTime() {
        safeLocalStorage.setItem(this.storageKeys.currentTime, String(this.currentTime));
    },

    /**
     * Fully stop playback and reset transient player state.
     *
     * @return {void}
     */
    stopAndReset() {
        this.stopProgressTimer();
        this.destroyAnalyzer();

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
        this.progressHeatmapGradient = '';
        this.progressHeatmapReady = false;
        safeLocalStorage.removeItem(this.storageKeys.currentTime);
    },
});

Alpine.start();

// Initialize Swup navigation for the partial containers used by the theme.
const swup = new Swup({
    containers: ['#swup-main', '#swup-header', '#swup-mobile-menu'],
    animateHistoryBrowsing: true,
    plugins: [new SwupFormsPlugin(), new SwupScriptsPlugin()],
});

/**
 * Run lightweight UI initializers after the initial load and Swup swaps.
 *
 * @return {void}
 */
function init() {
    createIcons({ icons });
    Alpine.store('player').init();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
} else {
    init();
}

swup.hooks.on('content:replace', () => {
    createIcons({ icons });
});
