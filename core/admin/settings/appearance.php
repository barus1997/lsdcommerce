<?php
/**
* Class and Function List:
* Function list:
* Classes list:
*/
require_once LSDC_PATH . 'core/functions/pluggable.php';
use LSDCommerce\Pluggable\LSDC_Admin_Settings;

$settings = get_option('lsdc_appearance_settings');
?>

<div class="entry columns col-gapless">
  <div class="column col-8">

    <section id="appearance" class="form-horizontal" style="padding: .4rem 10px;">
      <form>

        <div class="form-group">
          <div class="col-3 col-sm-12">
            <label class="form-label" for="font_family"><?php _e('Font', 'lsdcommerce'); ?></label>
          </div>
          <div class="col-4 col-sm-12" style="padding-bottom:10px;">
            <select class="form-select" id="font_family" name="font_family">
              <option>Poppins</option>
            </select>
            <div id="selectedfont" class="hidden"><?php esc_attr_e((empty($settings['font_family'])) ? 'Poppins' : $settings['font_family']); ?></div>
          </div>
        </div>
       
        <div class="form-group">
          <div class="col-3 col-sm-12">
            <label class="form-label" for="bg-color"><?php _e('Warna Latar', 'lsdcommerce'); ?></label>
          </div>
          <div class="col-9 col-sm-12" style="line-height:0;">
            <input type="text" name="background_theme_color" value="<?php esc_attr_e($settings['background_theme_color']); ?>" class="lsdc-color-picker"> 
            <div class="color-picker" style="display: inline-block;z-index:999;"></div>
          </div>
        </div>

        <div class="form-group">
          <div class="col-3 col-sm-12">
            <label class="form-label" for="theme-color"><?php _e('Warna Tema', 'lsdcommerce'); ?></label>
          </div>
          <div class="col-9 col-sm-12" style="line-height:0;">
            <input type="text" name="theme_color" value="<?php esc_attr_e($settings['theme_color']); ?>" class="lsdc-color-picker"> 
            <div class="color-picker" style="display: inline-block;z-index:999;"></div>
          </div>
        </div>

        <ul class="general-menu">
        <?php
        foreach (LSDC_Admin_Settings::apperaance_switch() as $key => $menu):
            if (isset($settings[$key])): // if Option Exist
        ?>
              <li>
                <label class="form-switch">
                  <input name="<?php esc_attr_e($key); ?>" id="<?php esc_attr_e($key); ?>" type="checkbox" <?php echo ($settings[$key] == 'on') ? 'checked="checked"' : ''; ?>>
                  <i class="form-icon"></i><?php esc_attr_e($menu[0]); ?>
                </label>
              </li>
            <?php
            else: ?>
              <li>
                <small style="float:right;"><?php esc_attr_e($menu[1]); ?></small>
                <label class="form-switch">
                  <input name="<?php esc_attr_e($key); ?>" id="<?php esc_attr_e($key); ?>" type="checkbox">
                  <i class="form-icon"></i><?php esc_attr_e($menu[0]); ?>
                </label>
                
              </li>
            <?php
            endif;
        endforeach;
        ?>
        </ul>

        <br>
      </form>
      <button class="btn btn-primary" id="lsdc_admin_appearance_save" style="width:120px"><?php _e('Simpan', 'lsdcommerce'); ?></button> <!-- lsdconation-admin.js on Click Saving -->
    </section>

  </div>

  <div class="column col-4">
    <h6>Shortcode <a class="btn btn-primary btn-sm float-right" target="_blank" href="https://docs.lsdplugins.com/lsdcommerce/" ><?php _e( 'Pelajari Shortcode', 'lsdcommerce' ); ?></a></h6>
    <p style="margin:0;"><?php _e( 'Menampilkan Halaman Checkout', 'lsdcommerce' ); ?></p><code>[lsdcommerce_checkout]</code>
    <p style="margin:0;"><?php _e( 'Menampilkan Produk Terbaru', 'lsdcommerce' ); ?></p><code>[lsdcommerce_latest_products]</code>
  <?php if( has_action('lsdcommerce_shortcode_hook' )) : ?>

    <?php do_action('lsdcommerce_shortcode_hook'); ?>
  <?php endif; ?>
  </div>

</div>

<script>
  if( localStorage.getItem("lsdc_font_cache") == null || localStorage.getItem("lsdc_font_cache") == '' ){
    jQuery.getJSON("https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyCoDdOKhPem_sbA-bDgJ_-4cVhJyekWk-U", function(fonts){
      var lsdc_font_cache = {};
      for (var i = 0; i < fonts.items.length; i++) {   
        lsdc_font_cache[fonts.items[i].family] = fonts.items[i].files.regular;
      }   
      localStorage.setItem("lsdc_font_cache", JSON.stringify(lsdc_font_cache)); 
    });
  }else{
    var lsdc_font_cache = JSON.parse(localStorage.getItem("lsdc_font_cache"));
    var selectedfont = jQuery('#selectedfont').text();
    jQuery.each(lsdc_font_cache, function(index, value) {
      jQuery('#fontlist')
         .remove("option")
         .append( jQuery( ( index == selectedfont ) ? "<option selected></option>" : "<option></option>" )
         .attr("value", index)
         .attr("style", "font-family:"+ index + "; font-size: 16px")
         .text(index));
    });  
  }
</script>