<?php
require_once 'vendor/autoload.php';

/**
 * The plugin bootstrap file
 *
 * @link              https://wordpress.org/plugins/wustache/
 * @since             1.0.0
 * @package           Wustache
 *
 * @wordpress-plugin
 * Plugin Name:       Wustache
 * Plugin URI:        https://wordpress.org/plugins/wustache/
 * Description:       The Wustache merges Mustache with Wordpress.
 * Version:           0.1
 * Author:            Cloudoki
 * Author URI:        http://cloudoki.com
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (! defined ('WPINC')) die;


/**
 *	The code that runs during plugin activation.
 */
function activate_wustache()
{
	Cloudoki\Wustache\Activator::activate();
}

/**
 *	The code that runs during plugin de-activation.
 */
function deactivate_wustache()
{
	Cloudoki\Wustache\Activator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Wustache' );
register_deactivation_hook( __FILE__, 'deactivate_Wustache' );


/**
 * Begin execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wustache() {

	$plugin = new Cloudoki\Wustache\Wustache ();
	$plugin->run();

}
run_wustache();
