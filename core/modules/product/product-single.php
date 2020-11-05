<?php 
/**
 * Cart Manager in Single Product
 */
function lsdc_cart_manager()
{
?>
    <!-- Quantity Button -->
    <div class="cart-qty-float fixed" product-id="<?php the_ID(); ?>">
        <div class="lsdc-qty" id="single-qty">
            <button type="button" class="minus button-qty" data-qty-action="minus">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>
            <input min="0" type="number" value="0" name="qty" disabled>
            <button type="button" class="plus button-qty" data-qty-action="plus">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>
        </div>
    </div>

    <!-- Cart Management Template : Passed 1.0.0 -->
    <script id="item-template" type="x-template">
        <div class="cart-basket">
            {{#items}}
            <div class="item" id="{{id}}">
                <div class="lsdp-row no-gutters">
                    <div class="col-auto item-name">
                        <div class="img">
                            <img src="{{thumbnail}}" alt="{{title}}"></div>
                        <h6>
                            <span class="name">{{title}}</span>
                            <span class="price">{{price}}</span>
                        </h6>
                    </div>
                    <div class="col-auto item-qty qty ml-auto">
                        <div class="lsdc-qty" >
                            <button type="button" class="minus button-qty" data-qty-action="minus">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                            <input min="0" type="number" value="{{qty}}" name="qty" disabled>
                            <button type="button" class="plus button-qty" data-qty-action="plus">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{/items}}
        </div>
    </script>

    <div id="cart-popup" class="cart-popup">
        <div class="overlay"></div>
        <div class="cart-container">
            <div class="cart-body hidden">
                <div class="lsdp-row no-gutters mb-3">
                    <div class="col-auto text-left">
                        <p><strong><?php _e('Item', 'lsdcommerce'); ?></strong></p>
                    </div>
                    <div class="col-4 text-right ml-auto">
                        <p><strong><?php _e('Quantity', 'lsdcommerce'); ?></strong></p>
                    </div>
                </div>
                <div class="cart-items p-0" id="cart-items">
                </div>
            </div>
            <div class="cart-footer">
                <div class="container">
                    <div class="lsdp-row no-gutters">
                        <div class="col-auto">
                            <div class="lsdp-row no-gutters align-items-center">
                                <div class="col-auto pr-0">
                                    <a href="javascript:void(0);" class="cart-manager">
                                        <span class="counter">0</span>
                                        <img src="<?php echo LSDC_URL; ?>assets/images/svg/cart.svg" alt="" class="icon-20">
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <div class="cart-footer-info">
                                        <h6><?php _e("Cart", 'lsdcommerce'); ?></h6>
                                        <h4><?php _e("Empty", 'lsdcommerce'); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto ml-auto inline-flex">
                            <button class="lsdp-btn lsdc-btn btn-primary px-5 lsdc-addto-cart"><?php _e('Add', 'lsdcommerce'); ?></button>
                            <a class="lsdp-btn lsdc-btn btn-primary btn-dark px-4" href="<?php echo get_the_permalink(lsdc_admin_get('general_settings', 'checkout_page')); ?>"><?php _e("Checkout", 'lsdcommerce'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
add_action('lsdcommerce_single_after', 'lsdc_cart_manager');
?>