<?php

namespace Ionos\SSO;

/**
 * Login class
 */
class Login {

	/**
	 * Login constructor.
	 */
	public function __construct() {
		add_action( 'login_form', [ $this, 'show_login_link' ] );
		add_filter( 'login_url', [ $this, 'add_login_url_parameter' ] );
		add_action( 'login_enqueue_scripts', [ $this, 'enqueue_sso_resources' ] );
	}

	/**
	 * Enqueue styles
	 */
	public function enqueue_sso_resources() {
		if ( Helper::is_enabled() ) {
			wp_enqueue_style(
				'ionos-sso-css',
				Helper::get_css_url( 'ionos-sso-login.css' ),
				[],
				filemtime( Helper::get_css_path( 'ionos-sso-login.css' ) )
			);
		}
	}

	/**
	 * Add filter to transfer any "action=ionos_oauth_register" parameter to the login URL
	 *
	 * @param string $login_url Login URL.
	 *
	 * @return string
	 */
	public function add_login_url_parameter( $login_url ) {

		if ( isset( $_GET['action'] ) && $_GET['action'] === 'ionos_oauth_register' ) {
			return add_query_arg(
				[
					'action' => 'ionos_oauth_register',
				],
				$login_url
			);
		} else {
			return $login_url;
		}
	}

	/**
	 * Build Single Sign-on link in WP Login screen
	 */
	public function show_login_link() {
		$login_html = '';

		if ( Helper::is_enabled() ) {
			$sso_login_url = add_query_arg(
				[
					'action' => 'ionos_oauth_register',
				]
			);

			$login_html .= '<p class="submit"><input type="submit" name="wp-submit" class="button button-primary button-large" id="sso_default_login" value="'
							. __( 'Log in via WordPress', 'ionos-sso' )
							. '" /></p>';
			$login_html .= '<p class="sso-login-or">
					<span>
						' . __( 'OR', 'ionos-sso' ) . '
					</span>
				</p>';
			$login_html .= '<p class="sso-login-link">
					<a href="' . esc_url( $sso_login_url ) . '" id="sso-login-link" class="button button-secondary button-large">
						' . __( 'Log in via SSO', 'ionos-sso' ) . '
					</a>
				</p>
			';
		}

		if ( ! Helper::is_enabled() && isset( $_GET['action'] ) && $_GET['action'] === 'ionos_oauth_register' ) {
			$login_html .= '<p><em>SSO is not enabled in your contract.</em></p>';

			if ( ! \get_option( 'ionos_install_mode' ) ) {
				$login_html .= '<p><em>Option <strong>ionos_install_mode</strong> is not enabled.</em></p>';
			}
		}

		echo $login_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
