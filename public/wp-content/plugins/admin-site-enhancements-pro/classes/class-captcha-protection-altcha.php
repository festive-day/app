<?php

namespace ASENHA\Classes;

use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

/**
 * Class for CAPTCHA Protection module using ALTCHA. 
 * Using code modified from the official ALTCHA plugin: https://wordpress.org/plugins/altcha-spam-protection/
 *
 * @link https://plugins.trac.wordpress.org/browser/altcha-spam-protection/tags/1.16.0/includes/core.php
 * @since 7.7.0
 */
class CAPTCHA_Protection_ALTCHA {

    public static $mode = 'captcha';

    public static $html_escape_allowed_tags = array(
        'altcha-widget' => array(
            'challengeurl' => array(),
            'strings' => array(),
            'auto' => array(),
            'floating' => array(),
            'delay' => array(),
            'hidelogo' => array(),
            'hidefooter' => array(),
            'blockspam' => array(),
            'spamfilter' => array(),
            'name' => array(),
        ),
        'div' => array(
            'class' => array(),
            'style' => array(),
        ),
        'input' => array(
            'class' => array(),
            'id' => array(),
            'name' => array(),
            'type' => array(),
            'value' => array(),
            'style' => array(),
        ),
        'noscript' => array(),
    );

    public $spamfilter_result = null;

    public function init_altcha() {
        $namespace = 'altcha/v1';
        $route     = 'challenge';
        register_rest_route( $namespace, $route, array(
            'methods'               => WP_REST_Server::READABLE,
            'callback'              => array( $this, 'altcha_generate_challenge_endpoint' ),
            'permission_callback'   => '__return_true'
        ) );
    }
    
    /**
     * This WP REST API endpoint will be referenced by altcha.js and added to the ALTCHA widget for processing back in PHP
     * e.g. https://www.site.com/wp-json/altcha/v1/challenge
     */
    public function altcha_generate_challenge_endpoint() {
        $resp = new WP_REST_Response( $this->generate_challenge() );
        $resp->set_headers( array( 'Cache-Control' => 'no-cache, no-store, max-age=0' ) );
        return $resp;
    }

    public function generate_challenge( $hmac_key = null, $complexity = null, $expires = null ) {
        $options = get_option( 'admin_site_enhancements', array() );

        if ( $hmac_key === null ) {
            $hmac_key = isset( $options['altcha_secret_key'] ) ? $options['altcha_secret_key'] : $this->random_secret();
        }
        if ( $complexity === null ) {
            $complexity = isset( $options['altcha_complexity'] ) ? $options['altcha_complexity'] : 'low';
        }
        if ( $expires === null ) {
            $expires = isset( $options['altcha_expiration'] ) ? intval( $options['altcha_expiration'] ): 3600;
        }

        $salt = $this->random_secret();

        if ( $expires > 0 ) {
            $salt = $salt . '?' . http_build_query( array (
                'expires' => time() + $expires
            ) );
        }

        switch ( $complexity ) {
            case 'low':
                $min_secret = 100;
                $max_secret = 1000;
                break;

            case 'medium':
                $min_secret = 1000;
                $max_secret = 20000;
                break;

            case 'high':
                $min_secret = 10000;
                $max_secret = 100000;
                break;

            default:
                $min_secret = 100;
                $max_secret = 10000;
        }

        $secret_number = random_int( $min_secret, $max_secret );
        $challenge = hash( 'sha256', $salt . $secret_number );
        $signature = hash_hmac( 'sha256', $challenge, $hmac_key );

        $response = array(
            'algorithm' => 'SHA-256',
            'challenge' => $challenge,
            'maxnumber' => $max_secret,
            'salt'      => $salt,
            'signature' => $signature
        );

        return $response;
    }
    
    public function random_secret() {
        return bin2hex( random_bytes( 12 ) );
    }
    
    /**
     * Load ALTCHA main script async and deferred
     */
    public function altcha_script_tags( $tag, $handle, $src ) {
        if ( 'asenha-altcha-main' == $handle ) {
            return str_replace( '<script', '<script async defer type="module"', $tag );
        }
        return $tag;
    }
    
    // ========== Add ALTCHA to various default WordPress forms ========= //
    
    public function add_altcha_to_login_form() {
        $this->altcha_wordpress_render_widget( self::$mode );
    }
    
    public function add_altcha_to_password_reset_form() {
        $this->altcha_wordpress_render_widget( self::$mode );
    }
    
    public function add_altcha_to_registration_form() {
        $this->altcha_wordpress_render_widget( self::$mode, 'altcha_register' );
    }
    
    public function add_altcha_to_comment_form() {
        $this->altcha_wordpress_render_widget( self::$mode );
    }

    // ========== Add ALTCHA to various default WooCommerce forms ========= //
    
    public function add_altcha_to_woo_login_form() {
        $this->altcha_wordpress_render_widget( self::$mode );
    }
    
    public function add_altcha_to_woo_lostpassword_form() {
        $this->altcha_wordpress_render_widget( self::$mode );        
    }
    
    public function add_altcha_to_woo_registration_form() {
        $this->altcha_wordpress_render_widget( self::$mode );        
    }

    // public function add_altcha_to_woo_checkout_form() {
    //     $this->altcha_wordpress_render_widget( self::$mode );        
    // }

    // ========== Render ALTCHA widget ========= //

    public function altcha_wordpress_render_widget( $mode, $name = null ) {
        echo wp_kses( $this->render_widget( $mode, true, null, $name ), self::$html_escape_allowed_tags );
    }

    public function render_widget( $mode, $wrap = false, $language = null, $name = null ) {
        $attrs = $this->get_widget_attrs( $mode, $language, $name );

        $attributes = join( ' ', array_map( function ( $key ) use ( $attrs ) {
            if ( is_bool( $attrs[$key] ) ) {
                return $attrs[$key] ? $key : '';
            }

            return esc_attr( $key ) . '="' . esc_attr( $attrs[$key] ) . '"';
        }, array_keys( $attrs ) ) );

        $html =
        "<altcha-widget "
        . $attributes
        . "></altcha-widget>"
        . "<noscript>"
        . "<div class=\"altcha-no-javascript\">This form requires JavaScript!</div>"
        . "</noscript>";

        if ( $wrap ) {
            return '<div class="altcha-widget-wrap">' . $html . '</div>';
        }

        return $html;
    }

    public function get_widget_attrs( $mode, $language = null, $name = null ) {
        $options = get_option( 'admin_site_enhancements', array() );
        
        $challengeurl = $this->get_challengeurl();
        $strings = wp_json_encode( $this->get_translations( $language ) );

        $auto = isset( $options['altcha_auto_verification'] ) ? $options['altcha_auto_verification'] : '';
        $widget_type = isset( $options['altcha_widget'] ) ? $options['altcha_widget'] : 'checkbox';
        $delay = isset( $options['altcha_enable_delay'] ) ? $options['altcha_enable_delay'] : false;
        $hidelogo = isset( $options['altcha_hide_logo'] ) ? $options['altcha_hide_logo'] : false;
        $hidefooter = isset( $options['altcha_hide_byline'] ) ? $options['altcha_hide_byline'] : false;

        $blockspam = false;

        $attrs = array(
            'challengeurl' => $challengeurl,
            'strings' => $strings,
        );

        if ( $name ) {
            $attrs['name'] = $name;
        }

        if ( $auto ) {
            $attrs['auto'] = $auto;
        }

        if ( 'invisible' == $widget_type ) {
            $attrs['floating'] = 'auto';
        }

        if ( $delay ) {
            $attrs['delay'] = '1500';
        }

        if ( $hidelogo ) {
            $attrs['hidelogo'] = '1';
        }

        if ( $hidefooter ) {
            $attrs['hidefooter'] = '1';
        }

        if ( $blockspam ) {
            $attrs['blockspam'] = '1';
        }

        if ( $mode === "captcha_spamfilter" ) {
            $attrs['spamfilter'] = '1';
        }
        
        return $attrs;
    }
    
    public function get_challengeurl() {
        return get_rest_url(null, "/altcha/v1/challenge");
    }

    public function get_translations( $language = null ) {
        $original_language = null;

        if ( $language !== null ) {
            $original_language = get_locale();
            switch_to_locale($language);
        }

        $ALTCHA_WEBSITE = 'https://altcha.org/';

        // Get custom labels
        $options = get_option( 'admin_site_enhancements', array() );
        $altcha_checkbox_label = isset( $options['altcha_checkbox_label'] ) ? $options['altcha_checkbox_label'] : '';
        $altcha_verifying_text = isset( $options['altcha_verifying_text'] ) ? $options['altcha_verifying_text'] : '';
        $altcha_verifying_wait_text = isset( $options['altcha_verifying_wait_text'] ) ? $options['altcha_verifying_wait_text'] : '';
        $altcha_verified_text = isset( $options['altcha_verified_text'] ) ? $options['altcha_verified_text'] : '';
        $altcha_verification_failed_text = isset( $options['altcha_verification_failed_text'] ) ? $options['altcha_verification_failed_text'] : '';

        $checkbox_label = ! empty( $altcha_checkbox_label ) ? $altcha_checkbox_label : __( 'I\'m not a robot', 'admin-site-enhancements' );
        $verifying_text = ! empty( $altcha_verifying_text ) ? $altcha_verifying_text : __( "Verifying you're not a robot...", 'admin-site-enhancements' );
        $verifying_wait_text = ! empty( $altcha_verifying_wait_text ) ? $altcha_verifying_wait_text : __( 'Verifying... please wait.', 'admin-site-enhancements' );
        $verified_text = ! empty( $altcha_verified_text ) ? $altcha_verified_text : __( 'Verified', 'admin-site-enhancements' );
        $verification_failed_text = ! empty( $altcha_verification_failed_text ) ? $altcha_verification_failed_text : __( 'Verification failed. Try again later.', 'admin-site-enhancements' );

        $translations = array(
            "label"     => $checkbox_label,
            "verifying" => $verifying_text,
            "waitAlert" => $verifying_wait_text,
            "verified"  => $verified_text,
            "error"     => $verification_failed_text,
            "footer"    => sprintf(
            /* translators: the placeholders contain opening and closing tags for a link (<a> tag) */
            __( 'Protected by %1$sALTCHA%2$s', 'admin-site-enhancements' ),
            '<a href="' . $ALTCHA_WEBSITE . '" target="_blank">',
            "</a>",
            ),
        );

        if ( $original_language !== null ) {
         switch_to_locale( $original_language );
        }

        return $translations;
    }

    // ========== Perform verification to submissions via various WordPress forms ========= //
    
    public function perform_altcha_login_verification( $user ) {
        if ( $user instanceof WP_Error ) {
            return $user;
        }

        if( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
            return $user; // Skip XMLRPC
        }

        if( defined('REST_REQUEST') && REST_REQUEST ) {
            return $user; // Skip REST API
        }

        if ( $this->is_woocommerce_active() && isset( $_POST['woocommerce-login-nonce']) ) {
            return $user; // WooCommerce form submissions are handled with separately perform_altcha_woo_login_verification()
        }

        // If submission does not contain the ALTCHA payload, i.e. the originating form does not have the ALTCHA widget
        if ( ! isset( $_POST['altcha'] ) ) {
            return $user;
        }

        $altcha_payload = isset( $_POST['altcha'] ) ? trim( sanitize_text_field( $_POST['altcha'] ) ) : '';

        if ( false === $this->verify( $altcha_payload ) ) {
            return new WP_Error( "altcha-error", '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( "Something went wrong.", 'admin-site-enhancements' ) );
        }

        return $user;
    }
        
    public function perform_altcha_password_reset_verification( $errors ) {
        if ( is_user_logged_in() ) {
            return $errors;
        }

        if ( $this->is_woocommerce_active() && isset( $_POST['woocommerce-lost-password-nonce']) ) {
            return $errors; // WooCommerce form submissions are handled with separately perform_altcha_woo_password_reset_verification()
        }

        // If submission does not contain the ALTCHA payload, i.e. the originating form does not have the ALTCHA widget
        if ( ! isset( $_POST['altcha'] ) ) {
            return $errors;
        }

        $altcha_payload = isset( $_POST['altcha'] ) ? trim( sanitize_text_field( $_POST['altcha'] ) ) : '';

        if ( false === $this->verify( $altcha_payload ) ) {
            $errors->add(
                'altcha_error_message',
                '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'Something went wrong.', 'admin-site-enhancements' )
            );
        }

        return $errors;
    }
    
    public function perform_altcha_registration_verification( $user_login, $user_email, $errors ) {
        // If submission does not contain the ALTCHA payload, i.e. the originating form does not have the ALTCHA widget
        if ( ! isset( $_POST['altcha_register'] ) ) {
            return $errors;
        }

        $altcha_payload = isset( $_POST['altcha_register'] ) ? trim( sanitize_text_field( $_POST['altcha_register'] ) ) : '';
        
        if ( false === $this->verify( $altcha_payload ) ) {
            return $errors->add(
                'altcha_error_message',
                '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'Something went wrong.', 'admin-site-enhancements' )
            );
        }

        return $errors;
    }
    
    public function perform_altcha_comment_verification( $comment ) {
        // Trackback or pingback
        if ( $comment['comment_type'] != '' && $comment['comment_type'] != 'comment') {
            return $comment;
        }

        // Admin replies
        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            return $comment;
        }

        // If submission does not contain the ALTCHA payload, i.e. the originating form does not have the ALTCHA widget
        if ( ! isset( $_POST['altcha'] ) ) {
            return $comment;
        }

        $altcha_payload = isset( $_POST['altcha'] ) ? trim( sanitize_text_field( $_POST['altcha'] ) ) : '';

        if ( false === $this->verify( $altcha_payload ) ) {
            wp_die('<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'Something went wrong.', 'admin-site-enhancements' ) );
        }

        return $comment;
    }

    // ========== Perform verification to submissions via various WooCommerce forms ========= //

    public function perform_altcha_woo_login_verification( $user ) {
        if ( $user instanceof WP_Error ) {
            return $user;
        }
        
        // Skip XMLRPC
        if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
            return $user;
        }
        
        // Skip REST API
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return $user;
        }
        
        // Only handle WooCommerce form submissions
        if ( ! isset( $_POST['woocommerce-login-nonce'] ) ) {
            return $user;
        }

        // If submission does not contain the ALTCHA payload, i.e. the originating form does not have the ALTCHA widget
        if ( ! isset( $_POST['altcha'] ) ) {
            return $user;
        }

        $altcha_payload = isset( $_POST['altcha'] ) ? trim( sanitize_text_field( $_POST['altcha'] ) ) : '';

        if ( false === $this->verify( $altcha_payload ) ) {
            return new WP_Error(
                'altcha_error',
                esc_html__( 'Something went wrong.', 'admin-site-enhancements' )
            );
        }

        return $user;        
    }
    
    public function perform_altcha_woo_password_reset_verification( $errors ) {
        if ( is_user_logged_in() ) {
            return $errors;
        }

        // Only handle WooCommerce form submissions
        if ( ! isset( $_POST['woocommerce-lost-password-nonce'] ) ) {
            return $errors;
        }

        // If submission does not contain the ALTCHA payload, i.e. the originating form does not have the ALTCHA widget
        if ( ! isset( $_POST['altcha'] ) ) {
            return $errors;
        }

        $altcha_payload = isset( $_POST['altcha'] ) ? trim( sanitize_text_field( $_POST['altcha'] ) ) : '';

        if ( false === $this->verify( $altcha_payload ) ) {
            $errors->add(
                'altcha_error',
                esc_html__( 'Something went wrong.', 'admin-site-enhancements' )
            );
        }

        return $errors;
    }

    public function perform_altcha_woo_registration_verification( $user_login, $user_email, $errors ) {
        // Skip XMLRPC
        if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
            return $errors;
        }

        // Skip REST API
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return $errors;
        }

        // Only handle WooCommerce form submissions
        if ( ! isset( $_POST['woocommerce-register-nonce'] ) ) {
            return $errors;
        }

        // If submission does not contain the ALTCHA payload, i.e. the originating form does not have the ALTCHA widget
        if ( ! isset( $_POST['altcha'] ) ) {
            return $errors;
        }

        $altcha_payload = isset( $_POST['altcha'] ) ? trim( sanitize_text_field( $_POST['altcha'] ) ) : '';
        
        if ( false === $this->verify( $altcha_payload ) ) {
            return $errors->add(
                'altcha_error',
                esc_html__( 'Something went wrong.', 'admin-site-enhancements' )
            );
        }

        return $errors;        
    }
    
    // public function perform_altcha_woo_checkout() {
    //     // Only handle WooCommerce form submissions
    //     if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) ) {
    //         return $user;
    //     }
        
    //     $altcha_payload = isset( $_POST['altcha'] ) ? trim( sanitize_text_field( $_POST['altcha'] ) ) : '';
        
    //     if ( false === $this->verify( $altcha_payload ) ) {
    //         wc_add_notice( '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'Something went wrong.', 'admin-site-enhancements' ) . ' ' . esc_html__( 'Please reload the page to proceed with the checkout process. The info you\'ve entered will be preserved.', 'admin-site-enhancements' ), 'error' );
    //     }
    // }
    
    // ========== ALTCHA verification process ========= //
    
    public function verify( $payload, $hmac_key = null ) {
        if ( $hmac_key === null ) {
            $options = get_option( 'admin_site_enhancements', array() );
            $hmac_key = isset( $options['altcha_secret_key'] ) ? $options['altcha_secret_key'] : $this->random_secret();
        }
        
        if ( empty( $payload ) || empty( $hmac_key ) ) {
            return false;
        }

        $data = json_decode( base64_decode( $payload ) );

        if ( isset( $data->verificationData ) ) {
            return $this->verify_server_signature( $payload, $hmac_key );
        }
        
        return $this->verify_solution( $payload, $hmac_key );
    }

    public function verify_server_signature( $payload, $hmac_key = null ) {
        if ( $hmac_key === null ) {
            $options = get_option( 'admin_site_enhancements', array() );
            $hmac_key = isset( $options['altcha_secret_key'] ) ? $options['altcha_secret_key'] : $this->random_secret();
        }

        $data = json_decode(base64_decode($payload));

        $alg_ok = ( $data->algorithm === 'SHA-256' );

        $calculated_hash = hash( 'sha256', $data->verificationData, true );
        $calculated_signature = hash_hmac( 'sha256', $calculated_hash, $hmac_key );
        $signature_ok = ( $data->signature === $calculated_signature );

        $verified = ( $alg_ok && $signature_ok );

        if ( $verified ) {
            $this->spamfilter_result = array();
            parse_str( $data->verificationData, $this->spamfilter_result );
            return $this->spamfilter_result['classification'] !== 'BAD';
        }

        return $verified;
    }

    public function verify_solution( $payload, $hmac_key = null ) {
        if ( $hmac_key === null ) {
            $options = get_option( 'admin_site_enhancements', array() );
            $hmac_key = isset( $options['altcha_secret_key'] ) ? $options['altcha_secret_key'] : $this->random_secret();
        }

        $data = json_decode( base64_decode( $payload ) );
        // vi( $data );
        $salt_url = wp_parse_url( $data->salt );
        parse_str( $salt_url['query'], $salt_params );

        if ( ! empty( $salt_params['expires'] ) ) {
            $expires = intval( $salt_params['expires'], 10 );
            if ( $expires > 0 && $expires < time() ) {
                return false;
            }
        }

        $alg_ok = ( $data->algorithm === 'SHA-256' );

        $calculated_challenge = hash( 'sha256', $data->salt . $data->number );
        // $calculated_challenge = 'random'; // for testing failed verification
        $challenge_ok = ( $data->challenge === $calculated_challenge );

        $calculated_signature = hash_hmac( 'sha256', $data->challenge, $hmac_key );
        $signature_ok = ( $data->signature === $calculated_signature );

        $verified = ( $alg_ok && $challenge_ok && $signature_ok );
        // vi( $verified );
        
        return $verified;
    }
    
    public function is_woocommerce_active() {
        return in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) );
    }

}