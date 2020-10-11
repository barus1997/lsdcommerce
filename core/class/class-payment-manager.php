<?php 
namespace LSDCommerce\Payments;

/*
 * Class Parent for Payment
 * storing : lsdc_payment_status
 * 
 * @since    1.3.5
 */
class LSDC_Payment {
    
    public static function register( $class ){
        global $lsdcommerce_payments;

        if ( !in_array( $class, (array)$lsdcommerce_payments ) )
        $lsdcommerce_payments[] = esc_attr( $class );
    }

    public static function release( $class ){
        global $lsdcommerce_payments;
    
        if ( in_array( $class, (array)$lsdcommerce_payments ) ) // if metode found
        unset( $lsdcommerce_payments[$class] );
    }

    public function status(){
        $lsdc_payment_status = get_option( 'lsdc_payment_status' );
        if( ! isset( $lsdc_payment_status[ $this->id ] ) ) $lsdc_payment_status[ $this->id ] = 'off';
        $status = $lsdc_payment_status[ $this->id ] == 'on' ? 'on' : 'off';
        ?>
            <div class="form-group">
                <label class="form-switch">
                    <input type="checkbox" id="<?php echo $this->id . '_status'; ?>" <?php echo ( $status == 'on' ) ? 'checked' : ''; ?>>
                    <i class="form-icon"></i> <?php _e( 'Enable', 'lsdcommerce' ); ?>
                </label>
            </div>
        <?php
    }

    public function confirmation(){
        return get_option( $this->id . '_confirmation' ) != '' ? get_option( $this->id . '_confirmation' ) : 'manual';
    }

    public function get_instruction(){ 
        $payment_option = get_option('lsdcommerce_payment_option'); // site-centris
        return esc_attr( $payment_option[$this->id]['instruction'] );
    }

    
    public function get_name(){ 
        $payment_option = get_option('lsdcommerce_payment_option'); // site-centris
        return esc_attr( $payment_option[$this->id]['name'] );
    }

    public function get_status(){
        $lsdc_payment_status = get_option( 'lsdc_payment_status' );
        if( ! isset( $lsdc_payment_status[ $this->id ] ) ) $lsdc_payment_status[ $this->id ] = 'off';
        $status = $lsdc_payment_status[ $this->id ] == 'on' ? 'on' : 'off';
        return $status;
    }

    public static function public_render(){
        global $lsdcommerce_payments; 

        $flag = false; // For Default Payment Selected 
        if( $lsdcommerce_payments ) {
            foreach( $lsdcommerce_payments as $key => $payment ){ //Iterasi
            
                if( class_exists( $payment ) ) : //checking Class
                    // add_action( 'plugins_loaded', function() { new $payment; } ); //Iniate Class

                    $instance = new $payment;
                    $payment_method = get_option( 'lsdcommerce_payment_option' );
        
                    $confirmation   = esc_attr( $instance->confirmation() );
                    $status         = esc_attr( $instance->get_status() );
                    $id             = $instance->id;
                    $pointer        = isset( $payment_method[$id]['alias'] ) ? $payment_method[$id]['alias'] : $id;
        
                    $logo           = esc_url( isset( $payment_method[$pointer]['logo'] ) ? $payment_method[$pointer]['logo'] : $instance->logo );
                    $name           = esc_attr( isset( $payment_method[$pointer]['name'] ) ? $payment_method[$pointer]['name'] : $instance->name );
                    $group          = esc_attr( isset( $payment_method[$pointer]['group_name'] ) ? $payment_method[$pointer]['group_name'] : $instance->group_name );
        
                    // Attach to Payment List Fronend
                    add_action( 'lsdcommerce_checkout_payment', function() use ( $id, $name, $logo, $status, $confirmation, $group, $flag ){ ?>

                        <?php if( $status == 'on' ) :  ?>
                            <div class="form-group"  data-id="<?php echo $id; ?>" data-method="<?php echo $group; ?>" >
                                <div class="item-radio">
                                    <input type="radio" name="payment_method" id="<?php echo $id; ?>" <?php echo ( $flag == false ) ? 'checked' : ''; ?>>
                                    <label for="<?php echo $id; ?>">
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="img">
                                                    <img src="<?php echo $logo; ?>" alt="<?php echo $name; ?>">
                                                </div>
                                            </div>
                                            <!-- <div class="col"> -->
                                                <!-- <h6><?php //echo $name; ?></h6> -->
                                                <!-- <p>Bayar melalui Virtual Bank, E-Wallet</p> -->
                                            <!-- </div> -->
                                        </div>
                                        <span class="type">
                                        <?php $confirmation == 'manual' ? _e('Manual','lsdcommerce') :  _e('Automatic','lsdcommerce') ; ?>
                                        </span>
                                    </label>
                                </div>
                            </div> 
                        <?php endif; ?>
    
                        <?php 
                    });
        
                endif;
                $flag = true; 
            }
        }
    }
}

?>