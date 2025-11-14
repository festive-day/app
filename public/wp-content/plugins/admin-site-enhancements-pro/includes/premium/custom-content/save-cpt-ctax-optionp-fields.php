<?php

if ( ! current_user_can( 'edit_post', $post_id ) ) {
	return;
}

if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
	return;
}

if ( ! isset( $_POST['asenha_cpt_ctax_optionp__meta_box_nonce_field'] ) 
	|| ! wp_verify_nonce( $_POST['asenha_cpt_ctax_optionp__meta_box_nonce_field'], 'asenha_cpt_ctax_optionp_meta_box_nonce_action' )
) {
	return;
}

// Only sanitize and save fields if this is creating/updating the ASENHA CPT post type
if ( 'asenha_cpt' === $post->post_type ) {

	// $posted = $_POST;

	// BASIC
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_singular_name', 'sanitize_text_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_plural_name', 'sanitize_text_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_key', 'sanitize_title_underscore' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_description', 'sanitize_textarea_field' );

	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_public', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_hierarchical', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_capability_type', 'sanitize_text_field', 'post' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_capability_type_custom_slug_singular', 'sanitize_title_underscore' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_capability_type_custom_slug_plural', 'sanitize_title_underscore' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_map_meta_cap', 'sanitize_checkbox' );
	
	// SUPPORTS & TAXONOMIES
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_supports', 'sanitize_array', array( 'title', 'editor' ) );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_taxonomies', 'sanitize_array', array() );
	
	// ADMIN
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_show_ui', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_show_in_menu', 'sanitize_checkbox' );

	$cpt_menu_icon = isset( $_POST['cpt_menu_icon'] ) ? $_POST['cpt_menu_icon'] : '';
	update_post_meta( 
		$post_id, 
		'cpt_menu_icon', 
		$cpt_menu_icon 
	);

	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_show_in_admin_bar', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_show_in_nav_menus', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_can_export', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_delete_with_user', 'sanitize_checkbox' );
	
	// FRONT END
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_publicly_queryable', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_query_var', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_use_custom_query_var_string', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_query_var_string', 'sanitize_title_underscore' );

	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_exclude_from_search', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_has_archive', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_use_custom_archive_slug', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_has_archive_custom_slug', 'sanitize_title_underscore' );

	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_rewrite', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_use_custom_rewrite_slug', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_rewrite_custom_slug', 'sanitize_title_underscore' );

	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_with_front', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_feeds', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_pages', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_ep_mask', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_ep_mask_custom', 'sanitize_title' );

	// REST API
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_show_in_rest', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_rest_base', 'sanitize_title_underscore' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_rest_namespace', 'sanitize_text_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'cpt_rest_controller_class', 'sanitize_text_field' );
	
	// LABELS
	$label_keys = array(
		'cpt_label_add_new',
		'cpt_label_add_new_item',
		'cpt_label_edit_item',
		'cpt_label_new_item',
		'cpt_label_view_item',
		'cpt_label_view_items',
		'cpt_label_search_items',
		'cpt_label_not_found',
		'cpt_label_not_found_in_trash',

		'cpt_label_parent_item_colon',
		'cpt_label_all_items',
		'cpt_label_archives',
		'cpt_label_attributes',
		'cpt_label_insert_into_item',
		'cpt_label_uploaded_to_this_item',
		'cpt_label_featured_image',
		'cpt_label_set_featured_image',
		'cpt_label_remove_featured_image',

		'cpt_label_use_featured_image',
		'cpt_label_menu_name',
		'cpt_label_filter_items_list',
		'cpt_label_filter_by_date',
		'cpt_label_items_list_navigation',
		'cpt_label_items_list',
		'cpt_label_item_published',
		'cpt_label_item_published_privately',
		'cpt_label_item_reverted_to_draft',

		'cpt_label_item_scheduled',
		'cpt_label_item_updated',
		'cpt_label_item_link',
		'cpt_label_item_link_description',
	);

	foreach ( $label_keys as $label_key ) {
		$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, $label_key, 'sanitize_text_field' );	
	}

	$options = get_option( ASENHA_SLUG_U );
	$options['custom_content_types_flush_rewrite_rules_needed'] = true;
	update_option( ASENHA_SLUG_U, $options, true );

}

if ( 'asenha_ctax' === $post->post_type ) {

	// $posted = $_POST;

	// BASIC
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_singular_name', 'sanitize_text_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_plural_name', 'sanitize_text_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_key', 'sanitize_title_underscore' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_description', 'sanitize_textarea_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_public', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_hierarchical', 'sanitize_checkbox' );

	// POST TYPES
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_post_types', 'sanitize_array', array() );

	// ADMIN
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_show_ui', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_show_in_menu', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_show_in_nav_menus', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_show_tagcloud', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_show_in_quick_edit', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_show_admin_column', 'sanitize_checkbox' );

	// FRONT END
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_publicly_queryable', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_query_var', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_use_custom_query_var_string', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_query_var_string', 'sanitize_title_underscore' );

	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_sort', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_rewrite', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_use_custom_rewrite_slug', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_rewrite_custom_slug', 'sanitize_title_underscore' );

	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_with_front', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_hierarchical_urls', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_ep_mask', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_ep_mask_custom', 'sanitize_title' );

	// REST API
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_show_in_rest', 'sanitize_checkbox' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_rest_base', 'sanitize_title_underscore' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_rest_namespace', 'sanitize_text_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'ctax_rest_controller_class', 'sanitize_text_field' );

	// LABELS
	$label_keys = array(
		'ctax_label_search_items',
		'ctax_label_popular_items',
		'ctax_label_all_items',
		'ctax_label_parent_item',
		'ctax_label_parent_item_colon',
		'ctax_label_name_field_description',
		'ctax_label_slug_field_description',
		'ctax_label_parent_field_description',
		'ctax_label_desc_field_description',
		'ctax_label_edit_item',
		'ctax_label_view_item',
		'ctax_label_update_item',
		'ctax_label_add_new_item',
		'ctax_label_new_item_name',
		'ctax_label_separate_items_with_commas',
		'ctax_label_add_or_remove_items',
		'ctax_label_choose_from_most_used',
		'ctax_label_not_found',
		'ctax_label_no_terms',
		'ctax_label_filter_by_item',
		'ctax_label_items_list_navigation',
		'ctax_label_items_list',
		'ctax_label_most_used',
		'ctax_label_back_to_items',
		'ctax_label_item_link',
		'ctax_label_item_link_description',
	);

	foreach ( $label_keys as $label_key ) {
		$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, $label_key, 'sanitize_text_field' );	
	}

	$options = get_option( ASENHA_SLUG_U );
	$options['custom_content_types_flush_rewrite_rules_needed'] = true;
	update_option( ASENHA_SLUG_U, $options, true );

}

if ( 'options_page_config' === $post->post_type ) {

	// $posted = $_POST;

	// MAIN
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'options_page_title', 'sanitize_text_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'options_page_menu_title', 'sanitize_text_field' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'options_page_menu_slug', 'sanitize_title_underscore' );
	$options_page_menu_icon = isset( $_POST['options_page_menu_icon'] ) ? $_POST['options_page_menu_icon'] : '';
	update_post_meta( 
		$post_id, 
		'options_page_menu_icon', 
		$options_page_menu_icon 
	);
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'options_page_parent_menu', 'sanitize_text_field', 'none' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'options_page_capability', 'sanitize_text_field', 'manage_options' );
	$common_methods->update_post_meta_after_sanitization__premium_only( $post_id, 'options_page_capability_custom', 'sanitize_text_field', 'manage_options' );

	$options = get_option( ASENHA_SLUG_U );
	$options['custom_content_types_flush_rewrite_rules_needed'] = true;
	update_option( ASENHA_SLUG_U, $options, true );

}