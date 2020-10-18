<?php 
set_lsdcommerce();
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

get_header(); ?>

<?php if( is_user_logged_in() ) : ?>

  <style>

    .tabs-component input[type=radio] {
      display: none !important;
    }

    .tabs-component [type=radio]:checked + label.tab,
    .tabs-component [type=radio]:not(:checked) + label.tab {
      padding-left: 0;
    }

    .tabs-component label.tab {
      display: inline-block; 
      cursor: pointer;
      padding: 10px 20px !important;
      text-align: center;
    }

    .tabs-component label.tab:after,
    .tabs-component label.tab:before {
      display: none; 
    }

    .tabs-component label.tab:last-of-type {
      border-bottom: none
    }

    .tabs-component label.tab:hover {
      background: #eee 
    }

    .tabs-component input[type=radio]:checked + label.tab {
      border-bottom: 3px solid var(--lsdc-color);
      color : var(--lsdc-color);
      margin: 0;
      margin-bottom: 2px;
    }

    .tabs-component .tab-body {
      position: absolute;
      opacity: 0;
      padding: 13px;
    }

    .tab-body-component {
      border-top: #ddd 3px solid;
      margin-top: -5px;
      position: initial
    }

    #tab1:checked~.tab-body-component #tab-body-1,
    #tab2:checked~.tab-body-component #tab-body-2,
    #tab3:checked~.tab-body-component #tab-body-3,
    #tab4:checked~.tab-body-component #tab-body-4 {
      position: relative;
      top: 0;
      opacity: 1
    }

  </style>

  <div id="lsdcommerce">
    <main class="page-content lsdc-bg-color max480">
    <input type="hidden" id="member-nonce" value="<?php echo wp_create_nonce( 'member-nonce' ); ?>" />

      <div class="tabs-component">
        <input type="radio" name="tab" id="tab1" checked="checked"/>
        <label class="tab" data-linking="tab1" for="tab1">Dashboard</label>

        <input type="radio" name="tab" id="tab2"/>
        <label class="tab" data-linking="tab2" for="tab2">Pembelian</label>

        <input type="radio" name="tab" id="tab3"/>
        <label class="tab" data-linking="tab3" for="tab3">Pengiriman</label>

        <input type="radio" name="tab" id="tab4"/>
        <label class="tab" data-linking="tab4" for="tab4">Profile</label>

        <div class="tab-body-component">

          <!-- Dashboard -->
          <div id="tab-body-1" class="tab-body">
            <?php $current_user = wp_get_current_user(); ?>
            <?php _e( 'Welcome', 'lsdcommerce' ); ?>, <span class="text-primary"><?php echo lsdc_get_user_name( $current_user->ID ); ?></span><br><br>

            <table>
              <tr>
                <th><?php _e( 'Order', 'lsdcommerce' ); ?></th>
                <th><?php _e( 'Download', 'lsdcommerce' ); ?></th>
                <th><?php _e( 'Pengiriman Digital', 'lsdcommerce' ); ?></th>
                <th><?php _e( 'Pengiirman Fisik', 'lsdcommerce' ); ?></th>
              </tr>
              <tr>
                <td><?php echo count_user_posts( $current_user->ID, 'lsdc-order' ); ?> <?php _e( 'Order', 'lsdcommerce' ); ?></td>
                <td></td>
                <td></td>
                <td></td>
              </tr>
            </table>

            <a class="lsdp-btn lsdc-btn btn-primary" href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php _e( 'Logout', 'lsdcommerce' ); ?></a>
          </div>
          
          <!-- Purchase -->
          <div id="tab-body-2" class="tab-body">
            <table>
                <tr>
                  <th><?php _e( "Pesanan", 'lsdcommerce' ); ?></th>
                  <th><?php _e( "Status", 'lsdcommerce' ); ?></th>
                  <th><?php _e( "Total", 'lsdcommerce' ); ?></th>
                  <th><?php _e( "Tanggal", 'lsdcommerce' ); ?></th>
                </tr>
                <?php 
                    $query = new WP_Query( array( 
                        'post_type' => 'lsdc-order',
                        'post_status' => 'publish',
                        'post_author' => $current_user->ID
                    ));
                ?>
                <?php if ( $query->have_posts() ) : ?>
                    <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                      <tr>
                        <td>INV#<?php echo abs( get_post_meta( get_the_ID(), 'order_id', true )); ?></td>
                        <td><?php echo lsdc_order_status_translate( get_the_ID() ); ?></td>
                        <td><?php echo lsdc_currency_format( true, get_post_meta( get_the_ID(), 'total', true )); ?></td>
                        <td><?php echo get_the_date( 'j M Y'); ?></td>
                      </tr>
                    <?php endwhile; wp_reset_postdata(); ?>
                <?php else: ?>
                    <!-- Alert to Input Program -->
                <?php endif; ?>
              </table>

              <!-- Next Update -->
              <!-- <table>
                <tr>
                  <th>Produk</th>
                  <th>Quantity</th>
                  <th>Total</th>
                </tr>
                <tr>
                  <td><a class="text-primary">LSDDonation</a></td>
                  <td>2 x Rp 140.000</td>
                  <td>Rp 280.000</td>
                </tr>
                <tr>
                  <td><a class="text-primary">LSDDonation - CrowdFunding</a></td>
                  <td>2 x Rp 225.000</td>
                  <td>Rp 450.000</td>
                </tr>
                <tr>
                  <td></td>
                  <td>Sub Total</td>
                  <td class="text-bold">Rp 730.000</td>
                </tr>
                <tr>
                  <td></td>
                  <td>Shipping</td>
                  <td>Rp 15.000</td>
                </tr>
                <tr>
                  <td></td>
                  <td>Total</td>
                  <td class="text-bold">Rp 745.000</td>
                </tr>
              </table> -->
          </div>

          <!-- Shipping -->
          <!-- GetOrder with Metavalue Status == shipped, complete, and Post Author same,  -->
          <!-- Get Type, Get Products, Download URL by Products -->
          <?php 
            $shiping_query = new WP_Query( array( 
                'post_type'   => 'lsdc-order',
                // 'post_status' => 'publish',
                // 'post_author' => $current_user->ID,
                'meta_query'  => array(
                  'relation'  => 'OR', /* <-- here */
                  array(
                      'key'     => 'status',
                      'value'   => 'shipped',
                      'compare' => '='
                  ),
                  array(
                      'key'     => 'status',
                      'value'   => 'completed',
                      'compare' => '='
                  )
                )
            ));
          ?>
          <div id="tab-body-3" class="tab-body">
            <table>
              <tr>
                <th>Tipe</th>
                <th>Order</th>
                <th>Produk</th>
                <th>Tindakan</th>
              </tr>
              <?php if ( $shiping_query->have_posts() ) : ?>
                    <?php while ( $shiping_query->have_posts() ) : $shiping_query->the_post(); ?>
                    <?php $type = json_decode( get_post_meta( get_the_ID(), 'shipping', true ) ); ?>
                    <?php if( isset($type->digital) ) : ?>
                    <!-- Digital Product As Each Item -->
                      <?php 
                      $products = json_decode( get_post_meta( get_the_ID(), 'products', true ));
                      ?>
                      <?php foreach ( $products as $key => $product ) : ?>
                        <tr>
                          <td>Digital</td>
                          <td>INV#<?php echo abs( get_post_meta( get_the_ID(), 'order_id', true )); ?></td>
                          <td><?php echo get_the_title( $product->id ); ?></td>
                          <td><a class="text-primary" href="<?php echo lsdc_product_download_link( $product->id ); ?>">Download v<?php echo lsdc_product_download_version( $product->id ); ?></a></td>
                        </tr>
                      <?php endforeach; ?>
        
                    <?php else: ?>
                      <tr>
                        <td>Fisik</td>
                        <td>INV#<?php echo abs( get_post_meta( get_the_ID(), 'order_id', true )); ?></td>
                        <td><?php echo get_the_date( 'j M Y' ); ?></td>
                        <td><a class="text-primary" target="_blank" href="https://cekresi.com/?noresi=<?php echo esc_attr( get_post_meta( get_the_ID(), 'resi', true ) ); ?>">Cek Resi</a></td>
                      </tr>
                    <?php endif; ?>
                <?php endwhile; wp_reset_postdata(); ?>
              <?php else: ?>
                    <!-- Alert to Input Program -->
              <?php endif; ?>
            </table>

            <!-- Shipping -->
            <!-- <table>
              <tr>
                <th>Order</th>
                <th>Produk</th>
                <th>Versi</th>
                <th>Tindakan</th>
              </tr>
              <tr>
                <td>#30</td>
                <td>LSDDonation</td>
                <td>1.0.5</td>
                <td><a href="" class="text-primary">Download</a></td>
              </tr>
              <tr>
                <td>#30</td>
                <td>LSDDonation - CrowdFunding</td>
                <td>1.0.3</td>
                <td><a href="" class="text-primary">Download</a></td>
              </tr>
            </table> -->
          </div>

          <!-- Profile -->
          <div id="tab-body-4" class="tab-body">
              <div class="container">
                <div class="columns">
        
                    <div class="column col-5">
                        <p class="lsdp-alert lsdc-info lsdp-hidden" id="alert-password"><?php _e( 'Your Input Wrong Old Password', 'lsdcommerce' ); ?></p>

                        <h6 class="card-title lsdp-mb-10"><?php _e( 'Change Password', 'lsdcommerce' ); ?></h6>
           
                        <form class="form-horizontal" action="">
                            <div class="lsdp-form-group">
                                <div class="col-5 col-sm-12 lsdp-mb-5">
                                    <label class="form-label" for="oldpassword"><?php _e( 'Old Password', 'lsdcommerce' ); ?></label>
                                </div>
                                <div class="col-12 col-sm-12">
                                    <input class="form-input fullwidth" id="oldpassword" type="password" placeholder="Old Password" autocomplete="on">
                                </div>
                            </div>
                            <div class="lsdp-form-group">
                                <div class="col-5 col-sm-12 lsdp-mb-5 mt-15">
                                    <label class="form-label" for="newpassword"><?php _e( 'New Password', 'lsdcommerce' ); ?></label>
                                </div>
                                <div class="col-12 col-sm-12">
                                    <input class="form-input fullwidth" id="newpassword" type="password" placeholder="New Password" autocomplete="on">
                                </div>
                            </div>
                            <div class="lsdp-form-group">
                                <div class="col-5 col-sm-12 lsdp-mb-5 mt-15">
                                    <label class="form-label" for="repeatpassword"><?php _e( 'Repeat New Password', 'lsdcommerce' ); ?></label>
                                </div>
                                <div class="col-12 col-sm-12">
                                    <input class="form-input fullwidth" id="repeatpassword" type="password" placeholder="Repeat Password" autocomplete="on">
                                </div>
                            </div>

                            <button class="lsdp-btn lsdc-btn btn-primary btn-block change-password"><?php _e( 'Update Password', 'lsdcommerce' ); ?></button>
                        </form>
                    </div>
                </div>
                
              </div>
          </div>
          
        </div>
      </div>
      
    </main>
  </div>
<?php else: ?>
  <div class="container max480">
    <div class="lsdp-alert lsdc-info lsdp-mt-10 lsdp-mb-10 lsdp-mx-10">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <p><?php _e( 'Silahkan Login untuk Mengakses Member Area', 'lsdcommerce' ); ?></p>
    </div>
  </div>
<?php endif; ?>

<?php get_footer(); ?>