<?php

global $post;
$meta = get_post_custom( $post->ID );

// BASIC
$cpt_singular_name 		= isset( $meta['cpt_singular_name'] ) ? $meta['cpt_singular_name'][0] : '';
$cpt_plural_name 		= isset( $meta['cpt_plural_name'] ) ? $meta['cpt_plural_name'][0] : '';
$cpt_key 				= isset( $meta['cpt_key'] ) ? $meta['cpt_key'][0] : '';
$cpt_description 		= isset( $meta['cpt_description'] ) ? $meta['cpt_description'][0] : '';
$cpt_capability_type 	= isset( $meta['cpt_capability_type'] ) ? $meta['cpt_capability_type'][0] : 'post';
$cpt_capability_type_custom_slug_singular = isset( $meta['cpt_capability_type_custom_slug_singular'] ) ? $meta['cpt_capability_type_custom_slug_singular'][0] : '';
$cpt_capability_type_custom_slug_plural = isset( $meta['cpt_capability_type_custom_slug_plural'] ) ? $meta['cpt_capability_type_custom_slug_plural'][0] : '';
$cpt_map_meta_cap 		=  isset( $meta['cpt_map_meta_cap'] ) ? $meta['cpt_map_meta_cap'][0] : false;
$cpt_public 			= isset( $meta['cpt_public'] ) ? $meta['cpt_public'][0] : true;
$cpt_hierarchical 		= isset( $meta['cpt_hierarchical'] ) ? $meta['cpt_hierarchical'][0] : false;

// SUPPORTS & TAXONOMIES
$cpt_supports                 = isset( $meta['cpt_supports'] ) ? maybe_unserialize( $meta['cpt_supports'][0] ) : array( 'title', 'editor' );
$cpt_supports_title           = ( ! empty ( $cpt_supports ) && in_array( 'title', $cpt_supports ) ? 'title' : '' );
$cpt_supports_editor          = ( ! empty ( $cpt_supports ) && in_array( 'editor', $cpt_supports ) ? 'editor' : '' );
$cpt_supports_author          = ( ! empty ( $cpt_supports ) && in_array( 'author', $cpt_supports ) ? 'author' : '' );
$cpt_supports_featured_image  = ( ! empty ( $cpt_supports ) && in_array( 'thumbnail', $cpt_supports ) ? 'thumbnail' : '' );
$cpt_supports_excerpt         = ( ! empty ( $cpt_supports ) && in_array( 'excerpt', $cpt_supports ) ? 'excerpt' : '' );
$cpt_supports_trackbacks      = ( ! empty ( $cpt_supports ) && in_array( 'trackbacks', $cpt_supports ) ? 'trackbacks' : '' );
$cpt_supports_custom_fields   = ( ! empty ( $cpt_supports ) && in_array( 'custom-fields', $cpt_supports ) ? 'custom-fields' : '' );
$cpt_supports_comments        = ( ! empty ( $cpt_supports ) && in_array( 'comments', $cpt_supports ) ? 'comments' : '' );
$cpt_supports_revisions       = ( ! empty ( $cpt_supports ) && in_array( 'revisions', $cpt_supports ) ? 'revisions' : '' );
$cpt_supports_page_attributes = ( ! empty ( $cpt_supports ) && in_array( 'page-attributes', $cpt_supports ) ? 'page-attributes' : '' );
$cpt_supports_post_formats    = ( ! empty ( $cpt_supports ) && in_array( 'post-formats', $cpt_supports ) ? 'post-formats' : '' );
$cpt_supports_none    		  = ( ! empty ( $cpt_supports ) && in_array( 'none', $cpt_supports ) ? 'none' : '' );

$cpt_taxonomies               = isset( $meta['cpt_taxonomies'] ) ? maybe_unserialize( $meta['cpt_taxonomies'][0] ) : array();

// ADMIN
$cpt_show_ui 			= isset( $meta['cpt_show_ui'] ) ? $meta['cpt_show_ui'][0] : $cpt_public;
$cpt_show_in_menu 		= isset( $meta['cpt_show_in_menu'] ) ? $meta['cpt_show_in_menu'][0] : $cpt_show_ui;
$cpt_menu_icon 			= isset( $meta['cpt_menu_icon'] ) ? $meta['cpt_menu_icon'][0] : 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik0yLjU4NiAxMS40MTRhMiAyIDAgMCAxIDAtMi44MjhsNi4wMDItNmEyIDIgMCAwIDEgMi44MjggMGw2LjAwMiA2YTIgMiAwIDAgMSAwIDIuODI4bC02LjAwMiA2YTIgMiAwIDAgMS0yLjgyOCAwbC02LjAwMi02WiI+PC9wYXRoPjwvc3ZnPg==';
$cpt_show_in_admin_bar 	= isset( $meta['cpt_show_in_admin_bar'] ) ? $meta['cpt_show_in_admin_bar'][0] : $cpt_show_in_menu;
$cpt_show_in_nav_menus 	= isset( $meta['cpt_show_in_nav_menus'] ) ? $meta['cpt_show_in_nav_menus'][0] : $cpt_public;
$cpt_can_export 		= isset( $meta['cpt_can_export'] ) ? $meta['cpt_can_export'][0] : true;
$cpt_delete_with_user	= isset( $meta['cpt_delete_with_user'] ) ? $meta['cpt_delete_with_user'][0] : null;

// FRONT END
$cpt_publicly_queryable 	= isset( $meta['cpt_publicly_queryable'] ) ? $meta['cpt_publicly_queryable'][0] : $cpt_public;
$cpt_query_var 				= isset( $meta['cpt_query_var'] ) ? $meta['cpt_query_var'][0] : true;
$cpt_use_custom_query_var_string = isset( $meta['cpt_use_custom_query_var_string'] ) ? $meta['cpt_use_custom_query_var_string'][0] : false;
$cpt_query_var_string 		= isset( $meta['cpt_query_var_string'] ) ? $meta['cpt_query_var_string'][0] : '';
$cpt_exclude_from_search 	= isset( $meta['cpt_exclude_from_search'] ) ? $meta['cpt_exclude_from_search'][0] : ! $cpt_public;
$cpt_has_archive 			= isset( $meta['cpt_has_archive'] ) ? $meta['cpt_has_archive'][0] : false;
$cpt_use_custom_archive_slug = isset( $meta['cpt_use_custom_archive_slug'] ) ? $meta['cpt_use_custom_archive_slug'][0] : false;
$cpt_has_archive_custom_slug = isset( $meta['cpt_has_archive_custom_slug'] ) ? $meta['cpt_has_archive_custom_slug'][0] : '';
$cpt_rewrite 				= isset( $meta['cpt_rewrite'] ) ? $meta['cpt_rewrite'][0] : true;
$cpt_use_custom_rewrite_slug = isset( $meta['cpt_use_custom_rewrite_slug'] ) ? $meta['cpt_use_custom_rewrite_slug'][0] : false;
$cpt_rewrite_custom_slug 	= isset( $meta['cpt_rewrite_custom_slug'] ) ? $meta['cpt_rewrite_custom_slug'][0] : '';
$cpt_with_front 			= isset( $meta['cpt_with_front'] ) ? $meta['cpt_with_front'][0] : true;
$cpt_feeds 					= isset( $meta['cpt_feeds'] ) ? $meta['cpt_feeds'][0] : $cpt_has_archive;
$cpt_pages 					= isset( $meta['cpt_pages'] ) ? $meta['cpt_pages'][0] : true;
$cpt_ep_mask 				= isset( $meta['cpt_ep_mask'] ) ? $meta['cpt_ep_mask'][0] : false;
$cpt_ep_mask_custom 		= isset( $meta['cpt_ep_mask_custom'] ) ? $meta['cpt_ep_mask_custom'][0] : '';

// REST API
$cpt_show_in_rest 			= isset( $meta['cpt_show_in_rest'] ) ? $meta['cpt_show_in_rest'][0] : true;
$cpt_rest_base 				= isset( $meta['cpt_rest_base'] ) ? $meta['cpt_rest_base'][0] : '';
$cpt_rest_namespace 		= isset( $meta['cpt_rest_namespace'] ) ? $meta['cpt_rest_namespace'][0] : '';
$cpt_rest_controller_class 	= isset( $meta['cpt_rest_controller_class'] ) ? $meta['cpt_rest_controller_class'][0] : '';

// LABELS
$cpt_label_add_new 			= isset( $meta['cpt_label_add_new'] ) ? $meta['cpt_label_add_new'][0] : 'Add New';
$cpt_label_add_new_item 	= isset( $meta['cpt_label_add_new_item'] ) ? $meta['cpt_label_add_new_item'][0] : '';
$cpt_label_edit_item 		= isset( $meta['cpt_label_edit_item'] ) ? $meta['cpt_label_edit_item'][0] : '';
$cpt_label_new_item 		= isset( $meta['cpt_label_new_item'] ) ? $meta['cpt_label_new_item'][0] : '';
$cpt_label_view_item 		= isset( $meta['cpt_label_view_item'] ) ? $meta['cpt_label_view_item'][0] : '';
$cpt_label_view_items 		= isset( $meta['cpt_label_view_items'] ) ? $meta['cpt_label_view_items'][0] : '';
$cpt_label_search_items 	= isset( $meta['cpt_label_search_items'] ) ? $meta['cpt_label_search_items'][0] : '';
$cpt_label_not_found 		= isset( $meta['cpt_label_not_found'] ) ? $meta['cpt_label_not_found'][0] : '';
$cpt_label_not_found_in_trash = isset( $meta['cpt_label_not_found_in_trash'] ) ? $meta['cpt_label_not_found_in_trash'][0] : '';
$cpt_label_parent_item_colon = isset( $meta['cpt_label_parent_item_colon'] ) ? $meta['cpt_label_parent_item_colon'][0] : '';
$cpt_label_all_items 		= isset( $meta['cpt_label_all_items'] ) ? $meta['cpt_label_all_items'][0] : '';
$cpt_label_archives 		= isset( $meta['cpt_label_archives'] ) ? $meta['cpt_label_archives'][0] : '';
$cpt_label_attributes 		= isset( $meta['cpt_label_attributes'] ) ? $meta['cpt_label_attributes'][0] : '';
$cpt_label_insert_into_item = isset( $meta['cpt_label_insert_into_item'] ) ? $meta['cpt_label_insert_into_item'][0] : '';
$cpt_label_uploaded_to_this_item = isset( $meta['cpt_label_uploaded_to_this_item'] ) ? $meta['cpt_label_uploaded_to_this_item'][0] : '';
$cpt_label_featured_image 	= isset( $meta['cpt_label_featured_image'] ) ? $meta['cpt_label_featured_image'][0] : 'Featured image';
$cpt_label_set_featured_image = isset( $meta['cpt_label_set_featured_image'] ) ? $meta['cpt_label_set_featured_image'][0] : 'Set featured image';
$cpt_label_remove_featured_image = isset( $meta['cpt_label_remove_featured_image'] ) ? $meta['cpt_label_remove_featured_image'][0] : 'Remove featured image';
$cpt_label_use_featured_image = isset( $meta['cpt_label_use_featured_image'] ) ? $meta['cpt_label_use_featured_image'][0] : 'Use as featured image';
$cpt_label_menu_name 		= isset( $meta['cpt_label_menu_name'] ) ? $meta['cpt_label_menu_name'][0] : '';
$cpt_label_filter_items_list = isset( $meta['cpt_label_filter_items_list'] ) ? $meta['cpt_label_filter_items_list'][0] : '';
$cpt_label_filter_by_date 	= isset( $meta['cpt_label_filter_by_date'] ) ? $meta['cpt_label_filter_by_date'][0] : 'Filter by date';
$cpt_label_items_list_navigation = isset( $meta['cpt_label_items_list_navigation'] ) ? $meta['cpt_label_items_list_navigation'][0] : '';
$cpt_label_items_list 		= isset( $meta['cpt_label_items_list'] ) ? $meta['cpt_label_items_list'][0] : '';
$cpt_label_item_published 	= isset( $meta['cpt_label_item_published'] ) ? $meta['cpt_label_item_published'][0] : '';
$cpt_label_item_published_privately = isset( $meta['cpt_label_item_published_privately'] ) ? $meta['cpt_label_item_published_privately'][0] : '';
$cpt_label_item_reverted_to_draft = isset( $meta['cpt_label_item_reverted_to_draft'] ) ? $meta['cpt_label_item_reverted_to_draft'][0] : '';
$cpt_label_item_scheduled 	= isset( $meta['cpt_label_item_scheduled'] ) ? $meta['cpt_label_item_scheduled'][0] : '';
$cpt_label_item_updated 	= isset( $meta['cpt_label_item_updated'] ) ? $meta['cpt_label_item_updated'][0] : '';
$cpt_label_item_link 		= isset( $meta['cpt_label_item_link'] ) ? $meta['cpt_label_item_link'][0] : '';
$cpt_label_item_link_description = isset( $meta['cpt_label_item_link_description'] ) ? $meta['cpt_label_item_link_description'][0] : '';