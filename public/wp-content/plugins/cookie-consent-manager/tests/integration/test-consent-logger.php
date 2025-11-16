<?php
/**
 * Test Consent Logger
 *
 * @package Cookie_Consent_Manager
 */

class Test_Consent_Logger extends WP_UnitTestCase {

    /**
     * Setup test environment
     */
    public function setUp() {
        parent::setUp();

        // Clear events table before each test
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->prefix}cookie_consent_events" );
    }

    /**
     * Test visitor ID generation
     */
    public function test_generate_visitor_id() {
        $visitor_id = CCM_Consent_Logger::generate_visitor_id();

        $this->assertNotEmpty( $visitor_id, 'Visitor ID should not be empty' );
        $this->assertEquals( 64, strlen( $visitor_id ), 'Visitor ID should be 64 chars (SHA256)' );
        $this->assertTrue( ctype_xdigit( $visitor_id ), 'Visitor ID should be hexadecimal' );
    }

    /**
     * Test visitor ID is deterministic
     */
    public function test_visitor_id_deterministic() {
        $visitor_id_1 = CCM_Consent_Logger::generate_visitor_id();
        $visitor_id_2 = CCM_Consent_Logger::generate_visitor_id();

        $this->assertEquals( $visitor_id_1, $visitor_id_2, 'Same request should generate same visitor ID' );
    }

    /**
     * Test record accept_all event
     */
    public function test_record_accept_all_event() {
        $event_id = CCM_Consent_Logger::record_event(
            array(
                'event_type'          => 'accept_all',
                'accepted_categories' => array( 'essential', 'functional', 'analytics', 'marketing' ),
                'rejected_categories' => array(),
            )
        );

        $this->assertNotFalse( $event_id, 'Event should be recorded successfully' );
        $this->assertGreaterThan( 0, $event_id, 'Event ID should be positive integer' );

        global $wpdb;
        $event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_events WHERE id = %d",
                $event_id
            )
        );

        $this->assertEquals( 'accept_all', $event->event_type );
        $this->assertNotEmpty( $event->visitor_id );
        $this->assertEquals( CCM_VERSION, $event->consent_version );
    }

    /**
     * Test record reject_all event
     */
    public function test_record_reject_all_event() {
        $event_id = CCM_Consent_Logger::record_event(
            array(
                'event_type'          => 'reject_all',
                'accepted_categories' => array( 'essential' ),
                'rejected_categories' => array( 'functional', 'analytics', 'marketing' ),
            )
        );

        $this->assertNotFalse( $event_id, 'Reject event should be recorded' );

        global $wpdb;
        $event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_events WHERE id = %d",
                $event_id
            )
        );

        $this->assertEquals( 'reject_all', $event->event_type );

        $accepted = json_decode( $event->accepted_categories, true );
        $this->assertEquals( array( 'essential' ), $accepted );
    }

    /**
     * Test record partial consent event
     */
    public function test_record_partial_consent() {
        $event_id = CCM_Consent_Logger::record_event(
            array(
                'event_type'          => 'accept_partial',
                'accepted_categories' => array( 'essential', 'functional' ),
                'rejected_categories' => array( 'analytics', 'marketing' ),
            )
        );

        $this->assertNotFalse( $event_id, 'Partial consent should be recorded' );

        global $wpdb;
        $event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_events WHERE id = %d",
                $event_id
            )
        );

        $accepted = json_decode( $event->accepted_categories, true );
        $rejected = json_decode( $event->rejected_categories, true );

        $this->assertCount( 2, $accepted );
        $this->assertCount( 2, $rejected );
    }

    /**
     * Test event timestamp is set
     */
    public function test_event_timestamp() {
        $before = time();

        $event_id = CCM_Consent_Logger::record_event(
            array(
                'event_type'          => 'accept_all',
                'accepted_categories' => array( 'essential' ),
            )
        );

        $after = time();

        global $wpdb;
        $event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_events WHERE id = %d",
                $event_id
            )
        );

        $event_time = strtotime( $event->event_timestamp );

        $this->assertGreaterThanOrEqual( $before, $event_time );
        $this->assertLessThanOrEqual( $after, $event_time );
    }

    /**
     * Test IP address is recorded
     */
    public function test_ip_address_recorded() {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';

        $event_id = CCM_Consent_Logger::record_event(
            array(
                'event_type'          => 'accept_all',
                'accepted_categories' => array( 'essential' ),
            )
        );

        global $wpdb;
        $event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_events WHERE id = %d",
                $event_id
            )
        );

        $this->assertEquals( '192.168.1.1', $event->ip_address );
    }

    /**
     * Test: Preference modification event is logged with correct categories
     *
     * T051: Verify modify event logged with correct categories when preferences change
     */
    public function test_preference_modification_logged() {
        // First, record an initial consent (accept_all)
        $initial_event_id = CCM_Consent_Logger::record_event(
            array(
                'event_type'          => 'accept_all',
                'accepted_categories' => array( 'essential', 'functional', 'analytics', 'marketing' ),
                'rejected_categories' => array(),
            )
        );

        $this->assertNotFalse( $initial_event_id, 'Initial consent should be recorded' );

        // Now simulate preference modification: user changes from accept_all to reject analytics and marketing
        $modify_event_id = CCM_Consent_Logger::record_event(
            array(
                'event_type'          => 'modify',
                'accepted_categories' => array( 'essential', 'functional' ),
                'rejected_categories' => array( 'analytics', 'marketing' ),
            )
        );

        $this->assertNotFalse( $modify_event_id, 'Modify event should be recorded' );
        $this->assertGreaterThan( $initial_event_id, $modify_event_id, 'Modify event should have higher ID than initial event' );

        global $wpdb;
        $modify_event = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cookie_consent_events WHERE id = %d",
                $modify_event_id
            )
        );

        // Verify event type is 'modify'
        $this->assertEquals( 'modify', $modify_event->event_type, 'Event type should be modify' );

        // Verify accepted categories are correct
        $accepted = json_decode( $modify_event->accepted_categories, true );
        $this->assertIsArray( $accepted, 'Accepted categories should be an array' );
        $this->assertCount( 2, $accepted, 'Should have 2 accepted categories' );
        $this->assertContains( 'essential', $accepted, 'Essential should be accepted' );
        $this->assertContains( 'functional', $accepted, 'Functional should be accepted' );
        $this->assertNotContains( 'analytics', $accepted, 'Analytics should not be in accepted' );
        $this->assertNotContains( 'marketing', $accepted, 'Marketing should not be in accepted' );

        // Verify rejected categories are correct
        $rejected = json_decode( $modify_event->rejected_categories, true );
        $this->assertIsArray( $rejected, 'Rejected categories should be an array' );
        $this->assertCount( 2, $rejected, 'Should have 2 rejected categories' );
        $this->assertContains( 'analytics', $rejected, 'Analytics should be rejected' );
        $this->assertContains( 'marketing', $rejected, 'Marketing should be rejected' );
        $this->assertNotContains( 'essential', $rejected, 'Essential should not be rejected' );
        $this->assertNotContains( 'functional', $rejected, 'Functional should not be rejected' );

        // Verify consent version is set
        $this->assertEquals( CCM_VERSION, $modify_event->consent_version, 'Consent version should match current version' );

        // Verify visitor ID is set
        $this->assertNotEmpty( $modify_event->visitor_id, 'Visitor ID should be set' );
        $this->assertEquals( 64, strlen( $modify_event->visitor_id ), 'Visitor ID should be 64 chars (SHA256)' );
    }
}
