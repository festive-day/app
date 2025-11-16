<?php
/**
 * Database Query Optimization Script
 * 
 * Analyzes key database queries using EXPLAIN to identify optimization opportunities
 * 
 * Usage: wp eval-file optimize-queries.php
 * Or: php optimize-queries.php (requires wp-load.php)
 */

// Load WordPress if running standalone
if ( ! defined( 'ABSPATH' ) ) {
    require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';
}

global $wpdb;

echo "==========================================\n";
echo "Database Query Optimization Analysis\n";
echo "==========================================\n\n";

$categories_table = $wpdb->prefix . 'cookie_consent_categories';
$cookies_table    = $wpdb->prefix . 'cookie_consent_cookies';
$events_table    = $wpdb->prefix . 'cookie_consent_events';

// Query 1: Categories list (from class-cookie-manager.php:161)
echo "Query 1: Categories List (Ordered by display_order)\n";
echo str_repeat( '-', 50 ) . "\n";
$query1 = "SELECT * FROM {$categories_table} ORDER BY display_order ASC";
echo "SQL: {$query1}\n\n";
$explain1 = $wpdb->get_results( "EXPLAIN {$query1}", ARRAY_A );
display_explain_results( $explain1 );
echo "\n";

// Query 2: Cookies by category (from class-cookie-manager.php:168-174)
echo "Query 2: Cookies by Category\n";
echo str_repeat( '-', 50 ) . "\n";
// Get first category ID for testing
$category_id = $wpdb->get_var( "SELECT id FROM {$categories_table} LIMIT 1" );
if ( $category_id ) {
    $query2 = $wpdb->prepare(
        "SELECT name, provider, purpose, expiration FROM {$cookies_table} WHERE category_id = %d",
        $category_id
    );
    echo "SQL: {$query2}\n\n";
    $explain2 = $wpdb->get_results( "EXPLAIN {$query2}", ARRAY_A );
    display_explain_results( $explain2 );
} else {
    echo "No categories found - skipping test\n";
}
echo "\n";

// Query 3: Categories with cookie count (from class-admin-interface.php:239-245)
echo "Query 3: Categories with Cookie Count (JOIN)\n";
echo str_repeat( '-', 50 ) . "\n";
$query3 = "SELECT c.*, COUNT(ck.id) as cookie_count
           FROM {$categories_table} c
           LEFT JOIN {$cookies_table} ck ON c.id = ck.category_id
           GROUP BY c.id
           ORDER BY c.display_order ASC";
echo "SQL: {$query3}\n\n";
$explain3 = $wpdb->get_results( "EXPLAIN {$query3}", ARRAY_A );
display_explain_results( $explain3 );
echo "\n";

// Query 4: Cookies list with category name (from class-admin-interface.php:541-549)
echo "Query 4: Cookies List with Category Name (JOIN)\n";
echo str_repeat( '-', 50 ) . "\n";
$query4 = "SELECT c.*, cat.name as category_name 
           FROM {$cookies_table} c
           LEFT JOIN {$categories_table} cat ON c.category_id = cat.id
           ORDER BY c.created_at DESC
           LIMIT 20";
echo "SQL: {$query4}\n\n";
$explain4 = $wpdb->get_results( "EXPLAIN {$query4}", ARRAY_A );
display_explain_results( $explain4 );
echo "\n";

// Query 5: Audit log with filters (from class-admin-interface.php:933-936)
echo "Query 5: Audit Log with Timestamp Filter\n";
echo str_repeat( '-', 50 ) . "\n";
$query5 = "SELECT * FROM {$events_table} 
           WHERE event_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
           ORDER BY event_timestamp DESC 
           LIMIT 50";
echo "SQL: {$query5}\n\n";
$explain5 = $wpdb->get_results( "EXPLAIN {$query5}", ARRAY_A );
display_explain_results( $explain5 );
echo "\n";

// Query 6: Retention cleanup query (from class-cookie-manager.php:336-338)
echo "Query 6: Retention Cleanup (DELETE with timestamp)\n";
echo str_repeat( '-', 50 ) . "\n";
$query6 = "SELECT COUNT(*) FROM {$events_table} 
           WHERE event_timestamp < DATE_SUB(NOW(), INTERVAL 3 YEAR)";
echo "SQL: {$query6}\n\n";
$explain6 = $wpdb->get_results( "EXPLAIN {$query6}", ARRAY_A );
display_explain_results( $explain6 );
echo "\n";

// Summary and recommendations
echo "==========================================\n";
echo "Optimization Recommendations\n";
echo "==========================================\n\n";

echo "Key Indexes Required (per data-model.md):\n";
echo "- idx_slug (categories table)\n";
echo "- idx_display_order (categories table)\n";
echo "- idx_category (cookies table)\n";
echo "- idx_visitor (events table)\n";
echo "- idx_timestamp (events table)\n";
echo "- idx_event_type (events table)\n\n";

echo "Performance Tips:\n";
echo "1. Ensure all WHERE clauses use indexed columns\n";
echo "2. JOIN operations should use indexed foreign keys\n";
echo "3. ORDER BY should use indexed columns when possible\n";
echo "4. Consider composite indexes for common query patterns\n";
echo "5. Monitor query performance as data volume grows\n\n";

/**
 * Display EXPLAIN results in readable format
 *
 * @param array $results EXPLAIN query results
 */
function display_explain_results( $results ) {
    if ( empty( $results ) ) {
        echo "No results returned\n";
        return;
    }

    // Table header
    $headers = array_keys( $results[0] );
    printf( "%-15s", 'Field' );
    foreach ( $headers as $header ) {
        printf( "%-20s", $header );
    }
    echo "\n" . str_repeat( '-', 15 + ( count( $headers ) * 20 ) ) . "\n";

    // Table rows
    foreach ( $results as $row ) {
        printf( "%-15s", 'Row' );
        foreach ( $row as $value ) {
            printf( "%-20s", $value );
        }
        echo "\n";
    }

    // Analysis
    echo "\nAnalysis:\n";
    foreach ( $results as $row ) {
        $type = isset( $row['type'] ) ? strtoupper( $row['type'] ) : '';
        $key  = isset( $row['key'] ) ? $row['key'] : '';
        $rows = isset( $row['rows'] ) ? $row['rows'] : '';

        if ( 'ALL' === $type ) {
            echo "  ⚠️  Full table scan detected - consider adding index\n";
        } elseif ( empty( $key ) && 'CONST' !== $type && 'SYSTEM' !== $type ) {
            echo "  ⚠️  No index used - check if index exists\n";
        } else {
            echo "  ✓ Index used: " . ( $key ? $key : 'N/A' ) . "\n";
        }

        if ( $rows > 1000 ) {
            echo "  ⚠️  Large number of rows examined: {$rows}\n";
        }
    }
}

