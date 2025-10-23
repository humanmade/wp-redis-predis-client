<?php

use PHPUnit\Framework\TestCase;

class WPPredisDecoratorTest extends TestCase {

	protected static $arguments;

	protected static $options = array();

	protected $client;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$arguments = array(
			'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
			'port' => getenv('REDIS_PORT') ?: 6379,
		);

		// Add authentication if REDIS_PASSWORD is set
		if ( getenv('REDIS_PASSWORD') ) {
			self::$arguments['password'] = getenv('REDIS_PASSWORD');
		}
	}

	public function setUp(): void {
		parent::setUp();
		$client = new Predis\Client( self::$arguments, self::$options );
		$this->client = new WP_Predis\Decorator( $client );
	}

	public function test_is_connected() {
		$this->client->connect();
		$this->assertTrue( $this->client->isConnected() );
	}

	public function test_hexists() {
		$key = 'test';
		$field = 'foo';

		$exists = $this->client->hexists( $key, $field );
		$this->assertFalse( $exists );
	}

	public function test_exists() {
		$key = 'test';

		$exists = $this->client->exists( $key );
		$this->assertFalse( $exists );
	}

	public function test_get() {
		$key = 'test';

		$value = $this->client->get( $key );
		$this->assertFalse( $value );
	}

	public function test_hget() {
		$key = 'test';
		$field = 'foo';

		$value = $this->client->hget( $key, $field );
		$this->assertFalse( $value );
	}

	public function test_setex() {
		$key = 'foo';
		$value = 'bar';

		$result = $this->client->setex( $key, 100, $value );
		$this->assertTrue( $result );
	}

	public function test_set() {
		$key = 'foo';
		$value = 'bar';

		$result = $this->client->set( $key, $value );
		$this->assertTrue( $result );
	}

	public function test_delete() {
		$this->client->mset( array(
			'foo' => 'bar',
			'bar' => 'baz',
			'baz' => 'qux',
		));

		$deleted = $this->client->del( 'foo', 'bar', 'baz' );
		$this->assertEquals( 3, $deleted );
	}

	public function test_hdelete() {
		$key = 'test';
		$fields = array(
			'foo',
			'bar',
			'baz',
		);

		$this->client->hmset( $key, array(
			'foo' => 'bar',
			'bar' => 'baz',
			'baz' => 'qux',
		));

		$deleted = $this->client->hdel( $key, 'foo', 'bar', 'baz' );
		$this->assertEquals( 3, $deleted );
	}

	public function test_info() {
		$phpredis_info = require( dirname( __FILE__ ) . '/fixtures/phpredis-info.php' );
		$predis_info = require( dirname( __FILE__ ) . '/fixtures/predis-info.php' );
		$actual = $this->client->transform_info( $predis_info );
		$this->assertEquals( array_keys( $phpredis_info ), array_keys( $actual ) );
		$this->assertEquals( $phpredis_info['db0'], $actual['db0'] );
	}

	public function test_close() {
		$this->assertEquals( null, $this->client->close() );
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->client->flushall();
	}
}
