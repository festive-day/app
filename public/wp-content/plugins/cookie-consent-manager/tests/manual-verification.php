<?php
/**
 * Manual Verification Script for Phase 2
 * Run via: wp eval-file tests/manual-verification.php
 *
 * @package Cookie_Consent_Manager
 */

// Load WordPress
require_once __DIR__ . '/../../../../../wp-load.php';

echo "=== Cookie Consent Manager - Phase 2 Verification ===\n\n";

$results = array(
    'passed' => 0,
    'failed' => 0,
);

/**
 * Test helper
 */
function test( $name, $condition, &$results ) {
    if ( $condition ) {
        echo "✓ PASS: {$name}\n";
        $results['passed']++;
        return true;
    } else {
        echo "✗ FAIL: {$name}\n";
        $results['failed']++;
        return false;
    }
}

// Test 1: Database tables exist
echo "--- Database Tables ---\n";
global $wpdb;

$tables = array(
    $wpdb->prefix . 'cookie_consent_categories',
    $wpdb->prefix . 'cookie_consent_cookies',
    $wpdb->prefix . 'cookie_consent_events',
);

foreach ( $tables as $table_name ) {
    $table = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
    test( "Table exists: {$table_name}", $table === $table_name, $results );
}

// Test 2: Default categories
echo "\n--- Default Categories ---\n";
$categories_table = $wpdb->prefix . 'cookie_consent_categories';
$count            = $wpdb->get_var( "SELECT COUNT(*) FROM {$categories_table}" );
test( '4 default categories exist', $count == 4, $results );

$categories = $wpdb->get_results( "SELECT slug, name, is_required FROM {$categories_table} ORDER BY display_order" );
$expected   = array(
    array( 'essential', 'Essential', 1 ),
    array( 'functional', 'Functional', 0 ),
    array( 'analytics', 'Analytics', 0 ),
    array( 'marketing', 'Marketing', 0 ),
);

foreach ( $categories as $i => $cat ) {
    test(
        "Category {$expected[$i][1]} correct",
        $cat->slug === $expected[ $i ][0] && $cat->name === $expected[ $i ][1] && $cat->is_required == $expected[ $i ][2],
        $results
    );
}

// Test 3: Core classes loaded
echo "\n--- Core Classes ---\n";
test( 'CCM_Cookie_Manager class exists', class_exists( 'CCM_Cookie_Manager' ), $results );
test( 'CCM_Consent_Logger class exists', class_exists( 'CCM_Consent_Logger' ), $results );
test( 'CCM_Storage_Handler class exists', class_exists( 'CCM_Storage_Handler' ), $results );
test( 'CCM_Cookie_Blocker class exists', class_exists( 'CCM_Cookie_Blocker' ), $results );
test( 'CCM_Admin_Interface class exists', class_exists( 'CCM_Admin_Interface' ), $results );

// Test 4: Visitor ID generation
echo "\n--- Consent Logger ---\n";
$visitor_id = CCM_Consent_Logger::generate_visitor_id();
test( 'Visitor ID generated', ! empty( $visitor_id ), $results );
test( 'Visitor ID is SHA256 (64 chars)', strlen( $visitor_id ) === 64, $results );
test( 'Visitor ID is hexadecimal', ctype_xdigit( $visitor_id ), $results );

// Test 5: Storage handler
echo "\n--- Storage Handler ---\n";
$consent_data = array( 'acceptedCategories' => array( 'essential', 'analytics' ) );
$hash         = CCM_Storage_Handler::generate_cookie_hash( $consent_data );
test( 'Cookie hash generated', ! empty( $hash ), $results );
test( 'Cookie hash is MD5 (32 chars)', strlen( $hash ) === 32, $results );

$valid_consent = array(
    'version'            => '1.0.0',
    'timestamp'          => time(),
    'consentGiven'       => true,
    'acceptedCategories' => array( 'essential' ),
);
test( 'Valid consent structure passes', CCM_Storage_Handler::validate_consent_structure( $valid_consent ), $results );

$invalid_consent = array( 'version' => '1.0.0' );
test( 'Invalid consent structure fails', ! CCM_Storage_Handler::validate_consent_structure( $invalid_consent ), $results );

// Test 6: Consent expiration
echo "\n--- Expiration Logic ---\n";
$recent_timestamp = time() - ( 6 * 30 * 24 * 60 * 60 ); // 6 months
$old_timestamp    = time() - ( 13 * 30 * 24 * 60 * 60 ); // 13 months
test( '6-month consent not expired', ! CCM_Storage_Handler::is_consent_expired( $recent_timestamp ), $results );
test( '13-month consent expired', CCM_Storage_Handler::is_consent_expired( $old_timestamp ), $results );

// Test 7: Version mismatch
echo "\n--- Version Mismatch ---\n";
test( 'Same version no mismatch', ! CCM_Storage_Handler::has_version_mismatch( CCM_VERSION ), $results );
test( 'Different version has mismatch', CCM_Storage_Handler::has_version_mismatch( '0.9.0' ), $results );

// Test 8: Event logging
echo "\n--- Event Logging ---\n";
$wpdb->query( "DELETE FROM {$wpdb->prefix}cookie_consent_events" ); // Clear test events

$event_id = CCM_Consent_Logger::record_event(
    array(
        'event_type'          => 'accept_all',
        'accepted_categories' => array( 'essential', 'functional', 'analytics', 'marketing' ),
    )
);
test( 'Event recorded successfully', $event_id !== false && $event_id > 0, $results );

$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cookie_consent_events WHERE id = %d", $event_id ) );
test( 'Event has correct type', $event->event_type === 'accept_all', $results );
test( 'Event has visitor_id', ! empty( $event->visitor_id ), $results );
test( 'Event has consent_version', $event->consent_version === CCM_VERSION, $results );

// Test 9: AJAX actions registered
echo "\n--- AJAX Actions ---\n";
global $wp_filter;
test( 'Frontend AJAX: ccm_get_banner_config', isset( $wp_filter['wp_ajax_nopriv_ccm_get_banner_config'] ), $results );
test( 'Frontend AJAX: ccm_record_consent', isset( $wp_filter['wp_ajax_nopriv_ccm_record_consent'] ), $results );
test( 'Frontend AJAX: ccm_check_dnt', isset( $wp_filter['wp_ajax_nopriv_ccm_check_dnt'] ), $results );
test( 'Admin AJAX: ccm_list_categories', isset( $wp_filter['wp_ajax_ccm_list_categories'] ), $results );
test( 'Admin AJAX: ccm_create_category', isset( $wp_filter['wp_ajax_ccm_create_category'] ), $results );

// Test 10: Plugin constants
echo "\n--- Plugin Constants ---\n";
test( 'CCM_VERSION defined', defined( 'CCM_VERSION' ), $results );
test( 'CCM_PLUGIN_DIR defined', defined( 'CCM_PLUGIN_DIR' ), $results );
test( 'CCM_PLUGIN_URL defined', defined( 'CCM_PLUGIN_URL' ), $results );
test( 'Version is 1.0.0', CCM_VERSION === '1.0.0', $results );

// Test 11: Cron job
echo "\n--- Scheduled Tasks ---\n";
$next_cleanup = wp_next_scheduled( 'ccm_cleanup_old_logs' );
test( 'Cleanup cron job scheduled', $next_cleanup !== false, $results );

// Summary
echo "\n=== SUMMARY ===\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";

if ( $results['failed'] === 0 ) {
    echo "\n✓ All Phase 2 tests PASSED!\n";
    exit( 0 );
} else {
    echo "\n✗ Some tests FAILED. Review output above.\n";
    exit( 1 );
}
