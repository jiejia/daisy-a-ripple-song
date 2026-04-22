<?php

declare(strict_types=1);

if (!defined('ABSPATH') && PHP_SAPI !== 'cli') {
    exit;
}

/** @var Symfony\Component\Finder\Finder $arsFinder */
$arsFinder = Isolated\Symfony\Component\Finder\Finder::class;

/** @var string $arsInputVendorDir Vendor directory used as the PHP-Scoper input tree. */
$arsInputVendorDir = getenv('ARS_SCOPER_INPUT_DIR') ?: (__DIR__ . '/vendor');

/** @var array<int, string> $arsExcludedFiles Vendor files that must remain unscoped. */
$arsExcludedFiles = [];

$arsExcludedFiles[] = $arsInputVendorDir . '/autoload.php';
$arsExcludedFiles = array_merge(
    $arsExcludedFiles,
    array_map(
        static fn (SplFileInfo $fileInfo): string => $fileInfo->getPathname(),
        iterator_to_array(
            $arsFinder::create()
                ->files()
                ->in($arsInputVendorDir . '/composer')
                ->name('*.php'),
            false
        )
    )
);

return [
    'prefix' => 'ARippleSong\\Themes\\Daisy\\Vendor',
    'output-dir' => __DIR__ . '/build/scoped',
    'finders' => [
        $arsFinder::create()
            ->files()
            ->in($arsInputVendorDir)
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
            ->exclude([
                'bin',
                'doc',
                'docs',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
            ]),
    ],
    'exclude-files' => [
        ...$arsExcludedFiles,
    ],
    'php-version' => null,
    'patchers' => [],
    'exclude-namespaces' => [],
    'exclude-classes' => [
        '~^WP_~',
        '~^Walker_~',
    ],
    'exclude-functions' => [
        'content_url',
        'get_blog_status',
        'get_current_screen',
        'plugins_url',
        'register_block_type',
        'site_url',
        'trailingslashit',
        'untrailingslashit',
    ],
    'exclude-constants' => [
        'ABSPATH',
        '~^DOING_~',
        '~^WP_~',
        'SCRIPT_DEBUG',
        'SITE_ID_CURRENT_SITE',
    ],
    'expose-global-constants' => false,
    'expose-global-classes' => false,
    'expose-global-functions' => false,
    'expose-namespaces' => [],
    'expose-classes' => [],
    'expose-functions' => [],
    'expose-constants' => [],
];
