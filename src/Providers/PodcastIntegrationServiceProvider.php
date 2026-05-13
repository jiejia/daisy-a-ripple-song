<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Constants\PodcastPluginConstant;
use Jiejia\DaisyARippleSong\Supports\Helper;

/**
 * Registers theme integrations that depend on the podcast plugin runtime.
 */
class PodcastIntegrationServiceProvider extends AbstractServiceProvider
{
    /**
     * Register podcast integration hooks.
     *
     * @return void
     */
    public function register(): void
    {
        if (!defined('IS_PODCAST_PLUGIN_ACTIVATED') || !IS_PODCAST_PLUGIN_ACTIVATED) {
            return;
        }

        // Include podcast episodes in tag archives.
        add_action('pre_get_posts', [$this, 'modifyTagArchiveQuery']);

        // Include participated podcast episodes in author archives.
        add_action('pre_get_posts', [Helper::class, 'modifyAuthorArchiveQuery']);
    }

    /**
     * Modify tag archive query to include both posts and podcast episodes.
     *
     * @param \WP_Query $query The WordPress query object.
     * @return void
     */
    public function modifyTagArchiveQuery($query): void
    {
        if (is_admin() || !$query->is_main_query() || !$query->is_tag()) {
            return;
        }

        $query->set('post_type', ['post', \Jiejia\ARippleSong\CPTs\Episode::slug()]);
    }
}
