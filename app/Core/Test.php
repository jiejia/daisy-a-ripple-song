<?php

/**
 * PHPUnit Bootstrap
 *
 * Load the local WordPress installation before running theme tests.
 */

/**
 * Resolve the project root wp-load.php path from the theme test directory.
 *
 * @var string $wpLoadPath
 */
$wpLoadPath = __DIR__ . '/../../../../../wp-load.php';

if (!file_exists($wpLoadPath)) {
    fwrite(STDERR, "Unable to locate wp-load.php for PHPUnit bootstrap.\n");
    exit(1);
}

require_once $wpLoadPath;
