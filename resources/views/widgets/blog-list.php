<?php
/**
 * Blog List Widget Template
 *
 * @var string                          $title
 * @var array<int, array<string, string>> $posts
 * @var int                             $columns
 * @var bool                            $showSeeAll
 * @var string                          $archiveUrl
 */
?>
<?php $gridClass = $columns === 1 ? 'grid-cols-1' : ($columns === 2 ? 'grid-cols-1 md:grid-cols-2' : 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3'); ?>
<div class="rounded-lg bg-base-100 p-4">
    <div class="grid grid-cols-[1fr_auto] items-center gap-2">
        <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
        <?php if ($showSeeAll): ?>
            <span class="text-xs text-base-content/70">
                <a href="<?php echo esc_url($archiveUrl); ?>"><?php esc_html_e('See all', 'a-ripple-song'); ?></a>
            </span>
        <?php endif; ?>
    </div>

    <ul class="mt-4 grid gap-4 gap-y-6 <?php echo esc_attr($gridClass); ?>">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <li class="rounded-lg bg-base-200/50 p-4 transition-colors hover:bg-base-200">
                    <h3 class="text-md font-bold">
                        <a href="<?php echo esc_url($post['permalink']); ?>"><?php echo esc_html($post['title']); ?></a>
                    </h3>

                    <div class="mt-2 grid gap-1 text-xs text-base-content/70">
                        <?php if (!empty($post['category_name']) && !empty($post['category_link'])): ?>
                            <span>
                                <a href="<?php echo esc_url($post['category_link']); ?>"><?php echo esc_html($post['category_name']); ?></a>
                            </span>
                        <?php endif; ?>

                        <span><?php echo esc_html($post['date']); ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="rounded-lg py-8 text-center text-base-content/50"><?php esc_html_e('No blog posts yet', 'a-ripple-song'); ?></li>
        <?php endif; ?>
    </ul>
</div>
