<?php
/**
 * Display Notifications Available for Manage
 * Storing : lsdc_notification_settings
 *
 * @since    1.0.0
 */
?>

<div id="notifications" class="verticaltab">
    <?php 
    global $lsdcommerce_notification;
    
    foreach( $lsdcommerce_notification as $key => $notification ){ 
        if( class_exists( $notification ) ) : 
            $instance = new $notification;
    ?>
            <section class="tabitem">
                <!-- Menu -->
                <?php echo $instance->settings_tab(); ?>

                <article> 
                    <!-- Status -->
                    <?php echo $instance->settings_header(); ?>

                    <!-- Manage -->
                    <?php echo $instance->settings_manage(); ?>
                </article>

            </section>
    <?php
        endif;
    }
    ?>
</div>