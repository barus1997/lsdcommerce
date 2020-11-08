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
     * Validation Customer Input
     * Regisered ? Use Current ID : Register and Sign User
     * 
     * @return array $order_object
     * @note Next will Be Validation Form
     */
    private function validate_form($order_object)
    {
        // Member Checkout
        if (is_user_logged_in())
        {
            // Set ID
            $order_object['form']['id'] = get_current_user_id();
            // Set Name on Empty
            if (trim(lsdc_get_user_name()) == '')
            {
                $names = explode(' ', $order_object['form']['name'], 2);
                if (isset($names[1]))
                {
                    // Update User First and Last Name
                    wp_update_user([
                        'ID' => get_current_user_id() ,
                        'first_name' => sanitize_text_field($names[0]), 
                        'last_name' => sanitize_text_field($names[1])
                    ]);
                }
                else
                {
                    // Update User FirstName
                    wp_update_user([
                        'ID' => get_current_user_id() , 
                        'first_name' => sanitize_text_field( $order_object['form']['name'] )
                    ]);
                }

            }
            // Set Phone on Empty
            if (trim(lsdc_get_user_phone()) == '')
            {
                update_user_meta(get_current_user_id() , 'user_phone', lsdc_format_phone( $order_object['form']['phone'] ) );
            }

            // Reset Order Object
            $order_object['form']['name'] = null;
            $order_object['form']['phone'] = null;
            $order_object['form']['email'] = null;

        // Guest Checkout
        }
        else
        {
            // Validation and Sanitize Customer
            foreach ($order_object['form'] as $key => $item)
            {
                switch ($key)
                {
                    case 'name':
                        $order_object['form'][$key] = sanitize_text_field($item);
                    break;
                    case 'phone':
                        $order_object['form'][$key] = lsdc_format_phone($item);
                    break;
                    case 'email':
                        $order_object['form'][$key] = sanitize_email($item);
                    break;
                }

                // Sanitize More Form
                do_action('lsdcommerce_form_sanitize', $order_object);
            }

            // Setup User
            $username = lsdc_format_username($order_object['form']['name']);
            $password = lsdc_create_password();
            $names = explode(' ', $order_object['form']['name'], 2);

            // Set Names
            if (!isset($names[1])) $names = $order_object['form']['name'];

            // Register User
            $user_data = array(
                'user_login' => $username,
                'user_pass' => $password,
                'first_name' => (!isset($names[1])) ? $names : esc_attr($names[0]) ,
                'last_name' => (!isset($names[1])) ? '' : esc_attr($names[1]) ,
                'user_email' => $order_object['form']['email'],
                'role' => 'customer'
            );
            $user_id = wp_insert_user($user_data);

            if ($user_id)
            {

                wp_send_new_user_notifications($user_id); // Send Notification New Account
                update_user_meta($user_id, 'user_phone', $order_object['form']['phone']);

                //Do Action for Addon Verification Email
                //update_user_meta( $new_user_id, 'verification', false );
                // do_action('lsdcommerce_form_save', $order_object);

                // Sign In via AJAX
                $credentials = array();
                $credentials['user_login'] = $username;
                $credentials['user_password'] = $password;
                wp_signon($credentials);
            }

            $order_object['form']['id'] = $user_id;

        }

        return $order_object;
    }

    public function create_order($order_object)
    {

        $start_time = microtime(true);
        //>-------------> Order Start ---------------<//

        // Validation Customer
        $order_object = $this->validate_form($order_object);

        // Procssing Products
        $subtotal = 0;
        $weights = 0;

        foreach ($order_object['products'] as $key => $product)
        {
            $variation_id = null;
            $product_id = lsdc_product_extract_ID($product['id']);
            $product_price = lsdc_product_price($product_id);
            $product_title = get_the_title($product_id);

            $limit_order = get_post_meta($product_id, '_limit_order', true);
            $product_qty = $limit_order > abs($product['qty']) ? abs($product['qty']) : abs($limit_order);

            /* Start - Pro CODE, Ignore, Don't Delete */
            // Checking Variation Exist in Product
            if (lsdc_product_variation_exist($product_id, sanitize_text_field($product['id'])))
            {
                // Assign Variation ID
                $variation_id = sanitize_text_field($product['id']);
                $order_object['products'][$key]['variation_id'] = $variation_id;
                $order_object['products'][$key]['variations'][] = array( $variation_id, lsdc_product_variation_price($product_id, $variation_id) - $product_price );

                // Product Price based on Variation
                $product_price = lsdc_product_variation_price($product_id, $variation_id);
                $product_title = $product_title . ' - ' . lsdc_product_variation_label($product_id, $variation_id);
                // Limit Order by Variation - Soon
                // Limit Stock by Variation - Soon
            }
            /* End - Pro CODE, Ignore, Don't Delete */

            // ReAssign to Order Object
            $order_object['products'][$key]['id'] = $product_id;
            $order_object['products'][$key]['qty'] = $product_qty;
            $order_object['products'][$key]['price_unit'] = $product_price;
            $order_object['products'][$key]['price_unit_text'] = $product_price != 0 ? lsdc_currency_format(false, $product_price) : __('Gratis', 'lsdcommerce');
            $order_object['products'][$key]['weight_unit'] = abs(get_post_meta($product_id, '_physical_weight', true));
            $order_object['products'][$key]['title'] = $product_title;
            $order_object['products'][$key]['thumbnail'] = get_the_post_thumbnail_url($product_id, 'lsdc-thumbnail-mini');
            $order_object['products'][$key]['total'] = lsdc_currency_format(true, $product_price * $product_qty);

            $subtotal += $product_price * $product_qty;
            $weights += abs(get_post_meta($product_id, '_physical_weight', true)) * $product_qty;
        }

        //--> Calculating Extras Cost
        $surcharge = 0;
        $extras = array();
        $extras['extras']['shipping'] = $order_object['shipping']; // Assign Shipping for Calculation
        $extras['extras']['shipping']['weights'] = $weights; //Assign Weights for  Calculation

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

        //--> Set Payment Data
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

        //--> GrandTotal
        $total = 0;
        $total = $subtotal + $surcharge;
        $order_object['weights']    = $weights;
        $order_object['surcharge']  = $surcharge;
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
            'post_author'   => get_current_user_id()
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
            lsdc_order_status( $order_id, 'processed' );
        }

        // Testing Shipping and Notification Direct
        // lsdc_shipping_schedule_action( $order_id );
        // lsdc_notification_schedule_action( $order_id, 'order' );

        // Flag Remove Token
        delete_transient( 'lsdcommerce_checkout_' . $order_object['order_key']  );
        lsdc_order_unread_counter(); // Adding Order Counter


        //>-------------> Order End ---------------<//
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        LSDC_Logger::log('Execution time of Order = ' . $execution_time . ' sec');
        LSDC_Logger::log('Order#' . $order_id . ' Created from ' . $ip_address);
    }

    /**
     * Generating Thanyou URL
     */
    public function thankyou_url($token)
    {
        return json_encode(array(
            'code' => '_order_created',
            'redirect' => get_site_url() . '/payment/thankyou/' . $token . '/'
        ));
    }

    /**
     * Getting Last ID of LSDCommerce Order
     * Add 1 to make Squence
     */
    public function get_order_ID()
    {
        global $wpdb;
        $lastrow = $wpdb->get_col("SELECT ID FROM $wpdb->posts where post_type='lsdc-order' ORDER BY post_date DESC ");
        if (isset($lastrow[0]))
        {
            return abs(get_post_meta(abs($lastrow[0]) , 'order_id', true)) + 1;
        }
        else
        {
            return 1;
        }
    }
}
?>