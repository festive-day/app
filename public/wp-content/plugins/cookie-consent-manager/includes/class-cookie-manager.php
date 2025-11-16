<?php
/**
 * Main Cookie Manager Class
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CCM_Cookie_Manager class
 */
class CCM_Cookie_Manager {

    /**
     * The single instance of the class
     *
     * @var CCM_Cookie_Manager
     */
    protected static $instance = null;

    /**
     * Get instance
     *
     * @return CCM_Cookie_Manager
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Register AJAX endpoints
        add_action( 'wp_ajax_ccm_get_banner_config', array( $this, 'ajax_get_banner_config' ) );
        add_action( 'wp_ajax_nopriv_ccm_get_banner_config', array( $this, 'ajax_get_banner_config' ) );

        add_action( 'wp_ajax_ccm_record_consent', array( $this, 'ajax_record_consent' ) );
        add_action( 'wp_ajax_nopriv_ccm_record_consent', array( $this, 'ajax_record_consent' ) );

        add_action( 'wp_ajax_ccm_check_dnt', array( $this, 'ajax_check_dnt' ) );
        add_action( 'wp_ajax_nopriv_ccm_check_dnt', array( $this, 'ajax_check_dnt' ) );

        // Add cookie settings link to footer
        add_action( 'wp_footer', array( $this, 'render_cookie_settings_link' ) );

        // Setup cron job for log cleanup
        if ( ! wp_next_scheduled( 'ccm_cleanup_old_logs' ) ) {
            wp_schedule_event( time(), 'daily', 'ccm_cleanup_old_logs' );
        }
        add_action( 'ccm_cleanup_old_logs', array( $this, 'cleanup_old_logs' ) );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        $plugin_url = plugin_dir_url( dirname( __FILE__ ) );
        $plugin_version = defined( 'CCM_VERSION' ) ? CCM_VERSION : '1.0.0';

        // Enqueue cookie blocker first (CRITICAL - must load before other scripts)
        wp_enqueue_script(
            'ccm-cookie-blocker',
            $plugin_url . 'public/js/cookie-blocker.js',
            array(), // No dependencies - must load first
            $plugin_version,
            false // Load in header with priority
        );

        // Set very high priority for blocker
        add_filter( 'script_loader_tag', function( $tag, $handle ) {
            if ( $handle === 'ccm-cookie-blocker' ) {
                // Make it load ASAP
                return str_replace( '<script', '<script data-ccm-blocker="true"', $tag );
            }
            return $tag;
        }, -9999, 2 );

        // Enqueue storage manager
        wp_enqueue_script(
            'ccm-storage-manager',
            $plugin_url . 'public/js/storage-manager.js',
            array(), // No dependencies
            $plugin_version,
            false // Load in header
        );

        // Enqueue consent banner (depends on storage manager)
        wp_enqueue_script(
            'ccm-consent-banner',
            $plugin_url . 'public/js/consent-banner.js',
            array( 'ccm-storage-manager' ),
            $plugin_version,
            true // Load in footer
        );

        // Localize script with AJAX URL and version
        wp_localize_script(
            'ccm-consent-banner',
            'CCM_CONFIG',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'version'  => $plugin_version,
                'nonce'    => wp_create_nonce( 'ccm_ajax_nonce' ),
            )
        );

        // Make AJAX URL and version available globally
        wp_add_inline_script(
            'ccm-storage-manager',
            'window.CCM_AJAX_URL = "' . esc_js( admin_url( 'admin-ajax.php' ) ) . '";' .
            'window.CCM_VERSION = "' . esc_js( $plugin_version ) . '";',
            'before'
        );

        // Enqueue banner styles
        wp_enqueue_style(
            'ccm-banner',
            $plugin_url . 'public/css/banner.css',
            array(),
            $plugin_version
        );
    }

    /**
     * AJAX handler for getting banner configuration
     */
    public function ajax_get_banner_config() {
        global $wpdb;

        // Check cache first
        $cache_key = 'ccm_banner_config';
        $cached = get_transient( $cache_key );

        if ( $cached !== false ) {
            wp_send_json_success( $cached );
            return;
        }

        // Get all categories ordered by display_order
        $categories_table = $wpdb->prefix . 'cookie_consent_categories';
        $cookies_table    = $wpdb->prefix . 'cookie_consent_cookies';

        $categories = $wpdb->get_results(
            "SELECT * FROM {$categories_table} ORDER BY display_order ASC"
        );

        // Get cookies for each category
        $categories_with_cookies = array();
        foreach ( $categories as $category ) {
            $cookies_raw = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT name, provider, purpose, expiration FROM {$cookies_table} WHERE category_id = %d",
                    $category->id
                ),
                ARRAY_A // Return as associative arrays for JSON encoding
            );

            // Ensure cookies array is properly formatted
            $cookies = array();
            if ( $cookies_raw ) {
                foreach ( $cookies_raw as $cookie ) {
                    $cookies[] = array(
                        'name'       => isset( $cookie['name'] ) ? $cookie['name'] : '',
                        'provider'   => isset( $cookie['provider'] ) ? $cookie['provider'] : '',
                        'purpose'    => isset( $cookie['purpose'] ) ? $cookie['purpose'] : '',
                        'expiration' => isset( $cookie['expiration'] ) ? $cookie['expiration'] : '',
                    );
                }
            }

            $categories_with_cookies[] = array(
                'slug'        => $category->slug,
                'name'        => $category->name,
                'description' => $category->description,
                'is_required' => (bool) $category->is_required,
                'cookies'     => $cookies,
            );
        }

        // Build banner text structure
        $banner_text = array(
            'heading'           => get_option( 'ccm_banner_heading', 'We use cookies' ),
            'message'           => get_option( 'ccm_banner_message', 'This site uses cookies to enhance your experience and analyze site usage.' ),
            'accept_all_label'  => get_option( 'ccm_accept_all_label', 'Accept All' ),
            'reject_all_label'  => get_option( 'ccm_reject_all_label', 'Reject All' ),
            'manage_label'      => get_option( 'ccm_manage_label', 'Manage Preferences' ),
        );

        $data = array(
            'categories'      => $categories_with_cookies,
            'banner_text'     => $banner_text,
            'consent_version' => defined( 'CCM_VERSION' ) ? CCM_VERSION : '1.0.0',
        );

        // Cache for 1 hour
        set_transient( $cache_key, $data, HOUR_IN_SECONDS );

        wp_send_json_success( $data );
    }

    /**
     * AJAX handler for recording consent
     */
    public function ajax_record_consent() {
        // Rate limiting check (10 requests per minute per visitor)
        $visitor_id = CCM_Consent_Logger::generate_visitor_id();
        $transient_key = 'ccm_rate_limit_' . substr( md5( $visitor_id ), 0, 16 );
        $request_count = get_transient( $transient_key );

        if ( $request_count !== false && $request_count >= 10 ) {
            wp_send_json_error( array( 'message' => 'Rate limit exceeded. Please wait before making another request.' ), 429 );
        }

        // Increment rate limit counter (60 second window)
        set_transient( $transient_key, ( $request_count ? $request_count + 1 : 1 ), 60 );

        // Get POST data
        $event_type           = isset( $_POST['event_type'] ) ? sanitize_text_field( wp_unslash( $_POST['event_type'] ) ) : '';
        $accepted_categories  = isset( $_POST['accepted_categories'] ) ? array_map( 'sanitize_text_field', (array) $_POST['accepted_categories'] ) : array();
        $rejected_categories  = isset( $_POST['rejected_categories'] ) ? array_map( 'sanitize_text_field', (array) $_POST['rejected_categories'] ) : array();
        $consent_version      = isset( $_POST['consent_version'] ) ? sanitize_text_field( wp_unslash( $_POST['consent_version'] ) ) : '';

        // Validate event type
        $valid_types = array( 'accept_all', 'reject_all', 'accept_partial', 'modify', 'revoke' );
        if ( ! in_array( $event_type, $valid_types, true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid event type: ' . esc_html( $event_type ) ), 400 );
        }

        // Validate categories are non-empty for non-revoke events
        if ( $event_type !== 'revoke' && empty( $accepted_categories ) ) {
            wp_send_json_error( array( 'message' => 'At least one category must be accepted' ), 400 );
        }

        // Validate category slugs exist in database (prevent invalid category attacks)
        if ( ! empty( $accepted_categories ) || ! empty( $rejected_categories ) ) {
            $all_categories = array_merge( $accepted_categories, $rejected_categories );
            $categories_table = $wpdb->prefix . 'cookie_consent_categories';
            
            // Build placeholders for prepared statement
            $placeholders = implode( ',', array_fill( 0, count( $all_categories ), '%s' ) );
            $query = $wpdb->prepare(
                "SELECT slug FROM {$categories_table} WHERE slug IN ({$placeholders})",
                ...$all_categories
            );
            
            $valid_slugs = $wpdb->get_col( $query );

            // Check if all provided slugs are valid
            $invalid_slugs = array_diff( $all_categories, $valid_slugs );
            if ( ! empty( $invalid_slugs ) ) {
                wp_send_json_error( array( 'message' => 'Invalid category slugs provided' ), 400 );
            }
        }

        // Record event
        $event_id = CCM_Consent_Logger::record_event(
            array(
                'event_type'          => $event_type,
                'accepted_categories' => $accepted_categories,
                'rejected_categories' => $rejected_categories,
            )
        );

        if ( $event_id ) {
            wp_send_json_success(
                array(
                    'event_id'   => $event_id,
                    'visitor_id' => $visitor_id,
                    'timestamp'  => current_time( 'mysql' ),
                )
            );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to record consent event' ), 500 );
        }
    }

    /**
     * AJAX handler for checking Do Not Track
     */
    public function ajax_check_dnt() {
        $dnt_enabled = false;

        // Check DNT header (multiple variations)
        if ( isset( $_SERVER['HTTP_DNT'] ) && $_SERVER['HTTP_DNT'] === '1' ) {
            $dnt_enabled = true;
        } elseif ( isset( $_SERVER['HTTP_X_DO_NOT_TRACK'] ) && $_SERVER['HTTP_X_DO_NOT_TRACK'] === '1' ) {
            $dnt_enabled = true;
        }

        wp_send_json_success(
            array(
                'dnt_enabled' => $dnt_enabled,
                'auto_reject' => $dnt_enabled, // Auto-reject if DNT is enabled
            )
        );
    }

    /**
     * Render cookie settings link in footer
     * 
     * NOTE: This is deprecated - footer link is now rendered in banner-template.php
     * Keeping for backwards compatibility but not rendering to avoid duplicates
     */
    public function render_cookie_settings_link() {
        // Do not render - footer link is handled in banner-template.php
        // This prevents duplicate links
        return;
    }

    /**
     * Cleanup old log entries (3-year retention)
     */
    public function cleanup_old_logs() {
        global $wpdb;

        $table = $wpdb->prefix . 'cookie_consent_events';

        $deleted = $wpdb->query(
            "DELETE FROM {$table} WHERE event_timestamp < DATE_SUB(NOW(), INTERVAL 3 YEAR)"
        );

        // Log cleanup action
        if ( $deleted > 0 ) {
            error_log( "CCM: Cleaned up {$deleted} old consent event records" );
        }
    }
}
