<?php
/**
 * Integration tests for cookie blocking functionality
 *
 * Tests script wrapper blocking (type="text/plain" before consent)
 *
 * @package Cookie_Consent_Manager
 */

class Test_Cookie_Blocking extends WP_UnitTestCase {

    private $blocker;
    private $storage_handler;

    public function setUp(): void {
        parent::setUp();

        require_once dirname( dirname( __DIR__ ) ) . '/includes/class-cookie-blocker.php';
        require_once dirname( dirname( __DIR__ ) ) . '/includes/class-storage-handler.php';

        $this->blocker = new Cookie_Consent_Manager_Cookie_Blocker();
        $this->storage_handler = new Cookie_Consent_Manager_Storage_Handler();

        // Clear any existing consent
        $this->storage_handler->clear_consent_cookie();
    }

    public function tearDown(): void {
        $this->storage_handler->clear_consent_cookie();
        parent::tearDown();
    }

    /**
     * Test: Scripts are blocked (type="text/plain") before consent
     */
    public function test_scripts_blocked_without_consent() {
        // Simulate script tag for Google Analytics (analytics category)
        $script_tag = '<script type="text/javascript" src="https://www.googletagmanager.com/gtag/js?id=GA-12345"></script>';

        // Run through blocker filter (no consent exists)
        $blocked_tag = $this->blocker->maybe_block_script( $script_tag, 'google-analytics', 'https://www.googletagmanager.com/gtag/js?id=GA-12345' );

        // Assert type changed to text/plain
        $this->assertStringContainsString( 'type="text/plain"', $blocked_tag );

        // Assert data-consent-category attribute added
        $this->assertStringContainsString( 'data-consent-category="analytics"', $blocked_tag );

        // Assert original src preserved
        $this->assertStringContainsString( 'https://www.googletagmanager.com/gtag/js?id=GA-12345', $blocked_tag );
    }

    /**
     * Test: Essential scripts are never blocked
     */
    public function test_essential_scripts_never_blocked() {
        // Simulate script tag for essential functionality
        $script_tag = '<script type="text/javascript" src="/wp-content/themes/etch/assets/js/main.js"></script>';

        // Run through blocker filter
        $result_tag = $this->blocker->maybe_block_script( $script_tag, 'etch-main', '/wp-content/themes/etch/assets/js/main.js' );

        // Assert type remains text/javascript (not blocked)
        $this->assertStringContainsString( 'type="text/javascript"', $result_tag );

        // Assert no data-consent-category added for essential
        $this->assertStringNotContainsString( 'data-consent-category', $result_tag );
    }

    /**
     * Test: Scripts are allowed with valid consent
     */
    public function test_scripts_allowed_with_consent() {
        // Set consent cookie (analytics accepted)
        $_COOKIE['wp_consent_status'] = $this->storage_handler->generate_cookie_hash( ['essential', 'analytics'] );

        // Simulate script tag for Google Analytics
        $script_tag = '<script type="text/javascript" src="https://www.googletagmanager.com/gtag/js?id=GA-12345"></script>';

        // Run through blocker filter (consent exists for analytics)
        $allowed_tag = $this->blocker->maybe_block_script( $script_tag, 'google-analytics', 'https://www.googletagmanager.com/gtag/js?id=GA-12345' );

        // Assert type remains text/javascript
        $this->assertStringContainsString( 'type="text/javascript"', $allowed_tag );

        // Assert data-consent-category still added (for runtime checks)
        $this->assertStringContainsString( 'data-consent-category="analytics"', $allowed_tag );
    }

    /**
     * Test: Marketing scripts blocked when only analytics consented
     */
    public function test_marketing_blocked_with_partial_consent() {
        // Set consent cookie (only analytics, not marketing)
        $_COOKIE['wp_consent_status'] = $this->storage_handler->generate_cookie_hash( ['essential', 'analytics'] );

        // Simulate script tag for Facebook Pixel (marketing category)
        $script_tag = '<script type="text/javascript" src="https://connect.facebook.net/en_US/fbevents.js"></script>';

        // Run through blocker filter
        $blocked_tag = $this->blocker->maybe_block_script( $script_tag, 'facebook-pixel', 'https://connect.facebook.net/en_US/fbevents.js' );

        // Assert type changed to text/plain (blocked)
        $this->assertStringContainsString( 'type="text/plain"', $blocked_tag );

        // Assert data-consent-category="marketing"
        $this->assertStringContainsString( 'data-consent-category="marketing"', $blocked_tag );
    }

    /**
     * Test: Inline scripts are blocked before consent
     */
    public function test_inline_scripts_blocked() {
        // Simulate inline script with Google Analytics code
        $inline_script = "<script type=\"text/javascript\">\ngtag('config', 'GA-12345');\n</script>";

        // Run through blocker filter
        $blocked_script = $this->blocker->maybe_block_script( $inline_script, 'inline-analytics', '' );

        // Assert type changed to text/plain
        $this->assertStringContainsString( 'type="text/plain"', $blocked_script );
    }

    /**
     * Test: document.cookie writes are intercepted
     *
     * Note: This requires JavaScript execution testing (browser-based)
     * For now, verify PHP-side cookie hash generation works
     */
    public function test_cookie_hash_generation() {
        $accepted = ['essential', 'analytics'];
        $hash = $this->storage_handler->generate_cookie_hash( $accepted );

        // Assert hash is MD5 (32 chars)
        $this->assertEquals( 32, strlen( $hash ) );

        // Assert same categories = same hash (idempotent)
        $hash2 = $this->storage_handler->generate_cookie_hash( $accepted );
        $this->assertEquals( $hash, $hash2 );

        // Assert different categories = different hash
        $hash3 = $this->storage_handler->generate_cookie_hash( ['essential'] );
        $this->assertNotEquals( $hash, $hash3 );
    }

    /**
     * Test: Blocked scripts have data-original-type attribute
     */
    public function test_blocked_scripts_preserve_original_type() {
        $script_tag = '<script type="text/javascript" src="https://example.com/script.js"></script>';

        $blocked_tag = $this->blocker->maybe_block_script( $script_tag, 'test-script', 'https://example.com/script.js' );

        // Assert original type preserved in data attribute
        $this->assertStringContainsString( 'data-original-type="text/javascript"', $blocked_tag );
    }

    /**
     * Test: Script blocker handles malformed script tags gracefully
     */
    public function test_malformed_script_tags_handled() {
        $malformed = '<script src="test.js">';  // No type attribute, no closing tag

        $result = $this->blocker->maybe_block_script( $malformed, 'test', 'test.js' );

        // Should not throw error, should add type="text/plain"
        $this->assertNotEmpty( $result );
        $this->assertStringContainsString( 'type="text/plain"', $result );
    }
}
