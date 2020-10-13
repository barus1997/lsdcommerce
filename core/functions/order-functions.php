<?php 
/**
 * Add Unread Order Count in Order Admin Page
 * 
 * @subpackage Order
 * @since 0.4.0
 */
function lsdc_order_unread_counter()
{
    $lsdc_order_unread_counter = get_option('lsdc_order_unread_counter');

    if( is_numeric( $lsdc_order_unread_counter ) ) {
		update_option('lsdc_order_unread_counter', $lsdc_order_unread_counter++ );
	}else{
		update_option('lsdc_order_unread_counter', 0);
	}
}

/**
 * Change Order Status and Fired Notification
 * 
 * @subpackage Order
 * @since 0.4.0
 * 
 * @param int $order_id;
 * @param string $status
 * @param boolean $notification
 */
function lsdc_order_status( $order_id, $status, $notification = true )
{
    // Updating Status
    update_post_meta( $order_id, 'status', $status );

    if( $notification == true ){
        switch ( $status ) 
        {
            // New Order
            case 'new':
                wp_schedule_single_event( time() + 3, 'lsdc_notification_schedule', array( $order_id, 'order' ) );   // Notification Cron
            break;

            // Paid Order
            case 'paid':
                if( in_array( 'digital', lsdc_product_check_type(  $order_id ) ) ){
                    lsdc_order_status( $order_id, 'processed' );
                }else{
                    wp_schedule_single_event( time() + 3, 'lsdc_notification_schedule', array( $order_id, 'paid' ) );  // Notification Cron
                }
            break;

            // Canceled Order
            case 'canceled': 
                wp_schedule_single_event( time() + 3, 'lsdc_notification_schedule', array( $order_id, 'canceled' ) );   // Notification Cron
            break;

            // Processed Order
            case 'processed':
                if( in_array( 'digital', lsdc_product_check_type(  $order_id ) ) ){
                    wp_schedule_single_event( time() + 6, 'lsdc_shipping_schedule' , array( $order_id ) );  // Shipping Cron
                    lsdc_order_status( $order_id, 'shipped' );
                }
            break;

            // Shipped Order
            case 'shipped':
                if( in_array( 'digital', lsdc_product_check_type(  $order_id ) ) ){
                    lsdc_order_status( $order_id, 'completed' );
                }else{
                    wp_schedule_single_event( time() + 6, 'lsdc_notification_schedule', array( $order_id, 'shipped' ) );   // Notification Cron
                }
            break;

            // Complete Order
            case 'completed': // Pesanan Selesai
                wp_schedule_single_event( time() + 6, 'lsdc_notification_schedule', array( $order_id, 'completed' ) );   // Notification Cron
            break;

            // Refunded Order
            case 'refunded': 
            break;
        }
    }

}

/**
 * Order Status Translation
 * 
 * @subpackage Order
 * @since 0.4.0
 * @param int $order_id;
 */
function lsdc_order_status_translate( $order_id )
{
    $status = esc_attr( get_post_meta( $order_id, 'status', true ) );
    switch ( $status ) {
        case 'new':
            return __( "Baru", 'lsdcommerce' );
            break;
        case 'paid':
            return __( "Dibayar", 'lsdcommerce' );
            break;
        case 'processed':
            return __( "Diproses", 'lsdcommerce' );
            break;
        case 'canceled':
            return __( "Batal", 'lsdcommerce' );
            break;
        case 'shipped':
            return __( "Dikirim", 'lsdcommerce' );
            break;
        case 'completed':
            return __( "Selesai", 'lsdcommerce' );
            break;
        case 'refunded':
            return __( "Dikembalikan", 'lsdcommerce' );
            break;
    }
}

/**
 * Getting Order ID by Hash Order | Order_ID
 * 
 * @subpackage Order
 * @since 0.4.0
 * @param mixed $hash
 */
function lsdc_order_ID( $hash )
{
    if( is_numeric( $hash ) ){
        $args = array(
            'post_status'   => 'publish',
            'post_type'     => 'lsdc-order',
            'meta_query' => array(
                array(
                    'key' => 'order_id',
                    'value' => $hash,
                    'compare' => '=',
                )
            )
        );
    }else{
        $args = array(
            'post_status'   => 'publish',
            'post_type'     => 'lsdc-order',
            'meta_query' => array(
                array(
                    'key' => 'order_key',
                    'value' => $hash,
                    'compare' => '=',
                )
            )
        );
    }

    $query = new WP_Query($args);
    if( $query->posts  ){
        return abs( $query->posts[0]->ID ); // Parsing Order ID 
    }else{
        return false;
    }
    
}
?>