<?php

namespace WP_Predis;
use Predis\Client as PredisClient;

class Decorator {

	// allows the client to be called directly
	public $client;

	// methods to coerce the return value to a boolean
	public $toBool = array(
		'set',
		'setex',
		'exists',
		'hexists',
	);

	// methods to coerce the return value from null to a boolean
	public $nullToBool = array(
		'get',
		'hget',
	);

	// track time spend waiting for redis responses.
	public $time_spent = 0;

	public function __construct( PredisClient $client ) {
		$this->client = $client;
	}

	public function info( $section = null ) {
		$info = $this->client->info();
		return $this->transform_info( $info );
	}

	public function close() {
		return $this->client->disconnect();
	}

	public function transform_info( $info ) {
		// let's pop this off because special formatting is required`
		$keyspace = $info['Keyspace'];
		unset( $info['Keyspace'] );

		$newInfo = array_reduce( $info, function( $carry, $item ) {
			return array_merge( $carry, $item );
		}, array());

		foreach ( $keyspace as $db => $values ) {
			$newInfo[ $db ] = str_replace( '&', ',', http_build_query( $values ) );
		}

		return $newInfo;
	}

	public function __call( $method_name, $args ) {
		// TODO perhaps we wrap this in a try/catch and return false when
		// there's an exception?
		$start = microtime( true );
		$value = call_user_func_array( array( $this->client, $method_name ), $args );
		$this->time_spent += microtime( true ) - $start;
		$returns = $value;

		$lowered = strtolower( $method_name );
		if ( in_array( $lowered, $this->toBool, true ) ) {
			$returns = (bool) $value;
		}

		if ( in_array( $lowered, $this->nullToBool, true ) ) {
			$returns = null === $value ? false : $value;
		}

		return $returns;
	}
}
