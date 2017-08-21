<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

$_wp_predis_dir = getenv( 'WP_PREDIS_DIR' );

require_once $_tests_dir . '/includes/functions.php';
require_once $_wp_predis_dir . '/functions.php';

WP_Predis\add_filters( 'tests_add_filter' );
