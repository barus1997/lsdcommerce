<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header(); 
?>

<main class="page-content">
        <div class="card">
        <div class="card-header card-header-white">
                <h6 class="card-title">
                    <?php echo get_queried_object()->name; ?>
                    <small><?php echo lsdc_count_taxonomy_post( 'lsdc-product-category', get_queried_object()->term_id );  echo ' ' . _n( 'Product', 'Products', lsdc_count_taxonomy_post( 'lsdc-product-category', get_queried_object()->term_id ), 'lsdc' );?></small>
                </h6>
                <!-- <div class="lsdp-row">
                    <div class="col-auto">
                        <a href="javascript:void(0);">
                            <img src="./assets/img/icons/search-outline.svg" alt="" class="icon-20">
                        </a>
                    </div>
                    <div class="col-auto ml-auto">
                        <a href="javascript:void(0);" class="cart-manager" data-action="show-cart" data-target="#cart-popup">
                            <span class="counter">5</span>
                            <img src="./assets/img/icons/cart-outline.svg" alt="" class="icon-20">
                        </a>
                    </div>
                </div> -->
            </div>
            <div class="card-body">
                <!-- <section class="filter">
                    <div class="container">
                        <div class="row">
                            <div class="col-12 position-relative">
                                <a href="javascript:void(0);" class="btn btn-block btn-link" lsdc-toggle="collapse" data-target="#filter-sort" aria-expanded="false">
                                    Urutkan <ion-icon name="chevron-down-outline"></ion-icon>
                                </a>
                                <div id="filter-sort" class="collapse position-absolute w-100">
                                    <a href="#" class="collapse-item">
                                        Termurah
                                    </a>
                                    <a href="#" class="collapse-item">
                                        Termahal
                                    </a>
                                    <a href="#" class="collapse-item">
                                        Terbaru
                                    </a>
                                    <a href="#" class="collapse-item">
                                        Terpopuler
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section> -->

                <section class="product py-3">
                    <div class="container">
                        <div class="row">
                    
                            <?php 
                                $query = new WP_Query( array( 
                                    'post_type' => 'lsdc-product',
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => 'lsdc-product-category',
                                            'field' => 'term_id',
                                            'terms' => get_queried_object()->term_id,
                                        )
                                    )
                                ));
                            ?>
                            <?php if ( $query->have_posts() ) : ?>
                                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                                <div class="col-12">
                                    <figure class="product-item product-item--list">
                                        <div class="product-item-img">
                                            <img src="https://play.lsdplugins.com/wp-content/uploads/2020/07/tropical_forest_dr-150x150.jpg" alt="">
                                        </div>
                                        <figcaption>
                                            <div class="row">
                                                <div class="col-12">
                                                    <h3 class="product-item-name">
                                                        <a href="<?php the_permalink(); ?>">
                                                            <?php the_title(); ?>
                                                        </a>
                                                    </h3>
                                                    <h6 class="product-item-price">
                                                        <?php do_action('lsdcommerce_price_hook'); ?>
                                                    </h6>
                                                </div>
                                            </div>
                                            <div class="row align-items-end">
                                                <div class="col-6">
                                                    <?php echo get_the_term_list( get_the_ID(), 'lsdc-product-category', ' <div class="product-item-category">', ', ', '</div>' ); ?>
                                                </div>
                                                <div class="col-auto ml-auto">
                                                    <div class="product-item-stock text-right">
                                                        <p>
                                                            <?php _e( 'Stock', 'lsdcommerce'); ?><br>
                                                            <?php if( get_post_meta( get_the_ID(), '_stock', true ) == 9999 ) : ?>
                                                                <?php _e( 'Available', 'lsdcommerce' ); ?>
                                                            <?php else: ?>
                                                                <?php echo get_post_meta( get_the_ID(), '_stock', true ); ?> <?php echo empty(get_post_meta( get_the_ID(), '_stock_unit', true )) ? 'pcs' : get_post_meta( get_the_ID(), '_stock_unit', true ); ?>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </figcaption>
                                    </figure>
                                </div>
                                <?php endwhile; wp_reset_postdata(); ?>
                            <?php else: ?>
                                <!-- Alert to Input Program -->
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

        </div>


<!-- 
        <div id="filter-modal" class="modal">
            <div class="modal-body">
                <div class="modal-title">
                    <div class="row align-items-center">
                        <div class="col-10">
                            <p>Filter</p>
                        </div>
                        <div class="col-auto ml-auto">
                            <a href="javascript:void(0);" class="modal-close">
                                <ion-icon name="close-outline"></ion-icon>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-content">
                    <p>
                        Kategori
                    </p>
                    <form>
                        <div class="form-group">
                            <div class="checkbox-square">
                                <input type="checkbox" name="filter" id="filter-1">
                                <label for="filter-1">T-Shirt</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-square">
                                <input type="checkbox" name="filter" id="filter-2">
                                <label for="filter-2">Jacket</label>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <div class="checkbox-square">
                                <input type="checkbox" name="filter" id="filter-3">
                                <label for="filter-3">Sweater</label>
                            </div>
                        </div>
                        <div class="form-group widget-price">
                            <p>Rentang Harga</p>
                            <div id="slider-range"></div>
                            <div class="row justify-content-between ">
                                <div class="col-auto">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text" class="form-control" id="amount-min">
                                    </div>
                                </div>
                                <div class="col-auto ml-auto">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text" class="form-control" id="amount-max">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">
                            Filter
                        </button>
                    </form>
                </div>
            </div>
        </div> -->
    </main> <!-- main -->
<?php get_footer(); ?>