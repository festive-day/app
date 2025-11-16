<?php
/**
 * Functionality Test Suite for Cookie Consent Manager
 *
 * Tests core functionality: banner display, consent recording, storage, etc.
 *
 * @package Cookie_Consent_Manager
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

// Only allow admin access
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied. Admin privileges required.' );
}

header( 'Content-Type: text/plain; charset=utf-8' );

echo "=== Cookie Consent Manager - Functionality Test Suite ===\n\n";

$results = array(
    'passed' => 0,
    'failed' => 0,
);

/**
 * Test helper function
 */
function test( $name, $condition, $message = '' ) {
    global $results;
    if ( $condition ) {
        echo "✓ PASS: {$name}\n";
        $results['passed']++;
    } else {
        echo "✗ FAIL: {$name}";
        if ( $message ) {
            echo " - {$message}";
        }
        echo "\n";
        $results['failed']++;
    }
}

// ==================================================================
// TEST 1: Database Tables Exist
// ==================================================================
echo "--- Test 1: Database Tables ---\n";

global $wpdb;
$tables = array(
    $wpdb->prefix . 'cookie_consent_categories',
    $wpdb->prefix . 'cookie_consent_cookies',
    $wpdb->prefix . 'cookie_consent_events',
);

foreach ( $tables as $table ) {
    $exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table;
    test( "Table exists: {$table}", $exists );
}

// ==================================================================
// TEST 2: Default Categories Exist
// ==================================================================
echo "\n--- Test 2: Default Categories ---\n";

$categories_table = $wpdb->prefix . 'cookie_consent_categories';
$categories = $wpdb->get_results( "SELECT slug, name, is_required FROM {$categories_table} ORDER BY display_order" );

$expected_categories = array( 'essential', 'functional', 'analytics', 'marketing' );
$found_categories = array();

foreach ( $categories as $cat ) {
    $found_categories[] = $cat->slug;
}

foreach ( $expected_categories as $expected ) {
    test( "Category exists: {$expected}", in_array( $expected, $found_categories, true ) );
}

// Check essential category is required
$essential = $wpdb->get_var( $wpdb->prepare( "SELECT is_required FROM {$categories_table} WHERE slug = %s", 'essential' ) );
test( "Essential category is required", (bool) $essential );

// ==================================================================
// TEST 3: AJAX Endpoints Registered
// ==================================================================
echo "\n--- Test 3: AJAX Endpoints ---\n";

$endpoints = array(
    'ccm_get_banner_config',
    'ccm_record_consent',
    'ccm_check_dnt',
);

foreach ( $endpoints as $endpoint ) {
    $registered = has_action( "wp_ajax_{$endpoint}" ) || has_action( "wp_ajax_nopriv_{$endpoint}" );
    test( "AJAX endpoint registered: {$endpoint}", $registered );
}

// ==================================================================
// TEST 4: Banner Configuration Endpoint
// ==================================================================
echo "\n--- Test 4: Banner Configuration ---\n";

// Simulate AJAX request
$_REQUEST['action'] = 'ccm_get_banner_config';

ob_start();
try {
    $manager = CCM_Cookie_Manager::get_instance();
    $reflection = new ReflectionClass( $manager );
    $method = $reflection->getMethod( 'ajax_get_banner_config' );
    $method->setAccessible( true );
    $method->invoke( $manager );
} catch ( Exception $e ) {
    echo json_encode( array( 'success' => false, 'error' => $e->getMessage() ) );
}
$output = ob_get_clean();

$response = json_decode( $output, true );

test( "Banner config returns JSON", is_array( $response ) );
test( "Banner config has success flag", isset( $response['success'] ) );

if ( isset( $response['data'] ) ) {
    test( "Banner config has categories", isset( $response['data']['categories'] ) );
    test( "Banner config has consent_version", isset( $response['data']['consent_version'] ) );
    test( "Categories array is not empty", ! empty( $response['data']['categories'] ) );
    
    // Check category structure
    if ( ! empty( $response['data']['categories'] ) ) {
        $category = $response['data']['categories'][0];
        test( "Category has slug", isset( $category['slug'] ) );
        test( "Category has name", isset( $category['name'] ) );
        test( "Category has description", isset( $category['description'] ) );
        test( "Category has is_required", isset( $category['is_required'] ) );
        test( "Category has cookies array", isset( $category['cookies'] ) && is_array( $category['cookies'] ) );
    }
}

unset( $_REQUEST['action'] );

// ==================================================================
// TEST 5: Consent Recording
// ==================================================================
echo "\n--- Test 5: Consent Recording ---\n";

// Clear test events
$wpdb->query( "DELETE FROM {$wpdb->prefix}cookie_consent_events WHERE visitor_id LIKE 'test_%'" );

$_POST['event_type'] = 'accept_all';
$_POST['accepted_categories'] = array( 'essential', 'analytics' );
$_POST['rejected_categories'] = array( 'marketing' );
$_POST['consent_version'] = '1.0.0';
$_REQUEST['action'] = 'ccm_record_consent';

ob_start();
try {
    $manager = CCM_Cookie_Manager::get_instance();
    $reflection = new ReflectionClass( $manager );
    $method = $reflection->getMethod( 'ajax_record_consent' );
    $method->setAccessible( true );
    $method->invoke( $manager );
} catch ( Exception $e ) {
    echo json_encode( array( 'success' => false, 'error' => $e->getMessage() ) );
}
$output = ob_get_clean();

$response = json_decode( $output, true );

test( "Consent recording returns JSON", is_array( $response ) );
test( "Consent recording successful", isset( $response['success'] ) && $response['success'] === true );

if ( isset( $response['data']['event_id'] ) ) {
    $event_id = $response['data']['event_id'];
    
    // Verify event was saved
    $event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cookie_consent_events WHERE id = %d", $event_id ) );
    test( "Event saved to database", ! empty( $event ) );
    
    if ( $event ) {
        test( "Event has correct type", $event->event_type === 'accept_all' );
        test( "Event has visitor_id", ! empty( $event->visitor_id ) );
    }
}

unset( $_POST['event_type'], $_POST['accepted_categories'], $_POST['rejected_categories'], $_POST['consent_version'], $_REQUEST['action'] );

// ==================================================================
// TEST 6: Consent Logger Class
// ==================================================================
echo "\n--- Test 6: Consent Logger ---\n";

$visitor_id = CCM_Consent_Logger::generate_visitor_id();
test( "Visitor ID generated", ! empty( $visitor_id ) );
test( "Visitor ID is 64 chars (SHA256)", strlen( $visitor_id ) === 64 );

$event_id = CCM_Consent_Logger::record_event( array(
    'event_type' => 'accept_partial',
    'accepted_categories' => array( 'essential', 'functional' ),
    'rejected_categories' => array( 'analytics', 'marketing' ),
) );

test( "Event recorded via logger", $event_id !== false && $event_id > 0 );

// ==================================================================
// TEST 7: File Structure
// ==================================================================
echo "\n--- Test 7: File Structure ---\n";

$required_files = array(
    'cookie-consent-manager.php',
    'includes/class-cookie-manager.php',
    'includes/class-consent-logger.php',
    'includes/class-cookie-blocker.php',
    'includes/class-storage-handler.php',
    'public/js/consent-banner.js',
    'public/js/cookie-blocker.js',
    'public/js/storage-manager.js',
    'public/css/banner.css',
    'public/templates/banner-template.php',
);

$plugin_dir = dirname( dirname( __FILE__ ) );

foreach ( $required_files as $file ) {
    $path = $plugin_dir . '/' . $file;
    test( "File exists: {$file}", file_exists( $path ) );
}

// ==================================================================
// TEST 8: WordPress Hooks
// ==================================================================
echo "\n--- Test 8: WordPress Hooks ---\n";

test( "wp_enqueue_scripts hook registered", has_action( 'wp_enqueue_scripts' ) );
test( "wp_footer hook registered", has_action( 'wp_footer' ) );
test( "Cron job scheduled", wp_next_scheduled( 'ccm_cleanup_old_logs' ) !== false );

// ==================================================================
// SUMMARY
// ==================================================================
echo "\n=== SUMMARY ===\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n\n";

if ( $results['failed'] === 0 ) {
    echo "✓ All functionality tests PASSED!\n";
} else {
    echo "✗ Some functionality tests FAILED. Review output above.\n";
}

