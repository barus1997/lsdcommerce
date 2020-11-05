<?php
use LSDCommerce\Notification\LSDC_Notification;

/**
 * Class Notification via Email
 * Logging
 * Settings
 * Support Editor ( PRO Version )
 */
Class LSDC_Notification_Email Extends LSDC_Notification {
    public $id           = 'lsdcommerce_notification_email';
    public $name         = 'LSDCommerce';
    public $type         = 'email';
    public $doc_url      = 'https://docs.lsdplugins.com/docs/lsdcommerce-notifikasi-email/';
    public $mail         = null;

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
        parent::register('LSDC_Notification_Email'); //Self Register

        // add_action( 'wp_ajax_nopriv_lsdc_notification_email_test', array( $this, 'test' ) );
        // add_action( 'wp_ajax_lsdc_notification_email_test', array( $this,'test' ) );
        
        // Sending Email Notification
        add_action( 'lsdc_notification_hook', array( $this, 'notification_processing') ); // Hooking

        $this->mail = new LSDC_Mail( $this->id );
    }

    // Hook to Notification
    public function notification_processing( $obj )
    {
        if( parent::status( $this->id ) ) // Status True
        {
            $subject    = $obj['subject'] . ' - ' . get_bloginfo('name'); // Subject :: Data Source - SiteName
            $event      = isset($obj['notification_event']) ?  $obj['notification_event'] : '';
            $email      = isset($obj['email']) ? $obj['email'] : '';

            if( isset( $obj['role'] ) &&  $obj['role'] == 'admin' ){
                $template_path = LSDC_PATH . 'templates/emails/'. lsdc_get_store( 'country' ) .'-email-admin-' . $event . '.html';
            }else{
                $template_path = LSDC_PATH . 'templates/emails/'. lsdc_get_store( 'country' ) .'-email-customer-' . $event . '.html';
            }
           
            $data       = $this->processing( $obj );

            $this->mail->send( $email, $subject, $event, $template_path, $data );
        }
    }

    /**
     * Proccessing Data Order to EMail
     */
    public function processing( $obj ){

        $order_id       = abs( $obj['order_id'] );
        $order_number   = abs( $obj['order_number'] );
        $order_ip       = get_post_meta( $order_id, 'ip', true );
        $status         = get_post_meta( $order_id, 'status', true );
        $customer       = json_decode( get_post_meta( $order_id, 'customer', true ) );
        $products       = (array) json_decode( get_post_meta( $order_id, 'products', true ) );
        $extras         = json_decode( get_post_meta( $order_id, 'extras', true ) );
        $shipping       = (array) json_decode( get_post_meta( $order_id, 'shipping', true ) );
        $payment_data   = (array) json_decode( get_post_meta( $order_id, 'payment_data', true ) );
        $subtotal       = abs( get_post_meta( $order_id, 'subtotal', true ) );
        $total          = abs( get_post_meta( $order_id, 'total', true ) );

        $customer_name = $customer_phone = $customer_email = null;

        if( get_post_meta( $order_id, 'customer_id', true ) ){
            $customer_id = abs( get_post_meta( $order_id, 'customer_id', true ) );
            $customer_name = lsdc_get_user_name( $customer_id );
            $customer_phone = lsdc_get_user_phone( $customer_id );
            $customer_email = lsdc_get_user_email( $customer_id );
        }else{
            $customer = json_decode( get_post_meta( $order_id, 'customer', true ) );
            $customer_name = $customer->name;
            $customer_phone = $customer->phone;
            $customer_email = $customer->email;
        }

        $extra_list = array();
         if( $extras ) : 
            foreach ( $extras as $key => $item ) :
                if( $item->value ){
                    $extra_list[] = array(
                        'label' => esc_attr( $item->label ),
                        'bold'  => esc_attr( $item->bold ),
                        'sign'  => $item->sign == '-' ? esc_attr( $item->sign ) : '',
                        'value' => $item->value
                    );
                }
            endforeach; 
        endif;

        // Support Data
        $data = array(
            'site_url'          => get_site_url(),
            'site_logo'         => get_theme_mod( 'custom_logo' ) != false ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : 'https://lsdplugins.com/wp-content/uploads/2020/09/LSDCommerce.png',
            'site_name'         => get_bloginfo('name'),
            'order_number'      => $order_number,
            'total_part'        => str_replace( substr($total, -3), '', lsdc_currency_format( true, $total ) ),
            'total_last'        => $total != 0 ? substr($total, -3) : null,
            'product_list'      => $products,
            'extra_list'        => $extra_list,
            'subtotal'          => lsdc_currency_format( true, $subtotal),
            'total'             => lsdc_currency_format( true, $total),
            'customer_name'     => esc_attr( trim( $customer_name )  ),
            'customer_phone'    => esc_attr( $customer_phone ),
            'customer_email'    => esc_attr( $customer_email ),
            'free_order'        => $total != 0 ? false : true,      
            'illustration'      => LSDC_URL . 'assets/img/illustration.png'
            // 'shipping' => array(
            //     'physical' => array(
            //         'address' => esc_attr( $shipping->physical->address ),
            //         'city' => esc_attr( $cities[$shipping->physical->city]->type . ' ' . $cities[$shipping->physical->city]->province ),
            //         'state' => esc_attr( $states[$shipping->physical->state]->province ),
            //         'country' => 'Indonesia'
            //     ),
            //     'digital' => array(
            //         'address' => 'email@gmail.com' // phone number
            //     )
            // )
        );

  
        // if( $status == 'order' ){
            $data = array_merge_recursive( $data, $payment_data );
        // }
        return $data;
    
    }

    public function test(){
        if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );

        $email = sanitize_email( $_POST['email'] );
        $template_path = LSDC_PATH . 'templates/emails/email-testing.html';
        $this->mail->log( $email, 'test', __( 'Email test, please check your inbox', 'lsdcommerce' ) );

        if( $template_path && $email )
        {
           $this->mail->send( $email, 'LSDCommerce Email Test' );
        }
        else
        {
           $this->mail->log( $email, 'test', __( 'Email Template or Email Address not found', 'lsdcommerce' ) );
        }
        echo 'action_success';
        wp_die();
    }

    public function settings_manage(){ ?>
        <div class="tabs-wrapper">
            <!-- <input type="radio" name="email" id="lsdc_notification_email_log" > -->
            <!-- <label class="tab" for="lsdc_notification_email_log"><?php //_e( 'Log', 'lsdcommerce'  ); ?></label> -->

            <?php do_action( 'lsdcommerce_notification_email_tab'); ?>
            
            <input type="radio" name="email" id="lsdc_notification_email_settings" checked="checked"/>
            <label class="tab" for="lsdc_notification_email_settings"><?php _e( 'Pengaturan', 'lsdcommerce'  ); ?></label>

            <div class="tab-body-wrapper">
                 <!------------ Tab : Log ------------>
                <div id="log" class="tab-body">
                    <table class="table-log table table-striped table-hover">
                        <tbody>
                        <?php 
                            $db = get_option( $this->id );
                            $log = isset( $db['log'] ) ? $db['log'] : array();
                        ?>
                        <?php if( $log ) : ?>
                            <?php foreach ( array_reverse( $log ) as $key => $value) : ?>
                                <tr>
                                    <td><?php echo lsdc_date_format( $value[0], 'j M Y, H:i:s' ); ?></td>
                                    <td><?php echo $value[1]; ?></td>
                                    <td><?php echo $value[2]; ?></td>
                                    <td><?php echo $value[3]; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else:  ?>
                            <tr><td><?php _e( 'Empty Log...', 'lsdcommerce' ); ?></td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php do_action( 'lsdcommerce_notification_email_tab_content'); ?>

                <!------------ Tab : Settings ------------>
                <div id="settings" class="tab-body">
                    <!-- Content Pengaturan -->
                    <?php //var_dump( get_option('lsdcommerce_notification_email') ); ?>
                    <form class="form-horizontal" block="settings">

                        <!-- Sender -->
                        <div class="form-group">
                            <div class="col-3 col-sm-12">
                                <label class="form-label" for="country"><?php _e( 'Pengirim', 'lsdcommerce' ); ?></label>
                            </div>
                            <div class="col-9 col-sm-12">
                                <input class="form-input" type="text" name="sender" placeholder="LSDPlugins" style="width:320px" value="<?php echo $this->mail->get_sender(); ?>">
                            </div>
                        </div>

                        <!-- Sender Email -->
                        <div class="form-group">
                            <div class="col-3 col-sm-12">
                                <label class="form-label" for="country"><?php _e( 'Alamat Email Pengirim', 'lsdcommerce' ); ?></label>
                            </div>
                            <div class="col-9 col-sm-12">
                                <input class="form-input" type="email" name="sender_email" placeholder="info@lsdplugins.com" style="width:320px" value="<?php echo $this->mail->get_sender_email(); ?>">
                            </div>
                        </div>

                        <button class="btn btn-primary lsdc_admin_option_save" option="lsdcommerce_notification_email" style="width:120px"><?php _e( 'Simpan', 'lsdcommerce' ); ?></button> 
                    </form>
      
                    <!-- <div class="divider" data-content="Test Email"></div> -->
                    <!-- <div class="input-group" style="width:50%;">
                        <input id="lsdc_notification_email_address" style="margin-top:3px;" class="form-input input-md" type="email" placeholder="test.email@gmail.com">
                        <button id="lsdc_notification_email_test" style="margin-top:3px;" class="btn btn-primary input-group-btn"><?php// _e( 'Test Email', 'lsdcommerce' ); ?></button>
                    </div> -->
                </div>
            </div>

        </div>

        <style>
            /* Action Tab */
            #lsdc_notification_email_log:checked~.tab-body-wrapper #log,
            #lsdc_notification_email_settings:checked~.tab-body-wrapper #settings {
                position: relative;
                top: 0;
                opacity: 1;
            }
        </style>

        <script>
            // On User Sending Test Email
            jQuery(document).on("click","#lsdc_notification_email_test",function( e ) {
                var tested_email = jQuery('#lsdc_notification_email_address').val();

                if( validateEmail( tested_email ) && tested_email != '' ){
                    jQuery(this).addClass('loading');
                    jQuery('#lsdc_notification_email_address').css('border', 'none');
                    
                    jQuery.post( lsdc_adm.ajax_url, { 
                        action : 'lsdcommerce_notification_email_test',
                        email  : tested_email,
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
                    jQuery('#lsdc_notification_email_address').css('border', '1px solid red');
                }
            });
        </script>
    <?php
    }

}
LSDC_Notification_Email::init();
?>