<?php 
/**
 * Column Information Order
 */
add_filter( 'manage_lsdc-order_posts_columns', 'lsdc_column_header' );
function lsdc_column_header( $columns ) {
    $columns = array(
        'cb'        => $columns['cb'],
        'order'     => __( 'Pesanan' , 'lsdcommerce' ),
        'date_order'=> __( 'Tanggal', 'lsdcommerce' ),
        'customer'  => __( 'Pembeli', 'lsdcommerce' ),
        'shipping'  => __( 'Pengiriman', 'lsdcommerce' ),
        'total'     => __( 'Total' ),
        'status'    => __( 'Status' ),
        'action'    => __( 'Action' ),
    );
    return $columns;
}

add_action( 'manage_lsdc-order_posts_custom_column', 'lsdc_column_content', 10, 2);
function lsdc_column_content( $column, $order_id ) {

    update_option('lsdc_order_counter', 0);
    // Image column
    if ( 'order' === $column ) {
        echo '<strong>INV#' . abs( get_post_meta( get_the_ID(), 'order_id', true )) . '</strong>';
    }

    if ( 'date_order' === $column ) {
        if( lsdc_date_diff(get_the_date( 'Y-m-d H:i:s', $order_id ), lsdc_date_now() ) > 1 ){
             echo get_the_date( 'j F Y', $order_id ) . ' @' . get_the_time('H:i:s', $order_id );
        }else{
            echo human_time_diff(  strtotime( get_the_date( 'Y-m-d H:i:s', $order_id ) )  , current_time( 'timestamp' ) ) . __( ' ago', 'lsdcommerce' );
            // echo ' @' . get_the_time('H:i:s', $order_id );
        }
    }

    if ( 'shipping' === $column ) {
        $shippings = (array) json_decode( get_post_meta( $order_id, 'shipping', true ) );

        if( isset( $shippings['physical'] ) ){
            $service = 'JNE REG';
        }else if( isset( $shippings['digital'] ) ){
            $service = 'Email';
        }
        
        foreach ($shippings as $key => $item) {
            if( empty($key) ){
                echo '_';
            }else{
                echo ucfirst( $key . ' : <br><strong>' . $service . '</strong>' );
            }
            
        }
    }

    if ( 'customer' === $column ) {
        $customer_id = abs( get_post_meta( $order_id, 'customer_id', true ) );
        $customers = (array) json_decode( get_post_meta( $order_id, 'customer', true ) );

        if( $customer_id ){
            echo lsdc_user_getname( $customer_id ) . ' - ' . lsdc_user_getphone( $customer_id ) . '<br><strong>' . lsdc_user_getemail( $customer_id ) . '</strong>' ;
        }else{
            if( ! empty( $customers['name'] ) ){
                echo $customers['name'] . ' - ' . $customers['phone'] . '<br><strong>' . $customers['email'] . '</strong>' ;
            }else{
                echo '_';
            }
        }

    }

    if ( 'total' === $column ) {
        echo lsdc_currency_format( true, get_post_meta( $order_id, 'total', true ) );
    }


    if ( 'status' === $column ) {
        echo '<span class="lsdc-status lsdc-'. strtolower( get_post_meta( $order_id, 'status', true )) .'">' . lsdc_order_status_translate( $order_id ) . '</span>';
    }

    // Type Column
    if ( 'action' === $column ) {
       ?>
       <?php if( get_post_meta( $order_id, 'status', true ) != 'paid' ) : ?>
       <button class="button lsdc-action-button" title="Sudah Dibayar" data-action="paid" data-id="<?php echo $order_id; ?>"><svg xmlns="http://www.w3.org/2000/svg"  style="margin-top:3px;padding:0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg></button>
       <?php endif; ?>
       <button class="button lsdc-action-button" title="Shipped" data-action="shipped" data-id="<?php echo $order_id; ?>"><svg xmlns="http://www.w3.org/2000/svg" style="margin-top:3px;padding:0"  width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-truck"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg></button>
       <button class="button lsdc-action-button" title="Complete Order" data-action="completed" data-id="<?php echo $order_id; ?>"><svg xmlns="http://www.w3.org/2000/svg" style="margin-top:3px;padding:0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
       <?php
    }
}

add_filter('post_row_actions','my_action_row', 10, 2);

function my_action_row($actions, $post){
    //check for your post type
    if ($post->post_type =="lsdc-order"){
        /*do you stuff here
        you can unset to remove actions
        and to add actions ex:
        // 
        */
        // unset( $actions['edit'] );
        unset( $actions['inline hide-if-no-js'] );
        // $actions['view'] = '<a href="http://www.google.com/?q='.get_permalink($post->ID).'">View</a>';
    }
    return $actions;
}

/**
 * Column Product Information
 */
add_filter( 'manage_lsdc-product_posts_columns', 'lsdc_product_header' );
function lsdc_product_header( $columns ) {
    $columns = array(
        'cb'        => $columns['cb'],
        'image'     => __( 'Image' ),
        'title'     => __( 'Name' ),
        'price'     => __( 'Price', 'lsdcommerce' ),
        'date'      => $columns['date'],
        );
    return $columns;
}

add_action( 'manage_lsdc-product_posts_custom_column', 'lsdc_product_columns', 10, 2);
function lsdc_product_columns( $column, $product_id ) {
    if ( 'image' === $column ) {
        echo get_the_post_thumbnail( $product_id, array(39, 39) );
    }

    if ( 'price' === $column ) {
        if( lsdc_price_discount( $product_id ) ){
            echo '<span style="text-decoration: line-through">' . lsdc_currency_format( true, lsdc_price_normal( $product_id ) ) .  '</span><br>';
        }
        echo lsdc_currency_format( true, lsdc_product_price( $product_id ) );
    }

}
?>