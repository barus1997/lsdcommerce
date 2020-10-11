<?php 
/**
 * @package LSDCommerce
 * @subpackage User
 * 
 * Create Phone Input
 */
add_role( 'customer', __('Customer' ),
    array(
        'read' => true, // Subscription
    )
);

/**
 * @package LSDCommerce
 * @subpackage User
 * 
 * Create Phone Input
 */
add_action( 'user_new_form', 'lsdc_user_phone_field' );
  add_action( 'show_user_profile', 'lsdc_user_phone_field' );
add_action( 'edit_user_profile', 'lsdc_user_phone_field' );
function lsdc_user_phone_field( $user ) 
{
	$phone = get_user_meta( $user->ID, 'user_phone', true ) != null ? get_user_meta( $user->ID, 'user_phone', true ) : null; 
?>
	<table class="form-table">
		<tr>
			<th><label for="user_phone"><?php _e( "Phone" ); ?></label></th>
			<td>
				<input type="text" name="user_phone" id="user_phone" class="regular-text" value="<?php echo lsdc_format_phone( $phone ); ?> "/><br/>
				<span class="description"><?php _e( "Please enter your phone." ); ?></span>
			</td>
		</tr>
	</table>
<?php
}

/**
 * @package LSDCommerce
 * @subpackage User
 * 
 * Saving User Phone 
 */
add_action( 'personal_options_update', 'lsdc_user_phone_save' );
add_action( 'edit_user_profile_update', 'lsdc_user_phone_save' );
function lsdc_user_phone_save( $user_id ) {

    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }

    update_usermeta( $user_id, 'user_phone', esc_attr( $_POST['user_phone'] ) );
}

// function mb_add_phone_field( $user_contact ){
//     $user_contact['user_phone'] = 'Phone';
//     return $user_contact;
// }
// add_filter('user_contactmethods', 'mb_add_phone_field');

function custom_user_profile_fields($user){


	if( $user == 'add-new-user' ){
		$company = null;
	}else{
		$company = null !== get_the_author_meta( 'company', $user->ID ) ? get_the_author_meta( 'company', $user->ID ) : null;
	}

	?>
	  <h3>Extra profile information</h3>
	  <table class="form-table">
		  <tr>
			  <th><label for="company">Company Name</label></th>
			  <td>
				  <input type="text" class="regular-text" name="company" value="<?php echo esc_attr( $company ); ?>" id="company" /><br />
				  <span class="description">Where are you?</span>
			  </td>
		  </tr>
	  </table>
	<?php
}
//   add_action( 'show_user_profile', 'custom_user_profile_fields' );
//   add_action( 'edit_user_profile', 'custom_user_profile_fields' );
//   add_action( "user_new_form", "custom_user_profile_fields" );
  
  function save_custom_user_profile_fields($user_id){
	  # again do this only if you can
	  if( !current_user_can('manage_options') )
		  return false;
  
	  # save my custom field
	  update_usermeta($user_id, 'user_phone', $_POST['user_phone']);
	//   update_usermeta($user_id, 'company', $_POST['company']);
  }
  add_action('user_register', 'save_custom_user_profile_fields');
  add_action('profile_update', 'save_custom_user_profile_fields');
?>