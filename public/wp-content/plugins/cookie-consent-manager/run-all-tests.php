#!/usr/bin/env php
<?php
/**
 * Test Runner for Cookie Consent Manager
 * Runs all available tests
 *
 * @package Cookie_Consent_Manager
 */

// Load WordPress
// From plugin root: go up to plugins/ -> wp-content/ -> public/ -> wp-load.php
$wp_load = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
    // Alternative: try from current working directory
    $wp_load = __DIR__ . '/../../../../wp-load.php';
}
require_once $wp_load;

// Set up admin user for CLI execution
if ( php_sapi_name() === 'cli' ) {
    // Create a temporary admin user for CLI testing
    if ( ! is_user_logged_in() ) {
        $admin_user = get_user_by( 'login', 'admin' );
        if ( ! $admin_user ) {
            // Create admin user if it doesn't exist
            $admin_id = wp_create_user( 'admin', 'admin', 'admin@localhost' );
            if ( ! is_wp_error( $admin_id ) ) {
                $admin_user = get_user_by( 'id', $admin_id );
                $admin_user->set_role( 'administrator' );
            }
        }
        if ( $admin_user ) {
            wp_set_current_user( $admin_user->ID );
        }
    }
} else {
    // Only allow admin access for web requests
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Access denied. Admin privileges required.' );
    }
}

header( 'Content-Type: text/plain; charset=utf-8' );

echo "==================================================\n";
echo "Cookie Consent Manager - Complete Test Suite\n";
echo "==================================================\n\n";

$total_results = array(
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
);

// Run functionality tests
echo "--- Running Functionality Tests ---\n";
ob_start();
include dirname( __FILE__ ) . '/tests/functionality-test.php';
$functionality_output = ob_get_clean();
echo $functionality_output;
echo "\n";

// Extract results from functionality test
if ( preg_match( '/Total: (\d+) passed, (\d+) failed/', $functionality_output, $matches ) ) {
    $total_results['passed'] += (int) $matches[1];
    $total_results['failed'] += (int) $matches[2];
}

// Run security tests
echo "\n--- Running Security Tests ---\n";
ob_start();
include dirname( __FILE__ ) . '/tests/security-test.php';
$security_output = ob_get_clean();
echo $security_output;
echo "\n";

// Extract results from security test
if ( preg_match( '/Total: (\d+) passed, (\d+) failed(?:, (\d+) warnings)?/', $security_output, $matches ) ) {
    $total_results['passed'] += (int) $matches[1];
    $total_results['failed'] += (int) $matches[2];
    if ( isset( $matches[3] ) ) {
        $total_results['warnings'] += (int) $matches[3];
    }
}

// Summary
echo "\n==================================================\n";
echo "Test Summary\n";
echo "==================================================\n";
echo "Total Passed: {$total_results['passed']}\n";
echo "Total Failed: {$total_results['failed']}\n";
if ( $total_results['warnings'] > 0 ) {
    echo "Total Warnings: {$total_results['warnings']}\n";
}
echo "\n";

if ( $total_results['failed'] === 0 ) {
    echo "✅ All tests passed!\n";
    exit( 0 );
} else {
    echo "❌ Some tests failed. Please review the output above.\n";
    exit( 1 );
}

