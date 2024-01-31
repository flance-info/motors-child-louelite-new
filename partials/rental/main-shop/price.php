<?php
$id = stm_get_wpml_product_parent_id(get_the_ID());

$product = wc_get_product($id);
$is_variation = get_post_meta($id, 'in_rental_car_de', true);

$title = get_theme_mod('stm_add_lauto_title');
$tel = get_theme_mod('stm_add_lauto_phone');
$email = get_theme_mod('stm_add_lauto_email');


$product_type = 'default';

if(!empty($product)):
    if( $product->is_type( 'variable' ) ):
        $variations = $product->get_available_variations();
        $prices = array();

		$fields = stm_get_rental_order_fields_values();

        if(!empty($variations)) {
            $max_price = 0;
            $i = 0;
            foreach($variations as $variation) {

                if((!empty($variation['display_price']) || !empty($variation['display_regular_price'])) and !empty($variation['variation_description'])) {

                    $gets = array(
                        'add-to-cart' => $id,
                        'product_id' => $id,
                        'variation_id' => $variation['variation_id'],
                    );

                    foreach($variation['attributes'] as $key => $val) {
                        $gets[$key] = $val;
                    }

                    $url = add_query_arg($gets, get_permalink($id));

                    $total_price = false;
                    if(!empty($fields['order_days'])) {
                        $total_price = (!empty($variation['display_price'])) ? $variation['display_price'] : $variation['display_regular_price'];//$variation['display_price'] * $fields['order_days'];
                    }

                    if(!empty($total_price)) {
                        if($max_price < $total_price) {
                            $max_price = $total_price;
                        }
                    }

                    $prices[] = array(
                        'price' => stm_get_default_variable_price($id, $i),
                        'text' => $variation['variation_description'],
                        'total' => $total_price,
                        'url' => $url,
                        'var_id' => $variation['variation_id']
                    );
                }

                $i++;
            }
        }

        if(!empty($prices && $is_variation)): ?>
            <div class="stm_rent_prices">
                <?php foreach($prices as $price): ?>
                    <div class="stm_rent_price">
                        <div class="total heading-font">
                            <?php
                                if(!empty($price['total'])) {
                                    echo sprintf( __('%s/Total', 'motors'), wc_price($price['total']) );
                                }
                            ?>
                        </div>
                        <div class="period">
                            <?php
                                $popupId = $price['var_id'] . '-' . $id;

                                if(!empty($price['price'])) {
                                    if(PricePerHour::hasPerHour() || PriceForDatePeriod::hasDatePeriod() || DiscountByDays::hasDiscount($id)) {
                                        echo '<div class="stm-show-rent-promo-info" data-popup-id="stm-promo-popup-wrap-' . $popupId . '">';
										$orderHours = ( empty( $fields['order_hours'] ) ) ? 0 : $fields['order_hours'];
                                        echo sprintf( __( '%s Days / %s Hours', 'motors' ), $fields['order_days'], $orderHours );
										echo '</div>';
										stm_get_popup_promo_price( $popupId, $id, $price['price'], $fields);
									} else {
										echo sprintf( __( '%s/Day', 'motors' ), wc_price( $price['price'] ) );
                                    }
                                }
                            ?>
                        </div>
                        <div class="pay">
                            <a class="heading-font" href="<?php echo esc_url($price['url']); ?>"><?php echo wp_strip_all_tags($price['text']); ?></a>
                        </div>
                        <?php if(!empty($max_price) and $price['total'] < $max_price ) : ?>
                            <div class="stm_discount"><?php echo sprintf( __('Saves you %s', 'motors'), wc_price($max_price - $price['total']) ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else:

            $title = get_theme_mod('stm_add_lauto_title');
            $tel = get_theme_mod('stm_add_lauto_phone');
            $email = get_theme_mod('stm_add_lauto_email');

            ?>

            <?php

            if(!empty($title)){
                ?>

                <div class="heading-font t-no-price" style="text-align: center;"><h5><?php echo $title;?></h5></div>


                <?php
            }

            ?>

            <div class="stm_rent_prices">

                <?php

                if(!empty($tel)){
                    ?>

                    <div class="stm_rent_price">
                        <div class="pay">
                            <a class="heading-font" href="tel:<?php echo $tel;?>"><i class="fa fa-phone"></i> Call</a>
                        </div>
                    </div>

                    <?php
                }

                if(!empty($email)){
                    ?>

                    <div class="stm_rent_price">
                        <div class="pay">
                            <a class="heading-font" href="mailto:<?php echo $email;?>"><i class="fa fa-at"></i> E-mail</a>
                        </div>
                    </div>

                    <?php
                }

                ?>

            </div>

        <?php
        endif; ?>
    <?php else:
        $prod = $product->get_data();
        $price = (empty($prod['sale_price'])) ? $prod['price'] : $prod['sale_price'];

        $fields = stm_get_rental_order_fields_values();

        $gets = array(
            'add-to-cart' => $id,
            'product_id' => $id
        );

        $total_price = false;
        if(!empty($fields['order_days'])) {
            $total_price = $product->get_price();
        }

        $url = add_query_arg($gets, get_permalink($id));
        if(!empty($price) and $url && $is_variation): ?>
            <div class="stm_rent_prices">
                <div class="stm_rent_price">
                    <div class="total heading-font">
                        <?php
                        if(!empty($total_price)) {
                            echo sprintf( __('%s/Total', 'motors'), wc_price($total_price) );
                        }
                        ?>
                    </div>
                    <div class="period">
                        <?php

						$popupId = $price . '-' . $id;

						if(!empty($price)) {
							if(PricePerHour::hasPerHour() || PriceForDatePeriod::hasDatePeriod() || DiscountByDays::hasDiscount($id)) {
								echo '<div class="stm-show-rent-promo-info" data-popup-id="stm-promo-popup-wrap-' . $popupId . '">';
								$orderHours = ( empty( $fields['order_hours'] ) ) ? 0 : $fields['order_hours'];
								echo sprintf( __( '%s Days / %s Hours', 'motors' ), $fields['order_days'], $orderHours );
								echo '</div>';

								stm_get_popup_promo_price( $popupId, $id, $price, $fields);
							} else {
								echo sprintf( __( '%s/Day', 'motors' ), wc_price( $price ) );
							}
						}
                        ?>
                    </div>
                    <div class="pay">
                        <a class="heading-font" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Pay now', 'motors'); ?></a>
                    </div>
                </div>
            </div>
        <?php else:  ?>

            <?php

            if(!empty($title)){
                ?>

                <div class="heading-font t-no-price" style="text-align: center;"><h5><?php echo $title;?></h5></div>


                <?php
            }

            ?>

            <div class="stm_rent_prices">

                <?php

                if(!empty($tel)){
                    ?>

                    <div class="stm_rent_price">
                        <div class="pay">
                            <a class="heading-font" href="tel:<?php echo $tel;?>"><i class="fa fa-phone"></i> Call</a>
                        </div>
                    </div>

                    <?php
                }

                if(!empty($email)){
                    ?>

                    <div class="stm_rent_price">
                        <div class="pay">
                            <a class="heading-font" href="mailto:<?php echo $email;?>"><i class="fa fa-at"></i> E-mail</a>
                        </div>
                    </div>

                    <?php
                }

                ?>

            </div>

            <?php
        endif; ?>
    <?php endif; ?>
<?php endif; ?>
