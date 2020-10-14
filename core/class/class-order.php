<?php 
namespace LSDCommerce\Order;
use LSDCommerce\Shipping\LSDC_Shipping;
use LSDCommerce\Logger\LSDC_Logger;

/**
 * Order Handle
 */
class LSDC_Order
{
    /**
     * Validation Customer Input,
     * Next will Be Validation Form
     */
    private function validation_customer( $order_object )
    {
        // Member Checkout
        if( is_user_logged_in() ) {
            // Set ID
            $order_object['form']['id'] = get_current_user_id();

        } else {
            // Validation and Sanitize Customer
            foreach ( $order_object['form'] as $key => $item) {
                switch ($key) {
                    case 'name':
                        $order_object['form'][$key] = sanitize_text_field( $item );
                        break;
                    case 'phone':
                        $order_object['form'][$key] = abs( $item );
                        break;
                    case 'email':
                        $order_object['form'][$key] = sanitize_email( $item );
                        break;
                }

                // Sanitize More Form
                do_action( 'lsdcommerce_form_sanitize' , $order_object );
            }

            // Setup User
            $phone      = abs( $order_object['form']['phone'] );
            $username   = lsdc_format_username( $order_object['form']['name'] );
            $password   = lsdc_generate_password();
            $names      = explode( ' ', $order_object['form']['name'], 2 );

            // Register User
            $user_data = array(
                'user_login'    => $username,
                'user_pass'     => $password,
                'first_name'    => esc_attr( $names[0] ),
                'last_name'     => esc_attr( $names[1] ),
                'user_email'    => $order_object['form']['email'],
                'role'          => 'customer'
            );
            $user_id = wp_insert_user( $user_data );

            if( $user_id ) {

                wp_send_new_user_notifications($user_id); // Send Credentials
                update_user_meta( $user_id, 'user_phone', lsdc_format_phone( $phone ) );

                //Do Action for Addon Verification Email
                //update_user_meta( $new_user_id, 'verification', false );
                do_action( 'lsdcommerce_form_save', $order_object );

                // Sign In via AJAX
                $credentials = array();
                $credentials['user_login'] = $username;
                $credentials['user_password'] = $password;
                wp_signon( $credentials );
            }
      
            $order_object['form']['id'] = $user_id;

        }

        return $order_object;
    }

    public function create_order( $order_object )
    {
        
        $start_time = microtime(true); //-> 00:00:00 Start Counting

        // Validation Customer
        $order_object = $this->validation_customer( $order_object );

        // Procssing Products
        $subtotal = 0; 
        $weights  = 0;

        foreach ( $order_object['products'] as $key => $product ) 
        {
            $variation_id   = null;
            $product_id     = abs( $product['id'] ); // get ID from JS

            /* Start - Pro CODE, Ignore, Don't Delete */
            if( isset( $product['variations'] ) ){
                if( lsdc_isVariation($product_id, esc_attr( $product['variations'] ) ) ){
                    $variation_id = esc_attr( $product['variations'] );
                }
                $order_object['products'][$key]['variations']    = $variation_id;
            }
             /* End - Pro CODE, Ignore, Don't Delete */
            
            $product_price = $variation_id != null ? lsdc_product_variation_price( $product_id, $variation_id ) : lsdc_product_price( $product_id );

            // ReAssign to Order Object
            $order_object['products'][$key]['id']           = $product_id;
            $order_object['products'][$key]['qty']          = abs( $product['qty'] );
            $order_object['products'][$key]['price_unit']   = $product_price; 
            $order_object['products'][$key]['price_unit_text'] = $product_price != 0 ? lsdc_currency_format( false, $product_price ) : __( 'Gratis', 'lsdcommerce' ); 
            $order_object['products'][$key]['weight_unit']  = abs( get_post_meta( $product_id, '_physical_weight', true ) );
            $order_object['products'][$key]['title']        = get_the_title( $product_id );
            $order_object['products'][$key]['thumbnail']    = get_the_post_thumbnail_url( $product_id , 'lsdc-thumbnail-mini' );
            $order_object['products'][$key]['total']        = lsdc_currency_format( true, $product_price * abs( $product['qty'] ) );

            $subtotal   += $product_price * abs( $product['qty'] );
            $weights    += abs( get_post_meta( $product_id, '_physical_weight', true ) ) * abs( $product['qty'] );
        }

        //--> Calculating Extras Cost
        $surcharge = 0;
        $extras = array();
        $extras['extras']['shipping'] = $order_object['shipping']; // Assign Shipping for Calculation
        $extras['extras']['shipping']['weights'] = $weights; //Assign Weights for  Calculation
        
        // Calculate Shipping Cost via lsdcommerce_payment_extras
        $surcharge = 0;
        if( has_filter('lsdcommerce_payment_extras') ) {
            // Procssing Raw Data from JS to Clean PHP
            $extras = apply_filters('lsdcommerce_payment_extras', $extras ); // Calculation in Callback Function
            if( $extras ){
                foreach ($extras as $key => $item) {
                    if( isset( $item['cost'] ) ){ //cost exist
                        $surcharge += intval( $item['cost'] ); // calc every extra
                    }
                }
            }
        }


        // unset($extras['extras']);
        $order_object['weights']   = $weights;
        $order_object['surcharge'] = $surcharge;

        // Payment Data
        $payment_data = array(
            'payment_name'          => lsdc_get_payment( $order_object['payment'], 'groupname' ) . lsdc_get_payment( $order_object['payment'], 'name' ),
            'payment_logo'          => lsdc_get_payment( $order_object['payment'], 'logo' ),
            'payment_instruction'   => lsdc_get_payment( $order_object['payment'], 'instruction' ),
            'code_label'            => lsdc_get_payment( $order_object['payment'], 'swiftcode' ) != null ? __( 'BIC/SWIFT : ', 'lsdcommerce' ) : null,
            'code_value'            => lsdc_get_payment( $order_object['payment'], 'swiftcode' ),
            'account_label'         => lsdc_get_payment( $order_object['payment'], 'account_number' ) != null ? __( 'No Rekening : ', 'lsdcommerce' ) : null,
            'account_code'          => lsdc_get_payment( $order_object['payment'], 'account_code' ) != null ? lsdc_get_payment( $order_object['payment'], 'account_code' ) : null,
            'account_number'        => lsdc_get_payment( $order_object['payment'], 'account_number' ),
            'holder_label'          => lsdc_get_payment( $order_object['payment'], 'account_holder' ) != null ?__( 'Atas Nama : ', 'lsdcommerce' ) : null,
            'holder_value'          => lsdc_get_payment( $order_object['payment'], 'account_holder' )
        );

        //Calc GrandTotal
        $total = 0;
        $total = $subtotal + $surcharge;

        $ip_address                 = lsdc_get_ip();
        $order_object['ip']         = $ip_address;
        $order_object['currency']   = lsdc_currency_get();
        $order_object['date']       = lsdc_date_now();
        $order_object['reference']  = '';
        $order_object['order_id']   = $this->get_order_ID();

        $args = array( 
            'post_type'     => 'lsdc-order', 
            'post_title'    => $order_object['order_id'],
            'post_status'   => 'publish', 
            'post_author'   => $order_object['form']['id']
        );
        $order_id = wp_insert_post( $args );

        // Saving Snapshot
        add_post_meta( $order_id, '_snapshot', json_encode( $order_object ) );
        add_post_meta( $order_id, 'order_id', esc_attr( $order_object['order_id'] )  );
        add_post_meta( $order_id, 'order_key', esc_attr( $order_object['order_key'] )  );

        add_post_meta( $order_id, 'customer_id', json_encode( $order_object['form']['id'] ) ); // was validate
        add_post_meta( $order_id, 'customer', json_encode( $order_object['form'] ) ); // was validate
        add_post_meta( $order_id, 'shipping', json_encode( $order_object['shipping'] ) ); // was calculate
        add_post_meta( $order_id, 'products', json_encode( $order_object['products'] ) ); // was saved, harus di save, jadi meskipun ada perubahan adata nggak bakal berubah
        add_post_meta( $order_id, 'extras', json_encode( $extras ) ); // was calc
        add_post_meta( $order_id, 'payment_id', esc_attr( $order_object['payment'] ) );
        add_post_meta( $order_id, 'payment_data', json_encode( $payment_data ) );

        add_post_meta( $order_id, 'subtotal', $subtotal );
        add_post_meta( $order_id, 'surcharge', $surcharge ); // Shipping, UniqueCode, Additional
        add_post_meta( $order_id, 'total', $total );
        add_post_meta( $order_id, 'ip', esc_attr( $order_object['ip'] ) );

        // Triggering Notification
        lsdc_order_status( $order_id, 'new' );

        // Free Product
        if( $total == 0 ) {
            lsdc_order_status( $order_id, 'processed', true );
        }

        // Testing Shipping and Notification Direct
        // lsdc_shipping_schedule_action( $order_id );
        // lsdc_notification_schedule_action( $order_id, 'order' );

        // Flag Remove Token
        delete_transient( 'lsdc_checkout_' . $order_object['order_key']  );
        lsdc_order_unread_counter(); // Adding Order Counter

        $end_time = microtime(true); 
        $execution_time = ($end_time - $start_time); 
        LSDC_Logger::log( 'Execution time of Order = ' . $execution_time . ' sec' );
        LSDC_Logger::log( 'Order#' . $order_id . ' Created from ' . $ip_address );
    }

    private function notification()
    {

    }

    /* Set by LSDCommerce */
    public static function status_pending(){
        // Sending Notification :: Order Received
        // Waiting Payment
    }

    /* Set by LSDCommerce */
    public static function status_processing(){
        // Sending Notification :: Payment Received
        // Payment Accept
        // Packing Order ( Show Button Delivery in Order )
        // Sending Shipping

        if( $digital ){
            // Create License

            // $customer               = json_decode( get_post_meta( $order_id, 'customer', true ) );
            // $obj['email']           = $customer->email;
            // $obj['order_id']        = $order_id;
            // $obj['order_number']    = $order_number;
            // $obj['type']            = 'digital'; //order,complete
            // LSDC_Shipping::sender( $obj );
            lsdc_order_status( $order_id, 'completed' );
        }

    }

    public static function status_shipped()
    {
        // ORder Was Shipped
    }


    public static function status_complete()
    {
        // Reduce Stock -> Move to Complete Status
        // $stock = abs( get_post_meta( $product_id, '_stock', true ) ) - 1;
        // update_post_meta( $product_id, '_stock', abs( $stock ) );

        // Create Invoice
    }

    public static function status_canceled()
    {
    }


    public static function status_refunded()
    {
    }


    /**
     * Snapshot to capture product price at Checkout moment
     * it's prevent to changing price, and integrity checkout
     */
    public function create_snapshot(){
        
    }

    /**
     * Generating Thanyou URL
     */
    public function thankyou_url( $token ){
        return json_encode(array(
            'code' => '_order_created',
            'redirect' => get_site_url() . '/payment/thankyou/'. $token . '/'
        ));
    }

    /**
     * Getting Last ID of LSDCommerce Order
     * Add 1 to make Squence
     */
    public function get_order_ID(){
        global $wpdb;
        $lastrow = $wpdb->get_col( "SELECT ID FROM $wpdb->posts where post_type='lsdc-order' ORDER BY post_date DESC " );
        if( isset( $lastrow[0] ) ){
            return abs( get_post_meta( abs( $lastrow[0] ), 'order_id', true  ) ) + 1;
        }else{
            return 1;
        }
    }
}
// new LSDC_Order; // Proactive Class
?>