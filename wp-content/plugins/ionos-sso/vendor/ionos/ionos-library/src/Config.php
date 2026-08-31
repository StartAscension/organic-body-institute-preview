<?php

namespace Ionos\Librarysso;

/**
 * Config singleton
 */
class Config {

	/**
	 * Instance of Config singleton.
	 *
	 * @var Config
	 */
	private static $instance;

	/**
	 * Config json as array.
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Data Provider which is used for fetching data.
	 *
	 * @var Data_Provider\Cloud
	 */
	private $data_provider;

	/**
	 * Create Singleton object
	 *
	 * @param Data_Provider\Cloud $data_provider Data Provider used for fetching data.
	 *
	 * @return Config
	 */
	public static function get_instance( Data_Provider\Cloud $data_provider = null ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $data_provider );
		}

		return self::$instance;
	}

	/**
	 * Delete Singleton object
	 */
	public static function delete_instance() {
		self::$instance = null;
	}

	/**
	 * Singleton wrapper function to retrieve a specific parameter without much code
	 * Call: Config::get()
	 *
	 * @param string $path Configuration path.
	 *
	 * @return string
	 */
	public static function get( string $path ) {
		return self::get_instance()->get_parameter( $path );
	}

	/**
	 * Config constructor.
	 *
	 * @param Data_Provider\Cloud|null $data_provider Data Provider used for fetching data.
	 */
	private function __construct( Data_Provider\Cloud $data_provider = null ) {
		if ( ! $data_provider instanceof Data_Provider\Cloud ) {
			$const_name = 'IONOS_' . strtoupper( Options::get_plugin_name() ) . '_CONFIG_URL';

			$service_urls = null;
			if ( defined( $const_name ) && constant( $const_name ) ) {
				$service_urls = [
					'config' => constant( $const_name ),
				];
			}

			$this->data_provider = new Data_Provider\Cloud( 'config', $service_urls );
		} else {
			$this->data_provider = $data_provider;
		}
		$this->config = $this->data_provider->request();
	}

	/**
	 * Returns specific plugin configuration element
	 *
	 * @param string $path Path to configuration element.
	 *
	 * @return mixed
	 */
	public function get_parameter( string $path ) {
		// Any configuration parameter can be overridden with a WP Option.
		$option_key = strtolower( Options::get_tenant_name() )
			. '_' . str_replace( '-', '_', Options::get_plugin_name() )
			. '_' . str_replace(
				'.',
				'_',
				$path
			);

		$option = \get_option( $option_key );
		if ( $option !== false ) {
			return $option;
		}

		// If no option is set, retrieve parameter from config object.
		$element = $this->config;
		foreach ( explode( '.', $path ) as $key ) {
			if ( is_array( $element ) && array_key_exists( $key, $element ) ) {
				$element = $element[ $key ];
			} else {
				return false;
			}
		}

		return $element;
	}
}
