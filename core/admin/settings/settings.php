<?php 
/**
 * General Settings
 * 
 * This page will be setup general settings of plugins
 * Setup Checkout Page, Terms and Conditions, and load any settings from extension
 * 
 * Hook : lsdc_admin_settings
 */
?>
<section id="settings" class="form-horizontal">
    <form>
        <?php 
            $settings = get_option('lsdc_general_settings', true ); 

            $checkout_page      = empty( $settings['checkout_page'] ) ? '' : abs( $settings['checkout_page'] );
            $terms_conditions   = empty( $settings['terms_conditions'] ) ? '' : abs( $settings['terms_conditions'] );
            $member_area        = empty( $settings['member_area'] ) ? '' : abs( $settings['member_area'] );
            $page_query         = new WP_Query( array( 'posts_per_page' => -1,'post_type' => 'page', 'post_status' => 'publish' ) );
        ?>

        <div class="form-group">
            <div class="col-3 col-sm-12">
                <label class="form-label" for="checkout_page">
                    <?php _e( 'Checkout Page', 'lsdcommerce' ); ?>
                </label>
            </div>
            <div class="col-9 col-sm-12 col-ml-auto">
                <select class="form-select" name="checkout_page">
                    <option value=""><?php _e( 'Please Choose your Checkout Page', 'lsdcommerce' ); ?></option>
                    <?php if ( $page_query->have_posts() ) : ?>
                        <?php while ( $page_query->have_posts() ) : $page_query->the_post(); ?>
                            <option value="<?php the_ID(); ?>" <?php echo $checkout_page == get_the_ID() ? 'selected' : '' ; ?>><?php the_title(); ?></option>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php endif; ?>
                </select>
                <small class="d-block"><?php _e( 'This page will be used as the checkout page', 'lsdcommerce' ); ?></small>
            </div>
        </div>

        <div class="form-group">
            <div class="col-3 col-sm-12">
                <label class="form-label" for="member_area">
                    <?php _e( 'Member Area', 'lsdcommerce' ); ?>
                </label>
            </div>
            <div class="col-9 col-sm-12 col-ml-auto">
                <select class="form-select" name="member_area">
                    <option value=""><?php _e( 'Please Choose your Member Area Page'); ?></option>
                    <?php if ( $page_query->have_posts() ) : ?>
                        <?php while ( $page_query->have_posts() ) : $page_query->the_post(); ?>
                            <option value="<?php the_ID(); ?>" <?php echo $member_area == get_the_ID() ? 'selected' : '' ; ?>><?php the_title(); ?></option>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="col-3 col-sm-12">
                <label class="form-label" for="terms_conditions">
                    <?php _e( 'Terms and Conditions Page', 'lsdcommerce' ); ?>
                </label>
            </div>
            <div class="col-9 col-sm-12 col-ml-auto">
                <select class="form-select" name="terms_conditions">
                    <option value=""><?php _e( 'Please Choose your Terms and Conditions Page'); ?></option>
                    <?php if ( $page_query->have_posts() ) : ?>
                        <?php while ( $page_query->have_posts() ) : $page_query->the_post(); ?>
                            <option value="<?php the_ID(); ?>" <?php echo $terms_conditions == get_the_ID() ? 'selected' : '' ; ?>><?php the_title(); ?></option>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php endif; ?>
                </select>
                <small class="d-block"><?php _e( 'This page will refer to the terms and conditions on the checkout page', 'lsdcommerce' ); ?></small>
            </div>
        </div>

        <!-- <div class="form-group">
            <div class="col-3 col-sm-12">
                <label class="form-label" for="wacheckout"><?php //_e( 'WhatsApp Checkout', 'lsdcommerce' ); ?></label>
            </div>
            <div class="col-9 col-sm-12 col-ml-auto">
                <label class="form-switch">
                   <input type="checkbox" name="wacheckout" <?php //echo ( $wacheckout == 'on' ) ? 'checked' : ''; ?>><i class="form-icon"></i> <?php //_e( 'Redirect to WhatsApp After Click Pay', 'lsdcommerce' ); ?> 
                </label>
            </div>
        </div>  -->
       
        <?php 
            // Hook for Load Extension Settings
            do_action( 'lsdc_admin_settings' ); 
        ?>
        
        <br>
        <button class="btn btn-primary" id="lsdc_admin_settings_save" style="width:120px"><?php _e( 'Save', 'lsdcommerce' ); ?></button> 
    </form>
</section>