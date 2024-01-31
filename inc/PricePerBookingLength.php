<?php

class PricePerBookingLength {
	private static $wpdbObj;
	private static $varId = 0;

	public function __construct() {
		global $wpdb;

		self::$wpdbObj = $wpdb;
		self::createPriceInfoDB();
		add_action( 'stm_price_booking_length', array( $this, 'priceForDateView' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'loadScriptStyles' ) );
		add_action( 'save_post', array( $this, 'stm_save_customer_note_meta' ), 15, 2 );
		add_filter( 'woocommerce_product_type_query', array( get_class(), 'setVarId' ), 100, 2 );
		add_filter( 'woocommerce_product_get_price', array( $this, 'updateVariationPrice' ), 100, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'updateVariationPrice' ), 100, 2 );
	}

	public static function hasDatePeriod() {
		return ( ! empty( self::getPeriods() ) ) ? true : false;
	}

    public static function table() {
		return self::$wpdbObj->prefix . 'rental_price_booking_length';
	}

	private function createPriceInfoDB() {
		$charset_collate = self::$wpdbObj->get_charset_collate();
		$table_name      = self::table();

		if ( self::$wpdbObj->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {

			$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            variation_id INT NOT NULL,
            starttime INT NOT NULL,
            endtime INT NOT NULL,
            price FLOAT NOT NULL,
            length INT NOT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	public static function setVarId( $bool, $productId ) {
		if ( 'product' === get_post_type( $productId ) ) {
			$terms = get_the_terms( $productId, 'product_type' );
			if ( $terms && ( 'simple' === $terms[0]->slug || 'variable' === $terms[0]->slug ) ) {
				self::$varId = stm_get_wpml_product_parent_id( $productId );
			}
		}
	}

	public static function updateVariationPrice( $price, $product ) {
		// update total cart
		if ( 'car_option' === $product->get_type() ) {
			return $price;
		}

		$table_name = self::table();

		$orderCookieData = stm_get_rental_order_fields_values();
		$newPrice        = 0;

		if ( 0 !== $orderCookieData['order_days'] ) {

			$varId = stm_get_wpml_product_parent_id( self::$varId );

			$endDate = ( isset( $orderCookieData['calc_return_date'] ) && ! empty( $orderCookieData['calc_return_date'] ) ) ? strtotime( $orderCookieData['calc_return_date'] ) : '';
			$dates   = stm_get_date_range_with_time( $orderCookieData['calc_pickup_date'], $orderCookieData['calc_return_date'] );

			$countDays = $orderCookieData['order_days'];

			$price = $price / $countDays;

			foreach ( $dates as $k => $date ) {
				$date = strtotime( $date );

				$endDate = ( $date !== $endDate && isset( $dates[ $k + 1 ] ) ) ? strtotime( $dates[ $k + 1 ] ) : $endDate;

				$response = self::$wpdbObj->get_row(
					self::$wpdbObj->prepare(
						"SELECT * FROM {$table_name}
                          WHERE
                          (variation_id = %d) AND
                          (length <= %d) AND
                          (starttime BETWEEN %s AND %s OR
                          endtime BETWEEN %s AND %s OR
                          (starttime <= %s AND endtime >= %s)) ORDER BY id DESC LIMIT 1",
						array( $varId, $countDays, $date, $endDate, $date, $endDate, $date, $endDate )
					)
				);
				if ( $k < $countDays ) {
					$price     = ( ! empty( $price ) ) ? $price : 0;
					$newPrice += ( ! empty( $response ) && isset( $response->price ) ) ? $response->price : $price;
				}
			}
		}

		$newPrice = ( 0 === $newPrice ) ? $price : $newPrice;

		return $newPrice;

		// return apply_filters( 'rental_variation_price_manipulations', $newPrice );
	}

	public static function getDescribeTotalByDays( $price, $varId ) {
		$table_name      = self::table();
		$orderCookieData = stm_get_rental_order_fields_values();
		$endDate         = ( isset( $orderCookieData['calc_return_date'] ) && ! empty( $orderCookieData['calc_return_date'] ) ) ? strtotime( $orderCookieData['calc_return_date'] ) : '';
		$dates           = stm_get_date_range_with_time( $orderCookieData['calc_pickup_date'], $orderCookieData['calc_return_date'] );

		$priceDescribe = array(
			'simple_price' => array(),
			'promo_price'  => array(),
		);

		$countDays = $orderCookieData['order_days'];

		foreach ( $dates as $k => $date ) {
			$date = strtotime( $date );

			$endDate = ( $date !== $endDate && isset( $dates[ $k + 1 ] ) ) ? strtotime( $dates[ $k + 1 ] ) : $endDate;

			$response = self::$wpdbObj->get_row(
				self::$wpdbObj->prepare(
					"SELECT * FROM {$table_name}
                          WHERE
                          (variation_id = %d) AND
                          (length <= %d) AND
                          (starttime BETWEEN %s AND %s OR
                          endtime BETWEEN %s AND %s OR
                          (starttime <= %s AND endtime >= %s)) ORDER BY id DESC LIMIT 1",
					array( $varId, $countDays, $date, $endDate, $date, $endDate, $date, $endDate )
				)
			);

			if ( $k < $countDays ) {
				if ( ! empty( $response ) && isset( $response->price ) ) {
					$priceDescribe['promo_price'][] = $response->price;
				} else {
					$priceDescribe['simple_price'][] = $price;
				}
			}
		}

		return $priceDescribe;
	}

	public static function getPeriods( $varId = '' ) {
		$id = ( ! empty( $varId ) ) ? $varId : stm_get_wpml_product_parent_id( get_the_ID() );

		$result = ( $id ) ? self::$wpdbObj->get_results( 'SELECT * FROM ' . self::table() . " WHERE `variation_id` = {$id} ORDER BY id ASC" ) : null;

		return $result;
	}

	public static function addPeriodIntoDB( $varId, $datePickup, $dateDrop, $price, $length ) {
		$table_name = self::table();

		if ( ! is_null( self::checkEntry( $datePickup, $dateDrop, $varId ) ) ) {
			self::$wpdbObj->update(
				$table_name,
				array(
					'variation_id' => $varId,
					'starttime'    => strtotime( $datePickup ),
					'endtime'      => strtotime( $dateDrop ),
                    'price'        => $price,
					'length'        => $length,
				),
				array(
					'variation_id' => $varId,
					'starttime'    => strtotime( $datePickup ),
					'endtime'      => strtotime( $dateDrop ),
				),
				array( '%d', '%d', '%d', '%s', '%d' ),
				array( '%d', '%d', '%d' )
			);
		} else {
			self::$wpdbObj->insert(
				$table_name,
				array(
					'variation_id' => $varId,
					'starttime'    => strtotime( $datePickup ),
					'endtime'      => strtotime( $dateDrop ),
					'price'        => $price,
                    'length'       => $length
				),
				array( '%d', '%d', '%d', '%s', '%d' )
			);
		}
	}

	public static function deleteEntry( $ids ) {
		$table_name = self::table();
		foreach ( explode( ',', $ids ) as $item ) {
			self::$wpdbObj->delete( $table_name, array( 'id' => $item ) );
		}
	}

	public static function checkEntry( $startTime, $endTime, $varId ) {
		$startTime = strtotime( $startTime );
		$endTime   = strtotime( $endTime );
		$result    = self::$wpdbObj->get_var( self::$wpdbObj->prepare( 'SELECT id FROM ' . self::table() . ' WHERE `variation_id` = %d AND `starttime` = %s AND `endtime` = %s', array( $varId, $startTime, $endTime ) ) );

		if ( ! empty( $result ) ) {
			return $result;
		}

		return null;
	}

	public static function loadScriptStyles() {
        wp_deregister_script( 'rental-price-js');
		wp_enqueue_script( 'rental-price-js', get_stylesheet_directory_uri() . '/assets/js/rental-price.js', array( 'jquery' ), '1.1', true );
		wp_enqueue_style( 'rental-price-styles', get_template_directory_uri() . '/inc/rental/assets/css/rental-price.css', array(), get_bloginfo( 'version' ), 'all' );
	}

	public static function stm_save_customer_note_meta( $post_id, $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		}

		if ( isset( $_POST['remove-booking-length'] ) && ! empty( $_POST['remove-booking-length'] ) ) {
			self::deleteEntry( sanitize_text_field( $_POST['remove-booking-length'] ) );
		}

		if ( isset( $_POST['date-booking-length-pickup'] ) && isset( $_POST['date-booking-length-drop'] ) && isset( $_POST['date-booking-length-price'] ) && isset( $_POST['length'] ) ) {
			$pickup_date_count = count( $_POST['date-booking-length-pickup'] );
			for ( $q = 0; $q < $pickup_date_count; $q++ ) {
				if ( isset( $_POST['date-booking-length-pickup'][ $q ] )
                    && isset( $_POST['date-booking-length-drop'][ $q ] )
                    && isset( $_POST['date-booking-length-price'][ $q ] )
                    && ! empty( $_POST['date-booking-length-pickup'][ $q ] )
                    && ! empty( $_POST['date-booking-length-drop'][ $q ] )
                    && ! empty( $_POST['date-booking-length-price'][ $q ] )
                    && ! empty( $_POST['length'][ $q ] )
                ) {
					self::addPeriodIntoDB(
                        $post->ID,
                        wp_slash( $_POST['date-booking-length-pickup'][ $q ] ),
                        wp_slash( $_POST['date-booking-length-drop'][ $q ] ),
						floatval( $_POST['date-booking-length-price'][ $q ] ),
                        // filter_var( $_POST['date-booking-length-price'][ $q ], FILTER_SANITIZE_NUMBER_FLOAT ),
                        wp_slash( $_POST['length'][ $q ] )
                     );
				}
			}
		}
	}

	public static function priceForDateView() {
		$periods = self::getPeriods();

		$disabled = ( get_the_ID() !== stm_get_wpml_product_parent_id( get_the_ID() ) ) ? 'disabled="disabled"' : '';

		?>
		<div class="rental-price-date-wrap">
			<ul class="rental-price-date-list">
				<?php
				if ( ! empty( $periods ) ) :
					foreach ( $periods as $k => $val ) :
						?>
						<li>
							<div class="repeat-number"><?php echo esc_html( $k + 1 ); ?></div>
							<table>
								<tr>
									<td>
										<?php echo esc_html__( 'Start Date', 'motors' ); ?>
									</td>
									<td>
										<input type="text" class="date-booking-length-pickup"
											value="<?php echo esc_html( gmdate( 'Y/m/d H:i', $val->starttime ) ); ?>"
											name="date-booking-length-pickup[]" <?php echo esc_attr( $disabled ); ?> />
									</td>
								</tr>
								<tr>
									<td>
										<?php echo esc_html__( 'End Date', 'motors' ); ?>
									</td>
									<td>
										<input type="text" class="date-booking-length-drop"
											value="<?php echo esc_html( date( 'Y/m/d H:i', $val->endtime ) ); ?>"
											name="date-booking-length-drop[]" <?php echo esc_attr( $disabled ); ?> />
									</td>
								</tr>
								<tr>
									<td>
										<?php echo esc_html__( 'Price', 'motors' ); ?>
									</td>
									<td>
										<input type="text" value="<?php echo esc_attr( $val->price ); ?>"
											name="date-booking-length-price[]" <?php echo esc_attr( $disabled ); ?> />
									</td>
								</tr>
                                <tr>
									<td>
										<?php echo esc_html__( 'Days >=', 'motors-child' ); ?>
									</td>
									<td>
										<input type="number" value="<?php echo esc_attr( $val->length ); ?>"
											name="length[]" <?php echo esc_attr( $disabled ); ?> />
									</td>
								</tr>
							</table>
							<div class="btn-wrap">
								<button class="remove-booking-length button-secondary"
										data-remove="<?php echo esc_attr( $val->id ); ?>" <?php echo esc_attr( $disabled ); ?>>
									<?php echo esc_html__( 'Remove', 'motors' ); ?>
								</button>
							</div>
						</li>
						<?php
					endforeach;
				else :
					?>
					<li>
						<div class="repeat-number">1</div>
						<table>
							<tr>
								<td>
									<?php echo esc_html__( 'Start Date', 'motors' ); ?>
								</td>
								<td>
									<input type="text" class="date-booking-length-pickup" name="date-booking-length-pickup[]"/>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo esc_html__( 'End Date', 'motors' ); ?>
								</td>
								<td>
									<input type="text" class="date-booking-length-drop" name="date-booking-length-drop[]"/>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo esc_html__( 'Price', 'motors' ); ?>
								</td>
								<td>
									<input type="text" name="date-booking-length-price[]"/>
								</td>
							</tr>
                            <tr>
                                <td>
                                    <?php echo esc_html__( 'Days >=', 'motors-child' ); ?>
                                </td>
                                <td>
                                    <input type="number" name="length[]" />
                                </td>
                            </tr>
						</table>
						<div class="btn-wrap">
							<button class="remove-booking-length button-secondary">
								<?php echo esc_html__( 'Remove', 'motors' ); ?>
							</button>
						</div>
					</li>
					<?php
				endif;
				?>
				<li>
					<button type="button" class="repeat-booking-length button-primary button-large" <?php echo esc_attr( $disabled ); ?>>
						<?php echo esc_html__( 'Add', 'motors' ); ?>
					</button>
				</li>
			</ul>
			<input type="hidden" name="remove-booking-length"/>
		</div>
		<?php
	}
}

new PricePerBookingLength();
