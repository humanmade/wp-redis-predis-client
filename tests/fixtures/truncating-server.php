<?php
/**
 * Fake Redis server for SafeStreamTest.
 *
 * Usage: php truncating-server.php <port> <truncate|complete>
 *
 * Answers every RESP command with a 100,000-byte bulk string. In "truncate"
 * mode it sends only the first 1,000 bytes of the first reply and then closes
 * the connection, like a server dropping a client mid-reply.
 */

$port = (int) $argv[1];
$mode = $argv[2];

$server = stream_socket_server( "tcp://127.0.0.1:{$port}", $errno, $errstr );
if ( ! $server ) {
	fwrite( STDERR, "listen failed: $errstr\n" );
	exit( 1 );
}
fwrite( STDOUT, "ready\n" );

$conn = stream_socket_accept( $server, 10 );
$size = 100000;

while ( $conn && ! feof( $conn ) ) {
	$request = fread( $conn, 65536 );
	if ( $request === '' || $request === false ) {
		break;
	}

	// One reply per command (each RESP command starts with '*').
	$commands = max( 1, substr_count( $request, '*' ) );

	if ( $mode === 'truncate' ) {
		fwrite( $conn, '$' . $size . "\r\n" . str_repeat( 'x', 1000 ) );
		break;
	}

	for ( $i = 0; $i < $commands; $i++ ) {
		fwrite( $conn, '$' . $size . "\r\n" . str_repeat( 'x', $size ) . "\r\n" );
	}
}

if ( $conn ) {
	fclose( $conn );
}
