<?php
/**
 * Integration tests for admin interface CRUD operations
 *
 * Tests CRUD operations on categories and cookies via admin AJAX endpoints
 *
 * @package Cookie_Consent_Manager
 */

class Test_Admin_Interface extends WP_UnitTestCase {

    private $admin_user_id;
    private $non_admin_user_id;

    /**
     * Setup test environment
     */
    public function setUp(): void {
        parent::setUp();

        // Create admin user for testing
        $this->admin_user_id = $this->factory->user->create(
            array(
                'role' => 'administrator',
            )
        );

        // Create non-admin user for permission testing
        $this->non_admin_user_id = $this->factory->user->create(
            array(
                'role' => 'subscriber',
            )
        );

        // Clear tables before each test
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->prefix}cookie_consent_cookies" );
        $wpdb->query( "DELETE FROM {$wpdb->prefix}cookie_consent_categories WHERE slug NOT IN ('essential', 'functional', 'analytics', 'marketing')" );

        // Ensure default categories exist
        $this->ensure_default_categories();
    }

    /**
     * Ensure default categories exist for testing
     */
    private function ensure_default_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'cookie_consent_categories';

        $defaults = array(
            array(
                'slug'        => 'essential',
                'name'        => 'Essential',
                'description' => 'Required for site functionality',
                'is_required' => 1,
                'display_order' => 10,
            ),
            array(
                'slug'        => 'functional',
                'name'        => 'Functional',
                'description' => 'Enhance site features',
                'is_required' => 0,
                'display_order' => 20,
            ),
            array(
                'slug'        => 'analytics',
                'name'        => 'Analytics',
                'description' => 'Help understand site usage',
                'is_required' => 0,
                'display_order' => 30,
            ),
            array(
                'slug'        => 'marketing',
                'name'        => 'Marketing',
                'description' => 'Personalize ads and content',
                'is_required' => 0,
                'display_order' => 40,
            ),
        );

        foreach ( $defaults as $category ) {
            $exists = $wpdb->get_var(
                $wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s", $category['slug'] )
            );

            if ( ! $exists ) {
                $wpdb->insert( $table, $category );
            }
        }
    }

    /**
     * Get admin nonce for AJAX requests
     */
    private function get_admin_nonce() {
        wp_set_current_user( $this->admin_user_id );
        return wp_create_nonce( 'ccm_admin_nonce' );
    }

    /**
     * Simulate AJAX request
     */
    private function simulate_ajax_request( $action, $data = array(), $user_id = null ) {
        if ( $user_id ) {
            wp_set_current_user( $user_id );
        } else {
            wp_set_current_user( $this->admin_user_id );
        }

        // Separate POST and GET data
        $_POST = array();
        $_GET = array();
        $_REQUEST = array();

        // Add nonce to POST for admin endpoints
        if ( isset( $data['nonce'] ) ) {
            $_POST['nonce'] = $data['nonce'];
            $_REQUEST['nonce'] = $data['nonce'];
        }

        // Add action
        $_REQUEST['action'] = $action;

        // Add other POST data
        foreach ( $data as $key => $value ) {
            if ( 'nonce' !== $key ) {
                $_POST[ $key ] = $value;
                $_REQUEST[ $key ] = $value;
            }
        }

        // Preserve existing $_GET (for pagination, filters, etc.)
        if ( ! empty( $_GET ) ) {
            foreach ( $_GET as $key => $value ) {
                $_REQUEST[ $key ] = $value;
            }
        }

        // Capture output
        ob_start();
        try {
            do_action( 'wp_ajax_' . $action );
        } catch ( WPDieException $e ) {
            // Expected for wp_send_json_* functions
        }
        $output = ob_get_clean();

        return json_decode( $output, true );
    }

    /**
     * Test: List categories returns all categories
     */
    public function test_list_categories_returns_all_categories() {
        $nonce = $this->get_admin_nonce();
        $response = $this->simulate_ajax_request(
            'ccm_list_categories',
            array( 'nonce' => $nonce )
        );

        $this->assertTrue( $response['success'], 'List categories should succeed' );
        $this->assertArrayHasKey( 'data', $response, 'Response should have data key' );
        $this->assertIsArray( $response['data'], 'Data should be an array' );
        $this->assertGreaterThanOrEqual( 4, count( $response['data'] ), 'Should have at least 4 default categories' );

        // Verify structure
        $category = $response['data'][0];
        $this->assertArrayHasKey( 'id', $category, 'Category should have id' );
        $this->assertArrayHasKey( 'slug', $category, 'Category should have slug' );
        $this->assertArrayHasKey( 'name', $category, 'Category should have name' );
        $this->assertArrayHasKey( 'cookie_count', $category, 'Category should have cookie_count' );
    }

    /**
     * Test: Create category with valid data
     */
    public function test_create_category_with_valid_data() {
        $nonce = $this->get_admin_nonce();
        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce'       => $nonce,
                'slug'        => 'social-media',
                'name'        => 'Social Media',
                'description' => 'Social sharing widgets',
            )
        );

        $this->assertTrue( $response['success'], 'Create category should succeed' );
        $this->assertArrayHasKey( 'data', $response, 'Response should have data key' );
        $this->assertArrayHasKey( 'id', $response['data'], 'Data should have id' );
        $this->assertGreaterThan( 0, $response['data']['id'], 'ID should be positive' );

        // Verify category was created in database
        global $wpdb;
        $category = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_categories WHERE id = %d",
                $response['data']['id']
            )
        );

        $this->assertNotNull( $category, 'Category should exist in database' );
        $this->assertEquals( 'social-media', $category->slug, 'Slug should match' );
        $this->assertEquals( 'Social Media', $category->name, 'Name should match' );
    }

    /**
     * Test: Create category fails without required fields
     */
    public function test_create_category_fails_without_required_fields() {
        $nonce = $this->get_admin_nonce();

        // Test without slug
        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'name'  => 'Test Category',
            )
        );

        $this->assertFalse( $response['success'], 'Create should fail without slug' );
        $this->assertEquals( 400, $response['data']['status'], 'Should return 400 status' );

        // Test without name
        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'test-category',
            )
        );

        $this->assertFalse( $response['success'], 'Create should fail without name' );
        $this->assertEquals( 400, $response['data']['status'], 'Should return 400 status' );
    }

    /**
     * Test: Create category fails with duplicate slug
     */
    public function test_create_category_fails_with_duplicate_slug() {
        $nonce = $this->get_admin_nonce();

        // Create first category
        $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'duplicate-test',
                'name'  => 'Duplicate Test',
            )
        );

        // Try to create duplicate
        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'duplicate-test',
                'name'  => 'Another Duplicate',
            )
        );

        // Should fail due to unique constraint
        $this->assertFalse( $response['success'], 'Create should fail with duplicate slug' );
        $this->assertArrayHasKey( 'errors', $response['data'], 'Should have errors array' );
    }

    /**
     * Test: Update category with valid data
     */
    public function test_update_category_with_valid_data() {
        $nonce = $this->get_admin_nonce();

        // Create category first
        $create_response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce'       => $nonce,
                'slug'        => 'update-test',
                'name'        => 'Original Name',
                'description' => 'Original description',
            )
        );

        $category_id = $create_response['data']['id'];

        // Update category
        $response = $this->simulate_ajax_request(
            'ccm_update_category',
            array(
                'nonce'       => $nonce,
                'id'          => $category_id,
                'name'        => 'Updated Name',
                'description' => 'Updated description',
            )
        );

        $this->assertTrue( $response['success'], 'Update should succeed' );

        // Verify update in database
        global $wpdb;
        $category = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_categories WHERE id = %d",
                $category_id
            )
        );

        $this->assertEquals( 'Updated Name', $category->name, 'Name should be updated' );
        $this->assertEquals( 'Updated description', $category->description, 'Description should be updated' );
    }

    /**
     * Test: Update category fails without ID
     */
    public function test_update_category_fails_without_id() {
        $nonce = $this->get_admin_nonce();

        $response = $this->simulate_ajax_request(
            'ccm_update_category',
            array(
                'nonce' => $nonce,
                'name'  => 'Test Name',
            )
        );

        $this->assertFalse( $response['success'], 'Update should fail without ID' );
        $this->assertEquals( 400, $response['data']['status'], 'Should return 400 status' );
    }

    /**
     * Test: Update category fails with invalid ID
     */
    public function test_update_category_fails_with_invalid_id() {
        $nonce = $this->get_admin_nonce();

        $response = $this->simulate_ajax_request(
            'ccm_update_category',
            array(
                'nonce' => $nonce,
                'id'    => 99999,
                'name'  => 'Test Name',
            )
        );

        // Should fail or return false (no rows updated)
        $this->assertFalse( $response['success'], 'Update should fail with invalid ID' );
    }

    /**
     * Test: Delete category with valid ID
     */
    public function test_delete_category_with_valid_id() {
        $nonce = $this->get_admin_nonce();

        // Create category first
        $create_response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'delete-test',
                'name'  => 'Delete Test',
            )
        );

        $category_id = $create_response['data']['id'];

        // Delete category
        $response = $this->simulate_ajax_request(
            'ccm_delete_category',
            array(
                'nonce' => $nonce,
                'id'    => $category_id,
            )
        );

        $this->assertTrue( $response['success'], 'Delete should succeed' );

        // Verify deletion in database
        global $wpdb;
        $category = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_categories WHERE id = %d",
                $category_id
            )
        );

        $this->assertNull( $category, 'Category should be deleted from database' );
    }

    /**
     * Test: Delete category fails for required category
     */
    public function test_delete_category_fails_for_required_category() {
        $nonce = $this->get_admin_nonce();

        // Get essential category ID
        global $wpdb;
        $essential_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}cookie_consent_categories WHERE slug = %s",
                'essential'
            )
        );

        // Try to delete required category
        $response = $this->simulate_ajax_request(
            'ccm_delete_category',
            array(
                'nonce' => $nonce,
                'id'    => $essential_id,
            )
        );

        $this->assertFalse( $response['success'], 'Delete should fail for required category' );
        $this->assertEquals( 409, $response['data']['status'], 'Should return 409 status (Conflict)' );

        // Verify category still exists
        $category = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_categories WHERE id = %d",
                $essential_id
            )
        );

        $this->assertNotNull( $category, 'Required category should still exist' );
    }

    /**
     * Test: Delete category cascades to cookies
     */
    public function test_delete_category_cascades_to_cookies() {
        $nonce = $this->get_admin_nonce();

        // Create category
        $create_response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'cascade-test',
                'name'  => 'Cascade Test',
            )
        );

        $category_id = $create_response['data']['id'];

        // Create cookie in that category
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'cookie_consent_cookies',
            array(
                'name'        => '_test_cookie',
                'category_id' => $category_id,
                'purpose'     => 'Test purpose',
                'expiration'  => '1 year',
            )
        );

        $cookie_id = $wpdb->insert_id;

        // Verify cookie exists
        $cookie = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_cookies WHERE id = %d",
                $cookie_id
            )
        );
        $this->assertNotNull( $cookie, 'Cookie should exist before category deletion' );

        // Delete category
        $response = $this->simulate_ajax_request(
            'ccm_delete_category',
            array(
                'nonce' => $nonce,
                'id'    => $category_id,
            )
        );

        $this->assertTrue( $response['success'], 'Delete should succeed' );

        // Verify cookie was cascade deleted
        $cookie = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_cookies WHERE id = %d",
                $cookie_id
            )
        );

        $this->assertNull( $cookie, 'Cookie should be cascade deleted' );
    }

    /**
     * Test: List cookies returns empty array initially
     */
    public function test_list_cookies_returns_empty_array() {
        $nonce = $this->get_admin_nonce();
        $response = $this->simulate_ajax_request(
            'ccm_list_cookies',
            array( 'nonce' => $nonce )
        );

        $this->assertTrue( $response['success'], 'List cookies should succeed' );
        $this->assertArrayHasKey( 'data', $response, 'Response should have data key' );
        $this->assertIsArray( $response['data'], 'Data should be an array' );
        $this->assertArrayHasKey( 'pagination', $response, 'Response should have pagination' );
    }

    /**
     * Test: Create cookie with valid data
     */
    public function test_create_cookie_with_valid_data() {
        $nonce = $this->get_admin_nonce();

        // Get a category ID
        global $wpdb;
        $category_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}cookie_consent_categories WHERE slug = %s",
                'analytics'
            )
        );

        $response = $this->simulate_ajax_request(
            'ccm_create_cookie',
            array(
                'nonce'       => $nonce,
                'name'        => '_test_cookie',
                'category_id' => $category_id,
                'purpose'     => 'Test purpose for cookie',
                'expiration'  => '1 year',
                'provider'    => 'Test Provider',
            )
        );

        $this->assertTrue( $response['success'], 'Create cookie should succeed' );
        $this->assertArrayHasKey( 'data', $response, 'Response should have data key' );
        $this->assertArrayHasKey( 'id', $response['data'], 'Data should have id' );
        $this->assertGreaterThan( 0, $response['data']['id'], 'ID should be positive' );

        // Verify cookie was created in database
        $cookie = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_cookies WHERE id = %d",
                $response['data']['id']
            )
        );

        $this->assertNotNull( $cookie, 'Cookie should exist in database' );
        $this->assertEquals( '_test_cookie', $cookie->name, 'Name should match' );
    }

    /**
     * Test: Create cookie fails without required fields
     */
    public function test_create_cookie_fails_without_required_fields() {
        $nonce = $this->get_admin_nonce();

        // Test without name
        $response = $this->simulate_ajax_request(
            'ccm_create_cookie',
            array(
                'nonce'       => $nonce,
                'category_id' => 1,
                'purpose'     => 'Test purpose',
            )
        );

        $this->assertFalse( $response['success'], 'Create should fail without name' );
        $this->assertEquals( 400, $response['data']['status'], 'Should return 400 status' );

        // Test without purpose
        $response = $this->simulate_ajax_request(
            'ccm_create_cookie',
            array(
                'nonce'       => $nonce,
                'name'        => '_test',
                'category_id' => 1,
            )
        );

        $this->assertFalse( $response['success'], 'Create should fail without purpose' );
    }

    /**
     * Test: Update cookie with valid data
     */
    public function test_update_cookie_with_valid_data() {
        $nonce = $this->get_admin_nonce();

        // Get a category ID
        global $wpdb;
        $category_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}cookie_consent_categories WHERE slug = %s",
                'analytics'
            )
        );

        // Create cookie first
        $create_response = $this->simulate_ajax_request(
            'ccm_create_cookie',
            array(
                'nonce'       => $nonce,
                'name'        => '_update_test',
                'category_id' => $category_id,
                'purpose'     => 'Original purpose',
                'expiration'  => '1 year',
            )
        );

        $cookie_id = $create_response['data']['id'];

        // Update cookie
        $response = $this->simulate_ajax_request(
            'ccm_update_cookie',
            array(
                'nonce'      => $nonce,
                'id'         => $cookie_id,
                'purpose'    => 'Updated purpose text',
                'expiration' => '2 years',
            )
        );

        $this->assertTrue( $response['success'], 'Update should succeed' );

        // Verify update in database
        $cookie = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_cookies WHERE id = %d",
                $cookie_id
            )
        );

        $this->assertEquals( 'Updated purpose text', $cookie->purpose, 'Purpose should be updated' );
        $this->assertEquals( '2 years', $cookie->expiration, 'Expiration should be updated' );
    }

    /**
     * Test: Delete cookie with valid ID
     */
    public function test_delete_cookie_with_valid_id() {
        $nonce = $this->get_admin_nonce();

        // Get a category ID
        global $wpdb;
        $category_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}cookie_consent_categories WHERE slug = %s",
                'analytics'
            )
        );

        // Create cookie first
        $create_response = $this->simulate_ajax_request(
            'ccm_create_cookie',
            array(
                'nonce'       => $nonce,
                'name'        => '_delete_test',
                'category_id' => $category_id,
                'purpose'     => 'Test purpose',
                'expiration'  => '1 year',
            )
        );

        $cookie_id = $create_response['data']['id'];

        // Delete cookie
        $response = $this->simulate_ajax_request(
            'ccm_delete_cookie',
            array(
                'nonce' => $nonce,
                'id'    => $cookie_id,
            )
        );

        $this->assertTrue( $response['success'], 'Delete should succeed' );

        // Verify deletion in database
        $cookie = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_cookies WHERE id = %d",
                $cookie_id
            )
        );

        $this->assertNull( $cookie, 'Cookie should be deleted from database' );
    }

    /**
     * Test: List cookies with pagination
     */
    public function test_list_cookies_with_pagination() {
        $nonce = $this->get_admin_nonce();

        // Get a category ID
        global $wpdb;
        $category_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}cookie_consent_categories WHERE slug = %s",
                'analytics'
            )
        );

        // Create multiple cookies
        for ( $i = 1; $i <= 5; $i++ ) {
            $this->simulate_ajax_request(
                'ccm_create_cookie',
                array(
                    'nonce'       => $nonce,
                    'name'        => "_pagination_test_{$i}",
                    'category_id' => $category_id,
                    'purpose'     => 'Test purpose for pagination',
                    'expiration'  => '1 year',
                )
            );
        }

        // Test pagination
        $_GET = array( 'page' => 1, 'per_page' => 2 );
        $response = $this->simulate_ajax_request(
            'ccm_list_cookies',
            array( 'nonce' => $nonce )
        );

        $this->assertTrue( $response['success'], 'List cookies should succeed' );
        $this->assertArrayHasKey( 'pagination', $response, 'Response should have pagination' );
        $this->assertEquals( 1, $response['pagination']['page'], 'Page should be 1' );
        $this->assertEquals( 2, $response['pagination']['per_page'], 'Per page should be 2' );
        $this->assertGreaterThanOrEqual( 5, $response['pagination']['total'], 'Total should be at least 5' );
    }

    /**
     * Test: View logs returns formatted data
     */
    public function test_view_logs_returns_formatted_data() {
        $nonce = $this->get_admin_nonce();

        // Create a test consent event
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'cookie_consent_events',
            array(
                'visitor_id'          => 'test_visitor_123',
                'event_type'          => 'accept_all',
                'accepted_categories' => wp_json_encode( array( 'essential', 'analytics' ) ),
                'rejected_categories' => wp_json_encode( array() ),
                'consent_version'     => '1.0.0',
            )
        );

        $_GET = array( 'page' => 1, 'per_page' => 50 );
        $response = $this->simulate_ajax_request(
            'ccm_view_logs',
            array( 'nonce' => $nonce )
        );

        $this->assertTrue( $response['success'], 'View logs should succeed' );
        $this->assertArrayHasKey( 'data', $response, 'Response should have data key' );
        $this->assertIsArray( $response['data'], 'Data should be an array' );
        $this->assertArrayHasKey( 'pagination', $response, 'Response should have pagination' );

        if ( ! empty( $response['data'] ) ) {
            $log = $response['data'][0];
            $this->assertArrayHasKey( 'event_type', $log, 'Log should have event_type' );
            $this->assertIsArray( $log['accepted_categories'], 'Accepted categories should be array' );
        }
    }

    /**
     * Test: Rate limiting prevents excessive requests
     */
    public function test_rate_limiting_prevents_excessive_requests() {
        $nonce = $this->get_admin_nonce();

        // Make 100 requests (should succeed)
        for ( $i = 0; $i < 100; $i++ ) {
            $response = $this->simulate_ajax_request(
                'ccm_list_categories',
                array( 'nonce' => $nonce )
            );
            $this->assertTrue( $response['success'], "Request {$i} should succeed" );
        }

        // 101st request should fail
        $response = $this->simulate_ajax_request(
            'ccm_list_categories',
            array( 'nonce' => $nonce )
        );
        $this->assertFalse( $response['success'], 'Request 101 should fail due to rate limit' );
        $this->assertEquals( 429, $response['data']['status'], 'Should return 429 status' );
    }

    /**
     * Test: SQL injection protection in category slug
     */
    public function test_sql_injection_protection_in_category_slug() {
        $nonce = $this->get_admin_nonce();

        // Attempt SQL injection in slug
        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => "'; DROP TABLE wp_cookie_consent_categories; --",
                'name'  => 'SQL Injection Test',
            )
        );

        // Should sanitize slug, not execute SQL
        $this->assertTrue( $response['success'], 'Should handle malicious input safely' );

        // Verify table still exists
        global $wpdb;
        $table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}cookie_consent_categories'" );
        $this->assertNotNull( $table_exists, 'Table should still exist' );
    }

    /**
     * Test: XSS protection in category description
     */
    public function test_xss_protection_in_category_description() {
        $nonce = $this->get_admin_nonce();

        $xss_payload = '<script>alert("XSS")</script><img src=x onerror=alert("XSS")>';

        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce'       => $nonce,
                'slug'        => 'xss-test',
                'name'        => 'XSS Test',
                'description' => $xss_payload,
            )
        );

        $this->assertTrue( $response['success'], 'Should handle XSS payload' );

        // Verify description was sanitized
        global $wpdb;
        $category = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT description FROM {$wpdb->prefix}cookie_consent_categories WHERE id = %d",
                $response['data']['id']
            )
        );

        // wp_kses_post should remove script tags
        $this->assertStringNotContainsString( '<script>', $category->description, 'Script tags should be removed' );
    }

    /**
     * Test: Validation prevents invalid category slug format
     */
    public function test_validation_prevents_invalid_category_slug_format() {
        $nonce = $this->get_admin_nonce();

        // Test with invalid slug (uppercase, spaces, special chars)
        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'Invalid Slug!@#',
                'name'  => 'Test Category',
            )
        );

        // Slug should be sanitized, but validation should check format
        // Since sanitize_title converts to lowercase-hyphenated, it should pass validation
        // But let's test with a truly invalid format
        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'a', // Too short (min 2 chars)
                'name'  => 'Test Category',
            )
        );

        $this->assertFalse( $response['success'], 'Should fail with invalid slug format' );
        $this->assertArrayHasKey( 'errors', $response['data'], 'Should have errors array' );
    }

    /**
     * Test: Validation prevents invalid cookie purpose length
     */
    public function test_validation_prevents_invalid_cookie_purpose_length() {
        $nonce = $this->get_admin_nonce();

        global $wpdb;
        $category_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}cookie_consent_categories WHERE slug = %s",
                'analytics'
            )
        );

        // Test with purpose too short (min 10 chars)
        $response = $this->simulate_ajax_request(
            'ccm_create_cookie',
            array(
                'nonce'       => $nonce,
                'name'        => '_test',
                'category_id' => $category_id,
                'purpose'     => 'Short', // Too short
                'expiration'  => '1 year',
            )
        );

        $this->assertFalse( $response['success'], 'Should fail with purpose too short' );
        $this->assertArrayHasKey( 'errors', $response['data'], 'Should have errors array' );
    }

    /**
     * Test: Admin endpoints require authentication (nonce)
     */
    public function test_admin_endpoints_require_nonce() {
        wp_set_current_user( $this->admin_user_id );

        // Try without nonce
        $response = $this->simulate_ajax_request(
            'ccm_list_categories',
            array()
        );

        // Should fail with nonce error
        $this->assertFalse( $response['success'], 'Should fail without nonce' );
    }

    /**
     * Test: Admin endpoints require manage_options capability
     */
    public function test_admin_endpoints_require_manage_options_capability() {
        wp_set_current_user( $this->non_admin_user_id );
        $nonce = wp_create_nonce( 'ccm_admin_nonce' );

        $response = $this->simulate_ajax_request(
            'ccm_list_categories',
            array( 'nonce' => $nonce ),
            $this->non_admin_user_id
        );

        $this->assertFalse( $response['success'], 'Should fail without manage_options capability' );
        $this->assertEquals( 403, $response['data']['status'], 'Should return 403 status' );
    }

    /**
     * Test: Category list includes cookie count
     */
    public function test_category_list_includes_cookie_count() {
        $nonce = $this->get_admin_nonce();

        // Create category
        $create_response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'cookie-count-test',
                'name'  => 'Cookie Count Test',
            )
        );

        $category_id = $create_response['data']['id'];

        // Add cookies to category
        global $wpdb;
        for ( $i = 1; $i <= 3; $i++ ) {
            $wpdb->insert(
                $wpdb->prefix . 'cookie_consent_cookies',
                array(
                    'name'        => "_test_cookie_{$i}",
                    'category_id' => $category_id,
                    'purpose'     => 'Test purpose',
                    'expiration'  => '1 year',
                )
            );
        }

        // List categories
        $response = $this->simulate_ajax_request(
            'ccm_list_categories',
            array( 'nonce' => $nonce )
        );

        // Find our category in the list
        $found_category = null;
        foreach ( $response['data'] as $category ) {
            if ( $category['id'] == $category_id ) {
                $found_category = $category;
                break;
            }
        }

        $this->assertNotNull( $found_category, 'Category should be in list' );
        $this->assertEquals( 3, $found_category['cookie_count'], 'Cookie count should be 3' );
    }

    /**
     * Test: Category slug is sanitized on create
     */
    public function test_category_slug_is_sanitized() {
        $nonce = $this->get_admin_nonce();

        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce' => $nonce,
                'slug'  => 'Test Category With Spaces & Special Chars!',
                'name'  => 'Test Category',
            )
        );

        $this->assertTrue( $response['success'], 'Create should succeed' );

        // Verify slug was sanitized
        global $wpdb;
        $category = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT slug FROM {$wpdb->prefix}cookie_consent_categories WHERE id = %d",
                $response['data']['id']
            )
        );

        $this->assertEquals( 'test-category-with-spaces-special-chars', $category->slug, 'Slug should be sanitized' );
    }

    /**
     * Test: Category description supports HTML (wp_kses_post)
     */
    public function test_category_description_supports_html() {
        $nonce = $this->get_admin_nonce();

        $html_description = '<p>This is a <strong>test</strong> description with <a href="#">links</a>.</p>';

        $response = $this->simulate_ajax_request(
            'ccm_create_category',
            array(
                'nonce'       => $nonce,
                'slug'        => 'html-test',
                'name'        => 'HTML Test',
                'description' => $html_description,
            )
        );

        $this->assertTrue( $response['success'], 'Create should succeed' );

        // Verify HTML was sanitized but preserved
        global $wpdb;
        $category = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT description FROM {$wpdb->prefix}cookie_consent_categories WHERE id = %d",
                $response['data']['id']
            )
        );

        $this->assertStringContainsString( '<p>', $category->description, 'HTML should be preserved' );
        $this->assertStringContainsString( '<strong>', $category->description, 'Strong tag should be preserved' );
    }
}

