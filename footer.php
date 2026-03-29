<?php
/**
 * Theme footer template.
 */
?>
<footer class="text-center text-base-content/70 text-xs">
    <div class="max-w-screen-xl mx-auto py-4 pt-0">
        <?php if (is_active_sidebar('footer-links')): ?>
            <div class="grid md:[grid-template-columns:repeat(auto-fit,minmax(calc(25%-0.75rem),1fr))] grid-cols-2 justify-items-stretch gap-4 mb-4">
                <?php dynamic_sidebar('footer-links'); ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 grid-flow-row gap-2 md:justify-between bg-base-100/60 rounded-lg p-4">
            <div class="md:justify-self-start">
                <?php
                echo wp_kses_post(
                    (
                        \App\ThemeOptions\General::getFooterCopyright() !== ''
                            ? \App\ThemeOptions\General::getFooterCopyright()
                            : sprintf(
                                __('© %1$s Powered by %2$s Theme', 'a-ripple-song'),
                                date_i18n('Y'),
                                '<a href="https://github.com/jiejia/a-ripple-song" target="_blank" rel="noopener noreferrer" class="text-primary">A Ripple Song</a>'
                            )
                    )
                );
                ?>
            </div>

            <?php if (\App\ThemeOptions\SocialLinks::hasLinks()): ?>
                <div class="md:justify-self-end">
                    <ul class="flex justify-center gap-2">
                        <?php foreach (\App\ThemeOptions\SocialLinks::getConfiguredLinks() as $platformKey => $social): ?>
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
<?php wp_footer(); ?>
</body>
</html>
