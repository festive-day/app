<?php
/**
 * Test Storage Handler
 *
 * @package Cookie_Consent_Manager
 */

class Test_Storage_Handler extends WP_UnitTestCase {

    /**
     * Test cookie hash generation
     */
    public function test_generate_cookie_hash() {
        $consent_data = array(
            'acceptedCategories' => array( 'essential', 'analytics' ),
        );

        $hash = CCM_Storage_Handler::generate_cookie_hash( $consent_data );

        $this->assertNotEmpty( $hash, 'Hash should not be empty' );
        $this->assertEquals( 32, strlen( $hash ), 'MD5 hash should be 32 chars' );
    }

    /**
     * Test cookie hash is deterministic
     */
    public function test_cookie_hash_deterministic() {
        $consent_data = array(
            'acceptedCategories' => array( 'essential', 'functional', 'analytics' ),
        );

        $hash1 = CCM_Storage_Handler::generate_cookie_hash( $consent_data );
        $hash2 = CCM_Storage_Handler::generate_cookie_hash( $consent_data );

        $this->assertEquals( $hash1, $hash2, 'Same categories should produce same hash' );
    }

    /**
     * Test cookie hash is order-independent
     */
    public function test_cookie_hash_order_independent() {
        $consent_data_1 = array(
            'acceptedCategories' => array( 'analytics', 'essential', 'functional' ),
        );

        $consent_data_2 = array(
            'acceptedCategories' => array( 'essential', 'functional', 'analytics' ),
        );

        $hash1 = CCM_Storage_Handler::generate_cookie_hash( $consent_data_1 );
        $hash2 = CCM_Storage_Handler::generate_cookie_hash( $consent_data_2 );

        $this->assertEquals( $hash1, $hash2, 'Category order should not affect hash' );
    }

    /**
     * Test validate consent structure - valid
     */
    public function test_validate_consent_structure_valid() {
        $consent_data = array(
            'version'            => '1.0.0',
            'timestamp'          => time(),
            'consentGiven'       => true,
            'acceptedCategories' => array( 'essential', 'analytics' ),
        );

        $is_valid = CCM_Storage_Handler::validate_consent_structure( $consent_data );

        $this->assertTrue( $is_valid, 'Valid consent structure should pass validation' );
    }

    /**
     * Test validate consent structure - missing version
     */
    public function test_validate_consent_structure_missing_version() {
        $consent_data = array(
            'timestamp'          => time(),
            'consentGiven'       => true,
            'acceptedCategories' => array( 'essential' ),
        );

        $is_valid = CCM_Storage_Handler::validate_consent_structure( $consent_data );

        $this->assertFalse( $is_valid, 'Missing version should fail validation' );
    }

    /**
     * Test validate consent structure - invalid timestamp
     */
    public function test_validate_consent_structure_invalid_timestamp() {
        $consent_data = array(
            'version'            => '1.0.0',
            'timestamp'          => 'invalid',
            'consentGiven'       => true,
            'acceptedCategories' => array( 'essential' ),
        );

        $is_valid = CCM_Storage_Handler::validate_consent_structure( $consent_data );

        $this->assertFalse( $is_valid, 'Invalid timestamp should fail validation' );
    }

    /**
     * Test validate consent structure - non-array categories
     */
    public function test_validate_consent_structure_invalid_categories() {
        $consent_data = array(
            'version'            => '1.0.0',
            'timestamp'          => time(),
            'consentGiven'       => true,
            'acceptedCategories' => 'essential',
        );

        $is_valid = CCM_Storage_Handler::validate_consent_structure( $consent_data );

        $this->assertFalse( $is_valid, 'Non-array categories should fail validation' );
    }

    /**
     * Test consent expiration - not expired
     */
    public function test_consent_not_expired() {
        $timestamp = time() - ( 6 * 30 * 24 * 60 * 60 ); // 6 months ago

        $is_expired = CCM_Storage_Handler::is_consent_expired( $timestamp );

        $this->assertFalse( $is_expired, '6-month-old consent should not be expired' );
    }

    /**
     * Test consent expiration - expired
     */
    public function test_consent_expired() {
        $timestamp = time() - ( 13 * 30 * 24 * 60 * 60 ); // 13 months ago

        $is_expired = CCM_Storage_Handler::is_consent_expired( $timestamp );

        $this->assertTrue( $is_expired, '13-month-old consent should be expired' );
    }

    /**
     * Test consent expiration - exactly 12 months
     */
    public function test_consent_expired_exactly_12_months() {
        $timestamp = time() - ( 365 * 24 * 60 * 60 ); // Exactly 12 months

        $is_expired = CCM_Storage_Handler::is_consent_expired( $timestamp );

        $this->assertFalse( $is_expired, 'Exactly 12-month-old consent should not be expired' );
    }

    /**
     * Test version mismatch - no mismatch
     */
    public function test_version_no_mismatch() {
        $has_mismatch = CCM_Storage_Handler::has_version_mismatch( CCM_VERSION );

        $this->assertFalse( $has_mismatch, 'Same version should not have mismatch' );
    }

    /**
     * Test version mismatch - mismatch detected
     */
    public function test_version_mismatch() {
        $has_mismatch = CCM_Storage_Handler::has_version_mismatch( '0.9.0' );

        $this->assertTrue( $has_mismatch, 'Different version should have mismatch' );
    }

    /**
     * Test consent expiration - 13 months (re-prompt scenario)
     *
     * T091: Test consent expiration per quickstart.md Scenario 7
     * Simulate 13 months passing, verify consent expires and re-prompt occurs
     */
    public function test_consent_expiration_13_months() {
        // Simulate consent given 13 months ago
        $thirteen_months_ago = time() - ( 13 * 30 * 24 * 60 * 60 );

        $is_expired = CCM_Storage_Handler::is_consent_expired( $thirteen_months_ago );

        $this->assertTrue( $is_expired, '13-month-old consent should be expired (triggers re-prompt)' );
    }

    /**
     * Test consent expiration - 11 months (still valid)
     *
     * T091: Verify consent persists for 12 months (SC-003)
     */
    public function test_consent_expiration_11_months() {
        // Simulate consent given 11 months ago
        $eleven_months_ago = time() - ( 11 * 30 * 24 * 60 * 60 );

        $is_expired = CCM_Storage_Handler::is_consent_expired( $eleven_months_ago );

        $this->assertFalse( $is_expired, '11-month-old consent should still be valid (within 12-month window)' );
    }

    /**
     * Test consent expiration - exactly 12 months + 1 day (expired)
     */
    public function test_consent_expiration_12_months_plus_one_day() {
        // Simulate consent given 12 months and 1 day ago
        $twelve_months_one_day_ago = time() - ( ( 365 * 24 * 60 * 60 ) + ( 24 * 60 * 60 ) );

        $is_expired = CCM_Storage_Handler::is_consent_expired( $twelve_months_one_day_ago );

        $this->assertTrue( $is_expired, 'Consent older than 12 months should be expired' );
    }
}
