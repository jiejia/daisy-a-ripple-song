<?php

namespace Jiejia\DaisyARippleSong\CustomAreas;

use Jiejia\DaisyARippleSong\Abstracts\AbstractCustomArea;

/**
 * Rightbar primary widget area definition.
 */
class RightbarPrimary extends AbstractCustomArea
{
    /**
     * Return the sidebar ID.
     *
     * @return string
     */
    public function id(): string
    {
        return 'rightbar-primary';
    }

    /**
     * Return the translated sidebar name.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Rightbar Primary', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated sidebar description.
     *
     * @return string
     */
    public function description(): string
    {
        return __('Primary right sidebar area for displaying various content modules', 'daisy-a-ripple-song');
    }
}
