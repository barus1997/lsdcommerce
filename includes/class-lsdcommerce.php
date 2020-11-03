<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    LSDCommerce
 * @subpackage LSDCommerce/includes
 * @author     LSD Plugin <dev@lsdplugin.com>
 */
class LSDCommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LSDCommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Set The Version
		if ( defined( 'LSDCOMMERCE_VERSION' ) ) {
			$this->version = LSDCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		// Set Plugin Name
		$this->plugin_name = 'lsdcommerce';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		$this->templates = array();
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			add_filter('page_attributes_dropdown_pages_args',array( $this, 'register_project_templates' ));
		} else {
			add_filter('theme_page_templates', array( $this, 'add_new_template' ));
		}
		add_filter('wp_insert_post_data',array( $this, 'register_project_templates' ));
		add_filter('template_include',array( $this, 'view_project_template'));
		
		$this->templates = array(
			'store.php' => 'LSDCommerce - Store',
			'member.php' => 'LSDCommerce - Member',
		);

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ));
	}

	public function add_new_template( $posts_templates ) {
		$posts_templates = array_merge( $posts_templates, $this->templates );
		return $posts_templates;
	}

	public function register_project_templates( $atts ) {
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		wp_cache_delete( $cache_key , 'lsdcommerce');
		$templates = array_merge( $templates, $this->templates );
		wp_cache_add( $cache_key, $templates, 'lsdcommerce', 1800 );

		return $atts;
	}

	public function view_project_template( $template ) {
		if ( is_search() ) {
			return $template;
		}


		global $post;
		if ( ! $post ) {
			return $template;
		}

		if ( ! isset( $this->templates[get_post_meta(
			$post->ID, '_wp_page_template', true
		)] ) ) {
			return $template;
		}

		// Allows filtering of file path
		$filepath = apply_filters( 'page_templater_plugin_dir_path', plugin_dir_path( __FILE__ ) );

		// Change your template path
		$global_template = lsdcommerce_template();
		$file = LSDC_PATH . 'templates/' . get_post_meta( $post->ID, '_wp_page_template', true );

		switch ( get_post_meta( $post->ID, '_wp_page_template', true ) ) {
			case 'store.php':
				if( $global_template['store'] != $file  ){
					$file = $global_template['store'];
				}
				break;
			case 'member.php':
				if( $global_template['member'] != $file  ){
					$file = $global_template['member'];
				}
				break;
		}

	

		// Just to be safe, we check if the file exist first
		if ( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		return $template;
	}
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - LSDCommerce_Loader. Orchestrates the hooks of the plugin.
	 * - LSDCommerce_Admin. Defines all hooks for the admin area.
	 * - LSDCommerce_Public. Defines all hooks for the public side of the site.
	 * - Load Core and Pluggable Function
	 * - Load Plugin API
	 * - Load Ecommerce Class
	 * - Load Init.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once LSDC_PATH . 'includes/class-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once LSDC_PATH . 'core/admin/class-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once LSDC_PATH . 'core/public/class-public.php';

        /**
		 * Calling Core Functions and Pluggable Functions
		 */
		require_once LSDC_PATH . 'core/functions/core-functions.php';
		require_once LSDC_PATH . 'core/functions/helper-functions.php';
		require_once LSDC_PATH . 'core/functions/get-functions.php';
		require_once LSDC_PATH . 'core/functions/order-functions.php';
		require_once LSDC_PATH . 'core/functions/product-functions.php';
		require_once LSDC_PATH . 'core/functions/set-functions.php';
		require_once LSDC_PATH . 'core/functions/pluggable.php';
		require_once LSDC_PATH . 'core/functions/updater.php';

		// Register Custom PostType, Metabox and Shortcodes
		require_once LSDC_PATH . 'core/plugin/posttypes.php';
		require_once LSDC_PATH . 'core/plugin/metaboxes.php';
		// require_once LSDC_PATH . 'core/plugin/dashboard.php';
		require_once LSDC_PATH . 'core/plugin/columns.php';
		require_once LSDC_PATH . 'core/plugin/shortcodes.php';
		require_once LSDC_PATH . 'core/plugin/user.php';

		// Core Manager
		require_once LSDC_PATH . 'core/class/class-mail.php';
		require_once LSDC_PATH . 'core/class/class-form.php';
		require_once LSDC_PATH . 'core/class/class-payment-manager.php';
		require_once LSDC_PATH . 'core/class/class-notification-manager.php';
		require_once LSDC_PATH . 'core/class/class-shipping-manager.php';
		require_once LSDC_PATH . 'core/class/class-member.php';
	

		// Defdult Payment
		require_once LSDC_PATH . 'core/payments/class-transfer-bank.php';

		// Default Notification
		require_once LSDC_PATH . 'core/notifications/class-notification-email.php';

		// Default Shipping
		require_once LSDC_PATH . 'core/shipping/class-digital-email.php';
		require_once LSDC_PATH . 'core/shipping/class-rajaongkir.php';
		require_once LSDC_PATH . 'core/shipping/class-physical-rajaongkir.php';

		// require_once LSDC_PATH . 'core/shipping/class-physical-cod.php';
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once LSDC_PATH . 'core/init.php'; // Caling Function to Initiate

		$this->loader = new LSDCommerce_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the LSDCommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function plugins_loaded() {
		// Loading Plugin Text Domain
		load_plugin_textdomain( 'lsdcommerce', false, LSDC_PATH . '/languages/' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new LSDCommerce_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_admin_menu' );
		$this->loader->add_filter( 'admin_init', $plugin_admin, 'load_admin_dependency' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new LSDCommerce_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'single_template', $plugin_public, 'single_template' );
		$this->loader->add_filter( 'init', $plugin_public, 'public_dependency' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    LSDCommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
