<?php

namespace Ionos\Librarysso;

/**
 * Handles requests to the WordPress.org API.
 */
class WpOrgApi {

	/**
	 * Gets plugins or themes from WP.org API.
	 *
	 * @param string $type Type of requested results. ( plugin | theme ).
	 * @param array  $slugs List of slugs to get infos for.
	 * @param array  $fields List of fields to get for each request.
	 *
	 * @throws \InvalidArgumentException If invalid arguments are passed.
	 * @throws \RuntimeException If request class is missing.
	 */
	public static function get_info( $type = 'plugin', $slugs = [], $fields = [] ) {
		if ( ! in_array( $type, [ 'plugin', 'theme' ], true ) ) {
			throw new \InvalidArgumentException( 'Invalid type. $type must be string and must have the value "plugin" or "theme".' );
		}

		if ( ! is_array( $slugs ) ) {
			throw new \InvalidArgumentException( 'Invalid type. $slugs must be of type array.' );
		}

		if ( ! is_array( $fields ) ) {
			throw new \InvalidArgumentException( 'Invalid type. $fields must be of type array.' );
		}

		if ( empty( $slugs ) ) {
			return [];
		}

		$field_query_string = '';
		foreach ( $fields as $name => $value ) {
			$field_query_string .= "&fields[{$name}]={$value}";
		}

		$requests = [];
		foreach ( $slugs as $slug ) {
			$requests[] = [
				'url'  => "https://api.wordpress.org/{$type}s/info/1.2/?action={$type}_information&slug={$slug}{$field_query_string}",
				'type' => 'GET',
				'data' => [
					'locale' => get_user_locale(),
				],
			];
		}

		if ( class_exists( \WpOrg\Requests\Requests::class ) === true ) {
			$responses = \WpOrg\Requests\Requests::request_multiple( $requests );
		} elseif ( class_exists( 'Requests' ) === true ) {
			// Legacy Fallback for WordPress Versions prior 6.2.
			$responses = Requests::request_multiple( $requests );
		} else {
			throw new \RuntimeException( 'No Requests class found.' );
		}

		$info = [];
		foreach ( $responses as $response ) {
			if ( ! isset( $response->status_code ) || $response->status_code !== 200 ) {
				continue;
			}

			$data = (array) json_decode( $response->body, true );
			if ( empty( $data ) ) {
				continue;
			}

			$info[ $data['slug'] ] = $data;
		}

		$sorted_info = [];
		foreach ( $slugs as $slug ) {
			if ( isset( $info[ $slug ] ) ) {
				$sorted_info[ $slug ] = $info[ $slug ];
			}
		}

		return $sorted_info;
	}
}
