<?php
/**
 * Podcast List Widget Template
 *
 * @var string                                      $title
 * @var bool                                        $showSeeAll
 * @var string                                      $archiveUrl
 * @var array<string, array<int, array<string, mixed>>> $tabs
 */
?>
<div class="" x-data="{ activeTab: 'recent' }">
    <div class="grid grid-cols-[1fr_auto] items-center gap-2">
        <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
        <?php if ($showSeeAll): ?>
            <span class="text-xs text-base-content/70">
                <a href="<?php echo esc_url($archiveUrl); ?>"><?php esc_html_e('See all', 'a-ripple-song'); ?></a>
            </span>
        <?php endif; ?>
    </div>

    <ul class="mt-3 flex gap-2">
        <li><button type="button" @click="activeTab = 'recent'" :class="activeTab === 'recent' ? 'bg-base-200' : 'bg-base-100'" class="btn btn-sm rounded-full"><?php esc_html_e('Recent', 'a-ripple-song'); ?></button></li>
        <li><button type="button" @click="activeTab = 'popular'" :class="activeTab === 'popular' ? 'bg-base-200' : 'bg-base-100'" class="btn btn-sm rounded-full"><?php esc_html_e('Popular', 'a-ripple-song'); ?></button></li>
        <li><button type="button" @click="activeTab = 'random'" :class="activeTab === 'random' ? 'bg-base-200' : 'bg-base-100'" class="btn btn-sm rounded-full"><?php esc_html_e('Random', 'a-ripple-song'); ?></button></li>
    </ul>

    <?php foreach ($tabs as $tabKey => $episodes): ?>
        <ul class="mt-4 grid gap-y-4" x-show="activeTab === <?php echo esc_attr(wp_json_encode($tabKey)); ?>" <?php echo $tabKey === 'recent' ? '' : 'style="display: none;"'; ?>>
            <?php if (!empty($episodes)): ?>
                <?php foreach ($episodes as $episode): ?>
                    <li class="rounded-lg bg-base-200/40 p-3 transition-colors hover:bg-base-200/60">
                        <div class="grid grid-cols-[64px_1fr_auto] items-center gap-3">
                            <div class="relative h-16 w-16 overflow-hidden rounded-md bg-base-300/60">
                                <?php if (!empty($episode['featured_image'])): ?>
                                    <img src="<?php echo esc_url((string) $episode['featured_image']); ?>" alt="<?php echo esc_attr((string) $episode['title']); ?>" class="h-full w-full object-cover">
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center">
                                        <i data-lucide="podcast" class="h-6 w-6 text-base-content/60"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="min-w-0">
                                <a href="<?php echo esc_url((string) $episode['permalink']); ?>" class="line-clamp-2 text-sm font-semibold">
                                    <?php echo esc_html((string) $episode['title']); ?>
                                </a>
                                <p class="mt-1 text-xs text-base-content/60"><?php echo esc_html((string) $episode['date']); ?></p>
                                <?php if (!empty($episode['description'])): ?>
                                    <p class="mt-1 line-clamp-2 text-xs text-base-content/50"><?php echo esc_html((string) $episode['description']); ?></p>
                                <?php endif; ?>
                            </div>

                            <button type="button"
                                    class="btn btn-sm btn-primary"
                                    @click='$store.player.addEpisode(<?php echo esc_attr(wp_json_encode($episode['player_payload'])); ?>)'>
                                <i data-lucide="play" class="h-4 w-4"></i>
                                <?php esc_html_e('Play', 'a-ripple-song'); ?>
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="py-8 text-center text-base-content/50"><?php esc_html_e('No ARS Episode content', 'a-ripple-song'); ?></li>
            <?php endif; ?>
        </ul>
    <?php endforeach; ?>
</div>
