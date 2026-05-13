<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;

/**
 * Registers custom block styles and starter patterns.
 */
class BlockServiceProvider extends AbstractServiceProvider
{
    /**
     * Register block hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Register block extensions during WordPress initialization.
        add_action('init', [$this, 'registerBlocks']);
    }

    /**
     * Register block styles and patterns.
     *
     * @return void
     */
    public function registerBlocks(): void
    {
        if (function_exists('register_block_style')) {
            register_block_style('core/group', [
                'name' => 'ars-panel',
                'label' => __('Panel', 'daisy-a-ripple-song'),
            ]);
        }

        if (!function_exists('register_block_pattern')) {
            return;
        }

        register_block_pattern('a-ripple-song/intro-panel', [
            'title' => __('Intro Panel', 'daisy-a-ripple-song'),
            'description' => __('A rounded introduction block with heading, text, and call to action.', 'daisy-a-ripple-song'),
            'categories' => ['text'],
            'content' => '<!-- wp:group {"align":"wide","className":"is-style-ars-panel"} --><div class="wp-block-group alignwide is-style-ars-panel"><!-- wp:heading --><h2>' . esc_html__('Start your next episode here', 'daisy-a-ripple-song') . '</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__('Use this pattern to introduce a featured story, announcement, or podcast episode.', 'daisy-a-ripple-song') . '</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#">' . esc_html__('Learn more', 'daisy-a-ripple-song') . '</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->',
        ]);
    }
}
