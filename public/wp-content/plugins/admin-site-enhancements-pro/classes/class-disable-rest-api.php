<?php

namespace ASENHA\Classes;

use WP_Error;

/**
 * Class for Disable REST API module
 *
 * @since 6.9.5
 */
class Disable_REST_API {

    /**
     * Disable REST API for non-authenticated users. This is for WP v4.7 or later.
     *
     * @since 2.9.0
     */
    public function disable_rest_api( $errors ) {

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $options = get_option( ASENHA_SLUG_U, array() );

            // Get roles allowed to access REST API while logged-in
            if ( ! function_exists( 'get_editable_roles' ) ) {
                require_once ABSPATH . 'wp-admin/includes/user.php';
            }
            
            $for_roles_all = array();
            $all_roles = get_editable_roles();
            $all_roles = array_keys( $all_roles ); // single dimensional array of all role slugs
            foreach ( $all_roles as $role ) {
                $for_roles_all[$role] = true;
            }
            
            $for_roles = isset( $options['enable_rest_api_for'] ) ? $options['enable_rest_api_for'] : $for_roles_all;
            $roles_rest_api_access_enabled = array();

            // Assemble single-dimensional array of roles for which duplication would be enabled
            if ( is_array( $for_roles ) && ( count( $for_roles ) > 0 ) ) {
                foreach( $for_roles as $role_slug => $rest_api_access_enabled ) {
                    if ( $rest_api_access_enabled ) {
                        $roles_rest_api_access_enabled[] = $role_slug;
                    }
                }
            }

            // Get REST API routes excluded from access blockage
            $excluded_api_routes_raw = ( isset( $options['disable_rest_api_excluded_routes'] ) ) ? explode( PHP_EOL, $options['disable_rest_api_excluded_routes'] ) : array();
            $excluded_api_routes = array();
            if ( ! empty( $excluded_api_routes_raw ) ) {
                foreach( $excluded_api_routes_raw as $excluded_api_route ) {
                    $excluded_api_routes[] = trim( $excluded_api_route );
                }
            }
        }
        
        $allow_rest_api_access = false;
        
        // Get the REST API route being requested,e.g. wp/v2/posts | altcha/v1/challenge (without preceding slash /)
        // Ref: https://developer.wordpress.org/reference/hooks/rest_authentication_errors/#comment-6463
        $route = ltrim( $GLOBALS['wp']->query_vars['rest_route'], '/' );
        
        if ( empty( $route ) ) {
            // This is when visiting /wp-json root
            $allow_rest_api_access = false;;
        } elseif ( false !== strpos( $route, 'altcha/v1' ) 
            || (  in_array( 'contact-form-7/wp-contact-form-7.php', get_option( 'active_plugins', array() ) ) 
                    && false !== strpos( $route, 'contact-form-7/' ) )
            || (  in_array( 'the-events-calendar/the-events-calendar.php', get_option( 'active_plugins', array() ) ) 
                    && false !== strpos( $route, 'tribe/' ) ) // Route used to AJAX load past events in the calendar
        ) {
            $allow_rest_api_access = true;
        } else {
            if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                if ( ! empty( $excluded_api_routes ) ) {
                    foreach ( $excluded_api_routes as $excluded_route ) {
                        if ( ! empty( $route ) 
                            && ! empty( $excluded_route )
                            && false !== strpos( $route, $excluded_route ) 
                        ) {
                            $allow_rest_api_access = true;
                            break;
                        }
                    }
                }
            }
        }

        if ( is_user_logged_in() ) {
            if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
                if ( count( $roles_rest_api_access_enabled ) > 0 ) {
                    $current_user = wp_get_current_user();
                    $current_user_roles = (array) $current_user->roles; // single dimensional array of role slugs

                    foreach ( $current_user_roles as $role ) {
                        if ( in_array( $role, $roles_rest_api_access_enabled ) ) {
                            $allow_rest_api_access = true;
                            break;
                        }
                    }
                }
            } else {
                $allow_rest_api_access = true;
            }
        }

        if ( ! $allow_rest_api_access ) {
            return new WP_Error(
                'rest_api_authentication_required', 
                __( 'The REST API has been restricted to authenticated users.', 'admin-site-enhancements' ), 
                array( 
                    'status' => rest_authorization_required_code() 
                ) 
            );            
        }

    }
    
}