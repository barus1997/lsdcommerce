<?php
/**
 * LSDCommerce
 *
 * @link              lsdplugins.com
 * @package           LSDCommerce
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       #DEV# LSDCommerce
 * Plugin URI:        lsdplugins.com/lsdcommerce
 * Description:       WordPress Ecommerce Indonesia
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

define( 'LSDCOMMERCE_VERSION', '0.0.1' );
define( 'LSDC_PATH', plugin_dir_path( __FILE__ ) );
define( 'LSDC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Set const LSDC_CONTENT to Refere
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
require_once LSDC_PATH . 'core/class/class-log.php';
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




// Load Unique Code by Seesion id in Browser, if Change the Uniqe Code will be change to
function lsdc_shipping_starter_calculation( $extras ){
	if( isset($extras['extras']['shipping']['physical']) ){
		$physical 	= $extras['extras']['shipping']['physical'];
		$city 		= $physical['city'];
		$service 	= $physical['service'];
		$state 		= $physical['state'];
		
	

		// Automatic Get Weight in LSDCommerce Order Proccessing
		if( isset($extras['extras']['shipping']['weights']) ){
			$weights = $extras['extras']['shipping']['weights'];
		}
		$extras['extras']['shipping']['weights'] = $weights;
		
		$detail = array( // Digital Courrier ID
			'destination'  	=> $city,
			'weight'     	=> $weights,
			'service'   	=> $service // 
		);

		$clean = lsdc_shipping_rajaongkir_starter_calc( $detail );
		$extras = array_merge( $extras, $clean );
	}
	return $extras;
}
add_filter( 'lsdcommerce_payment_extras', 'lsdc_shipping_starter_calculation' );

function call_taxonomy_template_from_directory( $template ){
	global $post;

	$taxonomy_slug = get_query_var('lsdc-product-category');
	if( $taxonomy_slug ){
		return LSDC_PATH . "/templates/category.php";
	}
}
add_filter('taxonomy_template', 'call_taxonomy_template_from_directory');

function lsdc_count_taxonomy_post( $name, $termid = false ){
    $terms = get_terms(
        array(
			'taxonomy'   => $name,
			'include' =>  get_queried_object()->term_id,
            'hide_empty' => false,
        )
    );
 
    $count = 0;
    foreach ($terms as $key => $value) {
        $count += $value->count;
    }
 
    return $count;
}
