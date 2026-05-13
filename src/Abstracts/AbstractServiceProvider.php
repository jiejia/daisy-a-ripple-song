<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

use Jiejia\DaisyARippleSong\Contracts\ServiceProvider;

/**
 * Base service provider for theme feature registration.
 */
abstract class AbstractServiceProvider implements ServiceProvider
{
    /**
     * Register WordPress hooks, services, or theme features.
     *
     * @return void
     */
    public function register(): void
    {
        // Child providers may override this method when registration is needed.
    }

    /**
     * Boot logic that should run after all providers are registered.
     *
     * @return void
     */
    public function boot(): void
    {
        // Child providers may override this method when boot logic is needed.
    }
}
