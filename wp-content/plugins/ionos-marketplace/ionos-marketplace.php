<?php
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
/**
 * Plugin Name:  Marketplace
 * Description:  Marketplace offers you a list of carefully selected plugins that will add functionality to your WordPress instance and improve your user experience.
 * Version: 2.3.0
 * License:      GPL-2.0-or-later
 * Author:       IONOS Group
 * Author URI:   https://www.ionos-group.com/brands.html
 * Domain Path:  /languages
 * Update URI:   ionos-marketplace
 * Text Domain:  ionos-marketplace
 *
 * @package Ionos\Marketplace
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


namespace Ionos\Marketplace;

use Ionos\Librarymarketplace\Options;
use Ionos\Librarymarketplace\Updater;
use Ionos\Librarymarketplace\Warning;
use Ionos\Navigation\Manager;
use Ionos\Marketplace\Plugins as MarketplacePlugins;

define( 'IONOS_MARKETPLACE_FILE', __FILE__ );
define( 'IONOS_MARKETPLACE_DIR', __DIR__ );
define( 'IONOS_MARKETPLACE_BASE', plugin_basename( __FILE__ ) );


$ionos_marketplace_autoloader = __DIR__ . '/vendor/autoload.php';
if ( is_readable( $ionos_marketplace_autoloader ) ) {
	require_once $ionos_marketplace_autoloader;
}

Options::set_plugin_name( 'marketplace' );

Options::clean_up( IONOS_MARKETPLACE_FILE );

/**
 * Init plugin.
 *
 * @return void
 */
function init() {
	new Updater();
	new Warning( 'ionos-marketplace' );

	require_once 'inc/Plugins.php';
	require_once 'inc/Themes.php';

	if ( is_blog_installed() ) {
		new MarketplacePlugins();
	}
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );
