<?php
/**
 * Fix Database Tables - Run this once to create missing tables
 *
 * Access via: /wp-content/plugins/cookie-consent-manager/fix-database.php
 * Or run via: php fix-database.php
 *
 * @package Cookie_Consent_Manager
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

global $wpdb;

echo "=== Cookie Consent Manager - Database Fix ===\n\n";

// Read migration file
$migration_file = __DIR__ . '/database/migrations/001-create-tables-up.sql';

if ( ! file_exists( $migration_file ) ) {
    die( "ERROR: Migration file not found: {$migration_file}\n" );
}

$sql = file_get_contents( $migration_file );

// Replace placeholder with actual prefix
$sql = str_replace( 'wp_', $wpdb->prefix, $sql );

echo "Running migration SQL...\n";
echo "Table prefix: {$wpdb->prefix}\n\n";

// Execute migration using direct SQL (dbDelta is too restrictive)
// Split by semicolon and execute each statement
$statements = array_filter( array_map( 'trim', explode( ';', $sql ) ) );

echo "Executing " . count( $statements ) . " SQL statements...\n";

$executed = 0;
$errors = 0;

foreach ( $statements as $i => $statement ) {
    if ( empty( $statement ) || strpos( $statement, '--' ) === 0 ) {
        continue;
    }

    // Skip comments
    $lines = explode( "\n", $statement );
    $lines = array_filter( $lines, function( $line ) {
        return strpos( trim( $line ), '--' ) !== 0;
    });
    $statement = implode( "\n", $lines );

    if ( empty( trim( $statement ) ) ) {
        continue;
    }

    echo "\nExecuting statement " . ($i + 1) . "...\n";
    echo "Preview: " . substr( str_replace( array( "\n", "\r" ), ' ', $statement ), 0, 80 ) . "...\n";

    $result = $wpdb->query( $statement );

    if ( $result === false ) {
        echo "✗ ERROR: " . $wpdb->last_error . "\n";
        $errors++;
    } else {
        echo "✓ Success\n";
        $executed++;
    }
}

echo "\nMigration complete: {$executed} statements executed, {$errors} errors\n\n";

// Check tables created
$tables = array(
    'cookie_consent_categories',
    'cookie_consent_cookies',
    'cookie_consent_events',
);

echo "Checking tables:\n";
foreach ( $tables as $table ) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name;
    $count = $exists ? $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ) : 0;

    if ( $exists ) {
        echo "✓ {$table}: EXISTS ({$count} rows)\n";
    } else {
        echo "✗ {$table}: MISSING\n";
    }
}

echo "\n";

// Insert default categories
echo "Inserting default categories...\n";

$categories_table = $wpdb->prefix . 'cookie_consent_categories';

// Check if categories already exist
$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$categories_table}" );

if ( $count == 0 ) {
    $categories = array(
        array(
            'slug'          => 'essential',
            'name'          => 'Essential',
            'description'   => 'Required for site functionality. These cookies are necessary for the website to work properly.',
            'is_required'   => 1,
            'display_order' => 10,
        ),
        array(
            'slug'          => 'functional',
            'name'          => 'Functional',
            'description'   => 'Enhance site features and provide personalized content.',
            'is_required'   => 0,
            'display_order' => 20,
        ),
        array(
            'slug'          => 'analytics',
            'name'          => 'Analytics',
            'description'   => 'Help us understand how visitors interact with our website.',
            'is_required'   => 0,
            'display_order' => 30,
        ),
        array(
            'slug'          => 'marketing',
            'name'          => 'Marketing',
            'description'   => 'Used to personalize ads and content based on your interests.',
            'is_required'   => 0,
            'display_order' => 40,
        ),
    );

    foreach ( $categories as $category ) {
        $result = $wpdb->insert( $categories_table, $category );
        if ( $result ) {
            echo "✓ Inserted: {$category['name']}\n";
        } else {
            echo "✗ Failed: {$category['name']}\n";
        }
    }
} else {
    echo "Categories already exist ({$count} found)\n";
}

echo "\n";

// Verify categories
echo "Verifying categories:\n";
$categories = $wpdb->get_results(
    "SELECT slug, name, is_required FROM {$categories_table} ORDER BY display_order"
);

foreach ( $categories as $cat ) {
    $required = $cat->is_required ? '(Required)' : '';
    echo "  - {$cat->name} ({$cat->slug}) {$required}\n";
}

echo "\n=== Database Fix Complete ===\n";
echo "\nNext steps:\n";
echo "1. Visit homepage to see banner: http://speckit-eval.local\n";
echo "2. Check diagnostic page: http://speckit-eval.local/wp-content/plugins/cookie-consent-manager/diagnostic.php\n";
