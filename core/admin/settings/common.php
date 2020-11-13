<?php 
require_once LSDC_PATH . 'core/functions/pluggable.php';
use LSDCommerce\Pluggable\LSDC_Admin_Settings;
?>

<div class="wrap lsdcommerce shadow">
    <?php
        /**
         * Set Default to Store Tab
         */
        $active_tab = "store";
        if(isset($_GET["tab"])){
            if($_GET["tab"] == "store"){
                $active_tab = "store";
            }else{
                $active_tab = $_GET["tab"];
            }
        }else{
            $active_tab = "store";
        }
    ?>

    <?php 
        /**
         * Admin Menu Extendable
         * 
         * For add new menu, add item to filter 'lsdc_admin_settings_tab'
         * 
         * Example Add:
         * $args = array( 'lisensi' => array( __('Lisensi', 'lsdc'), 'lisensi.php' ) );
         * LSDC_Admin_Settings::add_tab( $args );
         * 
         * Example Remove:
         * LSDC_Admin_Settings::remove_tab( 'shippings' );
         * 
         * You can using this function inside plugins_loaded action
         * @since    1.0.0
         */
        $tablist = LSDC_Admin_Settings::tabs();
    ?>

    <div class="column col-12 col-sm-12 px-0"> 
        <ul class="tab tab-primary">
        <?php foreach ($tablist as $key => $item) : ?>
            <li class="tab-item <?php if( $active_tab == $key ){ echo 'active'; } ?>">
                <a href="?page=lsdcommerce&tab=<?php esc_attr_e( $key ); ?>"><?php echo $item[0]; ?></a>
            </li>
        <?php endforeach; ?>
            <li class="tab-item <?php if( $active_tab == 'addons' ){ echo 'active'; } ?>">
                <a href="?page=lsdcommerce&tab=addons"><?php _e('Ekstensi', 'lsdc'); ?></a>
            </li>
        </ul>
    </div>

    <article class="lsdc-tab-content"> 
    <?php 
        if( isset($_GET["tab"]) ) {
            foreach ($tablist as $key => $item) {
                if( $_GET["tab"] ==  $key || $active_tab == $key ){
                    require_once( $item[1] ); 
                }else if( $_GET["tab"] == 'addons' ){
                    require_once( 'addons.php' ); 
                }
            }
        }else{
            require_once( 'store.php' ); //Fallback
        }
    ?>
    </article>
</div>

