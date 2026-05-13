<?php

namespace Jiejia\DaisyARippleSong\CustomAreas;

use Jiejia\DaisyARippleSong\Abstracts\AbstractCustomArea;

/**
 * Leftbar primary widget area definition.
 */
class LeftbarPrimary extends AbstractCustomArea
{
    /**
     * Return the sidebar ID.
     *
     * @return string
     */
    public function id(): string
    {
        return 'leftbar-primary';
    }

    /**
     * Return the translated sidebar name.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Leftbar Primary', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated sidebar description.
     *
     * @return string
     */
    public function description(): string
    {
        return __('Primary left sidebar area for displaying various content modules', 'daisy-a-ripple-song');
    }
}
