<?php
/**
 * Store translated labels used in the footer widgets.
 *
 * @var array<string, string> $footer_labels
 */
$footer_labels = [
    'link_1'          => __('Link 1', 'a-ripple-song'),
    'link_2'          => __('Link 2', 'a-ripple-song'),
    'link_3'          => __('Link 3', 'a-ripple-song'),
    'link_4'          => __('Link 4', 'a-ripple-song'),
    'powered_by'      => __('© %1$s Powered by %2$s Theme', 'a-ripple-song'),
    'theme_name'      => __('A Ripple Song', 'a-ripple-song'),
];
?>
<footer class="text-center text-base-content/70 text-xs mt-4">
    <div class="grid md:[grid-template-columns:repeat(auto-fit,minmax(calc(25%-0.75rem),1fr))] grid-cols-2 justify-items-stretch gap-4 mb-4">
        <div class="text-left bg-base-100/60 rounded-lg p-4">
            <h4 class="text-base-content/70 text-lg font-bold mb-2"><?php echo esc_html($footer_labels['link_1']); ?></h4>

        </div>

        <div class="text-left bg-base-100/60 rounded-lg p-4">
            <h4 class="text-base-content/70 text-lg font-bold mb-2"><?php echo esc_html($footer_labels['link_2']); ?></h4>

        </div>

        <div class="text-left bg-base-100/60 rounded-lg p-4">
            <h4 class="text-base-content/70 text-lg font-bold mb-2"><?php echo esc_html($footer_labels['link_3']); ?></h4>

        </div>

        <div class="text-left bg-base-100/60 rounded-lg p-4">
            <h4 class="text-base-content/70 text-lg font-bold mb-2"><?php echo esc_html($footer_labels['link_4']); ?></h4>

        </div>

    </div>

    <div class="grid md:grid-cols-2 grid-flow-row gap-2 md:justify-between bg-base-100/60 rounded-lg p-4">
        <div class="md:justify-self-start"><?php echo wp_kses_post(sprintf($footer_labels['powered_by'], '2026', '<a href="https://github.com/jiejia/a-ripple-song" target="_blank" class="text-primary">' . esc_html($footer_labels['theme_name']) . '</a>')); ?></div>
    </div>
</footer>
</div>
</div>
<?php get_template_part('resources/views/sections/mobile-menu'); ?>
<?php get_template_part('resources/views/sections/playlist-drawer'); ?>
<?php get_template_part('resources/views/sections/autoplay-confirm'); ?>
<?php wp_footer(); ?>
</body>

</html>
