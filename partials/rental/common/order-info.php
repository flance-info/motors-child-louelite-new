<?php
$cart_items = stm_get_cart_items();
$car_rent = $cart_items['car_class'];
$id = $car_rent['id'];

$priceDate = PriceForDatePeriod::getDescribeTotalByDays($car_rent['price'], $id);
$pricePerHour = get_post_meta( $id, 'rental_price_per_hour_info', true );
$discount = DiscountByDays::get_days_post_meta( $id );

if(class_exists('PricePerBookingLength')) {
    $pricePerBookingLength = PricePerBookingLength::getDescribeTotalByDays($car_rent['price'], $id);
    if(isset($pricePerBookingLength['promo_price']) && count($pricePerBookingLength['promo_price'])) {
        $priceDate = $pricePerBookingLength;
    }
}
?>

    <div class="title">
        <h4><?php echo sanitize_text_field($car_rent['name']); ?></h4>
        <div class="subtitle heading-font"><?php echo sanitize_text_field($car_rent['subname']); ?></div>
    </div>
    <?php if(has_post_thumbnail($id)):
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'stm-img-350-181');
        if(!empty($image[0])): ?>
            <div class="image">
                <img src="<?php echo esc_url($image[0]); ?>" />
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!--Car rent-->
    <div class="stm_rent_table">
        <div class="heading heading-font"><h4><?php esc_html_e('Rate', 'motors'); ?></h4></div>
        <table>
            <thead class="heading-font">
                <tr>
                    <td><?php esc_html_e('QTY', 'motors'); ?></td>
                    <td><?php esc_html_e('Rate', 'motors'); ?></td>
                    <td><?php esc_html_e('Subtotal', 'motors'); ?></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" class="divider"></td>
                </tr>
                <?php
                $total = $car_rent['total'];

                if(!empty($priceDate)) :
                ?>
                    <?php
                    if(count($priceDate['simple_price']) > 0):
                        $total = array_sum($priceDate['simple_price']);
                    ?>
                    <tr>
                        <td><?php echo sprintf(esc_html__('%s Days', 'motors'), count($priceDate['simple_price'])); ?></td>
                        <td>
                            <?php echo wc_price($priceDate['simple_price'][0] ); ?>
                        </td>
                        <td><?php echo wc_price(array_sum($priceDate['simple_price'])); ?></td>
                    </tr>
                    <?php
                    endif;

                    if(count($priceDate['promo_price']) > 0) :
						$total = (count($priceDate['simple_price']) > 0) ? $total + array_sum($priceDate['promo_price']) : array_sum($priceDate['promo_price']);

                        $promoPrices = array_count_values($priceDate['promo_price']);

                        foreach ($promoPrices as $k => $val) :
                    ?>
                        <tr>
                            <td><?php echo sprintf(esc_html__('%s Days', 'motors'), $val); ?></td>
                            <td>
								<?php echo wc_price( $k ); ?>
                            </td>
                            <td><?php echo wc_price($k * $val); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <tr>
                        <td><?php echo sprintf(esc_html__('%s Days', 'motors'), $car_rent['days']); ?></td>
                        <td>
							<?php echo wc_price($car_rent['price']); ?>
							<?php stm_getInfoWindowPriceManip($id); ?>
                        </td>
                        <td><?php echo wc_price($car_rent['total']); ?></td>
                    </tr>
                <?php endif; ?>
				<?php
				if(!empty($pricePerHour) && !empty($car_rent['hours'])):
					$total = ($total != ($car_rent['hours'] * $pricePerHour)) ? $total + ($car_rent['hours'] * $pricePerHour) : $car_rent['hours'] * $pricePerHour;
					?>
                    <tr>
                        <td><?php echo sprintf(esc_html__('%s Hours', 'motors'), $car_rent['hours']); ?></td>
                        <td>
							<?php echo wc_price( $pricePerHour ); ?>
                        </td>
                        <td><?php echo wc_price($car_rent['hours'] * $pricePerHour); ?></td>
                    </tr>
				<?php endif; ?>
                <?php
				if ( !empty( $discount ) ) :
					$currentDiscount = 0;
				    $days = 0;
					foreach ( $discount as $k => $val ) {
						if($val['days'] <= $car_rent['days']) {
						    $days = $val['days'];
							$currentDiscount = $val['percent'];
						}
					}

					$forDiscount = $total;
					$total = $total - (($total / 100) * $currentDiscount);

					?>
                    <tr>
                        <td colspan="2" class="stm-discount"><?php echo sprintf(__('Discount: <span class="show-discount-popup">%s Days and more %s sale</span>', 'motors'), $days, $currentDiscount . '%');?></td>
                        <td style="padding-right: 28px;"><?php echo '- ' . wc_price( ($forDiscount / 100) * $currentDiscount ); ?></td>
                    </tr>
				<?php endif; ?>
                <tr>
                    <td colspan="3" class="divider"></td>
                </tr>
            </tbody>
            <tfoot class="heading-font">
                <tr>
                    <td colspan="2"><?php esc_html_e('Rental Charges Rate', 'motors'); ?></td>
                    <td><?php echo wc_price($total); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!--Add-ons-->
    <?php if(!empty($cart_items['options'])): ?>
        <div class="stm_rent_table">
            <div class="heading heading-font"><h4><?php esc_html_e('Add-ons', 'motors'); ?></h4></div>
            <table>
                <thead class="heading-font">
                    <tr>
                        <td><?php esc_html_e('QTY', 'motors'); ?></td>
                        <td><?php esc_html_e('Rate', 'motors'); ?></td>
                        <td><?php esc_html_e('Subtotal', 'motors'); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3" class="divider"></td>
                    </tr>
                    <?php foreach($cart_items['options'] as $car_option): ?>
                        <tr>
                            <td>
                                <?php
                                    $opt_days = (!empty($car_option['opt_days'])) ? $car_option['opt_days'] : 1;
                                    $quant = (!empty(get_post_meta($car_option['id'], '_car_option', true))) ? $car_option['quantity'] : $car_option['quantity']/$opt_days;
                                    echo sprintf(esc_html__('%s x %1s %s %s day(s)', 'motors'), $quant, $car_option['name'], esc_html__("for", 'motors'), $car_option['opt_days']);
                                ?>
                            </td>
                            <td><?php echo wc_price($car_option['price']); ?></td>
                            <td><?php echo wc_price($car_option['total']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="divider"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="heading-font">
                    <tr>
                        <td colspan="2"><?php esc_html_e('Add-ons Charges Rate', 'motors'); ?></td>
                        <td><?php echo wc_price($cart_items['option_total']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

    <?php get_template_part('partials/rental/common/tax'); ?>

    <?php get_template_part('partials/rental/common/coupon'); ?>

    <div class="stm-rent-total heading-font">
        <table>
            <tr>
                <td><?php esc_html_e('Estimated total', 'motors'); ?></td>
                <td><?php echo wc_price($cart_items['total']); ?></td>
            </tr>
        </table>
    </div>
<?php
if(!empty($discount)):
	$desc = get_theme_mod('discount_program_desc', '');
?>
<div id="stm-discount-by-days-popup" class="stm-promo-popup-wrap">
    <div class="stm-promo-popup">
        <h5><?php echo __('Discount program', 'motors'); ?></h5>
        <?php if(!empty($desc)) : ?>
            <div class="stm-disc-prog-desc">
                <?php echo _($desc); ?>
            </div>
        <?php endif; ?>
        <div class="stm-table stm-pp-head">
            <div class="stm-pp-row stm-pp-qty heading-font"><?php _e('QTY', 'motors');?></div>
            <div class="stm-pp-row stm-pp-subtotal heading-font"><?php _e('DISCOUNT', 'motors');?></div>
        </div>
        <?php foreach ( $discount as $k => $val ) : ?>
        <div class="stm-table stm-pp-discount">
            <div class="stm-pp-row"><?php echo sprintf(__('%s Days and more', 'motors'), $val['days']);?></div>
            <div class="stm-pp-row"><?php echo esc_html('- ' . $val['percent'] . '%'); ?></div>
        </div>
        <?php endforeach; ?>
        <div class="stm-rental-ico-close" data-close-id="stm-discount-by-days-popup"></div>
    </div>
</div>
<?php endif; ?>
