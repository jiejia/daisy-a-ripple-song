<?php

/**
 * Tags Cloud Widget Template
 *
 * @var string     $title
 * @var \WP_Term[] $tags
 */
?>
<div class="">
    <h2 class="text-lg font-bold"><?php echo esc_html($title); ?></h2>
    <?php if (empty($tags)): ?>
        <div class="py-8 text-center">
            <div class="text-base-content/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-2 h-12 w-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <p class="text-sm font-medium"><?php esc_html_e('No tags yet', 'daisy-a-ripple-song'); ?></p>
                <p class="mt-1 text-xs"><?php esc_html_e('Tags will appear here after publishing articles with tags', 'daisy-a-ripple-song'); ?></p>
            </div>
        </div>
    <?php else: ?>
        <ul class="mt-0 flex flex-wrap gap-2 text-xs text-base-content/75 mt-2">
            <?php foreach ($tags as $tag): ?>
                <li>
                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>"
                        class="rounded-full bg-base-200/50 px-2 py-0.5 transition-colors hover:bg-base-200"
                        title="<?php echo esc_attr(sprintf(_n('%d post', '%d posts', $tag->count, 'daisy-a-ripple-song'), $tag->count)); ?>">
                        # <?php echo esc_html($tag->name); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>