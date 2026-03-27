<?php
/**
 * Store translated labels for the theme mode controls.
 *
 * @var array<string, string> $theme_mode_labels
 */
$theme_mode_labels = [
    'light'  => __('Light Mode', 'a-ripple-song'),
    'dark'   => __('Dark Mode', 'a-ripple-song'),
    'system' => __('Follow System', 'a-ripple-song'),
    'toggle' => __('Toggle Theme', 'a-ripple-song'),
];
?>
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
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-2" title="<?php bloginfo('description'); ?>">
                                <i data-lucide="podcast" class="w-6 h-6"></i>
                                <span class="text-2xl bg-gradient-to-r from-base-content/40 via-base-content/70 to-base-content bg-clip-text text-transparent transition-all duration-500 ease-in-out hover:from-base-content hover:via-base-content/70 hover:to-base-content/40"><?php bloginfo('name'); ?></span>
                            </a>
                        </h1>
                        <?php get_template_part('resources/views/sections/primary-navigation'); ?>
                        <div class="grid grid-flow-col justify-end gap-2 place-items-center">
                            <label for="search-modal" class="md:hidden block">
                                <i data-lucide="search" class="w-5 h-5"></i>
                            </label>
                            <!-- 主题循环切换按钮 -->
                            <button type="button" class="btn btn-ghost btn-sm btn-circle" @click="$store.theme.toggle()" :title="$store.theme.mode === 'light' ? <?php echo esc_attr(wp_json_encode($theme_mode_labels['light'])); ?> : ($store.theme.mode === 'dark' ? <?php echo esc_attr(wp_json_encode($theme_mode_labels['dark'])); ?> : <?php echo esc_attr(wp_json_encode($theme_mode_labels['system'])); ?>)" title="<?php echo esc_attr($theme_mode_labels['system']); ?>">
                                <i data-lucide="sun" class="w-5 h-5" x-show="$store.theme.isLight"></i>
                                <i data-lucide="moon" class="w-5 h-5" x-show="$store.theme.isDark && !$store.theme.isAuto"></i>
                                <i data-lucide="sun-moon" class="w-5 h-5" x-show="$store.theme.isAuto"></i>
                                <span class="sr-only"><?php echo esc_html($theme_mode_labels['toggle']); ?></span>
                            </button>

                            <label for="mobile-menu" class="xl:hidden block">
                                <i data-lucide="menu" class="w-5 h-5 cursor-pointer"></i>
                            </label>

                        </div>
                    </div>
                </div>
            </div>
        </header>
        <?php get_template_part('resources/views/sections/mobile-menu'); ?>
        <div class="max-w-screen-xl mx-auto h-full">
