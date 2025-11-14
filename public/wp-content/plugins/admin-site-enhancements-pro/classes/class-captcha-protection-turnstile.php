<?php

namespace ASENHA\Classes;

use WP_Error;

/**
 * Class for CAPTCHA Protection module using Cloudflare Turnstile 
 * Using code modified from the Simple Cloudflare Turnstile plugin by Elliot Sowersby: https://wordpress.org/plugins/simple-cloudflare-turnstile/
 *
 * @link https://plugins.trac.wordpress.org/browser/simple-cloudflare-turnstile/tags/1.30.0/simple-cloudflare-turnstile.php
 * @link https://plugins.trac.wordpress.org/browser/simple-cloudflare-turnstile/tags/1.30.0/inc/turnstile.php
 * @link https://plugins.trac.wordpress.org/browser/simple-cloudflare-turnstile/tags/1.30.0/inc/wordpress.php
 * @since 7.7.0
 */
class CAPTCHA_Protection_Turnstile {

    // ========== Add Turnstile to various default WordPress forms ========= //
	
	/**
	* Display the turnstile field on the login form.
	*/
	public function add_turnstile_to_login_form() {
		$this->show_turnstile_widget( '#wp-submit', 'turnstileWPCallback', 'wordpress-login', '-' . wp_rand(), 'wp-login', 'flexible', true );
	}

	/**
	 * Add Turnstile to password reset form
	 * 
	 * @since 7.7.0
	 */
	public function add_turnstile_to_password_reset_form() {
		$this->show_turnstile_widget( '#wp-submit', 'turnstileWPCallback', 'wordpress-reset', '-' . wp_rand(), 'wp-reset', 'flexible', true );
	}

	/**
	 * Add turnstile to registration form
	 * 
	 * @since 7.7.0
	 */
	public function add_turnstile_to_registration_form() {
		$this->show_turnstile_widget( '#wp-submit', 'turnstileWPCallback', 'wordpress-register', '-' . wp_rand(), 'wp-register', 'flexible', true );
	}

	/**
	 * Add Turnstile to comment form
	 * 
	 * @since 7.7.0
	 */
	public function add_turnstile_to_comment_form() {
		$this->show_turnstile_widget( '', '', 'wordpress-comment', '-' . wp_rand(), 'wp-comment', 'normal', false );
	}

    // ========== Perform verification to submissions via various WooCommerce forms ========= //

	public function add_turnstile_to_woo_login_form() {
		$this->show_turnstile_widget( '.woocommerce-form-login__submit', 'turnstileWPCallback', 'woocommerce-login', '-' . wp_rand(), 'woo-login', 'normal', false );
	}

	public function add_turnstile_to_woo_lostpassword_form() {
		$this->show_turnstile_widget( '.lost_reset_password .woocommerce-Button', 'turnstileWPCallback', 'woocommerce-reset', '-' . wp_rand(), 'woo-reset', 'normal', false );
	}

	public function add_turnstile_to_woo_registration_form() {
		$this->show_turnstile_widget( '.woocommerce-form-register__submit', 'turnstileWPCallback', 'woocommerce-register', '-' . wp_rand(), 'woo-register', 'normal', false );
	}

	// public function add_turnstile_to_woo_checkout_form() {
	// 	if ( function_exists( 'is_wc_endpoint_url' ) ) {
	// 		if ( is_wc_endpoint_url('order-received') ) {
	// 			return;
	// 		}			
	// 	}

	// 	$this->show_turnstile_widget( '', '', 'woocommerce-checkout', '-woo-checkout', 'woo-checkout', 'normal', false );
	// }

    // ========== Render Turnstile widget ========= //

	/**
	 * Add data-cfasync="false" to Turnstile <script> tag
	 * 
	 * @since 7.7.0
	 */
	public function turnstile_script_tags( $tag, $handle ) {
		if ( 'asenha-turnstile' === $handle ) {
			$tag = str_replace( "src='", "data-cfasync='false' src='", $tag );
		}
		
		return $tag;
	}

	/**
	 * Create turnstile field template.
	 *
	 * @param int $button_id
	 * @param string $callback
	 * @param string $form_name
	 * @param string $unique_id
	 * @param string $class
	 */
	public function show_turnstile_widget( $button_id = '', $callback = '', $form_name = '', $unique_id = '', $class = '', $widget_size = 'flexible', $disable_button = true ) {
        $options = get_option( 'admin_site_enhancements', array() );
        $site_key = ( isset( $options['turnstile_site_key'] ) ) ? sanitize_text_field( $options['turnstile_site_key'] ) : '';
        // $widget_size = 'flexible'; // flexible (100%) | normal (300px wide) | compact (150px square)
        $widget_theme = ( isset( $options['turnstile_widget_theme'] ) ) ? sanitize_text_field( $options['turnstile_widget_theme'] ) : 'light';

		$language = 'auto';
		$appearance = 'always'; // always | interaction-only
		// $disable_button = true;

		do_action( 'turnstile_enqueue_scripts' );
		do_action( 'turnstile_before_field', esc_attr( $unique_id ) );
		?>
		<div id="cf-turnstile<?php echo esc_attr( $unique_id ); ?>"
		class="cf-turnstile<?php if( $class ) { echo " " . esc_attr( $class ); } ?>" 
		<?php if ( $disable_button ) { ?>data-callback="<?php echo esc_attr( $callback ); ?>"<?php } ?>
		data-sitekey="<?php echo esc_attr( $site_key ); ?>"
		data-theme="<?php echo esc_attr( $widget_theme ); ?>"
		data-language="<?php echo esc_attr( $language ); ?>"
		data-size="<?php echo esc_attr( $widget_size ); ?>"
		data-retry="auto" 
		data-retry-interval="1000" 
		data-action="<?php echo esc_attr( $form_name ); ?>"
		data-appearance="<?php echo esc_attr( $appearance ); ?>"></div>
		<?php
		if ( $unique_id ) {
		?>
		<script>document.addEventListener("DOMContentLoaded", function() { setTimeout(function(){ var e=document.getElementById("cf-turnstile<?php echo esc_html($unique_id); ?>"); e&&!e.innerHTML.trim()&&(turnstile.remove("#cf-turnstile<?php echo esc_html($unique_id); ?>"), turnstile.render("#cf-turnstile<?php echo esc_html($unique_id); ?>", {sitekey:"<?php echo esc_html($site_key); ?>"})); }, 0); });</script>
		<?php
		}

		if ( $disable_button ) {
			?>
			<style><?php echo esc_html( $button_id ); ?> { pointer-events: none; opacity: 0.5; }</style>
			<?php
		}

		do_action( 'turnstile_after_field', esc_attr( $unique_id ), $button_id );
		?>
		<br class="cf-turnstile-br cf-turnstile-br<?php echo esc_attr( $unique_id ); ?>">
		<?php
	}

    // ========== Perform verification to submissions via various WordPress forms ========= //
			
	/**
	 * Authenticate login
	 */
	public function turnstile_authenticate_login( $user ) {
		// Skip when there is an error
        if ( is_wp_error( $user ) ) {
			return $user;
        }

		// Skip if no user is set
		if( ! isset( $user->ID ) ) { 
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

        if ( $this->is_woocommerce_active() && isset( $_POST['woocommerce-login-nonce']) ) {
            return $user; // WooCommerce form submissions are handled with separately turnstile_authenticate_woo_login()
        }

        // If submission does not contain the Turnstile payload, i.e. the originating form does not have the Turnstile widget
        if ( ! isset( $_POST['cf-turnstile-response'] ) ) {
            return $user;
        }

		// Check Turnstile
		$check = $this->turnstile_check();
		$success = $check['success'];

		if( $success != true ) {
			$user = new WP_Error( 'turnstile_error', esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' ) );
		}
		
		return $user;		
	}
	
	/**
	 * Validate turnstile during password reset
	 * 
	 * @since 7.7.0
	 */
	public function turnstile_validate_password_reset( $errors ) {
        if ( $this->is_woocommerce_active() && isset( $_POST['woocommerce-lost-password-nonce']) ) {
            return $user; // WooCommerce form submissions are handled with separately turnstile_authenticate_woo_login()
        }

        // If submission does not contain the Turnstile payload, i.e. the originating form does not have the Turnstile widget
        if ( ! isset( $_POST['cf-turnstile-response'] ) ) {
            return $errors;
        }

		$check = $this->turnstile_check();
		$success = $check['success'];

		if( $success != true ) {
			$errors->add( 'turnstile_error', esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' ) );
		}
	}
	
	/**
	 * Validate turnstile during registration
	 * 
	 * @since 7.7.0
	 */
	public function turnstile_validate_registration( $sanitized_user_login, $user_email, $errors ) {
		// Skip XMLRPC
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $errors;
		}

		// Skip REST API
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $errors;
		}

        if ( $this->is_woocommerce_active() && isset( $_POST['woocommerce-register-nonce']) ) {
            return $errors; // WooCommerce form submissions are handled with separately recaptcha_validate_woo_registration()
        }

        // If submission does not contain the Turnstile payload, i.e. the originating form does not have the Turnstile widget
        if ( ! isset( $_POST['cf-turnstile-response'] ) ) {
            return $errors;
        }

		$check = $this->turnstile_check();
		$success = $check['success'];

		if( $success != true ) {
			$errors->add( 'turnstile_error', esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' ) );
		}

		return $errors;
	}
	
	/**
	 * Verify Turnstile during commenting
	 * 
	 * @since 7.7.0
	 */
	public function turnstile_verify_comment( $comment ) {
        // If submission does not contain the Turnstile payload, i.e. the originating form does not have the Turnstile widget
        if ( ! isset( $_POST['cf-turnstile-response'] ) ) {
            return $comment;
        }

		$check = $this->turnstile_check();
		$success = $check['success'];

		if( $success != true ) {
			wp_die( 
				esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' ), 
				'asenha-turnstile', 
				array( 
					'response'  => 403, 
					'back_link' => 1, 
				) 
			);
		}

		return $comment;
	}

    // ========== Perform verification to submissions via various WooCommerce forms ========= //

	/**
	 * Verify Turnstile during WooCommerce login
	 * 
	 * @since 7.7.0
	 */
	public function turnstile_authenticate_woo_login( $user ) {
		// Skip when there is an error
        if ( is_wp_error( $user ) ) {
			return $user;
        }

		// Skip if no user is set
		if( ! isset( $user->ID ) ) { 
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

        // If submission does not contain the Turnstile payload, i.e. the originating form does not have the Turnstile widget
        if ( ! isset( $_POST['cf-turnstile-response'] ) ) {
            return $user;
        }

		// Check Turnstile
		$check = $this->turnstile_check();
		$success = $check['success'];

		if( $success != true ) {
			$user = new WP_Error( 'turnstile_error', esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' ) );
		}
		
		return $user;		
	}

	/**
	 * Validate turnstile during WooCommerce password reset
	 * 
	 * @since 7.7.0
	 */
	public function turnstile_validate_woo_password_reset( $errors ) {
        // Only handle WooCommerce form submissions
        if ( ! isset( $_POST['woocommerce-lost-password-nonce'] ) ) {
            return $errors;
        }

        // If submission does not contain the Turnstile payload, i.e. the originating form does not have the Turnstile widget
        if ( ! isset( $_POST['cf-turnstile-response'] ) ) {
            return $errors;
        }

		$check = $this->turnstile_check();
		$success = $check['success'];

		if( $success != true ) {
			$errors->add( 'turnstile_error', esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' ) );
		}
	}

	/**
	 * Validate turnstile during WooCommerce registration
	 * 
	 * @since 7.7.0
	 */
	public function turnstile_validate_woo_registration( $user_login, $user_email, $errors ) {
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

        // If submission does not contain the Turnstile payload, i.e. the originating form does not have the Turnstile widget
        if ( ! isset( $_POST['cf-turnstile-response'] ) ) {
            return $errors;
        }

		$check = $this->turnstile_check();
		$success = $check['success'];

		if( $success != true ) {
			$errors->add( 'turnstile_error', esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' ) );
		}

		return $errors;
	}
	
    // ========== Turnstile verification process ========= //

	/**
	 * Checks Turnstile Captcha POST is Valid
	 *
	 * @param string $postdata
	 * @return bool
	 */
	public function turnstile_check( $postdata = "" ) {

		$results = array();

		// Check if POST data is empty
		if ( empty( $postdata ) && isset( $_POST['cf-turnstile-response'] ) ) {
			$postdata = sanitize_text_field( $_POST['cf-turnstile-response'] );
		}

		// Get Turnstile Keys from Settings
        $options = get_option( 'admin_site_enhancements', array() );
        $site_key = ( isset( $options['turnstile_site_key'] ) ) ? sanitize_text_field( $options['turnstile_site_key'] ) : '';
        $secret_key = ( isset( $options['turnstile_secret_key'] ) ) ? sanitize_text_field( $options['turnstile_secret_key'] ) : '';

		if ( $site_key && $secret_key ) {

			$headers = array(
				'body' => [
					'secret' => $secret_key,
					'response' => $postdata
				]
			);
			$verify = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', $headers );
			$verify = wp_remote_retrieve_body( $verify );
			$response = json_decode( $verify );
			// vi( $response );

			if( $response->success ) {
				$results['success'] = $response->success;
			} else {
				$results['success'] = false;
			}

			return $results;
		} else {
			$results['success'] = false;
			return $results;
		}
		
	}

    public function is_woocommerce_active() {
        return in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) );
    }
}