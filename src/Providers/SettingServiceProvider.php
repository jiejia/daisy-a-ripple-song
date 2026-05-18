<?php
namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Settings\General;

class SettingServiceProvider extends AbstractServiceProvider
{

    private array $settings = [
        General::class,
    ];
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {

    }
}