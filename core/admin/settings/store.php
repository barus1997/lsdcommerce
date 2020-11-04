<?php 
/**
 * Store Settings
 * Storing : lsdc_store_settings
 *
 * @package LSDCommerce
 * @subpackage Store
 * @since 1.0.0
 */

$countries = array(
    array(
        'code2'             => "ID",
        'code3'             => "IDN",
        'name'              => "Indonesia",
        'currency'          => "IDR",
        'currency_format'   => "IDR - Rupiah ( Rp 150.000 )",
    ),
    array(
        'code2'             => "US",
        'code3'             => "USA",
        'name'              => "United States",
        'currency'          => "USD",
        'currency_format'   => "USD - Dollar ( $15 )",
    ),
);
?>

<section class="form-horizontal" id="store-settings">
    <?php 
        $store_settings         = get_option( 'lsdcommerce_store_settings' ); 
        $country_selected       = isset( $store_settings['country'] ) ? esc_attr( $store_settings['country'] ) : 'ID';
        $state_selected         = isset( $store_settings['state'] ) ? esc_attr( $store_settings['state'] ) : 3;
        $city_selected          = isset( $store_settings['city'] ) ? esc_attr( $store_settings['city'] ) : 455;
        $address_selected       = isset( $store_settings['address'] ) ? esc_attr( $store_settings['address'] ) : '';
        $postalcode_selected    = isset( $store_settings['postalcode'] ) ? esc_attr( $store_settings['postalcode'] ) : '';

        $currency_selected      = isset( $store_settings['currency'] ) ? esc_attr( $store_settings['currency'] ) : 'IDR';

        if( $country_selected ){
            $states = json_decode( file_get_contents( LSDC_PATH . 'assets/cache/' . $country_selected . '-states.json') );
            $cities = json_decode( file_get_contents( LSDC_PATH . 'assets/cache/' . $country_selected . '-cities.json') );
        }else{ // Default ID 
            $states = json_decode( file_get_contents( LSDC_PATH . 'assets/cache/ID-states.json') );
            $cities = json_decode( file_get_contents( LSDC_PATH . 'assets/cache/ID-cities.json') );
        }
    ?>

    <!-- Country -->
    <div class="form-group">
        <div class="col-3 col-sm-12">
            <label class="form-label" for="country">
                <?php _e( 'Negara', 'lsdcommerce' ); ?>
            </label>
        </div>

        <div class="col-9 col-sm-12">
            <select class="form-select" id="country"> <!-- lsdcommerce-admin.js : onChange trigger result States -->
                <?php foreach ($countries as $key => $country) : ?>
                    <option value="<?php echo $country['code2']; ?>" <?php echo ( $country['code2'] == $country_selected ) ? 'selected' : ''; ?>><?php echo $country['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- State -->
    <div class="form-group">
        <div class="col-3 col-sm-12">
            <label class="form-label" for="states">
                <?php _e( 'Provinsi', 'lsdcommerce' ); ?>
            </label>
        </div>
        <div class="col-9 col-sm-12">
            <select class="form-select" id="states">  <!-- lsdcommerce-admin.js onChange trigger result Cities -->
                <?php foreach ( $states as $key => $state) : ?>
                    <option value="<?php echo $state->province_id; ?>"  <?php echo (  $state->province_id == $state_selected  ) ? 'selected' : ''; ?>><?php echo $state->province; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- City -->
    <div class="form-group">
        <div class="col-3 col-sm-12">
            <label class="form-label" for="cities">
                <?php _e( 'Kota / Kabupaten', 'lsdcommerce' ); ?>
            </label>
        </div>
        <div class="col-9 col-sm-12">
            <select class="form-select" id="cities">
                <?php foreach ( $cities as $key => $city) : ?>
                    <?php if (  $city->province_id == $state_selected ) : ?>
                        <option value="<?php echo $city->city_id; ?>"  <?php echo (  $city->city_id ==  $city_selected  ) ? 'selected' : ''; ?>><?php echo $city->type . ' ' . $city->city_name; ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Adddres -->
    <div class="form-group">
        <div class="col-3 col-sm-12">
            <label class="form-label" for="address">
                <?php _e( 'Alamat', 'lsdcommerce' ); ?>
            </label>
        </div>
        <div class="col-9 col-sm-12">
            <textarea class="form-input" id="address" placeholder="Jl Dunia Maya, RT 010 RW 001, Desa Virtual, Kecamatan Digital" rows="3" style="width:59%;"><?php echo $address_selected;  ?></textarea>
        </div>
    </div>

    <!-- Postal Code -->
    <div class="form-group">
        <div class="col-3 col-sm-12">
            <label class="form-label" for="postalcode">
                <?php _e( 'Kode Pos', 'lsdcommerce' ); ?>
            </label>
        </div>
        <div class="col-9 col-sm-12">
            <input class="form-input" id="postalcode" type="number" placeholder="15561" style="width:59%;" value="<?php echo $postalcode_selected; ?>">
        </div>
    </div>

    <br>

    <!-- Currency -->
    <div class="form-group">
        <div class="col-3 col-sm-12">
            <label class="form-label" for="currency">
                <?php _e( 'Mata Uang', 'lsdcommerce' ); ?>
            </label>
        </div>
        <div class="col-9 col-sm-12">
            <select class="form-select" id="currency">
                <?php foreach ( $countries as $key => $country) : ?>
                    <option value="<?php echo $country['currency']; ?>" <?php echo ( $country['currency'] == $currency_selected ) ? 'selected' : ''; ?>><?php echo $country['currency_format']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <br>

    <button class="btn btn-primary" id="lsdc_admin_store_save" style="width:120px"><?php _e( 'Simpan', 'lsdcommerce' ); ?></button> <!-- lsdcommerce-admin.js on Click Saving -->

</section>
