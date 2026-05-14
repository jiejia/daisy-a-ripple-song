<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Carbon_Fields\Carbon_Fields;
use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;

/**
 * Boots the Carbon Fields library.
 */
class CarbonFieldsServiceProvider extends AbstractServiceProvider
{
    /**
     * Register the Carbon Fields boot hook.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'bootCarbonFields']);

        if (did_action('after_setup_theme') && !did_action('init')) {
            $this->bootCarbonFields();
        }
    }

    /**
     * Boot Carbon Fields once before WordPress init registers fields.
     *
     * @return void
     */
    public function bootCarbonFields(): void
    {
        if (Carbon_Fields::is_booted()) {
            return;
        }

        Carbon_Fields::boot();
    }
}
