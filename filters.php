<?php

require_once dirname( __FILE__ ) . 'functions.php';

add_filter( 'wp_redis_check_client_dependencies_callback', 'WP_Predis\check_client_dependencies_callback' );
add_filter( 'wp_redis_client_connection_callback', 'WP_Predis\client_connection_callback' );
add_filter( 'wp_redis_setup_client_connection_callback', 'WP_Predis\setup_client_connection_callback' );
add_filter( 'wp_redis_retry_exception_messages', 'WP_Predis\append_error_messages' );
