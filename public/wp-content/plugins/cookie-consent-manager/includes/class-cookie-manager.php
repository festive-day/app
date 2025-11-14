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
        // Will be fully implemented in Phase 3
        // For now, just register script handles
    }

    /**
     * AJAX handler for getting banner configuration
     */
    public function ajax_get_banner_config() {
        global $wpdb;

        // Get all categories ordered by display_order
        $categories_table = $wpdb->prefix . 'cookie_consent_categories';
        $cookies_table    = $wpdb->prefix . 'cookie_consent_cookies';

        $categories = $wpdb->get_results(
            "SELECT * FROM {$categories_table} ORDER BY display_order ASC"
        );

        // Get cookies for each category
        $categories_with_cookies = array();
        foreach ( $categories as $category ) {
            $cookies = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$cookies_table} WHERE category_id = %d",
                    $category->id
                )
            );

            $categories_with_cookies[] = array(
                'id'          => $category->id,
                'slug'        => $category->slug,
                'name'        => $category->name,
                'description' => $category->description,
                'is_required' => (bool) $category->is_required,
                'cookies'     => $cookies,
            );
        }

        wp_send_json_success(
            array(
                'categories'       => $categories_with_cookies,
                'consent_version'  => CCM_VERSION,
                'banner_text'      => get_option( 'ccm_banner_text', 'We use cookies to improve your experience.' ),
            )
        );
    }

    /**
     * AJAX handler for recording consent
     */
    public function ajax_record_consent() {
        // Verify request
        check_ajax_referer( 'ccm_ajax_nonce', 'nonce' );

        // Rate limiting check
        $visitor_id = CCM_Consent_Logger::generate_visitor_id();
        $transient_key = 'ccm_rate_limit_' . $visitor_id;
        $request_count = get_transient( $transient_key );

        if ( $request_count !== false && $request_count >= 10 ) {
            wp_send_json_error( array( 'message' => 'Rate limit exceeded' ), 429 );
        }

        // Increment rate limit counter
        set_transient( $transient_key, ( $request_count ? $request_count + 1 : 1 ), 60 );

        // Get POST data
        $event_type           = isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : '';
        $accepted_categories  = isset( $_POST['accepted_categories'] ) ? array_map( 'sanitize_text_field', $_POST['accepted_categories'] ) : array();
        $rejected_categories  = isset( $_POST['rejected_categories'] ) ? array_map( 'sanitize_text_field', $_POST['rejected_categories'] ) : array();

        // Validate event type
        $valid_types = array( 'accept_all', 'reject_all', 'accept_partial', 'modify', 'revoke' );
        if ( ! in_array( $event_type, $valid_types ) ) {
            wp_send_json_error( array( 'message' => 'Invalid event type' ), 400 );
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
                    'event_id' => $event_id,
                    'message'  => 'Consent recorded successfully',
                )
            );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to record consent' ), 500 );
        }
    }

    /**
     * AJAX handler for checking Do Not Track
     */
    public function ajax_check_dnt() {
        $dnt_enabled = false;

        // Check DNT header (multiple variations)
        if ( isset( $_SERVER['HTTP_DNT'] ) && $_SERVER['HTTP_DNT'] == '1' ) {
            $dnt_enabled = true;
        } elseif ( isset( $_SERVER['HTTP_X_DO_NOT_TRACK'] ) && $_SERVER['HTTP_X_DO_NOT_TRACK'] == '1' ) {
            $dnt_enabled = true;
        }

        wp_send_json_success(
            array(
                'dnt_enabled' => $dnt_enabled,
            )
        );
    }

    /**
     * Render cookie settings link in footer
     */
    public function render_cookie_settings_link() {
        echo '<div class="ccm-footer-link">';
        echo '<a href="#" id="ccm-cookie-settings-link">' . esc_html__( 'Cookie Settings', 'cookie-consent-manager' ) . '</a>';
        echo '</div>';
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
