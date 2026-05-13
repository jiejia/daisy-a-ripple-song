<?php

namespace Jiejia\DaisyARippleSong\Contracts;

/**
 * Defines a native WordPress settings page contract.
 */
interface Setting
{
    /**
     * Register this setting with WordPress.
     *
     * @return void
     */
    public function registerSetting(): void;

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
     * Return the WordPress settings group.
     *
     * @return string
     */
    public function optionGroup(): string;

    /**
     * Return the serialized WordPress option name.
     *
     * @return string
     */
    public function optionName(): string;

    /**
     * Return field definitions for this settings page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fields(): array;

    /**
     * Return default settings for this page.
     *
     * @return array<string, mixed>
     */
    public function defaultSettings(): array;

    /**
     * Sanitize the submitted settings value.
     *
     * @param mixed $value Raw submitted value.
     * @return array<string, mixed>
     */
    public function sanitize($value): array;

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function renderPage(): void;
}
