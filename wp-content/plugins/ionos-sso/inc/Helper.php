<?php
//phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

namespace Ionos\SSO;

use Ionos\Librarysso\Config;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Helper class
 */
class Helper {

	/**
	 * Get the url of the css folder
	 *
	 * @param string $file // css file name.
	 *
	 * @return string
	 */
	public static function get_css_url( $file = '' ) {
		return plugins_url( 'css/' . $file, __DIR__ );
	}

	/**
	 * Returns path to css folder with optional file.
	 *
	 * @param string $file File name.
	 *
	 * @return string
	 */
	public static function get_css_path( $file = '' ) {
		return self::get_plugin_dir_path() . 'css/' . $file;
	}

	/**
	 * Returns plugin dir path.
	 *
	 * @return string
	 */
	public static function get_plugin_dir_path() {
		return plugin_dir_path( __DIR__ );
	}

	/**
	 * Is the SSO enabled/authorized?
	 */
	public static function is_enabled() {
		if ( 'localhost' === wp_parse_url( home_url(), PHP_URL_HOST ) ) {
			return true;
		}
		return '1' === Config::get( 'main.enabled' ) && is_ssl();
	}
}
