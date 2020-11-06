<?php
/**
 * Template : Single
 * Displaying Detail Product
 * 
 * @since 1.0.0
 */

set_lsdcommerce();
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once LSDC_PATH . 'core/modules/product/product-single.php'; // Function for Product
?>

<?php get_header(); ?>

<div id="lsdcommerce-container" class="max480">
    <main class="page-content lsdcommerce">

        <?php do_action( 'lsdcommerce_single_before' ); ?>
        <!-- Product Detail -->
        <div id="product-detail" 
            data-id="<?php the_ID(); ?>"
            data-title="<?php the_title(); ?>"
            data-price="<?php echo lsdc_product_price(); ?>" 
            data-weight="<?php echo lsdc_product_weight(); ?>" 
            data-thumbnail="<?php the_post_thumbnail_url( get_the_ID(), 'lsdcommerce-thumbnail-mini' ); ?>" 
            data-limit="<?php echo empty( get_post_meta( get_the_ID(), '_limit_order', true ) ) ? 9999 : get_post_meta( get_the_ID(), '_limit_order', true ); ?>"  
            class="card">

            <div class="card-body lsdcommerce-bg-color">
                <section class="product-detail">
                    <figure id="featured-image">
                        <?php the_post_thumbnail( 'full' ); ?>
                    </figure>
                </section>

                <!-- Product Meta -->
                <section class="product-item--detail py-3">
    
                    <div class="lsdp-row p-default align-items-end">
                        <div class="col-8 py-10">
                            <!-- Product Name -->
                            <h2 class="product-item-name">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <!-- Product Price -->
                            <h6 class="product-item-price">
                                <?php do_action( 'lsdcommerce_single_price'); ?>
                            </h6>
                            
                            <!-- Product Category -->
                            <?php echo get_the_term_list( get_the_ID(), 'lsdc-product-category', ' <div class="product-item-category">', ', ', '</div>' ); ?>
                        </div>
                        
                        <div class="col-auto ml-auto">
                            <!-- Product Stock -->
                            <div class="product-item-stock text-right">
                                <?php echo lsdc_product_stock(); ?>
                            </div>
                        </div>
                    </div>
          
                </section>

                <?php do_action( 'lsdcommerce_single_tab_before' ); ?>

                <!-- Product Description -->
                <section class="product-description">
                    <?php do_action('lsdcommerce_single_tabs'); ?>
                </section>

                <?php do_action( 'lsdcommerce_single_tab_after' ); ?>
            </div>

        </div>

        <!-- Cart Manager -->
        <?php do_action( 'lsdcommerce_single_after' ); ?>

    </main> <!-- main -->
</div>

<?php get_footer();  ?>