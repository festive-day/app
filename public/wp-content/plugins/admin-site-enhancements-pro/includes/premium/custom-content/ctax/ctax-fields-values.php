<?php

global $post;
$meta = get_post_custom( $post->ID );

// BASIC
$ctax_singular_name 		= isset( $meta['ctax_singular_name'] ) ? $meta['ctax_singular_name'][0] : '';
$ctax_plural_name 			= isset( $meta['ctax_plural_name'] ) ? $meta['ctax_plural_name'][0] : '';
$ctax_key 					= isset( $meta['ctax_key'] ) ? $meta['ctax_key'][0] : '';
$ctax_description 			= isset( $meta['ctax_description'] ) ? $meta['ctax_description'][0] : '';
$ctax_public 				= isset( $meta['ctax_public'] ) ? $meta['ctax_public'][0] : true;
$ctax_hierarchical 			= isset( $meta['ctax_hierarchical'] ) ? $meta['ctax_hierarchical'][0] : false;

// POST TYPES
$ctax_post_types			= isset( $meta['ctax_post_types'] ) ? maybe_unserialize( $meta['ctax_post_types'][0] ) : array();

// ADMIN
$ctax_show_ui 				= isset( $meta['ctax_show_ui'] ) ? $meta['ctax_show_ui'][0] : $ctax_public;
$ctax_show_in_menu 			= isset( $meta['ctax_show_in_menu'] ) ? $meta['ctax_show_in_menu'][0] : $ctax_show_ui;
$ctax_show_in_nav_menus 	= isset( $meta['ctax_show_in_nav_menus'] ) ? $meta['ctax_show_in_nav_menus'][0] : $ctax_public;
$ctax_show_tagcloud 		= isset( $meta['ctax_show_tagcloud'] ) ? $meta['ctax_show_tagcloud'][0] : $ctax_show_ui;
$ctax_show_in_quick_edit	= isset( $meta['ctax_show_in_quick_edit'] ) ? $meta['ctax_show_in_quick_edit'][0] : $ctax_show_ui;
$ctax_show_admin_column		= isset( $meta['ctax_show_admin_column'] ) ? $meta['ctax_show_admin_column'][0] : false;

// FRONT END
$ctax_publicly_queryable 		= isset( $meta['ctax_publicly_queryable'] ) ? $meta['ctax_publicly_queryable'][0] : $ctax_public;
$ctax_query_var 				= isset( $meta['ctax_query_var'] ) ? $meta['ctax_query_var'][0] : true;
$ctax_use_custom_query_var_string = isset( $meta['ctax_use_custom_query_var_string'] ) ? $meta['ctax_use_custom_query_var_string'][0] : false;
$ctax_query_var_string 			= isset( $meta['ctax_query_var_string'] ) ? $meta['ctax_query_var_string'][0] : '';
$ctax_sort 						= isset( $meta['ctax_sort'] ) ? $meta['ctax_sort'][0] : false;
$ctax_rewrite 					= isset( $meta['ctax_rewrite'] ) ? $meta['ctax_rewrite'][0] : true;
$ctax_use_custom_rewrite_slug 	= isset( $meta['ctax_use_custom_rewrite_slug'] ) ? $meta['ctax_use_custom_rewrite_slug'][0] : false;
$ctax_rewrite_custom_slug 		= isset( $meta['ctax_rewrite_custom_slug'] ) ? $meta['ctax_rewrite_custom_slug'][0] : '';
$ctax_with_front 				= isset( $meta['ctax_with_front'] ) ? $meta['ctax_with_front'][0] : true;
$ctax_hierarchical_urls 		= isset( $meta['ctax_hierarchical_urls'] ) ? $meta['ctax_hierarchical_urls'][0] : false;
$ctax_ep_mask 					= isset( $meta['ctax_ep_mask'] ) ? $meta['ctax_ep_mask'][0] : false;
$ctax_ep_mask_custom 			= isset( $meta['ctax_ep_mask_custom'] ) ? $meta['ctax_ep_mask_custom'][0] : '';

// REST API
$ctax_show_in_rest 				= isset( $meta['ctax_show_in_rest'] ) ? $meta['ctax_show_in_rest'][0] : true;
$ctax_rest_base 				= isset( $meta['ctax_rest_base'] ) ? $meta['ctax_rest_base'][0] : '';
$ctax_rest_namespace 			= isset( $meta['ctax_rest_namespace'] ) ? $meta['ctax_rest_namespace'][0] : '';
$ctax_rest_controller_class 	= isset( $meta['ctax_rest_controller_clas'] ) ? $meta['ctax_rest_controller_clas'][0] : '';

// LABELS
$ctax_label_search_items 			= isset( $meta['ctax_label_search_items'] ) ? $meta['ctax_label_search_items'][0] : '';
$ctax_label_popular_items 			= isset( $meta['ctax_label_popular_items'] ) ? $meta['ctax_label_popular_items'][0] : '';
$ctax_label_all_items 				= isset( $meta['ctax_label_all_items'] ) ? $meta['ctax_label_all_items'][0] : '';
$ctax_label_parent_item 			= isset( $meta['ctax_label_parent_item'] ) ? $meta['ctax_label_parent_item'][0] : '';
$ctax_label_parent_item_colon 		= isset( $meta['ctax_label_parent_item_colon'] ) ? $meta['ctax_label_parent_item_colon'][0] : '';
$ctax_label_name_field_description 	= isset( $meta['ctax_label_name_field_description'] ) ? $meta['ctax_label_name_field_description'][0] : 'The name is how it appears on your site';
$ctax_label_slug_field_description 	= isset( $meta['ctax_label_slug_field_description'] ) ? $meta['ctax_label_slug_field_description'][0] : 'The “slug” is the URL-friendly version of the name';
$ctax_label_parent_field_description = isset( $meta['ctax_label_parent_field_description'] ) ? $meta['ctax_label_parent_field_description'][0] : 'Assign a parent term to create a hierarchy';
$ctax_label_desc_field_description 	= isset( $meta['ctax_label_desc_field_description'] ) ? $meta['ctax_label_desc_field_description'][0] : 'The description is not prominent by default; however, some themes may show it';

$ctax_label_edit_item 			= isset( $meta['ctax_label_edit_item'] ) ? $meta['ctax_label_edit_item'][0] : '';
$ctax_label_view_item 			= isset( $meta['ctax_label_view_item'] ) ? $meta['ctax_label_view_item'][0] : '';
$ctax_label_update_item 		= isset( $meta['ctax_label_update_item'] ) ? $meta['ctax_label_update_item'][0] : '';
$ctax_label_add_new_item 		= isset( $meta['ctax_label_add_new_item'] ) ? $meta['ctax_label_add_new_item'][0] : '';
$ctax_label_new_item_name 		= isset( $meta['ctax_label_new_item_name'] ) ? $meta['ctax_label_new_item_name'][0] : '';
$ctax_label_separate_items_with_commas = isset( $meta['ctax_label_separate_items_with_commas'] ) ? $meta['ctax_label_separate_items_with_commas'][0] : '';
$ctax_label_add_or_remove_items = isset( $meta['ctax_label_add_or_remove_items'] ) ? $meta['ctax_label_add_or_remove_items'][0] : '';
$ctax_label_choose_from_most_used = isset( $meta['ctax_label_choose_from_most_used'] ) ? $meta['ctax_label_choose_from_most_used'][0] : '';
$ctax_label_not_found 			= isset( $meta['ctax_label_not_found'] ) ? $meta['ctax_label_not_found'][0] : '';

$ctax_label_no_terms 			= isset( $meta['ctax_label_no_terms'] ) ? $meta['ctax_label_no_terms'][0] : '';
$ctax_label_filter_by_item 		= isset( $meta['ctax_label_filter_by_item'] ) ? $meta['ctax_label_filter_by_item'][0] : '';
$ctax_label_items_list_navigation = isset( $meta['ctax_label_items_list_navigation'] ) ? $meta['ctax_label_items_list_navigation'][0] : '';
$ctax_label_items_list 			= isset( $meta['ctax_label_items_list'] ) ? $meta['ctax_label_items_list'][0] : '';
$ctax_label_most_used 			= isset( $meta['ctax_label_most_used'] ) ? $meta['ctax_label_most_used'][0] : 'Most used';
$ctax_label_back_to_items 		= isset( $meta['ctax_label_back_to_items'] ) ? $meta['ctax_label_back_to_items'][0] : '';
$ctax_label_item_link 			= isset( $meta['ctax_label_item_link'] ) ? $meta['ctax_label_item_link'][0] : '';
$ctax_label_item_link_description = isset( $meta['ctax_label_item_link_description'] ) ? $meta['ctax_label_item_link_description'][0] : '';