<?php

namespace Jiejia\DaisyARippleSong\Providers;

use Carbon_Fields\Carbon_Fields;
use Jiejia\DaisyARippleSong\Abstracts\AbstractServiceProvider;

/**
 * Boots the Carbon Fields library.
 */
class CarbonFieldsServiceProvider extends AbstractServiceProvider
{
    /**
     * Register the Carbon Fields boot hook.
     *
     * @return void
     */
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'bootCarbonFields']);
        add_filter('carbon_fields_attachment_id_from_url', [$this, 'resolveAttachmentIdFromUploadUrl'], 10, 2);

        if (did_action('after_setup_theme') && !did_action('init')) {
            $this->bootCarbonFields();
        }
    }

    /**
     * Boot Carbon Fields once before WordPress init registers fields.
     *
     * @return void
     */
    public function bootCarbonFields(): void
    {
        if (Carbon_Fields::is_booted()) {
            return;
        }

        Carbon_Fields::boot();
    }

    /**
     * Resolve attachment IDs from uploaded file URLs that do not use the local uploads base URL.
     *
     * @param int $attachmentId Attachment ID Carbon Fields already found.
     * @param string $url Attachment URL saved by Carbon Fields.
     * @return int
     */
    public function resolveAttachmentIdFromUploadUrl(int $attachmentId, string $url): int
    {
        if ($attachmentId > 0) {
            return $attachmentId;
        }

        /** @var string $uploadPath Uploaded file URL path. */
        $uploadPath = (string) wp_parse_url($url, PHP_URL_PATH);

        if ($uploadPath === '' || !str_contains($uploadPath, '/wp-content/uploads/')) {
            return 0;
        }

        /** @var string $filename Uploaded file basename used for metadata lookup. */
        $filename = wp_basename($uploadPath);

        if ($filename === '') {
            return 0;
        }

        /** @var \WP_Query $attachmentQuery Query used to find matching attachment metadata. */
        $attachmentQuery = new \WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'fields' => 'ids',
            'posts_per_page' => 20,
            'no_found_rows' => true,
            'meta_query' => [
                [
                    'key' => '_wp_attachment_metadata',
                    'value' => $filename,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        foreach ($attachmentQuery->posts as $postId) {
            /** @var array<string,mixed>|false $metadata Attachment metadata. */
            $metadata = wp_get_attachment_metadata((int) $postId);

            if (!is_array($metadata)) {
                continue;
            }

            /** @var string $originalFile Original uploaded file basename. */
            $originalFile = !empty($metadata['file']) ? wp_basename((string) $metadata['file']) : '';

            /** @var array<string,array<string,mixed>> $sizes Registered image sizes metadata. */
            $sizes = !empty($metadata['sizes']) && is_array($metadata['sizes']) ? $metadata['sizes'] : [];

            /** @var array<int,string> $croppedFiles Cropped image basenames. */
            $croppedFiles = [];

            foreach ($sizes as $size) {
                if (!is_array($size) || empty($size['file'])) {
                    continue;
                }

                $croppedFiles[] = (string) $size['file'];
            }

            if ($originalFile === $filename || in_array($filename, $croppedFiles, true)) {
                return (int) $postId;
            }
        }

        return 0;
    }
}
