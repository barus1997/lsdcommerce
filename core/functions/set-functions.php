<?php 
// Hook Set
function lsdc_product_tabs()
{
    ?>
        <div class="lsdc-nav-tab">
            <?php $count = 0; foreach ( lsdc_product_tabs_header() as $key => $item) : ?>
                <a data-target="<?php echo $key; ?>" data-toggle="tab" class="nav-link <?php echo ( $count == 0 ) ? 'active' : ''; ?>"><?php echo $item; ?></a>
            <?php $count++; endforeach; ?>
        </div>

        <div class="lsdc-tab-content py-10 px-10">
            <div class="tab-pane show" data-tab="description">
                <?php the_content(); ?>
            </div>
            <?php do_action( 'lsdcommerce_single_tabs_content') ?>
        </div>
    <?php
}
add_action( 'lsdcommerce_single_tabs', 'lsdc_product_tabs' ); //Single Tabs

function lsdc_product_tabs_header(){
    $lsdc_product_tab_public = array(
        'description' => __( 'Description', 'lsdcommerce' )
    );
    $lsdc_product_tab_public = array_reverse( $lsdc_product_tab_public );
    if( has_filter('lsdcommerce_product_tabs_header') ) {
        $lsdc_product_tab_public = apply_filters( 'lsdcommerce_product_tabs_header', $lsdc_product_tab_public );
    }
    return array_reverse( $lsdc_product_tab_public );
}

// Add Class to Body in Single Product Template
// function lsdcommerce_single_hook(){
//     add_filter( 'body_class', function( $class ) {
//        $class[] = 'lsdcommerce-single'; 
//        return $class;
//     });
// }
// add_action( 'lsdcommerce_single', 'lsdcommerce_single_hook' );

// Restrict Wp-Admin and Redirect to Member
function lsdc_set_redirect_member() {
    if ( ! current_user_can( 'manage_options' ) && ( ! wp_doing_ajax() ) ) {
        wp_safe_redirect( 'https://lsdplugins.com/member/' ); // Replace this with the URL to redirect to.
        exit;
    }
}
add_action( 'admin_init', 'lsdc_set_redirect_member', 1 );

add_action( 'lsdcommerce_listing_price_hook', 'lsdc_price_frontend');
add_action( 'lsdcommerce_single_price', 'lsdc_price_frontend');
/**
 * Function to add State, City and Address for Shipping
 * 
 * This function will provide interface for getting shipping address,
 * you can trigger to load any available package shipping with javascript
 * The adress will be default same address like store its mean, local shipment
 * and if you change the city, the option package will be loaded.
 * 
 * @package LSDCommerce
 * @subpackage Shipping
 * 
 * @link https://docs.lsdplugins.com/en/docs/shipping-physical-target/
 * @since 1.0.0
 * @param action lsdcommerce_shipping_physical_control
 */
function lsdc_set_shipping_controls(){ ?>
    <p class="mb-10"><?php _e( "Shipping Address", 'lsdcommerce' ); ?></p>
    <?php 
        $store_settings         = get_option( 'lsdc_store_settings' ); 
        $country_selected       = isset( $store_settings['lsdc_store_country'] ) ? esc_attr( $store_settings['lsdc_store_country'] ) : 'ID';
        $state_selected         = isset( $store_settings['lsdc_store_state'] ) ? esc_attr( $store_settings['lsdc_store_state'] ) : 3;
        $city_selected          = isset( $store_settings['lsdc_store_city'] ) ? esc_attr( $store_settings['lsdc_store_city'] ) : 455;
        $address_selected       = isset( $store_settings['lsdc_store_address'] ) ? esc_attr( $store_settings['lsdc_store_address'] ) : '';
        $postalcode_selected    = isset( $store_settings['lsdc_store_postalcode'] ) ? esc_attr( $store_settings['lsdc_store_postalcode'] ) : '';

        $currency_selected      = isset( $store_settings['lsdc_store_currency'] ) ? esc_attr( $store_settings['lsdc_store_currency'] ) : 'IDR';

        if( $country_selected ){
            $states = json_decode( file_get_contents( LSDC_PATH . 'core/cache/' . $country_selected . '-states.json') );
            $cities = json_decode( file_get_contents( LSDC_PATH . 'core/cache/' . $country_selected . '-cities.json') );
        }else{
            $states = json_decode( file_get_contents( LSDC_PATH . 'core/cache/ID-states.json') );
            $cities = json_decode( file_get_contents( LSDC_PATH . 'core/cache/ID-cities.json') );
        }
    ?>
    <input type="text" id="country" value="ID" class="hidden">
    <div class="lsdp-row no-gutters">
        <div class="col-6">
            <div class="form-group">
                <select class="form-control custom-select swiper-no-swiping shipping-reset" id="states">  <!-- lsdcommerce-admin.js onChange trigger result Cities -->
                    <?php foreach ( $states as $key => $state) : ?>
                        <option value="<?php echo $state->province_id; ?>"  <?php echo (  $state->province_id == $state_selected  ) ? 'selected' : ''; ?>><?php echo $state->province; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                <select class="form-control custom-select swiper-no-swiping shipping-reset" id="cities">  
                <option value=""><?php _e( "Choose City", 'lsdcommerce' ); ?></option>
                <?php foreach ( $cities as $key => $city) : ?>
                    <?php if ( $city->province_id == $state_selected ) : ?>
                        <option value="<?php echo $city->city_id; ?>"><?php echo $city->type . ' ' . $city->city_name; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <!-- <div class="form-group">
        <select name="" class="form-control custom-select">
            <option>Pasar Kemis</option>
        </select>
    </div> -->
    <div class="form-group">
        <textarea id="shipping_address" class="form-control swiper-no-swiping" placeholder="Alamat"></textarea>
    </div>
    <?php
}
add_action( 'lsdcommerce_shipping_physical_control', 'lsdc_set_shipping_controls' ); 


/**
 * Function to get any packages available based on location
 * When : Checkout load on First
 * 
 * This function will provide available shipping packages
 * will load any enable shipping method, and calc cost based on location
 * and display option to user.
 * Algorithm
 * - Load Every Shipping Method with Status Enable
 * - Calculate Every Shipping Cost based on Local Location
 * - Sort by Cheaper
 * - Display to User
 * 
 * @package LSDCommerce
 * @subpackage Shipping
 * 
 * @link https://docs.lsdplugins.com/en/docs/shipping-physical-target/
 * @since 1.0.0
 * @param action lsdcommerce_shipping_physical_control
 */
function lsdc_set_shipping_packages(){ 
    $base = lsdc_get_store( 'city' );
    $target = lsdc_get_store( 'city' );

    global $lsdcommerce_shippings;
    $shipping_physical_results = array();
    if( isset($lsdcommerce_shippings) ){
        foreach ($lsdcommerce_shippings as $key => $class) {
            $object = new $class;
            if( $object->type == 'physical'){
                if( $object->get_status() == 'on'){
                    echo $object->shipping_list( $shipping_data );
                }
            }
        }
    }

    // $shipping_option = array(
    //     'jne-reg' => array(
    //         'logo'  =>  LSDC_URL . '/assets/img/jne.png',
    //         'label' => 'JNE REG ( 2-3 Hari )',
    //         'cost'  => 9000
    //     ),
    //     'jne-yes' => array(
    //         'logo'  =>  LSDC_URL . '/assets/img/jne.png',
    //         'label' => 'JNE YES ( 1 Hari )',
    //         'cost'  => 15000
    //     ),
    //     'free-shipping' => array(
    //         'logo'  =>  LSDC_URL . '/assets/img/logo.png',
    //         'label' => 'Gratis Ongkir',
    //         'cost'  => 0
    //     )
    // );

    // foreach ($lsdc_shippings as $key => $shipping) {
    //     $object = new $shipping;
    //     if( $object->type == 'physical' ){
    //         $shipping_option = $object->calc_list(); // a
    //     }
        
    // }

    lsdc_array_sort_bykey($shipping_option,"cost");
    ?>

    <?php $index = 0; foreach ($shipping_option as $key => $item) : ?>
        <div class="col-auto col-6 swiper-no-swiping">
            <div class="lsdp-form-group">
                <div class="item-radio">
                    <input type="radio" name="physical_courier" id="<?php echo $key; ?>" <?php echo $index == 0 ? 'checked' : ''; ?>>
                    <label for="<?php echo $key; ?>">
                        <img src="<?php echo $item['logo']; ?>" alt="<?php echo $item['label']; ?>">
                        <h6><?php echo $item['label']; ?></h6>
                        <p><?php echo $item['cost'] == 0 ? __( 'Free', 'lsdcommerce' ) : lsdc_currency_format( true, $item['cost'] ); ?></p>
                    </label>
                </div>
            </div>
        </div>
    <?php $index++; endforeach; ?>

<?php
}
// add_action( 'lsdcommerce_shipping_physical_services', 'lsdc_set_shipping_packages' );
?>