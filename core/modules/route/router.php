<?php 

// Custom Taxonomy
function call_taxonomy_template_from_directory( $template ){
	global $post;

	$taxonomy_slug = get_query_var('lsdc-product-category');
	if( $taxonomy_slug ){
		return LSDC_PATH . "/templates/category.php";
	}
}
add_filter('taxonomy_template', 'call_taxonomy_template_from_directory');

/**
 * @package LSDCommerce
 * @subpackage Checkout ? Order-received
 * 
 * Create Order Received Url Handler
 */
function lsdc_add_queryvars( $vars ){
    $vars[] = 'thankyou';
    $vars[] = 'payment';
    return $vars;
}
add_filter( 'query_vars', 'lsdc_add_queryvars' );

// Set Order Received to File
function lsdc_checkout_finish_url( $vars ){
    $hash   = get_query_var( 'thankyou' );
    $pay    = get_query_var( 'payment' );

    // Exist Order in Finish Url
    if( $hash && $pay == 'true'){
        add_filter( 'template_include', function() use( $hash ){
            // global $lsdd_templates;
            // require $lsdd_templates['thankyou']; // Using Default Template, It can Override via Global Variable
            require LSDC_PATH . '/templates/thankyou.php';
        });
    }

    // Empty Order in Finish Url
    if( empty( $hash ) && $pay == 'true' ){
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        get_template_part( 404 ); 
    }
}
add_action( 'template_redirect', 'lsdc_checkout_finish_url' );

function lsdcommerce_checkout_token(){
    // Buat Checkout Token Selamat 10 Menit
    // Kalo Nggak dipake diabakalan Expired
    if( ! isset( $_COOKIE['_lsdcommerce_token'] ) && is_page( lsdc_admin_get( 'general_settings', 'checkout_page' ) )  ){
        $token = wp_hash( lsdc_date_now() );
        $expired = lsdc_date_now();
        setcookie( "_lsdcommerce_token", $token . '-' . strtotime( $expired ), time() + 600, "/"  );
        if( ! get_transient( 'lsdcommerce_checkout_' . $token ) ){
            set_transient( 'lsdcommerce_checkout_' . $token , lsdc_date_now(), 600 );
        }        
    }else{
        if( isset( $_COOKIE['_lsdcommerce_token'] )  &&  ! is_page( lsdc_admin_get( 'general_settings', 'checkout_page' ) ) ){
            setcookie( "_lsdcommerce_token" , null, time() - 3600 , "/"  );
        }
    }
}
add_action( 'template_redirect', 'lsdcommerce_checkout_token' );

/**
 * Load Template in LSDCommerce
 * You can override this templates
 */
function lsdcommerce_template()
{
    $templates = array(
        'store' => LSDC_PATH . 'templates/store.php',
        'single' => LSDC_PATH . 'templates/single.php',
        'checkout' => LSDC_PATH . 'templates/checkout.php',
        'member' => LSDC_PATH . 'templates/member.php',
        'category' => LSDC_PATH . 'templates/category.php',
    );

    if (has_filter('lsdcommerce_template'))
    {
        $templates = apply_filters('lsdcommerce_template', $templates);
    }
    return $templates;
}
?>