<?php
/**
 * Local WordPress test suite configuration.
 *
 * Generated for Cookie Consent Manager to keep PHPUnit runs self-contained.
 */

// Database settings.
define( 'DB_NAME', 'local_tests' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', 'localhost:/Users/david_atlarge/Library/Application Support/Local/run/7ycJ_xFru/mysql/mysqld.sock' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Cookie Consent Manager Tests' );

// Path to the WordPress code that the tests should bootstrap.
define( 'ABSPATH', dirname( __DIR__ ) . '/wordpress/' );

define( 'WP_DEBUG', true );

// Disable multisite during the default test runs.
define( 'WP_TESTS_MULTISITE', false );

// Fixes "Error: `$_SERVER['HTTP_HOST']` not set."
$_SERVER['HTTP_HOST'] = 'example.org';

// Allow tests to override the PHP binary path if needed.
if ( ! defined( 'WP_PHP_BINARY' ) ) {
	define( 'WP_PHP_BINARY', PHP_BINARY );
}

