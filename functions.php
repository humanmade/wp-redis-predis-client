<?php
namespace WP_Predis;
use Predis\Client as PredisClient;
use WP_Predis\Decorator;
use Exception;

function check_client_dependencies() {
	if ( ! class_exists( 'Predis\Client' ) ) {
		return 'Warning! The Predis\Client class is unavailable, which is required by WP Redis Predis.';
	}
	return true;
}

function client_connection( $args ) {

	// TODO $connection_details['timeout']
	// TODO $connection_details['retry_interval']
	$params = build_params( $args );
	$options = build_options( $args );

	$client = new PredisClient( $params, $options );
	$redis = new Decorator( $client );
	return $redis;
}

function setup_client_connection( $redis, $settings, $keys_methods = array()) {
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
		),
	);

	return $options;
}
function check_client_dependencies_callback() {
	return 'WP_Predis\check_client_dependencies';
}
function client_connection_callback() {
	return 'WP_Predis\client_connection';
}
function setup_client_connection_callback() {
	return 'WP_Predis\setup_client_connection';
}
