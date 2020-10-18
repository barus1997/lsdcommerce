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
        $token          = explode( '-', $user_token )[0];
        $server_token   = get_transient( 'lsdc_checkout_' . $token  ); // Get Transient from Server based on Client Token
        // var_dump( $user_token );
        $timestamp      = strtotime( lsdc_date_now() ) - strtotime( $server_token ); 

        $validation     = false;

        #Flooding Blocker
        if( $timestamp > 550 ){ // Passed
            LSDC_Logger::log( 'Checkout Flooding from ' . lsdc_get_ip(), LSDC_Logger::WARNING );
            die( "_token_expired" );
        }

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
            $order_object['order_key'] = $token;
        
            $new = new LSDC_Order;
            $new->create_order( $order_object );
            echo $new->thankyou_url( $token );
        }

        wp_die();
    }
}
new LSDCommerce_Public_AJAX;

/**
 * Grouping Shipping AJAX
 */
Class LSDCommerce_Shipping_AJAX{
    public function __construct()
    {
        add_action( 'lsdcommerce_checkout_shipping', [ $this, 'shipping_method' ] );

        add_action( 'wp_ajax_nopriv_lsdc_shipping_physical_package', [ $this, 'shipping_package' ] );
        add_action( 'wp_ajax_lsdc_shipping_physical_package', [ $this, 'shipping_package' ] );
    }

    /**
     * Shipping Method
     */
    public function shipping_method()
    {
        global $lsdcommerce_shippings;

        $cookie_cart = isset( $_COOKIE['_lsdcommerce_cart'] ) ? $_COOKIE['_lsdcommerce_cart'] : null;
        $carts = (array) json_decode( stripslashes( $cookie_cart ) );
    
        // Checking Shipping Type on Cart
        $shipping_physical = false;
        $shipping_digital = false;

        if( isset($carts) ){
            foreach ($carts as $key => $product) {
                // Variation
                $shipping_type = get_post_meta( $product->id, '_shipping_type', true );
                switch ($shipping_type) {
                    case 'physical':
                        $shipping_physical = true;
                        break;
                    case 'digital':
                        $shipping_digital = true;
                        break;
                }
            }
        }
    
        $shipping_physical_list = array();
        $shipping_digital_list = array();

        if( isset($lsdcommerce_shippings) ){
            foreach ($lsdcommerce_shippings as $key => $class) 
            {
                $object = new $class;
                if( $shipping_physical && $object->type == 'physical'  ){
                    if( $object->get_status() == 'on'){
                        $shipping_physical_list[] = $object->name;
                    }
                }
                
                if( $shipping_digital && $object->type == 'digital' ){
                    if( $object->get_status() == 'on'){
                        $shipping_digital_list[] = $object->name;
                    }
                }
            }
        }
    
        ?>
        <?php if( !empty($shipping_digital_list) ) : ?>
            <h6 class="text-primary font-weight-medium lsdp-mb-10"><?php _e( "Pengiriman Digital", 'lsdcommerce' ); ?></h6>
                <div id="digital-shipping" class="lsdp-row no-gutters radio-courier">
                    <?php do_action( 'lsdcommerce_shipping_digital' ); ?>
                </div>
            <hr>
        <?php endif; ?>
    
        <?php if( !empty($shipping_physical_list) ) : ?>
            <h6 class="text-primary font-weight-medium lsdp-mb-10"><?php _e( "Pengiriman Fisik", 'lsdcommerce' ); ?></h6>
            <?php do_action( 'lsdcommerce_shipping_physical_control' ); ?>
            
            <div id="physical-shipping" class="lsdp-row no-gutters radio-courier">
                <?php do_action( 'lsdcommerce_shipping_physical_services' ); ?>
            </div>
        <?php endif; ?> 
    
        <!-- Empty and Not Set Shipping Channel -->
        <?php if( empty($shipping_physical_list) && empty($shipping_digital_list) ) : ?>
            <?php _e( 'Please contact admin to set up a shipping channel', 'lsdcommerce' ); ?>
        <?php endif; 
    }

    public function shipping_package(){
        global $lsdcommerce_shippings;
        if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
        if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'checkout-nonce' ) ) { die('Busted'); }
    
        $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
        $shipping_data = $_REQUEST['shipping']; // Token, Destination, Products
    
        // Load Shipping ON : Get Package based on User Data
        $shipping_physical_results = array();
        if( isset($lsdcommerce_shippings) ){
            foreach ($lsdcommerce_shippings as $key => $class) 
            {

                $object = new $class;
                if( $object->type == 'physical'){
                    if( $object->get_status() == 'on'){
                        echo $object->shipping_list( $shipping_data );
                    }
                }

            }
        }
        wp_die();
    }
}
new LSDCommerce_Shipping_AJAX;

Class LSDCommerce_Checkout_AJAX{
    public function __construct()
    {
        add_action( 'wp_ajax_nopriv_lsdc_checkout_extra_processing', [ $this, 'checkout_pre_processing' ]);
        add_action( 'wp_ajax_lsdc_checkout_extra_processing', [ $this, 'checkout_pre_processing' ]);
    }

    public function checkout_pre_processing(){
        $extras = array();
        if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
        if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'checkout-nonce' ) ) { die('Busted'); }

        $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
        empty( $_REQUEST['extras'] ) ? $extras = array() : $extras = $_REQUEST['extras']; // Extras Data from Javascript
    
        // Calculating Product -> Shipping Package
        $shipping_type = array();
        $weights  = 0;
        $grandtotal = 0;

        $products = $extras['products'];
        foreach ($products as $key => $product) {
            $product_id = abs( $product['id'] );

            /* PRO Code - Start, Just Ignore, don't Delete */
            if( isset( $product['variation_id'] ) ){
                $variation_id = esc_attr( $product['variation_id'], true );
            }else{
                $variation_id = null;
            }
            /* PRO Code - Start, Just Ignore, don't Delete */

            $product_price = $variation_id != null ? lsdc_product_variation_price( $product_id, lsdc_variation_ID( $variation_id, true) ) : lsdc_product_price( $product_id );
            // Assign to Object
            $extras['products'][$key]['id']           = $product_id;
            $extras['products'][$key]['variation_id'] = $variation_id;
            $extras['products'][$key]['qty']          = abs( $product['qty'] );
            $extras['products'][$key]['price_unit']   = $product_price; 
            $extras['products'][$key]['title']        = get_the_title( $product_id );
            $extras['products'][$key]['thumbnail']    = get_the_post_thumbnail_url( $product_id , 'lsdc-thumbnail-mini' );
            $extras['products'][$key]['total']        = lsdc_currency_format( true, $product_price * abs( $product['qty'] ) );
            $extras['products'][$key]['weight_unit']  = abs( get_post_meta( $product_id, '_physical_weight', true ) );

            $grandtotal  += $product_price * abs( $product['qty'] );
            $weights  += abs( get_post_meta( $product_id, '_physical_weight', true ) ) * abs( $product['qty'] );
            array_push( $shipping_type, get_post_meta( $product_id, '_shipping_type', true ) );
        }
        $extras['extras']['shipping']['weights'] = $weights; //Assign Weights for Shipping Calculation
        $extras['extras']['shipping']['types'] = $shipping_type;
        $extras['products']['grandtotal'] = $grandtotal;
        // Calculating Extras Cost
        $extra_cost = 0;
        $messages = array();
        if( has_filter('lsdcommerce_payment_extras') ) {
            // Procssing Raw Data from JS to Clean PHP
            // apply_filters('lsdcommerce_payment_extras', $extras );
            $extras = apply_filters('lsdcommerce_payment_extras', $extras );

            if( $extras ){
                // unset( $extras['products'] );
                // unset( $extras['extras'] );
                // unset( $extras['order_key'] );
                foreach ($extras as $key => $item) {
                    if( isset( $item['cost'] ) ){ //cost exist
                        $extra_cost += intval( $item['cost'] ); // calc every extra
                    }

                    if( isset( $item['messages'] ) ){ //
                        $messages[$key] = $item['messages'];
                    }
                }
            }

            // var_dump( $extras );
        }

        $templates = null;
        if( $extras ) {
            $templates .= '<div id="checkout-extras" data-total="' . intval( $extra_cost ) . '">'; 
                $templates .= '<table class="table table-borderless"><tbody>';
                    foreach ( $extras as $key => $item) {
                        if( !empty($item) && !empty( $item['value'] ) ) {
                            $templates .= '<tr>';
                                $label = esc_attr( $item['label'] );
                                if( isset( $item['bold'] ) && ! empty($item['bold']) ){
                                    $bold = '<span class="display-block text-uppercase font-weight-medium lsdc-theme-color">'. $item['bold'] .'</span>';
                                }else{
                                    $bold = null;
                                }

                                if( $item['sign'] == '-' ){
                                    $sign = '-';
                                }else{
                                    $sign = '';
                                }
                                $templates .= '<td>' .  $label .  $bold .'</td>';
                                $templates .= '<td class="text-right">' .  $sign .  $item['value'] .'</td>';
                            $templates .= '</tr>';
                        }
                    }
                    $templates .= '</tbody>';
                $templates .= '</table>';
            $templates .= '</div>';
        }

        echo json_encode( array(
            'error' => $messages,
            'template' => $templates
        ));
        wp_die();
    }
}
new LSDCommerce_Checkout_AJAX;
?>