<?php
require_once LSDC_PATH . 'core/class/class-log.php';
require_once LSDC_PATH . 'core/class/class-order.php';

use LSDCommerce\Logger\LSDC_Logger;
use LSDCommerce\Order\LSDC_Order;

/**
 * Class To Handle Public AJAX
 */
class LSDCommerce_Public_AJAX
{

    public function __construct()
    {
        add_action('wp_ajax_lsdcommerce_create_order', [ $this, 'create_order' ]);
        add_action('wp_ajax_nopriv_lsdcommerce_create_order', [ $this, 'create_order' ]);
    }

    public function create_order()
    {
        $_REQUEST = array_map('stripslashes_deep', $_REQUEST); // Stripslash
        // Checking Token and Nonce
        if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.'); //Token
        if (!wp_verify_nonce($_REQUEST['nonce'], 'checkout-nonce')) wp_send_json_error('Busted.'); //Nonce
        // Checking Token
        $user_token = sanitize_text_field($_REQUEST['token']);
        $token = sanitize_text_field(explode('-', $user_token) [0]);
        $server_token = get_transient('lsdcommerce_checkout_' . $token); // Get Transient from Server based on Client Token
        $timestamp = strtotime(lsdc_date_now()) - strtotime($server_token);
        $validation = false;

        #Flooding Blocker
        if ($timestamp > 600)
        {
            LSDC_Logger::log('Checkout Flooding from ' . lsdc_get_ip() , LSDC_Logger::WARNING);
            setcookie( "_lsdcommerce_token" , null, time() - 3600 , "/"  );
            die("_token_expired");
        }

        // Have Checkout Token
        if ($user_token)
        {
            $order_object = $_REQUEST['order']; //from JS ( Shipping, Customer, Products )
            $user = get_user_by('email', sanitize_email($order_object['form']['email']));
            if ($user->ID) // Email already exist
            
            {
                if (is_user_logged_in()) // already log-in
                
                {
                    // Member Checkout
                    $validation = true;
                }
                else
                {
                    die("_email_registered");
                }
            }
            else
            {
                // Guest Checkout
                $validation = true;
            }
        }
        else 
        // Empty user token
        
        {
            die("_token_expired");
        }

        // Validation True and Create Order, Generate Thankyou URL
        if ($validation)
        {
            $order_object['order_key'] = $token;
            $new = new LSDC_Order;
            $new->create_order($order_object);
            echo $new->thankyou_url($token);
        }

        wp_die();
    }
}
new LSDCommerce_Public_AJAX; // Auto Initiate

/**
 * Grouping Shipping AJAX
 */
class LSDCommerce_Shipping_AJAX
{
    public function __construct()
    {
        // Set Frontend Shipping
        add_action('lsdcommerce_checkout_shipping', [$this, 'shipping_method']);

        // Ajax Shipping Package
        add_action('wp_ajax_nopriv_lsdcommerce_shipping_package', [$this, 'shipping_package']);
        add_action('wp_ajax_lsdcommerce_shipping_package', [$this, 'shipping_package']);
    }

    /**
     * Shipping Method
     */
    public function shipping_method()
    {
        global $lsdcommerce_shippings;

        $cookie_cart = isset($_COOKIE['_lsdcommerce_cart']) ? $_COOKIE['_lsdcommerce_cart'] : null;
        $carts = (array)json_decode(stripslashes($cookie_cart));

        // Checking Shipping Type on Cart
        $shipping_physical = false;
        $shipping_digital = false;

        if (isset($carts))
        {
            foreach ($carts as $key => $product)
            {
                $product_id = lsdc_product_extract_ID($product->id);
                $shipping_type = get_post_meta($product_id, '_shipping_type', true);
                switch ($shipping_type)
                {
                    case 'physical':
                        $shipping_physical = true;
                    break;
                    case 'digital':
                        $shipping_digital = true;
                    break;
                }
            }
        }

        $shipping_physical_list = array();
        $shipping_digital_list = array();

        if (isset($lsdcommerce_shippings))
        {
            foreach ($lsdcommerce_shippings as $key => $class)
            {
                $object = new $class;
                if ($shipping_physical && $object->type == 'physical')
                {
                    if ($object->get_status() == 'on')
                    {
                        $shipping_physical_list[] = $object->name;
                    }
                }

                if ($shipping_digital && $object->type == 'digital')
                {
                    if ($object->get_status() == 'on')
                    {
                        $shipping_digital_list[] = $object->name;
                    }
                }
            }
        }
        ?>
        
        <?php if (!empty($shipping_digital_list)): ?>
            <h6 class="text-primary font-weight-medium lsdp-mb-10"><?php _e("Pengiriman Digital", 'lsdcommerce'); ?></h6>
                <div id="digital-shipping" class="lsdp-row no-gutters radio-courier">
                    <?php do_action('lsdcommerce_shipping_digital_services'); ?>
                </div>
            <hr>
        <?php
        endif; ?>
    
        <?php if (!empty($shipping_physical_list)): ?>
            <h6 class="text-primary font-weight-medium lsdp-mb-10"><?php _e("Pengiriman Fisik", 'lsdcommerce'); ?></h6>
            <?php do_action('lsdcommerce_shipping_physical_control'); ?>
            
            <div id="physical-shipping" class="lsdp-row no-gutters radio-courier">
                <?php do_action('lsdcommerce_shipping_physical_services'); ?>
            </div>
        <?php
        endif; ?> 
    
        <!-- Empty and Not Set Shipping Channel -->
        <?php if (empty($shipping_physical_list) && empty($shipping_digital_list)): ?>
            <div class="lsdp-alert lsdc-info lsdp-mt-10 lsdp-mx-10">
                <?php _e('Metode pengiriman tidak tersedia, Hubungi Administrator', 'lsdcommerce'); ?>
            </div>
        <?php
        endif;
    }

    public function shipping_package()
    {
        global $lsdcommerce_shippings;

        if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');
        if (!wp_verify_nonce($_REQUEST['nonce'], 'checkout-nonce')) die('Busted'); 

        $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
        $shipping_data = $_REQUEST['shipping']; // Token, Destination, Products
        // Load Shipping ON : Get Package based on User Data
        $shipping_physical_results = array();
        if (isset($lsdcommerce_shippings))
        {
            foreach ($lsdcommerce_shippings as $key => $class)
            {
                $object = new $class;
                if ($object->type == 'physical')
                {
                    if ($object->get_status() == 'on')
                    {
                        echo $object->shipping_list($shipping_data);
                    }
                }

            }
        }
        wp_die();
    }
}
new LSDCommerce_Shipping_AJAX; // Auto Initiate


/**
 * Grouping Checkout AJAX
 */
class LSDCommerce_Checkout_AJAX
{
    public function __construct()
    {
        add_action('wp_ajax_nopriv_lsdcommerce_checkout_extra_pre', [$this, 'checkout_pre_processing']);
        add_action('wp_ajax_lsdcommerce_checkout_extra_pre', [$this, 'checkout_pre_processing']);
    }

    public function checkout_pre_processing()
    {
        $extras = array();
        if (!check_ajax_referer('lsdc_nonce', 'security')) wp_send_json_error('Invalid security token sent.');
        if (!wp_verify_nonce($_REQUEST['nonce'], 'checkout-nonce')) die('Busted');

        $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
        empty($_REQUEST['extras']) ? $extras = array() : $extras = $_REQUEST['extras']; // Extras Data from Javascript

        // Calculating Product -> Shipping Package
        $shipping_type = array();
        $weights = 0;
        $grandtotal = 0;

        $products = $extras['products'];
        foreach ($products as $key => $product)
        {
            $variation_id = null;
            $product_id = lsdc_product_extract_ID($product['id']);
            $product_price = lsdc_product_price($product_id);
            $product_title = get_the_title($product_id);

            $limit_order = get_post_meta($product_id, '_limit_order', true);
            $product_qty = $limit_order > abs($product['qty']) ? abs($product['qty']) : abs($limit_order);

            /* Start - Pro CODE, Ignore, Don't Delete */
            // Checking Variation Exist in Product
            if (lsdc_product_variation_exist($product_id, sanitize_text_field($product['id'])))
            {
                // Assign Variation ID
                $variation_id = sanitize_text_field($product['id']);
                $extras['products'][$key]['variation_id'] = $variation_id;
                $extras['products'][$key]['variations'][] = array( $variation_id, lsdc_product_variation_price($product_id, $variation_id) - $product_price );

                // Product Price based on Variation
                $product_price = lsdc_product_variation_price($product_id, $variation_id);
                $product_title = $product_title . ' - ' . lsdc_product_variation_label($product_id, $variation_id);
                // Limit Order by Variation - Soon
                // Limit Stock by Variation - Soon
            }
            /* End - Pro CODE, Ignore, Don't Delete */

            // ReAssign to Order Object
            $extras['products'][$key]['id'] = $product_id;
            $extras['products'][$key]['qty'] = $product_qty;
            $extras['products'][$key]['price_unit'] = $product_price;
            $extras['products'][$key]['price_unit_text'] = $product_price != 0 ? lsdc_currency_format(false, $product_price) : __('Gratis', 'lsdcommerce');
            $extras['products'][$key]['weight_unit'] = abs(get_post_meta($product_id, '_physical_weight', true));
            $extras['products'][$key]['title'] = $product_title;
            $extras['products'][$key]['thumbnail'] = get_the_post_thumbnail_url($product_id, 'lsdc-thumbnail-mini');
            $extras['products'][$key]['total'] = lsdc_currency_format(true, $product_price * $product_qty);

            $grandtotal += $product_price * $product_qty;
            $weights += abs(get_post_meta($product_id, '_physical_weight', true)) * $product_qty;

            array_push($shipping_type, get_post_meta($product_id, '_shipping_type', true));
        }
        $extras['extras']['shipping']['weights'] = $weights; //Assign Weights for Shipping Calculation
        $extras['extras']['shipping']['types'] = $shipping_type;
        $extras['products']['grandtotal'] = $grandtotal;

        // Calculating Extras Cost
        $extra_cost = 0;
        $messages = array();

        if (has_filter('lsdcommerce_payment_extras'))
        {
            // Procssing Raw Data from JS to Clean PHP
            $extras = apply_filters('lsdcommerce_payment_extras', $extras);

            if ($extras)
            {
                // unset( $extras['products'] );
                // unset( $extras['extras'] );
                // unset( $extras['order_key'] );
                foreach ($extras as $key => $item)
                {
                    if (isset($item['cost']))
                    { //cost exist
                        $extra_cost += intval($item['cost']); // calc every extra
                        
                    }

                    if (isset($item['messages']))
                    { //
                        $messages[$key] = $item['messages'];
                    }
                }
            }

            // var_dump( $extras );
            
        }

        $templates = null;
        if ($extras)
        {
            $templates .= '<div id="checkout-extras" data-total="' . intval($extra_cost) . '">';
            $templates .= '<table class="table table-borderless"><tbody>';
            foreach ($extras as $key => $item)
            {
                if (!empty($item) && !empty($item['value']))
                {
                    $templates .= '<tr>';
                    $label = esc_attr($item['label']);
                    if (isset($item['bold']) && !empty($item['bold']))
                    {
                        $bold = '<span class="display-block text-uppercase font-weight-medium lsdc-theme-color">' . $item['bold'] . '</span>';
                    }
                    else
                    {
                        $bold = null;
                    }

                    if ($item['sign'] == '-')
                    {
                        $sign = '-';
                    }
                    else
                    {
                        $sign = '';
                    }
                    $templates .= '<td>' . $label . $bold . '</td>';
                    $templates .= '<td class="text-right">' . $sign . $item['value'] . '</td>';
                    $templates .= '</tr>';
                }
            }
            $templates .= '</tbody>';
            $templates .= '</table>';
            $templates .= '</div>';
        }

        echo json_encode(array(
            'error' => $messages,
            'template' => $templates
        ));
        wp_die();
    }
}
new LSDCommerce_Checkout_AJAX;  // Auto Initiate


class LSDCommerce_Member_AJAX{
    public function __construct()
    {
        add_action('wp_ajax_nopriv_lsdcommerce_member_view_order', [$this, 'member_view_order']);
        add_action('wp_ajax_lsdcommerce_member_view_order', [$this, 'member_view_order']);

        add_action('wp_ajax_nopriv_lsdcommerce_member_view_shipping', [$this, 'member_view_shipping']);
        add_action('wp_ajax_lsdcommerce_member_view_shipping', [$this, 'member_view_shipping']);

        add_action( 'wp_ajax_nopriv_lsdcommerce_member_change_password', [ $this, 'member_change_password' ] );
		add_action( 'wp_ajax_lsdcommerce_member_change_password', [ $this, 'member_change_password' ] );
    }

    public function member_view_order()
    {
        // Soon : Snapshot Products
		if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
        if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'member-nonce' ) ) { die('Busted'); }

        $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
		$order_id = abs( $_REQUEST['postid'] );
		$postdata = sanitize_text_field( $_REQUEST['postdata'] );
        
        if( get_current_user_id() == get_post_field ('post_author', $order_id) ) : ?>
          <table>
            <tr>
              <th><?php _e( 'Produk', 'lsdcommerce' ); ?></th>
              <th><?php _e( 'Jumlah', 'lsdcommerce' ); ?></th>
              <th><?php _e( 'Total', 'lsdcommerce' ); ?></th>
            </tr>
            <?php 
          
            $products = (array)json_decode(get_post_meta( $order_id, 'products', true));
            $total = get_post_meta( $order_id, 'total', true);
            $subtotal = 0;
            ?>
            <?php foreach ( $products as $key => $product) : ?>
              <tr>
                <td><?php echo esc_attr( $product->title ); ?></td>
                <td><?php echo esc_attr( $product->qty ); ?> x <?php echo $product->price_unit_text; ?></td>
                <td><?php echo esc_attr($product->total ); ?></td>
              </tr>
              <?php $subtotal += lsdc_currency_clean( $product->price_unit_text ); ?>
            <?php endforeach; ?>
            <tr>
              <td></td>
              <td><?php _e( 'Sub Total', 'lsdcommerce' ); ?></td>
              <td class="text-bold"><?php echo lsdc_currency_format( true, $subtotal ); ?></td>
            </tr>

            <?php $extras = json_decode( get_post_meta( $order_id, 'extras', true ) ); ?>
            <?php $extra = 0; if( $extras ) : foreach ( $extras as $key => $item) : ?>
              <?php if( isset($item->label) &&  $item->label ) : ?>
                  <tr>
                      <td></td>
                      <td>
                          <?php echo esc_attr( $item->label ); ?>
                          <?php if( isset( $item->bold ) ) : ?>
                          <small class="d-block lsdp-mt-5 text-primary"> 
                              <?php echo esc_attr( $item->bold ); // For Shipping ?>
                          </small>
                          <?php endif; ?>
                      </td>
                      <td>
                          <?php echo $item->sign == '-' ? esc_attr( $item->sign ) : ''; ?><?php echo $item->value; ?>
                      </td>
                  </tr>
              <?php endif; ?>
              <?php if( isset($item->cost) ) : ?>
                  <?php $extra += intval( $item->cost ); ?>
              <?php endif; ?>
              <?php endforeach; endif; ?>

            <tr>
              <td></td>
              <td><?php _e( 'Total', 'lsdcommerce' ); ?></td>
              <td class="text-bold"><?php echo lsdc_currency_format( true, $total ); ?></td>
            </tr>

          </table>
<!-- 
          <ul>
            <li>Dibayar : 10 Menit yang Lalu</li>
            <li>Dikirim : 2 jam yang lalu</li>
            <li>Diterima : 10 menit yang lalu</li>
          </ul>

          <br>
          <button class="lsdp-btn lsdc-btn"> Diterima </button><br> -->
        <?php endif;
        wp_die();
    }

    public function member_view_shipping(){

        if ( ! check_ajax_referer( 'lsdc_nonce', 'security' ) )  wp_send_json_error( 'Invalid security token sent.' );
        if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'member-nonce' ) ) { die('Busted'); }

        $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
		$order_id = abs( $_REQUEST['postid'] );
		$postdata = sanitize_text_field( $_REQUEST['postdata'] );

        if( get_current_user_id() == get_post_field ('post_author', $order_id) ) : ?>
          <table>
            <tr>
              <th><?php _e( 'Pesanan', 'lsdcommerce' ); ?></th>
              <th><?php _e( 'Produk', 'lsdcommerce' ); ?></th>
              <th><?php _e( 'Versi', 'lsdcommerce' ); ?></th>
              <th><?php _e( 'Tindakan', 'lsdcommerce' ); ?></th>
            </tr>
            <?php 
              $products = (array)json_decode(get_post_meta( $order_id, 'products', true));
              $total = get_post_meta( $order_id, 'total', true);
              $subtotal = 0;
              ?>
              <?php foreach ( $products as $key => $product) : ?>
              
                <tr>
                  <td>#<?php echo lsdc_product_extract_ID( $product->id ); ?></td>
                  <td><?php echo get_the_title( $product->id ); ?></td>
                  <td><?php echo lsdc_product_download_version( lsdc_product_extract_ID( $product->id ) ); ?></td>
                  <?php if( lsdc_product_type( $product->id ) == 'digital' ) : ?>
                    <td><a href="<?php echo lsdc_product_download_link( lsdc_product_extract_ID( $product->id ) ); ?>" class="text-primary"><?php _e( 'Unduh', 'lsdcommerce' ); ?></a></td>
                  <?php else: ?>
                    <td><a class="text-primary" target="_blank" href="https://cekresi.com/?noresi=<?php echo esc_attr( get_post_meta( $order_id , 'resi', true ) ); ?>"><?php _e( 'Cek Resi', 'lsdcommerce' ); ?></a></td>
                  <?php endif; ?>
                </tr>
            
              <?php endforeach; ?>
          </table>
          <?php endif; 
          wp_die();
    }

    public function member_change_password()
    {
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
new LSDCommerce_Member_AJAX;
?>