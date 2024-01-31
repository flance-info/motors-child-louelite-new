<?php

class PricePerHourExtend {
	const META_KEY_INFO   = 'rental_price_per_hour_info';
	private static $varId = 0;

	public function __construct() {
		do_action( 'stm_rental_meta_box' );
		add_action( 'save_post', array( get_class(), 'add_price_per_day_post_meta' ), 10, 2 );
		add_filter( 'woocommerce_product_type_query', array( get_class(), 'setVarId' ), 10, 2 );
		add_filter( 'woocommerce_product_get_price', array( get_class(), 'setVarPrice' ), 30, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( get_class(), 'setVarPrice' ), 30, 2 );
		add_filter( 'stm_rental_date_values', array( get_class(), 'updateDaysAndHour' ), 10, 1 );
		add_filter( 'stm_cart_items_content', array( get_class(), 'updateCart' ), 40, 1 );
	}

	public static function hasPerHour() {
		$pricePerHour = self::getPricePerHour( self::$varId );

		return ( ! empty( $pricePerHour ) ) ? true : false;
	}

	public static function add_price_per_day_post_meta( $post_id, $post ) {
		if ( isset( $_POST['price-per-hour'] ) && ! empty( $_POST['price-per-hour'] ) ) {
			update_post_meta( $post->ID, self::META_KEY_INFO, filter_var( $_POST['price-per-hour'], FILTER_SANITIZE_NUMBER_FLOAT ) );
		} else {
			delete_post_meta( $post->ID, self::META_KEY_INFO );
		}
	}

	public static function setVarId( $bool, $productId ) {
		if ( 'product' === get_post_type( $productId ) ) {
			$terms = get_the_terms( $productId, 'product_type' );

			if ( 'simple' === $terms && ( $terms[0]->slug || 'variable' === $terms[0]->slug ) ) {
				self::$varId = stm_get_wpml_product_parent_id( $productId );
			}
		}
	}

	public static function setVarPrice( $price, $product ) {

		if ( 'car_option' === $product->get_type() ) {
			return $price;
		}

		if ( ! empty( $product->get_data() ) ) {

			$pId = 'variation' === $product->get_type() ? $product->get_parent_id() : $product->get_id();

			self::$varId = stm_get_wpml_product_parent_id( $pId );

			$orderCookieData = stm_get_rental_order_fields_values();
			$pricePerHour    = self::getPricePerHour( self::$varId );

			if ( ! empty( $orderCookieData['order_hours'] ) && 0 !== $orderCookieData['order_hours'] && ! empty( $pricePerHour ) ) {
				$price = ( isset( $orderCookieData['ceil_days'] ) && ! empty( $orderCookieData['ceil_days'] ) ) ? $price : 0;
				$price = $price + ( $orderCookieData['order_hours'] * $pricePerHour );
			}
		}

		return $price;
	}

	public static function getPricePerHour( $varId ) {
		return get_post_meta( $varId, self::META_KEY_INFO, true );
	}

	public static function updateDaysAndHour( $data ) {
		$pickupDate = $data['calc_pickup_date'];
		$returnDate = $data['calc_return_date'];

		if ( '--' !== $pickupDate && '--' !== $returnDate ) {
			$date1 = stm_date_create_from_format( $pickupDate );
			$date2 = stm_date_create_from_format( $returnDate );

			if ( $date1 instanceof DateTime && $date2 instanceof DateTime ) {
				$diff = $date2->diff( $date1 )->format( '%a.%h' );
				$diff = explode( '.', $diff );

				$data['order_days'] = (int) $diff[0];

				$pricePerHour = self::getPricePerHour( self::$varId );

				if ( isset( $diff[1] ) && 0 !== (int) $diff[1] && empty( $pricePerHour ) ) {
					$data['order_days'] = (int) $diff[0] + 1;
					$data['ceil_days']  = (int) $diff[0] + 1;
				}

				if ( ! empty( self::getPricePerHour( self::$varId ) ) ) {
					$data['order_hours'] = (int) $diff[1];
				}
			}
		}

		return $data;
	}

	public static function updateCart( $cartItems ) {
		if ( isset( $cartItems['car_class']['total'] ) && isset( $cartItems['car_class']['id'] ) ) {
			$orderCookieData = stm_get_rental_order_fields_values();

			$pId          = $cartItems['car_class']['id'];
			$pricePerHour = self::getPricePerHour( $pId );

			if ( isset( $orderCookieData['order_hours'] ) && $orderCookieData['order_hours'] && ! empty( $pricePerHour ) ) {
				$cartItems['car_class']['total'] = ( ! empty( $cartItems['car_class']['days'] ) ) ? $cartItems['car_class']['total'] + ( $orderCookieData['order_hours'] * $pricePerHour ) : ( $orderCookieData['order_hours'] * $pricePerHour );
				$cartItems['car_class']['hours'] = $orderCookieData['order_hours'];

				if ( ! $orderCookieData['order_days'] ) {
					$cartItems['total'] = wc_price( $cartItems['car_class']['total'] );
				}
			}
		}

		return $cartItems;
	}

	public static function pricePerHourView() {
		$price = get_post_meta( stm_get_wpml_product_parent_id( get_the_ID() ), self::META_KEY_INFO, true );

		$disabled = ( (int) get_the_ID() !== (int) stm_get_wpml_product_parent_id( get_the_ID() ) ) ? 'disabled="disabled"' : '';

		?>
        <style type="text/css">
        .stm-rent-nav-tabs li:not(:last-of-type) a {
            border-left: 0 none;
            border-right: 2px solid #cccccc;
        }
        </style>
		<div class="admin-rent-info-wrap">
			<ul class="stm-rent-nav-tabs">
				<li>
					<a class="stm-nav-link active"
							data-id="price-per-hour"><?php echo esc_html__( 'Price Per Hour', 'motors' ); ?></a>
				</li>
				<li>
					<a class="stm-nav-link"
							data-id="discount-by-days">
						<?php echo esc_html( stm_me_get_wpcfto_mod( 'enable_fixed_price_for_days', false ) ? __( 'Fixed Price By Quantity Days', 'motors' ) : __( 'Discount By Days', 'motors' ) ); ?>
					</a>
				</li>
				<li>
					<a class="stm-nav-link"
							data-id="price-date-period"><?php echo esc_html__( 'Price For Date Period', 'motors' ); ?></a>
				</li>
                <!-- Start Customization -->
                <li>
					<a class="stm-nav-link"
							data-id="price-booking-length">
						<?php echo esc_html(  __( 'Price per Booking Length', 'motors-child' ) ); ?>
					</a>
				</li>
                <li>
					<a class="stm-nav-link"
							data-id="minimum-booking-days">
						<?php echo esc_html(  __( 'Minimum Booking Days', 'motors-child' ) ); ?>
					</a>
				</li>
                <li>
                    <a class="stm-nav-link"
                       data-id="unavailable-by-date">
                        <?php echo esc_html__( 'Unavailable By Date', 'motors-child' ); ?>
                    </a>
                </li>
                <!-- /End Customization -->
			</ul>
			<div class="stm-tabs-content">
				<div class="tab-pane show active" id="price-per-hour">
					<div class="price-per-hour-wrap">
						<div class="price-per-hour-input">
							<?php echo esc_html__( 'Price', 'motors' ); ?> <input type="text" name="price-per-hour"
									value="<?php echo esc_attr( $price ); ?>" <?php echo esc_attr( $disabled ); ?> />
						</div>
					</div>
				</div>
				<div class="tab-pane" id="discount-by-days">
					<?php
					if ( stm_me_get_wpcfto_mod( 'enable_fixed_price_for_days', false ) ) {
						do_action( 'stm_fixed_price_for_days' );
					} else {
						do_action( 'stm_disc_by_days' );
					}
					?>
				</div>
				<div class="tab-pane" id="price-date-period">
					<?php do_action( 'stm_date_period' ); ?>
				</div>

                <!-- Start Customization -->
                <div class="tab-pane" id="price-booking-length">
					<?php do_action( 'stm_price_booking_length' ); ?>
				</div>
                <div class="tab-pane" id="minimum-booking-days">
					<?php do_action( 'stm_minimum_booking_days' ); ?>
				</div>
                <div class="tab-pane" id="unavailable-by-date">
                    <?php do_action( 'stm_unavailable_by_date' ); ?>
                </div>
                <!-- /End Customization -->
			</div>
		</div>
		<?php
	}

}

new PricePerHourExtend();
