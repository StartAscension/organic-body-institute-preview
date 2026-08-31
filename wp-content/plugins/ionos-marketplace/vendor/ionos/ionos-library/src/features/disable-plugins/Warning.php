<?php

namespace Ionos\Librarymarketplace;

/**
 * Warning class
 */
class Warning {
	/**
	 * Plugin slug.
	 *
	 * @var mixed|string
	 */
	private $slug;

	/**
	 * Warning constructor.
	 *
	 * @param string $slug Plugin slug.
	 */
	public function __construct( string $slug = '' ) {
		$this->slug = $slug;

		\add_action( 'admin_enqueue_scripts', [ $this, 'add_script' ] );
	}

	/**
	 * Add plugins deactivation warning script
	 */
	public function add_script() {
		// Load styles.
		\wp_enqueue_style(
			'ionos-plugin-deactivate-warning',
			\plugins_url( 'css/plugin_warning.css', __FILE__ ),
			[],
			@\filemtime( \plugin_dir_path( __FILE__ ) . 'css/plugin_warning.css' )
		);
		// Load scripts.
		\wp_enqueue_script(
			'ionos-plugin-deactivate-warning',
			\plugins_url( 'js/plugin_warning.js', __FILE__ ),
			[],
			@\filemtime( \plugin_dir_path( __FILE__ ) . 'js/plugin_warning.js' ),
			true
		);
		\wp_localize_script(
			'ionos-plugin-deactivate-warning',
			'plugin_deactivation_warning',
			[
				'html' => @\file_get_contents( \plugin_dir_path( __FILE__ ) . 'html/plugin_warning.html' ),
			]
		);

		\wp_enqueue_script(
			'ionos-plugin-deactivate-warning-' . $this->slug,
			\plugins_url( 'js/plugin_warning_call.js', __FILE__ ),
			[],
			@\filemtime( \plugin_dir_path( __FILE__ ) . 'js/plugin_warning_call.js' ),
			true
		);
		\wp_localize_script(
			'ionos-plugin-deactivate-warning-' . $this->slug,
			'plugin_deactivation_warning_call',
			[
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
				'headline' => \__( 'Warning', $this->slug ),
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
				'body'     => \__( 'You are about to disable an IONOS Group plugin. This may cause dependency problems to other plugins. Do you really want to do that?', $this->slug ),
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain
				'primary'  => \__( 'Disable', $this->slug ),
				'slug'     => $this->slug,
			]
		);
	}
}
