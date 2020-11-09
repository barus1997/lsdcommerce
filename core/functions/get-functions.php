<?php 

/**
 * Get User Name by User ID
 * Block : User
 * @param int $user_id
 */
function lsdc_get_user_name( $user_id = false ){
    $user_id = empty( $user_id ) ?  get_current_user_id() : $user_id;
    return ucfirst( esc_attr( get_user_meta( $user_id, 'first_name', true ) ) ) . ' ' . esc_attr( get_user_meta( $user_id, 'last_name', true ) );
}

/**
 * Get User Phone by User ID
 * Block : User
 * @param int $user_id
 */
function lsdc_get_user_phone( $user_id = false ){
    $user_id = empty( $user_id ) ?  get_current_user_id() : $user_id;
    return esc_attr( get_user_meta( $user_id, 'user_phone', true ) );
}

/**
 * Get User Email by User ID
 * Block : User
 * @param int $user_id
 */
function lsdc_get_user_email( $user_id = false ){
    $user_id = empty( $user_id ) ?  get_current_user_id() : $user_id;
    $user = get_user_by( 'id', $user_id );
    if(  $user  ){
        return sanitize_email( $user->user_email );
    }else{
        return false;
    }
    
}

/**
 * Get : IP Address
 */
function lsdc_get_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Getting Store Settings
 * 
 * @package Core
 * @subpackage Get
 * @since 1.0.0
 * usage :: lsdc_get_store( 'country' );
 */
function lsdc_get_store( $key ){
    $store_settings = get_option( 'lsdcommerce_store_settings' ); 
    switch ($key) {
        case 'country':
            return isset( $store_settings['lsdc_store_country'] ) ? esc_attr( $store_settings['lsdc_store_country'] ) : 'ID';
            break;
        case 'state':
            return isset( $store_settings['lsdc_store_state'] ) ? esc_attr( $store_settings['lsdc_store_state'] ) : 3;
            break;
        case 'city':
            return isset( $store_settings['lsdc_store_city'] ) ? esc_attr( $store_settings['lsdc_store_city'] ) : 455;
            break;
        default:
            return $store_settings; //Return Settings
            break;
    }
}


/**
 * Getting Payment Settings
 * 
 * @package Core
 * @subpackage Get
 * @since 1.0.0
 * usage :: lsdc_get_store( 'country' );
 */
function lsdc_get_payment( $id, $type){
    $payment_method = get_option( 'lsdcommerce_payment_settings' );
    $pointer = isset( $payment_method[$id]['alias'] ) ? $payment_method[$id]['alias'] : $id; // Check if Alias Custom Exist or Not Using ID Default
    $method = $payment_method[$pointer]; // select data by alias if avaialbale

    switch ($type) {
        case 'groupname':
            return isset( $method['group_name'] ) && $method['group_name'] != '' ? esc_attr( $method['group_name'] . ' - ' ) : '';
            break;
        case 'name':
            return esc_attr( $method['name'] );
            break;
        case 'logo':
            return esc_attr( $method['logo'] );
            break;
        case 'swiftcode':
            return esc_attr( $method['swift_code'] );
            break;
        case 'account_code':
            return isset( $method['bank_code'] ) && $method['bank_code'] != '' ? '(' . esc_attr( $method['bank_code'] ) .') ' : '';
            break;
        case 'account_number':
            return esc_attr( $method['account_number'] );
            break;
        case 'account_holder':
            return esc_attr( $method['account_holder'] );
            break;
        case 'instruction':
            return esc_attr( $method['instruction'] );
            break;
    }
}

function lsdc_get_login_url(){
    return wp_login_url();
}
?>