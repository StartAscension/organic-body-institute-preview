<?php

namespace Ionos\Librarymarketplace;

use Exception;
use Ionos\PluginStateHookHandler\PluginState;

require_once 'Meta.php';

/**
 * Options class
 * Manages/retrieves global WP options and options set during call
 */
class Options {
	/**
	 * Plugin Name.
	 *
	 * @var string
	 */
	private static string $plugin_name;

	/**
	 * Path to plugin file.
	 *
	 * @var string
	 */
	public static string $plugin_file_path;

	/**
	 * Set plugin name
	 *
	 * @param string $plugin_name Plugin name.
	 */
	public static function set_plugin_name( string $plugin_name ) {
		self::$plugin_name = $plugin_name;
	}

	/**
	 * Return complete slug of plugin
	 *
	 * @return string
	 */
	public static function get_plugin_slug() {
		return 'ionos-' . self::get_plugin_name();
	}

	/**
	 * Get plugin name.
	 *
	 * @return string
	 */
	public static function get_plugin_name() {
		return self::$plugin_name;
	}

	/**
	 * Get tenant name from option.
	 *
	 * @return string
	 */
	public static function get_tenant_name() {
		return strtolower( \get_option( 'ionos_group_brand', 'ionos' ) );
	}

	/**
	 * Get the plugin directory path.
	 *
	 * @return string
	 */
	public static function get_plugin_dir_path() {
		if ( false !== strpos( __DIR__, WPMU_PLUGIN_DIR ) ) {
			return trailingslashit( WPMU_PLUGIN_DIR . '/' . self::get_plugin_slug() );
		}

		return trailingslashit( WP_PLUGIN_DIR . '/' . self::get_plugin_slug() );
	}

	/**
	 * Get the plugin directory url.
	 *
	 * @return string
	 */
	public static function get_plugin_url_path() {
		if ( false !== strpos( __DIR__, WPMU_PLUGIN_DIR ) ) {
			return trailingslashit( WPMU_PLUGIN_URL . '/' . self::get_plugin_slug() );
		}

		return trailingslashit( WP_PLUGIN_URL . '/' . self::get_plugin_slug() );
	}

	/**
	 * Get the plugin main file path.
	 *
	 * @param string $path Plugin file path.
	 *
	 * @throws Exception If plugin name is not set.
	 *
	 * @return string
	 */
	public static function get_main_plugin_file_path( $path ) {
		if ( empty( self::get_plugin_name() ) ) {
			throw new Exception( 'Call `set_plugin_name` first.' );
		}
		self::$plugin_file_path = \apply_filters( 'ionos_library_main_plugin_file_path', $path, self::get_plugin_name() );
		return self::$plugin_file_path;
	}

	/**
	 * Get the plugin main file basename.
	 *
	 * @param string $path Plugin file path.
	 *
	 * @throws Exception If plugin name is not set.
	 *
	 * @return string
	 */
	public static function get_main_plugin_file_basename( $path ) {
		return plugin_basename( self::get_main_plugin_file_path( $path ) );
	}

	/**
	 * Return the installation mode provided during the installation
	 * (available as WP option)
	 *
	 * @return string
	 */
	public static function get_installation_mode() {
		return strtolower( \get_option( self::get_tenant_name() . '_install_mode', 'standard' ) );
	}

	/**
	 * Return the contract's market value provided by the installation
	 *
	 * @return string
	 */
	public static function get_market() {

		$default_market    = 'US';
		$supported_markets = [ 'DE', 'CA', 'GB', 'UK', 'US', 'ES', 'MX', 'FR', 'IT' ];

		$market = (string) strtoupper( \get_option( self::get_tenant_name() . '_market', $default_market ) );

		if ( ! $market || ! in_array( $market, $supported_markets, true ) ) {
			$market = $default_market;
		}

		return $market;
	}

	/**
	 * Calls package to remove the library transients on uninstall.
	 *
	 * @param string $plugin_file_path Plugin file path.
	 *
	 * @throws Exception If plugin name is not set.
	 */
	public static function clean_up( $plugin_file_path ) {
		$transient_name_prefix = self::get_tenant_name() . '_' . self::get_plugin_name();
		$transients            = [
			"{$transient_name_prefix}_config",
			"{$transient_name_prefix}_plugin_info",
		];

		( new PluginState( $plugin_file_path ) )
			->register_cleanup_hooks()
			->remove_transients_on_uninstall( $transients );
	}
}
