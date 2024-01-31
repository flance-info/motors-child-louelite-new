<?php

class MinimumBookingDays {
	private static $wpdbObj;
	private static $varId = 0;

	public function __construct() {
		global $wpdb;

		self::$wpdbObj = $wpdb;
		self::createDB();
		add_action( 'stm_minimum_booking_days', array( $this, 'minimumBookingDaysView' ) );
		add_action( 'save_post', array( $this, 'stm_save_meta' ), 15, 2 );
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_fields' ), 15 );
		add_action( 'product_cat_edit_form_fields', array( $this, 'edit_category_fields' ), 15 );
		add_action( 'created_term', array( $this, 'save_category_fields' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'save_category_fields' ), 10, 3 );
	}

	public static function hasDatePeriod() {
		return ( ! empty( self::getPeriods() ) ) ? true : false;
	}

    public static function table() {
		return self::$wpdbObj->prefix . 'rental_minimum_booking_days';
	}

	private function createDB() {
		$charset_collate = self::$wpdbObj->get_charset_collate();
		$table_name      = self::table();

		if ( self::$wpdbObj->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {

			$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            variation_id INT NOT NULL,
            starttime INT NOT NULL,
            endtime INT NOT NULL,
            length INT NOT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	public static function getPeriods( $varId = '' ) {
		$id = ( ! empty( $varId ) ) ? $varId : stm_get_wpml_product_parent_id( get_the_ID() );

		$result = ( $id ) ? self::$wpdbObj->get_results( 'SELECT * FROM ' . self::table() . " WHERE `variation_id` = {$id} ORDER BY id ASC" ) : null;

		return $result;
	}

	public static function addPeriodIntoDB( $varId, $datePickup, $dateDrop, $length ) {
		$table_name = self::table();

		if ( ! is_null( self::checkEntry( $datePickup, $dateDrop, $varId ) ) ) {
			self::$wpdbObj->update(
				$table_name,
				array(
					'variation_id' => $varId,
					'starttime'    => strtotime( $datePickup ),
					'endtime'      => strtotime( $dateDrop ),
					'length'        => $length,
				),
				array(
					'variation_id' => $varId,
					'starttime'    => strtotime( $datePickup ),
					'endtime'      => strtotime( $dateDrop ),
				),
				array( '%d', '%d', '%d', '%d' ),
				array( '%d', '%d', '%d' )
			);
		} else {
			self::$wpdbObj->insert(
				$table_name,
				array(
					'variation_id' => $varId,
					'starttime'    => strtotime( $datePickup ),
					'endtime'      => strtotime( $dateDrop ),
                    'length'       => $length
				),
				array( '%d', '%d', '%d', '%d' )
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

	public static function stm_save_meta( $post_id, $post ) {
		if ( 'product' !== $post->post_type ) {
			return;
		}

		if ( isset( $_POST['remove-minimum-booking'] ) && ! empty( $_POST['remove-minimum-booking'] ) ) {
			self::deleteEntry( sanitize_text_field( $_POST['remove-minimum-booking'] ) );
		}

		if ( isset( $_POST['date-minimum-booking-pickup'] ) && isset( $_POST['date-minimum-booking-drop'] ) && isset( $_POST['length'] ) ) {
			$pickup_date_count = count( $_POST['date-minimum-booking-pickup'] );
			for ( $q = 0; $q < $pickup_date_count; $q++ ) {
				if ( isset( $_POST['date-minimum-booking-pickup'][ $q ] )
					&& isset( $_POST['date-minimum-booking-drop'][ $q ] )
                    && isset( $_POST['minimum-booking-length'][ $q ] )
                    && ! empty( $_POST['date-minimum-booking-pickup'][ $q ] )
                    && ! empty( $_POST['date-minimum-booking-drop'][ $q ] )
                    && ! empty( $_POST['minimum-booking-length'][ $q ] )
                ) {
					self::addPeriodIntoDB(
                        $post->ID,
                        wp_slash( $_POST['date-minimum-booking-pickup'][ $q ] ),
                        wp_slash( $_POST['date-minimum-booking-drop'][ $q ] ),
                        wp_slash( $_POST['minimum-booking-length'][ $q ] )
                     );
				}
			}
		}
	}


	public static function isDisableCar( $varId ) {
		$table_name      = self::table();
		$orderCookieData = stm_get_rental_order_fields_values();
		$countDays = $orderCookieData['order_days'];

		$period = self::getMinimumPeriod( $varId );

		return $countDays < $period;
	}


	public static function getMinimumPeriod( $varId ){
		$varId = ( ! empty( $varId ) ) ? $varId : stm_get_wpml_product_parent_id( get_the_ID() );
		$table_name      = self::table();
		$orderCookieData = stm_get_rental_order_fields_values();
		$startDate         = ( isset( $orderCookieData['pickup_date'] ) && ! empty( $orderCookieData['pickup_date'] ) ) ? strtotime( $orderCookieData['pickup_date'] ) : '';
		$endDate         = ( isset( $orderCookieData['return_date'] ) && ! empty( $orderCookieData['return_date'] ) ) ? strtotime( $orderCookieData['return_date'] ) : '';

		$countDays = $orderCookieData['order_days'];

		$period = 0;

		if(!empty($startDate) && !empty($endDate)) {
			$response = self::$wpdbObj->get_row(
				self::$wpdbObj->prepare(
					"SELECT * FROM {$table_name}
					WHERE
					(variation_id = %d) AND
					(starttime BETWEEN %s AND %s OR
						endtime BETWEEN %s AND %s OR
						(starttime <= %s AND endtime >= %s)) ORDER BY id DESC LIMIT 1",
						array( $varId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate )
				)
			);

			if(isset($response) && !empty($response) && isset($response->length)) {
				return intval($response->length);
			}
		}

		return $period;
	}


	public static function minimumBookingDaysView() {
		$periods = self::getPeriods();

		$disabled = ( get_the_ID() !== stm_get_wpml_product_parent_id( get_the_ID() ) ) ? 'disabled="disabled"' : '';

		?>
		<div class="rental-price-date-wrap" id="minimum-booking-tab-wrapper">
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
										<input type="text" class="date-minimum-booking-pickup"
											value="<?php echo esc_html( gmdate( 'Y/m/d H:i', $val->starttime ) ); ?>"
											name="date-minimum-booking-pickup[]" <?php echo esc_attr( $disabled ); ?> />
									</td>
								</tr>
								<tr>
									<td>
										<?php echo esc_html__( 'End Date', 'motors' ); ?>
									</td>
									<td>
										<input type="text" class="date-minimum-booking-drop"
											value="<?php echo esc_html( date( 'Y/m/d H:i', $val->endtime ) ); ?>"
											name="date-minimum-booking-drop[]" <?php echo esc_attr( $disabled ); ?> />
									</td>
								</tr>
                                <tr>
									<td>
										<?php echo esc_html__( 'Days >=', 'motors-child' ); ?>
									</td>
									<td>
										<input type="number" value="<?php echo esc_attr( $val->length ); ?>"
											name="minimum-booking-length[]" <?php echo esc_attr( $disabled ); ?> />
									</td>
								</tr>
							</table>
							<div class="btn-wrap">
								<button class="remove-minimum-booking button-secondary"
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
									<input type="text" class="date-minimum-booking-pickup" name="date-minimum-booking-pickup[]"/>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo esc_html__( 'End Date', 'motors' ); ?>
								</td>
								<td>
									<input type="text" class="date-minimum-booking-drop" name="date-minimum-booking-drop[]"/>
								</td>
							</tr>
                            <tr>
                                <td>
                                    <?php echo esc_html__( 'Days >=', 'motors-child' ); ?>
                                </td>
                                <td>
                                    <input type="number" name="minimum-booking-length[]" />
                                </td>
                            </tr>
						</table>
						<div class="btn-wrap">
							<button class="remove-minimum-booking button-secondary">
								<?php echo esc_html__( 'Remove', 'motors' ); ?>
							</button>
						</div>
					</li>
					<?php
				endif;
				?>
				<li>
					<button type="button" class="repeat-minimum-booking button-primary button-large" <?php echo esc_attr( $disabled ); ?>>
						<?php echo esc_html__( 'Add', 'motors' ); ?>
					</button>
				</li>
			</ul>
			<input type="hidden" name="remove-minimum-booking"/>
		</div>
		<?php
	}


	public static function add_category_fields(){
	?>
	<div class="form-field term-display-type-wrap">
		<label for="minimum_period"><?php esc_html_e( 'Minimum Booking Days', 'motors-child' ); ?></label>
		<input type="number" name="minimum_period" id="minimum_period" min="1" step="1" style="width: 122px;">
	</div>
	<?php
	}

	public static function edit_category_fields( $term ){
		$minimum_period = get_term_meta( $term->term_id, 'minimum_period', true );
		?>
		<tr class="form-field term-display-type-wrap">
			<th scope="row" valign="top"><label><?php esc_html_e( 'Minimum Booking Days', 'motors-child' ); ?></label></th>
			<td>
				<input type="number"
					name="minimum_period"
					id="minimum_period"
					min="1" step="1"
					value="<?php echo $minimum_period;?>"
					style="width: 122px;" />
			</td>
		</tr>
		<?php
	}

	public function save_category_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
		if ( isset( $_POST['minimum_period'] ) && 'product_cat' === $taxonomy ) { // WPCS: CSRF ok, input var ok.
			update_term_meta( $term_id, 'minimum_period', esc_attr( $_POST['minimum_period'] ) ); // WPCS: CSRF ok, sanitization ok, input var ok.
		}
	}
}

new MinimumBookingDays();
