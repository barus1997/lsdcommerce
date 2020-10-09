<?php 
namespace LSDCommerce\Pluggable;
/**
 * Mapping Pluggable
 * 
 * Appearance - Switch Option Filter
 * FrontEnd - FORM
 * LSDCommerce - Admin Menu
 * 
 */
 Class LSDC_Pluggable{
	 
	public static function form_register( $args ){
		global $lsdcommerce_form;
		$lsdcommerce_form[$args['id']] = $args;
	}

	public static function form_release( $args ){
		global $lsdcommerce_form;
        if ( in_array( $args['id'], (array)$lsdcommerce_form ) ) // if metode found
        unset( $lsdcommerce_form[$args['id']] );
	}

 }


 Class LSDC_Admin_Settings{
	/**
	 * Settings Filter
	 * 
	 * @block   Appreance - Switch Option
	 * @since   3.0.0
	 */
	 public static function switch_option( $id, $text, $visible = true ){
		add_filter('lsdc_appearance_switch_option', function( $before ) use ( $id, $text, $visible ) {
			$after = array(
				'lsdc_' . $id => array( $text , $visible )
			);
			return array_merge($before, $after);
		});
	}

	public static function add( $args ) {
		add_filter('lsdc_admin_settings_tab', function( $source ) use ( $args ){
			$source = array_merge( $args, $source);
			return $source;
		});
	}
	
	// Remove Existing Tab
	public static function remove( $id ) {
		add_filter('lsdc_admin_settings_tab', function( $source ) use ( $id ){
			unset( $source[$id] );
			return $source;
		});
	}

	public static function tabs(){
		$tablist = array(
			'store'         => array( __( 'Store', 'lsdcommerce' ), 'store.php' ),
			'appearance'    => array( __( 'Appearance', 'lsdcommerce' ), 'appearance.php' ),
			'notifications' => array( __( 'Notifications', 'lsdcommerce' ), 'notifications.php' ),
			'shippings'     => array( __( 'Shipping', 'lsdcommerce' ), 'shippings.php' ),
			'payments'      => array( __( 'Payments', 'lsdcommerce' ), 'payments.php' ),
			'settings'      => array( __( 'Settings', 'lsdcommerce'), 'settings.php' )
		);
			
		$tablist = array_reverse( $tablist );
		if( has_filter('lsdc_admin_settings_tab') ) {
			$tablist = apply_filters( 'lsdc_admin_settings_tab', $tablist );
		}
		return array_reverse( $tablist );
	}
 }



?>