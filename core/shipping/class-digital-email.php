<?php 
use LSDCommerce\Shipping\LSDC_Shipping;

/**
 * Class Shipping via Email
 * for Digital Product
 */
Class LSDC_Shipping_Email Extends LSDC_Shipping {
    public $id       = 'lsdcommerce_shipping_email';
    public $name     = 'Email';
    public $type     = 'digital';
    public $doc_url  = 'https://docs.lsdplugins.com/docs/lsdcommerce-pengiriman-via-email/';
    public $country  = 'GLOBAL';
    private $mail    = null;

    private static $instance;

    public static function init()
    {
      if ( is_null( self::$instance ) )
      {
        self::$instance = new self();
      }
      parent::register( 'LSDC_Shipping_Email' );
      return self::$instance;
    }


    public function __construct() {
        // add_action( 'wp_ajax_nopriv_lsdc_shipping_email_test', array( $this, 'shipping_email_test' ) );
        // add_action( 'wp_ajax_lsdc_shipping_email_test', array( $this,'shipping_email_test' ) );

        add_action( 'lsdcommerce_shipping_hook', array( $this, 'shipping_processing') );
        $this->mail = new LSDC_Mail( $this->id );
    }

    // public function shipping_email_test(){
    //     if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

    //     $email = sanitize_email( $_POST['email'] );
    //     $template_path = LSDC_PATH . 'templates/emails/email-testing.html';
    //     parent::log( $this->id, $email, $event, __( 'Email Shipping test, please check your inbox', 'lsdcommerce' ) );

    //     if( $template_path && $email )
    //     {
    //         $this->send_email( $email, 'LSDCommerce Email Shipping Test', $template_path , 'test' );
    //     }
    //     else
    //     {
    //         parent::log( $this->id, $email, $event, __( 'Email Template not found', 'lsdcommerce' ) );
    //     }

    //     echo 'action_success';
    //     wp_die();
    // }
    

    public static function get_name(){
        return $this->name;
    }

    public static function get_country(){
        return $this->country;
    }

    // Hook to Notification
    public function shipping_processing( $obj )
    {
        if( parent::get_status( $this->id ) ) // Status True
        {
            $subject    = $obj['subject'] . ' - ' . get_bloginfo('name'); // Subject :: Data Source - SiteName
            $type       = isset( $obj['type'] ) ?  $obj['type'] : '';
            $email      = isset( $obj['email'] ) ? $obj['email'] : '';
            $path       = LSDC_PATH . 'templates/emails/'. lsdc_get_store( 'country' ) .'-email-shipping-' . $type . '.html';

            $data       = $this->processing( $obj );

            $this->mail->send( $email, $subject, $event, $path, $data );
        }
    }

    public function processing( $obj ){

        $order_id       = abs( $obj['order_id'] );
        $order_number   = get_post_meta( $order_id, 'order_number', true );
        $customer_id    = abs( get_post_meta( $order_id, 'customer_id', true ) );
        $products       = (array) json_decode( get_post_meta( $order_id, 'products', true ) );


        $product_downloads = array();
        foreach ($products as $key => $value) {
            $item = (array) $value;
            $product_downloads[$key]['thumbnail'] = $item['thumbnail'];
            $product_downloads[$key]['title'] = $item['title'];
            $product_downloads[$key]['thumbnail'] = $item['thumbnail'];
            $product_downloads[$key]['download_link'] = lsdc_product_download_link( $item['id'] );
            $product_downloads[$key]['download_version'] = lsdc_product_download_version( $item['id'] );

            /* Pro Code */
            if( isset( $item['variations'] ) ){
                $product_downloads[$key]['variations'] = $item['variations'];
            }
            /* Pro Code */
        }

        $user_info = get_userdata( $customer_id );

        // Data
        $data = array(
            'order_number'          => $order_number,
            // 'site_logo'             => 'https://lasida-demo.now.sh/assets/img/logo.png',
            'member_label'          => __('Member Area : ', 'lsdcommerce'),
            'member_link'           => get_permalink( lsdc_get( 'general_settings', 'member_area') ),
            'member_text'           => __('Masuk', 'lsdcommerce'),
            'member_username_label' => __( 'Username : ', "lsdcommerce" ),
            'member_username'       => $user_info->user_login . '/' . lsdc_user_getemail( $customer_id ),
            'product_list'          => $product_downloads,
        );

        return $data;
    
    }

    public function manage()
    { ?>
          <div class="tabs-wrapper">
            <!-- <input type="radio" name="<?php //echo $this->id; ?>" id="lsdc_shipping_email_log" >
            <label class="tab" for="lsdc_shipping_email_log"><?php //_e( 'Log', 'lsdcommerce' ); ?></label> -->

            <input type="radio" name="<?php echo $this->id; ?>" id="lsdc_shipping_email_settings" checked="checked"/>
            <label class="tab" for="lsdc_shipping_email_settings"><?php _e( 'Settings', 'lsdcommerce' ); ?></label>

            <div class="tab-body-wrapper">
                 <!------------ Tab : Log ------------>
                <div id="lsdc_shipping_email_log_content" class="tab-body">
                    <table class="table-log table table-striped table-hover">
                        <tbody>
                        <?php 
                            $db = get_option( $this->id );
                            $log = isset( $db['log'] ) ? $db['log'] : array();
                        ?>
                        <?php if( $log ) : ?>
                            <?php foreach ( array_reverse( $log ) as $key => $value) : ?>
                                <tr>                                    <td><?php echo lsdc_date_format( $value[0], 'j M Y, H:i:s' ); ?></td>
                                    <td><?php echo $value[1]; ?></td>
                                    <td><?php echo $value[2]; ?></td>
                                    <td><?php echo $value[3]; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else:  ?>
                            <tr><td><?php _e( 'Empty Log', 'lsdcommerce' ); ?></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
 
                <!------------ Tab : Settings ------------>
                <div id="lsdc_shipping_email_settings_content" class="tab-body">
                    <!-- Content Pengaturan -->
                    <form class="form-horizontal" block="settings">

                        <!-- Sender -->
                        <div class="form-group">
                            <div class="col-3 col-sm-12">
                                <label class="form-label" for="country"><?php _e( 'Sender', "lsdcommerce" ); ?></label>
                            </div>
                            <div class="col-9 col-sm-12">
                                <input class="form-input" type="text" name="sender" placeholder="LSDPlugins" style="width:320px" value="<?php echo $this->mail->get_sender(); ?>">
                            </div>
                        </div>

                        <!-- Sender Email -->
                        <div class="form-group">
                            <div class="col-3 col-sm-12">
                                <label class="form-label" for="country"><?php _e( 'Sender Email', "lsdcommerce" ); ?></label>
                            </div>
                            <div class="col-9 col-sm-12">
                                <input class="form-input" type="email" name="sender_email" placeholder="shipping@lsdplugins.com" style="width:320px" value="<?php echo $this->mail->get_sender_email(); ?>">
                            </div>
                        </div>

                        <button class="btn btn-primary lsdc_admin_option_save" option="<?php echo $this->id; ?>" style="width:120px"><?php _e( 'Save', "lsdcommerce" ); ?></button> 
                    </form>
      
                    <!-- <div class="divider" data-content="Test Email"></div>
                    <div class="input-group" style="width:50%;">
                        <input id="lsdc_shipping_email_address" style="margin-top:3px;" class="form-input input-md" type="email" placeholder="email.test@gmail.com">
                        <button id="lsdc_shipping_email_test" style="margin-top:3px;" class="btn btn-primary input-group-btn"><?php //_e( 'Test Email', "lsdcommerce" ); ?></button>
                    </div> -->
                </div>
            </div>

        </div>

        <style>

            /* Action Tab */
            #lsdc_shipping_email_log:checked~.tab-body-wrapper #lsdc_shipping_email_log_content,
            #lsdc_shipping_email_settings:checked~.tab-body-wrapper #lsdc_shipping_email_settings_content {
                position: relative;
                top: 0;
                opacity: 1
            }
        </style>

        <script>
            // On User Sending Test Email
            jQuery(document).on("click","#lsdc_shipping_email_test",function( e ) {
                var email_fortest = jQuery('#lsdc_shipping_email_address').val();

                if( validateEmail( email_fortest ) && email_fortest != '' ){
                    jQuery(this).addClass('loading');
                    jQuery('#lsdc_shipping_email_address').css('border', 'none');
                    
                    jQuery.post( lsdc_adm.ajax_url, { 
                        action : 'lsdc_shipping_email_test',
                        email  : email_fortest,
                        security : lsdc_adm.ajax_nonce,
                        }, function( response ){
                            if( response.trim() == 'action_success' ){
                                location.reload();
                            }
                        }).fail(function(){
                            alert('Failed, please check your internet');
                        }
                    );

                }else{
                    jQuery('#lsdc_shipping_email_address').css('border', '1px solid red');
                }
            });
        </script>
    <?php
    }
}

LSDC_Shipping_Email::init();
?>