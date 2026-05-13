<?php

namespace Jiejia\DaisyARippleSong\Abstracts;

/**
 * Base abstract class for all REST API controllers.
 *
 * Subclasses must implement registerRoutes() to declare their endpoint
 * definitions, and may rely on the shared NAMESPACE constant so that
 * the API prefix remains consistent across the entire theme.
 */
abstract class AbstractController
{
    /**
     * REST API namespace shared by all theme controllers.
     *
     * @var string
     */
    public const NAMESPACE = 'aripplesong/v1';

    /**
     * Register all REST API routes handled by the concrete controller.
     *
     * Implementations should call register_rest_route() inside this method
     * and use self::NAMESPACE (or static::NAMESPACE) as the namespace argument.
     *
     * @return void
     */
    abstract public static function registerRoutes(): void;
}
