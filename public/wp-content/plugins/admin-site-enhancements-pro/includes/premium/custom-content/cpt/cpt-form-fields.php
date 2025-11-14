<?php

wp_nonce_field( 'asenha_cpt_ctax_optionp_meta_box_nonce_action', 'asenha_cpt_ctax_optionp__meta_box_nonce_field' );

?>
<section class="container">
  
	<ul class="tabs">
		<li class="tab-item"><div id="basic" class="item-active">Basic</div></li>
		<li class="tab-item"><div id="supports-taxonomies">Features & Taxonomies</div></li>
		<li class="tab-item"><div id="admin">Admin</div></li>
		<li class="tab-item"><div id="frontend">Front End</div></li>
		<li class="tab-item"><div id="restapi">REST API</div></li>
		<li class="tab-item"><div id="labels">Labels</div></li>
	</ul>

	<div class="wrapper_tab-content">
	    
	    <!-- BASIC tab -->
	    <div id="tab-content-basic" class="tab-content content-visible">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_singular_name">Singular and plural names <span class="required">*</span></label>
		            <input type="text" name="cpt_singular_name" id="cpt_singular_name" placeholder="e.g. Movie" value="<?php echo $cpt_singular_name; ?>" /required >
		            <input type="text" name="cpt_plural_name" id="cpt_plural_name" class="second-text-input" placeholder="e.g. Movies" value="<?php echo $cpt_plural_name; ?>" required />
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_key">Key / Slug <span class="required">*</span></label>
		            <input type="text" name="cpt_key" id="cpt_key" placeholder="e.g. movie (max. 20 characters)" value="<?php echo $cpt_key; ?>" maxlength="20" required />
		            <div class="input-description">Only lowercase alphanumeric characters, dashes, and underscores.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_description">Description</label>
		            <textarea name="cpt_description" id="cpt_description" class="" rows="3"><?php echo esc_html( $cpt_description ); ?></textarea>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_public">Public</label>
		            <label><input type="checkbox" id="cpt_public" name="cpt_public" <?php checked( $cpt_public, true ); ?> > Whether this post type is intended for <a href="https://developer.wordpress.org/reference/functions/register_post_type/#public" target="_blank">use publicly</a> either via the admin interface or by front-end users.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_hierarchical">Hierarchical</label>
		            <label><input type="checkbox" id="cpt_hierarchical" name="cpt_hierarchical" <?php checked( $cpt_hierarchical, true ); ?> > Whether or not items in this post type can have <a href="https://developer.wordpress.org/reference/functions/register_post_type/#hierarchical" target="_blank">parent-child relationships</a>.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_capability_type">Capability type</label>
		            <select name="cpt_capability_type" id="cpt_capability_type" tabindex="18">
		                <option value="post" <?php selected( $cpt_capability_type, 'post' ); ?>>post (default)</option>
		                <option value="page" <?php selected( $cpt_capability_type, 'page' ); ?>>page</option>
		                <option value="custom" <?php selected( $cpt_capability_type, 'custom' ); ?>>custom</option>
		            </select>
		            <label for="cpt_capability_type_custom_slug_singular" class="input-heading optional-text-input" style="display:none;">Singular and plural slugs</label>
		            <input type="text" name="cpt_capability_type_custom_slug_singular" id="cpt_capability_type_custom_slug_singular" value="<?php echo $cpt_capability_type_custom_slug_singular; ?>" placeholder="Enter singular slug, e.g. movie" style="display:none;" />
		            <input type="text" name="cpt_capability_type_custom_slug_plural" id="cpt_capability_type_custom_slug_plural" class="second-text-input" value="<?php echo $cpt_capability_type_custom_slug_plural; ?>" placeholder="Enter plural slug, e.g. movies" style="display:none;" />
		            <div class="input-description">The string(s) to use to build the <a href="https://developer.wordpress.org/reference/functions/register_post_type/#capability_type" target="_blank">read, edit, and delete capabilities</a>.</div>

		            <label for="cpt_map_meta_cap" class="input-heading optional-text-input" style="display:none;">Map meta capabilities</label>
		            <label id="label-for-cpt-map-meta-cap" style="display:none;"><input type="checkbox" id="cpt_map_meta_cap" name="cpt_map_meta_cap" <?php checked( $cpt_map_meta_cap, true ); ?> > Whether to use the internal default <a href="https://developer.wordpress.org/reference/functions/register_post_type/#capabilities" target="_blank">meta capability handling</a>.</label>
		    	</div>
	    	</div>
	    </div>
	    
	    <!-- SUPPORTS & TAXONOMIES tab -->
	    <div id="tab-content-supports-taxonomies" class="tab-content">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-halves">

		            <div class="input-heading">Supported Features</div>
		            <div class="description">Add support for various post editor features. Theme support is needed for <a href="https://developer.wordpress.org/reference/functions/add_theme_support/#post-thumbnails" target="_blank">Featured Image</a> and <a href="https://wordpress.org/documentation/article/post-formats/" target="_blank">Post Formats</a> to be used. Choose only "None" to disable all features.</div>

		            <label class="checkbox" for="cpt_supports_title"><input type="checkbox" name="cpt_supports[]" id="cpt_supports_title" value="title" <?php echo checked( $cpt_supports_title, 'title' ); ?> /> Title</label>

		            <label class="checkbox" for="cpt_supports_editor"><input type="checkbox" name="cpt_supports[]" id="cpt_supports_editor" value="editor" <?php echo checked( $cpt_supports_editor, 'editor' ); ?> /> Editor (must be enabled to use the block editor)</label>

		            <label class="checkbox" for="cpt_supports_author"><input type="checkbox" tabindex="30" name="cpt_supports[]" id="cpt_supports_author" value="author" <?php checked( $cpt_supports_author, 'author' ); ?> /> Author</label>
		            
		            <label class="checkbox" for="cpt_supports_featured_image"><input type="checkbox" name="cpt_supports[]" id="cpt_supports_featured_image" value="thumbnail" <?php checked( $cpt_supports_featured_image, 'thumbnail' ); ?> /> Featured Image</label>

		            <label class="checkbox" for="cpt_supports_excerpt"><input type="checkbox" name="cpt_supports[]" id="cpt_supports_excerpt" value="excerpt" <?php checked( $cpt_supports_excerpt, 'excerpt' ); ?> /> Excerpt</label>

		            <label class="checkbox" for="cpt_supports_trackbacks"><input type="checkbox" name="cpt_supports[]" id="cpt_supports_trackbacks" value="trackbacks" <?php checked( $cpt_supports_trackbacks, 'trackbacks' ); ?> /> Trackbacks</label>

		            <label class="checkbox" for="cpt_supports_custom_fields"><input type="checkbox" name="cpt_supports[]" id="cpt_supports_custom_fields" value="custom-fields" <?php checked( $cpt_supports_custom_fields, 'custom-fields' ); ?> /> Custom Fields</label>

		            <label class="checkbox" for="cpt_supports_comments"><input type="checkbox" name="cpt_supports[]" id="cpt_supports_comments" value="comments" <?php checked( $cpt_supports_comments, 'comments' ); ?> /> Comments</label>

		            <label class="checkbox" for="cpt_supports_revisions"><input type="checkbox" name="cpt_supports[]" id="cpt_supports_revisions" value="revisions" <?php checked( $cpt_supports_revisions, 'revisions' ); ?> /> Revisions</label>
		            
		            <label class="checkbox" for="cpt_supports_page_attributes"><input type="checkbox" tabindex="31" name="cpt_supports[]" id="cpt_supports_page_attributes" value="page-attributes" <?php checked( $cpt_supports_page_attributes, 'page-attributes' ); ?> /> Page Attributes</label>

		            <label class="checkbox" for="cpt_supports_post_formats"><input type="checkbox" tabindex="32" name="cpt_supports[]" id="cpt_supports_post_formats" value="post-formats" <?php checked( $cpt_supports_post_formats, 'post-formats' ); ?> /> Post Formats</label>
		            
		            <label class="checkbox" for="cpt_supports_none"><input type="checkbox" tabindex="32" name="cpt_supports[]" id="cpt_supports_none" value="none" <?php checked( $cpt_supports_none, 'none' ); ?> /> None</label>
		            
		    	</div>
		    	<div class="cct-form-input cct-form-input-halves">

		            <div class="input-heading">Supported Taxonomies</div>
		            <div class="description">Add support for core, built-in taxonomies. You can also create a custom taxonomy for this post type later.</div>
		            <?php

					// Get checkbox list of registered taxonomies		
					$taxonomies = get_taxonomies( array(), 'objects' );
					$available_taxonomies = array( 'category', 'post_tag' );
					foreach ( $taxonomies as $taxonomy => $tax_object ) {
						if ( in_array( $taxonomy, $available_taxonomies ) && $tax_object->public == true ) {
							$checked = ( in_array( $tax_object->name, $cpt_taxonomies ) ) ? ' checked' : '';
							?>
				            <label class="checkbox" for="cpt_taxonomies_<?php echo esc_attr( $tax_object->name ); ?>"><input type="checkbox" name="cpt_taxonomies[]" id="cpt_taxonomies_<?php echo esc_attr( $tax_object->name ); ?>" value="<?php echo esc_attr( $tax_object->name ); ?>" <?php echo esc_attr( $checked ); ?> /> <?php echo esc_html( $tax_object->label ); ?> <span class="faded">(<?php echo esc_html( $tax_object->name ); ?>)</span></label>
				            <?php
						}
					}

		            ?>
		    	</div>
		    </div>
	    </div>

	    <!-- ADMIN tab -->
	    <div id="tab-content-admin" class="tab-content">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_show_ui">Enable management UI</label>
		            <label><input type="checkbox" id="cpt_show_ui" name="cpt_show_ui" <?php checked( $cpt_show_ui, true ); ?> > Whether to generate and allow a  <a href="https://developer.wordpress.org/reference/functions/register_post_type/#show_ui" target="_blank">UI for managing</a> this post type in the admin.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_show_in_menu">Show in menu</label>
		            <label><input type="checkbox" id="cpt_show_in_menu" name="cpt_show_in_menu" <?php checked( $cpt_show_in_menu, true ); ?> > Whether to show the top-level admin menu. Enable Management UI must be enabled for this to work.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_menu_icon"> Menu icon</label>
		            <?php
		            $cpt_menu_icon_svg = base64_decode( str_replace( 'data:image/svg+xml;base64,', '', $cpt_menu_icon ) );
		            ?>
		            <div class="icon-picker">
				    	<div id="selected-menu-icon" class="selected-menu-icon"><?php echo $cpt_menu_icon_svg; ?></div>		
			            <input type="text" name="cpt_menu_icon" id="the_menu_icon" value="<?php echo $cpt_menu_icon; ?>" placeholder="e.g. dashicons-media-video" />
		            </div>
		            <div class="input-description" style="display: none;"><a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a> slug or <a href="https://developer.wordpress.org/reference/functions/register_post_type/#menu_icon" target="_blank">base64-encoded SVG</a>.</div>
		            <div class="choose-search-icon">
			            <button id="icon-picker-button" class="button">Change Icon</button>
			            <input type="search" id="search-input" placeholder="Search..." style="display:none;" />
		            </div>
		    	</div>
		    	<div id="menu-icons-row" class="cct-form-input cct-form-input-full-width zero-margin-bottom">
		    		<span id="close-icon-picker" style="display: none;"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 16"><path fill="currentColor" d="M15.1 3.1L12.9.9L8 5.9L3.1.9L.9 3.1l5 4.9l-5 4.9l2.2 2.2l4.9-5l4.9 5l2.2-2.2l-5-4.9z"/></svg></span>
		    	<?php
					require_once ASENHA_PATH . 'includes/premium/custom-content/assets/img/menu-icons.php';
		    	?>
				</div>		    	
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_show_in_admin_bar">Show in admin bar</label>
		            <label><input type="checkbox" id="cpt_show_in_admin_bar" name="cpt_show_in_admin_bar" <?php checked( $cpt_show_in_admin_bar, true ); ?> > Makes this post type available via the admin bar.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_show_in_nav_menus">Show in navigation menus</label>
		            <label><input type="checkbox" id="cpt_show_in_nav_menus" name="cpt_show_in_nav_menus" <?php checked( $cpt_show_in_nav_menus, true ); ?> > Make this post type available for selection in navigation menus.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_can_export">Can export</label>
		            <label><input type="checkbox" id="cpt_can_export" name="cpt_can_export" <?php checked( $cpt_can_export, true ); ?> > Whether to allow this post type to be exported.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_delete_with_user">Delete with user</label>
		            <label><input type="checkbox" id="cpt_delete_with_user" name="cpt_delete_with_user" <?php checked( $cpt_delete_with_user, true ); ?> > Whether to delete posts of this type when deleting a user.</label>
		    	</div>
		    </div>
	    </div>
	    
	    <!-- FRONT END tab -->
	    <div id="tab-content-frontend" class="tab-content">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_publicly_queryable">Publicly queryable</label>
		            <label><input type="checkbox" id="cpt_publicly_queryable" name="cpt_publicly_queryable" <?php checked( $cpt_publicly_queryable, true ); ?> > Whether <a href="https://developer.wordpress.org/reference/functions/register_post_type/#publicly_queryable" target="_blank">queries</a> can be performed on the front end. e.g. <span class="highlight">site.com?post_type=<span class="post-type-key-text">movie</span></span></label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-two-thirds">
		            <label for="cpt_
		            ">Query var</label>
		            <label><input type="checkbox" id="cpt_query_var" name="cpt_query_var" <?php checked( $cpt_query_var, true ); ?> > Sets the <a href="https://developer.wordpress.org/reference/functions/register_post_type/#query_var" target="_blank">query_var</a> key for this post type. Default to post type key. e.g. <span class="highlight">site.com?<span class="post-type-key-text">movie</span>=<span class="post-type-key-text">movie</span>-title-slug</span>.</label>
		            <label id="cpt-use-custom-query-var-string"><input type="checkbox" id="cpt_use_custom_query_var_string" name="cpt_use_custom_query_var_string" <?php checked( $cpt_use_custom_query_var_string, true ); ?> > Use a custom query_var key.</label>
		            <label for="cpt_query_var_string" class="input-heading optional-text-input" style="display:none">Custom query_var key</label>
		            <input type="text" name="cpt_query_var_string" id="cpt_query_var_string" value="<?php echo $cpt_query_var_string; ?>" placeholder="Enter custom string" style="display:none" />
		            <div id="query-var-string-description" class="input-description" style="display:none">e.g. <span class="highlight">site.com?<span class="custom-query-var-string">custom_string</span>=<span class="post-type-key-text">movie</span>-title-slug</span></div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_exclude_from_search">Exclude from search</label>
		            <label><input type="checkbox" id="cpt_exclude_from_search" name="cpt_exclude_from_search" <?php checked( $cpt_exclude_from_search, true ); ?> > Whether to exclude posts with this post type from front end <a href="https://developer.wordpress.org/reference/functions/register_post_type/#exclude_from_search" target="_blank">search results</a>.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_has_archive">Has archive</label>
		            <label><input type="checkbox" id="cpt_has_archive" name="cpt_has_archive" <?php checked( $cpt_has_archive, true ); ?> > Enable post type <a href="https://developer.wordpress.org/reference/functions/register_post_type/#has_archive" target="_blank">archives</a>.</label>
		            <label id="cpt-use-custom-archive-slug" style="display:none;"><input type="checkbox" id="cpt_use_custom_archive_slug" name="cpt_use_custom_archive_slug" <?php checked( $cpt_use_custom_archive_slug, true ); ?> > Use a custom archive slug.</label>
		            <label for="cpt_has_archive_custom_slug" class="input-heading optional-text-input" style="display:none">Custom archive slug</label>
		            <input type="text" name="cpt_has_archive_custom_slug" id="cpt_has_archive_custom_slug" value="<?php echo $cpt_has_archive_custom_slug; ?>" placeholder="Enter custom slug" style="display:none;" />
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_rewrite">Rewrite</label>
		            <label><input type="checkbox" id="cpt_rewrite" name="cpt_rewrite" <?php checked( $cpt_rewrite, true ); ?> > Enable <a href="https://developer.wordpress.org/reference/functions/register_post_type/#rewrite" target="_blank">rewrites</a> for this post type.</label>
		            <label id="cpt-use-custom-rewrite-slug"><input type="checkbox" id="cpt_use_custom_rewrite_slug" name="cpt_use_custom_rewrite_slug" <?php checked( $cpt_use_custom_rewrite_slug, true ); ?> > Use a custom rewrite slug.</label>
		            <label for="cpt_rewrite_custom_slug" class="input-heading optional-text-input" style="display:none;">Custom rewrite slug</label>
		            <input type="text" name="cpt_rewrite_custom_slug" id="cpt_rewrite_custom_slug" value="<?php echo $cpt_rewrite_custom_slug; ?>" placeholder="Enter custom slug" style="display:none;" />
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds enable-rewrite-related-field">
		            <label for="cpt_with_front">With front</label>
		            <label><input type="checkbox" id="cpt_with_front" name="cpt_with_front" <?php checked( $cpt_with_front, true ); ?> > Allow permalinks to be prepended with <a href="https://developer.wordpress.org/reference/functions/register_post_type/#rewrite" target="_blank">front base</a>.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds enable-rewrite-related-field">
		            <label for="cpt_feeds">Feeds</label>
		            <label><input type="checkbox" id="cpt_feeds" name="cpt_feeds" <?php checked( $cpt_feeds, true ); ?> > Whether the feed permalink structure should be built for this post type.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds enable-rewrite-related-field">
		            <label for="cpt_pages">Pagination</label>
		            <label><input type="checkbox" id="cpt_pages" name="cpt_pages" <?php checked( $cpt_pages, true ); ?> > Whether the permalink structure should provide for pagination.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds enable-rewrite-related-field">
		            <label for="cpt_ep_mask">Endpoint mask</label>
		            <label><input type="checkbox" id="cpt_ep_mask" name="cpt_ep_mask" <?php checked( $cpt_ep_mask, true ); ?> > Assign an <a href="https://developer.wordpress.org/reference/functions/register_post_type/#rewrite" target="_blank">endpoint mask</a> for this post type. Default: <em>EP_PERMALINK</em>.</label>
		            <label for="cpt_ep_mask_custom" class="input-heading optional-text-input" style="display:none;">Custom endpoint mask</label>
		            <input type="text" name="cpt_ep_mask_custom" id="cpt_ep_mask_custom" value="<?php echo $cpt_ep_mask_custom; ?>" placeholder="Enter constant" style="display:none;" />
		    	</div>
		    </div>
	    </div>

	    <div id="tab-content-restapi" class="tab-content">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-full-width">
		            <label for="cpt_show_in_rest">Show in REST</label>
		            <label><input type="checkbox" id="cpt_show_in_rest" name="cpt_show_in_rest" <?php checked( $cpt_show_in_rest, true ); ?> > Whether to expose this post type in the REST API. Must be enabled to use the block editor.</label>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds show-in-rest-related-field" style="display:none;">
		            <label for="cpt_rest_base"> REST API base slug</label>
		            <input type="text" name="cpt_rest_base" id="cpt_rest_base" value="<?php echo $cpt_rest_base; ?>" />
		            <div class="input-description">To change the base URL of REST API route. Default: <em>post type key/slug</em>.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds show-in-rest-related-field" style="display:none;">
		            <label for="cpt_rest_namespace"> REST API namespace</label>
		            <input type="text" name="cpt_rest_namespace" id="cpt_rest_namespace" value="<?php echo $cpt_rest_namespace; ?>" />
		            <div class="input-description">To change the namespace URL of REST API route. Default: <em>wp/v2</em>.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds show-in-rest-related-field" style="display:none;">
		            <label for="cpt_rest_controller_class"> REST API controller class</label>
		            <input type="text" name="cpt_rest_controller_class" id="cpt_rest_controller_class" value="<?php echo $cpt_rest_controller_class; ?>" />
		            <div class="input-description">REST API controller class name. Default: <em>WP_REST_Posts_Controller</em>.</div>
		    	</div>		    	
		    </div>
	    </div>
	    
	    <div id="tab-content-labels" class="tab-content">
	    	<div class="cct-form-wrapper">
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_add_new">Add new</label>
		            <input type="text" name="cpt_label_add_new" id="cpt_label_add_new" class="cpt-labels-default" value="<?php echo $cpt_label_add_new; ?>" />
		            <div class="input-description">Default is ‘Add New’ for both hierarchical and non-hierarchical types.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_add_new_item">Add new item</label>
		            <input type="text" name="cpt_label_add_new_item" id="cpt_label_add_new_item" class="cpt-labels-singular" value="<?php echo $cpt_label_add_new_item; ?>" placeholder="e.g. Add New Movie" />
		            <div class="input-description">Label for adding a new singular item.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_edit_item">Edit item</label>
		            <input type="text" name="cpt_label_edit_item" id="cpt_label_edit_item" class="cpt-labels-singular" value="<?php echo $cpt_label_edit_item; ?>" placeholder="e.g. Edit Movie" />
		            <div class="input-description">Label for editing a singular item.</div>
		    	</div>

		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_new_item">New item</label>
		            <input type="text" name="cpt_label_new_item" id="cpt_label_new_item" class="cpt-labels-singular" value="<?php echo $cpt_label_new_item; ?>" placeholder="e.g. New Movie" />
		            <div class="input-description">Label for the new item page title.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_view_item">View item</label>
		            <input type="text" name="cpt_label_view_item" id="cpt_label_view_item" class="cpt-labels-singular" value="<?php echo $cpt_label_view_item; ?>" placeholder="e.g. View Movie" />
		            <div class="input-description">Label for viewing a singular item.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_view_items">View items</label>
		            <input type="text" name="cpt_label_view_items" id="cpt_label_view_items" class="cpt-labels-plural" value="<?php echo $cpt_label_view_items; ?>" placeholder="e.g. View Movies" />
		            <div class="input-description">Label for viewing post type archives.</div>
		    	</div>

		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_search_items">Search items</label>
		            <input type="text" name="cpt_label_search_items" id="cpt_label_search_items" class="cpt-labels-plural" value="<?php echo $cpt_label_search_items; ?>" placeholder="e.g. Search Movies" />
		            <div class="input-description">Label for searching plural items.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_not_found">Not found</label>
		            <input type="text" name="cpt_label_not_found" id="cpt_label_not_found" class="cpt-labels-plural" value="<?php echo $cpt_label_not_found; ?>" placeholder="e.g. No movies found" />
		            <div class="input-description">Label used when no items are found.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_not_found_in_trash">Not found in Trash</label>
		            <input type="text" name="cpt_label_not_found_in_trash" id="cpt_label_not_found_in_trash" class="cpt-labels-plural" value="<?php echo $cpt_label_not_found_in_trash; ?>" placeholder="e.g. No movies found in Trash" />
		            <div class="input-description">Label used when no items are in the Trash.</div>
		    	</div>

		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_parent_item_colon">Parent item prefix</label>
		            <input type="text" name="cpt_label_parent_item_colon" id="cpt_label_parent_item_colon" class="cpt-labels-singular" value="<?php echo $cpt_label_parent_item_colon; ?>" placeholder="e.g. Parent Movie:" />
		            <div class="input-description">Label used to prefix parents of hierarchical items. Not used on non-hierarchical post types.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_all_items">All items</label>
		            <input type="text" name="cpt_label_all_items" id="cpt_label_all_items" class="cpt-labels-plural" value="<?php echo $cpt_label_all_items; ?>" placeholder="e.g. All Movies" />
		            <div class="input-description">Label to signify all items in a submenu link.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_archives">Nav menu archives</label>
		            <input type="text" name="cpt_label_archives" id="cpt_label_archives" class="cpt-labels-singular" value="<?php echo $cpt_label_archives; ?>" placeholder="e.g. Movie Archives" />
		            <div class="input-description">Label for archives in nav menus.</div>
		    	</div>
		    	
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_attributes">Attributes meta box</label>
		            <input type="text" name="cpt_label_attributes" id="cpt_label_attributes" class="cpt-labels-singular" value="<?php echo $cpt_label_attributes; ?>" placeholder="e.g. Movie Attributes" />
		            <div class="input-description">Label for the attributes meta box.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_insert_into_item">Media frame button</label>
		            <input type="text" name="cpt_label_insert_into_item" id="cpt_label_insert_into_item" class="cpt-labels-singular" value="<?php echo $cpt_label_insert_into_item; ?>" placeholder="e.g. Insert into movie" />
		            <div class="input-description">Label for the media frame button.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_uploaded_to_this_item">Media frame filter</label>
		            <input type="text" name="cpt_label_uploaded_to_this_item" id="cpt_label_uploaded_to_this_item" class="cpt-labels-singular" value="<?php echo $cpt_label_uploaded_to_this_item; ?>" placeholder="e.g. Uploaded to this movie" />
		            <div class="input-description">Label for the media frame filter.</div>
		    	</div>
		    	
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_featured_image">Featured image meta box</label>
		            <input type="text" name="cpt_label_featured_image" id="cpt_label_featured_image" class="cpt-labels-default" value="<?php echo $cpt_label_featured_image; ?>" placeholder="e.g. Featured image" />
		            <div class="input-description">Label for the featured image meta box title.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_set_featured_image">Set featured image</label>
		            <input type="text" name="cpt_label_set_featured_image" id="cpt_label_set_featured_image" class="cpt-labels-default" value="<?php echo $cpt_label_set_featured_image; ?>" placeholder="e.g. Set featured image" />
		            <div class="input-description">Label for removing the featured image.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_remove_featured_image">Remove featured image</label>
		            <input type="text" name="cpt_label_remove_featured_image" id="cpt_label_remove_featured_image" class="cpt-labels-default" value="<?php echo $cpt_label_remove_featured_image; ?>" placeholder="e.g. Remove featured image" />
		            <div class="input-description">Default is ‘Add New’ for both hierarchical and non-hierarchical types.</div>
		    	</div>
		    	
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_use_featured_image">Use as featured image</label>
		            <input type="text" name="cpt_label_use_featured_image" id="cpt_label_use_featured_image" class="cpt-labels-default" value="<?php echo $cpt_label_use_featured_image; ?>" placeholder="e.g. Use as featured image" />
		            <div class="input-description">Label in the media frame for using a featured image.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_menu_name">Menu name</label>
		            <input type="text" name="cpt_label_menu_name" id="cpt_label_menu_name" class="cpt-labels-plural" value="<?php echo $cpt_label_menu_name; ?>" placeholder="e.g. Movies" />
		            <div class="input-description">Label for the menu name. Default is the plural name of the post type.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_filter_items_list">Filter items list</label>
		            <input type="text" name="cpt_label_filter_items_list" id="cpt_label_filter_items_list" class="cpt-labels-plural" value="<?php echo $cpt_label_filter_items_list; ?>" placeholder="e.g. Filter movies list" />
		            <div class="input-description">Label for the table views hidden heading.</div>
		    	</div>
		    	
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_filter_by_date">Filter by date</label>
		            <input type="text" name="cpt_label_filter_by_date" id="cpt_label_filter_by_date" class="cpt-labels-default" value="<?php echo $cpt_label_filter_by_date; ?>" placeholder="e.g. Filter by date" />
		            <div class="input-description">Label for the date filter in list tables.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_items_list_navigation">Items list navigation</label>
		            <input type="text" name="cpt_label_items_list_navigation" id="cpt_label_items_list_navigation" class="cpt-labels-plural" value="<?php echo $cpt_label_items_list_navigation; ?>" placeholder="e.g. Movies list navigation" />
		            <div class="input-description">Label for the table pagination hidden heading.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_items_list">Item list</label>
		            <input type="text" name="cpt_label_items_list" id="cpt_label_items_list" class="cpt-labels-plural" value="<?php echo $cpt_label_items_list; ?>" placeholder="e.g. Movies list" />
		            <div class="input-description">Label for the table hidden heading.</div>
		    	</div>
		    	
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_item_published">Item published</label>
		            <input type="text" name="cpt_label_item_published" id="cpt_label_item_published" class="cpt-labels-singular" value="<?php echo $cpt_label_item_published; ?>" placeholder="e.g. Movie published" />
		            <div class="input-description">Label used when an item is published.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_item_published_privately">Item published privately</label>
		            <input type="text" name="cpt_label_item_published_privately" id="cpt_label_item_published_privately" class="cpt-labels-singular" value="<?php echo $cpt_label_item_published_privately; ?>" placeholder="e.g. Movie published privately" />
		            <div class="input-description">Label used when an item is published with private visibility.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_item_reverted_to_draft">Item reverted to draft</label>
		            <input type="text" name="cpt_label_item_reverted_to_draft" id="cpt_label_item_reverted_to_draft" class="cpt-labels-singular" value="<?php echo $cpt_label_item_reverted_to_draft; ?>" placeholder="e.g. Movie reverted to draft" />
		            <div class="input-description">Label used when an item is switched to a draft.</div>
		    	</div>
		    	
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_item_scheduled">Item scheduled</label>
		            <input type="text" name="cpt_label_item_scheduled" id="cpt_label_item_scheduled" class="cpt-labels-singular" value="<?php echo $cpt_label_item_scheduled; ?>" placeholder="e.g. Movie scheduled" />
		            <div class="input-description">Label used when an item is scheduled for publishing.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_item_updated">Item updated</label>
		            <input type="text" name="cpt_label_item_updated" id="cpt_label_item_updated" class="cpt-labels-singular" value="<?php echo $cpt_label_item_updated; ?>" placeholder="e.g. Movie updated" />
		            <div class="input-description">Label used when an item is updated.</div>
		    	</div>
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_item_link">Item link</label>
		            <input type="text" name="cpt_label_item_link" id="cpt_label_item_link" class="cpt-labels-singular" value="<?php echo $cpt_label_item_link; ?>" placeholder="e.g. Movie Link" />
		            <div class="input-description">Title for a navigation link block variation.</div>
		    	</div>
		    	
		    	<div class="cct-form-input cct-form-input-thirds">
		            <label for="cpt_label_item_link_description">Item link description</label>
		            <input type="text" name="cpt_label_item_link_description" id="cpt_label_item_link_description" class="cpt-labels-singular" value="<?php echo $cpt_label_item_link_description; ?>" placeholder="e.g. A link to a movie" />
		            <div class="input-description">Description for a navigation link block variation.</div>
		    	</div>
		    </div>
	    </div>
	    
	</div>
  
</section>
<?php