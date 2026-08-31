<?php
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Plugin Name:  Single Sign-On
 * Plugin URI:   https://www.ionos-group.com/brands.html
 * Description:  Single Sign-On allows you to log in with your hosting credentials. Additionally, if you're already logged in at your host, you can jump to your WordPress backend without the need for another login.
 * Version: 2.3.2
 * License:      GPLv2 or later
 * Author:       IONOS Group
 * Author URI:   https://www.ionos-group.com/brands.html
 * Update URI:   ionos-sso
 * Text Domain:  ionos-sso
 * Domain Path:  /languages
 *
 * @package ionos-sso
 */

/*
Copyright 2024 IONOS Group
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Online: http://www.gnu.org/licenses/gpl.txt
*/

namespace Ionos\SSO;

require_once __DIR__ . '/vendor/autoload.php';

use Ionos\Librarysso\Options;
use Ionos\Librarysso\Warning;
use Ionos\Librarysso\Updater;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

define( 'IONOS_SSO_FILE', __FILE__ );
define( 'IONOS_SSO_DIR', __DIR__ );
define( 'IONOS_SSO_BASE', plugin_basename( __FILE__ ) );


/**
 * Init plugin.
 *
 * @return void
 */
function init() {
	Options::set_plugin_name( 'sso' );

	new Updater();
	new Warning( 'ionos-sso' );
	new Manager();
	new Login();
	new DisableUrlChange();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Plugin translation.
 *
 * @return void
 */
function load_textdomain() {
	\load_plugin_textdomain(
		'ionos-sso',
		false,
		\dirname( \plugin_basename( __FILE__ ) ) . '/languages/'
	);
}
add_action( 'init', __NAMESPACE__ . '\load_textdomain' );
