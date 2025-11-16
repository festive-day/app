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
     * Verify admin request (nonce + capability + rate limiting)
     *
     * @return bool True if verified
     */
    private static function verify_admin_request() {
        check_ajax_referer( 'ccm_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions' ), 403 );
            return false;
        }

        // Rate limiting: 100 requests per minute per user
        $user_id = get_current_user_id();
        $transient_key = 'ccm_admin_rate_limit_' . $user_id;
        $request_count = get_transient( $transient_key );

        if ( $request_count !== false && $request_count >= 100 ) {
            wp_send_json_error( array( 'message' => 'Rate limit exceeded. Try again in 30 seconds.' ), 429 );
            return false;
        }

        // Increment rate limit counter (60 second window)
        set_transient( $transient_key, ( $request_count ? $request_count + 1 : 1 ), 60 );

        return true;
    }

    /**
     * Validate category slug format
     *
     * @param string $slug Slug to validate
     * @return bool True if valid
     */
    private static function validate_category_slug( $slug ) {
        // Must be lowercase alphanumeric with hyphens, 2-50 chars
        return preg_match( '/^[a-z0-9-]{2,50}$/', $slug ) === 1;
    }

    /**
     * Validate category name
     *
     * @param string $name Name to validate
     * @return bool True if valid
     */
    private static function validate_category_name( $name ) {
        $length = mb_strlen( $name );
        return $length >= 3 && $length <= 100;
    }

    /**
     * Validate cookie name
     *
     * @param string $name Name to validate
     * @return bool True if valid
     */
    private static function validate_cookie_name( $name ) {
        $length = mb_strlen( $name );
        return $length >= 1 && $length <= 255;
    }

    /**
     * Validate cookie purpose
     *
     * @param string $purpose Purpose to validate
     * @return bool True if valid
     */
    private static function validate_cookie_purpose( $purpose ) {
        $length = mb_strlen( $purpose );
        return $length >= 10 && $length <= 500;
    }

    /**
     * Validate domain format
     *
     * @param string $domain Domain to validate
     * @return bool True if valid
     */
    private static function validate_domain( $domain ) {
        if ( empty( $domain ) ) {
            return true; // Optional field
        }
        // Basic domain validation
        return preg_match( '/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/i', $domain ) === 1;
    }

    /**
     * Render admin page
     */
    public static function render_admin_page() {
        $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'categories';
        
        // Validate tab
        $valid_tabs = array( 'categories', 'cookies', 'logs', 'settings' );
        if ( ! in_array( $current_tab, $valid_tabs, true ) ) {
            $current_tab = 'categories';
        }
        ?>
        <div class="wrap ccm-admin-wrapper">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <nav class="ccm-nav-tabs nav-tab-wrapper">
                <a href="?page=cookie-consent-settings&tab=categories" 
                   class="nav-tab <?php echo 'categories' === $current_tab ? 'nav-tab-active' : ''; ?>" 
                   data-tab="categories">
                    <?php esc_html_e( 'Categories', 'cookie-consent-manager' ); ?>
                </a>
                <a href="?page=cookie-consent-settings&tab=cookies" 
                   class="nav-tab <?php echo 'cookies' === $current_tab ? 'nav-tab-active' : ''; ?>" 
                   data-tab="cookies">
                    <?php esc_html_e( 'Cookies', 'cookie-consent-manager' ); ?>
                </a>
                <a href="?page=cookie-consent-settings&tab=logs" 
                   class="nav-tab <?php echo 'logs' === $current_tab ? 'nav-tab-active' : ''; ?>" 
                   data-tab="logs">
                    <?php esc_html_e( 'Audit Logs', 'cookie-consent-manager' ); ?>
                </a>
                <a href="?page=cookie-consent-settings&tab=settings" 
                   class="nav-tab <?php echo 'settings' === $current_tab ? 'nav-tab-active' : ''; ?>" 
                   data-tab="settings">
                    <?php esc_html_e( 'Settings', 'cookie-consent-manager' ); ?>
                </a>
            </nav>

            <?php
            // Load appropriate tab view
            $view_file = CCM_PLUGIN_DIR . 'admin/views/' . $current_tab . '-tab.php';
            if ( file_exists( $view_file ) ) {
                include $view_file;
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'View file not found.', 'cookie-consent-manager' ) . '</p></div>';
            }
            ?>
        </div>
        <?php
    }

    /**
     * AJAX: List categories
     */
    public static function ajax_list_categories() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $categories_table = $wpdb->prefix . 'cookie_consent_categories';
        $cookies_table = $wpdb->prefix . 'cookie_consent_cookies';

        $categories = $wpdb->get_results(
            "SELECT c.*, COUNT(ck.id) as cookie_count
             FROM {$categories_table} c
             LEFT JOIN {$cookies_table} ck ON c.id = ck.category_id
             GROUP BY c.id
             ORDER BY c.display_order ASC"
        );

        // Format response per admin-api.md
        $formatted_categories = array();
        foreach ( $categories as $category ) {
            $formatted_categories[] = array(
                'id'           => (int) $category->id,
                'slug'         => $category->slug,
                'name'         => $category->name,
                'description'  => $category->description,
                'is_required'  => (bool) $category->is_required,
                'display_order' => (int) $category->display_order,
                'cookie_count' => (int) $category->cookie_count,
            );
        }

        wp_send_json_success( array( 'data' => $formatted_categories ) );
    }

    /**
     * AJAX: Create category
     */
    public static function ajax_create_category() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_categories';

        $errors = array();

        // Get and validate slug
        $slug = isset( $_POST['slug'] ) ? sanitize_title( $_POST['slug'] ) : '';
        if ( empty( $slug ) ) {
            $errors['slug'] = 'Slug is required';
        } elseif ( ! self::validate_category_slug( $slug ) ) {
            $errors['slug'] = 'Slug must be lowercase alphanumeric with hyphens only (2-50 chars)';
        } else {
            // Check uniqueness
            $existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $slug ) );
            if ( $existing ) {
                $errors['slug'] = 'Slug already exists';
            }
        }

        // Get and validate name
        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        if ( empty( $name ) ) {
            $errors['name'] = 'Name is required';
        } elseif ( ! self::validate_category_name( $name ) ) {
            $errors['name'] = 'Name must be 3-100 characters';
        }

        // Get and validate description
        $description = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
        if ( mb_strlen( $description ) > 500 ) {
            $errors['description'] = 'Description must be 500 characters or less';
        }

        // Get optional fields
        $is_required = isset( $_POST['is_required'] ) ? intval( $_POST['is_required'] ) : 0;
        $display_order = isset( $_POST['display_order'] ) ? intval( $_POST['display_order'] ) : 0;

        // If no display_order provided, auto-increment by 10
        if ( 0 === $display_order ) {
            $max_order = $wpdb->get_var( "SELECT MAX(display_order) FROM {$table}" );
            $display_order = $max_order ? $max_order + 10 : 10;
        }

        // If is_required=1, check that no other category has is_required=1
        if ( 1 === $is_required ) {
            $existing_required = $wpdb->get_var( "SELECT id FROM {$table} WHERE is_required = 1" );
            if ( $existing_required ) {
                $errors['is_required'] = 'Only one category can be required';
            }
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error( array( 'errors' => $errors ), 400 );
            return;
        }

        // Insert category
        $result = $wpdb->insert(
            $table,
            array(
                'slug'         => $slug,
                'name'         => $name,
                'description' => $description,
                'is_required' => $is_required,
                'display_order' => $display_order,
            ),
            array( '%s', '%s', '%s', '%d', '%d' )
        );

        if ( $result ) {
            // Clear banner config cache
            delete_transient( 'ccm_banner_config' );

            $category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $wpdb->insert_id ) );
            wp_send_json_success(
                array(
                    'data' => array(
                        'id'           => (int) $category->id,
                        'slug'         => $category->slug,
                        'name'         => $category->name,
                        'description' => $category->description,
                        'is_required'  => (bool) $category->is_required,
                        'display_order' => (int) $category->display_order,
                        'created_at'   => $category->created_at,
                    ),
                ),
                201
            );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to create category' ), 500 );
        }
    }

    /**
     * AJAX: Update category
     */
    public static function ajax_update_category() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_categories';

        $errors = array();

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'ID is required' ), 400 );
            return;
        }

        // Check if category exists
        $category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        if ( ! $category ) {
            wp_send_json_error( array( 'message' => 'Category not found' ), 404 );
            return;
        }

        // Get and validate name
        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        if ( empty( $name ) ) {
            $errors['name'] = 'Name is required';
        } elseif ( ! self::validate_category_name( $name ) ) {
            $errors['name'] = 'Name must be 3-100 characters';
        }

        // Get and validate description
        $description = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
        if ( mb_strlen( $description ) > 500 ) {
            $errors['description'] = 'Description must be 500 characters or less';
        }

        // Get optional fields
        $display_order = isset( $_POST['display_order'] ) ? intval( $_POST['display_order'] ) : $category->display_order;

        if ( ! empty( $errors ) ) {
            wp_send_json_error( array( 'errors' => $errors ), 400 );
            return;
        }

        // Update category (slug and is_required cannot be changed)
        $result = $wpdb->update(
            $table,
            array(
                'name'         => $name,
                'description' => $description,
                'display_order' => $display_order,
            ),
            array( 'id' => $id ),
            array( '%s', '%s', '%d' ),
            array( '%d' )
        );

        if ( $result !== false ) {
            // Clear banner config cache
            delete_transient( 'ccm_banner_config' );

            $updated_category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
            wp_send_json_success(
                array(
                    'data' => array(
                        'id'           => (int) $updated_category->id,
                        'slug'         => $updated_category->slug,
                        'name'         => $updated_category->name,
                        'description' => $updated_category->description,
                        'is_required'  => (bool) $updated_category->is_required,
                        'display_order' => (int) $updated_category->display_order,
                        'updated_at'   => $updated_category->updated_at,
                    ),
                )
            );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to update category' ), 500 );
        }
    }

    /**
     * AJAX: Delete category
     */
    public static function ajax_delete_category() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $categories_table = $wpdb->prefix . 'cookie_consent_categories';
        $cookies_table = $wpdb->prefix . 'cookie_consent_cookies';

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'ID is required' ), 400 );
            return;
        }

        // Check if category exists
        $category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$categories_table} WHERE id = %d", $id ) );
        if ( ! $category ) {
            wp_send_json_error( array( 'message' => 'Category not found' ), 404 );
            return;
        }

        // Check if category is required
        if ( $category->is_required ) {
            wp_send_json_error( array( 'message' => 'Cannot delete required category' ), 409 );
            return;
        }

        // Count cookies that will be deleted (CASCADE)
        $cookie_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$cookies_table} WHERE category_id = %d", $id ) );

        // Delete category (cookies will be CASCADE deleted by foreign key)
        $result = $wpdb->delete( $categories_table, array( 'id' => $id ), array( '%d' ) );

        if ( $result ) {
            // Clear banner config cache
            delete_transient( 'ccm_banner_config' );

            wp_send_json_success(
                array(
                    'message'       => 'Category deleted successfully',
                    'deleted_cookies' => (int) $cookie_count,
                )
            );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to delete category' ), 500 );
        }
    }

    /**
     * AJAX: List cookies
     */
    public static function ajax_list_cookies() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $cookies_table = $wpdb->prefix . 'cookie_consent_cookies';
        $categories_table = $wpdb->prefix . 'cookie_consent_categories';

        // Get pagination parameters
        $page = isset( $_GET['page'] ) ? max( 1, intval( $_GET['page'] ) ) : 1;
        $per_page = isset( $_GET['per_page'] ) ? min( 100, max( 1, intval( $_GET['per_page'] ) ) ) : 20;
        $offset = ( $page - 1 ) * $per_page;

        // Get filter parameters
        $category_id = isset( $_GET['category_id'] ) ? intval( $_GET['category_id'] ) : 0;

        // Build WHERE clause
        $where = array( '1=1' );
        $where_values = array();

        if ( $category_id > 0 ) {
            $where[] = 'c.category_id = %d';
            $where_values[] = $category_id;
        }

        $where_sql = implode( ' AND ', $where );

        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$cookies_table} c WHERE {$where_sql}";
        if ( ! empty( $where_values ) ) {
            $count_query = $wpdb->prepare( $count_query, $where_values );
        }
        $total = $wpdb->get_var( $count_query );

        // Get cookies with category name
        $query = "SELECT c.*, cat.name as category_name 
                  FROM {$cookies_table} c
                  LEFT JOIN {$categories_table} cat ON c.category_id = cat.id
                  WHERE {$where_sql}
                  ORDER BY c.created_at DESC
                  LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        $query = $wpdb->prepare( $query, $query_values );

        $cookies = $wpdb->get_results( $query );

        // Format response
        $formatted_cookies = array();
        foreach ( $cookies as $cookie ) {
            $formatted_cookies[] = array(
                'id'           => (int) $cookie->id,
                'name'         => $cookie->name,
                'category_id'  => (int) $cookie->category_id,
                'category_name' => $cookie->category_name,
                'provider'     => $cookie->provider,
                'purpose'      => $cookie->purpose,
                'expiration'   => $cookie->expiration,
                'domain'       => $cookie->domain,
                'created_at'   => $cookie->created_at,
            );
        }

        wp_send_json_success(
            array(
                'data' => $formatted_cookies,
                'pagination' => array(
                    'total'      => (int) $total,
                    'page'       => $page,
                    'per_page'   => $per_page,
                    'total_pages' => (int) ceil( $total / $per_page ),
                ),
            )
        );
    }

    /**
     * AJAX: Create cookie
     */
    public static function ajax_create_cookie() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $cookies_table = $wpdb->prefix . 'cookie_consent_cookies';
        $categories_table = $wpdb->prefix . 'cookie_consent_categories';

        $errors = array();

        // Get and validate name
        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        if ( empty( $name ) ) {
            $errors['name'] = 'Name is required';
        } elseif ( ! self::validate_cookie_name( $name ) ) {
            $errors['name'] = 'Name must be 1-255 characters';
        }

        // Get and validate category_id
        $category_id = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0;
        if ( ! $category_id ) {
            $errors['category_id'] = 'Category is required';
        } else {
            // Verify category exists
            $category = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$categories_table} WHERE id = %d", $category_id ) );
            if ( ! $category ) {
                $errors['category_id'] = 'Invalid category';
            }
        }

        // Get and validate purpose
        $purpose = isset( $_POST['purpose'] ) ? sanitize_textarea_field( wp_unslash( $_POST['purpose'] ) ) : '';
        if ( empty( $purpose ) ) {
            $errors['purpose'] = 'Purpose is required';
        } elseif ( ! self::validate_cookie_purpose( $purpose ) ) {
            $errors['purpose'] = 'Purpose must be 10-500 characters';
        }

        // Get and validate expiration
        $expiration = isset( $_POST['expiration'] ) ? sanitize_text_field( wp_unslash( $_POST['expiration'] ) ) : '';
        if ( empty( $expiration ) ) {
            $errors['expiration'] = 'Expiration is required';
        }

        // Get optional fields
        $provider = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
        if ( mb_strlen( $provider ) > 255 ) {
            $errors['provider'] = 'Provider must be 255 characters or less';
        }

        $domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';
        if ( ! empty( $domain ) && ! self::validate_domain( $domain ) ) {
            $errors['domain'] = 'Invalid domain format';
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error( array( 'errors' => $errors ), 400 );
            return;
        }

        // Insert cookie
        $result = $wpdb->insert(
            $cookies_table,
            array(
                'name'       => $name,
                'category_id' => $category_id,
                'provider'   => $provider,
                'purpose'    => $purpose,
                'expiration' => $expiration,
                'domain'     => $domain,
            ),
            array( '%s', '%d', '%s', '%s', '%s', '%s' )
        );

        if ( $result ) {
            // Clear banner config cache
            delete_transient( 'ccm_banner_config' );

            $cookie = $wpdb->get_row( $wpdb->prepare( "SELECT c.*, cat.name as category_name FROM {$cookies_table} c LEFT JOIN {$categories_table} cat ON c.category_id = cat.id WHERE c.id = %d", $wpdb->insert_id ) );
            wp_send_json_success(
                array(
                    'data' => array(
                        'id'           => (int) $cookie->id,
                        'name'         => $cookie->name,
                        'category_id'  => (int) $cookie->category_id,
                        'category_name' => $cookie->category_name,
                        'provider'     => $cookie->provider,
                        'purpose'      => $cookie->purpose,
                        'expiration'   => $cookie->expiration,
                        'domain'       => $cookie->domain,
                        'created_at'   => $cookie->created_at,
                    ),
                ),
                201
            );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to create cookie' ), 500 );
        }
    }

    /**
     * AJAX: Update cookie
     */
    public static function ajax_update_cookie() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $cookies_table = $wpdb->prefix . 'cookie_consent_cookies';
        $categories_table = $wpdb->prefix . 'cookie_consent_categories';

        $errors = array();

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'ID is required' ), 400 );
            return;
        }

        // Check if cookie exists
        $cookie = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$cookies_table} WHERE id = %d", $id ) );
        if ( ! $cookie ) {
            wp_send_json_error( array( 'message' => 'Cookie not found' ), 404 );
            return;
        }

        // Build update array
        $update_data = array();
        $update_format = array();

        // Update name if provided
        if ( isset( $_POST['name'] ) ) {
            $name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
            if ( empty( $name ) ) {
                $errors['name'] = 'Name is required';
            } elseif ( ! self::validate_cookie_name( $name ) ) {
                $errors['name'] = 'Name must be 1-255 characters';
            } else {
                $update_data['name'] = $name;
                $update_format[] = '%s';
            }
        }

        // Update category_id if provided
        if ( isset( $_POST['category_id'] ) ) {
            $category_id = intval( $_POST['category_id'] );
            if ( ! $category_id ) {
                $errors['category_id'] = 'Category is required';
            } else {
                $category = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$categories_table} WHERE id = %d", $category_id ) );
                if ( ! $category ) {
                    $errors['category_id'] = 'Invalid category';
                } else {
                    $update_data['category_id'] = $category_id;
                    $update_format[] = '%d';
                }
            }
        }

        // Update purpose if provided
        if ( isset( $_POST['purpose'] ) ) {
            $purpose = sanitize_textarea_field( wp_unslash( $_POST['purpose'] ) );
            if ( empty( $purpose ) ) {
                $errors['purpose'] = 'Purpose is required';
            } elseif ( ! self::validate_cookie_purpose( $purpose ) ) {
                $errors['purpose'] = 'Purpose must be 10-500 characters';
            } else {
                $update_data['purpose'] = $purpose;
                $update_format[] = '%s';
            }
        }

        // Update expiration if provided
        if ( isset( $_POST['expiration'] ) ) {
            $expiration = sanitize_text_field( wp_unslash( $_POST['expiration'] ) );
            if ( empty( $expiration ) ) {
                $errors['expiration'] = 'Expiration is required';
            } else {
                $update_data['expiration'] = $expiration;
                $update_format[] = '%s';
            }
        }

        // Update provider if provided
        if ( isset( $_POST['provider'] ) ) {
            $provider = sanitize_text_field( wp_unslash( $_POST['provider'] ) );
            if ( mb_strlen( $provider ) > 255 ) {
                $errors['provider'] = 'Provider must be 255 characters or less';
            } else {
                $update_data['provider'] = $provider;
                $update_format[] = '%s';
            }
        }

        // Update domain if provided
        if ( isset( $_POST['domain'] ) ) {
            $domain = sanitize_text_field( wp_unslash( $_POST['domain'] ) );
            if ( ! empty( $domain ) && ! self::validate_domain( $domain ) ) {
                $errors['domain'] = 'Invalid domain format';
            } else {
                $update_data['domain'] = $domain;
                $update_format[] = '%s';
            }
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error( array( 'errors' => $errors ), 400 );
            return;
        }

        if ( empty( $update_data ) ) {
            wp_send_json_error( array( 'message' => 'No fields to update' ), 400 );
            return;
        }

        // Update cookie
        $result = $wpdb->update(
            $cookies_table,
            $update_data,
            array( 'id' => $id ),
            $update_format,
            array( '%d' )
        );

        if ( $result !== false ) {
            // Clear banner config cache
            delete_transient( 'ccm_banner_config' );

            $updated_cookie = $wpdb->get_row( $wpdb->prepare( "SELECT c.*, cat.name as category_name FROM {$cookies_table} c LEFT JOIN {$categories_table} cat ON c.category_id = cat.id WHERE c.id = %d", $id ) );
            wp_send_json_success(
                array(
                    'data' => array(
                        'id'           => (int) $updated_cookie->id,
                        'name'         => $updated_cookie->name,
                        'category_id'  => (int) $updated_cookie->category_id,
                        'category_name' => $updated_cookie->category_name,
                        'provider'     => $updated_cookie->provider,
                        'purpose'      => $updated_cookie->purpose,
                        'expiration'   => $updated_cookie->expiration,
                        'domain'       => $updated_cookie->domain,
                        'updated_at'   => $updated_cookie->updated_at,
                    ),
                )
            );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to update cookie' ), 500 );
        }
    }

    /**
     * AJAX: Delete cookie
     */
    public static function ajax_delete_cookie() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_cookies';

        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'ID is required' ), 400 );
            return;
        }

        // Check if cookie exists
        $cookie = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
        if ( ! $cookie ) {
            wp_send_json_error( array( 'message' => 'Cookie not found' ), 404 );
            return;
        }

        // Delete cookie
        $result = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

        if ( $result ) {
            // Clear banner config cache
            delete_transient( 'ccm_banner_config' );

            wp_send_json_success( array( 'message' => 'Cookie deleted successfully' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to delete cookie' ), 500 );
        }
    }

    /**
     * AJAX: View logs
     */
    public static function ajax_view_logs() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_events';

        // Get pagination parameters
        $page = isset( $_GET['page'] ) ? max( 1, intval( $_GET['page'] ) ) : 1;
        $per_page = isset( $_GET['per_page'] ) ? min( 100, max( 1, intval( $_GET['per_page'] ) ) ) : 50;
        $offset = ( $page - 1 ) * $per_page;

        // Get filter parameters
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
        $end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
        $event_type = isset( $_GET['event_type'] ) ? sanitize_text_field( wp_unslash( $_GET['event_type'] ) ) : '';

        // Build WHERE clause
        $where = array( '1=1' );
        $where_values = array();

        if ( ! empty( $start_date ) ) {
            // Validate date format
            if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
                $where[] = 'DATE(event_timestamp) >= %s';
                $where_values[] = $start_date;
            }
        }

        if ( ! empty( $end_date ) ) {
            // Validate date format
            if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
                $where[] = 'DATE(event_timestamp) <= %s';
                $where_values[] = $end_date;
            }
        }

        if ( ! empty( $event_type ) ) {
            $valid_types = array( 'accept_all', 'reject_all', 'accept_partial', 'modify', 'revoke' );
            if ( in_array( $event_type, $valid_types, true ) ) {
                $where[] = 'event_type = %s';
                $where_values[] = $event_type;
            }
        }

        $where_sql = implode( ' AND ', $where );

        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
        if ( ! empty( $where_values ) ) {
            $count_query = $wpdb->prepare( $count_query, $where_values );
        }
        $total = $wpdb->get_var( $count_query );

        // Get logs
        $query = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY event_timestamp DESC LIMIT %d OFFSET %d";
        $query_values = array_merge( $where_values, array( $per_page, $offset ) );
        $query = $wpdb->prepare( $query, $query_values );

        $logs = $wpdb->get_results( $query );

        // Format response
        $formatted_logs = array();
        foreach ( $logs as $log ) {
            $accepted = ! empty( $log->accepted_categories ) ? json_decode( $log->accepted_categories, true ) : array();
            $rejected = ! empty( $log->rejected_categories ) ? json_decode( $log->rejected_categories, true ) : array();

            $formatted_logs[] = array(
                'id'                 => (int) $log->id,
                'visitor_id'         => $log->visitor_id,
                'event_type'         => $log->event_type,
                'accepted_categories' => $accepted,
                'rejected_categories' => $rejected,
                'consent_version'    => $log->consent_version,
                'ip_address'         => $log->ip_address,
                'user_agent'         => $log->user_agent,
                'event_timestamp'    => $log->event_timestamp,
            );
        }

        wp_send_json_success(
            array(
                'data' => $formatted_logs,
                'pagination' => array(
                    'total'      => (int) $total,
                    'page'       => $page,
                    'per_page'   => $per_page,
                    'total_pages' => (int) ceil( $total / $per_page ),
                ),
            )
        );
    }

    /**
     * AJAX: Export logs
     */
    public static function ajax_export_logs() {
        if ( ! self::verify_admin_request() ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_events';

        // Get filter parameters
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
        $end_date = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';

        // Build WHERE clause
        $where = array( '1=1' );
        $where_values = array();

        if ( ! empty( $start_date ) ) {
            if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
                $where[] = 'DATE(event_timestamp) >= %s';
                $where_values[] = $start_date;
            }
        }

        if ( ! empty( $end_date ) ) {
            if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
                $where[] = 'DATE(event_timestamp) <= %s';
                $where_values[] = $end_date;
            }
        }

        $where_sql = implode( ' AND ', $where );

        // Get all logs (no pagination for export)
        $query = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY event_timestamp DESC";
        if ( ! empty( $where_values ) ) {
            $query = $wpdb->prepare( $query, $where_values );
        }

        $logs = $wpdb->get_results( $query );

        // Set headers for CSV download
        $filename = 'consent-logs-' . date( 'Y-m-d' ) . '.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        // Output CSV
        $output = fopen( 'php://output', 'w' );

        // BOM for UTF-8
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

        // CSV headers
        fputcsv( $output, array( 'id', 'visitor_id', 'event_type', 'accepted_categories', 'rejected_categories', 'consent_version', 'ip_address', 'user_agent', 'event_timestamp' ) );

        // CSV rows
        foreach ( $logs as $log ) {
            fputcsv(
                $output,
                array(
                    $log->id,
                    $log->visitor_id,
                    $log->event_type,
                    $log->accepted_categories,
                    $log->rejected_categories,
                    $log->consent_version,
                    $log->ip_address,
                    $log->user_agent,
                    $log->event_timestamp,
                )
            );
        }

        fclose( $output );
        exit;
    }
}

// Initialize admin interface
CCM_Admin_Interface::init();
