<?php
/**
 * Single Content Partial
 *
 * Expected args:
 * - post_id: Post ID.
 * - title: Optional post title override.
 *
 * @var array<string, mixed> $args
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('rounded-lg bg-base-100 p-4'); ?>>
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold">
        <?php echo esc_html(html_entity_decode((string) ($args['title'] ?? get_the_title((int) ($args['post_id'] ?? get_the_ID()))))); ?>
      </h4>
      <?php get_template_part('resources/views/partials/entry-meta', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
    </div>
    <?php if (has_post_thumbnail()): ?>
      <div class="overflow-hidden rounded-lg">
        <?php the_post_thumbnail('large', ['class' => 'h-auto w-full rounded-lg shadow-md']); ?>
      </div>
    <?php endif; ?>
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
    <?php get_template_part('resources/views/partials/entry-tags', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
    <?php get_template_part('resources/views/partials/entry-authors', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
  </div>
</article>
<?php comments_template(); ?>
