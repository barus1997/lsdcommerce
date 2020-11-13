<?php
/**
 * LSDCommerce
 *
 * @link              lsdplugins.com
 * @package           LSDCommerce
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       LSDCommerce Test Change
 * Plugin URI:        lsdplugins.com/lsdcommerce
 * Description:       Plugin Toko Online Indonesia
 * Version:           1.0.0
 * Author:            LSD Plugins
 * Author URI:        lsdplugins.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       lsdcommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Definding Constant
define( 'LSDC_VERSION', '1.0.0' );
define( 'LSDC_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSDC_URL', plugin_dir_url( __FILE__ ) );
define( 'LSDC_FILE', __FILE__ );

/**
 * Set const LSDC_CONTENT to Referer
 * wp-content/uploads/lsdcommerce/
 * This const using to saving translation and wordpress data.
 */
$upload 	= wp_upload_dir();
$upload_dir = $upload['basedir'];
$upload_dir = $upload_dir . '/lsdcommerce';
define( 'LSDC_CONTENT', $upload_dir );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lsdcommerce-activator.php
 */
function activate_lsdcommerce() {
	require_once LSDC_PATH . 'includes/class-activator.php';
	LSDCommerce_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_lsdcommerce' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lsdcommerce-deactivator.php
 */
function deactivate_lsdcommerce() {
	require_once LSDC_PATH . 'includes/class-deactivator.php';
	LSDCommerce_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_lsdcommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once LSDC_PATH . 'includes/class-lsdcommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_lsdcommerce() {
	$plugin = new LSDCommerce();
	$plugin->run();
}
run_lsdcommerce();

