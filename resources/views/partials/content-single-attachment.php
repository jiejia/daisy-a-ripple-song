<?php
/**
 * Single Attachment Content Partial
 *
 * Expected args:
 * - post_id: Attachment post ID.
 * - title: Optional attachment title override.
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
    <div class="entry-content max-w-none text-sm text-base-content/80 [&_p]:py-2 [&_img]:mx-auto [&_img]:cursor-pointer [&_img]:rounded-lg [&_img]:shadow-md">
      <?php if (has_excerpt((int) ($args['post_id'] ?? get_the_ID()))): ?>
      <div class="prose max-w-none"><?php echo wp_kses_post(wpautop(get_the_excerpt((int) ($args['post_id'] ?? get_the_ID())))); ?></div>
      <?php endif; ?>
      <a href="<?php echo esc_url((string) wp_get_attachment_url((int) ($args['post_id'] ?? get_the_ID()))); ?>" download class="btn btn-primary btn-sm" target="_blank" rel="noopener noreferrer">
        <i data-lucide="download" class="w-4 h-4"></i>
        <?php esc_html_e('Download', 'daisy-a-ripple-song'); ?>
      </a>
    </div>
    <?php get_template_part('resources/views/partials/entry-authors', null, ['post_id' => (int) ($args['post_id'] ?? get_the_ID())]); ?>
  </div>
</article>
<div class="mt-4 rounded-lg bg-base-100 p-4">
  <?php comments_template(); ?>
</div>
