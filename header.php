<!DOCTYPE html>
<html <?php language_attributes(); ?> class="bg-base-200" x-data x-init="$store.theme.init()" :data-theme="$store.theme.current">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-base-200'); ?>>
    <?php wp_body_open(); ?>
    <div id="app" class="p-4 gap-4">
        <header class="fixed top-0 h-[55px] left-0 right-0 z-100 bg-base-100/75 transition-fade" id="swup-header">
            <div class="max-w-screen-xl mx-auto h-full">
                <div class="xl:px-0 px-4 py-3">
                    <div class="grid xl:grid-cols-[220px_1fr_300px] grid-cols-[220px_1fr] gap-4">
                        <h1 class="text-2xl font-bold text-center">
                            <a href="https://podcast.aripplesong.me/" class="flex items-center gap-2">
                                <i data-lucide="podcast" class="w-6 h-6"></i>
                                <span class="text-2xl bg-gradient-to-r from-base-content/40 via-base-content/70 to-base-content bg-clip-text text-transparent transition-all duration-500 ease-in-out hover:from-base-content hover:via-base-content/70 hover:to-base-content/40">A Ripple Song</span>
                            </a>
                        </h1>
                        <ul class="xl:grid hidden grid-flow-col gap-2 text-md justify-center" id="menu-1">

                            <li>
                                <a href="https://podcast.aripplesong.me/" class="grid place-items-center h-full w-full text-center px-4 rounded-lg text-base-content font-semibold bg-base-200/50" data-pjax="">
                                    Home
                                </a>
                            </li>
                            <li>
                                <a href="https://podcast.aripplesong.me/episodes/" class="grid place-items-center h-full w-full text-center px-4 rounded-lg text-base-content/80 hover:text-base-content" data-pjax="">
                                    Episodes
                                </a>
                            </li>
                            <li>
                                <a href="https://podcast.aripplesong.me/blog/" class="grid place-items-center h-full w-full text-center px-4 rounded-lg text-base-content/80 hover:text-base-content" data-pjax="">
                                    Blog
                                </a>
                            </li>
                        </ul>
                        <div class="grid grid-flow-col justify-end gap-2 place-items-center">
                            <label for="search-modal" class="md:hidden block">
                                <i data-lucide="search" class="w-5 h-5"></i>
                            </label>
                            <!-- 主题循环切换按钮 -->
                            <button type="button" class="btn btn-ghost btn-sm btn-circle" @click="$store.theme.toggle()" :title="$store.theme.mode === 'light' ? 'Light Mode' : ($store.theme.mode === 'dark' ? 'Dark Mode' : 'Follow System')" title="Follow System">
                                <i data-lucide="sun" class="w-5 h-5" x-show="$store.theme.isLight"></i>
                                <i data-lucide="moon" class="w-5 h-5" x-show="$store.theme.isDark && !$store.theme.isAuto""></i>
                                <i data-lucide=" sun-moon" class="w-5 h-5" x-show="$store.theme.isAuto"></i>
                                <span class="sr-only">Toggle Theme</span>
                            </button>

                            <label for="mobile-menu" class="xl:hidden block">
                                <i data-lucide="menu" class="w-5 h-5"></i>
                            </label>

                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="max-w-screen-xl mx-auto h-full">
