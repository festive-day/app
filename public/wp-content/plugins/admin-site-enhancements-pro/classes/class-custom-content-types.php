<?php

namespace ASENHA\Classes;

/**
 * Class for Custom Content Types module
 *
 * @since 6.9.5
 */
class Custom_Content_Types {

    /**
     * Register CPTs for registering additional custom post types and custom taxonomies
     * 
     * @since 5.1.0
     */
    public function register_asenha_cpts__premium_only() {
        // Custom Post Types
        register_post_type(
            'asenha_cpt',
            array(
                'labels'                => array(
                    'name'                  => __( 'Custom Post Types', 'admin-site-enhancements' ),
                    'singular_name'         => __( 'Custom Post Type', 'admin-site-enhancements' ),
                    'add_new'               => __( 'Add New', 'admin-site-enhancements' ),
                    'add_new_item'          => __( 'Add New Post Type', 'admin-site-enhancements' ),
                    'new_item'              => __( 'New Post Type', 'admin-site-enhancements' ),
                    'edit_item'             => __( 'Edit Post Type', 'admin-site-enhancements' ),
                    'update_item'           => __( 'Update Post Type', 'admin-site-enhancements' ),
                    'view_item'             => __( 'View Post Type', 'admin-site-enhancements' ),
                    'search_items'          => __( 'Search Post Types', 'admin-site-enhancements' ),
                    'not_found'             => __( 'No Custom Post Types found', 'admin-site-enhancements' ),
                    'not_found_in_trash'    => __( 'No Custom Post Types found in Trash', 'admin-site-enhancements' ),
                ),
                'public'            => false,
                'show_ui'           => true,
                'show_in_menu'      => 'options-general.php',
                'hierarchical'      => false,
                // Hide 'title' and 'editor'
                'supports'          => false,
                'rewrite'           => false,
                'query_var'         => false,
                'can_export'        => true,
                // Allow administrator to edit/read/delete without adding custom capabilites
                'map_meta_cap'      => true,
                // Limit CRUD operations to administrators
                'capabilities'      => array(
                    // Meta capabilities.
                    'edit_post'              => 'edit_asenha_cpt',
                    'read_post'              => 'read_asenha_cpt',
                    'delete_post'            => 'delete_asenha_cpt',

                    // Primitive capabilities used outside of map_meta_cap():
                    'edit_posts'             => 'manage_options',
                    'edit_others_posts'      => 'manage_options',
                    'publish_posts'          => 'manage_options',
                    'read_private_posts'     => 'manage_options',

                    // Primitive capabilities used within map_meta_cap():
                    'read'                   => 'read',
                    'delete_posts'           => 'manage_options',
                    'delete_private_posts'   => 'manage_options',
                    'delete_published_posts' => 'manage_options',
                    'delete_others_posts'    => 'manage_options',
                    'edit_private_posts'     => 'manage_options',
                    'edit_published_posts'   => 'manage_options',
                    'create_posts'           => 'manage_options',               
                ),
            )
        );

        // Custom Taxonomies
        register_post_type(
            'asenha_ctax',
            array(
                'labels'                => array(
                    'name'                  => __( 'Custom Taxonomies', 'admin-site-enhancements' ),
                    'singular_name'         => __( 'Custom Taxonomy', 'admin-site-enhancements' ),
                    'add_new'               => __( 'Add New', 'admin-site-enhancements' ),
                    'add_new_item'          => __( 'Add New Taxonomy', 'admin-site-enhancements' ),
                    'edit_item'             => __( 'Edit Taxonomy', 'admin-site-enhancements' ),
                    'new_item'              => __( 'New Taxonomy', 'admin-site-enhancements' ),
                    'view_item'             => __( 'View Taxonomy', 'admin-site-enhancements' ),
                    'search_items'          => __( 'Search Taxonomies', 'admin-site-enhancements' ),
                    'not_found'             => __( 'No Custom Taxonomies found', 'admin-site-enhancements' ),
                    'not_found_in_trash'    => __( 'No Custom Taxonomies found in Trash', 'admin-site-enhancements' ),
                ),
                'public'            => false,
                'show_ui'           => true,
                'show_in_menu'      => 'options-general.php',
                'hierarchical'      => false,
                // Hide 'title' and 'editor'
                'supports'          => false,
                'rewrite'           => false,
                'query_var'         => false,
                'can_export'        => true,
                // Allow administrator to edit/read/delete without adding custom capabilites
                'map_meta_cap'      => true,
                // Limit CRUD operations to administrators
                'capabilities'      => array(
                    // Meta capabilities.
                    'edit_post'              => 'edit_asenha_ctax',
                    'read_post'              => 'read_asenha_ctax',
                    'delete_post'            => 'delete_asenha_ctax',

                    // Primitive capabilities used outside of map_meta_cap():
                    'edit_posts'             => 'manage_options',
                    'edit_others_posts'      => 'manage_options',
                    'publish_posts'          => 'manage_options',
                    'read_private_posts'     => 'manage_options',

                    // Primitive capabilities used within map_meta_cap():
                    'read'                   => 'read',
                    'delete_posts'           => 'manage_options',
                    'delete_private_posts'   => 'manage_options',
                    'delete_published_posts' => 'manage_options',
                    'delete_others_posts'    => 'manage_options',
                    'edit_private_posts'     => 'manage_options',
                    'edit_published_posts'   => 'manage_options',
                    'create_posts'           => 'manage_options',               
                ),
            )
        );

    }
    
    /**
     * Add meta boxes to the creation screen of CPTs and custom taxonomies
     * 
     * @since 5.1.0
     */
    public function add_meta_boxes_for_asenha_cpts__premium_only() {
        
        add_meta_box(
            'asenha_cpt_meta_box',
            __( 'Post Type Options', 'admin-site-enhancements' ),
            [ $this, 'render_cpt_meta_box__premium_only' ],
            'asenha_cpt',
            'advanced',
            'high'
        );
        
        add_meta_box(
            'asenha_ctax_meta_box',
            __( 'Taxonomy Options', 'admin-site-enhancements' ),
            [ $this, 'render_ctax_meta_box__premium_only' ],
            'asenha_ctax',
            'advanced',
            'high'
        );

        // add_meta_box(
        //  'asenha_cpt_references_meta_box',
        //  'References',
        //  [ $this, 'render_cpt_references_meta_box__premium_only' ],
        //  'asenha_cpt',
        //  'side',
        //  'low'
        // );
        
        // add_meta_box(
        //  'asenha_ctax_references_meta_box',
        //  'References',
        //  [ $this, 'render_ctax_references_meta_box__premium_only' ],
        //  'asenha_ctax',
        //  'side',
        //  'low'
        // );
        
    }
    
    /**
     * Render options for custom post type
     * 
     * @since 5.1.0
     */
    public function render_cpt_meta_box__premium_only() {

        // Get or set the values of CPT fields
        require_once ASENHA_PATH . 'includes/premium/custom-content/cpt/cpt-fields-values.php';

        // Render metabox to input / update CPT fields
        require_once ASENHA_PATH . 'includes/premium/custom-content/cpt/cpt-form-fields.php';
        
    }

    /**
     * Render options for taxonomies
     * 
     * @since 5.1.0
     */
    public function render_ctax_meta_box__premium_only() {

        // Get or set the values of custom taxonomy fields
        require_once ASENHA_PATH . 'includes/premium/custom-content/ctax/ctax-fields-values.php';

        // Render metabox to input / update custom taxonomy fields
        require_once ASENHA_PATH . 'includes/premium/custom-content/ctax/ctax-form-fields.php';

    }

    /**
     * Render documentation metabox for the create CPT screen
     * 
     * @since 5.1.0
     */
    public function render_cpt_references_meta_box__premium_only() {
        ?>
        <div class="tips-wrapper">
            Message about upgrading to ASENHA PRO
            <ul>
                <li><span class="dashicons dashicons-yes"></span> Benefit #1</li>
            </ul>
            <a href="#" class="button">Upgrade Now</a>
        </div>
        <?php
    }
    
    /**
     * Render documentation metabox for the create CPT screen
     * 
     * @since 5.1.0
     */
    public function render_ctax_references_meta_box__premium_only() {
        ?>
        <div class="references-wrapper">
            Message about upgrading to ASENHA PRO
            <ul>
                <li><span class="dashicons dashicons-yes"></span> Benefit #1</li>
            </ul>
            <a href="#" class="button">Upgrade Now</a>
        </div>
        <?php
    }
    
    /**
     * Save fields for custom post types and taxonomies
     * 
     * @link https://developer.wordpress.org/reference/hooks/save_post/
     * @since 5.1.0
     */
    public function save_asenha_cpt_ctax_optionp_fields__premium_only( $post_id, $post, $update ) {

        $common_methods = new Common_Methods;

        // Sanitize and save custom post type, custom taxonomy or options page fields values in post meta
        require_once ASENHA_PATH . 'includes/premium/custom-content/save-cpt-ctax-optionp-fields.php';
        
    }
    
    /**
     * Flush rewrite rules after creating or updating CPTs and custom taxonomies
     * 
     * @since 5.1.0
     */
    public function asenha_cpts_flush_rewrite_rules__premium_only() {
        $options = get_option( ASENHA_SLUG_U );
        
        if ( true === $options['custom_content_types_flush_rewrite_rules_needed'] ) {
            flush_rewrite_rules();
            $options['custom_content_types_flush_rewrite_rules_needed'] = false;
            update_option( ASENHA_SLUG_U, $options, true );
        }
    }
    
    /**
     * Register CPTs and custom taxonomies created by custom content module
     * 
     * @since 5.1.0
     */
    public function register_cpts_and_ctaxs_created_by_asenha__premium_only() {
        
        // ====== REGISTER CPTS ======
        
        // Get CPT posts in wp_posts table added via Settings >> Custom Post Types
        $asenha_cpts_posts = get_posts( array(
            'numberposts'       => -1,
            'post_type'         => 'asenha_cpt',
            'post_status'       => 'publish',
            'suppress_filters'  => false,       
        ) );

        // Get CPT posts meta and use them to register the CPTs
        if ( $asenha_cpts_posts ) {
            foreach( $asenha_cpts_posts as $asenha_cpt ) {
                
                $post_type_key = get_post_meta( $asenha_cpt->ID, 'cpt_key', true );
                
                // --- Set LABELS ---

                $labels = array(
                    'name'                      => get_post_meta( $asenha_cpt->ID, 'cpt_plural_name', true ),
                    'singular_name'             => get_post_meta( $asenha_cpt->ID, 'cpt_singular_name', true ),
                    'add_new'                   => get_post_meta( $asenha_cpt->ID, 'cpt_label_add_new', true ),
                    'add_new_item'              => get_post_meta( $asenha_cpt->ID, 'cpt_label_add_new_item', true ),
                    'edit_item'                 => get_post_meta( $asenha_cpt->ID, 'cpt_label_edit_item', true ),
                    'new_item'                  => get_post_meta( $asenha_cpt->ID, 'cpt_label_new_item', true ),
                    'view_item'                 => get_post_meta( $asenha_cpt->ID, 'cpt_label_view_item', true ),
                    'view_items'                => get_post_meta( $asenha_cpt->ID, 'cpt_label_view_items', true ),
                    'search_items'              => get_post_meta( $asenha_cpt->ID, 'cpt_label_search_items', true ),
                    'not_found'                 => get_post_meta( $asenha_cpt->ID, 'cpt_label_not_found', true ),
                    'not_found_in_trash'        => get_post_meta( $asenha_cpt->ID, 'cpt_label_not_found_in_trash', true ),
                    'parent_item_colon'         => get_post_meta( $asenha_cpt->ID, 'cpt_label_parent_item_colon', true ),
                    'all_items'                 => get_post_meta( $asenha_cpt->ID, 'cpt_label_all_items', true ),
                    'archives'                  => get_post_meta( $asenha_cpt->ID, 'cpt_label_archives', true ),
                    'attributes'                => get_post_meta( $asenha_cpt->ID, 'cpt_label_attributes', true ),
                    'insert_into_item'          => get_post_meta( $asenha_cpt->ID, 'cpt_label_insert_into_item', true ),
                    'uploaded_to_this_item'     => get_post_meta( $asenha_cpt->ID, 'cpt_label_uploaded_to_this_item', true ),
                    'featured_image'            => get_post_meta( $asenha_cpt->ID, 'cpt_label_featured_image', true ),
                    'set_featured_image'        => get_post_meta( $asenha_cpt->ID, 'cpt_label_set_featured_image', true ),
                    'remove_featured_image'     => get_post_meta( $asenha_cpt->ID, 'cpt_label_remove_featured_image', true ),
                    'use_featured_image'        => get_post_meta( $asenha_cpt->ID, 'cpt_label_use_featured_image', true ),
                    'menu_name'                 => get_post_meta( $asenha_cpt->ID, 'cpt_label_menu_name', true ),
                    'filter_items_list'         => get_post_meta( $asenha_cpt->ID, 'cpt_label_filter_items_list', true ),
                    'filter_by_date'            => get_post_meta( $asenha_cpt->ID, 'cpt_label_filter_by_date', true ),
                    'items_list_navigation'     => get_post_meta( $asenha_cpt->ID, 'cpt_label_items_list_navigation', true ),
                    'items_list'                => get_post_meta( $asenha_cpt->ID, 'cpt_label_items_list', true ),
                    'item_published'            => get_post_meta( $asenha_cpt->ID, 'cpt_label_item_published', true ),
                    'item_published_privately'  => get_post_meta( $asenha_cpt->ID, 'cpt_label_item_published_privately', true ),
                    'item_reverted_to_draft'    => get_post_meta( $asenha_cpt->ID, 'cpt_label_item_reverted_to_draft', true ),
                    'item_scheduled'            => get_post_meta( $asenha_cpt->ID, 'cpt_label_item_scheduled', true ),
                    'item_updated'              => get_post_meta( $asenha_cpt->ID, 'cpt_label_item_updated', true ),
                    'item_link'                 => get_post_meta( $asenha_cpt->ID, 'cpt_label_item_link', true ),
                    'item_link_description'     => get_post_meta( $asenha_cpt->ID, 'cpt_label_item_link_description', true ),
                );

                // --- Set ARGS ---
                
                // Get capability type

                $cpt_capability_type = get_post_meta( $asenha_cpt->ID, 'cpt_capability_type', true );
                $cpt_custom_cap_slug_singular = get_post_meta( $asenha_cpt->ID, 'cpt_capability_type_custom_slug_singular', true );
                $cpt_custom_cap_slug_plural = get_post_meta( $asenha_cpt->ID, 'cpt_capability_type_custom_slug_plural', true );

                if ( $cpt_capability_type == 'post' || $cpt_capability_type == 'page' ) {
                    $capability_type = $cpt_capability_type;
                } elseif ( $cpt_capability_type == 'custom' ) {
                    if ( ! empty( $cpt_custom_cap_slug_singular ) && ! empty( $cpt_custom_cap_slug_plural ) ) {
                        $capability_type = array( $cpt_custom_cap_slug_singular, $cpt_custom_cap_slug_plural );                 
                    } else if ( ! empty( $cpt_custom_cap_slug_singular )  && empty( $cpt_custom_cap_slug_plural ) ) {
                        $capability_type = $cpt_custom_cap_slug_singular;
                    } else {
                        $capability_type = 'post';
                    }
                } else {
                    $capability_type = 'post';
                }
                
                // Define query_var value to use
                
                $cpt_query_var = get_post_meta( $asenha_cpt->ID, 'cpt_query_var', true );
                $cpt_use_custom_query_var_string = get_post_meta( $asenha_cpt->ID, 'cpt_use_custom_query_var_string', true );
                $cpt_query_var_string = get_post_meta( $asenha_cpt->ID, 'cpt_query_var_string', true );

                if ( $cpt_query_var ) {
                    if ( $cpt_use_custom_query_var_string ) {
                        if ( ! empty( $cpt_query_var_string ) ) {
                            $query_var = $cpt_query_var_string;
                        } else {
                            $query_var = true;
                        }
                    } else {
                        $query_var = true;
                    }
                } else {
                    $query_var = false;
                }

                // Define has_archive value to use
                
                $cpt_has_archive = get_post_meta( $asenha_cpt->ID, 'cpt_has_archive', true );
                $cpt_use_custom_archive_slug = get_post_meta( $asenha_cpt->ID, 'cpt_use_custom_archive_slug', true );
                $cpt_has_archive_custom_slug = get_post_meta( $asenha_cpt->ID, 'cpt_has_archive_custom_slug', true );

                if ( $cpt_has_archive ) {
                    if ( $cpt_use_custom_archive_slug ) {
                        if ( ! empty( $cpt_has_archive_custom_slug ) ) {
                            $has_archive = $cpt_has_archive_custom_slug;
                        } else {
                            $has_archive = true;
                        }
                    } else {
                        $has_archive = true;
                    }
                } else {
                    $has_archive = false;
                }
                
                // Define rewrite value to use

                $cpt_rewrite = get_post_meta( $asenha_cpt->ID, 'cpt_rewrite', true );
                $cpt_use_custom_rewrite_slug = get_post_meta( $asenha_cpt->ID, 'cpt_use_custom_rewrite_slug', true );
                $cpt_rewrite_custom_slug = get_post_meta( $asenha_cpt->ID, 'cpt_rewrite_custom_slug', true );
                $cpt_with_front = ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_with_front', true ) ) ? true : false;
                $cpt_feeds = ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_feeds', true ) ) ? true : false;
                $cpt_pages = ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_pages', true ) ) ? true : false;
                $cpt_ep_mask = get_post_meta( $asenha_cpt->ID, 'cpt_ep_mask', true );
                $cpt_ep_mask_custom = get_post_meta( $asenha_cpt->ID, 'cpt_ep_mask_custom', true );

                if ( $cpt_rewrite ) {
                    if ( $cpt_use_custom_rewrite_slug || $cpt_with_front || $cpt_pages || $cpt_ep_mask ) {
                        $rewrite = array(
                            'slug'          => ( $cpt_use_custom_rewrite_slug ) ? $cpt_rewrite_custom_slug : $post_type_key,
                            'with_front'    => $cpt_with_front,
                            'feeds'         => $cpt_feeds,
                            'pages'         => $cpt_pages,
                        );
                        if ( $cpt_ep_mask && ! empty( $cpt_ep_mask_custom ) ) {
                            $rewrite['ep_mask'] = $cpt_ep_mask_custom;
                        }
                    } else {
                        $rewrite = true;
                    }
                } else {
                    $rewrite = false;
                }
                
                // Supports
                $cpt_supports_raw = get_post_meta( $asenha_cpt->ID, 'cpt_supports', true );
                if ( is_array( $cpt_supports_raw ) && in_array( 'none', $cpt_supports_raw ) ) {
                    $supports = false;
                } else {
                    $supports = $cpt_supports_raw;
                }

                $args = array(
                    'labels'                => $labels,
                    'public'                => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_public', true ) ) ? true : false,
                    'hierarchical'          => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_hierarchical', true ) ) ? true : false,
                    'capability_type'       => $capability_type,
                    'map_meta_cap'          => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_map_meta_cap', true ) ) ? true : NULL,
                    'supports'              => $supports,
                    'taxonomies'            => get_post_meta( $asenha_cpt->ID, 'cpt_taxonomies', true ),
                    'show_ui'               => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_show_ui', true ) ) ? true : false,
                    'show_in_menu'          => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_show_in_menu', true ) ) ? true : false,
                    'menu_position'         => 5,
                    'menu_icon'             => get_post_meta( $asenha_cpt->ID, 'cpt_menu_icon', true ),
                    'show_in_admin_bar'     => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_show_in_admin_bar', true ) ) ? true : false,
                    'show_in_nav_menus'     => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_show_in_nav_menus', true ) ) ? true : false,
                    'can_export'            => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_can_export', true ) ) ? true : false,
                    'delete_with_user'      => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_delete_with_user', true ) ) ? true : false,
                    'publicly_queryable'    => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_publicly_queryable', true ) ) ? true : false,
                    'query_var'             => $query_var,
                    'has_archive'           => $has_archive,
                    'rewrite'               => $rewrite,
                    'show_in_rest'          => ( '1' == get_post_meta( $asenha_cpt->ID, 'cpt_show_in_rest', true ) ) ? true : false,
                );
                
                if ( ! empty( get_post_meta( $asenha_cpt->ID, 'cpt_rest_base', true ) ) ) {
                    $args['rest_base'] = get_post_meta( $asenha_cpt->ID, 'cpt_rest_base', true );
                }

                if ( ! empty( get_post_meta( $asenha_cpt->ID, 'cpt_rest_base', true ) ) ) {
                    $args['rest_namespace'] = get_post_meta( $asenha_cpt->ID, 'cpt_rest_namespace', true );
                }

                if ( ! empty( get_post_meta( $asenha_cpt->ID, 'cpt_rest_base', true ) ) ) {
                    $args['rest_controller_class'] = get_post_meta( $asenha_cpt->ID, 'cpt_rest_controller_class', true );
                }
                                                
                register_post_type( $post_type_key, $args );
            }
        }

        // ====== REGISTER CUSTOM TAXONOMIES ======
        
        // Get Taxonomy posts in wp_posts table added via Settings >> Custom Taxonomies
        $asenha_ctaxs_posts = get_posts( array(
            'numberposts'       => -1,
            'post_type'         => 'asenha_ctax',
            'post_status'       => 'publish',
            'suppress_filters'  => false,       
        ) );
        
        // Get CPT posts meta and use them to register the CPTs
        if ( $asenha_ctaxs_posts ) {
            foreach( $asenha_ctaxs_posts as $asenha_ctax ) {
                
                $taxonomy_key = get_post_meta( $asenha_ctax->ID, 'ctax_key', true );

                $labels = array(
                    'name'                      => get_post_meta( $asenha_ctax->ID, 'ctax_plural_name', true ),
                    'singular_name'             => get_post_meta( $asenha_ctax->ID, 'ctax_singular_name', true ),
                    'menu_name'                 => get_post_meta( $asenha_ctax->ID, 'ctax_plural_name', true ),
                    'search_items'              => get_post_meta( $asenha_ctax->ID, 'ctax_label_search_items', true ),
                    'popular_items'             => get_post_meta( $asenha_ctax->ID, 'ctax_label_popular_items', true ),
                    'all_items'                 => get_post_meta( $asenha_ctax->ID, 'ctax_label_all_items', true ),
                    'parent_item'               => get_post_meta( $asenha_ctax->ID, 'ctax_label_parent_item', true ),
                    'parent_item_colon'         => get_post_meta( $asenha_ctax->ID, 'ctax_label_parent_item_colon', true ),
                    'name_field_description'    => get_post_meta( $asenha_ctax->ID, 'ctax_label_name_field_description', true ),
                    'slug_field_description'    => get_post_meta( $asenha_ctax->ID, 'ctax_label_slug_field_description', true ),
                    'parent_field_description'  => get_post_meta( $asenha_ctax->ID, 'ctax_label_parent_field_description', true ),
                    'desc_field_description'    => get_post_meta( $asenha_ctax->ID, 'ctax_label_desc_field_description', true ),
                    'edit_item'                 => get_post_meta( $asenha_ctax->ID, 'ctax_label_edit_item', true ),
                    'view_item'                 => get_post_meta( $asenha_ctax->ID, 'ctax_label_view_item', true ),
                    'update_item'               => get_post_meta( $asenha_ctax->ID, 'ctax_label_update_item', true ),
                    'add_new_item'              => get_post_meta( $asenha_ctax->ID, 'ctax_label_add_new_item', true ),
                    'new_item_name'             => get_post_meta( $asenha_ctax->ID, 'ctax_label_new_item_name', true ),
                    'separate_items_with_commas' => get_post_meta( $asenha_ctax->ID, 'ctax_label_separate_items_with_commas', true ),
                    'add_or_remove_items'       => get_post_meta( $asenha_ctax->ID, 'ctax_label_add_or_remove_items', true ),
                    'choose_from_most_used'     => get_post_meta( $asenha_ctax->ID, 'ctax_label_choose_from_most_used', true ),
                    'not_found'                 => get_post_meta( $asenha_ctax->ID, 'ctax_label_not_found', true ),
                    'no_terms'                  => get_post_meta( $asenha_ctax->ID, 'ctax_label_no_terms', true ),
                    'filter_by_item'            => get_post_meta( $asenha_ctax->ID, 'ctax_label_filter_by_item', true ),
                    'items_list_navigation'     => get_post_meta( $asenha_ctax->ID, 'ctax_label_items_list_navigation', true ),
                    'items_list'                => get_post_meta( $asenha_ctax->ID, 'ctax_label_items_list', true ),
                    'most_used'                 => get_post_meta( $asenha_ctax->ID, 'ctax_label_most_used', true ),
                    'back_to_items'             => get_post_meta( $asenha_ctax->ID, 'ctax_label_back_to_items', true ),
                    'item_link'                 => get_post_meta( $asenha_ctax->ID, 'ctax_label_item_link', true ),
                    'item_link_description'     => get_post_meta( $asenha_ctax->ID, 'ctax_label_item_link_description', true ),
                );
                
                // --- Set ARGS ---

                // Define query_var value to use
                
                $ctax_query_var = get_post_meta( $asenha_ctax->ID, 'ctax_query_var', true );
                $ctax_use_custom_query_var_string = get_post_meta( $asenha_ctax->ID, 'ctax_use_custom_query_var_string', true );
                $ctax_query_var_string = get_post_meta( $asenha_ctax->ID, 'ctax_query_var_string', true );

                if ( $ctax_query_var ) {
                    if ( $ctax_use_custom_query_var_string ) {
                        if ( ! empty( $ctax_query_var_string ) ) {
                            $query_var = $ctax_query_var_string;
                        } else {
                            $query_var = true;
                        }
                    } else {
                        $query_var = true;
                    }
                } else {
                    $query_var = false;
                }

                // Define rewrite value to use

                $ctax_rewrite = get_post_meta( $asenha_ctax->ID, 'ctax_rewrite', true );
                $ctax_use_custom_rewrite_slug = get_post_meta( $asenha_ctax->ID, 'ctax_use_custom_rewrite_slug', true );
                $ctax_rewrite_custom_slug = get_post_meta( $asenha_ctax->ID, 'ctax_rewrite_custom_slug', true );
                $ctax_with_front = ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_with_front', true ) ) ? true : false;
                $ctax_hierarchical_urls = ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_hierarchical_urls', true ) ) ? true : false;
                $ctax_ep_mask = get_post_meta( $asenha_ctax->ID, 'ctax_ep_mask', true );
                $ctax_ep_mask_custom = get_post_meta( $asenha_ctax->ID, 'ctax_ep_mask_custom', true );

                if ( $ctax_rewrite ) {
                    if ( $ctax_use_custom_rewrite_slug || $ctax_with_front || $ctax_hierarchical_urls || $ctax_ep_mask ) {
                        $rewrite = array(
                            'slug'          => ( $ctax_use_custom_rewrite_slug ) ? $ctax_rewrite_custom_slug : '',
                            'with_front'    => $ctax_with_front,
                            'hierarchical'  => $ctax_hierarchical_urls,
                        );
                        if ( $ctax_ep_mask && ! empty( $ctax_ep_mask_custom ) ) {
                            $rewrite['ep_mask'] = $ctax_ep_mask_custom;
                        }
                    } else {
                        $rewrite = true;
                    }
                } else {
                    $rewrite = false;
                }

                $args = array(
                    'labels'                    => $labels,
                    'public'                    => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_public', true ) ) ? true : false,
                    'publicly_queryable'        => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_publicly_queryable', true ) ) ? true : false,
                    'hierarchical'              => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_hierarchical', true ) ) ? true : false,
                    'show_ui'                   => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_show_ui', true ) ) ? true : false,
                    'show_in_menu'              => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_show_in_menu', true ) ) ? true : false,
                    'show_in_nav_menus'         => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_show_in_nav_menus', true ) ) ? true : false,
                    'show_tagcloud'             => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_show_tagcloud', true ) ) ? true : false,
                    'show_in_quick_edit'        => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_show_in_quick_edit', true ) ) ? true : false,
                    'show_admin_column'         => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_show_admin_column', true ) ) ? true : false,
                    'capabilities'              => array(
                                                        'manage_terms'  => 'manage_categories',
                                                        'edit_terms'    => 'manage_categories',
                                                        'delete_terms'  => 'manage_categories',
                                                        'assign_terms'  => 'edit_posts',
                                                    ),
                    'query_var'                 => $query_var,
                    'rewrite'                   => $rewrite,
                    'update_count_callback'     => '_update_post_term_count',
                    'sort'                      => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_sort', true ) ) ? true : false,
                    'show_in_rest'              => ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_show_in_rest', true ) ) ? true : false,
                );
                
                // Add default term for hierarchical taxonomies
                
                // if ( ( '1' == get_post_meta( $asenha_ctax->ID, 'ctax_hierarchical', true ) ) ) {
                //     $args['default_term'] = array(
                //                             'name'          => 'Uncategorized',
                //                             'slug'          => 'uncategorized',
                //                             'description'   => 'Not yet categorized',
                //                         );
                // }
                
                // Add optional arguments related to REST API
                
                if ( ! empty( get_post_meta( $asenha_ctax->ID, 'ctax_rest_base', true ) ) ) {
                    $args['rest_base'] = get_post_meta( $asenha_ctax->ID, 'ctax_rest_base', true );
                }

                if ( ! empty( get_post_meta( $asenha_ctax->ID, 'ctax_rest_namespace', true ) ) ) {
                    $args['rest_namespace'] = get_post_meta( $asenha_ctax->ID, 'ctax_rest_namespace', true );
                }

                if ( ! empty( get_post_meta( $asenha_ctax->ID, 'ctax_rest_controller_class', true ) ) ) {
                    $args['rest_controller_class'] = get_post_meta( $asenha_ctax->ID, 'ctax_rest_controller_class', true );
                }
                
                // Register custom taxonomy with their supported post types
                
                $supported_post_types = get_post_meta( $asenha_ctax->ID, 'ctax_post_types', true );
                
                register_taxonomy( $taxonomy_key, $supported_post_types, $args );
            }
        }

    }
    
    /**
     * Use plural name as CPT and Custom Taxonomy title in admin table
     * 
     * @link https://developer.wordpress.org/reference/hooks/wp_insert_post_data/
     * @since 5.1.0
     */
    public function use_plural_name_as_cpt_ctax_title__premium_only( $data ) {
        if ( ( $data['post_type'] == 'asenha_cpt' && isset( $_POST['cpt_plural_name'] ) ) 
            || ( $data['post_type'] == 'asenha_ctax' && isset( $_POST['ctax_plural_name'] ) )
        ) {
            if ( isset( $_POST['cpt_plural_name'] ) ) {
                $data['post_title'] = $_POST['cpt_plural_name'];
            } elseif ( isset( $_POST['ctax_plural_name'] ) ) {
                $data['post_title'] = $_POST['ctax_plural_name'];               
            }
        }
        
        if ( $data['post_type'] == 'options_page_config' && isset( $_POST['options_page_title'] ) ) {
                $data['post_title'] = $_POST['options_page_title'];
        }
        
        return $data;
    }

    /**
     * Define columns to show for CPT admin table
     *
     * @since  5.1.0
     */
    public function cpt_posts_define_columns__premium_only( $columns ) {

        $columns = array(
            'cb' => $columns['cb'], // Checkbox for bulk actions
            'basic' => 'Basic Info',
            'supports' => 'Supported Features',
            'taxonomies' => 'Supported Taxonomies',
            'author' => 'Author',
            'date' => 'Date',
        );
        return $columns;
    }

    /**
     * Render custom columns content for CPT admin table
     *
     * @since  5.1.0
     */
    public function cpt_posts_custom_columns_content__premium_only( $column_name, $id ) {
        
        $edit_link = get_edit_post_link();
        $singular_name = get_post_meta( get_the_ID(), 'cpt_singular_name', true );
        $plural_name = get_post_meta( get_the_ID(), 'cpt_plural_name', true );
        $description = get_post_meta( get_the_ID(), 'cpt_description', true );
        $description_separator = ( ! empty( get_post_meta( get_the_ID(), 'cpt_description', true ) ) ) ? '<br />' : '';
        $key = get_post_meta( get_the_ID(), 'cpt_key', true );
        $public = ( get_post_meta( get_the_ID(), 'cpt_public', true ) ) ? 'Public' : 'Not public';
        $hierarchical = ( get_post_meta( get_the_ID(), 'cpt_hierarchical', true ) ) ? ' | Hierarchical' : ' | Not hierarchical';

        $supports_raw = get_post_meta( get_the_ID(), 'cpt_supports', true );
        $supports = '';
        $separator = ', ';

        if ( is_array( $supports_raw ) ) {
            if ( in_array( 'none', $supports_raw ) ) {
                $supports = 'None';
            } else {
                foreach( $supports_raw as $support_raw ) {
                    if ( $support_raw == 'title' ) {
                        $supports .= 'Title' . $separator;
                    }
                    if ( $support_raw == 'editor' ) {
                        $supports .= 'Editor' . $separator;
                    }
                    if ( $support_raw == 'author' ) {
                        $supports .= 'Author' . $separator;
                    }
                    if ( $support_raw == 'thumbnail' ) {
                        $supports .= 'Featured Image' . $separator;
                    }
                    if ( $support_raw == 'excerpt' ) {
                        $supports .= 'Excerpt' . $separator;
                    }
                    if ( $support_raw == 'trackbacks' ) {
                        $supports .= 'Trackback' . $separator;
                    }
                    if ( $support_raw == 'custom-fields' ) {
                        $supports .= 'Custom Fields' . $separator;
                    }
                    if ( $support_raw == 'comments' ) {
                        $supports .= 'Comments' . $separator;
                    }
                    if ( $support_raw == 'revisions' ) {
                        $supports .= 'Revisions' . $separator;
                    }
                    if ( $support_raw == 'page-attributes' ) {
                        $supports .= 'Page Attributes' . $separator;
                    }
                    if ( $support_raw == 'post-formats' ) {
                        $supports .= 'Post Formats' . $separator;
                    }
                }
            }
        }

        $supports = trim( trim( $supports ), ',' );
        
        $taxonomies = get_taxonomies( array(), 'objects' );
        $supported_taxonomies_slugs = array();
        foreach ( $taxonomies as $taxonomy_slug => $taxonomy_object  ) {
            foreach( $taxonomy_object->object_type as $object_type_slug ) {
                if ( $key == $object_type_slug ) {
                    $supported_taxonomies_slugs[] = $taxonomy_slug;
                }
            }
        }
        
        $taxonomies = '';
        $taxonomy_page_link_suffix = ( ! in_array( $key, array( 'post', 'page' ) ) ) ? '&post_type=' . $key : '' ;
        if ( ! empty( $supported_taxonomies_slugs ) ) {
            foreach( $supported_taxonomies_slugs as $taxonomy_slug ) {
                $taxonomy = get_taxonomy( $taxonomy_slug );
                $taxonomies .= '<a href="/wp-admin/edit-tags.php?taxonomy=' . $taxonomy_slug . $taxonomy_page_link_suffix . '">' . $taxonomy->labels->name . '</a>' . $separator;
            }
            $taxonomies = trim( trim( $taxonomies ), ',' );
        } else {
            $taxonomies = 'None supported yet.';
        }

        if ( $column_name === 'basic' ) {
            echo '<a href="' . esc_attr( $edit_link ) . '"><strong>' . esc_html( $plural_name ) . '</strong></a> (' . esc_html( $key ) . ')<br />' . esc_html( $description ) . esc_html( $description_separator ) . esc_html( $public ) . esc_html( $hierarchical ) ;
        }
        if ( $column_name === 'description' ) {
            echo wp_kses_post( get_post_meta( get_the_ID(), 'cpt_description', true ) );
        }
        if ( $column_name === 'supports' ) {
            echo esc_html( $supports );
        }
        if ( $column_name === 'taxonomies' ) {
            echo wp_kses_post( $taxonomies );
        }
    }

    /**
     * Define columns to show for custom taxonomies admin table
     *
     * @since  5.1.0
     */
    public function ctax_posts_define_columns__premium_only( $columns ) {

        $columns = array(
            'cb' => $columns['cb'], // Checkbox for bulk actions
            'basic' => 'Basic Info',
            'post_types' => 'Supported Post Types',
            'author' => 'Author',
            'date' => 'Date',
        );
        return $columns;
    }

    /**
     * Render custom columns content for CPT admin table
     *
     * @since  5.1.0
     */
    public function ctax_posts_custom_columns_content__premium_only( $column_name, $id ) {
        
        $edit_link = get_edit_post_link();
        $singular_name = get_post_meta( get_the_ID(), 'ctax_singular_name', true );
        $plural_name = get_post_meta( get_the_ID(), 'ctax_plural_name', true );
        $description = get_post_meta( get_the_ID(), 'ctax_description', true );
        $description_separator = ( ! empty( get_post_meta( get_the_ID(), 'ctax_description', true ) ) ) ? '<br />' : '';
        $key = get_post_meta( get_the_ID(), 'ctax_key', true );
        $public = ( get_post_meta( get_the_ID(), 'ctax_public', true ) ) ? 'Public' : 'Not public';
        $hierarchical = ( get_post_meta( get_the_ID(), 'ctax_hierarchical', true ) ) ? ' | Hierarchical' : ' | Not hierarchical';
        
        $post_types_raw = get_post_meta( get_the_ID(), 'ctax_post_types', true );
        $post_types = '';
        $separator = ', ';
        if ( ! empty( $post_types_raw ) ) {
            foreach( $post_types_raw as $post_type_slug ) {
                $post_type_page_link_suffix = ( ! in_array( $post_type_slug, array( 'post', 'page' ) ) ) ? '?post_type=' . $post_type_slug : '' ;
                $post_type = get_post_type_object( $post_type_slug );
                if ( is_object( $post_type ) && property_exists( $post_type, 'label' ) ) {
                    $post_type_label = $post_type->label;
                } elseif ( is_object( $post_type ) && ! is_null( $post_type->labels ) ) {
                    $post_type_label = $post_type->labels->name;                 
                } else {
                    $post_type_label = $post_type_slug;
                }
                $post_types .= '<a href="/wp-admin/edit.php' . $post_type_page_link_suffix . '">' . $post_type_label . '</a>' . $separator;
            }
            $post_types = trim( trim( $post_types ), ',' );
        } else {
            $post_types = 'None supported yet.';
        }

        if ( $column_name === 'basic' ) {
            echo '<a href="' . esc_attr( $edit_link ) . '"><strong>' . esc_html( $plural_name ) . '</strong></a> (' . esc_html( $key ) . ')<br />' . esc_html( $description ) . esc_html( $description_separator )  . esc_html( $public ) . esc_html( $hierarchical ) ;
        }
        if ( $column_name === 'post_types' ) {
            echo wp_kses_post( $post_types );
        }
    }

    /**
     * Remove Custom Post Types and Custom Taxonomies submenus from Settings menu
     * 
     * @since 5.1.0
     */
    public function remove_cpt_ctax_submenus_for_nonadmins__premium_only() {
        global $current_user;
        $roles = array();
        foreach( $current_user->roles as $key => $role ) {
            $roles[] = $role;
        }
        if ( ! in_array( 'administrator', $roles ) ) {
            remove_submenu_page( 'options-general.php', 'edit.php?post_type=asenha_cpt' );          
            remove_submenu_page( 'options-general.php', 'edit.php?post_type=asenha_ctax' );         
        }
    }    
    
}