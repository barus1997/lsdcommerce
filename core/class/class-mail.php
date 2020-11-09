<?php 
// Require Mustache for Templating
require LSDC_PATH . 'assets/lib/mustache/Autoloader.php';
Mustache_Autoloader::register();

/**
 * Sending Email with Custom Template
 * set Template, Subject, Receiver and Data
 * Automatic Processing Log and Test
 */
Class LSDC_Mail
{
    private $subject    = null;
    private $receiver   = null;
    private $data       = null;
    private $template   = null;
    private $option     = null;

    public function __construct( $id )
    {
      // Set Property based on Settings
      $this->option         = esc_attr( $id );
      $this->settings       = get_option( $this->option );
      $this->sender         = $this->settings['settings']['sender'];
      $this->sender_email   = $this->settings['settings']['sender_email'];

      // Handle Email Status
    //   add_filter( 'wp_mail', array( $this, 'on_wpmail' ));
    //   add_action( 'wp_mail_failed', array( $this,'on_wpmail_failed' ));
        add_filter( 'wp_mail_content_type', array( $this,'email_html_format') );
    }
    public function email_html_format(){
        return "text/html";
    }
    // Get Sender Name
    public function get_sender()
    {
        return esc_attr( $this->sender );
    }

    // Get Sender Email
    public function get_sender_email()
    {
        return sanitize_email( $this->sender_email );
    }

    // Logging Email Delivery Status, Empty If Reach 30 Data
    public function log( $receiver, $event, $message )
    {
        $option = get_option( $this->option );
        $log = isset( $option['log'] ) ? $option['log'] : array(); 
        if( $reciever == null ) $reciever = __( "Empty Receiver", 'lsdcommerce' );
        // Auto Reset Log on Reach 30 Item
        if( count( $log ) >= 30 ) $log = array(); 

        $log[] = array( lsdc_date_now(), $reciever, $event, $message ); // Push New Log
        $option['log'] = $log; // Set Log
        update_option( $this->option, $option ); // Saving Log
    }

    // Load on wpmail() function trigger and save to log
    public function on_wpmail( $message ) 
    {
        $this->log( $message["to"], $message["subject"],  __( 'Email was sending...', 'lsdcommerce' ) );
        return $args;
    }

    // Load on wpmail() function failed and save to log
    public function on_wpmail_failed( $error ) 
    {
        $message        = $error->error_data['wp_mail_failed'];
        $message_error  = $error->errors['wp_mail_failed'][0];
        $this->log( $message['to'][0],  $message['subject'],  'Email has failed : '. $message_error );
    }

    // Templating Function
    public function send( $email, $subject, $event, $path, $args )
    {
        if( ! $args ){
            $this->log( '-', '[INFO]', __( 'Template Data not found', 'lsdcommerce' ) );
        }
        // Checking Template Exist or Not
        if( ! file_exists( $path ) ){
            $this->log( '-', '[INFO]', __( 'Email Template not found', 'lsdcommerce' ) );
        }

        if( $path && $args ){
            // Processing
            $mustache = new Mustache_Engine();
            $template = file_get_contents( $path );
            $this->template = $mustache->render( $template, $args );
            $this->post( $email, $subject );
        }
    }

    // Sending email via wp_mail()
    public function post( $email, $subject ){
        $headers[] = 'From: '. $this->sender  .' <'. $this->sender_email .'>';
        $headers[] = 'Content-Type: text/html; charset="' . get_option( 'blog_charset' ) . '"';

        $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title>'. $subject .'</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=edge" /><meta name="viewport" content="width=device-width, initial-scale=1.0 " /><meta name="format-detection" content="telephone=no" />';
        $message .= '<!--[if !mso]>--><meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]--><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
        $message .= $this->template;
        $message .= '</html>';
        wp_mail( $email, $subject, $message, $headers );
    }
}


/* Usage */
// $mail_object = new LSDC_Mail( 'lsdcommerce_shipping_mail' );
// $mail_object->send( 'lasidaziz@gmail.com', 'Subject Email', $path, $data );
?>