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
 * Skip rendering the page header on the site front page.
 */
if (is_front_page()) {
    return;
}

/**
 * Resolve the page header title without relying on Blade view composers.
 *
 * @var string $pageTitle
 */
$pageTitle = (string) ($args['title'] ?? '');

/**
 * Normalize the queried post type value for archive title handling.
 *
 * @var string $queriedPostType
 */
$queriedPostType = '';

if (get_query_var('post_type')) {
    /** @var string|array<int, string> $rawPostType The raw queried post type value. */
    $rawPostType = get_query_var('post_type');

    if (is_array($rawPostType)) {
        $queriedPostType = count($rawPostType) === 1 ? (string) reset($rawPostType) : '';
    } else {
        $queriedPostType = (string) $rawPostType;
    }
}

if ($pageTitle === '') {
    if ($queriedPostType === 'post' && !is_archive() && !is_singular()) {
        $pageTitle = sprintf(
            /* translators: %s Archive object title. */
            __('Archives: %s', 'a-ripple-song'),
            __('Blog', 'a-ripple-song')
        );
    } elseif (is_home() && !is_front_page()) {
        /** @var int $postsPageId Configured posts page ID. */
        $postsPageId = (int) get_option('page_for_posts');

        /**
         * Read the assigned posts page title directly because single_post_title()
         * can return null in this request context.
         */
        $pageTitle = $postsPageId > 0 ? (string) get_the_title($postsPageId) : '';

        if ($pageTitle === '') {
            $pageTitle = __('Blog', 'a-ripple-song');
        }
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
            <?php echo wp_kses_post((string) $pageTitle); ?>
        </h2>
        <div class="rounded-md bg-base-200 px-2 py-1 text-sm text-base-content/70">
            <?php echo esc_html(number_format_i18n($total)); ?>
        </div>
    </div>
</div>
