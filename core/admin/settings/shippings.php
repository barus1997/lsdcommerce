<?php
/**
 * Display Notifications Available for Manage
 * Storing : lsdc_shipping_settings
 *
 * @since    1.0.0
 */
?>

<div id="shippings" class="verticaltab">
    <?php 
        global $lsdcommerce_shippings; 

        if( isset($lsdcommerce_shippings) ){
            foreach( $lsdcommerce_shippings as $key => $shipping ){ 
                if( class_exists( $shipping ) ) : 
                    $instance = new $shipping;
    ?>
                    <section class="tabitem">
                        <!-- Menu -->
                        <?php echo $instance->tab(); ?>
                        <!-- Content -->
                        <article> 
                            <?php echo $instance->header(); ?>
                            <?php echo $instance->manage(); ?>
                        </article>
                    </section>
        <?php
            endif;
            }
        }
        ?>
</div>
