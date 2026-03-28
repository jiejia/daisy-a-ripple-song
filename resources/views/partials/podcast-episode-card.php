<?php
/**
 * Podcast Episode Card Partial
 *
 * Expected args:
 * - post_id: Episode post ID.
 * - audio_file: Episode audio file URL.
 * - episode_data: Player payload used by Alpine.
 * - title: Episode title.
 * - show_link: Whether the title should link to the single page.
 *
 * @var array<string, mixed> $args
 */
?>
<div class="rounded-lg bg-base-200/50 hover:bg-base-200">
    <div class="grid grid-cols-[95px_1fr_30px] items-center p-4">
        <div>
            <a href="<?php echo esc_url(get_permalink((int) ($args['post_id'] ?? 0))); ?>" class="relative block h-20 w-20 overflow-hidden rounded-lg">
                <?php if (has_post_thumbnail((int) ($args['post_id'] ?? 0))): ?>
                    <img src="<?php echo esc_url((string) get_the_post_thumbnail_url((int) ($args['post_id'] ?? 0), 'thumbnail')); ?>"
                         alt="<?php echo esc_attr((string) get_the_title((int) ($args['post_id'] ?? 0))); ?>"
                         class="h-20 w-20 rounded-md object-cover">
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-base-900/30">
                        <i data-lucide="podcast" class="h-5 w-5 text-base-100"></i>
                    </div>
                <?php else: ?>
                    <div class="flex h-20 w-20 items-center justify-center rounded-md bg-base-300/50">
                        <i data-lucide="podcast" class="h-5 w-5 text-base-content/70"></i>
                    </div>
                <?php endif; ?>
            </a>
        </div>
        <div class="grid grid-flow-row gap-1 overflow-hidden">
            <h4 class="line-clamp-2 text-md font-bold">
                <?php if (($args['show_link'] ?? true)): ?>
                    <a href="<?php echo esc_url(get_permalink((int) ($args['post_id'] ?? 0))); ?>">
                        <?php echo esc_html(html_entity_decode((string) ($args['title'] ?? ''))); ?>
                    </a>
                <?php else: ?>
                    <?php echo esc_html(html_entity_decode((string) ($args['title'] ?? ''))); ?>
                <?php endif; ?>
            </h4>
            <?php get_template_part('resources/views/partials/entry-meta', null, ['post_id' => (int) ($args['post_id'] ?? 0)]); ?>
        </div>
        <div class="flex gap-2">
            <?php if (!empty($args['audio_file'])): ?>
                <button type="button"
                        @click="
                            if ($store.player.currentEpisode && $store.player.currentEpisode.id === episode.id) {
                                if ($store.player.isPlaying) {
                                    $store.player.pause();
                                } else {
                                    $store.player.play();
                                }
                            } else {
                                $store.player.addEpisode(episode);
                            }
                        "
                        class="cursor-pointer transition-colors hover:text-primary"
                        :title="$store.player.currentEpisode && $store.player.currentEpisode.id === episode.id && $store.player.isPlaying ? '<?php echo esc_js(__('Pause', 'a-ripple-song')); ?>' : '<?php echo esc_js(__('Play', 'a-ripple-song')); ?>'">
                    <i data-lucide="pause"
                       class="h-4 text-xs"
                       x-show="$store.player.currentEpisode && $store.player.currentEpisode.id === episode.id && $store.player.isPlaying"></i>
                    <i data-lucide="play"
                       class="h-4 text-xs"
                       x-show="!($store.player.currentEpisode && $store.player.currentEpisode.id === episode.id && $store.player.isPlaying)"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
