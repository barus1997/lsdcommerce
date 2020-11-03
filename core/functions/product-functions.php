<?php
/**
 * Get Product Price by Product ID ( Promo Prioritize )
 *
 * @subpackage Product
 * @since 0.4.0
 */
function lsdc_product_price($product_id = false)
{
    if ($product_id == null) $product_id = get_the_ID(); //Fallback Product ID
    $normal = lsdc_price_normal($product_id);
    $discount = lsdc_price_discount($product_id);

    if ($discount)
    {
        return abs($discount);
    }
    else
    {
        if ($normal)
        {
            return abs($normal);
        }
        else
        {
            return 0;
        }
    }
}

/**
 * Get Weight Product based on Product ID
 *
 * @subpackage Product
 * @since 0.4.0
 */
function lsdc_product_weight($product_id = false)
{
    if ($product_id == null) $product_id = get_the_ID(); //Fallback Product ID
    return abs(lsdc_currency_clear(get_post_meta($product_id, '_physical_weight', true)));
}

/**
 * Get Stock Product based on Product ID
 *
 * @subpackage Product
 * @since 0.4.0
 */
function lsdc_product_stock($product_id = false)
{
    if ($product_id == null) $product_id = get_the_ID(); //Fallback Product ID
    $stock = '<p>' . __('Stock', 'lsdcommerce') . '<span>';
    if (get_post_meta($product_id, '_stock', true) > 999):
        $stock .= __('Available', 'lsdcommerce');
    else:
        $stock .= abs(get_post_meta(get_the_ID() , '_stock', true)) . ' ' . esc_attr(get_post_meta(get_the_ID() , '_stock_unit', true));
    endif;
    $stock .= '</span></p>';

    return $stock;
}

/**
 * Get Digital Product Version
 *
 * @subpackage Product
 * @since 0.4.0
 */
function lsdc_product_download_version($product_id)
{
    /* Pro Code */
    $changelog = get_post_meta($product_id, '_product_update_changelog', true);

    if ($changelog)
    {
        foreach (array_reverse($changelog) as $key => $version)
        {
            if (strtotime($version['datetime']) <= strtotime(current_time('Y-m-d H:i:s')))
            {
                return esc_attr($version['version']);
            }
        }
        /* Pro Code */
    }
    else
    {
        if (get_post_meta($product_id, '_digital_version', true))
        {
            return esc_attr(get_post_meta($product_id, '_digital_version', true));
        }
    }
}

/**
 * Get Digital Product File
 *
 * @subpackage Product
 * @since 0.4.0
 */
function lsdc_product_download_link($product_id)
{
    /* Pro Code */
    $changelog = get_post_meta($product_id, '_product_update_changelog', true);

    if ($changelog)
    {
        foreach (array_reverse($changelog) as $key => $version)
        {
            if (strtotime($version['datetime']) <= strtotime(current_time('Y-m-d H:i:s')))
            {
                return esc_url($version['file_url']);
            }
        }
    }
    else
    {
        /* Pro Code */
        if (get_post_meta($product_id, '_digital_url', true))
        {
            return esc_url(get_post_meta($product_id, '_digital_url', true));
        }
    }
}

// var_dump( lsdc_product_download_link( 3397 ) );

/**
 * Check Product Type : Digital or Physical
 *
 * @subpackage Product
 * @since 0.4.0
 */
function lsdc_product_check_type($order_id)
{
    $products = (array)json_decode(get_post_meta($order_id, 'products', true));
    $types = array();

    foreach ($products as $key => $product)
    {
        $product_id = abs($product->id);
        $type = get_post_meta($product_id, '_shipping_type', true);

        if (!in_array($type, $types))
        {
            array_push($types, $type);
        }
    }
    return $types;
}

function lsdc_product_type( $product_id ){
    $type = strtolower(get_post_meta($product_id, '_shipping_type', true));
    return esc_attr( $type );
}

function lsdc_product_extract_ID($product_id)
{
    $productID = explode('-', $product_id);
    if (isset($productID[1]))
    {
        return abs($productID[0]);
    }
    else
    {
        return abs($product_id);
    }
}

?>