<?php
use LSDCommerce\Payments\LSDC_Payment;
/**
 * @package LSDCommerce
 * @subpackage Payment
 * Class Default for Transfer Bank Method
 *
 * @since    1.0.0
 */


// Transfer Bank BCA
Class LSDC_BankBCA Extends LSDC_Payment {
    public $id              = 'bankbca';
    public $name            = 'BCA'; //Name Of Payment

    public $group           = 'transferbank';
    public $group_name      = 'Transfer Bank';

    public $logo            =  LSDC_URL . 'assets/images/banks/bca.png';
    public $country         = 'ID';

    protected $bank_code    = '014';
    protected $swift_code   = 'CENAIDJA';

    private static $instance;
    
    public static function init()
    {
      if ( is_null( self::$instance ) )
      {
        self::$instance = new self();
      }
      return self::$instance;
    }

    public function __construct() 
    {
        parent::register( 'LSDC_BankBCA' );
    }

    public function manage(){ 
        $lsdc_method = get_option( 'lsdcommerce_payment_settings' ); // site-centris //Saving in Site
        if( empty( $lsdc_method ) )  $lsdc_method = array();

        if( ! isset($lsdc_method[$this->id] ) || $lsdc_method[$this->id] == '' ){ // Empty and Not Isset
            $lsdc_method[$this->id] = array(
                'name'              => $this->name,
                'logo'              => $this->logo,
                'group'             => $this->group,
                'group_name'        => $this->group_name,
                'bank_code'         => $this->bank_code,
                'swift_code'        => $this->swift_code,
                'account_holder'    => 'Lasida',
                'account_number'    => '65224545542',
                'instruction'       => __( 'Silahkan lakukan pembyaran ke rekening ini sesuai dengan total pembelian.', 'lsdcommerce' )
            );
            update_option(  'lsdcommerce_payment_settings' , $lsdc_method );
        }
      
    ?>

    <div id="<?php echo $this->id; ?>_content" class="payment-editor d-hide">
        <div class="panel-header text-center">
            <div class="panel-title h5 float-left"><?php _e( 'Edit  ', 'lsdcommerce' ); ?> <?php  echo $this->group_name; ?> <?php  echo $this->name; ?></div>
            <div class="panel-close float-right"><i class="icon icon-cross"></i></div>
        </div>
        
        <div class="panel-body">
            <form>
                <div class="divider text-center" style="margin-top:25px;" data-content="<?php _e( 'Akun Bank', 'lsdcommerce' ); ?>"></div>
                
                <div class="form-group">
                    <label class="form-label" for="account_number"><?php _e( 'Nomor Rekening ', 'lsdcommerce' ); ?></label>
                    <input class="form-input" type="text" name="account_number" value="<?php esc_attr_e($lsdc_method[$this->id]['account_number']); ?>" placeholder="6545464646">
                </div>

                <div class="form-group">
                    <label class="form-label" for="account_holder"><?php _e( 'Atas Nama', 'lsdcommerce' ); ?></label>
                    <input class="form-input" type="text" name="account_holder" value="<?php esc_attr_e($lsdc_method[$this->id]['account_holder']); ?>" placeholder="Lasida">
                </div>

                <div class="divider text-center" style="margin-top:25px;" data-content="<?php _e( 'Instruksi', 'lsdcommerce' ); ?>"></div>

                <div class="form-group">
                    <label class="form-label" for="instruction"><?php _e( 'Instruksi Pembayaran', 'lsdcommerce' ); ?></label>
                    <textarea class="form-input" name="instruction" placeholder="<?php _e( 'Silahkan lakukan pembyaran ke rekening ini sesuai dengan total pembelian.', 'lsdcommerce' ); ?>" lsdp-rows="3"><?php esc_attr_e( $lsdc_method[$this->id]['instruction'] ); ?></textarea>
                </div>
            </form>
        </div>

        <div class="panel-footer">
            <button class="btn btn-primary btn-block lsdc-payment-save" id="<?php echo $this->id; ?>_payment"><?php _e( 'Simpan', 'lsdcommerce' ); ?></button>
        </div>
    </div>
    <?php
    }
}
LSDC_BankBCA::init();

// Custom Bank

Class LSDC_BankCustomOne Extends LSDC_Payment {
    public $id              = 'custombankone';
    public $name            = ''; //Name Of Payment

    public $group           = 'banktransfer';
    public $group_name      = '';

    public $logo            =  LSDC_URL . 'assets/images/banks/custom.png';
    public $country         = 'GLOBAL';

    protected $bank_code    = '';
    protected $swift_code   = '';

    private static $instance;
    
    public static function init()
    {
      if ( is_null( self::$instance ) )
      {
        self::$instance = new self();
      }
      return self::$instance;
    }

    public function __construct() 
    {
        parent::register( 'LSDC_BankCustomOne' );
    }

    public function manage(){ 
        $lsdc_method = get_option( 'lsdcommerce_payment_settings' ); // site-centris //Saving in Site
        if( empty( $lsdc_method ) )  $lsdc_method = array();

        if( ! isset($lsdc_method[$this->id] ) || $lsdc_method[$this->id] == '' ){ // Empty and Not Isset
            $lsdc_method[$this->id] = array(
                'alias'             => 'custombankone',
                'name'              => __( 'Custom Bank One', 'lsdcommerce' ),
                'logo'              => $this->logo,
                'group'             => $this->group,
                'group_name'        => __( 'Bank Transfer', 'lsdcommerce' ),
                'bank_code'         => $this->bank_code,
                'swift_code'        => $this->swift_code,
                'account_holder'    => '6565656565',
                'account_number'    => 'Lasida',
                'instruction'       => __( 'Silahkan lakukan pembyaran ke rekening ini sesuai dengan total pembelian.', 'lsdcommerce' )
            );
            update_option( 'lsdcommerce_payment_settings' , $lsdc_method );
        }
        $payment_settings = get_option( 'lsdcommerce_payment_settings' );
        $pointer = isset( $payment_settings[$this->id]['alias'] ) ? $payment_settings[$this->id]['alias'] : $this->id;
    ?>

    <div id="<?php echo $this->id; ?>_content" class="payment-editor d-hide">
        <div class="panel-header text-center">
            <div class="panel-title h5  float-left"><?php _e( 'Edit Custom Bank', 'lsdcommerce' ); ?></div>
            <div class="panel-close float-right"><i class="icon icon-cross"></i></div>
        </div>
        
        <div class="panel-body">
            <form>

                <div class="form-group">
                    <label class="form-label" for="id"><?php _e( 'ID Custom Bank', 'lsdcommerce' ); ?></label>
                    <input class="form-input" type="text" name="alias" value="<?php echo $lsdc_method[$this->id]['alias']; ?>" placeholder="bankcanada">
                </div>

                <div class="form-group">
                    <label class="form-label" for="name"><?php _e( 'Nama Bank', 'lsdcommerce' ); ?></label>
                    <input class="form-input" type="text" name="name" value="<?php echo $lsdc_method[$pointer]['name']; ?>" placeholder="<?php echo $this->name; ?>">
                </div>
   
                <div class="form-group">
                    <label class="form-label" for="logo"><?php _e( 'Logo Bank', 'lsdcommerce' ); ?></label>
                    <?php if ( current_user_can( 'upload_files' ) ) : ?>
                        <img style="width:150px;margin-bottom:15px;" src="<?php echo ( $lsdc_method[$pointer]['logo'] == '' ) ? $this->logo : esc_url( $lsdc_method[$pointer]['logo'] ); ?>"/>
                        <input class="form-input" type="text" style="display:none;" name="logo" value="<?php echo ( $lsdc_method[$pointer]['logo'] == '' ) ? $this->logo : esc_url( $lsdc_method[$pointer]['logo'] ); ?>" >
                        <input type="button" value="<?php _e('Pilih Gambar', 'lsdcommerce' ); ?>" class="lsdc_admin_upload btn col-12">
                    <?php endif; ?>
                </div>

                <div class="divider text-center" style="margin-top:25px;" data-content="<?php _e( 'Akun Bank', 'lsdcommerce' ); ?>"></div>

                <div class="form-group">
                    <label class="form-label" for="bank_code"><?php _e( 'Kode Bank', 'lsdcommerce' ); ?></label>
                    <input class="form-input" type="text" name="bank_code" value="<?php echo $lsdc_method[$pointer]['bank_code']; ?>" placeholder="014">
                </div>

                <div class="form-group">
                    <label class="form-label" for="swift_code"><?php _e( 'Kode BIC / SWIFT ( jika ada )', 'lsdcommerce' ); ?></label>
                    <input class="form-input" type="text" name="swift_code" value="<?php echo $lsdc_method[$pointer]['swift_code']; ?>" placeholder="ABCAKSBSA">
                </div>
                <div class="form-group">
                    <label class="form-label" for="account_number"><?php _e( 'Nomor Rekening', 'lsdcommerce' ); ?></label>
                    <input class="form-input" type="text" name="account_number" value="<?php echo $lsdc_method[$pointer]['account_number']; ?>" placeholder="6545464646">
                </div>

                <div class="form-group">
                    <label class="form-label" for="account_holder"><?php _e( 'Atas Nama', 'lsdcommerce' ); ?></label>
                    <input class="form-input" type="text" name="account_holder" value="<?php echo $lsdc_method[$pointer]['account_holder']; ?>" placeholder="Lasida">
                </div>

                <div class="divider text-center" style="margin-top:25px;" data-content="<?php _e( 'Instruksi', 'lsdcommerce' ); ?>"></div>
                
                <div class="form-group">
                    <label class="form-label" for="instruction"><?php _e( 'Instruksi Pembayaran', 'lsdcommerce' ); ?></label>
                    <textarea class="form-input" name="instruction" placeholder="<?php _e( 'Silahkan lakukan pembayaran ke rekening ini sesuai dengan total pembelian.', 'lsdcommerce' ); ?>" lsdp-rows="3"><?php esc_attr_e( $lsdc_method[$pointer]['instruction'] ); ?></textarea>
                </div>
             </form>
        </div>

        <div class="panel-footer">
            <button class="btn btn-primary btn-block lsdc-payment-save" id="<?php echo $this->id; ?>_payment"><?php _e( 'Simpan', 'lsdcommerce' ); ?></button>
        </div>
    </div>
    <?php
    }
}
LSDC_BankCustomOne::init();
?>