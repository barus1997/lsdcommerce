<?php
/**
 * Create Role Customer
 *
 * @package User
 * @subpackage Role
 * @since 1.0.0
 */
add_role('customer', __('Customer') , array(
    'read' => true, // Subscription Access
    
));

/**
 * Craete Phone Field
 *
 * @package User
 * @subpackage Form
 * @since 1.0.0
 */
add_action('user_new_form', 'lsdc_user_phone_field');
add_action('show_user_profile', 'lsdc_user_phone_field');
add_action('edit_user_profile', 'lsdc_user_phone_field');
function lsdc_user_phone_field($user)
{
    $phone = get_user_meta($user->ID, 'user_phone', true) != null ? get_user_meta($user->ID, 'user_phone', true) : null;
?>
	<table class="form-table">
		<tr>
			<th><label for="user_phone"><?php _e("Phone"); ?></label></th>
			<td>
				<input type="text" name="user_phone" id="user_phone" class="regular-text" value="<?php echo lsdc_format_phone($phone); ?> "/><br/>
				<span class="description"><?php _e("Silahkan masukan nomor telepon."); ?></span>
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
add_action('personal_options_update', 'lsdc_user_phone_save');
add_action('edit_user_profile_update', 'lsdc_user_phone_save');
add_action('user_register', 'lsdc_user_phone_save');
add_action('profile_update', 'lsdc_user_phone_save');
function lsdc_user_phone_save($user_id)
{
    if (!current_user_can('edit_user', $user_id)) return false;
    if (!current_user_can('manage_options')) return false;

    update_usermeta($user_id, 'user_phone', lsdc_format_phone($_POST['user_phone']));
}
?>