<?php 
/** 
 * Meta Box LSDCommerce
 *
 * Register Metabox Product Data
 * 
 * @package LSDCommerce
 * @subpackage Product
 * @since   1.0.0
 * 
 * Hook ::
 * Register Extend Tab :: lsdc_product_tab_header
 * Register Extend Tab Content :: lsdc_product_tab_content
 * Extending Product Digital :: lsdc_product_digital_extend
 * Extend Product Upload :: lsdc_product_upload
 */

//  Digital Product :: Add Option File URL
function lsdc_admin_upload_input()
{ 
    ?>
    <input type="text" name="digital_url" style="width:45%;margin-right:15px;" placeholder="store.com/download.zip" class="default" value="<?php echo get_post_meta( get_the_ID(), '_digital_url', true ); ?>">
    <?php
}
add_action( 'lsdc_product_upload', 'lsdc_admin_upload_input' );


/**
 * Register Metabox
 */
function lsdc_metabox_register() 
{
    add_meta_box(
        'lsdc_product_data',  
         __( 'Data Produk', 'lsdcommerce' ) ,              
        'lsdc_product_data', 
        'lsdc-product',                
        'normal',                 
        'high'                    
    );
}
add_filter( 'add_meta_boxes', 'lsdc_metabox_register' );


/**
 * Product Data
 * 
 * Displaying Information Product
 * Price
 * Stock
 * Shipping
 */
function lsdc_product_data()
{
    global $post; wp_nonce_field( basename( __FILE__ ), 'lsdc_admin_nonce' ); ?>

    <style>
        #lsdc_product_data .inside,
        #lsdc_product_data .wp-tab-bar,
        #lsdc_product_data .wp-tab-panel{
            margin:0 !important;
        }

        #lsdc_product_data .inside,
        #lsdc_product_data .wp-tab-bar{
            padding: 0;
        }

        #lsdc_product_data .wp-tab-panel{
            min-height: 250px;
            height: auto;
            max-height: 100%;
        }

        #lsdc_product_data .wp-tab-active{
            border: none;
        }

        #lsdc_product_data .wp-tab-bar li {
            padding: 7px 10px;
            display: block;
            margin: 0;
            border-bottom: 1px solid #ddd;
        }

        li.wp-tab-active {
            background: #f3f3f3;
        }

        #lsdc_product_data a:active, 
        #lsdc_product_data a:hover,
        #lsdc_product_data a:focus{
            box-shadow: none;
            outline: 0;
        }

        .wp-tab-bar li{
            text-decoration: none;
        }

        #lsdc_product_data .wp-tab-bar li a span{
            padding: 0 10px;

        }

        #lsdc_product_data .wp-tab-bar li a{
            display:flex;
            justify-content: left;
        }

        .metabox-field{
            padding: 7px 0;
        }

        lsdp-hide{
            display:none;
        }

        .mfield{
            margin: 4px 0 6px;
        }

        #lsdc_product_data ul.wp-tab-bar { /* Style Vertical Tab Widh*/
            float: left;
            width: 165px;
            text-align: left;
            margin: 0 -165px 0 5px;
            padding: 0;
        }

        #lsdc_product_data div.wp-tab-panel {
            margin: 0 5px 0 125px;
        }
    </style>

    <!-- Start tabs -->
    <div id="product-data">
        <!-- Vertical Tab -->
        <ul class="wp-tab-bar">
            <li class="wp-tab-active">
                <a href="#price">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tag"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                    <span><?php _e( 'Harga', 'lsdcommerce' ); ?></span>
                </a>
            </li>
            <li>
                <a href="#stock">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-package"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    <span><?php _e( 'Stok', 'lsdcommerce' ); ?></span>
                </a>
            </li>
            <li>
                <a href="#shipping">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-truck"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
                    <span><?php _e( 'Pengiriman', 'lsdcommerce' ); ?></span>
                </a>
            </li>

            <!-- Hookable :: Extending Tab -->
            <?php do_action('lsdc_product_tab_header'); ?>
        </ul>
        
        <!-- Price Content -->
        <div class="wp-tab-panel" id="price">
            <?php
            $price_normal = empty( get_post_meta( $post->ID, '_price_normal', true )) ? null : lsdc_currency_format( false, abs( get_post_meta( $post->ID, '_price_normal', true ) ) , lsdc_currency_get(), false  ) ;
            $price_discount = empty( get_post_meta( $post->ID, '_price_discount', true )) ? null : lsdc_currency_format(false, abs( get_post_meta( $post->ID, '_price_discount', true ) ) , lsdc_currency_get(), false );
            $limit_order = empty( get_post_meta( $post->ID, '_limit_order', true ) ) ? 9999 : get_post_meta( $post->ID, '_limit_order', true );
            ?>
            <div class="metabox-field">
                <label for="price_normal"><?php esc_attr_e( 'Harga Normal', 'lsdcommerce' ); ?> ( <?php lsdc_currency_view( 'symbol' ); ?> )</label>
                <p class="mfield"><input type="text" name="price_normal" class="currency" placeholder="<?php lsdc_currency_view( 'format' ); ?>" value="<?php echo $price_normal; ?>"></p>
                
                <label for="price_discount"><?php esc_attr_e( 'Harga Promo', 'lsdcommerce' ); ?> ( <?php lsdc_currency_view( 'symbol' ); ?> )</label>
                <p class="mfield"><input type="text" name="price_discount" class="currency" placeholder="<?php lsdc_currency_view( 'format' ); ?>" value="<?php echo $price_discount; ?>"></p>
            </div>
        </div>

        <!-- Stock Tab -->
        <div class="wp-tab-panel lsdp-hide" id="stock">
            <div class="metabox-field">
                <label for="stock"><?php esc_attr_e( 'Stok', 'lsdcommerce' ); ?> ( <small>9999 = <?php _e( 'Available', 'lsdcommerce' ); ?></small> )</label>
                <p class="mfield"><input type="text" name="stock" placeholder="9999" value="<?php echo get_post_meta( $post->ID, '_stock', true ); ?>"></p>

                <label for="stock_unit"><?php esc_attr_e( 'Stok Unit', 'lsdcommerce' ); ?> </label>
                <p class="mfield"><input type="text" name="stock_unit" placeholder="pcs" value="<?php echo get_post_meta( $post->ID, '_stock_unit', true ); ?>"></p>

                <label for="limit_order"><?php esc_attr_e( 'Limit Pembelian', 'lsdcommerce' ); ?> </label>
                <p class="mfield"><input type="text" name="limit_order" placeholder="1" value="<?php echo abs( $limit_order ); ?>"></p>
                <small>Setiap orang yang berbelanja hanya bisa memasukan barang sesuai dengan limit pembelian</small>
            </div>
        </div>

        <!-- Shipping Tab -->
        <div class="wp-tab-panel lsdp-hide" id="shipping">
            <div class="tabs tabs-inside">
                <!-- Digital -->
                <input name="shipping_tabs" value="digital" type="radio"/>
                <label class="label" for="shipping-1"><?php esc_attr_e( 'Produk Digital', 'lsdcommerce' ); ?></label>
                <div class="pane-metabox">
                    <label for="digital_version"><?php esc_attr_e( 'Versi', 'lsdcommerce' ); ?> : </label>
                    <input type="text" class="form-input" name="digital_version" placeholder="1.0.0" value="<?php echo get_post_meta( $post->ID, '_digital_version', true ); ?>">

                    <label for="digital_file" style="margin-left:10px;"><?php esc_attr_e( 'File', 'lsdcommerce' ); ?> : </label>
                     <!-- Hookable :: Extending for Upload via DropBox -->
                    <?php do_action( 'lsdc_product_upload' ); ?>
       
                    <!-- Hookable :: Extending for More Information Digital -->
                    <?php do_action( 'lsdc_product_digital_extend' ); ?>
                </div>
                <!-- Physical -->
                <input name="shipping_tabs" value="physical" type="radio" />
                <label class="label" for="shipping-2"><?php esc_attr_e( 'Produk Fisik', 'lsdcommerce' ); ?></label>
                <div class="pane-metabox">
                    <label for="physical_weight"><?php esc_attr_e( 'Berat', 'lsdcommerce' ); ?> /g : </label>
                    <input type="text" class="form-input currency" name="physical_weight" placeholder="50" value="<?php echo get_post_meta( $post->ID, '_physical_weight', true ); ?>">

                    <label for="physical_volume" style="margin-left:10px"><?php esc_attr_e( 'Volume', 'lsdcommerce' ); ?> /cm : </label>
                    <input type="text" class="form-input currency" name="physical_volume" placeholder="300" value="<?php echo get_post_meta( $post->ID, '_physical_volume', true ); ?>">
                </div>
                <?php $shipping_type = empty( get_post_meta( $post->ID, '_shipping_type', true ) ) ? 'digital' : get_post_meta( $post->ID, '_shipping_type', true ); ?>
                <script>jQuery( 'input[value="<?php echo esc_attr( $shipping_type ); ?>"]' ).prop( "checked", true );</script>
            </div>

        </div>

        <!-- Hookable :: Extending Tab Content -->
        <?php do_action('lsdc_product_tab_content'); ?>

        <div class="spacer" style="clear: both;"></div>
    </div>

    <!-- Script for Tab Click -->
    <script>
        jQuery(document).ready( function( $ ) {
            $('.wp-tab-bar a').click(function(event){
                event.preventDefault();
                
                // Limit effect to the container element.
                var context = $(this).closest('.wp-tab-bar').parent();
                $('.wp-tab-bar li', context).removeClass('wp-tab-active');
                
                $(this).closest('li').addClass('wp-tab-active');
                $('.wp-tab-panel', context).addClass('lsdp-hide');
                $( $(this).attr('href'), context ).removeClass('lsdp-hide');

            });

            // Make setting wp-tab-active optional.
            $('.wp-tab-bar').each(function(){
                if ( $('.wp-tab-active', this).length ){
                    $('.wp-tab-active', this).click();
                }else{
                    $('a', this).first().click();
                }
            });
        });
    </script>
    <?php
}


/**
 * Product Save
 * Saving metabox data.
 */
function lsdc_metabox_save( $post_id ) {

    if ( !isset($_POST['lsdc_admin_nonce'] ) || !wp_verify_nonce($_POST['lsdc_admin_nonce'], basename(__FILE__))) 
        return 'Nonce not Verified';
    
    if ( wp_is_post_autosave( $post_id ) ) // Check AutoSave
        return 'autosave';

    if ( wp_is_post_revision( $post_id ) ) // Check Revision
        return 'revision';

    if ( 'lsdc-product' == $_POST['post_type'] ) { // Check Permission
        if ( ! current_user_can( 'edit_page', $post_id ) ){
            return 'cannot edit page';
        }elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
            return 'cannot edit post';
        }
    }

    $version = isset($_POST['digital_version']) ? sanitize_text_field( $_POST['digital_version'] ) : '1.0.0';

    update_post_meta( $post_id, '_price_normal', abs( sanitize_text_field( lsdc_currency_clear( $_POST['price_normal'] ))) );
    update_post_meta( $post_id, '_price_discount', abs( sanitize_text_field( lsdc_currency_clear( $_POST['price_discount'] ))) );

    update_post_meta( $post_id, '_stock', empty( $_POST['stock'] ) ? 1 : abs( sanitize_text_field( $_POST['stock'])));
    update_post_meta( $post_id, '_stock_unit', sanitize_text_field( $_POST['stock_unit']));
    update_post_meta( $post_id, '_limit_order', sanitize_text_field( $_POST['limit_order']));

    update_post_meta( $post_id, '_shipping_type', sanitize_text_field( $_POST['shipping_tabs']) );

        // Digital
        update_post_meta( $post_id, '_digital_url', sanitize_text_field( $_POST['digital_url']) );
        update_post_meta( $post_id, '_digital_version', $version );
        // Physical
        update_post_meta( $post_id, '_physical_weight', lsdc_number_clear( $_POST['physical_weight']) );
        update_post_meta( $post_id, '_physical_volume', lsdc_number_clear( $_POST['physical_volume']) );
}
add_action( 'save_post', 'lsdc_metabox_save' );
add_action( 'new_to_publish', 'lsdc_metabox_save' );

/**
 * Metabox Order Detail
 */
?>