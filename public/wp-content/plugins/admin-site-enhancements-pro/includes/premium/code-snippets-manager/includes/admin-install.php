<?php
/**
 * Code Snippets Manager
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Code_Snippets_Manager_Install 
 */
class Code_Snippets_Manager_Install {

    public static function install() {
        self::create_roles();
        self::register_post_type();
        flush_rewrite_rules();
    }  

    /**
     * Create roles and capabilities.
     */
    public static function create_roles() {
        global $wp_roles;


        if ( ! current_user_can('update_plugins') )
            return;

        if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        if ( isset( $wp_roles->roles['code_snippets_editor'] ) ) 
            return;

        // Add Code Snippet Editor role
        add_role( 'code_snippets_editor', __( 'Code Snippets Editor', 'admin-site-enhancements'), array() );

        $capabilities = array();

        $capability_types = array( 'code_snippets' );

        foreach ( $capability_types as $capability_type ) {

            $capabilities[ $capability_type ] = array(
                // Post type
                "edit_{$capability_type}",
                "read_{$capability_type}",
                "delete_{$capability_type}",
                "edit_{$capability_type}s",
                "edit_others_{$capability_type}s",
                "publish_{$capability_type}s",
                "delete_{$capability_type}s",
                "delete_published_{$capability_type}s",
                "delete_others_{$capability_type}s",
                "edit_published_{$capability_type}s",
            );
        }

        foreach ( $capabilities as $cap_group ) {
            foreach ( $cap_group as $cap ) {
                $wp_roles->add_cap( 'code_snippets_editor', $cap );
                $wp_roles->add_cap( 'administrator', $cap );
            }
        }
    }

    /**
     * Create the code-snippets-manager post type
     */
    public static function register_post_type() {
        $labels = array(
            'name'                   => _x( 'Code Snippets', 'post type general name', 'admin-site-enhancements'),
            'singular_name'          => _x( 'Code Snippet', 'post type singular name', 'admin-site-enhancements'),
            'menu_name'              => _x( 'Code Snippets', 'admin menu', 'admin-site-enhancements'),
            'name_admin_bar'         => _x( 'Code Snippet', 'add new on admin bar', 'admin-site-enhancements'),
            'add_new'                => _x( 'Add Code Snippet', 'add new', 'admin-site-enhancements'),
            'add_new_item'           => __( 'Add Code Snippet', 'admin-site-enhancements'),
            'new_item'               => __( 'New Code Snippet', 'admin-site-enhancements'),
            'edit_item'              => __( 'Edit Code Snippet', 'admin-site-enhancements'),
            'view_item'              => __( 'View Code Snippet', 'admin-site-enhancements'),
            'all_items'              => __( 'All Code Snippets', 'admin-site-enhancements'),
            'search_items'           => __( 'Search Code Snippets', 'admin-site-enhancements'),
            'parent_item_colon'      => __( 'Parent Code Snippet:', 'admin-site-enhancements'),
            'not_found'              => __( 'No Code Snippets found.', 'admin-site-enhancements'),
            'not_found_in_trash'     => __( 'No Code Snippets found in Trash.', 'admin-site-enhancements')
        );

        $capability_type = 'code_snippets';
        $capabilities = array(
            'edit_post'              => "edit_{$capability_type}",
            'read_post'              => "read_{$capability_type}",
            'delete_post'            => "delete_{$capability_type}",
            'edit_posts'             => "edit_{$capability_type}s",
            'edit_others_posts'      => "edit_others_{$capability_type}s",
            'publish_posts'          => "publish_{$capability_type}s",
            'read'                   => "read_{$capability_type}",
            'delete_posts'           => "delete_{$capability_type}s",
            'delete_published_posts' => "delete_published_{$capability_type}s",
            'delete_others_posts'    => "delete_others_{$capability_type}s",
            'edit_published_posts'   => "edit_published_{$capability_type}s",
            'create_posts'           => "edit_{$capability_type}s",
        );

        $args = array(
            'labels'                 => $labels,
            'description'            => __( 'CSS, JS, HTML and PHP code snippets', 'admin-site-enhancements' ),
            'public'                 => true,
            'publicly_queryable'     => false,
            'show_ui'                => true,
            'show_in_menu'           => true,
            // https://icon-sets.iconify.design/heroicons/code-bracket-20-solid/
            'menu_icon'              => 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGZpbGwtcnVsZT0iZXZlbm9kZCIgZD0iTTYuMjggNS4yMmEuNzUuNzUgMCAwIDEgMCAxLjA2TDIuNTYgMTBsMy43MiAzLjcyYS43NS43NSAwIDAgMS0xLjA2IDEuMDZMLjk3IDEwLjUzYS43NS43NSAwIDAgMSAwLTEuMDZsNC4yNS00LjI1YS43NS43NSAwIDAgMSAxLjA2IDBabTcuNDQgMGEuNzUuNzUgMCAwIDEgMS4wNiAwbDQuMjUgNC4yNWEuNzUuNzUgMCAwIDEgMCAxLjA2bC00LjI1IDQuMjVhLjc1Ljc1IDAgMCAxLTEuMDYtMS4wNkwxNy40NCAxMGwtMy43Mi0zLjcyYS43NS43NSAwIDAgMSAwLTEuMDZabS0yLjM0My0zLjIwOWEuNzUuNzUgMCAwIDEgLjYxMi44NjdsLTIuNSAxNC41YS43NS43NSAwIDAgMS0xLjQ3OC0uMjU1bDIuNS0xNC41YS43NS43NSAwIDAgMSAuODY2LS42MTJaIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=',
            'query_var'              => false,
            'rewrite'                => array( 'slug' => 'code-snippet' ),
            'capability_type'        => $capability_type,
            'capabilities'           => $capabilities, 
            'has_archive'            => true,
            'hierarchical'           => false,
            'exclude_from_search'    => true,
            'can_export'             => false,
            'supports'               => array( 'title', 'page-attributes', 'revisions' ),
            'taxonomies'             => array( 'asenha_code_snippet_category' ),
        );

        register_post_type( 'asenha_code_snippet', $args );
    }
    
    /**
     * Create snippet category taxonomy
     * 
     * @since 6.3.1
     */
    public static function register_category() {
        $labels = array(
            'name'              => 'Snippet Categories',
            'singular_name'     => 'Snippet Category',
            'menu_name'         => 'Snippet Categories',
            'search_items'      => 'Search Snippet Categories',
            'all_items'         => 'All Categories',
            'edit_item'         => 'Edit Snippet Category',
            'update_item'       => 'Update Snippet Category',
        );
      
        $args = array(
            'public'                => true,
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_admin_column'     => true,
            'show_in_quick_edit'    => true,
            'capabilities'          => array(
                                        'manage_terms'      => 'manage_categories',
                                        'edit_terms'        => 'manage_categories',
                                        'delete_terms'      => 'manage_categories',
                                        'assign_terms'      => 'edit_code_snippets',
            ),
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'snippet-category' ),
            'default_term'          => array(
                                        'name'      => 'Uncategorized',
                                        'slug'      => 'uncategorized',
            ),
        );
      
        register_taxonomy( 'asenha_code_snippet_category', 'asenha_code_snippet', $args );
    }
    
}

?>
