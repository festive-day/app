<?php

namespace ASENHA\Classes;

use WP_Error;

/**
 * Class for CAPTCHA Protection module using Google reCAPTCHA. 
 * Using code modified from the Protector using ReCaptcha plugin v1.0: https://wordpress.org/plugins/protector-using-recaptcha/
 * Using code modified from the Advanced Google reCAPTCHA plugin: https://wordpress.org/plugins/advanced-google-recaptcha/
 *
 * @link https://plugins.trac.wordpress.org/browser/protector-using-recaptcha/trunk/includes/recaptcha-handler.php
 * @link https://plugins.trac.wordpress.org/browser/advanced-google-recaptcha/tags/1.27/advanced-google-recaptcha.php
 * @link https://plugins.trac.wordpress.org/browser/advanced-google-recaptcha/tags/1.27/libs/functions.php
 * @since 7.7.0
 */
class CAPTCHA_Protection_reCAPTCHA {

    public static $html_escape_allowed_tags = array(
        'div' => array(
            'class' => array(),
            'style' => array(),
            'data-sitekey' => array(),
            'data-size'    => array(),
        ),
        'input' => array(
            'type' => array(),
            'name' => array(),
            'value' => array(),
            'id' => array(),
            'class' => array(),
            'style' => array(),
        ),
        'script' => array(
            'src' => array(),
            'id' => array(),
        ),
    );
    
    // ========== Add reCAPTCHA to various default WordPress forms ========= //
    
    public function add_recaptcha_to_login_form() {
        $form_type = 'login';
        $this->render_recaptcha_widget( $form_type );
    }
    
    public function add_recaptcha_to_password_reset_form() {
        $form_type = 'password-reset';
        $this->render_recaptcha_widget( $form_type );
    }
    
    public function add_recaptcha_to_registration_form() {
        $form_type = 'registration';
        $this->render_recaptcha_widget( $form_type );
    }
    
    public function add_recaptcha_to_comment_form() {
        $form_type = 'comment';
        $this->render_recaptcha_widget( $form_type );
    }

    // ========== Add reCAPTCHA to various default WooCommerce forms ========= //

    public function add_recaptcha_to_woo_login_form() {
        $form_type = 'login';
        $this->render_recaptcha_widget( $form_type );
    }
    
    public function add_recaptcha_to_woo_lostpassword_form() {
        $form_type = 'password-reset';
        $this->render_recaptcha_widget( $form_type );
    }
    
    public function add_recaptcha_to_woo_registration_form() {
        $form_type = 'registration';
        $this->render_recaptcha_widget( $form_type );
    }

    // public function add_recaptcha_to_woo_checkout_form() {
    //     $form_type = 'checkout';
    //     $this->render_recaptcha_widget( $form_type );
    // }
    
    // ========== Render reCAPTCHA widget ========= //

    public function render_recaptcha_widget( $form_type ) {
        $nonce_field = wp_nonce_field( 'asenha_recaptcha_action', 'asenha_recaptcha_nonce', true, false );
        $recaptcha_html = $this->get_recaptcha_html( $form_type ) . $nonce_field;
        echo wp_kses( $recaptcha_html, self::$html_escape_allowed_tags );
    }

    public function get_recaptcha_html( $form_type ) {
        $options = get_option( 'admin_site_enhancements', array() );

        $recaptcha_widget = isset( $options['recaptcha_widget'] ) ? $options['recaptcha_widget'] : 'v2_checkbox';

        switch ( $recaptcha_widget ) {
            case 'v2_checkbox';
                $site_key = isset( $options['recaptcha_site_key_v2_checkbox'] ) ? $options['recaptcha_site_key_v2_checkbox'] : '';
                $additional_container_class = 'recaptcha-checkbox';
                break;

            case 'v3_invisible';
                $site_key = isset( $options['recaptcha_site_key_v3_invisible'] ) ? $options['recaptcha_site_key_v3_invisible'] : '';
                $additional_container_class = 'recaptcha-invisible';
                break;
        }

        $container_class = 'asenha-recaptcha-container asenha-' . sanitize_html_class( $form_type ) . '-form ' . $additional_container_class;
        
        switch ( $recaptcha_widget ) {
            case 'v2_checkbox';
                $recaptcha_html = '<div class="' . esc_attr( $container_class ) . '">
                        <div class="g-recaptcha" data-sitekey="' . esc_attr( $site_key ) . '"></div>
                   </div>';
                break;
                
            case 'v3_invisible';
                // Ref: https://plugins.trac.wordpress.org/browser/advanced-google-recaptcha/tags/1.27/libs/functions.php#L492
                $recaptcha_html = '<input type="hidden" name="g-recaptcha-response" class="asenha-recaptcha-response" value="" />
                        <script>
                        function wpcaptcha_captcha(){
                            grecaptcha.execute("' . esc_html($site_key) . '", {action: "submit"}).then(function(token) {
                                var captchas = document.querySelectorAll(".asenha-recaptcha-response");
                                captchas.forEach(function(captcha) {
                                    captcha.value = token;
                                });
                            });
                        }
                        </script>';
                break;
        }
        
        if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
            if ( 'checkout' == $form_type ) {
                switch ( $recaptcha_widget ) {
                    case 'v2_checkbox';
                        $recaptcha_html .= '<script>
                            jQuery("form.woocommerce-checkout").on("submit", function(){
                                setTimeout(function(){
                                    grecaptcha.reset();
                                },100);
                            });
                            </script>';
                        break;


                    case 'v3_invisible';
                        $recaptcha_html .= '<script>
                            jQuery("form.woocommerce-checkout").on("submit", function(){
                                setTimeout(function(){
                                    wpcaptcha_captcha();
                                },100);
                            });
                            </script>';
                        break;
                }
            }
        }

        switch ( $recaptcha_widget ) {
            case 'v2_checkbox';
                // We insert the script inline like this instead of via login_enqueue_scripts hook, as this approach works.
                // Ref: https://plugins.trac.wordpress.org/browser/advanced-google-recaptcha/tags/1.27/libs/functions.php#L549
                $recaptcha_html .= "<script src='https://www.google.com/recaptcha/api.js?ver=" . esc_attr( ASENHA_VERSION ) . "' id='wpcaptcha-recaptcha-js'></script>"; // phpcs:ignore
                break;
                
            case 'v3_invisible';
                // We insert the script inline like this instead of via login_enqueue_scripts hook, as this approach works.
                // Ref: https://plugins.trac.wordpress.org/browser/advanced-google-recaptcha/tags/1.27/libs/functions.php#L549
                $recaptcha_html .= "<script src='https://www.google.com/recaptcha/api.js?onload=wpcaptcha_captcha&render=" . esc_html($site_key) . "&ver=" . esc_attr( ASENHA_VERSION ) . "' id='asenha-recaptcha-js'></script>"; // phpcs:ignore
                break;
        }
        
        return $recaptcha_html;
    }

    // ========== Perform verification to submissions via various WordPress forms ========= //

    public function recaptcha_authenticate_login( $user ) {
        if ( $user instanceof WP_Error ) {
            return $user;
        }

        if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
            return $user; // Skip XMLRPC
        }

        if ( defined('REST_REQUEST') && REST_REQUEST ) {
            return $user; // Skip REST API
        }

        if ( $this->is_woocommerce_active() && isset( $_POST['woocommerce-login-nonce']) ) {
            return $user; // WooCommerce form submissions are handled with separately recaptcha_authenticate_woo_login()
        }

        // If submission does not contain the reCAPTCHA payload, i.e. the originating form does not have the reCAPTCHA widget
        if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
            return $user;
        }

        if ( ! $this->verify_recaptcha( 'login' ) ) {
            return new WP_Error( 'recaptcha_error', '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'reCAPTCHA verification failed.', 'admin-site-enhancements' ) );
        }
        
        return $user;
    }
    
    public function recaptcha_validate_password_reset( $errors, $user_data ) {
        if ( $this->is_woocommerce_active() && isset( $_POST['woocommerce-lost-password-nonce']) ) {
            return $errors; // WooCommerce form submissions are handled with separately recaptcha_validate_woo_password_reset()
        }

        // If submission does not contain the reCAPTCHA payload, i.e. the originating form does not have the reCAPTCHA widget
        if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
            return $errors;
        }

        if ( ! $this->verify_recaptcha( 'password-reset' ) ) {
            $errors->add( 'recaptcha_error', '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'reCAPTCHA verification failed.', 'admin-site-enhancements' ) );
        }
    }
    
    public function recaptcha_validate_registration( $sanitized_user_login, $user_email, $errors ) {
        if ( $this->is_woocommerce_active() && isset( $_POST['woocommerce-register-nonce']) ) {
            return $user; // WooCommerce form submissions are handled with separately recaptcha_validate_woo_registration()
        }
        
        if ( ! $this->verify_recaptcha( 'registration' ) ) {
            $errors->add( 'recaptcha_error', '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'reCAPTCHA verification failed.', 'admin-site-enhancements' ) );
        }
        
        return $errors;
    }
    
    public function recaptcha_verify_comment( $comment ) {
        // If submission does not contain the reCAPTCHA payload, i.e. the originating form does not have the reCAPTCHA widget
        if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
            return $comment;
        }

        if ( ! $this->verify_recaptcha( 'comment' ) ) {
            wp_die( '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'reCAPTCHA verification failed.', 'admin-site-enhancements' ) );
        }
        
        return $comment;
    }

    // ========== Perform verification to submissions via various WooCommerce forms ========= //

    public function recaptcha_authenticate_woo_login( $user ) {
        if ( $user instanceof WP_Error ) {
            return $user;
        }

        if( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
            return $user; // Skip XMLRPC
        }

        if( defined('REST_REQUEST') && REST_REQUEST ) {
            return $user; // Skip REST API
        }

        // Only handle WooCommerce form submissions
        if ( ! isset( $_POST['woocommerce-login-nonce'] ) ) {
            return $user;
        }

        // If submission does not contain the reCAPTCHA payload, i.e. the originating form does not have the reCAPTCHA widget
        if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
            return $user;
        }

        if ( ! $this->verify_recaptcha( 'login' ) ) {
            return new WP_Error( 'recaptcha_error', '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'reCAPTCHA verification failed.', 'admin-site-enhancements' ) );
        }
        
        return $user;
    }
    
    public function recaptcha_validate_woo_password_reset( $errors, $user_data ) {
        // If submission does not contain the reCAPTCHA payload, i.e. the originating form does not have the reCAPTCHA widget
        if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
            return $errors;
        }

        // Only handle WooCommerce form submissions
        if ( ! isset( $_POST['woocommerce-lost-password-nonce'] ) ) {
            return $errors;
        }

        if ( ! $this->verify_recaptcha( 'password-reset' ) ) {
            $errors->add( 'recaptcha_error', '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'reCAPTCHA verification failed.', 'admin-site-enhancements' ) );
        }
    }
    
    public function recaptcha_validate_woo_registration( $user_login, $user_email, $errors ) {
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

        // If submission does not contain the reCAPTCHA payload, i.e. the originating form does not have the reCAPTCHA widget
        if ( ! isset( $_POST['g-recaptcha-response'] ) ) {
            return $errors;
        }
        
        if ( ! $this->verify_recaptcha( 'registration' ) ) {
            $errors->add( 'recaptcha_error', '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'reCAPTCHA verification failed.', 'admin-site-enhancements' ) );
        }

        return $errors;
    }

    // public function recaptcha_validate_woo_checkout() {
    //     // Only handle WooCommerce form submissions
    //     if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) ) {
    //         return $user;
    //     }
        
    //     if ( ! $this->verify_recaptcha( 'checkout' ) ) {
    //         wc_add_notice( '<strong>' . esc_html__( 'Error', 'admin-site-enhancements' ) . '</strong>: ' . esc_html__( 'reCAPTCHA verification failed.', 'admin-site-enhancements' ) . ' ' . esc_html__( 'Please reload the page to perform the reCAPTCHA verification again. The info you\'ve entered will be preserved.', 'admin-site-enhancements' ), 'error' );
    //         // [TODO] Find a way to call grecaptcha.reset() so user don't need to reload the page. Ref: https://stackoverflow.com/a/27703907. There is a grecaptcha.reset() in get_recaptcha_html() on this class, but it does not seem to work.
    //         // Maybe look at https://developer.woocommerce.com/docs/cart-and-checkout-checkout-flow-and-events/
    //         // Add the JS script to helper.js (currently empty)
    //     }
    // }
    
    // ========== reCAPTCHA verification process ========= //

    public function verify_recaptcha( $location ) {
        $options = get_option( 'admin_site_enhancements', array() );

        $recaptcha_widget = isset( $options['recaptcha_widget'] ) ? $options['recaptcha_widget'] : 'v2_checkbox';

        switch ( $recaptcha_widget ) {
            case 'v2_checkbox';
                $secret_key = isset( $options['recaptcha_secret_key_v2_checkbox'] ) ? $options['recaptcha_secret_key_v2_checkbox'] : '';
                break;

            case 'v3_invisible';
                $secret_key = isset( $options['recaptcha_secret_key_v3_invisible'] ) ? $options['recaptcha_secret_key_v3_invisible'] : '';
                break;
        }
        
        if ( empty( $secret_key ) ) {
            return false;
        }

        // Verify nonce
        $nonce = isset( $_POST['asenha_recaptcha_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['asenha_recaptcha_nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'asenha_recaptcha_action') ) {
            return false; // Nonce verification failed
        }

        $response = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
        $remote_ip = '';

        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $raw_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
            $validated_ip = filter_var( $raw_ip, FILTER_VALIDATE_IP );
            if ( false !== $validated_ip ) {
                $remote_ip = $validated_ip;
            }
        }

        // Prepare the API request
        $verify = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret'   => $secret_key,
                'response' => $response,
                'remoteip' => $remote_ip,
            ),
        ));
        // vi( $verify );

        $success = false;

        if ( ! is_wp_error( $verify ) && isset( $verify['body']) ) {
            $result = json_decode( $verify['body'], true );
            // vi( $result );

            if ( 'v2_checkbox' == $recaptcha_widget ) {
                $success = isset( $result['success'] ) ? (bool) $result['success'] : false;
            }

            if ( 'v3_invisible' == $recaptcha_widget ) {
                // For checking minimum score for passing verification. Range is 0 to 1 (0 is most likely bot)
                // Ref: https://developers.google.com/recaptcha/docs/v3#interpreting_the_score
                $score = isset( $result['score'] ) ? $result['score'] : false;
                if ( is_numeric( $score ) && is_float( $score ) ) {
                    if ( $score >= 0.2 ) {
                        // In the login context, entering email from browser memory and typing password manually may yield the low score of 0.2. So, we set that as the minimum score here.
                        $success = isset( $result['success'] ) ? (bool) $result['success'] : false;
                    }
                }
            }
        }
        // vi( $success );
        // $success = false; // For testing failed reCAPTCHA check
        
        return $success;
    }

    public function is_woocommerce_active() {
        return in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) );
    }
}