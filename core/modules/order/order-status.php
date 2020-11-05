<?php 

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
    if( isset( $post->post_type) && $post->post_type != 'lsdc-order') return false;

    echo "<script>
    jQuery(document).ready( function() {
    jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"completed\">Completed</option>' );
    });
    </script>";

}
add_action('admin_footer-edit.php','lsdc_post_status_quick_edit');

?>