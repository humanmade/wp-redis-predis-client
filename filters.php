<?php

require_once( dirname( __FILE__ ) . '/vendor/autoload.php' );
require_once( dirname( __FILE__ ) . '/functions.php' );

add_filter( 'wp_redis_check_client_dependencies_callback', function() {
	return 'wp_redis_client_check_dependencies';
});
add_filter( 'wp_redis_client_connection_callback', function() {
	return 'wp_redis_client_connection';
});
add_filter( 'wp_redis_setup_client_connection_callback', function() {
	return 'wp_redis_client_setup_connection';
});
