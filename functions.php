<?php

namespace WP_Predis;
use Predis\Client as PredisClient;
use Exception;

require_once( dirname( __FILE__ ) . '/class-wp-predis-decorator.php' );

function add_filters( $add_filter_fn = 'add_filter' ) {
	$add_filter_fn( 'wp_redis_check_client_dependencies_callback', 'WP_Predis\check_client_dependencies_callback' );
	$add_filter_fn( 'wp_redis_prepare_client_connection_callback', 'WP_Predis\prepare_client_connection_callback' );
	$add_filter_fn( 'wp_redis_perform_client_connection_callback', 'WP_Predis\perform_client_connection_callback', 10, 3 );
	$add_filter_fn( 'wp_redis_retry_exception_messages', 'WP_Predis\append_error_messages' );
}

function check_client_dependencies() {
	if ( ! class_exists( 'Predis\Client' ) ) {
		return 'Warning! The Predis\Client class is unavailable, which is required by WP Redis Predis.';
	}
	return true;
}

function prepare_client_connection( $args ) {

	// TODO $connection_details['timeout']
	// TODO $connection_details['retry_interval']
	$params = build_params( $args );
	$options = build_options( $args );

	$client = new PredisClient( $params, $options );
	$redis = new Decorator( $client );
	return $redis;
}

function perform_client_connection( $redis, $settings, $keys_methods = array()) {
	try {
		// we ignore $keys_methods because both auth and database selection are
		// handled in Predis/Client instantiation.
		$redis->connect();
	} catch ( Exception $e ) {

		// Predis throws an Exception when it fails a server call.
		// To prevent WordPress from fataling, we catch the Exception.
		// TODO Perhaps we catch and rethrow? or return instance of
		// WP_Error?
		throw new Exception( $e->getMessage(), $e->getCode(), $e );
	}
	return true;
}

function build_params( $args ) {
	$scheme = 'tcp';
	$hostKey = 'host';
	$isTLS = isset( $args['ssl'] ) && is_array( $args['ssl'] );

	if ( $isTLS ) {
		$scheme = 'tls';
	} elseif ( null === $args['port'] ) {
		// If port is null, it's a socket connection
		$scheme = 'unix';
		$hostKey = 'path';
	}

	$params = array(
		'scheme' => $scheme,
		$hostKey => $args['host'],
	);

	if ( 'unix' !== $scheme ) {
		$params['port'] = $args['port'];
	}

	if ( $isTLS ) {
		$params['ssl'] = $args['ssl'];
	}
	return $params;
}

function build_options( $args ) {

	$options = array(
		'exceptions' => true,
		'parameters' => array(
			'password' => isset( $args['auth'] ) ? $args['auth'] : null,
			'database' => isset( $args['database'] ) ? $args['database'] : null,
			'persistent' => isset( $args['persistent'] ) ? $args['persistent'] : null,
		),
	);

	return $options;
}

function append_error_messages( $errors ) {
	return array(
		'/^Connection refused/',
	);
}

function check_client_dependencies_callback() {
	return 'WP_Predis\check_client_dependencies';
}

function prepare_client_connection_callback() {
	return 'WP_Predis\prepare_client_connection';
}

function perform_client_connection_callback() {
	return 'WP_Predis\perform_client_connection';
}
