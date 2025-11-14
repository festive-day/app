<?php

namespace ASENHA\Classes;

/**
 * Class related to sanitization of settings fields for saving as options
 *
 * @since 2.2.0
 */
class Settings_Sanitization {

	/**
	 * Sanitize options
	 *
	 * @since 1.0.0
	 */
	function sanitize_for_options( $options ) {

		// Call WordPress globals required for validating the fields	
		global $wp_roles, $asenha_all_post_types, $asenha_public_post_types, $asenha_nonpublic_post_types, $asenha_gutenberg_post_types, $asenha_revisions_post_types, $active_plugin_slugs;

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			global $workable_nodes;
		}

		$roles = $wp_roles->get_names();

		$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
		// if ( false === $options_extra ) {
		// 	add_option( ASENHA_SLUG_U . '_extra', array(), true );
		// }
		
		$common_methods = new Common_Methods;
		
		// =================================================================
		// CONTENT MANAGEMENT
		// =================================================================

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Custom Content Types
			if ( ! isset( $options['custom_content_types'] ) ) $options['custom_content_types'] = false;
			$options['custom_content_types'] = ( 'on' == $options['custom_content_types'] ? true : false );

			if ( ! isset( $options['custom_content_types_flush_rewrite_rules_needed'] ) ) $options['custom_content_types_flush_rewrite_rules_needed'] = false;        	

			if ( ! isset( $options['custom_field_groups'] ) ) $options['custom_field_groups'] = false;
			$options['custom_field_groups'] = ( 'on' == $options['custom_field_groups'] ? true : false );

        }

		// Content Duplication
		if ( ! isset( $options['enable_duplication'] ) ) $options['enable_duplication'] = false;
		$options['enable_duplication'] = ( 'on' == $options['enable_duplication'] ? true : false );

		if ( ! isset( $options['duplication_redirect_destination'] ) ) $options['duplication_redirect_destination'] = 'edit';

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['enable_duplication_link_at'] ) ) $options['enable_duplication_link_at'] = array( 'post-action','admin-bar' );
			$options['enable_duplication_link_at'] = ( ! empty( $options['enable_duplication_link_at'] ) ? $options['enable_duplication_link_at'] : array() );

			if ( ! isset( $options['enable_duplication_on_post_types_type'] ) ) $options['enable_duplication_on_post_types_type'] = 'only-on';
			$options['enable_duplication_on_post_types_type'] = ( ! empty( $options['enable_duplication_on_post_types_type'] ) ) ? sanitize_text_field( $options['enable_duplication_on_post_types_type'] ) : 'only-on';


			if ( is_array( $asenha_public_post_types ) ) {
				foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
					if ( ! isset( $options['enable_duplication_on_post_types'][$post_type_slug] ) ) $options['enable_duplication_on_post_types'][$post_type_slug] = false;
					$options['enable_duplication_on_post_types'][$post_type_slug] = ( 'on' == $options['enable_duplication_on_post_types'][$post_type_slug] ? true : false );
				}
			}

			if ( is_array( $asenha_nonpublic_post_types ) ) {
				foreach ( $asenha_nonpublic_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
					if ( ! isset( $options['enable_duplication_on_post_types'][$post_type_slug] ) ) $options['enable_duplication_on_post_types'][$post_type_slug] = false;
					$options['enable_duplication_on_post_types'][$post_type_slug] = ( 'on' == $options['enable_duplication_on_post_types'][$post_type_slug] ? true : false );
				}
			}
			
			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
					if ( ! isset( $options['enable_duplication_for'][$role_slug] ) ) $options['enable_duplication_for'][$role_slug] = false;
					$options['enable_duplication_for'][$role_slug] = ( 'on' == $options['enable_duplication_for'][$role_slug] ? true : false );
				}
			}
        }

		// Content Order
		if ( ! isset( $options['content_order'] ) ) $options['content_order'] = false;
		$options['content_order'] = ( 'on' == $options['content_order'] ? true : false );

		if ( is_array( $asenha_all_post_types ) ) {
			foreach ( $asenha_all_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				if ( post_type_supports( $post_type_slug, 'page-attributes' ) || is_post_type_hierarchical( $post_type_slug ) ) {
					if ( ! isset( $options['content_order_for'][$post_type_slug] ) ) $options['content_order_for'][$post_type_slug] = false;
					$options['content_order_for'][$post_type_slug] = ( 'on' == $options['content_order_for'][$post_type_slug] ? true : false );	
				}
			}
		}

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( is_array( $asenha_all_post_types ) ) {
				foreach ( $asenha_all_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
					if ( ! post_type_supports( $post_type_slug, 'page-attributes' ) && ! is_post_type_hierarchical( $post_type_slug ) ) {
						if ( ! isset( $options['content_order_for_other_post_types'][$post_type_slug] ) ) $options['content_order_for_other_post_types'][$post_type_slug] = false;
						$options['content_order_for_other_post_types'][$post_type_slug] = ( 'on' == $options['content_order_for_other_post_types'][$post_type_slug] ? true : false );	
					}
				}
			}

			if ( ! isset( $options['content_order_frontend'] ) ) $options['content_order_frontend'] = false;
			$options['content_order_frontend'] = ( 'on' == $options['content_order_frontend'] ? true : false );
        }

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Terms Order
			if ( ! isset( $options['terms_order'] ) ) $options['terms_order'] = false;
			$options['terms_order'] = ( 'on' == $options['terms_order'] ? true : false );

			if ( is_array( $asenha_public_post_types ) ) {
				foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
					$post_type_taxonomies = get_object_taxonomies( $post_type_slug );

					// Get the hierarchical taxonomies for the post type
					foreach ( $post_type_taxonomies as $key => $taxonomy_name ) {
		                $taxonomy_info = get_taxonomy( $taxonomy_name );

		                if ( empty( $taxonomy_info->hierarchical ) ||  $taxonomy_info->hierarchical !== TRUE ) {
		                    unset( $post_type_taxonomies[$key] );
		                }
		            }
		            
		            // Only if there's at least 1 hierarchical taxonomy for the post type
		            if ( count( $post_type_taxonomies ) > 0 ) {
						
						if ( ! isset( $options['terms_order_for'][$post_type_slug] ) ) $options['terms_order_for'][$post_type_slug] = false;
						$options['terms_order_for'][$post_type_slug] = ( 'on' == $options['terms_order_for'][$post_type_slug] ? true : false );	
		            	
		            }
				}
			}

			if ( ! isset( $options['terms_order_frontend'] ) ) $options['terms_order_frontend'] = false;
			$options['terms_order_frontend'] = ( 'on' == $options['terms_order_frontend'] ? true : false );
        }

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Enable Media Categories
			if ( ! isset( $options['enable_media_categories'] ) ) $options['enable_media_categories'] = false;
			$options['enable_media_categories'] = ( 'on' == $options['enable_media_categories'] ? true : false );

			if ( ! isset( $options['media_categories_side_panel'] ) ) $options['media_categories_side_panel'] = 200;
			$options['media_categories_side_panel'] = ( ! empty( $options['media_categories_side_panel'] ) ) ? sanitize_text_field( $options['media_categories_side_panel'] ) : 20;
        }

		// Enable Media Replacement
		if ( ! isset( $options['enable_media_replacement'] ) ) $options['enable_media_replacement'] = false;
		$options['enable_media_replacement'] = ( 'on' == $options['enable_media_replacement'] ? true : false );

		if ( ! isset( $options['disable_media_replacement_cache_busting'] ) ) $options['disable_media_replacement_cache_busting'] = false;
		$options['disable_media_replacement_cache_busting'] = ( 'on' == $options['disable_media_replacement_cache_busting'] ? true : false );

		// Enable SVG Upload
		if ( ! isset( $options['enable_svg_upload'] ) ) $options['enable_svg_upload'] = false;
		$options['enable_svg_upload'] = ( 'on' == $options['enable_svg_upload'] ? true : false );

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
				if ( ! isset( $options['enable_svg_upload_for'][$role_slug] ) ) $options['enable_svg_upload_for'][$role_slug] = false;
				$options['enable_svg_upload_for'][$role_slug] = ( 'on' == $options['enable_svg_upload_for'][$role_slug] ? true : false );
			}
		}

		// Enable SVG Upload
		if ( ! isset( $options['enable_avif_upload'] ) ) $options['enable_avif_upload'] = false;
		$options['enable_avif_upload'] = ( 'on' == $options['enable_avif_upload'] ? true : false );        	

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Public Preview for Drafts
			if ( ! isset( $options['public_preview_for_drafts'] ) ) $options['public_preview_for_drafts'] = false;
			$options['public_preview_for_drafts'] = ( 'on' == $options['public_preview_for_drafts'] ? true : false );

			if ( ! isset( $options['public_preview_max_days'] ) ) $options['public_preview_max_days'] = 3;
			$options['public_preview_max_days'] = ( ! empty( $options['public_preview_max_days'] ) ) ? sanitize_text_field( $options['public_preview_max_days'] ) : 3;

			if ( is_array( $asenha_public_post_types ) ) {
				foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
					if ( ! isset( $options['public_drafts_preview_for'][$post_type_slug] ) ) $options['public_drafts_preview_for'][$post_type_slug] = false;
					$options['public_drafts_preview_for'][$post_type_slug] = ( 'on' == $options['public_drafts_preview_for'][$post_type_slug] ? true : false );
				}
			}        	
        }

		// Enable External Permalinks
		if ( ! isset( $options['enable_external_permalinks'] ) ) $options['enable_external_permalinks'] = false;
		$options['enable_external_permalinks'] = ( 'on' == $options['enable_external_permalinks'] ? true : false );

		if ( is_array( $asenha_public_post_types ) ) {
			foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				if ( ! isset( $options['enable_external_permalinks_for'][$post_type_slug] ) ) $options['enable_external_permalinks_for'][$post_type_slug] = false;
				$options['enable_external_permalinks_for'][$post_type_slug] = ( 'on' == $options['enable_external_permalinks_for'][$post_type_slug] ? true : false );
			}
		}

		// Open All External Links in New Tab
		if ( ! isset( $options['external_links_new_tab'] ) ) $options['external_links_new_tab'] = false;
		$options['external_links_new_tab'] = ( 'on' == $options['external_links_new_tab'] ? true : false );

		// Allow Custom Nav Menu Items to Open in New Tab
		if ( ! isset( $options['custom_nav_menu_items_new_tab'] ) ) $options['custom_nav_menu_items_new_tab'] = false;
		$options['custom_nav_menu_items_new_tab'] = ( 'on' == $options['custom_nav_menu_items_new_tab'] ? true : false );

		// Enable Auto-Publishing of Posts with Missed Schedules
		if ( ! isset( $options['enable_missed_schedule_posts_auto_publish'] ) ) $options['enable_missed_schedule_posts_auto_publish'] = false;
		$options['enable_missed_schedule_posts_auto_publish'] = ( 'on' == $options['enable_missed_schedule_posts_auto_publish'] ? true : false );

		// =================================================================
		// ADMIN INTERFACE
		// =================================================================
		
		// Hide or Modify Elements / Clean Up Admin Bar

		if ( ! isset( $options['hide_modify_elements'] ) ) $options['hide_modify_elements'] = false;
		$options['hide_modify_elements'] = ( 'on' == $options['hide_modify_elements'] ? true : false );

		if ( ! isset( $options['hide_ab_wp_logo_menu'] ) ) $options['hide_ab_wp_logo_menu'] = false;
		$options['hide_ab_wp_logo_menu'] = ( 'on' == $options['hide_ab_wp_logo_menu'] ? true : false );

		if ( ! isset( $options['hide_ab_site_menu'] ) ) $options['hide_ab_site_menu'] = false;
		$options['hide_ab_site_menu'] = ( 'on' == $options['hide_ab_site_menu'] ? true : false );

		if ( ! isset( $options['hide_ab_customize_menu'] ) ) $options['hide_ab_customize_menu'] = false;
		$options['hide_ab_customize_menu'] = ( 'on' == $options['hide_ab_customize_menu'] ? true : false );

		if ( ! isset( $options['hide_ab_comments_menu'] ) ) $options['hide_ab_comments_menu'] = false;
		$options['hide_ab_comments_menu'] = ( 'on' == $options['hide_ab_comments_menu'] ? true : false );

		if ( ! isset( $options['hide_ab_updates_menu'] ) ) $options['hide_ab_updates_menu'] = false;
		$options['hide_ab_updates_menu'] = ( 'on' == $options['hide_ab_updates_menu'] ? true : false );

		if ( ! isset( $options['hide_ab_new_content_menu'] ) ) $options['hide_ab_new_content_menu'] = false;
		$options['hide_ab_new_content_menu'] = ( 'on' == $options['hide_ab_new_content_menu'] ? true : false );

		if ( ! isset( $options['hide_ab_howdy'] ) ) $options['hide_ab_howdy'] = false;
		$options['hide_ab_howdy'] = ( 'on' == $options['hide_ab_howdy'] ? true : false );

		if ( ! isset( $options['hide_help_drawer'] ) ) $options['hide_help_drawer'] = false;
		$options['hide_help_drawer'] = ( 'on' == $options['hide_help_drawer'] ? true : false );
		
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			foreach( $workable_nodes as $node_id => $node ) {
				if ( ! isset( $options['disabled_plugins_admin_bar_items'][$node_id] ) ) $options['disabled_plugins_admin_bar_items'][$node_id] = false;
				$options['disabled_plugins_admin_bar_items'][$node_id] = ( 'on' == $options['disabled_plugins_admin_bar_items'][$node_id] ? true : false );
			}
		}

		// Hide Admin Notices
		if ( ! isset( $options['hide_admin_notices'] ) ) $options['hide_admin_notices'] = false;
		$options['hide_admin_notices'] = ( 'on' == $options['hide_admin_notices'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['hide_admin_notices_for_nonadmins'] ) ) $options['hide_admin_notices_for_nonadmins'] = false;
			$options['hide_admin_notices_for_nonadmins'] = ( 'on' == $options['hide_admin_notices_for_nonadmins'] ? true : false );

			if ( ! isset( $options['hide_admin_notices_menu_for_nonadmins'] ) ) $options['hide_admin_notices_menu_for_nonadmins'] = false;
			$options['hide_admin_notices_menu_for_nonadmins'] = ( 'on' == $options['hide_admin_notices_menu_for_nonadmins'] ? true : false );
		}

		// Disable Dashboard Widgets
		if ( ! isset( $options['disable_dashboard_widgets'] ) ) $options['disable_dashboard_widgets'] = false;
		$options['disable_dashboard_widgets'] = ( 'on' == $options['disable_dashboard_widgets'] ? true : false );

		$dashboard_widgets = $options_extra['dashboard_widgets'];

		if ( is_array( $dashboard_widgets ) ) {
			foreach ( $dashboard_widgets as $widget ) {
				if ( ! isset( $options['disabled_dashboard_widgets'][$widget['id'].'__'.$widget['context'].'__'.$widget['priority']] ) ) $options['disabled_dashboard_widgets'][$widget['id'].'__'.$widget['context'].'__'.$widget['priority']] = false;
				$options['disabled_dashboard_widgets'][$widget['id'].'__'.$widget['context'].'__'.$widget['priority']] = ( 'on' == $options['disabled_dashboard_widgets'][$widget['id'].'__'.$widget['context'].'__'.$widget['priority']] ? true : false );
			}
		}

		if ( ! isset( $options['disable_welcome_panel_in_dashboard'] ) ) $options['disable_welcome_panel_in_dashboard'] = false;
		$options['disable_welcome_panel_in_dashboard'] = ( 'on' == $options['disable_welcome_panel_in_dashboard'] ? true : false );

		// Hide Admin Bar
		if ( ! isset( $options['hide_admin_bar'] ) ) $options['hide_admin_bar'] = false;
		$options['hide_admin_bar'] = ( 'on' == $options['hide_admin_bar'] ? true : false );

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
				// on the frontend
				if ( ! isset( $options['hide_admin_bar_for'][$role_slug] ) ) $options['hide_admin_bar_for'][$role_slug] = false;
				$options['hide_admin_bar_for'][$role_slug] = ( 'on' == $options['hide_admin_bar_for'][$role_slug] ? true : false );

				if ( ! isset( $options['hide_admin_bar_always_show_for_admins'] ) ) $options['hide_admin_bar_always_show_for_admins'] = false;
				$options['hide_admin_bar_always_show_for_admins'] = ( 'on' == $options['hide_admin_bar_always_show_for_admins'] ? true : false );

				if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
					// on the backend
					if ( ! isset( $options['hide_admin_bar_on_backend_for'][$role_slug] ) ) $options['hide_admin_bar_on_backend_for'][$role_slug] = false;
					$options['hide_admin_bar_on_backend_for'][$role_slug] = ( 'on' == $options['hide_admin_bar_on_backend_for'][$role_slug] ? true : false );
				}
			}
		}

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Admin logo

			if ( ! isset( $options['admin_logo'] ) ) $options['admin_logo'] = false;
			$options['admin_logo'] = ( 'on' == $options['admin_logo'] ? true : false );

			if ( ! isset( $options['admin_logo_location'] ) ) $options['admin_logo_location'] = 'admin_bar';
			$options['admin_logo_location'] = ( ! empty( $options['admin_logo_location'] ) ) ? sanitize_text_field( $options['admin_logo_location'] ) : 'admin_bar';

			if ( ! isset( $options['admin_logo_image'] ) ) $options['admin_logo_image'] = '';
			$options['admin_logo_image'] = ( ! empty( $options['admin_logo_image'] ) ) ? $options['admin_logo_image'] : '';

			if ( ! isset( $options['admin_logo_link_frontend'] ) ) $options['admin_logo_link_frontend'] = false;
			$options['admin_logo_link_frontend'] = ( 'on' == $options['admin_logo_link_frontend'] ? true : false );
		}
		
		// Wider Admin Menu

		if ( ! isset( $options['wider_admin_menu'] ) ) $options['wider_admin_menu'] = false;
		$options['wider_admin_menu'] = ( 'on' == $options['wider_admin_menu'] ? true : false );

		if ( ! isset( $options['admin_menu_width'] ) ) $options['admin_menu_width'] = 200;
		$options['admin_menu_width'] = ( ! empty( $options['admin_menu_width'] ) ) ? sanitize_text_field( $options['admin_menu_width'] ) : 200;

		// Admin Menu Organizer

		if ( ! isset( $options['customize_admin_menu'] ) ) $options['customize_admin_menu'] = false;
		$options['customize_admin_menu'] = ( 'on' == $options['customize_admin_menu'] ? true : false );
		
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Admin Columns Manager
			if ( ! isset( $options['admin_columns_manager'] ) ) $options['admin_columns_manager'] = false;
			$options['admin_columns_manager'] = ( 'on' == $options['admin_columns_manager'] ? true : false );
        }

		// Enhance List Tables
		if ( ! isset( $options['enhance_list_tables'] ) ) $options['enhance_list_tables'] = false;
		$options['enhance_list_tables'] = ( 'on' == $options['enhance_list_tables'] ? true : false );

		// Show Featured Image Column
		if ( ! isset( $options['show_featured_image_column'] ) ) $options['show_featured_image_column'] = false;
		$options['show_featured_image_column'] = ( 'on' == $options['show_featured_image_column'] ? true : false );

		// Show Excerpt Column
		if ( ! isset( $options['show_excerpt_column'] ) ) $options['show_excerpt_column'] = false;
		$options['show_excerpt_column'] = ( 'on' == $options['show_excerpt_column'] ? true : false );

		// Show Last Modified Column
		if ( ! isset( $options['show_last_modified_column'] ) ) $options['show_last_modified_column'] = false;
		$options['show_last_modified_column'] = ( 'on' == $options['show_last_modified_column'] ? true : false );

		// Show ID Column
		if ( ! isset( $options['show_id_column'] ) ) $options['show_id_column'] = false;
		$options['show_id_column'] = ( 'on' == $options['show_id_column'] ? true : false );

		// Show File Size Column in Media Library
		if ( ! isset( $options['show_file_size_column'] ) ) $options['show_file_size_column'] = false;
		$options['show_file_size_column'] = ( 'on' == $options['show_file_size_column'] ? true : false );

		// Show ID in Action Row
		if ( ! isset( $options['show_id_in_action_row'] ) ) $options['show_id_in_action_row'] = false;
		$options['show_id_in_action_row'] = ( 'on' == $options['show_id_in_action_row'] ? true : false );

		// Show Custom Taxonomy Filters
		if ( ! isset( $options['show_custom_taxonomy_filters'] ) ) $options['show_custom_taxonomy_filters'] = false;
		$options['show_custom_taxonomy_filters'] = ( 'on' == $options['show_custom_taxonomy_filters'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['show_custom_taxonomy_filters_non_hierarchical'] ) ) $options['show_custom_taxonomy_filters_non_hierarchical'] = false;
			$options['show_custom_taxonomy_filters_non_hierarchical'] = ( 'on' == $options['show_custom_taxonomy_filters_non_hierarchical'] ? true : false );
		}

		// Hide Date Column
		if ( ! isset( $options['hide_date_column'] ) ) $options['hide_date_column'] = false;
		$options['hide_date_column'] = ( 'on' == $options['hide_date_column'] ? true : false );

		// Hide Comments Column
		if ( ! isset( $options['hide_comments_column'] ) ) $options['hide_comments_column'] = false;
		$options['hide_comments_column'] = ( 'on' == $options['hide_comments_column'] ? true : false );

		// Hide Post Tags Column
		if ( ! isset( $options['hide_post_tags_column'] ) ) $options['hide_post_tags_column'] = false;
		$options['hide_post_tags_column'] = ( 'on' == $options['hide_post_tags_column'] ? true : false );

		// Custom Admin Footer Text

		if ( ! isset( $options['custom_admin_footer_text'] ) ) $options['custom_admin_footer_text'] = false;
		$options['custom_admin_footer_text'] = ( 'on' == $options['custom_admin_footer_text'] ? true : false );

		if ( ! isset( $options['custom_admin_footer_left'] ) ) $options['custom_admin_footer_left'] = '';
		$options['custom_admin_footer_left'] = ( ! empty( $options['custom_admin_footer_left'] ) ) ? wp_kses_post( $options['custom_admin_footer_left'] ) : '';

		if ( ! isset( $options['custom_admin_footer_right'] ) ) $options['custom_admin_footer_right'] = '';
		$options['custom_admin_footer_right'] = ( ! empty( $options['custom_admin_footer_right'] ) ) ? wp_kses_post( $options['custom_admin_footer_right'] ) : '';

		// Various Admin UI Enhancements

		if ( ! isset( $options['various_admin_ui_enhancements'] ) ) $options['various_admin_ui_enhancements'] = false;
		$options['various_admin_ui_enhancements'] = ( 'on' == $options['various_admin_ui_enhancements'] ? true : false );

		// Media Library Infinite Scrolling
		if ( ! isset( $options['media_library_infinite_scrolling'] ) ) $options['media_library_infinite_scrolling'] = false;
		$options['media_library_infinite_scrolling'] = ( 'on' == $options['media_library_infinite_scrolling'] ? true : false );

		// Display Active Plugins First
		if ( ! isset( $options['display_active_plugins_first'] ) ) $options['display_active_plugins_first'] = false;
		$options['display_active_plugins_first'] = ( 'on' == $options['display_active_plugins_first'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Preserve Taxonomy Hierarchy
			if ( ! isset( $options['preserve_taxonomy_hierarchy'] ) ) $options['preserve_taxonomy_hierarchy'] = false;
			$options['preserve_taxonomy_hierarchy'] = ( 'on' == $options['preserve_taxonomy_hierarchy'] ? true : false );			

			// Enable Dashboard Columns Settings
			if ( ! isset( $options['enable_dashboard_columns_settings'] ) ) $options['enable_dashboard_columns_settings'] = false;
			$options['enable_dashboard_columns_settings'] = ( 'on' == $options['enable_dashboard_columns_settings'] ? true : false );			

			// Add User Roles to Admin Body Classes
			if ( ! isset( $options['add_user_roles_to_admin_body_classes'] ) ) $options['add_user_roles_to_admin_body_classes'] = false;
			$options['add_user_roles_to_admin_body_classes'] = ( 'on' == $options['add_user_roles_to_admin_body_classes'] ? true : false );			

			// Add Username to Admin Body Classes
			if ( ! isset( $options['add_username_to_admin_body_classes'] ) ) $options['add_username_to_admin_body_classes'] = false;
			$options['add_username_to_admin_body_classes'] = ( 'on' == $options['add_username_to_admin_body_classes'] ? true : false );			

			// Open Admin Links in New Tab
			if ( ! isset( $options['open_admin_links_in_new_tab'] ) ) $options['open_admin_links_in_new_tab'] = false;
			$options['open_admin_links_in_new_tab'] = ( 'on' == $options['open_admin_links_in_new_tab'] ? true : false );			
		}

		// =================================================================
		// LOG IN/OUT | REGISTER
		// =================================================================

		// Change Login URL
		if ( ! isset( $options['change_login_url'] ) ) $options['change_login_url'] = false;
		$options['change_login_url'] = ( 'on' == $options['change_login_url'] ? true : false );

		if ( ! isset( $options['custom_login_slug'] ) ) $options['custom_login_slug'] = 'backend';
		$options['custom_login_slug'] = ( ! empty( $options['custom_login_slug'] ) ) ? sanitize_text_field( trim( $options['custom_login_slug'], '/' ) ) : 'backend';

		if ( ! isset( $options['custom_login_whitelist'] ) ) $options['custom_login_whitelist'] = '';
		$options['custom_login_whitelist'] = ( ! empty( $options['custom_login_whitelist'] ) ) ? sanitize_textarea_field( $options['custom_login_whitelist'] ) : '';			

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['default_login_redirect_slug'] ) ) $options['default_login_redirect_slug'] = 'not_found';
			$options['default_login_redirect_slug'] = ( ! empty( $options['default_login_redirect_slug'] ) ) ? sanitize_text_field( $options['default_login_redirect_slug'] ) : 'not_found';        	
        }

		// Login ID Type

		if ( ! isset( $options['login_id_type_restriction'] ) ) $options['login_id_type_restriction'] = false;
		$options['login_id_type_restriction'] = ( 'on' == $options['login_id_type_restriction'] ? true : false );

		if ( ! isset( $options['login_id_type'] ) ) $options['login_id_type'] = 'username';
		$options['login_id_type'] = ( ! empty( $options['login_id_type'] ) ) ? sanitize_text_field( $options['login_id_type'] ) : 'username';

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Login Page Customizer
			if ( ! isset( $options['login_page_customizer'] ) ) $options['login_page_customizer'] = false;
			$options['login_page_customizer'] = ( 'on' == $options['login_page_customizer'] ? true : false );
			
			if ( ! isset( $options['login_page_form_position'] ) ) $options['login_page_form_position'] = 'center';

			if ( ! isset( $options['login_page_form_color_scheme'] ) ) $options['login_page_form_color_scheme'] = 'light';
			$options['login_page_form_color_scheme'] = ( ! empty( $options['login_page_form_color_scheme'] ) ) ? $options['login_page_form_color_scheme'] : 'light';

			if ( ! isset( $options['login_page_form_section_color_bg'] ) ) $options['login_page_form_section_color_bg'] = '#1e73be';
			$options['login_page_form_section_color_bg'] = ( ! empty( $options['login_page_form_section_color_bg'] ) ) ? $options['login_page_form_section_color_bg'] : '#1e73be';

			if ( ! isset( $options['login_page_form_section_color_transparency'] ) ) $options['login_page_form_section_color_transparency'] = '0.8';
			$options['login_page_form_section_color_transparency'] = ( isset( $options['login_page_form_section_color_transparency'] ) ) ? sanitize_text_field( $options['login_page_form_section_color_transparency'] ) : '0.8';

			if ( ! isset( $options['login_page_logo_image_type'] ) ) $options['login_page_logo_image_type'] = 'custom';
			$options['login_page_logo_image_type'] = ( ! empty( $options['login_page_logo_image_type'] ) ) ? $options['login_page_logo_image_type'] : 'custom';

			if ( ! isset( $options['login_page_logo_image'] ) ) $options['login_page_logo_image'] = '';
			$options['login_page_logo_image'] = ( ! empty( $options['login_page_logo_image'] ) ) ? $options['login_page_logo_image'] : '';

			if ( ! isset( $options['login_page_logo_image_external'] ) ) $options['login_page_logo_image_external'] = '';
			$options['login_page_logo_image_external'] = ( ! empty( $options['login_page_logo_image_external'] ) ) ? $options['login_page_logo_image_external'] : '';

			if ( ! isset( $options['login_page_logo_image_attachment_id'] ) ) $options['login_page_logo_image_attachment_id'] = '';
			if ( ! isset( $options['login_page_logo_image_width_original'] ) ) $options['login_page_logo_image_width_original'] = '';
			if ( ! isset( $options['login_page_logo_image_height_original'] ) ) $options['login_page_logo_image_height_original'] = '';

			if ( ! isset( $options['login_page_logo_image_width'] ) ) $options['login_page_logo_image_width'] = '';
			$options['login_page_logo_image_width'] = ( ! empty( $options['login_page_logo_image_width'] ) ) ? $options['login_page_logo_image_width'] : '';

			if ( ! isset( $options['login_page_logo_image_height'] ) ) $options['login_page_logo_image_height'] = '';
			$options['login_page_logo_image_height'] = ( ! empty( $options['login_page_logo_image_height'] ) ) ? $options['login_page_logo_image_height'] : '';
			
			if ( ! isset( $options['login_page_background'] ) ) $options['login_page_background'] = 'pattern';
			$options['login_page_background'] = ( ! empty( $options['login_page_background'] ) ) ? $options['login_page_background'] : 'pattern';

			if ( ! isset( $options['login_page_background_pattern'] ) ) $options['login_page_background_pattern'] = 'blurry-gradient-blue';
			$options['login_page_background_pattern'] = ( ! empty( $options['login_page_background_pattern'] ) ) ? sanitize_text_field( $options['login_page_background_pattern'] ) : 'blurry-gradient-blue';

			if ( ! isset( $options['login_page_background_image'] ) ) $options['login_page_background_image'] = '';
			$options['login_page_background_image'] = ( ! empty( $options['login_page_background_image'] ) ) ? $options['login_page_background_image'] : '';

			if ( ! isset( $options['login_page_background_color'] ) ) $options['login_page_background_color'] = '#f0f0f1';
			$options['login_page_background_color'] = ( ! empty( $options['login_page_background_color'] ) ) ? $options['login_page_background_color'] : '#f0f0f1';

			if ( ! isset( $options['login_page_login_button_color'] ) ) $options['login_page_login_button_color'] = '#2271b1';
			$options['login_page_login_button_color'] = ( ! empty( $options['login_page_login_button_color'] ) ) ? $options['login_page_login_button_color'] : '#2271b1';

			if ( ! isset( $options['login_page_hide_remember_me'] ) ) $options['login_page_hide_remember_me'] = false;
			$options['login_page_hide_remember_me'] = ( 'on' == $options['login_page_hide_remember_me'] ? true : false );

			// if ( ! isset( $options['login_page_disable_registration'] ) ) $options['login_page_disable_registration'] = false;
			// $options['login_page_disable_registration'] = ( 'on' == $options['login_page_disable_registration'] ? true : false );
			if ( ! isset( $options['login_page_hide_registration_reset'] ) ) $options['login_page_hide_registration_reset'] = false;
			$options['login_page_hide_registration_reset'] = ( 'on' == $options['login_page_hide_registration_reset'] ? true : false );

			if ( ! isset( $options['login_page_hide_homepage_link'] ) ) $options['login_page_hide_homepage_link'] = false;
			$options['login_page_hide_homepage_link'] = ( 'on' == $options['login_page_hide_homepage_link'] ? true : false );

			if ( ! isset( $options['login_page_hide_language_switcher'] ) ) $options['login_page_hide_language_switcher'] = false;
			$options['login_page_hide_language_switcher'] = ( 'on' == $options['login_page_hide_language_switcher'] ? true : false );

			if ( ! isset( $options['login_page_external_css'] ) ) $options['login_page_external_css'] = '';
			$options['login_page_external_css'] = ( ! empty( $options['login_page_external_css'] ) ) ? sanitize_url( $options['login_page_external_css'] ) : '';			

			if ( ! isset( $options['login_page_custom_css'] ) ) $options['login_page_custom_css'] = '';
			$options['login_page_custom_css'] = ( ! empty( $options['login_page_custom_css'] ) ) ? $options['login_page_custom_css'] : '';
		}

		if ( ! bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Use Site Identity on the Login Page
			if ( ! isset( $options['site_identity_on_login'] ) ) $options['site_identity_on_login'] = false;
			$options['site_identity_on_login'] = ( 'on' == $options['site_identity_on_login'] ? true : false );			
		}

		// Enable Login Logout Menu
		if ( ! isset( $options['enable_login_logout_menu'] ) ) $options['enable_login_logout_menu'] = false;
		$options['enable_login_logout_menu'] = ( 'on' == $options['enable_login_logout_menu'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['custom_login_menu_label'] ) ) $options['custom_login_menu_label'] = '';
			$options['custom_login_menu_label'] = ( ! empty( $options['custom_login_menu_label'] ) ) ? sanitize_text_field( $options['custom_login_menu_label'] ) : '';			

			if ( ! isset( $options['custom_logout_menu_label'] ) ) $options['custom_logout_menu_label'] = '';
			$options['custom_logout_menu_label'] = ( ! empty( $options['custom_logout_menu_label'] ) ) ? sanitize_text_field( $options['custom_logout_menu_label'] ) : '';
		}

		// Redirect After Login
		if ( ! isset( $options['redirect_after_login'] ) ) $options['redirect_after_login'] = false;
		$options['redirect_after_login'] = ( 'on' == $options['redirect_after_login'] ? true : false );

		if ( ! bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['redirect_after_login_type'] ) ) $options['redirect_after_login_type'] = 'single_url';
			$options['redirect_after_login_type'] = ( ! empty( $options['redirect_after_login_type'] ) ) ? sanitize_text_field( $options['redirect_after_login_type'] ) : 'single_url';
		}

		if ( ! isset( $options['redirect_after_login_to_slug'] ) ) $options['redirect_after_login_to_slug'] = '';
		$options['redirect_after_login_to_slug'] = ( ! empty( $options['redirect_after_login_to_slug'] ) ) ? sanitize_text_field( $options['redirect_after_login_to_slug'] ) : '';

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
				if ( ! isset( $options['redirect_after_login_for'][$role_slug] ) ) $options['redirect_after_login_for'][$role_slug] = false;
				$options['redirect_after_login_for'][$role_slug] = ( 'on' == $options['redirect_after_login_for'][$role_slug] ? true : false );
			}
		}

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
					if ( ! isset( $options['redirect_after_login_for_separate_role'][$role_slug] ) ) $options['redirect_after_login_for_separate_role'][$role_slug] = false;
					$options['redirect_after_login_for_separate_role'][$role_slug] = ( 'on' == $options['redirect_after_login_for_separate_role'][$role_slug] ? true : false );

					if ( ! isset( $options['redirect_after_login_for_separate_slug'][$role_slug] ) ) $options['redirect_after_login_for_separate_slug'][$role_slug] = '';
					$options['redirect_after_login_for_separate_slug'][$role_slug] = ( ! empty( $options['redirect_after_login_for_separate_slug'][$role_slug] ) ? sanitize_text_field( $options['redirect_after_login_for_separate_slug'][$role_slug] ) : '' );
				}
			}
		}

		// Redirect After Logout
		if ( ! isset( $options['redirect_after_logout'] ) ) $options['redirect_after_logout'] = false;
		$options['redirect_after_logout'] = ( 'on' == $options['redirect_after_logout'] ? true : false );

		if ( ! bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['redirect_after_logout_type'] ) ) $options['redirect_after_logout_type'] = 'single_url';
			$options['redirect_after_logout_type'] = ( ! empty( $options['redirect_after_logout_type'] ) ) ? sanitize_text_field( $options['redirect_after_logout_type'] ) : 'single_url';
		}

		if ( ! isset( $options['redirect_after_logout_to_slug'] ) ) $options['redirect_after_logout_to_slug'] = '';
		$options['redirect_after_logout_to_slug'] = ( ! empty( $options['redirect_after_logout_to_slug'] ) ) ? sanitize_text_field( $options['redirect_after_logout_to_slug'] ) : '';

		if ( is_array( $roles ) ) {
			foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
				if ( ! isset( $options['redirect_after_logout_for'][$role_slug] ) ) $options['redirect_after_logout_for'][$role_slug] = false;
				$options['redirect_after_logout_for'][$role_slug] = ( 'on' == $options['redirect_after_logout_for'][$role_slug] ? true : false );
			}
		}

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
					if ( ! isset( $options['redirect_after_logout_for_separate_role'][$role_slug] ) ) $options['redirect_after_logout_for_separate_role'][$role_slug] = false;
					$options['redirect_after_logout_for_separate_role'][$role_slug] = ( 'on' == $options['redirect_after_logout_for_separate_role'][$role_slug] ? true : false );

					if ( ! isset( $options['redirect_after_logout_for_separate_slug'][$role_slug] ) ) $options['redirect_after_logout_for_separate_slug'][$role_slug] = '';
					$options['redirect_after_logout_for_separate_slug'][$role_slug] = ( ! empty( $options['redirect_after_logout_for_separate_slug'][$role_slug] ) ? sanitize_text_field( $options['redirect_after_logout_for_separate_slug'][$role_slug] ) : '' );
				}
			}
		}
		
		// Last Login Column
		if ( ! isset( $options['enable_last_login_column'] ) ) $options['enable_last_login_column'] = false;
		$options['enable_last_login_column'] = ( 'on' == $options['enable_last_login_column'] ? true : false );

		// Registration Date Column
		if ( ! isset( $options['registration_date_column'] ) ) $options['registration_date_column'] = false;
		$options['registration_date_column'] = ( 'on' == $options['registration_date_column'] ? true : false );

		// =================================================================
		// CUSTOM CODE
		// =================================================================

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Code Snippets Manager
			if ( ! isset( $options['enable_code_snippets_manager'] ) ) $options['enable_code_snippets_manager'] = false;
			$options['enable_code_snippets_manager'] = ( 'on' == $options['enable_code_snippets_manager'] ? true : false );

			if ( ! isset( $options['code_snippets_manager_flush_rewrite_rules_needed'] ) ) $options['code_snippets_manager_flush_rewrite_rules_needed'] = true;

			if ( ! isset( $options['code_snippets_editor_theme'] ) ) $options['code_snippets_editor_theme'] = 'dark';
			$options['code_snippets_editor_theme'] = ( ! empty( $options['code_snippets_editor_theme'] ) ) ? sanitize_text_field( $options['code_snippets_editor_theme'] ) : 'dark';		
		}

		// Enable Custom Admin CSS
		if ( ! isset( $options['enable_custom_admin_css'] ) ) $options['enable_custom_admin_css'] = false;
		$options['enable_custom_admin_css'] = ( 'on' == $options['enable_custom_admin_css'] ? true : false );

		if ( ! isset( $options['custom_admin_css'] ) ) $options['custom_admin_css'] = '';
		$options['custom_admin_css'] = ( ! empty( $options['custom_admin_css'] ) ) ? $options['custom_admin_css'] : '';

		// Enable Custom Frontend CSS
		if ( ! isset( $options['enable_custom_frontend_css'] ) ) $options['enable_custom_frontend_css'] = false;
		$options['enable_custom_frontend_css'] = ( 'on' == $options['enable_custom_frontend_css'] ? true : false );

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['custom_frontend_css_priority'] ) ) $options['custom_frontend_css_priority'] = 10;
			$options['custom_frontend_css_priority'] = ( ! empty( $options['custom_frontend_css_priority'] ) ) ? $options['custom_frontend_css_priority'] : 10;        	
        }

		if ( ! isset( $options['custom_frontend_css'] ) ) $options['custom_frontend_css'] = '';
		$options['custom_frontend_css'] = ( ! empty( $options['custom_frontend_css'] ) ) ? $options['custom_frontend_css'] : '';

		// Custom Body Class
		if ( ! isset( $options['enable_custom_body_class'] ) ) $options['enable_custom_body_class'] = false;
		$options['enable_custom_body_class'] = ( 'on' == $options['enable_custom_body_class'] ? true : false );

		if ( is_array( $asenha_public_post_types ) ) {
			foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				if ( ! isset( $options['enable_custom_body_class_for'][$post_type_slug] ) ) $options['enable_custom_body_class_for'][$post_type_slug] = false;
				$options['enable_custom_body_class_for'][$post_type_slug] = ( 'on' == $options['enable_custom_body_class_for'][$post_type_slug] ? true : false );
			}
		}

		// Manage ads.txt and app-ads.txt
		if ( ! isset( $options['manage_ads_appads_txt'] ) ) $options['manage_ads_appads_txt'] = false;
		$options['manage_ads_appads_txt'] = ( 'on' == $options['manage_ads_appads_txt'] ? true : false );

		if ( ! isset( $options['ads_txt_content'] ) ) $options['ads_txt_content'] = '';
		$options['ads_txt_content'] = ( ! empty( $options['ads_txt_content'] ) ) ? $options['ads_txt_content'] : '';

		if ( ! isset( $options['app_ads_txt_content'] ) ) $options['app_ads_txt_content'] = '';
		$options['app_ads_txt_content'] = ( ! empty( $options['app_ads_txt_content'] ) ) ? $options['app_ads_txt_content'] : '';

		// Manage robots.txt
		if ( ! isset( $options['manage_robots_txt'] ) ) $options['manage_robots_txt'] = false;
		$options['manage_robots_txt'] = ( 'on' == $options['manage_robots_txt'] ? true : false );

		if ( ! isset( $options['robots_txt_content'] ) ) { 
			$options['robots_txt_content'] = '';
		} else {
			if ( ! empty( $options['robots_txt_content'] ) ) {
				$options['robots_txt_content'] = $options['robots_txt_content'];
				$is_robots_txt_real_file = is_file( ABSPATH . 'robots.txt' );
				// rename real robots.txt file if it exists and Mange robots.txt is enabled
				if ( $is_robots_txt_real_file && ( 'on' == $options['manage_robots_txt'] ) ) {
					$robots_txt_backup_filename = 'robots_backup_' . date('Y_m_d__H_i', time()) . '.txt';
					$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
					$options_extra['robots_txt_backup_file_name'] = $robots_txt_backup_filename;
					update_option( ASENHA_SLUG_U. '_extra', $options_extra, true );
					rename( ABSPATH . 'robots.txt', ABSPATH . $robots_txt_backup_filename );
				} elseif (  'on' != $options['manage_robots_txt'] ) {
					$options_extra = get_option( ASENHA_SLUG_U. '_extra', array() );
					if ( array_key_exists( 'robots_txt_backup_file_name', $options_extra ) ) {
						if ( is_file( ABSPATH . $options_extra['robots_txt_backup_file_name'] ) ) {
							rename( ABSPATH . $options_extra['robots_txt_backup_file_name'], ABSPATH . 'robots.txt' );
						}
					}
				}
			} else {
				$options['robots_txt_content'] = '';
			}
		}

		// Insert <head>, <body> and <footer> code
		if ( ! isset( $options['insert_head_body_footer_code'] ) ) $options['insert_head_body_footer_code'] = false;
		$options['insert_head_body_footer_code'] = ( 'on' == $options['insert_head_body_footer_code'] ? true : false );

		if ( ! isset( $options['disable_code_unslash'] ) ) $options['disable_code_unslash'] = false;
		$options['disable_code_unslash'] = ( 'on' == $options['disable_code_unslash'] ? true : false );

		if ( ! isset( $options['head_code_priority'] ) ) $options['head_code_priority'] = 10;
		$options['head_code_priority'] = ( ! empty( $options['head_code_priority'] ) ) ? $options['head_code_priority'] : 10;

		if ( ! isset( $options['head_code'] ) ) $options['head_code'] = '';
		$options['head_code'] = ( ! empty( $options['head_code'] ) ) ? $common_methods->sanitize_html_js_css_code( $options['head_code'] ) : '';

		if ( ! isset( $options['body_code_priority'] ) ) $options['body_code_priority'] = 10;
		$options['body_code_priority'] = ( ! empty( $options['body_code_priority'] ) ) ? $options['body_code_priority'] : 10;

		if ( ! isset( $options['body_code'] ) ) $options['body_code'] = '';
		$options['body_code'] = ( ! empty( $options['body_code'] ) ) ? $common_methods->sanitize_html_js_css_code( $options['body_code'] ) : '';

		if ( ! isset( $options['footer_code_priority'] ) ) $options['footer_code_priority'] = 10;
		$options['footer_code_priority'] = ( ! empty( $options['footer_code_priority'] ) ) ? $options['footer_code_priority'] : 10;

		if ( ! isset( $options['footer_code'] ) ) $options['footer_code'] = '';
		$options['footer_code'] = ( ! empty( $options['footer_code'] ) ) ? $common_methods->sanitize_html_js_css_code( $options['footer_code'] ) : '';

		// =================================================================
		// DISABLE COMPONENTS
		// =================================================================

		// Disable Gutenberg
		if ( ! isset( $options['disable_gutenberg'] ) ) $options['disable_gutenberg'] = false;
		$options['disable_gutenberg'] = ( 'on' == $options['disable_gutenberg'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['disable_gutenberg_type'] ) ) $options['disable_gutenberg_type'] = 'only-on';
			$options['disable_gutenberg_type'] = ( ! empty( $options['disable_gutenberg_type'] ) ) ? sanitize_text_field( $options['disable_gutenberg_type'] ) : 'only-on';
		}

		if ( is_array( $asenha_gutenberg_post_types ) ) {
			foreach ( $asenha_gutenberg_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, 
				if ( ! isset( $options['disable_gutenberg_for'][$post_type_slug] ) ) $options['disable_gutenberg_for'][$post_type_slug] = false;
				$options['disable_gutenberg_for'][$post_type_slug] = ( 'on' == $options['disable_gutenberg_for'][$post_type_slug] ? true : false );
			}
		}

		if ( ! isset( $options['disable_gutenberg_frontend_styles'] ) ) $options['disable_gutenberg_frontend_styles'] = false;
		$options['disable_gutenberg_frontend_styles'] = ( 'on' == $options['disable_gutenberg_frontend_styles'] ? true : false );

		// Disable Comments
		if ( ! isset( $options['disable_comments'] ) ) $options['disable_comments'] = false;
		$options['disable_comments'] = ( 'on' == $options['disable_comments'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['disable_comments_type'] ) ) $options['disable_comments_type'] = 'only-on';
			$options['disable_comments_type'] = ( ! empty( $options['disable_comments_type'] ) ) ? sanitize_text_field( $options['disable_comments_type'] ) : 'only-on';
		}

		if ( is_array( $asenha_public_post_types ) ) {
			foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, $post_type_label is Posts
				if ( ! isset( $options['disable_comments_for'][$post_type_slug] ) ) $options['disable_comments_for'][$post_type_slug] = false;
				$options['disable_comments_for'][$post_type_slug] = ( 'on' == $options['disable_comments_for'][$post_type_slug] ? true : false );
			}
		}

		// Disable REST API
		if ( ! isset( $options['disable_rest_api'] ) ) $options['disable_rest_api'] = false;
		$options['disable_rest_api'] = ( 'on' == $options['disable_rest_api'] ? true : false );

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['disable_rest_api_excluded_routes'] ) ) $options['disable_rest_api_excluded_routes'] = '';
			$options['disable_rest_api_excluded_routes'] = ( ! empty( $options['disable_rest_api_excluded_routes'] ) ) ? sanitize_textarea_field( $options['disable_rest_api_excluded_routes'] ) : '';			

			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
					if ( ! isset( $options['enable_rest_api_for'][$role_slug] ) ) $options['enable_rest_api_for'][$role_slug] = false;
					$options['enable_rest_api_for'][$role_slug] = ( 'on' == $options['enable_rest_api_for'][$role_slug] ? true : false );
				}
			}
        }

		// Disable Feeds
		if ( ! isset( $options['disable_feeds'] ) ) $options['disable_feeds'] = false;
		$options['disable_feeds'] = ( 'on' == $options['disable_feeds'] ? true : false );

		// Disable Embeds
		if ( ! isset( $options['disable_embeds'] ) ) $options['disable_embeds'] = false;
		$options['disable_embeds'] = ( 'on' == $options['disable_embeds'] ? true : false );

		// Disable Auto Updates
		if ( ! isset( $options['disable_all_updates'] ) ) $options['disable_all_updates'] = false;
		$options['disable_all_updates'] = ( 'on' == $options['disable_all_updates'] ? true : false );

		// Disable Author Archives
		if ( ! isset( $options['disable_author_archives'] ) ) $options['disable_author_archives'] = false;
		$options['disable_author_archives'] = ( 'on' == $options['disable_author_archives'] ? true : false );

		// Disable Smaller Components

		if ( ! isset( $options['disable_smaller_components'] ) ) $options['disable_smaller_components'] = false;
		$options['disable_smaller_components'] = ( 'on' == $options['disable_smaller_components'] ? true : false );

		if ( ! isset( $options['disable_head_generator_tag'] ) ) $options['disable_head_generator_tag'] = false;
		$options['disable_head_generator_tag'] = ( 'on' == $options['disable_head_generator_tag'] ? true : false );

		if ( ! isset( $options['disable_feed_generator_tag'] ) ) $options['disable_feed_generator_tag'] = false;
		$options['disable_feed_generator_tag'] = ( 'on' == $options['disable_feed_generator_tag'] ? true : false );

		if ( ! isset( $options['disable_resource_version_number'] ) ) $options['disable_resource_version_number'] = false;
		$options['disable_resource_version_number'] = ( 'on' == $options['disable_resource_version_number'] ? true : false );

		if ( ! isset( $options['disable_head_wlwmanifest_tag'] ) ) $options['disable_head_wlwmanifest_tag'] = false;
		$options['disable_head_wlwmanifest_tag'] = ( 'on' == $options['disable_head_wlwmanifest_tag'] ? true : false );

		if ( ! isset( $options['disable_head_rsd_tag'] ) ) $options['disable_head_rsd_tag'] = false;
		$options['disable_head_rsd_tag'] = ( 'on' == $options['disable_head_rsd_tag'] ? true : false );

		if ( ! isset( $options['disable_head_shortlink_tag'] ) ) $options['disable_head_shortlink_tag'] = false;
		$options['disable_head_shortlink_tag'] = ( 'on' == $options['disable_head_shortlink_tag'] ? true : false );

		if ( ! isset( $options['disable_frontend_dashicons'] ) ) $options['disable_frontend_dashicons'] = false;
		$options['disable_frontend_dashicons'] = ( 'on' == $options['disable_frontend_dashicons'] ? true : false );

		if ( ! isset( $options['disable_emoji_support'] ) ) $options['disable_emoji_support'] = false;
		$options['disable_emoji_support'] = ( 'on' == $options['disable_emoji_support'] ? true : false );

		if ( ! isset( $options['disable_jquery_migrate'] ) ) $options['disable_jquery_migrate'] = false;
		$options['disable_jquery_migrate'] = ( 'on' == $options['disable_jquery_migrate'] ? true : false );

		if ( ! isset( $options['disable_block_widgets'] ) ) $options['disable_block_widgets'] = false;
		$options['disable_block_widgets'] = ( 'on' == $options['disable_block_widgets'] ? true : false );

		if ( ! isset( $options['disable_lazy_load'] ) ) $options['disable_lazy_load'] = false;
		$options['disable_lazy_load'] = ( 'on' == $options['disable_lazy_load'] ? true : false );

		if ( ! isset( $options['disable_application_passwords'] ) ) $options['disable_application_passwords'] = false;
		$options['disable_application_passwords'] = ( 'on' == $options['disable_application_passwords'] ? true : false );

		if ( ! isset( $options['disable_plugin_theme_editor'] ) ) $options['disable_plugin_theme_editor'] = false;
		$options['disable_plugin_theme_editor'] = ( 'on' == $options['disable_plugin_theme_editor'] ? true : false );

		// =================================================================
		// SECURITY
		// =================================================================

		// Limit Login Attempts
		if ( ! isset( $options['limit_login_attempts'] ) ) $options['limit_login_attempts'] = false;
		$options['limit_login_attempts'] = ( 'on' == $options['limit_login_attempts'] ? true : false );

		if ( ! isset( $options['login_fails_allowed'] ) ) $options['login_fails_allowed'] = 3;
		$options['login_fails_allowed'] = ( ! empty( $options['login_fails_allowed'] ) ) ? sanitize_text_field( $options['login_fails_allowed'] ) : 3;

		if ( ! isset( $options['login_lockout_maxcount'] ) ) $options['login_lockout_maxcount'] = 3;
		$options['login_lockout_maxcount'] = ( ! empty( $options['login_lockout_maxcount'] ) ) ? sanitize_text_field( $options['login_lockout_maxcount'] ) : 3;

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['limit_login_attempts_ip_whitelist'] ) ) $options['limit_login_attempts_ip_whitelist'] = '';
			$options['limit_login_attempts_ip_whitelist'] = ( ! empty( $options['limit_login_attempts_ip_whitelist'] ) ) ? sanitize_textarea_field( $options['limit_login_attempts_ip_whitelist'] ) : '';
		}

		if ( ! isset( $options['limit_login_attempts_header_override'] ) ) $options['limit_login_attempts_header_override'] = '';
		$options['limit_login_attempts_header_override'] = ( ! empty( $options['limit_login_attempts_header_override'] ) ) ? sanitize_text_field( $options['limit_login_attempts_header_override'] ) : '';			

		if ( ! isset( $options['login_attempts_log_table'] ) ) $options['login_attempts_log_table'] = '';
		$options['login_attempts_log_table'] = '';

		if ( ! isset( $options['limit_login_attempts'] ) ) {
			$options['failed_login_attempts_log_schedule_cleanup_by_amount'] = false;	
		}
		$options['failed_login_attempts_log_schedule_cleanup_by_amount'] = ( 'on' == $options['limit_login_attempts'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// CAPTCHA Protection
			if ( ! isset( $options['captcha_protection'] ) ) $options['captcha_protection'] = false;
			$options['captcha_protection'] = ( 'on' == $options['captcha_protection'] ? true : false );

			if ( ! isset( $options['captcha_wp_locations'] ) ) $options['captcha_wp_locations'] = array();
			$options['captcha_wp_locations'] = ( ! empty( $options['captcha_wp_locations'] ) ? $options['captcha_wp_locations'] : array() );

			if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
				if ( ! isset( $options['captcha_woo_locations'] ) ) $options['captcha_woo_locations'] = array();
				$options['captcha_woo_locations'] = ( ! empty( $options['captcha_woo_locations'] ) ? $options['captcha_woo_locations'] : array() );
			}

			if ( ! isset( $options['captcha_protection_types'] ) ) $options['captcha_protection_types'] = 'altcha';

			if ( ! isset( $options['altcha_secret_key'] ) ) $options['altcha_secret_key'] = '';
			$options['altcha_secret_key'] = ( ! empty( $options['altcha_secret_key'] ) ) ? sanitize_text_field( $options['altcha_secret_key'] ) : '';

			if ( ! isset( $options['altcha_widget'] ) ) $options['altcha_widget'] = 'checkbox';
			
			if ( ! isset( $options['altcha_complexity'] ) ) $options['altcha_complexity'] = 'medium';
			if ( ! isset( $options['altcha_expiration'] ) ) $options['altcha_expiration'] = '3600';
			if ( ! isset( $options['altcha_auto_verification'] ) ) $options['altcha_auto_verification'] = '';

			if ( ! isset( $options['altcha_enable_delay'] ) ) $options['altcha_enable_delay'] = true;
			$options['altcha_enable_delay'] = ( 'on' == $options['altcha_enable_delay'] ? true : false );

			if ( ! isset( $options['altcha_hide_logo'] ) ) $options['altcha_hide_logo'] = false;
			$options['altcha_hide_logo'] = ( 'on' == $options['altcha_hide_logo'] ? true : false );

			if ( ! isset( $options['altcha_hide_byline'] ) ) $options['altcha_hide_byline'] = true;
			$options['altcha_hide_byline'] = ( 'on' == $options['altcha_hide_byline'] ? true : false );

			if ( ! isset( $options['altcha_checkbox_label'] ) ) $options['altcha_checkbox_label'] = '';
			$options['altcha_checkbox_label'] = ( ! empty( $options['altcha_checkbox_label'] ) ) ? sanitize_text_field( $options['altcha_checkbox_label'] ) : '';

			if ( ! isset( $options['altcha_verifying_text'] ) ) $options['altcha_verifying_text'] = '';
			$options['altcha_verifying_text'] = ( ! empty( $options['altcha_verifying_text'] ) ) ? sanitize_text_field( $options['altcha_verifying_text'] ) : '';

			if ( ! isset( $options['altcha_verifying_wait_text'] ) ) $options['altcha_verifying_wait_text'] = '';
			$options['altcha_verifying_wait_text'] = ( ! empty( $options['altcha_verifying_wait_text'] ) ) ? sanitize_text_field( $options['altcha_verifying_wait_text'] ) : '';

			if ( ! isset( $options['altcha_verified_text'] ) ) $options['altcha_verified_text'] = '';
			$options['altcha_verified_text'] = ( ! empty( $options['altcha_verified_text'] ) ) ? sanitize_text_field( $options['altcha_verified_text'] ) : '';

			if ( ! isset( $options['altcha_verification_failed_text'] ) ) $options['altcha_verification_failed_text'] = '';
			$options['altcha_verification_failed_text'] = ( ! empty( $options['altcha_verification_failed_text'] ) ) ? sanitize_text_field( $options['altcha_verification_failed_text'] ) : '';

			if ( ! isset( $options['recaptcha_widget'] ) ) $options['recaptcha_widget'] = 'v2_checkbox';

			if ( ! isset( $options['recaptcha_site_key_v2_checkbox'] ) ) $options['recaptcha_site_key_v2_checkbox'] = '';
			$options['recaptcha_site_key_v2_checkbox'] = ( ! empty( $options['recaptcha_site_key_v2_checkbox'] ) ) ? sanitize_text_field( $options['recaptcha_site_key_v2_checkbox'] ) : '';

			if ( ! isset( $options['recaptcha_secret_key_v2_checkbox'] ) ) $options['recaptcha_secret_key_v2_checkbox'] = '';
			$options['recaptcha_secret_key_v2_checkbox'] = ( ! empty( $options['recaptcha_secret_key_v2_checkbox'] ) ) ? sanitize_text_field( $options['recaptcha_secret_key_v2_checkbox'] ) : '';

			if ( ! isset( $options['recaptcha_site_key_v3_invisible'] ) ) $options['recaptcha_site_key_v3_invisible'] = '';
			$options['recaptcha_site_key_v3_invisible'] = ( ! empty( $options['recaptcha_site_key_v3_invisible'] ) ) ? sanitize_text_field( $options['recaptcha_site_key_v3_invisible'] ) : '';

			if ( ! isset( $options['recaptcha_secret_key_v3_invisible'] ) ) $options['recaptcha_secret_key_v3_invisible'] = '';
			$options['recaptcha_secret_key_v3_invisible'] = ( ! empty( $options['recaptcha_secret_key_v3_invisible'] ) ) ? sanitize_text_field( $options['recaptcha_secret_key_v3_invisible'] ) : '';

			if ( ! isset( $options['turnstile_widget_theme'] ) ) $options['turnstile_widget_theme'] = 'light';

			if ( ! isset( $options['turnstile_site_key'] ) ) $options['turnstile_site_key'] = '';
			$options['turnstile_site_key'] = ( ! empty( $options['turnstile_site_key'] ) ) ? sanitize_text_field( $options['turnstile_site_key'] ) : '';

			if ( ! isset( $options['turnstile_secret_key'] ) ) $options['turnstile_secret_key'] = '';
			$options['turnstile_secret_key'] = ( ! empty( $options['turnstile_secret_key'] ) ) ? sanitize_text_field( $options['turnstile_secret_key'] ) : '';
		}
		
		// Obfuscate Author Slugs
		if ( ! isset( $options['obfuscate_author_slugs'] ) ) $options['obfuscate_author_slugs'] = false;
		$options['obfuscate_author_slugs'] = ( 'on' == $options['obfuscate_author_slugs'] ? true : false );

		// Obfuscate Email Address
		if ( ! isset( $options['obfuscate_email_address'] ) ) $options['obfuscate_email_address'] = false;
		$options['obfuscate_email_address'] = ( 'on' == $options['obfuscate_email_address'] ? true : false );

		if ( ! isset( $options['obfuscate_email_address_in_content'] ) ) $options['obfuscate_email_address_in_content'] = false;
		$options['obfuscate_email_address_in_content'] = ( 'on' == $options['obfuscate_email_address_in_content'] ? true : false );

		if ( ! isset( $options['obfuscate_email_address_visitor_only'] ) ) $options['obfuscate_email_address_visitor_only'] = false;
		$options['obfuscate_email_address_visitor_only'] = ( 'on' == $options['obfuscate_email_address_visitor_only'] ? true : false );

		// Disable XML-RPC
		if ( ! isset( $options['disable_xmlrpc'] ) ) $options['disable_xmlrpc'] = false;
		$options['disable_xmlrpc'] = ( 'on' == $options['disable_xmlrpc'] ? true : false );

		// =================================================================
		// OPTIMIZATIONS
		// =================================================================

		// Image Upload Control
		if ( ! isset( $options['image_upload_control'] ) ) $options['image_upload_control'] = false;
		$options['image_upload_control'] = ( 'on' == $options['image_upload_control'] ? true : false );

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['convert_to_jpg_quality'] ) ) $options['convert_to_jpg_quality'] = 82;
			$options['convert_to_jpg_quality'] = ( isset( $options['convert_to_jpg_quality'] ) && ! empty( $options['convert_to_jpg_quality'] ) ) ? $options['convert_to_jpg_quality'] : 82;

			if ( ! isset( $options['convert_to_webp'] ) ) $options['convert_to_webp'] = false;
			$options['convert_to_webp'] = ( 'on' == $options['convert_to_webp'] ? true : false );

			if ( ! isset( $options['convert_to_webp_quality'] ) ) $options['convert_to_webp_quality'] = 82;
			$options['convert_to_webp_quality'] = ( isset( $options['convert_to_webp_quality'] ) && ! empty( $options['convert_to_webp_quality'] ) ) ? $options['convert_to_webp_quality'] : 82;

			// if ( ! isset( $options['keep_original_image'] ) ) $options['keep_original_image'] = false;
			// $options['keep_original_image'] = ( 'on' == $options['keep_original_image'] ? true : false );

			if ( ! isset( $options['disable_image_conversion'] ) ) $options['disable_image_conversion'] = false;
			$options['disable_image_conversion'] = ( 'on' == $options['disable_image_conversion'] ? true : false );

			$registered_image_subsizes = wp_get_registered_image_subsizes();
			
			foreach ( $registered_image_subsizes as $image_subsize_slug => $image_subsize_info ) {
				if ( ! isset( $options['disabled_image_sizes'][$image_subsize_slug] ) ) $options['disabled_image_sizes'][$image_subsize_slug] = false;
				$options['disabled_image_sizes'][$image_subsize_slug] = ( 'on' == $options['disabled_image_sizes'][$image_subsize_slug] ? true : false );
			}
        }

		if ( ! isset( $options['image_max_width'] ) ) $options['image_max_width'] = 1920;
		$options['image_max_width'] = ( ! empty( $options['image_max_width'] ) ) ? sanitize_text_field( $options['image_max_width'] ) : 1920;

		if ( ! isset( $options['image_max_height'] ) ) $options['image_max_height'] = 1920;
		$options['image_max_height'] = ( ! empty( $options['image_max_height'] ) ) ? sanitize_text_field( $options['image_max_height'] ) : 1920;

		// Enable Revisions Control
		if ( ! isset( $options['enable_revisions_control'] ) ) $options['enable_revisions_control'] = false;
		$options['enable_revisions_control'] = ( 'on' == $options['enable_revisions_control'] ? true : false );

		if ( ! isset( $options['revisions_max_number'] ) ) $options['revisions_max_number'] = 10;
		$options['revisions_max_number'] = ( ! empty( $options['revisions_max_number'] ) ) ? sanitize_text_field( $options['revisions_max_number'] ) : 10;

		if ( is_array( $asenha_revisions_post_types ) ) {
			foreach ( $asenha_revisions_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, 
				if ( ! isset( $options['enable_revisions_control_for'][$post_type_slug] ) ) $options['enable_revisions_control_for'][$post_type_slug] = false;
				$options['enable_revisions_control_for'][$post_type_slug] = ( 'on' == $options['enable_revisions_control_for'][$post_type_slug] ? true : false );
			}
		}

		// Enable Heartbeat Control
		if ( ! isset( $options['enable_heartbeat_control'] ) ) $options['enable_heartbeat_control'] = false;
		$options['enable_heartbeat_control'] = ( 'on' == $options['enable_heartbeat_control'] ? true : false );

		if ( ! isset( $options['heartbeat_control_for_admin_pages'] ) ) $options['heartbeat_control_for_admin_pages'] = 'default';
		if ( ! isset( $options['heartbeat_control_for_post_edit'] ) ) $options['heartbeat_control_for_post_edit'] = 'default';
		if ( ! isset( $options['heartbeat_control_for_frontend'] ) ) $options['heartbeat_control_for_frontend'] = 'default';

		if ( ! isset( $options['heartbeat_interval_for_admin_pages'] ) ) $options['heartbeat_interval_for_admin_pages'] = 60;
		$options['heartbeat_interval_for_admin_pages'] = ( ! empty( $options['heartbeat_interval_for_admin_pages'] ) ) ? sanitize_text_field( $options['heartbeat_interval_for_admin_pages'] ) : 60;

		if ( ! isset( $options['heartbeat_interval_for_post_edit'] ) ) $options['heartbeat_interval_for_post_edit'] = 15;
		$options['heartbeat_interval_for_post_edit'] = ( ! empty( $options['heartbeat_interval_for_post_edit'] ) ) ? sanitize_text_field( $options['heartbeat_interval_for_post_edit'] ) : 15;

		if ( ! isset( $options['heartbeat_interval_for_frontend'] ) ) $options['heartbeat_interval_for_frontend'] = 60;
		$options['heartbeat_interval_for_frontend'] = ( ! empty( $options['heartbeat_interval_for_frontend'] ) ) ? sanitize_text_field( $options['heartbeat_interval_for_frontend'] ) : 60;

		// =================================================================
		// UTILITIES
		// =================================================================

		// SMTP Email Delivery
		if ( ! isset( $options['smtp_email_delivery'] ) ) $options['smtp_email_delivery'] = false;
		$options['smtp_email_delivery'] = ( 'on' == $options['smtp_email_delivery'] ? true : false );

		if ( ! isset( $options['smtp_host'] ) ) $options['smtp_host'] = '';
		$options['smtp_host'] = ( ! empty( $options['smtp_host'] ) ) ? sanitize_text_field( $options['smtp_host'] ) : '';

		if ( ! isset( $options['smtp_port'] ) ) $options['smtp_port'] = '';
		$options['smtp_port'] = ( ! empty( $options['smtp_port'] ) ) ? $options['smtp_port'] : '';

		if ( ! isset( $options['smtp_security'] ) ) $options['smtp_security'] = 'none';
		$options['smtp_security'] = ( ! empty( $options['smtp_security'] ) ) ? $options['smtp_security'] : 'none';

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['smtp_authentication'] ) ) $options['smtp_authentication'] = 'enable';
			$options['smtp_authentication'] = ( ! empty( $options['smtp_authentication'] ) ) ? $options['smtp_authentication'] : 'enable';			
		}

		if ( ! isset( $options['smtp_username'] ) ) $options['smtp_username'] = '';
		$options['smtp_username'] = ( ! empty( $options['smtp_username'] ) ) ? sanitize_text_field( $options['smtp_username'] ) : '';

		if ( ! isset( $options['smtp_password'] ) ) $options['smtp_password'] = '';
		$options['smtp_password'] = ( ! empty( $options['smtp_password'] ) ) ? $options['smtp_password'] : '';

		if ( ! isset( $options['smtp_default_from_name'] ) ) $options['smtp_default_from_name'] = '';
		$options['smtp_default_from_name'] = ( ! empty( $options['smtp_default_from_name'] ) ) ? sanitize_text_field( $options['smtp_default_from_name'] ) : '';

		if ( ! isset( $options['smtp_default_from_email'] ) ) $options['smtp_default_from_email'] = '';
		$options['smtp_default_from_email'] = ( ! empty( $options['smtp_default_from_email'] ) ) ? sanitize_text_field( $options['smtp_default_from_email'] ) : '';
		
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['smtp_replyto_name'] ) ) $options['smtp_replyto_name'] = '';
			$options['smtp_replyto_name'] = ( ! empty( $options['smtp_replyto_name'] ) ) ? sanitize_text_field( $options['smtp_replyto_name'] ) : '';

			if ( ! isset( $options['smtp_replyto_email'] ) ) $options['smtp_replyto_email'] = '';
			$options['smtp_replyto_email'] = ( ! empty( $options['smtp_replyto_email'] ) ) ? sanitize_text_field( $options['smtp_replyto_email'] ) : '';			

			if ( ! isset( $options['smtp_bcc_emails'] ) ) $options['smtp_bcc_emails'] = '';
			$options['smtp_bcc_emails'] = ( ! empty( $options['smtp_bcc_emails'] ) ) ? sanitize_text_field( $options['smtp_bcc_emails'] ) : '';			
		}

		if ( ! isset( $options['smtp_default_from_description'] ) ) $options['smtp_default_from_description'] = '';

		if ( ! isset( $options['smtp_force_from'] ) ) $options['smtp_force_from'] = false;
		$options['smtp_force_from'] = ( 'on' == $options['smtp_force_from'] ? true : false );

		if ( ! isset( $options['smtp_bypass_ssl_verification'] ) ) $options['smtp_bypass_ssl_verification'] = false;
		$options['smtp_bypass_ssl_verification'] = ( 'on' == $options['smtp_bypass_ssl_verification'] ? true : false );

		if ( ! isset( $options['smtp_debug'] ) ) $options['smtp_debug'] = false;
		$options['smtp_debug'] = ( 'on' == $options['smtp_debug'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['smtp_email_log'] ) ) {
				$options['smtp_email_log'] = false;
				$options['smtp_email_log_schedule_cleanup_by_amount'] = false;	
			}
			$options['smtp_email_log'] = ( 'on' == $options['smtp_email_log'] ? true : false );
			$options['smtp_email_log_schedule_cleanup_by_amount'] = ( 'on' == $options['smtp_email_log'] ? true : false );

			if ( ! isset( $options['smtp_email_log_entries_amount_to_keep'] ) ) $options['smtp_email_log_entries_amount_to_keep'] = 1000;
			$options['smtp_email_log_entries_amount_to_keep'] = ( ! empty( $options['smtp_email_log_entries_amount_to_keep'] ) ) ? intval( $options['smtp_email_log_entries_amount_to_keep'] ) : 1000;
		}

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			// Form Builder
			if ( ! isset( $options['form_builder'] ) ) $options['form_builder'] = false;
			$options['form_builder'] = ( 'on' == $options['form_builder'] ? true : false );

			if ( ! isset( $options['form_builder_email_template'] ) ) $options['form_builder_email_template'] = 'boxed-plain';

			if ( ! isset( $options['form_builder_email_header_image'] ) ) $options['form_builder_email_header_image'] = '';
			$options['form_builder_email_header_image'] = ( ! empty( $options['form_builder_email_header_image'] ) ) ? $options['form_builder_email_header_image'] : '';

			if ( ! isset( $options['form_builder_email_header_image_attachment_id'] ) ) $options['form_builder_email_header_image_attachment_id'] = '';

			if ( ! isset( $options['form_builder_email_custom_css'] ) ) $options['form_builder_email_custom_css'] = '';
			$options['form_builder_email_custom_css'] = ( ! empty( $options['form_builder_email_custom_css'] ) ) ? $options['form_builder_email_custom_css'] : '';

			if ( ! isset( $options['altcha_secret_key'] ) && ! isset( $options['form_builder_altcha_secret_key'] ) ) {
				$options['form_builder_altcha_secret_key'] = '';
			} elseif ( isset( $options['altcha_secret_key'] ) && ! isset( $options['form_builder_altcha_secret_key'] ) ) {
				$options['form_builder_altcha_secret_key'] = $options['altcha_secret_key'];
			}
			$options['form_builder_altcha_secret_key'] = ( ! empty( $options['form_builder_altcha_secret_key'] ) ) ? sanitize_text_field( $options['form_builder_altcha_secret_key'] ) : '';

			if ( ! isset( $options['form_builder_recaptcha_site_key_v2_checkbox'] ) ) $options['form_builder_recaptcha_site_key_v2_checkbox'] = '';
			$options['form_builder_recaptcha_site_key_v2_checkbox'] = ( ! empty( $options['form_builder_recaptcha_site_key_v2_checkbox'] ) ) ? sanitize_text_field( $options['form_builder_recaptcha_site_key_v2_checkbox'] ) : '';

			if ( ! isset( $options['form_builder_recaptcha_secret_key_v2_checkbox'] ) ) $options['form_builder_recaptcha_secret_key_v2_checkbox'] = '';
			$options['form_builder_recaptcha_secret_key_v2_checkbox'] = ( ! empty( $options['form_builder_recaptcha_secret_key_v2_checkbox'] ) ) ? sanitize_text_field( $options['form_builder_recaptcha_secret_key_v2_checkbox'] ) : '';

			if ( ! isset( $options['form_builder_recaptcha_site_key_v3_invisible'] ) ) $options['form_builder_recaptcha_site_key_v3_invisible'] = '';
			$options['form_builder_recaptcha_site_key_v3_invisible'] = ( ! empty( $options['form_builder_recaptcha_site_key_v3_invisible'] ) ) ? sanitize_text_field( $options['form_builder_recaptcha_site_key_v3_invisible'] ) : '';

			if ( ! isset( $options['form_builder_recaptcha_secret_key_v3_invisible'] ) ) $options['form_builder_recaptcha_secret_key_v3_invisible'] = '';
			$options['form_builder_recaptcha_secret_key_v3_invisible'] = ( ! empty( $options['form_builder_recaptcha_secret_key_v3_invisible'] ) ) ? sanitize_text_field( $options['form_builder_recaptcha_secret_key_v3_invisible'] ) : '';

			if ( ! isset( $options['form_builder_turnstile_site_key'] ) ) $options['form_builder_turnstile_site_key'] = '';
			$options['form_builder_turnstile_site_key'] = ( ! empty( $options['form_builder_turnstile_site_key'] ) ) ? sanitize_text_field( $options['form_builder_turnstile_site_key'] ) : '';

			if ( ! isset( $options['form_builder_turnstile_secret_key'] ) ) $options['form_builder_turnstile_secret_key'] = '';
			$options['form_builder_turnstile_secret_key'] = ( ! empty( $options['form_builder_turnstile_secret_key'] ) ) ? sanitize_text_field( $options['form_builder_turnstile_secret_key'] ) : '';
						
			// Local User Avatar
			if ( ! isset( $options['local_user_avatar'] ) ) $options['local_user_avatar'] = false;
			$options['local_user_avatar'] = ( 'on' == $options['local_user_avatar'] ? true : false );
		}
		
		// Multiple User Roles
		if ( ! isset( $options['multiple_user_roles'] ) ) $options['multiple_user_roles'] = false;
		$options['multiple_user_roles'] = ( 'on' == $options['multiple_user_roles'] ? true : false );

		// Image Sizes Panel
		if ( ! isset( $options['image_sizes_panel'] ) ) $options['image_sizes_panel'] = false;
		$options['image_sizes_panel'] = ( 'on' == $options['image_sizes_panel'] ? true : false );

		// View Admin as Role
		if ( ! isset( $options['view_admin_as_role'] ) ) $options['view_admin_as_role'] = false;
		$options['view_admin_as_role'] = ( 'on' == $options['view_admin_as_role'] ? true : false );

		// Enable Password Protection
		if ( ! isset( $options['enable_password_protection'] ) ) $options['enable_password_protection'] = false;
		$options['enable_password_protection'] = ( 'on' == $options['enable_password_protection'] ? true : false );

		if ( ! isset( $options['password_protection_password'] ) ) $options['password_protection_password'] = 'secret';
		$options['password_protection_password'] = ( ! empty( $options['password_protection_password'] ) ) ? $options['password_protection_password'] : 'secret';

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['password_protection_ip_whitelist'] ) ) $options['password_protection_ip_whitelist'] = '';
			$options['password_protection_ip_whitelist'] = ( ! empty( $options['password_protection_ip_whitelist'] ) ) ? sanitize_textarea_field( $options['password_protection_ip_whitelist'] ) : '';

			if ( ! isset( $options['password_protection_header_override'] ) ) $options['password_protection_header_override'] = '';
			$options['password_protection_header_override'] = ( ! empty( $options['password_protection_header_override'] ) ) ? sanitize_text_field( $options['password_protection_header_override'] ) : '';

			if ( ! isset( $options['password_protection_password_field_label'] ) ) $options['password_protection_password_field_label'] = '';
			$options['password_protection_password_field_label'] = ( ! empty( $options['password_protection_password_field_label'] ) ) ? sanitize_text_field( $options['password_protection_password_field_label'] ) : '';

			if ( ! isset( $options['password_protection_button_label'] ) ) $options['password_protection_button_label'] = '';
			$options['password_protection_button_label'] = ( ! empty( $options['password_protection_button_label'] ) ) ? sanitize_text_field( $options['password_protection_button_label'] ) : '';
		}

		// Maintenance Mode
		if ( ! isset( $options['maintenance_mode'] ) ) $options['maintenance_mode'] = false;
		$options['maintenance_mode'] = ( 'on' == $options['maintenance_mode'] ? true : false );

		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['maintenance_page_type'] ) ) $options['maintenance_page_type'] = 'custom';
			$options['maintenance_page_type'] = ( ! empty( $options['maintenance_page_type'] ) ) ? sanitize_text_field( $options['maintenance_page_type'] ) : 'custom';

			if ( ! isset( $options['maintenance_page_title'] ) ) $options['maintenance_page_title'] = 'Under maintenance';
			$options['maintenance_page_title'] = ( ! empty( $options['maintenance_page_title'] ) ) ? sanitize_text_field( $options['maintenance_page_title'] ) : 'Under maintenance';			
		}

		if ( ! isset( $options['maintenance_page_heading'] ) ) $options['maintenance_page_heading'] = 'We\'ll be back soon.';
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$options['maintenance_page_heading'] = ( ! empty( $options['maintenance_page_heading'] ) ) ? wp_kses_post( wpautop( $options['maintenance_page_heading'] ) ) : 'We\'ll be back soon.';
		} else {
			$options['maintenance_page_heading'] = ( ! empty( $options['maintenance_page_heading'] ) ) ? sanitize_text_field( $options['maintenance_page_heading'] ) : 'We\'ll be back soon.';
		}

		if ( ! isset( $options['maintenance_page_description'] ) ) $options['maintenance_page_description'] = 'This site is undergoing maintenance for an extended period today. Thanks for your patience.';
		if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			$options['maintenance_page_description'] = ( ! empty( $options['maintenance_page_description'] ) ) ? wp_kses_post( wpautop( $options['maintenance_page_description'] ) ) : 'This site is undergoing maintenance for an extended period today. Thanks for your patience.';		
		} else {
			$options['maintenance_page_description'] = ( ! empty( $options['maintenance_page_description'] ) ) ? sanitize_text_field( $options['maintenance_page_description'] ) : 'This site is undergoing maintenance for an extended period today. Thanks for your patience.';			
		}

		if ( ! isset( $options['maintenance_page_background'] ) ) $options['maintenance_page_background'] = 'stripes';
		$options['maintenance_page_background'] = ( ! empty( $options['maintenance_page_background'] ) ) ? $options['maintenance_page_background'] : 'stripes';

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['maintenance_page_background_pattern'] ) ) $options['maintenance_page_background_pattern'] = 'blurry-gradient-blue';
			$options['maintenance_page_background_pattern'] = ( ! empty( $options['maintenance_page_background_pattern'] ) ) ? sanitize_text_field( $options['maintenance_page_background_pattern'] ) : 'blurry-gradient-blue';

			if ( ! isset( $options['maintenance_page_background_image'] ) ) $options['maintenance_page_background_image'] = '';
			$options['maintenance_page_background_image'] = ( ! empty( $options['maintenance_page_background_image'] ) ) ? $options['maintenance_page_background_image'] : '';

			if ( ! isset( $options['maintenance_page_background_color'] ) ) $options['maintenance_page_background_color'] = '#ffffff';
			$options['maintenance_page_background_color'] = ( ! empty( $options['maintenance_page_background_color'] ) ) ? $options['maintenance_page_background_color'] : '#ffffff';	

			if ( ! isset( $options['maintenance_page_head_code'] ) ) $options['maintenance_page_head_code'] = '';
			$options['maintenance_page_head_code'] = ( ! empty( $options['maintenance_page_head_code'] ) ) ? $options['maintenance_page_head_code'] : '';

			if ( ! isset( $options['maintenance_page_custom_css'] ) ) $options['maintenance_page_custom_css'] = '';
			$options['maintenance_page_custom_css'] = ( ! empty( $options['maintenance_page_custom_css'] ) ) ? $options['maintenance_page_custom_css'] : '';

			if ( ! isset( $options['maintenance_page_slug'] ) ) $options['maintenance_page_slug'] = '';
			$options['maintenance_page_slug'] = ( ! empty( $options['maintenance_page_slug'] ) ) ? sanitize_text_field( trim( $options['maintenance_page_slug'], '/' ) ) : '';

			if ( ! isset( $options['maintenance_page_exclude_urls'] ) ) $options['maintenance_page_exclude_urls'] = '';
			$options['maintenance_page_exclude_urls'] = ( ! empty( $options['maintenance_page_exclude_urls'] ) ) ? sanitize_textarea_field( $options['maintenance_page_exclude_urls'] ) : '';			

			if ( is_array( $roles ) ) {
				foreach ( $roles as $role_slug => $role_label ) { // e.g. $role_slug is administrator, $role_label is Administrator
					if ( 'administrator' != $role_slug ) {
						if ( ! isset( $options['maintenance_mode_access_for'][$role_slug] ) ) $options['maintenance_mode_access_for'][$role_slug] = false;
						$options['maintenance_mode_access_for'][$role_slug] = ( 'on' == $options['maintenance_mode_access_for'][$role_slug] ? true : false );
					}
				}
			}

        }

		if ( ! isset( $options['maintenance_mode_description'] ) ) $options['maintenance_mode_description'] = '';

		// Redirect 404 to Homepage
		if ( ! isset( $options['redirect_404_to_homepage'] ) ) $options['redirect_404_to_homepage'] = false;
		$options['redirect_404_to_homepage'] = ( 'on' == $options['redirect_404_to_homepage'] ? true : false );

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['redirect_404_to_slug'] ) ) $options['redirect_404_to_slug'] = '';
			$options['redirect_404_to_slug'] = ( ! empty( $options['redirect_404_to_slug'] ) ) ? sanitize_text_field( $options['redirect_404_to_slug'] ) : '';        	
        }

		// Show System Summary in At a Glance Dashboard Widget
		if ( ! isset( $options['display_system_summary'] ) ) $options['display_system_summary'] = false;
		$options['display_system_summary'] = ( 'on' == $options['display_system_summary'] ? true : false );

		// Search Engines Visibility Status
		if ( ! isset( $options['search_engine_visibility_status'] ) ) $options['search_engine_visibility_status'] = false;
		$options['search_engine_visibility_status'] = ( 'on' == $options['search_engine_visibility_status'] ? true : false );

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
			if ( ! isset( $options['live_site_url'] ) ) $options['live_site_url'] = '';
			$options['live_site_url'] = ( ! empty( $options['live_site_url'] ) ) ? sanitize_url( rtrim( trim( $options['live_site_url'] ), '/' ) ) : '';
        }

		return $options;

	}

	/**
	 * Sanitize checkbox field. For reference purpose. Not currently in use.
	 *
	 * @since 1.0.0
	 */
	function asenha_sanitize_checkbox_field( $value ) {

		// A checked checkbox field will originally be saved as an 'on' value in the option. We transform that into true (shown as 1) or false (shown as empty value)
		return 'on' === $value ? true : false;

	}

}