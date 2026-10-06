<?php

namespace WP_Predis;

use Predis\Connection\Resource\Stream;
use RuntimeException;

/**
 * A Predis stream that treats an empty read at EOF as a closed connection.
 *
 * Predis 3's Stream::read() only treats `false` from fread() as an error, but
 * PHP returns '' once a stream has reached EOF. If the server closes the
 * connection part way through a bulk reply, StreamConnection::readByChunks()
 * then loops forever at 100% CPU. Throwing here lets readByChunks() turn it
 * into a ConnectionException, as Predis 1.x and 2.x did.
 */
class Safe_Stream extends Stream {
	public function read( int $length ): string {
		$string = parent::read( $length );

		if ( $string === '' && $length !== 0 && $this->eof() ) {
			throw new RuntimeException( 'Connection closed by peer during read', 1 );
		}

		return $string;
	}
}
