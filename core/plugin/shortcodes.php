<?php
// Shortcode Checkout
function lsdcommerce_checkout_sc( $attr ) 
{	
    if( ! is_admin() ){
        ob_start();
        require_once LSDC_PATH . 'templates/checkout.php';
        $output = ob_get_clean();    
        return $output;
    }else{
        return false;
    }
}
add_shortcode( 'lsdcommerce_checkout', 'lsdcommerce_checkout_sc' );

// SHortcode Latest Products
function lsdcommerce_latest_products_sc( $attr ) 
{	
    if( ! is_admin() ){
        ob_start();
        require_once LSDC_PATH . 'core/shortcodes/latest-products.php';
        wp_enqueue_style( 'lsdcommerce-single' );
        $output = ob_get_clean();    
        return $output;
    }else{
        return false;
    }
}
add_shortcode( 'lsdcommerce_latest_products', 'lsdcommerce_latest_products_sc' );