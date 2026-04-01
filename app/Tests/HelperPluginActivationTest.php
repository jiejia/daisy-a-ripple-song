<?php

namespace App\Tests;

use App\Constants\PodcastPluginConstant;
use App\Core\Helper;
use PHPUnit\Framework\TestCase;

/**
 * Helper Plugin Activation Test
 *
 * Verify the Helper::isPluginActivated() method with PHPUnit.
 */
class HelperPluginActivationTest extends TestCase
{

    /**
     * Assert that the helper matches WordPress activation state for the known podcast plugin.
     *
     * @return void
     */
    public function testMatchesWordPressActivationStateForKnownPlugin(): void
    {
        if (!function_exists('get_plugins') || !function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        /** @var string $pluginSlug Known plugin slug in the current project. */
        $pluginSlug = PodcastPluginConstant::PLUGIN_SLUG;

        /** @var string $pluginFile Expected plugin bootstrap file path for the podcast plugin. */
        $pluginFile = $this->resolvePluginFileBySlug($pluginSlug);

        $this->assertNotSame(
            '',
            $pluginFile,
            'Expected known plugin slug "' . $pluginSlug . '" to resolve to a plugin file.'
        );

        /** @var bool $expectedActivation WordPress activation state for the known plugin. */
        $expectedActivation = is_plugin_active($pluginFile);

        /** @var bool $isActivated Helper result for the known plugin slug. */
        $isActivated = Helper::isPluginActivated($pluginSlug);

        $this->assertSame(
            $expectedActivation,
            $isActivated,
            'Expected helper activation state for plugin slug "' . $pluginSlug . '" to match WordPress.'
        );
    }

    /**
     * Assert that the helper returns false for a plugin slug that does not exist.
     *
     * @return void
     */
    public function testReturnsFalseForMissingPlugin(): void
    {
        /** @var string $pluginSlug Fake plugin slug that should not exist in the project. */
        $pluginSlug = 'plugin-slug-that-should-not-exist';

        /** @var bool $isActivated Helper result for the missing plugin slug. */
        $isActivated = Helper::isPluginActivated($pluginSlug);

        $this->assertFalse(
            $isActivated,
            'Expected missing plugin slug "' . $pluginSlug . '" to return false.'
        );
    }

    /**
     * Resolve a plugin file path using the same slug conventions as the theme helper.
     *
     * @param string $pluginSlug Plugin slug.
     * @return string
     */
    private function resolvePluginFileBySlug(string $pluginSlug): string
    {
        /** @var string $directoryPluginFile Conventional directory plugin bootstrap file. */
        $directoryPluginFile = $pluginSlug . '/' . $pluginSlug . '.php';

        if (file_exists(WP_PLUGIN_DIR . '/' . $directoryPluginFile)) {
            return $directoryPluginFile;
        }

        /** @var string $singleFilePlugin Conventional single-file plugin bootstrap file. */
        $singleFilePlugin = $pluginSlug . '.php';

        if (file_exists(WP_PLUGIN_DIR . '/' . $singleFilePlugin)) {
            return $singleFilePlugin;
        }

        /** @var array<string, array<string, string>> $plugins Installed plugins keyed by plugin file path. */
        $plugins = get_plugins();

        foreach (array_keys($plugins) as $pluginFile) {
            /** @var string $pluginDirectorySlug Directory slug for directory-based plugins. */
            $pluginDirectorySlug = dirname($pluginFile);

            /** @var string $pluginFileSlug File slug for single-file plugins. */
            $pluginFileSlug = basename($pluginFile, '.php');

            if ($pluginDirectorySlug === $pluginSlug || $pluginFileSlug === $pluginSlug) {
                return $pluginFile;
            }
        }

        return '';
    }
}
