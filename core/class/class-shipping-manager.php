<?php 
namespace LSDCommerce\Shipping;

/**
 * Class Parent for Shipping
 * properties : metode_name
 * @since    1.3.5
 */
class LSDC_Shipping {

    public static function register( $option )
    {
        global $lsdcommerce_shippings;

        if ( !in_array( $option, (array)$lsdcommerce_shippings ) )
        $lsdcommerce_shippings[] = esc_attr( $option );
    }

    public static function release( $option )
    {
        global $lsdcommerce_shippings;

        if ( in_array( $option, (array)$lsdcommerce_shippings ) ) // if metode found
        unset( $lsdcommerce_shippings[$option] );
    }
    /**
     * What : Main Function to Sending Notification
     * When : Every want to Sending Notification
     * @param array $order
     * @param string $event ( order | complete | cancel | )
     * 
     */
    public static function sender(  $obj ){
        // Hook for multiple shipping
        do_action( 'lsdcommerce_shipping_hook', $obj );

    }

    public function tab()
    { 
    ?>     
        <input type="radio" name="sections" id="<?php echo $this->id; ?>">
        <label data-linking="<?php echo $this->id; ?>" class="tablabel form-switch" for="<?php echo $this->id; ?>">
            <span><?php echo ucfirst( $this->type ); ?></span>
            <p><?php echo $this->name; ?></p>
        </label>
    <?php 
    }

    public function header()
    { 
        $lsdc_shipping_status = get_option( 'lsdc_shipping_status' );
       
        if( ! isset( $lsdc_shipping_status[ $this->id ] ) ) $lsdc_shipping_status[ $this->id ] = 'off';
        $status = $lsdc_shipping_status[ $this->id ] == 'on' ? 'on' : 'off'; ?>

        <div class="lsdc-shipping-status">
            <h5>
                <?php echo $this->name; ?>
                <a style="margin-left:10px; border-radius: 20px;padding: 5px 25px;" href="<?php echo $this->doc_url; ?>" target="_blank" class="btn btn-primary"><?php _e( 'Panduan', 'lsdcommerce' ); ?></a>
            </h5>
            <div class="form-group">
                <label class="form-switch">
                <input type="checkbox" id="<?php echo $this->id . '_status'; ?>" <?php echo ( $status == 'on' ) ? 'checked' : ''; ?>>
                    <i class="form-icon"></i> <?php _e( 'Aktifkan ', 'lsdcommerce' ); ?><?php echo $this->name; ?>
                </label>
            </div>
        </div>
    <?php
    }

    public function get_status()
    {
        $lsdc_shipping_status = get_option( 'lsdc_shipping_status' );
        if( ! isset( $lsdc_shipping_status[ $this->id ] ) ) $lsdc_shipping_status[ $this->id ] = 'off';
        return $lsdc_shipping_status[ $this->id ] == 'on' ? 'on' : 'off'; 
    }


    public static function public_render()
    {
        global $lsdcommerce_shippings; // getting global shipping
	    $flag = false; // For Default Payment Selected 
        if( $lsdcommerce_shippings ) {

            foreach( $lsdcommerce_shippings as $key => $shipping ){ //Iterasi

                if( class_exists( $shipping ) ) : //checking Class
                    $instance = new $shipping;
                    
                    $status = $instance->get_status();
                    $id     = $instance->id;
                    $name   = $instance->name;
                    $type   = $instance->type;

                    if( $type == 'digital' ) :
                        // Attach to Shipping Digital List
                        add_action( 'lsdcommerce_shipping_digital_services', function() use ( $id, $name, $type, $status, $flag ){ 

                            ?>
                            <?php if( $status == 'on' ) :  ?>
                                <div class="col-auto col-6 col-xs-12 swiper-no-swiping">
                                    <div class="lsdp-form-group">
                                        <div class="item-radio">
                                            <input type="radio" name="<?php echo $type; ?>_courier" id="<?php echo $id; ?>" <?php echo ( $flag == false ) ? 'checked' : ''; ?>>
                                            <label for="email">
                                                <h6><?php echo $name; ?></h6>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php 
                        });
                    endif;

                    if( $type == 'physical' ) :
                        // Attach to Shipping Physical List
                        add_action( 'lsdcommerce_shipping_physical_list', function() use ( $id, $name, $type, $status, $flag ){
                        });
                    endif;
    
                endif;
                $flag = true; 
            }
        }
    }
    
}
?>