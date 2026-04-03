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
                    <li x-data="{ episode: <?php echo esc_attr(wp_json_encode($episode['player_payload'])); ?> }">
                        <?php
                        get_template_part('resources/views/partials/podcast-episode-card', null, [
                            'post_id' => (int) $episode['id'],
                            'audio_file' => (string) ($episode['player_payload']['audioUrl'] ?? ''),
                            'episode_data' => $episode['player_payload'],
                            'title' => (string) $episode['title'],
                            'show_link' => true,
                        ]);
                        ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="py-8 text-center text-base-content/50"><?php esc_html_e('No ARS Episode content', 'a-ripple-song'); ?></li>
            <?php endif; ?>
        </ul>
    <?php endforeach; ?>
</div>
