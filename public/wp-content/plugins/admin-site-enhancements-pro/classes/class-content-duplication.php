<?php

namespace ASENHA\Classes;

use WP_Admin_Bar;
use WC_Admin_Duplicate_Product; 

/**
 * Class for Content Duplication module
 *
 * @since 6.9.5
 */
class Content_Duplication {
    
    public $inapplicable_post_types = array( 
        'attachment', // public
        'asenha_code_snippet', // public, ASE
        'asenha_cpt', // non-public, ASE
        'asenha_ctax', // non-public, ASE
        'asenha_cfgroup', // non-public, ASE
        'asenha_options_page', // non-public, ASE
        'options_page_config', // non-public, ASE
        'revision', // non-public
        'nav_menu_item', // non-public
        'custom_css', // non-public
        'customize_changeset', // non-public
        'oembed_cache', // non-public
        'user_request', // non-public
        'wp_block', // non-public
        'wp_template', // non-public
        'wp_template_part', // non-public
        'wp_global_styles', // non-public
        'wp_navigation', // non-public
        'wp_font_family', // non-public
        'wp_font_face', // non-public
        'patterns_ai_data', // non-public
        'product_variation', // non-public, WooCommerce
        'shop_order', // non-public, WooCommerce
        'shop_order_refund', // non-public, WooCommerce
        'shop_coupon', // non-public, WooCommerce
        'shop_order_placehold', // non-public, WooCommerce
        // 'elementor_library', // public, Elementor -- not excluded as data is stored only in wp_posts and wp_postmeta
        // 'e-landing-page', // public, Elementor -- not excluded as data is stored only in wp_posts and wp_postmeta
        'elementor_snippet', // non-public, Elementor
        'elementor_font', // non-public, Elementor
        'elementor_icons', // non-public, Elementor
        'sfwd-assignment', // public, LearnDash
        'sfwd-certificates', // public, LearnDash
        'sfwd-courses', // public, LearnDash
        'sfwd-lessons', // public, LearnDash
        'sfwd-quiz', // public, LearnDash
        'sfwd-essays', // public, LearnDash
        'sfwd-topic', // public, LearnDash
        'sfwd-transactions', // public, LearnDash
        'sfwd-question', // non-public, LearnDash
        'ld-exam', // non-public, LearnDash
        'wfacp_checkout', // public, FunnelKit Automation
        'wffn_oty', // public, FunnelKit Funnel Builder
        'wffn_optin', // public, FunnelKit Funnel Builder
        'wffn_landing', // public, FunnelKit Funnel Builder
        'wffn_ty', // public, FunnelKit Funnel Builder
        'kadence_form', // non-public, Kadence Blocks
        'kadence_header', // non-public, Kadence Blocks
        'kadence_navigation', // non-public, Kadence Blocks
        'kadence_lottie', // non-public, Kadence Blocks
        'kadence_vector', // non-public, Kadence Blocks
        'kb_icon', // non-public, Kadence Blocks
    );
    
    /**
     * Enable duplication of pages, posts and custom posts
     *
     * @since 1.0.0
     */
    public function duplicate_content() {
        $allow_duplication = false;

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            global $roles_duplication_enabled;
            if ( is_null( $roles_duplication_enabled ) ) {
                $roles_duplication_enabled = array();
            }

            $current_user = wp_get_current_user();
            $current_user_roles = (array) $current_user->roles; // single dimensional array of role slugs

            if ( count( $roles_duplication_enabled ) > 0 ) {

                // Add mime type for user roles set to enable SVG upload
                foreach ( $current_user_roles as $role ) {
                    if ( in_array( $role, $roles_duplication_enabled ) ) {
                        // Do something here
                        $allow_duplication = true;
                    }
                }   

            }
        } else {
            if ( current_user_can( 'edit_posts' ) ) {
                $allow_duplication = true;
            }
        }

        $original_post_id = intval( sanitize_text_field( $_REQUEST['post'] ) );
        $nonce = sanitize_text_field( $_REQUEST['nonce'] );

        if ( wp_verify_nonce( $nonce, 'asenha-duplicate-' . $original_post_id ) && $allow_duplication ) {

            $original_post = get_post( $original_post_id );
            
            $post_type = $original_post->post_type;

            $common_methods = new Common_Methods;
            $is_woocommerce_active = $common_methods->is_woocommerce_active();
            
            if ( 'product' != $post_type 
                || ( 'product' == $post_type && ! $is_woocommerce_active ) // Non-WooCommerce 'product' post type
            ) {

                // Set some attributes for the duplicate post

                $new_post_title_suffix = __( 'DUPLICATE', 'admin-site-enhancements' );
                $new_post_status = 'draft';
                $current_user = wp_get_current_user();
                $new_post_author_id = $current_user->ID;

                // Create the duplicate post and store the ID
                
                $args = array(

                    'comment_status'    => $original_post->comment_status,
                    'ping_status'       => $original_post->ping_status,
                    'post_author'       => $new_post_author_id,
                    // We replace single backslash with double backslash, so that upon saving, it becomes single backslash again
                    // This is to compensate for the default behaviour that removes single/unescaped backslashes upon saving content
                    // This ensures CSS styles using var(--varname) in the Block Editor, which is saved as var(\u002d\u002varname)
                    // Will not become var(u002du002dsecondary) in the duplicated post (not the missing backslash)
                    'post_content'      => str_replace( '\\', "\\\\", $original_post->post_content ),
                    'post_excerpt'      => $original_post->post_excerpt,
                    'post_parent'       => $original_post->post_parent,
                    'post_password'     => $original_post->post_password,
                    'post_status'       => $new_post_status,
                    'post_title'        => $original_post->post_title . ' (' . $new_post_title_suffix . ')',
                    'post_type'         => $original_post->post_type,
                    'to_ping'           => $original_post->to_ping,
                    'menu_order'        => $original_post->menu_order,

                );

                $new_post_id = wp_insert_post( $args );

                // Copy over the taxonomies

                $original_taxonomies = get_object_taxonomies( $original_post->post_type );

                if ( ! empty( $original_taxonomies ) && is_array( $original_taxonomies ) ) {

                    foreach( $original_taxonomies as $taxonomy ) {

                        $original_post_terms = wp_get_object_terms( $original_post_id, $taxonomy, array( 'fields' => 'slugs' ) );

                        wp_set_object_terms( $new_post_id, $original_post_terms, $taxonomy, false );

                    }

                }

                // Get the post meta keys to exclude from post meta duplication
                if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                    // Get a list of ASE custom fields keys to exclude from post meta duplication
                    if ( class_exists( 'Custom_Field_Group' ) ) {
                        $all_cf_info = get_cf_info( false, $original_post_id );
                        $excluded_post_meta_keys = array_keys( $all_cf_info );
                    } else {
                        $excluded_post_meta_keys = array();
                    }
                } else {
                    $excluded_post_meta_keys = array();                    
                }

                // Copy over the post meta
                $original_post_metas = get_post_meta( $original_post_id ); // all meta keys and the corresponding values

                if ( ! empty( $original_post_metas ) ) {
                    foreach( $original_post_metas as $meta_key => $meta_values ) {                        
                        if ( ! in_array( $meta_key, $excluded_post_meta_keys ) ) {
                            // Only copy over post meta that are not ASE custom fields. We will handle that later.
                            foreach( $meta_values as $meta_value ) {
                                update_post_meta( $new_post_id, $meta_key, wp_slash( maybe_unserialize( $meta_value ) ) );
                            }
                        }
                    }
                }

                if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                    if ( class_exists( 'Custom_Field_Group' ) ) {
                        // Prepare $field_data, $post_date and $options 
                        // for /includes/premium/custom-content/cfgroup/includes/form.php >> init() >> CFG()->save()
                        $cf_raw_values = get_cf( false, 'raw', $original_post_id );

                        $common_methods = new Common_Methods;
                        $field_data = $common_methods->convert_ase_cf_raw_values_to_cfg_save_format__premium_only( $cf_raw_values, $all_cf_info );
                        // vi( $field_data, '', 'processed from original post for duplicating from' );
                        
                        $post_data = array(
                            'ID'    => $new_post_id,
                        );
                        // vi( $post_data, '', 'for duplicating ASE post meta' );
                        
                        // Get Custom Field Group IDs from the original post
                        $cfgroup_ids = array();
                        foreach ( $all_cf_info as $cf_name => $cf_info ) {
                            if ( isset( $cf_info['group_id'] ) && ! in_array( $cf_info['group_id'], $cfgroup_ids ) ) {
                                $cfgroup_ids[] = $cf_info['group_id'];
                            }
                        }
                        
                        $options = array(
                            'format'        => 'input',
                            'field_groups'  => $cfgroup_ids,
                        );
                        // vi( $options, '', 'for duplicating ASE post meta' );
                        
                        $result = CFG()->save(
                            $field_data,
                            $post_data,
                            $options
                        );
                        // vi( $result, '', 'of ASE field data duplication during content duplication' );
                    }
                }
                
            }
            
            $options = get_option( ASENHA_SLUG_U, array() );
            $duplication_redirect_destination = isset( $options['duplication_redirect_destination'] ) ? $options['duplication_redirect_destination'] : 'edit';

            switch ( $duplication_redirect_destination ) {
                case 'edit':
                    // Redirect to edit screen of the duplicate post
                    wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );                
                    break;

                case 'list':
                    // Redirect to list table of the corresponding post type of original post
                    if ( 'post' == $post_type ) {
                        wp_redirect( admin_url( 'edit.php' ) );
                    } else {
                        wp_redirect( admin_url( 'edit.php?post_type=' . $post_type ) );
                    }               
                    break;
            }           
            
        } else {

            wp_die( 'You do not have permission to perform this action.' );

        }

    }

    /** 
     * Add row action link to perform duplication in page/post list tables
     *
     * @since 1.0.0
     */
    public function add_duplication_action_link( $actions, $post ) {        
        $duplication_link_locations = $this->get_duplication_link_locations();
        
        $allow_duplication = $this->is_user_allowed_to_duplicate_content();
        
        $post_type = $post->post_type;
        $post_type_is_duplicable = $this->is_post_type_duplicable( $post_type );

        if ( $allow_duplication && $post_type_is_duplicable ) {
            // Not WooCommerce product
            if ( in_array( 'post-action', $duplication_link_locations ) ) {
                $actions['asenha-duplicate'] = '<a href="admin.php?action=duplicate_content&amp;post=' . $post->ID . '&amp;nonce=' . wp_create_nonce( 'asenha-duplicate-' . $post->ID ) . '" title="' . __( 'Duplicate this as draft', 'admin-site-enhancements' ) . '">' . __( 'Duplicate', 'admin-site-enhancements' ) . '</a>';
            }
        }

        return $actions;
    }
    
    /**
     * Add admin bar duplicate link
     * 
     * @since 6.3.0
     */
    public function add_admin_bar_duplication_link( WP_Admin_Bar $wp_admin_bar ) {      
        $duplication_link_locations = $this->get_duplication_link_locations();

        $allow_duplication = $this->is_user_allowed_to_duplicate_content();
        
        global $pagenow, $post;

        if ( is_object( $post ) ) {
            if ( property_exists( $post, 'post_type' ) ) {
                $post_type = $post->post_type;
                $inapplicable_post_types = array( 'attachment' );

                $post_type_is_duplicable = $this->is_post_type_duplicable( $post_type );

                if ( $allow_duplication && $post_type_is_duplicable ) {
                    if ( ( 'post.php' == $pagenow && ! in_array( $post_type, $inapplicable_post_types ) ) 
                        || is_singular() 
                        || ( is_front_page() && ! is_home() ) // When the homepage uses a specific page, not showing latest posts
                    ) {
                        if ( in_array( 'admin-bar', $duplication_link_locations ) ) {
                            $common_methods = new Common_Methods;
                            $post_type_singular_label = $common_methods->get_post_type_singular_label( $post );
                            
                            $post_id = 0;
                            if ( is_front_page() && ! is_home() ) {
                                $post_id = get_option( 'page_on_front' );
                            } else {
                                // if ( property_exists( $post, 'ID' ) ) {
                                    $post_id = (int) $post->ID;
                                // } else {
                                //     $post_id = 0;
                                // }
                            }

                            if ( $post_id > 0 ) {
                                $wp_admin_bar->add_menu( array(
                                    'id'    => 'duplicate-content',
                                    'parent' => null,
                                    'group'  => null,
                                    'title' => sprintf(
                                        /* translators: %s is the singular label for the post type */
                                        __( 'Duplicate %s', 'admin-site-enhancements' ),
                                        $post_type_singular_label
                                    ),
                                    'href'  => admin_url( 'admin.php?action=duplicate_content&amp;post=' . $post_id . '&amp;nonce=' . wp_create_nonce( 'asenha-duplicate-' . $post_id ) ),
                                ) );
                            }
                        }
                    }
                }                
            }
        }
    }

    /**
     * Add duplication link in post submit/update box
     * 
     * @since 6.9.3
     */
    public function add_submitbox_duplication_link__premium_only() {
        $duplication_link_locations = $this->get_duplication_link_locations();

        $allow_duplication = $this->is_user_allowed_to_duplicate_content();

        global $post, $pagenow;

        if ( is_object( $post ) ) {
            $post_type = $post->post_type;
            $post_type_is_duplicable = $this->is_post_type_duplicable( $post_type );            
        } else {
            $post_type_is_duplicable = false;
        }

        if ( $allow_duplication && $post_type_is_duplicable && is_object( $post ) && 'post.php' == $pagenow && in_array( 'publish-section', $duplication_link_locations ) ) {
            $common_methods = new Common_Methods;
            $post_type_singular_label = $common_methods->get_post_type_singular_label( $post );

            $duplication_link_section = '<div class="additional-actions"><span id="duplication"><a href="admin.php?action=duplicate_content&amp;post=' . $post->ID . '&amp;nonce=' . wp_create_nonce( 'asenha-duplicate-' . $post->ID ) . '" title="' . __( 'Duplicate this as draft', 'admin-site-enhancements' ) . '">' . sprintf(
                    /* translators: %s is the singular label for the post type */
                    __( 'Duplicate %s', 'admin-site-enhancements' ),
                    $post_type_singular_label
                ) . '</a></span></div>';
            echo wp_kses_post( $duplication_link_section );
        }
    }
    
    /**
     * Add duplication button in the block editor
     * 
     * @since 6.9.3
     */
    public function add_gutenberg_duplication_link__premium_only() {
        global $post, $pagenow;
        $common_methods = new Common_Methods;
        $duplication_link_locations = $this->get_duplication_link_locations();

        $allow_duplication = $this->is_user_allowed_to_duplicate_content();

        if ( is_object( $post ) ) {
            $post_type = $post->post_type;
            $post_type_is_duplicable = $this->is_post_type_duplicable( $post_type );            
        } else {
            $post_type_is_duplicable = false;
        }

        if ( $allow_duplication && $post_type_is_duplicable && is_object( $post ) && 'post.php' == $pagenow && in_array( 'publish-section', $duplication_link_locations ) ) {
            // Check if we're inside the block editor. Ref: https://wordpress.stackexchange.com/a/309955.
            if ( $common_methods->is_in_block_editor() ) {
                $post_type_singular_label = $common_methods->get_post_type_singular_label( $post );

                // Ref: https://plugins.trac.wordpress.org/browser/duplicate-page/tags/4.5/duplicatepage.php#L286
                wp_enqueue_style( 'asenha-gutenberg-content-duplication', ASENHA_URL . 'assets/css/gutenberg-content-duplication.css' );

                wp_register_script( 'asenha-gutenberg-content-duplication', ASENHA_URL . 'assets/js/gutenberg-content-duplication.js', array( 'wp-edit-post', 'wp-plugins', 'wp-i18n', 'wp-element' ), ASENHA_VERSION);

                wp_localize_script( 'asenha-gutenberg-content-duplication', 'cd_params', array(
                    'cd_post_id'        => intval($post->ID),
                    'cd_nonce'          => wp_create_nonce( 'asenha-duplicate-' . $post->ID ),
                    'cd_post_text'      => sprintf(
                                            /* translators: %s is the singular label for the post type */
                                            __( 'Duplicate %s', 'admin-site-enhancements' ),
                                            $post_type_singular_label
                                        ),
                    'cd_post_title'     => __( 'Duplicate this as draft', 'admin-site-enhancements' ),
                    'cd_duplicate_link' => "admin.php?action=duplicate_content"
                    )
                );

                wp_enqueue_script( 'asenha-gutenberg-content-duplication' );
            }
        }
    }

    /**
     * Check at which locations duplication link should enabled
     * 
     * @since 6.9.3
     */
    public function get_duplication_link_locations() {
        $options = get_option( ASENHA_SLUG_U, array() );
        
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $duplication_link_locations = isset( $options['enable_duplication_link_at'] ) ? $options['enable_duplication_link_at'] : array( 'post-action', 'admin-bar', 'publish-section' );        
        } else {
            $duplication_link_locations = array( 'post-action', 'admin-bar' );
        }

        return $duplication_link_locations;
    }

    /**
     * Check if a user role is allowed to duplicate content
     * 
     * @since 6.9.3
     */
    public function is_user_allowed_to_duplicate_content() {
        $allow_duplication = false;

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            global $roles_duplication_enabled;
            if ( is_null( $roles_duplication_enabled ) ) {
                $roles_duplication_enabled = array();
            }

            $current_user = wp_get_current_user();
            $current_user_roles = (array) $current_user->roles; // single dimensional array of role slugs

            if ( count( $roles_duplication_enabled ) > 0 ) {

                // Add mime type for user roles set to enable SVG upload
                foreach ( $current_user_roles as $role ) {
                    if ( in_array( $role, $roles_duplication_enabled ) ) {
                        // Do something here
                        $allow_duplication = true;
                    }
                }   

            }
        } else {
            if ( current_user_can( 'edit_posts' ) ) {
                $allow_duplication = true;
            }
        }
        
        return $allow_duplication;
    }
    
    /**
     * Check if the post type can be duplicated
     * 
     * @since 6.9.7
     */
    public function is_post_type_duplicable( $post_type ) {
        $common_methods = new Common_Methods;
        $asenha_public_post_types = $common_methods->get_public_post_type_slugs();
        $inapplicable_post_types = $this->inapplicable_post_types;

        $is_woocommerce_active = $common_methods->is_woocommerce_active();
        
        $options = get_option( ASENHA_SLUG_U, array() );

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $enable_duplication_on_post_types_type = isset( $options['enable_duplication_on_post_types_type'] ) ? $options['enable_duplication_on_post_types_type'] : 'only-on';
        } else {
            $enable_duplication_on_post_types_type = 'only-on';
        }

        $asenha_public_post_types_slugs = array();
        if ( is_array( $asenha_public_post_types ) ) {
            foreach ( $asenha_public_post_types as $post_type_slug => $post_type_label ) { // e.g. $post_type_slug is post, 
                $asenha_public_post_types_slugs[] = $post_type_slug;
            }
        }

        $enable_duplication_on_post_types = isset( $options['enable_duplication_on_post_types'] ) ? $options['enable_duplication_on_post_types'] : array();
        $post_types_for_enable_duplication = array();
        
        if ( ! empty( $enable_duplication_on_post_types ) && count( $enable_duplication_on_post_types ) > 0 ) {
            foreach( $enable_duplication_on_post_types as $post_type_slug => $is_duplication_enabled ) {
                if ( $is_duplication_enabled ) {
                    $post_types_for_enable_duplication[] = $post_type_slug;
                }
            }
        } else {
            $post_types_for_enable_duplication = $asenha_public_post_types_slugs;
        }

        if ( 'only-on' == $enable_duplication_on_post_types_type 
            && in_array( $post_type, $post_types_for_enable_duplication ) 
            && ! in_array( $post_type, $inapplicable_post_types )
            || 
            'except-on' == $enable_duplication_on_post_types_type 
            && ! in_array( $post_type, $post_types_for_enable_duplication )
            && ! in_array( $post_type, $inapplicable_post_types )
        ) {
            if ( 'product' != $post_type 
                || ( 'product' == $post_type && ! $is_woocommerce_active )
            ) {
                return true;
            }
        } else {
            return false;
        }
        
    }
    
}