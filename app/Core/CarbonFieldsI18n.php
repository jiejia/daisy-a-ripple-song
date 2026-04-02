<?php

namespace App\Core;

/**
 * Load Carbon Fields PHP translations bundled with the theme.
 */
class CarbonFieldsI18n
{
    /**
     * Load the Carbon Fields PHP textdomain for the current admin locale.
     *
     * @return void
     */
    public function loadPhpTextdomain(): void
    {
        /** Resolve the active locale for admin and frontend contexts. */
        $locale = is_admin() ? get_user_locale() : get_locale();

        /** Build the bundled Carbon Fields MO file path for the current locale. */
        $moFile = get_template_directory() . '/resources/lang/carbon-fields-' . (string) $locale . '.mo';

        if (file_exists($moFile)) {
            load_textdomain('carbon-fields', $moFile);
        }
    }
}
