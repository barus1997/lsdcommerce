<?php

/**
 * Fired during plugin activation
 *
 * @link       lsdplugins.com
 * @since      1.0.0
 *
 * @package    LSDCommerce
 * @subpackage LSDCommerce/core/common
 */
class LSDCommerce_Activator {

	public static function activate() {

		// Activate Theme
		// Sending Usage Data
		lsdc_track_init();
		lsdc_track_push();
	}
	
} 
?>