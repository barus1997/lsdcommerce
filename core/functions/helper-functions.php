<?php
/**
* Class and Function List:
* Function list:
* - lsdc_create_schedule()
* - lsdc_create_password()
* - lsdc_create_uniquecode()
* - lsdc_adjust_brightness()
* - lsdc_array_sort_bykey()
* - lsdc_pagination()
* - lsdc_powered_text()
* - lsdc_format_username()
* - lsdc_format_phone()
* Classes list:
*/
use LSDCommerce\Notification\LSDC_Notification;
use LSDCommerce\Order\LSDC_Order;

/**
 * Create Schedule
 *
 * @package Core
 * @subpackage Create
 * @since 1.0.0
 */
function lsdc_create_schedule($name, $time)
{
    $timestamp = wp_next_scheduled($name);
    if ($timestamp == false)
    {
        wp_schedule_event(time() , 'daily', $name);
    }
}

/**
 * Create Password
 *
 * @package Core
 * @subpackage Create
 * @since 1.0.0
 */
function lsdc_create_password($length = 10)
{
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))) , 1, $length);
}

/**
 * Create Unique Code Reserved
 *
 * Limitiation 490 Transaction Unique /day
 * Tip : Add TimeStamp for Automatic Confirmation
 * Bug : Collosion Uniqued ID
 *
 * @package Core
 * @subpackage Create
 * @since 1.0.0
 */
function lsdc_create_uniquecode()
{
    $reserved = get_option('lsdc_uniquecode_reserved');
    $uncode = rand(4, 499);
    $code = array();
    if ($reserved)
    {
        if (in_array($uncode, $reserved))
        { // Exist
            lsdc_create_uniquecode();
        }
        else
        {
            array_push($reserved, $uncode);
            update_option('lsdc_uniquecode_reserved', $reserved);
        }
        // Auto Reset
        if (count($reserved) == 495)
        {
            update_option('lsdc_uniquecode_reserved', '');
        }
    }
    else
    {
        $code[] = $uncode;
        update_option('lsdc_uniquecode_reserved', $code);
    }
    return absint($uncode);
}

/**
 * Increases or decreases the brightness of a color by a percentage of the current brightness.
 * Source : https://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php
 */
function lsdc_adjust_brightness($hex, $steps)
{
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3)
    {
        $hex = str_repeat(substr($hex, 0, 1) , 2) . str_repeat(substr($hex, 1, 1) , 2) . str_repeat(substr($hex, 2, 1) , 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color)
    {
        $color = hexdec($color); // Convert to decimal
        $color = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color) , 2, '0', STR_PAD_LEFT); // Make two char hex code
        
    }

    return $return;
}

/**
 * Array : Sort by Key
 */
function lsdc_array_sort_bykey(&$array, $key)
{
    $sorter = array();
    $ret = array();
    reset($array);
    foreach ($array as $ii => $va)
    {
        $sorter[$ii] = $va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va)
    {
        $ret[$ii] = $array[$ii];
    }
    $array = $ret;
}

/**
 * Pagination Function
 */
function lsdc_pagination($url, $current = 1, $perpage = 5, $total = 6, $classitem = 'page-item', $active = 'active', $extra = false)
{

    $part = ceil($total / $perpage);
    $middle = ceil($part / 2);
    $current = min(max(1, $current) , $total);
    $next = true;
    $prev = true;

    $pagination = '<ul class="pagination">';
    if ($part == $current) $next = false; // Disale Next on Last == Current
    if (1 == $current) $prev = false; // Disale Next on Last == Current
    if ($prev)
    {
        $pagination .= '<li class="' . $classitem . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . ($current - 1) . $extra . '">' . __('Prev', 'lsdc') . '</a></li>';
    }

    if ($part == 1)
    { // Minium Item
        $pagination .= '<li class="' . $classitem . ' ' . $active . ' active"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=1' . $extra . '">' . $current . '</a></li>';
    }
    elseif ($part < 4)
    { // 3 Pages
        for ($i = 1;$i <= $part;$i++)
        {
            if ($i == $current)
            {
                $pagination .= '<li class="' . $classitem . ' ' . $active . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra . '">' . $i . '</a></li>';
            }
            else
            {
                $pagination .= '<li class="' . $classitem . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra . '">' . $i . '</a></li>';
            }
        }
    }
    else
    { // Many Pages
        // Starter Page
        for ($i = 1;$i <= $part;$i++)
        {
            if ($i < 3)
            { // FIrst 2 Pages
                if ($i == $current)
                {
                    $pagination .= '<li class="' . $classitem . ' ' . $active . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra . '">' . $i . '</a></li>';
                }
                else
                {
                    $pagination .= '<li class="' . $classitem . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra . '">' . $i . '</a></li>';
                }
            }
        }

        $tmp = array();
        if ($current > 2 && $current < $total - 2)
        { // Current More than 2, and Current Not 8 in 10
            if ($current != 3 && $current < $total - 2)
            {
                $pagination .= '<li class="' . $classitem . '"><span>...</span></li>';
            }

            for ($i = $current;$i < $part;$i++)
            {
                if ($i > 2 && $i < $part - 1 && count($tmp) < 2)
                {
                    if ($i == $current)
                    {
                        $pagination .= '<li class="' . $classitem . ' ' . $active . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra . '">' . $i . '</a></li>';
                    }
                    else
                    {
                        $pagination .= '<li class="' . $classitem . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra . '">' . $i . '</a></li>';
                    }
                    array_push($tmp, $i);
                }
            }

            if ($current < $part - 2)
            { // If Lower Than Part - 9 Disable
                $pagination .= '<li class="' . $classitem . '"><span>...</span></li>';
            }
        }
        else
        {
            $pagination .= '<li class="' . $classitem . '"><span>...</span></li>';
        }

        for ($i = 1;$i <= $part;$i++)
        {

            if ($i > $part - 2)
            {
                if ($i == $current)
                {
                    $pagination .= '<li class="' . $classitem . ' ' . $active . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra . '">' . $i . '</a></li>';
                }
                else
                {
                    $pagination .= '<li class="' . $classitem . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . $i . $extra . '">' . $i . '</a></li>';
                }
            }
        }

    }

    if ($next)
    {
        $pagination .= '<li class="' . $classitem . '"><a href="' . $url . (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . 'step=' . ($current + 1) . $extra . '">' . __('Next', 'lsdc') . '</a></li>';
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
            <?php _e('Powered by', 'lsdcommerce'); ?> <a href="https://lsdplugins.com/lsdcommerce/" class="text-primary">LSDCommerce</a>
        </p>
	<?php
}
add_action('lsdcommerce_single_after', 'lsdc_powered_text');

/**
 * Formatting User Name ( Nama Depan Nama Belakang  = namadepannamabelakang )
 * Block : Format | User
 * @param string $fullname
 */
function lsdc_format_username($fullname)
{
    $names = explode(' ', $fullname);
    $names = array_map('esc_attr', $names); // Sanitize Array
    return strtolower(implode('', $names));
}

/**
 * Formatting Indonesian Phone Number
 * Block : Format
 * @param string $fullname
 */
function lsdc_format_phone($phone)
{
    $phone = (string)$phone;

    if ($phone[0] != '0')
    {
        if ($phone[1] != '6')
        {
            $format = '0' . $phone;
        }
        else
        {
            $format = $phone;
        }
    }
    else
    {
        $format = $phone;
    }

    // Checking Phone Length
    if (strlen($phone) > 13 || strlen($phone) < 11)
    {
        $format = null;
    }

    return trim($format);
}

/**
 * Source : https://gist.github.com/nicklasos/365a251d63d94876179c
 */
function lsdc_multiple_download(array $urls, $save_path = '/tmp')
{
    $multi_handle = curl_multi_init();
    $file_pointers = [];
    $curl_handles = [];

    // Add curl multi handles, one per file we don't already have
    foreach ($urls as $key => $url) {
        $file = $save_path . '/' . basename($url);
        if(!is_file($file)) {
            $curl_handles[$key] = curl_init($url);
            $file_pointers[$key] = fopen($file, "w");
            curl_setopt($curl_handles[$key], CURLOPT_FILE, $file_pointers[$key]);
            curl_setopt($curl_handles[$key], CURLOPT_HEADER, 0);
            curl_setopt($curl_handles[$key], CURLOPT_CONNECTTIMEOUT, 60);
            curl_multi_add_handle($multi_handle,$curl_handles[$key]);
        }
    }

    // Download the files
    do {
        curl_multi_exec($multi_handle,$running);
    } while ($running > 0);

    // Free up objects
    foreach ($urls as $key => $url) {
        curl_multi_remove_handle($multi_handle, $curl_handles[$key]);
        curl_close($curl_handles[$key]);
        fclose ($file_pointers[$key]);
    }
    curl_multi_close($multi_handle);
}

// Files to download
$urls = [
    'http://static.scribd.com/docs/cdbwpohq0ayey.pdf',
    'http://static.scribd.com/docs/8wyxlxfufftas.pdf',
    'http://static.scribd.com/docs/9q29bbglnc2gk.pdf'
];
// lsdc_multiple_download($urls);

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
