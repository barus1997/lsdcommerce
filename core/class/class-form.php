<?php 
namespace LSDCommerce\Form;

/**
 * Handling Form in Checkout Page
 * - Rendering Form
 * - Data Form via Filter ( Soon )
 * - Register Form ( Soon )
 * - Unregister Form ( Soon )
 * -- Extendable Form ( Soon )
 */
class LSDC_Form
{
	    
    public static function public_render()
    {
       global $lsdcommerce_form;
       foreach ( $lsdcommerce_form as $key => $item ) 
       {
           $id             = $item['id'];
           $type           = $item['type'];
           $placeholder    = $item['placeholder'];
           $required       = $item['req'];
           $value          = null;

           if( is_user_logged_in() ) {
               $user_id     = get_current_user_id();
               $user_info   = get_userdata( $user_id );
               switch ( $id ) 
               {
                   case 'name':
                       $value = lsdc_get_user_name( $user_id );
                       break;
                   case 'phone':
                       $value = lsdc_format_phone( get_user_meta( $user_id, 'user_phone' , true ) );
                       break;
                   case 'email':
                       $value = sanitize_email( $user_info->user_email );
                       break;
               }

           }

           // Attach to Action in FrontEnd
           add_action( 'lsdcommerce_checkout_form', function() use ( $id, $type, $placeholder, $required, $value )
           {
               echo '<div class="lsdp-form-group floating-label">
                       <input type="'. $type .'" class="form-control swiper-no-swiping" name="' . $id . '" placeholder="'. $placeholder .'" value="'. $value .'" req="'. $required .'">
                       <label>'. $placeholder .'</label>
                   </div>';
           });
       }
    }
    
}
?>