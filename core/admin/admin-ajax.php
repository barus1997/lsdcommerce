<?php

/**
 * @package LSDCommerce
 * @subpackage Store
 * Handle Saving Store Data in WordPress Option
 *
 * @since    1.0.0
 */
add_action( 'wp_ajax_nopriv_lsdc_admin_store_save', 'lsdc_admin_store_save' );
add_action( 'wp_ajax_lsdc_admin_store_save', 'lsdc_admin_store_save' );
function lsdc_admin_store_save(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

    $store = $_REQUEST['store'];
    $saving = array();
    $saving['country']       = sanitize_text_field( $store['country'] );
    $saving['state']         = sanitize_text_field( $store['state'] );
    $saving['city']          = sanitize_text_field( $store['city'] );
    $saving['address']       = sanitize_text_field( $store['address'] );
    $saving['postalcode']    = sanitize_text_field( $store['postalcode'] );
    $saving['currency']      = sanitize_text_field( $store['currency'] );
    update_option( 'lsdc_store_settings', $saving );
    echo 'action_success';

    wp_die();
}

// -------------------------------------------------------------------------------- //

/**
 * @package LSDCommerce
 * @subpackage Appearance
 * Handle Saving Store Data in WordPress Option
 *
 * @since    1.0.0
 */
add_action( 'wp_ajax_nopriv_lsdc_admin_appearance_save', 'lsdc_admin_appearance_save' );
add_action( 'wp_ajax_lsdc_admin_appearance_save', 'lsdc_admin_appearance_save' );
function lsdc_admin_appearance_save(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    $data       = $_REQUEST['appearance'];
    $out        = array();
    parse_str(html_entity_decode($data), $out);


    update_option( 'lsdc_appearance_settings', $out );
    echo 'action_success';

    wp_die();
}

// -------------------------------------------------------------------------------- //

/**
 * @package LSDCommerce
 * @subpackage Payment
 * Handle Enable On Off Payment Method
 *
 * @since    1.0.0
 */
add_action( 'wp_ajax_nopriv_lsdc_admin_payment_status', 'lsdc_admin_payment_status' );
add_action( 'wp_ajax_lsdc_admin_payment_status', 'lsdc_admin_payment_status' );
function lsdc_admin_payment_status(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
    
    // Toggle Payment Method
    $id                         = str_replace( "_status", "", esc_attr( $_REQUEST['id'] ) );

    $state                      = esc_attr( $_REQUEST['state'] );
    $lsdc_payment_status        = get_option( 'lsdc_payment_status' );
    if( $lsdc_payment_status == '' ) $lsdc_payment_status = array();
    $lsdc_payment_status[$id]   = $state;

    update_option('lsdc_payment_status', $lsdc_payment_status );
    echo 'action_success';

    wp_die();
}



/**
 * @package LSDCommerce
 * @subpackage Payment
 * Saving Data Bank based on User Settings
 *
 * @since    1.0.0
 */
add_action( 'wp_ajax_nopriv_lsdcommerce_payment_option', 'lsdcommerce_payment_option' );
add_action( 'wp_ajax_lsdcommerce_payment_option', 'lsdcommerce_payment_option' );
function lsdcommerce_payment_option(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
 
    $origin_id  = sanitize_title( strtolower(preg_replace("/[^a-z]+/i", "", $_REQUEST['id'] ))); // Sanitize Title
    $method     = 'lsdcommerce_payment_option';

    $data       = $_REQUEST['serialize'];
    $out        = array();
    parse_str( $data, $out );

    $clean = array();

    // Support Custom Banks
    foreach ($out as $key => $value) {
        if( $key == 'logo'){
            $clean[$key] = esc_url($value);
        }else{
            $clean[$key] = sanitize_text_field( stripslashes_deep($value) );
        }

        if( $key == 'alias' ){
            $alias = lsdc_sanitize_id( $value );
        }
    }

    // Storing to Settings
    $payment_method = get_option( $method ); 

    if( isset($payment_method[$origin_id]) )
    {
        $merge = array_merge( $payment_method[$origin_id], $clean ); // merge array
    }
    else if( isset($payment_method[$alias]) )
    {
        $merge = array_merge( $payment_method[$alias], $clean ); // merge array for custom banks
    }
    else
    {
        $merge = $clean; // If Empty Setting, Create Array
    }

    // If Has Custom Bank
    if( $alias )
    {
        $merge['alias'] = $origin_id;
        $payment_method[$origin_id]['alias']  = $alias; // set custom by alias || pointing to alias data
        $payment_method[$alias]  = $merge; // set alias data independent
    }
    else
    {
        $payment_method[$origin_id] = $merge;
    }
    
    update_option( $method, $payment_method );
    echo 'action_success';
   
    wp_die();
}

// -------------------------------------------------------------------------------- //

/**
 * @package LSDCommerce
 * @subpackage Notification
 * Handle Enable On Off Notification Method
 *
 * @since    1.0.0
 */
add_action( 'wp_ajax_nopriv_lsdc_admin_notification_status', 'lsdc_admin_notification_status' );
add_action( 'wp_ajax_lsdc_admin_notification_status', 'lsdc_admin_notification_status' );
function lsdc_admin_notification_status(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
    
    // Toggle Notification Method
    $id                         = str_replace( "_status", "", esc_attr( $_REQUEST['id'] ) );
    $state                      = esc_attr( $_REQUEST['state'] );
    $lsdc_payment_status        = get_option( 'lsdcommerce_notification_status' );

    if( $lsdc_payment_status == '' ) $lsdc_payment_status = array();
    $lsdc_payment_status[$id]   = $state;

    update_option('lsdcommerce_notification_status', $lsdc_payment_status );
    echo 'action_success';

    wp_die();
}

// -------------------------------------------------------------------------------- //

/**
 * @package LSDCommerce
 * @subpackage Shipping
 * Handle Enable On Off Shipping Method
 *
 * @since    1.0.0
 */
add_action( 'wp_ajax_nopriv_lsdc_admin_shipping_status', 'lsdc_admin_shipping_status' );
add_action( 'wp_ajax_lsdc_admin_shipping_status', 'lsdc_admin_shipping_status' );
function lsdc_admin_shipping_status(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
    
    // Toggle Shipping Method
    $id                         = str_replace( "_status", "", esc_attr( $_REQUEST['id'] ) );
    $state                      = esc_attr( $_REQUEST['state'] );
    $lsdc_payment_status        = get_option( 'lsdc_shipping_status' );

    if( $lsdc_payment_status == '' ) $lsdc_payment_status = array();
    $lsdc_payment_status[$id]   = $state;

    update_option('lsdc_shipping_status', $lsdc_payment_status );
    echo 'action_success';

    wp_die();
}

// ========================== ADMIN - SETTINGS ========================== //

/**
 * @package LSDCommerce
 * @subpackage Settings
 * Handle Saving General Settings LSDCommerce
 *
 * @since    1.0.0
 */
add_action( 'wp_ajax_nopriv_lsdc_admin_settings_save', 'lsdc_admin_settings_save' );
add_action( 'wp_ajax_lsdc_admin_settings_save', 'lsdc_admin_settings_save' );
function lsdc_admin_settings_save(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    $data       = $_REQUEST['settings'];
    $out        = array();
    parse_str(sanitize_text_field($data), $out);

    update_option( 'lsdc_general_settings', $out );
    echo 'action_success';

    wp_die();
}

// ========================== ADMIN - GLOBAL SAVE ========================== //

/**
 * @package LSDCommerce
 * @subpackage Settings
 * Handle Saving General Settings LSDCommerce
 *
 * @since    1.0.0
 */
add_action( 'wp_ajax_nopriv_lsdc_admin_save', 'lsdc_admin_save' );
add_action( 'wp_ajax_lsdc_admin_save', 'lsdc_admin_save' );
function lsdc_admin_save(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    $data       = $_REQUEST['settings']; // Data Option
    $id         = esc_attr( $_REQUEST['id'] ); // ID Option
    $out        = array();
    parse_str(html_entity_decode($data), $out);

    $allowed_html = wp_kses_allowed_html( 'post' );
    $sanitize = array();
    foreach ($out as $key => $item) {
        if( $key == 'lsdc_tac' ){
            $item = wp_kses( $item, $allowed_html );
        }else{
            $item = sanitize_text_field($item);
        }
        $sanitize[$key] = $item; //restructure
    }
    
    $settings = get_option( $id );
    if( empty($settings) ){
        $merge = $sanitize; 
    }else{
        $merge = array_merge( $settings, $sanitize );
    }

    update_option( $id, $merge );
    echo 'action_success';
    wp_die();
}

add_action( 'wp_ajax_nopriv_lsdc_admin_option_save', 'lsdc_admin_option_save' );
add_action( 'wp_ajax_lsdc_admin_option_save', 'lsdc_admin_option_save' );
function lsdc_admin_option_save(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    $data       = esc_attr( $_REQUEST['settings'] );
    $option     = esc_attr( $_REQUEST['option'] );
    $block      = sanitize_text_field( $_REQUEST['block'] );

    $out        = array();
    parse_str(html_entity_decode($data), $out);
 
    // Sanitizing
    $sanitize = array();
    foreach ($out as $key => $item) {
        if( $key == 'sender_email' ){
            $item = sanitize_email( $item );
        }else{
            $item = sanitize_text_field($item);
        }
        $sanitize[$key] = $item; //restructure
    }

    // Saving
    $saved  = array();
    $exist = get_option( $option );
    if( $exist ){ // update
        if( $block ){
            $exist[$block] = $sanitize;
        }else{
            $exist = $sanitize;
        }
        update_option( $option, $exist );
    }else{ // saved
        if( $block ){
            $saved[$block] = $sanitize;
        }else{
            $saved = $sanitize;
        }
        update_option( $option, $saved );
    }

    echo 'action_success';

    wp_die();
}

// ORDER

add_action( 'wp_ajax_nopriv_lsdc_admin_order_action', 'lsdc_admin_order_action' );
add_action( 'wp_ajax_lsdc_admin_order_action', 'lsdc_admin_order_action' );
function lsdc_admin_order_action(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    $data       = esc_attr( $_REQUEST['data'] );
    $order_id   = abs( $_REQUEST['orderid'] );
    switch ($data) {
        case 'paid':
            lsdc_order_status( $order_id, 'paid' );
            echo 'action_success';
            break;
        case 'processed':
            lsdc_order_status( $order_id, 'processed' );
            echo 'action_success';
            break;
        case 'shipped':
            lsdc_order_status( $order_id, 'shipped' );
            echo 'action_success';
            break;
        case 'completed':
            lsdc_order_status( $order_id, 'completed' );
            echo 'action_success';
            break;
        case 'refunded':
            lsdc_order_status( $order_id, 'refunded' );
            echo 'action_success';
            break;
        case 'canceled':
            lsdc_order_status( $order_id, 'canceled' );
            echo 'action_success';
            break;
    }
    wp_die();
}

add_action( 'wp_ajax_nopriv_lsdc_admin_order_resi', 'lsdc_admin_order_resi' );
add_action( 'wp_ajax_lsdc_admin_order_resi', 'lsdc_admin_order_resi' );
function lsdc_admin_order_resi(){
    if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
    $resi       = esc_attr( $_REQUEST['resi'] );
    $order_id   = abs( $_REQUEST['orderid'] );
    update_post_meta( $order_id, 'resi', $resi );
}


?>