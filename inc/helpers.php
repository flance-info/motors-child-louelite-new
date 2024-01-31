<?php

add_filter( 'stm_rental_order_taxes', function ( $taxes ) {

	uasort( $taxes, function ( $a, $b ) {
		// Extract the tax type (QST, GST) from the label
		preg_match( '/\b(QST|GST)\b/', $a['label'], $matchesA );
		preg_match( '/\b(QST|GST)\b/', $b['label'], $matchesB );
		// Define the order of tax types
		$order = [ 'GST', 'QST' ];
		// Get the positions of tax types in the order array
		$positionA = array_search( $matchesA[1], $order );
		$positionB = array_search( $matchesB[1], $order );

		// Compare the positions and return the result
		return $positionA - $positionB;
	} );

	return $taxes;
} );