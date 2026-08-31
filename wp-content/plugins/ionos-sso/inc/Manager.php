<?php

namespace Ionos\SSO;

use Ionos\Librarysso\Options;

/**
 * Manager class
 */
class Manager {
	const JUMP_HOST_DOMAIN         = 'webapps-sso.hosting.ionos.com';
	const WP_PSS_API               = 'https://cisapi.hosting.ionos.com/%s/managed-wordpress/provisionings?domain-binding=%s';
	const DEFAULT_OAUTH_ERROR      = 'The login process via SSO failed';
	const TRANSIENT_PREFIX         = 'ionos_sso_';
	const TRANSIENT_ADSERVER_TOKEN = 'ionos_adserver_token';

	/**
	 * SSO constructor.
	 */
	public function __construct() {
		add_action( 'authenticate', [ $this, 'oauth' ] );
		add_action( 'wp_logout', [ $this, 'cleanup_after_logout' ] );
	}

	/**
	 * Handle oauth process.
	 *
	 * @param  \WP_User|null $user User object or null.
	 *
	 * @return null | \WP_User
	 */
	public function oauth( $user ) {

		if ( $user instanceof \WP_User ) {
			return $user;
		}

		if ( Helper::is_enabled() && isset( $_GET['action'] ) ) {
			switch ( $_GET['action'] ) {

				// Start signing process after recognizing the CP parameter.
				case 'ionos_oauth_register':
					$this->register_domain();
					$this->oauth_authenticate();
					break;

				// Validate access after signing on CP.
				case 'ionos_oauth_authenticate':
					return $this->validate_user_access();

				default:
					break;
			}
		}

		return null;
	}

	/**
	 * Validate access token coming from CP.
	 *
	 * @return null | \WP_User
	 */
	public function validate_user_access() {

		if ( ! isset( $_GET['access_token'] )
			|| ! isset( $_GET['state'] )
			|| $_GET['state'] !== get_transient( self::TRANSIENT_PREFIX . 'state' )
		) {
			$this->login_error( 'No access_token or state' );
			return null;
		}

		// Decrypt access token.
		$decoded_access_token = $this->decrypt_access_token(
			$_GET['access_token'],
			get_transient( self::TRANSIENT_PREFIX . 'iv' ),
			get_transient( self::TRANSIENT_PREFIX . 'secret' )
		);
		if ( $decoded_access_token === false ) {
			$this->login_error( 'Access token could not be decrypted' );
			return null;
		}

		// Validate access token by WordPress PSS.
		$is_valid_access_token = $this->check_customer_access_authorization( $decoded_access_token );

		// If valid, log the first admin user.
		if ( $is_valid_access_token === true ) {
			// Add user information to transient.
			delete_transient( self::TRANSIENT_PREFIX . 'state' );
			delete_transient( self::TRANSIENT_PREFIX . 'secret' );
			delete_transient( self::TRANSIENT_PREFIX . 'iv' );
			set_transient(
				self::TRANSIENT_PREFIX . 'access_token',
				base64_encode( $decoded_access_token ),
				60 * 60 * 2
			);

			$this->save_adserver_token( $decoded_access_token );

			return $this->get_admin_user();

		} else {
			$this->login_error( 'Invalid access token' );
		}

		return null;
	}

	/**
	 * Saves the token for the adserver.
	 *
	 * @param string $access_token The access token.
	 * @return null
	 */
	public function save_adserver_token( $access_token ) {
		if ( \get_transient( self::TRANSIENT_ADSERVER_TOKEN ) ) {
			return;
		}

		$response = wp_remote_post(
			'https://frontend-services.ionos.com/session/session/oauth',
			[
				'headers' => [
					'Accept' => 'application/xml',
					'token'  => $access_token,
				],
			]
		);

		if ( is_wp_error( $response ) ||
			! isset( $response['response']['code'] )
			|| 200 !== $response['response']['code']
		) {
			return false;
		}

		$xml = simplexml_load_string( $response['body'] );

		if ( false === $xml || ! isset( $xml['token'] ) ) {
			return false;
		}

		\set_transient(
			self::TRANSIENT_ADSERVER_TOKEN,
			(string) $xml['token'],
			HOUR_IN_SECONDS * 3
		);

		return true;
	}

	/**
	 * Register Domain to JumpHost and store keys into the session.
	 * The keys are needed to encrypt access_token
	 */
	private function register_domain() {
		$oauth_register_result = $this->oauth_register();

		set_transient(
			self::TRANSIENT_PREFIX . 'state',
			$oauth_register_result['state'],
			7200
		);
		set_transient(
			self::TRANSIENT_PREFIX . 'secret',
			$oauth_register_result['secret'],
			600
		);
		set_transient(
			self::TRANSIENT_PREFIX . 'iv',
			$oauth_register_result['iv'],
			600
		);
	}

	/**
	 * Redirect to IONOS OAuth login form.
	 *
	 * @return mixed [ $state, $iv, $secret ]
	 */
	private function oauth_register() {
		// parse url parameters to array.
		! empty( $_GET['redirect_to'] )
			? parse_str( parse_url( $_GET['redirect_to'], PHP_URL_QUERY ), $param_array )
			: $param_array = $_GET;

		$param_array = array_merge( $param_array, [ 'action' => 'ionos_oauth_authenticate' ] );

		$response = wp_remote_post(
			'https://' . self::JUMP_HOST_DOMAIN . '/api/wordpress/register',
			[
				'method' => 'POST',
				'body'   => [
					'wp_callback' => add_query_arg(
						$param_array,
						$this->strip_www( wp_login_url() )
					),
					'market'      => Options::get_market(),
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			$this->login_error( 'Could not communicate with OAuth-Endpoint' );
			return null;
		}

		$response_decoded = json_decode( $response['body'], true );
		if ( is_array( $response ) && isset( $response['response'] )
			&& isset( $response['response']['code'] )
			&& 200 === $response['response']['code']
			&& is_array( $response_decoded )
			&& isset( $response_decoded['state'], $response_decoded['secret'], $response_decoded['iv'] )
		) {
			return $response_decoded;
		} else {
			$this->login_error( 'Could not decode OAuth response' );
			return null;
		}
	}

	/**
	 * Throw error message.
	 *
	 * @param  string $msg Error message to display.
	 */
	private function login_error( $msg = '' ) {
		add_filter(
			'wp_login_errors',
			function ( $errors ) use ( $msg ) {
				$errors->add(
					'ionos_oauth_error',
					'<strong>Error</strong>: ' . $msg
				);
				return $errors;
			}
		);
	}

	/**
	 * Redirect to jumphost.
	 */
	private function oauth_authenticate() {
		$state = get_transient( self::TRANSIENT_PREFIX . 'state' );
		if ( false === $state ) {
			$this->login_error( 'Could not get transient' );

			return null;
		}

		$param = [
			'state'  => $state,
			'action' => 'ionos_oauth_authenticate',
		];

		wp_redirect( 'https://' . self::JUMP_HOST_DOMAIN . '/wordpress/login?' . http_build_query( $param ) );
	}

	/**
	 * Decrypt the access token.
	 *
	 * @param  string $string  // String that have to decrypted.
	 * @param  string $iv  // Individual value needed for decrypting.
	 * @param  string $secret  // needed for decrypting.
	 *
	 * @return string|false
	 */
	private function decrypt_access_token( $string, $iv, $secret ) {
		return openssl_decrypt( $string, 'AES-256-CBC', $secret, 0, $iv );
	}

	/**
	 * Validate access token via WP_PSS_API
	 *
	 * @param  string|null $access_token  // Decrypted access token.
	 *
	 * @return bool
	 */
	private function check_customer_access_authorization( $access_token = null ) {
		if ( is_null( $access_token ) ) {
			return false;
		}

		$customer_domain = parse_url(
			filter_var(
				get_site_url(),
				FILTER_VALIDATE_URL
			),
			PHP_URL_HOST
		);
		$customer_domain = $this->strip_www( $customer_domain );

		$response = wp_remote_get(
			sprintf(
				self::WP_PSS_API,
				self::JUMP_HOST_DOMAIN,
				$customer_domain
			),
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $access_token,
				],
				'timeout' => 30,
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( isset( $response['response']['code'] )
			&& 200 === $response['response']['code']
		) {
			$provisions = json_decode( $response['body'], true );

			return $this->validate_provisioning( $provisions, $customer_domain );
		}

		return false;
	}

	/**
	 * Validate provisioning.
	 *
	 * @param array         $provisions Provisions array.
	 * @param string | null $customer_domain The customer domain.
	 *
	 * @return bool
	 */
	private function validate_provisioning( array $provisions = [], $customer_domain = null ) {
		if ( empty( $provisions ) || empty( $customer_domain ) ) {
			return false;
		}

		$projects = [];
		foreach ( $provisions as $provisioning ) {
			if ( ! empty( $provisioning['projects'] ) ) {
				$projects = array_merge( $projects, $provisioning['projects'] );
			}
		}

		$domains = [];
		foreach ( $projects as $project ) {
			if ( ! empty( $project['domain_binding'] ) ) {
				$domains[] = strtolower( $project['domain_binding'] );
			}
		}

		return in_array( strtolower( $customer_domain ), $domains, true );
	}

	/**
	 * Remove session parameter and revoke access token by jumphost.
	 */
	public function cleanup_after_logout() {
		$access_token = get_transient(
			self::TRANSIENT_PREFIX
										. 'access_token'
		);
		wp_remote_post(
			'https://' . self::JUMP_HOST_DOMAIN . '/api/wordpress/revoke',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $access_token,
				],
			]
		);
		delete_transient( self::TRANSIENT_PREFIX . 'state' );
		delete_transient( self::TRANSIENT_PREFIX . 'secret' );
		delete_transient( self::TRANSIENT_PREFIX . 'iv' );
	}

	/**
	 * Login first admin user
	 *
	 * @return null | \WP_User
	 */
	public function get_admin_user() {
		foreach ( get_users() as $user ) {
			if ( user_can( $user->ID, 'manage_options' ) ) {
				return $user;
			}
		}
		$this->login_error( 'No admin user found' );
		return null;
	}

	/**
	 * Strip www. from domain.
	 *
	 * @param string $url The URL.
	 *
	 * @return string
	 */
	private function strip_www( $url ) {
		$patterns = [
			'www.'         => '',
			'https://www.' => 'https://',
		];

		foreach ( $patterns as $pattern => $prefix ) {
			if ( 0 === stripos( $url, $pattern ) ) {
				return $prefix . substr( $url, strlen( $pattern ) );
			}
		}

		return $url;
	}
}
