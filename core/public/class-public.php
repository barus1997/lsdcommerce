<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       lsdplugins.com
 * @since      1.0.0
 *
 * @package    LSDCommerce
 * @subpackage LSDCommerce/public
 */
class LSDCommerce_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) 
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() 
	{
		// Global Style LSDPlugins like Grid, Responseve etc
		wp_enqueue_style( 'lsdplugins', LSDC_URL . 'assets/css/lsdplugins.css', array(), '1.0.0', 'all' );

		// Loading Theme Style Plugin
		wp_enqueue_style( 'animate', LSDC_URL . 'assets/css/animate.css', array(), '3.5.2', 'all' );
		wp_enqueue_style( 'swiperJS', LSDC_URL . 'assets/lib/swiper/swiper.css', array(), '5.3.6', 'all' );
		wp_register_style( 'lsdcommerce-theme', LSDC_URL . 'assets/lib/lsdcommerce/theme.css', array(), $this->version, 'all' );
		wp_register_style( 'lsdcommerce-theme-single', LSDC_URL . 'assets/lib/lsdcommerce/theme-single.css', array(), $this->version, 'all' );
		wp_register_style( 'lsdcommerce-responsive', LSDC_URL . 'assets/lib/lsdcommerce/responsive.css', array(), $this->version, 'all' );

		// Reset THeme in Member 
		wp_enqueue_style('lsdcommerce-theme');
		wp_enqueue_style('lsdcommerce-theme-single'); 
		wp_enqueue_style('lsdcommerce-responsive');

		// Enquene Font Based on LSDCommerce > Appearance > Font
		$settings = get_option('lsdd_appearance_settings' );
		wp_enqueue_style( $this->plugin_name, LSDC_URL . 'assets/css/lsdcommerce-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'lsdc-google-fonts', '//fonts.googleapis.com/css?family='. esc_attr( ( empty($settings['lsdd_fontlist']) ) ? 'Poppins' : $settings['lsdd_fontlist'] ) , array(), $this->version );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 * - Mustache JS for Templating
	 * - SwiperJS for Checkout Tab
	 * - LSDCommerce Helper for helper functions
	 * - Public LSDCommerce, for Javascript only in public
	 * - Localize for accsing ajax and transfer data to Javascript
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		global $lsdcommerce;
		
		wp_enqueue_script( 'mustache', 'https://ajax.cdnjs.com/ajax/libs/mustache.js/0.3.0/mustache.min.js', null, null, false );
		wp_enqueue_script( 'swiperJS', LSDC_URL . 'assets/lib/swiper/swiper.js', array( 'jquery' ), '5.3.6', false );

		wp_enqueue_script( 'lsdcommerce-helper' , LSDC_URL . 'assets/js/lsdcommerce-helper.js', array(), $this->version, false );
		wp_enqueue_script( $this->plugin_name, LSDC_URL . 'assets/js/lsdcommerce-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-single', LSDC_URL . 'assets/js/lsdcommerce-single.js', array( 'jquery' ), $this->version, false );

		// Enquene Script Checkout just in Checkout Page
		if( is_page(  lsdc_get( 'general_settings', 'checkout_page' ) ) ){
			wp_enqueue_script( $this->plugin_name . '-checkout', LSDC_URL . 'assets/js/lsdcommerce-checkout.js', array( 'jquery' ), $this->version, false );
		}

		wp_localize_script( $this->plugin_name , 'lsdc_pub', array(
			'ajax_url' 		=> admin_url( 'admin-ajax.php' ),
			'ajax_nonce' 	=> wp_create_nonce('lsdc_nonce'),
			'plugin_url' 	=> LSDC_URL,
			'translation' 	=> array(
				'cart_empty' 		=> __( 'Empty', 'lsdcommerce' ),
				'data_incorrect' 	=> __( 'Please, Fill Data Correctly', 'lsdcommerce' ),
			)
		));
    }
    
	/**
	 * Overidde Single template Custom Post Type LSDC Product
	 * to Display custom detail template
	 */
	public function single_template( $template )
	{
        global $post;

        if ( 'lsdc-product' === $post->post_type ) {
            return LSDC_PATH . 'templates/single.php';
        }
	}
	
	/**
	 * Function to Load Public Depedency to Init Action
	 * Load Ajax Function.
	 */
	public function public_dependency()
	{
		// Require Public Ajax
		require_once LSDC_PATH . 'core/public/class-public-ajax.php';
	}

}
