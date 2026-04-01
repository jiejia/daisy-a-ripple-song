<?php

namespace App\Tests;

use App\Constants\PodcastPluginConstant;
use App\Core\Helper;
use PHPUnit\Framework\TestCase;

/**
 * Helper Plugin Installation Test
 *
 * Verify the Helper::isPluginInstalled() method with PHPUnit.
 */
class HelperPluginInstallationTest extends TestCase
{

    /**
     * Assert that the helper returns true for a plugin that exists.
     *
     * @return void
     */
    public function testReturnsTrueForInstalledPlugin(): void
    {
        /** @var string $pluginSlug Known installed plugin slug in the current project. */
        $pluginSlug = PodcastPluginConstant::PLUGIN_SLUG;

        /** @var bool $isInstalled Helper result for the installed plugin slug. */
        $isInstalled = Helper::isPluginInstalled($pluginSlug);

        $this->assertTrue(
            $isInstalled,
            'Expected installed plugin slug "' . $pluginSlug . '" to return true.'
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

        /** @var bool $isInstalled Helper result for the missing plugin slug. */
        $isInstalled = Helper::isPluginInstalled($pluginSlug);

        $this->assertFalse(
            $isInstalled,
            'Expected missing plugin slug "' . $pluginSlug . '" to return false.'
        );
    }
}
