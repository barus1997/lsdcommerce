<?php 
require_once LSDC_PATH . 'core/functions/pluggable.php'; // Pluggable Functions
require_once LSDC_PATH . 'core/class/class-form.php';

use LSDCommerce\Pluggable\LSDC_Pluggable;
use LSDCommerce\Form\LSDC_Form;

use LSDCommerce\Shipping\LSDC_Shipping;
use LSDCommerce\Payments\LSDC_Payment;

add_image_size( 'lsdcommerce-thumbnail-mini', 90, 90, true );
add_image_size( 'lsdcommerce-thumbnail-single', 500, 9999, false );
add_image_size( 'lsdcommerce-thumbnail-listing', 450, 450, true );

LSDC_Pluggable::form_register( array( "id" => "name", "type" => "text", "placeholder" => __( "Nama" ,'lsdcommerce' ), "req" => "true" ) );
LSDC_Pluggable::form_register( array( "id" => "phone", "type" => "text", "placeholder" => __( "Telepon" ,'lsdcommerce' ), "req" => "true" ) );
LSDC_Pluggable::form_register( array( "id" => "email", "type" => "email", "placeholder" => __( "Email" ,'lsdcommerce' ), "req" => "true" ) );

// Initial Core in Public Init
function lsdcommerce_init(){
    global $current_user;
    // Disable Admin Bar
    wp_get_current_user();
    if ( ! user_can( $current_user, "subscriber" ) && ! current_user_can( 'manage_options' ) ) {
        add_filter('show_admin_bar', '__return_false'); 
    }

    // Create Order Received URL
    add_rewrite_rule( 'payment/thankyou/([^/]+)', 'index.php?payment=true&thankyou=$matches[1]', 'top' );
    add_rewrite_rule( 'payment/thankyou', 'index.php?payment=true', 'top' );
    do_action( 'lsdcommerce_init' );
}
add_action('init', 'lsdcommerce_init');

function lsdcommerce_checkout_init(){
    LSDC_Form::public_render();
    LSDC_Shipping::public_render();
    LSDC_Payment::public_render();
}
add_action('lsdcommerce_checkout', 'lsdcommerce_checkout_init');

// Apply style Based on Settings
function lsdc_apperance(){
    $fontFamily         = lsdc_get('appearance_settings', 'font_family' ) == null ? 'Poppins' : lsdc_get('appearance_settings', 'font_family' );
    $backgroundTheme    = empty( lsdc_get('appearance_settings', 'background_theme_color' )) ? 'transparent' : lsdc_get('appearance_settings', 'background_theme_color' );
    $colorTheme         = lsdc_get('appearance_settings', 'theme_color' );
    $lighter            = lsdc_adjust_brightness( $colorTheme, 50 );
    $darker             = lsdc_adjust_brightness( $colorTheme, -40 );
    echo '<style>
            :root {
                --lsdc-color: '. $colorTheme .';
                --lsdc-lighter-color: '. $lighter .';
                --lsdc-darker-color: '. $darker .';
                --lsdc-background-color: '. $backgroundTheme .';
            }
            
            .lsdc-bg-color{
                background: '. $backgroundTheme .';
            }

            .lsdc-theme-color{
                color: '. $colorTheme .';
            }

            .lsdc-content{
                font-family: -apple-system, BlinkMacSystemFont, "'. $fontFamily . '", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            }
        </style>';
}
add_action( 'wp_head', 'lsdc_apperance');

// Add Body Class LSDplugins
add_filter('body_class', function($classes) {
    if ( in_array('lsdplugins', $classes) ) {
        wp_enqueue_style( 'lsdplugins' );
    }else{
		$classes[] = 'lsdplugins'; 
    }
 
    // Set Class via Condition
    global $lsdcommerce;
    if( isset( $lsdcommerce['page'] ) ){
        $classes[] = 'lsdcommerce';
    }

    // Set Class via Shortcodes
    global $post;
    if( isset($post->post_content) && has_shortcode( $post->post_content, 'lsdcommerce_checkout' ) ) {
        $classes[] = 'lsdcommerce';
    }

    return $classes;
});

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

?>