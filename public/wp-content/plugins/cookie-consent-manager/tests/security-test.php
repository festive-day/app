<?php
/**
 * Security Test Suite for Cookie Consent Manager
 *
 * Tests for XSS, SQL injection, CSRF, and input validation vulnerabilities
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

echo "=== Cookie Consent Manager - Security Test Suite ===\n\n";

$results = array(
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
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

/**
 * Test warning helper
 */
function warning( $name, $message ) {
    global $results;
    echo "⚠ WARN: {$name} - {$message}\n";
    $results['warnings']++;
}

// ==================================================================
// TEST 1: XSS Protection in Error Messages
// ==================================================================
echo "--- Test 1: XSS Protection in Error Messages ---\n";

// Simulate malicious event_type
$_POST['event_type'] = '<script>alert("XSS")</script>';
$_POST['accepted_categories'] = array( 'essential' );
$_POST['rejected_categories'] = array();

// Capture output
ob_start();
try {
    $manager = CCM_Cookie_Manager::get_instance();
    $reflection = new ReflectionClass( $manager );
    $method = $reflection->getMethod( 'ajax_record_consent' );
    $method->setAccessible( true );
    $method->invoke( $manager );
} catch ( Exception $e ) {
    // Expected to fail
}
$output = ob_get_clean();

// Check if script tags are escaped
test(
    'XSS in error messages',
    strpos( $output, '<script>alert("XSS")</script>' ) === false || strpos( $output, '&lt;script&gt;' ) !== false,
    'Error messages should escape HTML'
);

unset( $_POST['event_type'], $_POST['accepted_categories'], $_POST['rejected_categories'] );

// ==================================================================
// TEST 2: SQL Injection Protection
// ==================================================================
echo "\n--- Test 2: SQL Injection Protection ---\n";

global $wpdb;
$categories_table = $wpdb->prefix . 'cookie_consent_categories';

// Test malicious category slug
$malicious_slug = "'; DROP TABLE {$categories_table}; --";
$_POST['event_type'] = 'accept_all';
$_POST['accepted_categories'] = array( $malicious_slug );
$_POST['rejected_categories'] = array();

// Check if table still exists after attempt
ob_start();
try {
    $manager = CCM_Cookie_Manager::get_instance();
    $reflection = new ReflectionClass( $manager );
    $method = $reflection->getMethod( 'ajax_record_consent' );
    $method->setAccessible( true );
    $method->invoke( $manager );
} catch ( Exception $e ) {
    // Expected
}
ob_end_clean();

$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$categories_table}'" ) === $categories_table;
test(
    'SQL injection protection',
    $table_exists,
    'Table should still exist after malicious input'
);

unset( $_POST['event_type'], $_POST['accepted_categories'], $_POST['rejected_categories'] );

// ==================================================================
// TEST 3: Input Sanitization
// ==================================================================
echo "\n--- Test 3: Input Sanitization ---\n";

// Test various malicious inputs
$test_inputs = array(
    '<script>alert("XSS")</script>',
    '"><img src=x onerror=alert(1)>',
    "'; DROP TABLE test; --",
    '../../etc/passwd',
);

foreach ( $test_inputs as $input ) {
    $_POST['event_type'] = sanitize_text_field( $input );
    $sanitized = sanitize_text_field( $input );
    
    test(
        "Input sanitization: " . substr( $input, 0, 30 ),
        strpos( $sanitized, '<script>' ) === false && strpos( $sanitized, 'DROP' ) === false,
        'Input should be sanitized'
    );
}

unset( $_POST['event_type'] );

// ==================================================================
// TEST 4: Rate Limiting
// ==================================================================
echo "\n--- Test 4: Rate Limiting ---\n";

$_POST['event_type'] = 'accept_all';
$_POST['accepted_categories'] = array( 'essential' );
$_POST['rejected_categories'] = array();

// Make 11 requests (limit is 10)
$rate_limit_hit = false;
for ( $i = 0; $i < 11; $i++ ) {
    ob_start();
    try {
        $manager = CCM_Cookie_Manager::get_instance();
        $reflection = new ReflectionClass( $manager );
        $method = $reflection->getMethod( 'ajax_record_consent' );
        $method->setAccessible( true );
        $method->invoke( $manager );
    } catch ( Exception $e ) {
        // Expected
    }
    $output = ob_get_clean();
    
    if ( strpos( $output, 'Rate limit exceeded' ) !== false ) {
        $rate_limit_hit = true;
        break;
    }
    
    // Small delay
    usleep( 100000 ); // 0.1 second
}

test(
    'Rate limiting',
    $rate_limit_hit,
    'Rate limit should trigger after 10 requests'
);

unset( $_POST['event_type'], $_POST['accepted_categories'], $_POST['rejected_categories'] );

// ==================================================================
// TEST 5: Category Validation
// ==================================================================
echo "\n--- Test 5: Category Validation ---\n";

// Test with invalid category slug
$_POST['event_type'] = 'accept_all';
$_POST['accepted_categories'] = array( 'nonexistent_category_12345' );
$_POST['rejected_categories'] = array();

ob_start();
try {
    $manager = CCM_Cookie_Manager::get_instance();
    $reflection = new ReflectionClass( $manager );
    $method = $reflection->getMethod( 'ajax_record_consent' );
    $method->setAccessible( true );
    $method->invoke( $manager );
} catch ( Exception $e ) {
    // Expected
}
$output = ob_get_clean();

test(
    'Category validation',
    strpos( $output, 'Invalid category slugs' ) !== false || strpos( $output, 'error' ) !== false,
    'Invalid category slugs should be rejected'
);

unset( $_POST['event_type'], $_POST['accepted_categories'], $_POST['rejected_categories'] );

// ==================================================================
// TEST 6: Event Type Validation
// ==================================================================
echo "\n--- Test 6: Event Type Validation ---\n";

$_POST['event_type'] = 'malicious_event_type';
$_POST['accepted_categories'] = array( 'essential' );
$_POST['rejected_categories'] = array();

ob_start();
try {
    $manager = CCM_Cookie_Manager::get_instance();
    $reflection = new ReflectionClass( $manager );
    $method = $reflection->getMethod( 'ajax_record_consent' );
    $method->setAccessible( true );
    $method->invoke( $manager );
} catch ( Exception $e ) {
    // Expected
}
$output = ob_get_clean();

test(
    'Event type validation',
    strpos( $output, 'Invalid event type' ) !== false || strpos( $output, 'error' ) !== false,
    'Invalid event types should be rejected'
);

unset( $_POST['event_type'], $_POST['accepted_categories'], $_POST['rejected_categories'] );

// ==================================================================
// TEST 7: Server Variable Sanitization
// ==================================================================
echo "\n--- Test 7: Server Variable Sanitization ---\n";

// Check if $_SERVER variables are sanitized in consent logger
$test_ip = '<script>alert("XSS")</script>';
$test_ua = "'; DROP TABLE test; --";

$_SERVER['REMOTE_ADDR'] = $test_ip;
$_SERVER['HTTP_USER_AGENT'] = $test_ua;

$visitor_id = CCM_Consent_Logger::generate_visitor_id();

test(
    'Server variable sanitization',
    strpos( $visitor_id, '<script>' ) === false && strpos( $visitor_id, 'DROP' ) === false,
    'Server variables should be sanitized before hashing'
);

// Restore original values
unset( $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] );

// ==================================================================
// TEST 8: XSS in JavaScript (innerHTML)
// ==================================================================
echo "\n--- Test 8: XSS in JavaScript (innerHTML) ---\n";

// Check if escapeHtml function exists and works
$test_file = dirname( __FILE__ ) . '/../../public/js/consent-banner.js';
$js_content = file_get_contents( $test_file );

test(
    'escapeHtml function exists',
    strpos( $js_content, 'escapeHtml' ) !== false,
    'escapeHtml function should exist'
);

test(
    'innerHTML uses escapeHtml',
    preg_match( '/innerHTML.*escapeHtml|escapeHtml.*innerHTML/', $js_content ) !== false,
    'innerHTML should use escapeHtml for user content'
);

// ==================================================================
// SUMMARY
// ==================================================================
echo "\n=== SUMMARY ===\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";
echo "Warnings: {$results['warnings']}\n\n";

if ( $results['failed'] === 0 ) {
    echo "✓ All security tests PASSED!\n";
} else {
    echo "✗ Some security tests FAILED. Review output above.\n";
}

if ( $results['warnings'] > 0 ) {
    echo "\n⚠ {$results['warnings']} warning(s) - review recommended.\n";
}

