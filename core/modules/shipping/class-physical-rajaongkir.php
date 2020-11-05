<?php 
use LSDCommerce\Shipping\LSDC_Shipping;

Class LSDC_Shipping_RajaOngkir Extends LSDC_Shipping {
    public $id          = 'lsdc_shipping_rajaongkir_starter';
    public $name        = 'RajaOngkir Starter';
    public $type        = 'physical';
    public $doc_url     = 'https://docs.lsdplugins.com/lsdcommerce/shipping-rajaongkir/';
    public $country     = 'ID';
    public $rajaongkir  = null;

    private static $instance;

    public static function init()
    {
      if ( is_null( self::$instance ) )
      {
        self::$instance = new self();
      }
      return self::$instance;
    }

    public function __construct() {
        parent::register( 'LSDC_Shipping_RajaOngkir' );
        // TestCase : Data Empty
        // update_option( $this->id,null );

        if( empty( get_option( $this->id ) ) ){
            $new = array();
            $new['courier'] = 'jne';
            $new['apikey']  = '80aa49704fc30a939124a831882dea72';
            update_option( $this->id, $new );
        }
    }

    public static function get_name(){
        return $this->name;
    }

    public static function get_type(){
        return $this->type;
    }

    public function get_apikey(){
        return isset( get_option($this->id)['apikey'] ) ? strtolower( get_option($this->id)['apikey'] ) : '80aa49704fc30a939124a831882dea72';
    }

    // Load Shipping Service
    public static function shipping_list( $shipping ){
        $apikey = isset( get_option('lsdc_shipping_rajaongkir_starter')['apikey'] ) ? strtolower( get_option('lsdc_shipping_rajaongkir_starter')['apikey'] ) : '80aa49704fc30a939124a831882dea72';
        $rajaongkir = new LSDCommerce_RajaOngkir( 'starter', $apikey );

        $store_settings = get_option( 'lsdcommerce_store_settings' ); 
        $city_selected  = isset( $store_settings['lsdc_store_city'] ) ? esc_attr( $store_settings['lsdc_store_city'] ) : 455;

        $products = $shipping['products'];
        $weights = 0;
        foreach ($products as $key => $item) {
            $weights += intval( get_post_meta( $item['id'], '_physical_weight', true ) ) * intval($item['qty'] );
            // get_post_meta( $post->ID, '_physical_volume', true )
        }
    
        $shipping['origin']  = $city_selected;
        $shipping['courier'] = isset( get_option('lsdc_shipping_rajaongkir_starter')['courier'] ) ? strtolower( get_option('lsdc_shipping_rajaongkir_starter')['courier'] ) : 'jne';
    
        $calc_rajaongkir = $rajaongkir->cost( $shipping['origin'], $shipping['target']['city'], $weights, $shipping['courier'] );
        $shipping_physical_results = $calc_rajaongkir['rajaongkir']['results'];

        $shipper = $shipping_physical_results[0]['code'];
        $name = strtoupper( $shipper );

        ob_start();
            foreach ($shipping_physical_results[0]['costs'] as $key => $item) {
                $service = $item["service"];
                $shipper_id = $shipper . '-' . strtolower($service);
                ?>
                <div class="col-auto col-6 col-xs-12 swiper-no-swiping">
                    <div class="lsdp-form-group">
                        <div class="item-radio">
                            <input type="radio" name="physical_courier" id="<?php echo $shipper_id; ?>" <?php echo $key == 0 ? 'checked' : ''; ?>>
                            <label for="<?php echo $shipper_id; ?>">
                                <img src="<?php echo LSDC_URL . 'assets/img/' . $shipper . '.png'; ?>" alt="<?php echo $name; ?>">
                                <h6><?php echo $name . ' ' . strtoupper( $service ) . ' ( ' . $item["cost"][0]["etd"] . ' ) '; ?></h6>
                                <p><?php echo $item["cost"][0]["value"] == 0 ? __( 'Free', 'lsdcommerce' ) : lsdc_currency_format( true, $item["cost"][0]["value"] ); ?></p>
                            </label>
                        </div>
                    </div>
                </div>
                <?php 
            }
        $listing = ob_get_clean();
        return $listing;
    }
    
    /**
     * What : Function to get cost with rajaongkir starter
     * When : lsdc_order_insert();
     * 
     * @see : https://docs.lsdplugins.com/en/docs/shipping-rajaongkir/
     * 
     * @package LSDCommerce
     * @subpackage Order/Insert
     * @since 1.0.0
     *
     * @param array $detail
     * @return int `cost` 
     */
    public static function calc( $shipping ){
        $apikey = isset( get_option('lsdc_shipping_rajaongkir_starter')['apikey'] ) ? strtolower( get_option('lsdc_shipping_rajaongkir_starter')['apikey'] ) : '80aa49704fc30a939124a831882dea72';
        $rajaongkir = new LSDCommerce_RajaOngkir( 'starter', $apikey );
        $store_settings = get_option( 'lsdcommerce_store_settings' ); 
        $city_selected  = isset( $store_settings['lsdc_store_city'] ) ? esc_attr( $store_settings['lsdc_store_city'] ) : 455;
    
        $shipping['origin']  = $city_selected;
        $shipping['courier'] = isset( get_option('lsdc_shipping_rajaongkir_starter')['courier'] ) ? strtolower( get_option('lsdc_shipping_rajaongkir_starter')['courier'] ) : 'jne';
        
        $calc_rajaongkir = $rajaongkir->cost( $shipping['origin'], $shipping['destination'], $shipping['weight'], $shipping['courier'] );

        // Results from RajaOngkir
        $shipping_physical_results = $calc_rajaongkir['rajaongkir']['results'];
        $shipper = $shipping_physical_results[0]['code']; // Shipper Name ( JNE )

        foreach ($shipping_physical_results[0]['costs'] as $key => $item) {
            $service = strtolower($item["service"]); // Package Name ( OKE, YES, REG))
            $shipper_id = $shipper . '-' . $service; // Concat JNE OKE

            if( $shipper_id == $shipping['service'] ){ //Package Matching

                // Set Transient to Extra Payment
                $transient = array(
                    'shipping' => array( 
                        'label' => __( 'Shipping : ', 'lsdcommerce' ),
                        'bold'	=> strtoupper( $shipper ) . ' ' . strtoupper( $service ) . ' ( ' . $item["cost"][0]["etd"] . ' ' . __( 'days', 'lsdcommerce' ) . ' ) ', // Bolding on Naming
                        'sign'  => '+', // Sign Add or Sub
                        'value' => lsdc_currency_format( true, $item["cost"][0]["value"] ), // Int - Cost Backend for Calc : eg: 100000
                        'cost'	=> intval( '+' . $item["cost"][0]["value"] ) // String - Cost Frontend : eg : +10000/ -10000
                    )
                );
        
                return $transient;
                break;
            }
        }
     
    }

    public function manage(){ 
        $settings           =  get_option( $this->id );
        $courier_selected   =  $settings['courier'];
    ?>
          <div class="tabs-wrapper">

            <input type="radio" name="<?php echo $this->id; ?>" id="rajaongkir-settings" checked="checked"/>
            <label class="tab" for="tab4"><?php _e( 'Pengaturan', 'lsdd' ); ?></label>

            <div class="tab-body-wrapper">
                <!------------ Tab : Settings ------------>
                <div id="rajaongkir-body" class="tab-body form-horizontal">
                    <!-- Content Pengaturan -->
                    <form>
                        <div class="form-group">
                            <div class="col-3 col-sm-12">
                                <label class="form-label" for="privacy">Pilihan Jasa Pengiriman : </label>
                            </div>
                            <div class="col-9 col-sm-12">
                                <label class="form-radio">
                                    <input type="radio" name="courier" value="JNE" checked><i class="form-icon"></i> JNE
                                </label>
                                <label class="form-radio">
                                    <input type="radio" name="courier" value="TIKI"><i class="form-icon"></i> TIKI
                                </label>
                                <label class="form-radio">
                                    <input type="radio" name="courier" value="POS"><i class="form-icon"></i> POS
                                </label>
                            </div>
                        </div>

                        <script>jQuery( 'input[value="<?php esc_attr_e( $courier_selected ); ?>"]' ).prop( "checked", true );</script>
                
                        <div class="form-group">
                            <div class="col-3 col-sm-12">
                                <label class="form-label" for="apikey">API KEY</label>
                            </div>
                            <div class="col-9 col-sm-12">
                                <input class="form-input" type="text" name="apikey" placeholder="80aa49704fc30a939124a831882dea72" style="width:320px" value="<?php echo $this->get_apikey(); ?>">
                            </div>
                        </div>

                        <br>
                        <button class="btn btn-primary lsdc-admin-save" id="<?php echo $this->id; ?>" style="width:120px"><?php _e( 'Simpan', 'lsdcommerce' ); ?></button> 
                    </form>
                </div>
            </div>

        </div>

        <style>
            /* Action Tab */
            #rajaongkir-settings:checked~.tab-body-wrapper #rajaongkir-body,
            #tab2:checked~.tab-body-wrapper #tab-body-2,
            #tab3:checked~.tab-body-wrapper #tab-body-3,
            #tab4:checked~.tab-body-wrapper #tab-body-4 {
                position: relative;
                top: 0;
                opacity: 1
            }
        </style>

    <?php
    }
}
LSDC_Shipping_RajaOngkir::init(); 

function lsdc_shipping_rajaongkir_starter_calc( $detail ){
    return LSDC_Shipping_RajaOngkir::calc( $detail ); 
}
?>