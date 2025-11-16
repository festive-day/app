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
class Cookie_Consent_Manager_Cookie_Blocker {

    /**
     * Script registry mapping handles to categories
     *
     * @var array
     */
    private static $script_registry = array();

    /**
     * Initialize blocker
     */
    public static function init() {
        // Hook into script tag rendering
        add_filter( 'script_loader_tag', array( __CLASS__, 'modify_script_tag' ), 10, 3 );

        // Register known third-party scripts
        self::register_known_scripts();
    }

    /**
     * Register known third-party scripts with their categories
     */
    private static function register_known_scripts() {
        // Analytics
        self::$script_registry['google-analytics'] = 'analytics';
        self::$script_registry['gtag'] = 'analytics';
        self::$script_registry['ga'] = 'analytics';
        self::$script_registry['google-tag-manager'] = 'analytics';

        // Marketing
        self::$script_registry['facebook-pixel'] = 'marketing';
        self::$script_registry['fb-pixel'] = 'marketing';
        self::$script_registry['facebook-jssdk'] = 'marketing';

        // Functional (examples)
        self::$script_registry['youtube'] = 'functional';
        self::$script_registry['vimeo'] = 'functional';

        // Allow filtering
        self::$script_registry = apply_filters( 'ccm_script_registry', self::$script_registry );
    }

    /**
     * Modify script tag to add consent attributes and block if needed
     *
     * @param string $tag Script tag HTML
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @return string Modified script tag
     */
    public static function modify_script_tag( $tag, $handle, $src ) {
        // Get script category
        $category = self::get_script_category( $handle, $src );

        // Essential scripts are never blocked
        if ( $category === 'essential' ) {
            return $tag;
        }

        // Check if user has consented to this category
        $has_consent = self::check_consent( $category );

        // Add data-consent-category attribute
        $tag = str_replace( '<script', '<script data-consent-category="' . esc_attr( $category ) . '"', $tag );

        // Block script if no consent
        if ( ! $has_consent ) {
            // Save original type
            if ( strpos( $tag, 'type=' ) !== false ) {
                $tag = preg_replace( '/type=["\']([^"\']+)["\']/', 'type="text/plain" data-original-type="$1"', $tag );
            } else {
                $tag = str_replace( '<script', '<script type="text/plain" data-original-type="text/javascript"', $tag );
            }
        }

        return $tag;
    }

    /**
     * Check if user has consented to a category
     *
     * @param string $category Category slug
     * @return bool True if consented
     */
    private static function check_consent( $category ) {
        // Check for consent cookie
        if ( ! isset( $_COOKIE['wp_consent_status'] ) ) {
            return false;
        }

        // Get accepted categories from localStorage (via cookie hash)
        // For now, we rely on client-side JavaScript to handle this
        // PHP-side check is basic: if cookie exists, assume some consent given
        // More accurate check would require storing categories in cookie itself

        // In production, you'd decode the hash or store categories differently
        // For this implementation, we'll be permissive on server-side
        // and rely on JavaScript blocker for accuracy

        return false; // Conservative: block on server, JavaScript will activate
    }

    /**
     * Get script category based on handle and source URL
     *
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @return string Category slug (essential, functional, analytics, marketing)
     */
    public static function get_script_category( $handle, $src = '' ) {
        // Check registry first (exact match)
        if ( isset( self::$script_registry[ $handle ] ) ) {
            return self::$script_registry[ $handle ];
        }

        // Check handle patterns
        if ( self::is_analytics_script( $handle, $src ) ) {
            return 'analytics';
        }

        if ( self::is_marketing_script( $handle, $src ) ) {
            return 'marketing';
        }

        if ( self::is_functional_script( $handle, $src ) ) {
            return 'functional';
        }

        // Default to essential (safe default - won't block)
        return 'essential';
    }

    /**
     * Check if script is analytics
     *
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @return bool True if analytics
     */
    private static function is_analytics_script( $handle, $src ) {
        $patterns = array(
            'google-analytics',
            'gtag',
            'ga.js',
            'analytics.js',
            'googletagmanager',
            'matomo',
            'piwik',
        );

        foreach ( $patterns as $pattern ) {
            if ( stripos( $handle, $pattern ) !== false || stripos( $src, $pattern ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if script is marketing
     *
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @return bool True if marketing
     */
    private static function is_marketing_script( $handle, $src ) {
        $patterns = array(
            'facebook',
            'fbevents',
            'fbq',
            'twitter',
            'linkedin',
            'pinterest',
            'tiktok',
        );

        foreach ( $patterns as $pattern ) {
            if ( stripos( $handle, $pattern ) !== false || stripos( $src, $pattern ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if script is functional
     *
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @return bool True if functional
     */
    private static function is_functional_script( $handle, $src ) {
        $patterns = array(
            'youtube',
            'vimeo',
            'maps',
            'recaptcha',
        );

        foreach ( $patterns as $pattern ) {
            if ( stripos( $handle, $pattern ) !== false || stripos( $src, $pattern ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper for integration tests
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script src
     * @return string Modified tag
     */
    public function maybe_block_script( $tag, $handle, $src ) {
        return self::modify_script_tag( $tag, $handle, $src );
    }
}

// Initialize blocker
Cookie_Consent_Manager_Cookie_Blocker::init();
