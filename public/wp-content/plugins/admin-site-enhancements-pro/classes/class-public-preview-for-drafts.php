<?php

namespace ASENHA\Classes;

/**
 * Class for Public Preview for Drafts
 *
 * @since 6.9.5
 */
class Public_Preview_For_Drafts {

    /**
     * Add public preview link on draft posts of post types where it is enabled
     * 
     * @since 7.5.0
     */
    public function add__action_row_public_preview_link( $actions, $post ) {
        $options = get_option( ASENHA_SLUG_U, array() );
        $public_drafts_preview_for = isset( $options['public_drafts_preview_for'] ) ? $options['public_drafts_preview_for'] : array();
        $public_drafts_preview_for_post_types = array();

        if ( is_array( $public_drafts_preview_for ) && count( $public_drafts_preview_for ) > 0 ) {
            foreach ( $public_drafts_preview_for as $post_type_slug => $is_public_preview_enabled ) {
                if ( $is_public_preview_enabled ) {
                    $public_drafts_preview_for_post_types[] = $post_type_slug;
                }
            }
        }
        
        if ( ! empty( $public_drafts_preview_for_post_types ) ) {
            $post_type = $post->post_type;
            $post_status = $post->post_status;
            
            if ( in_array( $post_type, $public_drafts_preview_for_post_types ) 
                && 'draft' == $post_status
            ) {
                $public_preview_link = self::get_preview_link( $post );
                $actions['asenha-public-preview'] = '<a href="' . $public_preview_link . '" title="' . __( 'Link to preview this draft publiclly', 'admin-site-enhancements' ) . '">' . __( 'Public Preview', 'admin-site-enhancements' ) . '</a>';                
            }
        }
        
        return $actions;
    }

    /**
     * Add public preview button in post submit/update box in classic editor
     * 
     * @since 7.5.0
     */
    public function add_submitbox_public_preview_button() {
        global $post, $pagenow;

        $options = get_option( ASENHA_SLUG_U, array() );
        $public_drafts_preview_for = isset( $options['public_drafts_preview_for'] ) ? $options['public_drafts_preview_for'] : array();
        $public_drafts_preview_for_post_types = array();

        if ( is_array( $public_drafts_preview_for ) && count( $public_drafts_preview_for ) > 0 ) {
            foreach ( $public_drafts_preview_for as $post_type_slug => $is_public_preview_enabled ) {
                if ( $is_public_preview_enabled ) {
                    $public_drafts_preview_for_post_types[] = $post_type_slug;
                }
            }
        }
        
        if ( ! empty( $public_drafts_preview_for_post_types ) ) {
            if ( in_array( $post->post_type, $public_drafts_preview_for_post_types ) ) {
                if ( 'draft' == $post->post_status ) {
                    $common_methods = new Common_Methods;
                    $post_type_singular_label = $common_methods->get_post_type_singular_label( $post );
                    $public_preview_link = self::get_preview_link( $post );

                    $public_preview_link_section = '<div class="additional-actions"><span id="public-preview"><a href="' . $public_preview_link . '" title="' . __( 'Link to preview this draft publiclly', 'admin-site-enhancements' ) . '">' . __( 'Public Preview', 'admin-site-enhancements' ) . '</a></span></div>';
                    echo wp_kses_post( $public_preview_link_section );                    
                }
            }
        }
    }
    
    /**
     * Add public preview button in the block editor
     * 
     * @since 7.5.1
     */
    public function add_gutenberg_public_preview_button() {
        global $post, $pagenow;
        $common_methods = new Common_Methods;

        if ( is_object( $post ) && 'post.php' == $pagenow ) {
            if ( 'draft' == $post->post_status ) {
                // Check if we're inside the block editor. Ref: https://wordpress.stackexchange.com/a/309955.
                if ( $common_methods->is_in_block_editor() ) {
                    $public_preview_link = self::get_preview_link( $post );

                    // Ref: https://plugins.trac.wordpress.org/browser/duplicate-page/tags/4.5/duplicatepage.php#L286
                    wp_enqueue_style( 'asenha-gutenberg-public-preview', ASENHA_URL . 'assets/premium/css/gutenberg-public-preview.css' );

                    wp_register_script( 'asenha-gutenberg-public-preview', ASENHA_URL . 'assets/premium/js/gutenberg-public-preview.js', array( 'wp-edit-post', 'wp-plugins', 'wp-i18n', 'wp-element' ), ASENHA_VERSION);

                    wp_localize_script( 'asenha-gutenberg-public-preview', 'pp_params', array(
                        'pp_post_text'      => __( 'Public Preview', 'admin-site-enhancements' ),
                        'pp_post_title'     => __( 'Link to preview this draft publiclly', 'admin-site-enhancements' ),
                        'pp_public_preview_link' => $public_preview_link
                        )
                    );

                    wp_enqueue_script( 'asenha-gutenberg-public-preview' );
                }                
            }
        }        
    }

    /**
     * Registers the new query var `_ppp`.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L483
     *
     * @param  array $qv Existing list of query variables.
     * @return array List of query variables.
     */
    public static function add_query_var( $qv ) {
        $qv[] = 'pp';

        return $qv;
    }
    
    /**
     * Registers the filter to handle a public preview.
     *
     * Filter will be set if it's the main query, a preview, a singular page
     * and the query var `_ppp` exists.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L499
     *
     * @param object $query The WP_Query object.
     */
    public static function show_public_preview( $query ) {
        if (
            $query->is_main_query() &&
            $query->is_preview() &&
            $query->is_singular() &&
            $query->get( 'pp' )
        ) {
            if ( ! headers_sent() ) {
                nocache_headers();
                header( 'X-Robots-Tag: noindex' );
            }
            if ( function_exists( 'wp_robots_no_robots' ) ) { // WordPress 5.7+
                add_filter( 'wp_robots', 'wp_robots_no_robots' );
            } else {
                add_action( 'wp_head', 'wp_no_robots' );
            }

            add_filter( 'posts_results', array( __CLASS__, 'set_post_to_publish' ), 10, 2 );
        }
    }    

    /**
     * Sets the post status of the first post to publish, so we don't have to do anything
     * *too* hacky to get it to load the preview.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L576
     *
     * @param  array $posts The post to preview.
     * @return array The post that is being previewed.
     */
    public static function set_post_to_publish( $posts ) {
        // Remove the filter again, otherwise it will be applied to other queries too.
        remove_filter( 'posts_results', array( __CLASS__, 'set_post_to_publish' ), 10 );

        if ( empty( $posts ) ) {
            return $posts;
        }

        $post_id = (int) $posts[0]->ID;

        // If the post has gone live, redirect to it's proper permalink.
        self::maybe_redirect_to_published_post( $post_id );

        if ( ! self::verify_nonce( get_query_var( 'pp' ), 'public_preview_for_draft_' . $post_id ) ) {
            wp_die( __( 'This link has expired or is invalid!', 'admin-site-enhancements' ), 403 );
        }

        if ( 'draft' == $posts[0]->post_status ) {
            // Set post status to publish so that it's visible.
            $posts[0]->post_status = 'publish';

            // Disable comments and pings for this post.
            add_filter( 'comments_open', '__return_false' );
            add_filter( 'pings_open', '__return_false' );
            add_filter( 'wp_link_pages_link', array( __CLASS__, 'filter_wp_link_pages_link' ), 10, 2 );
        }

        return $posts;
    }
    
    /**
     * Filters the HTML output of individual page number links to use the
     * preview link.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L555
     *
     * @param string $link        The page number HTML output.
     * @param int    $page_number Page number for paginated posts' page links.
     * @return string The filtered HTML output.
     */
    public static function filter_wp_link_pages_link( $link, $page_number ) {
        $post = get_post();
        if ( ! $post ) {
            return $link;
        }

        $preview_link = self::get_preview_link( $post );
        $preview_link = add_query_arg( 'page', $page_number, $preview_link );

        return preg_replace( '~href=(["|\'])(.+?)\1~', 'href=$1' . $preview_link . '$1', $link );
    }

    /**
     * Redirects to post's proper permalink, if it has gone live.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L610
     *
     * @param int $post_id The post id.
     * @return false False of post status is not a published status.
     */
    private static function maybe_redirect_to_published_post( $post_id ) {
        if ( ! in_array( get_post_status( $post_id ), array( 'publish', 'private' ), true ) ) {
            return false;
        }

        wp_safe_redirect( get_permalink( $post_id ), 301 );
        exit;
    }

    /**
     * Returns the public preview link.
     *
     * The link is the home link with these parameters:
     *  - preview, always true (query var for core)
     *  - _ppp, a custom nonce, see DS_Public_Post_Preview::create_nonce()
     *  - page_id or p or p and post_type to specify the post.
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L290
     *
     * @param WP_Post $post The post object.
     * @return string The generated public preview link.
     */
    public static function get_preview_link( $post ) {
        if ( 'page' === $post->post_type ) {
            $args = array(
                'page_id' => $post->ID,
            );
        } elseif ( 'post' === $post->post_type ) {
            $args = array(
                'p' => $post->ID,
            );
        } else {
            $args = array(
                'p'         => $post->ID,
                'post_type' => $post->post_type,
            );
        }

        $args['preview'] = 'true';
        $args['pp']    = self::create_nonce( 'public_preview_for_draft_' . $post->ID );

        $link = add_query_arg( $args, home_url( '/' ) );

        return $link;
    }

    /**
     * Get the time-dependent variable for nonce creation.
     *
     * @see wp_nonce_tick()
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L628
     *
     * @return int The time-dependent variable.
     */
    private static function nonce_tick() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $public_preview_max_days = isset( $options['public_preview_max_days'] ) ? $options['public_preview_max_days'] : 3;

        $nonce_life = $public_preview_max_days * DAY_IN_SECONDS; 

        return ceil( time() / ( $nonce_life / 2 ) );
    }

    /**
     * Creates a random, one time use token. Without an UID.
     *
     * @see wp_create_nonce()
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L644
     *
     * @since 7.5.0
     *
     * @param  string|int $action Scalar value to add context to the nonce.
     * @return string The one use form token.
     */
    private static function create_nonce( $action = -1 ) {
        $i = self::nonce_tick();

        return substr( wp_hash( $i . $action, 'nonce' ), -12, 10 );
    }

    /**
     * Verifies that correct nonce was used with time limit. Without an UID.
     *
     * @see wp_verify_nonce()
     *
     * @since 7.5.0
     * @link https://plugins.trac.wordpress.org/browser/public-post-preview/tags/2.10.0/public-post-preview.php#L661
     *
     * @param string     $nonce  Nonce that was used in the form to verify.
     * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
     * @return bool               Whether the nonce check passed or failed.
     */
    private static function verify_nonce( $nonce, $action = -1 ) {
        $i = self::nonce_tick();

        // Nonce generated 0-12 hours ago.
        if ( substr( wp_hash( $i . $action, 'nonce' ), -12, 10 ) === $nonce ) {
            return 1;
        }

        // Nonce generated 12-24 hours ago.
        if ( substr( wp_hash( ( $i - 1 ) . $action, 'nonce' ), -12, 10 ) === $nonce ) {
            return 2;
        }

        // Invalid nonce.
        return false;
    }
    
}