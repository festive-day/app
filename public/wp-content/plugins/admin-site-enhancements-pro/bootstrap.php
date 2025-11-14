<?php

// We're using the singleton design pattern
// https://code.tutsplus.com/articles/design-patterns-in-wordpress-the-singleton-pattern--wp-31621
// https://carlalexander.ca/singletons-in-wordpress/
// https://torquemag.io/2016/11/singletons-wordpress-good-evil/

/**
 * Main class of the plugin used to add functionalities
 *
 * @since 1.0.0
 */
class Admin_Site_Enhancements {

	// Refers to a single instance of this class
	private static $instance = null;

	/**
	 * Creates or returns a single instance of this class
	 *
	 * @return Admin_Site_Enhancements a single instance of this class
	 * @since 1.0.0
	 */
	public static function get_instance() {

		if ( null == self::$instance ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

	/**
	 * Initialize plugin functionalities
	 */
	private function __construct() {
		global $wp_post_types, $pagenow, $typenow;

		// Redirect to settings page on license activation
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			add_action( 'admin_init', 'asenha_goto_settings__premium_only', 1 );
		}
		
		// Setup admin menu, admin page, settings, settings sections, sections fields, admin scripts, plugin action links, etc.
	
		// Register admin menu and add the settings page.
		add_action( 'admin_menu', 'asenha_register_admin_menu' );

		// Register plugin settings

		// Instantiate object for registration of settings section and fields
		$settings = new ASENHA\Classes\Settings_Sections_Fields;

		add_action( 'admin_init', [ $settings, 'register_sections_fields' ] );

		// Suppress all notices on the plugin's main page. Then add notification for successful settings update.
		add_action( 'admin_notices', 'asenha_suppress_add_notices', 5 );
		add_action( 'all_admin_notices', 'asenha_suppress_generic_notices', 5 );

		// Enqueue admin scripts and styles
		add_action( 'admin_enqueue_scripts', 'asenha_admin_scripts' );
		add_action( 'admin_head', 'asenha_admin_menu_organizer_css' );

		// Enqueue public scripts and styles
		add_action( 'wp_enqueue_scripts', 'asenha_public_scripts' );
		
		// Dequeue scripts that prevents settings page from working
		add_action( 'wp_print_scripts', 'asenha_dequeue_scritps', PHP_INT_MAX );
		add_action( 'admin_print_footer_scripts', 'asenha_dequeue_scritps', PHP_INT_MAX );
		add_action( 'admin_enqueue_scripts-tools_page_admin-site-enhancements', 'asenha_dequeue_scritps', PHP_INT_MAX );
		add_action( 'admin_print_scripts-tools_page_admin-site-enhancements', 'asenha_dequeue_scritps', PHP_INT_MAX );

		// Add admin bar inline styles
		add_action( 'admin_head', 'asenha_admin_bar_item_js_css' );
		add_action( 'wp_head', 'asenha_admin_bar_item_js_css' );			

		// Add action links in plugins page
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			add_filter( 'plugin_action_links_' . ASENHA_SLUG . '-pro/' . ASENHA_SLUG . '.php', 'asenha_plugin_action_links' );
		} else {
			add_filter( 'plugin_action_links_' . ASENHA_SLUG . '/' . ASENHA_SLUG . '.php', 'asenha_plugin_action_links' );
		}
		
		// Mark that a user have supported ASE (via AJAX)
		add_action( 'wp_ajax_have_supported', 'asenha_have_supported' );

		// Dismiss upgrade nudge (via AJAX)
		add_action( 'wp_ajax_dismiss_upgrade_nudge', 'asenha_dismiss_upgrade_nudge' );

		// Dismiss promo nudge (via AJAX)
		add_action( 'wp_ajax_dismiss_promo_nudge', 'asenha_dismiss_promo_nudge' );

		// Dismiss support nudge (via AJAX)
		add_action( 'wp_ajax_dismiss_support_nudge', 'asenha_dismiss_support_nudge' );

		if ( function_exists( 'bwasenha_fs' ) ) {
			bwasenha_fs()->add_filter( 'plugin_icon', 'fs_custom_optin_icon__premium_only' );
		}

		// Get all ASE options, default to empty array in case it's not been created yet
		$options = get_option( ASENHA_SLUG_U, array() );

		// Export / Import ASE things
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Export ASE Settings
			add_action( 'admin_init', 'asenha_settings_export__premium_only' );
			// Import ASE Settings
			add_action( 'admin_init', 'asenha_settings_import__premium_only' );

			if ( array_key_exists( 'enable_code_snippets_manager', $options ) && $options['enable_code_snippets_manager'] ) {
				// Export Code Snippets
				add_action( 'admin_init', 'asenha_snippets_export__premium_only' );
				// Import Code Snippets
				add_action( 'admin_init', 'asenha_snippets_import__premium_only' );				
			}
		}
		
		// Add style="display:[something];" to the safe CSS attributes. 
		// Ref: https://github.com/WordPress/wordpress-develop/blob/6.4/src/wp-includes/kses.php#L2329
		// Ref: https://wordpress.stackexchange.com/a/195433
		add_filter( 'safe_style_css', function( $styles ) {
		    $styles[] = 'display';
		    return $styles;
		} );		

		// ===== Activate modules based on settings ===== 

		// =================================================================
		// CONTENT MANAGEMENT
		// =================================================================

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Content Managememnt >> Custom Content Types

        	// wp-admin paths where Custom Content Types modules should not be enabled as it triggers a conflict / JS error
			$cct_disabled_admin_paths = array(
				// WP Grid Builder admin pages, triggers "Uncaught TypeError: Cannot convert undefined or null to object" on /admin/js/app.js
				'/wp-admin/admin.php?page=wp-grid-builder',
			);
			
			if ( array_key_exists( 'custom_content_types', $options ) && $options['custom_content_types'] ) {
				$custom_content_types = new ASENHA\Classes\Custom_Content_Types;
				add_action( 'init', [ $custom_content_types, 'register_asenha_cpts__premium_only' ] );
				add_action( 'add_meta_boxes', [ $custom_content_types, 'add_meta_boxes_for_asenha_cpts__premium_only' ] );
				add_action( 'save_post', [ $custom_content_types, 'save_asenha_cpt_ctax_optionp_fields__premium_only' ], 10, 3 );
				add_filter( 'wp_insert_post_data', [ $custom_content_types, 'use_plural_name_as_cpt_ctax_title__premium_only' ], 99 );
				add_action( 'admin_init', [ $custom_content_types, 'asenha_cpts_flush_rewrite_rules__premium_only' ] );
				add_action( 'init', [ $custom_content_types, 'register_cpts_and_ctaxs_created_by_asenha__premium_only' ] );
				add_action( 'manage_asenha_cpt_posts_columns', [ $custom_content_types, 'cpt_posts_define_columns__premium_only' ] );
				add_action( 'manage_asenha_cpt_posts_custom_column', [ $custom_content_types, 'cpt_posts_custom_columns_content__premium_only' ], 10, 2 );
				add_action( 'manage_asenha_ctax_posts_columns', [ $custom_content_types, 'ctax_posts_define_columns__premium_only' ] );
				add_action( 'manage_asenha_ctax_posts_custom_column', [ $custom_content_types, 'ctax_posts_custom_columns_content__premium_only' ], 10, 2 );
				add_action( 'admin_menu', [ $custom_content_types, 'remove_cpt_ctax_submenus_for_nonadmins__premium_only' ], 999 );
				
				require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup/cfgroup.php';
				require_once ASENHA_PATH . 'includes/premium/custom-content/options-pages/options_pages.php';

				// Maybe enable Oxygen builder integration
				if ( in_array( 'oxygen/functions.php', get_option( 'active_plugins', array() ) ) ) {
					require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/oxygen.php';
				}

				// Maybe enable Bricks builder integration
				$theme = wp_get_theme();
				if ( ( 'Bricks' == $theme->name || 'Bricks' == $theme->parent_theme ) ) {
					add_filter( 'bricks/dynamic_data/register_providers', function( $providers ) {
						require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/bricks.php';
					    $providers[] = 'ase';
					    return $providers;
					} );
				}

				// Maybe enable Breakdance builder integration
				if ( in_array( 'breakdance/plugin.php', get_option( 'active_plugins', array() ) )
					|| function_exists( '\Breakdance\DynamicData\registerField' ) 
					|| class_exists( '\Breakdance\DynamicData\Field' ) 
				) {
					require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/breakdance.php';
				}

				// Maybe enable Elementor integration
				if ( in_array( 'elementor/elementor.php', get_option( 'active_plugins', array() ) ) ) {
					require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor.php';
				}
				
				// Maybe enable repeater field integration with Elementor Pro
				if ( in_array( 'elementor-pro/elementor-pro.php', get_option( 'active_plugins', array() ) ) ) {
					require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/elementor-ase-repeater/elementor-ase-repeater.php';
				}
			}
        }

		// Content Duplication
		if ( array_key_exists( 'enable_duplication', $options ) && $options['enable_duplication'] ) {
	        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				if ( array_key_exists( 'enable_duplication_for', $options ) && isset( $options['enable_duplication_for'] ) ) {
					global $roles_duplication_enabled;
					$for_roles = isset( $options['enable_duplication_for'] ) ? $options['enable_duplication_for'] : array();
					$roles_duplication_enabled = array();

					// Assemble single-dimensional array of roles for which duplication would be enabled
					if ( is_array( $for_roles ) && ( count( $for_roles ) > 0 ) ) {
						foreach( $for_roles as $role_slug => $duplication_enabled ) {
							if ( $duplication_enabled ) {
								$roles_duplication_enabled[] = $role_slug;
							}
						}
					}
				}
			}
			
			$content_duplication = new ASENHA\Classes\Content_Duplication;
			add_action( 'admin_action_duplicate_content', [ $content_duplication, 'duplicate_content' ] );
			add_filter( 'page_row_actions', [ $content_duplication, 'add_duplication_action_link' ], 20, 2 );
			add_filter( 'post_row_actions', [ $content_duplication, 'add_duplication_action_link' ], 20, 2 );
			add_action( 'admin_bar_menu', [ $content_duplication, 'add_admin_bar_duplication_link' ], 100 );
	        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
	        	add_action( 'post_submitbox_misc_actions', [ $content_duplication, 'add_submitbox_duplication_link__premium_only' ] );
	        	add_action( 'admin_head', [ $content_duplication, 'add_gutenberg_duplication_link__premium_only' ] );
	        }
		}

		// Content Order
		if ( array_key_exists( 'content_order', $options ) && $options['content_order'] ) {
			if ( ( array_key_exists( 'content_order_for', $options ) && ! empty( $options['content_order_for'] ) ) 
				|| ( array_key_exists( 'content_order_for_other_post_types', $options ) && ! empty( $options['content_order_for_other_post_types'] ) ) 
			) {
				$content_order = new ASENHA\Classes\Content_Order;
				add_action( 'admin_menu', [ $content_order, 'add_content_order_submenu' ] );
				add_action( 'admin_init', [ $content_order, 'maybe_perform_menu_link_redirects' ] );
				add_action( 'admin_footer', [ $content_order, 'add_additional_elements' ] );
				add_filter( 'admin_enqueue_scripts', [ $content_order, 'add_list_tables_scripts' ] );
				add_action( 'wp_ajax_save_custom_order', [ $content_order, 'save_custom_content_order' ] );
				add_filter( 'pre_get_posts', [ $content_order, 'orderby_menu_order' ], PHP_INT_MAX );
				// TODO: https://developer.wordpress.org/reference/hooks/ajax_query_attachments_args/ (for grid view of media library)
				add_filter( 'save_post', [ $content_order, 'set_menu_order_for_new_posts' ], 10, 3 );
				if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					add_action( 'add_attachment', [ $content_order, 'set_menu_order_for_new_attachments__premium_only'  ] );
                    add_action( 'the_post', [ $content_order, 'apply_custom_order_for_adjacent_posts__premium_only' ], 10, 2 );
				}
			}

		}

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	// Terms Order
			if ( array_key_exists( 'terms_order', $options ) && $options['terms_order'] ) {
				if ( array_key_exists( 'terms_order_for', $options ) && ! empty( $options['terms_order_for'] ) )  {
					require_once ASENHA_PATH . 'includes/premium/tax-terms-order/tax-terms-order.php';			
				}
			}
        }
        
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	// Media Categories
			if ( array_key_exists( 'enable_media_categories', $options ) && $options['enable_media_categories'] ) {
				require_once ASENHA_PATH . 'includes/premium/media-categories/media-categories-module.php';
				$media_categories_extras = new ASENHA\Classes\Media_Categories_Extras;
				add_action( 'admin_footer-upload.php', [ $media_categories_extras, 'add_bulk_actions' ] );
				// https://developer.wordpress.org/reference/hooks/load-page_hook/
				add_action( 'load-upload.php', [ $media_categories_extras, 'handle_bulk_action' ] );
			}
        }

		// Media Replacement
		if ( array_key_exists( 'enable_media_replacement', $options ) && $options['enable_media_replacement'] ) {
			$media_replacement = new ASENHA\Classes\Media_Replacement;
			$disable_media_replacement_cache_busting = isset( $options['disable_media_replacement_cache_busting'] ) ? $options['disable_media_replacement_cache_busting'] : false;
			add_filter( 'media_row_actions', [ $media_replacement, 'modify_media_list_table_edit_link' ], 10, 2 );
			add_filter( 'attachment_fields_to_edit', [ $media_replacement, 'add_media_replacement_button' ], 10, 2 );
			add_action( 'edit_attachment', [ $media_replacement, 'replace_media' ] );
			add_filter( 'post_updated_messages', [ $media_replacement, 'attachment_updated_custom_message' ] );
			// Mayve bust browser cache of old/replaced images by appending a time stamp URL parameter
			if ( ! $disable_media_replacement_cache_busting ) {
				add_filter('wp_calculate_image_srcset', [ $media_replacement, 'append_cache_busting_param_to_image_srcset' ], 10, 5 );
				add_filter('wp_get_attachment_image_src', [ $media_replacement, 'append_cache_busting_param_to_attachment_image_src' ], 10, 2 );
				add_filter('wp_prepare_attachment_for_js', [ $media_replacement, 'append_cache_busting_param_to_attachment_for_js' ], 10, 2 );
				add_filter('wp_get_attachment_url', [ $media_replacement, 'append_cache_busting_param_to_attachment_url' ], 20, 2 );
			}
		}

		// Media Library Infinite Scrolling
		if ( array_key_exists( 'media_library_infinite_scrolling', $options ) && $options['media_library_infinite_scrolling'] ) {
			add_filter( 'media_library_infinite_scrolling', '__return_true' );
		}

		// SVG Upload
		if ( array_key_exists( 'enable_svg_upload', $options ) && $options['enable_svg_upload'] && array_key_exists( 'enable_svg_upload_for', $options ) && isset( $options['enable_svg_upload_for'] ) ) {

			global $roles_svg_upload_enabled;

			$enable_svg_upload = $options['enable_svg_upload'];
			$for_roles = $options['enable_svg_upload_for'];

			// User has role(s). Do further checks.
			if ( isset( $for_roles ) && ( count( $for_roles ) > 0 ) ) {

				// Assemble single-dimensional array of roles for which SVG upload would be enabled
				$roles_svg_upload_enabled = array();
				foreach( $for_roles as $role_slug => $svg_upload_enabled ) {
					if ( $svg_upload_enabled ) {
						$roles_svg_upload_enabled[] = $role_slug;
					}
				}

			}
			
			$svg_upload = new ASENHA\Classes\SVG_Upload;
			add_filter( 'upload_mimes', [ $svg_upload, 'add_svg_mime' ] );
			add_filter( 'wp_check_filetype_and_ext', [ $svg_upload, 'confirm_file_type_is_svg' ], 10, 4 );
			add_filter( 'wp_handle_sideload_prefilter', [ $svg_upload, 'sanitize_and_maybe_allow_svg_upload' ] );
			add_filter( 'wp_handle_upload_prefilter', [ $svg_upload, 'sanitize_and_maybe_allow_svg_upload' ] );
			add_filter( 'xmlrpc_prepare_media_item', [ $svg_upload, 'sanitize_xmlrpc_svg_upload' ], 10, 2 );
			add_action( 'rest_insert_attachment', [ $svg_upload, 'sanitize_after_upload' ], 10, 3 );
			add_filter( 'wp_generate_attachment_metadata', [ $svg_upload, 'generate_svg_metadata' ], 10, 3 );
			add_filter( 'wp_calculate_image_srcset', [ $svg_upload, 'disable_svg_srcset' ] );
			if ( ! in_array( 'auto-sizes/auto-sizes.php', get_option( 'active_plugins', array() ) ) ) {
				// Only add this filter when https://wordpress.org/plugins/auto-sizes/ is not active to prevent PHP deprecation notice
				add_filter( 'wp_calculate_image_sizes', [ $svg_upload, 'remove_svg_responsive_image_attr' ], 10, 3 );				
			}
			add_action( 'wp_ajax_svg_get_attachment_url', [ $svg_upload, 'get_svg_attachment_url' ] );
			add_filter( 'wp_prepare_attachment_for_js', [ $svg_upload, 'get_svg_url_in_media_library' ] );
		}

		// AVIF Upload
		if ( array_key_exists( 'enable_avif_upload', $options ) && $options['enable_avif_upload'] ) {
			$avif_upload = new ASENHA\Classes\AVIF_Upload;
			add_filter( 'mime_types', [ $avif_upload, 'add_avif_mime_type' ] );
			add_filter( 'upload_mimes', [ $avif_upload, 'allow_avif_mime_type_upload' ] );
			add_filter( 'getimagesize_mimes_to_exts', [ $avif_upload, 'add_avif_mime_type_to_exts' ] );
			add_filter( 'wp_generate_attachment_metadata', [ $avif_upload, 'add_avif_image_dimension' ], 10, 3 );
			add_filter( 'file_is_displayable_image', [ $avif_upload, 'make_avif_displayable' ], 10, 2 );
			add_filter( 'wp_check_filetype_and_ext', [ $avif_upload, 'handle_exif_and_fileinfo_fail' ], 10, 5 );
		}

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Public Preview for Drafts
			if ( array_key_exists( 'public_preview_for_drafts', $options ) && $options['public_preview_for_drafts'] ) {
				if ( array_key_exists( 'public_drafts_preview_for', $options ) && ! empty( $options['public_drafts_preview_for'] ) )  {
					$public_preview_for_drafts = new ASENHA\Classes\Public_Preview_For_Drafts;
					if ( ! is_admin() ) {
						add_filter( 'query_vars', [ $public_preview_for_drafts, 'add_query_var' ] );
						add_action( 'pre_get_posts', [ $public_preview_for_drafts, 'show_public_preview' ] );
					} else {
						add_filter( 'page_row_actions', [ $public_preview_for_drafts, 'add__action_row_public_preview_link' ], 10, 2 );
						add_filter( 'post_row_actions', [ $public_preview_for_drafts, 'add__action_row_public_preview_link' ], 10, 2 );
			        	add_action( 'post_submitbox_misc_actions', [ $public_preview_for_drafts, 'add_submitbox_public_preview_button' ] );
			        	add_action( 'admin_head', [ $public_preview_for_drafts, 'add_gutenberg_public_preview_button' ] );
					}
				}

			}
        }
		
		// External Permalinks
		if ( array_key_exists( 'enable_external_permalinks', $options ) && $options['enable_external_permalinks'] ) {
			if ( array_key_exists( 'enable_external_permalinks_for', $options ) && ! empty( $options['enable_external_permalinks_for'] ) )  {
				$external_permalinks = new ASENHA\Classes\External_Permalinks;
				add_action( 'add_meta_boxes', [ $external_permalinks, 'add_external_permalink_meta_box' ], 10, 2 );
				add_action( 'save_post', [ $external_permalinks, 'save_external_permalink' ] );

				// Filter the permalink for use by get_permalink()
				add_filter( 'page_link', [ $external_permalinks, 'use_external_permalink_for_pages' ], 20, 2 );
				add_filter( 'post_link', [ $external_permalinks, 'use_external_permalink_for_posts' ], 20, 2 );
				add_filter( 'post_type_link', [ $external_permalinks, 'use_external_permalink_for_posts' ], 20, 2 );

				// Enable redirection to external permalink when page/post is opened directly via it's WP permalink
				add_action( 'wp', [ $external_permalinks, 'redirect_to_external_permalink' ] );
			}
		}

		// Open All External Links in New Tab
		if ( array_key_exists( 'external_links_new_tab', $options ) && $options['external_links_new_tab'] ) {
			$open_external_links_in_new_tab = new ASENHA\Classes\Open_External_Links_In_New_Tab;
			add_filter( 'the_content', [ $open_external_links_in_new_tab,'add_target_and_rel_atts_to_content_links'] );
			if ( in_array( 'elementor/elementor.php', get_option( 'active_plugins', array() ) ) ) {
				add_filter( 'elementor/frontend/the_content', [ $open_external_links_in_new_tab,'add_target_and_rel_atts_to_content_links'] );
			}
		}

		// Allow Custom Menu Links to Open in New Tab
		if ( array_key_exists( 'custom_nav_menu_items_new_tab', $options ) && $options['custom_nav_menu_items_new_tab'] ) {
			$custom_nav_menu_items_in_new_tab = new ASENHA\Classes\Custom_Nav_Menu_Items_In_New_Tab;
			add_filter( 'wp_nav_menu_item_custom_fields', [ $custom_nav_menu_items_in_new_tab,'add_custom_nav_menu_open_in_new_tab_field'], 10, 4 );
			add_action( 'wp_update_nav_menu_item', [ $custom_nav_menu_items_in_new_tab, 'save_custom_nav_menu_open_in_new_tab_status'], 10, 3 );
			add_action( 'nav_menu_link_attributes', [ $custom_nav_menu_items_in_new_tab, 'add_attributes_to_custom_nav_menu_item' ], 10, 3 );
		}

		// Auto-Publishing of Posts with Missed Schedules
		if ( array_key_exists( 'enable_missed_schedule_posts_auto_publish', $options ) && $options['enable_missed_schedule_posts_auto_publish'] ) {
			$auto_publish_posts_with_missed_schedule = new ASENHA\Classes\Auto_Publish_Posts_With_Missed_Schedule;
			add_action( 'wp_head', [ $auto_publish_posts_with_missed_schedule, 'publish_missed_schedule_posts' ] );
			add_action( 'admin_head', [ $auto_publish_posts_with_missed_schedule, 'publish_missed_schedule_posts' ] );
		}

		// =================================================================
		// ADMIN INTERFACE
		// =================================================================

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Admin Logo
			if ( array_key_exists( 'admin_logo', $options ) && $options['admin_logo'] ) {
				if ( array_key_exists( 'admin_logo_image', $options ) && ! empty( $options['admin_logo_image'] ) ) {
					if ( array_key_exists( 'admin_logo_location', $options ) ) {
						$admin_logo = new ASENHA\Classes\Admin_Logo;
						switch ( $options['admin_logo_location'] ) {
							case 'admin_bar':
						        add_action( 'admin_bar_menu', [ $admin_logo, 'add_admin_bar_logo' ] );
						        add_action( 'admin_head', [ $admin_logo, 'add_admin_bar_logo_css' ] );
						        add_action( 'wp_head', [ $admin_logo, 'add_admin_bar_logo_css' ] );
								break;

							case 'admin_menu':
						        add_action( 'admin_head', [ $admin_logo, 'add_admin_menu_logo' ] );
								break;

							case 'admin_bar_and_menu':
								if ( is_admin() ) {
							        add_action( 'admin_head', [ $admin_logo, 'add_admin_menu_logo' ] );								
								} else {
							        add_action( 'admin_bar_menu', [ $admin_logo, 'add_admin_bar_logo' ] );
							        add_action( 'admin_head', [ $admin_logo, 'add_admin_bar_logo_css' ] );
							        add_action( 'wp_head', [ $admin_logo, 'add_admin_bar_logo_css' ] );									
								}
								break;
						}
					}
				}
			}
		}

		// Hide or Modify Elements / Clean Up Admin Bar
		if ( array_key_exists( 'hide_modify_elements', $options ) && $options['hide_modify_elements'] ) {
			$cleanup_admin_bar = new ASENHA\Classes\Cleanup_Admin_Bar;
			// Priority 5 to execute earlier than the normal 10. This is for removing default items.
			add_filter( 'admin_bar_menu', [ $cleanup_admin_bar, 'modify_admin_bar_menu' ], 5 );
			add_filter( 'admin_bar_menu', [ $cleanup_admin_bar, 'remove_howdy' ], PHP_INT_MAX - 100 );
			if ( array_key_exists( 'hide_help_drawer', $options ) && $options['hide_help_drawer'] ) {
				add_action( 'admin_head', [ $cleanup_admin_bar, 'hide_help_drawer' ] );
			}
	        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
	        	// Capture extra admin bar nodes, e.g. from plugins
				add_action( 'wp_before_admin_bar_render', [ $cleanup_admin_bar, 'capture_extra_admin_bar_nodes__premium_only' ], 99999 );
				// Remove admin bar nodes from plugins
				add_filter( 'wp_before_admin_bar_render', [ $cleanup_admin_bar, 'remove_extra_admin_bar_nodes__premium_only' ], PHP_INT_MAX );
			}
		}

		// Hide Admin Notices
		if ( array_key_exists( 'hide_admin_notices', $options ) && $options['hide_admin_notices'] ) {
			$hide_admin_notices = new ASENHA\Classes\Hide_Admin_Notices;
			add_action( 'admin_footer', [ $hide_admin_notices, 'admin_notices_wrapper' ], 9 );
			// add_action( 'all_admin_notices', [ $hide_admin_notices, 'admin_notices_wrapper' ] );
			// add_action( 'admin_notices', [ $hide_admin_notices, 'show_test_admin_notice_for_all_user_roles' ] );
			add_action( 'admin_bar_menu', [ $hide_admin_notices, 'admin_notices_menu' ] );
			add_action( 'admin_print_styles', [ $hide_admin_notices, 'admin_notices_menu_inline_css' ] );
		}

		// Disable Dashboard Widgets
		if ( array_key_exists( 'disable_dashboard_widgets', $options ) && $options['disable_dashboard_widgets'] ) {
			$disable_dashboard_widgets = new ASENHA\Classes\Disable_Dashboard_Widgets;
			add_action( 'wp_dashboard_setup', [ $disable_dashboard_widgets, 'disable_dashboard_widgets' ], PHP_INT_MAX );
			add_action( 'admin_init', [ $disable_dashboard_widgets, 'maybe_remove_welcome_panel' ] );
		}

		// Hide Admin Bar
		if ( array_key_exists( 'hide_admin_bar', $options ) && $options['hide_admin_bar'] ) {
			$hide_admin_bar = new ASENHA\Classes\Hide_Admin_Bar;
		}
		// On the frontend
		if ( array_key_exists( 'hide_admin_bar', $options ) && $options['hide_admin_bar'] && array_key_exists( 'hide_admin_bar_for', $options ) && isset( $options['hide_admin_bar_for'] ) ) {
			add_filter( 'show_admin_bar', [ $hide_admin_bar, 'hide_admin_bar_for_roles_on_frontend' ] );
		}
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// On the backend
			if ( array_key_exists( 'hide_admin_bar', $options ) && $options['hide_admin_bar'] && array_key_exists( 'hide_admin_bar_on_backend_for', $options ) && isset( $options['hide_admin_bar_on_backend_for'] ) ) {
				add_action( 'admin_print_scripts', [ $hide_admin_bar, 'hide_admin_bar_for_roles_on_backend__premium_only' ] );
			}			
		}

		// Wider Admin Menu
		if ( array_key_exists( 'wider_admin_menu', $options ) && $options['wider_admin_menu'] ) {
			$wider_admin_menu = new ASENHA\Classes\Wider_Admin_Menu;
			add_action( 'admin_head', [ $wider_admin_menu, 'set_custom_menu_width' ], 99 );
		}

		// Admin Menu Organizer
		if ( array_key_exists( 'customize_admin_menu', $options ) && $options['customize_admin_menu'] ) {
	        $options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
	        $admin_menu_options = isset( $options_extra['admin_menu'] ) ? $options_extra['admin_menu'] : array();

			$admin_menu_organizer = new ASENHA\Classes\Admin_Menu_Organizer;
			add_action( 'admin_menu', [ $admin_menu_organizer, 'add_menu_item' ] );
			// add_action( 'wp_ajax_save_custom_menu_order', [ $admin_menu_organizer, 'save_custom_menu_order' ] );
			// add_action( 'wp_ajax_save_hidden_menu_items', [ $admin_menu_organizer, 'save_hidden_menu_items' ] );
			if ( array_key_exists( 'custom_menu_order', $admin_menu_options ) ) {
				add_filter( 'custom_menu_order', '__return_true', PHP_INT_MAX );
				add_filter( 'menu_order', [ $admin_menu_organizer, 'render_custom_menu_order' ], PHP_INT_MAX );
			}
			
			if ( array_key_exists( 'custom_menu_titles', $admin_menu_options ) ) {
				add_action( 'admin_menu', [ $admin_menu_organizer, 'apply_custom_menu_item_titles' ], 9999999995 );
                add_action( 'init', [ $admin_menu_organizer, 'apply_custom_title_for_posts_menu' ] );
			}
			
			if ( array_key_exists( 'custom_menu_hidden', $admin_menu_options ) 
				|| array_key_exists( 'custom_menu_always_hidden', $admin_menu_options ) 
			) {
				add_action( 'admin_menu', [ $admin_menu_organizer, 'hide_menu_items' ], 9999999996 );
				add_action( 'admin_menu', [ $admin_menu_organizer, 'add_hidden_menu_toggle' ], 9999999997 );
				add_action( 'admin_enqueue_scripts', [ $admin_menu_organizer, 'enqueue_toggle_hidden_menu_script' ] );
		        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					add_action( 'plugins_loaded', [ $admin_menu_organizer, 'handle_plugins_custom_capabilities__premium_only' ] );
		        	add_action( 'current_screen', [ $admin_menu_organizer, 'maybe_restrict_access_to_current_screen__premium_only' ] );
		        }
			}

			add_action( 'wp_ajax_save_admin_menu', [ $admin_menu_organizer, 'save_admin_menu' ] );
			
	        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
	        	if ( array_key_exists( 'custom_submenu_always_hidden', $admin_menu_options ) ) {
	        		add_action( 'admin_menu', [ $admin_menu_organizer, 'hide_submenu_items__premium_only' ], 9999999995 );
	        	}
	        	
	        	if ( array_key_exists( 'custom_menu_new_separators', $admin_menu_options ) ) {
	        		add_action( 'admin_menu', [ $admin_menu_organizer, 'add_new_separators__premium_only' ], 9999999995 );
	        	}
				add_action( 'wp_ajax_reset_admin_menu', [ $admin_menu_organizer, 'reset_admin_menu__premium_only' ] );
			}
		}

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Admin Columns Manager
			if ( array_key_exists( 'admin_columns_manager', $options ) && $options['admin_columns_manager'] ) {
				$admin_columns_manager = new ASENHA\Classes\Admin_Columns_Manager;
				add_action( 'admin_menu', [ $admin_columns_manager, 'add_admin_columns_organizer_menu__premium_only' ] );
				// add_action( 'admin_init', [ $admin_columns_manager, 'register_admin_columns_sections_fields__premium_only' ] );

				add_action( 'admin_footer', [ $admin_columns_manager, 'add_additional_elements__premium_only' ] );
				add_filter( 'admin_enqueue_scripts', [ $admin_columns_manager, 'add_list_tables_scripts__premium_only' ] );

				// add_action( 'wp_loaded', [ $admin_columns_manager, 'render_columns__premium_only' ] );
				add_action( 'admin_init', [ $admin_columns_manager, 'render_columns__premium_only' ] );
				add_filter( 'post_date_column_time', [ $admin_columns_manager, 'filter_post_date_column_time__premium_only' ], 10, 4 );
				add_action( 'pre_get_posts', [ $admin_columns_manager, 'apply_column_sort__premium_only' ] );
				add_action( 'current_screen', [ $admin_columns_manager, 'maybe_redirect_to_default_sort_premium_only' ] );
				add_action( 'wp_ajax_save_columns_order', [ $admin_columns_manager, 'save_columns_order__premium_only' ] );
				add_action( 'wp_ajax_reset_columns', [ $admin_columns_manager, 'reset_columns__premium_only' ] );
				add_action( 'wp_ajax_reindex_custom_fields', [ $admin_columns_manager, 'reindex_custom_fields__premium_only' ] );

				// For implementing custom styles to list tables
				add_filter( 'admin_body_class', [ $admin_columns_manager, 'admin_columns_body_class__premium_only'], PHP_INT_MAX );
				// add_filter( 'admin_enqueue_scripts', [ $admin_columns_manager, 'admin_columns_css__premium_only' ] );
				add_filter( 'admin_print_footer_scripts', [ $admin_columns_manager, 'admin_columns_css__premium_only' ], PHP_INT_MAX );
				
				// Reload list table after performing quick edit on post types with custom admin columns
				// Notice the use of high priority of 0
				add_action( 'wp_ajax_inline-save', [ $admin_columns_manager, 'wp_ajax_inline_save_with_page_reload' ], 0 );
			}
        }

		// Show Custom Taxonomy Filters
		if ( array_key_exists( 'show_custom_taxonomy_filters', $options ) && $options['show_custom_taxonomy_filters'] ) {
			$show_custom_taxonomy_filters = new ASENHA\Classes\Show_Custom_Taxonomy_Filters;
			add_action( 'restrict_manage_posts', [ $show_custom_taxonomy_filters, 'show_custom_taxonomy_filters' ] );
			if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				if ( array_key_exists( 'show_custom_taxonomy_filters_non_hierarchical', $options ) && $options['show_custom_taxonomy_filters_non_hierarchical'] ) {
					add_action( 'posts_where', [ $show_custom_taxonomy_filters, 'filter_where_clause__premium_only' ], 10, 2 );
				}
			}
		}

		// Enhance List Tables
		if ( array_key_exists( 'enhance_list_tables', $options ) && $options['enhance_list_tables'] ) {
			$enhance_list_tables = new ASENHA\Classes\Enhance_List_Tables;

			// Show Featured Image Column
			if ( array_key_exists( 'show_featured_image_column', $options ) && $options['show_featured_image_column'] ) {
				add_action( 'admin_init', [ $enhance_list_tables, 'show_featured_image_column' ] );
			}

			// Show Excerpt Column
			if ( array_key_exists( 'show_excerpt_column', $options ) && $options['show_excerpt_column'] ) {
				add_action( 'admin_init', [ $enhance_list_tables, 'show_excerpt_column' ] );
			}

			// Show Last Modified Column
			if ( array_key_exists( 'show_last_modified_column', $options ) && $options['show_last_modified_column'] ) {
				add_action( 'admin_init', [ $enhance_list_tables, 'show_last_modified_column' ] );
			}

			// Show ID Column
			if ( array_key_exists( 'show_id_column', $options ) && $options['show_id_column'] ) {
				add_action( 'admin_init', [ $enhance_list_tables, 'show_id_column' ] );
			}

			// Show File Size Column in Media Library
			if ( array_key_exists( 'show_file_size_column', $options ) && $options['show_file_size_column'] ) {
				add_filter('manage_upload_columns', [ $enhance_list_tables, 'add_column_file_size' ] );
				add_action('manage_media_custom_column', [ $enhance_list_tables, 'display_file_size' ], 10, 2 );
				if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					// add_filter('manage_upload_sortable_columns', [ $enhance_list_tables, 'make_file_size_columns_sortable__premium_only' ] );
					// add_action('pre_get_posts', [ $enhance_list_tables, 'sort_media_by_file_size__premium_only' ] );					
				}
				add_action('admin_head', [ $enhance_list_tables, 'add_media_styles' ] );
			}

			// Show ID in Action Row
			if ( array_key_exists( 'show_id_in_action_row', $options ) && $options['show_id_in_action_row'] ) {
				add_action( 'admin_init', [ $enhance_list_tables, 'show_id_in_action_row' ] );
			}

			// Hide Date Column
			if ( array_key_exists( 'hide_date_column', $options ) && $options['hide_date_column'] ) {
				add_action( 'admin_init', [ $enhance_list_tables, 'hide_date_column' ] );
			}

			// Hide Comments Column
			if ( array_key_exists( 'hide_comments_column', $options ) && $options['hide_comments_column'] ) {
				add_action( 'admin_init', [ $enhance_list_tables, 'hide_comments_column' ] );
			}

			// Hide Post Tags Column
			if ( array_key_exists( 'hide_post_tags_column', $options ) && $options['hide_post_tags_column'] ) {
				add_action( 'admin_init', [ $enhance_list_tables, 'hide_post_tags_column' ] );
			}

		}
		
		// Various Admin UI Enhancements
		if ( array_key_exists( 'various_admin_ui_enhancements', $options ) && $options['various_admin_ui_enhancements'] ) {
			$various_admin_ui_enhancements = new ASENHA\Classes\Various_Admin_Ui_Enhancements;

			// Display Active Plugins First
			if ( array_key_exists( 'display_active_plugins_first', $options ) && $options['display_active_plugins_first'] ) {
				add_action( 'admin_head-plugins.php', [ $various_admin_ui_enhancements, 'show_active_plugins_first' ] );
			}

	        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
	        	// Preserve Taxonomy Hierarchy
				if ( array_key_exists( 'preserve_taxonomy_hierarchy', $options ) && $options['preserve_taxonomy_hierarchy'] ) {
					add_action( 'admin_init', [ $various_admin_ui_enhancements, 'preserve_taxonomy_hierarchy__premium_only' ] );
				}

	        	// Enable Dashboard Columns Settings
				if ( array_key_exists( 'enable_dashboard_columns_settings', $options ) && $options['enable_dashboard_columns_settings'] ) {
					add_action( 'load-index.php', [ $various_admin_ui_enhancements, 'enable_dashboard_columns_settings__premium_only' ] );
				}

	        	// Add User Roles to Admin Body Classes
				if ( array_key_exists( 'add_user_roles_to_admin_body_classes', $options ) && $options['add_user_roles_to_admin_body_classes'] ) {
					add_filter( 'admin_body_class', [ $various_admin_ui_enhancements, 'add_user_roles_to_admin_body_classes__premium_only' ] );
				}

	        	// Add Username to Admin Body Classes
				if ( array_key_exists( 'add_username_to_admin_body_classes', $options ) && $options['add_username_to_admin_body_classes'] ) {
					add_filter( 'admin_body_class', [ $various_admin_ui_enhancements, 'add_username_to_admin_body_classes__premium_only' ] );
				}

	        	// Open Admin Links in New Tab
				if ( array_key_exists( 'open_admin_links_in_new_tab', $options ) && $options['open_admin_links_in_new_tab'] ) {
					add_action( 'admin_head', [ $various_admin_ui_enhancements, 'script_to_open_admin_links_in_new_tab__premium_only' ] );
				}
	        }
		}
		
		// Custom Admin Footer Text
		if ( array_key_exists( 'custom_admin_footer_text', $options ) && $options['custom_admin_footer_text'] ) {
			$custom_admin_footer_text = new ASENHA\Classes\Custom_Admin_Footer_Text;
			
			// Update footer text
			if ( is_asenha() ) {
				add_filter( 'admin_footer_text', 'asenha_footer_text', 20 );
			} else {
				add_filter( 'admin_footer_text', [ $custom_admin_footer_text, 'custom_admin_footer_text_left' ], 20 );				
			}
			
			// Update footer version text
			if ( is_asenha() ) {
				add_filter( 'update_footer', 'asenha_footer_version_text', 20 );
			} else {
				add_filter( 'update_footer', [ $custom_admin_footer_text, 'custom_admin_footer_text_right' ], 20 );				
			}
		} else {
			// Update footer text
			if ( is_asenha() ) {
				add_filter( 'admin_footer_text', 'asenha_footer_text', 20 );
			}
			
			// Update footer version text
			if ( is_asenha() ) {
				add_filter( 'update_footer', 'asenha_footer_version_text', 20 );
			}
		}

		// =================================================================
		// LOG IN | LOG OUT
		// =================================================================

		// Change Login URL
		if ( array_key_exists( 'change_login_url', $options ) && $options['change_login_url'] ) {
			if ( array_key_exists( 'custom_login_slug', $options ) && ! empty( $options['custom_login_slug'] ) )  {
				$change_login_url = new ASENHA\Classes\Change_Login_URL;
				add_action( 'init', [ $change_login_url, 'redirect_on_custom_login_url' ] );
				if ( in_array( 'gravityforms/gravityforms.php', get_option( 'active_plugins', array() ) ) ) {
					// Load earlier than Gravity Forms process_exterior_pages()
					add_action( 'wp', [ $change_login_url, 'prevent_redirect_to_custom_login_url' ], 0 );
				}
				add_filter( 'login_url', [ $change_login_url, 'customize_login_url' ], 10, 3 );
				add_filter( 'lostpassword_url', [ $change_login_url, 'customize_lost_password_url' ] );
				add_filter( 'register_url', [ $change_login_url, 'customize_register_url' ] );
				add_action( 'wp_loaded', [ $change_login_url, 'redirect_on_default_login_urls' ] );
				add_action( 'wp_login_failed', [ $change_login_url, 'redirect_to_custom_login_url_on_login_fail' ] );
				add_filter( 'login_message', [ $change_login_url, 'add_failed_login_message' ] );
				// No need to modify logout_url or perform redirect on logout
				// The customized login URL is already being returned after logout
				// add_action( 'wp_logout', [ $change_login_url, 'redirect_to_custom_login_url_on_logout_success' ] );
				// add_filter( 'logout_url', [ $change_login_url, 'customize_logout_url' ], 10, 2 );
			}
		}

		// Login ID Type
		if ( array_key_exists( 'login_id_type_restriction', $options ) && $options['login_id_type_restriction'] ) {
			if ( array_key_exists( 'login_id_type', $options ) && ! empty( $options['login_id_type'] ) )  {
				$login_id_type = new ASENHA\Classes\Login_ID_Type;
				switch ( $options['login_id_type'] ) {
					case 'username':
						remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );
						add_filter( 'login_form_defaults', [ $login_id_type, 'change_login_form_defaults' ] );
						add_filter( 'gettext', [ $login_id_type, 'gettext_login_id_username' ], 20, 3 );
						break;

					case 'email';
						add_filter( 'authenticate', [ $login_id_type, 'authenticate_email' ], 20, 2 );
						add_filter( 'gettext', [ $login_id_type, 'gettext_login_id_email' ], 20, 3 );
						break;
				}
			}
		}

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Login Page Customizer
			if ( array_key_exists( 'login_page_customizer', $options ) && $options['login_page_customizer'] ) {
				$login_page_customizer = new ASENHA\Classes\Login_Page_Customizer;
				add_action( 'login_enqueue_scripts', [ $login_page_customizer, 'enqueue_jquery' ] );
				add_action( 'login_head', [ $login_page_customizer, 'add_custom_style' ], PHP_INT_MAX );
				$site_identity_on_login_page = new ASENHA\Classes\Site_Identity_On_Login_Page;
				add_filter( 'login_headerurl', [ $site_identity_on_login_page, 'use_site_url_on_login' ] );
				// add_action( 'plugins_loaded', [ $login_page_customizer, 'maybe_disable_registration' ] );
				if ( array_key_exists( 'login_page_hide_language_switcher', $options ) && $options['login_page_hide_language_switcher'] ) {
					add_filter( 'login_display_language_dropdown', '__return_false' );
				}
			}			
		}

		if ( ! bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Use Site Identity on the Login Page
			if ( array_key_exists( 'site_identity_on_login', $options ) && $options['site_identity_on_login'] ) {
				$site_identity_on_login_page = new ASENHA\Classes\Site_Identity_On_Login_Page;
				add_action( 'login_head', [ $site_identity_on_login_page, 'use_site_icon_on_login' ] );
				add_filter( 'login_headerurl', [ $site_identity_on_login_page, 'use_site_url_on_login' ] );
			}			
		}

		// Login Logout Menu

		if ( array_key_exists( 'enable_login_logout_menu', $options ) && $options['enable_login_logout_menu'] ) {
			$login_logout_menu = new ASENHA\Classes\Login_Logout_Menu;
			add_action( 'admin_head-nav-menus.php', [ $login_logout_menu, 'add_login_logout_metabox' ] );
			add_filter( 'wp_setup_nav_menu_item', [ $login_logout_menu, 'set_login_logout_menu_item_dynamic_url' ] );
			add_filter( 'wp_nav_menu_objects', [ $login_logout_menu, 'maybe_remove_login_or_logout_menu_item' ] );
		}

		// Last Login Column

		if ( array_key_exists( 'enable_last_login_column', $options ) && $options['enable_last_login_column'] ) {
			$last_login_column = new ASENHA\Classes\Last_Login_Column;
			add_action( 'wp_login', [ $last_login_column, 'log_login_datetime' ], 3, 1 ); // Earlier than Redirect After Login
			add_filter( 'manage_users_columns', [ $last_login_column, 'add_last_login_column' ] );
			add_filter( 'manage_users_custom_column', [ $last_login_column, 'show_last_login_info' ], 10, 3 );
			add_action( 'admin_print_styles-users.php', [ $last_login_column, 'add_column_style' ] );
			if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				add_filter( 'manage_users_sortable_columns', [ $last_login_column, 'make_last_login_column_sortable__premium_only' ] );
				if ( is_admin() ) {
					add_action( 'pre_get_users', [ $last_login_column, 'apply_last_login_column_sort__premium_only' ] );
				}
			}
		}

		// Registration Date Column

		if ( array_key_exists( 'registration_date_column', $options ) && $options['registration_date_column'] ) {
			$registration_date_column = new ASENHA\Classes\Registration_Date_Column;
			add_filter( 'manage_users_columns', [ $registration_date_column, 'add_registration_date_column' ] );
			add_filter( 'manage_users_custom_column', [ $registration_date_column, 'display_registration_date' ], 10, 3 );
			if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				add_filter( 'manage_users_sortable_columns', [ $registration_date_column, 'make_registration_date_column_sortable__premium_only' ] );
			}
		}

		// Redirect After Login

		if ( array_key_exists( 'redirect_after_login', $options ) && $options['redirect_after_login'] ) {
			if ( array_key_exists( 'redirect_after_login_for', $options ) && ! empty( $options['redirect_after_login_for'] ) )  {
				$redirect_after_login = new ASENHA\Classes\Redirect_After_Login;
				add_filter( 'wp_login', [ $redirect_after_login, 'redirect_after_login' ], 5, 2 );
			}
		}

		// Redirect After Logout

		if ( array_key_exists( 'redirect_after_logout', $options ) && $options['redirect_after_logout'] ) {
			if ( array_key_exists( 'redirect_after_logout_for', $options ) && ! empty( $options['redirect_after_logout_for'] ) )  {
				$redirect_after_logout = new ASENHA\Classes\Redirect_After_Logout;
				add_action( 'wp_logout', [ $redirect_after_logout, 'redirect_after_logout' ], 5, 1 ); // load earlier than Change Login URL add_action
				// add_filter( 'logout_redirect', [ $redirect_after_logout, 'apply_custom_logout_redirect' ], PHP_INT_MAX, 3 );
				// add_filter( 'logout_url', [ $redirect_after_logout, 'add_redirect_to_in_logout_url' ], PHP_INT_MAX, 2 );
			}
		}

		// =================================================================
		// CUSTOM CODE
		// =================================================================

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Code Snippets Manager

			if ( array_key_exists( 'enable_code_snippets_manager', $options ) && $options['enable_code_snippets_manager'] ) {
				require_once ASENHA_PATH . 'vendor/scssphp/scssphp/scss.inc.php';
				require_once ASENHA_PATH . 'includes/premium/code-snippets-manager/code-snippets-manager.php';
				$code_snippets_manager = new ASENHA\Classes\Code_Snippets_Manager;
				add_action( 'pre_post_update', [ $code_snippets_manager, 'record_last_edited_php_snippets__premium_only' ], 10, 2 );
				add_action( 'plugins_loaded', [ $code_snippets_manager, 'maybe_show_safe_mode_admin_bar_icon__premium_only' ] );
				// Not on login URL or, on login URL but is not logging out.
				// Uses source code for is_login() introduced in WP 6.2.
				if ( false === strpos( wp_login_url(), $_SERVER['SCRIPT_NAME'] )
					|| ( false !== strpos( wp_login_url(), $_SERVER['SCRIPT_NAME'] ) && 
						( ! isset( $_GET['action'] ) || 'logout' != sanitize_text_field( $_GET['action'] ) )
						) 
				) {
					add_filter( 'wp_die_handler', [ $code_snippets_manager, 'custom_wp_die_handler__premium_only' ] );					
				}
			}
        }

		// Enable Custom Admin / Frontend CSS
		if ( array_key_exists( 'enable_custom_admin_css', $options ) && $options['enable_custom_admin_css'] 
			|| ( array_key_exists( 'enable_custom_frontend_css', $options ) && $options['enable_custom_frontend_css'] )
		) {
			$custom_css = new ASENHA\Classes\Custom_Css;
		}

		if ( array_key_exists( 'enable_custom_admin_css', $options ) && $options['enable_custom_admin_css'] ) {
			// add_filter( 'admin_enqueue_scripts', [ $custom_css, 'custom_admin_css' ] );
			add_filter( 'admin_print_footer_scripts', [ $custom_css, 'custom_admin_css' ] );
		}

		if ( array_key_exists( 'enable_custom_frontend_css', $options ) && $options['enable_custom_frontend_css'] ) {
	        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				if ( array_key_exists( 'custom_frontend_css_priority', $options ) ) {
					add_filter( 'wp_head', [ $custom_css, 'custom_frontend_css' ], $options['custom_frontend_css_priority'] );
				} else {
					add_filter( 'wp_head', [ $custom_css, 'custom_frontend_css' ] );				
				}
	        } else {
				add_filter( 'wp_head', [ $custom_css, 'custom_frontend_css' ] );
	        }
		}

		// Insert <head>, <body> and <footer> code
		if ( array_key_exists( 'insert_head_body_footer_code', $options ) && $options['insert_head_body_footer_code'] ) {
			$insert_code = new ASENHA\Classes\Insert_Head_Body_Footer_Code;

			if ( isset( $options['head_code_priority'] ) ) {
				add_action( 'wp_head', [ $insert_code, 'insert_head_code' ], $options['head_code_priority'] );
			} else {
				add_action( 'wp_head', [ $insert_code, 'insert_head_code' ], 10 );
			}

			if ( function_exists( 'wp_body_open' ) && version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ) {

				if ( isset( $options['body_code_priority'] ) ) {
					add_action( 'wp_body_open', [ $insert_code, 'insert_body_code' ], $options['body_code_priority'] );
				} else {
					add_action( 'wp_body_open', [ $insert_code, 'insert_body_code' ], 10 );
				}
			}

			if ( isset( $options['footer_code_priority'] ) ) {
				add_action( 'wp_footer', [ $insert_code, 'insert_footer_code' ], $options['footer_code_priority'] );
			} else {
				add_action( 'wp_footer', [ $insert_code, 'insert_footer_code' ], 10 );
			}

		}
		
		// Custom Body Class
		if ( array_key_exists( 'enable_custom_body_class', $options ) && $options['enable_custom_body_class'] ) {
			if ( array_key_exists( 'enable_custom_body_class_for', $options ) && ! empty( $options['enable_custom_body_class_for'] ) )  {
				$custom_body_class = new ASENHA\Classes\Custom_Body_Class;
				add_action( 'add_meta_boxes', [ $custom_body_class, 'add_custom_body_class_meta_box' ], 10, 2 );
				add_action( 'save_post', [ $custom_body_class, 'save_custom_body_class' ], 99 );
				add_filter( 'body_class', [ $custom_body_class, 'append_custom_body_class'], 99 );
			}
		}

		// Manage ads.txt and app-ads.txt
		if ( array_key_exists( 'manage_ads_appads_txt', $options ) && $options['manage_ads_appads_txt'] ) {
			$manage_ads_appads_txt = new ASENHA\Classes\Manage_Ads_Appads_Txt;
			add_action( 'init', [ $manage_ads_appads_txt, 'show_ads_appads_txt_content' ] );
		}

		// Manage robots.txt
		if ( array_key_exists( 'manage_robots_txt', $options ) && $options['manage_robots_txt'] ) {
			$manage_robots_txt = new ASENHA\Classes\Manage_Robots_Txt;
			add_filter( 'robots_txt', [ $manage_robots_txt, 'maybe_show_custom_robots_txt_content' ], PHP_INT_MAX, 2 );
		}

		// =================================================================
		// DISABLE COMPONENTS
		// =================================================================

		// Disable Gutenberg
		if ( array_key_exists( 'disable_gutenberg', $options ) && $options['disable_gutenberg'] ) {
			if ( array_key_exists( 'disable_gutenberg_for', $options ) && ! empty( $options['disable_gutenberg_for'] ) )  {
				$disable_gutenberg = new ASENHA\Classes\Disable_Gutenberg;
				if ( ! class_exists( 'Classic_Editor' ) ) {
					require_once ASENHA_PATH . 'includes/empty-class-classic-editor.php';
				}
				add_action( 'admin_init', [ $disable_gutenberg, 'disable_gutenberg_for_post_types_admin' ] );
				add_action( 'admin_print_styles', [ $disable_gutenberg, 'safari_18_fix' ] );
				if ( array_key_exists( 'disable_gutenberg_frontend_styles', $options ) && $options['disable_gutenberg_frontend_styles'] ) {
					add_action( 'wp_enqueue_scripts', [ $disable_gutenberg, 'disable_gutenberg_for_post_types_frontend' ], 999999 );
				}
			}
		}

		// Disable Comments
		if ( array_key_exists( 'disable_comments', $options ) && $options['disable_comments'] ) {
			if ( array_key_exists( 'disable_comments_for', $options ) && ! empty( $options['disable_comments_for'] ) )  {
				$disable_comments = new ASENHA\Classes\Disable_Comments;
				add_action( 'admin_init', [ $disable_comments, 'disable_comments_for_post_types_edit' ] ); // also work with 'init', 'admin_init', 'wp_loaded', 'do_meta_boxes' hooks
				add_action( 'template_redirect', [ $disable_comments, 'show_blank_comment_template' ] );
				add_action( 'wp_loaded', [ $disable_comments, 'hide_existing_comments_on_frontend' ] );
				add_filter( 'comments_array', [ $disable_comments, 'maybe_return_empty_comments' ], 20, 2 );
				add_filter( 'comments_open', [ $disable_comments, 'close_comments_pings_on_frontend' ], 20, 2 );
				add_filter( 'pings_open', [ $disable_comments, 'close_comments_pings_on_frontend' ], 20, 2 );
				add_filter( 'get_comments_number', [ $disable_comments, 'return_zero_comments_count' ], 20, 2 );
				// Disable commenting via XML-RPC
				add_filter( 'xmlrpc_allow_anonymous_comments', '__return_false' );
				add_filter( 'xmlrpc_methods', [ $disable_comments, 'disable_xmlrpc_comments' ] );
				// Disable commenting via REST API
				add_filter( 'rest_endpoints', [ $disable_comments, 'disable_rest_api_comments_endpoints' ] );
				add_filter( 'rest_pre_insert_comment', [ $disable_comments, 'return_blank_comment' ], 10, 2 );
			}
		}

		// Disable REST API
		if ( array_key_exists( 'disable_rest_api', $options ) && $options['disable_rest_api'] ) { 			
			if ( version_compare( get_bloginfo('version'), '4.7', '>=' ) ) {
				$disable_rest_api = new ASENHA\Classes\Disable_REST_API;
				add_filter( 'rest_authentication_errors', [ $disable_rest_api, 'disable_rest_api' ] );
			} else {
				// REST API 1.x
				add_filter( 'json_enabled', '__return_false' );
				add_filter( 'json_jsonp_enabled', '__return_false' );
				// REST API 2.x
				add_filter( 'rest_enabled', '__return_false' );
				add_filter( 'rest_jsonp_enabled', '__return_false' );
			}

			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 ); // Disable REST API links in HTML <head>
			remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 ); // Disable REST API link in HTTP headers
			remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' ); // Remove REST API URL from the WP RSD endpoint.
		}

		// Disable Feeds
		if ( array_key_exists( 'disable_feeds', $options ) && $options['disable_feeds'] ) {
			remove_action( 'wp_head', 'feed_links', 2 ); // Remove feed links in <head>
			remove_action( 'wp_head', 'feed_links_extra', 3 ); // Remove feed links in <head>
			remove_action( 'do_feed_rdf', 'do_feed_rdf', 10, 0 );
			remove_action( 'do_feed_rss', 'do_feed_rss', 10, 0 );
			remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );
			remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1 );
			$disable_feeds = new ASENHA\Classes\Disable_Feeds;
			add_action( 'template_redirect', [ $disable_feeds, 'redirect_feed_to_403' ], 10, 1 );
		}

		// Disable Feeds
		if ( array_key_exists( 'disable_embeds', $options ) && $options['disable_embeds'] ) {
			$disable_embeds = new ASENHA\Classes\Disable_Embeds;
			add_action( 'init', [ $disable_embeds, 'disable_embeds_init' ], 9999 );
		}

		// Disable All Updates
		if ( array_key_exists( 'disable_all_updates', $options ) && $options['disable_all_updates'] ) {
			$disable_updates = new ASENHA\Classes\Disable_Updates;
			add_action( 'admin_init', [ $disable_updates, 'disable_update_notices_version_checks' ] );

			// Disable core update
			add_filter( 'pre_transient_update_core', [ $disable_updates, 'override_version_check_info' ] );
			add_filter( 'pre_site_transient_update_core', [ $disable_updates, 'override_version_check_info' ] );

			// Disable theme updates
			add_filter( 'pre_transient_update_themes', [ $disable_updates, 'override_version_check_info' ] );
			add_filter( 'pre_site_transient_update_themes', [ $disable_updates, 'override_version_check_info' ] );
			add_action( 'pre_set_site_transient_update_themes', [ $disable_updates, 'override_version_check_info' ], 20 );

			// Disable plugin updates
			add_filter( 'pre_transient_update_plugins', [ $disable_updates, 'override_version_check_info' ] );
			add_filter( 'pre_site_transient_update_plugins', [ $disable_updates, 'override_version_check_info' ] );
			add_action( 'pre_set_site_transient_update_plugins', [ $disable_updates, 'override_version_check_info' ], 20 );

			// Disable auto updates
			add_filter( 'automatic_updater_disabled', '__return_true' );
			if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) { 
				define( 'AUTOMATIC_UPDATER_DISABLED', true );
			}
			if ( ! defined( 'WP_AUTO_UPDATE_CORE') ) { 
				define( 'WP_AUTO_UPDATE_CORE', false );
			}

			add_filter( 'auto_update_core', '__return_false' );
			add_filter( 'wp_auto_update_core', '__return_false' );
			add_filter( 'allow_minor_auto_core_updates', '__return_false' );
			add_filter( 'allow_major_auto_core_updates', '__return_false' );
			add_filter( 'allow_dev_auto_core_updates', '__return_false' );

			add_filter( 'auto_update_plugin', '__return_false' );
			add_filter( 'auto_update_theme', '__return_false' );
			add_filter( 'auto_update_translation', '__return_false' );

			remove_action( 'init', 'wp_schedule_update_checks' );

			// Disable update emails
			add_filter( 'auto_core_update_send_email', '__return_false' );
			add_filter( 'send_core_update_notification_email', '__return_false' );
			add_filter( 'automatic_updates_send_debug_email', '__return_false' );

			// Remove Dashboard >> Updates menu
			add_action( 'admin_menu', [ $disable_updates, 'remove_updates_menu' ] );

		}

		// Disable Author Archives
		if ( array_key_exists( 'disable_author_archives', $options ) && $options['disable_author_archives'] ) {
			$disable_author_archives = new ASENHA\Classes\Disable_Author_Archives;
			add_action( 'template_redirect', [ $disable_author_archives, 'redirect_to_404' ], 1 );
			add_filter( 'author_link', [ $disable_author_archives, 'disable_frontend_author_link' ], PHP_INT_MAX );
			add_filter( 'user_row_actions', [ $disable_author_archives, 'remove_user_view_action' ], PHP_INT_MAX );
			if ( class_exists( 'WP_Sitemaps' ) ) {
				add_filter( 'wp_sitemaps_add_provider', [ $disable_author_archives, 'remove_users_from_sitemap' ], PHP_INT_MAX, 2 );
			}
			add_filter( 'author_rewrite_rules', [ $disable_author_archives, 'disable_rewrite_rules_for_authors' ], 10 );
		}

		// Disable Smaller Components
		if ( array_key_exists( 'disable_smaller_components', $options ) && $options['disable_smaller_components'] ) {
			$disable_smaller_components = new ASENHA\Classes\Disable_Smaller_Components;

			if ( array_key_exists( 'disable_head_generator_tag', $options ) && $options['disable_head_generator_tag'] ) {
				remove_action( 'wp_head', 'wp_generator' );
			}

			if ( array_key_exists( 'disable_feed_generator_tag', $options ) && $options['disable_feed_generator_tag'] ) {
				add_filter( 'the_generator', [ $disable_smaller_components, 'remove_feed_generator_tag' ], 10, 2 );
			}

			if ( array_key_exists( 'disable_resource_version_number', $options ) && $options['disable_resource_version_number'] ) {
				add_filter( 'style_loader_src', [ $disable_smaller_components, 'remove_resource_version_number' ], PHP_INT_MAX );
				add_filter( 'script_loader_src', [ $disable_smaller_components, 'remove_resource_version_number' ], PHP_INT_MAX );
			}

			if ( array_key_exists( 'disable_head_wlwmanifest_tag', $options ) && $options['disable_head_wlwmanifest_tag'] ) {
				remove_action( 'wp_head', 'wlwmanifest_link' );
			}

			if ( array_key_exists( 'disable_head_rsd_tag', $options ) && $options['disable_head_rsd_tag'] ) {
				remove_action( 'wp_head', 'rsd_link' );
			}

			if ( array_key_exists( 'disable_head_shortlink_tag', $options ) && $options['disable_head_shortlink_tag'] ) {
				// https://github.com/WordPress/wordpress-develop/blob/646fd2308f87332b0db5218ee2071c0ff8db0d4c/src/wp-includes/default-filters.php#L358
				remove_action( 'wp_head', 'wp_shortlink_wp_head' );
				// https://github.com/WordPress/wordpress-develop/blob/646fd2308f87332b0db5218ee2071c0ff8db0d4c/src/wp-includes/default-filters.php#L363
				remove_action ( 'template_redirect', 'wp_shortlink_header', 11, 0 );
			}

			if ( array_key_exists( 'disable_frontend_dashicons', $options ) && $options['disable_frontend_dashicons'] ) {
				add_action( 'init', [ $disable_smaller_components, 'disable_dashicons_public_assets' ] );
			}

			if ( array_key_exists( 'disable_emoji_support', $options ) && $options['disable_emoji_support'] ) {
				add_action( 'init', [ $disable_smaller_components, 'disable_emoji_support' ] );
			}

			if ( array_key_exists( 'disable_jquery_migrate', $options ) && $options['disable_jquery_migrate'] ) {
				add_action( 'wp_default_scripts', [ $disable_smaller_components, 'disable_jquery_migrate' ] );
			}

			if ( array_key_exists( 'disable_block_widgets', $options ) && $options['disable_block_widgets'] ) {
				// Disables the block editor from managing widgets in the Gutenberg plugin.
				add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );

				// Disables the block editor from managing widgets.
				add_filter( 'use_widgets_block_editor', '__return_false' );
			}

			if ( array_key_exists( 'disable_lazy_load', $options ) && $options['disable_lazy_load'] ) {
				add_filter( 'wp_lazy_loading_enabled', '__return_false' );
				add_filter( 'wp_get_attachment_image_attributes', [ $disable_smaller_components, 'eager_load_featured_images' ] );
			}

			if ( array_key_exists( 'disable_application_passwords', $options ) && $options['disable_application_passwords'] ) {
				add_filter('wp_is_application_passwords_available', '__return_false');
			}

			if ( array_key_exists( 'disable_plugin_theme_editor', $options ) ) {
				if ( $options['disable_plugin_theme_editor'] ) {
					add_action( 'admin_init', [ $disable_smaller_components, 'disable_plugin_theme_editor' ], PHP_INT_MAX );
				} else {
					add_action( 'admin_init', [ $disable_smaller_components, 'enable_plugin_theme_editor' ], PHP_INT_MAX );				
				}
			}

		}

		// =================================================================
		// SECURITY
		// =================================================================

		// Limit Login Attempts
		if ( array_key_exists( 'limit_login_attempts', $options ) && $options['limit_login_attempts'] ) {
			$limit_login_attempts = new ASENHA\Classes\Limit_Login_Attempts;
			add_filter( 'authenticate', [ $limit_login_attempts, 'maybe_allow_login' ], 999, 3 ); // Very low priority so it is processed last
			add_action( 'wp_login_errors', [ $limit_login_attempts, 'login_error_handler' ], 999, 2 );
			add_action( 'login_enqueue_scripts', [ $limit_login_attempts, 'maybe_hide_login_form' ] );
			add_filter( 'login_message', [ $limit_login_attempts, 'add_failed_login_message' ] );
			add_action( 'wp_login_failed', [ $limit_login_attempts, 'log_failed_login' ], 5 ); // Higher priority than one in Change Login URL
			add_action( 'wp_login', [ $limit_login_attempts, 'clear_failed_login_log' ] );
			// Log table clean up
			add_action( 'added_option', [ $limit_login_attempts, 'trigger_clear_or_schedule_log_clean_up_by_amount' ] );
			add_action( 'updated_option', [ $limit_login_attempts, 'trigger_clear_or_schedule_log_clean_up_by_amount' ] );
			add_action( 'plugins_loaded', [ $limit_login_attempts, 'clear_or_schedule_log_clean_up_by_amount' ] );
			add_action( 'asenha_failed_login_attempts_log_cleanup_by_amount', [ $limit_login_attempts, 'perform_failed_login_attempts_log_clean_up_by_amount' ] );
		}

		// CAPTCHA Protection
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( array_key_exists( 'captcha_protection', $options ) && $options['captcha_protection'] ) {
		        $captcha_type = ( isset( $options['captcha_protection_types'] ) ) ? $options['captcha_protection_types'] : 'altcha';
				$captcha_wp_locations = ( array_key_exists( 'captcha_wp_locations', $options ) ) ? $options['captcha_wp_locations'] : array();

				switch ( $captcha_type ) {
					case 'altcha';
						$altcha = new ASENHA\Classes\CAPTCHA_Protection_ALTCHA;
						add_action( 'rest_api_init', [ $altcha, 'init_altcha' ] );
						add_action( 'login_enqueue_scripts', 'asenha_login_altcha_scripts__premium_only' );
						add_action( 'wp_enqueue_scripts', 'asenha_frontend_altcha_scripts__premium_only' );
						add_filter( 'script_loader_tag', [ $altcha, 'altcha_script_tags' ], 10, 3 );
						// ALTCHA for default WordPress forms
					    if ( in_array( 'wp_login_form', $captcha_wp_locations ) ) {
							add_action( 'login_form', [ $altcha, 'add_altcha_to_login_form' ], 10, 0 );
							add_filter( 'authenticate', [ $altcha, 'perform_altcha_login_verification' ], 20, 1  );
					    }
					    if ( in_array( 'wp_password_reset_form', $captcha_wp_locations ) ) {
					    	add_action( 'lostpassword_form', [ $altcha, 'add_altcha_to_password_reset_form' ], 10, 0 );
					    	add_filter( 'lostpassword_post', [ $altcha, 'perform_altcha_password_reset_verification' ], 10, 1 );
					    }
					    if ( in_array( 'wp_registration_form', $captcha_wp_locations ) ) {
							add_action( 'register_form', [ $altcha, 'add_altcha_to_registration_form' ], 10, 0 );
							add_action( 'register_post', [ $altcha, 'perform_altcha_registration_verification' ], 10, 3 );
					    }
					    if ( in_array( 'wp_comment_form', $captcha_wp_locations ) ) {
					    	add_action( 'comment_form_after_fields', [ $altcha, 'add_altcha_to_comment_form' ], 10, 0 );
					    	// add_action( 'comment_form_logged_in_after', [ $altcha, 'add_altcha_to_comment_form' ], 10, 0 );
					    	add_filter( 'preprocess_comment', [ $altcha, 'perform_altcha_comment_verification' ], 10, 1 );
					    }

					    // When WooCommerce is active
						$captcha_woo_locations = ( array_key_exists( 'captcha_woo_locations', $options ) ) ? $options['captcha_woo_locations'] : array();
						if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
						    if ( in_array( 'woo_login_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_login_form', [ $altcha, 'add_altcha_to_woo_login_form' ], 10, 0 );
						    	add_filter( 'authenticate', [ $altcha, 'perform_altcha_woo_login_verification' ], 30, 1 );
						    }
						    if ( in_array( 'woo_password_reset_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_lostpassword_form', [ $altcha, 'add_altcha_to_woo_lostpassword_form' ], 10, 0 );
						    	add_filter( 'lostpassword_post', [ $altcha, 'perform_altcha_woo_password_reset_verification' ], 20, 1 );
						    }
						    if ( in_array( 'woo_registration_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_register_form', [ $altcha, 'add_altcha_to_woo_registration_form' ], 10, 0 );
								add_action( 'woocommerce_register_post', [ $altcha, 'perform_altcha_woo_registration_verification' ], 10, 3 );
						    }
						    // if ( in_array( 'woo_checkout_form', $captcha_woo_locations ) ) {
						    // 	add_action( 'woocommerce_review_order_before_submit', [ $altcha, 'add_altcha_to_woo_checkout_form' ], 10, 0 );
						    // 	add_action( 'woocommerce_checkout_process', [ $altcha, 'perform_altcha_woo_checkout' ] );
						    // }
						}
						break;

					case 'recaptcha';
						$recaptcha = new ASENHA\Classes\CAPTCHA_Protection_reCAPTCHA;
						add_action( 'login_enqueue_scripts', 'asenha_login_recaptcha_scripts__premium_only' );
						add_action( 'wp_enqueue_scripts', 'asenha_frontend_recaptcha_scripts__premium_only' );
						// reCAPTCHA for default WordPress forms
					    if ( in_array( 'wp_login_form', $captcha_wp_locations ) ) {
							add_action( 'login_form', [ $recaptcha, 'add_recaptcha_to_login_form' ] );
							add_filter( 'authenticate', [ $recaptcha, 'recaptcha_authenticate_login' ], 20, 1 );
					    }
					    if ( in_array( 'wp_password_reset_form', $captcha_wp_locations ) ) {
							add_action( 'lostpassword_form', [ $recaptcha, 'add_recaptcha_to_password_reset_form' ] );
							add_action( 'lostpassword_post', [ $recaptcha, 'recaptcha_validate_password_reset' ], 10, 2 );
					    }
					    if ( in_array( 'wp_registration_form', $captcha_wp_locations ) ) {
							add_action( 'register_form', [ $recaptcha, 'add_recaptcha_to_registration_form' ] );
							add_action( 'register_post', [ $recaptcha, 'recaptcha_validate_registration' ], 10, 3 );
					    }
					    if ( in_array( 'wp_comment_form', $captcha_wp_locations ) ) {
							add_action( 'comment_form_after_fields', [ $recaptcha, 'add_recaptcha_to_comment_form' ] );
					    	add_filter( 'preprocess_comment', [ $recaptcha, 'recaptcha_verify_comment' ], 10, 1 );
					    }

					    // When WooCommerce is active
						if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
							$captcha_woo_locations = ( array_key_exists( 'captcha_woo_locations', $options ) ) ? $options['captcha_woo_locations'] : array();
						    if ( in_array( 'woo_login_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_login_form', [ $recaptcha, 'add_recaptcha_to_woo_login_form' ] );
								add_filter( 'authenticate', [ $recaptcha, 'recaptcha_authenticate_woo_login' ], 30, 1 );
						    }
						    if ( in_array( 'woo_password_reset_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_lostpassword_form', [ $recaptcha, 'add_recaptcha_to_woo_lostpassword_form' ] );
								add_action( 'lostpassword_post', [ $recaptcha, 'recaptcha_validate_woo_password_reset' ], 20, 2 );
						    }
						    if ( in_array( 'woo_registration_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_register_form', [ $recaptcha, 'add_recaptcha_to_woo_registration_form' ] );
								add_action( 'woocommerce_register_post', [ $recaptcha, 'recaptcha_validate_woo_registration' ], 10, 3 );
						    }
						    // if ( in_array( 'woo_checkout_form', $captcha_woo_locations ) ) {
						    // 	add_action( 'woocommerce_review_order_before_submit', [ $recaptcha, 'add_recaptcha_to_woo_checkout_form' ], 10, 0 );
						    // 	add_action( 'woocommerce_checkout_process', [ $recaptcha, 'recaptcha_validate_woo_checkout' ] );
						    // }
						}
						break;

					case 'turnstile';
						$turnstile = new ASENHA\Classes\CAPTCHA_Protection_Turnstile;
						add_action( 'login_enqueue_scripts', 'asenha_login_turnstile_scripts__premium_only' );
						add_action( 'wp_enqueue_scripts', 'asenha_frontend_turnstile_scripts__premium_only' );
						add_filter( 'script_loader_tag', [ $turnstile, 'turnstile_script_tags' ], 10, 2 );
						// Turnstile for default WordPress forms
					    if ( in_array( 'wp_login_form', $captcha_wp_locations ) ) {
							add_action( 'login_form', [ $turnstile, 'add_turnstile_to_login_form' ] );
							add_filter( 'authenticate', [ $turnstile, 'turnstile_authenticate_login' ], 21, 1 );
					    }
					    if ( in_array( 'wp_password_reset_form', $captcha_wp_locations ) ) {
							add_action( 'lostpassword_form', [ $turnstile, 'add_turnstile_to_password_reset_form' ] );
							add_action( 'lostpassword_post', [ $turnstile, 'turnstile_validate_password_reset' ], 10, 1 );
					    }
					    if ( in_array( 'wp_registration_form', $captcha_wp_locations ) ) {
							add_action( 'register_form', [ $turnstile, 'add_turnstile_to_registration_form' ] );
							add_action( 'register_post', [ $turnstile, 'turnstile_validate_registration' ], 10, 3 );
					    }
					    if ( in_array( 'wp_comment_form', $captcha_wp_locations ) ) {
							add_action( 'comment_form_after_fields', [ $turnstile, 'add_turnstile_to_comment_form' ] );
					    	add_filter( 'preprocess_comment', [ $turnstile, 'turnstile_verify_comment' ], 10, 1 );
					    }

					    // When WooCommerce is active
						if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
							$captcha_woo_locations = ( array_key_exists( 'captcha_woo_locations', $options ) ) ? $options['captcha_woo_locations'] : array();
						    if ( in_array( 'woo_login_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_login_form', [ $turnstile, 'add_turnstile_to_woo_login_form' ] );
								add_filter( 'authenticate', [ $turnstile, 'turnstile_authenticate_woo_login' ], 30, 1 );
						    }
						    if ( in_array( 'woo_password_reset_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_lostpassword_form', [ $turnstile, 'add_turnstile_to_woo_lostpassword_form' ] );
								add_action( 'lostpassword_post', [ $turnstile, 'turnstile_validate_woo_password_reset' ], 20, 1 );
						    }
						    if ( in_array( 'woo_registration_form', $captcha_woo_locations ) ) {
						    	add_action( 'woocommerce_register_form', [ $turnstile, 'add_turnstile_to_woo_registration_form' ] );
								add_action( 'woocommerce_register_post', [ $turnstile, 'turnstile_validate_woo_registration' ], 10, 3 );
						    }
						    // if ( in_array( 'woo_checkout_form', $captcha_woo_locations ) ) {
						    	// add_action( 'woocommerce_review_order_before_submit', [ $turnstile, 'add_turnstile_to_woo_checkout_form' ], 10, 0 );
						    	// add_action( 'woocommerce_checkout_process', [ $turnstile, 'turnstile_validate_woo_checkout' ] );
						    // }
						}
						break;
				}
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
					$captcha_protection = new ASENHA\Classes\CAPTCHA_Protection;
					add_filter( 'wp_login', [ $captcha_protection, 'maybe_keep_original_redirect' ], 3, 2 ); // Earlier than Redirect After Login module
				}
			}
		}

		// Obfuscate Author Slugs
		if ( array_key_exists( 'obfuscate_author_slugs', $options ) && $options['obfuscate_author_slugs'] ) {
			$obfuscate_author_slugs = new ASENHA\Classes\Obfuscate_Author_Slugs;
			add_action( 'pre_get_posts', [ $obfuscate_author_slugs, 'alter_author_query' ], 10 );
			add_filter( 'author_link', [ $obfuscate_author_slugs, 'alter_author_link' ], 10, 3 );
			add_filter( 'rest_prepare_user', [ $obfuscate_author_slugs, 'alter_json_users' ], 10, 3 );
		}

		// Email Address Obfuscator
		if ( array_key_exists( 'obfuscate_email_address', $options ) && $options['obfuscate_email_address'] ) {
			$email_address_obfuscator = new ASENHA\Classes\Email_Address_Obfuscator;
			add_shortcode( 'obfuscate', [ $email_address_obfuscator, 'obfuscate_string' ] );
			add_filter( 'safe_style_css', [ $email_address_obfuscator, 'add_additional_attributes_to_safe_css' ] );
			add_filter( 'widget_text', 'shortcode_unautop');
			add_filter( 'widget_text', 'do_shortcode' );
	        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				if ( array_key_exists( 'obfuscate_email_address_in_content', $options ) && $options['obfuscate_email_address_in_content'] ) {
					add_filter( 'the_content', [ $email_address_obfuscator, 'obfuscate_emails_in_content__premium_only' ] );
					if ( in_array( 'elementor/elementor.php', get_option( 'active_plugins', array() ) ) ) {
						add_filter( 'elementor/frontend/the_content', [ $email_address_obfuscator, 'obfuscate_emails_in_content__premium_only' ] );
					}
				}	        	
	        }
		}

		// Disable XML-RPC
		if ( array_key_exists( 'disable_xmlrpc', $options ) && $options['disable_xmlrpc'] ) {
			$disable_xml_rpc = new ASENHA\Classes\Disable_XML_RPC;
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_action( 'wp', [ $disable_xml_rpc, 'remove_xmlrpc_link' ], 11 );
			add_filter( 'xmlrpc_methods', [ $disable_xml_rpc, 'remove_xmlrpc_methods' ] );
			add_filter( 'wp_xmlrpc_server_class', [ $disable_xml_rpc, 'maybe_disable_xmlrpc' ] );
			// Hide xmlrpc.php in HTTP response headers
			add_filter( 'wp_headers', [ $disable_xml_rpc, 'hide_xmlrpc_in_http_response_headers' ] );
			add_filter( 'pings_open', '__return_false', PHP_INT_MAX );
		}

		// =================================================================
		// OPTIMIZATIONS
		// =================================================================

		// Image Upload Control
		if ( array_key_exists( 'image_upload_control', $options ) && $options['image_upload_control'] ) {
			$image_upload_control = new ASENHA\Classes\Image_Upload_Control;
			// Fix image rotation. Ref: https://plugins.trac.wordpress.org/browser/fix-image-rotation/tags/2.2.2/includes/class-fix-image-rotation.php
			if ( extension_loaded( 'exif' ) && function_exists( 'exif_read_data' ) ) {
				add_filter( 'wp_handle_upload_prefilter', [ $image_upload_control, 'prefilter_maybe_fix_image_orientation' ], 10, 1 );
				add_filter( 'wp_handle_upload', [ $image_upload_control, 'maybe_fix_image_orientation' ], 1, 3 );
			}
			// Resize and convert happens here
			add_filter( 'wp_handle_upload', [ $image_upload_control, 'image_upload_handler' ] );

			if ( array_key_exists( 'disabled_image_sizes', $options ) && isset( $options['disabled_image_sizes'] ) ) {
				add_filter( 'intermediate_image_sizes_advanced', [ $image_upload_control, 'disable_intermediate_image_sizes__premium_only' ], 10, 2 );
			}
		}

		// Revisions Control
		if ( array_key_exists( 'enable_revisions_control', $options ) && $options['enable_revisions_control'] ) {
			$revisions_control = new ASENHA\Classes\Revisions_Control;
			add_filter( 'wp_revisions_to_keep', [ $revisions_control, 'limit_revisions_to_max_number' ], 10, 2 );
		}

		// Heartbeat Control
		if ( array_key_exists( 'enable_heartbeat_control', $options ) && $options['enable_heartbeat_control'] ) {
			$heartbeat_control = new ASENHA\Classes\Heartbeat_Control;
			add_filter( 'heartbeat_settings', [ $heartbeat_control, 'maybe_modify_heartbeat_frequency' ], 99, 2 );
			add_action( 'admin_enqueue_scripts', [ $heartbeat_control, 'maybe_disable_heartbeat' ], 99 );
			add_action( 'wp_enqueue_scripts', [ $heartbeat_control, 'maybe_disable_heartbeat' ], 99 );
		}

		// =================================================================
		// UTILITIES
		// =================================================================

		// SMTP Email Delivery
		if ( array_key_exists( 'smtp_email_delivery', $options ) && $options['smtp_email_delivery'] ) {
			$email_delivery = new ASENHA\Classes\Email_Delivery;
			add_action( 'phpmailer_init', [ $email_delivery, 'deliver_email_via_smtp' ], 99999 );
			add_action( 'wp_ajax_send_test_email', [ $email_delivery, 'send_test_email' ] );
			if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				// Email Delivery Log
				if ( array_key_exists( 'smtp_email_log', $options ) && $options['smtp_email_log'] ) {
					add_filter( 'wp_mail', [ $email_delivery, 'log_via_wp_mail__premium_only' ] );
					add_action( 'wp_mail_failed', [ $email_delivery, 'process_error__premium_only' ] );
					add_action( 'phpmailer_init', [ $email_delivery, 'additional_logging__premium_only' ], PHP_INT_MAX );
					add_action( 'admin_menu', [ $email_delivery, 'add_email_log_submenu__premium_only' ] );
					require_once ASENHA_PATH . 'includes/premium/email-delivery/email-log-list-table.php';
					add_action( 'wp_ajax_get_ed_log_data', [ $email_delivery, 'get_ed_log_data__premium_only' ] );
					// Perform scheduled clean up of log entries up to a defined max number
					add_action( 'added_option', [ $email_delivery, 'trigger_clear_or_schedule_log_clean_up_by_amount__premium_only' ] );
					add_action( 'updated_option', [ $email_delivery, 'trigger_clear_or_schedule_log_clean_up_by_amount__premium_only' ] );
					add_action( 'plugins_loaded', [ $email_delivery, 'clear_or_schedule_log_clean_up_by_amount__premium_only' ] );
					add_action( 'asenha_email_log_cleanup_by_amount', [ $email_delivery, 'perform_email_log_clean_up_by_amount__premium_only' ] );
					add_action( 'wp_ajax_resend_email', [ $email_delivery, 'resend_email__premium_only' ] );
					add_action( 'admin_action_delete_email', [ $email_delivery, 'delete_email__premium_only' ] ); // GET request to admin.php
					add_action( 'admin_notices', [ $email_delivery, 'maybe_show_email_deletion_notice__premium_only' ] );
					add_action( 'admin_action_clear_log', [ $email_delivery, 'clear_log__premium_only' ] ); // GET request to admin.php
					add_action( 'admin_notices', [ $email_delivery, 'maybe_show_clear_log_notice__premium_only' ] );
				}
			}
		}

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
        	// Form Builder
			if ( array_key_exists( 'form_builder', $options ) && $options['form_builder'] ) {
				require_once ASENHA_PATH . 'includes/premium/form-builder/form-builder.php';				
			}

			// Local User Avatar
			if ( array_key_exists( 'local_user_avatar', $options ) && $options['local_user_avatar'] ) {
				$local_user_avatar = new ASENHA\Classes\Local_User_Avatar;
				// Render Profile Picture fields
				// For when current user edits their own profile
				add_action( 'show_user_profile', [ $local_user_avatar, 'render_personal_profile_fields__premium_only' ] );
				// For when admins edit user profiles
				add_action( 'edit_user_profile', [ $local_user_avatar, 'render_personal_profile_fields__premium_only' ] );
				
				// Update Profile Picture fields / user meta
				// For when current user edits their own profile
				add_action( 'personal_options_update', [ $local_user_avatar, 'update_personal_profile_user_meta__premium_only' ] );
				// For when admins edit user profiles
				add_action( 'edit_user_profile_update', [ $local_user_avatar, 'update_personal_profile_user_meta__premium_only' ] );
				
				// Delete Profile Picture user meta when attachment is deleted
				add_action( 'delete_attachment', [ $local_user_avatar, 'delete_personal_profile_user_meta__premium_only' ] );
				
				// Override avatar with the local one uploaded
				add_filter( 'get_avatar', [ $local_user_avatar, 'override_avatar_with_local__premium_only' ], 5, 5 );
				add_filter( 'get_avatar_url', [ $local_user_avatar, 'override_avatar_url_with_local__premium_only' ], 10, 3 );	
			}
        }

		// Multiple User Roles
		if ( array_key_exists( 'multiple_user_roles', $options ) && $options['multiple_user_roles'] ) {
			$multiple_user_roles = new ASENHA\Classes\Multiple_User_Roles;
			// Show roles checkboxes
			add_action( 'show_user_profile', [ $multiple_user_roles, 'add_multiple_roles_ui' ] ); // for when user edits their own profile
			add_action( 'edit_user_profile', [ $multiple_user_roles, 'add_multiple_roles_ui' ] ); // for when editing other user's profile
			add_action( 'user_new_form', [ $multiple_user_roles, 'add_multiple_roles_ui' ] ); // new user creation
			// Save roles selections
			add_action( 'personal_options_update', [ $multiple_user_roles, 'save_roles_assignment' ] ); // for when user edits their own profile
			add_action( 'edit_user_profile_update', [ $multiple_user_roles, 'save_roles_assignment' ] ); // for when editing other user's profile
			add_action( 'user_register', [ $multiple_user_roles, 'save_roles_assignment' ] ); // new user creation
		}
		
		// Image Sizes Panel
		if ( array_key_exists( 'image_sizes_panel', $options ) && $options['image_sizes_panel'] ) {
			$image_sizes_panel = new ASENHA\Classes\Image_Sizes_Panel;
			add_action( 'add_meta_boxes', array( $image_sizes_panel, 'add_image_sizes_meta_box' ) );
		}

		// View Admin as Role
		if ( array_key_exists( 'view_admin_as_role', $options ) && $options['view_admin_as_role'] ) {
			$view_admin_as_role = new ASENHA\Classes\View_Admin_As_Role;
			add_action( 'admin_bar_menu', [ $view_admin_as_role, 'view_admin_as_admin_bar_menu' ], 8 ); // Priority 8 so it is next to username section
			add_action( 'init', [ $view_admin_as_role, 'role_switcher_to_view_admin_as' ] );
			add_action( 'profile_update', [ $view_admin_as_role, 'maybe_prevent_switchback_to_administrator' ], 20 );
			// add_action( 'wp_die_handler', [ $view_admin_as_role, 'custom_error_page_on_switch_failure' ] );
			add_action( 'admin_footer', [ $view_admin_as_role, 'add_floating_reset_button' ] );
		}

		// Password Protection
		if ( array_key_exists( 'enable_password_protection', $options ) && $options['enable_password_protection'] ) {
			$password_protection = new ASENHA\Classes\Password_Protection;
			add_action( 'plugins_loaded', [ $password_protection, 'show_password_protection_admin_bar_icon' ] );
			add_action( 'init', [ $password_protection, 'maybe_disable_page_caching' ], 1 );
			add_action( 'template_redirect', [ $password_protection, 'maybe_show_login_form' ], 0 ); // load early
			add_action( 'init', [ $password_protection, 'maybe_process_login' ], 1 );
			add_action( 'asenha_password_protection_error_messages', [ $password_protection, 'add_login_error_messages' ] );
			if ( function_exists( 'wp_site_icon' ) ) { // WP v4.3+
				add_action( 'asenha_password_protection_login_head', 'wp_site_icon' );
			}
		}

		// Maintenance Mode
		if ( array_key_exists( 'maintenance_mode', $options ) && $options['maintenance_mode'] ) {
			$maintenance_mode = new ASENHA\Classes\Maintenance_Mode;

	        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				if ( array_key_exists( 'maintenance_mode_access_for', $options ) && isset( $options['maintenance_mode_access_for'] ) ) {
					global $roles_maintenance_mode_frontend_access;
					$for_roles = isset( $options['maintenance_mode_access_for'] ) ? $options['maintenance_mode_access_for'] : array();
					$roles_maintenance_mode_frontend_access = array();

					// Assemble single-dimensional array of roles for which duplication would be enabled

					// Admins are always allowed
					$roles_maintenance_mode_frontend_access[] = 'administrator';

					if ( is_array( $for_roles ) && ( count( $for_roles ) > 0 ) ) {
						foreach( $for_roles as $role_slug => $frontend_access_allowed ) {
							if ( $frontend_access_allowed ) {
								$roles_maintenance_mode_frontend_access[] = $role_slug;
							}
						}
					}
				}
			}

			add_action( 'send_headers', [ $maintenance_mode, 'maintenance_mode_redirect' ] );
			add_action( 'plugins_loaded', [ $maintenance_mode, 'show_maintenance_mode_admin_bar_icon' ] );
		}

		// Redirect 404 to Homepage
		if ( array_key_exists( 'redirect_404_to_homepage', $options ) && $options['redirect_404_to_homepage'] ) {
			$redirect_fourofour = new ASENHA\Classes\Redirect_Fourofour;
			add_filter( 'template_redirect', [ $redirect_fourofour, 'redirect_404' ], PHP_INT_MAX );
		}

		// Display System Summary
		if ( array_key_exists( 'display_system_summary', $options ) && $options['display_system_summary'] ) {
			// require_once ASENHA_PATH . 'includes/premium/display-system-summary/ignore-directories.php';
			$display_system_summary = new ASENHA\Classes\Display_System_Summary;
			add_action( 'rightnow_end', [ $display_system_summary, 'display_system_summary' ] );
		}

		// Search Engines Visibility Status
		if ( array_key_exists( 'search_engine_visibility_status', $options ) && $options['search_engine_visibility_status'] ) {
			$search_engines_visibility = new ASENHA\Classes\Search_Engines_Visibility;
			if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
				add_action( 'admin_init', [ $search_engines_visibility, 'handle_search_engine_visibility__premium_only' ] );
			}
			add_action( 'admin_init', [ $search_engines_visibility, 'maybe_display_search_engine_visibility_status' ] );
		}

	}

}

Admin_Site_Enhancements::get_instance();