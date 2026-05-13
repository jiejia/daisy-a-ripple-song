<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Contracts\CustomArea;
use Jiejia\DaisyARippleSong\CustomAreas\FooterLinks;
use Jiejia\DaisyARippleSong\CustomAreas\HomeMain;
use Jiejia\DaisyARippleSong\CustomAreas\LeftbarPrimary;
use Jiejia\DaisyARippleSong\CustomAreas\RightbarPrimary;

/**
 * Registers theme widget areas.
 */
class CustomAreaServiceProvider extends AbstractServiceProvider
{
    /**
     * Custom area classes registered by this provider.
     *
     * @var array<int,class-string<CustomArea>>
     */
    private array $customAreas = [
        FooterLinks::class,
        HomeMain::class,
        RightbarPrimary::class,
        LeftbarPrimary::class,
    ];

    /**
     * Register custom area hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Register sidebars when WordPress initializes widgets.
        add_action('widgets_init', [$this, 'registerCustomAreas']);
    }

    /**
     * Register all configured widget areas.
     *
     * @return void
     */
    public function registerCustomAreas(): void
    {
        foreach ($this->customAreas as $customAreaClass) {
            // Build each custom area definition before registering it with WordPress.
            $customArea = new $customAreaClass();

            register_sidebar($customArea->args());
        }
    }
}
