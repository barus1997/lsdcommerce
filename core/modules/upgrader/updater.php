<?php 
/**
 * @package     LSDCommerce
 * @subpackage  License
 * 
 * Checking Plugin Update Every 6 hours this code for client
 * 
 * @since    1.0.0
 */
define( 'LSDC_SERVER', 'http://tester.lsdplugins.com/api' );

Class LSDCommerce_Updater {
    protected $id              = 'lsdcommerce';
    protected $product_key     = 'ba03a2f215d1bdf708219d8740b04a2c';
    protected $product_slug    = array( 'lsdcommerce' );
    protected $license_api     = LSDC_SERVER;
    protected $current_version = LSDC_VERSION;

    public function __construct() {
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
            $remote = wp_remote_get( $this->license_api . '/v1/product/updates/' . $this->product_key, array( 
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
        // delete_transient( $this->id . '_update' );
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
