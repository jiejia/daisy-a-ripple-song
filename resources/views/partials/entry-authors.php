<?php
/**
 * Entry Authors Partial
 *
 * Expected args:
 * - post_id: Post ID used to render the author avatars.
 *
 * @var array<string, mixed> $args
 */
?>
<?php if ($authorIds = \ARippleSong\Themes\Daisy\Core\Helper::getPostAllAuthors((int) ($args['post_id'] ?? get_the_ID()))): ?>
<div class="avatar-group mt-2 justify-center -space-x-2">
    <?php foreach ($authorIds as $authorId): ?>
        <?php if ($author = get_userdata((int) $authorId)): ?>
            <div class="avatar">
                <a href="<?php echo esc_url(get_author_posts_url($author->ID)); ?>" class="block w-6" title="<?php echo esc_attr($author->display_name); ?>">
                    <img src="<?php echo esc_url(get_avatar_url($author->ID, ['size' => 96])); ?>" alt="<?php echo esc_attr($author->display_name); ?>" />
                </a>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>
