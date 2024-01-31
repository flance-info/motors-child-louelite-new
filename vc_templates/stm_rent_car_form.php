<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

$args = array(
	'post_type'      => 'stm_office',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
);

$style_type = 'style_1';
if ( ! empty( $style ) && 'style_2' === $style ) {
	$style_type = 'style_2';
}

/* ------ Start Customization ------- */
$work_hour = !empty( $office_working_hours ) ? array_map('trim', explode( ', ', $office_working_hours )) : '';
$work_hour_weekends = !empty( $office_weekends_working_hours ) ? array_map('trim', explode( ', ', $office_weekends_working_hours )) : '';
$disable_weekends = (!empty($disable_weekends)) ? true : false;

$disable_saturday = isset($disable_saturday) ? $disable_saturday : 'enable';
$disable_sunday = isset($disable_sunday) ? $disable_sunday : 'enable';

$start_of_week = (int)get_option( 'start_of_week', 1 );

$disabled_days_start = [];
$disabled_days_end = [];

$disabled_days = array(
	'start' => [],
	'end' => []
);

$weekends = ($start_of_week == 1) ? [0,6] : [6,7];

if($disable_weekends) {
	if($disable_saturday == 'all') {
		$disabled_days['start'][] = 6;
		$disabled_days['end'][] = 6;
	} else if($disable_saturday == 'pickup') {
		$disabled_days['start'][] = 6;
	} else if($disable_saturday == 'return') {
		$disabled_days['end'][] = 6;
	}

	if($disable_sunday == 'all') {
		$disabled_days['start'][] = ($start_of_week == 1) ? 0 : 7;
		$disabled_days['end'][] = ($start_of_week == 1) ? 0 : 7;
	} else if($disable_sunday == 'pickup') {
		$disabled_days['start'][] = ($start_of_week == 1) ? 0 : 7;
	} else if($disable_sunday == 'return') {
		$disabled_days['end'][] = ($start_of_week == 1) ? 0 : 7;
	}

	// if($disable_saturday == 'all'){
	// 	$weekends[] = ($start_of_week == 1) ? 6 : 6;
	// }
	// if($disable_sunday == 'all'){
	// 	$weekends[] = ($start_of_week == 1) ? 0 : 7;
	// }
}

// $disabled_days = $weekends;

/* --------------- END -------------- */

$fields    = stm_get_rental_order_fields_values( true );
$locations = stm_rental_locations( true );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );

$form_url   = stm_woo_shop_page_url();
$reserv_url = stm_woo_shop_page_url();
$items      = stm_get_cart_items();
if ( ! empty( $items['car_class'] ) ) {
	$is_disable = MinimumBookingDays::isDisableCar( $items['car_class']['id'] );
    $is_disableByDays = UnavailableByDate::isDisableCar( $items['car_class']['id'] );
	if(!$is_disable && !$is_disableByDays) {
		$form_url = stm_woo_shop_checkout_url();
	}
}

$date_time_format = $fields['format'];
$my_locale        = explode( '_', get_locale() );
?>

<div class="stm_rent_car_form_wrapper <?php echo esc_attr( $style_type . ' ' . $align . ' ' . $css_class ); ?>">
	<div class="stm_rent_car_form">
		<form action="<?php echo esc_url( $form_url ); ?>" method="get">
			<h4><?php esc_html_e( 'Pick Up', 'motors-wpbakery-widgets' ); ?></h4>
			<div class="stm_rent_form_fields">
				<h4 class="stm_form_title"><?php esc_html_e( 'Place to pick up the Car*', 'motors-wpbakery-widgets' ); ?></h4>
				<div class="stm_pickup_location">
					<i class="stm-service-icon-pin"></i>
					<select name="pickup_location" data-class="stm_rent_location">
						<option value=""><?php esc_html_e( 'Choose office', 'motors-wpbakery-widgets' ); ?></option>
						<?php if ( ! empty( $locations ) ) : ?>
							<?php foreach ( $locations as $location ) : ?>
								<option value="<?php echo esc_attr( $location[5] ); ?>" <?php echo ( esc_attr( $location[5] ) === $fields['pickup_location_id'] ) ? 'selected="selected"' : ''; ?>>
									<?php echo esc_html( $location[4] ); ?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>
				<label>
					<input type="checkbox" name="return_same" <?php echo ( 'on' === $fields['return_same'] ) ? 'checked' : ''; ?>/>
					<?php esc_html_e( 'Return to the same location', 'motors-wpbakery-widgets' ); ?>
				</label>

			</div>


			<div class="stm_rent_form_fields stm_rent_form_fields-drop">

				<div class="stm_same_return <?php echo ( 'on' === $fields['return_same'] ) ? '' : 'active'; ?>">
					<h4><?php esc_html_e( 'Return', 'motors-wpbakery-widgets' ); ?></h4>
					<h4 class="stm_form_title"><?php esc_html_e( 'Place to drop the Car*', 'motors-wpbakery-widgets' ); ?></h4>
					<div class="stm_pickup_location stm_drop_location">
						<i class="stm-service-icon-pin"></i>
						<select name="drop_location" data-class="stm_rent_location">
							<option value=""><?php esc_html_e( 'Choose office', 'motors-wpbakery-widgets' ); ?></option>
							<?php if ( ! empty( $locations ) ) : ?>
								<?php foreach ( $locations as $location ) : ?>
									<option
										<?php echo ( $location[5] === $fields['return_location_id'] ) ? 'selected="selected"' : ''; ?>
										value="<?php echo esc_attr( $location[5] ); ?>">
										<?php echo esc_html( $location[4] ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>

					</div>
				</div>

			</div>
			<div class="stm_rent_form_fields stm-horizontal">
				<div class="stm_date_time_input">
					<h4 class="stm_form_title"><?php esc_html_e( 'Pick-up Date/Time*', 'motors-wpbakery-widgets' ); ?></h4>
					<div class="stm_date_input">
						<input type="text" value="<?php echo esc_attr( $fields['pickup_date'] ); ?>" class="stm-date-timepicker-start" name="pickup_date" placeholder="<?php esc_attr_e( 'Pickup Date', 'motors-wpbakery-widgets' ); ?>" required readonly
								<?php echo ( ! empty( $fields['calc_pickup_date'] ) ) ? 'data-dt-hide="' . esc_attr( $fields['calc_pickup_date'] ) . '"' : ''; ?>
						/>
						<i class="stm-icon-date"></i>
					</div>
				</div>
				<div class="stm_date_time_input">
					<h4 class="stm_form_title"><?php esc_html_e( 'Drop Date/Time*', 'motors-wpbakery-widgets' ); ?></h4>
					<div class="stm_date_input">
						<input type="text" class="stm-date-timepicker-end" name="return_date"
							   value="<?php echo esc_attr( $fields['return_date'] ); ?>"
							   placeholder="<?php esc_attr_e( 'Return Date', 'motors-wpbakery-widgets' ); ?>" required readonly
								<?php echo ( ! empty( $fields['calc_return_date'] ) ) ? 'data-dt-hide="' . esc_attr( $fields['calc_return_date'] ) . '"' : ''; ?>
						/>
						<i class="stm-icon-date"></i>
					</div>
				</div>
			</div>
			<?php
			$old_days = stm_get_rental_order_fields_values();
			if ( ! empty( $old_days['order_days'] ) ) :
				?>
				<input type="hidden" name="order_old_days" value="<?php echo esc_attr( $old_days['order_days'] ); ?>" />
			<?php endif; ?>
			<?php if ( isset( $_GET['lang'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<input type="hidden" name="lang" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['lang'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
			<?php endif; ?>
			<?php if ( 'style_1' === $style_type ) : ?>
				<div class="form-btn-wrap stm-footer">
				<button type="submit"><?php esc_html_e( 'Find a vehicle', 'motors-wpbakery-widgets' ); ?><i
						class="fas fa-arrow-right"></i></button>
					<button type="submit" class="clear-data normal_font" data-type="clear-data"><i class="stm-rental-redo"></i><span><?php esc_html_e( 'Reset', 'motors-wpbakery-widgets' ); ?></span></button>
				</div>
			<?php else : ?>
				<button type="submit"><?php esc_html_e( 'Continue reservation', 'motors-wpbakery-widgets' ); ?></button>
					<button type="submit" class="clear-data normal_font" data-type="clear-data">
						<i class="stm-rental-redo"></i>
						<span>
							<?php esc_html_e( 'Clear Data', 'motors-wpbakery-widgets' ); ?>
						</span>
					</button>
			<?php endif; ?>
		</form>
	</div>
</div>

<script>
	(function($) {
		"use strict";

		$(document).ready(function(){
			$('input[name="return_same"]').on('change', function(){
			if($(this).prop('checked')) {
				$('.stm_same_return').slideUp();
			} else {
				$('.stm_same_return').slideDown();
			}
			});

			let form = $('.stm_rent_car_form form')

			$('.stm_pickup_location select').on('select2:open', function() {
				$('body').addClass('stm_background_overlay');
				$('.select2-container').css('width', $('.select2-dropdown').outerWidth());
			});

			$('.stm_pickup_location select').on('select2:close', function(){
				$('body').removeClass('stm_background_overlay');
			});

			$('.stm_date_time_input input').on('change', function(){
			if($(this).val() === '') {
				$(this).removeClass('active');
			} else {
				$(this).addClass('active');
			}
			});


			var locations = <?php echo wp_json_encode( $locations ); ?>;
			var contents = [];
			var content = '';
			var i = 0;


			for (i = 0; i < locations.length; i++) {
				content = '<ul class="stm_locations_description <?php echo esc_attr( $align . '_position' ); ?>">';
				content += '<li>' + locations[i][0] + '</li>';
				content += '</ul>';

				contents.push(content);
			}

			$(document).on('mouseover', '.stm_rent_location .select2-results__options li', function(){
				var currentLi = ($(this).index()) - 1;
				$('.stm_rent_location .stm_locations_description').remove();
				$('.stm_rent_location').append(contents[currentLi]);
			});

			var stmStart = $('.stm-date-timepicker-start')
			var stmEnd = $('.stm-date-timepicker-end')
			var stmStartVal = stmStart.val();
			var stmEndVal = stmEnd.val();

			/*Timepicker*/
			var stmToday = new Date();
			var stmTomorrow = new Date(+new Date() + 86400000);
			var startDate = stmStartVal ? stmStartVal : false;
			var endDate = stmEndVal ? stmEndVal : false;
			var dateTimeFormat = '<?php echo esc_js( $date_time_format ); ?>';
			var dateTimeFormatHide = '<?php echo esc_js( $fields['moment_format'] ); ?>';

			/* ------ Start Customization ------- */
			let disable_weekends = '<?php echo $disable_weekends;?>';

			let schedule_week = <?php echo !empty( $work_hour ) ? wp_json_encode( $work_hour ) : '[]'; ?>;
			let schedule_weekends = <?php echo !empty( $work_hour_weekends ) ? wp_json_encode( $work_hour_weekends ) : '[]'; ?>;
			let weekends = <?php echo !empty( $weekends ) ? wp_json_encode( $weekends ) : '[]'; ?>;

			var schedule_used = schedule_week;

			let disable_saturday = '<?php echo esc_js( $disable_saturday ); ?>';
			let disable_sunday = '<?php echo esc_js( $disable_sunday ); ?>';

			/* --------------- END -------------- */

			stmStart.datetimepicker({
				format: dateTimeFormat,
				dayOfWeekStart: <?php echo esc_js( get_option( 'start_of_week' ) ); ?>,
				defaultDate: stmToday,
				defaultSelect: true,
				closeOnDateSelect: false,
				timeHeightInTimePicker: 40,
				validateOnBlur: false,
				<?php if ( ! empty( $work_hour ) ) : ?>
				allowTimes: schedule_used,
				<?php endif; ?>
				disabledWeekDays: <?php echo wp_json_encode($disabled_days['start']); ?>,
				fixed: false,
				lang: currentLocale ? currentLocale : '<?php echo esc_js( $my_locale[0] ); ?>',
				onShow: function( ct ) {
					$('body').addClass('stm_background_overlay stm-lock');

					var stmEndDate = stmEnd.val() ? moment(stmEnd.val()) : false;

					if(stmEndDate) {
						stmEndDate = stmEndDate.toDate();
					}

					this.setOptions({
						minDate: new Date(),
						maxDate: stmEndDate
					});

					$(".xdsoft_time_variant").css('margin-top', '-600px');
				},
				onSelectDate: function (ct, $i) {
					$i.datetimepicker('close');

					$('.xdsoft_time').removeClass('xdsoft_current');

					/* ------ Start Customization ------- */
					if($.inArray(ct.getDay(),weekends) !== -1){
						schedule_used = schedule_weekends;
					} else {
						schedule_used = schedule_week;
					}

					this.setOptions({
						allowTimes: schedule_used,
						minTime: schedule_used[0]
					});
					/* --------------- END -------------- */

				},
				onClose: function (ct, $i) {
					startDate = ct;

					/* ------ Start Customization ------- */
					let val = $i.val();
					let hour = parseInt(moment(ct).format('HH'));
					let time = moment(ct).format('HH:mm');
					let datetime = moment(ct).format('MMMM D, Y HH:mm');

					let schedule_h_start = parseInt(moment(schedule_used[0], 'HH:mm A').format('HH'));
					let i_end = schedule_used.length - 1;
					let schedule_h_end = parseInt(moment(schedule_used[i_end], 'HH:mm A').format('HH'));

					if(hour < schedule_h_start) {
						datetime = datetime.replace(time, schedule_used[0]);
					}
					if(hour > schedule_h_end) {
						datetime = datetime.replace(time, schedule_used[i_end]);
					}

					$i.val(datetime);
					/* --------------- END -------------- */

					if (ct < new Date()) {
						$i.datetimepicker('reset');
					}

					$i.attr('data-dt-hide', moment(ct).format(dateTimeFormatHide));

					if (startDate && endDate) {
						checkDate(moment(startDate).format(dateTimeFormatHide), moment(endDate).format(dateTimeFormatHide));
					}

					$('body').removeClass('stm_background_overlay stm-lock');

				},
				onGenerate: function () {
					if(!stmStart.val()) {
						$('.xdsoft_time').removeClass('xdsoft_current');
					}
				}
			});

			stmEnd.datetimepicker({
				format:dateTimeFormat,
				dayOfWeekStart: <?php echo esc_js( get_option( 'start_of_week' ) ); ?>,
				defaultDate: stmTomorrow,
				defaultSelect: true,
				closeOnDateSelect: false,
				timeHeightInTimePicker: 40,
				validateOnBlur: false,
				<?php if ( ! empty( $work_hour ) ) : ?>
				allowTimes: schedule_used,
				<?php endif; ?>
				disabledWeekDays: <?php echo wp_json_encode($disabled_days['end']); ?>,
				fixed: false,
				lang: currentLocale ? currentLocale : '<?php echo esc_js( $my_locale[0] ); ?>',
				onShow: function (ct, $i) {
					$('body').addClass('stm_background_overlay stm-lock');

					var stmStartDate = startDate ? moment(startDate).add(1, 'day') : false;
					if (stmStartDate) {
						stmStartDate = stmStartDate.toDate();
					} else {
						stmStartDate = new Date();
					}

					this.setOptions({
						minDate: stmStartDate,
						defaultDate: stmStartDate,
					})
				},
				onSelectDate: function (ct, $i) {
					$('.xdsoft_time').removeClass('xdsoft_current');
					$i.datetimepicker('close');

					/* ------ Start Customization ------- */
					if($.inArray(ct.getDay(),weekends) !== -1){
						schedule_used = schedule_weekends;
					} else {
						schedule_used = schedule_week;
					}

					this.setOptions({
						allowTimes: schedule_used,
						minTime: schedule_used[0]
					});
					/* --------------- END -------------- */
				},
				onClose: function( ct, $i ) {
					endDate = ct;

					/* ------ Start Customization ------- */
					let val = $i.val();
					let hour = parseInt(moment(ct).format('HH'));
					let time = moment(ct).format('HH:mm');
					let datetime = moment(ct).format('MMMM D, Y HH:mm');

					let schedule_h_start = parseInt(moment(schedule_used[0], 'HH:mm A').format('HH'));
					let i_end = schedule_used.length - 1;
					let schedule_h_end = parseInt(moment(schedule_used[i_end], 'HH:mm A').format('HH'));

					if(hour < schedule_h_start) {
						datetime = datetime.replace(time, schedule_used[0]);
					}
					if(hour > schedule_h_end) {
						datetime = datetime.replace(time, schedule_used[i_end]);
					}

					$i.val(datetime);
					/* --------------- END -------------- */

					if(ct < new Date()) {
						$i.datetimepicker('reset');
					}

					$i.attr('data-dt-hide', moment(ct).format(dateTimeFormatHide));

					if(!stmStart.val() && !$i.val()) {
						checkDate(moment(startDate).format(dateTimeFormatHide), moment(endDate).format(dateTimeFormatHide));
					}

					var s = moment(startDate);
					var e = moment(ct);

					if(e.diff(s) < 0) {
						s = s.add(1, 'day');
						$i.val('');
						$i.attr('data-dt-hide', '');
						this.setOptions({
							minDate: s.toDate(),
							defaultDate: s.toDate()
						});
					}

					$('body').removeClass('stm_background_overlay stm-lock');
				},
				onGenerate: function (ct, $i) {
					if ($i.val() === '') {
						$('.xdsoft_time').removeClass('xdsoft_current');
					}
				}
			});

			let clear_button = $('form .clear-data')

			<?php if ( ! empty( $fields['pickup_date'] ) && ! empty( $fields['return_date'] ) ) : ?>
			clear_button.each(function (index, item) {
				$(item).addClass('isNotHidden')
			})
			<?php endif; ?>

			clear_button.each(function (index, item) {
				if (!$(item).hasClass('isNotHidden'))
					$(item).hide()
			})

			form.find('select, input').change(function () {
				let form = $(this.form)
				form.find('.clear-data').show()
			})

			clear_button.on('click', function (e) {
				e.preventDefault();

				let form = $(this.form)

				form.attr('action', '<?php echo esc_url( $reserv_url ); ?>');

				jQuery.ajax({
					url: ajaxurl,
					type: "GET",
					dataType: 'json',
					context: this,
					data: 'action=stm_ajax_clear_data&security=' + stm_security_nonce,
					success: function (data) {}
				});

				$("select[name='pickup_location']").val('').trigger('change');
				$("select[name='drop_location']").val('').trigger('change');

				$.each(form.serializeArray(), function (i, field) {
					if(field.name === 'pickup_location' || field.name === 'drop_location') {
						$.cookie('stm_' + field.name + '_' + stm_site_blog_id, '', { expires: -1, path: '/' });
						$.cookie('stm_car_watched', '', { expires: -1, path: '/' });
						return
					} else {
						let _field = $('input[name="' + field.name + '"]')
						_field.val('');
						_field.attr('data-dt-hide', '');
					}

					$.cookie('stm_' + field.name + '_' + stm_site_blog_id, '', { expires: -1, path: '/' });
					$.cookie('stm_car_watched', '', { expires: -1, path: '/' });
				});

				$(this).hide();

				return false;
			});

			/*Set cookie with order data*/
			form.on('submit', function (e) {
				var error = false;

				$('.stm_pickup_location').removeClass('stm_error');

				/*Save in cookies all fields*/
				if($.cookie('stm_pickup_date_' + stm_site_blog_id) != null) {
					$.cookie('stm_pickup_date_old_' + stm_site_blog_id, $.cookie('stm_pickup_date_' + stm_site_blog_id), {expires: 7, path: '/'});
					$.cookie('stm_return_date_old_' + stm_site_blog_id, $.cookie('stm_return_date_' + stm_site_blog_id), {expires: 7, path: '/'});
				}

				$.each($(this).serializeArray(), function (i, field) {
					$.cookie('stm_' + field.name + '_' + stm_site_blog_id, encodeURIComponent(field.value), {expires: 7, path: '/'});

					if(field.name == 'pickup_date' || field.name == 'return_date') {
						if (typeof $('input[name="' + field.name + '"]').attr('data-dt-hide') == 'undefined' || $('input[name="' + field.name + '"]').attr('data-dt-hide') == '') {
							$('input[name="' + field.name + '"]').addClass('stm_error');
							error = true;
						} else {
							$('input[name="' + field.name + '"]').removeClass('stm_error');
							error = false;
						}

						$.cookie('stm_calc_' + field.name + '_' + stm_site_blog_id, (typeof $('input[name="' + field.name + '"]').attr('data-dt-hide') != 'undefined') ? $('input[name="' + field.name + '"]').attr('data-dt-hide') : '', {expires: 7, path: '/'});
					}
				});


				if(!$('input[name="return_same"]').prop('checked')) {
					$.cookie('stm_return_same_' + stm_site_blog_id, "off", {expires: 7, path: '/'});
				}

				var stm_pickup_location = $('.stm_pickup_location select').val();
				var return_same = $('input[name="return_same"]').prop('checked');
				var stm_drop_location = $('.stm_drop_location select').val();

				if (stm_pickup_location == '') {
					$('.stm_pickup_location:not(".stm_drop_location")').addClass('stm_error');
					error = true;
				}

				if (return_same == '' && stm_drop_location == '') {
					$('.stm_drop_location').addClass('stm_error');
					error = true;
				}

				if (error) {
					e.preventDefault();
				}
			});

			$('.stm-template-car_rental .stm_rent_order_info .image.image-placeholder a').on('click', function(e){
				var $stmThis = $('.stm_rent_car_form form');
				$stmThis.trigger('submit');
				e.preventDefault();
			});

			$('body').on('click touchstart', '.stm-rental-overlay', function(e) {
				$('.stm-date-timepicker-start').trigger('blur');
				$('.stm-date-timepicker-end').trigger('blur');
				$('.xdsoft_datetimepicker').hide();
				$('body').removeClass('stm_background_overlay');
			});

		});

	})(jQuery);

	function checkDate ($start, $end) {

		var locationId = jQuery('select[name="pickup_location"]').select2("val");
		var stm_timeout_rental;
		if(locationId != '') {
			jQuery.ajax({
				url: ajaxurl,
				type: "GET",
				dataType: 'json',
				context: this,
				data: 'startDate=' + $start + '&endDate=' + $end + '&action=stm_ajax_check_is_available_car_date&security=' + stm_security_nonce,
				success: function (data) {
					jQuery("#select-vehicle-popup").attr("href", $("#select-vehicle-popup").attr('href').split("?")[0] + "?pickup_location=" + locationId);
					if (data != '') {
						clearTimeout(stm_timeout_rental);
						jQuery('.choose-another-class').addClass('single-add-to-compare-visible');
						jQuery(".choose-another-class").addClass('car-reserved');
						jQuery(".choose-another-class").find(".stm-title.h5").html(data);
						stm_timeout_rental = setTimeout(function () {
							jQuery('.choose-another-class').removeClass('single-add-to-compare-visible').removeClass('car-reserved');
						}, 10000);
					}
				}
			});
		}
	}


	function tConvert (time) {
	  // Check correct time format and split into components
	  time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

	  if (time.length > 1) { // If time format correct
	    time = time.slice (1);  // Remove full string match value
	    time[5] = +time[0] < 12 ? '' : ''; // Set AM/PM
	    time[0] = +time[0] % 12 || 12; // Adjust hours
	  }
	  return time.join (''); // return adjusted time or original string
	}
</script>
