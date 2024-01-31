<?php
    global $product;
    $hasGallery = (!empty($product->get_gallery_image_ids())) ? true : false;

    $col_1 = '';
    $col_2 = 'col-md-12 col-sm-12';
    if(has_post_thumbnail()) {
        $col_1 = 'col-md-4 col-sm-4 first';
        $col_2 = 'col-md-8 col-sm-8 second';
    }

    $id = get_the_ID();

    $s_title = get_post_meta($id, 'cars_info', true);
    $car_info = stm_get_car_rent_info($id);
    $excerpt = get_the_excerpt($id);

    $current_car = stm_get_cart_items();

    $current_car_id = 0;

    if(!empty($current_car['car_class'])) {
        if(!empty($current_car['car_class']['id'])) {
            $current_car_id = $current_car['car_class']['id'];
        }
    }


    $is_promo = false;
    $discount = 0;

    if(class_exists('DiscountByDays')) {
        $discount = DiscountByDays::get_days_post_meta( $id );
    }

    $dates = (isset($_COOKIE['stm_calc_pickup_date_' . get_current_blog_id()])) ? stm_check_order_available($id, $_COOKIE['stm_calc_pickup_date_' . get_current_blog_id()], $_COOKIE['stm_calc_return_date_' . get_current_blog_id()]) : array();

    if ( !empty( $discount ) ) {
        $currentDiscount = 0;
        foreach ( $discount as $k => $val ) {
            if(intval($val['days']) <= count($dates)) {
                $currentDiscount = $val['percent'];
            }
        }
        // $is_promo = !empty($currentDiscount) ? true : $is_promo;
    }

    // if(class_exists('PricePerBookingLength')) {
    //     $pricePerBookingLength = PricePerBookingLength::getDescribeTotalByDays(0, $id);
    //     if(isset($pricePerBookingLength['promo_price']) && count($pricePerBookingLength['promo_price'])) {
    //         $is_promo = true;
    //     }
    // }

    if(class_exists('PriceForDatePeriod')) {
		$pareint_id = stm_get_wpml_product_parent_id(get_the_ID());
		
        if(PriceForDatePeriod::hasDatePeriod()) {
            $promo = PriceForDatePeriod::getDescribeTotalByDays(1,$pareint_id);
            if(isset($promo['promo_price']) && is_array($promo['promo_price']) && count($promo['promo_price'])) {
                $is_promo = true;
            }
        }
    }

    $current_car = '';
    if($id == $current_car_id) {
        $current_car = 'current_car';
    }

    $dates = (isset($_COOKIE['stm_calc_pickup_date_' . get_current_blog_id()])) ? stm_check_order_available($id, $_COOKIE['stm_calc_pickup_date_' . get_current_blog_id()], $_COOKIE['stm_calc_return_date_' . get_current_blog_id()]) : array();
    $disableCar = (count($dates) > 0) ? 'stm-disable-car' : '';

    $disableCarText = esc_html__('This Class is already booked in: ', 'motors');

    $is_disable = false;

    if(class_exists('MinimumBookingDays')) {
        $is_disable = MinimumBookingDays::isDisableCar($id);
        $minimum_period = MinimumBookingDays::getMinimumPeriod($id);
        $disableCar = $is_disable ? 'stm-disable-car' : $disableCar;

        $terms = get_the_terms( $id, 'product_cat' );
        foreach ($terms as $term) {
            $product_cat_id = $term->term_id;
            $term_period = (int)get_term_meta($term->term_id, 'minimum_period', true);
            if(!empty($term_period)) {
                $orderCookieData = stm_get_rental_order_fields_values();
        		$countDays = intval($orderCookieData['order_days']);
                if($term_period > $countDays) {
                    $is_disable = true;
                    $minimum_period = $term_period;
                }
            }
            break;
        }

        if($is_disable) {
            // $disableCarText = esc_html__('Minimum booking period: ', 'motors-child') . '<span class="yellow">'.$minimum_period . esc_html__( ' days', 'motors-child').'</span>';
            $disableCarText = esc_html__('Unavailable - Call for More Information', 'motors-child');
        }

    }

    if(class_exists('UnavailableByDate')) {
        $is_unavailable = UnavailableByDate::isDisableCar($id);
        $is_disable = ($is_unavailable) ? true : $is_disable;
        if($is_disable) {
            $disableCar = 'stm-disable-car';
            $disableCarText = esc_html__('Unavailable - Call for More Information', 'motors-child');
        }
    }

    if($is_promo) {
        $current_car .= ' is_promo';
    }

    if($is_disable) {
        $current_car = '';
        $is_promo = false;
    }
?>

<div class="stm_single_class_car <?php echo esc_attr($current_car); ?> <?php echo esc_attr($disableCar)?>" id="product-<?php echo esc_attr($id); ?>">
    <?php if($is_promo): ?>
        <div class="stm-promotion-title">
    		<div class="heading-font"><?php _e('Promotion', 'motors-child'); ?></div>
    		<span class="external"></span>
    	</div>
    <?php endif; ?>

    <div class="row">
        <div class="<?php echo sanitize_text_field($col_1) ?>">
            <?php if(has_post_thumbnail()): ?>
                <div class="image">
                    <?php the_post_thumbnail('stm-img-350-181', array('class' => 'img-responsive')); ?>
                    <?php if($hasGallery):?>
                        <div class="stm-rental-photos-unit stm-car-photos-<?php echo esc_attr($id); ?>">
                            <i class="stm-boats-icon-camera"></i>
                            <span class="heading-font"><?php echo count($product->get_gallery_image_ids()); ?></span>
                        </div>
                        <script>
                            jQuery(document).ready(function(){

                                jQuery(".stm-car-photos-<?php echo esc_attr($id); ?>").on('click', function() {
                                    jQuery(this).lightGallery({
                                        dynamic: true,
                                        dynamicEl: [
                                            <?php foreach($product->get_gallery_image_ids() as $car_photo):
                                            $image = wp_get_attachment_image_src( $car_photo, 'full', false, array( 'title' => get_post_field( 'post_title', $car_photo ) ) );
                                            ?>
                                            {
                                                src  : "<?php echo esc_url($image[0]); ?>"
                                            },
                                            <?php endforeach; ?>
                                        ],
                                        download: false,
                                        mode: 'lg-fade',
                                    })


                                });
                            });

                        </script>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="<?php echo sanitize_text_field($col_2) ?>">
            <div class="row">

                <div class="col-md-6 col-sm-6">
                    <div class="top">
                        <div class="heading-font">
                            <h3><?php the_title(); ?></h3>
                            <?php if(!empty($s_title)): ?>
                                <div class="s_title"><?php echo sanitize_text_field($s_title); ?></div>
                            <?php endif; ?>
                            <?php if(!empty($car_info)): ?>
                                <div class="infos">
                                    <?php foreach($car_info as $slug => $info):
                                        $name = $info['value'];
                                        if($info['numeric']) {
                                            $name = $info['value'] . ' ' . esc_html__($info['name'], 'motors');
                                        }
                                        $font = $info['font'];
                                        ?>
                                        <div class="single_info stm_single_info_font_<?php echo esc_attr($font) ?>">
                                            <i class="<?php echo esc_attr($font); ?>"></i>
                                            <span><?php echo sanitize_text_field($name); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($excerpt)): ?>
                            <div class="stm-more">
                                <a href="#">
                                    <span><?php esc_html_e('More information', 'motors'); ?></span>
                                    <i class="fa fa-angle-down"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                    <?php get_template_part('partials/rental/main-shop/price'); ?>
                </div>
                <?php if(!empty($excerpt)): ?>
                    <div class="col-md-12 col-sm-12">
                        <div class="more">
                            <div class="lists-inline">
                                <?php echo apply_filters('the_content', $excerpt); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
	<?php if(!empty($disableCar)): ?>
		<div class="stm-enable-car-date">
			<?php
				$formatedDates = array();
				foreach ($dates as $val){
					$formatedDates[] = stm_get_formated_date($val, 'd M');
				}
			?>
			<h3><?php echo $disableCarText . "<span class='yellow'>" . implode('<span>,</span> ', $formatedDates);?></span>.</h3>
		</div>
	<?php endif; ?>
</div>
