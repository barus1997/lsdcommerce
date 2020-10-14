<?php 
namespace LSDCommerce\Member;

class LSDC_Member{
    public function __construct(){
		add_action( 'wp_ajax_nopriv_lsdc_member_profile_password', [ $this, 'lsdc_member_profile_password' ] );
		add_action( 'wp_ajax_lsdc_member_profile_password', [ $this, 'lsdc_member_profile_password' ] );
    }

    public function change_password(){
		if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'member-nonce' ) ) { die('Busted'); }

		$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
		$old = sanitize_text_field( $_REQUEST['old'] );
		$new = sanitize_text_field( $_REQUEST['new'] );
	
		// Check if Old Password Match
		$current_user = wp_get_current_user();
		$userdata = get_user_by('login', $current_user->user_login);
		$result = wp_check_password($old, $userdata->user_pass, $userdata->ID);
	
		if( ! is_user_logged_in() ){ // False for Un Logged User
			echo false;
		}else{
			if($result){
				wp_set_password( $new, $current_user->ID ); // Set New Password
				echo true;
			}else{
				echo false;
			}
		}
	
		wp_die();
	}
}
new LSDC_Member;
?>