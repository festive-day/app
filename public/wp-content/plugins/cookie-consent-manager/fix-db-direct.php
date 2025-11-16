<?php
/**
 * Direct SQL execution - Creates missing tables
 */

require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
global $wpdb;

echo "=== Direct SQL Execution ===\n\n";

// Create categories table
$sql1 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cookie_consent_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_required TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

echo "Creating categories table...\n";
$result1 = $wpdb->query( $sql1 );
if ( $result1 === false ) {
    echo "✗ Error: " . $wpdb->last_error . "\n\n";
} else {
    echo "✓ Categories table created\n\n";
}

// Create cookies table
$sql2 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cookie_consent_cookies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(255),
    purpose TEXT,
    expiration VARCHAR(100),
    domain VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES {$wpdb->prefix}cookie_consent_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

echo "Creating cookies table...\n";
$result2 = $wpdb->query( $sql2 );
if ( $result2 === false ) {
    echo "✗ Error: " . $wpdb->last_error . "\n\n";
} else {
    echo "✓ Cookies table created\n\n";
}

// Insert default categories
echo "Inserting default categories...\n";

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
    $result = $wpdb->insert( $wpdb->prefix . 'cookie_consent_categories', $category );
    if ( $result ) {
        echo "✓ Inserted: {$category['name']}\n";
    } else {
        echo "✗ Failed: {$category['name']} - " . $wpdb->last_error . "\n";
    }
}

echo "\n=== Verification ===\n\n";

// Check tables
$tables = array( 'cookie_consent_categories', 'cookie_consent_cookies', 'cookie_consent_events' );
foreach ( $tables as $table ) {
    $table_name = $wpdb->prefix . $table;
    $exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name;
    $count = $exists ? $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ) : 0;
    echo $exists ? "✓ {$table}: EXISTS ({$count} rows)\n" : "✗ {$table}: MISSING\n";
}

// List categories
echo "\nCategories:\n";
$cats = $wpdb->get_results( "SELECT slug, name, is_required FROM {$wpdb->prefix}cookie_consent_categories ORDER BY display_order" );
foreach ( $cats as $cat ) {
    $req = $cat->is_required ? '(Required)' : '';
    echo "  - {$cat->name} ({$cat->slug}) {$req}\n";
}

echo "\n=== Complete ===\n";
