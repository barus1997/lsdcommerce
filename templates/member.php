<?php 
set_lsdcommerce();
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

get_header(); ?>

<?php if( is_user_logged_in() ) : ?>
<div id="lsdcommerce-member">

  <main class="page-content lsdc-bg-color max480">
  <input type="hidden" id="member-nonce" value="<?php echo wp_create_nonce( 'member-nonce' ); ?>" />

  <div class="tabs-component">
    <input type="radio" name="tab" id="tab1" checked="checked"/>
    <label class="tab" data-linking="tab1" for="tab1"><?php _e( 'Dashboard', 'lsdcommerce' ); ?></label>

    <input type="radio" name="tab" id="tab2"/>
    <label class="tab" data-linking="tab2" for="tab2"><?php _e( 'Pembelian', 'lsdcommerce' ); ?></label>

    <input type="radio" name="tab" id="tab3"/>
    <label class="tab" data-linking="tab3" for="tab3"><?php _e( 'Pengiriman', 'lsdcommerce' ); ?></label>

    <input type="radio" name="tab" id="tab4"/>
    <label class="tab" data-linking="tab4" for="tab4"><?php _e( 'Profil', 'lsdcommerce' ); ?></label>

    <div class="tab-body-component">

      <!-- Dashboard -->
      <div id="tab-body-1" class="tab-body">
        <?php $current_user = wp_get_current_user(); ?>
        <?php _e( 'Selamat Datang', 'lsdcommerce' ); ?>, <span class="text-primary"><?php echo lsdc_get_user_name( $current_user->ID ); ?></span><br><br>

        <p>Untuk melihat detail pembelian anda bisa mengakses menu pembelian, untuk melihat pengiriman yang sedang berlansung anda bisa cek pengiriman</p>
        <a class="lsdp-btn lsdc-btn btn-primary" href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php _e( 'Logout', 'lsdcommerce' ); ?></a>
      </div>
      
      <!-- Purchase -->
      <div id="tab-body-2" class="tab-body">
        <!-- Listing Pesanan -->
        <table>
            <tr>
              <th><?php _e( "Pesanan", 'lsdcommerce' ); ?></th>
              <th><?php _e( "Tanggal", 'lsdcommerce' ); ?></th>
              <th><?php _e( "Total", 'lsdcommerce' ); ?></th>
              <th><?php _e( "Status", 'lsdcommerce' ); ?></th>
            </tr>
            <?php
                $query = new WP_Query( array( 
                  'post_type'   => 'lsdc-order',
                  'post_status' => 'publish',
                  'post_author' => $current_user->ID,
                  'meta_query'  => array(
                    array(
                        'key'     => 'customer_id',
                        'value'   => $current_user->ID,
                        'compare' => '='
                    )
                  )
                ));
            ?>
            <?php if ( $query->have_posts() ) : ?>
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                  <tr>
                    <td><a href="?invoice=<?php the_ID(); ?>">INV#<?php echo abs( get_post_meta( get_the_ID(), 'order_id', true )); ?></a></td>
                    <td><?php echo get_the_date( 'j M Y'); ?></td>
                    <td><?php echo lsdc_currency_format( true, get_post_meta( get_the_ID(), 'total', true )); ?></td>
                    <td><?php echo lsdc_order_status_translate( get_the_ID() ); ?></td>
                  </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php else: ?>
                <!-- Alert to Input Program -->
            <?php endif; ?>
          </table>

          <!-- Next Update -->
          <?php if( isset( $_GET['invoice'] ) ) :  $order_id = abs($_GET['invoice']); ?>
            <?php if( get_current_user_id() == get_post_field ('post_author', $order_id) ) : ?>
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
                    <td><a class="text-primary"><?php echo esc_attr( $product->title ); ?></a></td>
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
                              <small class="d-block  text-primary"> 
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
            <?php endif; ?>
          <?php endif; ?>
      </div>

      <!-- Shipping -->
      <!-- GetOrder with Metavalue Status == shipped, complete, and Post Author same,  -->
      <!-- Get Type, Get Products, Download URL by Products -->
      <?php 
        $shiping_query = new WP_Query( array( 
            'post_type'   => 'lsdc-order',
            'post_status' => 'publish',
            'post_author' => $current_user->ID,
            'meta_query'  => array(
              'relation'  => 'AND', /* <-- here */
              array(
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
              ),
              array(
                'key'     => 'customer_id',
                'value'   => $current_user->ID,
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
                      <td><a href="?shipping=<?php the_ID(); ?>">INV#<?php echo abs( get_post_meta( get_the_ID(), 'order_id', true )); ?></a></td>
                      <td><?php echo lsdc_product_title_summary(  get_the_ID() ); ?></td>
                      <td><a class="text-primary" href="<?php echo lsdc_product_download_link( $product->id ); ?>">Download</a></td>
                    </tr>
                  <?php endforeach; ?>
    
                <?php else: ?>
                  <tr>
                    <td>Fisik</td>
                    <td><a href="?shipping=<?php the_ID(); ?>">INV#<?php echo abs( get_post_meta( get_the_ID(), 'order_id', true )); ?></a></td>
                    <td><?php echo lsdc_product_title_summary(  get_the_ID() ); ?></td>
                    <td><a class="text-primary" target="_blank" href="https://cekresi.com/?noresi=<?php echo esc_attr( get_post_meta( get_the_ID(), 'resi', true ) ); ?>">Cek Resi</a></td>
                  </tr>
                <?php endif; ?>
            <?php endwhile; wp_reset_postdata(); ?>
          <?php else: ?>
                <!-- Alert to Input Program -->
          <?php endif; ?>
        </table>

        <!-- Shipping -->          <!-- Next Update -->
          <?php if( isset( $_GET['shipping'] ) ) :  $order_id = abs($_GET['shipping']); ?>
            <?php if( get_current_user_id() == get_post_field ('post_author', $order_id) ) : ?>
            <table>
              <tr>
                <th>Order</th>
                <th>Produk</th>
                <th>Versi</th>
                <th>Tindakan</th>
              </tr>
              <?php 
                $order_id = 65;
                $products = (array)json_decode(get_post_meta( $order_id, 'products', true));
                $total = get_post_meta( $order_id, 'total', true);
                $subtotal = 0;
                ?>
                <?php foreach ( $products as $key => $product) : ?>
                  <tr>
                    <td>#<?php echo lsdc_product_extract_ID( $product->id ); ?></td>
                    <td><?php echo get_the_title( $product->id ); ?></td>
                    <td><?php echo lsdc_product_download_version( lsdc_product_extract_ID( $product->id ) ); ?></td>
                    <td><a href="<?php echo lsdc_product_download_link( lsdc_product_extract_ID( $product->id ) ); ?>" class="text-primary">Download</a></td>
                  </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
          <?php endif; ?>
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