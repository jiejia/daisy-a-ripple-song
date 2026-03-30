<?php
/**
 * Page Header Partial
 *
 * Expected args:
 * - title: Optional heading override.
 * - total: Optional total result count override.
 *
 * @var array<string, mixed> $args
 */

/**
 * Resolve the page header title without relying on Blade view composers.
 *
 * @var string $pageTitle
 */
$pageTitle = (string) ($args['title'] ?? '');

if ($pageTitle === '') {
    if (is_home() && !is_front_page()) {
        $pageTitle = single_post_title('', false);
    } elseif (is_search()) {
        $pageTitle = sprintf(
            /* translators: %s Search query text. */
            __('Search Results for: %s', 'a-ripple-song'),
            get_search_query()
        );
    } elseif (is_archive()) {
        $pageTitle = get_the_archive_title();
    } else {
        $pageTitle = get_bloginfo('name');
    }
}

/**
 * Resolve the total result count shown in the badge.
 *
 * @var int $total
 */
$total = isset($args['total']) ? (int) $args['total'] : (int) ($GLOBALS['wp_query']->found_posts ?? 0);
?>
<div class="mb-4 rounded-lg bg-base-100 p-4">
    <div class="grid grid-cols-[1fr_auto] items-center">
        <h2 class="text-lg font-bold">
            <?php echo wp_kses_post($pageTitle); ?>
        </h2>
        <div class="rounded-md bg-base-200 px-2 py-1 text-sm text-base-content/70">
            <?php echo esc_html(number_format_i18n($total)); ?>
        </div>
    </div>
</div>
