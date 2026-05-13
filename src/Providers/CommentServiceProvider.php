<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;
use Jiejia\DaisyARippleSong\Supports\Helper;

/**
 * Registers comment form and comment reply behavior.
 */
class CommentServiceProvider extends AbstractServiceProvider
{
    /**
     * Register comment hooks.
     *
     * @return void
     */
    public function register(): void
    {
        // Load WordPress threaded comment support only on eligible singular screens.
        add_action('wp_enqueue_scripts', [$this, 'enqueueCommentReply']);

        // Apply theme comment form styling filters.
        add_filter('comment_form_defaults', [Helper::class, 'filterCommentFormDefaults']);
        add_filter('comment_form_default_fields', [Helper::class, 'filterCommentFormDefaultFields']);
        add_filter('comment_form_field_comment', [Helper::class, 'filterCommentFormFieldComment']);
    }

    /**
     * Enqueue the built-in threaded comment reply script when needed.
     *
     * @return void
     */
    public function enqueueCommentReply(): void
    {
        if (!is_singular() || !comments_open() || !(bool) get_option('thread_comments')) {
            return;
        }

        wp_enqueue_script('comment-reply');
    }
}
