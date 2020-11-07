<?php
/**
 * Class and Function List:
 * Function list:
 * - lsdc_product_price()
 * - lsdc_product_weight()
 * - lsdc_product_stock()
 * - lsdc_product_download_version()
 * - lsdc_product_download_link()
 * - lsdc_product_check_type()
 * - lsdc_product_type()
 * - lsdc_product_extract_ID()
 * - lsdc_product_variation_exist()
 * - lsdc_product_variation_price()
 * - lsdc_product_variation_label()
 * Classes list:
 */
/**
 * Get Product Price by Product ID ( Promo Prioritize )
 *
 * @subpackage Product
 * @since 1.0.0
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
 * @since 1.0.0
 */
function lsdc_product_weight($product_id = false)
{
    if ($product_id == null) $product_id = get_the_ID(); //Fallback Product ID
    return abs(lsdc_currency_clean(get_post_meta($product_id, '_physical_weight', true)));
}

/**
 * Get Stock Product based on Product ID
 *
 * @subpackage Product
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
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

/**
 * Check Product Type : Digital or Physical
 *
 * @subpackage Product
 * @since 1.0.0
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

/**
 * Check Product Type
 * @return string type product : digital | physical
 */
function lsdc_product_type($product_id)
{
    $type = strtolower(get_post_meta($product_id, '_shipping_type', true));
    return esc_attr($type);
}

/**
 * Extracting ID from Possibilty Variation
 */
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

/**
 * Checking Variation Exist
 * @param int $id : 8
 * @param string $variation : 8-hitam-xl
 */
function lsdc_product_variation_exist($id, $variation)
{
    $variations = json_decode(get_post_meta($id, '_variations', true));
    $multi_variations = explode('-', $variation); // [ 8, hitam, xl ]
    unset($multi_variations[0]); // remove id product [ hitam, xl ]
    $multi_variations = array_map('strtolower', $multi_variations);

    $temp = array();
    if (!empty($variations))
    { // Check Variation
        foreach ($variations as $key => $variant)
        { // Multi Variations
            foreach ($variant->items as $key => $item)
            { // Inside Variation
                if (in_array(strtolower($item->name) , $multi_variations))
                { // name exist
                    $temp[] = strtolower($item->name);
                }
            }
        }

    }

    if (!empty($temp))
    {
        return true;
    }
    else
    {
        return false;
    }
}

/**
 * Summary Product Title for More than one
 * based on OrderID
 * 
 * Output : Product One, Product Tw...
 */
function lsdc_product_title_summary( $order_id ){
    $products = (array)json_decode(get_post_meta($order_id, 'products', true));
    $names = array();

    foreach ($products as $key => $product)
    {
        $product_id = lsdc_product_extract_ID($product->id);
        array_push( $names, get_the_title( $product_id) );
    }

    if( isset( $names[1]) ){
        $string = $names[0] . ', ' . $names[1] . '...';
    }else{
        $string = $names[0];
    }
    return $string;
}
/**
 * Get Product Variation Price
 * by Product ID and Variation ID ( Promo Prioritize )
 *
 * @subpackage LSDCommerce Pro
 * @since 0.4.0
 * @param int $id : 8
 * @param string $variation : 8-hitam-xl
 */
function lsdc_product_variation_price($id, $variation)
{
    $variations = (array)json_decode(get_post_meta($id, '_variations', true)); // Get Variations Data from Product
    $multi_variations = explode('-', $variation); // [ 8, hitam, xl ]
    unset($multi_variations[0]); // remove id product [ hitam, xl ]
    $multi_variations = array_map('strtolower', $multi_variations);

    $variation_price = null;

    if (!empty($variations))
    { // Check Variation
        foreach ($variations as $key => $variant)
        { // Multi Variations
            foreach ($variant->items as $key => $item)
            { // Inside Variation
                if (in_array(strtolower($item->name) , $multi_variations))
                {
                    $variation_price = lsdc_currency_clean($item->price);
                }
            }
        }
    }

    $normal = lsdc_price_normal($id);
    $discount = lsdc_price_discount($id);

    // Add Variation Price to Base Price
    if ($discount)
    {
        return abs($discount) + abs($variation_price);
    }
    else
    {
        if ($normal)
        {
            return abs($normal) + abs($variation_price);
        }
        else
        {
            return 0;
        }
    }
}

function lsdc_product_variation_label($id, $variation)
{
    $variations = (array)json_decode(get_post_meta($id, '_variations', true)); // Get Variations Data from Product
    $multi_variations = explode('-', $variation); // [ 8, hitam, xl ]
    unset($multi_variations[0]); // remove id product [ hitam, xl ]
    $multi_variations = array_map('strtolower', $multi_variations);

    $variation_price = null;

    if (!empty($variations))
    { // Check Variation
        foreach ($variations as $key => $variant)
        { // Multi Variations
            foreach ($variant->items as $key => $item)
            { // Inside Variation
                if (in_array(strtolower($item->name) , $multi_variations))
                {
                    return esc_attr($item->name);
                }
            }
        }
    }
}
?>