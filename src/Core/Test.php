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
    exit("Unable to locate wp-load.php for PHPUnit bootstrap.\n");
}

require_once $wpLoadPath;
