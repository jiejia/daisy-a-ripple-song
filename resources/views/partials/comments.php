<?php

/**
 * Comments Partial
 *
 * Render the comments list, comments navigation, and comment form.
 */

/**
 * Stop rendering comments when the post is password protected.
 */
if (post_password_required()) {
  return;
}

/**
 * Resolve the current comment count for the heading text.
 *
 * @var int $commentsNumber
 */
$commentsNumber = (int) get_comments_number();

/**
 * Build the comments heading text.
 *
 * @var string $commentsTitle
 */
$commentsTitle = sprintf(
  _nx('One response', '%1$s responses', $commentsNumber, 'comments title', 'a-ripple-song'),
  number_format_i18n($commentsNumber)
);

/**
 * Resolve whether the comments list is paginated.
 *
 * @var bool $isPaginated
 */
$isPaginated = get_comment_pages_count() > 1 && (bool) get_option('page_comments');

/**
 * Resolve the previous comments navigation markup.
 *
 * @var string $previousComments
 */
$previousComments = get_previous_comments_link(__('Older comments', 'a-ripple-song'));

/**
 * Resolve the next comments navigation markup.
 *
 * @var string $nextComments
 */
$nextComments = get_next_comments_link(__('Newer comments', 'a-ripple-song'));

/**
 * Resolve whether the comments section is closed for the current post.
 *
 * @var bool $isClosed
 */
$isClosed = !comments_open() && $commentsNumber > 0;
?>
<div class="mt-4 rounded-lg bg-base-100 p-4 <?php echo comments_open() ? '' : 'hidden'; ?>">
  <section id="comments" class="comments text-sm">
    <?php if (have_comments()): ?>
      <h2 class="mb-4 flex items-center gap-2 text-base font-bold">
        <i data-lucide="message-circle" class="h-4 w-4"></i>
        <?php echo esc_html($commentsTitle); ?>
      </h2>

      <ol class="comment-list space-y-4">
        <?php
        /**
         * Render the current post comments list.
         */
        wp_list_comments([
          'style' => 'ol',
          'short_ping' => true,
          'callback' => [\ARippleSong\Themes\Daisy\Core\Helper::class, 'renderComment'],
          'avatar_size' => 24,
        ]);
        ?>
      </ol>

      <?php if ($isPaginated): ?>
        <nav aria-label="<?php echo esc_attr__('Comment', 'a-ripple-song'); ?>" class="mt-4">
          <ul class="flex justify-center gap-2 text-sm">
            <?php if ($previousComments): ?>
              <li class="previous">
                <div class="btn btn-xs btn-outline gap-1">
                  <i data-lucide="chevron-left" class="h-3 w-3"></i>
                  <?php echo wp_kses_post($previousComments); ?>
                </div>
              </li>
            <?php endif; ?>

            <?php if ($nextComments): ?>
              <li class="next">
                <div class="btn btn-xs btn-outline gap-1">
                  <?php echo wp_kses_post($nextComments); ?>
                  <i data-lucide="chevron-right" class="h-3 w-3"></i>
                </div>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($isClosed): ?>
      <div class="alert alert-warning mb-6 rounded-lg text-sm">
        <i data-lucide="lock" class="h-4 w-4"></i>
        <span><?php esc_html_e('Comments are closed.', 'a-ripple-song'); ?></span>
      </div>
    <?php endif; ?>

    <div class="rounded-lg bg-base-200/50 p-4 mt-4">
      <h3 class="mb-4 flex items-center gap-2 text-base font-bold">
        <i data-lucide="pen-line" class="h-4 w-4"></i>
        <?php esc_html_e('Leave a Comment', 'a-ripple-song'); ?>
      </h3>
      <?php comment_form(); ?>
    </div>
  </section>
</div>
