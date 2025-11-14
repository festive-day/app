<?php
/**
 * Storage Handler Class
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CCM_Storage_Handler class
 */
class CCM_Storage_Handler {

    /**
     * Generate cookie hash from consent data
     *
     * @param array $consent_data Consent data
     * @return string Cookie hash
     */
    public static function generate_cookie_hash( $consent_data ) {
        if ( empty( $consent_data['acceptedCategories'] ) || ! is_array( $consent_data['acceptedCategories'] ) ) {
            return '';
        }

        $categories = $consent_data['acceptedCategories'];
        sort( $categories );

        return md5( implode( ',', $categories ) );
    }

    /**
     * Validate consent object structure
     *
     * @param array $consent_data Consent data
     * @return bool True if valid
     */
    public static function validate_consent_structure( $consent_data ) {
        $required_keys = array( 'version', 'timestamp', 'consentGiven', 'acceptedCategories' );

        foreach ( $required_keys as $key ) {
            if ( ! isset( $consent_data[ $key ] ) ) {
                return false;
            }
        }

        if ( ! is_array( $consent_data['acceptedCategories'] ) ) {
            return false;
        }

        if ( ! is_numeric( $consent_data['timestamp'] ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check if consent has expired (12 months)
     *
     * @param int $timestamp Consent timestamp
     * @return bool True if expired
     */
    public static function is_consent_expired( $timestamp ) {
        $twelve_months_in_seconds = 365 * 24 * 60 * 60;
        $current_time             = time();

        return ( $current_time - $timestamp ) > $twelve_months_in_seconds;
    }

    /**
     * Check for version mismatch
     *
     * @param string $consent_version Consent version
     * @return bool True if version mismatch
     */
    public static function has_version_mismatch( $consent_version ) {
        return $consent_version !== CCM_VERSION;
    }
}
