<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Supports\Helper;

/**
 * Registers AJAX handlers for post metric tracking.
 */
class AjaxServiceProvider extends AbstractServiceProvider
{
    /**
     * Register AJAX hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('wp_ajax_aripplesong_increment_view', [Helper::class, 'incrementViewCount']);
        add_action('wp_ajax_nopriv_aripplesong_increment_view', [Helper::class, 'incrementViewCount']);
        add_action('wp_ajax_aripplesong_increment_play', [Helper::class, 'incrementPlayCount']);
        add_action('wp_ajax_nopriv_aripplesong_increment_play', [Helper::class, 'incrementPlayCount']);
        add_action('wp_ajax_aripplesong_get_metrics', [Helper::class, 'getMetrics']);
        add_action('wp_ajax_nopriv_aripplesong_get_metrics', [Helper::class, 'getMetrics']);
    }
}
