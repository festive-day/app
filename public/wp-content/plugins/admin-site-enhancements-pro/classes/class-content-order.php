<?php

namespace ASENHA\Classes;

use WP_Query;

/**
 * Class for Content Order module
 *
 * @since 6.9.5
 */
class Content_Order {

    /** 
     * Add "Custom Order" sub-menu for post types
     * 
     * @since 5.0.0
     */
    public function add_content_order_submenu( $context ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $content_order_for = isset( $options['content_order_for'] ) ? $options['content_order_for'] : array();
        $content_order_enabled_post_types = array();
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $content_order_for_other_post_types = isset( $options['content_order_for_other_post_types'] ) ? $options['content_order_for_other_post_types'] : array();
            $content_order_other_enabled_post_types = array();            
        }
        
        if ( is_array( $content_order_for ) && count( $content_order_for ) > 0 ) {
            foreach ( $content_order_for as $post_type_slug => $is_custom_order_enabled ) {
                if ( $is_custom_order_enabled ) {
                    $post_type_object = get_post_type_object( $post_type_slug );

                    if ( is_object( $post_type_object ) && property_exists( $post_type_object, 'labels' ) ) {
                        $post_type_name_plural = $post_type_object->labels->name;
                        if ( 'post' == $post_type_slug ) {
                            $hook_suffix = add_posts_page(
                                $post_type_name_plural . ' Order', // Page title
                                __( 'Order', 'admin-site-enhancements' ), // Menu title
                                'edit_others_posts', // Capability required
                                'custom-order-posts', // Menu and page slug
                                [ $this, 'custom_order_page_output' ] // Callback function that outputs page content
                            );
                        } else if ( 'sfwd-courses' == $post_type_slug ) {
                            // LearnDash LMS Courses
                            // Add 'Order' submenu item under LearnDash menu
                            // Linked URL will be /wp-admin/admin.php?page=custom-order-sfwd-courses
                            // We will add a redirect to the correct URL via $this->maybe_perform_menu_link_redirects() hooked in admin_init
                            $hook_suffix = add_submenu_page(
                                'learndash-lms', // Parent (menu) slug. Ref: https://developer.wordpress.org/reference/functions/add_submenu_page/#comment-1404
                                $post_type_name_plural . ' ' . __( 'Order', 'admin-site-enhancements' ), // Page title
                                $post_type_name_plural . ' '  . __( 'Order', 'admin-site-enhancements' ), // Menu title
                                'edit_others_posts', // Capability required
                                'custom-order-' . $post_type_slug, // Menu and page slug
                                [ $this, 'custom_order_page_output' ],  // Callback function that outputs page content
                                9999 // position
                            );

                            // Add the actual, functional 'Order' submenu page at /edit.php?post_type=sfwd-courses&page=custom-order-sfwd-courses
                            // We will redirect to this URL from /wp-admin/admin.php?page=custom-order-sfwd-courses created above using $this->maybe_perform_menu_link_redirects() hooked in admin_init
                            $hook_suffix = add_submenu_page(
                                'edit.php?post_type=' . $post_type_slug, // Parent (menu) slug. Ref: https://developer.wordpress.org/reference/functions/add_submenu_page/#comment-1404
//                                 'learndash-lms', // Parent (menu) slug. Ref: https://developer.wordpress.org/reference/functions/add_submenu_page/#comment-1404
                                $post_type_name_plural . ' '  . __( 'Order', 'admin-site-enhancements' ), // Page title
                                $post_type_name_plural . ' '  . __( 'Order', 'admin-site-enhancements' ), // Menu title
                                'edit_others_posts', // Capability required
                                'custom-order-' . $post_type_slug, // Menu and page slug
                                [ $this, 'custom_order_page_output' ],  // Callback function that outputs page content
                                9999 // position
                            );
                        } else {
                            $hook_suffix = add_submenu_page(
                                'edit.php?post_type=' . $post_type_slug, // Parent (menu) slug. Ref: https://developer.wordpress.org/reference/functions/add_submenu_page/#comment-1404
                                $post_type_name_plural . ' Order', // Page title
                                __( 'Order', 'admin-site-enhancements' ), // Menu title
                                'edit_others_posts', // Capability required
                                'custom-order-' . $post_type_slug, // Menu and page slug
                                [ $this, 'custom_order_page_output' ],  // Callback function that outputs page content
                                9999 // position
                            );
                        }

                        add_action( 'admin_print_styles-' . $hook_suffix, [ $this, 'enqueue_content_order_styles' ] );
                        add_action( 'admin_print_scripts-' . $hook_suffix, [ $this, 'enqueue_content_order_scripts' ] );                    
                    }
                }
            }
        }

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            if ( is_array( $content_order_for_other_post_types ) && count( $content_order_for_other_post_types ) > 0 ) {
                foreach ( $content_order_for_other_post_types as $post_type_slug => $is_custom_order_enabled ) {
                    if ( $is_custom_order_enabled ) {
                        $post_type_object = get_post_type_object( $post_type_slug );

                        if ( is_object( $post_type_object ) && property_exists( $post_type_object, 'labels' ) ) {
                            $post_type_name_plural = $post_type_object->labels->name;
                            if ( 'post' == $post_type_slug ) {
                                $hook_suffix = add_posts_page(
                                    $post_type_name_plural . ' Order', // Page title
                                    __( 'Order', 'admin-site-enhancements' ), // Menu title
                                    'edit_others_posts', // Capability required
                                    'custom-order-posts', // Menu and page slug
                                    [ $this, 'custom_order_page_output' ] // Callback function that outputs page content
                                );
                            } elseif ( 'attachment' == $post_type_slug ) {
                                $hook_suffix = add_media_page(
                                    $post_type_name_plural . ' Order', // Page title
                                    __( 'Order', 'admin-site-enhancements' ), // Menu title
                                    'edit_others_posts', // Capability required
                                    'custom-order-attachments', // Menu and page slug
                                    [ $this, 'custom_order_page_output' ] // Callback function that outputs page content
                                );
                            } else {
                                $hook_suffix = add_submenu_page(
                                    'edit.php?post_type=' . $post_type_slug, // Parent (menu) slug. Ref: https://developer.wordpress.org/reference/functions/add_submenu_page/#comment-1404
                                    $post_type_name_plural . ' Order', // Page title
                                    __( 'Order', 'admin-site-enhancements' ), // Menu title
                                    'edit_others_posts', // Capability required
                                    'custom-order-' . $post_type_slug, // Menu and page slug
                                    [ $this, 'custom_order_page_output' ],  // Callback function that outputs page content
                                    9999 // position
                                );
                            }

                            add_action( 'admin_print_styles-' . $hook_suffix, [ $this, 'enqueue_content_order_styles' ] );
                            add_action( 'admin_print_scripts-' . $hook_suffix, [ $this, 'enqueue_content_order_scripts' ] );                    
                        }
                    }
                }                
            }
        }

    }

    /**
     * Add additinal HTML elements on list tables
     * 
     * @since 7.6.10
     */
    public function add_additional_elements() {
        global $pagenow, $typenow;

        $common_methods = new Common_Methods;
        $options = get_option( ASENHA_SLUG_U, array() );

        $content_order_for = isset( $options['content_order_for'] ) ? $options['content_order_for'] : array();
        $content_order_enabled_post_types = $common_methods->get_array_of_keys_with_true_value( $content_order_for );

        $content_order_other_enabled_post_types = array();
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $content_order_for_other_post_types = isset( $options['content_order_for_other_post_types'] ) ? $options['content_order_for_other_post_types'] : array();
            $content_order_other_enabled_post_types = $common_methods->get_array_of_keys_with_true_value( $content_order_for_other_post_types );
        }
        
        // List tables of pages, posts and CPTs. Administrators and Editors only.
        if ( 'edit.php' == $pagenow 
            && current_user_can( 'edit_others_posts' ) 
            && ( in_array( $typenow, $content_order_enabled_post_types ) ||  in_array( $typenow, $content_order_other_enabled_post_types ) )
        ) {
            // Add "Order" button
            if ( 'post' == $typenow ) {
                $typenow = 'posts';
            }
            ?>
            <div id="content-order-button">
                <a class="button" href="<?php echo esc_url( get_admin_url() ); ?>edit.php?post_type=<?php echo esc_attr( $typenow ); ?>&page=custom-order-<?php echo esc_attr( $typenow ); ?>"><?php _e( 'Order', 'admin-site-enhancements' ); ?></a>
            </div>
            <?php           
        }        
    }

    /**
     * Add scripts for content list tables
     * 
     * @since 7.6.10
     */
    public function add_list_tables_scripts( $hook_suffix ) {
        global $pagenow, $typenow;

        $common_methods = new Common_Methods;
        $options = get_option( ASENHA_SLUG_U, array() );

        $content_order_for = isset( $options['content_order_for'] ) ? $options['content_order_for'] : array();
        $content_order_enabled_post_types = $common_methods->get_array_of_keys_with_true_value( $content_order_for );

        $content_order_other_enabled_post_types = array();
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $content_order_for_other_post_types = isset( $options['content_order_for_other_post_types'] ) ? $options['content_order_for_other_post_types'] : array();
            $content_order_other_enabled_post_types = $common_methods->get_array_of_keys_with_true_value( $content_order_for_other_post_types );
        }
                
        // List tables of pages, posts and CPTs
        if ( 'edit.php' == $hook_suffix 
            && current_user_can( 'edit_others_posts' ) 
            && ( in_array( $typenow, $content_order_enabled_post_types ) ||  in_array( $typenow, $content_order_other_enabled_post_types ) )
        ) {
            wp_enqueue_style( 'asenha-list-tables-content-order', ASENHA_URL . 'assets/css/list-tables-content-order.css', array(), ASENHA_VERSION );
            wp_enqueue_script( 'asenha-list-tables-content-order', ASENHA_URL . 'assets/js/list-tables-content-order.js', array( 'jquery' ), ASENHA_VERSION, false );
        }
        
    }
    
    /**
     * Maybe perform redirects from the 'Order' submenu link
     * 
     * @since 7.6.9
     */
    public function maybe_perform_menu_link_redirects() {
        $request_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] ); // e.g. /wp-admin/index.php?page=page-slug

        // Redirect for LearnDash LMS Courses post type ('sfwd-courses')
        if ( in_array( 'sfwd-lms/sfwd_lms.php', get_option( 'active_plugins', array() ) ) 
        ) {
            if ( false !== strpos( $request_uri, 'admin.php?page=custom-order-sfwd-courses' ) ) {
                wp_safe_redirect( get_admin_url() . 'edit.php?post_type=sfwd-courses&page=custom-order-sfwd-courses' );
                exit();
            }
        }
    }
    
    /**
     * Output content for the custom order page for each enabled post types
     * Not using settings API because all done via AJAX
     * 
     * @since 5.0.0
     */
    public function custom_order_page_output() {

        $post_status = array( 'publish', 'future', 'draft', 'pending', 'private' );

        $parent_slug = get_admin_page_parent();
        
        if ( 'edit.php' == $parent_slug ) {
            $post_type_slug = 'post';
        } elseif ( 'upload.php' == $parent_slug ) {
            $post_type_slug = 'attachment';
            $post_status = array( 'inherit', 'private' );
        } else {
            $post_type_slug = str_replace( 'edit.php?post_type=', '', $parent_slug );
        }

        // Object with properties for each post status and the count of posts for each status
        // $post_count_object = wp_count_posts( $post_type_slug );

        // Number of items with the status 'publish(ed)', 'future' (scheduled), 'draft', 'pending' and 'private'
        // $post_count = absint( $post_count_object->publish )
        //            + absint( $post_count_object->future )
        //            + absint( $post_count_object->draft )
        //            + absint( $post_count_object->pending )
        //            + absint( $post_count_object->private );
        ?>
        <div class="wrap">
            <div class="page-header">
                <h2>
                    <?php
                        echo esc_html( get_admin_page_title() );
                    ?>
                </h2>
                <?php
                if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                ?>
                <div id="toggles" style="display:none;">
                    <input type="checkbox" id="toggle-taxonomy-terms" name="terms" value="" /><label for="toggle-taxonomy-terms"><?php echo esc_html__( 'Show taxonomy terms', 'admin-site-enhancements' ); ?></label>
                    <input type="checkbox" id="toggle-excerpt" name="excerpt" value="" /><label for="toggle-excerpt"><?php echo esc_html__( 'Show excerpt', 'admin-site-enhancements' ); ?></label>
                    <?php
                    if ( 'attachment' != $post_type_slug 
                        && is_post_type_hierarchical( $post_type_slug )
                    ) {
                    ?>
                    <input type="checkbox" id="toggle-child-posts" name="child-posts" value="" /><label for="toggle-child-posts"><?php echo esc_html__( 'Hide child posts', 'admin-site-enhancements' ); ?></label>
                    <?php
                    }
                    ?>
                </div>
                <?php
                }
                ?>
            </div>
        <?php
        // Get posts
        $args = array(
                'post_type'         => $post_type_slug,
                'numberposts'       => -1, // Get all posts
                'orderby'           => 'menu_order title', // By menu order then by title
                'order'             => 'ASC',
                'post_status'       => $post_status,
        );

        // Add the following to non-attachment post types
        if ( 'attachment' != $post_type_slug 
            && is_post_type_hierarchical( $post_type_slug )
        ) {
            // In hierarchical post types, only return non-child posts as we currently only sort parent posts
            $args['post_parent'] = 0; 
        }
        
        $posts = get_posts( $args );
        
        if ( ! empty( $posts ) ) {
            ?>
            <ul id="item-list">
            <?php
            foreach ( $posts as $post ) {
                $this->custom_order_single_item_output( $post );
            }
            ?>
            </ul>
            <div id="updating-order-notice" class="updating-order-notice" style="display: none;"><img src="<?php echo esc_attr( ASENHA_URL ) . 'assets/img/oval.svg'; ?>" id="spinner-img" class="spinner-img" /><span class="dashicons dashicons-saved" style="display:none;"></span>Updating order...</div>
            <?php
        } else {
            ?>
            <h3>There is nothing to sort for this post type.</h3>
            <?php            
        }
        ?>
        </div> <!-- End of div.wrap -->
        <?php
    }
    
    /**
     * Output single item sortable for custom content order
     * 
     * @since 5.0.0
     */
    private function custom_order_single_item_output( $post ) {
        if ( is_post_type_hierarchical( $post->post_type ) ) {
            $post_type_object = get_post_type_object( $post->post_type );

            $children = get_pages( array( 
                'child_of'  => $post->ID, 
                'post_type' => $post->post_type,
            ) );

            if ( count( $children ) > 0 ) {
                if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                    $has_child_label = '<span class="has-child-label" style="display: none;"> <span class="dashicons dashicons-arrow-right"></span> Has child ' . strtolower( $post_type_object->label ) . '</span>';
                } else {
                    $has_child_label = '<span class="has-child-label"> <span class="dashicons dashicons-arrow-right"></span> Has child ' . strtolower( $post_type_object->label ) . '</span>';
                }
                $has_child = 'true';
            } else {
                $has_child_label = '';                      
                $has_child = 'false';
            }                       
        } else {
            $has_child_label = '';
            $has_child = 'false';
        }

        $post_status_label_class = ( $post->post_status == 'publish' ) ? ' item-status-hidden' : '';
        $post_status_object = get_post_status_object( $post->post_status );
        
        if ( 'attachment' == $post->post_type ) {
            $post_status_label_separator = '';
            $post_status_label = ''; // Attachments / media only has the post status 'inherit'. Let's not show it.
        } else {
            $post_status_label_separator = ' — ';
            $post_status_label = $post_status_object->label;        
        }

        if ( empty( wp_trim_excerpt( $post->post_excerpt, $post ) ) ) {
            $short_excerpt = '';
        } else {
            $excerpt_trimmed = implode(" ", array_slice( explode( " ", wp_trim_excerpt( $post->post_excerpt, $post ) ), 0, 30 ) );
            $short_excerpt = '<span class="item-excerpt"> | ' . $excerpt_trimmed . '</span>';           
        }

        $taxonomies = get_object_taxonomies( $post->post_type, 'objects' );
        // vi( $taxonomies );
        $taxonomies_and_terms = '';
        foreach( $taxonomies as $taxonomy ) {
            $terms = array();
            if ( $taxonomy->hierarchical ) {
                $taxonomy_terms = get_the_terms( $post->ID, $taxonomy->name );
                if ( is_array( $taxonomy_terms ) && ! empty( $taxonomy_terms ) ) {
                    foreach( $taxonomy_terms as $term ) {
                        $terms[] = $term->name;
                    }                   
                }
            }
            $terms = implode( ', ', $terms );
            $taxonomies_and_terms .= ' | ' . $taxonomy->label . ': ' . $terms;                              
        }
        if ( ! empty( $taxonomies_and_terms ) ) {
            $taxonomies_and_terms = '<span class="item-taxonomy-terms">' . $taxonomies_and_terms . '</span>';
        }

        // If WPML plugin is active, let's get the current language
        if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins', array() ) ) ) {
            $current_language = apply_filters( 'wpml_current_language', null );
            $current_post_language_info = apply_filters( 'wpml_post_language_details', null, $post->ID );
            if ( ! is_wp_error( $current_post_language_info ) ) {
                $current_post_language = $current_post_language_info['language_code'];            
            } else {
                // wpml has not set language information for the post
                // e.g. post is not translated  yet, so, let's use the current site/admin language
                $current_post_language = $current_language;
            }
            $same_language = $current_language === $current_post_language; // true if language is the same, false otherwise
        } else {
            // WPML is not active, $same_language is always true, e.g. all posts are in English
            $same_language = true;
        }

        // Only render sortable for posts that have the same language as the current chosen language
        if ( $same_language ) {
        ?>
        <li id="list_<?php echo esc_attr( $post->ID ); ?>" class="list-item" data-id="<?php echo esc_attr( $post->ID ); ?>" data-menu-order="<?php echo esc_attr( $post->menu_order ); ?>" data-parent="<?php echo esc_attr( $post->post_parent ); ?>" data-has-child="<?php echo esc_attr( $has_child ); ?>" data-post-type="<?php echo esc_attr( $post->post_type ); ?>">
            <div class="row">
                <div class="row-content">
                    <?php 
                    if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                        echo '<div class="content-main">
                                    <span class="dashicons dashicons-menu"></span><a href="' . esc_attr( get_edit_post_link( $post->ID ) ) . '" class="item-title">' . esc_html( $post->post_title ) . '</a><span class="item-status' . esc_attr( $post_status_label_class ) . '">' . esc_html( $post_status_label_separator ) . esc_html( $post_status_label ) . '</span>' . wp_kses_post( $has_child_label ) . wp_kses_post( $taxonomies_and_terms ) . wp_kses_post( $short_excerpt ) . '<div class="fader"></div>
                                </div>';
                    } else {
                        echo '<div class="content-main">
                                    <span class="dashicons dashicons-menu"></span><a href="' . esc_attr( get_edit_post_link( $post->ID ) ) . '" class="item-title">' . esc_html( $post->post_title ) . '</a><span class="item-status' . esc_attr( $post_status_label_class ) . '">' . esc_html( $post_status_label_separator ) . esc_html( $post_status_label ) . '</span>' . wp_kses_post( $has_child_label ) . wp_kses_post( $taxonomies_and_terms ) . wp_kses_post( $short_excerpt ) . '<div class="fader"></div>
                                </div>';
                    }

                    if ( ! in_array( $post->post_type, array( 'asenha_code_snippet' ) ) ) {
                        echo '<div class="content-additional">
                                <a href="' . esc_attr( get_the_permalink( $post->ID ) ) . '" target="_blank" class="button item-view-link">View</a>
                            </div>';
                    }
                    ?>
                </div>
            </div>
            <?php
            if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                if ( 'true' === $has_child ) {
                    ?>
                    <ul class="child-list" data-parent="<?php echo esc_attr( $post->ID ); ?>">
                        <?php
                        // if ( 11920 == $post->ID ) {
                        //     vi( $children, '', 'of post ID 11920' );
                        // }
                        foreach ( $children as $child_post ) {
                            if ( $post->ID == $child_post->post_parent ) {
                                $this->custom_order_single_item_output( $child_post );
                            }
                        }
                        ?>
                    </ul>
                    <?php
                    $has_child = false; // we've rendered the child posts, let's reset
                } else {
                    ?>
                    <ul class="child-list empty-list" data-parent="<?php echo esc_attr( $post->ID ); ?>"></ul>
                    <?php
                }
            }

        } // if ( $same_language )
        ?>
        </li>
        <?php
    }
    
    /**
     * Enqueue styles for content order pages
     * 
     * @since 5.0.0
     */
    public function enqueue_content_order_styles() {
        wp_enqueue_style( 
            'content-order-style', 
            ASENHA_URL . 'assets/css/content-order.css', 
            array(), 
            ASENHA_VERSION 
        );
    }

    /**
     * Enqueue scripts for content order pages
     * 
     * @since 5.0.0
     */
    public function enqueue_content_order_scripts() {
        global $typenow;
        wp_enqueue_script( 
            'content-order-jquery-ui-touch-punch', 
            ASENHA_URL . 'assets/js/jquery.ui.touch-punch.min.js', 
            array( 'jquery-ui-sortable' ), 
            '0.2.3', 
            true 
        );
        wp_register_script( 
            'content-order-nested-sortable', 
            ASENHA_URL . 'assets/js/jquery.mjs.nestedSortable.js', 
            array( 'content-order-jquery-ui-touch-punch' ), 
            '2.0.0', 
            true 
        );
        wp_enqueue_script( 
            'content-order-sort', 
            ASENHA_URL . 'assets/js/content-order-sort.js', 
            array( 'content-order-nested-sortable' ), 
            ASENHA_VERSION, 
            true 
        );
        wp_localize_script(
            'content-order-sort',
            'contentOrderSort',
            array(
                'action'        => 'save_custom_order',
                'nonce'         => wp_create_nonce( 'order_sorting_nonce' ),
                'hirarchical'   => is_post_type_hierarchical( $typenow ) ? 'true' : 'false',
            )
        );
    }
    
    /**
     * Save custom content order coming from ajax call
     * 
     * @since 5.0.0
     */
    public function save_custom_content_order() {
        global $wpdb;
        
        // Check user capabilities
        if ( ! current_user_can( 'edit_others_posts' ) ) {
            wp_send_json( 'Something went wrong.' );
        }
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'order_sorting_nonce' ) ) {
            wp_send_json( 'Something went wrong.' );
        }
        
        // Get ajax variables
        $action = isset( $_POST['action'] ) ? $_POST['action'] : '' ;
        // Item parent is currently 0, as we only handle sorting of non-child posts
        $item_parent = isset( $_POST['item_parent'] ) ? absint( $_POST['item_parent'] ) : 0 ;
        $menu_order_start = isset( $_POST['start'] ) ? absint( $_POST['start'] ) : 0 ;
        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0 ;
        $item_menu_order = isset( $_POST['menu_order'] ) ? absint( $_POST['menu_order'] ) : 0 ;
        $items_to_exclude = isset( $_POST['excluded_items'] ) ? absint( $_POST['excluded_items'] ) : array();
        $post_type = isset( $_POST['post_type'] ) ? $_POST['post_type'] : false ;
        
        // Make processing faster by removing certain actions
        remove_action( 'pre_post_update', 'wp_save_post_revision' );
        
        // $response array for ajax response
        $response = array();

        // Update the item whose order/position was moved
        if ( $post_id > 0 && ! isset( $_POST['more_posts'] ) ) {
            // https://developer.wordpress.org/reference/classes/wpdb/update/
            if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                $wpdb->update(
                    $wpdb->posts, // The table
                    array( // The data
                        'post_parent'   => $item_parent,
                        'menu_order'    => $item_menu_order,
                    ),
                    array( // The post ID
                        'ID'            => $post_id
                    )
                );
            } else {
                $wpdb->update(
                    $wpdb->posts, // The table
                    array( // The data
                        'menu_order'    => $item_menu_order,
                    ),
                    array( // The post ID
                        'ID'            => $post_id
                    )
                );                
            }

            clean_post_cache( $post_id );
            $items_to_exclude[] = $post_id;
        }
        
        if ( 'attachment' == $post_type ) {
            $post_status = array( 'inherit', 'private' );
        } else {
            $post_status = array( 'publish', 'future', 'draft', 'pending', 'private' );        
        }
        
        // Get all posts from the post type related to ajax request
        $query_args = array(
            'post_type'                 => $post_type,
            'orderby'                   => 'menu_order title',
            'order'                     => 'ASC',
            'posts_per_page'            => -1, // Get all posts
            'suppress_filters'          => true,
            'ignore_sticky_posts'       => true,
            'post_status'               => $post_status,
            'post__not_in'              => $items_to_exclude,
            'update_post_term_cache'    => false, // Speed up processing by not updating term cache
            'update_post_meta_cache'    => false, // Speed up processing by not updating meta cache
        );

        if ( 'attachment' == $post_type ) {
            // do nothing, we do not add post_parent parameter as media items can be attached to other posts, making them the parent.
        } else {
            // Item parent is currently 0, as we only handle sorting of non-child posts
            $query_args['post_parent'] = $item_parent;
        }
        
        $posts = new WP_Query( $query_args );
                        
        if ( $posts->have_posts() ) {
            // Iterate through posts and update menu order and post parent
            foreach ( $posts->posts as $post ) {
                // If the $post is the one being displaced (shited downward) by the moved item, increment it's menu_order by one
                if ( $menu_order_start == $item_menu_order && $post_id > 0 ) {
                    $menu_order_start++;
                }
                
                // Only process posts other than the moved item, which has been processed earlier outside this loop
                if ( $post_id != $post->ID ) {
                    // Update menu_order
                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'menu_order'    => $menu_order_start,
                        ),
                        array(
                            'ID'            => $post->ID
                        )
                    );
                    clean_post_cache( $post->ID );
                }
                
                $items_to_exclude[] = $post->ID;
                $menu_order_start++;
            }
            die( json_encode( $response ) );
        } else {
            die( json_encode( $response ) );
        }
    }

    /**
     * Set default ordering of list tables of sortable post types by 'menu_order'
     * 
     * @link https://developer.wordpress.org/reference/classes/wp_query/#methods
     * @since 5.0.0
     */
    public function orderby_menu_order( $query ) {
        global $pagenow, $typenow;
        $query_post_type = $query->get('post_type');

        $options = get_option( ASENHA_SLUG_U, array() );

        // Hierarchical post types that should be custom ordered
        $content_order_for = isset( $options['content_order_for'] ) ? $options['content_order_for'] : array();
        $content_order_enabled_post_types = array();
        if ( is_array( $content_order_for ) && count( $content_order_for ) > 0 ) {
            foreach ( $content_order_for as $post_type_slug => $is_custom_order_enabled ) {
                if ( $is_custom_order_enabled ) {
                    $content_order_enabled_post_types[] = $post_type_slug;
                }
            }            
        }
        $should_be_custom_sorted = false;

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            // Non-hierarchical post types that should be custom ordered
            $content_order_for_other_post_types = isset( $options['content_order_for_other_post_types'] ) ? $options['content_order_for_other_post_types'] : array();
            $content_order_other_enabled_post_types = array();
            if ( is_array( $content_order_for_other_post_types ) && count( $content_order_for_other_post_types ) > 0 ) {
                foreach ( $content_order_for_other_post_types as $post_type_slug => $is_custom_order_enabled ) {
                    if ( $is_custom_order_enabled ) {
                        $content_order_other_enabled_post_types[] = $post_type_slug;
                    }
                }                
            }

            // All post types that should be custom ordered
            $content_order_post_types = array_merge( $content_order_enabled_post_types, $content_order_other_enabled_post_types );
        } else {
            // All post types that should be custom ordered
            $content_order_post_types = $content_order_enabled_post_types;
        }
        
        // Use custom order in wp-admin listing pages/tables for enabled post types
        if ( is_admin() && ( 'edit.php' == $pagenow || 'upload.php' == $pagenow ) && ! isset( $_GET['orderby'] ) ) {
            if ( in_array( $typenow, $content_order_post_types ) ) {
                $query->set( 'orderby', 'menu_order title' );
                $query->set( 'order', 'ASC' );
            }
        }
        
        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            // Use custom order in the frontend for enabled post types
            $content_order_frontend = isset( $options['content_order_frontend'] ) ? $options['content_order_frontend'] : false;
            if ( $content_order_frontend 
                && ! is_admin() 
                && ! $query->is_search() 
            ) {
                // $should_be_custom_sorted_on_frontend = false;

                // if ( in_array( $query->get('post_type'), $content_order_enabled_post_types ) 
                //     || in_array( $query->get('post_type'), $content_order_other_enabled_post_types ) 
                // ) {
                //     $should_be_custom_sorted_on_frontend = true;
                // }

                //===== Up to ASE v7.8.0 ====
                //===== Does not work for taxonomy term page on the frontend ====
                // if ( $query->is_main_query() ) {
                //     // On post types archive pages
                //     // if ( $query->is_post_type_archive() && $should_be_custom_sorted_on_frontend ) {
                //         // vi( $should_be_custom_sorted_on_frontend, '', 'is_main_query' );
                //         $query->set( 'orderby', 'menu_order title' );
                //         $query->set( 'order', 'ASC' );
                //     }

                // } else {
                //     // On secondary queries
                //     if ( $should_be_custom_sorted_on_frontend ) {
                //         // vi( $should_be_custom_sorted_on_frontend, '', 'NOT is_main_query' );
                //         $query->set( 'orderby', 'menu_order title' );
                //         $query->set( 'order', 'ASC' );                      
                //     }
                // }

                //===== WP Sort Order v1.3.5 =====
                //===== Works for taxonomy term page on the frontend but not on single page with custom query loop ====
                // if ( isset( $query->query['suppress_filters'] ) ) {
                //     if ( $query->get( 'orderby' ) == 'date' ) $query->set( 'orderby', 'menu_order' );
                //     if ( $query->get( 'order' ) == 'DESC' ) $query->set( 'order', 'ASC' );
                // // WP_Query( contain main_query )
                // } else {
                //     if ( ! $query->get( 'orderby' ) ) $query->set( 'orderby', 'menu_order' );
                //     if ( ! $query->get( 'order' ) ) $query->set( 'order', 'ASC' );
                // }

                if ( is_home() ) {
                    // Page with (blog) posts
                    if ( $query->is_main_query() ) {
                        if ( in_array( 'post', $content_order_post_types ) ) {
                            $query->set( 'orderby', 'menu_order title' );
                            $query->set( 'order', 'ASC' );
                        }
                    }
                } elseif ( is_archive() ) {
                    // Ref: https://developer.wordpress.org/reference/functions/is_archive/
                    $post_type = '';
                    $should_be_custom_sorted = false;

                    if ( is_post_type_archive() ) {
                        $post_type = get_query_var( 'post_type' );
                        if ( in_array( $post_type, $content_order_post_types ) ) {
                            $should_be_custom_sorted = true;
                        }
                    } elseif ( is_category() || is_tag() || is_tax() ) {
                        $term = get_queried_object();
                        if ( ! is_null( $term ) && is_object( $term ) ) {
                            $taxonomy = $term->taxonomy;
                            $taxonomy_object = get_taxonomy( $taxonomy );
                            $post_types_for_taxonomy = $taxonomy_object->object_type;

                            if ( is_array( $post_types_for_taxonomy ) && is_array( $content_order_post_types ) ) {
                                $intersecting_post_types = array_intersect( $post_types_for_taxonomy, $content_order_post_types );
                            } else {
                                $intersecting_post_types = array();
                            }

                            if ( ! empty( $intersecting_post_types ) ) {
                                $should_be_custom_sorted = true;
                            }                            
                        }
                    } else if ( is_author() ) {
                        // Maybe modify query in the future
                    } else if ( is_date() ) {
                        // Maybe modify query in the future                        
                    }

                    if ( $should_be_custom_sorted ) {
                        $query->set( 'orderby', 'menu_order title' );
                        $query->set( 'order', 'ASC' );                        
                    }
                } else {
                    if ( $query->is_main_query() ) {
                        // is_singular() || is_single() || is_page() || etc.
                        // Most likely does not contain a list of posts, so, we do nothing
                    } else {
                        // List of posts via custom query loop inside a single page/post content
                        if ( in_array( $query->get('post_type'), $content_order_post_types ) ) {
                            $query->set( 'orderby', 'menu_order title' );
                            $query->set( 'order', 'ASC' );
                        }
                    }
                }
                
            } 
        }
    }
    
    /**
     * Make sure newly created posts are assigned the highest menu_order so it's added at the bottom of the existing order
     * 
     * @since 6.2.1
     */
    public function set_menu_order_for_new_posts( $post_id, $post, $update ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $content_order_for = isset( $options['content_order_for'] ) ? $options['content_order_for'] : array();
        $content_order_enabled_post_types = array();
        if ( is_array( $content_order_for ) && count( $content_order_for ) > 0 ) {
            foreach ( $content_order_for as $post_type_slug => $is_custom_order_enabled ) {
                if ( $is_custom_order_enabled ) {
                    $content_order_enabled_post_types[] = $post_type_slug;
                }
            }
        }

        // Only assign menu_order if there are none assigned when creating the post, i.e. menu_order is 0
        if ( in_array( $post->post_type, $content_order_enabled_post_types )
            // New posts most likely are immediately assigned the auto-draft status
            && ( 'auto-draft' == $post->post_status || 'publish' == $post->post_status )
            && $post->menu_order == '0'
            && false === $update
        ) {
            $post_with_highest_menu_order = get_posts( array(
                'post_type'         => $post->post_type,
                'posts_per_page'    => 1,
                'orderby'           => 'menu_order',
                'order'             => 'DESC',
                // 'fields'         => 'ids', // return post IDs instead of objects
            ) );
        
            if ( $post_with_highest_menu_order ) {
                $new_menu_order = (int) $post_with_highest_menu_order[0]->menu_order + 1;
                
                // Assign the one higher menu_order to the new post
                $args = array(
                    'ID'            => $post_id,
                    'menu_order'    => $new_menu_order,
                );
                wp_update_post( $args );                
            }
        }
        
    }
    
    /**
     * Make sure newly created posts are assigned the highest menu_order so it's added at the bottom of the existing order
     * 
     * @since 7.0.0
     */
    public function set_menu_order_for_new_attachments__premium_only( $post_id ) {
        $post = get_post( $post_id );
        
        $options = get_option( ASENHA_SLUG_U, array() );
        $content_order_for_other_post_types = isset( $options['content_order_for_other_post_types'] ) ? $options['content_order_for_other_post_types'] : array();

        $content_order_other_enabled_post_types = array();
        if ( is_array( $content_order_for_other_post_types ) && count( $content_order_for_other_post_types ) > 0 ) {
            foreach ( $content_order_for_other_post_types as $post_type_slug => $is_custom_order_enabled ) {
                if ( $is_custom_order_enabled ) {
                    $content_order_other_enabled_post_types[] = $post_type_slug;
                }
            }                
        }

        if ( in_array( $post->post_type, $content_order_other_enabled_post_types )
            && $post->menu_order == '0'
        ) {
            $post_with_highest_menu_order = get_posts( array(
                'post_type'         => $post->post_type,
                'post_status'       => array( 'inherit', 'private' ),
                'posts_per_page'    => 1,
                'orderby'           => 'menu_order',
                'order'             => 'DESC',
                // 'fields'         => 'ids', // return post IDs instead of objects
            ) );

            if ( $post_with_highest_menu_order ) {
                $new_menu_order = (int) $post_with_highest_menu_order[0]->menu_order + 1;
                
                // Assign the one higher menu_order to the new post
                $args = array(
                    'ID'            => $post_id,
                    'menu_order'    => $new_menu_order,
                );
                wp_update_post( $args );                
            }            
        }
        
    }
    
    /**
     * Apply custom order when retrieving previous and next posts
     * 
     * @link https://plugins.trac.wordpress.org/browser/post-types-order/tags/2.2.6/include/class.cpto.php#L64
     * @since 7.4.2
     */
    public function apply_custom_order_for_adjacent_posts__premium_only( $post, $query ) {
        if ( is_admin() ) {
            return;
        }

        $adjacent_posts_nav_should_be_custom_sorted = false;
        
        if ( is_single() ) {
            $post_type = $post->post_type;

            $options = get_option( ASENHA_SLUG_U, array() );
            $content_order_for = isset( $options['content_order_for'] ) ? $options['content_order_for'] : array();
            $content_order_enabled_post_types = array();
            if ( is_array( $content_order_for ) && count( $content_order_for ) > 0 ) {
                foreach ( $content_order_for as $post_type_slug => $is_custom_order_enabled ) {
                    if ( $is_custom_order_enabled ) {
                        $content_order_enabled_post_types[] = $post_type_slug;
                    }
                }            
            }

            if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                $content_order_for_other_post_types = isset( $options['content_order_for_other_post_types'] ) ? $options['content_order_for_other_post_types'] : array();
                $content_order_other_enabled_post_types = array();
                if ( is_array( $content_order_for_other_post_types ) && count( $content_order_for_other_post_types ) > 0 ) {
                    foreach ( $content_order_for_other_post_types as $post_type_slug => $is_custom_order_enabled ) {
                        if ( $is_custom_order_enabled ) {
                            $content_order_other_enabled_post_types[] = $post_type_slug;
                        }
                    }                
                }

                if ( in_array( $post_type, $content_order_enabled_post_types ) 
                    || in_array( $post_type, $content_order_other_enabled_post_types ) 
                ) {
                    $adjacent_posts_nav_should_be_custom_sorted = true;
                }
            } else {
                if ( in_array( $post_type, $content_order_enabled_post_types ) ) {
                    $adjacent_posts_nav_should_be_custom_sorted = true;
                }
            }
            
        }
        
        if ( $adjacent_posts_nav_should_be_custom_sorted ) {
            add_filter( 'get_previous_post_where', array( $this, 'get_previous_post_where__premium_only' ), 99, 3);
            add_filter( 'get_previous_post_sort', array( $this, 'get_previous_post_sort__premium_only' ) );
            add_filter( 'get_next_post_where', array( $this, 'get_next_post_where__premium_only' ), 99, 3);
            add_filter( 'get_next_post_sort', array( $this, 'get_next_post_sort__premium_only' ) );            
        }
    }
    
    /**
     * Set the WHERE clause to get the previous post
     * 
     * @link https://plugins.trac.wordpress.org/browser/post-types-order/tags/2.2.6/include/class.functions.php#L88
     * @since 7.4.2
     */
    public function get_previous_post_where__premium_only( $where, $in_same_term, $excluded_terms ) {
        global $post, $wpdb;

        if ( empty( $post ) ) {
            return $where;
        }
        
        // WordPress does not pass through this varialbe, so we presume it's category..
        $taxonomy = 'category';
        if ( preg_match( '/ tt.taxonomy = \'([^\']+)\'/i', $where, $match ) ) {
            $taxonomy = $match[1];
        }
        
        $_join = '';
        $_where = '';
        
        if ( $in_same_term || ! empty( $excluded_terms ) ) 
            {
                $_join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
                $_where = $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

                if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) {
                    // back-compat, $excluded_terms used to be $excluded_terms with IDs separated by " and "
                    if ( false !== strpos( $excluded_terms, ' and ' ) ) {
                        _deprecated_argument( __FUNCTION__, '3.3', sprintf( esc_html__( 'Use commas instead of %s to separate excluded terms.' ), "'and'" ) );
                        $excluded_terms = explode( ' and ', $excluded_terms );
                    } else {
                        $excluded_terms = explode( ',', $excluded_terms );
                    }

                    $excluded_terms = array_map( 'intval', $excluded_terms );
                }

                if ( $in_same_term ) {
                    $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

                    // Remove any exclusions from the term array to include.
                    $term_array = array_diff( $term_array, (array) $excluded_terms );
                    $term_array = array_map( 'intval', $term_array );
            
                    $_where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
                }

                if ( ! empty( $excluded_terms ) ) {
                    $_where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', $excluded_terms ) . ') )';
                }
            }
            
        $current_menu_order = $post->menu_order;
        
        $query = $wpdb->prepare( "SELECT p.* FROM $wpdb->posts AS p
                    $_join
                    WHERE p.post_date < %s  AND p.menu_order = %d AND p.post_type = %s AND p.post_status = 'publish' $_where" ,  $post->post_date, $current_menu_order, $post->post_type);
        $results = $wpdb->get_results($query);
                
        if ( count( $results ) > 0 ) {
            $where .= $wpdb->prepare( " AND p.menu_order = %d", $current_menu_order );
        } else {
            $where = str_replace("p.post_date < '". $post->post_date  ."'", "p.menu_order > '$current_menu_order'", $where);  
        }
        
        return $where;
    }
    
    /**
     * Set the sorting for getting the previous post
     * 
     * @link https://plugins.trac.wordpress.org/browser/post-types-order/tags/2.2.6/include/class.functions.php#L165
     * @since 7.4.2
     */
    public function get_previous_post_sort__premium_only() {
        global $post, $wpdb;
        
        $sort = 'ORDER BY p.menu_order ASC, p.post_date DESC LIMIT 1';

        return $sort;
    }

    /**
     * Set the WHERE clause to get the next post
     * 
     * @link https://plugins.trac.wordpress.org/browser/post-types-order/tags/2.2.6/include/class.functions.php#L182
     * @since 7.4.2
     */
    public function get_next_post_where__premium_only( $where, $in_same_term, $excluded_terms ) {
        global $post, $wpdb;

        if ( empty( $post ) ) {
            return $where;
        }
        
        $taxonomy = 'category';
        if ( preg_match( '/ tt.taxonomy = \'([^\']+)\'/i', $where, $match ) ) {
            $taxonomy = $match[1];
        }
        
        $_join = '';
        $_where = '';
                    
        if ( $in_same_term || ! empty( $excluded_terms ) ) 
            {
                $_join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
                $_where = $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

                if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) {
                    // Back-compatibility, $excluded_terms used to be $excluded_terms with IDs separated by " and "
                    if ( false !== strpos( $excluded_terms, ' and ' ) ) {
                        _deprecated_argument( __FUNCTION__, '3.3', sprintf( esc_html__( 'Use commas instead of %s to separate excluded terms.' ), "'and'" ) );
                        $excluded_terms = explode( ' and ', $excluded_terms );
                    } else {
                        $excluded_terms = explode( ',', $excluded_terms );
                    }

                    $excluded_terms = array_map( 'intval', $excluded_terms );
                }

                if ( $in_same_term ) {
                    $term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

                    // Remove any exclusions from the term array to include.
                    $term_array = array_diff( $term_array, (array) $excluded_terms );
                    $term_array = array_map( 'intval', $term_array );
            
                    $_where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
                }

                if ( ! empty( $excluded_terms ) ) {
                    $_where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', $excluded_terms ) . ') )';
                }
            }
            
        $current_menu_order = $post->menu_order;
        
        // Check if there are more posts with lower menu_order
        $query = $wpdb->prepare( "SELECT p.* FROM $wpdb->posts AS p
                    $_join
                    WHERE p.post_date > %s AND p.menu_order = %d AND p.post_type = %s AND p.post_status = 'publish' $_where", $post->post_date, $current_menu_order, $post->post_type );
        $results = $wpdb->get_results($query);
                
        if ( count( $results ) > 0 ) {
            $where .= $wpdb->prepare(" AND p.menu_order = %d", $current_menu_order );
        } else {
            $where = str_replace("p.post_date > '". $post->post_date  ."'", "p.menu_order < '$current_menu_order'", $where);  
        }
        
        return $where;
    }

    /**
     * Set the sorting for getting the next post
     * 
     * @link https://plugins.trac.wordpress.org/browser/post-types-order/tags/2.2.6/include/class.functions.php#L259
     * @since 7.4.2
     */
    public function get_next_post_sort__premium_only() {
        global $post, $wpdb; 
        
        $sort = 'ORDER BY p.menu_order DESC, p.post_date ASC LIMIT 1';
        
        return $sort;
    }
    
}