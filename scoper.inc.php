<?php

declare(strict_types=1);

if (!defined('ABSPATH') && PHP_SAPI !== 'cli') {
    exit;
}

/** @var Symfony\Component\Finder\Finder $arsFinder */
$arsFinder = Isolated\Symfony\Component\Finder\Finder::class;

/** @var string $arsThemePrefix Prefix used for scoped theme-specific hooks. */
$arsThemePrefix = 'daisyaripplesong';

/** @var string $arsInputVendorDir Vendor directory used as the PHP-Scoper input tree. */
$arsInputVendorDir = getenv('ARS_SCOPER_INPUT_DIR') ?: (__DIR__ . '/vendor');

/** @var array<int, string> $arsExcludedFiles Vendor files that must remain unscoped. */
$arsExcludedFiles = [];

$arsExcludedFiles[] = $arsInputVendorDir . '/autoload.php';

/** @var string $arsCarbonFieldsTemplatesDir Carbon Fields PHP templates directory. */
$arsCarbonFieldsTemplatesDir = $arsInputVendorDir . '/htmlburger/carbon-fields/templates';

if (is_dir($arsCarbonFieldsTemplatesDir)) {
    // Carbon Fields templates can start with HTML before the first PHP tag.
    // Prefixing those files injects a namespace after output and breaks parsing.
    $arsExcludedFiles = array_merge(
        $arsExcludedFiles,
        array_map(
            static fn (SplFileInfo $fileInfo): string => $fileInfo->getPathname(),
            iterator_to_array(
                $arsFinder::create()
                    ->files()
                    ->in($arsCarbonFieldsTemplatesDir)
                    ->name('*.php'),
                false
            )
        )
    );
}

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
    'prefix' => 'Jiejia\\DaisyARippleSong\\Vendor',
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
    'patchers' => [
        static function (string $filePath, string $prefix, string $contents) use ($arsThemePrefix): string {
            return str_replace(
                'carbon_fields_register_fields',
                $arsThemePrefix . '_carbon_fields_register_fields',
                $contents
            );
        },
    ],
    'exclude-namespaces' => [],
    'exclude-classes' => [
        '~^WP_~',
        '~^Walker_~',
        'wpdb',
    ],
    'exclude-functions' => [
        '__',
        '_e',
        'add_action',
        'add_filter',
        'apply_filters',
        'content_url',
        'determine_locale',
        'did_action',
        'do_action',
        'doing_action',
        'esc_attr',
        'get_blog_status',
        'get_current_screen',
        'is_feed',
        'plugin_dir_path',
        'plugins_url',
        'register_block_type',
        'sanitize_title',
        'site_url',
        'trailingslashit',
        'untrailingslashit',
        'wp_enqueue_script',
        'wp_enqueue_style',
        'wp_localize_script',
        'wp_strip_all_tags',
    ],
    'exclude-constants' => [
        'ABSPATH',
        '~^DOING_~',
        '~^WP_~',
        'SCRIPT_DEBUG',
        'SITE_ID_CURRENT_SITE',
    ],
    'expose-global-constants' => true,
    'expose-global-classes' => true,
    'expose-global-functions' => true,
    'expose-namespaces' => [],
    'expose-classes' => [],
    'expose-functions' => [],
    'expose-constants' => [],
];
