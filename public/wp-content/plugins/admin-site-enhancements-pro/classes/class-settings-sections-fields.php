<?php

namespace ASENHA\Classes;

/**
 * Class related to registration of settings fields
 *
 * @since 2.2.0
 */
class Settings_Sections_Fields {

	/**
	 * Register plugin settings and the corresponding fields
	 *
	 * @link https://wpshout.com/making-an-admin-options-page-with-the-wordpress-settings-api/
	 * @link https://rudrastyh.com/wordpress/creating-options-pages.html
	 * @since 1.0.0
	 */
	function register_sections_fields() {
		
		add_settings_section(
			'main-section',
			'', // Section title. Can be blank.
			'', // Callback function to output section intro. Can be blank.
			ASENHA_SLUG
		);

		$common_methods = new Common_Methods;
		$wp_config = new WP_Config_Transformer;


		// Register main setttings

		// Instantiate object for sanitization of settings fields values
		$sanitization = new Settings_Sanitization;

		// Instantiate object for rendering of settings fields for the admin page
		$render_field = new Settings_Fields_Render;

		register_setting( 
			ASENHA_ID, // Option group or option_page
			ASENHA_SLUG_U,
			array(
				'type'					=> 'array', // 'string', 'boolean', 'integer', 'number', 'array', or 'object'
				'description'			=> '', // A description of the data attached to this setting.
				'sanitize_callback'		=> [ $sanitization, 'sanitize_for_options' ],
				'show_in_rest'			=> false,
				'default'				=> array(), // When calling get_option()
			)
		);

		// =================================================================
		// Call WordPress globals and set new globals required for the fields
		// =================================================================

		global $wp_version, $wp_roles, $wpdb, $asenha_all_post_types, $asenha_public_post_types, $asenha_nonpublic_post_types, $asenha_gutenberg_post_types, $asenha_revisions_post_types, $active_plugin_slugs, $workable_nodes;

		$options = get_option( ASENHA_SLUG_U, array() );
		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );

		$roles = $wp_roles->get_names();
		
		// Get array of slugs and plural labels for all post types, e.g. array( 'post' => 'Posts', 'page' => 'Pages' )
		$asenha_all_post_types = array();
		$all_post_type_names = get_post_types( array(), 'names' );
		foreach( $all_post_type_names as $post_type_name ) {
			$post_type_object = get_post_type_object( $post_type_name );
			$asenha_all_post_types[$post_type_name] = $post_type_object->label;
		}
		asort( $asenha_all_post_types ); // sort by value, ascending		

		// Get array of slugs and plural labels for public post types, e.g. array( 'post' => 'Posts', 'page' => 'Pages' )
		$asenha_public_post_types = array();
		$asenha_public_post_types = $common_methods->get_public_post_type_slugs();

		// Get array of slugs and plural labels for non-public post types, e.g. array( 'post' => 'Posts', 'page' => 'Pages' )
		$asenha_nonpublic_post_types = array();
		$nonpublic_post_type_names = get_post_types( array( 'public' => false ), 'names' );
		foreach( $nonpublic_post_type_names as $post_type_name ) {
			$post_type_object = get_post_type_object( $post_type_name );
			$asenha_nonpublic_post_types[$post_type_name] = $post_type_object->label;
		}
		asort( $asenha_nonpublic_post_types ); // sort by value, ascending

		// Get array of slugs and plural labels for post types that can be edited with the Gutenberg block editor, e.g. array( 'post' => 'Posts', 'page' => 'Pages' )
		$asenha_gutenberg_post_types = array();
		$gutenberg_not_applicable_types = array( 
			'attachment', 
			'revision', 
			'nav_menu_item', 
			'custom_css', 
			'customize_changeset', 
			'oembed_cache', 
			'user_request', 
			'wp_block', 
			'wp_template', 
			'wp_template_part', 
			'wp_global_styles', 
			'wp_navigation',
			'wp_font_family',
			'wp_font_face',
			// ACF
			'acf-post-type',
			'acf-taxonomy',
			'acf-field-group',
			'acf-ui-options-page',
			// Elementor
			'e-landing-page',
			'elementor_library',
			'elementor_snippet',
			'elementor_font',
			'elementor_icons',
			// Oxygen Classic
			'ct_template',
			'scheduled-action',
			// Breakdance
			'breakdance_template',
			'breakdance_header',
			'breakdance_footer',
			'breakdance_popup',
			'breakdance_block',
			'breakdance_acf_block',
			'breakdance_form_res',
			// Bricks
			'bricks_template',
			'bricks_fonts',
		);
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	$gutenberg_not_applicable_types[] = 'asenha_cpt';
        	$gutenberg_not_applicable_types[] = 'asenha_ctax';
        	$gutenberg_not_applicable_types[] = 'asenha_cfgroup';
        	$gutenberg_not_applicable_types[] = 'asenha_options_page';
        	$gutenberg_not_applicable_types[] = 'options_page_config';
        	$gutenberg_not_applicable_types[] = 'asenha_code_snippet';
        	$gutenberg_not_applicable_types[] = 'formbuilder-styles';
        }

		$rest_api_enabled_post_type_names = get_post_types( array( 'show_in_rest' => true ), 'objects' );
		foreach ( $rest_api_enabled_post_type_names as $post_type_slug => $post_type_info ) {
			$asenha_gutenberg_post_types[$post_type_slug] = $post_type_info->label;
			if ( in_array( $post_type_slug, $gutenberg_not_applicable_types ) ) {
				unset( $asenha_gutenberg_post_types[$post_type_slug] );
			}
		}

		// Get array of slugs and plural labels for post types supporting revisions, e.g. array( 'post' => 'Posts', 'page' => 'Pages' )
		$asenha_revisions_post_types = array();
		foreach ( get_post_types( array(), 'names' ) as $post_type_slug ) { // post type slug/name
			if ( post_type_supports( $post_type_slug, 'revisions' ) ) {
				$post_type_object = get_post_type_object( $post_type_slug );
				if ( property_exists( $post_type_object, 'label' ) ) {
					$asenha_revisions_post_types[$post_type_slug] = $post_type_object->label;
				}
			}
		}

		// Get array of active plugins slugs
		$active_plugins = get_option( 'active_plugins', array() );
		$active_plugin_slugs = array();
		foreach( $active_plugins as $active_plugin ) {
			// e.g. debug-log-manager/debug-log-manager.php
			$active_plugin = explode( "/", $active_plugin );
			$active_plugin_slugs[] = $active_plugin[0];
		}

		// Set array of select field options for background patterns
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$background_pattern_options = array(
						__( 'Blurry Gradient', 'admin-site-enhancements' ) . ' - ' . __( 'Blue', 'admin-site-enhancements' )			=> 'blurry-gradient-blue',
						__( 'Blurry Gradient', 'admin-site-enhancements' ) . ' - ' . __( 'Gray', 'admin-site-enhancements' )			=> 'blurry-gradient-gray',
						__( 'Blurry Gradient', 'admin-site-enhancements' ) . ' - ' . __( 'Green', 'admin-site-enhancements' )			=> 'blurry-gradient-green',
						__( 'Blurry Gradient', 'admin-site-enhancements' ) . ' - ' . __( 'Pink', 'admin-site-enhancements' )			=> 'blurry-gradient-pink',
						__( 'Blurry Gradient', 'admin-site-enhancements' ) . ' - ' . __( 'Purple', 'admin-site-enhancements' )			=> 'blurry-gradient-purple',
						__( 'Blurry Gradient', 'admin-site-enhancements' ) . ' - ' . __( 'Yellow', 'admin-site-enhancements' )			=> 'blurry-gradient-yellow',
						__( 'Stacked Steps', 'admin-site-enhancements' ) . ' - ' . __( 'Blue', 'admin-site-enhancements' )				=> 'stacked-steps-blue',
						__( 'Stacked Steps', 'admin-site-enhancements' ) . ' - ' . __( 'Gray', 'admin-site-enhancements' )				=> 'stacked-steps-gray',
						__( 'Stacked Steps', 'admin-site-enhancements' ) . ' - ' . __( 'Green', 'admin-site-enhancements' )				=> 'stacked-steps-green',
						__( 'Stacked Steps', 'admin-site-enhancements' ) . ' - ' . __( 'Pink', 'admin-site-enhancements' )				=> 'stacked-steps-pink',
						__( 'Stacked Steps', 'admin-site-enhancements' ) . ' - ' . __( 'Purple', 'admin-site-enhancements' )			=> 'stacked-steps-purple',
						__( 'Stacked Steps', 'admin-site-enhancements' ) . ' - ' . __( 'Yellow', 'admin-site-enhancements' )			=> 'stacked-steps-yellow',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Blue', 'admin-site-enhancements' )					=> 'blob-scene-blue',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Light Blue', 'admin-site-enhancements' )			=> 'blob-scene-blue-light',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Gray', 'admin-site-enhancements' )					=> 'blob-scene-gray',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Light Gray', 'admin-site-enhancements' )			=> 'blob-scene-gray-light',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Green', 'admin-site-enhancements' )				=> 'blob-scene-green',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Light Green', 'admin-site-enhancements' )			=> 'blob-scene-green-light',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Pink', 'admin-site-enhancements' )					=> 'blob-scene-pink',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Light Pink', 'admin-site-enhancements' )			=> 'blob-scene-pink-light',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Purple', 'admin-site-enhancements' )				=> 'blob-scene-purple',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Light Purple', 'admin-site-enhancements' )			=> 'blob-scene-purple-light',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Yellow', 'admin-site-enhancements' )				=> 'blob-scene-yellow',
						__( 'Blob Scene', 'admin-site-enhancements' ) . ' - ' . __( 'Light Yellow', 'admin-site-enhancements' )			=> 'blob-scene-yellow-light',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Blue', 'admin-site-enhancements' )				=> 'stacked-waves-blue',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Blue Inverted', 'admin-site-enhancements' )		=> 'stacked-waves-blue-inverted',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Gray', 'admin-site-enhancements' )				=> 'stacked-waves-gray',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Gray Inverted', 'admin-site-enhancements' )		=> 'stacked-waves-gray-inverted',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Green', 'admin-site-enhancements' )				=> 'stacked-waves-green',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Green Inverted', 'admin-site-enhancements' )	=> 'stacked-waves-green-inverted',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Pink', 'admin-site-enhancements' )				=> 'stacked-waves-pink',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Pink Inverted', 'admin-site-enhancements' )		=> 'stacked-waves-pink-inverted',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Purple', 'admin-site-enhancements' )			=> 'stacked-waves-purple',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Purple Inverted', 'admin-site-enhancements' )	=> 'stacked-waves-purple-inverted',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Yellow', 'admin-site-enhancements' )			=> 'stacked-waves-yellow',
						__( 'Stacked Waves', 'admin-site-enhancements' ) . ' - ' . __( 'Yellow Inverted', 'admin-site-enhancements' )	=> 'stacked-waves-yellow-inverted',
					);			
		} else {
			$background_pattern_options = array();
		}
						
		// =================================================================
		// CONTENT MANAGEMENT
		// =================================================================

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Custom Content Types

			$field_id = 'custom_content_types';
			$field_slug = 'custom-content-types';
			$field_title = __( 'Custom Content Types', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG, // Settings page slug
				'main-section', // Section ID
				array(
					'option_name'		=> ASENHA_SLUG_U,
					'field_id'			=> $field_id,
					'field_slug'		=> $field_slug,
					'field_title'		=> $field_title,
					'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'	=> __( 'Conveniently register and edit custom post types and custom taxonomies. Enable the creation of custom field groups for your post types and options pages to store data for display on any part of your website.', 'admin-site-enhancements' ),
					'field_options_wrapper'		=> true,
					'field_options_moreless'	=> true,
					'class'				=> 'asenha-toggle content-management ' . $field_slug,
				)
			);

			// $field_id = 'custom_field_groups';
			// $field_slug = 'custom-field-groups';

			// add_settings_field(
			// 	$field_id,
			// 	'',
			// 	[ $render_field, 'render_checkbox_plain' ],
			// 	ASENHA_SLUG,
			// 	'main-section',
			// 	array(
			// 		'option_name'			=> ASENHA_SLUG_U,
			// 		'field_id'				=> $field_id,
			// 		'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
			// 		'field_label'			=> 'Enable the creation of custom field groups for your post types',
			// 		'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			// 	)
			// );

			$field_id = 'custom_content_types_description';
			$field_slug = 'custom-content-types-description';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'Please find the relevant menu items under Settings.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-description top-border content-management ' . $field_slug,
				)
			);
        }

		// Enable Content Duplication
		$content_duplication = new Content_Duplication;

		$field_id = 'enable_duplication';
		$field_slug = 'enable-duplication';
		$field_title = __( 'Content Duplication', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'		=> ASENHA_SLUG_U,
				'field_id'			=> $field_id,
				'field_slug'		=> $field_slug,
				'field_title'		=> $field_title,
				'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'	=> __( 'Enable one-click duplication of pages, posts and custom posts. The corresponding taxonomy terms and post meta will also be duplicated.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'				=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		$field_id = 'duplication_redirect_destination';
		$field_slug = 'duplication-redirect-destination';

		add_settings_field(
			$field_id,
			__( 'After duplication, redirect to:', 'admin-site-enhancements' ),
			[ $render_field, 'render_radio_buttons_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				// 'field_label'			=> 'Temporary label',
				'field_radios'			=> array(
					__( 'Edit screen', 'admin-site-enhancements' )	=> 'edit',
					__( 'List view', 'admin-site-enhancements' )	=> 'list',
				),
				'field_default'			=> 'edit',
				'class'					=> 'asenha-radio-buttons shift-up content-management ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'enable_duplication_link_at';
			$field_slug = 'enable-duplication-link-at';
			
			$options = array(
				__( 'List view post action row', 'admin-site-enhancements' )	=> 'post-action',
				__( 'Admin bar', 'admin-site-enhancements' )					=> 'admin-bar',
				__( 'Edit screen publish section', 'admin-site-enhancements' )	=> 'publish-section',
			);

			add_settings_field(
				$field_id,
				__( 'Show duplication link on:', 'admin-site-enhancements' ),
				[ $render_field, 'render_checkboxes_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . '][]',
					'field_options'			=> $options,
					'field_default'			=> array( 'post-action', 'admin-bar', 'publish-section' ),
					'layout'				=> 'vertical', // 'horizontal' or 'vertical'
					'class'					=> 'asenha-checkboxes margin-top-8 content-management ' . $field_slug,
				)
			);

			$field_id = 'enable_duplication_on_post_types_type';
			$field_slug = 'enable-duplication-on-post-types-type';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'Enable only on:', 'admin-site-enhancements' )		=> 'only-on',
						__( 'Enable except on:', 'admin-site-enhancements' )	=> 'except-on',
					),
					'field_default'			=> 'only-on',
					'class'					=> 'asenha-radio-buttons bold-label content-management ' . $field_slug,
				)
			);

			$inapplicable_post_types = $content_duplication->inapplicable_post_types;

			// Exclude 'product' post type if WooCommerce is active, as WC already has it's own duplicate feature
	        $is_woocommerce_active = $common_methods->is_woocommerce_active();
			if ( $is_woocommerce_active ) {
				$inapplicable_post_types[] = 'product';			
			}

			$field_id = 'heading_for_public_cpt_duplication';
			$field_slug = 'heading-for-public-cpt-duplication';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'Public post types:', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading non-bold underline margin-top-m12 content-management ' . $field_slug,
				)
			);

			$field_id = 'enable_duplication_on_post_types';
			$field_slug = 'enable-duplication-on-public-post-types';
			
			if ( is_array( $asenha_public_post_types ) ) {
				foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
					if ( ! in_array( $post_type_slug, $inapplicable_post_types ) ) {
						add_settings_field(
							$field_id . '_' . $post_type_slug,
							'',
							[ $render_field, 'render_checkbox_subfield' ],
							ASENHA_SLUG,
							'main-section',
							array(
								'option_name'			=> ASENHA_SLUG_U,
								'parent_field_id'		=> $field_id,
								'field_id'				=> $post_type_slug,
								'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
								'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>',
								'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $post_type_slug,
							)
						);						
					}
				}
			}

			$field_id = 'heading_for_nonpublic_cpt_duplication';
			$field_slug = 'heading-for-nonpublic-cpt-duplication';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'Non-public post types:', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading non-bold underline margin-top-m12 content-management ' . $field_slug,
				)
			);
			
			$field_id = 'enable_duplication_on_post_types';
			$field_slug = 'enable-duplication-on-nonpublic-post-types';

			if ( is_array( $asenha_nonpublic_post_types ) ) {
				foreach ( $asenha_nonpublic_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
					if ( ! in_array( $post_type_slug, $inapplicable_post_types ) ) {
						add_settings_field(
							$field_id . '_' . $post_type_slug,
							'',
							[ $render_field, 'render_checkbox_subfield' ],
							ASENHA_SLUG,
							'main-section',
							array(
								'option_name'			=> ASENHA_SLUG_U,
								'parent_field_id'		=> $field_id,
								'field_id'				=> $post_type_slug,
								'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
								'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>',
								'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $post_type_slug,
							)
						);						
					}
				}
			}

			$field_id = 'heading_for_enable_duplication_for';
			$field_slug = 'heading-for-enable-duplication-for';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'Enable only for:', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading margin-top-8 content-management ' . $field_slug,
				)
			);

			$field_id = 'enable_duplication_for';
			$field_slug = 'enable-duplication-for';

			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

					add_settings_field(
						$field_id . '_' . $role_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'field_id'				=> $role_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $role_slug . ']',
							'field_label'			=> $role_label,
							'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $role_slug,
						)
					);

				}
			}


        }

		// Content Order

		$field_id = 'content_order';
		$field_slug = 'content-order';
		$field_title = __( 'Content Order', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Enable custom ordering of various "hierarchical" content types or those supporting "page attributes". A new \'Order\' sub-menu will appear for enabled content type(s). The "All {Posts}" list page for enabled post types in wp-admin will automatically use the custom order.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		$field_id = 'content_order_for';
		$field_slug = 'content-order-for';

		if ( is_array( $asenha_all_post_types ) ) {
			$inapplicable_post_types = array(
				// 'asenha_code_snippet', // ASE code snippets
			);

			foreach ( $asenha_all_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				$is_hierarchical_label = ( is_post_type_hierarchical( $post_type_slug ) ) ? ' <span class="faded">- Hierarchical</span>' : '';
				if ( ( post_type_supports( $post_type_slug, 'page-attributes' ) || is_post_type_hierarchical( $post_type_slug ) ) 
					&& ! in_array( $post_type_slug, $inapplicable_post_types )
				) {
					add_settings_field(
						$field_id . '_' . $post_type_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'field_id'				=> $post_type_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
							'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>' . $is_hierarchical_label,
							'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $post_type_slug,
						)
					);
				}
			}
		}

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'content_order_for_non_hierarchical_description';
			$field_slug = 'content-order-for-non-hierarchical-description';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'Also enable custom ordering for the following post type(s), which are non-hierarchical and does not support page attributes:', 'admin-site-enhancements' ),
					'class'					=> 'asenha-description asenha-th-border-top margin-top-8 padding-top-0 content-management ' . $field_slug,
				)
			);

			$field_id = 'content_order_for_other_post_types';
			$field_slug = 'content-order-for-other-post-types';

			if ( is_array( $asenha_all_post_types ) ) {
				$inapplicable_post_types = array(
					'wp_font_family',			// WP Font Families
					'wp_font_face',				// WP Font Faces
					'wp_global_styles',			// WP Global Styles
					'wp_navigation', 			// WP Navigation Menus
					'wp_block',					// WP (block) Patterns
					'patterns_ai_data',			// (WP?) Patterns AI Data
					'wp_template',				// WP Templates
					'wp_template_part',			// WP Template Parts
					'user_request',				// (WP?) User requests
					'oembed_cache',				// WP oEmbed Responses
					'customize_changeset',		// WP Changesets
					'custom_css',				// (WP?) Custom CSS
					'revision',					// WP Revisions
					'nav_menu_item',			// (WP?) Navigation Menu Items
					'asenha_cpt',				// ASE CPTs
					'asenha_ctax',				// ASE Custom Taxonomies
					'asenha_cfgroup',			// ASE Custom Field Groups
					'asenha_options_page',		// ASE Options Pages
					'options_page_config',		// ASE Options Pages Configs
					'ct_template', 				// Oxygen builder template
					'scheduled-action',			// Oxygen scheduled actions
					'shop_order',				// WooCommerce Orders
					'shop_coupon',				// WooCommerce Coupons
					'shop_order_placehold',		// WooCommerce 'Posts'
					'shop_order_refund',		// WooCommerce Refunds
					'product_variation',		// WooCommerce (Product) Variations
					'breakdance_template', 		// Breakdance Templates
					'breakdance_header', 		// Breakdance Headers
					'breakdance_footer', 		// Breakdance Footers
					'breakdance_block', 		// Breakdance Global Blocks
					'breakdance_acf_block', 	// Breakdance ACF Blocks
					'breakdance_form_res', 		// Breakdance Form Submissions
					'breakdance_popup', 		// Breakdance Popups
					'elementor_snippet', 		// Elementor Custom Code
					'elementor_icons', 			// Elementor Custom Icons
					'elementor_font', 			// Elementor Custom Fonts
					'elementor_library', 		// Elementor My Templates
					'bricks_template', 			// Bricks Templates
					'bricks_fonts', 			// Bricks Custom Fonts
				);
				foreach ( $asenha_all_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
					if ( ! post_type_supports( $post_type_slug, 'page-attributes' ) 
						&& ! is_post_type_hierarchical( $post_type_slug ) 
						&& ! in_array( $post_type_slug, $inapplicable_post_types ) 
					) {
						add_settings_field(
							$field_id . '_' . $post_type_slug,
							'',
							[ $render_field, 'render_checkbox_subfield' ],
							ASENHA_SLUG,
							'main-section',
							array(
								'option_name'			=> ASENHA_SLUG_U,
								'parent_field_id'		=> $field_id,
								'field_id'				=> $post_type_slug,
								'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
								'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>' . $is_hierarchical_label,
								'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $post_type_slug,
							)
						);
					}
				}
			}

			$field_id = 'content_order_frontend';
			$field_slug = 'content-order-frontend';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Use custom order on frontend query and display of enabled post types. You may need to manually set query order by <code>menu_order</code> in an <code>ascending</code> order.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th asenha-th-border-top content-management ' . $field_slug,
				)
			);
        }

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Terms Order

			$field_id = 'terms_order';
			$field_slug = 'terms-order';
			$field_title = __( 'Terms Order', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'				=> ASENHA_SLUG_U,
					'field_id'					=> $field_id,
					'field_slug'				=> $field_slug,
					'field_title'				=> $field_title,
					'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'			=> __( 'Enable custom ordering of terms from various "hierarchical" taxonomies. A new "Term Order" sub-menu will appear for enabled post type(s) with at least one such taxonomies. Terms listing and checkboxes in wp-admin will automatically use the custom order.', 'admin-site-enhancements' ),
					'field_options_wrapper'		=> true,
					'field_options_moreless'	=> true,
					'class'						=> 'asenha-toggle content-management ' . $field_slug,
				)
			);

			$field_id = 'terms_order_for';
			$field_slug = 'terms-order-for';

			if ( is_array( $asenha_all_post_types ) ) {
				foreach ( $asenha_all_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts

					$post_type_taxonomies = get_object_taxonomies( $post_type_slug );
					$hierarchical_taxonomies = array();

					// Get the hierarchical taxonomies for the post type
					foreach ( $post_type_taxonomies as $key => $taxonomy_name ) {
		                $taxonomy_info = get_taxonomy( $taxonomy_name );

		                if ( empty( $taxonomy_info->hierarchical ) ||  $taxonomy_info->hierarchical !== TRUE ) {
		                    unset( $post_type_taxonomies[$key] );
		                } else {
		                	$hierarchical_taxonomies[] = $taxonomy_info->label;
		                }
		            }
		            
		            $hierarchical_taxonomies_comma_separated = implode( ', ', $hierarchical_taxonomies );
		            
		            // Only if there's at least 1 hierarchical taxonomy for the post type
		            if ( count( $hierarchical_taxonomies ) > 0 ) {

						add_settings_field(
							$field_id . '_' . $post_type_slug,
							'',
							[ $render_field, 'render_checkbox_subfield' ],
							ASENHA_SLUG,
							'main-section',
							array(
								'option_name'			=> ASENHA_SLUG_U,
								'parent_field_id'		=> $field_id,
								'field_id'				=> $post_type_slug,
								'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
								'field_label'			=> $post_type_label . '<span class="dashicons dashicons-arrow-right-alt2"></span> ' . $hierarchical_taxonomies_comma_separated,
								'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $post_type_slug,
							)
						);
		            
		            }

				}
			}

			$field_id = 'terms_order_frontend';
			$field_slug = 'terms-order-frontend';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Use custom order of terms on frontend query and display.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th asenha-th-border-top content-management ' . $field_slug,
				)
			);
        }
		
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Media Categories

			$field_id = 'enable_media_categories';
			$field_slug = 'enable-media-categories';
			$field_title = __( 'Media Categories', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'		=> ASENHA_SLUG_U,
					'field_id'			=> $field_id,
					'field_slug'		=> $field_slug,
					'field_title'		=> $field_title,
					'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'	=> __( 'Add categories for the media library and enable drag-and-drop categorization of media items. Categories can then be used to filter media items during media insertion into content.', 'admin-site-enhancements' ),
					'field_options_wrapper'		=> true,
					'field_options_moreless'	=> true,
					'class'				=> 'asenha-toggle content-management ' . $field_slug,
				)
			);

			$field_id = 'media_categories_side_panel';
			$field_slug = 'media-categories-side-panel';

			add_settings_field(
				$field_id,
				'', // Field title
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> __( 'Categories side panel:', 'admin-site-enhancements' ),
					'field_suffix'			=> '<span class="faded">' . __( '(Default is 20%)', 'admin-site-enhancements' ) . '</span>',
					'field_select_options'	=> array(
						'20% wide'			=> 20,
						'25% wide'			=> 25,
						'30% wide'			=> 30,
						'35% wide'			=> 35,
						'40% wide'			=> 40,
						'Hide the panel'	=> 'hide',
					),
					'field_select_default'	=> 20,
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-number asenha-hide-th extra-narrow shift-up content-management ' . $field_slug,
					'display_none_on_load'	=> true,
				)
			);
        }

		// Media Replacement

		$field_id = 'enable_media_replacement';
		$field_slug = 'enable-media-replacement';
		$field_title = __( 'Media Replacement', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'		=> ASENHA_SLUG_U,
				'field_id'			=> $field_id,
				'field_slug'		=> $field_slug,
				'field_title'		=> $field_title,
				'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'	=> __( 'Easily replace any type of media file with a new one while retaining the existing media ID, publish date and file name. So, no existing links will break.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'				=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		$field_id = 'disable_media_replacement_cache_busting';
		$field_slug = 'disable-media-replacement-cache-busting';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable adding timestamp URL parameter on newly replaced images for busting the browser cache.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Enable SVG Upload

		$field_id = 'enable_svg_upload';
		$field_slug = 'enable-svg-upload';
		$field_title = __( 'SVG Upload', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Allow some or all user roles to upload SVG files, which will then be sanitized to keep things secure.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		$field_id = 'enable_svg_upload_for';
		$field_slug = 'enable-svg-upload-for';

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

				add_settings_field(
					$field_id . '_' . $role_slug,
					'',
					[ $render_field, 'render_checkbox_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						'parent_field_id'		=> $field_id,
						'field_id'				=> $role_slug,
						'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $role_slug . ']',
						'field_label'			=> $role_label,
						'class'					=> 'asenha-checkbox asenha-hide-th asenha-half admin-interface ' . $field_slug . ' ' . $role_slug,
					)
				);

			}
		}

		// Enable AVIF Upload

		$field_id = 'enable_avif_upload';
		$field_slug = 'enable-avif-upload';
		$field_title = __( 'AVIF Upload', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Enable uploading <a href="https://www.smashingmagazine.com/2021/09/modern-image-formats-avif-webp/" target="_blank">AVIF</a> files in the Media Library. You can convert your existing PNG, JPG and GIF files using a tool like <a href="https://squoosh.app/" target="_blank">Squoosh</a>.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		$field_id = 'avif_support_status';
		$field_slug = 'avif-support-status';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_avif_support_status' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'class'					=> 'asenha-toggle content-management ' . $field_slug,
			)
		);
		
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Public Preview for Drafts

			$field_id = 'public_preview_for_drafts';
			$field_slug = 'public-preview-for-drafts';
			$field_title = __( 'Public Preview for Drafts', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'				=> ASENHA_SLUG_U,
					'field_id'					=> $field_id,
					'field_slug'				=> $field_slug,
					'field_title'				=> $field_title,
					'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'			=> __( 'Enable public preview for draft posts from some or all public post types. You can find the public preview link in the list view post action row and edit screen Publish section.', 'admin-site-enhancements' ),
					'field_options_wrapper'		=> true,
					'field_options_moreless'	=> true,
					'class'						=> 'asenha-toggle content-management ' . $field_slug,
				)
			);

			$field_id = 'public_preview_max_days';
			$field_slug = 'public-preview-max-days';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_number_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> __( 'Set links to be active for a maximum of', 'admin-site-enhancements' ),
					'field_suffix'			=> __( 'days for:', 'admin-site-enhancements' ),
					'field_intro'			=> '',
					'field_placeholder'		=> '3',
					'field_min'				=> 1,
					'field_max'				=> 365,
					'field_description'		=> '',
					'class'					=> 'asenha-number asenha-hide-th narrow content-management ' . $field_slug,
				)
			);

			$field_id = 'public_drafts_preview_for';
			$field_slug = 'public-drafts-preview-for';

			if ( is_array( $asenha_public_post_types ) ) {
				foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { 
					// e.g. $post_type_slug is 'post', $post_type_label is 'Posts'
					if ( 'attachment' != $post_type_slug ) {
						add_settings_field(
							$field_id . '_' . $post_type_slug,
							'',
							[ $render_field, 'render_checkbox_subfield' ],
							ASENHA_SLUG,
							'main-section',
							array(
								'option_name'			=> ASENHA_SLUG_U,
								'parent_field_id'		=> $field_id,
								'field_id'				=> $post_type_slug,
								'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
								'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>',
								'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $post_type_slug,
							)
						);
					}
				}
			}
        }
							
		// Enable External Permalinks

		$field_id = 'enable_external_permalinks';
		$field_slug = 'enable-external-permalinks';
		$field_title = __( 'External Permalinks', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Enable pages, posts and/or custom post types to have permalinks that point to external URLs. The rel="noopener noreferrer nofollow" attribute will also be added for enhanced security and SEO benefits. Compatible with links added using <a href="https://wordpress.org/plugins/page-links-to/" target="_blank">Page Links To</a>.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		$field_id = 'enable_external_permalinks_for';
		$field_slug = 'enable-external-permalinks-for';

		if ( is_array( $asenha_public_post_types ) ) {
			foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				if ( 'attachment' != $post_type_slug ) {
					add_settings_field(
						$field_id . '_' . $post_type_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'field_id'				=> $post_type_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
							'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>',
							'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $post_type_slug,
						)
					);
				}
			}
		}

		// Open All External Links in New Tab

		$field_id = 'external_links_new_tab';
		$field_slug = 'external-links-new-tab';
		$field_title = __( 'Open All External Links in New Tab', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Force all links to external sites in post content, where <a href="https://developer.wordpress.org/reference/hooks/the_content/" target="_blank">the_content</a> hook is used, to open in new browser tab via target="_blank" attribute. The rel="noopener noreferrer nofollow" attribute will also be added for enhanced security and SEO benefits.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> false,
				'field_options_moreless'	=> false,
				'class'						=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		// Allow Custom Nav Menu Items to Open in New Tab

		$field_id = 'custom_nav_menu_items_new_tab';
		$field_slug = 'custom-nav-menu-items-new-tab';
		$field_title = __( 'Allow Custom Navigation Menu Items to Open in New Tab', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Allow custom navigation menu items to have links that open in new browser tab via target="_blank" attribute. The rel="noopener noreferrer nofollow" attribute will also be added for enhanced security and SEO benefits.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> false,
				'field_options_moreless'	=> false,
				'class'						=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		// Enable Auto-Publishing of Posts with Missed Schedules

		$field_id = 'enable_missed_schedule_posts_auto_publish';
		$field_slug = 'enable-missed-schedule-posts-auto-publish';
		$field_title = __( 'Auto-Publish Posts with Missed Schedule', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'		=> ASENHA_SLUG_U,
				'field_id'			=> $field_id,
				'field_slug'		=> $field_slug,
				'field_title'		=> $field_title,
				'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'	=> __( 'Trigger publishing of scheduled posts of all types marked with "missed schedule", anytime the site is visited.', 'admin-site-enhancements' ),
				'class'				=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		// =================================================================
		// ADMIN INTERFACE
		// =================================================================

		// Hide or Modify Elements / Clean Up Admin Bar

		$field_id = 'hide_modify_elements';
		$field_slug = 'hide-modify-elements';

		add_settings_field(
			$field_id,
			__( 'Clean Up Admin Bar', 'admin-site-enhancements' ),
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Remove various elements from the admin bar.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		$field_id = 'hide_ab_wp_logo_menu';
		$field_slug = 'hide-ab-wp-logo-menu';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove WordPress logo/menu', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);

		$field_id = 'hide_ab_site_menu';
		$field_slug = 'hide-ab-site-menu';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove home icon and site name', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);
		
		$field_id = 'hide_ab_customize_menu';
		$field_slug = 'hide-ab-customize-menu';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove customize menu', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);

		$field_id = 'hide_ab_updates_menu';
		$field_slug = 'hide-ab-updates-menu';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove updates counter/link', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);

		$field_id = 'hide_ab_comments_menu';
		$field_slug = 'hide-ab-comments-menu';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove comments counter/link', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);

		$field_id = 'hide_ab_new_content_menu';
		$field_slug = 'hide-ab-new-content-menu';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove new content menu', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);

		$field_id = 'hide_ab_howdy';
		$field_slug = 'hide-ab-howdy';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove \'Howdy\'', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);

		$field_id = 'hide_help_drawer';
		$field_slug = 'hide-help-drawer';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove the Help tab and drawer', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			
			// Get list of workable admin bar nodes from options

			$workable_nodes = ( isset( $options_extra['ab_nodes_workable'] ) ) ? $options_extra['ab_nodes_workable'] : array();

			ksort( $workable_nodes );
			
			if ( ! empty( $workable_nodes ) ) {

				$field_id = 'plugins_extra_admin_bar_items';
				$field_slug = 'plugins-extra-admin-bar-items';

				add_settings_field(
					$field_id,
					'',
					[ $render_field, 'render_description_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						'field_description'		=> __( 'Remove extra elements listed below, which most likely come from your theme or plugins.', 'admin-site-enhancements' ),
						'class'					=> 'asenha-description utilities ' . $field_slug,
					)
				);

				$field_id = 'disabled_plugins_admin_bar_items';
				$field_slug = 'disabled-plugins-admin-bar-items';
				
				foreach( $workable_nodes as $node_id => $node ) {
					
					if ( ! is_null( $common_methods->strip_html_tags_and_content( $node['title'] ) )
						&& ! empty( trim( $common_methods->strip_html_tags_and_content( $node['title'] ) ) ) ) {
						$node_title = $common_methods->strip_html_tags_and_content( $node['title'] );					
					} else {
						$node_title = $node_id;
					}

					add_settings_field(
						$field_id . '_' . $node_id,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'field_id'				=> $node_id,
							'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . '][' . $node_id . ']',
							'field_label'			=> $node_title . ' <span class="faded">(' . $node_id . ')</span>',
							'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug . ' ' . $node_id,
						)
					);
				}

				$field_id = 'plugins_extra_admin_bar_items_description';
				$field_slug = 'plugins-extra-admin-bar-items-description';
				
				$all_pages_url = admin_url( 'edit.php?post_type=page' );
				$all_posts_url = admin_url( 'edit.php' );

				add_settings_field(
					$field_id,
					'',
					[ $render_field, 'render_description_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						/* translators: 1: URL to "All Pages" 2: URL to "All Posts" */
						'field_description'		=> sprintf( __( 'Make sure to visit one of your frontend <a href="%1$s">pages</a> or <a href="%2$s">posts</a> while logged-in, to detect elements only visible in the frontend admin bar, and return here to see them on the list above.', 'admin-site-enhancements' ), $all_pages_url, $all_posts_url ),
						'class'					=> 'asenha-description utilities ' . $field_slug,
					)
				);

			}

		}

		// Hide Admin Notices

		$field_id = 'hide_admin_notices';
		$field_slug = 'hide-admin-notices';
		$field_title = __( 'Hide Admin Notices', 'admin-site-enhancements' );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_options_wrapper = true;
			$field_options_moreless = true;
		} else {
			$field_options_wrapper = false;
			$field_options_moreless = false;
		}

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'		=> ASENHA_SLUG_U,
				'field_id'			=> $field_id,
				'field_slug'		=> $field_slug,
				'field_title'		=> $field_title,
				'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'	=> __( 'Clean up admin pages by moving notices into a separate panel easily accessible via the admin bar.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> $field_options_wrapper,
				'field_options_moreless'	=> $field_options_moreless,
				'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {

			$field_id = 'hide_admin_notices_for_nonadmins';
			$field_slug = 'hide-admin-notices-for-nonadmins';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Also hide admin notices for non-administrators.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
				)
			);

			$field_id = 'hide_admin_notices_menu_for_nonadmins';
			$field_slug = 'hide-admin-notices-menu-for-nonadmins';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( "Hide the 'Notices' menu in the admin bar for non-administrators.", 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
				)
			);
		
		}	

		// Disable Dashboard Widgets

		$field_id = 'disable_dashboard_widgets';
		$field_slug = 'disable-dashboard-widgets';
		$field_title = __( 'Disable Dashboard Widgets', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Clean up and speed up the dashboard by completely disabling some or all widgets. Disabled widgets won\'t load any assets nor show up under Screen Options.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		$field_id = 'disable_welcome_panel_in_dashboard';
		$field_slug = 'disable-welcome-panel-in-dashboard';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Welcome to WordPress', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
			)
		);

		$field_id = 'disabled_dashboard_widgets';
		$field_slug = 'disabled-dashboard-widgets';

		if ( array_key_exists( 'dashboard_widgets', $options_extra ) ) {
			$dashboard_widgets = $options_extra['dashboard_widgets'];
		} else {
			$disable_dashboard_widgets = new Disable_Dashboard_Widgets;
			$dashboard_widgets = $disable_dashboard_widgets->get_dashboard_widgets();
			$options_extra['dashboard_widgets'] = $dashboard_widgets;
			update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
		}

		foreach ( $dashboard_widgets as $widget ) {
			add_settings_field(
				$field_id . '_' . $widget['id'],
				'',
				[ $render_field, 'render_checkbox_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'parent_field_id'		=> $field_id,
					'field_id'				=> $widget['id'] . '__' . $widget['context'] . '__' . $widget['priority'],
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . '][' . $widget['id'] . '__' . $widget['context'] . '__' . $widget['priority'] . ']',
					'field_label'			=> $widget['title'] . ' <span class="faded">(' . $widget['id'] . ')</span>',
					'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug . ' ' . $widget['id'],
				)
			);
		}

		// Hide Admin Bar

		$field_id = 'hide_admin_bar';
		$field_slug = 'hide-admin-bar';
		$field_title = __( 'Hide Admin Bar', 'admin-site-enhancements' );
		
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_description = __( 'Hide admin bar for all or some user roles.', 'admin-site-enhancements' );
		} else {
			$field_description = __( 'Hide admin bar on the frontend for all or some user roles.', 'admin-site-enhancements' );			
		}

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> $field_description,
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'heading_for_hide_admin_bar_on_frontend';
			$field_slug = 'heading-for-hide-admin-bar-on-frontend';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'On the frontend:', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading shift-more-up admin-interface ' . $field_slug,
				)
			);
		}

		$field_id = 'hide_admin_bar_for';
		$field_slug = 'hide-admin-bar-for';

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

				add_settings_field(
					$field_id . '_' . $role_slug,
					'',
					[ $render_field, 'render_checkbox_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						'parent_field_id'		=> $field_id,
						'field_id'				=> $role_slug,
						'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $role_slug . ']',
						'field_label'			=> $role_label,
						'class'					=> 'asenha-checkbox asenha-hide-th asenha-half admin-interface ' . $field_slug . ' ' . $role_slug,
					)
				);

			}
		}

		$field_id = 'hide_admin_bar_always_show_for_admins';
		$field_slug = 'hide-admin-bar-always-show-for-admins';

		add_settings_field(
			$field_id,
			'', // Field title
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Always show the admin bar for administrators on the frontend. Useful for when an administrator has multiple user roles, and the admin bar is set to be hidden for the other user role(s).', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th asenha-th-border-top admin-interface ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// On the backend
			$field_id = 'heading_for_hide_admin_bar_on_backend';
			$field_slug = 'heading-for-hide-admin-bar-on-backend';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'On the backend:', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading asenha-hide-th asenha-th-border-top admin-interface ' . $field_slug,
				)
			);

			$field_id = 'hide_admin_bar_on_backend_for';
			$field_slug = 'hide-admin-bar-on-backend-for';

			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

					add_settings_field(
						$field_id . '_' . $role_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'field_id'				=> $role_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $role_slug . ']',
							'field_label'			=> $role_label,
							'class'					=> 'asenha-checkbox asenha-hide-th asenha-half admin-interface ' . $field_slug . ' ' . $role_slug,
						)
					);

				}
			}
		}

		$field_id = 'hide_admin_bar_description';
		$field_slug = 'hide-admin-bar-description';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> '<div class="asenha-warning">' . __( 'The settings above will override the \'Toolbar\' settings in the user profile edit screen.', 'admin-site-enhancements' ) . '</div>',
				'class'					=> 'asenha-description login-logout ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Admin Logo

			$field_id = 'admin_logo';
			$field_slug = 'admin-logo';
			$field_title = __( 'Admin Logo', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'				=> ASENHA_SLUG_U,
					'field_id'					=> $field_id,
					'field_slug'				=> $field_slug,
					'field_title'				=> $field_title,
					'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'			=> __( 'Set a logo for the admin area.', 'admin-site-enhancements' ),
					'field_options_wrapper'		=> true,
					'field_options_moreless'	=> true,
					'class'						=> 'asenha-toggle admin-interface ' . $field_slug,
				)
			);

			$field_id = 'admin_logo_location';
			$field_slug = 'admin-logo-location';

			add_settings_field(
				$field_id,
				__( 'Location', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'Admin bar', 'admin-site-enhancements' )	=> 'admin_bar',
						__( 'Admin menu', 'admin-site-enhancements' )	=> 'admin_menu',
						__( 'Admin bar (frontend) & admin menu (backend)', 'admin-site-enhancements' )	=> 'admin_bar_and_menu',
					),
					'field_default'			=> 'admin_bar',
					'class'					=> 'asenha-radio-buttons admin-interface ' . $field_slug,
				)
			);

			$field_id = 'admin_logo_image';
			$field_slug = 'admin-logo-image';

			add_settings_field(
				$field_id,
				__( 'Logo Image', 'admin-site-enhancements' ),
				[ $render_field, 'render_media_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_media_frame_title'		=> __( 'Select an Image', 'admin-site-enhancements' ), // Media frame title
					'field_media_frame_multiple' 	=> false, // Allow multiple selection?
					'field_media_frame_library_type' => 'image', // Which media types to show
					'field_media_frame_button_text'	=> __( 'Use Selected Image', 'admin-site-enhancements' ), // Insertion button text
					'field_intro'					=> '',
					'field_description'				=> __( 'You can also paste an image URL hosted on another site.', 'admin-site-enhancements' ),
					'class'							=> 'asenha-textarea margin-top-12 admin-interface ' . $field_slug,
				)
			);

			$field_id = 'admin_logo_link_heading';
			$field_slug = 'admin-logo-link-heading';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'Logo Link', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading login-logout ' . $field_slug,
				)
			);
			
			$field_id = 'admin_logo_link_frontend';
			$field_slug = 'admin-logo-link-frontend';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'For the admin bar logo, link back to the dashboard when it is shown on the frontend', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
				)
			);
		}
				
		// Wider Admin Menu

		$field_id = 'wider_admin_menu';
		$field_slug = 'wider-admin-menu';
		$field_title = __( 'Wider Admin Menu', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Give the admin menu more room to better accommodate wider items.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'class'						=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		$field_id = 'admin_menu_width';
		$field_slug = 'admin-menu-width';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_select_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> __( 'Set width to', 'admin-site-enhancements' ),
				'field_suffix'			=> '<span class="faded">' . __( '(Default is 160px)', 'admin-site-enhancements' ) . '</span>',
				'field_select_options'	=> array(
					'180px'	=> '180px',
					'200px'	=> '200px',
					'220px'	=> '220px',
					'240px'	=> '240px',
					'260px'	=> '260px',
					'280px'	=> '280px',
					'300px'	=> '300px',
				),
				'field_select_default'	=> 200,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-number asenha-hide-th extra-narrow shift-up admin-interface ' . $field_slug,
				'display_none_on_load'	=> true,
			)
		);

		// Admin Menu Organizer

		$field_id = 'customize_admin_menu';
		$field_slug = 'customize-admin-menu';
		$field_title = __( 'Admin Menu Organizer', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				// 'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Customize the order of the admin menu and optionally change menu item title or hide some items.', 'admin-site-enhancements' ) . ' ' . sprintf( 
							/* translators: %s is URL to the Admin Menu Organizer settings page */
							__( 'Once enabled, you can find the <a href="%s">Admin Menu</a> item under the Settings menu.', 'admin-site-enhancements' ), 
							admin_url( 'options-general.php?page=admin-menu-organizer' )					
						),
				'field_options_wrapper'		=> false,
				'field_options_moreless'	=> false,
				'class'						=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);
		
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Admin Columns Manager

			$field_id = 'admin_columns_manager';
			$field_slug = 'admin-columns-manager';
			$field_title = __( 'Admin Columns Manager', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'		=> ASENHA_SLUG_U,
					'field_id'			=> $field_id,
					'field_slug'		=> $field_slug,
					'field_title'		=> $field_title,
					'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'	=> __( 'Manage and organize columns in the admin listing for pages, posts and custom post types.', 'admin-site-enhancements' ),
					'field_options_wrapper'		=> true,
					'field_options_moreless'	=> true,
					'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
				)
			);
        }

		// Show Custom Taxonomy Filters

		$field_id = 'show_custom_taxonomy_filters';
		$field_slug = 'show-custom-taxonomy-filters';
		$field_title = __( 'Show Custom Taxonomy Filters', 'admin-site-enhancements' );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_options_wrapper = true;
			$field_options_moreless = true;
		} else {
			$field_options_wrapper = false;
			$field_options_moreless = false;
		}

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_description'			=> __( 'Show additional filter(s) on list tables for hierarchical, custom taxonomies.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> $field_options_wrapper,
				'field_options_moreless'	=> $field_options_moreless,
				'class'						=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {

			$field_id = 'show_custom_taxonomy_filters_non_hierarchical';
			$field_slug = 'show-custom-taxonomy-filters-non-hierarchical';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Also show additional filter(s) for non-hierarchical taxonomies.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th admin-interface ' . $field_slug,
				)
			);
		
		}	

		// Enhance List Tables

		$field_id = 'enhance_list_tables';
		$field_slug = 'enhance-list-tables';
		$field_title = __( 'Enhance List Tables', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'		=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Improve the usefulness of listing pages for various post types and taxonomies, media, comments and users by adding / removing columns and elements.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle content-management ' . $field_slug,
			)
		);

		// Show Featured Image Column

		$field_id = 'show_featured_image_column';
		$field_slug = 'show-featured-image-column';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Show featured image column', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Show Excerpt Column

		$field_id = 'show_excerpt_column';
		$field_slug = 'show-excerpt-column';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Show excerpt column', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Show Last Modified Column

		$field_id = 'show_last_modified_column';
		$field_slug = 'show-last-modified-column';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Show last modified column', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Show ID Column

		$field_id = 'show_id_column';
		$field_slug = 'show-id-column';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Show ID column', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Show File Size Column in Media Library

		$field_id = 'show_file_size_column';
		$field_slug = 'show-file-size-column';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Show file size column in media library', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Show ID in Action Row

		$field_id = 'show_id_in_action_row';
		$field_slug = 'show-id-in-action_row';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Show ID in action rows along with links for Edit, View, etc.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Hide Date Column

		$field_id = 'hide_date_column';
		$field_slug = 'hide-date-column';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove date column', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);
		
		// Hide Comments Column

		$field_id = 'hide_comments_column';
		$field_slug = 'hide-comments-column';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove comments column', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Hide Post Tags Column

		$field_id = 'hide_post_tags_column';
		$field_slug = 'hide-post-tags-column';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Remove tags column (for posts)', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th content-management ' . $field_slug,
			)
		);

		// Various Admin UI Enhancements

		$field_id = 'various_admin_ui_enhancements';
		$field_slug = 'various-admin-ui-enhancements';
		$field_title = __( 'Various Admin UI Enhancements', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Various, smaller enhancements for different parts of the admin interface.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		// Media Library Infinite Scrolling

		$field_id = 'media_library_infinite_scrolling';
		$field_slug = 'media-library-infinite-scrolling';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'		=> ASENHA_SLUG_U,
				'field_id'			=> $field_id,
				'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_label'	=> '<strong>' . __( 'Media Library Infinite Scrolling', 'admin-site-enhancements' ) . '</strong><br />' . __( 'Re-enable infinite scrolling in the grid view of the media library. Useful for scrolling through a large library.', 'admin-site-enhancements' ),
				'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		// Display Active Plugins First
		$field_id = 'display_active_plugins_first';
		$field_slug = 'display-active-plugins-first';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'		=> ASENHA_SLUG_U,
				'field_id'			=> $field_id,
				'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_label'		=> '<strong>' . __( 'Display Active Plugins First', 'admin-site-enhancements' ) . '</strong><br />' . __( 'Display active / activated plugins at the top of the Installed Plugins list. Useful when your site has many deactivated plugins for testing or development purposes.', 'admin-site-enhancements' ),
				'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Preserve Taxonomy Hierarchy
			$field_id = 'preserve_taxonomy_hierarchy';
			$field_slug = 'preserve-taxonomy-hierarchy';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'			=> $field_id,
					'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_label'		=> '<strong>' . __( 'Preserve Taxonomy Hierarchy', 'admin-site-enhancements' ) . '</strong><br />' . __( 'Preserve the visual hierarchy of taxonomy terms checklist in the classic editor.', 'admin-site-enhancements' ),
					'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
				)
			);

			// Enable Dashboard Columns Settings
			$field_id = 'enable_dashboard_columns_settings';
			$field_slug = 'enable-dashboard-columns-settings';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'			=> $field_id,
					'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_label'		=> '<strong>' . __( 'Enable Dashboard Columns Settings', 'admin-site-enhancements' ) . '</strong><br />' . __( 'Enable manual settings of dashboard columns layout in Screen Options. You can choose between 1 to 4 columns.', 'admin-site-enhancements' ),
					'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
				)
			);

			// Add User Roles to Admin Body Classes
			$field_id = 'add_user_roles_to_admin_body_classes';
			$field_slug = 'add-user-roles-to-admin-body-classes';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'			=> $field_id,
					'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_label'		=> '<strong>' . __( 'Add User Role Slug(s) to Admin Body Classes', 'admin-site-enhancements' ) . '</strong><br />' . __( 'Useful for when you need to modify the admin area only for certain user role(s).', 'admin-site-enhancements' ),
					'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
				)
			);

			// Add Username to Admin Body Classes
			$field_id = 'add_username_to_admin_body_classes';
			$field_slug = 'add-username-to-admin-body-classes';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'			=> $field_id,
					'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_label'		=> '<strong>' . __( 'Add Username to Admin Body Classes', 'admin-site-enhancements' ) . '</strong><br />' . __( 'Useful for when you need to modify the admin area only for certain user(s).', 'admin-site-enhancements' ),
					'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
				)
			);

			// Open Admin Links in New Tab
			$field_id = 'open_admin_links_in_new_tab';
			$field_slug = 'open-admin-links-in-new-tab';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'			=> $field_id,
					'field_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_label'		=> '<strong>' . __( 'Open Admin Links in New Tab', 'admin-site-enhancements' ) . '</strong><br />' . __( 'Force all links in admin pages to open in new browser tab via target="_blank" attribute.', 'admin-site-enhancements' ),
					'class'				=> 'asenha-toggle admin-interface ' . $field_slug,
				)
			);
		}

		// Custom Admin Footer Text

		$field_id = 'custom_admin_footer_text';
		$field_slug = 'custom-admin-footer-text';
		$field_title = __( 'Custom Admin Footer Text', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Customize the text you see on the footer of wp-admin pages other than this ASE settings page.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle utilities ' . $field_slug,
			)
		);
		
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$media_buttons = true;
			$quicktags = false;
			$toolbar1 = 'bold,italic,underline,separator,link,unlink,undo,redo,code';
		} else {
			$media_buttons = false;
			$quicktags = false;
			$toolbar1 = 'bold,italic,underline';
		}

		$field_id = 'custom_admin_footer_left';
		$field_slug = 'custom-admin-footer-left';

		// https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
		// https://www.tiny.cloud/docs/advanced/available-toolbar-buttons/
		$editor_settings = array(
			'media_buttons'		=> $media_buttons,
			'textarea_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
			'textarea_rows'		=> 3,
			// 'teeny'				=> true,
			'tiny_mce'			=> true,
			'tinymce'			=> array(
				'toolbar1'		=> $toolbar1,
				'content_css'	=> ASENHA_URL . 'assets/css/settings-wpeditor.css',
				// 'wp_skip_init'	=> true,
			),
			'editor_css'		=> '',
			'quicktags'			=> $quicktags,
			'default_editor'	=> 'tinymce', // 'tinymce' or 'html'
		);

		add_settings_field(
			$field_id,
			__( 'Left Side', 'admin-site-enhancements' ),
			[ $render_field, 'render_wpeditor_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_intro'			=> '',
				'field_description'		=> __( 'Default text is: <em>Thank you for creating with <a href="https://wordpress.org/">WordPress</a></em>.', 'admin-site-enhancements' ),
				'field_placeholder'		=> '',
				'editor_settings'		=> $editor_settings,
				'class'					=> 'asenha-textarea admin-interface has-wpeditor ' . $field_slug,
			)
		);

		$field_id = 'custom_admin_footer_right';
		$field_slug = 'custom-admin-footer-right';

		// https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
		// https://www.tiny.cloud/docs/advanced/available-toolbar-buttons/
		$editor_settings = array(
			'media_buttons'		=> $media_buttons,
			'textarea_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
			'textarea_rows'		=> 3,
			// 'teeny'				=> true,
			'tiny_mce'			=> true,
			'tinymce'			=> array(
				'toolbar1'		=> $toolbar1,
				'content_css'	=> ASENHA_URL . 'assets/css/settings-wpeditor.css',
			),
			'editor_css'		=> '',
			'quicktags'			=> $quicktags,
			'default_editor'	=> 'tinymce', // 'tinymce' or 'html'
		);

		add_settings_field(
			$field_id,
			__( 'Right Side', 'admin-site-enhancements' ),
			[ $render_field, 'render_wpeditor_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 1,
				'field_intro'			=> '',
				'field_description'		=> sprintf( 
					/* translators: %s is the WordPress version number */
					__( 'Default text is: <em>Version %s</em>', 'admin-site-enhancements' ), 
					$wp_version 
					),
				'field_placeholder'		=> '',
				'editor_settings'		=> $editor_settings,
				'class'					=> 'asenha-textarea admin-interface has-wpeditor ' . $field_slug,
			)
		);
		
		// =================================================================
		// LOG IN/OUT | REGISTER
		// =================================================================

		// Change Login URL

		$field_id = 'change_login_url';
		$field_slug = 'change-login-url';
		$field_title = __( 'Change Login URL', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				/* translators: %s is URL to default login page */
				'field_description'		=> sprintf( 
					/* translators: %s is the default login URL */
					__( 'Default is %s', 'admin-site-enhancements' ), 
					site_url( '/wp-login.php' ) 
				),
				'field_options_moreless'=> true,
				'field_options_wrapper'	=> true,
				'class'					=> 'asenha-toggle login-logout ' . $field_slug,
			)
		);

		$field_id = 'custom_login_slug';
		$field_slug = 'custom-login-slug';

		add_settings_field(
			$field_id,
			__( 'New login URL:', 'admin-site-enhancements' ),
			[ $render_field, 'render_text_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> site_url() . '/',
				'field_suffix'			=> '/',
				'field_placeholder'		=> __( 'e.g. backend', 'admin-site-enhancements' ),
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix login-logout ' . $field_slug,
			)
		);

		$field_id = 'custom_login_whitelist';
		$field_slug = 'custom-login-whitelist';

		add_settings_field(
			$field_id,
			'<strong>' . __( 'Allow login from:' , 'admin-site-enhancements' ) . '</strong> <span class="weight-normal">' . site_url() . '/</span>',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 3,
				'field_intro'			=> '',
				'field_description'		=> __( 'Enter one path per line', 'admin-site-enhancements' ),
				'field_placeholder'		=> __( 'e.g. dashboard', 'admin-site-enhancements' ),
				'class'					=> 'asenha-textarea margin-top-16 login-logout ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'default_login_redirect_slug';
			$field_slug = 'default-login-redirect-slug';

			add_settings_field(
				$field_id,
				__( 'Redirect default URLs to:', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> site_url() . '/',
					'field_suffix'			=> '',
					'field_placeholder'		=> __( 'e.g. my-account', 'admin-site-enhancements' ),
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix margin-top-8 login-logout ' . $field_slug,
				)
			);        	
        }

		$field_id = 'change_login_url_description';
		$field_slug = 'change-login-url-description';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> __( '<div class="asenha-warning">"New login URL" <strong>only works for/with the default WordPress login page</strong>. If you have a login page you manually created with a page builder or with another plugin, please add them to the "Allow login from" section.<br /><br />This module is <strong>not yet compatible with two-factor authentication (2FA) methods</strong>. If you use a 2FA plugin, please use the change login URL feature bundled in that plugin, or use another plugin that is compatible with it.<br /><br />And obviously, to improve security, please <strong>use something other than \'login\'</strong> for the custom login slug.</div>', 'admin-site-enhancements' ),
				'class'					=> 'asenha-description login-logout ' . $field_slug,
			)
		);

		// Login ID Type

		$field_id = 'login_id_type_restriction';
		$field_slug = 'login-id-type-restriction';
		$field_title = __( 'Login ID Type', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Restrict login ID to username or email address only.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'class'						=> 'asenha-toggle login-logout ' . $field_slug,
			)
		);

		$field_id = 'login_id_type';
		$field_slug = 'login-id-type';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_radio_buttons_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				// 'field_label'			=> 'Temporary label',
				'field_radios'			=> array(
					__( 'Username only', 'admin-site-enhancements' )		=> 'username',
					__( 'Email address only', 'admin-site-enhancements' )	=> 'email',
				),
				'field_default'			=> 'username',
				'class'					=> 'asenha-radio-buttons shift-up login-logout ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Login Page Customizer

			$field_id = 'login_page_customizer';
			$field_slug = 'login-page-customizer';
			$field_title = __( 'Login Page Customizer', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_title'			=> $field_title,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					/* translators: %s is URL to default login page */
					'field_description'		=> __( 'Easily customize the design of the login page.', 'admin-site-enhancements' ),
					'field_options_moreless'=> true,
					'field_options_wrapper'	=> true,
					'class'					=> 'asenha-toggle login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_form_position';
			$field_slug = 'login-page-form-position';

			add_settings_field(
				$field_id,
				__( 'Login Form Position', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'Left Edge', 'admin-site-enhancements' ) 	=> 'left-edge',
						__( 'Left Half', 'admin-site-enhancements' ) 	=> 'left-half',
						__( 'Center', 'admin-site-enhancements' )		=> 'center',
						__( 'Right Half', 'admin-site-enhancements' )	=> 'right-half',
						__( 'Right Edge', 'admin-site-enhancements' )	=> 'right-edge',
					),
					'field_default'			=> 'center',
					'class'					=> 'asenha-radio-buttons margin-top-8 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_form_color_scheme';
			$field_slug = 'login-page-form-color-scheme';

        	$field_radios = array(
				__( 'Light', 'admin-site-enhancements' )	=> 'light',
				__( 'Dark', 'admin-site-enhancements' )		=> 'dark',
				__( 'Custom', 'admin-site-enhancements' )	=> 'custom',
			);

			add_settings_field(
				$field_id,
				__( 'Login Form Background', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> $field_radios,
					'field_default'			=> 'default',
					'class'					=> 'asenha-radio-buttons margin-top-16 utilities ' . $field_slug,
				)
			);

			$field_id = 'login_page_form_color_scheme_custom';
			$field_slug = 'login-page-form-color-scheme-custom';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-row flex-gap-8 login-page-form-color-scheme-custom"></div>',
				)
			);

			$field_id = 'login_page_form_section_color_bg';
			$field_slug = 'login-page-form-section-color-bg';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_color_picker_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_intro'					=> '',
					'field_description'				=> '',
					'field_default_value'			=> '#1e73be',
					'class'							=> 'asenha-color-picker margin-top-m8 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_form_section_color_transparency';
			$field_slug = 'login-page-form-section-color-transparency';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> __( 'Transparency', 'admin-site-enhancements' ),
					'field_suffix'			=> '<span class="faded">' . __( '(Default is 20%)', 'admin-site-enhancements' ) . '</span>',
					'field_select_options'	=> array(
						'None'				=> '1',
						'5%'				=> '0.95',
						'10%'				=> '0.9',
						'15%'				=> '0.85',
						'20%'				=> '0.8',
						'25%'				=> '0.75',
						'30%'				=> '0.7',
						'35%'				=> '0.65',
						'40%'				=> '0.6',
						'45%'				=> '0.55',
						'50%'				=> '0.5',
						'55%'				=> '0.45',
						'60%'				=> '0.4',
						'65%'				=> '0.35',
						'70%'				=> '0.3',
						'75%'				=> '0.25',
						'80%'				=> '0.2',
						'85%'				=> '0.15',
						'90%'				=> '0.1',
						'95%'				=> '0.05',
						'Fully transparent'	=> '0',
					),
					'field_select_default'	=> '0.8',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-number asenha-hide-th extra-narrow shift-up admin-interface ' . $field_slug,
					'display_none_on_load'	=> true,
				)
			);

			$field_id = 'login_page_logo_image_type';
			$field_slug = 'login-page-logo-image-type';

        	$field_radios = array(
				__( 'Site Icon', 'admin-site-enhancements' )		=> 'site_icon',
				__( 'Media library', 'admin-site-enhancements' )	=> 'custom',
				__( 'Image URL', 'admin-site-enhancements' )		=> 'custom_url',
			);

			add_settings_field(
				$field_id,
				__( 'Logo Image', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> $field_radios,
					'field_default'			=> 'custom',
					'class'					=> 'asenha-radio-buttons margin-top-16 utilities ' . $field_slug,
				)
			);
			
			$field_id = 'login_page_logo_image';
			$field_slug = 'login-page-logo-image';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_media_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_media_frame_title'		=> __( 'Select an Image', 'admin-site-enhancements' ), // Media frame title
					'field_media_frame_multiple' 	=> false, // Allow multiple selection?
					'field_media_frame_library_type' => 'image', // Which media types to show
					'field_media_frame_button_text'	=> __( 'Use Selected Image', 'admin-site-enhancements' ), // Insertion button text
					'field_intro'					=> '',
					'field_description'				=> '',
					'class'							=> 'asenha-textarea margin-top-m8 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_external';
			$field_slug = 'login-page-logo-image-external';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> __( 'e.g. https://www.site.com/media/brand_logo.jpg', 'admin-site-enhancements' ),
					'class'					=> 'asenha-text margin-top-m8 margin-bottom-9 utilities full-width ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_external_description';
			$field_slug = 'login-page-logo-image-external-description';
			
			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> '<a href="https://myfreeonlinetools.com/get-image-size-and-dimensions/" target="_blank"><div>' . __( 'Get image dimension', 'admin-site-enhancements' ) . '</a> <span class="dashicons dashicons-arrow-right-alt2"></span> <a href="https://andrew.hedges.name/experiments/aspect_ratio/" target="_blank">' . __( 'Get smaller dimension', 'admin-site-enhancements' ) . '</a></div>',
					'class'					=> 'asenha-description margin-top-m8 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_size';
			$field_slug = 'login-page-logo-image-size';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-row flex-gap-40 login-page-logo-image-size"></div>',
				)
			);

			$field_id = 'login_page_logo_image_attachment_id';
			$field_slug = 'login-page-logo-image-attachment-id';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_width_classname'	=> 'normal',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="field-sublabel sublabel-narrow">' . __( 'Attachment ID', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> '',
					'field_placeholder'		=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix no-field-title margin-top-8 force-hide login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_width_original';
			$field_slug = 'login-page-logo-image-width-original';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_width_classname'	=> 'normal',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="field-sublabel sublabel-narrow">' . __( 'Original width', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> 'px',
					'field_placeholder'		=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix no-field-title margin-top-8 force-hide login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_height_original';
			$field_slug = 'login-page-logo-image-height-original';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_width_classname'	=> 'normal',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="field-sublabel sublabel-narrow">' . __( 'Original height', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> 'px',
					'field_placeholder'		=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix no-field-title margin-top-8 force-hide login-logout ' . $field_slug,
				)
			);
			
			$field_id = 'login_page_logo_image_width';
			$field_slug = 'login-page-logo-image-width';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_width_classname'	=> 'normal',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="field-sublabel sublabel-narrow">' . __( 'Width', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> 'px',
					'field_placeholder'		=> __( 'e.g. 280', 'admin-site-enhancements' ),
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix no-field-title margin-top-8 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_height';
			$field_slug = 'login-page-logo-image-height';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_width_classname'	=> 'normal',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="field-sublabel sublabel-narrow">' . __( 'Height', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> 'px',
					'field_placeholder'		=> __( 'e.g. 72', 'admin-site-enhancements' ),
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix no-field-title margin-top-8 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_width_external';
			$field_slug = 'login-page-logo-image-width-external';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_width_classname'	=> 'normal',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="field-sublabel sublabel-narrow">' . __( 'Width', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> 'px',
					'field_placeholder'		=> __( 'e.g. 280', 'admin-site-enhancements' ),
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix no-field-title margin-top-8 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_height_external';
			$field_slug = 'login-page-logo-image-height-external';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_width_classname'	=> 'normal',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="field-sublabel sublabel-narrow">' . __( 'Height', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> 'px',
					'field_placeholder'		=> __( 'e.g. 72', 'admin-site-enhancements' ),
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix no-field-title margin-top-8 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_logo_image_description';
			$field_slug = 'login-page-logo-image-description';
			
			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'Make sure the ratio between width and height matches the original ratio to prevent the image from getting distorted.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-description margin-top-m8 login-logout ' . $field_slug,
				)
			);
			
			$field_id = 'login_page_logo_site_icon_description';
			$field_slug = 'login-page-logo-site-icon-description';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'Will use the site icon set in Settings >> General.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-description margin-top-m8 login-logout ' . $field_slug,
				)
			);
			
			$field_id = 'login_page_background';
			$field_slug = 'login-page-background';

        	$field_radios = array(
				__( 'Pattern', 'admin-site-enhancements' )	=> 'pattern',
				__( 'Image', 'admin-site-enhancements' )	=> 'image',
				__( 'Color', 'admin-site-enhancements' )	=> 'solid_color',
			);

			add_settings_field(
				$field_id,
				__( 'Page Background', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> $field_radios,
					'field_default'			=> 'default',
					'class'					=> 'asenha-radio-buttons margin-top-16 utilities ' . $field_slug,
				)
			);

			$field_id = 'login_page_background_pattern';
			$field_slug = 'login-page-background-pattern';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> $background_pattern_options,
					'field_select_default'	=> 'blurry-gradient-blue',
					'field_intro'			=> '',
					'field_description'		=> sprintf( 
													/* translators: %1$s etc are links to external resources */
													__( 'Create your own patterns with: %1$s', 'admin-site-enhancements' ),
													'<a href="https://haikei.app/generators/" target="_blank">haikei</a>',
												),
					'class'					=> 'asenha-number asenha-hide-th extra-narrow shift-up admin-interface ' . $field_slug,
					'display_none_on_load'	=> true,
				)
			);

			$field_id = 'login_page_background_image';
			$field_slug = 'login-page-background-image';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_media_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_media_frame_title'		=> __( 'Select an Image', 'admin-site-enhancements' ), // Media frame title
					'field_media_frame_multiple' 	=> false, // Allow multiple selection?
					'field_media_frame_library_type' => 'image', // Which media types to show
					'field_media_frame_button_text'	=> __( 'Use Selected Image', 'admin-site-enhancements' ), // Insertion button text
					'field_intro'					=> '',
					'field_description'				=> __( 'You can also paste an image URL hosted on another site.', 'admin-site-enhancements' ) . '<br />' .sprintf( 
															/* translators: %1$s etc are links to external resources */
															__( 'Resources: %1$s | %2$s | %3$s | %4$s', 'admin-site-enhancements' ),
															'<a href="https://openverse.org/" target="_blank">openverse</a>',
															'<a href="https://unsplash.com/" target="_blank">Unsplash</a>',
															'<a href="https://haikei.app/generators/" target="_blank">haikei</a>',
															'<a href="https://gradients.app/en/new" target="_blank">Gradients.app</a>'
														),
					'class'							=> 'asenha-textarea shift-more-up login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_background_color';
			$field_slug = 'login-page-background-color';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_color_picker_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_intro'					=> '',
					'field_description'				=> '',
					'field_default_value'			=> 'f0f0f1', // Show or hide on page load
					'class'							=> 'asenha-color-picker shift-more-up login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_login_button_color';
			$field_slug = 'login-page-login-button-color';

			add_settings_field(
				$field_id,
				__( 'Login Button Color', 'admin-site-enhancements' ),
				[ $render_field, 'render_color_picker_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_intro'					=> '',
					'field_description'				=> __( 'Default state', 'admin-site-enhancements' ),
					'field_default_value'			=> '2271b1', // Show or hide on page load
					'class'							=> 'asenha-color-picker margin-top-16 login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_hide_elements';
			$field_slug = 'login-page-hide-elements';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'Hide Elements', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading margin-top-12 login-logout ' . $field_slug,
				)
			);

			// $field_id = 'login_page_disable_registration';
			// $field_slug = 'login-page-disable-registration';

			// add_settings_field(
			// 	$field_id,
			// 	'',
			// 	[ $render_field, 'render_checkbox_plain' ],
			// 	ASENHA_SLUG,
			// 	'main-section',
			// 	array(
			// 		'option_name'			=> ASENHA_SLUG_U,
			// 		'field_id'				=> $field_id,
			// 		'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
			// 		'field_label'			=> __( 'Disable registration', 'admin-site-enhancements' ),
			// 		'class'					=> 'asenha-checkbox asenha-hide-th login-logout ' . $field_slug,
			// 	)
			// );

			$field_id = 'login_page_hide_remember_me';
			$field_slug = 'login-page-hide-remember-me';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Hide "Remember Me" checkbox', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_hide_registration_reset';
			$field_slug = 'login-page-hide-registration-reset';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Hide registration and password reset links', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_hide_homepage_link';
			$field_slug = 'login-page-hide-homepage-link';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Hide link to homepage', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_hide_language_switcher';
			$field_slug = 'login-page-hide-language-switcher';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Hide language switcher', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th login-logout ' . $field_slug,
				)
			);

			$field_id = 'login_page_external_css';
			$field_slug = 'login-page-external-css';

			add_settings_field(
				$field_id,
				__( 'Load External CSS <span class="faded">(Provide full URL)</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> __( 'e.g. https://www.example.com/custom-login-style.css', 'admin-site-enhancements' ),
					'class'					=> 'asenha-text margin-top-12 utilities full-width ' . $field_slug,
				)
			);    

			$field_id = 'login_page_custom_css';
			$field_slug = 'login-page-custom-css';

			add_settings_field(
				$field_id,
				__( 'Custom CSS', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 20,
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-textarea syntax-highlighted margin-top-12 login-logout ' . $field_slug,
				)
			);
		}

		if ( ! bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Use Site Identity on the Login Page

			$field_id = 'site_identity_on_login';
			$field_slug = 'site-identity-on-login';
			$field_title = __( 'Site Identity on Login Page', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_title'			=> $field_title,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'		=> sprintf( 
							/* translators: %s is URL to the Customizer */
							__( 'Use the site icon and URL to replace the default WordPress logo with link to wordpress.org on the login page. Go to <a href="%1$s">General Settings</a> or the <a href="%2$s">Customizer</a> to set or change your site icon.', 'admin-site-enhancements' ), 
							admin_url( 'options-general.php' ),
							admin_url( 'customize.php' ),						
						),
					'field_options_wrapper'	=> true,
					'class'					=> 'asenha-toggle login-logout ' . $field_slug,
				)
			);			
		}

		// Enable Log In/Out Menu

		$field_id = 'enable_login_logout_menu';
		$field_slug = 'enable-login-logout-menu';
		$field_title = __( 'Log In/Out Menu', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Enable log in, log out and dynamic log in/out menu item for addition to any menu.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle login-logout ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'custom_login_menu_label';
			$field_slug = 'custom-login-menu-label';

			add_settings_field(
				$field_id,
				__( 'Custom "Log In" Label', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'only for dynamic log in/out menu item', 'admin-site-enhancements' ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-top-12 width-75 login-logout ' . $field_slug,
				)
			);

			$field_id = 'custom_logout_menu_label';
			$field_slug = 'custom-logout-menu-label';

			add_settings_field(
				$field_id,
				__( 'Custom "Log Out" Label', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'only for dynamic log in/out menu item', 'admin-site-enhancements' ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-top-12 width-75 login-logout ' . $field_slug,
				)
			);

			$field_id = 'custom_login_logout_label_description';
			$field_slug = 'custom-login-logout-label-description';
	    	$field_description = __( 'These custom labels will override the "Navigation Label" set for the menu item.', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> $field_description,
					'class'					=> 'asenha-description login-logout ' . $field_slug,
				)
			);
		}
		
		// Last Login Column

		$field_id = 'enable_last_login_column';
		$field_slug = 'enable-last-login-column';
		$field_title = __( 'Last Login Column', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Log when users on the site last logged in and display the date and time in the users list table.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle login-logout ' . $field_slug,
			)
		);

		// Registration Date Column

		$field_id = 'registration_date_column';
		$field_slug = 'registration-date-column';
		$field_title = __( 'Registration Date Column', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Display the registration date and time in the users list table.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle login-logout ' . $field_slug,
			)
		);
		
		// Redirect After Login

		$field_id = 'redirect_after_login';
		$field_slug = 'redirect-after-login';
		$field_title = __( 'Redirect After Login', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Set custom redirect URL for all or some user roles after login.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle login-logout ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'redirect_after_login_type';
			$field_slug = 'redirect-after-login-type';

			add_settings_field(
				$field_id,
				__( 'Redirection type', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'A single URL for some or all roles', 'admin-site-enhancements' )	=> 'single_url',
						__( 'Separate URL for each role', 'admin-site-enhancements' )			=> 'separate_urls',
					),
					'field_default'			=> 'single_url',
					'class'					=> 'asenha-radio-buttons asenha-th-border-bottom padding-bottom-8 login-logout ' . $field_slug,
				)
			);			
		}

		$field_id = 'redirect_after_login_to_slug';
		$field_slug = 'redirect-after-login-to-slug';

		add_settings_field(
			$field_id,
			__( 'Redirect to:', 'admin-site-enhancements' ),
			[ $render_field, 'render_text_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> site_url() . '/',
				'field_suffix'			=> __( 'for:', 'admin-site-enhancements' ),
				'field_placeholder'		=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix login-logout ' . $field_slug,
			)
		);

		$field_id = 'redirect_after_login_for';
		$field_slug = 'redirect-after-login-for';

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

				add_settings_field(
					$field_id . '_' . $role_slug,
					'',
					[ $render_field, 'render_checkbox_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						'parent_field_id'		=> $field_id,
						'field_id'				=> $role_slug,
						'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $role_slug . ']',
						'field_label'			=> $role_label,
						'class'					=> 'asenha-checkbox asenha-hide-th asenha-half login-logout ' . $field_slug . ' ' . $role_slug,
					)
				);

			}
		}

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'redirect_after_login_for_separate';
			$field_slug = 'redirect-after-login-for-separate';

			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

					add_settings_field(
						$field_id . '_role_' . $role_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'sub_field_id'			=> $field_id .'_role',
							'field_id'				=> $role_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'_role][' . $role_slug . ']',
							'field_label'			=> sprintf(
															/* translators: %s is the user role label, e.g. Administrator */
															__( 'For %s, redirect to:', 'admin-site-enhancements' ), 
															$role_label
														),
							'class'					=> 'asenha-checkbox asenha-hide-th margin-top-8 login-logout ' . $field_slug . ' ' . $role_slug,
						)
					);

					add_settings_field(
						$field_id . '_slug_' . $role_slug,
						'',
						[ $render_field, 'render_checkbox_field_text_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'sub_field_id'			=> $field_id .'_slug',
							'field_id'				=> $role_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'_slug][' . $role_slug . ']',
							'field_type'			=> 'with-prefix-suffix',
							'field_prefix'			=> site_url() . '/',
							'field_suffix'			=> '',
							'field_placeholder'		=> '',
							'field_description'		=> '',
							'class'					=> 'asenha-text with-prefix-suffix th-hidden margin-top-m12 margin-left-20 login-logout ' . $field_slug . ' ' . $role_slug,
						)
					);

				}
			}
		}

		// Redirect After Logout

		$field_id = 'redirect_after_logout';
		$field_slug = 'redirect-after-logout';
		$field_title = __( 'Redirect After Logout', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Set custom redirect URL for all or some user roles after logout.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle login-logout ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'redirect_after_logout_type';
			$field_slug = 'redirect-after-logout-type';

			add_settings_field(
				$field_id,
				__( 'Redirection type', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'A single URL for some or all roles', 'admin-site-enhancements' )	=> 'single_url',
						__( 'Separate URL for each role', 'admin-site-enhancements' )			=> 'separate_urls',
					),
					'field_default'			=> 'single_url',
					'class'					=> 'asenha-radio-buttons asenha-th-border-bottom padding-bottom-8 login-logout ' . $field_slug,
				)
			);			
		}

		$field_id = 'redirect_after_logout_to_slug';
		$field_slug = 'redirect-after-logout-to-slug';

		add_settings_field(
			$field_id,
			__( 'Redirect to:', 'admin-site-enhancements' ),
			[ $render_field, 'render_text_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> site_url() . '/',
				'field_suffix'			=> __( 'for:', 'admin-site-enhancements' ),
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix login-logout ' . $field_slug,
			)
		);

		$field_id = 'redirect_after_logout_for';
		$field_slug = 'redirect-after-logout-for';

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

				add_settings_field(
					$field_id . '_' . $role_slug,
					'',
					[ $render_field, 'render_checkbox_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						'parent_field_id'		=> $field_id,
						'field_id'				=> $role_slug,
						'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $role_slug . ']',
						'field_label'			=> $role_label,
						'class'					=> 'asenha-checkbox asenha-hide-th asenha-half login-logout ' . $field_slug . ' ' . $role_slug,
					)
				);

			}
		}

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'redirect_after_logout_for_separate';
			$field_slug = 'redirect-after-logout-for-separate';

			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

					add_settings_field(
						$field_id . '_role_' . $role_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'sub_field_id'			=> $field_id .'_role',
							'field_id'				=> $role_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'_role][' . $role_slug . ']',
							'field_label'			=> sprintf(
															/* translators: %s is the user role label, e.g. Administrator */
															__( 'For %s, redirect to:', 'admin-site-enhancements' ), 
															$role_label
														),
							'class'					=> 'asenha-checkbox asenha-hide-th margin-top-8 login-logout ' . $field_slug . ' ' . $role_slug,
						)
					);

					add_settings_field(
						$field_id . '_slug_' . $role_slug,
						'',
						[ $render_field, 'render_checkbox_field_text_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'sub_field_id'			=> $field_id .'_slug',
							'field_id'				=> $role_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'_slug][' . $role_slug . ']',
							'field_type'			=> 'with-prefix-suffix',
							'field_prefix'			=> site_url() . '/',
							'field_suffix'			=> '',
							'field_placeholder'		=> '',
							'field_description'		=> '',
							'class'					=> 'asenha-text with-prefix-suffix th-hidden margin-top-m12 margin-left-20 login-logout ' . $field_slug . ' ' . $role_slug,
						)
					);

				}
			}
		}
		
		// =================================================================
		// CUSTOM CODE
		// =================================================================

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Code Snippets Manager

			$field_id = 'enable_code_snippets_manager';
			$field_slug = 'enable-code-snippets-manager';
			$field_title = __( 'Code Snippets Manager', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'				=> ASENHA_SLUG_U,
					'field_id'					=> $field_id,
					'field_slug'				=> $field_slug,
					'field_title'				=> $field_title,
					'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'			=> __( 'Conveniently add and manage CSS / SCSS, JS, HTML and PHP code snippets to modify your site\'s content, design, behaviour and functionalities', 'admin-site-enhancements' ),
					'field_options_wrapper'		=> true,
					'field_options_moreless'	=> true,
					'class'						=> 'asenha-toggle custom-code ' . $field_slug,
				)
			);        	

			$field_id = 'code_snippets_manager_description';
			$field_slug = 'code-snippets-manager-description';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'Once enabled, you can find the Code Snippets menu item on the admin menu.', 'admin-site-enhancements' ) . ' ' . __( 'You might need to reload your browser tab to see it.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-description custom-code ' . $field_slug,
				)
			);

			$field_id = 'code_snippets_editor_theme';
			$field_slug = 'code-snippets-editor-theme';

			add_settings_field(
				$field_id,
				__( 'Code editor theme', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'Dark', 'admin-site-enhancements' )		=> 'dark',
						__( 'Light', 'admin-site-enhancements' )	=> 'light',
					),
					'field_default'			=> 'dark',
					'class'					=> 'asenha-radio-buttons margin-top-8 padding-bottom-8 custom-code ' . $field_slug,
				)
			);		
        }

		// Enable Custom Admin CSS

		$field_id = 'enable_custom_admin_css';
		$field_slug = 'enable-custom-admin-css';
		$field_title = __( 'Custom Admin CSS', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Add custom CSS on all admin pages for all user roles.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle custom-code ' . $field_slug,
			)
		);

		$field_id = 'custom_admin_css';
		$field_slug = 'custom-admin-css';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 30,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-textarea asenha-hide-th syntax-highlighted custom-code ' . $field_slug,
			)
		);

		// Enable Custom Frontend CSS

		$field_id = 'enable_custom_frontend_css';
		$field_slug = 'enable-custom-frontend-css';
		$field_title = __( 'Custom Frontend CSS', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Add custom CSS on all frontend pages for all user roles.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle custom-code ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'custom_frontend_css_priority';
			$field_slug = 'custom-frontend-css-priority';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_number_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> __( '<strong>Insert CSS with the priority of</strong>', 'admin-site-enhancements' ),
					'field_suffix'			=> '',
					'field_intro'			=> '',
					'field_placeholder'		=> '10',
					'field_min'				=> 0,
					'field_max'				=> PHP_INT_MAX,
					/* translators: &lt;/head&gt; and &lt;head&gt; is escaped </head> and <head>, keep them as is in the translation */
					'field_description'		=> __( 'Default is 10. Larger number inserts CSS closer to &lt;/head&gt; and allows it to better override other CSS loaded earlier in &lt;head&gt;.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-number asenha-hide-th narrow custom-code ' . $field_slug,
				)
			);
        }

		$field_id = 'custom_frontend_css';
		$field_slug = 'custom-frontend-css';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 30,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-textarea asenha-hide-th syntax-highlighted custom-code ' . $field_slug,
			)
		);

		// Insert <head>, <body> and <footer> code

		$field_id = 'insert_head_body_footer_code';
		$field_slug = 'insert-head-body-footer-code';
		$field_title = __( 'Insert &lt;head&gt;, &lt;body&gt; and &lt;footer&gt; Code', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			/* translators: keep &lt;head&gt; &lt;body&gt; &lt;footer&gt; as is in the translation */
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				/* translators: keep &lt;meta&gt; &lt;link&gt; &lt;script&gt; &lt;style&gt; as is in the translation */
				'field_description'			=> __( 'Easily insert &lt;meta&gt;, &lt;link&gt;, &lt;script&gt; and &lt;style&gt; tags, Google Analytics, Tag Manager, AdSense, Ads Conversion and Optimize code, Facebook, TikTok and Twitter pixels, etc.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle custom-code ' . $field_slug,
			)
		);

		$field_id = 'disable_code_unslash';
		$field_slug = 'disable-code-unslash';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Do not remove backslashes from the code output on the frontend', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th asenha-th-border-bottom custom-code ' . $field_slug,
			)
		);

		$field_id = 'head_code_priority';
		$field_slug = 'head-code-priority';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				/* translators: keep &lt;/head&gt; as is in the translation */
				'field_prefix'			=> __( '<strong>Code to insert before &lt;/head&gt; with the priority of</strong>', 'admin-site-enhancements' ),
				'field_suffix'			=> '',
				'field_intro'			=> '',
				'field_placeholder'		=> '10',
				'field_min'				=> 0,
				'field_max'				=> PHP_INT_MAX,
				/* translators: keep &lt;/head&gt; as is in the translation */
				'field_description'		=> __( 'Default is 10. Larger number insert code closer to &lt;/head&gt;', 'admin-site-enhancements' ),
				'class'					=> 'asenha-number asenha-hide-th narrow custom-code ' . $field_slug,
			)
		);

		$field_id = 'head_code';
		$field_slug = 'head-code';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 15,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-textarea asenha-hide-th syntax-highlighted custom-code ' . $field_slug,
			)
		);

		$field_id = 'body_code_priority';
		$field_slug = 'body-code-priority';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				/* translators: keep &lt;body&gt; as is in the translation */
				'field_prefix'			=> __( '<strong>Code to insert after &lt;body&gt; with the priority of</strong>', 'admin-site-enhancements' ),
				'field_suffix'			=> '',
				'field_intro'			=> '',
				'field_placeholder'		=> '10',
				'field_min'				=> 0,
				'field_max'				=> PHP_INT_MAX,
				/* translators: keep &lt;body&gt; as is in the translation */
				'field_description'		=> __( 'Default is 10. Smaller number insert code closer to &lt;body&gt;', 'admin-site-enhancements' ),
				'class'					=> 'asenha-number asenha-hide-th narrow custom-code ' . $field_slug,
			)
		);

		$field_id = 'body_code';
		$field_slug = 'body-code';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 15,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-textarea asenha-hide-th syntax-highlighted custom-code ' . $field_slug,
			)
		);

		$field_id = 'footer_code_priority';
		$field_slug = 'footer-code-priority';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				/* translators: keep &lt;/body&gt; as is in the translation */
				'field_prefix'			=> __( '<strong>Code to insert in footer section before &lt;/body&gt; with the priority of</strong>', 'admin-site-enhancements' ),
				'field_suffix'			=> '',
				'field_intro'			=> '',
				'field_placeholder'		=> '10',
				'field_min'				=> 0,
				'field_max'				=> PHP_INT_MAX,
				/* translators: keep &lt;/body&gt; as is in the translation */
				'field_description'		=> __( 'Default is 10. Larger number insert code closer to &lt;/body&gt;', 'admin-site-enhancements' ),
				'class'					=> 'asenha-number asenha-hide-th narrow custom-code ' . $field_slug,
			)
		);

		$field_id = 'footer_code';
		$field_slug = 'footer-code';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 15,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-textarea asenha-hide-th syntax-highlighted custom-code ' . $field_slug,
			)
		);

		// Custom Body Class

		$field_id = 'enable_custom_body_class';
		$field_slug = 'enable-custom-body-class';
		$field_title = __( 'Custom Body Class', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				/* translators: &lt;body&gt; is escaped <body>, keep it as is in the translation */
				'field_description'			=> __( 'Add custom &lt;body&gt; class(es) on the singular view of some or all public post types. Compatible with classes already added using <a href="https://wordpress.org/plugins/wp-custom-body-class" target="_blank">Custom Body Class plugin</a>.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle custom-code ' . $field_slug,
			)
		);

		$field_id = 'enable_custom_body_class_for';
		$field_slug = 'enable-custom-body-class-for';

		if ( is_array( $asenha_public_post_types ) ) {
			foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				if ( 'attachment' != $post_type_slug ) {
					add_settings_field(
						$field_id . '_' . $post_type_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'field_id'				=> $post_type_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
							'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>',
							'class'					=> 'asenha-checkbox asenha-hide-th asenha-half custom-code ' . $field_slug . ' ' . $post_type_slug,
						)
					);
				}
			}
		}

		// Manage ads.txt and app-ads.txt

		$field_id = 'manage_ads_appads_txt';
		$field_slug = 'manage-ads-appads-txt';
		$field_title = __( 'Manage ads.txt and app-ads.txt', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Easily edit and validate your <a href="/ads.txt" target="_blank">ads.txt</a> and <a href="/app-ads.txt" target="_blank">app-ads.txt</a> content.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle custom-code ' . $field_slug,
			)
		);

		$field_id = 'ads_txt_content';
		$field_slug = 'ads-txt-content';
		
		$ads_txt_url_urlencoded = urlencode( site_url( 'ads.txt' ) );
		$ads_txt_str_replaced = str_replace( '.', '-', sanitize_text_field( $_SERVER['SERVER_NAME'] ) );

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 15,
				'field_intro'			=> __( '<strong>Your ads.txt content:</strong>', 'admin-site-enhancements' ),
				'field_description'		=> __( 'Validate with:', 'admin-site-enhancements' ) . ' <a href="https://adstxt.guru/validator/url/?url=' . $ads_txt_url_urlencoded . '" target="_blank">adstxt.guru</a> | <a href="https://www.adstxtvalidator.com/ads_txt/' . $ads_txt_str_replaced . '" target="_blank">adstxtvalidator.com</a><div class="vspacer"></div>', 'admin-site-enhancements',
				'class'					=> 'asenha-textarea asenha-hide-th syntax-highlighted custom-code ' . $field_slug,
			)
		);

		$field_id = 'app_ads_txt_content';
		$field_slug = 'app-ads-txt-content';
		
		$appads_txt_url_urlencoded = urlencode( site_url( 'app-ads.txt' ) );

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 15,
				'field_intro'			=> __( '<strong>Your app-ads.txt content:</strong>', 'admin-site-enhancements' ),
				'field_description'		=> __( 'Validate with:', 'admin-site-enhancements' ) . ' <a href="https://adstxt.guru/validator/url/?url=' . $appads_txt_url_urlencoded . '" target="_blank">adstxt.guru</a>',
				'class'					=> 'asenha-textarea asenha-hide-th syntax-highlighted custom-code ' . $field_slug,
			)
		);

		// Manage robots.txt

		$field_id = 'manage_robots_txt';
		$field_slug = 'manage-robots-txt';
		$field_title = __( 'Manage robots.txt', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Easily edit and validate your <a href="/robots.txt" target="_blank">robots.txt</a> content.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle custom-code ' . $field_slug,
			)
		);

		$field_id = 'robots_txt_content';
		$field_slug = 'robots-txt-content';

		$robots_txt_url_urlencoded = urlencode( site_url( 'robots.txt' ) );

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_textarea_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'textarea',
				'field_rows'			=> 20,
				'field_intro'			=> '',
				'field_description'		=> __( 'Validate with:', 'admin-site-enhancements' ) . ' <a href="https://www.websiteplanet.com/webtools/robots-txt/?url=' . $robots_txt_url_urlencoded . '" target="_blank">websiteplanet.com</a> | <a href="https://seositecheckup.com/tools/robotstxt-test" target="_blank">seositecheckup.tools</a><div class="vspacer"></div>',
				'class'					=> 'asenha-textarea asenha-hide-th syntax-highlighted custom-code ' . $field_slug,
			)
		);

		// =================================================================
		// DISABLE COMPONENTS
		// =================================================================

		// Disable Gutenberg

		$field_id = 'disable_gutenberg';
		$field_slug = 'disable-gutenberg';
		$field_title = __( 'Disable Gutenberg', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Disable the Gutenberg block editor for some or all applicable post types.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle disable-components ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'disable_gutenberg_type';
			$field_slug = 'disable-gutenberg-type';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'Disable only on', 'admin-site-enhancements' )				=> 'only-on',
						__( 'Disable except on', 'admin-site-enhancements' )			=> 'except-on',
						__( 'Disable on all post types', 'admin-site-enhancements' )	=> 'all-post-types',
					),
					'field_default'			=> 'only-on',
					'class'					=> 'asenha-radio-buttons bold-label shift-up asenha-th-border-bottom disable-components ' . $field_slug,
				)
			);
		}

		$field_id = 'disable_gutenberg_for';
		$field_slug = 'disable-gutenberg-for';

		if ( is_array( $asenha_gutenberg_post_types ) ) {
			foreach ( $asenha_gutenberg_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				add_settings_field(
					$field_id . '_' . $post_type_slug,
					'',
					[ $render_field, 'render_checkbox_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						'parent_field_id'		=> $field_id,
						'field_id'				=> $post_type_slug,
						'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
						'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>',
						'class'					=> 'asenha-checkbox asenha-checkbox-item asenha-hide-th asenha-half disable-components ' . $field_slug . ' ' . $post_type_slug,
					)
				);
			}
		}

		$field_id = 'disable_gutenberg_frontend_styles';
		$field_slug = 'disable-gutenberg-frontend-styles';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Also disable frontend block styles / CSS files for the selected post types.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th asenha-th-border-top disable-components ' . $field_slug,
			)
		);

		// Disable Comments

		$field_id = 'disable_comments';
		$field_slug = 'disable-comments';
		$field_title = __( 'Disable Comments', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Disable comments for some or all public post types. When disabled, existing comments will also be hidden on the frontend.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle disable-components ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'disable_comments_type';
			$field_slug = 'disable-comments-type';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'Disable only on', 'admin-site-enhancements' )		=> 'only-on',
						__( 'Disable except on', 'admin-site-enhancements' )	=> 'except-on',
						__( 'Disable on all post types', 'admin-site-enhancements' )	=> 'all-post-types',
					),
					'field_default'			=> 'only-on',
					'class'					=> 'asenha-radio-buttons bold-label shift-up asenha-th-border-bottom disable-components ' . $field_slug,
				)
			);			
		}

		$field_id = 'disable_comments_for';
		$field_slug = 'disable-comments-for';

		if ( is_array( $asenha_public_post_types ) ) {
			foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				if ( post_type_supports( $post_type_slug, 'comments' ) ) {
					add_settings_field(
						$field_id . '_' . $post_type_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'field_id'				=> $post_type_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
							'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>',
							'class'					=> 'asenha-checkbox asenha-checkbox-item asenha-hide-th asenha-half disable-components ' . $field_slug . ' ' . $post_type_slug,
						)
					);					
				}
			}
		}

		// Disable REST API

		$field_id = 'disable_rest_api';
		$field_slug = 'disable-rest-api';
		$field_title = __( 'Disable REST API', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Disable REST API access for non-authenticated users and remove URL traces from &lt;head&gt;, HTTP headers and WP RSD endpoint.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle disable-components ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'disable_rest_api_excluded_routes';
			$field_slug = 'disable-rest-api-excluded-routes';

			add_settings_field(
				$field_id,
				__( 'Enable access for non-authenticated users to REST API routes that contains the following strings:', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 3,
					'field_intro'			=> '',
					'field_description'		=> __( 'Enter one string per line. These routes should originally be publicly accessible.', 'admin-site-enhancements' ),
					'field_placeholder'		=> __( 'e.g. wp/v2/posts', 'admin-site-enhancements' ),
					'class'					=> 'asenha-textarea margin-top-16 security ' . $field_slug,
				)
			);

			$field_id = 'heading_for_enable_rest_api_for';
			$field_slug = 'heading-for-enable-rest-api-for';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'Enable access for the following, authenticated user role(s):', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading margin-top-8 content-management ' . $field_slug,
				)
			);
			
			$field_id = 'enable_rest_api_for';
			$field_slug = 'enable-rest-api-for';

			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator

					add_settings_field(
						$field_id . '_' . $role_slug,
						'',
						[ $render_field, 'render_checkbox_subfield' ],
						ASENHA_SLUG,
						'main-section',
						array(
							'option_name'			=> ASENHA_SLUG_U,
							'parent_field_id'		=> $field_id,
							'field_id'				=> $role_slug,
							'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $role_slug . ']',
							'field_label'			=> $role_label,
							'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $role_slug,
						)
					);

				}
			}
        }

		// Disable Feeds

		$field_id = 'disable_feeds';
		$field_slug = 'disable-feeds';
		$field_title = __( 'Disable Feeds', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Disable all RSS, Atom and RDF feeds. This includes feeds for posts, categories, tags, comments, authors and search. Also removes traces of feed URLs from &lt;head&gt;.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle disable-components ' . $field_slug,
			)
		);

		// Disable Embeds

		$field_id = 'disable_embeds';
		$field_slug = 'disable-embeds';
		$field_title = __( 'Disable Embeds', 'admin-site-enhancements' );
		
		$oembed_providers_doc = 'https://developer.wordpress.org/advanced-administration/wordpress/oembed/#does-this-work-with-any-url';

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Prevent your site content from being embedded on other sites.', 'admin-site-enhancements' ) . ' ' . sprintf( /* translators: %s is URL of WP Dev Resource page */ __( 'Prevent embedding of non-<a href="%s" target="_blank">whitelisted</a> sites in your site.', 'admin-site-enhancements' ), $oembed_providers_doc ) . ' ' . __( 'Disable all, related JavaScript.', 'admin-site-enhancements' ) . ' ' . __( 'Removes support for the embed block.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle disable-components ' . $field_slug,
			)
		);

		// Disable Auto Updates

		$field_id = 'disable_all_updates';
		$field_slug = 'disable-all-updates';
		$field_title = __( 'Disable All Updates', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Completely disable core, theme and plugin updates and auto-updates. Will also disable update checks, notices and emails.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle disable-components ' . $field_slug,
			)
		);

		// Disable Author Archives

		$field_id = 'disable_author_archives';
		$field_slug = 'disable-author-archives';
		$field_title = __( 'Disable Author Archives', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Return 404 (Not Found) error when trying to load author archives. Remove or disable links to author archives. Remove authors archives from the sitemap.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle disable-components ' . $field_slug,
			)
		);
		
		// Disable Smaller Components

		$field_id = 'disable_smaller_components';
		$field_slug = 'disable-smaller-components';
		$field_title = __( 'Disable Smaller Components', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Prevent smaller components from running or loading. Make the site more secure, load slightly faster and be more optimized for crawling by search engines.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless'	=> true,
				'class'					=> 'asenha-toggle disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_head_generator_tag';
		$field_slug = 'disable-head-generator-tag';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable the <strong>generator &lt;meta&gt; tag</strong> in &lt;head&gt;, which discloses the WordPress version number. Older versions(s) might contain unpatched security loophole(s).', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_feed_generator_tag';
		$field_slug = 'disable-feed-generator-tag';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable the <strong>&lt;generator&gt; tag</strong> in RSS feed &lt;channel&gt;, which discloses the WordPress version number. Older versions(s) might contain unpatched security loophole(s).', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);
		
		$field_id = 'disable_resource_version_number';
		$field_slug = 'disable-resource-version-number';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable <strong>version number</strong> on static resource URLs referenced in &lt;head&gt;, which can disclose WordPress version number. Older versions(s) might contain unpatched security loophole(s). Applies to non-logged-in view of pages. This will also increase cacheability of static assets, but may have unintended consequences. Make sure you know what you are doing.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);
		
		$field_id = 'disable_head_wlwmanifest_tag';
		$field_slug = 'disable-head-wlwmanifest-tag';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable the <strong>Windows Live Writer (WLW) manifest &lt;link&gt; tag</strong> in &lt;head&gt;. The WLW app was discontinued in 2017.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_head_rsd_tag';
		$field_slug = 'disable-head-rsd-tag';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable the <strong>Really Simple Discovery (RSD) &lt;link&gt; tag</strong> in &lt;head&gt;. It\'s not needed if your site is not using pingback or remote (XML-RPC) client to manage posts.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_head_shortlink_tag';
		$field_slug = 'disable-head-shortlink-tag';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable the default <strong>WordPress shortlink &lt;link&gt; tag</strong> in &lt;head&gt;. Ignored by search engines and has minimal practical use case. Usually, a dedicated shortlink plugin or service is preferred that allows for nice names in the short links and tracking of clicks when sharing the link on social media.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_frontend_dashicons';
		$field_slug = 'disable-frontend-dashicons';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable loading of <strong>Dashicons CSS and JS files</strong> on the front-end for public site visitors. This might break the layout or design of custom forms, including custom login forms, if they depend on Dashicons. Make sure to check those forms after disabling.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_emoji_support';
		$field_slug = 'disable-emoji-support';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable <strong>emoji support for pages, posts and custom post types</strong> on the admin and frontend. The support is primarily useful for older browsers that do not have native support for it. Most modern browsers across different OSes and devices now have native support for it.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_jquery_migrate';
		$field_slug = 'disable-jquery-migrate';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable <strong>jQuery Migrate</strong> script on the frontend, which should no longer be needed if your site uses modern theme and plugins.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_block_widgets';
		$field_slug = 'disable-block-widgets';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable <strong>block-based widgets settings screen</strong>. Restores the classic widgets settings screen when using a classic (non-block) theme. This has no effect on block themes.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_lazy_load';
		$field_slug = 'disable-lazy-load';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable <strong>lazy loading of images</strong> that was natively added since WordPress v5.5.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);

		$field_id = 'disable_application_passwords';
		$field_slug = 'disable-application-passwords';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Disable <strong>application passwords</strong> that was added since WordPress v5.6.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);
		
		$field_id = 'disable_plugin_theme_editor';
		$field_slug = 'disable-plugin-theme-editor';
		
		$is_wpconfig_writeable = $wp_config->wpconfig_file( 'writeability' );
		$disallow_file_edit_exists = $wp_config->exists( 'constant', 'DISALLOW_FILE_EDIT' );
		
		if ( $is_wpconfig_writeable ) {
			$field_label = __( 'Disable the <strong>plugin and theme editor</strong>.', 'admin-site-enhancements' );
		} else {
            if ( $disallow_file_edit_exists ) {
				$field_label = __( 'Disable the <strong>plugin and theme editor</strong>. <span class="warning-text">Note that wp-config.php in this site is not writeable and <code>DISALLOW_FILE_EDIT</code> constant is already defined there. You either need to make it writeable to make this setting functional, or, you need to manually change the value of <code>DISALLOW_FILE_EDIT</code> there.</span>', 'admin-site-enhancements' );            	
            } else {
				$field_label = __( 'Disable the <strong>plugin and theme editor</strong>.', 'admin-site-enhancements' );        	
            }
		}

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> $field_label,
				'class'					=> 'asenha-checkbox asenha-hide-th disable-components ' . $field_slug,
			)
		);
				
		// =================================================================
		// SECURITY
		// =================================================================

		// Limit Login Attempts

		$field_id = 'limit_login_attempts';
		$field_slug = 'limit-login-attempts';
		$field_title = __( 'Limit Login Attempts', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Prevent brute force attacks by limiting the number of failed login attempts allowed per IP address.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle security ' . $field_slug,
			)
		);

		$field_id = 'login_fails_allowed';
		$field_slug = 'login-fails-allowed';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> '',
				'field_suffix'			=> __( 'failed login attempts allowed before 15 minutes lockout', 'admin-site-enhancements' ),
				'field_intro'			=> '',
				'field_placeholder'		=> '3',
				'field_min'				=> 1,
				'field_max'				=> 10,
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix extra-narrow no-margin security ' . $field_slug,
			)
		);

		$field_id = 'login_lockout_maxcount';
		$field_slug = 'login-lockout-maxcount';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> '',
				'field_suffix'			=> __( 'lockout(s) will block further login attempts for 24 hours', 'admin-site-enhancements' ),
				'field_intro'			=> '',
				'field_placeholder'		=> '3',
				'field_min'				=> 1,
				'field_max'				=> 10,
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix extra-narrow no-margin security ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'limit_login_attempts_ip_whitelist';
			$field_slug = 'limit-login-attempts-ip-whitelist';

			add_settings_field(
				$field_id,
				__( 'Never block the following IP addresses:', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 3,
					'field_intro'			=> '',
					'field_description'		=> __( 'Enter one IPv4 address per line', 'admin-site-enhancements' ) . '<br />' . 
											sprintf(
												/* translators: %1$s is the current user's IP address and %1$s is the header from which it is retrieved */
												__( 'Your IP address is %1$s <span class="faded">(from %2$s header)</span>', 'admin-site-enhancements' ), 
												$common_methods->get_user_ip_address( 'ip', 'limit-login-attempts' ),
												$common_methods->get_user_ip_address( 'header', 'limit-login-attempts' )
											),
					'field_placeholder'		=> __( 'e.g. 202.73.201.157', 'admin-site-enhancements' ),
					'class'					=> 'asenha-textarea margin-top-16 security ' . $field_slug,
				)
			);

        }

		$field_id = 'limit_login_attempts_header_override';
		$field_slug = 'limit-login-attempts-header-override';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Detect IP address from the following header first:</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_text_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> '',
				'field_prefix'			=> '',
				'field_suffix'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix full-width flex-column security ' . $field_slug,
			)
		);

		$field_id = 'limit_login_attempts_header_override_description';
		$field_slug = 'limit-login-attempts-header-override-description';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> __( 'For example, if your site is behind a trusted proxy, it\'s a good idea to use the <code>HTTP_X_FORWARDED_FOR</code> header. Leave empty to use the default detection method.' ),
				'class'					=> 'asenha-description security ' . $field_slug,
			)
		);

		$field_id = 'login_attempts_log_table';
		$field_slug = 'login-attempts-log-table';

		add_settings_field(
			$field_id,
			__( 'Failed login attempts:', 'admin-site-enhancements' ),
			[ $render_field, 'render_datatable' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'datatable',
				'field_description'		=> __( 'Only the latest 1,000 entries are kept in the database.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-text datatable margin-top-16 security ' . $field_slug,
				'table_title'			=> __( 'Failed Login Attempts Log', 'admin-site-enhancements' ),
				'table_name'			=> $wpdb->prefix . 'asenha_failed_logins',
			)
		);

		// Obfuscate Author Slugs

		$field_id = 'obfuscate_author_slugs';
		$field_slug = 'obfuscate-author-slugs';
		$field_title = __( 'Obfuscate Author Slugs', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Obfuscate publicly exposed author page URLs that shows the user slugs / usernames, e.g. <em>sitename.com/author/username1/</em> into <em>sitename.com/author/a6r5b8ytu9gp34bv/</em>, and output 404 errors for the original URLs. Also obfuscates in /wp-json/wp/v2/users/ REST API endpoint.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> false,
				'class'					=> 'asenha-toggle security ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// CAPTCHA Protection

			$field_id = 'captcha_protection';
			$field_slug = 'captcha-protection';
			$field_title = __( 'CAPTCHA Protection', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'				=> ASENHA_SLUG_U,
					'field_id'					=> $field_id,
					'field_slug'				=> $field_slug,
					'field_title'				=> $field_title,
					'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'			=> __( 'Add CAPTCHA protection to various forms in your site to help prevent automated bot, spam or malicious submissions.', 'admin-site-enhancements' ),
					'field_options_wrapper'		=> true,
					'field_options_moreless'	=> true,
					'class'						=> 'asenha-toggle security ' . $field_slug,
				)
			);
			
			$field_id = 'captcha_wp_locations';
			$field_slug = 'captcha-wp-locations';
			
			$options = array(
				__( 'Login', 'admin-site-enhancements' )			=> 'wp_login_form',
				__( 'Password reset', 'admin-site-enhancements' )	=> 'wp_password_reset_form',
				__( 'Registration', 'admin-site-enhancements' )		=> 'wp_registration_form',
				__( 'Comment', 'admin-site-enhancements' )			=> 'wp_comment_form',
			);

			add_settings_field(
				$field_id,
				__( 'On WordPress forms:', 'admin-site-enhancements' ),
				[ $render_field, 'render_checkboxes_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . '][]',
					'field_options'			=> $options,
					'field_default'			=> array( 'wp_login_form' ),
					'layout'				=> 'horizontal', // 'horizontal' or 'vertical'
					'class'					=> 'asenha-checkboxes margin-top-8 security ' . $field_slug,
				)
			);

		    // When WooCommerce is active
			if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
				$field_id = 'captcha_woo_locations';
				$field_slug = 'captcha-woo-locations';
				
				$options = array(
						__( 'Login', 'admin-site-enhancements' )			=> 'woo_login_form',
						__( 'Password reset', 'admin-site-enhancements' )	=> 'woo_password_reset_form',
						__( 'Registration', 'admin-site-enhancements' )		=> 'woo_registration_form',
						// __( 'Checkout', 'admin-site-enhancements' )			=> 'woo_checkout_form',
				);

				add_settings_field(
					$field_id,
					__( 'On WooCommerce forms:', 'admin-site-enhancements' ),
					[ $render_field, 'render_checkboxes_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'field_id'				=> $field_id,
						'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . '][]',
						'field_options'			=> $options,
						'field_default'			=> array( 'wp_login_form' ),
						'layout'				=> 'horizontal', // 'horizontal' or 'vertical'
						'class'					=> 'asenha-checkboxes margin-top-8 security ' . $field_slug,
					)
				);				
			}
						
			$field_id = 'captcha_protection_types';
			$field_slug = 'captcha-protection-types';

			add_settings_field(
				$field_id,
				__( 'CAPTCHA type', 'admin-site-enhancements' ),
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> array(
						__( 'ALTCHA (self-hosted)', 'admin-site-enhancements' )		=> 'altcha',
						__( 'Google reCAPTCHA', 'admin-site-enhancements' )			=> 'recaptcha',
						__( 'Cloudflare Turnstile', 'admin-site-enhancements' )		=> 'turnstile',
					),
					'field_select_default'	=> 'altcha',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-select extra-narrow margin-top-8 margin-bottom-12 security ' . $field_slug,
					'display_none_on_load'	=> false,
				)
			);

			$field_id = 'captcha_protection_altcha_wrapper';
			$field_slug = 'captcha-protection-altcha-wrapper';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-column full-width-div captcha-protection-altcha-wrapper"></div>',
				)
			);

			$field_id = 'captcha_protection_recaptcha_wrapper';
			$field_slug = 'captcha-protection-recaptcha-wrapper';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-column full-width-div captcha-protection-recaptcha-wrapper"></div>',
				)
			);

			$field_id = 'captcha_protection_turnstile_wrapper';
			$field_slug = 'captcha-protection-turnstile-wrapper';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-column full-width-div captcha-protection-turnstile-wrapper"></div>',
				)
			);
			
			// ALTCHA
			
			$field_id = 'altcha_secret_key';
			$field_slug = 'altcha-secret-key';

			add_settings_field(
				$field_id,
				__( 'Secret key', 'admin-site-enhancements' ) . ' <span class="faded">(' . __ ( 'auto-generated', 'admin-site-enhancements' ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-4 security width-66 ' . $field_slug,
					'read_only'				=> true,
				)
			);

			$field_id = 'altcha_widget';
			$field_slug = 'altcha-widget';

			add_settings_field(
				$field_id,
				__( 'ALTCHA widget', 'admin-site-enhancements' ),
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> array(
						__( "\"I'm not a robot\" checkbox", 'admin-site-enhancements' )	=> 'checkbox',
						__( "Invisible / floating", 'admin-site-enhancements' )			=> 'invisible',
					),
					'field_select_default'	=> 'checkbox',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-number extra-narrow margin-bottom-8 security ' . $field_slug,
					'display_none_on_load'	=> false,
				)
			);

			$field_id = 'altcha_complexity';
			$field_slug = 'altcha-complexity';

			add_settings_field(
				$field_id,
				__( 'Complexity', 'admin-site-enhancements' ) . ' <span class="faded">(' . __ ( 'of the CAPTCHA challenge', 'admin-site-enhancements' ) . ')</span>',
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> array(
						__( 'Low', 'admin-site-enhancements' )				=> 'low',
						__( 'Medium', 'admin-site-enhancements' )			=> 'medium',
						__( 'High (default)', 'admin-site-enhancements' )	=> 'high',
					),
					'field_select_default'	=> 'high',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-select extra-narrow margin-bottom-12 force-hide security ' . $field_slug,
					// 'display_none_on_load'	=> true,
				)
			);

			$field_id = 'altcha_expiration';
			$field_slug = 'altcha-expiration';

			add_settings_field(
				$field_id,
				__( 'Expiration', 'admin-site-enhancements' ) . ' <span class="faded">(' . __ ( 'the life-span of the challenge', 'admin-site-enhancements' ) . ')</span>',
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> array(
						__( '1 hour (default)', 'admin-site-enhancements' )	=> '3600',
						__( '4 hours', 'admin-site-enhancements' )	=> '14400',
						__( 'Never', 'admin-site-enhancements' )	=> '0',
					),
					'field_select_default'	=> '3600',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-select extra-narrow margin-bottom-12 force-hide security ' . $field_slug,
					// 'display_none_on_load'	=> true,
				)
			);

			$field_id = 'altcha_auto_verification';
			$field_slug = 'altcha-auto-verification';

			add_settings_field(
				$field_id,
				__( 'Auto verification', 'admin-site-enhancements' ),
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> array(
						__( 'Disabled', 'admin-site-enhancements' )			=> '',
						__( 'On page load', 'admin-site-enhancements' )		=> 'onload',
						__( 'On form focus', 'admin-site-enhancements' )	=> 'onfocus',
						__( 'On form submit', 'admin-site-enhancements' )	=> 'onsubmit',
					),
					'field_select_default'	=> '',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-select extra-narrow margin-bottom-16 force-hide security ' . $field_slug,
					'display_none_on_load'	=> false,
				)
			);

			$field_id = 'altcha_enable_delay';
			$field_slug = 'altcha-enable-delay';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Add a delay of 1.5 seconds to verification', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th force-hide security ' . $field_slug,
					// 'display_none_on_load'	=> true,
				)
			);

			$field_id = 'altcha_hide_logo';
			$field_slug = 'altcha-hide-logo';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Hide ALTCHA logo', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th margin-bottom-8 force-hide security ' . $field_slug,
					'display_none_on_load'	=> false,
				)
			);

			$field_id = 'altcha_hide_byline';
			$field_slug = 'altcha-hide-byline';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Hide "Powered by ALTCHA" byline', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th force-hide security ' . $field_slug,
					// 'display_none_on_load'	=> true,
				)
			);

			$field_id = 'altcha_advanced_toggler';
			$field_slug = 'altcha-advanced-toggler';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_content_toggler' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'field_id'			=> $field_id,
					'field_slug'		=> $field_slug,
					'show_text'			=> __( 'Advanced Options', 'admin-site-enhancements' ),
					'hide_text'			=> __( 'Advanced Options', 'admin-site-enhancements' ),
					'content_selector'	=> '.altcha-advanced-wrapper',
				)
			);

			$field_id = 'altcha_advanced_wrapper';
			$field_slug = 'altcha-advanced-wrapper';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-column full-width-div altcha-advanced-wrapper" style="display: none;"></div>',
				)
			);
			
			$field_id = 'altcha_checkbox_label';
			$field_slug = 'altcha-checkbox-label';

			add_settings_field(
				$field_id,
				__( 'Label for checkbox', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'Default:', 'admin-site-enhancements' ) . ' ' . __( "I'm not a robot", "admin-site-enhancements" ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text security full-width ' . $field_slug,
				)
			);
			
			$field_id = 'altcha_verifying_text';
			$field_slug = 'altcha-verifying-text';

			add_settings_field(
				$field_id,
				__( 'Label for verification process', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'Default:', 'admin-site-enhancements' ) . ' ' . __( "Verifying you're not a robot...", "admin-site-enhancements" ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text security margin-top-12 full-width ' . $field_slug,
				)
			);
			
			$field_id = 'altcha_verifying_wait_text';
			$field_slug = 'altcha-verifying-wait-text';

			add_settings_field(
				$field_id,
				__( 'Label for verification wait request', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'Default:', 'admin-site-enhancements' ) . ' ' . __( "Verifying... please wait.", "admin-site-enhancements" ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text security margin-top-12 full-width ' . $field_slug,
				)
			);

			$field_id = 'altcha_verified_text';
			$field_slug = 'altcha-verified-text';

			add_settings_field(
				$field_id,
				__( 'Label for verification success', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'Default:', 'admin-site-enhancements' ) . ' ' . __( "Verified", "admin-site-enhancements" ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text security margin-top-12 full-width ' . $field_slug,
				)
			);
						
			$field_id = 'altcha_verification_failed_text';
			$field_slug = 'altcha-verification-failed-text';

			add_settings_field(
				$field_id,
				__( 'Label for verification failure', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'Default:', 'admin-site-enhancements' ) . ' ' . __( "Verification failed. Try again later.", "admin-site-enhancements" ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text security margin-top-12 margin-bottom-12 full-width ' . $field_slug,
				)
			);
			
			// reCAPTCHA
			
			$field_id = 'recaptcha_widget';
			$field_slug = 'recaptcha-widget';

			add_settings_field(
				$field_id,
				__( 'reCAPTCHA widget', 'admin-site-enhancements' ),
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> array(
						'v2 - ' . __( "\"I'm not a robot\" checkbox", 'admin-site-enhancements' )	=> 'v2_checkbox',
						'v3 - ' . __( "Invisible / floating", 'admin-site-enhancements' )			=> 'v3_invisible',
					),
					'field_select_default'	=> 'v2_checkbox',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-number extra-narrow margin-bottom-8 security ' . $field_slug,
					'display_none_on_load'	=> false,
				)
			);

			$field_id = 'recaptcha_site_key_v2_checkbox';
			$field_slug = 'recaptcha-site-key-v2-checkbox';

			add_settings_field(
				$field_id,
				__( 'Site key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> false,
				)
			);

			$field_id = 'recaptcha_secret_key_v2_checkbox';
			$field_slug = 'recaptcha-secret-key-v2-checkbox';

			add_settings_field(
				$field_id,
				__( 'Secret key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> false,
				)
			);

			$field_id = 'recaptcha_site_key_v3_invisible';
			$field_slug = 'recaptcha-site-key-v3-invisible';

			add_settings_field(
				$field_id,
				__( 'Site key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> false,
				)
			);

			$field_id = 'recaptcha_secret_key_v3_invisible';
			$field_slug = 'recaptcha-secret-key-v3-invisible';

			add_settings_field(
				$field_id,
				__( 'Secret key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> false,
				)
			);

			$field_id = 'recaptcha_setup_instruction';
			$field_slug = 'recaptcha-setup-instruction';
	    	$field_description = '<div class="asenha-warning">' . __( 'Please login once with the chosen reCAPTCHA widget enabled to complete the setup process for that widget type and enable reCAPTCHA protection on your forms.', 'admin-site-enhancements' ) . '</div>';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> $field_description,
					'class'					=> 'asenha-description security ' . $field_slug,
				)
			);

			// Turnstile

			$field_id = 'turnstile_widget_theme';
			$field_slug = 'turnstile-widget-theme';

			add_settings_field(
				$field_id,
				__( 'Widget theme', 'admin-site-enhancements' ),
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> array(
						__( 'Light', 'admin-site-enhancements' )	=> 'light',
						__( 'Dark', 'admin-site-enhancements' )		=> 'dark',
						__( 'Auto', 'admin-site-enhancements' )		=> 'auto',
					),
					'field_select_default'	=> 'light',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-select extra-narrow margin-bottom-8 security ' . $field_slug,
					'display_none_on_load'	=> false,
				)
			);
			
			$field_id = 'turnstile_site_key';
			$field_slug = 'turnstile-site-key';

			add_settings_field(
				$field_id,
				__( 'Site key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> false,
				)
			);

			$field_id = 'turnstile_secret_key';
			$field_slug = 'turnstile-secret-key';

			add_settings_field(
				$field_id,
				__( 'Secret key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> false,
				)
			);
		}

		// Obfuscate Email Address

		$field_id = 'obfuscate_email_address';
		$field_slug = 'obfuscate-email-address';
		$field_title = __( 'Email Address Obfuscator', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Obfuscate email address to prevent spam bots from harvesting them, but make it readable like a regular email address for human visitors.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'field_options_moreless' => true,
				'class'					=> 'asenha-toggle security ' . $field_slug,
			)
		);

		$field_id = 'obfuscate_email_address_description';
		$field_slug = 'obfuscate-email-address-description';

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	$field_description = __( 'Use a shortcode like the following examples to display an email address on the frontend of your site:', 'admin-site-enhancements' );
        	$field_description .= '<ul>
        			<li><strong>[obfuscate email="info@orange.com"]</strong> ';
        	$field_description .= __( "to display the email on it's own line", 'admin-site-enhancements' );
        	$field_description .= '</li>
        			<li><strong>[obfuscate email="info@orange.com" display="inline"]</strong> ';
        	$field_description .= __( 'to show the email inline', 'admin-site-enhancements' );
        	$field_description .= '</li>
        			<li><strong>[obfuscate email="info@orange.com" display="inline" link="yes"]</strong> ';
        	$field_description .= __( 'to show the email inline and linked with <strong>mailto:</strong>', 'admin-site-enhancements' );
        	$field_description .= '</li>
        			<li><strong>[obfuscate email="info@orange.com" display="inline" link="yes" subject="';
        	$field_description .= __( "I'm interested to learn about your services...", 'admin-site-enhancements' ); 
        	$field_description .= '"]</strong> '; 
        	$field_description .= __( 'to show the email inline and linked with <strong>mailto:</strong> with a pre-defined subject line.', 'admin-site-enhancements' );
        	$field_description .= '</li>
        			<li><strong>[obfuscate email="info@orange.com" display="inline" link="yes" class="custom-class-name"]</strong> '; 
        	$field_description .= __( 'to show the email inline, linked with <strong>mailto:</strong> and has a custom CSS class to more easily customize the style.', 'admin-site-enhancements' ) ;
        	$field_description .= '</li>
        			<li><strong>[obfuscate email="info@orange.com" link="yes" text="Contact Us" class="custom-class-name"]</strong> ';
        	$field_description .= __( 'to show the email, linked with <strong>mailto:</strong>, with a custom text and has a custom CSS class to more easily customize the style, e.g. as a "Contact Us" button.', 'admin-site-enhancements' );
        	$field_description .= '</li>
        		</ul>';
        } else {
        	$field_description = __( 'Use a shortcode like the following examples to display an email address on the frontend of your site: 
        		<ul>
        			<li><strong>[obfuscate email="john@example.com"]</strong> to display the email on it\'s own line</li>
        			<li><strong>[obfuscate email="john@example.com" display="inline"]</strong> to show the email inline</li>
        		</ul>', 'admin-site-enhancements' );
        }

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> $field_description,
				'class'					=> 'asenha-description security ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'obfuscate_email_address_in_content';
			$field_slug = 'obfuscate-email-address-in-content';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Automatically obfuscate email addresses in post content.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th asenha-th-border-top security ' . $field_slug,
				)
			);

			$field_id = 'obfuscate_email_address_visitor_only';
			$field_slug = 'obfuscate-email-address-visitor-only';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Only auto-obfuscate email addresses for site visitors, not for logged-in users.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th security ' . $field_slug,
				)
			);
        }

		// Disable XML-RPC

		$field_id = 'disable_xmlrpc';
		$field_slug = 'disable-xmlrpc';
		$field_title = __( 'Disable XML-RPC', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Protect your site from brute force, DOS and DDOS attacks via <a href="https://kinsta.com/blog/xmlrpc-php/#what-is-xmlrpcphp" target="_blank">XML-RPC</a>. Also disables trackbacks and pingbacks.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> false,
				'class'					=> 'asenha-toggle security ' . $field_slug,
			)
		);

		// =================================================================
		// OPTIMIZATIONS
		// =================================================================

		// Image Upload Control

		$field_id = 'image_upload_control';
		$field_slug = 'image-upload-control';
		$field_title = __( 'Image Upload Control', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Resize newly uploaded, large images to a smaller dimension and delete originally uploaded files. BMPs and non-transparent PNGs will be converted to JPGs and resized.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle optimizations ' . $field_slug,
			)
		);

		$field_id = 'image_max_width';
		$field_slug = 'image-max-width';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> '<span class="subfield-prefix">' . __( 'Max width:', 'admin-site-enhancements' ) . '</span>',
				'field_suffix'			=> __( 'pixels. <span class="faded">(Default is 1920 pixels)</span>', 'admin-site-enhancements' ),
				'field_intro'			=> '',
				'field_placeholder'		=> '1920',
				'field_min'				=> 100,
				'field_max'				=> 3840,
				'field_description'		=> '',
				'class'					=> 'asenha-number asenha-hide-th narrow optimizations ' . $field_slug,
			)
		);

		$field_id = 'image_max_height';
		$field_slug = 'image-max-height';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> '<span class="subfield-prefix">' . __( 'Max height:', 'admin-site-enhancements' ) . '</span>',
				'field_suffix'			=> __( 'pixels <span class="faded">(Default is 1920 pixels)</span>', 'admin-site-enhancements' ),
				'field_intro'			=> '',
				'field_placeholder'		=> '1920',
				'field_min'				=> 100,
				'field_max'				=> 10000,
				// 'field_description'		=> 'To exclude an image from conversion and resizing, append \'-nr\' suffix to the file name, e.g. bird-photo-4k-nr.jpg',
				'class'					=> 'asenha-number asenha-hide-th narrow margin-bottom-4 optimizations ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'image_conversion_wrapper';
			$field_slug = 'image-conversion-wrapper';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-column full-width-div image-conversion-wrapper" style="display: none;"></div>',
				)
			);
			
			$field_id = 'convert_to_jpg_quality';
			$field_slug = 'convert-to-jpg-quality';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_number_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="subfield-prefix">' . __( 'JPG quality:', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> '',
					'field_intro'			=> '',
					'field_placeholder'		=> '82',
					'field_min'				=> 10,
					'field_max'				=> 100,
					'field_description'		=> __( 'Default is 82 from a range between 1 to 100. The higher the number, the higher the quality and the file size.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-number asenha-hide-th narrow optimizations ' . $field_slug,
				)
			);

			$field_id = 'convert_to_webp';
			$field_slug = 'convert-to-webp';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Convert BMP, PNG and JPG uploads to WebP instead of JPG.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th optimizations ' . $field_slug,
				)
			);

			$field_id = 'convert_to_webp_quality';
			$field_slug = 'convert-to-webp-quality';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_number_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="subfield-prefix">' . __( 'WebP quality:', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> '',
					'field_intro'			=> '',
					'field_placeholder'		=> '82',
					'field_min'				=> 10,
					'field_max'				=> 100,
					'field_description'		=> __( 'Default is 82 from a range between 1 to 100. The higher the number, the higher the quality and the file size.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-number asenha-hide-th narrow optimizations ' . $field_slug,
				)
			);

			// $field_id = 'keep_original_image';
			// $field_slug = 'keep-original-image';

			// add_settings_field(
			// 	$field_id,
			// 	'',
			// 	[ $render_field, 'render_checkbox_plain' ],
			// 	ASENHA_SLUG,
			// 	'main-section',
			// 	array(
			// 		'option_name'			=> ASENHA_SLUG_U,
			// 		'field_id'				=> $field_id,
			// 		'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
			// 		'field_label'			=> __( 'Do not delete original images.', 'admin-site-enhancements' ),
			// 		'class'					=> 'asenha-checkbox asenha-hide-th top-border padding-top-16 optimizations ' . $field_slug,
			// 	)
			// );
        }

		$field_id = 'image_upload_control_description';
		$field_slug = 'image-upload-control-description';
    	$field_description = __( 'To exclude an image from conversion and resizing, append \'-nr\' suffix to the file name, e.g. bird-photo-4k-nr.jpg', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> $field_description,
				'class'					=> 'asenha-description top-border optimizations ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'disable_image_conversion';
			$field_slug = 'disable-image-conversion';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					'field_label'			=> __( 'Only enable resize and disable conversion on all image uploads.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-checkbox asenha-hide-th custom-code ' . $field_slug,
				)
			);

			$field_id = 'heading_for_disabled_image_sizes';
			$field_slug = 'heading-for-disabled-image-sizes';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'Disable generation of the following intermediate sizes:', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading top-border optimizations ' . $field_slug,
				)
			);

			$field_id = 'disabled_image_sizes';
			$field_slug = 'disabled-image-sizes';

			$default_sizes = array( 'thumb', 'thumbnail', 'post-thumbnail', 'medium', 'medium_large', 'large', '1536x1536', '2048x2048', '-scaled', 'full' );
			$registered_image_subsizes = wp_get_registered_image_subsizes();
			
			foreach ( $registered_image_subsizes as $image_subsize_slug => $image_subsize_info ) {
				// $image_subsize element: 
				// 'thumbnail' => array(
				// 	width 	=> 150,
				// 	height 	=> 150,	
				// 	crop 	=> true,
				// )

				if ( in_array( $image_subsize_slug, $default_sizes ) ) {
					$size_type = 'default';
				} else {
					$size_type = 'additional';
				}
				
				if ( is_bool( $image_subsize_info['crop'] ) ) {
					$crop = ( $image_subsize_info['crop'] ) ? __( 'crop', 'admin-site-enhancements' ) : __( 'no crop', 'admin-site-enhancements' );
					$crop_position = '';
				} elseif ( is_array( $image_subsize_info['crop'] ) ) {
					$crop = __( 'crop', 'admin-site-enhancements' );
					$crop_position = 'x-' . $image_subsize_info['crop'][0] . ' ' . 'y-' . $image_subsize_info['crop'][1];
				}
				
				if ( 'crop' == $crop ) {
					if ( ! empty( $crop_position ) ) {
						$crop_info = $crop . ' | ' . $crop_position;
					} else {
						$crop_info = $crop;						
					}
				} else {
					$crop_info = $crop;
				}
							
				add_settings_field(
					$field_id . '_' . $image_subsize_slug,
					'',
					[ $render_field, 'render_checkbox_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						'parent_field_id'		=> $field_id,
						'field_id'				=> $image_subsize_slug,
						'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . '][' . $image_subsize_slug . ']',
						'field_label'			=> $image_subsize_slug . ' <span class="faded">(' . $size_type . ' | ' . $image_subsize_info['width'] . 'x' . $image_subsize_info['height'] . ' | ' . $crop_info . ')</span>',
						'class'					=> 'asenha-checkbox asenha-hide-th optimizations ' . $field_slug . ' ' . $image_subsize_slug,
					)
				);

			}        	
        }

		// Enable Revisions Control

		$field_id = 'enable_revisions_control';
		$field_slug = 'enable-revisions-control';
		$field_title = __( 'Revisions Control', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Prevent bloating the database by limiting the number of revisions to keep for some or all post types supporting revisions.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle optimizations ' . $field_slug,
			)
		);

		$field_id = 'revisions_max_number';
		$field_slug = 'revisions-max-number';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> __( 'Limit to', 'admin-site-enhancements' ),
				'field_suffix'			=> __( 'revisions for:', 'admin-site-enhancements' ),
				'field_intro'			=> '',
				'field_placeholder'		=> '10',
				'field_min'				=> 1,
				'field_max'				=> 100,
				'field_description'		=> '',
				'class'					=> 'asenha-number asenha-hide-th extra-narrow optimizations ' . $field_slug,
			)
		);

		$field_id = 'enable_revisions_control_for';
		$field_slug = 'enable-revisions-control-for';

		if ( is_array( $asenha_revisions_post_types ) ) {
			// Exclude Bricks builder template CPT as revisions are handled via a constant
			// Ref: https://academy.bricksbuilder.io/article/revisions/
			unset( $asenha_revisions_post_types['bricks_template'] );
			foreach ( $asenha_revisions_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				add_settings_field(
					$field_id . '_' . $post_type_slug,
					'',
					[ $render_field, 'render_checkbox_subfield' ],
					ASENHA_SLUG,
					'main-section',
					array(
						'option_name'			=> ASENHA_SLUG_U,
						'parent_field_id'		=> $field_id,
						'field_id'				=> $post_type_slug,
						'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $post_type_slug . ']',
						'field_label'			=> $post_type_label . ' <span class="faded">('. $post_type_slug .')</span>',
						'class'					=> 'asenha-checkbox asenha-hide-th asenha-half optimizations ' . $field_slug . ' ' . $post_type_slug,
					)
				);
			}
		}

		// Enable Heartbeat Control

		$field_id = 'enable_heartbeat_control';
		$field_slug = 'enable-heartbeat-control';
		$field_title = __( 'Heartbeat Control', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Modify the interval of the WordPress heartbeat API or disable it on admin pages, post creation/edit screens and/or the frontend. This will help reduce CPU load on the server.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle optimizations ' . $field_slug,
			)
		);

		$field_id = 'heartbeat_control_for_admin_pages';
		$field_slug = 'heartbeat-control-for-admin-pages';

		add_settings_field(
			$field_id,
			__( 'On admin pages', 'admin-site-enhancements' ),
			[ $render_field, 'render_radio_buttons_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				// 'field_label'			=> 'Temporary label',
				'field_radios'			=> array(
					__( 'Keep as is', 'admin-site-enhancements' )	=> 'default',
					__( 'Modify', 'admin-site-enhancements' )		=> 'modify',
					__( 'Disable', 'admin-site-enhancements' )		=> 'disable',
				),
				'field_default'			=> 'default',
				'class'					=> 'asenha-radio-buttons optimizations ' . $field_slug,
			)
		);

		$field_id = 'heartbeat_interval_for_admin_pages';
		$field_slug = 'heartbeat-interval-for-admin-pages';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_select_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> __( 'Set interval to once every', 'admin-site-enhancements' ),
				'field_suffix'			=> __( '<span class="faded">(Default is 1 minute)</span>', 'admin-site-enhancements' ),
				'field_select_options'	=> array(
					__( '15 seconds', 'admin-site-enhancements' )	=> 15,
					__( '30 seconds', 'admin-site-enhancements' )	=> 30,
					__( '1 minute', 'admin-site-enhancements' )		=> 60,
					__( '2 minutes', 'admin-site-enhancements' )	=> 120,
					__( '3 minutes', 'admin-site-enhancements' )	=> 180,
					__( '5 minutes', 'admin-site-enhancements' )	=> 300,
					__( '10 minutes', 'admin-site-enhancements' )	=> 600,
				),
				'field_select_default'	=> 60,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-number asenha-hide-th extra-narrow shift-up optimizations ' . $field_slug,
				'display_none_on_load'	=> true,
			)
		);

		$field_id = 'heartbeat_control_for_post_edit';
		$field_slug = 'heartbeat-control-for-post-edit';

		add_settings_field(
			$field_id,
			__( 'On post creation and edit screens', 'admin-site-enhancements' ),
			[ $render_field, 'render_radio_buttons_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				// 'field_label'			=> 'Temporary label',
				'field_radios'			=> array(
					__( 'Keep as is', 'admin-site-enhancements' )	=> 'default',
					__( 'Modify', 'admin-site-enhancements' )		=> 'modify',
					__( 'Disable', 'admin-site-enhancements' )		=> 'disable',
				),
				'field_default'			=> 'default',
				'class'					=> 'asenha-radio-buttons optimizations top-border ' . $field_slug,
			)
		);

		$field_id = 'heartbeat_interval_for_post_edit';
		$field_slug = 'heartbeat-interval-for-post-edit';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_select_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> __( 'Set interval to once every', 'admin-site-enhancements' ),
				'field_suffix'			=> __( '<span class="faded">(Default is 15 seconds)</span>', 'admin-site-enhancements' ),
				'field_select_options'	=> array( 
					__( '15 seconds', 'admin-site-enhancements' )	=> 15,
					__( '30 seconds', 'admin-site-enhancements' )	=> 30, 
					__( '45 seconds', 'admin-site-enhancements' )	=> 45, 
					__( '60 seconds', 'admin-site-enhancements' )	=> 60, 
					__( '90 seconds', 'admin-site-enhancements' )	=> 90, 
					__( '120 seconds', 'admin-site-enhancements' )	=> 120 
				),
				'field_select_default'	=> 15,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-number asenha-hide-th extra-narrow shift-up optimizations ' . $field_slug,
				'display_none_on_load'	=> true,
			)
		);

		$field_id = 'heartbeat_control_for_frontend';
		$field_slug = 'heartbeat-control-for-frontend';

		add_settings_field(
			$field_id,
			__( 'On the frontend', 'admin-site-enhancements' ),
			[ $render_field, 'render_radio_buttons_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				// 'field_label'			=> 'Temporary label',
				'field_radios'			=> array(
					__( 'Keep as is', 'admin-site-enhancements' )	=> 'default',
					__( 'Modify', 'admin-site-enhancements' )		=> 'modify',
					__( 'Disable', 'admin-site-enhancements' )		=> 'disable',
				),
				'field_default'			=> 'default',
				'class'					=> 'asenha-radio-buttons optimizations top-border ' . $field_slug,
			)
		);

		$field_id = 'heartbeat_interval_for_frontend';
		$field_slug = 'heartbeat-interval-for-frontend';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_select_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> __( 'Set interval to once every', 'admin-site-enhancements' ),
				'field_suffix'			=> '',
				'field_select_options'	=> array( 
					__( '15 seconds', 'admin-site-enhancements' )	=> 15,
					__( '30 seconds', 'admin-site-enhancements' )	=> 30,
					__( '1 minute', 'admin-site-enhancements' )		=> 60,
					__( '2 minutes', 'admin-site-enhancements' )	=> 120,
					__( '3 minutes', 'admin-site-enhancements' )	=> 180,
					__( '5 minutes', 'admin-site-enhancements' )	=> 300,
					__( '10 minutes', 'admin-site-enhancements' )	=> 600,
				),
				'field_select_default'	=> 60,
				'field_intro'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-number asenha-hide-th extra-narrow shift-up optimizations ' . $field_slug,
				'display_none_on_load'	=> true,
			)
		);

		// =================================================================
		// UTILITIES
		// =================================================================

		// SMTP Email Delivery

		$field_id = 'smtp_email_delivery';
		$field_slug = 'smtp-email-delivery';
		$field_title = __( 'Email Delivery', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Set custom sender name and email. Optionally use external SMTP service to ensure notification and transactional emails from your site are being delivered to inboxes.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_default_from_description';
		$field_slug = 'smtp-default-from-description';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> __( 'If set, the following sender name/email overrides WordPress core defaults but can still be overridden by other plugins that enables custom sender name/email, e.g. form plugins.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-description utilities ' . $field_slug,
			)
		);
		
		$field_id = 'smtp_default_from_name';
		$field_slug = 'smtp-default-from-name';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Sender name</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_text_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> '',
				'field_prefix'			=> '',
				'field_suffix'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix wide utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_default_from_email';
		$field_slug = 'smtp-default-from-email';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Sender email</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_text_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> '',
				'field_prefix'			=> '',
				'field_suffix'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix wide utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_force_from';
		$field_slug = 'smtp-force-from';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Force the usage of the sender name/email defined above. It will override those set by other plugins.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th utilities ' . $field_slug,
			)
		);
		
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'smtp_replyto_name';
			$field_slug = 'smtp-replyto-name';

			add_settings_field(
				$field_id,
				__( '<span class="field-sublabel sublabel-wide">Reply-to name</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix wide utilities ' . $field_slug,
				)
			);

			$field_id = 'smtp_replyto_email';
			$field_slug = 'smtp-replyto-email';

			add_settings_field(
				$field_id,
				__( '<span class="field-sublabel sublabel-wide">Reply-to email</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix wide utilities ' . $field_slug,
				)
			);

			$field_id = 'smtp_bcc_emails';
			$field_slug = 'smtp-bcc-emails';

			add_settings_field(
				$field_id,
				__( '<span class="field-sublabel sublabel-wide">Bcc</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> __( 'Separate with comma for multiple emails', 'admin-site-enhancements' ),
					'class'					=> 'asenha-text with-prefix-suffix with-description wide utilities ' . $field_slug,
				)
			);
		}

		$field_id = 'smtp_description';
		$field_slug = 'smtp--description';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> __( 'If set, the following SMTP service/account wil be used to deliver your emails.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-description top-border utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_host';
		$field_slug = 'smtp-host';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Host</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_text_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> '',
				'field_prefix'			=> '',
				'field_suffix'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix wide utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_port';
		$field_slug = 'smtp-port';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Port</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_number_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> '',
				'field_prefix'			=> '',
				'field_suffix'			=> '',
				'field_intro'			=> '',
				'field_description'		=> '',
				'field_min'				=> 1,
				'field_max'				=> 100000,
				'class'					=> 'asenha-text with-prefix-suffix narrow utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_security';
		$field_slug = 'smtp-security';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Security</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_radio_buttons_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				// 'field_label'			=> 'Temporary label',
				'field_radios'			=> array(
					__( 'None', 'admin-site-enhancements' )		=> 'none',
					__( 'SSL', 'admin-site-enhancements' )		=> 'ssl',
					__( 'TLS', 'admin-site-enhancements' )		=> 'tls',
				),
				'field_default'			=> 'default',
				'class'					=> 'asenha-radio-buttons with-prefix-suffix utilities ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'smtp_authentication';
			$field_slug = 'smtp-authentication';

			add_settings_field(
				$field_id,
				__( '<span class="field-sublabel sublabel-wide">Authentication</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'Enable', 'admin-site-enhancements' )	=> 'enable',
						__( 'Disable', 'admin-site-enhancements' )	=> 'disable',
					),
					'field_default'			=> 'enable',
					'class'					=> 'asenha-radio-buttons with-prefix-suffix utilities ' . $field_slug,
				)
			);			
		}

		$field_id = 'smtp_username';
		$field_slug = 'smtp-username';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Username</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_text_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> '',
				'field_prefix'			=> '',
				'field_suffix'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix wide utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_password';
		$field_slug = 'smtp-password';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Password</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_password_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> '',
				'field_prefix'			=> '',
				'field_suffix'			=> '',
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix wide utilities ' . $field_slug,
			)
		);
		
		$field_id = 'smtp_bypass_ssl_verification';
		$field_slug = 'smtp-bypass-ssl-verification';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Bypass verification of SSL certificate. This would be insecure if mail is delivered across the internet but could help in certain local and/or containerized WordPress scenarios.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th margin-top-8 utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_debug';
		$field_slug = 'smtp-debug';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_checkbox_plain' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				'field_label'			=> __( 'Enable debug mode and output the debug info into WordPress debug.log file.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-checkbox asenha-hide-th bottom-border utilities ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'smtp_email_log';
			$field_slug = 'smtp-email-log';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_checkbox_plain' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> __( 'Enable email delivery log, which serves as an archive and may help with troubleshooting delivery issues.', 'admin-site-enhancements' ),
					'field_label'		=> '<strong>' . __( 'Email Delivery Log', 'admin-site-enhancements' ) . '</strong><br />' . __( 'Log email deliveries for archiving or troubleshooting.', 'admin-site-enhancements' ),

					'class'					=> 'asenha-checkbox asenha-hide-th margin-top-8 utilities ' . $field_slug,
				)
			);

			$field_id = 'smtp_email_log_entries_amount_to_keep';
			$field_slug = 'smtp-email-log-entries-amount-to-keep';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> __( 'Keep the most recent', 'admin-site-enhancements' ),
					'field_suffix'			=> __( 'log entries and delete older ones.', 'admin-site-enhancements' ),
					'field_select_options'	=> array(
						// '1'			=> 1, // For testing
						'10'		=> 10,
						'25'		=> 25,
						'50'		=> 50,
						'100'		=> 100,
						'250'		=> 250,
						'500'		=> 500,
						'1,000'		=> 1000,
						'5,000'		=> 5000,
						'10,000'	=> 10000,
						'50,000'	=> 50000,
						'100,000'	=> 100000,
					),
					'field_select_default'	=> 1000,
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-number asenha-hide-th padding-left-20 extra-narrow bottom-border utilities ' . $field_slug,
					'display_none_on_load'	=> true,
				)
			);
		}
		
		$field_id = 'smtp_send_test_email_description';
		$field_slug = 'smtp-send-test-email-description';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> __( 'After saving the settings above, check if everything is configured properly below.', 'admin-site-enhancements' ),
				'class'					=> 'asenha-description utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_send_test_email_to';
		$field_slug = 'smtp-send-test-email-to';

		add_settings_field(
			$field_id,
			__( '<span class="field-sublabel sublabel-wide">Send a test email to</span>', 'admin-site-enhancements' ),
			[ $render_field, 'render_custom_html' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'html'		=> '<input type="text" id="test-email-to" class="asenha-subfield-text" name="" placeholder="" value=""><a id="send-test-email" class="button send-test-email">' . __( 'Send Now', 'admin-site-enhancements' ) . '</a>',
				'class'		=> 'asenha-html wide utilities ' . $field_slug,
			)
		);

		$field_id = 'smtp_send_test_email_result';
		$field_slug = 'smtp-send-test-email-result';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> '<div id="ajax-result" class="ajax-result-div" style="display:none;">
				<div class="sending-test-email"><img src="' . ASENHA_URL . 'assets/img/oval.svg" id="sending-test-email-spinner" class="spinner-img">' . __( 'Sending test email...', 'admin-site-enhancements' ) . '</div>
				<div id="test-email-success" class="test-email-success" style="display:none;"><span class="dashicons dashicons-yes"></span> <span>' . __( 'Test email was successfully processed</span>.<br />Please check the destination email\'s inbox to verify successful delivery.', 'admin-site-enhancements' ) . '</div>
				<div id="test-email-failed" class="test-email-failed" style="display:none;"><span class="dashicons dashicons-no-alt"></span> <span>' . __( 'Oops, something went wrong</span>.<br />Please double check your settings and the destination email address.', 'admin-site-enhancements' ) . '</div></div>',
				'class'					=> 'asenha-description utilities ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Form Builder
			$field_id = 'form_builder';
			$field_slug = 'form-builder';
			$field_title = __( 'Form Builder', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_title'			=> $field_title,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'		=> __( 'Enable the creation of various types of forms on the frontend to collect information from site visitors or users or members.', 'admin-site-enhancements' ) . ' ' . __( 'Once enabled, you can find the Forms menu item in the admin menu.', 'admin-site-enhancements' ),
					'field_options_moreless'=> true,
					'field_options_wrapper'	=> true,
					'class'					=> 'asenha-toggle utilities ' . $field_slug,
				)
			);

			$field_id = 'form_builder_email_delivery_description';
			$field_slug = 'form-builder-email-delivery-description';

			add_settings_field(
				$field_id,
				__( 'Email delivery', 'admin-site-enhancements' ),
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'Please ensure email delivery is correctly set up on this site for forms to work properly.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-description margin-top-8 utilities ' . $field_slug,
				)
			);

			$field_id = 'form_builder_email_template';
			$field_slug = 'form-builder-email-template';

			add_settings_field(
				$field_id,
				__( 'Email template', 'admin-site-enhancements' ),
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> array(
						__( 'Plain (no header image)', 'admin-site-enhancements' )	=> 'plain',
						__( 'Boxed Plain', 'admin-site-enhancements' )				=> 'boxed-plain',
						__( 'Lightly Striped', 'admin-site-enhancements' )			=> 'lightly-striped',
						__( 'Solid Bar Labels', 'admin-site-enhancements' )			=> 'solid-bar-labels',
					),
					'field_select_default'	=> 'boxed-plain',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-select extra-narrow margin-top-8 margin-bottom-12 utilities ' . $field_slug,
					'display_none_on_load'	=> false,
				)
			);

			$field_id = 'form_builder_email_header_image';
			$field_slug = 'form-builder-email-header-image';

			add_settings_field(
				$field_id,
				'Email header image',
				[ $render_field, 'render_media_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_media_frame_title'		=> __( 'Select an Image', 'admin-site-enhancements' ), // Media frame title
					'field_media_frame_multiple' 	=> false, // Allow multiple selection?
					'field_media_frame_library_type' => 'image', // Which media types to show
					'field_media_frame_button_text'	=> __( 'Use Selected Image', 'admin-site-enhancements' ), // Insertion button text
					'field_intro'					=> '',
					'field_description'				=> '',
					'class'							=> 'asenha-textarea margin-bottom-12 login-logout ' . $field_slug,
				)
			);

			$field_id = 'form_builder_email_header_image_attachment_id';
			$field_slug = 'form-builder-email-header-image-attachment-id';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_width_classname'	=> 'normal',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '<span class="field-sublabel sublabel-narrow">' . __( 'Attachment ID', 'admin-site-enhancements' ) . '</span>',
					'field_suffix'			=> '',
					'field_placeholder'		=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix no-field-title margin-top-8 force-hide login-logout ' . $field_slug,
				)
			);

			$field_id = 'form_builder_send_test_email_to';
			$field_slug = 'form-builder-send-test-email-to';

			add_settings_field(
				$field_id,
				__( '<span class="field-sublabel sublabel-wide">Send a test email to</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<input type="text" id="form-builder-test-email-to" class="asenha-subfield-text" name="" placeholder="" value=""><a id="form-builder-send-test-email" class="button form-builder-send-test-email">' . __( 'Send Now', 'admin-site-enhancements' ) . '</a>',
					'class'		=> 'asenha-html wide utilities ' . $field_slug,
				)
			);

			$field_id = 'form_builder_send_test_email_result';
			$field_slug = 'form-builder-send-test-email-result';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> '<div id="form-builder-ajax-result" class="form-builder-ajax-result-div" style="display:none;">
					<div class="form-builder-sending-test-email"><img src="' . ASENHA_URL . 'assets/img/oval.svg" id="sending-test-email-spinner" class="spinner-img">' . __( 'Sending test email...', 'admin-site-enhancements' ) . '</div>
					<div id="form-builder-test-email-success" class="test-email-success" style="display:none;"><span class="dashicons dashicons-yes"></span> <span>' . __( 'Test email was successfully processed</span>.<br />Please check the destination email\'s inbox to verify successful delivery.', 'admin-site-enhancements' ) . '</div>
					<div id="form-builder-test-email-failed" class="test-email-failed" style="display:none;"><span class="dashicons dashicons-no-alt"></span> <span>' . __( 'Oops, something went wrong</span>.<br />Please double check your settings and the destination email address.', 'admin-site-enhancements' ) . '</div></div>',
					'class'					=> 'asenha-description utilities ' . $field_slug,
				)
			);

			$field_id = 'form_builder_email_custom_css';
			$field_slug = 'form-builder-email-custom-css';

			add_settings_field(
				$field_id,
				__( 'Email custom CSS', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 10,
					'field_intro'			=> '',
					'field_description'		=> __( 'Selectors you can style includes:', 'admin-site-enhancements' ) . '<br /> body, .header-image-tr, .header-image-td, .container-tr, .container, .content, .content p, .field-row-with-top-border, .field-row-tr, .field-row-highlight-color, .field-row-regular-color, .field-row, .field-label, .field-value, .footer-text-tr, .footer-text',
					'class'					=> 'asenha-textarea syntax-highlighted margin-bottom-8 utilities ' . $field_slug,
				)
			);
			
			$field_id = 'form_builder_captcha_description';
			$field_slug = 'form-builder-captcha-description';

			add_settings_field(
				$field_id,
				__( 'Spam protection fields', 'admin-site-enhancements' ),
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'For the ALTCHA, reCAPTCHA and Turnstile fields, the widget type / theme, site keys and secret keys are taken from the values set in the "Security >> CAPTCHA Protection" module. Please configure that module to use these fields in the form builder.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-description utilities ' . $field_slug,
				)
			);

			$field_id = 'form_builder_captcha_info_toggler';
			$field_slug = 'form-builder-captcha-info-toggler';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_content_toggler' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'field_id'			=> $field_id,
					'field_slug'		=> $field_slug,
					'show_text'			=> __( 'Show more', 'admin-site-enhancements' ),
					'hide_text'			=> __( 'Show less', 'admin-site-enhancements' ),
					'content_selector'	=> '.form-builder-captcha-info-wrapper',
				)
			);

			$field_id = 'form_builder_captcha_info_wrapper';
			$field_slug = 'form-builder-captcha-info-wrapper';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-column full-width-div form-builder-captcha-info-wrapper" style="display: none;"></div>',
				)
			);
			
			$field_id = 'form_builder_altcha_secret_key';
			$field_slug = 'form-builder-altcha-secret-key';

			add_settings_field(
				$field_id,
				__( 'ALTCHA secret key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text top-border padding-top-16 margin-bottom-12 security width-75 ' . $field_slug,
					'read_only'				=> true,
				)
			);

			$field_id = 'form_builder_recaptcha_site_key_v2_checkbox';
			$field_slug = 'form-builder-recaptcha-site-key-v2-checkbox';

			add_settings_field(
				$field_id,
				__( 'reCAPTCHA v2 site key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text top-border padding-top-16 margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> true,
				)
			);

			$field_id = 'form_builder_recaptcha_secret_key_v2_checkbox';
			$field_slug = 'form-builder-recaptcha-secret-key-v2-checkbox';

			add_settings_field(
				$field_id,
				__( 'reCAPTCHA v2 secret key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> true,
				)
			);

			$field_id = 'form_builder_recaptcha_site_key_v3_invisible';
			$field_slug = 'form-builder-recaptcha-site-key-v3-invisible';

			add_settings_field(
				$field_id,
				__( 'reCAPTCHA v3 site key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> true,
				)
			);

			$field_id = 'form_builder_recaptcha_secret_key_v3_invisible';
			$field_slug = 'form-builder-recaptcha-secret-key-v3-invisible';

			add_settings_field(
				$field_id,
				__( 'reCAPTCHA v3 secret key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> true,
				)
			);

			$field_id = 'form_builder_turnstile_site_key';
			$field_slug = 'form-builder-turnstile-site-key';

			add_settings_field(
				$field_id,
				__( 'Turnstile site key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text top-border padding-top-16 margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> true,
				)
			);

			$field_id = 'form_builder_turnstile_secret_key';
			$field_slug = 'form-builder-turnstile-secret-key';

			add_settings_field(
				$field_id,
				__( 'Turnstile secret key', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text margin-bottom-8 security width-75 ' . $field_slug,
					'read_only'				=> true,
				)
			);
			
			// Local User Avatar
			$field_id = 'local_user_avatar';
			$field_slug = 'local-user-avatar';
			$field_title = __( 'Local User Avatar', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				$field_title,
				[ $render_field, 'render_checkbox_toggle' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_title'			=> $field_title,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_description'		=> __( 'Enable usage of any image from WordPress Media Library as user avatars.', 'admin-site-enhancements' ),
					'field_options_wrapper'	=> true,
					'class'					=> 'asenha-toggle utilities ' . $field_slug,
				)
			);
		}

		// Multiple User Roles

		$field_id = 'multiple_user_roles';
		$field_slug = 'multiple-user-roles';
		$field_title = __( 'Multiple User Roles', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Enable assignment of multiple roles during user account creation and editing. This maybe useful for working with roles not defined in WordPress core, e.g. from e-commerce or LMS plugins.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'class'					=> 'asenha-toggle utilities ' . $field_slug,
			)
		);
		
		// Image Sizes Panel

		$field_id = 'image_sizes_panel';
		$field_slug = 'image-sizes-panel';
		$field_title = __( 'Image Sizes Panel', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Display a panel showing and linking to all available sizes when viewing an image in the media library. Especially useful to quickly get the URL of a particular image size.', 'admin-site-enhancements' ),
				'field_options_wrapper'	=> true,
				'class'					=> 'asenha-toggle utilities ' . $field_slug,
			)
		);

		// View Admin as Role

		$field_id = 'view_admin_as_role';
		$field_slug = 'view-admin-as-role';
		$field_title = __( 'View Admin as Role', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'View admin pages and the site (logged-in) as one of the non-administrator user roles.', 'admin-site-enhancements' ),
				'field_options_moreless'=> true,
				'field_options_wrapper'	=> true,
				'class'					=> 'asenha-toggle utilities ' . $field_slug,
			)
		);

		$current_user = wp_get_current_user();
		$current_user_username = $current_user->user_login;

		$field_id = 'view_admin_as_role_description';
		$field_slug = 'view-admin-as-role-description';
		
		$role_reset_link = site_url( '/?reset-for=' ) . $current_user_username;

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> '<div class="asenha-warning"><strong>' . sprintf( 
												/* translators: %s is URL of the role reset link */
												__( 'If something goes wrong</strong> and you need to regain access to your account as an administrator, please visit the following URL: <br /><strong>%s</strong><br /><br />If you use <strong>Ninja Firewall</strong>, please uncheck "Block attempts to gain administrative privileges" in the Firewall Policies settings before you try to view as a non-admin user role to <strong>prevent being locked out</strong> of your admin account.', 'admin-site-enhancements' ), 
												$role_reset_link 
											) . '<br /><br />' . __( 'In any case, please also <strong>create at least one backup admin user</strong> as a last resort should your primary admin user fails to properly login as admin. With this second admin user, you can also restore the admin role for your primary admin user.', 'admin-site-enhancements' ) . '</div>',
				'class'					=> 'asenha-description utilities ' . $field_slug,
			)
		);

		// Enable Password Protection

		$field_id = 'enable_password_protection';
		$field_slug = 'enable-password-protection';
		$field_title = __( 'Password Protection', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Password-protect the entire site to hide the content from public view and search engine bots / crawlers. Logged-in administrators can still access the site as usual.', 'admin-site-enhancements' ),
				'field_options_moreless'=> true,
				'field_options_wrapper'	=> true,
				'class'					=> 'asenha-toggle utilities ' . $field_slug,
			)
		);

		$field_id = 'password_protection_password';
		$field_slug = 'password-protection-password';

		add_settings_field(
			$field_id,
			__( 'Set the password:', 'admin-site-enhancements' ),
			[ $render_field, 'render_password_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_type'			=> 'with-prefix-suffix',
				'field_prefix'			=> '',
				'field_suffix'			=> '<span class="faded">' . __( '(Default is \'secret\')', 'admin-site-enhancements' ) . '</span>',
				'field_description'		=> '',
				'class'					=> 'asenha-text with-prefix-suffix utilities ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'password_protection_ip_whitelist';
			$field_slug = 'password-protection-ip-whitelist';

			add_settings_field(
				$field_id,
				__( 'Allow visitors with the following IP addresses to view the site directly:', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 3,
					'field_intro'			=> '',
					'field_description'		=> __( 'Enter one IPv4 address per line', 'admin-site-enhancements' ) . '<br />' . 
											sprintf(
												/* translators: %1$s is the current user's IP address and %1$s is the header from which it is retrieved */
												__( 'Your IP address is %1$s <span class="faded">(from %2$s header)</span>', 'admin-site-enhancements' ), 
												$common_methods->get_user_ip_address( 'ip', 'password-protection' ),
												$common_methods->get_user_ip_address( 'header', 'password-protection' )
											),
					'field_placeholder'		=> __( 'e.g. 202.73.201.157', 'admin-site-enhancements' ),
					'class'					=> 'asenha-textarea margin-top-16 utilities ' . $field_slug,
				)
			);

			$field_id = 'password_protection_header_override';
			$field_slug = 'password-protection-header-override';

			add_settings_field(
				$field_id,
				__( '<span class="field-sublabel sublabel-wide">Detect IP address from the following header first:</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix full-width flex-column security ' . $field_slug,
				)
			);

			$field_id = 'password_protection_header_override_description';
			$field_slug = 'password-protection-header-override-description';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'For example, if your site is behind a trusted proxy, it\'s a good idea to use the <code>HTTP_X_FORWARDED_FOR</code> header. Leave empty to use the default detection method.' ),
					'class'					=> 'asenha-description security ' . $field_slug,
				)
			);

			// Determine which password to show in the description section for bypass via URL param below
			$stored_password = ( isset( $options['password_protection_password'] ) ) ? $options['password_protection_password'] : '';
			if ( ! empty( $stored_password ) ) {
				$displayed_password = $stored_password;
			} else {
				$displayed_password = 'yourpassword';				
			}

			$field_id = 'password_protection_description_url_parameter';
			$field_slug = 'password-protection-description-url-parameter';
			
			/* translators: 1: the password, 2: the site URL, 3: the password */
	    	$field_description = sprintf( __( 'You can also append <strong>?bypass=%1$s</strong> in the URL to bypass the password entry form. e.g. %2$s/?bypass=%3$s. This can be useful when you need to quickly share a dev site with another person, e.g. a client.', 'admin-site-enhancements' ), $displayed_password, site_url(), $displayed_password);

			add_settings_field(
				$field_id,
				__( 'Bypass password protection with URL parameter', 'admin-site-enhancements' ),
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> $field_description,
					'class'					=> 'asenha-description margin-top-16 utilities ' . $field_slug,
				)
			);

			$field_id = 'password_protection_description_design';
			$field_slug = 'password-protection-description-design';
			
			/* translators: 1: the password, 2: the site URL, 3: the password */
	    	$field_description = __( 'If you enable the Login Page Customizer module, the Login Form Background and Page Background elements will also be applied to the design of the password-protected pages.', 'admin-site-enhancements' );

			add_settings_field(
				$field_id,
				__( 'Design of password-protected pages', 'admin-site-enhancements' ),
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> $field_description,
					'class'					=> 'asenha-description margin-top-16 utilities ' . $field_slug,
				)
			);

			$field_id = 'password_protection_advanced_toggler';
			$field_slug = 'password-protection-advanced-toggler';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_content_toggler' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'field_id'			=> $field_id,
					'field_slug'		=> $field_slug,
					'show_text'			=> __( 'Advanced Options', 'admin-site-enhancements' ),
					'hide_text'			=> __( 'Advanced Options', 'admin-site-enhancements' ),
					'content_selector'	=> '.password-protection-advanced-wrapper',
				)
			);

			$field_id = 'password_protection_advanced_wrapper';
			$field_slug = 'password-protection-advanced-wrapper';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_custom_html' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'html'		=> '<div class="subfields-container subfields-in-column full-width-div password-protection-advanced-wrapper" style="display: none;"></div>',
				)
			);
			
			$field_id = 'password_protection_password_field_label';
			$field_slug = 'password-protection-password-field-label';

			add_settings_field(
				$field_id,
				__( 'Label for password field', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'Default:', 'admin-site-enhancements' ) . ' ' . __( "Password", "admin-site-enhancements" ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text utilities margin-top-16 full-width ' . $field_slug,
				)
			);

			$field_id = 'password_protection_button_label';
			$field_slug = 'password-protection-button-label';

			add_settings_field(
				$field_id,
				__( 'Label for button', 'admin-site-enhancements' ) . ' <span class="faded">(' . __( 'Default:', 'admin-site-enhancements' ) . ' ' . __( "View Content", "admin-site-enhancements" ) . ')</span>',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> '',
					'class'					=> 'asenha-text utilities margin-top-16 margin-bottom-12 full-width ' . $field_slug,
				)
			);
        }

		// Maintenance Mode

		$field_id = 'maintenance_mode';
		$field_slug = 'maintenance-mode';
		$field_title = __( 'Maintenance Mode', 'admin-site-enhancements' );

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> __( 'Show a customizable maintenance page on the frontend while performing a brief maintenance to your site. Logged-in administrators can still view the site as usual.', 'admin-site-enhancements' ),
				'field_options_wrapper'		=> true,
				'field_options_moreless'	=> true,
				'class'						=> 'asenha-toggle utilities ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'maintenance_page_type';
			$field_slug = 'maintenance-page-type';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_radio_buttons_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
					// 'field_label'			=> 'Temporary label',
					'field_radios'			=> array(
						__( 'Use a customizable page', 'admin-site-enhancements' )		=> 'custom',
						__( 'Use an existing page', 'admin-site-enhancements' )			=> 'existing',
					),
					'field_default'			=> 'custom',
					'class'					=> 'asenha-radio-buttons bold-label shift-up disable-components ' . $field_slug,
				)
			);
		}

		$field_id = 'maintenance_page_type_custom';
		$field_slug = 'maintenance-page-type-custom';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_custom_html' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'html'		=> '<div class="subfields-container subfields-in-column maintenance-page-type-custom"></div>',
			)
		);
			
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'maintenance_page_title';
			$field_slug = 'maintenance-page-title';

			add_settings_field(
				$field_id,
				__( 'Page Title <span class="faded">(shown in the browser tab)</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> __( 'Under maintenance', 'admin-site-enhancements' ),
					'class'					=> 'asenha-text margin-top-12 utilities full-width ' . $field_slug,
				)
			);
		}

		$field_id = 'maintenance_page_heading';
		$field_slug = 'maintenance-page-heading';

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {

			// https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
			// https://www.tiny.cloud/docs/advanced/available-toolbar-buttons/
			$editor_settings = array(
				'media_buttons'		=> true,
				'textarea_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
				'textarea_rows'		=> 3,
				'tiny_mce'			=> true,
				'tinymce'			=> array(
					// 'toolbar1'		=> 'bold,italic,underline,separator,link,unlink,undo,redo',
					'toolbar1'		=> 'bold,italic,underline,strikethrough,superscript,subscript,blockquote,bullist,numlist,alignleft,aligncenter,alignjustify,alignright,alignnone,link,unlink,fontsizeselect,forecolor,undo,redo,removeformat,code',
					'content_css'	=> ASENHA_URL . 'assets/css/settings-wpeditor.css',
				),
				'editor_css'		=> '',
				'wpautop'			=> true,
				'quicktags'			=> false,
				'default_editor'	=> 'tinymce', // 'tinymce' or 'html'
			);

			add_settings_field(
				$field_id,
				__( 'Heading', 'admin-site-enhancements' ),
				[ $render_field, 'render_wpeditor_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_intro'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> __( 'We\'ll be back soon.', 'admin-site-enhancements' ),
					'editor_settings'		=> $editor_settings,
					'class'					=> 'asenha-textarea utilities has-wpeditor margin-top-20 ' . $field_slug,
				)
			);
        } else {
			add_settings_field(
				$field_id,
				__( 'Heading', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> __( 'We\'ll be back soon.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-text utilities full-width margin-bottom-20 ' . $field_slug,
				)
			);        	
        }
		
		$field_id = 'maintenance_page_description';
		$field_slug = 'maintenance-page-description';

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$editor_settings = array(
				'media_buttons'		=> true,
				'textarea_name'		=> ASENHA_SLUG_U . '['. $field_id .']',
				'textarea_rows'		=> 3,
				'tiny_mce'			=> true,
				'tinymce'			=> array(
					// 'toolbar1'		=> 'bold,italic,underline,separator,link,unlink,undo,redo',
					'toolbar1'		=> 'bold,italic,underline,strikethrough,superscript,subscript,blockquote,bullist,numlist,alignleft,aligncenter,alignjustify,alignright,alignnone,link,unlink,fontsizeselect,forecolor,undo,redo,removeformat,code',
					'content_css'	=> ASENHA_URL . 'assets/css/settings-wpeditor.css',
				),
				'editor_css'		=> '',
				'wpautop'			=> true,
				'quicktags'			=> false,
				'default_editor'	=> 'tinymce', // 'tinymce' or 'html'
			);

			add_settings_field(
				$field_id,
				__( 'Description', 'admin-site-enhancements' ),
				[ $render_field, 'render_wpeditor_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_intro'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> __( 'This site is undergoing maintenance for an extended period today. Thanks for your patience.', 'admin-site-enhancements' ),
					'editor_settings'		=> $editor_settings,
					'class'					=> 'asenha-textarea margin-top-12 utilities has-wpeditor ' . $field_slug,
				)
			);
        } else {
			add_settings_field(
				$field_id,
				__( 'Description', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 5,
					'field_intro'			=> '',
					'field_description'		=> '',
					'field_placeholder'		=> __( 'This site is undergoing maintenance for an extended period today. Thanks for your patience.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-textarea utilities ' . $field_slug,
				)
			);
        }

		$field_id = 'maintenance_page_background';
		$field_slug = 'maintenance-page-background';

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	$field_radios = array(
					__( 'Stripes', 'admin-site-enhancements' )	=> 'stripes',
					__( 'Curves', 'admin-site-enhancements' )	=> 'curves',
					__( 'Lines', 'admin-site-enhancements' )	=> 'lines',
					__( 'Pattern', 'admin-site-enhancements' )	=> 'pattern',
					__( 'Image', 'admin-site-enhancements' )	=> 'image',
					__( 'Color', 'admin-site-enhancements' )	=> 'solid_color',
				);
        } else {
        	$field_radios = array(
					__( 'Stripes', 'admin-site-enhancements' )	=> 'stripes',
					__( 'Curves', 'admin-site-enhancements' )	=> 'curves',
					__( 'Lines', 'admin-site-enhancements' )	=> 'lines',
				);        	
        }

		add_settings_field(
			$field_id,
			__( 'Background', 'admin-site-enhancements' ),
			[ $render_field, 'render_radio_buttons_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_name'			=> ASENHA_SLUG_U . '[' . $field_id . ']',
				// 'field_label'			=> 'Temporary label',
				'field_radios'			=> $field_radios,
				'field_default'			=> 'default',
				'class'					=> 'asenha-radio-buttons margin-top-12 utilities ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'maintenance_page_background_pattern';
			$field_slug = 'maintenance-page-background-pattern';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_select_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_select_options'	=> $background_pattern_options,
					'field_select_default'	=> 'blurry-gradient-blue',
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-number asenha-hide-th extra-narrow shift-up utilities ' . $field_slug,
					'display_none_on_load'	=> true,
				)
			);

			$field_id = 'maintenance_page_background_image';
			$field_slug = 'maintenance-page-background-image';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_media_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_media_frame_title'		=> __( 'Select an Image', 'admin-site-enhancements' ), // Media frame title
					'field_media_frame_multiple' 	=> false, // Allow multiple selection?
					'field_media_frame_library_type' => 'image', // Which media types to show
					'field_media_frame_button_text'	=> __( 'Use Selected Image', 'admin-site-enhancements' ), // Insertion button text
					'field_intro'					=> '',
					'field_description'				=> __( 'You can also paste an image URL hosted on another site.', 'admin-site-enhancements' ) . '<br />' . sprintf( 
															/* translators: %1$s etc are links to external resources */
															__( 'Resources: %1$s | %2$s | %3$s | %4$s', 'admin-site-enhancements' ),
															'<a href="https://openverse.org/" target="_blank">openverse</a>',
															'<a href="https://unsplash.com/" target="_blank">Unsplash</a>',
															'<a href="https://haikei.app/generators/" target="_blank">haikei</a>',
															'<a href="https://gradients.app/en/new" target="_blank">Gradients.app</a>'
														),
					'class'							=> 'asenha-textarea shift-more-up utilities ' . $field_slug,
				)
			);

			$field_id = 'maintenance_page_background_color';
			$field_slug = 'maintenance-page-background-color';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_color_picker_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'					=> ASENHA_SLUG_U,
					'field_id'						=> $field_id,
					'field_slug'					=> $field_slug,
					'field_name'					=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_intro'					=> '',
					'field_description'				=> '',
					'field_default_value'			=> 'eeeeee', // Show or hide on page load
					'class'							=> 'asenha-textarea shift-more-up utilities ' . $field_slug,
				)
			);
			
			$field_id = 'maintenance_page_head_code';
			$field_slug = 'maintenance-page-head-code';

			add_settings_field(
				$field_id,
				__( 'Custom &lt;head&gt; Code', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 10,
					'field_intro'			=> '',
					'field_description'		=> __( 'You can, for example, use this to add &lt;link&gt; for loading custom web fonts.', 'admin-site-enhancements' ),
					'class'					=> 'asenha-textarea syntax-highlighted utilities ' . $field_slug,
				)
			);
			
			$field_id = 'maintenance_page_custom_css';
			$field_slug = 'maintenance-page-custom-css';

			add_settings_field(
				$field_id,
				__( 'Custom CSS', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 20,
					'field_intro'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-textarea syntax-highlighted margin-top-12 utilities ' . $field_slug,
				)
			);
        }
        
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
	        $field_id = 'maintenance_page_slug';
			$field_slug = 'maintenance-page-slug';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> site_url() . '/',
					'field_suffix'			=> '/',
					'field_placeholder'		=> __( 'e.g. maintenance', 'admin-site-enhancements' ),
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix utilities ' . $field_slug,
				)
			);

			$field_id = 'maintenance_page_exclude_urls';
			$field_slug = 'maintenance-page-exclude-urls';

			add_settings_field(
				$field_id,
				__( 'Exclude the following URLs from maintenance mode:', 'admin-site-enhancements' ),
				[ $render_field, 'render_textarea_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_slug'			=> $field_slug,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'textarea',
					'field_rows'			=> 3,
					'field_intro'			=> '',
					'field_description'		=> __( 'To exclude https://www.site.com/contact/, just enter the relative URL /contact/. Enter one URL per line.', 'admin-site-enhancements' ),
					'field_placeholder'		=> __( 'e.g. /contact/', 'admin-site-enhancements' ),
					'class'					=> 'asenha-textarea margin-top-16 security ' . $field_slug,
				)
			);

			$field_id = 'heading_for_maintenance_mode_access_for';
			$field_slug = 'heading-for-maintenance-mode-access-for';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_subfields_heading' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'subfields_heading'		=> __( 'Also enable frontend access for:', 'admin-site-enhancements' ),
					'class'					=> 'asenha-heading margin-top-8 content-management ' . $field_slug,
				)
			);

			$field_id = 'maintenance_mode_access_for';
			$field_slug = 'maintenance-mode-access-for';

			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
					if ( 'administrator' != $role_slug ) {
						add_settings_field(
							$field_id . '_' . $role_slug,
							'',
							[ $render_field, 'render_checkbox_subfield' ],
							ASENHA_SLUG,
							'main-section',
							array(
								'option_name'			=> ASENHA_SLUG_U,
								'parent_field_id'		=> $field_id,
								'field_id'				=> $role_slug,
								'field_name'			=> ASENHA_SLUG_U . '['. $field_id .'][' . $role_slug . ']',
								'field_label'			=> $role_label,
								'class'					=> 'asenha-checkbox asenha-hide-th asenha-half content-management ' . $field_slug . ' ' . $role_slug,
							)
						);						
					}
				}
			}
        }

		$field_id = 'maintenance_mode_description';
		$field_slug = 'maintenance-mode-description';

		add_settings_field(
			$field_id,
			'',
			[ $render_field, 'render_description_subfield' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_description'		=> '<div class="asenha-warning"><strong>' . __( 'Please clear your cache</strong> after enabling or disabling maintenance mode. This ensures site visitors see either the maintenance page or the actual content of each page.', 'admin-site-enhancements' ) . '</div>',
				'class'					=> 'asenha-description utilities ' . $field_slug,
			)
		);

		// Redirect 404 to Homepage

		$field_id = 'redirect_404_to_homepage';
		$field_slug = 'redirect-404-to-homepage';

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	$module_title = __( 'Redirect 404', 'admin-site-enhancements' );
        	$module_description = __( 'Perform 301 (permanent) redirect to a URL of your choice', 'admin-site-enhancements' );
        	$field_options_wrapper = true;
        	$field_options_moreless = true;
        } else {
        	$module_title = __( 'Redirect 404 to Homepage', 'admin-site-enhancements' );
        	$module_description = __( 'Perform 301 (permanent) redirect to the homepage for all 404 (not found) pages.', 'admin-site-enhancements' );
        	$field_options_wrapper = false;
        	$field_options_moreless = false;
        }

		$field_title = $module_title;

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'				=> ASENHA_SLUG_U,
				'field_id'					=> $field_id,
				'field_slug'				=> $field_slug,
				'field_title'				=> $field_title,
				'field_name'				=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'			=> $module_description,
				'field_options_wrapper'		=> $field_options_wrapper,
				'field_options_moreless'	=> $field_options_moreless,
				'class'						=> 'asenha-toggle utilities ' . $field_slug,
			)
		);

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_id = 'redirect_404_to_slug';
			$field_slug = 'redirect-404-to-slug';

			add_settings_field(
				$field_id,
				__( 'Redirect to:', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> 'with-prefix-suffix',
					'field_prefix'			=> site_url() . '/',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix margin-top-8 login-logout ' . $field_slug,
				)
			);        	
        }

		// Display System Summary

		$field_id = 'display_system_summary';
		$field_slug = 'display-system-summary';
		$field_title = __( 'Display System Summary', 'admin-site-enhancements' );

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	$module_description = __( 'Show quick summary of the system the site is running on to admins, in the "At a Glance" dashboard widget. This includes the web server software, the PHP version, the database software and server IP address. It will also show the size of the site, database, root, wp-content, plugins, themes and upload folders.', 'admin-site-enhancements' );
        } else {
        	$module_description = __( 'Show quick summary of the system the site is running on to admins, in the "At a Glance" dashboard widget. This includes the web server software, the PHP version, the database software and server IP address.', 'admin-site-enhancements' );
        }


		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> $module_description,
				'field_options_wrapper'	=> true,
				'class'					=> 'asenha-toggle utilities ' . $field_slug,
			)
		);

		// Search Engines Visibility Status

		$field_id = 'search_engine_visibility_status';
		$field_slug = 'search-engine-visibility-status';
		$field_title = __( 'Search Engines Visibility Status', 'admin-site-enhancements' );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$field_options_moreless = true;
			$field_options_wrapper = true;
		} else {
			$field_options_moreless = false;
			$field_options_wrapper = false;
		}

		add_settings_field(
			$field_id,
			$field_title,
			[ $render_field, 'render_checkbox_toggle' ],
			ASENHA_SLUG,
			'main-section',
			array(
				'option_name'			=> ASENHA_SLUG_U,
				'field_id'				=> $field_id,
				'field_slug'			=> $field_slug,
				'field_title'			=> $field_title,
				'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
				'field_description'		=> __( 'Show admin bar status when search engines are set to be discouraged from indexing the site. This is set through a "Search engine visibility" checkbox in Settings >> Reading.', 'admin-site-enhancements' ),
				'field_options_moreless'=> $field_options_moreless,
				'field_options_wrapper'	=> $field_options_wrapper,
				'class'					=> 'asenha-toggle utilities ' . $field_slug,
			)
		);

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {	
			$field_id = 'live_site_url';
			$field_slug = 'live-site-url';

			add_settings_field(
				$field_id,
				__( '<span class="field-sublabel sublabel-wide">Live Site URL</span>', 'admin-site-enhancements' ),
				[ $render_field, 'render_text_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_id'				=> $field_id,
					'field_name'			=> ASENHA_SLUG_U . '['. $field_id .']',
					'field_type'			=> '',
					'field_prefix'			=> '',
					'field_suffix'			=> '',
					'field_description'		=> '',
					'class'					=> 'asenha-text with-prefix-suffix wide utilities ' . $field_slug,
				)
			);

			$field_id = 'live_site_url_description';
			$field_slug = 'live-site-url-description';

			add_settings_field(
				$field_id,
				'',
				[ $render_field, 'render_description_subfield' ],
				ASENHA_SLUG,
				'main-section',
				array(
					'option_name'			=> ASENHA_SLUG_U,
					'field_description'		=> __( 'By specifying your live / production site\'s URL above, search engine visibility will be automatically disabled on your development / staging sites. This will prevent accidental indexing by search engines. Make sure you include the HTTP protocol, e.g. https://www.google.com', 'admin-site-enhancements' ),
					'class'					=> 'asenha-description utilities ' . $field_slug,
				)
			);
		}

	}

}