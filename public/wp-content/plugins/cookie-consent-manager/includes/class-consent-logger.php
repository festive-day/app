<?php
/**
 * Consent Logger Class
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CCM_Consent_Logger class
 */
class CCM_Consent_Logger {

    /**
     * Record consent event
     *
     * @param array $event_data Event data
     * @return int|false Event ID or false on failure
     */
    public static function record_event( $event_data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cookie_consent_events';

        // Generate visitor ID if not provided
        if ( empty( $event_data['visitor_id'] ) ) {
            $event_data['visitor_id'] = self::generate_visitor_id();
        }

        // Sanitize server variables
        $ip_address = isset( $event_data['ip_address'] ) 
            ? sanitize_text_field( $event_data['ip_address'] )
            : ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
        
        $user_agent = isset( $event_data['user_agent'] )
            ? sanitize_text_field( $event_data['user_agent'] )
            : ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );

        // Prepare data for insertion
        $data = array(
            'visitor_id'           => $event_data['visitor_id'],
            'event_type'           => $event_data['event_type'],
            'accepted_categories'  => isset( $event_data['accepted_categories'] ) ? wp_json_encode( $event_data['accepted_categories'] ) : null,
            'rejected_categories'  => isset( $event_data['rejected_categories'] ) ? wp_json_encode( $event_data['rejected_categories'] ) : null,
            'consent_version'      => isset( $event_data['consent_version'] ) ? sanitize_text_field( $event_data['consent_version'] ) : CCM_VERSION,
            'ip_address'           => $ip_address,
            'user_agent'           => $user_agent,
        );

        // Insert event
        $result = $wpdb->insert( $table, $data );

        if ( $result ) {
            // Fire WordPress action hooks
            do_action( 'ccm_consent_event_recorded', $wpdb->insert_id, $data );

            switch ( $event_data['event_type'] ) {
                case 'accept_all':
                case 'accept_partial':
                    do_action( 'cookie_consent_given', $data );
                    break;
                case 'modify':
                    do_action( 'cookie_consent_modified', $data );
                    break;
                case 'revoke':
                    do_action( 'cookie_consent_revoked', $data );
                    break;
            }

            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Generate visitor ID hash
     *
     * @return string Hashed visitor ID
     */
    public static function generate_visitor_id() {
        $ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        $salt      = defined( 'AUTH_SALT' ) ? AUTH_SALT : 'ccm_salt';

        return hash( 'sha256', $ip . $user_agent . $salt );
    }

    /**
     * Get consent history for visitor
     *
     * @param string $visitor_id Visitor ID
     * @return array Consent history
     */
    public static function get_visitor_history( $visitor_id ) {
        // Stub - will be implemented in Phase 6
        return array();
    }
}
