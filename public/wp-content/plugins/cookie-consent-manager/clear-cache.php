<?php
/**
 * Clear cache and test database query
 */

require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
global $wpdb;

echo "=== Clear Cache & Test ===\n\n";

// Clear transient cache
delete_transient( 'ccm_banner_config' );
echo "✓ Cleared transient cache\n\n";

// Test database query
$categories_table = $wpdb->prefix . 'cookie_consent_categories';
echo "Querying: {$categories_table}\n";

$categories = $wpdb->get_results(
    "SELECT * FROM {$categories_table} ORDER BY display_order ASC"
);

echo "Found " . count( $categories ) . " categories\n\n";

if ( $categories ) {
    foreach ( $categories as $cat ) {
        echo "  - {$cat->name} (ID: {$cat->id}, slug: {$cat->slug})\n";
    }
} else {
    echo "Database error: " . $wpdb->last_error . "\n";
}

echo "\n✓ Complete\n";
