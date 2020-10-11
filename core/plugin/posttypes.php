<?php 
/** 
 * Post Type LSDCommerce
 *
 * Register Product and Order Post Type
 * 
 * @block   WordPressAPI
 * @since   1.0.0
 */

function lsdc_register_posttypes(){

    // PostType : Product
    $supports = array(
        'title',
        'editor', 
        'thumbnail', 
    );

    $labels = array(
        'name' 			=> _x('Products', 'plural', 'lsdcommerce'),
        'singular_name' => _x('Product', 'singular', 'lsdcommerce'),
        'add_new' 		=> _x('Add Product', 'addnew', 'lsdcommerce'),
        'add_new_item' 	=> __('Add Product', 'lsdcommerce'),
        'new_item' 		=> __('New Product', 'lsdcommerce'),
        'edit_item' 	=> __('Edit Product', 'lsdcommerce'),
        'view_item' 	=> __('View Product', 'lsdcommerce'),
        'all_items' 	=> __('All Product', 'lsdcommerce'),
        'search_items' 	=> __('Find Product', 'lsdcommerce'),
        'not_found' 	=> __('Empty Product.', 'lsdcommerce'),
    );

    $args = array(
        'supports' 			=> $supports,
        'labels' 			=> $labels,
        'public' 			=> true,
        'show_in_menu' 		=> false,
        'show_in_admin_bar' => true,
        'query_var' 		=> true,
        'rewrite' 			=> array ('slug' => __( 'product', 'lsdcommerce' ) ),
        'has_archive' 		=> true,
        'hierarchical' 		=> false,
        'taxonomies'    	=> array( 'lsdc-product-category' ),
        'comments' 			=> true
    );

    register_post_type('lsdc-product', $args);

    register_taxonomy(
        'lsdc-product-category', 
        'lsdc-product',
        array(
            'hierarchical' 	=> true,
            'label' 		=> __( 'Category' ), 
            'query_var' 	=> true,
            'public' 		=> true,
            'rewrite' 		=> array(
                'slug'			=> __( 'category', 'lsdcommerce' ),   
                'with_front' 	=> true, 
                'hierarchical'  => true
            ),
            'has_archive' => false,
        )
    );

    // Flush The Permalink
    // if ( get_option( 'lsdd_crowdfunding_flush' ) ) {
    //     // Rewrite Embed Donation
    //     add_rewrite_rule( 'embed/campaign/([^/]+)', 'index.php?embed_campaign=yes&campaign=$matches[1]', 'top' );
    //     flush_rewrite_rules();
    //     delete_option( 'lsdd_crowdfunding_flush' );
    // }

    // PostType : Order

    $labels = array(
        'name'          => _x('Orders', 'plural', 'lsdcommerce'),
        'singular_name' => _x('Order', 'singular', 'lsdcommerce'),
        'add_new'       => _x('New Order', 'addorder', 'lsdcommerce'),
        'add_new_item'  => __('Add Order', 'lsdcommerce'),
        'new_item'      => __('New Order', 'lsdcommerce'),
        'edit_item'     => __('Edit Order', 'lsdcommerce'),
        'view_item'     => __('View Order', 'lsdcommerce'),
        'all_items'     => __('All Order', 'lsdcommerce'),
        'search_items'  => __('Find Order', 'lsdcommerce'),
        'not_found'     => __('Order Not Found.', 'lsdcommerce'),
    );

    $args = array(
        'supports'              => false,
        'labels'                => $labels,
        'public'                => false, //Enable for CrowdFunding
        'publicly_queryable'    => false,  
        'show_ui'               => true, 
        'show_in_menu'          => false,
        'show_in_admin_bar'     => false,
        'query_var'             => false,
        'rewrite'               => false,
        'has_archive'           => false,
        'hierarchical'          => false,
        'menu_icon'             => LSDC_URL . 'assets/img/order.svg',
        'comments'              => false,
        'capability_type'       => 'post',
        'capabilities' => array(
            'create_posts' => 'do_not_allow', // Removes support for the "Add New" function, including Super Admin's
        ),
        'map_meta_cap' => true, // Set to false, if users are not allowed to edit/delete existing posts
    );

    register_post_type('lsdc-order', $args);
}
add_action( 'init', 'lsdc_register_posttypes' );

function lsdc_post_status_register(){
    register_post_status( 'completed', array(
        'label'         => _x( 'Completed', 'post' ),
        'label_count'   => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true
    ));
}
add_action( 'init', 'lsdc_post_status_register' );

function lsdc_post_status_dropdown() {
    global $post;
    if($post->post_type != 'lsdc-order') return false;

    $status = ( $post->post_status == 'completed' ) ? "jQuery( '#post-status-display' ).text( 'Completed' ); jQuery( 'select[name=\"post_status\"]' ).val('completed');" : '';
    
    echo "<script>  jQuery(document).ready( function() {
                        jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"completed\">Completed</option>' ); ".$status."
                    });
        </script>";
    }
add_action( 'post_submitbox_misc_actions', 'lsdc_post_status_dropdown');

function lsdc_post_status_quick_edit() {
    global $post;
    if($post->post_type != 'lsdc-order') return false;

    echo "<script>
    jQuery(document).ready( function() {
    jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"completed\">Completed</option>' );
    });
    </script>";

}
add_action('admin_footer-edit.php','lsdc_post_status_quick_edit');

?>