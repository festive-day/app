<?php

namespace ASENHA\Classes;

/**
 * Class for Search Engines Visibility Status module
 *
 * @since 6.9.5
 */
class Search_Engines_Visibility {
    
    /**
     * Maybe change the status of search engine visibility based on the current site's URL and the live site's URL
     * 
     * @since 6.9.13
     */
    public function handle_search_engine_visibility__premium_only() {
        if ( wp_doing_ajax() || wp_doing_cron() ) {
            return;
        }

        $options = get_option( ASENHA_SLUG_U, array() );
        $blog_public = intval( get_option( 'blog_public' ) ); // 0 means search engine visibility is disabled, 1 means it's enabled
        $current_site_url = get_site_url(); // e.g. https://dev.site.com with no trailing slash

        if ( isset( $options['live_site_url'] ) ) {
            if ( false !== strpos( $options['live_site_url'], 'http' ) ) {
                $live_site_url = $options['live_site_url'];
            } else {
                // Legacy support for when base64 encoding was used prior to v7.3.1
                $live_site_url = base64_decode( $options['live_site_url'] );
            }
        } else {
            $live_site_url = '';
        }
        
        // If there's no live / production site URL defined, do nothing
        if ( empty( $live_site_url ) ) {
            return;
        }

        if ( ! empty( $live_site_url ) // live / production site URL is defined
            && $current_site_url !== $live_site_url // we're on a dev/staging/local site
            // && $blog_public !== 0 // search engine visibility is enabled
        ) {
            // Let's disable search engine visibility 
            update_option( 'blog_public', 0 );
        }
    }

    /**
     * Display search engine visibility status indicator and notice
     * 
     * @since 6.6.0
     */
    public function maybe_display_search_engine_visibility_status() {
        // Check if the user is an admin
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Get the option 'blog_public' to check search engine visibility
        // If 'blog_public' is '0', it means 'Discourage search engines from indexing this site' is checked
        if ( get_option( 'blog_public' ) === '0' ) {
            // add_action( 'admin_notices', array( $this, 'display_admin_notice_for_search_visibility' ) );
            add_action( 'admin_bar_menu', array( $this, 'add_notice_in_admin_bar' ), 100 );
        }
    }

    public function display_admin_notice_for_search_visibility() {
        // echo '<div class="notice notice-warning is-dismissible">';
        // echo '<p><strong>Search Engine Visibility is OFF</strong>. Search engines are discouraged from indexing this site. <a href="' . esc_url( admin_url( 'options-reading.php' ) ) . '"><strong>Change the setting »</strong></a></p>';
        // echo '</div>';
    }

    public function add_notice_in_admin_bar( $wp_admin_bar ) {
        $node_id = 'search_visibility_notice';

        // Add inline style for warning background color
        ?>
        <style>#wpadminbar #wp-admin-bar-search_visibility_notice > .ab-item { background-color: #ff9a00; color: #fff; font-weight: 600; }</style>
        <?php

        $args = array(
            'id'        => $node_id,
            'parent'    => 'top-secondary',
            'title'     => __( 'SE Visibility: OFF', 'admin-site-enhancements' ),
            'href'      => admin_url( 'options-reading.php' ),
            'meta'      => array( 
                'title' => __( 'Search engines are discouraged from indexing this site. Click to change the settings.', 'admin-site-enhancements' )
            ),
        );
        $wp_admin_bar->add_node( $args );
    }
        
}