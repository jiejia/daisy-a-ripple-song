<?php
/**
 * Content Partial
 *
 * Expected args:
 * - post_id: Post ID used to render the content block.
 * - title: Optional title override.
 *
 * @var array<string, mixed> $args
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('mb-4 rounded-lg bg-base-100 p-4'); ?>>
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold">
        <a href="<?php echo esc_url(get_permalink((int) ($args['post_id'] ?? get_the_ID()))); ?>">
          <?php echo esc_html(html_entity_decode((string) ($args['title'] ?? get_the_title((int) ($args['post_id'] ?? get_the_ID()))))); ?>
        </a>
      </h4>
      <?php get_template_part('resources/views/partials/entry-meta', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
    </div>
    <?php if (has_post_thumbnail()): ?>
      <a href="<?php echo esc_url(get_permalink((int) ($args['post_id'] ?? get_the_ID()))); ?>" class="block overflow-hidden rounded-lg">
        <?php the_post_thumbnail('large', ['class' => 'h-auto w-full rounded-lg shadow-md']); ?>
      </a>
    <?php endif; ?>
    <div class="entry-content prose max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md">
      <?php the_excerpt(); ?>
    </div>
    <?php get_template_part('resources/views/partials/entry-tags', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
    <?php get_template_part('resources/views/partials/entry-authors', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
  </div>
  <div class="mt-4 rounded-lg bg-base-100 p-4">
  </div>
</article>
