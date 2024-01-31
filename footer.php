</div> <!--main-->
</div> <!--wrapper-->
<?php do_action( 'stm_pre_footer' ); ?>
<?php if ( !is_404() and !is_page_template( 'coming-soon.php' ) ) { ?>
    <footer id="footer">
        <?php get_template_part( 'partials/footer/footer' ); ?>
        <?php get_template_part( 'partials/footer/copyright' ); ?>
        <?php get_template_part( 'partials/global-alerts' ); ?>
        <!-- Searchform -->
        <?php get_template_part( 'partials/modals/searchform' ); ?>
    </footer>
<?php } elseif ( is_page_template( 'coming-soon.php' ) ) {
    get_template_part( 'partials/footer/footer-coming-soon' );
}; ?>

<?php
if ( get_theme_mod( 'frontend_customizer' ) ) {
    get_template_part( 'partials/frontend_customizer' );
}
?>

<?php wp_footer(); ?>

<?php
if ( !stm_is_auto_parts() && !stm_is_rental() ) :
    if ( is_singular( stm_listings_post_type() ) ) {
        if ( get_theme_mod( 'show_calculator', true ) ) get_template_part( 'partials/modals/car-calculator' );
        if ( get_theme_mod( 'show_offer_price', false ) ) get_template_part( 'partials/modals/trade-offer' );
        if ( get_theme_mod( 'show_trade_in', false ) ) get_template_part( 'partials/modals/trade-in' );
    }

    if ( stm_is_motorcycle() ) {
        if ( get_theme_mod( 'show_calculator', true ) ) get_template_part( 'partials/modals/car-calculator' );
        if ( get_theme_mod( 'show_offer_price', true ) ) get_template_part( 'partials/modals/trade-offer' );
    }

    if ( get_theme_mod( 'show_test_drive', true ) ) get_template_part( 'partials/modals/test-drive' );
    get_template_part( 'partials/modals/get-car-price' );

    $show_compare = ( is_single( get_the_ID() ) ) ? get_theme_mod( 'show_listing_compare', true ) : get_theme_mod( 'show_compare', true );

    if ( $show_compare ) get_template_part( 'partials/single-car/single-car-compare-modal' );

    if ( stm_pricing_enabled() ) {
        get_template_part( 'partials/modals/limit_exceeded' );
        get_template_part( 'partials/modals/subscription_ended' );
    }
    ?>
    <?php if ( is_listing( array( 'listing_two', 'listing_three' ) ) ) : ?>
    <div class="notification-wrapper">
        <div class="notification-wrap">
            <div class="message-container">
                <span class="message"></span>
            </div>
            <div class="btn-container">
                <button class="notification-close">
                    <?php echo esc_html__( 'Close', 'motors' ); ?>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>
    <div class="modal_content"></div>
<?php
endif;

if ( stm_is_rental() ) {
    get_template_part( 'partials/modals/rental-notification-choose-another-class' );
    echo '<div class="stm-rental-overlay"></div>';
}
?>

</body>
</html>

<?php
// global $woocommerce;
// $cartfee = $woocommerce->cart->get_fees();
// echo '<pre>';
// print_r($cartfee);
// echo '</pre>';
// print_r(get_post(1075));
?>