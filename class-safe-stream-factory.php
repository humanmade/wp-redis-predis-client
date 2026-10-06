<?php

namespace WP_Predis;

use Predis\Connection\ParametersInterface;
use Predis\Connection\Resource\StreamFactory;
use Psr\Http\Message\StreamInterface;

/**
 * Stream factory that returns Safe_Stream instances.
 *
 * Mirrors StreamFactory::createStream(), reusing its socket initializers.
 */
class Safe_Stream_Factory extends StreamFactory {
	public function createStream( ParametersInterface $parameters ): StreamInterface {
		$parameters = $this->assertParameters( $parameters );

		switch ( $parameters->scheme ) {
			case 'tcp':
			case 'redis':
				return new Safe_Stream( $this->tcpStreamInitializer( $parameters ) );

			case 'unix':
				return new Safe_Stream( $this->unixStreamInitializer( $parameters ) );

			case 'tls':
			case 'rediss':
				return new Safe_Stream( $this->tlsStreamInitializer( $parameters ) );

			default:
				return parent::createStream( $parameters );
		}
	}
}
