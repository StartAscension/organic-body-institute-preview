<?php

namespace Ionos\SSO;

use Ionos\Librarysso\Config;
use Ionos\Librarysso\Options;

/**
 * Ensures that the customer can only change their domain via the control panel.
 */
class DisableUrlChange {

	/**
	 * DisableUrlChange constructor.
	 */
	public function __construct() {
		// Prevent the user from changing the site URL & home URL in the WordPress admin. The user should change the domain in the control panel instead.
		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		define( 'WP_SITEURL', get_site_url() );
		define( 'WP_HOME', get_home_url() );

		add_action( 'load-options-general.php', [ $this, 'add_url_change_notice' ] );
	}

	/**
	 * Disable URL input fields
	 */
	public function add_url_change_notice() {
		add_action( 'admin_footer', [ $this, 'print_footer_script' ] );
	}

	/**
	 * Print inline script to disable URL input fields
	 */
	public function print_footer_script() {
		if ( ! is_plugin_active( 'ionos-assistant/ionos-assistant.php' ) ) {
			// Get the control panel application link.
			$control_panel_link = Config::get( 'links.control_panel_applications_' . Options::get_market() );

			// Warning message to be displayed below the 'home' field.
			$warning_message = sprintf(
				/* translators: %s: link to control panel */
				__( 'Please change the domain in the <a href="%s" target="_blank">Control Panel</a> instead of here. Otherwise, you may no longer be able to log in.', 'ionos-sso' ),
				esc_url( $control_panel_link )
			);

			// JavaScript code to create and insert the warning message element.
			echo '
                <script type="text/javascript">
                    const warningElement = document.createElement(\'p\');
                    warningElement.classList.add(\'description\');
                    warningElement.innerHTML = \'' . wp_kses_post( $warning_message ) . '\';

                    const homeField = document.getElementById(\'home\');
                    if (homeField) {
                        homeField.parentNode.insertBefore(warningElement, homeField.nextSibling);
                    }

                    const homeDescription = document.getElementById(\'home-description\');
                    if (homeDescription) {
						homeDescription.style.display = \'none\';
					}
                </script>
            ';
		}
	}
}
