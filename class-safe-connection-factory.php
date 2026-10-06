<?php

namespace WP_Predis;

use Predis\Connection\Factory;
use Predis\Connection\ParametersInterface;
use Predis\Connection\StreamConnection;

/**
 * Connection factory that creates stream connections backed by Safe_Stream.
 *
 * Predis' default factory creates StreamConnection without a way to pass a
 * stream factory, so stream schemes are built here instead. prepareConnection()
 * still runs, so AUTH, SELECT and the HELLO handshake are unchanged.
 */
class Safe_Connection_Factory extends Factory {
	const STREAM_SCHEMES = array( 'tcp', 'redis', 'unix', 'tls', 'rediss' );

	public function create( $parameters ) {
		if ( ! $parameters instanceof ParametersInterface ) {
			$parameters = $this->createParameters( $parameters );
		}

		if ( ! in_array( $parameters->scheme, self::STREAM_SCHEMES, true ) ) {
			return parent::create( $parameters );
		}

		$connection = new StreamConnection( $parameters, new Safe_Stream_Factory() );
		$this->prepareConnection( $connection );

		return $connection;
	}
}
