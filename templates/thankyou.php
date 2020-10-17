<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_filter( 'document_title_parts', function( $title ) use ( $hash ){
    $title['title'] = __( "Terimakasih telah berbelanja di toko kami", 'lsdcommerce' );
    return $title;
});

if( ! wp_doing_ajax() ){
    get_header();

    $order_id = lsdc_order_ID( $hash );

    if( $order_id ) :
        $products   = json_decode( get_post_meta( $order_id, 'products', true ) );
        $extras     = json_decode( get_post_meta( $order_id, 'extras', true ) );
        $total      = json_decode( get_post_meta( $order_id, 'total', true ) );
        $shipping   = json_decode( get_post_meta( $order_id, 'shipping', true ) );
        
        $payment_id = get_post_meta( $order_id, 'payment_id', true );
        $order_ip   = get_post_meta( $order_id, 'ip', true );
        $customer_id = abs( get_post_meta( $order_id, 'customer_id', true ) );
    endif;
}
?>

<?php if( $order_id ) : ?>
<main id="lsdcommerce-thankyou" class="page-content max480 lsdcommerce">
     <div class="card">
        <div class="card-body">
            <div class="page-thankyou">
            
                <div id="checkout-alert" class="lsdp-alert lsdc-info lsdp-mt-10">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <p><?php _e( 'Pesanan anda telah kami terima, terimakasih telah memesan' , 'lsdcommerce' ); ?></p>
                </div>

                <div class="section-payment-instruction">
                    <div class="icon">
                        <img src="<?php echo LSDC_URL; ?>/assets/img/happy.png" alt="">
                    </div>
                    <?php if( $total != 0 ) : ?>
                        <p><?php _e( 'Tolong selesaikan pembayaran anda dan ikuti instruksi berikut ini' , 'lsdcommerce' ); ?></p>
                        <p><small><?php _e( 'Silahkan transfer sesuai dengan total yang tertera' , 'lsdcommerce' ); ?></small></p>

                        <h5 class="copy-nominal lsdp-mb-5">
                            <?php echo str_replace( substr($total, -3), '', lsdc_currency_format( true, $total ) ); ?><span class="text-underline"><?php  echo substr($total, -3); ?></span>
                        </h5>

                        <div id="copy-total" class="hidden"><?php echo lsdc_currency_clear( $total ); ?></div>
                        <button class="lsdp-btn lsdc-btn btn-primary copy-btn" onclick="lsdcommerce_copy('#copy-total', this)"><?php _e( 'Salin', 'lsdcommerce' ); ?></button>
                    <!-- Free Product -->
                    <?php else: ?>
                        <p><?php _e( "Produk ini gratis, anda tidak perlu membayar apapun" , 'lsdcommerce' ); ?></p>
                        <h5 class="font-weight-medium mb-0"><?php _e( "Gratis" , 'lsdcommerce' ); ?></h5>
                    <?php endif; ?>
                </div>

                <?php if( $total != 0 ) : ?>
                <div class="section-bank">
                <?php 
                    $payment_data = array(
                        'payment_name'      => lsdc_get_payment( $payment_id, 'groupname' ) . lsdc_get_payment( $payment_id, 'name' ),
                        'payment_image'     => lsdc_get_payment( $payment_id, 'logo' ),
                        'code_label'        => lsdc_get_payment( $payment_id, 'swiftcode' ) != null ? __( 'BIC/SWIFT : ' , 'lsdcommerce' ) : null,
                        'code_value'        => lsdc_get_payment( $payment_id, 'swiftcode' ),
                        'account_label'     => lsdc_get_payment( $payment_id, 'account_number' ) != null ? __( 'Rekening : ', 'lsdcommerce') : null,
                        'account_code'      => lsdc_get_payment( $payment_id, 'account_code' ) != null ? lsdc_get_payment( $payment_id, 'account_code' ) : null,
                        'account_number'    => lsdc_get_payment( $payment_id, 'account_number' ),
                        'holder_label'      => lsdc_get_payment( $payment_id, 'account_holder' ) != null ?__( 'Atas Nama : ', 'lsdcommerce') : null,
                        'holder_value'      => lsdc_get_payment( $payment_id, 'account_holder' ),
                        'instruction_text'  => lsdc_get_payment( $payment_id, 'instruction' )
                    );
                ?>
                    <h6 class="text-primary font-weight-medium text-center"><?php echo esc_attr(  $payment_data['payment_name'] ); ?></h6>
                    <hr class="half">

                    <!-- Instruction Text -->
                    <p class="grey text-center lsdp-mb-10"><?php echo esc_attr( $payment_data['instruction_text'] ); ?></p>

                    <table class="table table-banks table-borderless ">
                        <tbody>
                            <tr>
                                <td>
                                    <img src="<?php echo esc_url( $payment_data['payment_image'] ); ?>" alt="<?php echo esc_attr(  $payment_data['payment_name'] ); ?>" height="25">
                                </td>
                                <td>
                                    <span class="bank-code"><?php echo esc_attr( $payment_data['code_label'] ); ?> <?php echo esc_attr( $payment_data['code_value'] ); ?></span>
                                    <h6 class="bank-account"><?php echo esc_attr( $payment_data['account_label'] ); ?> <?php echo esc_attr( $payment_data['account_code'] ); ?> <span id="copy-account" class="font-medium"><?php echo esc_attr( $payment_data['account_number'] ); ?></span></h6>
                                    <p class="grey"><?php echo esc_attr( $payment_data['holder_label'] ); ?> <?php echo esc_attr( $payment_data['holder_value'] ); ?></p>
                                </td>
                                <td class="text-right action">
                                    <button class="lsdp-btn lsdc-btn btn-primary copy-btn" onclick="lsdcommerce_copy('#copy-account', this)"><?php _e( 'Salin', 'lsdcommerce' ); ?></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
                <?php endif; ?>

                    <div class="section-transaction">
                        <h6 class="text-primary font-weight-medium text-center"><?php _e( "Detail Transaksi", "lsdcommerce"); ?></h6>
                        <hr class="half">
                        <table class="table table-transaction table-borderless">
                            <tbody>
                                <?php $total = 0; $subtotal = 0; foreach ( $products as $key => $item) : ?>
                                    <tr id="<?php echo $item->id; ?>">
                                        <td class="product-thumbnail">
                                            <div class="img-product">
                                                <img src="<?php echo esc_url( $item->thumbnail ); ?>" alt="<?php echo esc_attr( $item->title ); ?>">
                                            </div>
                                        </td>
                                        <td class="product-title">
                                            <p><a href="<?php echo get_permalink( $item->id ); ?>" alt="<?php echo esc_attr( $item->title ); ?>">
                                            <?php echo esc_attr( $item->title ); ?>
                                            <?php echo $item->variation != null ? ' - ' . lsdc_variation_label( $item->id, $item->variation ) : null; ?>
                                            </a>
                                                <small class="d-block"> <?php echo abs( $item->qty ); ?> x <?php echo lsdc_currency_format( true, $item->price_unit ); ?></small>
                                                <?php do_action( 'lsdc_variation_display'); ?>
                                            </p>
                                        </td>
                                        <td class="product-item text-right">
                                            <?php echo lsdc_currency_format( true, abs( $item->qty  ) * intval( $item->price_unit  ) ); ?>
                                        </td>
                                    </tr>
                                <?php $subtotal += abs( $item->qty  ) * intval( $item->price_unit  ); endforeach; ?>

                                <tr>
                                    <td colspan="2"><?php _e( 'Sub Total', 'lsdcommerce' ); ?></td>
                                    <td class="text-right"><?php echo lsdc_currency_format( true, $subtotal ); ?></td>
                                </tr>

                                <!-- Display Extra :: Shipping, Unique Code -->
                                <?php $extra = 0; if( $extras ) : foreach ( $extras as $key => $item) : ?>
                                <?php if( $item->label ) : ?>
                                    <tr>
                                        <td colspan="2">
                                            <?php echo esc_attr( $item->label ); ?>
                                            <?php if( isset( $item->bold ) ) : ?>
                                            <small class="d-block  text-primary"> 
                                                <?php echo esc_attr( $item->bold ); // For Shipping ?>
                                            </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <?php echo $item->sign == '-' ? esc_attr( $item->sign ) : ''; ?><?php echo $item->value; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php $extra += intval( $item->cost ); endforeach; endif; ?>
    
                                <!-- Total -->
                                <tr>
                                    <?php $total = $subtotal + $extra; 
                                    ?>
                                    <td colspan="2" class="font-weight-medium"><?php _e( 'Total', 'lsdcommerce' ); ?></td>
                                    <td class="text-right font-weight-medium"><?php echo lsdc_currency_format( true, abs( $total ) ); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if( lsdc_get_ip() == $order_ip ) : ?>
                        <div class="section-customer">
                            <h6 class="text-primary font-weight-medium text-center"><?php _e('Detail Pembeli', 'lsdcommerce'); ?></h6>
                            <hr class="half">
                            <table class="table table-transaction table-borderless">
                                <tbody>
                                    <?php 
                                        $store_settings         = get_option( 'lsdc_store_settings' ); 
                                        $country_selected       = isset( $store_settings['lsdc_store_country'] ) ? esc_attr( $store_settings['lsdc_store_country'] ) : 'ID';
                                        $currency_selected      = isset( $store_settings['lsdc_store_currency'] ) ? esc_attr( $store_settings['lsdc_store_currency'] ) : 'IDR';
                                
                                        if( $country_selected ){
                                            $states = json_decode( file_get_contents( LSDC_PATH . 'core/cache/' . $country_selected . '-states.json') );
                                            $cities = json_decode( file_get_contents( LSDC_PATH . 'core/cache/' . $country_selected . '-cities.json') );
                                        }else{
                                            $states = json_decode( file_get_contents( LSDC_PATH . 'core/cache/ID-states.json') );
                                            $cities = json_decode( file_get_contents( LSDC_PATH . 'core/cache/ID-cities.json') );
                                        }
                                    ?>
                        
                                    <tr>
                                        <td colspan="2"  class="font-weight-medium"><?php _e('Name', 'lsdcommerce'); ?></td>
                                        <td><?php echo lsdc_get_user_name(  $customer_id  ); ?></td>
                                    </tr>
                                    <?php if( ! empty( lsdc_get_user_phone(  $customer_id  ) ) ) : ?>
                                    <tr>
                                        <td colspan="2"  class="font-weight-medium"><?php _e('Phone', 'lsdcommerce'); ?></td>
                                        <td><?php echo lsdc_get_user_phone(  $customer_id  ); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if( ! empty( lsdc_get_user_email(  $customer_id  ) ) ) : ?>
                                        <tr>
                                            <td colspan="2"  class="font-weight-medium"><?php _e('Email', 'lsdcommerce'); ?></td>
                                            <td><?php echo lsdc_get_user_email(  $customer_id  ); ?></td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php if( isset($shipping->physical ) ) : ?>
                                        <tr>
                                            <td colspan="2"  class="font-weight-medium"><?php _e('Address', 'lsdcommerce'); ?></td>
                                            <td><?php echo esc_attr( $shipping->physical->address ); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"  class="font-weight-medium"><?php _e('City', 'lsdcommerce'); ?></td>
                                            <td><?php echo esc_attr( $cities[$shipping->physical->city - 1 ]->type . ' ' . $cities[$shipping->physical->city - 1]->city_name ); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"  class="font-weight-medium"><?php _e('State', 'lsdcommerce'); ?></td>
                                            <td><?php echo esc_attr( $states[$shipping->physical->state - 1]->province ); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"  class="font-weight-medium"><?php _e('Country', 'lsdcommerce'); ?></td>
                                            <td>Indonesia</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <br>
                            <small><?php _e('Dapat Dilihat oleh','lsdcommerce') ?> : <?php echo $order_ip; ?></small>
                        </div>
                    <?php endif; ?> 

                </div>
            </div>
        </div>

        <?php do_action( 'lsdcommerce_powered_hook' ); ?>
        
    </main> <!-- main -->
<?php else: ?>
<div class="lsdp-alert danger">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
    <p><?php _e( 'Order does not exist', 'lsdcommerce' ); ?></p>
</div>
<?php endif; ?>
<br><br>          
     
<?php 
if( ! wp_doing_ajax() ){
    get_footer();
} 
?>