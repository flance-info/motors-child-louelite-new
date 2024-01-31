<?php

remove_action( 'stm_rental_meta_box', 'stm_me_rental_add_meta_box' );
add_action( 'stm_rental_meta_box', 'stm_me_rental_add_meta_box_extend' );


function stm_me_rental_add_meta_box_extend() {
	add_action( 'add_meta_boxes', 'stm_me_add_price_per_hour_metabox_extend' );
}

function stm_me_add_price_per_hour_metabox_extend() {
	$title = __( 'Car Rent Price Info', 'stm_motors_extends' );

	if ( get_the_ID() !== stm_get_wpml_product_parent_id( get_the_ID() ) ) {
		$title = __( 'Car Rent Price Info (This fields are not editable.)', 'stm_motors_extends' );
	}

	add_meta_box(
		'price_per_hour',
		$title,
		array( 'PricePerHourExtend', 'pricePerHourView' ),
		'product',
		'advanced',
		'high'
	);
}
