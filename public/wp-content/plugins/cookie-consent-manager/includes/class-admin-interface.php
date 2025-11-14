<?php
/**
 * Admin Interface Class
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CCM_Admin_Interface class
 */
class CCM_Admin_Interface {

    /**
     * Initialize admin interface
     */
    public static function init() {
        // Add admin menu
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );

        // Enqueue admin assets
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );

        // Register AJAX handlers for admin
        self::register_admin_ajax_handlers();
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_options_page(
            __( 'Cookie Consent Manager', 'cookie-consent-manager' ),
            __( 'Cookie Consent', 'cookie-consent-manager' ),
            'manage_options',
            'cookie-consent-settings',
            array( __CLASS__, 'render_admin_page' )
        );
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets( $hook ) {
        // Only load on our settings page
        if ( 'settings_page_cookie-consent-settings' !== $hook ) {
            return;
        }

        // Enqueue will be fully implemented in Phase 6
        wp_enqueue_style( 'ccm-admin-styles', CCM_PLUGIN_URL . 'admin/css/admin-styles.css', array(), CCM_VERSION );
        wp_enqueue_script( 'ccm-admin-scripts', CCM_PLUGIN_URL . 'admin/js/admin-scripts.js', array( 'jquery' ), CCM_VERSION, true );

        wp_localize_script(
            'ccm-admin-scripts',
            'ccmAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'ccm_admin_nonce' ),
            )
        );
    }

    /**
     * Register admin AJAX handlers
     */
    private static function register_admin_ajax_handlers() {
        // Category management
        add_action( 'wp_ajax_ccm_list_categories', array( __CLASS__, 'ajax_list_categories' ) );
        add_action( 'wp_ajax_ccm_create_category', array( __CLASS__, 'ajax_create_category' ) );
        add_action( 'wp_ajax_ccm_update_category', array( __CLASS__, 'ajax_update_category' ) );
        add_action( 'wp_ajax_ccm_delete_category', array( __CLASS__, 'ajax_delete_category' ) );

        // Cookie management
        add_action( 'wp_ajax_ccm_list_cookies', array( __CLASS__, 'ajax_list_cookies' ) );
        add_action( 'wp_ajax_ccm_create_cookie', array( __CLASS__, 'ajax_create_cookie' ) );
        add_action( 'wp_ajax_ccm_update_cookie', array( __CLASS__, 'ajax_update_cookie' ) );
        add_action( 'wp_ajax_ccm_delete_cookie', array( __CLASS__, 'ajax_delete_cookie' ) );

        // Audit logs
        add_action( 'wp_ajax_ccm_view_logs', array( __CLASS__, 'ajax_view_logs' ) );
        add_action( 'wp_ajax_ccm_export_logs', array( __CLASS__, 'ajax_export_logs' ) );
    }

    /**
     * Verify admin request (nonce + capability)
     *
     * @return bool True if verified
     */
    private static function verify_admin_request() {
        check_ajax_referer( 'ccm_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
            return false;
        }

        return true;
    }

    /**
     * Render admin page
     */
    public static function render_admin_page() {
        // Basic admin page shell - full implementation in Phase 6
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Manage cookie consent settings, categories, and cookies.', 'cookie-consent-manager' ); ?></p>
            <div class="notice notice-info">
                <p><?php esc_html_e( 'Admin interface will be fully implemented in Phase 6.', 'cookie-consent-manager' ); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: List categories
     */
    public static function ajax_list_categories() {
        self::verify_admin_request();

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_categories';

        $categories = $wpdb->get_results(
            "SELECT c.*, COUNT(ck.id) as cookie_count
             FROM {$table} c
             LEFT JOIN {$wpdb->prefix}cookie_consent_cookies ck ON c.id = ck.category_id
             GROUP BY c.id
             ORDER BY c.display_order ASC"
        );

        wp_send_json_success( array( 'categories' => $categories ) );
    }

    /**
     * AJAX: Create category
     */
    public static function ajax_create_category() {
        self::verify_admin_request();

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_categories';

        $slug        = isset( $_POST['slug'] ) ? sanitize_title( $_POST['slug'] ) : '';
        $name        = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $description = isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '';

        if ( empty( $slug ) || empty( $name ) ) {
            wp_send_json_error( array( 'message' => 'Slug and name are required' ), 400 );
        }

        $result = $wpdb->insert(
            $table,
            array(
                'slug'        => $slug,
                'name'        => $name,
                'description' => $description,
            )
        );

        if ( $result ) {
            wp_send_json_success( array( 'id' => $wpdb->insert_id ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to create category' ), 500 );
        }
    }

    /**
     * AJAX: Update category
     */
    public static function ajax_update_category() {
        self::verify_admin_request();

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_categories';

        $id          = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        $name        = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $description = isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '';

        if ( ! $id || empty( $name ) ) {
            wp_send_json_error( array( 'message' => 'ID and name are required' ), 400 );
        }

        $result = $wpdb->update(
            $table,
            array(
                'name'        => $name,
                'description' => $description,
            ),
            array( 'id' => $id )
        );

        if ( $result !== false ) {
            wp_send_json_success( array( 'message' => 'Category updated' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to update category' ), 500 );
        }
    }

    /**
     * AJAX: Delete category
     */
    public static function ajax_delete_category() {
        self::verify_admin_request();

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_categories';

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'ID is required' ), 400 );
        }

        // Check if category is required
        $is_required = $wpdb->get_var(
            $wpdb->prepare( "SELECT is_required FROM {$table} WHERE id = %d", $id )
        );

        if ( $is_required ) {
            wp_send_json_error( array( 'message' => 'Cannot delete required category' ), 400 );
        }

        $result = $wpdb->delete( $table, array( 'id' => $id ) );

        if ( $result ) {
            wp_send_json_success( array( 'message' => 'Category deleted' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to delete category' ), 500 );
        }
    }

    /**
     * AJAX: List cookies (stub for Phase 6)
     */
    public static function ajax_list_cookies() {
        self::verify_admin_request();
        wp_send_json_success( array( 'cookies' => array() ) );
    }

    /**
     * AJAX: Create cookie (stub for Phase 6)
     */
    public static function ajax_create_cookie() {
        self::verify_admin_request();
        wp_send_json_error( array( 'message' => 'Not implemented yet' ), 501 );
    }

    /**
     * AJAX: Update cookie (stub for Phase 6)
     */
    public static function ajax_update_cookie() {
        self::verify_admin_request();
        wp_send_json_error( array( 'message' => 'Not implemented yet' ), 501 );
    }

    /**
     * AJAX: Delete cookie (stub for Phase 6)
     */
    public static function ajax_delete_cookie() {
        self::verify_admin_request();
        wp_send_json_error( array( 'message' => 'Not implemented yet' ), 501 );
    }

    /**
     * AJAX: View logs (stub for Phase 6)
     */
    public static function ajax_view_logs() {
        self::verify_admin_request();
        wp_send_json_success( array( 'logs' => array() ) );
    }

    /**
     * AJAX: Export logs (stub for Phase 6)
     */
    public static function ajax_export_logs() {
        self::verify_admin_request();
        wp_send_json_error( array( 'message' => 'Not implemented yet' ), 501 );
    }
}

// Initialize admin interface
CCM_Admin_Interface::init();
