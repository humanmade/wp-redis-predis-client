<?php
WP_Predis\add_filters();

// Immediately register shutdown function, to ensure we catch all errors, even
// before WP registers its own shutdown function.
register_shutdown_function( 'WP_Predis\shutdown' );

require_once dirname( __FILE__ ) . '/plugins/wp-redis/object-cache.php';
