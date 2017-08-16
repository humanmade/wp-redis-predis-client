<?php

class FunctionsTest extends PHPUnit_Framework_TestCase {

	protected $connection_details = array(
		'host' => '127.0.0.1',
		'port' => 6379,
		'timeout' => 1000,
		'retry_interval' => 100,
	);

	public function test_dependencies() {
		$result = wp_redis_client_check_dependencies();
		if ( class_exists( 'Predis\Client' ) ) {
			$this->assertTrue( $result );
		} else {
			$this->assertTrue( is_string( $result ) );
		}
	}

	public function test_socket_params() {
		$socket_path = '/path/to/redis.sock';
		$args = array(
			'host' => $socket_path,
			'port' => null,
		);
		$expected = array(
			'scheme' => 'unix',
			'path' => $socket_path,
		);
		$actual = wp_predis_build_params( $args );
		$this->assertTrue( arrays_are_similar( $expected, $actual ) );
	}

	public function test_tls_params() {
		$host = '127.0.0.1';
		$ssl = array(
			'cafile' => 'private.pem',
			'verify_peer' => true,
		);
		$args = array(
			'host' => $host,
			'port' => 6379,
			'ssl' => $ssl,
		);
		$expected = array(
			'scheme' => 'tls',
			'host' => $host,
			'port' => 6379,
			'ssl' => $ssl,
		);
		$actual = wp_predis_build_params( $args );
		$this->assertTrue( arrays_are_similar( $expected, $actual ) );
	}

	public function test_redis_client_connection() {
		$redis = wp_redis_client_connection( $this->connection_details );
		$redis->connect();
		$this->assertTrue( $redis->isConnected() );
	}

	public function test_setup_connection() {
		$redis = wp_redis_client_connection( $this->connection_details );
		$isSetUp = wp_redis_client_setup_connection( $redis, array(), array(
			// we test the 'exists' function gets called and we pass
			// $this->connection_details['host'] as the argument. We only care
			// about if calling 'exists' throws an exception or not
			'exists' => 'host',
		) );
		$this->assertTrue( $isSetUp );
	}

	// we test that 'database' gets passed by setting up 2 connections, one to
	// the default and the other to a different database and modifying the same
	// key and testing that they're different
	public function test_setup_second_database() {
		$redis = wp_redis_client_connection( $this->connection_details );
		$second_db = array_merge( $this->connection_details, array(
			'database' => 2,
		) );
		$redis2 = wp_redis_client_connection( $second_db );
		$keys_methods = array(
			'database' => 'select',
		);
		wp_redis_client_setup_connection( $redis, $this->connection_details, $keys_methods );
		wp_redis_client_setup_connection( $redis2, $second_db, $keys_methods );
		$redis->set( 'foo', 'bar' );
		$redis2->set( 'foo', 'baz' );
		$this->assertEquals( $redis->get( 'foo' ), 'bar' );
		$this->assertEquals( $redis2->get( 'foo' ), 'baz' );
		$redis->del( 'foo' );
		$redis2->del( 'foo' );
	}

	// we test that auth gets passed by the fact that auth will fail with a bad
	// password
	public function test_setup_connection_auth() {
		$auth = array_merge( $this->connection_details, array(
			'auth' => 'thiswillfail',
		) );
		$redis = wp_redis_client_connection( $auth );
		$keys_methods = array(
			'auth' => 'auth',
		);
		$this->setExpectedException( 'Exception' );
		wp_redis_client_setup_connection( $redis, $auth, $keys_methods );
	}
}
