<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines a Carbon Fields settings page contract.
 */
interface Setting
{
    /**
     * Return the Carbon Fields page slug.
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
     * Return all Carbon Fields fields for the settings page.
     *
     * @return array<int,\Carbon_Fields\Field\Field>
     */
    public function fields(): array;

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
