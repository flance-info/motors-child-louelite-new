<?php
require_once 'inc/helpers.php';
require_once 'inc/lauto.php';
require_once 'inc/PricePerHourExtend.php';
require_once 'inc/PricePerBookingLength.php';
require_once 'inc/MinimumBookingDays.php';
require_once 'inc/UnavailableByDate.php';
require_once 'inc/rent_price_extend.php';

function pre_var($var){
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}

function pre_die($var){
	pre_var($var);
	die();
}


add_action( 'wp_enqueue_scripts', 'stm_enqueue_parent_styles' );

function stm_enqueue_parent_styles() {

	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('stm-theme-style') );

	wp_deregister_style( 'stm-theme-style-sass' );
	wp_enqueue_style( 'stm-theme-style-sass', get_stylesheet_directory_uri() . '/assets/css/app.css', array('stm-skin-custom'), STM_THEME_VERSION, 'all' );
	wp_enqueue_style( 'stm-fixes', get_stylesheet_directory_uri() . '/assets/css/fixes.css', array('stm-skin-custom', 'stm-theme-style'), STM_THEME_VERSION, 'all' );

}

add_filter( 'woocommerce_order_button_text', 'misha_custom_button_text' );

function misha_custom_button_text( $button_text ) {
   return 'Make booking'; // new text is here
}



add_filter('stm_rent_current_total', 'child_current_total', 15);
function child_current_total($total) {

	$tax_totals = WC()->cart->get_tax_totals();
	$tax_rates = [];

	if(count($tax_totals)) {
		$tax_class = new WC_Tax();
		foreach ($tax_totals as $key => $item) {
			$tax_data = $tax_class->_get_tax_rate($item->tax_rate_id);
			$tax_name = $tax_data['tax_rate_name'];
			$tax_rates[$tax_name] = floatval($tax_data['tax_rate']);
		}
	}

	$cartfees = WC()->cart->get_fees();

    if(!empty($cartfees)) {
    	$fields = stm_get_rental_order_fields_values();

    	// $original_fees_total = 0;
        // $calculated_fees_total = 0;

		$fees_orig = [];
		$fees = [];
		$taxes = [];

        foreach($cartfees as $key => $fee) {
            // $original_fees_total += $fee->total;
			// $calculated_fees_total += $fee->total * floatval($fields['order_days']);

			$fees_orig[] = $fee->total;
            $fees[] = $fee->total * floatval($fields['order_days']);

            $fee_tax = 0;

            if($fee->taxable == 1) {
                // $calculated_fees_total += $fee->tax * $fields['order_days'];
				$fees[] = $fee->tax * floatval($fields['order_days']);
            }
        }

        $total = WC()->cart->total;
	    // $total -= $original_fees_total;
	    // $total += $calculated_fees_total;

		$total -= array_sum($fees_orig);
		$total -= WC()->cart->tax_total;

	    $total += array_sum($fees);

		if(count($tax_rates)) {
			foreach ($tax_rates as $key => $rate) {
				$taxes[] = ($total / 100) * $rate;
			}
		}

		$total += array_sum($taxes);

	    return $total;

    } else {
    	return $total;
    }
}

add_filter('stm_rental_order_taxes', 'stm_child_rental_order_taxes', 15);
function stm_child_rental_order_taxes($taxes){
	$tax_totals = WC()->cart->get_tax_totals();
	$tax_rates = [];

	if(count($tax_totals)) {
		$tax_class = new WC_Tax();
		foreach ($tax_totals as $key => $item) {
			$tax_data = $tax_class->_get_tax_rate($item->tax_rate_id);
			$tax_name = $tax_data['tax_rate_name'];
			$tax_rates[$key] = floatval($tax_data['tax_rate']);
		}
	}

	$cartfees = WC()->cart->get_fees();

    if(!empty($cartfees)) {
    	$fields = stm_get_rental_order_fields_values();

		$fees_orig = [];
		$fees = [];

        foreach($cartfees as $key => $fee) {

			$fees_orig[] = $fee->total;
            $fees[] = $fee->total * floatval($fields['order_days']);

            $fee_tax = 0;

            if($fee->taxable == 1) {
				$fees[] = $fee->tax * floatval($fields['order_days']);
            }
        }

        $total = WC()->cart->total;

		$total -= array_sum($fees_orig);
		$total -= WC()->cart->tax_total;

	    $total += array_sum($fees);

		if(count($tax_rates)) {
			foreach ($tax_rates as $key => $rate) {
				$taxes[$key]['value'] = wc_price(($total / 100) * $rate);
			}
		}
    }

	return $taxes;
}


// order received calculated fees and totals
add_filter('stm_rental_order_info', 'child_rental_order_info');
function child_rental_order_info($order_info) {
    $billing_fields = stm_rental_billing_info();
    $items = stm_get_cart_items();
    if ( !empty( $billing_fields['total'] ) ) {
        $order_info['payment']['content'] = sprintf( __( 'Estimated Total - %s', 'motors' ), wc_price($items['total']) );
    }

    return $order_info;
}

// calculated fees in emails
add_filter('woocommerce_get_order_item_totals', 'child_order_item_totals', 100, 3);
function child_order_item_totals($total_rows, $that, $tax_display) {

    $fees = $that->get_fees();
    $fields = stm_get_rental_order_fields_values();

    foreach ($total_rows as $key => $value) {
        if(strpos($key, 'fee_') !== false){
            unset($total_rows[$key]);
        }
    }

    foreach ($fees as $fee_id => $fee_value) {
        $current_fee = get_page_by_title($fee_value->get_name(), OBJECT, 'waef');

        if (get_locale() == 'fr_FR') {
            $fee_label = get_field('fee_french_title', $current_fee->ID);
        } else {
            $fee_label = $fee_value->get_name();
        }

        $fee_tax = 0;
        $total_tax = $fee_value->get_total_tax();


        if(!empty($total_tax)) {
            $fee_tax = floatval($total_tax) * $fields['order_days'];
        }


        $fee_rows['fee_'.$fee_id] = [
            'label' => $fee_label,
            'value' => wc_price($fee_value->get_total() * $fields['order_days'] + $fee_tax),
        ];
    }

    $total_rows = $fee_rows + $total_rows;

    $items = stm_get_cart_items();

    $total_rows['order_total']['value'] = wc_price($items['total']);

    foreach($total_rows as $key => $val) {
        if($key == 'cart_subtotal') {
            $item = $total_rows[$key];
            unset($total_rows[$key]);
            array_unshift($total_rows, $item);
        }

        if($key == 'payment_method') {
            $item = $total_rows[$key];
            unset($total_rows[$key]);
            array_push($total_rows, $item);
        }

        if($key == 'order_total') {
            $item = $total_rows[$key];
            unset($total_rows[$key]);
            array_push($total_rows, $item);
        }
    }

    return $total_rows;
}


if(!function_exists('stm_getDefaultVariablePrice')) {
	function stm_getDefaultVariablePrice($product_id, $index, $from){
		return stm_get_default_variable_price($product_id, $index, $from);
	}
}


add_action('template_redirect', function(){
	if(is_checkout()) {
		$items      = stm_get_cart_items();
		if ( ! empty( $items['car_class'] ) ) {
			$is_disable = MinimumBookingDays::isDisableCar( $items['car_class']['id'] );
            $is_disableByDays = UnavailableByDate::isDisableCar( $items['car_class']['id'] );
			if($is_disable || $is_disableByDays) {
				wp_safe_redirect(stm_woo_shop_page_url());
			}
		}
	}
//	stm_get_cars();
});


function stm_get_cars(){
	$ids = [];
	$ids_first = [];
	$ids_disabled = [];
	$args = array(
		'post_status' => 'publish',
		'numberposts' => -1,
		'posts_per_page' => -1,
	);
	$products = wc_get_products( $args );
	if(count($products)) {
		foreach ($products as $key => $product) {
			$id = $product->get_ID();
			$is_disable = MinimumBookingDays::isDisableCar($id);
            $is_disableByDays = UnavailableByDate::isDisableCar($id);
			if($is_disable || $is_disableByDays){
				$ids_disabled[] = $id;
			} else {
                $is_variation = get_post_meta($id, 'in_rental_car_de', true);
                if(empty($is_variation)) {
                    $ids[] = $id;
                } else {
                    $ids_first[] = $id;
                }
			}

		}
		$ids = array_merge($ids_first, $ids);
		$ids = array_merge($ids,$ids_disabled);
	}

	return $ids;
}


add_action('pre_get_posts', 'stm_child_pre_get_posts');

function stm_child_pre_get_posts($query){
	if ( ! is_admin() && is_shop() && $query->is_main_query() ) {
		$query->query_vars['post__in'] = stm_get_cars();
		$query->query_vars['orderby'] = 'post__in';
		$query->query_vars['order'] = 'ASC';
	}
}
