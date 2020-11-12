<?php 

/**
 * Taxonomy Post Counter
 * based on taxonomy name and term_id
 * 
 * @since 1.0.0
 * @return int 
 */
function lsdc_count_taxonomy_post( $name, $termid = false ){
    $terms = get_terms(
        array(
			'taxonomy'   => $name,
			'include'    => get_queried_object()->term_id,
            'hide_empty' => false,
        )
    );
 
    $count = 0;
    foreach ($terms as $key => $value) {
        $count += $value->count;
    }
    return abs($count);
}

/**
 * Counting item in Posttype with Translation
 * 
 * @package Core
 * @subpackage Count
 * @since 1.0.0
 * usage :: lsdc_count_products( 'lsdc-product', 'Product', 'Products' )
 */
function lsdc_count_products( $posttype, $singular, $plural )
{
    $total = wp_count_posts( $posttype )->publish; 
    $text =  _n( $singular, $plural, wp_count_posts( $posttype )->publish, 'lsdcommerce' );
    return $total . ' ' . $text;
}

function lsdc_count_posttypes( $posttype, $author_id ){
    $query = new WP_Query(array(
        'post_type' => $posttype,
        'post_status' => 'publish',
        'post_author' => $author_id,
        'meta_query' => array(
            array(
                'key' => 'customer_id',
                'value' => $author_id,
                'compare' => '='
            )
        )
    ));

    $counter = 0;
    while ($query->have_posts()): $query->the_post();
        $counter++;
    endwhile; wp_reset_postdata();
    return $counter;
}
?>