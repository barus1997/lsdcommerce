<?php 
/**
 * @package     LSDCommerce
 * @subpackage  License
 * 
 * Checking Plugin Update Every 6 hours this code for client
 * 
 * @since    1.0.0
 */


Class LSDCommerce_Updater {
    protected $id              = 'lsdcommerce';
    protected $product_key     = '79dfd32ea504a119ad24c31cc11de5a3';
    protected $product_slug    = array( 'lsdcommerce' );
    protected $license_api     = 'http://play.lsdplugins.com/wp-json';
    protected $current_version = LSDCOMMERCE_VERSION;

    public function __construct() {
        // Adding Plugin Info on Update Available
        add_action( 'upgrader_process_complete', array( $this, 'destroy_update' ), 10, 2 );
        add_action( 'in_plugin_update_message-'. $this->id .'/'. $this->id .'.php', array( $this, 'fatal_update' ), 10, 2 );

       
    }

    public function check_update(){
        add_filter('site_transient_update_plugins', array( $this, 'new_update' ) );
        add_filter('transient_update_plugins', array( $this, 'new_update' ) );
        add_filter('plugins_api', array( $this, 'plugin_info' ), 20, 3);
        // delete_transient( $this->id . '_update' );
    }

    public function new_update( $transient ){
        // Checking Transient, Empty -> Using Transient
        if ( empty( $transient->checked ) ) return $transient;
        if ( ! is_object( $transient ) ) return $transient;


        //Get Transient & Information Update
        if( false == $remote = get_transient( $this->id . '_update' ) ) { // If Transient Empty and License key available

            // Remote GET
            $remote = wp_remote_get( $this->license_api . '/v1/lsdcommerce/product/updates/' . $this->product_key, array( 
                'timeout' => 30,
                'headers' => array(
                    'Accept' => 'application/json'
                ))
            );
     

            if ( ! is_wp_error( $remote ) ) { // WP Error Causing CURL
                $remote = json_decode( $remote['body'] );
            }else{
                set_transient( $this->id . '_update', 'failed_get_update', 300 ); // Waiting 5 minutes
            }

            //Get Response Body
            if ( ! is_wp_error( $remote ) && isset( $remote->slug ) && in_array( $remote->slug, $this->product_slug ) ) {
                set_transient( $this->id . '_update', $remote, 60 * 60 * 6 ); // 6 hours cache
            }else{
                set_transient( $this->id . '_update', 'failed_get_update', 60 ); // 6 hours cache
            }
        }

        // Debug
        //  delete_transient( $this->id . '_update' );
        // var_dump( $remote );

        // Processing Update 
        if( get_transient( $this->id . '_update' ) != 'failed_get_update' ) {
            $remote = get_transient( $this->id . '_update' );

            if ( ! is_wp_error( $remote )) :
                $plugin_data        = get_plugin_data( LSDC_FILE );
                $plugin_version     = $plugin_data['Version'];  
                
                if( $remote && version_compare( $this->current_version, $remote->version, '<' ) ) { //change this if want to update
         
                    $res                                = new stdClass();
                    $res->slug                          = $this->id;
                    $res->plugin                        = $this->id .'/' . $this->id . '.php';
                    $res->new_version                   = $remote->version;
                    $res->tested                        = $remote->tested;
                    if( isset($remote->download_url) ){
                        $res->package = $remote->download_url;
                    } 
                    $transient->response[$res->plugin] = $res;

                }
            endif;
        }

        return $transient;
        
    }

    public function plugin_info( $res, $action, $args ){

    
        if( $action !== 'plugin_information' ) return false;
        if( get_transient( $this->id . '_update' ) == 'failed_get_update' ) return false;
     
        $remote = get_transient( $this->id . '_update' ) ;
      
        if( !is_wp_error( $remote ) && $args->slug == $this->id ) {
            $res                    = new stdClass();
            $res->name              = $remote->name;
            $res->slug              = $this->id;
            $res->version           = $remote->version;
            $res->tested            = $remote->tested;
            $res->requires          = $remote->requires;
            $res->author            = $remote->author; // I decided to write it directly in the plugin
            $res->author_profile    = $remote->author_profile; // WordPress.org profile
            if( isset($remote->download_url) ){
                $res->download_link = $remote->download_url; 
                $res->trunk = $remote->download_url;
            }
            $res->last_updated      = $remote->last_updated;
            $sections               = $remote->sections[0];
            $res->sections          = array( 
                                        'description'   => $sections->description, // description tab
                                        'installation'  => $sections->installation, // installation tab
                                        'changelog'     => $sections->changelog );
            $res->banners           = array( 
                                        'low'   => $remote->low_image,
                                        'high'  => $remote->high_image );
            
        }

        return $res;
    }

    public function destroy_update( $upgrader_object, $options ){
        if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
            delete_transient( $this->id . '_update' );
        }
    }

    public function fatal_update( $dataserver, $plugin_info_object ){
        if( empty( $dataserver['package'] ) ) {
            printf( __( 'Automatic Update is failed, please %s download %s it manually', 'lsdd' ), '<a href="https://lsdplugins.com/lsdcommerce/" target="_blank">', '</a>' );
        }
    }

}

function lsdcommerce_plugin_updater() {

    if ( is_admin() ) { 
        $update = new LSDCommerce_Updater(); 
        $update->check_update();
    }

}
add_action('plugins_loaded', 'lsdcommerce_plugin_updater');


/**
 * Initzialze Tracking
 * Fired : When Plugin Active and Empty Track Data
 */
function lsdc_track_init(){
    global $wpdb;
    $site_usage = get_option( plugin_basename( LSDC_PATH ) . '_site_usage', true );
    if( empty( $site_usage )  ){
        $theme = wp_get_theme();
        $domain = str_replace( ".","_", parse_url(get_site_url())['host']);
        $site_usage[$domain] = array(
            'server' => $_SERVER['SERVER_SOFTWARE'],
            'server_php_version' => phpversion(),
            'server_mysql_version' => $wpdb->db_version(),
            'wp_version' => get_bloginfo( 'version' ),
            'wp_memory_limit' => WP_MEMORY_LIMIT,
            'wp_max_upload' => ini_get('upload_max_filesize'),
            'wp_permalink' => get_option( 'permalink_structure' ),
            'wp_multisite' => is_multisite(),
            'wp_language' => get_bloginfo( 'language' ),
            'wp_theme' => $theme->get( 'Name' ),
            'wp_plugins' => '',
            'site_url' => get_bloginfo( 'url' ),
            'site_email' => get_bloginfo( 'admin_email' ),
            'plugin_usage' => array(
                'plugin' => plugin_basename( LSDC_PATH ),
                'active' => is_plugin_active( plugin_basename( LSDC_PATH ) . '/'.  plugin_basename( LSDC_PATH ) .'.php' ),
                'active_day' => array(), 
                'updated' => 0,
                'version' => LSDCOMMERCE_VERSION,
                'storage' => is_dir( LSDC_CONTENT ),
                'translation' => false
            )
        );
        update_option( plugin_basename( LSDC_PATH ) . '_site_usage', $site_usage );
    }
}

/**
 * Function to Updating Update Log
 * Fired : When plugin update
 */
function lsdc_track_updated(){
    $domain = str_replace( ".","_", parse_url(get_site_url())['host']);
    $site_usage = get_option( plugin_basename( LSDC_PATH ) . '_site_usage', true );
    $old = $site_usage[$domain]['plugin_usage']['updated'];

    if( is_array( $old ) ) {
        if( ! in_array( LSDCOMMERCE_VERSION, $old ) ){
            array_push( $old, LSDCOMMERCE_VERSION );
        }
    }else{
        $old = array( LSDCOMMERCE_VERSION );
    }
    $site_usage[$domain]['plugin_usage']['updated'] = $old; // Updating Data Active
    update_option( plugin_basename( LSDC_PATH ) . '_site_usage', $site_usage );
    lsdc_track_push();
}


/**
 * Function to Track Active Day based on Daily Check
 * Trigger by Cron
 */
function lsdc_track_activeday(){
    $domain = str_replace( ".","_", parse_url(get_site_url())['host']);
    $site_usage = get_option( plugin_basename( LSDC_PATH ) . '_site_usage', true );
    $old = abs( $site_usage[$domain]['plugin_usage']['active_day'] );
    $site_usage[$domain]['plugin_usage']['active_day'] = $old + 1; // Updating Data Active
    update_option( plugin_basename( LSDC_PATH ) . '_site_usage', $site_usage );
}

/**
 * Function to get random hoour today, for cron fired
 * @return Date with Random Hours
 */
function lsdc_date_randomhour_today(){
    // Convert to timetamps
    $min = strtotime( lsdc_date_now() ); // Now
    $max = strtotime( lsdc_date_format( lsdc_date_now(), 'Y-m-d' ) . ' ' . date("H:i:s", mktime(23, 59, 0)) ); // Today untul 23.59:00
    // Generate random number using above bounds
    $val = rand($min, $max);
    // Convert back to desired date format
    return date('Y-m-d H:i:s', $val);
}


/**
 * Function to Push Track to Server Usage
 * Fired when Cron Execution or Event Fired
 */
function lsdc_track_push(){
    $domain = str_replace( ".","_", parse_url(get_site_url())['host']);
    $body	= get_option( plugin_basename( LSDC_PATH ) . '_site_usage', true );

    $headers = array(
        'Content-Type'  => 'application/json',
    );

    $payload = array(
        'method' 		=> 'POST',
        'timeout' 		=> 30,
        'headers'     	=> $headers,
        'httpversion'	=> '1.0',
        'sslverify' 	=> false,
        'body' 			=> json_encode($body),
        'cookies' 		=> array()
    );

    if( $domain != 'localhost' ){
        // LSDC_SERVER
        $response = wp_remote_post('https://play.lsdplugins.com/wp-json/v1/usages/', $payload);
        $response = json_decode(wp_remote_retrieve_body( $response ), TRUE );
    }
    return $response;
}