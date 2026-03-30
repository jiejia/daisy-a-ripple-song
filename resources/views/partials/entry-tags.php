<?php
/**
 * Entry Tags Partial
 *
 * Expected args:
 * - post_id: Post ID used to render the tag list.
 *
 * @var array<string, mixed> $args
 */
?>
<?php if ($tags = get_the_tags((int) ($args['post_id'] ?? get_the_ID()))): ?>
<div class="mt-2 grid grid-flow-row gap-2">
    <ul class="flex flex-wrap gap-2">
        <?php foreach ($tags as $tag): ?>
        <li>
            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="rounded-full bg-base-200/50 px-2 py-0.5 text-xs text-base-content/50 hover:bg-base-200"># <?php echo esc_html($tag->name); ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
