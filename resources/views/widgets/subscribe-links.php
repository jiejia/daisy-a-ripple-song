<?php

/**
 * Subscribe Links Widget Template
 *
 * @var string               $title
 * @var array<string, string> $links
 */
?>
<?php if (!empty(array_filter($links))): ?>
    <div class="rounded-lg bg-base-100 p-4">
        <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
        <div class="mt-2 grid grid-flow-row gap-2">
            <?php if (!empty($links['apple'])): ?>
                <a href="<?php echo esc_url($links['apple']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm border-black bg-gradient-to-r from-gray-600 via-gray-800 to-black text-white transition-all duration-500 ease-in-out hover:from-black hover:via-gray-800 hover:to-gray-600">
                    <span data-simple-icon="applepodcasts" data-simple-icon-label="<?php echo esc_attr__('Apple Podcast', 'a-ripple-song'); ?>" class="inline-flex h-4 w-4 items-center justify-center"></span>
                    <?php esc_html_e('Apple Podcast', 'a-ripple-song'); ?>
                </a>
            <?php endif; ?>

            <?php if (!empty($links['spotify'])): ?>
                <a href="<?php echo esc_url($links['spotify']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm border-[#00b544] bg-gradient-to-r from-green-400 via-green-500 to-[#03C755] text-white transition-all duration-500 ease-in-out hover:from-[#03C755] hover:via-green-500 hover:to-green-400">
                    <span data-simple-icon="spotify" data-simple-icon-label="<?php echo esc_attr__('Spotify', 'a-ripple-song'); ?>" class="inline-flex h-4 w-4 items-center justify-center"></span>
                    <?php esc_html_e('Spotify', 'a-ripple-song'); ?>
                </a>
            <?php endif; ?>

            <?php if (!empty($links['youtube'])): ?>
                <a href="<?php echo esc_url($links['youtube']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm border-[#f1d800] bg-gradient-to-r from-yellow-300 via-yellow-400 to-[#FEE502] text-[#181600] transition-all duration-500 ease-in-out hover:from-[#FEE502] hover:via-yellow-400 hover:to-yellow-300">
                    <span data-simple-icon="youtubemusic" data-simple-icon-label="<?php echo esc_attr__('YouTube Music', 'a-ripple-song'); ?>" class="inline-flex h-4 w-4 items-center justify-center"></span>
                    <?php esc_html_e('YouTube Music', 'a-ripple-song'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
