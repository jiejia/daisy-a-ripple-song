import Swup from 'swup';
import { createIcons, icons } from 'lucide';
import { Howl, Howler } from 'howler';
import AudioMotionAnalyzer from 'audiomotion-analyzer';
import Alpine from 'alpinejs'
import SwupFormsPlugin from '@swup/forms-plugin';
import SwupScriptsPlugin from '@swup/scripts-plugin';

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

// 初始化 Swup (v4.x 版本)
const swup = new Swup({
    containers: ['#swup-main', '#swup-header', '#swup-mobile-menu'], // 指定要替换的容器
    animateHistoryBrowsing: true,
    // 让 Swup 在无刷新切换后重新执行页面内联脚本
    plugins: [new SwupFormsPlugin(), new SwupScriptsPlugin()]
});


function init() {
    // 重新初始化 Lucide 图标
    createIcons({ icons });

}

// 页面首次加载
document.addEventListener('DOMContentLoaded', init);


// Swup v4.x 使用 hooks API
swup.hooks.on('content:replace', init);