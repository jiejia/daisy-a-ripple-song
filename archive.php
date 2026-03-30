<?php get_header(); ?>

<div class="layout">
    <?php get_template_part('resources/views/sections/leftbar'); ?>
    <div class="">
        <main id="swup-main" class="main transition-fade">
        <?php while (have_posts()): ?>
            <?php
            /**
             * Advance the loop before resolving the matching partial template.
             */
            the_post();

            /**
             * Resolve the post-type-specific partial first, then fall back to the shared content partial.
             *
             * @var string|false $contentTemplate
             */
            $contentTemplate = locate_template([
                'resources/views/partials/content-' . get_post_type() . '.php',
                'resources/views/partials/content.php',
            ], false, false);
            ?>
            <?php if ($contentTemplate): ?>
                <?php load_template($contentTemplate, false, ['post_id' => get_the_ID()]); ?>
            <?php endif; ?>
        <?php endwhile; ?>

        <?php the_posts_pagination(); ?>
        </main>

    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
