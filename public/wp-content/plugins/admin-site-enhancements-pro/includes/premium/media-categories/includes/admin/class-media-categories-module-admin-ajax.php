<?php
/**
 * Admin AJAX class.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

/**
 * Saves settings via AJAX at Media Categories Module > Settings.
 *
 * @since   1.1.6
 */
class Media_Categories_Module_Admin_AJAX {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.1.6
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.1.6
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_action( 'wp_ajax_media_categories_module_save_settings', array( $this, 'save_settings' ) );

	}

	/**
	 * Saves Settings
	 *
	 * @since   1.1.6
	 */
	public function save_settings() {

		// Check nonce.
		check_ajax_referer( 'media-categories-module-save-settings', 'nonce' );

		// Bail if user isn't an Administrator.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized.', 'admin-site-enhancements' ), 401 );
		}

		// Convert JSON string into PHP multidimensional array.
		$settings = $this->base->get_class( 'settings' )->convert_multidimensional_form_data_json_string_to_array( $_REQUEST['settings'] );

		// Bail if no settings.
		if ( ! is_array( $settings ) ) {
			wp_send_json_error( __( 'No settings data detected.', 'admin-site-enhancements' ) );
		}

		// Save General Settings.
		$result = $this->base->get_class( 'settings' )->update_settings( 'general', $settings['general'] );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Save User Options Settings.
		$result = $this->base->get_class( 'settings' )->update_settings( 'user-options', $settings['user-options'] );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Save Addon Settings.
		$result = apply_filters( 'media_categories_module_admin_save_settings', true, $settings );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// If here, OK.
		wp_send_json_success( __( 'Settings saved.', 'admin-site-enhancements' ) );

	}

}
