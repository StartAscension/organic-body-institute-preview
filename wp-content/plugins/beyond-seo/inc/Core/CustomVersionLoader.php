<?php
declare( strict_types=1 );

namespace RankingCoach\Inc\Core;

if ( !defined('ABSPATH') ) {
	exit;
}

use Doctrine\Persistence\Mapping\MappingException;
use Exception;
use RankingCoach\Inc\Core\Base\BaseConstants;
use RankingCoach\Inc\Core\Base\Traits\RcInstanceCreatorTrait;
use RankingCoach\Inc\Core\Base\Traits\RcLoggerTrait;
use stdClass;
use WP_Error;
use WP_Upgrader;
use function rcdc;

/**
 * Represents a system responsible for automatically updating an application
 * or software to the latest version available.
 *
 * This class is designed to handle the process of checking for updates,
 * downloading new versions, and applying updates in a seamless manner.
 */
class CustomVersionLoader {
    use RcLoggerTrait;
    use RcInstanceCreatorTrait;

    protected static ?self $instance = null;

	protected string $current_version;
	protected string $plugin_slug;
	protected string $plugin_file;
	protected string $update_url;
	protected string $plugin_name;
    protected bool $was_active_at_start = false;

	protected const HOUR_IN_SECONDS = 3600;

	public const RANKINGCOACH_UPDATE_PLUGIN_URL = 'https://wordpress.rankingcoach.com/update/archives/beyond-seo.json';

    /**
     * Auto_Updater constructor.
     *
     * @param string|null $plugin_file
     * @throws MappingException
     */
	public function __construct( ?string $plugin_file = null) {
        if(!$plugin_file) {
            $plugin_file = RANKINGCOACH_PLUGIN_BASENAME;
        }

        $pluginData = PluginConfiguration::getInstance()->getPluginData();

		$this->plugin_file = plugin_basename($plugin_file);
		$this->plugin_slug = dirname($this->plugin_file);
		$this->current_version = $pluginData['Version'];
		$this->plugin_name = $pluginData['Name'];
		$this->update_url = self::RANKINGCOACH_UPDATE_PLUGIN_URL;

        // Check if the plugin is currently active (before any potential update-deactivation)
        $active_plugins = get_option('active_plugins', []);
        $this->was_active_at_start = is_array($active_plugins) && in_array($this->plugin_file, $active_plugins, true);
        if (!$this->was_active_at_start && is_multisite()) {
            $active_sitewide_plugins = get_site_option('active_sitewide_plugins', []);
            $this->was_active_at_start = is_array($active_sitewide_plugins) && isset($active_sitewide_plugins[$this->plugin_file]);
        }

		// Hook into the update check
		add_filter('pre_set_site_transient_update_plugins', [ $this, 'checkUpdate'] );
		// Hook into the plugin info screen
		add_filter('plugins_api', [ $this, 'pluginInfo'], 20, 3);

        add_filter('upgrader_pre_install', [ $this, 'onUpgraderPreInstall'], 5, 2);
        add_filter('upgrader_post_install', [ $this, 'onUpgraderPostInstall'], 10, 3);
        add_filter('update_plugin_complete_actions', [ $this, 'filterUpdatePluginCompleteActions'], 10, 2);

        // Delete Symfony cache to reset caching after update
        add_action('upgrader_process_complete', function (WP_Upgrader $upgrader, array $options = []){
            try {
                rcdc();
            } catch (MappingException $e) {
                // Doing nothing if cache clearing fails
            }
        });
        // Hook to update the plugin version option after update completion
        add_action('upgrader_process_complete', [ $this, 'syncPluginVersionOnUpdate'], 10, 2);

        // Ensure plugin version is always synchronized
        $this->ensurePluginVersionSync();
	}

    /**
     * Get the instance of the class
     * @param string|null $params
     * @return CustomVersionLoader
     * @throws MappingException
     */
    public static function getInstance(?string $params = null): CustomVersionLoader {
        if (null === self::$instance) {
            self::$instance = new self($params);
            // Ensure plugin version is always synchronized
            self::$instance->ensurePluginVersionSync();
        }
        return self::$instance;
    }

	/**
	 * Force update check
	 * @return void
	 */
	public static function forceUpdateCheck(): void {
		// Delete the cached update information
		delete_transient(BaseConstants::OPTION_AUTOUPDATE_PLUGIN_UPDATE_INFO);

		// Delete the WordPress core updates cache
		delete_site_transient('update_plugins');

		// Trigger the update check
		wp_clean_plugins_cache();

		// Trigger the update check
		wp_update_plugins();
	}

	/**
	 * Check for updates
	 * @param $transient
	 * @return mixed
	 */
	public function checkUpdate($transient): mixed {

		if (empty($transient->checked)) {
			return $transient;
		}

		// Get update info from your server
		$remote_info = $this->getRemoteInfo();

		if ($remote_info && version_compare($this->current_version, $remote_info->version, '<')) {
			$obj = new stdClass();
			$obj->slug = $this->plugin_slug;
			$obj->new_version = $remote_info->version;
			$obj->url = $remote_info->url;
			$obj->package = $remote_info->download_url;
			$obj->name = $this->plugin_name;
			$obj->requires = $remote_info->requires;
			$obj->tested = $remote_info->tested;
			$obj->requires_php = $remote_info->requires_php;
			// Add plugin icons for the update page
			$obj->icons = $this->getPluginIcons($remote_info);

			$transient->response[$this->plugin_file] = $obj;
		}

		//wp_die(json_encode($transient));

		return $transient;
	}

	/**
	 * Get plugin info
	 * @param $false
	 * @param $action
	 * @param $response
	 * @return mixed
	 */
	public function pluginInfo($false, $action, $response): mixed {
		if ($action !== 'plugin_information') {
			return $false;
		}

		if ($response->slug !== $this->plugin_slug) {
			return $false;
		}

		$remote_info = $this->getRemoteInfo();

		if (!$remote_info) {
			return $false;
		}

		$response = new stdClass();
		$response->name = $this->plugin_name;
		$response->slug = $this->plugin_slug;
		$response->version = $remote_info->version;
		$response->author = $remote_info->author;
		$response->requires = $remote_info->requires;
		$response->requires_php = $remote_info->requires_php;
		$response->tested = $remote_info->tested;
		$response->last_updated = $remote_info->last_updated;
		$response->sections = [
			'description' => $remote_info->description,
			'changelog' => $remote_info->changelog
		];
		$response->download_link = $remote_info->download_url;
		// Add plugin icons for the plugin info popup
		$response->icons = $this->getPluginIcons($remote_info);

		return $response;
	}

	/**
	 * Get remote info
	 * @return mixed
	 */
	private function getRemoteInfo(): mixed {
		// Check transient first
		$cache = get_transient(BaseConstants::OPTION_AUTOUPDATE_PLUGIN_UPDATE_INFO);
		if ($cache !== false) {
			return $cache;
		}

		// Get info from your update server
		$response = wp_remote_get($this->update_url);

		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
			return false;
		}

		$data = json_decode(wp_remote_retrieve_body($response));

        if (property_exists($data, 'build_type' ) && $data->build_type !== 'production' && wp_get_environment_type() === 'production') {
            return false;
        }

		// Cache the response for 12 hours
		set_transient(BaseConstants::OPTION_AUTOUPDATE_PLUGIN_UPDATE_INFO, $data, 12 * self::HOUR_IN_SECONDS);

		return $data;
	}

    /**
     * Ensures the plugin version in wp_options matches the actual plugin version.
     * This acts as a safety net for cases where update hooks might not fire.
     *
     * @return void
     */
    private function ensurePluginVersionSync(): void {
        try {
            $plugin_data = PluginConfiguration::getInstance()->getPluginData();
            $current_version = $plugin_data['Version'] ?? '';
            $stored_version = get_option(BaseConstants::OPTION_PLUGIN_VERSION, '');

            if (!empty($current_version) && $stored_version !== $current_version) {
                $result = update_option(BaseConstants::OPTION_PLUGIN_VERSION, $current_version);
                if (!$result && get_option(BaseConstants::OPTION_PLUGIN_VERSION) !== $current_version) {
                    $this->log("Failed to update plugin version option to '{$current_version}'", 'ERROR');
                }
            }
        } catch (Exception $e) {
            $this->log('Error ensuring plugin version sync: ' . $e->getMessage(), 'ERROR');
        }
    }

    /**
     * Determines whether the beyond-seo plugin is currently being updated.
     * Handles single plugin updates ($hook_extra['plugin']), bulk updates ($hook_extra['plugins']),
     * and fallback request parameters ($_POST, $_GET).
     *
     * @param array $hook_extra
     * @return bool
     */
    protected function isBeyondSeoUpdating(array $hook_extra = []): bool {
        $legacy_basename = $this->plugin_file ?: plugin_basename(RANKINGCOACH_PLUGIN_BASENAME);
        $new_basename = 'beyondseo/beyondseo.php';

        $targets = [];

        if (!empty($hook_extra['plugin']) && is_string($hook_extra['plugin'])) {
            $targets[] = $hook_extra['plugin'];
        }

        if (!empty($hook_extra['plugins'])) {
            if (is_array($hook_extra['plugins'])) {
                $targets = array_merge($targets, $hook_extra['plugins']);
            } elseif (is_string($hook_extra['plugins'])) {
                $targets[] = $hook_extra['plugins'];
            }
        }

        if (!empty($hook_extra['slug']) && is_string($hook_extra['slug'])) {
            $targets[] = $hook_extra['slug'];
        }

        // Request parameter fallbacks (e.g. AJAX update-plugin or custom upgrader calls)
        if (!empty($_POST['plugin']) && is_string($_POST['plugin'])) {
            $targets[] = sanitize_text_field(wp_unslash($_POST['plugin']));
        }
        if (!empty($_GET['plugin']) && is_string($_GET['plugin'])) {
            $targets[] = sanitize_text_field(wp_unslash($_GET['plugin']));
        }
        if (!empty($_POST['slug']) && is_string($_POST['slug'])) {
            $targets[] = sanitize_text_field(wp_unslash($_POST['slug']));
        }
        if (!empty($_GET['slug']) && is_string($_GET['slug'])) {
            $targets[] = sanitize_text_field(wp_unslash($_GET['slug']));
        }
        if (!empty($_POST['plugins']) && is_array($_POST['plugins'])) {
            foreach ($_POST['plugins'] as $p) {
                if (is_string($p)) {
                    $targets[] = sanitize_text_field(wp_unslash($p));
                }
            }
        }
        if (!empty($_GET['plugins']) && is_array($_GET['plugins'])) {
            foreach ($_GET['plugins'] as $p) {
                if (is_string($p)) {
                    $targets[] = sanitize_text_field(wp_unslash($p));
                }
            }
        }

        foreach ($targets as $target) {
            if (
                $target === $legacy_basename
                || $target === $new_basename
                || $target === 'beyond-seo'
                || $target === 'beyondseo'
                || str_contains($target, 'beyond-seo.php')
                || str_contains($target, 'beyondseo.php')
                || str_contains($target, 'beyond-seo')
                || str_contains($target, 'beyondseo')
                || dirname($target) === 'beyond-seo'
                || dirname($target) === 'beyondseo'
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Intercepts upgrader_pre_install (priority 5) before core deactivates the plugin.
     * Records whether the plugin was active prior to upgrade in memory and transient.
     *
     * @param mixed $response Current installation response
     * @param array $hook_extra Extra arguments passed to hook
     * @return mixed
     */
    public function onUpgraderPreInstall(mixed $response, array $hook_extra = []): mixed {
        if (is_wp_error($response)) {
            return $response;
        }

        if (!$this->isBeyondSeoUpdating($hook_extra)) {
            return $response;
        }

        $active_plugins = get_option('active_plugins', []);
        $legacy_basename = $this->plugin_file ?: plugin_basename(RANKINGCOACH_PLUGIN_BASENAME);
        $is_active = is_array($active_plugins) && (
            in_array($legacy_basename, $active_plugins, true)
            || in_array('beyond-seo/beyond-seo.php', $active_plugins, true)
        );

        if (!$is_active && is_multisite()) {
            $active_sitewide_plugins = get_site_option('active_sitewide_plugins', []);
            $is_active = is_array($active_sitewide_plugins) && (
                isset($active_sitewide_plugins[$legacy_basename])
                || isset($active_sitewide_plugins['beyond-seo/beyond-seo.php'])
            );
        }

        if ($is_active || $this->was_active_at_start) {
            $this->was_active_at_start = true;
            set_transient('rankingcoach_migrating_slug_active', 1, 300);
            if (is_multisite()) {
                set_site_transient('rankingcoach_migrating_slug_active', 1, 300);
            }
            $this->log("Upgrader pre-install: Detected {$legacy_basename} was active before upgrade");
        }

        return $response;
    }

    /**
     * Intercepts upgrader_post_install (priority 10) after files have been extracted and moved.
     * Immediately migrates active_plugins and active_sitewide_plugins to point to beyondseo/beyondseo.php.
     *
     * @param mixed $response Current installation response
     * @param array $hook_extra Extra arguments passed to hook
     * @param array $result Installation result details
     * @return mixed
     */
    public function onUpgraderPostInstall(mixed $response, array $hook_extra = [], array $result = []): mixed {
        if (is_wp_error($response)) {
            return $response;
        }

        if (!$this->isBeyondSeoUpdating($hook_extra)) {
            return $response;
        }

        $legacy_basename = $this->plugin_file ?: plugin_basename(RANKINGCOACH_PLUGIN_BASENAME);
        $new_basename = 'beyondseo/beyondseo.php';

        $active_plugins = get_option('active_plugins', []);
        $is_in_active_plugins = is_array($active_plugins) && (
            in_array($legacy_basename, $active_plugins, true)
            || in_array('beyond-seo/beyond-seo.php', $active_plugins, true)
        );

        $is_in_sitewide = false;
        if (is_multisite()) {
            $active_sitewide_plugins = get_site_option('active_sitewide_plugins', []);
            $is_in_sitewide = is_array($active_sitewide_plugins) && (
                isset($active_sitewide_plugins[$legacy_basename])
                || isset($active_sitewide_plugins['beyond-seo/beyond-seo.php'])
            );
        }

        $was_active = $this->was_active_at_start
            || $is_in_active_plugins
            || $is_in_sitewide
            || (bool) get_transient('rankingcoach_migrating_slug_active')
            || (is_multisite() && (bool) get_site_transient('rankingcoach_migrating_slug_active'));

        if ($was_active) {
            $this->migrateActivePluginSlug($legacy_basename, $new_basename);
        }

        $this->migrateModuleSettings();

        delete_transient(BaseConstants::OPTION_AUTOUPDATE_PLUGIN_UPDATE_INFO);
        delete_site_transient('update_plugins');
        wp_clean_plugins_cache();

        return $response;
    }

    /**
     * Filters the action links displayed after a plugin update is completed.
     * Rewrites any reactivation links pointing to beyond-seo/beyond-seo.php so they target beyondseo/beyondseo.php.
     *
     * @param array $actions Array of HTML action links
     * @param string $plugin Plugin basename
     * @return array
     */
    public function filterUpdatePluginCompleteActions(array $actions, string $plugin): array {
        $legacy_basename = $this->plugin_file ?: plugin_basename(RANKINGCOACH_PLUGIN_BASENAME);
        $new_basename = 'beyondseo/beyondseo.php';

        if ($plugin === $legacy_basename || str_contains($plugin, 'beyond-seo.php') || $plugin === $new_basename) {
            foreach ($actions as $key => $action_html) {
                if (is_string($action_html)) {
                    $actions[$key] = str_replace(
                        [
                            'beyond-seo%2Fbeyond-seo.php',
                            'beyond-seo/beyond-seo.php',
                            'plugin=beyond-seo',
                        ],
                        [
                            'beyondseo%2Fbeyondseo.php',
                            'beyondseo/beyondseo.php',
                            'plugin=beyondseo',
                        ],
                        $action_html
                    );
                }
            }
        }

        return $actions;
    }

    /**
     * Syncs the plugin version to the database after a plugin update.
     * Includes validation, error handling, and only writes if version changed.
     *
     * @param WP_Upgrader $upgrader The upgrader instance
     * @param array $options Update options
     * @return void
     */
    public function syncPluginVersionOnUpdate(WP_Upgrader $upgrader, array $options = []): void {
        // Only process plugin updates
        if (!isset($options['action']) || $options['action'] !== 'update' || !isset($options['type']) || $options['type'] !== 'plugin') {
            return;
        }

        if (!$this->isBeyondSeoUpdating($options)) {
            return;
        }

        $legacy_basename = $this->plugin_file ?: plugin_basename(RANKINGCOACH_PLUGIN_BASENAME);
        $new_basename = 'beyondseo/beyondseo.php';

        try {
            $active_plugins = get_option('active_plugins', []);
            $is_in_active_plugins = is_array($active_plugins) && (
                in_array($legacy_basename, $active_plugins, true)
                || in_array('beyond-seo/beyond-seo.php', $active_plugins, true)
            );

            $is_in_sitewide = false;
            if (is_multisite()) {
                $active_sitewide_plugins = get_site_option('active_sitewide_plugins', []);
                $is_in_sitewide = is_array($active_sitewide_plugins) && (
                    isset($active_sitewide_plugins[$legacy_basename])
                    || isset($active_sitewide_plugins['beyond-seo/beyond-seo.php'])
                );
            }

            $was_active = $this->was_active_at_start
                || $is_in_active_plugins
                || $is_in_sitewide
                || (bool) get_transient('rankingcoach_migrating_slug_active')
                || (is_multisite() && (bool) get_site_transient('rankingcoach_migrating_slug_active'));

            if ($was_active) {
                $this->migrateActivePluginSlug($legacy_basename, $new_basename);
            }

            $this->migrateModuleSettings();

            delete_transient('rankingcoach_migrating_slug_active');
            if (is_multisite()) {
                delete_site_transient('rankingcoach_migrating_slug_active');
            }

            delete_transient(BaseConstants::OPTION_AUTOUPDATE_PLUGIN_UPDATE_INFO);
            delete_site_transient('update_plugins');

            // Get the current version from plugin header
            // Since we are in the old plugin's hook, PluginConfiguration might 
            // still refer to the old one or the new one depending on how it works.
            $pluginData = PluginConfiguration::getInstance()->getPluginData();
            $current_version = $pluginData['Version'] ?? '';

            if (!empty($current_version)) {
                // Update version in DB
                update_option(BaseConstants::OPTION_PLUGIN_VERSION, $current_version);
            }

            wp_clean_plugins_cache();
		} catch (Exception $e) {
			$this->log('Error in syncPluginVersionOnUpdate: ' . $e->getMessage(), 'ERROR');
		}
	}

    /**
     * Migrates all module settings from the legacy beyond-seo slug to the new beyondseo slug.
     * Iterates over all known modules and dynamically searches for any additional module options.
     *
     * @return void
     */
    public function migrateModuleSettings(): void {
        $module_names = [
            'advancedAnalytics',
            'backlinkManager',
            'brokenLinkChecker',
            'competitorAnalysis',
            'contentAnalysis',
            'contentDuplicationChecker',
            'contentScheduler',
            'coreWebVitalsMonitor',
            'ecommerceSeoOptimizer',
            'imageOptimizer',
            'internalLinkSuggestions',
            'internationalSeo',
            'keywordRankTracker',
            'keywordResearchTool',
            'linkAnalyzer',
            'linkCounter',
            'localSeoGmb',
            'localSeoOptimizer',
            'metaTags',
            'mobileSeoAnalyzer',
            'pageSpeed',
            'performanceOptimizer',
            'rankingcoachDashboard',
            'redirectManager',
            'schemaMarkup',
            'seoAudit',
            'serpFeatureTracking',
            'sitemap',
            'socialMedia',
            'specializedPluginIntegrations',
            'textOptimizer',
            'userEngagementMetrics',
            'videoSeo',
            'voiceSearchOptimization',
            'webmasterTools',
        ];

        $sentinel = new stdClass();

        // 1. Migrate known module settings
        foreach ($module_names as $module_name) {
            $legacy_option = 'beyond-seo_module_(' . $module_name . ')_settings';
            $new_option    = 'beyondseo_module_(' . $module_name . ')_settings';

            $legacy_value = get_option($legacy_option, $sentinel);
            if ($legacy_value !== $sentinel) {
                if (get_option($new_option, $sentinel) === $sentinel) {
                    update_option($new_option, $legacy_value);
                }
                delete_option($legacy_option);
                $this->log("Migrated module settings for {$module_name} from {$legacy_option} to {$new_option}");
            }
        }

        // 2. Dynamically find and migrate any remaining beyond-seo_module_ options in the database
        global $wpdb;
        if (isset($wpdb) && $wpdb instanceof \wpdb) {
            $like_pattern = $wpdb->esc_like('beyond-seo_module_') . '%';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT option_name, option_value, autoload FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $like_pattern
                ),
                ARRAY_A
            );

            if (is_array($results)) {
                foreach ($results as $row) {
                    $legacy_key = $row['option_name'] ?? '';
                    if (empty($legacy_key)) {
                        continue;
                    }

                    $new_key = 'beyondseo' . substr($legacy_key, strlen('beyond-seo'));
                    $target_val = get_option($new_key, $sentinel);
                    if ($target_val === $sentinel) {
                        $unserialized_val = maybe_unserialize($row['option_value']);
                        $autoload = in_array($row['autoload'] ?? '', ['yes', 'on', '1', true], true);
                        update_option($new_key, $unserialized_val, $autoload);
                    }
                    delete_option($legacy_key);
                    $this->log("Migrated dynamic module option from {$legacy_key} to {$new_key}");
                }
            }
        }
    }

    /**
     * Migrates the active plugin slug in WordPress options to ensure the new plugin
     * remains activated after a folder/file rename during update.
     * 
     * @param string $legacy_basename
     * @param string $new_basename
     */
    private function migrateActivePluginSlug(string $legacy_basename, string $new_basename): void {
        // Handle regular active plugins
        $active_plugins = get_option('active_plugins', []);
        if (!is_array($active_plugins)) {
            $active_plugins = [];
        }

        $filtered_active = [];

        foreach ($active_plugins as $plugin) {
            if (
                $plugin === $legacy_basename
                || $plugin === 'beyond-seo/beyond-seo.php'
                || (is_string($plugin) && dirname($plugin) === 'beyond-seo')
                || (is_string($plugin) && str_contains($plugin, 'beyond-seo.php'))
            ) {
                continue; // Explicitly strip legacy entry
            }
            $filtered_active[] = $plugin;
        }

        // Add the new plugin basename
        if (!in_array($new_basename, $filtered_active, true)) {
            $filtered_active[] = $new_basename;
        }

        $cleaned_active = array_values(array_unique($filtered_active));
        if ($cleaned_active !== $active_plugins) {
            update_option('active_plugins', $cleaned_active);
            $this->log("Migrated active plugin status from {$legacy_basename} to {$new_basename}");
        }

        // Handle multisite network-active plugins
        if (is_multisite()) {
            $active_sitewide = get_site_option('active_sitewide_plugins', []);
            if (is_array($active_sitewide)) {
                $sitewide_changed = false;
                $activation_time = time();

                foreach ($active_sitewide as $key => $val) {
                    if (
                        $key === $legacy_basename
                        || $key === 'beyond-seo/beyond-seo.php'
                        || (is_string($key) && dirname($key) === 'beyond-seo')
                        || (is_string($key) && str_contains($key, 'beyond-seo.php'))
                    ) {
                        $activation_time = $val;
                        unset($active_sitewide[$key]);
                        $sitewide_changed = true;
                    }
                }

                if (!isset($active_sitewide[$new_basename])) {
                    $active_sitewide[$new_basename] = $activation_time;
                    $sitewide_changed = true;
                }

                if ($sitewide_changed) {
                    update_site_option('active_sitewide_plugins', $active_sitewide);
                    $this->log("Migrated network-active plugin from {$legacy_basename} to {$new_basename}");
                }
            }
        }
    }

	/**
	 * Get plugin icons for the update page
	 *
	 * @return array Array of icon URLs
	 */
	private function getPluginIcons(?object $remote_info = null): array {
		try {
			$icon_url = plugins_url('inc/Core/Admin/assets/icons/rC-color-whistle.svg', $this->plugin_file);
			
			return [
				'svg' => $icon_url,
				'1x' => $icon_url,
				'2x' => $icon_url,
				'default' => $icon_url
			];
		} catch (Exception $e) {
			$this->log('Error loading plugin icons: ' . $e->getMessage(), 'WARNING');
			return [];
		}
	}
}
