<?php
/**
 * Comments Template Loader
 *
 * WordPress comments_template() always resolves this file first.
 * Delegate the actual markup to the partial template so the comments UI
 * stays aligned with the rest of the theme structure.
 */

/**
 * Resolve the comments partial template path.
 *
 * @var string|false $commentsTemplate
 */
$commentsTemplate = locate_template('resources/views/partials/comments.php', false, false);

if ($commentsTemplate) {
    load_template($commentsTemplate, false);

    return;
}
