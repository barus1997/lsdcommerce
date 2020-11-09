<?php
/**
* Class and Function List:
* Function list:
* - lsdc_order_unread_counter()
* - lsdc_order_status()
* - lsdc_order_on_new()
* - lsdc_order_on_paid()
* - lsdc_order_on_canceled()
* - lsdc_order_on_processed()
* - lsdc_order_on_shipped()
* - lsdc_order_on_completed()
* - lsdc_order_status_translate()
* - lsdc_order_ID()
* - lsdc_order_get_total()
* - lsdc_order_get_email()
* Classes list:
*/
/**
 * Order Unread Counter
 *
 * @subpackage Order
 * @since 1.0.0
 */
function lsdc_order_unread_counter()
{
    $unread = abs(get_option('lsdcommerce_order_unread'));
    if (is_numeric($unread))
    {
        $unread++;
        update_option('lsdcommerce_order_unread', $unread);
    }
    else
    {
        update_option('lsdcommerce_order_unread', 0);
    }
}

/**
 * Change Order Status and Fired Action
 *
 * @subpackage Order
 * @since 1.0.0
 *
 * @param int $order_id;
 * @param string $status
 */
function lsdc_order_status($order_id, $status)
{
    // Updating Status
    update_post_meta($order_id, 'status', $status);

    // Fired Action
    switch ($status)
    {
            // New Order
            
        case 'new':
            do_action('lsdcommerce_order_status_new', $order_id);
        break;

            // Paid Order
            
        case 'paid':
            do_action('lsdcommerce_order_status_paid', $order_id);

            if (in_array('digital', lsdc_product_check_type($order_id)) && ! in_array('physical', lsdc_product_check_type($order_id)) ) // Product Digital and Not Physical
            {
                lsdc_order_status($order_id, 'processed');
            }
        break;

            // Canceled Order
            
        case 'canceled':
            do_action('lsdcommerce_order_status_canceled', $order_id);
        break;

            // Processed Order
            
        case 'processed':
            do_action('lsdcommerce_order_status_processed', $order_id);

            // Digital Auto Shipped
            if (in_array('digital', lsdc_product_check_type($order_id)) && ! in_array('physical', lsdc_product_check_type($order_id)) )
            {
                lsdc_order_status($order_id, 'shipped');
            }
        break;

            // Shipped Order
            
        case 'shipped':
            do_action('lsdcommerce_order_status_shipped', $order_id);

            // Digital Auto Completed
            if (in_array('digital', lsdc_product_check_type($order_id)) && ! in_array('physical', lsdc_product_check_type($order_id)) )
            {
                lsdc_order_status($order_id, 'completed');
            }
        break;

            // Complete Order
            
        case 'completed':
            do_action('lsdcommerce_order_status_completed', $order_id);
        break;

            // Refunded Order
            
        case 'refunded':
        break;
    }
}

/**
 * Action on New Order
 *
 * @subpackage Order
 * @since 1.0.0
 */
function lsdc_order_on_new($order_id)
{
    // Not Digital Product and Not Free Product -> Send Notification Waiting Payment
    if (!in_array('digital', lsdc_product_check_type($order_id)) && !lsdc_order_get_total($order_id) == 0)
    {
        wp_schedule_single_event(time() + 3, 'lsdc_notification_schedule', array(
            $order_id,
            'order'
        ));
    }
}
add_action('lsdcommerce_order_status_new', 'lsdc_order_on_new');

/**
 * Action on Paid Order
 *
 * @subpackage Order
 * @since 1.0.0
 */
function lsdc_order_on_paid($order_id)
{
    wp_schedule_single_event( time() + 9, 'lsdc_notification_schedule', array( $order_id, 'paid' ) );  // Notification Cron
    
}
add_action('lsdcommerce_order_status_paid', 'lsdc_order_on_paid');

/**
 * Action on Cancled Order
 *
 * @subpackage Order
 * @since 1.0.0
 */
function lsdc_order_on_canceled($order_id)
{
    wp_schedule_single_event( time() + 9, 'lsdc_notification_schedule', array( $order_id, 'canceled' ) );   // Notification Cron
    
}
add_action('lsdcommerce_order_status_canceled', 'lsdc_order_on_canceled');

/**
 * Action on Processed Order
 *
 * @subpackage Order
 * @since 1.0.0
 */
function lsdc_order_on_processed($order_id)
{
    if (in_array('digital', lsdc_product_check_type($order_id)))
    {
        wp_schedule_single_event(time() + 15, 'lsdc_shipping_schedule', array(
            $order_id
        )); // Shipping Cron
        
    }
}
add_action('lsdcommerce_order_status_processed', 'lsdc_order_on_processed');

/**
 * Action on Shipped Order
 *
 * @subpackage Order
 * @since 1.0.0
 */
function lsdc_order_on_shipped($order_id)
{
    if (in_array('digital', lsdc_product_check_type($order_id)))
    {
        lsdc_order_status($order_id, 'completed');
    }
    else
    {
        wp_schedule_single_event( time() + 9, 'lsdc_notification_schedule', array( $order_id, 'shipped' ) );   // Notification Cron
        
    }
}
add_action('lsdcommerce_order_status_shipped', 'lsdc_order_on_processed');

/**
 * Action on Completed Order
 *
 * @subpackage Order
 * @since 1.0.0
 */
function lsdc_order_on_completed($order_id)
{
    wp_schedule_single_event(time() + 15, 'lsdc_notification_schedule', array(
        $order_id,
        'completed'
    )); // Notification Cron
    
}
add_action('lsdcommerce_order_status_completed', 'lsdc_order_on_completed');

/**
 * Order Status Translation
 *
 * @subpackage Order
 * @since 1.0.0
 * @param int $order_id;
 */
function lsdc_order_status_translate($order_id)
{
    $status = esc_attr(get_post_meta($order_id, 'status', true));
    switch ($status)
    {
        case 'new':
            return __("Baru", 'lsdcommerce');
        break;
        case 'paid':
            return __("Dibayar", 'lsdcommerce');
        break;
        case 'processed':
            return __("Diproses", 'lsdcommerce');
        break;
        case 'canceled':
            return __("Batal", 'lsdcommerce');
        break;
        case 'shipped':
            return __("Dikirim", 'lsdcommerce');
        break;
        case 'completed':
            return __("Selesai", 'lsdcommerce');
        break;
        case 'refunded':
            return __("Dikembalikan", 'lsdcommerce');
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
function lsdc_order_ID($hash)
{
    if (is_numeric($hash))
    {
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'lsdc-order',
            'meta_query' => array(
                array(
                    'key' => 'order_id',
                    'value' => $hash,
                    'compare' => '=',
                )
            )
        );
    }
    else
    {
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'lsdc-order',
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
    if ($query->posts)
    {
        return abs($query->posts[0]
            ->ID); // Parsing Order ID
        
    }
    else
    {
        return false;
    }

}

/**
 * Getting Total Order by OrderID
 */
function lsdc_order_get_total($order_id)
{
    return abs(get_post_meta($order_id, 'total', true));
}

/**
 * Getting Email by OrderID
 */
function lsdc_order_get_email($order_id)
{
    if (get_post_meta($order_id, 'customer_id', true))
    {
        $customer_id = abs(get_post_meta($order_id, 'customer_id', true));
        return lsdc_get_user_email($customer_id);
    }
    else
    {
        $customer = json_decode(get_post_meta($order_id, 'customer', true));
        return $customer->email;
    }
}
?>
