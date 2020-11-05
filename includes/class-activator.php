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

		// Create Page Etalase
		$title = __( 'Etalase', 'lsdcommerce' );
		$check = get_page_by_title($title);
		$page = array(
			'post_title' 	=> $title,
			'post_type' 	=> 'page',
			'post_status' 	=> 'publish',
			'post_slug' 	=> __( 'checkout', 'lsdcommerce' ),
			'page_template' => 'store.php'
		);

		if( ! isset( $check->ID ) ){
			$id = wp_insert_post($page);
		}

		// Create Page Checkout + Setup
		$title = __( 'Checkout', 'lsdcommerce' );
		$check = get_page_by_title($title);
		$page = array(
			'post_title' 	=> $title,
			'post_type' 	=> 'page',
			'post_status' 	=> 'publish',
			'post_slug' 	=> __( 'checkout', 'lsdcommerce' ),
			'post_content' => '[lsdcommerce_checkout]'
		);

		if( ! isset( $check->ID ) ){
			$id = wp_insert_post($page);
			lsdc_set( 'general_settings', 'checkout_page', $id );
		}
	
		// Create Payment Page
		$title = __( 'Member', 'lsdcommerce' );
		$check = get_page_by_title($title);
		$page = array(
			'post_title' 	=> $title,
			'post_type' 	=> 'page',
			'post_status' 	=> 'publish',
			'post_slug' 	=> __( 'member', 'lsdcommerce' ),
			'page_template' => 'member.php'
		);

		if( ! isset( $check->ID ) ){
			$id = wp_insert_post($page);
			lsdc_set( 'general_settings', 'member_area', $id );
		}

		// Set Report Read
		update_option('lsdcommerce_order_unread', 0);


		// Sending Usage Data
		// lsdc_track_init();
	}
	
} 
?>