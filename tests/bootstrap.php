<?php

require_once( dirname( dirname( __FILE__ ) ) . '/functions.php' );
/**
 * Determine if two associative arrays are similar
 *
 * Both arrays must have the same indexes with identical values
 * without respect to key ordering
 *
 * @param array $a
 * @param array $b
 * @return bool
 */
function arrays_are_similar( $a, $b ) {
	// if the indexes don't match, return immediately
	// if ( count( array_diff_assoc( $a, $b ) ) ) {
		// return false;
	// }
	// we know that the indexes, but maybe not values, match.
	// compare the values between the two arrays
	foreach ( $a as $k => $v ) {
		if ( $v !== $b[ $k ] ) {
			return false;
		}
	}
	// we have identical indexes, and no unequal values
	return true;
}
