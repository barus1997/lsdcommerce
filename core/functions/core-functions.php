<?php
/**
* Class and Function List:
* Function list:
* - lsdc_currency_format()
* - lsdc_currency_clean()
* - lsdc_currency_view()
* - lsdc_currency_get()
* - lsdc_date_now()
* - lsdc_date_format()
* - lsdc_date_diff()
* - lsdc_price_normal()
* - lsdc_price_discount()
* - lsdc_price_frontend()
* - lsdc_admin_get()
* - lsdc_admin_set()
* - lsdc_clean_id()
* - lsdc_clean_number()
* - lsdc_create_schedule()
* - set_lsdcommerce()
* - is_lsdcommerce()
* - lsdc_pro()
* Classes list:
*/

/**
 * Currency Formatting
 *
 * @package Core
 * @subpackage Currency
 * @since 1.0.0
 *
 * Plain to Format || 10000 -> Rp 10.000
 */
function lsdc_currency_format($symbol = true, $float, $curr = "IDR")
{
    $c['IDR'] = array(
        0, // Extra Digit 100,00
        ',', // Separator Extra
        '.', // Separator 3 Digit
        'Rp '// Currrency Symbol
    );
    $c['USD'] = array(
        0,
        '.',
        ',',
        '$'
    );
    if (abs($float) == 0)
    {
        return __("Gratis", 'lsdcommerce');
    }
    else
    {
        if ($symbol == false)
        {
            return number_format($float, $c[$curr][0], $c[$curr][1], $c[$curr][2]); //10000-> Rp 10.000
            
        }
        else
        {
            return $c[$curr][3] . number_format($float, $c[$curr][0], $c[$curr][1], $c[$curr][2]); //10000-> Rp 10.000
            
        }
    }
}

/**
 * Clean Currency Formatting
 *
 * @package Core
 * @subpackage Currency
 * @since 1.0.0
 *
 * Format to Plain || Rp 10.000 --> 100000
 */
function lsdc_currency_clean($formatted_number)
{
    $formatted_number = preg_replace('/[^0-9]/', '', $formatted_number);
    $formatted_number = preg_replace('/\,/', '', $formatted_number);
    return abs(preg_replace('/\./', '', $formatted_number)); // Rp 10.000 -> 10000
    
}

/**
 * Currency Placeholder
 *
 * @package Core
 * @subpackage Currency
 * @since 1.0.0
 */
function lsdc_currency_view($type = 'symbol')
{
    $currency = array(
        'IDR' => array(
            'symbol' => 'Rp ',
            'format' => '15.000'
        ) ,
        'USD' => array(
            'symbol' => '$',
            'format' => '1'
        ) ,
    );
    echo $currency[lsdc_currency_get() ][$type];
}

/**
 * get currency based on store settings
 *
 * @package Core
 * @subpackage Currency
 * @since 1.0.0
 */
function lsdc_currency_get()
{
    $settings = get_option('lsdcommerce_store_settings', true);
    return isset($settings['lsdc_store_currency']) ? esc_attr($settings['lsdc_store_currency']) : 'IDR';
}

/**
 * get date now
 *
 * @package Core
 * @subpackage Date
 * @since 1.0.0
 */
function lsdc_date_now()
{
    return date('Y-m-d H:i:s', current_time('timestamp', 0));
}

/**
 * get date with formatting
 *
 * @package Core
 * @subpackage Date
 * @since 1.0.0
 */
function lsdc_date_format($str, $format = 'j M Y')
{
    return date($format, strtotime($str));
}

/**
 * get date diff
 *
 * @package Core
 * @subpackage Date
 * @since 1.0.0
 */
function lsdc_date_diff($date1, $date2)
{
    $diff = strtotime($date2) - strtotime($date1);
    return abs(round($diff / 86400));
}

/**
 * get price normal
 *
 * @package Core
 * @subpackage Price
 * @since 1.0.0
 */
function lsdc_price_normal($product_id = false)
{
    return abs(get_post_meta($product_id, '_price_normal', true));
}

/**
 * get price discount
 *
 * @package Core
 * @subpackage Price
 * @since 1.0.0
 */
function lsdc_price_discount($product_id = false)
{
    return abs(get_post_meta($product_id, '_price_discount', true));
}

/**
 * get price frontend based on prioritize price discount
 *
 * @package Core
 * @subpackage Price
 * @since 1.0.0
 */
function lsdc_price_frontend($product_id = false)
{
    if ($product_id == null) $product_id = get_the_ID(); //Fallback Product ID
    $normal = lsdc_price_normal($product_id);
    $discount = lsdc_price_discount($product_id);

    if ($discount): ?>
        <span class="product-item-price-discount">
            <?php echo lsdc_currency_format(true, get_post_meta(get_the_ID() , '_price_normal', true)); ?>
        </span> 
        <span class="product-price product-item-price-normal discounted">
            <?php echo lsdc_currency_format(true, get_post_meta(get_the_ID() , '_price_discount', true)); ?>
        </span>
    <?php
    else: ?>
        <?php if ($normal): ?>
        <span class="product-price product-item-price-normal">
            <?php echo lsdc_currency_format(true, get_post_meta(get_the_ID() , '_price_normal', true)); ?>
        </span>
        <?php
        else: ?>
            <span class="product-item-price-normal">
                <?php _e("Free", 'lsdcommerce'); ?>
            </span>
        <?php
        endif; ?>
    <?php
    endif;
}

/**
 * Getting Admin Setting by Option and Item
 *
 * @package Core
 * @subpackage Admin
 * @since 1.0.0
 *
 * Usage ::  get_the_permalink( lsdc_admin_get( 'general_settings', 'checkout_page' ) );
 */
function lsdc_admin_get($option, $item)
{
    $settings = get_option('lsdc_' . $option);
    return empty($settings[$item]) ? null : esc_attr($settings[$item]);
}

/**
 * Setting Admin Setting by Option and Item
 *
 * @package Core
 * @subpackage Admin
 * @since 1.0.0
 *
 * Usage ::  get_the_permalink( lsdc_admin_get( 'general_settings', 'checkout_page' ) );
 */
function lsdc_admin_set($option, $item, $value)
{
    $settings = empty(get_option('lsdc_' . $option)) ? array() : get_option('lsdc_' . $option);
    $settings[$item] = sanitize_text_field($value);
    update_option('lsdc_' . $option, $settings);
}

/**
 * Clean ID
 *
 * @package Core
 * @subpackage Clean
 * @since 1.0.0
 */
function lsdc_clean_id($string)
{
    return sanitize_title(strtolower(preg_replace("/[^a-z0-9]+/i", "-", $string)));
}

/**
 * Clean Number
 *
 * @package Core
 * @subpackage Clean
 * @since 1.0.0
 */
function lsdc_clean_number($formatted_number)
{
    $formatted_number = preg_replace('/[^0-9]/', '', $formatted_number);
    $formatted_number = preg_replace('/\,/', '', $formatted_number);
    return abs(preg_replace('/\./', '', $formatted_number)); // Rp 10.000 -> 10000 || 1.000 kg -> 1000
    
}

/**
 * Set LSDCommerce Page with Class lsdcommerce
 *
 * @package Core
 * @subpackage General
 * @since 1.0.0
 * Note : Not Working on Shortcode
 */
function set_lsdcommerce($page = false)
{
    global $lsdcommerce;
    if ($page != false)
    {
        $lsdcommerce['page'] = $page;
    }
    else
    {
        $lsdcommerce['page'] = 'lsdcommerce';
    }

}

/**
 * Conditionals Tags LSDCommerce Page
 *
 * @package Core
 * @subpackage General
 * @since 1.0.0
 */
function is_lsdcommerce($page = false)
{
    global $lsdcommerce;
    if ($page != false)
    {
        if (isset($lsdcommerce['page']) && $lsdcommerce['page'] == $page) return true;
    }
    else
    {
        if (isset($lsdcommerce['page'])) return true;
    }

    return false;
}

/**
 * Checking LSDCommerce Pro Exist
 *
 * @package Core
 * @subpackage General
 * @since 1.0.0
 */
function lsdc_pro()
{
    if (is_plugin_active('lsdcommerce-pro/lsdcommerce-pro.php'))
    {
        return true;
    }else{
        return false;
    }
}


/**
 * Initzialze Tracking
 * Fired : When Plugin Active and Empty Track Data
 */
function lsdc_track_init(){
    global $wpdb;
    $site_usage = get_option( plugin_basename( LSDC_PATH ) . '_site_usage', true );

    if( empty( $site_usage ) || ! isset( $site_usage['server'] ) ){
        $theme = wp_get_theme();
        $domain = str_replace( ".","_", parse_url(get_site_url())['host']);

        $site_usage = array();
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
                'active' => true,
                'active_day' => 0, 
                'updated' => 0,
                'version' => LSDC_VERSION,
                'storage' => is_dir( LSDC_CONTENT ),
                'translation' => false
            )
        );
        update_option( plugin_basename( LSDC_PATH ) . '_site_usage', $site_usage );
    }

    lsdc_track_act();
}


/**
 * Function to Updating Track
 * On Active or Deactive
 */
function lsdc_track_act(){
    $site_usage = get_option( plugin_basename( LSDC_PATH ) . '_site_usage' );
    $domain = str_replace( ".","_", parse_url(get_site_url())['host']);

    if( isset( $site_usage[$domain] ) ){
        $site_usage[$domain]['plugin_usage']['active'] = ! is_plugin_active( plugin_basename( LSDC_PATH ) . '/'.  plugin_basename( LSDC_PATH ) .'.php' );
        update_option( plugin_basename( LSDC_PATH ) . '_site_usage', $site_usage );
    }
    lsdc_track_push();
}

/**
 * Function to Updating Update Log
 * Fired : When plugin update
 */
function lsdc_track_updated(){
    $domain = str_replace( ".","_", parse_url(get_site_url())['host']);
    $site_usage = get_option( plugin_basename( LSDC_PATH ) . '_site_usage');
    $old = $site_usage[$domain]['plugin_usage']['updated'];

    if( is_array( $old ) ) {
        if( ! in_array( LSDC_VERSION, $old ) ){
            array_push( $old, LSDC_VERSION );
        }
    }else{
        $old = array( LSDC_VERSION );
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
    $site_usage = get_option( plugin_basename( LSDC_PATH ) . '_site_usage');
    $old = abs( $site_usage[$domain]['plugin_usage']['active_day'] );
    $site_usage[$domain]['plugin_usage']['active_day'] = $old + 1; // Updating Data Active
    update_option( plugin_basename( LSDC_PATH ) . '_site_usage', $site_usage );
    lsdc_track_push();
}


/**
 * Function to get random hoour today, for cron fired
 * @return Date with Random Hours
 */
// function lsdc_date_randomhour_today(){
//     // Convert to timetamps
//     $min = strtotime( lsdc_date_now() ); // Now
//     $max = strtotime( lsdc_date_format( lsdc_date_now(), 'Y-m-d' ) . ' ' . date("H:i:s", mktime(23, 59, 0)) ); // Today untul 23.59:00
//     // Generate random number using above bounds
//     $val = rand($min, $max);
//     // Convert back to desired date format
//     return date('Y-m-d H:i:s', $val);
// }


/**
 * Function to Push Track to Server Usage
 * Fired when Cron Execution or Event Fired
 */
function lsdc_track_push(){
    $domain = str_replace( ".","_", parse_url(get_site_url())['host']);
    $body	= get_option( plugin_basename( LSDC_PATH ) . '_site_usage');

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
        $response = wp_remote_post( 'http://stats.lsdplugins.com/api/v1/lsdcommerce/' , $payload);
        $response = json_decode(wp_remote_retrieve_body( $response ), TRUE );
    }
    return $response;
}


/**
 * Create Daily Update
 * Track Data Daily
 */
if( !wp_next_scheduled( 'lsdcommerce_daily_update' ) ) {
    wp_schedule_event( time(), 'daily', 'lsdcommerce_daily_update' );
    add_action( 'lsdcommerce_daily_update', function(){
        lsdc_track_activeday();
    });
}
?>