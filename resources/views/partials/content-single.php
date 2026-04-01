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
<article class="rounded-lg bg-base-100 p-4">
  <div class="grid grid-flow-row gap-2">
    <div class="grid grid-flow-row gap-1">
      <h4 class="text-md font-bold">
        <?php echo esc_html(html_entity_decode((string) ($args['title'] ?? get_the_title((int) ($args['post_id'] ?? get_the_ID()))))); ?>
      </h4>
      <?php get_template_part('resources/views/partials/entry-meta', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
    </div>
    <div class="max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md" id="content">
      <?php the_content(); ?>
    </div>
    <?php get_template_part('resources/views/partials/entry-tags', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
    <?php get_template_part('resources/views/partials/entry-authors', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
  </div>
</article>
<?php comments_template(); ?>
