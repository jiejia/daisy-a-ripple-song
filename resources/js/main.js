import Swup from 'swup';
import { createIcons, icons } from 'lucide';
import { Howl, Howler } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import Alpine from 'alpinejs'

createIcons({ icons });

window.Alpine = Alpine

// Theme Store
Alpine.store('theme', {
    mode: 'auto', // 'light', 'dark', 'auto'
    modes: ['light', 'dark', 'auto'],
    storageKey: 'theme-mode',
    lightTheme: 'retro',
    darkTheme: 'dim',
    current: 'retro',

    init() {
        let mode = localStorage.getItem(this.storageKey);
        if (!this.modes.includes(mode)) mode = 'auto';
        this.mode = mode;
    },

    toggle() {
        this.mode = this.mode === 'light' ? 'dark' : this.mode === 'dark' ? 'auto' : 'light';
        localStorage.setItem(this.storageKey, this.mode);
    },

    get current() {
        if (this.mode === 'light') return this.lightTheme;
        if (this.mode === 'dark') return this.darkTheme;

        // auto 模式：读取系统偏好
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        return prefersDark ? this.darkTheme : this.lightTheme;
    },

    get isDark() {
        return this.mode === 'dark';
    },

    get isLight() {
        return this.mode === 'light';
    },

    get isAuto() {
        return this.mode === 'auto';
    }
});


Alpine.start()

