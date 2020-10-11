<?php 
/**
 * Dashboard Widget LSDCommerce
 * Total Today Order, Order Completed, Order Pending
 * ^ Paid Promote Space
 */
function lsdc_custom_dashboard() {
    global $wp_meta_boxes;
    wp_add_dashboard_widget('custom_help_widget', 'LSDCommerce - lsdplugins.com', 'lsdc_custom_dashboard_content');
}
add_action('wp_dashboard_setup', 'lsdc_custom_dashboard');
?>
 
<?php function lsdc_custom_dashboard_content() { ?>
    <?php 
        $order_query = new WP_Query( 
            array( 
                'post_type'     => 'lsdc-order', 
                'post_status'   => 'publish'
            )
        );

        $order_today = 0;
        $complete_today = 0;
        $pending_today = 0;
        
        $today              = date( 'Y-m-d', current_time( 'timestamp', 0 ) );
        if ( $order_query->have_posts() ) :
            while ( $order_query->have_posts() ) : $order_query->the_post(); 
                // Calculating Today Income
                $complate = get_post_meta( get_the_ID(), 'status', true );
                if( strtotime($today) == strtotime( get_the_date() ) ){
                    $order_today++;
                    
                    switch ($complate) {
                        case 'complete':
                            $complete_today++;
                            break;
                        case 'order':
                            $pending_today++;
                            break;
                        default:
                            # code...
                            break;
                    }
                    // Calculating Data Customer Today
                    // if ( ! array_key_exists( lsdc_format_phone($customer->phone), $customer_today_temp) && ! in_array( lsdc_date_format( get_the_date() ), $customer_today_temp ) ) {
                    // $customer_today_temp[lsdc_format_phone($customer->phone)] = $customer->email;
                    // }
                }
            endwhile; wp_reset_postdata();
        endif;
    ?>
    <style>
        /* Clear floats after the columns */
        .lsdc-dashboard.row:after {
        content: "";
        display: table;
        clear: both;
        }

        .lsdc-dashboard.row p{
            margin:0;
        }

        .lsdc-dashboard.row h4{
            font-size: 1.3rem !important;
            font-weight: 600 !important;
        }

        .column {
        float: left;
        width: 25%;
        }


        @media screen and (max-width: 600px) {
        .column {
            width: 100%;
        }
        }
    </style>

    <div class="lsdc-dashboard row">
        <div class="column">
            <p><?php _e( 'Pesanan Hari ini', 'lsdcommerce' ); ?></p>
            <h4 style="color:maroon;"><?php echo abs( $order_today ); ?></h4>     
        </div>                    
    
        <div class="column">
            <p><?php _e( 'Pesanan Selesai', 'lsdcommerce' ); ?></p>
            <h4 style="color:maroon;"><?php echo abs( $complete_today ); ?></h4>           
        </div>                   
    
        <div class="column">
            <p><?php _e( 'Pesanan Pending', 'lsdcommerce' ); ?></p>
            <h4 style="color:maroon;"><?php echo abs( $pending_today ); ?></h4>    
        </div>              
    
        <div class="column">
  
        </div>  

        <!-- Paid Promote -->
    </div>
<?php } ?>