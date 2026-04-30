<?php
/**
 * Store translated labels for the theme mode controls.
 *
 * @var array<string, string> $theme_mode_labels
 */
$theme_mode_labels = [
    'light'  => __('Light Mode', 'daisy-a-ripple-song'),
    'dark'   => __('Dark Mode', 'daisy-a-ripple-song'),
    'system' => __('Follow System', 'daisy-a-ripple-song'),
    'toggle' => __('Toggle Theme', 'daisy-a-ripple-song'),
];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="bg-base-200" data-theme="<?php echo esc_attr(\ARippleSong\Themes\Daisy\ThemeOptions\General::getLightTheme()); ?>" x-data x-init="$store.theme.init()" :data-theme="$store.theme.current">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-base-200'); ?>>
    <?php wp_body_open(); ?>
    <a class="skip-link screen-reader-text" href="#swup-main">
        <?php esc_html_e('Skip to content', 'daisy-a-ripple-song'); ?>
    </a>
    <div id="app" class="p-4 gap-4 mb-[<?php echo IS_PODCAST_PLUGIN_ACTIVATED ? '180px' : '0px'; ?>] md:mb-0">
        <header class="fixed top-0 h-[55px] left-0 right-0 z-50 bg-base-100/75 transition-fade z-[100]" id="swup-header">
            <div class="max-w-screen-xl mx-auto h-full">
                <div class="xl:px-0 px-4 py-3">
                    <div class="grid xl:grid-cols-[220px_minmax(0,1fr)_300px] grid-cols-[220px_minmax(0,1fr)] gap-4">
                        <h1 class="min-w-0 text-2xl font-bold text-center">
                            <?php if (\ARippleSong\Themes\Daisy\ThemeOptions\General::getSiteLogoUrl() !== ''): ?>
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex min-w-0 items-center gap-2" title="<?php bloginfo('description'); ?>">
                                    <img src="<?php echo esc_url(\ARippleSong\Themes\Daisy\ThemeOptions\General::getSiteLogoUrl()); ?>" alt="<?php bloginfo('name'); ?>" class="h-8 w-auto max-w-[220px] object-contain">
                                </a>
                            <?php else: ?>
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="flex min-w-0 items-center gap-2" title="<?php bloginfo('description'); ?>">
                                    <i data-lucide="podcast" class="w-6 h-6"></i>
                                    <span class="min-w-0 text-2xl bg-gradient-to-r from-base-content/40 via-base-content/70 to-base-content bg-clip-text text-transparent transition-all duration-500 ease-in-out hover:from-base-content hover:via-base-content/70 hover:to-base-content/40"><?php bloginfo('name'); ?></span>
                                </a>
                            <?php endif; ?>
                        </h1>
                        <?php get_template_part('resources/views/sections/primary-navigation'); ?>
                        <div class="grid grid-flow-col justify-end gap-2 place-items-center">
                            <button type="button" class="btn btn-ghost btn-sm btn-circle md:hidden  inline-flex items-center justify-center" onclick="search_modal.showModal()" aria-label="<?php echo esc_attr__('Open search', 'daisy-a-ripple-song'); ?>">
                                <i data-lucide="search" class="w-5 h-5"></i>
                            </button>
                            <!-- 主题循环切换按钮 -->
                            <button type="button" class="btn btn-ghost btn-sm btn-circle" @click="$store.theme.toggle()" :title="$store.theme.mode === 'light' ? <?php echo esc_attr(wp_json_encode($theme_mode_labels['light'])); ?> : ($store.theme.mode === 'dark' ? <?php echo esc_attr(wp_json_encode($theme_mode_labels['dark'])); ?> : <?php echo esc_attr(wp_json_encode($theme_mode_labels['system'])); ?>)" title="<?php echo esc_attr($theme_mode_labels['system']); ?>">
                                <i data-lucide="sun" class="w-5 h-5" x-show="$store.theme.isLight"></i>
                                <i data-lucide="moon" class="w-5 h-5" x-show="$store.theme.isDark && !$store.theme.isAuto"></i>
                                <i data-lucide="sun-moon" class="w-5 h-5" x-show="$store.theme.isAuto"></i>
                                <span class="sr-only"><?php echo esc_html($theme_mode_labels['toggle']); ?></span>
                            </button>

                            <button type="button" class="btn btn-ghost btn-sm btn-circle xl:hidden" onclick="document.getElementById('mobile-menu').checked = true" aria-label="<?php echo esc_attr__('Open menu', 'daisy-a-ripple-song'); ?>">
                                <i data-lucide="menu" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <?php get_template_part('resources/views/sections/mobile-menu'); ?>
        <?php get_template_part('resources/views/sections/playlist-drawer'); ?>
        <div class="max-w-screen-xl mx-auto h-full">
