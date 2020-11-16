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
class LSDCommerce_Public
{

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
    public function __construct($plugin_name, $version)
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
        wp_enqueue_style('lsdplugins', LSDC_URL . 'assets/dev/css/utils/lsdplugins.css', array() , '1.0.0', 'all');

        // Loading Theme Style Plugin
        // wp_enqueue_style( 'animate', LSDC_URL . 'assets/css/animate.css', array(), '3.5.2', 'all' );
        wp_enqueue_style('swiper', LSDC_URL . 'assets/lib/swiper/swiper.css', array() , '5.3.6', 'all');
        wp_enqueue_style($this->plugin_name . '-theme', LSDC_URL . 'assets/dev/css/frontend/theme.css', array() , $this->version, 'all');

        wp_register_style($this->plugin_name . '-single', LSDC_URL . 'assets/dev/css/frontend/single.css', array() , $this->version, 'all');
        if( is_singular('lsdc-product') || is_page_template( 'store.php') || is_tax( 'lsdc-product-category' ) ){
            wp_enqueue_style( $this->plugin_name . '-single' );
        }

        if( is_page_template( 'member.php') ){
            wp_enqueue_style($this->plugin_name . '-member', LSDC_URL . 'assets/dev/css/frontend/member.css', array() , $this->version, 'all');
        }
        

        wp_enqueue_style($this->plugin_name . '-responsive', LSDC_URL . 'assets/dev/css/frontend/responsive.css', array() , $this->version, 'all');
        // Enquene Font Based on LSDCommerce > Appearance > Font
        wp_enqueue_style($this->plugin_name, LSDC_URL . 'assets/dev/css/frontend/public.css', array() , $this->version, 'all');
        wp_enqueue_style('lsdcommerce-google-fonts', '//fonts.googleapis.com/css?family=' . esc_attr((empty(lsdc_admin_get('appearance_settings', 'font_family'))) ? 'Poppins' : lsdc_admin_get('appearance_settings', 'font_family')) , array() , $this->version);
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        // wp_enqueue_script( 'mustache', 'https://ajax.cdnjs.com/ajax/libs/mustache.js/0.3.0/mustache.min.js', null, null, false );
        wp_enqueue_script('mustache', LSDC_URL . 'assets/lib/mustache/mustache.min.js', array() , '0.3.0', false);
        wp_enqueue_script('swiper', LSDC_URL . 'assets/lib/swiper/swiper.js', array('jquery') , '5.3.6', false);

        wp_enqueue_script($this->plugin_name . '-helper', LSDC_URL . 'assets/dev/js/utils/lsdcommerce-helper.js', array( 'jquery' ) , $this->version, false);

        // Checkout Page
        if (is_page(lsdc_admin_get('general_settings', 'checkout_page')))
        {
            wp_enqueue_script($this->plugin_name . '-checkout', LSDC_URL . 'assets/dev/js/frontend/lsdcommerce-checkout.js', array(
                'jquery'
            ) , $this->version, false);
        }

        // Single Product
        if (is_singular('lsdc-product') || is_tax( 'lsdc-product-category' ))
        {
            wp_enqueue_script($this->plugin_name . '-single', LSDC_URL . 'assets/dev/js/frontend/lsdcommerce-single.js', array(
                'jquery'
            ) , $this->version, false);
        }

        wp_enqueue_script($this->plugin_name, LSDC_URL . 'assets/dev/js/frontend/lsdcommerce-public.js', array(
            'jquery'
        ) , $this->version, false);

        wp_localize_script($this->plugin_name, 'lsdc_pub', array(
            'ajax_url' => esc_js(admin_url('admin-ajax.php')) ,
            'ajax_nonce' => esc_js(wp_create_nonce('lsdc_nonce')) ,
            'plugin_url' => esc_js(LSDC_URL) ,
            'site_url' => esc_js( get_site_url() ),
            'translation' => array(
                'cart_empty' => __('Kosong', 'lsdcommerce') ,
                'data_incorrect' => __('Silahkan isi data dengan benar', 'lsdcommerce') ,
            )
        ));
    }

    /**
     * Function to Load Public Depedency to Init Action
     * Load Ajax Function.
     */
    public function enquene_dependency()
    {
        require_once LSDC_PATH . 'core/public/class-public-ajax.php';
    }

    /**
     * Overidde Single template Custom Post Type LSDC Product
     * to Display custom detail template
     */
    public function single_template($template)
    {
        global $post;

        if ('lsdc-product' === $post->post_type)
        {
            return LSDC_PATH . 'templates/single.php';
        }

        return $template;
    }
}

