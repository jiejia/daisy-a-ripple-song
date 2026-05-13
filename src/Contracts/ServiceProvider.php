<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines the lifecycle methods for theme service providers.
 */
interface ServiceProvider
{
    /**
     * Register WordPress hooks, services, or theme features.
     *
     * @return void
     */
    public function register(): void;

    /**
     * Boot logic that should run after all providers are registered.
     *
     * @return void
     */
    public function boot(): void;
}
