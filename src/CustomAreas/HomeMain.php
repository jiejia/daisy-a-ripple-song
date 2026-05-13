<?php

namespace Jiejia\DaisyARippleSong\CustomAreas;

use Jiejia\DaisyARippleSong\Abstracts\AbstractCustomArea;

/**
 * Home main widget area definition.
 */
class HomeMain extends AbstractCustomArea
{
    /**
     * Return the sidebar ID.
     *
     * @return string
     */
    public function id(): string
    {
        return 'home-main';
    }

    /**
     * Return the translated sidebar name.
     *
     * @return string
     */
    public function name(): string
    {
        return __('Home Main', 'daisy-a-ripple-song');
    }

    /**
     * Return the translated sidebar description.
     *
     * @return string
     */
    public function description(): string
    {
        return __('Main area of the homepage for displaying various content modules', 'daisy-a-ripple-song');
    }
}
