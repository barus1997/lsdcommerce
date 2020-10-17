<?php 
/**
 * Column Order Information Header
 */
function lsdc_admin_order_columns_tab( $columns ) {
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
add_filter( 'manage_lsdc-order_posts_columns', 'lsdc_admin_order_columns_tab' );


function lsdc_admin_order_columns_content( $column, $order_id ) {
    update_option('lsdc_order_unread_counter', 0);

    // Image column
    if ( 'order' === $column ) {
        echo '<strong>INV#' . abs( get_post_meta( get_the_ID(), 'order_id', true )) . '</strong> ';
        $products = (array) json_decode( get_post_meta( $order_id, 'products', true ) );
        echo ' - ' . count( $products ) . __( ' Produk', 'lsdcommerce' );
    }

    // Date Order
    if ( 'date_order' === $column ) {
        if( lsdc_date_diff(get_the_date( 'Y-m-d H:i:s', $order_id ), lsdc_date_now() ) > 1 ){
             echo get_the_date( 'j F Y', $order_id ) . ' @' . get_the_time('H:i:s', $order_id );
        }else{
            echo human_time_diff(  strtotime( get_the_date( 'Y-m-d H:i:s', $order_id ) )  , current_time( 'timestamp' ) ) . __( ' ago', 'lsdcommerce' );
            // echo ' @' . get_the_time('H:i:s', $order_id );
        }
    }

    // Shipping
    if ( 'shipping' === $column ) {
        $shippings = (array) json_decode( get_post_meta( $order_id, 'shipping', true ) );
       

        if( isset( $shippings['physical'] ) ){
            $extras = (array) json_decode( get_post_meta( $order_id, 'extras', true ) );
            echo $extras['shipping']->bold;
            echo '<br><strong>' .  $extras['shipping']->value . '</strong>';
        }else if( isset( $shippings['digital'] ) ){
            $service = $shippings['digital']->service;
        }
        
        foreach ($shippings as $key => $item) {
            if( empty($key) ){
                echo '_';
            }else{
                if( $service == 'lsdcommerce_shipping_email' ){
                    echo ucfirst( $key . ' : <br><strong>Email</strong>' );
                }else{
                    echo ucfirst( $service );
                }
            }
            
        }
    }

    // Customer
    if ( 'customer' === $column ) {
        $customer_id = abs( get_post_meta( $order_id, 'customer_id', true ) );
        $customers = (array) json_decode( get_post_meta( $order_id, 'customer', true ) );

        if( $customer_id ){
            echo lsdc_get_user_name( $customer_id ) . ' - ' . lsdc_get_user_phone( $customer_id ) . '<br><strong>' . lsdc_get_user_email( $customer_id ) . '</strong>' ;
        }else{
            if( ! empty( $customers['name'] ) ){
                echo $customers['name'] . ' - ' . $customers['phone'] . '<br><strong>' . $customers['email'] . '</strong>' ;
            }else{
                echo '_';
            }
        }

    }

    // Total Pembelian
    if ( 'total' === $column ) {
        echo lsdc_currency_format( true, get_post_meta( $order_id, 'total', true ) );
    }

    // Status
    if ( 'status' === $column ) {
        echo '<span class="lsdc-status lsdc-'. strtolower( get_post_meta( $order_id, 'status', true )) .'">' . lsdc_order_status_translate( $order_id ) . '</span>';
    }

    // Type Column
    if ( 'action' === $column ) {
       do_action( 'lsdcommerce_admin_order_action', $order_id );
       switch ( get_post_meta( $order_id, 'status', true ) ) {
           case 'new':
                ?>
                <button class="button lsdc-action-button" title="Sudah Dibayar" data-action="paid" data-id="<?php echo $order_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg"  style="margin-top:3px;padding:0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                </button><?php
            break;
            case 'paid':
                ?>
                <button class="button lsdc-action-button" title="Diproses" data-action="processed" data-id="<?php echo $order_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" style="margin-top:3px;padding:0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                </button>
                <?php
            break;
            case 'processed':
                ?>
                <div class="inputresi">
                    <input type="text" class="resi" placeholder="Kode Resi">
                    <button class="button lsdc-action-button" title="Dikirim" data-action="shipped" data-id="<?php echo $order_id; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" style="margin-top:3px;padding:0"  width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-truck"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    </button>
                </div>

                <style>
                td.action.column-action{
                    position: relative;
                }
                .inputresi {
                    position: absolute;
                    top: 0;
                    width: 260px;
                    right: 0;
                    display: inline-block;
                    padding: 10px;
                }

                .inputresi input{
                    width: 200px;
                    padding: 3px 10px;
                }
                </style>
                <?php
            break;
            case 'shipped':
                ?>
                <button class="button lsdc-action-button" title="Complete Order" data-action="completed" data-id="<?php echo $order_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" style="margin-top:3px;padding:0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>

                <?php
            break;
            case 'completed':
                ?>
                <!-- Refund -->
                <button class="button lsdc-action-button" title="Refund" data-action="refunded" data-id="<?php echo $order_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" style="margin-top:3px;padding:0"  width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-ccw"><polyline points="1 4 1 10 7 10"></polyline><polyline points="23 20 23 14 17 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg>
                </button><?php
            break;
            default:
               # code...
               break;
       }
    }
}
add_action( 'manage_lsdc-order_posts_custom_column', 'lsdc_admin_order_columns_content', 10, 2 );



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