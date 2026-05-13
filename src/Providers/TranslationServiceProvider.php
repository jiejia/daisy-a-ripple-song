<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Theme;

/**
 * Registers theme translation loading.
 */
class TranslationServiceProvider extends AbstractServiceProvider
{
    /**
     * Register the WordPress translation loader hook.
     *
     * @return void
     */
    public function register(): void
    {
        // Load translations once WordPress prepares the active theme.
        add_action('after_setup_theme', [$this, 'loadTranslations']);
    }

    /**
     * Load the bundled theme textdomain.
     *
     * @return void
     */
    public function loadTranslations(): void
    {
        /** @var string $locale Active locale used to resolve the bundled MO file. */
        $locale = function_exists('determine_locale') ? determine_locale() : get_locale();

        /** @var string $moFile Absolute path to the bundled MO translation file. */
        $moFile = Theme::DIR . 'resources/lang/' . Theme::SLUG . '-' . $locale . '.mo';

        if (file_exists($moFile)) {
            load_textdomain(Theme::SLUG, $moFile);

            return;
        }

        load_theme_textdomain(Theme::SLUG, Theme::DIR . 'resources/lang');
    }
}
