<?php

// Mock WordPress functions for standalone testing
// When running in WordPress environment, these will be overridden by actual WP functions
if ( ! function_exists( 'wp_debug_backtrace_summary' ) ) {
	/**
	 * Mock wp_debug_backtrace_summary for testing
	 *
	 * Returns a simple backtrace string for testing purposes.
	 * In WordPress, this function returns a formatted backtrace.
	 *
	 * @return string
	 */
	function wp_debug_backtrace_summary() {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );
		$summary = [];
		foreach ( $backtrace as $trace ) {
			if ( isset( $trace['function'] ) ) {
				$function = $trace['function'];
				if ( isset( $trace['class'] ) ) {
					$function = $trace['class'] . '::' . $function;
				}
				$summary[] = $function;
			}
		}
		return implode( ', ', $summary );
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Mock apply_filters for testing
	 *
	 * Returns the first argument unchanged (no filters applied).
	 * In WordPress, this function applies registered filters.
	 *
	 * @param string $hook_name The name of the filter hook.
	 * @param mixed  $value     The value to filter.
	 * @return mixed The value, unchanged.
	 */
	function apply_filters( $hook_name, $value ) {
		// In standalone tests, just return the value unchanged
		return $value;
	}
}

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
