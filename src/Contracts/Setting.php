<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines a theme settings section contract.
 */
interface Setting
{
    /**
     * Return the settings page slug.
     *
     * @return string
     */
    public function pageSlug(): string;

    /**
     * Return the translated settings page title.
     *
     * @return string
     */
    public function pageTitle(): string;

    /**
     * Return default values for the settings page.
     *
     * @return array<string,mixed>
     */
    public function defaultSettings(): array;

    /**
     * Return the field prefix used by this settings page.
     *
     * @return string
     */
    public function fieldPrefix(): string;

    /**
     * Return the parent admin menu slug for this settings page.
     *
     * @return string
     */
    public function parentPageSlug(): string;
}
