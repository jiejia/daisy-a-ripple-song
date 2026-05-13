<?php

namespace Jiejia\DaisyARippleSong\Controllers;

use Jiejia\DaisyARippleSong\Abstracts\AbstractController;

/**
 * REST API controller for post metric tracking (view counts and play counts).
 */
class MetricsController extends AbstractController
{
    /**
     * Register all REST API routes handled by this controller.
     *
     * @return void
     */
    public static function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/metrics/views', [
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [static::class, 'incrementViewCount'],
            'permission_callback' => '__return_true',
            'args'                => [
                'post_id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'minimum'           => 1,
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/metrics/plays', [
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [static::class, 'incrementPlayCount'],
            'permission_callback' => '__return_true',
            'args'                => [
                'post_id' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'minimum'           => 1,
                ],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/metrics', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [static::class, 'getMetrics'],
            'permission_callback' => '__return_true',
            'args'                => [
                'post_ids' => [
                    'required' => true,
                    'type'     => 'array',
                    'items'    => [
                        'type'    => 'integer',
                        'minimum' => 1,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Check whether a post can be read for metric tracking purposes.
     *
     * @param \WP_Post $post The target post object.
     * @return bool True when the post metrics may be read or updated.
     */
    private static function canReadMetricPost(\WP_Post $post): bool
    {
        if ($post->post_status === 'publish') {
            return true;
        }

        return current_user_can('read_post', $post->ID);
    }

    /**
     * Increment the view count for a readable post.
     *
     * @param \WP_REST_Request $request REST request object.
     * @return \WP_REST_Response
     */
    public static function incrementViewCount(\WP_REST_Request $request): \WP_REST_Response
    {
        /** @var int $postId Requested post ID. */
        $postId = (int) $request->get_param('post_id');

        /** @var \WP_Post|null $post Target post object. */
        $post = $postId ? get_post($postId) : null;

        if (!$post instanceof \WP_Post || !self::canReadMetricPost($post)) {
            return new \WP_REST_Response(['message' => 'Invalid post ID.'], 400);
        }

        /** @var int $count Updated view count. */
        $count = max(0, (int) get_post_meta($postId, '_views_count', true)) + 1;

        update_post_meta($postId, '_views_count', $count);

        return new \WP_REST_Response(['count' => $count], 200);
    }

    /**
     * Increment the play count for a readable podcast episode.
     *
     * @param \WP_REST_Request $request REST request object.
     * @return \WP_REST_Response
     */
    public static function incrementPlayCount(\WP_REST_Request $request): \WP_REST_Response
    {
        /** @var int $postId Requested post ID. */
        $postId = (int) $request->get_param('post_id');

        /** @var \WP_Post|null $post Target post object. */
        $post = $postId ? get_post($postId) : null;

        if (
            !$post instanceof \WP_Post
            || $post->post_type !== \Jiejia\ARippleSong\CPTs\Episode::slug()
            || !self::canReadMetricPost($post)
        ) {
            return new \WP_REST_Response(['message' => 'Invalid podcast episode post.'], 400);
        }

        /** @var int $count Updated play count. */
        $count = max(0, (int) get_post_meta($postId, '_play_count', true)) + 1;

        update_post_meta($postId, '_play_count', $count);

        return new \WP_REST_Response(['count' => $count], 200);
    }

    /**
     * Fetch the current metrics for a list of readable posts.
     *
     * @param \WP_REST_Request $request REST request object.
     * @return \WP_REST_Response
     */
    public static function getMetrics(\WP_REST_Request $request): \WP_REST_Response
    {
        /** @var int[] $postIds Sanitized post IDs from the request. */
        $postIds = array_values(array_filter(array_map('absint', (array) ($request->get_param('post_ids') ?? []))));

        if ($postIds === []) {
            return new \WP_REST_Response(['message' => 'No post IDs provided.'], 400);
        }

        /** @var array<int, array<string, int|string|null>> $data Metrics payload keyed by post ID. */
        $data = [];

        foreach ($postIds as $postId) {
            /** @var \WP_Post|null $post Post object for the requested ID. */
            $post = get_post($postId);

            if (!$post instanceof \WP_Post || !self::canReadMetricPost($post)) {
                continue;
            }

            /** @var int $views Current post view count. */
            $views = max(0, (int) get_post_meta($postId, '_views_count', true));

            /** @var int|null $plays Current post play count when the post is a podcast episode. */
            $plays = $post->post_type === \Jiejia\ARippleSong\CPTs\Episode::slug()
                ? max(0, (int) get_post_meta($postId, '_play_count', true))
                : null;

            $data[$postId] = [
                'views'    => $views,
                'plays'    => $plays,
                'postType' => $post->post_type,
            ];
        }

        return new \WP_REST_Response(['counts' => $data], 200);
    }
}
