<?php 
namespace LSDCommerce\Notification;

/**
 * What : Parent Class Notification
 * When : Object Notification Create
 * 
 * initiate Class via global variable trhough init action
 */
class LSDC_Notification{

    public static function register( $class )
    {
        global $lsdcommerce_notification;

        if ( !in_array( $class, (array) $lsdcommerce_notification ) )
        $lsdcommerce_notification[] = esc_attr( $class );
    }

    public static function release( $class )
    {
        global $lsdcommerce_notification;
    
        if ( in_array( $class, (array) $lsdcommerce_notification ) ) // if metode found
        unset( $lsdcommerce_notification[$class] );
    }

    public function settings_header()
    { 
        $notification_status = get_option( 'lsdcommerce_notification_status' );
        
        if( ! isset( $notification_status[ $this->id ] ) ) $notification_status[ $this->id ] = 'off';
        $status = $notification_status[ $this->id ] == 'on' ? 'on' : 'off'; ?>
        
        <div class="lsdc-notification-status">
            <h5>
                <?php echo $this->name; ?>
                <a style="margin-left:10px; border-radius: 20px;padding: 5px 25px;" href="<?php echo $this->doc_url; ?>" target="_blank" class="btn btn-primary"><?php _e( 'Panduan', 'lsdcommerce' ); ?></a>
            </h5>
            <div class="form-group">
                <label class="form-switch">
                <input type="checkbox" id="<?php echo $this->id . '_status'; ?>" <?php echo ( $status == 'on' ) ? 'checked' : ''; ?>>
                    <i class="form-icon"></i> <?php _e( 'Enable ', 'lsdcommerce' ); ?><?php echo $this->name; ?>
                </label>
            </div>
        </div>
    <?php
    }

    public function settings_tab(){ ?>
        <input type="radio" name="sections" id="<?php echo $this->id; ?>">
        <label data-linking="<?php echo $this->id; ?>" class="tablabel form-switch" for="<?php echo $this->id; ?>">
            <span><?php echo ucfirst( $this->type ); ?></span>
            <p><?php echo $this->name; ?></p>
        </label>
    <?php 
    }

    /**
     * What : Main Function to Sending Notification
     * When : Every want to Sending Notification
     * @param array $order
     * @param string $event ( order | complete | cancel | )
     * 
     */
    public static function sender( $object ){
        // Hook for multiple notification
        do_action( 'lsdc_notification_hook', $object );
    }

    // Get status by type
    public static function status( $notification_id ){
        return isset( get_option( 'lsdcommerce_notification_status' )[$notification_id] ) ? get_option( 'lsdcommerce_notification_status' )[$notification_id] == 'off' ? false : true : false;
    }

    // Saving Log Notification by Notification ID
    public static function log( $notification_id, $reciever, $event, $message ){
        $db = get_option( $notification_id ); /// Get Log
        $log = isset( $db['log'] ) ? $db['log'] : array(); // Check Log
        if( count($log) >= 30 ) $log = array(); // Auto Reset Log on Reach 30 Item

        $log[] = array( lsdc_date_now(), $reciever, $event, $message); // Push New Log
        $db['log'] = $log; // Set Log
    
        update_option( $notification_id, $db ); // Saving Log
    }
}
?>