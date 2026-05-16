<?php
/**
 * DaisyUI Pagination Partial
 *
 * Expected args:
 * - query: Optional WP_Query instance used to resolve the total number of pages.
 * - current: Optional current page number override.
 *
 * @var array<string, mixed> $args
 */

/**
 * Resolve the query object used by this pagination partial.
 *
 * @var WP_Query|null $paginationQuery
 */
$paginationQuery = isset($args['query']) && $args['query'] instanceof WP_Query ? $args['query'] : ($GLOBALS['wp_query'] ?? null);

/**
 * Stop rendering when no valid query object is available.
 */
if (!$paginationQuery instanceof WP_Query) {
    return;
}

/**
 * Resolve the total page count for the query.
 *
 * @var int $totalPages
 */
$totalPages = max(1, (int)$paginationQuery->max_num_pages);

/**
 * Stop rendering when there is only one page of results.
 */
if ($totalPages <= 1) {
    return;
}

/**
 * Resolve the current page number from args or WordPress query vars.
 *
 * @var int $currentPage
 */
$currentPage = isset($args['current'])
        ? max(1, (int)$args['current'])
        : max(1, (int)get_query_var('paged'), (int)get_query_var('page'));

/**
 * Clamp the current page number to the query range.
 */
$currentPage = min($currentPage, $totalPages);

/**
 * Build the visible page number set, using 0 as the ellipsis marker.
 *
 * @var array<int, int> $pages
 */
$pages = [];

if ($totalPages <= 7) {
    /**
     * Render every page number when the result set is small.
     */
    $pages = range(1, $totalPages);
} else {
    /**
     * Always render the first page link.
     */
    $pages[] = 1;

    /**
     * Resolve the start of the sliding page number window.
     *
     * @var int $windowStart
     */
    $windowStart = max(2, $currentPage - 1);

    /**
     * Resolve the end of the sliding page number window.
     *
     * @var int $windowEnd
     */
    $windowEnd = min($totalPages - 1, $currentPage + 1);

    if ($currentPage <= 4) {
        /**
         * Keep the first window wide enough near the beginning.
         */
        $windowEnd = min($totalPages - 1, 5);
    }

    if ($currentPage >= $totalPages - 3) {
        /**
         * Keep the last window wide enough near the end.
         */
        $windowStart = max(2, $totalPages - 4);
    }

    if ($windowStart > 2) {
        /**
         * Add a leading ellipsis marker when the window skips pages.
         */
        $pages[] = 0;
    }

    for ($pageNumber = $windowStart; $pageNumber <= $windowEnd; $pageNumber++) {
        /**
         * Add each page number in the sliding window.
         */
        $pages[] = $pageNumber;
    }

    if ($windowEnd < $totalPages - 1) {
        /**
         * Add a trailing ellipsis marker when the window skips pages.
         */
        $pages[] = 0;
    }

    /**
     * Always render the final page link.
     */
    $pages[] = $totalPages;
}
?>

<div class="rounded-lg bg-base-100 p-2">
    <nav class="flex justify-center">
        <div class="join">
            <?php foreach ($pages as $pageNumber): ?>
                <?php if ($pageNumber === 0): ?>
                    <span class="join-item btn btn-square btn-disabled">...</span>
                <?php elseif ($pageNumber === $currentPage): ?>
                    <span class="join-item btn btn-square btn-primary btn-active"
                          aria-current="page"><?php echo esc_html(number_format_i18n($pageNumber)); ?></span>
                <?php else: ?>
                    <a class="join-item btn btn-square"
                       href="<?php echo esc_url(get_pagenum_link($pageNumber)); ?>"><?php echo esc_html(number_format_i18n($pageNumber)); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </nav>
</div>


