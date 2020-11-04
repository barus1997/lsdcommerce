<?php
namespace LSDCommerce\Pluggable;

class LSDC_Pluggable
{
    /**
     * Function to Register Form based on Args
     */
    public static function form_register($args)
    {
        global $lsdcommerce_form;
        $lsdcommerce_form[$args['id']] = $args;
    }
    /**
     * Function to Release Form based on Args
     */
    public static function form_release($args)
    {
        global $lsdcommerce_form;
        if (in_array($args['id'], (array)$lsdcommerce_form)) unset($lsdcommerce_form[$args['id']]);
    }

}

class LSDC_Admin_Settings
{
    /**
     * Function to load Switch Option
     */
    public static function switch_option($id, $text, $visible = true)
    {
        add_filter('lsdcommerce_appearance_switch_option', function ($before) use ($id, $text, $visible)
        {
            $after = array(
                'lsdc_' . $id => array(
                    $text,
                    $visible
                )
            );
            return array_merge($before, $after);
        });
    }

	 /**
     * Load Appearance Switch Option
     * @since	1.0.0
     */
    public static function apperaance_switch()
      {
        $switch_option = array(
            // 'lsdc_contoh' => array( __( 'Contoh', 'lsdc' ) ),
        );

        if (has_filter('lsdcommerce_appearance_switch_option'))
          {
            $switch_option = apply_filters('lsdcommerce_appearance_switch_option', $switch_option);
		  }
        return array_reverse($switch_option);
      }
    /**
     * Function to load Tabs
     */
    public static function tabs()
    {
        $tablist = array(
            'store' => array(
                __('Toko', 'lsdcommerce') ,
                'store.php'
            ) ,
            'appearance' => array(
                __('Tampilan', 'lsdcommerce') ,
                'appearance.php'
            ) ,
            'notifications' => array(
                __('Notifikasi', 'lsdcommerce') ,
                'notifications.php'
            ) ,
            'shippings' => array(
                __('Pengiriman', 'lsdcommerce') ,
                'shippings.php'
            ) ,
            'payments' => array(
                __('Pembayaran', 'lsdcommerce') ,
                'payments.php'
            ) ,
            'settings' => array(
                __('Pengaturan', 'lsdcommerce') ,
                'settings.php'
            )
        );

        $tablist = array_reverse($tablist);
        if (has_filter('lsdc_admin_settings_tab'))
        {
            $tablist = apply_filters('lsdc_admin_settings_tab', $tablist);
        }
        return array_reverse($tablist);
	}
	
	/**
     * Function to Add New Tab in LSDCommerce Admin Settings
     */
    public static function add_tab($args)
    {
        add_filter('lsdc_admin_settings_tab', function ($source) use ($args)
        {
            $source = array_merge($args, $source);
            return $source;
        });
    }

    /**
     * Function to Remove Tab in LSDCommerce Admin Settings
     */
    public static function remove_tab($id)
    {
        add_filter('lsdc_admin_settings_tab', function ($source) use ($id)
        {
            unset($source[$id]);
            return $source;
        });
    }
}
?>
