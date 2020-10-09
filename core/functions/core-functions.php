<?php 
use LSDCommerce\Notification\LSDC_Notification;
use LSDCommerce\Shipping\LSDC_Shipping;
use LSDCommerce\Logger\LSDC_Logger;

// Display price Plain
function lsdc_product_price( $product_id = false )
{
    if( $product_id == null ) $product_id = get_the_ID(); //Fallback Product ID

    $normal     = lsdc_price_normal( $product_id );
    $discount   = lsdc_price_discount( $product_id );

    if( $discount ){
        return abs($discount);
    }else{
        if( $normal ){
            return abs($normal);
        }else{
            return 0;
        }
    }
}

// Display price Plain
function lsdc_product_variation_price( $product_id, $variation_id )
{
    if( $product_id == null ) $product_id = get_the_ID(); //Fallback Product ID
    $variation_id = esc_attr( $variation_id );

    $variations = (array) json_decode( get_post_meta( $product_id, '_variations', true ) ); 

    $variation_price = null;

    foreach ( $variations as $key => $items) {
        foreach ( $items->items as $child => $item) {

            if( strtolower($item->name) == strtolower($variation_id) ){
                $variation_price = abs($item->price);
            }
        }
    }
    // $variations['items'][$variation_id]['price'];

    $normal     = lsdc_price_normal( $product_id );
    $discount   = lsdc_price_discount( $product_id );

    if( $discount ){
        return abs($discount) + abs($variation_price);
    }else{
        if( $normal ){
            return abs($normal) + abs( $variation_price );
        }else{
            return 0;
        }
    }
}


// Display Weight Product
function lsdc_product_weight( $product_id = false )
{
    if( $product_id == null ) $product_id = get_the_ID(); //Fallback Product ID
    
    return abs( lsdc_currency_clear( get_post_meta( $product_id, '_physical_weight', true ) ) );
}

// Display price Plain
function lsdc_product_stock( $product_id = false )
{
    if( $product_id == null ) $product_id = get_the_ID(); //Fallback Product ID

    $stock = '<p>' . __( 'Stock', 'lsdcommerce' ) .'<span>';
        if( get_post_meta( $product_id, '_stock', true ) == 9999 ) :
            $stock .= __( 'Available', 'lsdcommerce' );
        else :
            $stock .= abs( get_post_meta( get_the_ID(), '_stock', true ) ) . ' ' . esc_attr( get_post_meta( get_the_ID(), '_stock_unit', true ) );
        endif;
    $stock .= '</span></p>';

    return $stock;
}

function lsdc_product_download_version( $product_id ){
    if( get_post_meta( $product_id, '_digital_version', true ) ){
        return esc_attr( get_post_meta( $product_id, '_digital_version', true ) );
    }
}

function lsdc_product_download_link( $product_id ){
    if( get_post_meta( $product_id, '_digital_url', true ) ){
        return esc_url( get_post_meta( $product_id, '_digital_url', true ) );
    }
}


function lsdc_cart_manager()
{
    ?>
    <!-- Quantity Button -->
    <div class="cart-qty-float fixed" product-id="<?php the_ID(); ?>">
        <div class="lsdc-qty" id="single-qty">
            <button type="button" class="minus button-qty" data-qty-action="minus">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>
            <input min="0" type="number" value="0" name="qty" disabled>
            <button type="button" class="plus button-qty" data-qty-action="plus">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>
        </div>
    </div>

    <!-- Cart Management Template : Passed 1.0.0 -->
    <script id="item-template" type="x-template">
        <div class="cart-basket">
            {{#items}}
            <div class="item" id="{{id}}">
                <div class="lsdp-row no-gutters">
                    <div class="col-auto item-name">
                        <div class="img">
                            <img src="{{thumbnail}}" alt="{{title}}"></div>
                        <h6>
                            <span class="name">{{title}}</span>
                            <span class="price">{{price}}</span>
                        </h6>
                    </div>
                    <div class="col-auto item-qty qty ml-auto">
                        <div class="lsdc-qty" >
                            <button type="button" class="minus button-qty" data-qty-action="minus">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                            <input min="0" type="number" value="{{qty}}" name="qty" disabled>
                            <button type="button" class="plus button-qty" data-qty-action="plus">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{/items}}
        </div>
    </script>

    <div id="cart-popup" class="cart-popup">
        <div class="overlay"></div>
        <div class="cart-container">
            <div class="cart-body hidden">
                <div class="lsdp-row no-gutters mb-3">
                    <div class="col-auto text-left">
                        <p><strong><?php _e( 'Item','lsdcommerce'); ?></strong></p>
                    </div>
                    <div class="col-4 text-right ml-auto">
                        <p><strong><?php _e( 'Quantity','lsdcommerce'); ?></strong></p>
                    </div>
                </div>
                <div class="cart-items p-0" id="cart-items">
                </div>
            </div>
            <div class="cart-footer">
                <div class="container">
                    <div class="lsdp-row no-gutters">
                        <div class="col-auto">
                            <div class="lsdp-row no-gutters align-items-center">
                                <div class="col-auto pr-0">
                                    <a href="javascript:void(0);" class="cart-manager">
                                        <span class="counter">0</span>
                                        <img src="<?php echo LSDC_URL; ?>assets/img/icons/cart-outline.svg" alt="" class="icon-20">
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="cart-footer-info">
                                        <h6><?php _e( "Cart", 'lsdcommerce' ); ?></h6>
                                        <h4><?php _e( "Empty", 'lsdcommerce' ); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto ml-auto inline-flex">
                            <button class="lsdp-btn lsdc-btn btn-primary px-5 lsdc-addto-cart"><?php _e( 'Add', 'lsdcommerce' ); ?></button>
                            <a class="lsdp-btn lsdc-btn btn-primary btn-dark px-4" href="<?php echo get_the_permalink( lsdc_get( 'general_settings', 'checkout_page' )); ?>"><?php _e( "Checkout", 'lsdcommerce' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action('lsdcommerce_single_after', 'lsdc_cart_manager' );
/**
 * This Core Function Provide any Functionality of LSDCommerce
 * - Currency
 * - Date
 * - Price
 */

 
/**
 * @package LSDCommerce
 * @subpackage Currency
 * 
 * @since 1.0.0
 */
// Plain to Format || 10000 -> Rp 10.000
function lsdc_currency_format( $symbol = true, $float,$curr = "IDR" ){
	$c['IDR'] = array( 0, ',', '.', 'Rp ' );
    $c['USD'] = array( 0, '.', ',', '$' );
    if( abs($float) == 0 ){
        return __( "Gratis", 'lsdcommerce' );
    }else{
        if( $symbol == false ){
            return number_format($float,$c[$curr][0],$c[$curr][1],  $c[$curr][2]); //10000-> Rp 10.000
        }else{
            return $c[$curr][3] . number_format($float,$c[$curr][0],$c[$curr][1],  $c[$curr][2]); //10000-> Rp 10.000
        }
    }
}

// Formatted to Plain || Rp 10.000 -> 100000
function lsdc_currency_clear( $formatted_number ){
	$formatted_number = preg_replace('/[^0-9]/', '', $formatted_number );
	$formatted_number = preg_replace('/\,/', '', $formatted_number );
	return abs( preg_replace('/\./', '', $formatted_number ) ); // Rp 10.000 -> 10000
}

// Display Currency Symbol based Store Settings
function lsdc_currency_view( $type = 'symbol' ){
    $currency = array(
        'IDR'   => array( 
            'symbol' => 'Rp ',
            'format' => '15.000'
        ),
        'USD'   => array( 
            'symbol' => '$',
            'format' => '1'
        ),
    );
    echo $currency[lsdc_currency_get()][$type];
}

// Get Currency based on Store Settings
function lsdc_currency_get(){
    $settings = get_option('lsdc_store_settings', true );
    return isset( $settings['lsdc_store_currency'] ) ? esc_attr( $settings['lsdc_store_currency'] ) : 'IDR';
}




/**
 * @package LSDCommerce
 * @subpackage Date
 * 
 * @since 1.0.0
 */
// Generate Date Now
function lsdc_date_now(){
    return date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
}
// Format Date
function lsdc_date_format( $str, $format = 'j M Y ' ){
    return date( $format, strtotime( $str ));
}
// Getting Date Diff
function lsdc_date_diff($date1, $date2){ 
    $diff = strtotime($date2) - strtotime($date1); 
    return abs(round($diff / 86400)); 
} 

/**
 * @package LSDCommerce
 * @subpackage Price
 * 
 * @since 1.0.0
 */
function lsdc_price_normal( $product_id = false ){
    return abs( get_post_meta( $product_id, '_price_normal', true ) );
}

function lsdc_price_discount( $product_id = false ){
    return abs( get_post_meta( $product_id, '_price_discount', true ) );
}

// Display Price with HTML
function lsdc_price_frontend( $product_id = false ){
    if( $product_id == null ) $product_id = get_the_ID(); //Fallback Product ID

    $normal     = lsdc_price_normal( $product_id );
    $discount   = lsdc_price_discount( $product_id );

    if( $discount ) : ?>
        <span class="product-item-price-discount">
            <?php echo lsdc_currency_format( true, get_post_meta( get_the_ID(), '_price_normal', true ) ); ?>
        </span> 
        <span class="product-price product-item-price-normal discounted">
            <?php echo lsdc_currency_format( true, get_post_meta( get_the_ID(), '_price_discount', true ) ); ?>
        </span>
    <?php else: ?>
        <?php if( $normal ) : ?>
        <span class="product-price product-item-price-normal">
            <?php echo lsdc_currency_format( true, get_post_meta( get_the_ID(), '_price_normal', true ) ); ?>
        </span>
        <?php else: ?>
            <span class="product-item-price-normal">
                <?php _e( "Free", 'lsdcommerce' ); ?>
            </span>
        <?php endif; ?>
    <?php endif;
}

function lsdc_variation_ID( $id, $variation = false ){
    $ids = explode( "-", $id );
    if( $variation == true ){
        // Prood : 3451-pedes, what about 3451-xl-biru ?
        return esc_attr( $ids[1] );
    }else{
        if( isset( $ids[0] ) ){ 
            return $ids[0];
        }else{
            return $id;
        }
    }

}

/**
 * Checking isVariation Exist or Not
 */
function lsdc_isVariation( $id, $variation ){
    $variations = json_decode( get_post_meta( $id, '_variations', true) );

    $temp = array();
    foreach ( $variations[0]->items  as $key => $item ) {
        if( strtolower($item->name) == strtolower( $variation ) ){
            $temp[] = strtolower($item->name);
        }
    }

    if( in_array(strtolower( $variation ), $temp ) ){
        return true;
    }else{
        return false;
    }
}

function lsdc_variation_label(  $id, $variation ){
    $variations = json_decode( get_post_meta( $id, '_variations', true) );

    $label = null;
    foreach ( $variations[0]->items  as $key => $item ) {
        if( strtolower($item->name) == strtolower( $variation ) ){
            return $item->name;
        }
    }
}

/**
 * Getting Option from Settings, with Parent and Point Selectord
 * 
 * @package LSDCommerce
 * @subpackage General
 * @since 1.0.0
 * 
 * Usage ::  get_the_permalink( lsdc_get( 'general_settings', 'checkout_page' ) );
 */
function lsdc_get( $option, $item ){
    $settings = get_option( 'lsdc_' . $option, true ); 
    return empty( $settings[$item] ) ? null : esc_attr( $settings[$item] );
}

/**
 * Create ID
 * @param string $string
 * return lower case and striped
 */
function lsdc_createID( $string ){
    return sanitize_title( strtolower(preg_replace("/[^a-z0-9]+/i", "-", $string )));
}


// Shipping --------------------------------------------------------------
function lsdc_number_clear( $formatted_number ){
	$formatted_number = preg_replace('/[^0-9]/', '', $formatted_number );
	$formatted_number = preg_replace('/\,/', '', $formatted_number );
	return abs( preg_replace('/\./', '', $formatted_number ) ); // Rp 10.000 -> 10000
}

function lsd_create_schedule( $name, $time ){
    $timestamp = wp_next_scheduled( $name );
    if( $timestamp == false ){
        wp_schedule_event( time(), 'daily', $name );
    }
}

/**
 * Load Template in LSDCommerce
 * You can override this templates
 */
function lsdcommerce_template(){
	$templates = array( 
		'store' 	=> LSDC_PATH . 'templates/store.php',
		'single' 	=> LSDC_PATH . 'templates/single.php',
		'checkout' 	=> LSDC_PATH . 'templates/checkout.php',
		'member' 	=> LSDC_PATH . 'templates/member.php',
		'category' 	=> LSDC_PATH . 'templates/category.php',
	);

	if( has_filter('lsdcommerce_template') ) {
		$templates = apply_filters('lsdcommerce_template', $templates);
	}
	return $templates;
}

/**
 * Conditionals Tags
 */
function is_lsdcommerce( $page = false ){
    global $lsdcommerce;
    if( $page != false ){
        if( isset( $lsdcommerce['page'] ) && $lsdcommerce['page'] == $page ) return true;
    }else{
        if( isset( $lsdcommerce['page'] ) ) return true;
    }
   
	return false;
}

// Set Lsdcommerce Class on Body for Page
// Shortcode Not Working
function set_lsdcommerce( $page = false ){
    global $lsdcommerce;
    if( $page != false ){
        $lsdcommerce['page'] = $page;
    }else{
        $lsdcommerce['page'] = 'lsdcommerce';
    }
    
}


/**
 * Notification Handler
 * Trigger via CRON
 * Hard to Debug
 */
function lsdc_notification_schedule_action( $order_id, $event ) 
{
    $notify = array(
        'order' => array(
            'subject'   => __( "Pesanan Diterima" , 'lsdcommerce' ),
            'receiver'  => array( 'buyer', 'admin' )
        ),
        'canceled' => array(
            'subject'   => __( "Pesanan Dibatalkan" , 'lsdcommerce' ),
            'receiver'  => array( 'buyer' )
        ),
        'paid' => array(
            'subject'   => __( "Pesanan Dibayar" , 'lsdcommerce' ),
            'receiver'  => array( 'buyer' )
        ),
        'shipped' => array(
            'subject'   => __( "Sedang Dikirim" , 'lsdcommerce' ),
            'receiver'  => array( 'buyer' )
        ),
        'completed' => array(
            'subject'   => __( "Pesanan Selesai" , 'lsdcommerce' ),
            'receiver'  => array( 'buyer' )
        )
    );


    $order_number = null;
    if( ! empty( get_post_meta( $order_id, 'order_id', true ) ) ){
        $order_number = abs( get_post_meta( $order_id, 'order_id', true ) );
    }

    // Getting Customer based on ID or Direct
    $customer_email = null;
    if( get_post_meta( $order_id, 'customer_id', true ) ){
        $customer_id = abs( get_post_meta( $order_id, 'customer_id', true ) );
        $customer_email = lsdc_user_getemail( $customer_id );
    }else{
        $customer = json_decode( get_post_meta( $order_id, 'customer', true ) );
        $customer_email = $customer->email;
    }



    // Buyer
    if( isset( $notify[$event]['receiver'][0] ) ){
        $payload                       = array();
        $payload['email']              = $customer_email;
        $payload['subject']            = $notify[$event]['subject'] . ' #' . $order_number;
        $payload['order_id']           = $order_id;
        $payload['order_number']       = $order_number;
        $payload['notification_event'] = $event;
        LSDC_Logger::log( 'Buyer Notification : ' . json_encode( $payload ) );
        var_dump( $payload );
        LSDC_Notification::sender( $payload );
    }

    
    // Admin
    // if( isset( $notify[$event]['receiver'][1] ) ){
    //     $payload                       = array();
    //     $payload['email']              = get_bloginfo('admin_email');
    //     $payload['subject']            = 'Pesanan Baru #' . $order_number;
    //     $payload['order_number']       = $order_number;
    //     $payload['notification_event'] = $event;
    //     LSDC_Logger::log( 'Admin Notification : ' . json_encode( $payload ) );
    //     LSDC_Notification::sender( $payload );
    // }


}
add_action( 'lsdc_notification_schedule', 'lsdc_notification_schedule_action', 10, 2 );


/**
 * Shipping Handler
 */
function lsdc_shipping_schedule_action( $order_id ) 
{
    $customer_email = null;
    if( get_post_meta( $order_id, 'customer_id', true ) ){
        $customer_id = abs( get_post_meta( $order_id, 'customer_id', true ) );
        $customer_email = lsdc_user_getemail( $customer_id );
    }else{
        $customer = json_decode( get_post_meta( $order_id, 'customer', true ) );
        $customer_email = $customer->email;
    }

    $payload = array();
    $payload['subject']     = __( "Product Delivery" , 'lsdcommerce' );
    $payload['order_id']    = $order_id;
    $payload['type']        = 'digital';
    $payload['email']       = $customer_email;
    LSDC_Logger::log( 'Shipping : #' . $order_id );
    LSDC_Shipping::sender( $payload );



    // Auto Completed Order for Empty and Digital Purchase
    $total = abs( get_post_meta( $order_id, 'total', true ) );
    $shipping = (array) json_decode( get_post_meta( $order_id, 'shipping', true ) );
    if( $total == 0 && isset( $shipping['digital'] ) && ! isset( $shipping['physical'] ) ) {
        lsdc_order_status( $order_id, 'completed', true );
    }
}
add_action( 'lsdc_shipping_schedule', 'lsdc_shipping_schedule_action', 10, 1 );


