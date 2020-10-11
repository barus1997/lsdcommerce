<?php 
use LSDCommerce\Logger\LSDC_Logger;
use LSDCommerce\Order\LSDC_Order;
require_once LSDC_PATH . 'core/class/class-order.php';
/**
 * Class To Handle Public AJAX
 */
Class LSDCommerce_Public_AJAX{
    
    public function __construct()
    {
        add_action( 'wp_ajax_lsdcommerce_create_order', array( $this, 'create_order' ) ); 
        add_action( 'wp_ajax_nopriv_lsdcommerce_create_order', array( $this, 'create_order' ) );
    }

    public function create_order()
    {
        $_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );

        // Checking Token and Nonce
        if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' ); //Token
        if ( ! wp_verify_nonce(  $_REQUEST['nonce'], 'checkout-nonce' ) ) { wp_send_json_error( 'Busted.' ); } //Nonce

        // Checking Token
        $user_token     = esc_attr( $_REQUEST['token'] );
        $token          = explode( '-', $user_token );
        $server_token   = get_transient( 'lsdc_checkout_' . $token[0]  ); // Get Transient from Server based on Client Token
        $timestamp      = strtotime( lsdc_date_now() ) - strtotime( $server_token ); 
        // var_dump(  $timestamp );
        $validation     = false;

        #Flooding Blocker
        // if( $timestamp > 500 ){ // Passed
        //     LSDC_Logger::log( 'Checkout Flooding from ' . lsdc_get_ip(), LSDC_Logger::WARNING );
        //     die( "_token_expired" );
        // }

        // Have Checkout Token
        if( $user_token ){
            $order_object  = $_REQUEST['order']; //from JS ( Shipping, Customer, Products )
            // var_dump( $order_objecvar_dump( $order_object );t ); //DEBUG MODE

            #Email Exist in Member
            $user = get_user_by( 'email', $order_object['form']['email'] );
            if( $user->ID ){
                if( is_user_logged_in()){
                    // Member Checkout
                    $validation = true;
                }else{
                    die( "_email_registered" );
                }
            }else{
                // Guest Checkout
                $validation = true;
            }
        }else{
            die( "_token_expired" );
        }

        if( $validation ){
            $order_object['order_key'] = $token[0];
        
            $new = new LSDC_Order;
            $new->create_order( $order_object );
            // echo $new->thankyou_url( $token );
        }

        wp_die();
    }
}
new LSDCommerce_Public_AJAX;
?>