<?php
/**
 * Plugin Name: Cookie Consent Manager
 * Plugin URI: https://example.com/cookie-consent-manager
 * Description: GDPR/CCPA-compliant cookie consent management for WordPress with Etch theme integration
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cookie-consent-manager
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'CCM_VERSION', '1.0.0' );
define( 'CCM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CCM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CCM_PLUGIN_FILE', __FILE__ );

/**
 * Detect if current request is running inside the Etch builder UI.
 *
 * The builder uses a front-end request with ?etch=magic which should never load
 * the cookie banner or blockers (they interfere with builder controls).
 *
 * @return bool
 */
function ccm_is_etch_builder_request() {
    if ( defined( 'CCM_TEST_DISABLE_BUILDER_GUARD' ) && CCM_TEST_DISABLE_BUILDER_GUARD ) {
        return false;
    }

    if ( isset( $_GET['etch'] ) && 'magic' === $_GET['etch'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return true;
    }

    /**
     * Allow overrides (e.g. future Etch flags).
     *
     * Developers can hook this filter if Etch introduces additional query vars.
     */
    return (bool) apply_filters( 'ccm_is_etch_builder_request', false );
}

// Require core class files
require_once CCM_PLUGIN_DIR . 'includes/class-cookie-manager.php';
require_once CCM_PLUGIN_DIR . 'includes/class-consent-logger.php';
require_once CCM_PLUGIN_DIR . 'includes/class-cookie-blocker.php';
require_once CCM_PLUGIN_DIR . 'includes/class-storage-handler.php';
require_once CCM_PLUGIN_DIR . 'includes/class-admin-interface.php';

// Require template files
require_once CCM_PLUGIN_DIR . 'public/templates/banner-template.php';

/**
 * Plugin activation hook
 */
function ccm_activate_plugin() {
    global $wpdb;

    // Run database migrations
    $migration_file = CCM_PLUGIN_DIR . 'database/migrations/001-create-tables-up.sql';
    if ( file_exists( $migration_file ) ) {
        $sql = file_get_contents( $migration_file );

        // Replace placeholder with actual prefix
        $sql = str_replace( 'wp_', $wpdb->prefix, $sql );

        // Execute migration
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    // Insert default categories
    ccm_insert_default_categories();

    // Set plugin version
    update_option( 'ccm_db_version', CCM_VERSION );
    update_option( 'ccm_plugin_version', CCM_VERSION );
}
register_activation_hook( __FILE__, 'ccm_activate_plugin' );

/**
 * Plugin deactivation hook
 */
function ccm_deactivate_plugin() {
    // Remove scheduled cron jobs
    wp_clear_scheduled_hook( 'ccm_cleanup_old_logs' );
}
register_deactivation_hook( __FILE__, 'ccm_deactivate_plugin' );

/**
 * Insert default cookie categories
 */
function ccm_insert_default_categories() {
    global $wpdb;

    $table = $wpdb->prefix . 'cookie_consent_categories';

    // Check if categories already exist
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

    if ( $count == 0 ) {
        $categories = array(
            array(
                'slug'          => 'essential',
                'name'          => 'Essential',
                'description'   => 'Required for site functionality',
                'is_required'   => 1,
                'display_order' => 10,
            ),
            array(
                'slug'          => 'functional',
                'name'          => 'Functional',
                'description'   => 'Enhance site features',
                'is_required'   => 0,
                'display_order' => 20,
            ),
            array(
                'slug'          => 'analytics',
                'name'          => 'Analytics',
                'description'   => 'Help understand site usage',
                'is_required'   => 0,
                'display_order' => 30,
            ),
            array(
                'slug'          => 'marketing',
                'name'          => 'Marketing',
                'description'   => 'Personalize ads and content',
                'is_required'   => 0,
                'display_order' => 40,
            ),
        );

        foreach ( $categories as $category ) {
            $wpdb->insert( $table, $category );
        }
    }
}

/**
 * Initialize plugin
 */
function ccm_init_plugin() {
    // Initialize main cookie manager class
    CCM_Cookie_Manager::get_instance();
}
add_action( 'plugins_loaded', 'ccm_init_plugin' );
