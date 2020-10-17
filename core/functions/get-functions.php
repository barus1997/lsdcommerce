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
    return sanitize_email( $user->user_email );
}

?>