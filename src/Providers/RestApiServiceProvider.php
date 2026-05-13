<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Controllers\MetricsController;

/**
 * Registers REST API routes for theme features.
 *
 * To expose new endpoints, append the controller class to $controllers.
 * Each entry must extend AbstractController and implement registerRoutes().
 */
class RestApiServiceProvider extends AbstractServiceProvider
{
    /**
     * Controller classes whose routes will be registered on rest_api_init.
     *
     * @var class-string<\Jiejia\DaisyARippleSong\Abstracts\AbstractController>[]
     */
    private array $controllers = [
        MetricsController::class,
    ];

    /**
     * Register REST API hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('rest_api_init', function (): void {
            foreach ($this->controllers as $controller) {
                $controller::registerRoutes();
            }
        });
    }
}
