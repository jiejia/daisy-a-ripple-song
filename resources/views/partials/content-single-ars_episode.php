<?php
/**
 * Single Episode Content Partial
 *
 * Expected args:
 * - post_id: Episode post ID.
 * - title: Optional episode title override.
 *
 * @var array<string, mixed> $args
 */

/**
 * Resolve the episode post ID from the supplied args or the current loop item.
 *
 * @var int $postId
 */
$postId = (int) ($args['post_id'] ?? get_the_ID());

/**
 * Resolve the episode audio file with support for both public and underscored meta keys.
 *
 * @var string $audioFile
 */
$audioFile = (string) get_post_meta($postId, 'audio_file', true);

if ($audioFile === '') {
    $audioFile = (string) get_post_meta($postId, '_audio_file', true);
}

/**
 * Build the frontend player payload for Alpine state.
 *
 * @var array<string, mixed> $episodeData
 */
$episodeData = \ARippleSong\Themes\Daisy\Core\Helper::getEpisodeData($postId);

/**
 * Resolve the title shown in the episode card.
 *
 * @var string $title
 */
$title = (string) ($args['title'] ?? get_the_title($postId));
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('rounded-lg bg-base-100 p-4'); ?> x-data="<?php echo esc_attr((string) wp_json_encode(['episode' => $episodeData], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>">
    <div class="grid grid-flow-row gap-2">
        <?php get_template_part('resources/views/partials/podcast-episode-card', null, [
            'post_id' => $postId,
            'audio_file' => $audioFile,
            'episode_data' => $episodeData,
            'title' => $title,
            'show_link' => false,
        ]); ?>
        <div class="entry-content max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md">
            <?php the_content(); ?>
        </div>
        <?php
        wp_link_pages([
            'before' => '<nav class="page-links mt-4 flex flex-wrap items-center gap-2 text-sm"><span class="font-semibold">' . esc_html__('Pages:', 'daisy-a-ripple-song') . '</span>',
            'after' => '</nav>',
            'link_before' => '<span class="btn btn-xs btn-outline">',
            'link_after' => '</span>',
        ]);
        ?>
        <?php get_template_part('resources/views/partials/entry-tags', null, ['post_id' => $postId]); ?>
        <?php get_template_part('resources/views/partials/entry-authors', null, ['post_id' => $postId]); ?>
    </div>
</article>
<?php comments_template(); ?>
