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
                 * Resolve the single-post partial first, then fall back to the shared single content partial.
                 *
                 * @var string|false $contentTemplate
                 */
                $contentTemplate = locate_template([
                    'resources/views/partials/content-single-' . get_post_type() . '.php',
                    'resources/views/partials/content-single.php',
                    'resources/views/partials/content-' . get_post_type() . '.php',
                    'resources/views/partials/content.php',
                ], false, false);
                ?>
                <?php if ($contentTemplate): ?>
                    <?php load_template($contentTemplate, false, ['post_id' => get_the_ID()]); ?>
                <?php endif; ?>
            <?php endwhile; ?>
        </main>

    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
