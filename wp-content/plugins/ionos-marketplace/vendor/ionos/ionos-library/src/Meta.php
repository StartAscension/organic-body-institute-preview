<?php

namespace Ionos\Librarymarketplace;

/**
 * Meta class
 * Gets meta information from plugin header and provides access it
 */
class Meta {

	/**
	 * Array of meta fields.
	 *
	 * @var array
	 */
	private static $meta = [];

	/**
	 * Provides access to a single meta field
	 *
	 * @param string $meta_name Meta Field name.
	 *
	 * @return string
	 */
	public static function get_meta( $meta_name ) {
		if ( empty( self::$meta ) ) {
			$plugin_main_file_path = isset( Options::$plugin_file_path ) ? Options::$plugin_file_path : Options::get_plugin_dir_path() . Options::get_plugin_slug() . '.php';
			self::$meta            = get_plugin_data( $plugin_main_file_path );
		}

		if ( ! empty( self::$meta ) && array_key_exists( $meta_name, self::$meta ) ) {
			return self::$meta[ $meta_name ];
		}

		return '';
	}
}
