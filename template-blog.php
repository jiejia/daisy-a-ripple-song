<?php
/**
 * Template Name: Blog Template
 */
?>

<?php get_header(); ?>

<div class="layout">
    <?php get_template_part('resources/views/sections/leftbar'); ?>
    <div class="">
        <main id="swup-main" class="main transition-fade" data-current-post-id="<?php echo esc_attr((string) (is_singular() ? (int) get_queried_object_id() : 0)); ?>" data-current-post-type="<?php echo esc_attr((string) (is_singular() ? (string) get_post_type(get_queried_object_id()) : '')); ?>">
            <?php
            /**
             * Resolve the paged value for this static page template archive.
             *
             * @var int $paged
             */
            $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

            /**
             * Query published blog posts for the custom blog template.
             *
             * @var WP_Query $blogQuery
             */
            $blogQuery = new WP_Query([
                'post_type' => 'post',
                'post_status' => 'publish',
                'ignore_sticky_posts' => true,
                'posts_per_page' => (int) get_option('posts_per_page'),
                'paged' => $paged,
            ]);
            ?>

            <?php get_template_part('resources/views/partials/page-header', null, [
                'title' => __('Blog', 'daisy-a-ripple-song'),
                'total' => (int) $blogQuery->found_posts,
            ]); ?>

            <?php if (!$blogQuery->have_posts()): ?>
                <div class="rounded-lg bg-base-100 p-4">
                    <span><?php esc_html_e('Sorry, no results were found.', 'daisy-a-ripple-song'); ?></span>
                </div>
            <?php endif; ?>

            <ul class="grid grid-flow-row gap-y-2">
                <?php while ($blogQuery->have_posts()): ?>
                    <?php
                    /**
                     * Advance the custom blog query before rendering the content partial.
                     */
                    $blogQuery->the_post();

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
            </ul>

            <?php get_template_part('resources/views/partials/pagination', null, [
                'query' => $blogQuery,
                'current' => $paged,
            ]); ?>

            <?php
            /**
             * Restore the global post after the custom blog query loop.
             */
            wp_reset_postdata();
            ?>
        </main>

    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
