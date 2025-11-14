<?php

namespace ASENHA\Classes;

/**
 * Class related to rendering of settings fields on the admin page
 *
 * @since 2.2.0
 */
class Settings_Fields_Render {

	/**
	 * Render checkbox field as a toggle/switcher
	 *
	 * @since 1.0.0
	 */
	function render_checkbox_toggle( $args ) {

		$option_name = isset( $args['option_name'] ) ? $args['option_name'] : '';
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_name = $args['field_name'];
		$field_title = isset( $args['field_title'] ) ? $args['field_title'] : '';
		$field_description = $args['field_description'];
		$field_option_value = ( array_key_exists( $args['field_id'], $options ) ) ? $options[$args['field_id']] : false;

		echo '<input type="checkbox" id="' . esc_attr( $field_name ) . '" class="asenha-field-checkbox" name="' . esc_attr( $field_name ) . '" ' . checked( $field_option_value, true, false ) . '>';
		echo '<label for="' . esc_attr( $field_name ) . '"></label>';

		// For field with additional options / sub-fields, we add a wrapper to enclose field descriptions
		if ( array_key_exists( 'field_options_wrapper', $args ) && $args['field_options_wrapper'] ) {
			// For when the options / sub-fields occupy lengthy vertical space, we add show all / less toggler
			if ( array_key_exists( 'field_options_moreless', $args ) && $args['field_options_moreless'] ) {
				echo '<div class="asenha-field-with-options field-show-more">';
				echo '<a id="' . esc_attr( $args['field_slug'] ) . '-show-moreless" class="show-more-less show-more" href="#">' . __( 'Expand', 'admin-site-enhancements' ) . ' &#9660;</a>';
				echo '<div class="asenha-field-options-wrapper wrapper-show-more">';
			} else {
				echo '<div class="asenha-field-with-options">';
				echo '<div class="asenha-field-options-wrapper">';
			}

		}

		echo '<div class="asenha-field-description" data-search-filter data-module-info="' . esc_attr( strtolower( $field_title ) ) . '">' . wp_kses_post( $field_description ) . '</div>';

		// For field with additional options / sub-fields, we add wrapper for them
		if ( array_key_exists( 'field_options_wrapper', $args ) && $args['field_options_wrapper'] ) {
			echo '<div class="asenha-subfields" style="display:none"></div>';
		}


		// For field with additional options / sub-fields, we add a wrapper to enclose field descriptions
		if ( array_key_exists( 'field_options_wrapper', $args ) && $args['field_options_wrapper'] ) {
			echo '</div>';
			echo '</div>';
		}

	}

	/**
	 * Render checkbox field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 1.9.0
	 */
	function render_checkbox_plain( $args ) {

		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_name = $args['field_name'];
		$field_label = $args['field_label'];
		
		$default_value = false;
		switch ( $args['field_id'] ) {
			case 'login_page_disable_registration':
				$default_value = ( 1 == get_option( 'users_can_register' ) ) ? false : true;
				break;
		}
		
		$field_option_value = ( isset( $options[$args['field_id']] ) ) ? $options[$args['field_id']] : $default_value;

		$display_none_on_load = isset( $args['display_none_on_load'] ) ? $args['display_none_on_load'] : false;
		if ( $display_none_on_load ) {
			$inline_style = 'display:none;';
		} else {
			$inline_style = '';
		}
		
		echo '<input type="checkbox" id="' . esc_attr( $field_name ) . '" class="asenha-subfield-checkbox" style="' . esc_attr( $inline_style ) . '" name="' . esc_attr( $field_name ) . '" ' . checked( $field_option_value, true, false ) . '>';
		echo '<label for="' . esc_attr( $field_name ) . '" class="asenha-subfield-checkbox-label" style="' . esc_attr( $inline_style ) . '">' . wp_kses_post( $field_label ) . '</label>';

	}

	/**
	 * Render checkbox field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 1.3.0
	 */
	function render_checkbox_subfield( $args ) {

		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_name = $args['field_name'];
		$field_label = $args['field_label'];
		
		$parent_field_id = isset( $args['parent_field_id'] ) ? $args['parent_field_id'] : '';
		$sub_field_id = isset( $args['sub_field_id'] ) ? $args['sub_field_id'] : '';

		if ( in_array( $parent_field_id, array(
			'enable_duplication_for',
			'enable_rest_api_for',
		) ) ) {
			// Default is true/enabled. Usually for options introduced at a later date where the previous default is true/enabled.
			$default_value = true;
		} else {
			// Default is false / checked
			$default_value = false;
		}

		if ( in_array( $parent_field_id, array( 
				'redirect_after_login_for_separate',
				'redirect_after_logout_for_separate',
			) )
			&& ! empty( $sub_field_id )
		) {
			$field_option_value = ( isset( $options[$sub_field_id][$args['field_id']] ) ) ? $options[$sub_field_id][$args['field_id']] : $default_value;
		} else {
			$field_option_value = ( isset( $options[$parent_field_id][$args['field_id']] ) ) ? $options[$parent_field_id][$args['field_id']] : $default_value;		
		}
		
		echo '<input type="checkbox" id="' . esc_attr( $field_name ) . '" class="asenha-subfield-checkbox" name="' . esc_attr( $field_name ) . '" ' . checked( $field_option_value, true, false ) . '>';
		echo '<label for="' . esc_attr( $field_name ) . '" class="asenha-subfield-checkbox-label">' . wp_kses_post( $field_label ) . '</label>';

	}

	/**
	 * Render radio buttons field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 1.3.0
	 */
	function render_radio_buttons_subfield( $args ) {

		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_id = $args['field_id'];
		$field_name = $args['field_name'];
		$field_radios = $args['field_radios'];
		if ( ! empty( $args['field_default'] ) ) {
			$default_value = $args['field_default'];
		} else {
			$default_value = false;
		}
		$field_description = ( isset( $args['field_description'] ) ) ? $args['field_description'] : '';
		$field_option_value = ( isset( $options[$field_id] ) ) ? $options[$field_id] : $default_value;

		echo '<div class="asenha-subfield-radio-button-wrapper">';
		foreach ( $field_radios as $radio_label => $radio_value ) {
			echo '<input type="radio" id="' . esc_attr( $field_id . '_' . $radio_value ) . '" class="asenha-subfield-radio-button" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $radio_value ) . '" ' . checked( $radio_value, $field_option_value, false ) . '>';
			echo '<label for="' . esc_attr( $field_id . '_' . $radio_value ) . '" class="asenha-subfield-radio-button-label">' . wp_kses_post( $radio_label ) . '</label>';
		}
		echo '</div>';
		if ( ! empty( $field_description ) ) {
			echo '<div class="asenha-subfield-description">' . wp_kses_post( $field_description ) . '</div>';
		}

	}

	/**
	 * Render checkboxes field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 6.9.2
	 */
	function render_checkboxes_subfield( $args ) {
		$options = get_option( ASENHA_SLUG_U, array() );		

		$field_id = $args['field_id'];
		$field_name = $args['field_name'];
		$field_options = $args['field_options'];
		$layout = ! empty( $args['layout'] ) ? $args['layout'] : 'horizontal';
		$default_value = ! empty( $args['field_default'] ) ? $args['field_default'] : array();
		$field_option_value = ( isset( $options[$field_id] ) ) ? (array) $options[$field_id] : $default_value;

		echo '<div class="wrapper-for-checkboxes ' . esc_attr( $layout ) . '">';
		foreach ( $field_options as $option_label => $option_value ) {
			echo '<div>';
			echo '<input type="checkbox" id="' . esc_attr( $field_id . '_' . $option_value ) . '" class="asenha-subfield-radio-button" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $option_value ) . '" ' . checked( in_array( $option_value, $field_option_value ), 1, false ) . '>';
			echo '<label for="' . esc_attr( $field_id . '_' . $option_value ) . '" class="asenha-subfield-radio-button-label">' . wp_kses_post( $option_label ) . '</label>';
			echo '</div>';
		}
		echo '</div>';

	}

	/**
	 * Render text field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 1.4.0
	 */
	function render_text_subfield( $args ) {
		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_id = $args['field_id'];
		$field_slug = str_replace( '_', '-', $field_id );
		$field_name = $args['field_name'];
		$field_width_classname = isset( $args['field_width_classname'] ) ? $args['field_width_classname'] : '';
		$field_type = $args['field_type'];
		$field_prefix = $args['field_prefix'];
		$field_suffix = $args['field_suffix'];
		$field_is_read_only = isset( $args['read_only'] ) ? $args['read_only'] : false;
		$field_is_read_only_output = ( $field_is_read_only ) ? ' readonly="readonly"' : '';
		$field_placeholder = isset( $args['field_placeholder'] ) ? $args['field_placeholder'] : '';
		$field_description = $args['field_description'];

		if ( isset( $options[$field_id] ) ) {
			if ( 'live_site_url' == $field_id ) {
				if ( false !== strpos( $options[$field_id], 'http' ) ) {
					$field_option_value = $options[$field_id];
				} else {
					// Legacy support for when base64 encoding was used prior to v7.3.1
					$field_option_value = base64_decode( $options[$field_id] );
				}
			} elseif ( 'login_page_logo_image_width' == $field_id || 'login_page_logo_image_height' == $field_id  ) {
				if ( isset( $options[$field_id] )
				) {
					if ( is_numeric( $options[$field_id] ) ) {
						$field_option_value = $options[$field_id];
					} else {
						$field_option_value = str_replace( 'px', '', $options[$field_id] );
					}
				}
			} else {
				$field_option_value = $options[$field_id];
			}			
		} else {
			if ( 'altcha_secret_key' == $field_id ) {
				if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					$captcha_protection_altcha = new CAPTCHA_Protection_ALTCHA;
					$field_option_value = $captcha_protection_altcha->random_secret();
				} else {
					$field_option_value = '';
				}
			} else {
				$field_option_value = '';
			}
		}

		if ( ! empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-prefix with-suffix';
		} elseif ( ! empty( $field_prefix ) && empty( $field_suffix ) ) {
			$field_classname = ' with-prefix';
		} elseif ( empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-suffix';
		} else {
			$field_classname = '';
		}
		
		if ( ! empty( $field_width_classname ) ) {
			$field_classname .= ' ' . $field_width_classname;
		}
		
		echo wp_kses_post( $field_prefix ) . '<input type="text" id="' . esc_attr( $field_name ) . '" class="asenha-subfield-text' . esc_attr( $field_classname ) . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $field_placeholder ) . '" value="' . esc_attr( $field_option_value ) . '"' . esc_html( $field_is_read_only_output ) .'>' . wp_kses_post( $field_suffix );
		echo '<label for="' . esc_attr( $field_name ) . '" class="asenha-subfield-checkbox-label">' . esc_html( $field_description ) . '</label>';

	}

	/**
	 * Render text field as sub-field of a checkbox field. e.g. in Redirect After Login module
	 *
	 * @since 7.3.3
	 */
	function render_checkbox_field_text_subfield( $args ) {
		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_id = $args['field_id'];
		$field_name = $args['field_name'];
		$field_width_classname = isset( $args['field_width_classname'] ) ? $args['field_width_classname'] : '';
		$field_type = $args['field_type'];
		$field_prefix = $args['field_prefix'];
		$field_suffix = $args['field_suffix'];
		$field_placeholder = isset( $args['field_placeholder'] ) ? $args['field_placeholder'] : '';
		$field_description = $args['field_description'];

		$parent_field_id = isset( $args['parent_field_id'] ) ? $args['parent_field_id'] : '';
		$sub_field_id = isset( $args['sub_field_id'] ) ? $args['sub_field_id'] : '';

		if ( 'redirect_after_login_for_separate' == $parent_field_id 
			&& ! empty( $sub_field_id )
		) {
			$field_option_value = ( isset( $options[$sub_field_id][$field_id] ) ) ? $options[$sub_field_id][$field_id] : '';
		} else {
			$field_option_value = ( isset( $options[$parent_field_id][$field_id] ) ) ? $options[$parent_field_id][$field_id] : '';		
		}
		
		// $field_option_value = ( isset( $options[$field_id] ) ) ? $options[$field_id] : '';
		$field_option_value = ( isset( $options[$parent_field_id . '_slug'][$args['field_id']] ) ) ? $options[$parent_field_id . '_slug'][$field_id] : '';

		if ( ! empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-prefix with-suffix';
		} elseif ( ! empty( $field_prefix ) && empty( $field_suffix ) ) {
			$field_classname = ' with-prefix';
		} elseif ( empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-suffix';
		} else {
			$field_classname = '';		
		}
		
		if ( ! empty( $field_width_classname ) ) {
			$field_classname .= ' ' . $field_width_classname;
		}

		echo wp_kses_post( $field_prefix ) . '<input type="text" id="' . esc_attr( $field_name ) . '" class="asenha-subfield-text' . esc_attr( $field_classname ) . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $field_placeholder ) . '" value="' . esc_attr( $field_option_value ) . '">' . wp_kses_post( $field_suffix );
		echo '<label for="' . esc_attr( $field_name ) . '" class="asenha-subfield-checkbox-label">' . esc_html( $field_description ) . '</label>';
	}
	
	/**
	 * Render description field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 4.6.0
	 */
	function render_description_subfield( $args ) {

		$field_description = $args['field_description'];

		echo '<div class="asenha-subfield-description">' . wp_kses( $field_description, get_kses_with_style_src_svg_ruleset() ) . '</div>';

	}

	/**
	 * Render heading for sub-fields of a toggle/switcher checkbox
	 *
	 * @since 5.0.0
	 */
	function render_subfields_heading( $args ) {

		$subfields_heading = $args['subfields_heading'];

		echo '<div class="asenha-subfields-heading">' . wp_kses_post( $subfields_heading ) . '</div>';

	}

	/**
	 * Render password field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 4.1.0
	 */
	function render_password_subfield( $args ) {

		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_id = $args['field_id'];
		$field_name = $args['field_name'];
		$field_type = $args['field_type'];
		$field_prefix = $args['field_prefix'];
		$field_suffix = $args['field_suffix'];
		$field_description = $args['field_description'];
		$field_option_value = ( isset( $options[$args['field_id']] ) ) ? $options[$args['field_id']] : '';

		if ( ! empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-prefix with-suffix';
		} elseif ( ! empty( $field_prefix ) && empty( $field_suffix ) ) {
			$field_classname = ' with-prefix';
		} elseif ( empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-suffix';
		} else {
			$field_classname = '';		
		}

		$placeholder = '';

		echo wp_kses_post( $field_prefix ) . '<input type="password" id="' . esc_attr( $field_name ) . '" class="asenha-subfield-password' . esc_attr( $field_classname ) . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $placeholder ) . '" size="24" autocomplete="off" value="' . esc_attr( $field_option_value ) . '">' . wp_kses_post( $field_suffix );
		echo '<label for="' . esc_attr( $field_name ) . '" class="asenha-subfield-checkbox-label">' . esc_html( $field_description ) . '</label>';

	}

	/**
	 * Render number field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 1.4.0
	 */
	function render_number_subfield( $args ) {

		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_id = $args['field_id'];
		$field_name = $args['field_name'];
		$field_type = $args['field_type'];
		$field_prefix = $args['field_prefix'];
		$field_suffix = $args['field_suffix'];
		$field_intro = $args['field_intro'];
		$field_placeholder = isset( $args['field_placeholder'] ) ? $args['field_placeholder'] : '';
		$field_min = isset( $args['field_min'] ) ? $args['field_min'] : 1;
		$field_max = isset( $args['field_max'] ) ? $args['field_max'] : 10;
		$field_description = isset( $args['field_description'] ) ? $args['field_description'] : '';
		$field_option_value = ( isset( $options[$args['field_id']] ) ) ? $options[$args['field_id']] : '';

		if ( ! empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-prefix with-suffix';
		} elseif ( ! empty( $field_prefix ) && empty( $field_suffix ) ) {
			$field_classname = ' with-prefix';
		} elseif ( empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-suffix';
		} else {
			$field_classname = '';		
		}

		echo '<div class="asenha-subfield-number-wrapper">';

		if ( ! empty( $field_intro ) ) {
			echo '<div class="asenha-subfield-number-intro">' . wp_kses_post( $field_intro ) . '</div>';
		}

		echo '<div>' . wp_kses_post( $field_prefix ) . '<input type="number" id="' . esc_attr( $field_name ) . '" class="asenha-subfield-number' . esc_attr( $field_classname ) . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $field_placeholder ) . '" step="1" min="' . esc_attr( $field_min ) . '" max="' . esc_attr( $field_max ) . '" value="' . esc_attr( $field_option_value ) . '">' . wp_kses_post( $field_suffix ) . '</div>';

		if ( ! empty( $field_description ) ) {
			echo '<div class="asenha-subfield-number-description">' . wp_kses_post( $field_description ) . '</div>';
		}

		echo '</div>';

	}

	/**
	 * Render select field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 1.4.0
	 */
	function render_select_subfield( $args ) {

		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}
		
		$field_id = $args['field_id'];
		$field_name = $args['field_name'];
		$field_type = $args['field_type'];
		$field_prefix = $args['field_prefix'];
		$field_suffix = $args['field_suffix'];
		$field_select_options = $args['field_select_options'];
		$field_select_default = $args['field_select_default'];
		$field_intro = $args['field_intro'];
		$field_description = $args['field_description'];
		if ( ! empty( $field_select_default ) ) {
			$default_value = $field_select_default;
		} else {
			$default_value = false;
		}
		$field_option_value = ( isset( $options[$field_id] ) ) ? $options[$field_id] : $default_value;

		if ( ! empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-prefix with-suffix';
		} elseif ( ! empty( $field_prefix ) && empty( $field_suffix ) ) {
			$field_classname = ' with-prefix';
		} elseif ( empty( $field_prefix ) && ! empty( $field_suffix ) ) {
			$field_classname = ' with-suffix';
		} else {
			$field_classname = '';		
		}

		$display_none_on_load = isset( $args['display_none_on_load'] ) ? $args['display_none_on_load'] : false;
		if ( $display_none_on_load ) {
			$inline_style = 'display:none;';
		} else {
			$inline_style = '';
		}

		echo '<div class="asenha-subfield-select-wrapper">';

		if ( ! empty( $field_intro ) ) {
			echo '<div class="asenha-subfield-select-intro">' . wp_kses_post( $field_intro ) . '</div>';
		}

		echo '<div style="' . esc_attr( $inline_style ) . '" class="asenha-subfield-select-inner">' . wp_kses_post( $field_prefix );
		echo '<select name="' . esc_attr( $field_name ) . '" class="asenha-subfield-select' . esc_attr( $field_classname ) . '">';

		foreach ( $field_select_options as $label => $value ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, $field_option_value, false ) . '>' . esc_html( $label ) . '</option>';
		}

		echo '</select>';
		echo wp_kses_post( $field_suffix ) . '</div>';

		if ( ! empty( $field_description ) ) {
			echo '<div class="asenha-subfield-select-description">' . wp_kses_post( $field_description ) . '</div>';
		}

		echo '</div>';

	}

	/**
	 * Render textarea field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 2.3.0
	 */
	function render_textarea_subfield( $args ) {

		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_id = $args['field_id'];
		$field_slug = $args['field_slug'];
		$field_name = $args['field_name'];
		$field_type = $args['field_type'];
		$field_rows = $args['field_rows'];
		$field_intro = $args['field_intro'];
		$field_description = $args['field_description'];
		$field_placeholder = isset( $args['field_placeholder'] ) ? $args['field_placeholder'] : '';

		// Always load textarea content from robots.txt URL, whether it's a real, custom-made robots.txt file or a virtual one generated by WordPress
		if ( 'robots_txt_content' == $field_id ) {
			if ( array_key_exists( 'manage_robots_txt', $options ) ) { // Manage robots.txt feature has been enabled before
				if ( ! $options['manage_robots_txt'] ) { // Manage robots.txt feature is NOT enabled
					if ( array_key_exists( 'robots_txt_content', $options ) && $options['robots_txt_content'] ) {
						$field_option_value = $options['robots_txt_content'];
					} else {
						$robots_txt_content = wp_remote_get( get_site_url() . '/robots.txt' );
						$robots_txt_content = esc_textarea( trim( wp_remote_retrieve_body( $robots_txt_content ) ) );
						$field_option_value = $robots_txt_content;						
					}
				} else { // Manage robots.txt feature is enabled
					if ( array_key_exists( 'robots_txt_content', $options ) && $options['robots_txt_content'] ) {
						$field_option_value = $options['robots_txt_content'];
					} else {
						$robots_txt_content = wp_remote_get( get_site_url() . '/robots.txt' );
						$robots_txt_content = esc_textarea( trim( wp_remote_retrieve_body( $robots_txt_content ) ) );
						$field_option_value = $robots_txt_content;						
					}
				}
			} else { // Manage robots.txt feature has never been enabled yet
					$robots_txt_content = wp_remote_get( get_site_url() . '/robots.txt' );
					$robots_txt_content = esc_textarea( trim( wp_remote_retrieve_body( $robots_txt_content ) ) );
					$field_option_value = $robots_txt_content;				
			}
		} else {
			$field_option_value = ( isset( $options[$args['field_id']] ) ) ? $options[$args['field_id']] : '';
		}

		echo '<div class="asenha-subfield-textarea-wrapper">';

		if ( ! empty( $field_intro ) ) {
			echo '<div class="asenha-subfield-textarea-intro">' . wp_kses_post( $field_intro ) . '</div>';
		}


		echo '<textarea rows="' . esc_attr( $field_rows ) . '" class="asenha-subfield-textarea" id="' . esc_attr( $field_name ) . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $field_placeholder ) . '">' . esc_textarea( $field_option_value ) . '</textarea>';

		if ( ! empty( $field_description ) ) {
			echo '<div class="asenha-subfield-textarea-description">' . wp_kses_post( $field_description ) . '</div>';
		}

		echo '</div>';

	}

	/**
	 * Render textarea field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 2.3.0
	 */
	function render_wpeditor_subfield( $args ) {
		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_id = $args['field_id'];
		$field_slug = $args['field_slug'];
		$field_name = $args['field_name'];
		$field_type = $args['field_type'];
		$field_intro = $args['field_intro'];
		$field_description = $args['field_description'];
		$field_placeholder = isset( $args['field_placeholder'] ) ? $args['field_placeholder'] : '';
		$field_option_value = ( isset( $options[$args['field_id']] ) ) ? $options[$args['field_id']] : '';
		$editor_settings = $args['editor_settings']; // https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/

		echo '<div class="asenha-subfield-wpeditor-wrapper">';

		if ( ! empty( $field_intro ) ) {
			echo '<div class="asenha-subfield-wpeditor-intro">' . wp_kses_post( $field_intro ) . '</div>';
		}

		$content = $field_option_value;
		$editor_id = str_replace( array( '[', ']' ), array( '--', '' ), $field_name );
		// vi( $editor_id, '', 'for ' . $field_name );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wp_editor( $content , $editor_id, $editor_settings );

		if ( ! empty( $field_description ) ) {
			echo '<div class="asenha-subfield-wpeditor-description">' . wp_kses_post( $field_description ) . '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render custom HTML subfield
	 *
	 * @since 5.3.0
	 */
	function render_custom_html( $args ) {
		
		echo wp_kses( $args['html'], get_kses_with_custom_html_ruleset() );
		
	}
	
	/**
	 * Render media subfield
	 * 
	 * @since 6.2.2
	 */
	function render_media_subfield( $args ) {

		$field_id = $args['field_id'];
		$field_slug = $args['field_slug'];
		$field_name = $args['field_name'];
		$field_media_frame_title = $args['field_media_frame_title'];
		$field_media_frame_multiple = $args['field_media_frame_multiple'];
		$field_media_frame_library_type = $args['field_media_frame_library_type'];
		$field_media_frame_button_text = $args['field_media_frame_button_text'];
		$field_intro = $args['field_intro'];
		$field_description = $args['field_description'];
		
		$options = get_option( $args['option_name'], array() );
		$field_option_value = ( isset( $options[$field_id] ) ) ? $options[$field_id] : '';
		?>
		<div class="media-subfield-wrapper">
			<input id="<?php echo esc_attr( $field_slug ); ?>" class="image-picker" type="text" size="40" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_url( $field_option_value ); ?>" />
			<button id="<?php echo esc_attr( $field_slug ); ?>-button" class="image-picker-button button-secondary"><?php echo __( 'Select an Image', 'admin-site-enhancements' ); ?></button>
			<?php
			if ( ! empty( $field_description ) ) {
				echo '<div class="asenha-subfield-description media-subfield">' . wp_kses_post( $field_description ) . '</div>';
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render media subfield
	 * 
	 * @since 6.2.2
	 */
	function render_color_picker_subfield( $args ) {

		$common_methods = new Common_Methods;

		$field_id = $args['field_id'];
		$field_slug = $args['field_slug'];
		$field_name = $args['field_name'];
		$field_intro = $args['field_intro'];
		$field_description = $args['field_description'];
		$field_default_value = $args['field_default_value'];

		$options = get_option( $args['option_name'], array() );
		$field_option_value = ( isset( $options[$field_id] ) ) ? $options[$field_id] : $field_default_value;
		?>
		<div class="color-subfield-wrapper">
			<input type="text" id="<?php echo esc_attr( $field_slug ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $common_methods->sanitize_hex_color( $field_option_value ) ); ?>" data-default-color="<?php echo esc_attr( $common_methods->sanitize_hex_color( $field_default_value ) ); ?>" class="color-picker"/>
		</div>
		<?php
	}

	/**
	 * Render text field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 1.4.0
	 */
	function render_content_toggler( $args ) {
		$field_id = $args['field_id'];
		$field_slug = $args['field_slug'];
		$field_slug = str_replace( '_', '-', $field_id );

		$show_text = isset( $args['show_text'] ) ? $args['show_text'] : '';
		$hide_text = isset( $args['hide_text'] ) ? $args['hide_text'] : '';
		$content_selector = isset( $args['content_selector'] ) ? $args['content_selector'] : '';
		?>
		<div class="subfield-content-toggler <?php echo esc_attr( $field_slug ); ?>"><a href="#" data-show-text="<?php echo esc_attr( $show_text ); ?>" data-hide-text="<?php echo esc_attr( $hide_text ); ?>" data-target-selector="<?php echo esc_attr( $content_selector ); ?>" class="asenha-content-toggler" data-expanded="no"><?php echo esc_html( $show_text ); ?> <span>▼</span></a></div>
		<?php
	}

	/**
	 * Render sortable menu field
	 *
	 * @since 2.0.0
	 */
	function render_sortable_menu() {
		$triangle_right_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 16 16"><path fill="currentColor" d="M14.222 6.687a1.5 1.5 0 0 1 0 2.629l-10 5.499A1.5 1.5 0 0 1 2 13.5V2.502a1.5 1.5 0 0 1 2.223-1.314z"/></svg>';

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			?>
			<div class="module-description"><?php echo esc_html__( 'Drag and drop menu items to the desired position. Optionally change 3rd party plugin/theme\'s menu item titles or hide some items until toggled by clicking "Show All" at the bottom of the admin menu. You can also choose to always hide menu items for all or some user roles.', 'admin-site-enhancements' ); ?></div>
			<?php
        } else {
			?>
			<div class="module-description"><?php echo esc_html__( 'Drag and drop menu items to the desired position. Optionally change 3rd party plugin/theme\'s menu item titles or hide some items until toggled by clicking "Show All" at the bottom of the admin menu.', 'admin-site-enhancements' ); ?></div>
			<?php        	
        }
		?>
		<ul id="custom-admin-menu" class="menu ui-sortable">
		<?php

		global $menu, $submenu;
		
		$common_methods = new Common_Methods;		
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		$options = isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array();

		// Set menu items to be excluded from title renaming. These are from WordPress core.
		$renaming_not_allowed = array( 
			'menu-dashboard', 
			'menu-pages', 
			// 'menu-posts', 
			'menu-media', 
			'menu-comments', 
			'menu-appearance', 
			'menu-plugins', 
			'menu-users', 
			'menu-tools', 
			'menu-settings' 
		);

		// Get custom menu item titles
		if ( array_key_exists( 'custom_menu_titles', $options ) ) {
			$custom_menu_titles = $options['custom_menu_titles'];
			$custom_menu_titles = explode( ',', $custom_menu_titles );
		} else {
			$custom_menu_titles = array();
		}

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Get data on menu items to be hidden by user roles
			if ( isset( $options['custom_menu_always_hidden'] ) ) {
				$menu_always_hidden = $options['custom_menu_always_hidden'];
				$menu_always_hidden = json_decode( $menu_always_hidden, true );
			} else {
				$menu_always_hidden = array();
			}
        }

		// Get menu items hidden by toggle
		$menu_hidden_by_toggle = $common_methods->get_menu_hidden_by_toggle();

		$i = 1;

		// Check if there's an existing custom menu order data stored in options

		if ( array_key_exists( 'custom_menu_order', $options ) ) {

			$custom_menu = $options['custom_menu_order'];
			$custom_menu = explode( ',', $custom_menu );

			$menu_key_in_use = array();

			// Render sortables with data in custom menu order

			foreach ( $custom_menu as $custom_menu_item ) {

				foreach ( $menu as $menu_key => $menu_info ) {

			        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
						if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
							$menu_item_title = $menu_info[2];
							$menu_item_id = $menu_info[2];
							$submenu_sortable_id = '';
							$menu_url_fragment = '';
						} else {
							$menu_item_title = $menu_info[0];
							$menu_item_id = $menu_info[5];
							$submenu_sortable_id = $menu_info[2];
							$menu_url_fragment = $menu_info[2];
						}			        	

						// Check if submenu exists
						if ( array_key_exists( $menu_info[2], $submenu ) && @is_countable( $submenu[$menu_info[2]] ) && @sizeof( $submenu[$menu_info[2]] ) > 0 ) {
							$submenu_exists = true;
						} else {
							$submenu_exists = false;						
						}		        	
			        } else {
						if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
							$menu_item_title = $menu_info[2];
							$menu_item_id = $menu_info[2];
						} else {
							$menu_item_title = $menu_info[0];
							$menu_item_id = $menu_info[5];
						}
						$menu_url_fragment = '';
			        }

					if ( $custom_menu_item == $menu_item_id ) {

						$menu_item_id_transformed = $common_methods->transform_menu_item_id( $menu_item_id );

						if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
							$is_custom_menu = $this->is_custom_menu_item__premium_only( $menu_item_id );						
						} else {
							$is_custom_menu = 'no';
						}

						?>
						<li id="<?php echo esc_attr( $menu_item_id ); ?>" class="menu-item parent-menu-item menu-item-depth-0" data-custom-menu-item="<?php echo esc_attr( $is_custom_menu ); ?>">
							<div class="menu-item-bar">
								<div class="menu-item-handle">
									<span class="dashicons dashicons-menu"></span>
									<div class="item-title">
										<div class="title-wrapper">
											<span class="menu-item-title">
											<?php
											if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
												$separator_name_ori = $menu_info[2];
												$separator_name = str_replace( 'separator', 'Separator-', $separator_name_ori );
												$separator_name = str_replace( '--last', '-Last', $separator_name );
												$separator_name = str_replace( '--woocommerce', '--WooCommerce', $separator_name );
												echo '~~ ' . esc_html( $separator_name ) . ' ~~';
											} else {
												if ( in_array( $menu_item_id, $renaming_not_allowed ) ) {
													$menu_item_title = $menu_info[0];
													echo wp_kses_post( $common_methods->strip_html_tags_and_content( $menu_item_title ) );
												} else {

													// Get defaul/custom menu item title
													foreach ( $custom_menu_titles as $custom_menu_title ) {

														// At this point, $custom_menu_title value looks like toplevel_page_snippets__Code Snippets

														$custom_menu_title = explode( '__', $custom_menu_title );

														if ( $custom_menu_title[0] == $menu_item_id ) {
															$menu_item_title = $common_methods->strip_html_tags_and_content( $custom_menu_title[1] ); // e.g. Code Snippets
															break; // stop foreach loop so $menu_item_title is not overwritten in the next iteration
														} else {
															$menu_item_title = $common_methods->strip_html_tags_and_content( $menu_info[0] );
														}

													}

													?>
													<input type="text" value="<?php echo wp_kses_post( $menu_item_title ); ?>" class="menu-item-custom-title" data-menu-item-id="<?php echo esc_attr( $menu_item_id ); ?>">
													<?php
												}
											}
											?>
											</span><!-- end of .menu-item-title -->
										<?php
								        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
											if ( $submenu_exists ) {
												?>
												<div class="submenu-toggle"><span class="arrow-right"><?php echo wp_kses( $triangle_right_icon, $common_methods->get_kses_extended_ruleset() ); ?></span><span class="submenu-text"><?php echo esc_html__( 'Submenu', 'admin-site-enhancements' ); ?></span></div>
												<?php
											}								        	
								        }
										?>
										</div><!-- end of .title-wrapper -->
										<div class="options-for-hiding">
											<?php
									        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
									        	$hide_text = __( 'Hide', 'admin-site-enhancements' );
									        	$checkbox_class = 'parent-menu-hide-checkbox-prm';
									        } else {
									        	$hide_text = __( 'Hide until toggled', 'admin-site-enhancements' );
									        	$checkbox_class = 'parent-menu-hide-checkbox';	        	
									        }
								        	?>
											<label class="menu-item-checkbox-label">
												<?php
													if ( in_array( $custom_menu_item, $menu_hidden_by_toggle ) ) {
													?>
												<input type="checkbox" id="hide-status-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="<?php echo esc_attr( $checkbox_class ); ?>" data-menu-item-title="<?php echo esc_attr( $common_methods->strip_html_tags_and_content( $menu_item_title ) ); ?>" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-item-id-ori="<?php echo esc_attr( $menu_item_id ); ?>" data-menu-url-fragment="<?php echo esc_attr( $menu_url_fragment ); ?>" checked>
												<span><?php echo esc_html( $hide_text ); ?></span>
													<?php
													} else {
													?>
												<input type="checkbox" id="hide-status-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="<?php echo esc_attr( $checkbox_class ); ?>" data-menu-item-title="<?php echo esc_attr( $common_methods->strip_html_tags_and_content( $menu_item_title ) ); ?>" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-item-id-ori="<?php echo esc_attr( $menu_item_id ); ?>" data-menu-url-fragment="<?php echo esc_attr( $menu_url_fragment ); ?>">
												<span><?php echo esc_html( $hide_text ); ?></span>
													<?php
													}
												?>
											</label>
											<?php
									        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
											?>
												<div class="options-toggle" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>">
													<span class="arrow-right"><?php echo wp_kses( $triangle_right_icon, $common_methods->get_kses_extended_ruleset() ); ?></span><span class="options-text"><?php echo esc_html__( 'Options', 'admin-site-enhancements' ); ?></span>
												</div>
											<?php
									        }
											?>
										</div><!-- end of .options-for-hiding -->
									</div><!-- end of .item-title -->
								</div><!-- end of .menu-item-handle -->
							</div><!-- end of .menu-item-bar -->
							<?php
					        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
								$menu_required_capability = $menu_info[1];
								
								$always_hide_checked_status = '';
								$all_or_selected_roles = '';
								if ( is_array( $menu_always_hidden ) && ! empty( $menu_always_hidden ) ) {
									foreach( $menu_always_hidden as $hidden_menu_item_id => $info ) {
										$hidden_menu_item_id = $common_methods->restore_menu_item_id( $hidden_menu_item_id );
										if ( $hidden_menu_item_id == $menu_item_id) {
											if ( isset( $info['always_hide'] ) && $info['always_hide'] ) {
												$always_hide_checked_status = ' checked';
											}				
											if ( isset( $info['always_hide_for'] ) && $info['always_hide_for'] ) {
												$all_or_selected_roles = $info['always_hide_for'];
											}

										}
									}									
								}

								$this->render_sortable_menu_options__premium_only( $menu_item_id, $menu_required_capability, $always_hide_checked_status, $all_or_selected_roles, true );
					        }

						$i = 1;

				        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
							if ( $submenu_exists ) {
					        	// Render submenu
								$menu_item_id = $menu_info[2];
								$this->render_sortable_submenu__premium_only( $submenu, $menu_item_id, $submenu_sortable_id );
							}
				        }
						?>
							<div class="remove-menu-item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="#bbbbbb" d="M24 2.4L21.6 0L12 9.6L2.4 0L0 2.4L9.6 12L0 21.6L2.4 24l9.6-9.6l9.6 9.6l2.4-2.4l-9.6-9.6z"/></svg></div>
						</li>
						<?php
						$menu_key_in_use[] = $menu_key;
					}
				}
			}

			// Render the rest of the current menu towards the end of the sortables

			foreach ( $menu as $menu_key => $menu_info ) {

				if ( ! in_array( $menu_key, $menu_key_in_use ) ) {

			        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
						if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
							$menu_item_id = $menu_info[2];
							$menu_url_fragment = '';
						} else {
							$menu_item_id = $menu_info[5];
							$menu_url_fragment = $menu_info[2];
						}			        	
						$submenu_sortable_id = $menu_info[2];
						$menu_item_title = $menu_info[0];

						// Check if submenu exists
						if ( array_key_exists( $menu_info[2], $submenu ) && @is_countable( $submenu[$menu_info[2]] ) && @sizeof( $submenu[$menu_info[2]] ) > 0 ) {
							$submenu_exists = true;
						} else {
							$submenu_exists = false;						
						}
			        } else {
						if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
							$menu_item_id = $menu_info[2];
						} else {
							$menu_item_id = $menu_info[5];
						}			        	
						$menu_item_title = $menu_info[0];
						$menu_url_fragment = '';
			        }
			        
			        // Strip tags
					$menu_item_title = $common_methods->strip_html_tags_and_content( $menu_item_title );

					// Exclude Show All/Less toggles

					if ( false === strpos( $menu_item_id, 'toplevel_page_asenha_' ) ) {

						$menu_item_id_transformed = $common_methods->transform_menu_item_id( $menu_item_id );

						if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
							$is_custom_menu = $this->is_custom_menu_item__premium_only( $menu_item_id );						
						} else {
							$is_custom_menu = 'no';
						}

						?>
						<li id="<?php echo esc_attr( $menu_item_id ); ?>" class="menu-item parent-menu-item menu-item-depth-0" data-custom-menu-item="<?php echo esc_attr( $is_custom_menu ); ?>">
							<div class="menu-item-bar">
								<div class="menu-item-handle">
									<span class="dashicons dashicons-menu"></span>
									<div class="item-title">
										<div class="title-wrapper">
											<span class="menu-item-title">
												<?php
												if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
													$separator_name_ori = $menu_info[2];
													$separator_name = str_replace( 'separator', 'Separator-', $separator_name_ori );
													$separator_name = str_replace( '--last', '-Last', $separator_name );
													$separator_name = str_replace( '--woocommerce', '--WooCommerce', $separator_name );
													echo '~~ ' . esc_html( $separator_name ) . ' ~~';
												} else {
												?>
													<input type="text" value="<?php echo wp_kses_post( $menu_item_title ); ?>" class="menu-item-custom-title" data-menu-item-id="<?php echo esc_attr( $menu_item_id ); ?>">
												<?php													
												}
												?>
											</span>
											<?php
									        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
												if ( $submenu_exists ) {
													?>
													<div class="submenu-toggle"><span class="arrow-right"><?php echo wp_kses( $triangle_right_icon, $common_methods->get_kses_extended_ruleset() ); ?></span><span class="submenu-text"><?php echo esc_html__( 'Submenu', 'admin-site-enhancements' ); ?></span></div>
													<?php
												}
											}
											?>
										</div>
										<div class="options-for-hiding">
											<?php
									        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
									        	$hide_text = __( 'Hide', 'admin-site-enhancements' );
									        	$checkbox_class = 'parent-menu-hide-checkbox-prm';
									        } else {
									        	$hide_text = __( 'Hide until toggled', 'admin-site-enhancements' );
									        	$checkbox_class = 'parent-menu-hide-checkbox';	        	
									        }
								        	?>
								        	<label class="menu-item-checkbox-label">
												<input type="checkbox" id="hide-status-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="<?php echo esc_attr( $checkbox_class ); ?>" data-menu-item-title="<?php echo esc_attr( $common_methods->strip_html_tags_and_content( $menu_item_title ) ); ?>" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-item-id-ori="<?php echo esc_attr( $menu_item_id ); ?>" data-menu-url-fragment="<?php echo esc_attr( $menu_url_fragment ); ?>">
												<span><?php echo esc_html( $hide_text ); ?></span>
											</label>
											<?php
									        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
											?>
												<div class="options-toggle" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>">
													<span class="arrow-right"><?php echo wp_kses( $triangle_right_icon, $common_methods->get_kses_extended_ruleset() ); ?></span><span class="options-text">Options</span>
												</div>
											<?php
									        }
											?>
										</div><!-- end of .options-for-hiding -->
									</div><!-- end of .item-title -->
								</div><!-- end of .menu-item-handle -->
							</div><!-- end of .menu-item-bar -->
							<?php
					        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
								$menu_required_capability = $menu_info[1];
								$always_hide_checked_status = '';
								$all_or_selected_roles = '';
								$this->render_sortable_menu_options__premium_only( $menu_item_id, $menu_required_capability, $always_hide_checked_status, $all_or_selected_roles, true );
					        }

						$i = 1;

				        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
							if ( $submenu_exists ) {
					        	// Render submenu
								$menu_item_id = $menu_info[2];
								$this->render_sortable_submenu__premium_only( $submenu, $menu_item_id, $submenu_sortable_id );
							}
				        }
						?>
							<div class="remove-menu-item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="#bbbbbb" d="M24 2.4L21.6 0L12 9.6L2.4 0L0 2.4L9.6 12L0 21.6L2.4 24l9.6-9.6l9.6 9.6l2.4-2.4l-9.6-9.6z"/></svg></div>
						</li><!-- end of .menu-item -->
						<?php
					}
				}
			}
		} else { // No custom menu order has been saved yet

			// Render sortables with existing items in the admin menu

			foreach ( $menu as $menu_key => $menu_info ) {

		        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
						$menu_item_id = $menu_info[2];
						$submenu_sortable_id = '';
						$menu_url_fragment = '';
					} else {
						$menu_item_id = $menu_info[5];
						$submenu_sortable_id = $menu_info[2];
						$menu_url_fragment = $menu_info[2];
					}			        	
					$menu_item_title = $menu_info[0];

					// Check if submenu exists
					if ( array_key_exists( $menu_info[2], $submenu ) && @is_countable( $submenu[$menu_info[2]] ) && @sizeof( $submenu[$menu_info[2]] ) > 0 ) {
						$submenu_exists = true;
					} else {
						$submenu_exists = false;						
					}
		        } else {
					if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
						$menu_item_id = $menu_info[2];
					} else {
						$menu_item_id = $menu_info[5];
					}
					$menu_url_fragment = '';
					$menu_item_title = $menu_info[0];
		        }

				$menu_item_id_transformed = $common_methods->transform_menu_item_id( $menu_item_id );

		        // Strip tags
				$menu_item_title = $common_methods->strip_html_tags_and_content( $menu_item_title );

				if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					$is_custom_menu = $this->is_custom_menu_item__premium_only( $menu_item_id );						
				} else {
					$is_custom_menu = 'no';
				}

				?>
				<li id="<?php echo esc_attr( $menu_item_id ); ?>" class="menu-item parent-menu-item menu-item-depth-0" data-custom-menu-item="<?php echo esc_attr( $is_custom_menu ); ?>">
					<div class="menu-item-bar">
						<div class="menu-item-handle">
							<span class="dashicons dashicons-menu"></span>
							<div class="item-title">
								<div class="title-wrapper">
									<span class="menu-item-title">
									<?php
									if ( false !== strpos( $menu_info[4], 'wp-menu-separator' ) ) {
										$separator_name_ori = $menu_info[2];
										$separator_name = str_replace( 'separator', 'Separator-', $separator_name_ori );
										$separator_name = str_replace( '--last', '-Last', $separator_name );
										$separator_name = str_replace( '--woocommerce', '--WooCommerce', $separator_name );
										echo '~~ ' . esc_html( $separator_name ) . ' ~~';
									} else {
										if ( in_array( $menu_item_id, $renaming_not_allowed ) ) {
												echo wp_kses_post( $menu_item_title );
										} else {
											?>
											<input type="text" value="<?php echo wp_kses_post( $menu_item_title ); ?>" class="menu-item-custom-title" data-menu-item-id="<?php echo esc_attr( $menu_item_id ); ?>">
											<?php
										}
									}
									?>
									</span>
									<?php
							        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
										if ( $submenu_exists ) {
											?>
											<div class="submenu-toggle"><span class="arrow-right"><?php echo wp_kses( $triangle_right_icon, $common_methods->get_kses_extended_ruleset() ); ?></span><span class="submenu-text"><?php echo esc_html__( 'Submenu', 'admin-site-enhancements' ); ?></span></div>
											<?php
										}
									}
									?>
								</div><!-- end of .title-wrapper -->
								<div class="options-for-hiding">
									<?php
							        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
							        	$hide_text = __( 'Hide', 'admin-site-enhancements' );
							        	$checkbox_class = 'parent-menu-hide-checkbox-prm';
							        } else {
							        	$hide_text = __( 'Hide until toggled', 'admin-site-enhancements' );
							        	$checkbox_class = 'parent-menu-hide-checkbox';	        	
							        }
						        	?>
									<label class="menu-item-checkbox-label">
										<input type="checkbox" id="hide-status-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="<?php echo esc_attr( $checkbox_class ); ?>" data-menu-item-title="<?php echo esc_attr( $common_methods->strip_html_tags_and_content( $menu_item_title ) ); ?>" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-item-id-ori="<?php echo esc_attr( $menu_item_id ); ?>" data-menu-url-fragment="<?php echo esc_attr( $menu_url_fragment ); ?>">
										<span><?php echo esc_html( $hide_text ); ?></span>
									</label>
									<?php
							        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
									?>
										<div class="options-toggle" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>">
											<span class="arrow-right"><?php echo wp_kses( $triangle_right_icon, $common_methods->get_kses_extended_ruleset() ); ?></span><span class="options-text">Options</span>
										</div>
									<?php
							        }
									?>
								</div><!-- end of .options-for-hiding -->
							</div><!-- end of .item-title -->
						</div><!-- end of .menu-item-handle -->
					</div><!-- end of .menu-item-bar -->
				<?php
		        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					$menu_required_capability = $menu_info[1];					
					$always_hide_checked_status = '';
					$all_or_selected_roles = '';
					$this->render_sortable_menu_options__premium_only( $menu_item_id, $menu_required_capability, $always_hide_checked_status, $all_or_selected_roles, true );
		        }

				$i = 1;

		        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					if ( $submenu_exists ) {
				        // Render submenu
						$menu_item_id = $menu_info[2];
						$this->render_sortable_submenu__premium_only( $submenu, $menu_item_id, $submenu_sortable_id );
					}
				}
				?>
					<div class="remove-menu-item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="#bbbbbb" d="M24 2.4L21.6 0L12 9.6L2.4 0L0 2.4L9.6 12L0 21.6L2.4 24l9.6-9.6l9.6 9.6l2.4-2.4l-9.6-9.6z"/></svg></div>
				</li>
				<?php
			}
		}
		?>
		</ul>
		<?php

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	?>
        	<div class="admin-menu-actions-wrapper">
        		<div class="admin-menu-main-actions">
	        		<a id="add-menu-separator" class="admin-menu-action" href="#"><?php echo esc_html__( 'Add Separator', 'admin-site-enhancements' ); ?></a>
        		</div>
	        	<div class="reset-menu-wrapper">
	        		<img src="<?php echo esc_attr( ASENHA_URL ); ?>assets/img/oval.svg" class="reset-menu-spinner" style="display: none;" /><a href="#" id="reset-menu"><?php echo esc_html__( 'Reset Menu', 'admin-site-enhancements' ); ?></a>
	        	</div>
        	</div>
        	<?php
        }

		// Hidden input field to store custom menu order (from options as is, or sortupdate) upon clicking Save Changes. 

        $field_id = 'custom_menu_order';
		$field_option_value = ( isset( $options[$field_id] ) ) ? $options[$field_id] : '';

		echo '<input type="hidden" id="' . esc_attr( $field_id ) . '" class="asenha-subfield-text" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_option_value ) . '">';

		// Hidden input field to store hidden menu items (from options as is, or 'Hide' checkbox clicks) upon clicking Save Changes.
		$this->output_admin_menu_organizer_hidden_field( 'custom_menu_hidden' );

		// Hidden input field to store custom menu titles (from options as is, or custom values entered on each non-WP-default menu items.
		$this->output_admin_menu_organizer_hidden_field( 'custom_menu_titles' );

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {

			// Hidden input field to store menu items that should always be hidden for all or some roles upon clicking Save Changes.
			$this->output_admin_menu_organizer_hidden_field( 'custom_menu_always_hidden' );
			
			// Hidden input field to store custom submenu items order upon clicking Save Changes.
			$this->output_admin_menu_organizer_hidden_field( 'custom_submenus_order' );
			
			// Hidden input field to store submenu items that should always be hidden for all or some roles upon clicking Save Changes.
			$this->output_admin_menu_organizer_hidden_field( 'custom_submenu_always_hidden' );
			
			// Hidden input field to store new separators upon clicking Save Changes.
			$this->output_admin_menu_organizer_hidden_field( 'custom_menu_new_separators' );
			
        }
        
	}
	
	/**
	 * Output hidden field
	 * 
	 * @since 6.9.13
	 */
	public function output_admin_menu_organizer_hidden_field( $field_id ) {
            $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
            $options = isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array();

			$field_name = $field_id;
			$field_option_value = ( isset( $options[$field_id] ) ) ? $options[$field_id] : '';

			echo '<input type="hidden" id="' . esc_attr( $field_name ) . '" class="asenha-subfield-text" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( stripslashes( $field_option_value ) ) . '">';
	}

	/**
	 * Render sortable submenu items
	 *
	 * @since 5.1.0
	 */
	function render_sortable_submenu__premium_only( $submenu, $menu_item_id, $submenu_sortable_id ) {
		$triangle_right_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 16 16"><path fill="currentColor" d="M14.222 6.687a1.5 1.5 0 0 1 0 2.629l-10 5.499A1.5 1.5 0 0 1 2 13.5V2.502a1.5 1.5 0 0 1 2.223-1.314z"/></svg>';
		
		$common_methods = new Common_Methods;
		$submenu_sortable_id = $common_methods->transform_menu_item_id( $submenu_sortable_id );

		// Exception/exclusion: $menu_item_id which should have their $submenu_item_title stripped of HTML tags but have the text/title intact
		$menu_item_ids_for_preserving_submenu_titles = array(
			'mainwp_tab', // https://wordpress.org/plugins/mainwp/
			'cff-top', // https://wordpress.org/plugins/custom-facebook-feed/
		);
		?>
		<div class="submenu-wrapper" style="display:none;">
			<ul id="<?php echo esc_attr( $submenu_sortable_id ); ?>" class="submenu-sortable">
				<?php
				foreach( $submenu[$menu_item_id] as $item_key => $item_info ) {
					$submenu_item_title = isset( $item_info[0] ) ? $item_info[0] : '';
					// Very rarely, submenu items use special characters / symbols, which breaks the JS. Let's remove it.
					$submenu_item_title = str_replace( 
						array( 
							'↳', 
							'➤', // e.g. WP fail2ban
							'⭐️', 
							'✛',
	                        '<img draggable="false" role="img" class="emoji" alt="👋" src="https://s.w.org/images/core/emoji/15.0.3/svg/1f44b.svg">', // WOW-Plugin (Float Menu plugin) sub-menu 'Hey' has this image at the end of the title.
	                        '👋', // WOW-Plugin (Float Menu plugin) sub-menu 'Hey' has this image at the end of the title.
	                        '→', // Independent Analytics >> Upgrade to Pro submenu item
                            '»', // wpDiscuz
						), 
						'', 
						$submenu_item_title 
					);
					$submenu_item_title_slug = sanitize_title( $submenu_item_title );
					$submenu_item_slug = isset( $item_info[1] ) ? $item_info[1] : '';

					$submenu_required_capability = $submenu_item_slug;

					$submenu_item_id = isset( $item_info[2] ) ? $item_info[2] : '';
					$submenu_item_id_length = strlen( $submenu_item_id );
					// Get the first two and last two characters and generate hex code
					// $submenu_item_title_hex = bin2hex( substr( $submenu_item_title_slug, 0, 2 ) . substr( $submenu_item_title_slug, -2 ) );
					$submenu_url_fragment = $submenu_item_id;

					// We need to make this as unique as possible for JS interactions, so, we string together three parts
					$submenu_item_id_combination = $submenu_sortable_id . '_-_' . $submenu_item_title_slug .'-_-' . $submenu_item_id_length;
					// e.g. index_php_-_site-options-_-24

					// $submenu_item_id_combination = $submenu_sortable_id . '_-_' . $submenu_item_title_slug .'-_-' . $submenu_item_title_hex;
					// e.g. index_php_-_site-options-_-696e6564
					?>
					<li id="<?php echo esc_attr( $submenu_item_id ); ?>" class="menu-item menu-item-depth-0">
						<div class="menu-item-bar">
							<div class="menu-item-handle">
								<span class="dashicons dashicons-menu"></span>
								<div class="item-title">
									<div class="title-wrapper">
										<?php if ( in_array( $menu_item_id, $menu_item_ids_for_preserving_submenu_titles ) ): ?>
										<span class="menu-item-title"><?php echo wp_kses_post( strip_tags( $submenu_item_title ) ); ?></span>
										<?php else: ?>
										<span class="menu-item-title"><?php echo wp_kses_post( $common_methods->strip_html_tags_and_content( $submenu_item_title ) ); ?></span>
										<?php endif; ?>
									</div>
									<?php
										$custom_menu_item = '';
										$submenu_hidden_by_toggle = array();
									?>
									<div class="options-for-hiding">
										<?php
								        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
								        	$hide_text = __( 'Hide', 'admin-site-enhancements' );
								        	$checkbox_class = 'submenu-hide-checkbox-prm';
								        } else {
								        	$hide_text = __( 'Hide until toggled', 'admin-site-enhancements' );
								        	$checkbox_class = 'submenu-hide-checkbox';	        	
								        }
							        	?>
										<label class="menu-item-checkbox-label">
											<?php
												if ( in_array( $custom_menu_item, $submenu_hidden_by_toggle ) ) {
												?>
											<input type="checkbox" id="hide-status-for-<?php echo esc_attr( $submenu_item_id_combination ); ?>" class="<?php echo esc_attr( $checkbox_class ); ?>" data-menu-item-title="<?php echo esc_attr( $common_methods->strip_html_tags_and_content( $submenu_item_title ) ); ?>" data-menu-item-id="<?php echo esc_attr( $submenu_item_id_combination ); ?>" data-menu-item-id-ori="<?php echo esc_attr( $submenu_item_id ); ?>" data-menu-url-fragment="<?php echo esc_attr( $submenu_url_fragment ); ?>" checked>
											<span><?php echo esc_html( $hide_text ); ?></span>
												<?php
												} else {
												?>
											<input type="checkbox" id="hide-status-for-<?php echo esc_attr( $submenu_item_id_combination ); ?>" class="<?php echo esc_attr( $checkbox_class ); ?>" data-menu-item-title="<?php echo esc_attr( $common_methods->strip_html_tags_and_content( $submenu_item_title ) ); ?>" data-menu-item-id="<?php echo esc_attr( $submenu_item_id_combination ); ?>" data-menu-item-id-ori="<?php echo esc_attr( $submenu_item_id ); ?>" data-menu-url-fragment="<?php echo esc_attr( $submenu_url_fragment ); ?>">
											<span><?php echo esc_html( $hide_text ); ?></span>
												<?php
												}
											?>
										</label>
										<?php
								        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
										?>
											<div class="options-toggle" data-menu-item-id="<?php echo esc_attr( $submenu_item_id_combination ); ?>">
												<span class="arrow-right"><?php echo wp_kses( $triangle_right_icon, $common_methods->get_kses_extended_ruleset() ); ?></span><span class="options-text"><?php echo esc_html__( 'Options', 'admin-site-enhancements' ); ?></span>
											</div>
										<?php
								        }
										?>
									</div><!-- end of .options-for-hiding -->
								</div><!-- end of .item-title -->
							</div><!-- end of .menu-item-handle -->
						</div><!-- end of .menu-item-bar -->
						<?php
						// Get data on menu items to be hidden by user roles
			            $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
			            $options = isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array();

						if ( isset( $options['custom_submenu_always_hidden'] ) ) {
							$submenu_always_hidden = $options['custom_submenu_always_hidden'];
							$submenu_always_hidden = json_decode( $submenu_always_hidden, true );
						} else {
							$submenu_always_hidden = array();
						}

						$always_hide_checked_status = '';
						$all_or_selected_roles = '';
						if ( is_array( $submenu_always_hidden ) && ! empty( $submenu_always_hidden ) ) {
							foreach( $submenu_always_hidden as $hidden_submenu_item_id => $info ) {
								if ( $hidden_submenu_item_id == $submenu_item_id_combination) {
									if ( isset( $info['always_hide'] ) && $info['always_hide'] ) {
										$always_hide_checked_status = ' checked';
									}				
									if ( isset( $info['always_hide_for'] ) && $info['always_hide_for'] ) {
										$all_or_selected_roles = $info['always_hide_for'];
									}

								}
							}									
						}

						$this->render_sortable_menu_options__premium_only( $submenu_item_id_combination, $submenu_required_capability, $always_hide_checked_status, $all_or_selected_roles, false );
				        ?>
					</li>
					<?php
				}
				?>
			</ul><!-- end of .submenu-sortable -->
		</div><!-- end of .submenu-wrapper -->
		<?php		
	}

	/**
	 * Render sortable menu options for hiding by role
	 *
	 * @since 5.1.0
	 */
	function render_sortable_menu_options__premium_only( $menu_item_id, $menu_required_capability, $always_hide_checked_status, $all_or_selected_roles, $is_parent_menu = true ) {
		
		$common_methods = new Common_Methods;

		// For parent menu items
		$menu_hidden_by_toggle = $common_methods->get_menu_hidden_by_toggle();
		$menu_item_id_transformed = $common_methods->transform_menu_item_id( $menu_item_id );

		if ( ! empty( $menu_hidden_by_toggle ) && in_array( $menu_item_id, $menu_hidden_by_toggle ) ) {
			$hide_by_toggle_checked_status = ' checked';
		} else {
			$hide_by_toggle_checked_status = '';			
		}
		
		// For submenu items
		if ( ! $is_parent_menu ) {
			if ( false !== strpos( $menu_item_id, '_-_') ) {
				$submenu_item_id_parts = explode( '_-_', $menu_item_id );
				$submenu_item_id = $common_methods->restore_menu_item_id( $submenu_item_id_parts[0] ) . '_-_' . $submenu_item_id_parts[1];
				
				// Get indexed array of strings that looks like index.php_-_submenu-item-slug
				// Notice index.php is the regular (not transformed) submenu item's ID
				$submenu_hidden_by_toggle = $common_methods->get_submenu_hidden_by_toggle__premium_only();

				if ( ! empty( $submenu_hidden_by_toggle ) && in_array( $submenu_item_id, $submenu_hidden_by_toggle ) ) {
					$hide_by_toggle_checked_status = ' checked';
				} else {
					$hide_by_toggle_checked_status = '';			
				}
			}
		}
		
		if ( $is_parent_menu ) {
			$menu_type = 'parent';
			$settings_class = 'parent-menu';
			$hide_by_toggle_class = 'hide-until-toggled-checkbox';
		} else {
			$menu_type = 'sub';
			$settings_class = 'submenu';			
			$hide_by_toggle_class = 'hide-until-toggled-submenu-checkbox';
		}
		
		if ( $all_or_selected_roles == 'all-roles' ) {
			$all_roles_checked_status = ' checked';
			$all_roles_except_checked_status = '';
			$selected_roles_checked_status = '';
		} elseif ( $all_or_selected_roles == 'all-roles-except' ) {
			$all_roles_checked_status = '';
			$all_roles_except_checked_status = ' checked';
			$selected_roles_checked_status = '';			
		} elseif ( $all_or_selected_roles == 'selected-roles' ) {
			$all_roles_checked_status = '';
			$all_roles_except_checked_status = '';
			$selected_roles_checked_status = ' checked';			
		} else {
			$all_roles_checked_status = ' checked';
			$all_roles_except_checked_status = '';			
			$selected_roles_checked_status = '';			
		}
		
		// Handle "Posts >> Tags" submenu item. It requires 'manage_post_tags' capability, which is mapped to 'manage_categories' capability. Ref: https://wordpress.stackexchange.com/a/296144
		if ( 'manage_post_tags' == $menu_required_capability ) {
			$menu_required_capability = 'manage_categories';
		}
    	?>
		<!-- Render options panel for hiding menu -->
		<div id="options-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="menu-item-settings <?php echo esc_attr( $settings_class ); ?>-item-settings wp-clearfix" style="display:none;">
			<div class="hide-toggle-or-always">
				<label class="menu-item-checkbox-label">
					<input type="checkbox" id="hide-until-toggled-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="<?php echo esc_attr( $hide_by_toggle_class ); ?>" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>"<?php echo esc_attr( $hide_by_toggle_checked_status ); ?>>
					<span><?php _e( 'Hide until toggled', 'admin-site-enhancements' ); ?></span>
				</label>
			</div>
			<div id="all-selected-roles-options-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="all-selected-roles-options" data-menu-item-id="<?php echo esc_attr( $menu_item_id ); ?>">
				<label class="menu-item-checkbox-label">
					<input type="checkbox" id="hide-by-role-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="hide-by-role-checkbox" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-type="<?php echo esc_attr( $menu_type ); ?>" <?php echo esc_attr( $always_hide_checked_status ); ?>>
					<span><?php echo esc_html__( 'Always hide for user role(s)', 'admin-site-enhancements' ); ?></span>
				</label>
				<fieldset id="all-selected-roles-radio-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" style="display:none;">
				    <div>
				      <input type="radio" id="all-roles-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="all-selected-roles-radios" name="hide-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" value="all-roles" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-type="<?php echo esc_attr( $menu_type ); ?>" <?php echo esc_attr( $all_roles_checked_status ); ?>>
				      <label for="all-roles-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>">all roles</label>
				    </div>
				    <div>
				      <input type="radio" id="all-roles-except-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="all-selected-roles-radios" name="hide-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" value="all-roles-except" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-type="<?php echo esc_attr( $menu_type ); ?>" <?php echo esc_attr( $all_roles_except_checked_status ); ?>>
				      <label for="all-roles-except-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>">all roles except</label>
				    </div>
				    <div>
				      <input type="radio" id="selected-roles-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="all-selected-roles-radios" name="hide-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" value="selected-roles" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-type="<?php echo esc_attr( $menu_type ); ?>" <?php echo esc_attr( $selected_roles_checked_status ); ?>>
				      <label for="selected-roles-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>">selected roles</label>
				    </div>
				</fieldset>
			</div>
			<div id="hide-for-roles-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="hide-for-roles-options" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" data-menu-type="<?php echo esc_attr( $menu_type ); ?>" style="display:none;">
				<?php
	        	global $wp_roles;
				$roles = $wp_roles->get_names();
				if ( $all_or_selected_roles == 'all-roles-except' || $all_or_selected_roles == 'selected-roles' ) {
					$roles_menu_is_hidden_for = $common_methods->get_roles_menu_is_hidden_for__premium_only( $menu_item_id_transformed, $is_parent_menu );
				}
				foreach ( $roles as $role_slug => $role_name ) {
					$role_obj = get_role( $role_slug );
					if ( is_object( $role_obj ) && is_array( $role_obj->capabilities ) ) {
						$role_capabilities = array_keys( $role_obj->capabilities );
					} else {
						$role_capabilities = array();
					}

					if ( in_array( $menu_required_capability, $role_capabilities ) ) {
						$role_is_checked_status = '';
						if ( $all_or_selected_roles == 'all-roles-except' ||  $all_or_selected_roles == 'selected-roles' ) {
							if ( in_array( $role_slug, $roles_menu_is_hidden_for ) ) {
								$role_is_checked_status = ' checked';
							}							
						}
						?>
						<label class="menu-item-checkbox-label">
							<input type="checkbox" class="menu-item-checkbox role-checkbox" data-role="<?php echo esc_attr( $role_slug ); ?>"<?php echo esc_attr( $role_is_checked_status ); ?>>
							<span><?php echo esc_html( $role_name ); ?></span>
						</label>
						<?php
					}
				}
				?>
			</div>
			<div id="menu-required-capability-for-<?php echo esc_attr( $menu_item_id_transformed ); ?>" class="menu-required-capability" data-menu-item-id="<?php echo esc_attr( $menu_item_id_transformed ); ?>" style="display:none;"><?php echo wp_kses_post( 
				sprintf( 
				/* translators: the required user capability to view menu item */
				__( 'The role(s) above have the <code>%s</code> capability required to view and access the menu item.', 'admin-site-enhancements' ), 
				$menu_required_capability 
				) 
			); ?>
			</div>
		</div><!-- end of .menu-item-settings -->
    	<?php
	}
	
	/**
	 * Check if a menu item is a custom menu added via "Add menu" or "Add separator"
	 * 
	 * @since 6.9.13
	 */
	public function is_custom_menu_item__premium_only( $menu_item_id ) {
        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
        $options = isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array();
		
		// Get custom menu items added via "Add menu" or "Add separator"
        $new_separators = isset( $options['custom_menu_new_separators'] ) ? $options['custom_menu_new_separators'] : '';
        $new_separators = json_decode( stripslashes( $new_separators ), true );
        if ( is_array( $new_separators ) ) {
	        $new_separators_menu_ids = array_keys( $new_separators ); // e.g. array( 'separator5', 'separator6' )
        } else {
        	$new_separators_menu_ids = array();
        }

		if ( in_array( $menu_item_id, $new_separators_menu_ids ) ) {
			return 'yes';
		} else {
			return 'no';
		}		
	}

	/**
	 * Render textarea field as sub-field of a toggle/switcher checkbox
	 *
	 * @since 2.3.0
	 */
	function render_datatable( $args ) {

		global $wpdb;

		$option_name = $args['option_name'];
		
		if ( ! empty( $option_name ) ) {
			$options = get_option( $option_name, array() );
		} else {
			$options = get_option( ASENHA_SLUG_U, array() );		
		}

		$field_id = $args['field_id'];
		$field_slug = $args['field_slug'];
		$field_name = $args['field_name'];
		$field_type = $args['field_type'];
		$field_description = $args['field_description'];
		$table_title = $args['table_title'];
		$table_name = $args['table_name'];
		$field_option_value = ( isset( $options[$args['field_id']] ) ) ? $options[$args['field_id']] : '';

		?>
		<table id="login-attempts-log" class="wp-list-table widefat striped datatable">
			<thead>
				<tr class="datatable-tr">
					<th class="datatable-th"><?php _e( 'IP Address<br />Last Username', 'admin-site-enhancements' ); ?></th>
					<th class="datatable-th"><?php _e( 'Attempts<br />Lockouts', 'admin-site-enhancements' ); ?></th>
					<th class="datatable-th"><?php _e( 'Last Attempt On', 'admin-site-enhancements' ); ?></th>
				</tr>
			</thead>
			<tbody>
		<?php

		$limit = 1000;

		$sql = $wpdb->prepare( "SELECT * FROM {$table_name} ORDER BY unixtime DESC LIMIT %d", array( $limit ) );

		$entries = $wpdb->get_results( $sql, ARRAY_A );

		foreach( $entries as $entry ) {

			$unixtime = $entry['unixtime'];
			if ( function_exists( 'wp_date' ) ) {
				$date = wp_date( 'F j, Y', $unixtime );
				$time = wp_date( 'H:i:s', $unixtime );
			} else {
				$date = date_i18n( 'F j, Y', $unixtime );
				$time = date_i18n( 'H:i:s', $unixtime );
			}
			?>
			<tr class="datatable-tr">
				<td class="datatable-td"><?php echo esc_html( $entry['ip_address'] ); ?><br /><?php echo esc_html( $entry['username'] ); ?></td>
				<td class="datatable-td"><?php echo esc_html( $entry['fail_count'] ); ?><br /><?php echo esc_html( $entry['lockout_count'] ); ?></td>
				<td class="datatable-td"><span class="unixtime"><?php echo esc_html( $entry['unixtime'] ); ?></span><?php echo esc_html( $date ); ?><br /><?php echo esc_html( $time ); ?></td>
			</tr>
			<?php
		}

		?>
			</tbody>
		</table>
		<?php

		echo '<div class="asenha-subfield-description">' . esc_html( $field_description ) . '</div>';
		echo '<input type="hidden" id="' . esc_attr( $field_name ) . '" class="asenha-subfield-datatable" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $field_option_value ) . '">';
	}
	
	/**
	 * Render checks and status for AVIF support
	 * 
	 * @link https://php.watch/versions/8.1/gd-avif
	 * @since 5.7.0
	 */
	public function render_avif_support_status() {
		
		// Check status of GD extension and it's AVIF support

		if ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) {
			$is_gd_enabled = true;
			$gd_info = gd_info();
			$gd_version = $gd_info['GD Version'];
			$gd_avif_support = ( isset( $gd_info['AVIF Support'] ) ) ? isset( $gd_info['AVIF Support'] ) : false ;
			if ( $gd_avif_support ) {
				$gd_status = $gd_version . ' <span class="supported">' . __( 'with AVIF support', 'admin-site-enhancements' ) . '</span>';
			} else {
				$gd_status = $gd_version . ' <span class="unsupported">' . __( 'without AVIF support', 'admin-site-enhancements' ) . '</span>';				
			}
		} else {
			$is_gd_enabled = false;
			$gd_avif_support = false;
			$gd_status = __( 'Not available', 'admin-site-enhancements' );
		}

		// Check status of ImageMagick library and it's AVIF support
		
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			$is_imagick_enabled = true;
			$imagick_version = \Imagick::getVersion();
			
			if ( preg_match( '/((?:[0-9]+\.?)+)/', $imagick_version['versionString'], $matches ) ) {
				$imagick_version = $matches[0];
			} else {
				$imagick_version = $imagick_version['versionString'];			
			}
						
			if ( version_compare( $imagick_version, '7.0.25', '>=' ) ) {
				$imagick_avif_support = true;
				$imagick_status = $imagick_version . ' <span class="supported">with AVIF support</span>';
			} else {
				$imagick_avif_support = false;
				$imagick_status = $imagick_version . ' <span class="unsupported">without AVIF support</span>';
			}

		} else {
			$is_imagick_enabled = false;
			$imagick_avif_support = false;
			$imagick_status = __( 'Not available', 'admin-site-enhancements' );
		}

		echo '<div class="asenha-subfield-status">';
		echo '<div class="status-title">' . __( 'AVIF Support Status', 'admin-site-enhancements' ) . '</div>';
		echo '<div class="status-body">';
		echo '<div class="status-item"><span class="status-item-title">PHP</span> : ' . wp_kses_post( phpversion() ) . '</div>';
		echo '<div class="status-item"><span class="status-item-title">GD</span> : ' . wp_kses_post( $gd_status ) . '</div>';
		echo '<div class="status-item"><span class="status-item-title">ImageMagick</span> : ' . wp_kses_post( $imagick_status) . '</div>';
		echo '</div>';
		echo '<div class="status-footer">' . __( 'Full AVIF support requires that your server / hosting has <a href="https://php.watch/versions/8.1/gd-avif" target="_blank">GD extension</a> compiled with AVIF support in PHP 8.1 or greater, or, <a href="https://web.archive.org/web/20240104012918/https://avif.io/blog/tutorials/imagemagick/" target="_blank">ImageMagick 7.0.25 or greater</a> installed. Without either, you can still upload AVIF files but without auto-generation of the smaller thumbnail sizes. A majority of <a href="https://web.archive.org/web/20231207174740/https://avif.io/blog/articles/avif-browser-support/" target="_blank">modern desktop and mobile browsers</a> support the display of AVIF files.', 'admin-site-enhancements' ) . '</div>';
		echo '</div>';
	}

}