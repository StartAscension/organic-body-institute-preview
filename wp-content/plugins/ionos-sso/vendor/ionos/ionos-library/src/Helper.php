<?php

namespace Ionos\Librarysso;

/**
 * Helper class
 */
class Helper {
	/**
	 * Get css url
	 *
	 * @param string $file CSS file url.
	 * @return string
	 */
	public static function get_css_url( $file = '' ) {
		return Options::get_plugin_url_path() . 'css/' . $file;
	}

	/**
	 * Get css path
	 *
	 * @param string $file CSS file path.
	 * @return string
	 */
	public static function get_css_path( $file = '' ) {
		return Options::get_plugin_dir_path() . 'css/' . $file;
	}

	/**
	 * Get js url
	 *
	 * @param string $file JS file url.
	 * @return string
	 */
	public static function get_js_url( $file = '' ) {
		return Options::get_plugin_dir_path() . 'js/' . $file;
	}

	/**
	 * Get js path
	 *
	 * @param string $file JS file path.
	 * @return mixed
	 */
	public static function get_js_path( $file = '' ) {
		return Options::get_plugin_dir_path() . 'js/' . $file;
	}
}
