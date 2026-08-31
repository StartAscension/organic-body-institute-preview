<?php

namespace Ionos\Librarysso;

/**
 * Manager class
 */
class Updater {

	/**
	 * The path to where the info.json files are located. With trailing slash.
	 *
	 * @var string
	 */
	private $update_server_full_path = 'https://s3-de-central.profitbricks.com/web-hosting/ionos-group/';

	/**
	 * Updater constructor.
	 */
	public function __construct() {
		add_filter( 'update_plugins_' . Options::get_plugin_slug(), [ $this, 'check_update' ], 10, 0 );
		add_filter( 'auto_update_plugin', [ $this, 'force_auto_update' ], 10, 2 );
		add_filter( 'plugins_api', [ $this, 'plugin_popup' ], 10, 3 );
	}

	/**
	 * Gets the latest info for the plugin from the server.
	 * The plugin, that uses this filter, must define "Update URI" in the header, that is set to
	 * the slug of the plugin, ionos-security, ionos-loop, e.g.
	 *
	 * @return array|null The info for the plugin. Null in case of error.
	 */
	public function check_update() {
		$slug   = Options::get_plugin_slug();
		$update = wp_remote_get( $this->update_server_full_path . $slug . '.info.json' );

		if ( is_wp_error( $update ) || 200 !== wp_remote_retrieve_response_code( $update ) ) {
			return;
		}

		$body = json_decode( wp_remote_retrieve_body( $update ), true );

		$update = [
			'version' => $body['latest_version'],
			'slug'    => $slug,
			'package' => $body['download_url'],
		];
		return $update;
	}

	/**
	 * Returns the update information popup
	 *
	 * @param object|bool $result The result object or false.
	 * @param string      $action The type of information being requested.
	 * @param object      $args Plugin arguments.
	 *
	 * @return bool|object
	 */
	public function plugin_popup( $result, string $action, object $args ) {
		$slug = Options::get_plugin_slug();

		if ( $action !== 'plugin_information' ) {
			return $result;
		}

		if ( ! empty( $args->slug ) && $args->slug === $slug ) {
			$data_provider = new Data_Provider\Cloud( 'plugin_info' );
			$update_info   = $data_provider->request();

			if ( $this->is_valid_update_info( $update_info ) === false ) {
				return null;
			}

			if ( is_admin() ) {
				if ( ! function_exists( 'get_plugin_data' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}
				$plugin_data = get_plugin_data( __FILE__ );
			}

			$result = (object) [
				'name'              => $plugin_data['Name'] ?? '',
				'slug'              => $args->slug,
				'requires'          => $update_info['requires_wp'] ?? $args->wp_version,
				'tested'            => $update_info['tested_to'] ?? $args->wp_version,
				'icons'             => Config::get( 'branding.icon_svg' ) ? [ 'svg' => Config::get( 'branding.icon_svg' ) ] : [],
				'version'           => $update_info['latest_version'],
				'last_updated'      => $update_info['last_updated'],
				'homepage'          => $plugin_data['Homepage'] ?? '',
				'short_description' => $plugin_data['Description'] ?? '',
				'sections'          => [
					'Changelog' => $this->render_changelog( $update_info['changelog'] ),
				],
				'download_link'     => $update_info['download_url'],
			];
		}

		return $result;
	}

	/**
	 * Return changelog html
	 *
	 * @param  array $changelog Changelog array.
	 *
	 * @return string
	 */
	public function render_changelog( array $changelog ) {
		$result = '';

		if ( is_array( $changelog ) ) {
			foreach ( $changelog as $version ) {
				if ( isset( $version['version'] ) ) {
					$result .= '<h4>' . $version['version'] . '</h4>';
					if ( isset( $version['changes'] ) && is_array( $version['changes'] ) ) {
						$result .= '<ul>';
						foreach ( $version['changes'] as $change ) {
							$result .= '<li>' . $change . '</li>';
						}
						$result .= '</ul>';
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Force auto update
	 *
	 * @param object $update Update object.
	 * @param object $item Plugin object.
	 *
	 * @return bool|object
	 */
	public function force_auto_update( $update, $item ) {
		if ( isset( $item->slug ) && $item->slug === Options::get_plugin_slug() ) {
			return true;
		} else {
			return $update;
		}
	}

	/**
	 * Validate info
	 *
	 * @param array $data Update info.
	 *
	 * @return bool
	 */
	private function is_valid_update_info( $data ) {
		return is_array( $data )
				&& array_key_exists( 'icons', $data )
				&& array_key_exists( 'changelog', $data )
				&& array_key_exists( 'download_url', $data ) && is_string( $data['download_url'] )
				&& array_key_exists( 'latest_version', $data ) && is_string( $data['latest_version'] )
				&& array_key_exists( 'last_updated', $data );
	}
}
