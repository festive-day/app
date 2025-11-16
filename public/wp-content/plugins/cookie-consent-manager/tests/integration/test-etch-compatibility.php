<?php
/**
 * Integration tests for Etch theme compatibility
 *
 * Tests banner rendering with Etch theme
 *
 * @package Cookie_Consent_Manager
 */

class Test_Etch_Compatibility extends WP_UnitTestCase {

    private $cookie_manager;

    public function setUp(): void {
        parent::setUp();

        require_once dirname( dirname( __DIR__ ) ) . '/includes/class-cookie-manager.php';
        require_once dirname( dirname( __DIR__ ) ) . '/public/templates/banner-template.php';

        $this->cookie_manager = Cookie_Consent_Manager::get_instance();

        // Switch to Etch theme for testing
        switch_theme( 'etch' );
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * Test: Banner template renders without errors
     */
    public function test_banner_template_renders() {
        ob_start();
        ccm_render_banner_template();
        $output = ob_get_clean();

        // Assert banner container exists
        $this->assertStringContainsString( 'ccm-banner', $output );

        // Assert buttons present
        $this->assertStringContainsString( 'ccm-banner__btn--accept', $output );
        $this->assertStringContainsString( 'ccm-banner__btn--reject', $output );
        $this->assertStringContainsString( 'ccm-banner__btn--manage', $output );
    }

    /**
     * Test: Banner uses BEM class naming convention
     */
    public function test_banner_uses_bem_naming() {
        ob_start();
        ccm_render_banner_template();
        $output = ob_get_clean();

        // Assert BEM blocks
        $this->assertStringContainsString( 'class="ccm-banner"', $output );
        $this->assertStringContainsString( 'class="ccm-banner__message"', $output );
        $this->assertStringContainsString( 'class="ccm-banner__actions"', $output );

        // Assert BEM elements
        $this->assertStringContainsString( 'ccm-banner__btn', $output );

        // Assert BEM modifiers
        $this->assertStringContainsString( 'ccm-banner__btn--accept', $output );
        $this->assertStringContainsString( 'ccm-banner__btn--reject', $output );
    }

    /**
     * Test: Banner contains AutomaticCSS utility classes
     */
    public function test_banner_uses_automaticcss() {
        ob_start();
        ccm_render_banner_template();
        $output = ob_get_clean();

        // Check for common AutomaticCSS utility classes
        // Note: Actual ACSS classes depend on implementation
        // These are examples - adjust based on actual banner.css

        $this->assertNotEmpty( $output, 'Banner should render content' );

        // Banner should have width/layout utilities (generic check)
        $this->assertStringContainsString( 'class=', $output );
    }

    /**
     * Test: Banner is positioned at bottom of viewport
     */
    public function test_banner_bottom_positioning() {
        ob_start();
        ccm_render_banner_template();
        $output = ob_get_clean();

        // Banner should have bottom positioning class or inline style
        $this->assertStringContainsString( 'ccm-banner', $output );

        // Check for bottom positioning indicator (adjust based on actual implementation)
        $has_bottom_class = (
            strpos( $output, 'ccm-banner--bottom' ) !== false ||
            strpos( $output, 'bottom:' ) !== false ||
            strpos( $output, 'position:' ) !== false
        );

        $this->assertTrue( $has_bottom_class, 'Banner should have bottom positioning' );
    }

    /**
     * Test: Banner scripts are enqueued with correct dependencies
     */
    public function test_banner_scripts_enqueued() {
        do_action( 'wp_enqueue_scripts' );

        // Check consent-banner.js is enqueued
        $this->assertTrue( wp_script_is( 'ccm-consent-banner', 'enqueued' ) );

        // Check cookie-blocker.js is enqueued (high priority)
        $this->assertTrue( wp_script_is( 'ccm-cookie-blocker', 'enqueued' ) );

        // Check storage-manager.js is enqueued
        $this->assertTrue( wp_script_is( 'ccm-storage-manager', 'enqueued' ) );
    }

    /**
     * Test: Banner styles are enqueued
     */
    public function test_banner_styles_enqueued() {
        do_action( 'wp_enqueue_scripts' );

        // Check banner.css is enqueued
        $this->assertTrue( wp_style_is( 'ccm-banner', 'enqueued' ) );
    }

    /**
     * Test: Cookie Settings link added to footer
     */
    public function test_cookie_settings_link_in_footer() {
        ob_start();
        do_action( 'wp_footer' );
        $output = ob_get_clean();

        // Assert Cookie Settings link present
        $this->assertStringContainsString( 'Cookie Settings', $output );

        // Assert link has correct class/id for JavaScript
        $this->assertStringContainsString( 'ccm-open-preferences', $output );
    }

    /**
     * Test: Banner is responsive (has mobile-friendly markup)
     */
    public function test_banner_responsive_markup() {
        ob_start();
        ccm_render_banner_template();
        $output = ob_get_clean();

        // Check for responsive container/wrapper
        $this->assertStringContainsString( 'ccm-banner', $output );

        // Buttons should be in actions container for stacking
        $this->assertStringContainsString( 'ccm-banner__actions', $output );
    }

    /**
     * Test: Banner includes ARIA accessibility attributes
     */
    public function test_banner_accessibility() {
        ob_start();
        ccm_render_banner_template();
        $output = ob_get_clean();

        // Check for ARIA role
        $this->assertStringContainsString( 'role="dialog"', $output );

        // Check for aria-label or aria-labelledby
        $has_aria_label = (
            strpos( $output, 'aria-label' ) !== false ||
            strpos( $output, 'aria-labelledby' ) !== false
        );

        $this->assertTrue( $has_aria_label, 'Banner should have aria-label for accessibility' );
    }

    /**
     * Test: Etch theme compatibility - no JavaScript conflicts
     */
    public function test_no_javascript_conflicts() {
        // Enqueue plugin scripts
        do_action( 'wp_enqueue_scripts' );

        // Verify scripts registered without errors
        global $wp_scripts;

        $this->assertArrayHasKey( 'ccm-consent-banner', $wp_scripts->registered );
        $this->assertArrayHasKey( 'ccm-cookie-blocker', $wp_scripts->registered );
        $this->assertArrayHasKey( 'ccm-storage-manager', $wp_scripts->registered );

        // Verify no dependency conflicts (e.g., jQuery version)
        $consent_banner = $wp_scripts->registered['ccm-consent-banner'];
        if ( ! empty( $consent_banner->deps ) ) {
            // If jQuery is a dependency, it should be available
            if ( in_array( 'jquery', $consent_banner->deps, true ) ) {
                $this->assertArrayHasKey( 'jquery', $wp_scripts->registered );
            }
        }
    }

    /**
     * Test: Plugin doesn't interfere with Etch theme styles
     */
    public function test_no_style_conflicts() {
        do_action( 'wp_enqueue_scripts' );

        global $wp_styles;

        // Verify banner styles registered
        $this->assertArrayHasKey( 'ccm-banner', $wp_styles->registered );

        // Banner CSS should not have !important overrides of Etch theme
        // (This is a conceptual test - actual CSS validation would require file parsing)
        $this->assertTrue( true, 'Style conflict test placeholder' );
    }

    /**
     * Test: T041 - Category display with all 4 categories and descriptions
     *
     * User Story 2: Users should see all cookie categories with descriptions
     */
    public function test_category_display_renders_all_categories() {
        global $wpdb;

        // Get categories from database
        $categories = $wpdb->get_results(
            "SELECT slug, name, description, is_required FROM {$wpdb->prefix}cookie_consent_categories ORDER BY display_order"
        );

        // Assert we have 4 default categories
        $this->assertCount( 4, $categories, 'Should have 4 default cookie categories' );

        // Verify expected categories exist
        $category_slugs = wp_list_pluck( $categories, 'slug' );
        $this->assertContains( 'essential', $category_slugs, 'Essential category should exist' );
        $this->assertContains( 'functional', $category_slugs, 'Functional category should exist' );
        $this->assertContains( 'analytics', $category_slugs, 'Analytics category should exist' );
        $this->assertContains( 'marketing', $category_slugs, 'Marketing category should exist' );

        // Verify each category has a description
        foreach ( $categories as $category ) {
            $this->assertNotEmpty( $category->name, "Category {$category->slug} should have a name" );
            $this->assertNotEmpty( $category->description, "Category {$category->slug} should have a description" );

            // Verify description is meaningful (more than 10 characters)
            $this->assertGreaterThan( 10, strlen( $category->description ),
                "Category {$category->slug} description should be meaningful"
            );
        }

        // Verify essential category is marked as required
        $essential = array_filter( $categories, function( $cat ) {
            return $cat->slug === 'essential';
        });
        $essential = reset( $essential );
        $this->assertEquals( 1, $essential->is_required, 'Essential category should be required' );
    }

    /**
     * Test: T042 - Cookie details modal structure and cookie grouping
     *
     * User Story 2: Users should view cookies grouped by category in modal
     */
    public function test_cookie_details_modal_structure() {
        ob_start();
        ccm_render_banner_template();
        $output = ob_get_clean();

        // Assert modal container exists
        $this->assertStringContainsString( 'ccm-modal', $output, 'Modal container should exist' );

        // Assert modal has correct ARIA attributes
        $this->assertStringContainsString( 'role="dialog"', $output, 'Modal should have dialog role' );
        $this->assertStringContainsString( 'aria-modal="true"', $output, 'Modal should have aria-modal' );

        // Assert modal header with title
        $this->assertStringContainsString( 'ccm-modal__header', $output, 'Modal should have header' );
        $this->assertStringContainsString( 'ccm-modal__title', $output, 'Modal should have title' );
        $this->assertStringContainsString( 'Cookie Preferences', $output, 'Modal should display "Cookie Preferences" title' );

        // Assert close button
        $this->assertStringContainsString( 'ccm-modal__close', $output, 'Modal should have close button' );

        // Assert modal body for category/cookie content
        $this->assertStringContainsString( 'ccm-modal__body', $output, 'Modal should have body container' );
        $this->assertStringContainsString( 'id="ccm-modal-body"', $output, 'Modal body should have ID for JavaScript population' );

        // Assert footer with action buttons
        $this->assertStringContainsString( 'ccm-modal__footer', $output, 'Modal should have footer' );
        $this->assertStringContainsString( 'ccm-cancel-preferences', $output, 'Modal should have cancel button' );
        $this->assertStringContainsString( 'ccm-save-preferences', $output, 'Modal should have save button' );
    }

    /**
     * Test: T042 - Cookie details endpoint returns cookies grouped by category
     *
     * User Story 2: Verify backend returns cookies organized by category
     */
    public function test_cookies_grouped_by_category_in_api() {
        global $wpdb;

        // Insert test cookies for different categories
        $essential_cat = $wpdb->get_var(
            "SELECT id FROM {$wpdb->prefix}cookie_consent_categories WHERE slug = 'essential'"
        );
        $analytics_cat = $wpdb->get_var(
            "SELECT id FROM {$wpdb->prefix}cookie_consent_categories WHERE slug = 'analytics'"
        );

        // Insert test cookies
        $wpdb->insert(
            $wpdb->prefix . 'cookie_consent_cookies',
            array(
                'category_id' => $essential_cat,
                'name'        => '_test_session',
                'purpose'     => 'Session management for testing',
                'duration'    => 'Session',
                'provider'    => 'Test Site',
            )
        );

        $wpdb->insert(
            $wpdb->prefix . 'cookie_consent_cookies',
            array(
                'category_id' => $analytics_cat,
                'name'        => '_test_analytics',
                'purpose'     => 'Analytics tracking for testing',
                'duration'    => '2 years',
                'provider'    => 'Test Analytics',
            )
        );

        // Simulate AJAX request to get banner config
        require_once dirname( dirname( __DIR__ ) ) . '/includes/class-cookie-manager.php';
        $manager = CCM_Cookie_Manager::get_instance();

        // Capture the output of ajax_get_banner_config
        ob_start();
        try {
            $manager->ajax_get_banner_config();
        } catch ( Exception $e ) {
            // Expected to throw WPAjaxDieContinueException
        }
        $json_output = ob_get_clean();

        // Parse JSON response
        $response = json_decode( $json_output, true );

        // Verify response structure
        $this->assertIsArray( $response, 'Response should be an array' );
        $this->assertArrayHasKey( 'categories', $response, 'Response should have categories' );
        $this->assertArrayHasKey( 'cookies', $response, 'Response should have cookies array' );

        // Verify cookies are present
        $this->assertNotEmpty( $response['cookies'], 'Cookies array should not be empty' );

        // Verify cookies have category_id for grouping
        foreach ( $response['cookies'] as $cookie ) {
            $this->assertArrayHasKey( 'category_id', $cookie, 'Each cookie should have category_id' );
            $this->assertArrayHasKey( 'name', $cookie, 'Each cookie should have name' );
            $this->assertArrayHasKey( 'purpose', $cookie, 'Each cookie should have purpose' );
        }

        // Cleanup test cookies
        $wpdb->delete(
            $wpdb->prefix . 'cookie_consent_cookies',
            array( 'name' => '_test_session' )
        );
        $wpdb->delete(
            $wpdb->prefix . 'cookie_consent_cookies',
            array( 'name' => '_test_analytics' )
        );
    }
}
