<?php
/**
 * Entry Meta Partial
 *
 * Expected args:
 * - post_id: Post ID used to render the meta line.
 *
 * @var array<string, mixed> $args
 */
?>
<p class="text-xs text-base-content/50">
    <time class="dt-published" datetime="<?php echo esc_attr(get_post_time('c', true, (int) ($args['post_id'] ?? 0))); ?>">
        <?php echo esc_html(get_the_date('', (int) ($args['post_id'] ?? 0))); ?>
    </time>
    <?php if (IS_PODCAST_PLUGIN_INSTALLED): ?>
        <span class="ml-2">
            · <span class="js-views-count" data-post-id="<?php echo esc_attr((string) ((int) ($args['post_id'] ?? 0))); ?>" data-post-type="<?php echo esc_attr((string) get_post_type((int) ($args['post_id'] ?? 0))); ?>"><?php echo esc_html(number_format_i18n((int) get_post_meta((int) ($args['post_id'] ?? 0), '_views_count', true))); ?></span> <?php esc_html_e('views', 'a-ripple-song'); ?>
            <?php if (get_post_type((int) ($args['post_id'] ?? 0)) === \App\Constants\PodcastPluginConstant::PODCAST_POST_TYPE): ?>
                · <span class="js-play-count" data-post-id="<?php echo esc_attr((string) ((int) ($args['post_id'] ?? 0))); ?>" data-post-type="<?php echo esc_attr((string) get_post_type((int) ($args['post_id'] ?? 0))); ?>"><?php echo esc_html(number_format_i18n((int) get_post_meta((int) ($args['post_id'] ?? 0), '_play_count', true))); ?></span> <?php esc_html_e('plays', 'a-ripple-song'); ?>
            <?php endif; ?>
        </span>
    <?php endif; ?>
</p>
