<?php

namespace App\ThemeOptions;

use App\Constants\PodcastPluginConstant;

/**
 * Recommended plugin admin page for the theme settings menu.
 */
class RecommendedPlugins
{
    /** @var string $pageSlug Admin page slug for the recommended plugins screen. */
    protected const PAGE_SLUG = 'ars_recommended_plugins';

    /** @var string $activateAction Admin-post action for plugin activation. */
    protected const ACTIVATE_ACTION = 'ars_activate_recommended_plugin';

    /** @var string $installAction Admin-post action for plugin installation. */
    protected const INSTALL_ACTION = 'ars_install_recommended_plugin';

    /** @var string $noticeQueryArg Query arg used to transport admin notices. */
    protected const NOTICE_QUERY_ARG = 'ars_notice';

    /** @var string $noticeTypeQueryArg Query arg used to transport notice types. */
    protected const NOTICE_TYPE_QUERY_ARG = 'ars_notice_type';

    /**
     * Register all recommended plugin admin hooks.
     *
     * @return void
     */
    public static function boot(): void
    {
        add_action('admin_menu', [static::class, 'registerPage'], 1100);
        add_action('admin_post_' . static::ACTIVATE_ACTION, [static::class, 'handleActivateAction']);
        add_action('admin_post_' . static::INSTALL_ACTION, [static::class, 'handleInstallAction']);
        add_action('admin_notices', [static::class, 'renderAdminNotice']);
    }

    /**
     * Register the Recommended Plugins submenu page.
     *
     * @return void
     */
    public static function registerPage(): void
    {
        /** @var string $parentFile Parent admin page file for the shared settings menu. */
        $parentFile = General::getAdminMenuParentFile();

        add_submenu_page(
            $parentFile,
            __('Recommended Plugins', 'a-ripple-song'),
            __('Recommended Plugins', 'a-ripple-song'),
            'install_plugins',
            static::PAGE_SLUG,
            [static::class, 'renderPage']
        );
    }

    /**
     * Render the Recommended Plugins admin screen.
     *
     * @return void
     */
    public static function renderPage(): void
    {
        if (!current_user_can('install_plugins')) {
            wp_die(esc_html__('You are not allowed to manage plugins on this site.', 'a-ripple-song'));
        }

        /** @var array<int, array<string, mixed>> $plugins Recommended plugins enriched with status data. */
        $plugins = static::getRecommendedPlugins();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Recommended Plugins', 'a-ripple-song'); ?></h1>
            <p><?php echo esc_html__('These plugins are recommended for the A Ripple Song theme.', 'a-ripple-song'); ?></p>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th scope="col"><?php echo esc_html__('Name', 'a-ripple-song'); ?></th>
                        <th scope="col"><?php echo esc_html__('Slug', 'a-ripple-song'); ?></th>
                        <th scope="col"><?php echo esc_html__('Description', 'a-ripple-song'); ?></th>
                        <th scope="col"><?php echo esc_html__('Status', 'a-ripple-song'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plugins as $plugin) : ?>
                        <tr>
                            <td><?php echo esc_html((string) $plugin['name']); ?></td>
                            <td><code><?php echo esc_html((string) $plugin['slug']); ?></code></td>
                            <td><?php echo esc_html((string) $plugin['description']); ?></td>
                            <td>
                                <?php echo esc_html((string) $plugin['statusLabel']); ?>

                                <?php if ((string) $plugin['status'] === 'inactive') : ?>
                                    <p>
                                        <a class="button button-secondary" href="<?php echo esc_url(static::getActivateUrl((string) $plugin['slug'])); ?>">
                                            <?php echo esc_html__('Activate', 'a-ripple-song'); ?>
                                        </a>
                                    </p>
                                <?php elseif ((string) $plugin['status'] === 'missing' && (bool) $plugin['canInstall']) : ?>
                                    <p>
                                        <a class="button button-secondary" href="<?php echo esc_url(static::getInstallUrl((string) $plugin['slug'])); ?>">
                                            <?php echo esc_html__('Install', 'a-ripple-song'); ?>
                                        </a>
                                    </p>
                                <?php elseif ((string) $plugin['status'] === 'missing') : ?>
                                    <p class="description">
                                        <?php echo esc_html__('This plugin will be installable after it is published on WordPress.org.', 'a-ripple-song'); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Handle the activation action for a recommended plugin.
     *
     * @return void
     */
    public static function handleActivateAction(): void
    {
        if (!current_user_can('activate_plugins')) {
            wp_die(esc_html__('You are not allowed to activate plugins on this site.', 'a-ripple-song'));
        }

        check_admin_referer(static::ACTIVATE_ACTION);

        /** @var string $pluginSlug Requested plugin slug from the admin action. */
        $pluginSlug = isset($_GET['plugin']) ? sanitize_key(wp_unslash((string) $_GET['plugin'])) : '';

        if (!static::isRecommendedPlugin($pluginSlug)) {
            static::redirectWithNotice('recommended-plugin-not-found', 'error');
        }

        static::loadPluginFunctions();

        /** @var string $pluginFile Plugin bootstrap file resolved from the plugin slug. */
        $pluginFile = static::resolvePluginFileBySlug($pluginSlug);

        if ($pluginFile === '') {
            static::redirectWithNotice('plugin-file-missing', 'error');
        }

        /** @var null|\WP_Error $activationResult Result returned by the WordPress activation API. */
        $activationResult = activate_plugin($pluginFile);

        if (is_wp_error($activationResult)) {
            static::redirectWithNotice('plugin-activation-failed', 'error');
        }

        static::redirectWithNotice('plugin-activated', 'success');
    }

    /**
     * Handle the installation action for a recommended plugin.
     *
     * @return void
     */
    public static function handleInstallAction(): void
    {
        if (!current_user_can('install_plugins')) {
            wp_die(esc_html__('You are not allowed to install plugins on this site.', 'a-ripple-song'));
        }

        check_admin_referer(static::INSTALL_ACTION);

        /** @var string $pluginSlug Requested plugin slug from the admin action. */
        $pluginSlug = isset($_GET['plugin']) ? sanitize_key(wp_unslash((string) $_GET['plugin'])) : '';

        if (!static::isRecommendedPlugin($pluginSlug)) {
            static::redirectWithNotice('recommended-plugin-not-found', 'error');
        }

        static::loadPluginInstallerFunctions();

        /** @var array<string, mixed>|object|\WP_Error $pluginInformation Remote plugin information from WordPress.org. */
        $pluginInformation = plugins_api('plugin_information', [
            'slug' => $pluginSlug,
            'fields' => [
                'short_description' => true,
                'sections' => false,
                'versions' => false,
                'banners' => false,
                'reviews' => false,
                'ratings' => false,
                'downloaded' => false,
                'active_installs' => false,
                'last_updated' => false,
                'tags' => false,
            ],
        ]);

        if (is_wp_error($pluginInformation) || empty($pluginInformation->download_link)) {
            static::redirectWithNotice('plugin-install-unavailable', 'error');
        }

        /** @var \Automatic_Upgrader_Skin $skin Quiet upgrader skin for the admin-post workflow. */
        $skin = new \Automatic_Upgrader_Skin();

        /** @var \Plugin_Upgrader $upgrader Official WordPress plugin upgrader instance. */
        $upgrader = new \Plugin_Upgrader($skin);

        /** @var bool|\WP_Error $installResult Installation result from the upgrader. */
        $installResult = $upgrader->install($pluginInformation->download_link);

        if (is_wp_error($installResult) || $installResult !== true) {
            static::redirectWithNotice('plugin-install-failed', 'error');
        }

        static::redirectWithNotice('plugin-installed', 'success');
    }

    /**
     * Render an admin notice after redirecting back to the custom page.
     *
     * @return void
     */
    public static function renderAdminNotice(): void
    {
        if (!static::isRecommendedPluginsPage()) {
            return;
        }

        /** @var string $noticeKey Notice message key provided by the redirect. */
        $noticeKey = isset($_GET[static::NOTICE_QUERY_ARG]) ? sanitize_key(wp_unslash((string) $_GET[static::NOTICE_QUERY_ARG])) : '';

        /** @var string $noticeType Notice type provided by the redirect. */
        $noticeType = isset($_GET[static::NOTICE_TYPE_QUERY_ARG]) ? sanitize_key(wp_unslash((string) $_GET[static::NOTICE_TYPE_QUERY_ARG])) : 'success';

        if ($noticeKey === '') {
            return;
        }

        /** @var array<string, string> $noticeMessages Message map for supported notice keys. */
        $noticeMessages = static::getNoticeMessages();

        if (!isset($noticeMessages[$noticeKey])) {
            return;
        }

        /** @var string $noticeClass Final WordPress admin notice class. */
        $noticeClass = $noticeType === 'error' ? 'notice notice-error' : 'notice notice-success';
        ?>
        <div class="<?php echo esc_attr($noticeClass); ?> is-dismissible">
            <p><?php echo esc_html($noticeMessages[$noticeKey]); ?></p>
        </div>
        <?php
    }

    /**
     * Return the recommended plugins with derived status metadata.
     *
     * @return array<int, array<string, mixed>>
     */
    protected static function getRecommendedPlugins(): array
    {
        static::loadPluginFunctions();

        /** @var array<string, array<string, string>> $installedPlugins Installed plugin headers keyed by plugin file. */
        $installedPlugins = get_plugins();

        /** @var array<int, array<string, mixed>> $recommendedPlugins Recommended plugins enriched for rendering. */
        $recommendedPlugins = [];

        foreach (static::getRecommendedPluginDefinitions() as $pluginDefinition) {
            /** @var string $pluginSlug Recommended plugin slug. */
            $pluginSlug = (string) $pluginDefinition['slug'];

            /** @var string $pluginFile Matching installed plugin file, if available. */
            $pluginFile = static::resolvePluginFileBySlug($pluginSlug);

            /** @var array<string, string> $pluginHeader Installed plugin header data, if available. */
            $pluginHeader = $pluginFile !== '' && isset($installedPlugins[$pluginFile]) ? $installedPlugins[$pluginFile] : [];

            /** @var string $pluginStatus Derived plugin installation status. */
            $pluginStatus = 'missing';

            if ($pluginFile !== '') {
                $pluginStatus = is_plugin_active($pluginFile) ? 'active' : 'inactive';
            }

            $recommendedPlugins[] = [
                'slug' => $pluginSlug,
                'name' => isset($pluginHeader['Name']) && $pluginHeader['Name'] !== '' ? $pluginHeader['Name'] : (string) $pluginDefinition['name'],
                'description' => (string) $pluginDefinition['description'],
                'pluginFile' => $pluginFile,
                'status' => $pluginStatus,
                'statusLabel' => static::getStatusLabel($pluginStatus),
                'canInstall' => $pluginStatus === 'missing' ? static::canInstallFromWordPressOrg($pluginSlug) : false,
            ];
        }

        return $recommendedPlugins;
    }

    /**
     * Return the hard-coded recommended plugin definitions.
     *
     * @return array<int, array<string, string>>
     */
    protected static function getRecommendedPluginDefinitions(): array
    {
        return [
            [
                'slug' => PodcastPluginConstant::PLUGIN_SLUG,
                'name' => PodcastPluginConstant::PLUGIN_NAME,
                'description' => __('Podcast features for the A Ripple Song theme, including episode management and podcast feed support.', 'a-ripple-song'),
            ],
        ];
    }

    /**
     * Return the status label for a recommended plugin row.
     *
     * @param string $status Internal status key.
     * @return string
     */
    protected static function getStatusLabel(string $status): string
    {
        if ($status === 'active') {
            return __('Active', 'a-ripple-song');
        }

        if ($status === 'inactive') {
            return __('Installed but Inactive', 'a-ripple-song');
        }

        return __('Not Installed', 'a-ripple-song');
    }

    /**
     * Return whether a missing plugin can be installed from WordPress.org.
     *
     * @param string $pluginSlug Plugin slug.
     * @return bool
     */
    protected static function canInstallFromWordPressOrg(string $pluginSlug): bool
    {
        static::loadPluginInstallerFunctions();

        /** @var object|\WP_Error $pluginInformation Remote plugin information lookup result. */
        $pluginInformation = plugins_api('plugin_information', [
            'slug' => $pluginSlug,
            'fields' => [
                'sections' => false,
                'versions' => false,
                'banners' => false,
                'reviews' => false,
                'ratings' => false,
                'downloaded' => false,
                'active_installs' => false,
                'last_updated' => false,
                'tags' => false,
            ],
        ]);

        return !is_wp_error($pluginInformation) && !empty($pluginInformation->download_link);
    }

    /**
     * Return whether the slug belongs to the recommended plugin list.
     *
     * @param string $pluginSlug Plugin slug.
     * @return bool
     */
    protected static function isRecommendedPlugin(string $pluginSlug): bool
    {
        foreach (static::getRecommendedPluginDefinitions() as $pluginDefinition) {
            if ((string) $pluginDefinition['slug'] === $pluginSlug) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the plugin file path for a plugin slug.
     *
     * @param string $pluginSlug Plugin slug.
     * @return string
     */
    protected static function resolvePluginFileBySlug(string $pluginSlug): string
    {
        /** @var string $normalizedPluginSlug Sanitized plugin slug used for file matching. */
        $normalizedPluginSlug = sanitize_key($pluginSlug);

        if ($normalizedPluginSlug === '') {
            return '';
        }

        /** @var string $directoryPluginFile Conventional directory-based plugin bootstrap file. */
        $directoryPluginFile = $normalizedPluginSlug . '/' . $normalizedPluginSlug . '.php';

        if (file_exists(WP_PLUGIN_DIR . '/' . $directoryPluginFile)) {
            return $directoryPluginFile;
        }

        /** @var string $singleFilePlugin Conventional single-file plugin bootstrap file. */
        $singleFilePlugin = $normalizedPluginSlug . '.php';

        if (file_exists(WP_PLUGIN_DIR . '/' . $singleFilePlugin)) {
            return $singleFilePlugin;
        }

        /** @var array<string, array<string, string>> $installedPlugins Installed plugins keyed by plugin file. */
        $installedPlugins = get_plugins();

        foreach (array_keys($installedPlugins) as $pluginFile) {
            /** @var string $pluginDirectorySlug Directory slug for directory-based plugins. */
            $pluginDirectorySlug = dirname($pluginFile);

            /** @var string $pluginFileSlug File slug for single-file plugins. */
            $pluginFileSlug = basename($pluginFile, '.php');

            if ($pluginDirectorySlug === $normalizedPluginSlug || $pluginFileSlug === $normalizedPluginSlug) {
                return $pluginFile;
            }
        }

        return '';
    }

    /**
     * Return the activate action URL for a plugin row.
     *
     * @param string $pluginSlug Plugin slug.
     * @return string
     */
    protected static function getActivateUrl(string $pluginSlug): string
    {
        /** @var string $activateUrl Signed admin-post URL for plugin activation. */
        $activateUrl = add_query_arg([
            'action' => static::ACTIVATE_ACTION,
            'plugin' => $pluginSlug,
        ], admin_url('admin-post.php'));

        return wp_nonce_url($activateUrl, static::ACTIVATE_ACTION);
    }

    /**
     * Return the install action URL for a plugin row.
     *
     * @param string $pluginSlug Plugin slug.
     * @return string
     */
    protected static function getInstallUrl(string $pluginSlug): string
    {
        /** @var string $installUrl Signed admin-post URL for plugin installation. */
        $installUrl = add_query_arg([
            'action' => static::INSTALL_ACTION,
            'plugin' => $pluginSlug,
        ], admin_url('admin-post.php'));

        return wp_nonce_url($installUrl, static::INSTALL_ACTION);
    }

    /**
     * Redirect back to the recommended plugins screen with a notice payload.
     *
     * @param string $noticeKey Notice message key.
     * @param string $noticeType Notice type.
     * @return void
     */
    protected static function redirectWithNotice(string $noticeKey, string $noticeType): void
    {
        /** @var string $redirectUrl Redirect destination for the recommended plugins page. */
        $redirectUrl = add_query_arg([
            'page' => static::PAGE_SLUG,
            static::NOTICE_QUERY_ARG => $noticeKey,
            static::NOTICE_TYPE_QUERY_ARG => $noticeType,
        ], admin_url('admin.php'));

        wp_safe_redirect($redirectUrl);
        exit;
    }

    /**
     * Return the supported admin notice messages.
     *
     * @return array<string, string>
     */
    protected static function getNoticeMessages(): array
    {
        return [
            'recommended-plugin-not-found' => __('The requested recommended plugin could not be found.', 'a-ripple-song'),
            'plugin-file-missing' => __('The selected plugin is not installed on this site.', 'a-ripple-song'),
            'plugin-activation-failed' => __('The plugin could not be activated.', 'a-ripple-song'),
            'plugin-activated' => __('The plugin was activated successfully.', 'a-ripple-song'),
            'plugin-install-unavailable' => __('The plugin is not yet available for installation from WordPress.org.', 'a-ripple-song'),
            'plugin-install-failed' => __('The plugin could not be installed.', 'a-ripple-song'),
            'plugin-installed' => __('The plugin was installed successfully. You can activate it now.', 'a-ripple-song'),
        ];
    }

    /**
     * Return whether the current request targets the recommended plugins page.
     *
     * @return bool
     */
    protected static function isRecommendedPluginsPage(): bool
    {
        if (!is_admin()) {
            return false;
        }

        /** @var string $page Current admin page slug. */
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash((string) $_GET['page'])) : '';

        return $page === static::PAGE_SLUG;
    }

    /**
     * Load the core WordPress plugin management functions when needed.
     *
     * @return void
     */
    protected static function loadPluginFunctions(): void
    {
        if (!function_exists('get_plugins') || !function_exists('is_plugin_active') || !function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    }

    /**
     * Load the core WordPress plugin installer functions when needed.
     *
     * @return void
     */
    protected static function loadPluginInstallerFunctions(): void
    {
        static::loadPluginFunctions();

        if (!function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }

        if (!class_exists(\Plugin_Upgrader::class) || !class_exists(\Automatic_Upgrader_Skin::class)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/misc.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
    }
}
