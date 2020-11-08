<?php
/**
 * Class and Function List:
 * Function list:
 * - lsdc_admin_store_save()
 * - lsdc_admin_appearance_save()
 * - lsdc_admin_payment_status()
 * - lsdc_admin_payment_option()
 * - lsdc_admin_notification_status()
 * - lsdc_admin_shipping_status()
 * - lsdc_admin_settings_save()
 * - lsdc_admin_save()
 * - lsdc_admin_option_save()
 * - lsdc_license_register()
 * Classes list:
 */

/**
 * Handle Saving Store Data in WordPress Option
 *
 * @package Admin
 * @subpackage Store
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_store_save', 'lsdc_admin_store_save');
add_action('wp_ajax_lsdc_admin_store_save', 'lsdc_admin_store_save');
function lsdc_admin_store_save() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');
    // Improved Security
    $store = $_REQUEST['store'];
    $temp = array();
    $temp['country'] = sanitize_text_field($store['country']);
    $temp['state'] = sanitize_text_field($store['state']);
    $temp['city'] = sanitize_text_field($store['city']);
    $temp['address'] = sanitize_text_field($store['address']);
    $temp['postalcode'] = sanitize_text_field($store['postalcode']);
    $temp['currency'] = sanitize_text_field($store['currency']);

    update_option('lsdcommerce_store_settings', $temp);
    echo 'action_success'; // Force Save
    wp_die();
}

/**
 *  Handle Saving Apperance Data in WordPress Option
 *
 * @package Admin
 * @subpackage Appearance
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_appearance_save', 'lsdc_admin_appearance_save');
add_action('wp_ajax_lsdc_admin_appearance_save', 'lsdc_admin_appearance_save');
function lsdc_admin_appearance_save() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    $data = $_REQUEST['appearance'];
    $output = array();
    parse_str(html_entity_decode($data) , $output);

    update_option('lsdc_appearance_settings', $output);
    echo 'action_success';

    wp_die();
}

/**
 * Handle Switch Option Payment
 *
 * @package Admin
 * @subpackage Payment
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_payment_status', 'lsdc_admin_payment_status');
add_action('wp_ajax_lsdc_admin_payment_status', 'lsdc_admin_payment_status');
function lsdc_admin_payment_status() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $id = str_replace("_status", "", esc_attr($_REQUEST['id']));
    $state = esc_attr($_REQUEST['state']);
    $lsdc_payment_status = get_option('lsdc_payment_status');
    if ($lsdc_payment_status == '') $lsdc_payment_status = array();
    $lsdc_payment_status[$id] = $state;

    update_option('lsdc_payment_status', $lsdc_payment_status);
    echo 'action_success';

    wp_die();
}

/**
 * Saving Data Bank based on User Settings
 *
 * @package Admin
 * @subpackage Payment
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_payment_option', 'lsdc_admin_payment_option');
add_action('wp_ajax_lsdc_admin_payment_option', 'lsdc_admin_payment_option');
function lsdc_admin_payment_option() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $origin_id = sanitize_title(strtolower(preg_replace("/[^a-z]+/i", "", $_REQUEST['id']))); // Sanitize Title
    $method = 'lsdcommerce_payment_settings'; // Set Method Name to Varibale
    $data = $_REQUEST['serialize']; // string form
    $output = array();
    parse_str($data, $output); // string form  to array
    $temp = array();

    // Load Output
    foreach ($output as $key => $item) {
        if ($key == 'logo') {
            $temp[$key] = esc_url($item); // Sanitize Logo
            
        }
        else {
            $temp[$key] = sanitize_text_field(stripslashes_deep($item)); // Sanitize Text Field
            
        }

        if ($key == 'alias') {
            $alias = lsdc_clean_id($item); // Sanitize ID
        }
    }

    // Storing to Settings
    $payment_method = get_option($method);

    if (isset($payment_method[$origin_id])) {
        $merge = array_merge($payment_method[$origin_id], $temp); // merge array
        
    }
    else if (isset($payment_method[$alias])) {
        $merge = array_merge($payment_method[$alias], $temp); // merge array for custom banks
        
    }
    else {
        $merge = $temp; // If Empty Setting, Create Array
        
    }

    // If Has Custom Bank
    if ($alias) {
        $merge['alias'] = $origin_id;
        $payment_method[$origin_id]['alias'] = $alias; // set custom by alias || pointing to alias data
        $payment_method[$alias] = $merge; // set alias data independent
        
    }
    else {
        $payment_method[$origin_id] = $merge; // default
        
    }

    update_option($method, $payment_method); // saved
    echo 'action_success';

    wp_die();
}

/**
 * Handle Enable On Off Notification Method
 *
 * @package Admin
 * @subpackage Notification
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_notification_status', 'lsdc_admin_notification_status');
add_action('wp_ajax_lsdc_admin_notification_status', 'lsdc_admin_notification_status');
function lsdc_admin_notification_status() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    // Toggle Notification Method
    $id = str_replace("_status", "", esc_attr($_REQUEST['id']));
    $state = esc_attr($_REQUEST['state']);
    $lsdc_payment_status = get_option('lsdc_notification_status');

    if ($lsdc_payment_status == '') $lsdc_payment_status = array();
    $lsdc_payment_status[$id] = $state;

    update_option('lsdc_notification_status', $lsdc_payment_status);
    echo 'action_success';

    wp_die();
}

/**
 * Handle Enable On Off Shipping Method
 *
 * @package LSDCommerce
 * @subpackage Shipping
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_shipping_status', 'lsdc_admin_shipping_status');
add_action('wp_ajax_lsdc_admin_shipping_status', 'lsdc_admin_shipping_status');
function lsdc_admin_shipping_status() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    // Toggle Shipping Method
    $id = str_replace("_status", "", esc_attr($_REQUEST['id']));
    $state = esc_attr($_REQUEST['state']);
    $lsdc_payment_status = get_option('lsdc_shipping_status');

    if ($lsdc_payment_status == '') $lsdc_payment_status = array();
    $lsdc_payment_status[$id] = $state;

    update_option('lsdc_shipping_status', $lsdc_payment_status);
    echo 'action_success';

    wp_die();
}

// ========================== ADMIN - SETTINGS ========================== //

/**
 * Handle Saving General Settings LSDCommerce
 *
 * @package LSDCommerce
 * @subpackage Settings
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_settings_save', 'lsdc_admin_settings_save');
add_action('wp_ajax_lsdc_admin_settings_save', 'lsdc_admin_settings_save');
function lsdc_admin_settings_save() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    $data = $_REQUEST['settings'];
    $temp = array();
    parse_str(sanitize_text_field($data) , $temp);

    update_option('lsdc_general_settings', $temp);
    echo 'action_success';

    wp_die();
}

/**
 * Handle Saving All Settings LSDCommerce based on ID
 * Used by RajaOngkir
 *
 * @package LSDCommerce
 * @subpackage Settings
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_save', 'lsdc_admin_save');
add_action('wp_ajax_lsdc_admin_save', 'lsdc_admin_save');
function lsdc_admin_save() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    $data = $_REQUEST['settings']; // Data Option
    $id = esc_attr($_REQUEST['id']); // ID Option
    $temp = array();
    parse_str(html_entity_decode($data) , $temp);

    $allowed_html = wp_kses_allowed_html('post');
    $sanitize = array();
    foreach ($temp as $key => $item) {
        if ($key == 'lsdc_tac') {
            $item = wp_kses($item, $allowed_html); // Text Editor Sanitize
            
        }
        else {
            $item = sanitize_text_field($item);
        }
        $sanitize[$key] = $item; //restructure
        
    }

    $settings = get_option($id);
    if (empty($settings)) {
        $merge = $sanitize;
    }
    else {
        $merge = array_merge($settings, $sanitize);
    }

    update_option($id, $merge);
    echo 'action_success';
    wp_die();
}

/**
 * Handle Saving Admin Option LSDCommerce based on ID
 * Used by Digital and Physical
 *
 * @package LSDCommerce
 * @subpackage Settings
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_admin_option_save', 'lsdc_admin_option_save');
add_action('wp_ajax_lsdc_admin_option_save', 'lsdc_admin_option_save');
function lsdc_admin_option_save() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
    $data = esc_attr($_REQUEST['settings']);
    $option = esc_attr($_REQUEST['option']);
    $block = sanitize_text_field($_REQUEST['block']);

    $temp = array();
    parse_str(html_entity_decode($data) , $temp);

    // Sanitizing
    $sanitize = array();
    foreach ($temp as $key => $item) {
        if ($key == 'sender_email') {
            $item = sanitize_email($item);
        }
        else {
            $item = sanitize_text_field($item);
        }
        $sanitize[$key] = $item; //restructure
        
    }

    // Saving
    $saved = array();
    $exist = get_option($option);
    if ($exist) { // update
        if ($block) {
            $exist[$block] = $sanitize;
        }
        else {
            $exist = $sanitize;
        }
        update_option($option, $exist);
    }
    else { // saved
        if ($block) {
            $saved[$block] = $sanitize;
        }
        else {
            $saved = $sanitize;
        }
        update_option($option, $saved);
    }

    echo 'action_success';

    wp_die();
}

/**
 * Register and Unregister
 * Support Addon and Parent Plugin
 *
 * @package Admin
 * @subpackage Licenses
 * @since    1.0.0
 */
add_action('wp_ajax_nopriv_lsdc_license_register', 'lsdc_license_register');
add_action('wp_ajax_lsdc_license_register', 'lsdc_license_register');
function lsdc_license_register() {
    if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');

    $type = sanitize_text_field($_POST['type']);
    $key = sanitize_text_field($_POST['key']);
    $id = sanitize_text_field($_POST['id']); // used for save the license in local
    $domain = parse_url(get_site_url()) ['host'];

    if ($key != null && $type == 'register') { //register
        $remote = lsdc_remote_activate($key);

        if ($remote['code'] == 200) {
            // Auto Setup Plugin
            do_action('lsdc_hook_autosetup');

            $licenses = empty(get_option('lsdcommerce_licenses')) ? array() : get_option('lsdcommerce_licenses');
            $licenses[$id] = array( // $id for save the
                'expired' => $remote['expired'],
                'status' => $remote['status'],
                'registered' => $domain,
                'key' => $key,
            );
            update_option('lsdcommerce_licenses', $licenses);

        }
        echo json_encode($remote);
    }
    else { // unregister
        $remote = lsdc_remote_deactivate(lsdc_license_get('key', $id));
        if ($remote['code'] == 200 || $remote['code'] == 501 || $remote['code'] == 500 || $remote['code'] == 502) {
            $licenses = get_option('lsdcommerce_licenses');
            unset($licenses[$id]);
            update_option('lsdcommerce_licenses', $licenses);
        }
        echo json_encode($remote);
    }

    wp_die();
}
?>