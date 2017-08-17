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

	public function __construct( PredisClient $client ) {
		$this->client = $client;
	}

	protected function __call( $method_name, $args ) {
		// TODO perhaps we wrap this in a try/catch and return false when
		// there's an exception?
		$value = call_user_func_array( array( $this->client, $method_name ), $args );
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
