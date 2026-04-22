<?php
/**
 * Theme footer template.
 */
?>
<footer class="text-center text-base-content/70 text-xs mt-4">
    <div class="max-w-screen-xl mx-auto py-4 pt-0">
        <?php if (is_active_sidebar('footer-links')): ?>
            <div class="grid md:[grid-template-columns:repeat(auto-fit,minmax(calc(25%-0.75rem),1fr))] grid-cols-2 justify-items-stretch gap-4 mb-4">
                <?php dynamic_sidebar('footer-links'); ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 grid-flow-row gap-2 md:justify-between bg-base-100/60 rounded-lg p-4 items-center">
            <div class="md:justify-self-start self-center">
                <?php
                echo wp_kses_post(
                    (
                        \ARippleSong\Themes\Daisy\ThemeOptions\General::getFooterCopyright() !== ''
                            ? \ARippleSong\Themes\Daisy\ThemeOptions\General::getFooterCopyright()
                            : sprintf(
                                __('© %1$s Powered by %2$s Theme', 'daisy-a-ripple-song'),
                                wp_date('Y'),
                                '<a href="' . esc_url((string) wp_get_theme()->get('ThemeURI')) . '" target="_blank" rel="noopener noreferrer" class="text-primary">' . esc_html((string) wp_get_theme()->get('Name')) . '</a>'
                            )
                    )
                );
                ?>
            </div>

            <?php if (\ARippleSong\Themes\Daisy\ThemeOptions\SocialLinks::hasLinks()): ?>
                <div class="md:justify-self-end self-center">
                    <ul class="flex justify-center gap-2">
                        <?php foreach (\ARippleSong\Themes\Daisy\ThemeOptions\SocialLinks::getConfiguredLinks() as $platformKey => $social): ?>
                            <li>
                                <a
                                    href="<?php echo esc_url($social['url']); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title="<?php echo esc_attr($social['label']); ?>"
                                    aria-label="<?php echo esc_attr($social['label']); ?>"
                                >
                                    <span
                                        data-simple-icon="<?php echo esc_attr($platformKey); ?>"
                                        data-simple-icon-label="<?php echo esc_attr($social['label']); ?>"
                                        class="inline-flex h-4 w-4 items-center justify-center"
                                    ></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</footer>
</div>
</div>
<?php get_template_part('resources/views/sections/autoplay-confirm'); ?>
<?php get_template_part('resources/views/sections/search-modal'); ?>
<?php get_template_part('resources/views/sections/sidebar-drawer'); ?>
<?php get_template_part('resources/views/sections/leftbar-drawer'); ?>
<?php wp_footer(); ?>
<label
    for="leftbar-drawer"
    class="fixed left-0 top-1/2 z-[99] -translate-y-1/2 cursor-pointer rounded-r-md bg-base-300/80 px-1 py-3 text-base-content/70 shadow-sm transition-all duration-200 hover:bg-base-300 hover:px-2 hover:text-base-content lg:hidden"
    aria-label="<?php echo esc_attr__('Open Left Sidebar', 'daisy-a-ripple-song'); ?>"
>
    <i data-lucide="chevron-right" class="h-3 w-3"></i>
</label>

<label
    for="sidebar-drawer"
    class="fixed right-0 top-1/2 z-[99] -translate-y-1/2 cursor-pointer rounded-l-md bg-base-300/80 px-1 py-3 text-base-content/70 shadow-sm transition-all duration-200 hover:bg-base-300 hover:px-2 hover:text-base-content md:hidden"
    aria-label="<?php echo esc_attr__('Open Right Sidebar', 'daisy-a-ripple-song'); ?>"
>
    <i data-lucide="chevron-left" class="h-3 w-3"></i>
</label>

<button
    x-data="{ show: false }"
    x-init="window.addEventListener('scroll', () => { show = window.scrollY > 300 })"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-4"
    @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
    class="btn btn-circle btn-primary fixed bottom-52 right-4 z-50 shadow-lg md:bottom-6"
    aria-label="<?php echo esc_attr__('Back to top', 'daisy-a-ripple-song'); ?>"
>
    <i data-lucide="arrow-up" class="h-5 w-5"></i>
</button>
</body>
</html>
