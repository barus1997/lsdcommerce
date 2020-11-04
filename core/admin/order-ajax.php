<?php

/**
* Class and Function List:
* Function list:
* - lsdc_admin_order_action()
* - lsdc_admin_order_resi()
* Classes list:
*/

/**
 * Handle admin order action status
 *
 * @package Order
 * @subpackage Action
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_order_action', 'lsdc_admin_order_action');
add_action('wp_ajax_lsdc_admin_order_action', 'lsdc_admin_order_action');
function lsdc_admin_order_action() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    $data = sanitize_text_field($_REQUEST['data']);
    $order_id = abs($_REQUEST['orderid']);
    switch ($data) {
        case 'paid':
            lsdc_order_status($order_id, 'paid');
            echo 'action_success';
        break;
        case 'processed':
            lsdc_order_status($order_id, 'processed');
            echo 'action_success';
        break;
        case 'shipped':
            lsdc_order_status($order_id, 'shipped');
            echo 'action_success';
        break;
        case 'completed':
            lsdc_order_status($order_id, 'completed');
            echo 'action_success';
        break;
        case 'refunded':
            lsdc_order_status($order_id, 'refunded');
            echo 'action_success';
        break;
        case 'canceled':
            lsdc_order_status($order_id, 'canceled');
            echo 'action_success';
        break;
    }
    wp_die();
}

/**
 * Handle admin order resi
 *
 * @package Order
 * @subpackage Action
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_order_resi', 'lsdc_admin_order_resi');
add_action('wp_ajax_lsdc_admin_order_resi', 'lsdc_admin_order_resi');
function lsdc_admin_order_resi() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    $resi = sanitize_text_field($_REQUEST['resi']);
    $order_id = abs($_REQUEST['orderid']);
    update_post_meta($order_id, 'resi', $resi);
}
?>