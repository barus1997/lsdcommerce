<?php 
use LSDCommerce\Pluggable\LSDC_Pluggable;
use LSDCommerce\Form\LSDC_Form;
use LSDCommerce\Shipping\LSDC_Shipping;
use LSDCommerce\Payments\LSDC_Payment;

add_image_size( 'lsdcommerce-thumbnail-mini', 90, 90, true );
add_image_size( 'lsdcommerce-thumbnail-single', 500, 9999, false );
add_image_size( 'lsdcommerce-thumbnail-listing', 380, 190, true );

// Registering Form to Checkout
LSDC_Pluggable::form_register( array( "id" => "name", "type" => "text", "placeholder" => __( "Name" ,'lsdcommerce' ), "req" => "true" ) );
LSDC_Pluggable::form_register( array( "id" => "phone", "type" => "text", "placeholder" => __( "Phone" ,'lsdcommerce' ), "req" => "true" ) );
LSDC_Pluggable::form_register( array( "id" => "email", "type" => "email", "placeholder" => __( "Email" ,'lsdcommerce' ), "req" => "true" ) );

// Initial Core in Public Init
function lsdcommerce_init(){
    global $current_user;
    // Disable Admin Bar
    wp_get_current_user();
    if ( ! user_can( $current_user, "subscriber" ) && ! current_user_can( 'manage_options' ) ) {
        add_filter('show_admin_bar', '__return_false'); 
    }

    // Rendering Form
    LSDC_Form::public_render();
    LSDC_Payment::public_render();

    // Create Order Received URL
    add_rewrite_rule( 'payment/thankyou/([^/]+)', 'index.php?payment=true&thankyou=$matches[1]', 'top' );
    add_rewrite_rule( 'payment/thankyou', 'index.php?payment=true', 'top' );
}
add_action('init', 'lsdcommerce_init');

function lsdcommerce_checkout_init(){
    LSDC_Shipping::public_render();
}
add_action('lsdcommerce_checkout', 'lsdcommerce_checkout_init');

function lsdcommerce_checkout_token(){
    // Buat Checkout Token Selamat 10 Menit
    // Kalo Nggak dipake diabakalan Expired
    if( ! isset( $_COOKIE['_lsdcommerce_token'] ) && is_page( lsdc_get( 'general_settings', 'checkout_page' ) )  ){
        $token = wp_hash( lsdc_date_now() );
        $expired = lsdc_date_now();
        setcookie( "_lsdcommerce_token", $token . '-' . strtotime( $expired ), time() + 600, "/"  );
        if( ! get_transient( 'lsdc_checkout_' . $token ) ){
            set_transient( 'lsdc_checkout_' . $token , lsdc_date_now(), 600 );
        }        
    }else{
        if( isset( $_COOKIE['_lsdcommerce_token'] )  &&  ! is_page( lsdc_get( 'general_settings', 'checkout_page' ) ) ){
            setcookie( "_lsdcommerce_token" , null, time() - 3600 , "/"  );
        }
    }
}
add_action( 'template_redirect', 'lsdcommerce_checkout_token' );

// Apply style Based on Settings
function lsdc_apperance(){
    $settings       = get_option('lsdc_appearance_settings', true );
    $fontselected   = $settings['lsdc_fontlist'] == null ? 'Poppins' : $settings['lsdc_fontlist'];
    $bg_theme       = empty( $settings['lsdc_bgtheme_color'] ) ? 'transparent' : $settings['lsdc_bgtheme_color'];
    $theme          = $settings['lsdc_theme_color'];
    $lighter        = lsdc_adjust_brightness( $theme, 50 );
    $darker         = lsdc_adjust_brightness( $theme, -40 );
    echo '<style>
            :root {
                --lsdc-color: '. $theme .';
                --lsdc-lighter-color: '. $lighter .';
                --lsdc-darker-color: '. $darker .';
                --lsdc-background-color: '. $bg_theme .';
            }
            
            .lsdc-bg-color{
                background: '. $bg_theme .';
            }

            .lsdc-theme-color{
                color: '. $theme .';
            }

            .lsdc-content{
                font-family: -apple-system, BlinkMacSystemFont, "'. $fontselected . '", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
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

/**
 * @package LSDCommerce
 * @subpackage Checkout ? Order-received
 * 
 * Create Order Received Url Handler
 */
function lsdc_add_queryvars( $vars ){
    $vars[] = 'thankyou';
    $vars[] = 'payment';
    return $vars;
}
add_filter( 'query_vars', 'lsdc_add_queryvars' );

// Set Order Received to File
function lsdc_checkout_finish_url( $vars ){
    $hash   = get_query_var( 'thankyou' );
    $pay    = get_query_var( 'payment' );

    // Exist Order in Finish Url
    if( $hash && $pay == 'true'){
        add_filter( 'template_include', function() use( $hash ){
            // global $lsdd_templates;
            // require $lsdd_templates['thankyou']; // Using Default Template, It can Override via Global Variable
            require LSDC_PATH . '/templates/thankyou.php';
        });
    }

    // Empty Order in Finish Url
    if( empty( $hash ) && $pay == 'true' ){
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        get_template_part( 404 ); 
    }
}
add_action( 'template_redirect', 'lsdc_checkout_finish_url' );
?>