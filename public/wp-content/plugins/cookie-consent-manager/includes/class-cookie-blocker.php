<?php
/**
 * Cookie Blocker Class
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CCM_Cookie_Blocker class
 */
class CCM_Cookie_Blocker {

    /**
     * Initialize blocker
     */
    public static function init() {
        // Hook into script tag rendering
        add_filter( 'script_loader_tag', array( __CLASS__, 'modify_script_tag' ), 10, 3 );
    }

    /**
     * Modify script tag to add consent attributes
     *
     * @param string $tag Script tag HTML
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @return string Modified script tag
     */
    public static function modify_script_tag( $tag, $handle, $src ) {
        // Stub - will be implemented in Phase 3
        return $tag;
    }

    /**
     * Check if script should be blocked
     *
     * @param string $handle Script handle
     * @return bool True if should be blocked
     */
    public static function should_block_script( $handle ) {
        // Stub - will be implemented in Phase 3
        return false;
    }

    /**
     * Get script category
     *
     * @param string $handle Script handle
     * @return string Category slug
     */
    public static function get_script_category( $handle ) {
        // Stub - will be implemented in Phase 3
        return 'essential';
    }
}

// Initialize blocker
CCM_Cookie_Blocker::init();
