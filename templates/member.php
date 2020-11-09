<?php
set_lsdcommerce();
if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly
    
}

get_header(); ?>

<?php if (is_user_logged_in()): ?>
<div id="lsdcommerce-member">

  <main class="page-content lsdc-bg-color max480">
  <input type="hidden" id="member-nonce" value="<?php echo wp_create_nonce('member-nonce'); ?>" />

  <div class="tabs-component">
    <input type="radio" name="tab" id="dashboard" checked="checked"/>
    <label class="tab" data-linking="dashboard" for="dashboard"><?php _e('Dashboard', 'lsdcommerce'); ?></label>

    <input type="radio" name="tab" id="order"/>
    <label class="tab" data-linking="order" for="order"><?php _e('Pembelian', 'lsdcommerce'); ?></label>

    <input type="radio" name="tab" id="shipping"/>
    <label class="tab" data-linking="shipping" for="shipping"><?php _e('Pengiriman', 'lsdcommerce'); ?></label>

    <input type="radio" name="tab" id="profile"/>
    <label class="tab" data-linking="profile" for="profile"><?php _e('Profil', 'lsdcommerce'); ?></label>

    <div class="tab-body-component">

      <!-- Dashboard -->
      <div id="tab-body-1" class="tab-body">
        <?php $current_user = wp_get_current_user(); ?>
        <?php _e('Selamat Datang', 'lsdcommerce'); ?>, <span class="text-primary"><?php echo lsdc_get_user_name($current_user->ID); ?></span><br><br>

        <p class="lsdp-mb-10">untuk melihat arsip pembelian anda bisa klik tab pembelian</p>
        <a class="lsdp-btn lsdc-btn btn-primary" href="<?php echo wp_logout_url(get_permalink()); ?>"><?php _e('Keluar', 'lsdcommerce'); ?></a>
      </div>
      
      <!-- Purchase -->
      <div id="tab-body-2" class="tab-body">
        <!-- Listing Pesanan -->
        <table>
            <tr>
              <th><?php _e("Pesanan", 'lsdcommerce'); ?></th>
              <th><?php _e("Tanggal", 'lsdcommerce'); ?></th>
              <th><?php _e("Total", 'lsdcommerce'); ?></th>
              <th><?php _e("Status", 'lsdcommerce'); ?></th>
            </tr>
            <?php
    $query = new WP_Query(array(
        'post_type' => 'lsdc-order',
        'post_status' => 'publish',
        'post_author' => $current_user->ID,
        'meta_query' => array(
            array(
                'key' => 'customer_id',
                'value' => $current_user->ID,
                'compare' => '='
            )
        )
    ));
?>
            <?php if ($query->have_posts()): ?>
                <?php while ($query->have_posts()):
            $query->the_post(); ?>
                  <tr>
                    <td><a view-ajax data-post="order" id="<?php the_ID(); ?>">INV#<?php echo abs(get_post_meta(get_the_ID() , 'order_id', true)); ?></a></td>
                    <td><?php echo get_the_date('j M Y'); ?></td>
                    <td><?php echo lsdc_currency_format(true, get_post_meta(get_the_ID() , 'total', true)); ?></td>
                    <td><?php echo lsdc_order_status_translate(get_the_ID()); ?></td>
                  </tr>
                <?php
        endwhile;
        wp_reset_postdata(); ?>
            <?php
    else: ?>
                <!-- Alert to Input Program -->
            <?php
    endif; ?>
          </table>

         <div class="lsdc-ajax-response" data-post="order">
         </div>

      </div>

      <!-- Shipping -->
      <!-- GetOrder with Metavalue Status == shipped, complete, and Post Author same,  -->
      <!-- Get Type, Get Products, Download URL by Products -->
      <?php
    $shiping_query = new WP_Query(array(
        'post_type' => 'lsdc-order',
        'post_status' => 'publish',
        'post_author' => $current_user->ID,
        'meta_query' => array(
            'relation' => 'AND', /* <-- here */
            array(
                'relation' => 'OR', /* <-- here */
                array(
                    'key' => 'status',
                    'value' => 'shipped',
                    'compare' => '='
                ) ,
                array(
                    'key' => 'status',
                    'value' => 'completed',
                    'compare' => '='
                )
            ) ,
            array(
                'key' => 'customer_id',
                'value' => $current_user->ID,
                'compare' => '='
            )
        )
    ));
?>
      <div id="tab-body-3" class="tab-body">
    
          <?php if ($shiping_query->have_posts()): ?>
          <table>
            <tr>
              <th><?php _e('Tipe', 'lsdcommerce'); ?></th>
              <th><?php _e('Order', 'lsdcommerce'); ?></th>
              <th><?php _e('Produk', 'lsdcommerce'); ?></th>
              <th><?php _e('Tindakan', 'lsdcommerce'); ?></th>
            </tr>
                <?php while ($shiping_query->have_posts()):
            $shiping_query->the_post(); ?>
                <?php $type = lsdc_product_check_type(get_the_ID());
            if (isset($type[0]) && isset($type[1]))
            {
                $tipe = __("Fisik dan Digital", 'lsdcommerce');
            }
            else if (isset($type[0]))
            {
                if ($type[0] == 'digital')
                {
                    $tipe = __("Digital", 'lsdcommerce');
                }
                else
                {
                    $tipe = __("Fisik", 'lsdcommerce');
                }
            }

?>
                <?php if (isset($type->digital)): ?>
                <!-- Digital Product As Each Item -->
                  <?php
                $products = json_decode(get_post_meta(get_the_ID() , 'products', true));
?>
                  <?php foreach ($products as $key => $product): ?>
                    <tr>
                      <td><?php echo $tipe; ?></td>
                      <td>INV#<?php echo abs(get_post_meta(get_the_ID() , 'order_id', true)); ?></td>
                      <td><?php echo lsdc_product_title_summary(get_the_ID()); ?></td>
                      <td><a view-ajax data-post="shipping" id="<?php the_ID(); ?>"><?php _e('Detail', 'lsdcommerce'); ?></a></td>
                    </tr>
                  <?php
                endforeach; ?>
    
                <?php
            else: ?>
                  <tr>
                    <td><?php echo $tipe; ?></td>
                    <td>INV#<?php echo abs(get_post_meta(get_the_ID() , 'order_id', true)); ?></td>
                    <td><?php echo lsdc_product_title_summary(get_the_ID()); ?></td>
                    <td><a view-ajax data-post="shipping" id="<?php the_ID(); ?>"><?php _e('Detail', 'lsdcommerce'); ?></a></td>
                  </tr>
                <?php
            endif; ?>
            <?php
        endwhile;
        wp_reset_postdata(); ?>
            </table>

            <div class="lsdc-ajax-response" data-post="shipping">
            </div>
          <?php
    else: ?>
               <!-- Alert to Input Program -->
              <p class="lsdp-alert lsdc-info lsdp-mt-0"><?php _e('Belum ada pengiriman', 'lsdcommerce'); ?></p>
          <?php
    endif; ?>
      </div>

      <!-- Profile -->
      <div id="tab-body-4" class="tab-body">
          <div class="container">
            <div class="columns">
    
                <div class="column col-5">
                    <p class="lsdp-alert lsdc-info lsdp-hidden lsdp-mt-0 lsdp-mb-5" id="alert-password"><?php _e('Password lama kamu salah', 'lsdcommerce'); ?></p>

                    <h6 class="card-title lsdp-mb-10"><?php _e('Ganti Kata Sandi', 'lsdcommerce'); ?></h6>
        
                    <form class="form-horizontal" action="">
                        <div class="lsdp-form-group">
                            <div class="col-5 col-sm-12 lsdp-mb-5">
                                <label class="form-label" for="oldpassword"><?php _e('Kata sandi lama', 'lsdcommerce'); ?></label>
                            </div>
                            <div class="col-12 col-sm-12">
                                <input class="form-input fullwidth" id="oldpassword" type="password" placeholder="Old Password" autocomplete="on">
                            </div>
                        </div>
                        <div class="lsdp-form-group">
                            <div class="col-5 col-sm-12 lsdp-mb-5 mt-15">
                                <label class="form-label" for="newpassword"><?php _e('Kata sandi baru', 'lsdcommerce'); ?></label>
                            </div>
                            <div class="col-12 col-sm-12">
                                <input class="form-input fullwidth" id="newpassword" type="password" placeholder="New Password" autocomplete="on">
                            </div>
                        </div>
                        <div class="lsdp-form-group">
                            <div class="col-5 col-sm-12 lsdp-mb-5 mt-15">
                                <label class="form-label" for="repeatpassword"><?php _e('Ulangi kata sandi', 'lsdcommerce'); ?></label>
                            </div>
                            <div class="col-12 col-sm-12">
                                <input class="form-input fullwidth" id="repeatpassword" type="password" placeholder="Repeat Password" autocomplete="on">
                            </div>
                        </div>

                        <button class="lsdp-btn lsdc-btn btn-primary btn-block lsdc-change-password"><?php _e('Perbaharui Kata Sandi', 'lsdcommerce'); ?></button>
                    </form>
                </div>
            </div>
            
          </div>
      </div>
      
    </div>
  </div>
      
  </main>
</div>
<?php
else: ?>
  <div class="container max480">
    <div class="lsdp-alert lsdc-info lsdp-mt-10 lsdp-mb-10 lsdp-mx-10">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <p><?php _e('Silahkan Login untuk Mengakses Member Area', 'lsdcommerce'); ?></p>
    </div>
  </div>
<?php
endif; ?>

<?php get_footer(); ?>