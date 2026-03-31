<?php get_header(); ?>

<div class="layout">
    <?php get_template_part('resources/views/sections/leftbar'); ?>
    <div class="">
        <main id="swup-main" class="main transition-fade">
            <?php
            /**
             * Resolve the current author archive object.
             *
             * @var WP_User|object|null $author
             */
            $author = get_queried_object();

            /**
             * Resolve the author display name for the page heading.
             *
             * @var string $authorName
             */
            $authorName = is_object($author) && isset($author->display_name) ? (string) $author->display_name : '';
            ?>
            <?php get_template_part('resources/views/partials/page-header', null, ['title' => __('Author:', 'a-ripple-song') . ' ' . $authorName]); ?>

            <?php if (have_posts()): ?>
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
            <?php else: ?>
                <div class="alert alert-warning">
                    <span><?php esc_html_e('Sorry, no results were found.', 'a-ripple-song'); ?></span>
                </div>
            <?php endif; ?>
        </main>

    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
