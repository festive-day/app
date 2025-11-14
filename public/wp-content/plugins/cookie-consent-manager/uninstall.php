<?php
/**
 * Uninstall script for Cookie Consent Manager
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly or not uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop tables
$tables = array(
    $wpdb->prefix . 'cookie_consent_events',
    $wpdb->prefix . 'cookie_consent_cookies',
    $wpdb->prefix . 'cookie_consent_categories',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// Delete options
delete_option( 'ccm_db_version' );
delete_option( 'ccm_plugin_version' );
delete_option( 'ccm_banner_text' );

// Clear scheduled hooks
wp_clear_scheduled_hook( 'ccm_cleanup_old_logs' );

// Delete transients
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ccm_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ccm_%'" );
