<?php
/**
 * Database Index Verification Script
 * 
 * Verifies that all required indexes exist per data-model.md specifications
 * 
 * Usage: wp eval-file verify-indexes.php
 * Or: php verify-indexes.php (requires wp-load.php)
 */

// Load WordPress if running standalone
if ( ! defined( 'ABSPATH' ) ) {
    require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';
}

global $wpdb;

echo "==========================================\n";
echo "Database Index Verification\n";
echo "==========================================\n\n";

$categories_table = $wpdb->prefix . 'cookie_consent_categories';
$cookies_table    = $wpdb->prefix . 'cookie_consent_cookies';
$events_table    = $wpdb->prefix . 'cookie_consent_events';

// Required indexes per data-model.md
$required_indexes = array(
    'categories' => array(
        'idx_slug'          => array( 'table' => $categories_table, 'column' => 'slug' ),
        'idx_display_order' => array( 'table' => $categories_table, 'column' => 'display_order' ),
    ),
    'cookies' => array(
        'idx_category' => array( 'table' => $cookies_table, 'column' => 'category_id' ),
        'idx_name'     => array( 'table' => $cookies_table, 'column' => 'name' ),
    ),
    'events' => array(
        'idx_visitor'    => array( 'table' => $events_table, 'column' => 'visitor_id' ),
        'idx_timestamp'  => array( 'table' => $events_table, 'column' => 'event_timestamp' ),
        'idx_event_type' => array( 'table' => $events_table, 'column' => 'event_type' ),
    ),
);

$all_passed = true;

// Check each table
foreach ( $required_indexes as $table_type => $indexes ) {
    echo "Table: {$table_type}\n";
    echo str_repeat( '-', 50 ) . "\n";

    // Get all indexes for this table
    $table_name = '';
    switch ( $table_type ) {
        case 'categories':
            $table_name = $categories_table;
            break;
        case 'cookies':
            $table_name = $cookies_table;
            break;
        case 'events':
            $table_name = $events_table;
            break;
    }

    // Check if table exists
    $table_exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM information_schema.tables 
         WHERE table_schema = %s AND table_name = %s",
        DB_NAME,
        $table_name
    ) );

    if ( ! $table_exists ) {
        echo "  ⚠️  Table {$table_name} does not exist\n\n";
        $all_passed = false;
        continue;
    }

    // Get indexes for this table
    $indexes_query = $wpdb->prepare(
        "SELECT INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
         ORDER BY INDEX_NAME, SEQ_IN_INDEX",
        DB_NAME,
        $table_name
    );
    $existing_indexes = $wpdb->get_results( $indexes_query, ARRAY_A );

    // Build index map (index_name => columns[])
    $index_map = array();
    foreach ( $existing_indexes as $idx ) {
        $idx_name = $idx['INDEX_NAME'];
        if ( ! isset( $index_map[ $idx_name ] ) ) {
            $index_map[ $idx_name ] = array();
        }
        $index_map[ $idx_name ][] = $idx['COLUMN_NAME'];
    }

    // Check each required index
    foreach ( $indexes as $index_name => $index_info ) {
        $column = $index_info['column'];
        $found  = false;

        // Check if index exists
        foreach ( $index_map as $existing_idx_name => $columns ) {
            // Match by name or by column
            if ( $existing_idx_name === $index_name || in_array( $column, $columns, true ) ) {
                // Verify it's on the correct column
                if ( in_array( $column, $columns, true ) ) {
                    echo "  ✓ {$index_name} on {$column}\n";
                    $found = true;
                    break;
                }
            }
        }

        if ( ! $found ) {
            echo "  ✗ {$index_name} on {$column} - MISSING\n";
            $all_passed = false;
        }
    }

    // Show all indexes (including primary key and foreign keys)
    echo "\n  All indexes on {$table_name}:\n";
    if ( empty( $index_map ) ) {
        echo "    (none found)\n";
    } else {
        foreach ( $index_map as $idx_name => $columns ) {
            echo "    - {$idx_name}: " . implode( ', ', $columns ) . "\n";
        }
    }

    echo "\n";
}

// Summary
echo "==========================================\n";
echo "Summary\n";
echo "==========================================\n\n";

if ( $all_passed ) {
    echo "✓ All required indexes are present!\n\n";
} else {
    echo "✗ Some required indexes are missing.\n";
    echo "Please review the schema.sql and migration files.\n\n";
}

// Show SQL to create missing indexes (if any)
echo "To create missing indexes, use:\n";
echo "ALTER TABLE {$categories_table} ADD INDEX idx_slug (slug);\n";
echo "ALTER TABLE {$categories_table} ADD INDEX idx_display_order (display_order);\n";
echo "ALTER TABLE {$cookies_table} ADD INDEX idx_category (category_id);\n";
echo "ALTER TABLE {$cookies_table} ADD INDEX idx_name (name);\n";
echo "ALTER TABLE {$events_table} ADD INDEX idx_visitor (visitor_id);\n";
echo "ALTER TABLE {$events_table} ADD INDEX idx_timestamp (event_timestamp);\n";
echo "ALTER TABLE {$events_table} ADD INDEX idx_event_type (event_type);\n\n";

exit( $all_passed ? 0 : 1 );

