<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Controllers\MetricsController;

/**
 * Registers REST API routes for theme features.
 */
class RestApiServiceProvider extends AbstractServiceProvider
{
    /**
     * Register REST API hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('rest_api_init', [MetricsController::class, 'registerRoutes']);
    }
}
