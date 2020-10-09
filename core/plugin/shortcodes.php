<?php

// Shortcode Checkout
function lsdcommerce_checkout_sc( $attr ) 
{	
    if( ! is_admin() ){
        require_once LSDC_PATH . 'templates/checkout.php';
    }
}
add_shortcode( 'lsdcommerce_checkout', 'lsdcommerce_checkout_sc' );

// Shortcode Member
function lsdcommerce_member( $attr ) 
{	
    // Check Login or Not
    if( ! is_user_logged_in() ) {
		$output = lsdcommerce_template_login();
	}else{
        $output = lsdcommerce_member_template();
    }
	return $output;
}
add_shortcode( 'lsdcommerce_member', 'lsdcommerce_member' );

function lsdcommerce_member_template(){
    ob_start(); $current_user = wp_get_current_user(); $avatar_url = get_avatar_url( get_current_user_id() );    //update_user_meta($current_user->ID, 'verification', '' );?>

    <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">


    <div class="header columns" style="padding:10px;">
        <div class="column col-12">

            <!-- HeadBar -->
            <div class="navbar">
                <div class="navbar-section">
                    <a class="btn mx-2" href="https://docs.lsdcommerce.com/">Documentation</a>
                </div>
                <div class="navbar-center mt-2"><img src="https://lsdplugins.com/wp-content/uploads/2020/06/LSD-Plugins-Logo-50x32.png" alt="lsdcommerce"></div>
                <div class="navbar-section"><a class="btn btn-primary mx-2" href="<?php echo wp_logout_url(get_permalink()); ?>">Sign out</a>
                </div>
            </div>

            <!-- User Information -->
            <div class="text-center" style="margin-top:30px;">

                <!-- User Account -->
                <figure class="avatar avatar-xl">
                    <div class="mb-review-avatar text-center" style="margin:-2px;">
                        <?php if ( ! $avatar_url ) : ?>
                            <img src="<?php echo lsdcommerce_URL . 'images/avatar.png'; ?>" alt="<?php echo $current_user->user_nicename; ?>">
                        <?php else : ?>
                            <img src="<?php echo esc_url( $avatar_url ); ?>" />
                        <?php endif; ?>    
                    </div>
                </figure>
                <h6 class="empty-title h5 mt-2 mb-2 text-center"><?php echo strtoupper( $current_user->user_nicename ); ?></h6>

            </div>
        </div>
    

        <div class="column col-12">
            <div class="column col-4 col-sm-12" style="margin:20px auto -15px;">
                <ul class="tab tab-block lsdc-nav-tabs">
                    <li class="tab-item active"><a href="#download"><?php _e( 'Download' , 'lsdc' ); ?> </a></li>
                    <!-- <li class="tab-item"><a href="#order"><?php //_e( 'Order' , 'lsdc' ); ?></a></li> -->
                    <!-- <li class="tab-item"><a href="#profile"><?php //_e( 'Profile' , 'lsdc' ); ?></a></li> -->
                </ul>
            </div>
        </div>
   
    </div>

    
    <style>
        table td,
        table tr,
        table,
        th {
            line-height: normal;
            border: none !important;
        }

        #listing-download .col-6{
            margin-bottom: 20px;
        }
    </style>

    <div id="download" class="content columns tab-pane active" style="padding:10px;background:#FAFAFA;">
        <div class="container">

            <div class="columns">
                <?php 
                require_once LSDC_LM_PATH . 'core/class-db-licenses.php';
                $licenses = new LSDC_License_Manager_DB;
                $licenses = $licenses->get_licences( array( 'customer_id' => $current_user->ID ) );


             
                ?>
                <div id="listing-download" class="column col-7 columns" style="margin: 30px auto;">
                    <?php foreach ($licenses as $key => $item) : ?>
                        <?php 
                            if( $item->date_expired ){
                                $expired    = strtotime( $item->date_expired ); 
                            }else{  // Checking Expired License
                                $create = date( 'Y-m-d H:i:s', strtotime( $item->date_created ));
                                $expired = strtotime( $create . '+' . $item->day_expired . ' days' );
                            }

                            $today      = strtotime('now');
                            $timeleft   = $expired - $today;
                            $daysleft   = round((($timeleft/24)/60)/60); // Output As a Day

                            if( $daysleft <= 0 ){
                                $daysleft = 'Expired';
                            }else{
                                $daysleft = $daysleft . ' Days left';
                            }

                            // Checking Latest Product URL
                            $plugin_info = get_post_meta( $item->product_id, '_plugin_info', true );
                            $update_changelog = get_post_meta( $item->product_id, '_lsdl_update_changelog', true );
                            
                            // Processing Changelog
                            $changelog = '';
                            foreach ( array_reverse( $update_changelog ) as $key => $version) {
                                if ( strtotime( $version['datetime'] ) <= strtotime( current_time('Y-m-d H:i:s') ) ) {
                                    $changelog_version = '<h4>v'. $version['version'] .' - '.  lsdc_date_format( $version['datetime'] ) .'</h4><ul>';
                                    foreach ($version['features'] as $key => $feature) {
                                        $changelog_version .= '<li>'. $feature .'</li>';
                                    }
                                    $changelog_version .= '</ul>';
                                    $changelog .= $changelog_version;
                                }
                            }
                            
                            // Newest
                            $newest = array();
                            foreach ( $update_changelog as $key => $version) {
                                if ( strtotime( $version['datetime'] ) <= strtotime( current_time('Y-m-d H:i:s') ) ) {
                                    $newest = $version;
                                }
                            }
                            if( ! empty( $item->domain ) ){
                                $parseArray = json_decode( $item->domain );
                            }
                        ?>
                        <div class="column col-6">
                            <div class="card" style="padding:13px;position:relative;">
                                <div class="card-subtitle" style="line-height:normal;margin-bottom:0;"><?php echo get_the_title($item->product_id); ?> </div>
                                <div class="card-title h5 <?php echo $item->status != 'active' ? 'text-gray' : 'text-success'; ?>" style="line-height:normal">
                                <?php echo strtoupper( $item->status ); ?> [<?php echo count( $parseArray ); ?>/<?php echo $item->max_domain; ?>]</div>
                                <span class="label <?php echo $daysleft == 'Expired' ? '' : 'label-primary'; ?>" style="position:absolute;right:0;padding: 5px 10px;"><?php echo $daysleft; ?></span>
                                <div class="domain-list" style="margin-top:10px;">
                                <?php 
                                    if( $parseArray ){
                                        foreach ( $parseArray as $key => $domain) {
                                            echo '<span class="label label-success" style="margin-bottom: 5px;">' . $domain . '</span> ';
                                        }
                                    }
                                    $parseArray = [];
                                ?>
                                </div>
                        
                                <input type="text" class="form-input" onClick="this.select();"  value="<?php echo $item->license_key; ?>" style="padding:8px !important;margin-top:10px;cursor:pointer">
                                <?php if( $newest['file_url'] ) : ?>
                                    <a href="<?php echo esc_url( $newest['file_url'] ); ?>" class="btn btn-link"><?php _e('Download', 'lsdc'); ?> <?php echo esc_attr( $newest['version'] ); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>    
                </div>
            </div>
    
        </div>
    </div>
    
    <!-- Package -->
    <div id="order" class="content columns tab-pane" style="padding:10px;background:#FAFAFA;line-height:normal;">
        <div class="container">
            <?php 
            
            // require_once( lsdcommerce_PATH . 'admin/class-lsdcommerce-order.php' );

            // $orders_db = new SP_Orders_DB;
            // $orders = $orders_db->get_orders();
            ?>

            <?php //if( $orders ) : ?>
                <!-- <div class="column col-6 columns" style="margin: 40px auto;">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sender</th>
                                <th>Expired</th>
                                <th>Payment</th>
                                <th>Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php //foreach ($orders as $key => $value) : ?>
                                <tr>
                                    <td><?php //echo lsdcommerce_date_format($value->date); ?></td>
                                    <td><?php //echo ucfirst( $value->sender ); ?></td>
                                    <td><?php //echo lsdcommerce_date_format($value->expired); ?></td>
                                    <td><?php //echo strtoupper($value->gateway); ?></td>
                                    <td><?php //echo lsdcommerce_currency_format($value->total, $value->currency); ?></td>
                                    <td><?php //echo ucfirst($value->status); ?></td>
                                </tr>
                            <?php //endforeach; ?>
                        </tbody>
                    </table>
                </div> -->
            <?php //endif; ?>
        </div>
    </div>
    </div>
    
    <!-- Profile -->
    <div id="profile" class="content columns tab-pane" style="padding:10px;background:#FAFAFA;line-height:normal">
  
    </div>
    
    </div>
        <style>
            .form-horizontal input{
                background: #fff; line-height:normal;
            }
            .tab-pane{
                display:none !important;
            }
            .tab-pane.active{
                display:flex !important;
            }
        </style>
        <script>
            window.addEventListener("load", function() {
        
                var myTabs = document.querySelectorAll("ul.lsdc-nav-tabs > li");
                function myTabClicks(tabClickEvent) {
                    for (var i = 0; i < myTabs.length; i++) {
                        myTabs[i].classList.remove("active");
                    }
                    var clickedTab = tabClickEvent.currentTarget;
                    clickedTab.classList.add("active");
                    tabClickEvent.preventDefault();

                    var myContentPanes = document.querySelectorAll(".tab-pane");
                    for (i = 0; i < myContentPanes.length; i++) {
                        myContentPanes[i].classList.remove("active");
                    }
                    var anchorReference = tabClickEvent.target;
                    var activePaneId = anchorReference.getAttribute("href");
                    var activePane = document.querySelector(activePaneId);
                    activePane.classList.add("active");
                    window.scrollTo(0,0);
                }
                for (i = 0; i < myTabs.length; i++) {
                    myTabs[i].addEventListener("click", myTabClicks)
                }
            });
    
        </script>
        <?php		
           
        $render = ob_get_clean();
        return $render;
}

function lsdcommerce_template_login(){
	ob_start(); ?>
	

    <link rel="stylesheet" href="https://unpkg.com/spectre.css/dist/spectre.min.css">
    <div class="container" style=" justify-content: center;
  align-items: center;">
        <div class="columns ">
            <div class="column col-lg-4"></div>
            <div class="column col-lg-4 col-xs-12" style="margin-top: 13%;">
                <div class="text-center">
                    <img src="https://lsdplugins.com/wp-content/uploads/2020/06/SQUARE-LSD.png" alt="LSD Plugins Login" width="80">
                </div>
     
                <div class="column ">

                    <form action="" method="post">
                        
                        <div class="form-group">
                            <label class="form-label" for="lsdcommerce_email"><?php _e( 'Email', 'lsdc'); ?></label>
                            <input class="form-input"  name="lsdcommerce_email" type="text" placeholder="E-mail" style="width:100%" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="lsdcommerce_password"><?php _e( 'Password', 'lsdc'); ?></label>
                            <input class="form-input" name="lsdcommerce_password" type="password" style="width:100%" placeholder="Password" required>
                        </div>

                        <div class="form-group">
                            <input type="hidden" name="lsdcommerce_login_nonce" value="<?php echo wp_create_nonce('lsdcommerce-login'); ?>"/>
                            <input class="btn btn-primary btn-block" type="submit" value="Sign In">
                        </div>

                        <?php lsdcommerce_error_messages(); ?>
                    </form>
                </div>
            </div>
            <div class="column col-lg-4"></div>                                     
        </div>
    </div>


	<?php		
    $render = ob_get_clean();
    return $render;
}

/**
 * @package LSDCommerce
 * @subpackage Member
 * 
 * Handle Login
 */
function lsdcommerce_login_action() {
	
	if( isset( $_POST['lsdcommerce_email'] ) && wp_verify_nonce( $_POST['lsdcommerce_login_nonce'], 'lsdcommerce-login') ) { // Checking Email and Nonce

        $email = $_POST['lsdcommerce_email'];
        
		if( empty( $email ) ){ //No email
			lsdcommerce_errors()->add('empty_email', __( 'Email field is empty.', 'lsdc' ) );
		}else if( !filter_var($email, FILTER_VALIDATE_EMAIL) ){ //Invalid Email
			lsdcommerce_errors()->add('invalid_email', __( 'Email is invalid.' , 'lsdc' ));
        }
        
        // $user = get_user_by('login', $_POST['lsdcommerce_email']);
		$user = get_user_by( 'email', trim($email) );

		if( !$user ) { // Check email exist
			lsdcommerce_errors()->add('invalid_email', __( 'Email not registered.' , 'lsdc' ));
        }
        
        // Check Password Empty
		if(!isset($_POST['lsdcommerce_password']) || $_POST['lsdcommerce_password'] == '') {
			lsdcommerce_errors()->add('empty_password', __( 'Please enter a password', 'lsdc' ));
		}

		// Checking Password
		if( $_POST['lsdcommerce_password'] && isset($user->user_pass) ){
			if( ! wp_check_password($_POST['lsdcommerce_password'], $user->user_pass, $user->ID ) ) {
				lsdcommerce_errors()->add('empty_password', __('Email or password combination is incorrect'));
			}
		}

		// retrieve all error messages
		$errors = lsdcommerce_errors()->get_error_messages();
 
		// on Empty Error, Set Login
		if (empty($errors) ) {
            clean_user_cache($user->ID);
            wp_clear_auth_cookie();
            wp_set_current_user( $user->ID, $user->user_login );
            wp_set_auth_cookie( $user->ID, true, true );
            update_user_caches( $user );

            wp_safe_redirect( site_url('member') );
			exit();
		}
	}
}
add_action('init', 'lsdcommerce_login_action');


// Error Handle
function lsdcommerce_errors(){
    static $wp_error; // Will hold global variable safely
    return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
}

function lsdcommerce_error_messages() {
	if( $codes = lsdcommerce_errors()->get_error_codes() ) {
		echo '<div class="lsdcommerce-errors">';
		    // Loop error codes and display errors
		   foreach($codes as $code){
		        $message = lsdcommerce_errors()->get_error_message($code);
		        echo '<span class="error"><strong>' . __('Error') . '</strong>: ' . $message . '</span><br/><br/>';
		    }
		echo '</div>';
	}	
}

require_once LSDC_PATH . 'core/shortcodes/reset-password.php'; ?>