<?php 
namespace LSDCommerce\Member;

class LSDC_Member{
    public function __construct(){

    }

    public function change_password(){
        // Checking Nonce AJAX
        var_dump( 'Pa Pa Ya' );
		// if ( ! check_ajax_referer( 'senderpad_nonce', 'security' ) ) {
		// 	wp_send_json_error( 'Invalid security token sent.' );
		// }
	
		// // Ajax
		// $oldpassword = sanitize_text_field($_REQUEST['oldpassword']);
		// $newpassword = sanitize_text_field($_REQUEST['newpassword']);
		
		// // Chekc if Old Password Match
		// $current_user = wp_get_current_user();
		// $userdata = get_user_by('login', $current_user->user_login);
		// $result = wp_check_password($oldpassword, $userdata->user_pass, $userdata->ID);
		// if($result){
		// 	wp_set_password( $newpassword, $current_user->ID );
		// 	echo 'success';
		// }else{
		// 	echo 'error';
		// }
		wp_die();
	}
}
// add_action( 'wp_ajax_nopriv_lsdc_member_change_password', LSDC_Member::change_password() );
// add_action( 'wp_ajax_lsdc_member_change_password', LSDC_Member::change_password() );
?>