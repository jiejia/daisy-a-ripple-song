<?php get_header(); ?>

<?php if (have_posts()) : ?>
    <div class="post-list">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
                <header class="post-card__header">
                    <?php the_title(sprintf('<h2 class="post-card__title"><a href="%s">', esc_url(get_permalink())), '</a></h2>'); ?>
                </header>
                <div class="post-card__content">
                    <?php the_excerpt(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php else : ?>
    <article class="post-card post-card--empty">
        <h2 class="post-card__title"><?php esc_html_e('No posts found.', 'a-ripple-song'); ?></h2>
    </article>
<?php endif; ?>

<?php get_footer(); ?>
