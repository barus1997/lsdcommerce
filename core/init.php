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
    $fontFamily         = lsdc_admin_get('appearance_settings', 'font_family' ) == null ? 'Poppins' : lsdc_admin_get('appearance_settings', 'font_family' );
    $backgroundTheme    = empty( lsdc_admin_get('appearance_settings', 'background_theme_color' )) ? 'transparent' : lsdc_admin_get('appearance_settings', 'background_theme_color' );
    $colorTheme         = lsdc_admin_get('appearance_settings', 'theme_color' );
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

/**
 * Notification Handler
 * Trigger via CRON
 * Hard to Debug
 */
function lsdc_notification_schedule_action($order_id, $event)
{
    $notify = array(
        'order' => array(
            'subject' => __("Menunggu Pembayaran", 'lsdcommerce') ,
            'receiver' => array(
                'buyer',
                'admin'
            )
        ) ,
        'canceled' => array(
            'subject' => __("Pesanan Dibatalkan", 'lsdcommerce') ,
            'receiver' => array(
                'buyer'
            )
        ) ,
        'paid' => array(
            'subject' => __("Pembayaran Diterima", 'lsdcommerce') ,
            // 'receiver'  => array( 'buyer' )
            
        ) ,
        'shipped' => array(
            'subject' => __("Sedang Dikirim", 'lsdcommerce') ,
            'receiver' => array(
                'buyer'
            )
        ) ,
        'completed' => array(
            'subject' => __("Pesanan Selesai", 'lsdcommerce') ,
            'receiver' => array(
                'buyer'
            )
        )
    );

    $order_number = null;
    if (!empty(get_post_meta($order_id, 'order_id', true)))
    {
        $order_number = abs(get_post_meta($order_id, 'order_id', true));
    }

    // Getting Customer based on ID or Direct
    $customer_email = null;
    if (get_post_meta($order_id, 'customer_id', true))
    {
        $customer_id = abs(get_post_meta($order_id, 'customer_id', true));
        $customer_email = lsdc_get_user_email($customer_id);
    }
    else
    {
        $customer = json_decode(get_post_meta($order_id, 'customer', true));
        $customer_email = $customer->email;
    }

    // Buyer
    if (isset($notify[$event]['receiver'][0]))
    {
        $payload = array();
        $payload['email'] = $customer_email;
        $payload['subject'] = $notify[$event]['subject'] . ' #' . $order_number;
        $payload['order_id'] = $order_id;
        $payload['order_number'] = $order_number;
        $payload['notification_event'] = $event;
        LSDC_Logger::log('Buyer Notification : ' . json_encode($payload));
        LSDC_Notification::sender($payload);
    }

    // Admin
    if (isset($notify[$event]['receiver'][1]))
    {
        $payload = array();
        $payload['email'] = 'lasidaziz@gmail.com';
        $payload['subject'] = 'Pesanan Baru #' . $order_number;
        $payload['order_id'] = $order_id;
        $payload['order_number'] = $order_number;
        $payload['role'] = 'admin';
        $payload['notification_event'] = $event;
        LSDC_Logger::log('Admin Notification : ' . json_encode($payload));
        LSDC_Notification::sender($payload);
    }

}
add_action('lsdc_notification_schedule', 'lsdc_notification_schedule_action', 10, 2);


/**
 * Shipping Handler
 * Just for Digital Product
 */
function lsdc_shipping_schedule_action($order_id)
{
    $payload = array();
    $payload['subject'] = __("Pengiriman Produk ", 'lsdcommerce');
    $payload['order_id'] = $order_id;
    $payload['type'] = 'digital';
    $payload['email'] = lsdc_order_get_email($order_id);
    LSDC_Logger::log('Shipping for Order: #' . $order_id);
    LSDC_Shipping::sender($payload);

    // Auto Completed Order for Empty and Digital Purchase
    $total = abs(get_post_meta($order_id, 'total', true));
    $shipping = (array)json_decode(get_post_meta($order_id, 'shipping', true));
    if ($total == 0 && isset($shipping['digital']) && !isset($shipping['physical']))
    {
        lsdc_order_status($order_id, 'completed');
    }
}
add_action('lsdc_shipping_schedule', 'lsdc_shipping_schedule_action', 10, 1);
?>