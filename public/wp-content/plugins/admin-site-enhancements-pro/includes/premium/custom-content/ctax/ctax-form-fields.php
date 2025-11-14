<?php

wp_nonce_field( 'asenha_cpt_ctax_optionp_meta_box_nonce_action', 'asenha_cpt_ctax_optionp__meta_box_nonce_field' );

?>
<section class="container">
  
  <ul class="tabs">
    <li class="tab-item"><div id="basic" class="item-active">Basic</div></li>
    <li class="tab-item"><div id="post-types">Post Types</div></li>
    <li class="tab-item"><div id="admin">Admin</div></li>
    <li class="tab-item"><div id="frontend">Front End</div></li>
    <li class="tab-item"><div id="rest-api">REST API</div></li>
    <li class="tab-item"><div id="labels">Labels</div></li>
  </ul>
  
  <div class="wrapper_tab-content">
    
	    <div id="tab-content-basic" class="tab-content content-visible">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="ctax_singular_name">Singular and plural names <span class="required">*</span></label>
		            <input type="text" name="ctax_singular_name" id="ctax_singular_name" placeholder="e.g. Genre" value="<?php echo esc_attr( $ctax_singular_name ); ?>" /required >
		            <input type="text" name="ctax_plural_name" id="ctax_plural_name" class="second-text-input" placeholder="e.g. Genres" value="<?php echo esc_attr( $ctax_plural_name ); ?>" required />
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="ctax_key">Key / Slug <span class="required">*</span></label>
		            <input type="text" name="ctax_key" id="ctax_key" placeholder="e.g. genre (max. 32 characters)" value="<?php echo esc_attr( $ctax_key ); ?>" maxlength="32" required />
		            <div class="input-description">Only lowercase alphanumeric characters, dashes, and underscores.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="ctax_description">Description</label>
		            <textarea name="ctax_description" id="ctax_description" class="" rows="3"><?php echo esc_html( $ctax_description ); ?></textarea>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="ctax_public">Public</label>
		            <label><input type="checkbox" id="ctax_public" name="ctax_public" <?php checked( $ctax_public, true ); ?> > Whether this taxonomy is intended for <a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">use publicly</a> either via the admin interface or by front-end users.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="ctax_hierarchical">Hierarchical</label>
		            <label><input type="checkbox" id="ctax_hierarchical" name="ctax_hierarchical" <?php checked( $ctax_hierarchical, true ); ?> > Whether or not items in this taxonomy can have <a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">parent-child relationships</a>.</label>
		    	</div>
	    	</div>
	    </div>    
    <div id="tab-content-post-types" class="tab-content">
    	<div class="cct-form-wrapper">
	    	<div class="cct-form-input cct-form-input-full-width">
	            <div class="input-heading">Supported Post Types</div>
	            <div class="description">Add support for registered, public post types.</div>
	    		<?php
	    		$args = array();
	    		$post_types = get_post_types( $args, 'objects' );
	    		$excluded_post_types = array( 
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
	    			'asenha_cpt',
	    			'asenha_ctax',
	    		);
	    		foreach ( $post_types as $post_type_slug => $post_type_object ) {
	    			if ( ! in_array( $post_type_slug, $excluded_post_types ) && true === $post_type_object->public ) {
						$checked = ( in_array( $post_type_object->name, $ctax_post_types ) ) ? ' checked' : '';
	    				?>
				            <label class="checkbox" for="ctax_post_types_<?php echo esc_attr( $post_type_object->name ); ?>"><input type="checkbox" name="ctax_post_types[]" id="ctax_post_types_<?php echo esc_attr( $post_type_object->name ); ?>" value="<?php echo esc_attr( $post_type_object->name ); ?>" <?php echo esc_attr( $checked ); ?> /> <?php echo esc_html( $post_type_object->label ); ?> <span class="faded">(<?php echo esc_html( $post_type_object->name ); ?>)</span></label>
	    				<?php
	    			}
	    		}
	    		?>
	    	</div>
	    </div>
    </div>
    
    <div id="tab-content-admin" class="tab-content">
    	<div class="cct-form-wrapper">
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_show_ui">Enable management UI</label>
	            <label><input type="checkbox" id="ctax_show_ui" name="ctax_show_ui" <?php checked( $ctax_show_ui, true ); ?> > Whether to generate a default UI for managing this taxonomy.</label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_show_in_menu">Show in menu</label>
	            <label><input type="checkbox" id="ctax_show_in_menu" name="ctax_show_in_menu" <?php checked( $ctax_show_in_menu, true ); ?> > Where to show the taxonomy in the admin menu. Enable Management UI must be enabled.</label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_show_in_nav_menus">Show in navigation menus</label>
	            <label><input type="checkbox" id="ctax_show_in_nav_menus" name="ctax_show_in_nav_menus" <?php checked( $ctax_show_in_nav_menus, true ); ?> > Make this taxonomy available for selection in navigation menus.</label>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_show_tagcloud">Show in tag cloud</label>
	            <label><input type="checkbox" id="ctax_show_tagcloud" name="ctax_show_tagcloud" <?php checked( $ctax_show_tagcloud, true ); ?> > Whether to allow the Tag Cloud widget to use this taxonomy.</label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_show_in_quick_edit">Show in quick edit</label>
	            <label><input type="checkbox" id="ctax_show_in_quick_edit" name="ctax_show_in_quick_edit" <?php checked( $ctax_show_in_quick_edit, true ); ?> > Whether to show the taxonomy in the quick/bulk edit panel.</label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_show_admin_column">Show admin column</label>
	            <label><input type="checkbox" id="ctax_show_admin_column" name="ctax_show_admin_column" <?php checked( $ctax_show_admin_column, true ); ?> > Whether to allow automatic creation of taxonomy columns on associated post-types table.</label>
	    	</div>
	    </div>
    </div>
    
    <div id="tab-content-frontend" class="tab-content">
    	<div class="cct-form-wrapper">
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_publicly_queryable">Publicly queryable</label>
	            <label><input type="checkbox" id="ctax_publicly_queryable" name="ctax_publicly_queryable" <?php checked( $ctax_publicly_queryable, true ); ?> > Whether <a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">queries</a> can be performed on the front end. e.g. <span class="highlight">site.com?taxonomy=<span class="taxonomy-key-text">genre</span></span></label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-two-thirds">
	            <label for="ctax_query_var">Query var</label>
	            <label><input type="checkbox" id="ctax_query_var" name="ctax_query_var" <?php checked( $ctax_query_var, true ); ?> > Sets the <a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">query_var</a> key for this taxonomy. Default to taxonomy key. e.g. <span class="highlight">site.com?<span class="taxonomy-key-text">genre</span>=<span class="taxonomy-key-text">genre</span>-title-slug</span>.</label>
	            <label id="ctax-use-custom-query-var-string"><input type="checkbox" id="ctax_use_custom_query_var_string" name="ctax_use_custom_query_var_string" <?php checked( $ctax_use_custom_query_var_string, true ); ?> > Use a custom query_var key.</label>
	            <label for="ctax_query_var_string" class="input-heading optional-text-input" style="display:none">Custom query_var key</label>
	            <input type="text" name="ctax_query_var_string" id="ctax_query_var_string" value="<?php echo esc_attr( $ctax_query_var_string ); ?>" placeholder="Enter custom string" style="display:none" />
	            <div id="query-var-string-description" class="input-description" style="display:none">e.g. <span class="highlight">site.com?<span class="custom-query-var-string">custom_string</span>=<span class="taxonomy-key-text">genre</span>-title-slug</span></div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_sort">Sort</label>
	            <label><input type="checkbox" id="ctax_sort" name="ctax_sort" <?php checked( $ctax_sort, true ); ?> > Whether terms in this taxonomy should be <a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">sorted in the order</a> they are provided to <code>wp_set_object_terms()</code>.</label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_rewrite">Rewrite</label>
	            <label><input type="checkbox" id="ctax_rewrite" name="ctax_rewrite" <?php checked( $ctax_rewrite, true ); ?> > Enable <a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">rewrites</a> for this taxonomy.</label>
	            <label id="ctax-use-custom-rewrite-slug"><input type="checkbox" id="ctax_use_custom_rewrite_slug" name="ctax_use_custom_rewrite_slug" <?php checked( $ctax_use_custom_rewrite_slug, true ); ?> > Use a custom rewrite slug.</label>
	            <label for="ctax_rewrite_custom_slug" class="input-heading optional-text-input" style="display:none;">Custom rewrite slug</label>
	            <input type="text" name="ctax_rewrite_custom_slug" id="ctax_rewrite_custom_slug" value="<?php echo esc_attr( $ctax_rewrite_custom_slug ); ?>" placeholder="Enter custom slug" style="display:none;" />
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds enable-rewrite-related-field">
	            <label for="ctax_with_front">With front</label>
	            <label><input type="checkbox" id="ctax_with_front" name="ctax_with_front" <?php checked( $ctax_with_front, true ); ?> > Allow permalinks to be prepended with <a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">front base</a>.</label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds enable-rewrite-related-field">
	            <label for="ctax_hierarchical_urls">Hierarchical URLs</label>
	            <label><input type="checkbox" id="ctax_hierarchical_urls" name="ctax_hierarchical_urls" <?php checked( $ctax_hierarchical_urls, true ); ?> > Whether to enable hierarchical rewrite tag.</label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds enable-rewrite-related-field">
	            <label for="ctax_ep_mask">Endpoint mask</label>
	            <label><input type="checkbox" id="ctax_ep_mask" name="ctax_ep_mask" <?php checked( $ctax_ep_mask, true ); ?> > Assign an <a href="https://developer.wordpress.org/reference/functions/register_taxonomy/" target="_blank">endpoint mask</a> for this taxonomy. Default: <em>EP_NONE</em>.</label>
	            <label for="ctax_ep_mask_custom" class="input-heading optional-text-input" style="display:none;">Custom endpoint mask</label>
	            <input type="text" name="ctax_ep_mask_custom" id="ctax_ep_mask_custom" value="<?php echo esc_attr( $ctax_ep_mask_custom ); ?>" placeholder="Enter constant" style="display:none;" />
	    	</div>
	    </div>
    </div>
    
    <div id="tab-content-rest-api" class="tab-content">
    	<div class="cct-form-wrapper">
	    	<div class="cct-form-input cct-form-input-full-width">
	            <label for="ctax_show_in_rest">Show in REST</label>
	            <label><input type="checkbox" id="ctax_show_in_rest" name="ctax_show_in_rest" <?php checked( $ctax_show_in_rest, true ); ?> > Whether to expose this taxonomy in the REST API. Must be enabled for the taxonomy to be available in the block editor.</label>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds show-in-rest-related-field" style="display:none;">
	            <label for="ctax_rest_base"> REST API base slug</label>
	            <input type="text" name="ctax_rest_base" id="ctax_rest_base" value="<?php echo esc_attr( $ctax_rest_base ); ?>" />
	            <div class="input-description">To change the base URL of REST API route. Default: <em>taxonomy key/slug</em>.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds show-in-rest-related-field" style="display:none;">
	            <label for="ctax_rest_namespace"> REST API namespace</label>
	            <input type="text" name="ctax_rest_namespace" id="ctax_rest_namespace" value="<?php echo esc_attr( $ctax_rest_namespace ); ?>" />
	            <div class="input-description">To change the namespace URL of REST API route. Default: <em>wp/v2</em>.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds show-in-rest-related-field" style="display:none;">
	            <label for="ctax_rest_controller_class"> REST API controller class</label>
	            <input type="text" name="ctax_rest_controller_class" id="ctax_rest_controller_class" value="<?php echo esc_attr( $ctax_rest_controller_class ); ?>" />
	            <div class="input-description">REST API controller class name. Default: <em>WP_REST_Terms_Controller</em>.</div>
	    	</div>		    	
	    </div>
    </div>
    <div id="tab-content-labels" class="tab-content">
    	<div class="cct-form-wrapper">
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_search_items">Search items</label>
	            <input type="text" name="ctax_label_search_items" id="ctax_label_search_items" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_search_items ); ?>" placeholder="e.g. Search Genres" />
	            <div class="input-description">Used in the admin list table for managing custom taxonomies.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_popular_items">Popular items</label>
	            <input type="text" name="ctax_label_popular_items" id="ctax_label_popular_items" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_popular_items ); ?>" placeholder="e.g. Popular Genres" />
	            <div class="input-description">Used in the admin list table for managing custom taxonomies. This label is only used for non-hierarchical taxonomies.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_all_items">All items</label>
	            <input type="text" name="ctax_label_all_items" id="ctax_label_all_items" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_all_items ); ?>" placeholder="e.g. All Genres" />
	            <div class="input-description">Used as tab text when showing all terms for hierarchical taxonomy while editing post.</div>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_parent_item">Parent item</label>
	            <input type="text" name="ctax_label_parent_item" id="ctax_label_parent_item" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_parent_item ); ?>" placeholder="e.g. Parent Genre" />
	            <div class="input-description">Used in the admin list table for managing custom taxonomies. This label is only used for hierarchical taxonomies.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_parent_item_colon">Parent item colon</label>
	            <input type="text" name="ctax_label_parent_item_colon" id="ctax_label_parent_item_colon" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_parent_item_colon ); ?>" placeholder="e.g. Parent Genre:" />
	            <div class="input-description">Used in the admin list table for managing custom taxonomies. This label is only used for hierarchical taxonomies.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_name_field_description">Name field description</label>
	            <input type="text" name="ctax_label_name_field_description" id="ctax_label_name_field_description" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_name_field_description ); ?>" placeholder="e.g. The name is how it appears on your site" />
	            <div class="input-description">Description for the Name field on Edit taxonomies screen.</div>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_slug_field_description">Slug field description</label>
	            <input type="text" name="ctax_label_slug_field_description" id="ctax_label_slug_field_description" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_slug_field_description ); ?>" placeholder="e.g. The “slug” is the URL-friendly version of the name" />
	            <div class="input-description">Description for the Slug field on Edit taxonomies screen.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_parent_field_description">Parent field description</label>
	            <input type="text" name="ctax_label_parent_field_description" id="ctax_label_parent_field_description" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_parent_field_description ); ?>" placeholder="e.g. Assign a parent term to create a hierarchy" />
	            <div class="input-description">Description for the Parent field on Edit taxonomies screen.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_desc_field_description">Description field description</label>
	            <input type="text" name="ctax_label_desc_field_description" id="ctax_label_desc_field_description" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_desc_field_description ); ?>" placeholder="e.g. The description is not prominent by default; however, some themes may show it" />
	            <div class="input-description">Description for the Description field on Edit taxonomies screen.</div>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_edit_item">Edit item</label>
	            <input type="text" name="ctax_label_edit_item" id="ctax_label_edit_item" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_edit_item ); ?>" placeholder="e.g. Edit Genre" />
	            <div class="input-description">Used at the top of the term editor screen for an existing taxonomy term.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_view_item">View item</label>
	            <input type="text" name="ctax_label_view_item" id="ctax_label_view_item" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_view_item ); ?>" placeholder="e.g. View Genre" />
	            <div class="input-description">Used in the admin bar when viewing editor screen for an existing taxonomy term.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_update_item">Update item</label>
	            <input type="text" name="ctax_label_update_item" id="ctax_label_update_item" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_update_item ); ?>" placeholder="e.g. Update Genre" />
	            <div class="input-description">Used in the admin list table for managing custom taxonomies.</div>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_add_new_item">Add new item</label>
	            <input type="text" name="ctax_label_add_new_item" id="ctax_label_add_new_item" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_add_new_item ); ?>" placeholder="e.g. Add New Genre" />
	            <div class="input-description">Used at the top of the term editor screen and button text for a new taxonomy term.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_new_item_name">New item name</label>
	            <input type="text" name="ctax_label_new_item_name" id="ctax_label_new_item_name" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_new_item_name ); ?>" placeholder="e.g. New Genre Name" />
	            <div class="input-description">Used in the admin list table for managing custom taxonomies.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_separate_items_with_commas">Separate items with commas</label>
	            <input type="text" name="ctax_label_separate_items_with_commas" id="ctax_label_separate_items_with_commas" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_separate_items_with_commas ); ?>" placeholder="e.g. Separate genres with commas" />
	            <div class="input-description">Used in the admin list table for managing custom taxonomies. This label is only used for non-hierarchical taxonomies.</div>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_add_or_remove_items">Add or remove item</label>
	            <input type="text" name="ctax_label_add_or_remove_items" id="ctax_label_add_or_remove_items" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_add_or_remove_items ); ?>" placeholder="e.g. Add or remove genre" />
	            <div class="input-description">Used in the admin list table for managing custom taxonomies. This label is only used for non-hierarchical taxonomies.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_choose_from_most_used">Choose from most used</label>
	            <input type="text" name="ctax_label_choose_from_most_used" id="ctax_label_choose_from_most_used" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_choose_from_most_used ); ?>" placeholder="e.g. Choose from the most used genres" />
	            <div class="input-description">The text displayed via clicking ‘Choose from the most used items’ in the taxonomy meta box when no items are available. This label is only used for non-hierarchical taxonomies.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_not_found">Not found</label>
	            <input type="text" name="ctax_label_not_found" id="ctax_label_not_found" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_not_found ); ?>" placeholder="e.g. No genres found" />
	            <div class="input-description">Used when indicating that there are no terms in the given taxonomy within the meta box and taxonomy list table.</div>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_no_terms">No terms</label>
	            <input type="text" name="ctax_label_no_terms" id="ctax_label_no_terms" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_no_terms ); ?>" placeholder="e.g. No genres" />
	            <div class="input-description">Used when indicating that there are no terms in the given taxonomy associated with an object.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_filter_by_item">Filter by item</label>
	            <input type="text" name="ctax_label_filter_by_item" id="ctax_label_filter_by_item" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_filter_by_item ); ?>" placeholder="e.g. Filter by genre" />
	            <div class="input-description">This label is only used for hierarchical taxonomies. Used in the posts list table.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_items_list_navigation">Items list navigation</label>
	            <input type="text" name="ctax_label_items_list_navigation" id="ctax_label_items_list_navigation" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_items_list_navigation ); ?>" placeholder="e.g. Genres list navigation" />
	            <div class="input-description">Screen reader text for the pagination heading on the term listing screen.</div>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_items_list">Items list</label>
	            <input type="text" name="ctax_label_items_list" id="ctax_label_items_list" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_items_list ); ?>" placeholder="e.g. Genres list" />
	            <div class="input-description">Screen reader text for the items list heading on the term listing screen.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_most_used">Most used</label>
	            <input type="text" name="ctax_label_most_used" id="ctax_label_most_used" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_most_used ); ?>" placeholder="e.g. Most Used" />
	            <div class="input-description">Title for the Most Used tab.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_back_to_items">Back to items</label>
	            <input type="text" name="ctax_label_back_to_items" id="ctax_label_back_to_items" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_back_to_items ); ?>" placeholder="e.g. Back to genres" />
	            <div class="input-description">The text displayed after a term has been updated for a link back to main index.</div>
	    	</div>

	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_item_link">Item link</label>
	            <input type="text" name="ctax_label_item_link" id="ctax_label_item_link" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_item_link ); ?>" placeholder="e.g. Genre Link" />
	            <div class="input-description">Used in the block editor. Title for a navigation link block variation.</div>
	    	</div>
	    	<div class="cct-form-input cct-form-input-thirds">
	            <label for="ctax_label_item_link_description">Item link description</label>
	            <input type="text" name="ctax_label_item_link_description" id="ctax_label_item_link_description" class="ctax-labels-default" value="<?php echo esc_attr( $ctax_label_item_link_description ); ?>" placeholder="e.g. A link to a genre" />
	            <div class="input-description">Used in the block editor. Description for a navigation link block variation.</div>
	    	</div>

	    </div>
    </div>
    
  </div>
  
</section>