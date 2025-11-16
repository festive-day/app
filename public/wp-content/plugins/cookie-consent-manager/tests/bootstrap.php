<?php
/**
 * PHPUnit Bootstrap File
 *
 * @package Cookie_Consent_Manager
 */

// Load WordPress test environment
$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find $_tests_dir/includes/functions.php\n";
    exit( 1 );
}

// Load PHPUnit Polyfills if available (required by WP test suite).
$project_root = dirname( dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) );
$polyfills_autoload = $project_root . '/tools/phpunit-polyfills/src/phpunitpolyfills-autoload.php';

if ( file_exists( $polyfills_autoload ) && ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $polyfills_autoload );
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load plugin
 */
function _manually_load_plugin() {
    require dirname( dirname( __FILE__ ) ) . '/cookie-consent-manager.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up WordPress test environment
require $_tests_dir . '/includes/bootstrap.php';
