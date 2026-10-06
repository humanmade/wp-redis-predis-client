<?php

use PHPUnit\Framework\TestCase;
use Predis\Connection\ConnectionException;

/**
 * Regression test for Predis 3 spinning forever when the server closes the
 * connection part way through a bulk reply.
 */
class SafeStreamTest extends TestCase {

	/** @var resource|null */
	protected $server_process;

	protected function tearDown(): void {
		if ( $this->server_process ) {
			proc_terminate( $this->server_process );
			proc_close( $this->server_process );
			$this->server_process = null;
		}
		parent::tearDown();
	}

	/**
	 * Start the fake server fixture and return the port it listens on.
	 */
	protected function start_server( string $mode ): int {
		$port = random_int( 20000, 40000 );
		$cmd = array( PHP_BINARY, __DIR__ . '/fixtures/truncating-server.php', (string) $port, $mode );
		$this->server_process = proc_open( $cmd, array( 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ), $pipes );
		$this->assertSame( "ready\n", fgets( $pipes[1] ), 'Fake server did not start' );
		return $port;
	}

	protected function create_client( int $port ) {
		return WP_Predis\prepare_client_connection(
			array(
				'host' => '127.0.0.1',
				'port' => $port,
				'persistent' => false,
			)
		);
	}

	public function test_connection_closed_mid_reply_throws() {
		$port = $this->start_server( 'truncate' );
		$client = $this->create_client( $port );

		// Without the fix this call never returns. Fail instead of hanging the suite.
		set_time_limit( 10 );

		try {
			$this->expectException( ConnectionException::class );
			$this->expectExceptionMessage( 'Error while reading bytes from the server.' );
			$client->get( 'foo' );
		} finally {
			set_time_limit( 0 );
		}
	}

	public function test_complete_reply_is_returned() {
		$port = $this->start_server( 'complete' );
		$client = $this->create_client( $port );

		$this->assertSame( str_repeat( 'x', 100000 ), $client->get( 'foo' ) );
	}

	public function test_client_uses_safe_connection_factory() {
		$options = WP_Predis\build_options( array() );
		$client = new Predis\Client( 'tcp://127.0.0.1:6379', $options );

		$this->assertInstanceOf( WP_Predis\Safe_Connection_Factory::class, $client->getOptions()->connections );
	}
}
