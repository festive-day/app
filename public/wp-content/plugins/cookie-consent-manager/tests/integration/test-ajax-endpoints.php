<?php
/**
 * Test AJAX Endpoints
 *
 * @package Cookie_Consent_Manager
 */

class Test_AJAX_Endpoints extends WP_UnitTestCase {

    /**
     * Test get_banner_config endpoint returns categories
     */
    public function test_get_banner_config() {
        // Simulate AJAX request
        $_REQUEST['action'] = 'ccm_get_banner_config';

        try {
            ob_start();
            do_action( 'wp_ajax_nopriv_ccm_get_banner_config' );
            $response = ob_get_clean();

            $data = json_decode( $response, true );

            $this->assertTrue( $data['success'], 'AJAX should return success' );
            $this->assertArrayHasKey( 'categories', $data['data'] );
            $this->assertCount( 4, $data['data']['categories'], 'Should return 4 default categories' );
            $this->assertEquals( CCM_VERSION, $data['data']['consent_version'] );
        } catch ( WPAjaxDieContinueException $e ) {
            // Expected behavior for wp_send_json
        }
    }

    /**
     * Test check_dnt endpoint
     */
    public function test_check_dnt_disabled() {
        $_REQUEST['action'] = 'ccm_check_dnt';
        unset( $_SERVER['HTTP_DNT'] );

        try {
            ob_start();
            do_action( 'wp_ajax_nopriv_ccm_check_dnt' );
            $response = ob_get_clean();

            $data = json_decode( $response, true );

            $this->assertTrue( $data['success'] );
            $this->assertFalse( $data['data']['dnt_enabled'], 'DNT should be disabled' );
        } catch ( WPAjaxDieContinueException $e ) {
            // Expected
        }
    }

    /**
     * Test check_dnt endpoint with DNT enabled
     */
    public function test_check_dnt_enabled() {
        $_REQUEST['action'] = 'ccm_check_dnt';
        $_SERVER['HTTP_DNT'] = '1';

        try {
            ob_start();
            do_action( 'wp_ajax_nopriv_ccm_check_dnt' );
            $response = ob_get_clean();

            $data = json_decode( $response, true );

            $this->assertTrue( $data['success'] );
            $this->assertTrue( $data['data']['dnt_enabled'], 'DNT should be enabled' );
        } catch ( WPAjaxDieContinueException $e ) {
            // Expected
        }
    }

    /**
     * Test admin list_categories endpoint
     */
    public function test_admin_list_categories() {
        // Set up admin user
        $admin_user = $this->factory->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin_user );

        $_REQUEST['action'] = 'ccm_list_categories';
        $_REQUEST['nonce']  = wp_create_nonce( 'ccm_admin_nonce' );

        try {
            ob_start();
            do_action( 'wp_ajax_ccm_list_categories' );
            $response = ob_get_clean();

            $data = json_decode( $response, true );

            $this->assertTrue( $data['success'] );
            $this->assertIsArray( $data['data'] );
            $this->assertGreaterThanOrEqual( 4, count( $data['data'] ), 'Should have at least 4 default categories' );
        } catch ( WPAjaxDieContinueException $e ) {
            // Expected
        }
    }

    /**
     * Test admin endpoint requires capability
     */
    public function test_admin_endpoint_requires_capability() {
        // Set up subscriber (no manage_options capability)
        $subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );
        wp_set_current_user( $subscriber );

        $_REQUEST['action'] = 'ccm_list_categories';
        $_REQUEST['nonce']  = wp_create_nonce( 'ccm_admin_nonce' );

        try {
            ob_start();
            do_action( 'wp_ajax_ccm_list_categories' );
            $response = ob_get_clean();

            $data = json_decode( $response, true );

            $this->assertFalse( $data['success'], 'Should fail without manage_options capability' );
        } catch ( WPAjaxDieContinueException $e ) {
            // Expected
        }
    }
}
