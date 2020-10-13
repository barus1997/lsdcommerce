<?php 
use LSDCommerce\Notification\LSDC_Notification;
use LSDCommerce\Order\LSDC_Order;

// Counting item in Posttype with Translation
// usage :: lsdc_count_products( 'lsdc-product', 'Product', 'Products' )
function lsdc_count_products( $posttype, $singular, $plural )
{
    $total = wp_count_posts( $posttype )->publish; 
    $text =  _n( $singular, $plural, wp_count_posts( $posttype )->publish, 'lsdcommerce' );
    return $total . ' ' . $text;
}

/**
 * Generate : Unique Code Reserved
 * 
 * Limitiation 490 Transaction Unique /day
 * Tip : Add TimeStamp for Automatic Confirmation
 * Bug : Collosion Uniqued ID
 */
function lsdc_generate_uniquecode(){
    $reserved = get_option( 'lsdc_uniquecode_reserved' );
    $uncode = rand(4,499);
    $code = array();
    if( $reserved ){
        if (in_array($uncode, $reserved)){ // Exist
            lsdc_generate_uniquecode();
        }else{
            array_push( $reserved, $uncode );
            update_option( 'lsdc_uniquecode_reserved', $reserved );
        }
        // Auto Reset
        if( count($reserved) == 495 ){
            update_option( 'lsdc_uniquecode_reserved', '' );
        }
    }else{
        $code[] = $uncode;
        update_option( 'lsdc_uniquecode_reserved', $code );
    }
	return absint($uncode);
}

/**
 * Increases or decreases the brightness of a color by a percentage of the current brightness.
 * Source : https://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php
 */
function lsdc_adjust_brightness($hex, $steps) {
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color   = hexdec($color); // Convert to decimal
        $color   = max(0,min(255,$color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}

/**
 * Get : IP Address
 */
function lsdc_get_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Get : Store Settings
 */
function lsdc_get_store( $key ){
    $store_settings = get_option( 'lsdc_store_settings' ); 
    switch ($key) {
        case 'country':
            return isset( $store_settings['lsdc_store_country'] ) ? esc_attr( $store_settings['lsdc_store_country'] ) : 'ID';
            break;
        case 'state':
            return isset( $store_settings['lsdc_store_state'] ) ? esc_attr( $store_settings['lsdc_store_state'] ) : 3;
            break;
        case 'city':
            return isset( $store_settings['lsdc_store_city'] ) ? esc_attr( $store_settings['lsdc_store_city'] ) : 455;
            break;
        default:
            return $store_settings; //Return Settings
            break;
    }
}

/**
 * Get : Store Settings
 */
function lsdc_get_payment( $id, $type){
    $payment_method = get_option( 'lsdcommerce_payment_option' );
    $pointer = isset( $payment_method[$id]['alias'] ) ? $payment_method[$id]['alias'] : $id; // Check if Alias Custom Exist or Not Using ID Default
    $method = $payment_method[$pointer]; // select data by alias if avaialbale

    switch ($type) {
        case 'groupname':
            return isset( $method['group_name'] ) && $method['group_name'] != '' ? esc_attr( $method['group_name'] . ' - ' ) : '';
            break;
        case 'name':
            return esc_attr( $method['name'] );
            break;
        case 'logo':
            return esc_attr( $method['logo'] );
            break;
        case 'swiftcode':
            return esc_attr( $method['swift_code'] );
            break;
        case 'account_code':
            return isset( $method['bank_code'] ) && $method['bank_code'] != '' ? '(' . esc_attr( $method['bank_code'] ) .') ' : '';
            break;
        case 'account_number':
            return esc_attr( $method['account_number'] );
            break;
        case 'account_holder':
            return esc_attr( $method['account_holder'] );
            break;
        case 'instruction':
            return esc_attr( $method['instruction'] );
            break;
    }
}

/**
 * Array : Sort by Key
 */
function lsdc_array_sort_bykey(&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

/**
 * Pagination Function
 */
function lsdc_pagination( $url , $current = 1, $perpage = 5, $total = 6, $classitem = 'page-item', $active = 'active', $extra = false){

    $part = ceil( $total / $perpage );
    $middle = ceil( $part / 2 );
    $current = min(max(1, $current), $total);
    $next = true;
    $prev = true;

 
    $pagination = '<ul class="pagination">';
        if( $part == $current ) $next = false; // Disale Next on Last == Current
        if( 1 == $current ) $prev = false; // Disale Next on Last == Current

        if( $prev ){
            $pagination .= '<li class="'. $classitem .'"><a href="'. $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . ( $current - 1 ) . $extra .'">'. __( 'Prev', 'lsdc' ) .'</a></li>';
        }
       
        if( $part == 1 ){ // Minium Item
            $pagination .= '<li class="'. $classitem . ' ' . $active .' active"><a href="'. $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=1' . $extra . '">'. $current .'</a></li>';
        }elseif ( $part < 4 ) { // 3 Pages
            for ( $i = 1 ; $i <= $part; $i++ ) { 
                if( $i == $current ){
                    $pagination .= '<li class="'. $classitem . ' ' . $active . '"><a href="'. $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra .'">'. $i .'</a></li>';
                }else{
                    $pagination .= '<li class="'. $classitem . '"><a href="'. $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra .'">'. $i .'</a></li>';
                }
            }
        }else{ // Many Pages
            
            // Starter Page
            for ( $i = 1 ; $i <= $part; $i++ ) { 
                if ($i < 3 ) { // FIrst 2 Pages
                    if( $i == $current ){
                        $pagination .= '<li class="'. $classitem . ' ' . $active . '"><a href="'.  $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra .'">'. $i .'</a></li>';
                    }else{
                        $pagination .= '<li class="'. $classitem . '"><a href="'.  $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra .'">'. $i .'</a></li>';
                    }
                }
            }
            
            $tmp = array();
            if( $current > 2 && $current < $total - 2){ // Current More than 2, and Current Not 8 in 10
                if ( $current != 3 &&  $current < $total - 2 ){
                    $pagination .= '<li class="'. $classitem .'"><span>...</span></li>';
                }

                for ( $i = $current; $i < $part; $i++ ) { 
                    if ($i > 2 && $i < $part - 1 && count($tmp) < 2 ) {
                        if( $i == $current ){
                            $pagination .= '<li class="'. $classitem . ' ' . $active . '"><a href="'.  $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra .'">'. $i .'</a></li>';
                        }else{
                            $pagination .= '<li class="'. $classitem . '"><a href="'.  $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra .'">'. $i .'</a></li>';
                        }
                        array_push( $tmp, $i );
                    } 
                }

                if ( $current < $part - 2 ){ // If Lower Than Part - 9 Disable
                    $pagination .= '<li class="'. $classitem .'"><span>...</span></li>';
                }
            }else{
                $pagination .= '<li class="'. $classitem .'"><span>...</span></li>';
            }
         

            for ( $i = 1 ; $i <= $part; $i++ ) { 

                if ($i > $part - 2 ) {
                    if( $i == $current ){
                        $pagination .= '<li class="'. $classitem . ' ' . $active . '"><a href="'.  $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra .'">'. $i .'</a></li>';
                    }else{
                        $pagination .= '<li class="'. $classitem . '"><a href="'.  $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra .'">'. $i .'</a></li>';
                    }
                } 
            }

        }

        if( $next ){
            $pagination .= '<li class="'. $classitem .'"><a href="'. $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . ( $current + 1 ) . $extra .'">'. __( 'Next', 'lsdc' ) .'</a></li>';
        }
        
    $pagination .= '<ul>';
    echo $pagination;
}

/**
 * Set Powered Text
 */
function lsdc_powered_text()
{ 
    ?>
        <p class="text-center powered"> 
            <?php _e( 'Powered by', 'lsdcommerce' ); ?> <a href="https://lsdplugins.com/lsdcommerce/" class="text-primary">LSDCommerce</a>
        </p>
	<?php
}
add_action( 'lsdcommerce_single_after', 'lsdc_powered_text');




/**
 * Get User Name by User ID
 * Block : User
 * @param int $user_id
 */
function lsdc_user_getname( $user_id = false ){
    $user_id = empty( $user_id ) ?  get_current_user_id() : $user_id;
    return ucfirst( esc_attr( get_user_meta( $user_id, 'first_name', true ) ) ) . ' ' . esc_attr( get_user_meta( $user_id, 'last_name', true ) );
}

/**
 * Get User Phone by User ID
 * Block : User
 * @param int $user_id
 */
function lsdc_user_getphone( $user_id = false ){
    $user_id = empty( $user_id ) ?  get_current_user_id() : $user_id;
    return esc_attr( get_user_meta( $user_id, 'user_phone', true ) );
}

/**
 * Get User Email by User ID
 * Block : User
 * @param int $user_id
 */
function lsdc_user_getemail( $user_id = false ){
    $user_id = empty( $user_id ) ?  get_current_user_id() : $user_id;
    $user = get_user_by( 'id', $user_id );
    return sanitize_email( $user->user_email );
}

/**
 * Formatting User Name ( Nama Depan Nama Belakang  = namadepannamabelakang )
 * Block : Format | User
 * @param string $fullname
 */
function lsdc_format_username( $fullname ){
    $names = explode( ' ', $fullname );
    $names = array_map( 'esc_attr', $names ); // Sanitize Array
    return strtolower( implode( '', $names ) );
}

/**
 * Formatting Indonesian Phone Number
 * Block : Format
 * @param string $fullname
 */
function lsdc_format_phone( $phone )
{
    $phone = (string) $phone;

    if( $phone[0] != '0' ){
        if( $phone[1] != '6' ){
            $format = '0'. $phone;
        }else{
            $format = $phone;
        }
    }else{
        $format = $phone;
    }

    return trim($format);
}

/**
 * Generating Password for New User
 * Block : Generate
 * @param int $length
 */
function lsdc_generate_password($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}


/**
 * Experimental Function
 * - Defering
 * - Preload
 */
// Adapted from https://gist.github.com/toscho/1584783
// add_filter( 'clean_url', function( $url )
// {
//     if ( FALSE === strpos( $url, 'swiper.js' ) )
//     { // not our file
// 		return $url;
//     }
//     // Must be a ', not "!
//     return "$url' defer='defer";
// }, 11, 1 );


// function defer_parsing_of_js( $url ) {
//     if ( is_user_logged_in() ) return $url; //don't break WP Admin
//     if ( FALSE === strpos( $url, '.js' ) ) return $url;
//     if ( strpos( $url, 'jquery.js' ) ) return $url;
//     return str_replace( ' src', ' defer src', $url );
// }
// add_filter( 'script_loader_tag', 'defer_parsing_of_js', 10 );

// function add_rel_preload($html, $handle, $href, $media) {
//     if (is_admin())
//     	return $html;

// $html = <<<EOT
//     <link rel='preload' as='style' onload="this.onload=null;this.rel='stylesheet'" id='$handle' href='$href' type='text/css' media='all' />
// EOT;
//     return $html;
// }
// add_filter( 'style_loader_tag', 'add_rel_preload', 10, 4 );

// function lsdc_array_push( $order_id, $state ){
//     // $status = get_post_meta( $order_id, 'status', true);
//     // if ( ! in_array( $state, $status ) ){ // Jika dalem array
//     //     if( is_array( $status ) ){
//     //         $status[] = $state;
//     //     }else{
//     //         $status = (array) json_decode( $status );
//     //         $status[] = $state;
//     //     }
//     // }
// }
?>