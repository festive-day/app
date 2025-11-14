<?php

namespace ASENHA\Classes;

/**
 * Class for additional features for Media Categories module
 *
 * @since 7.8.3
 */
class Media_Categories_Extras {

    /**
     * Add bulk category removal actions in the list view of media library
     * 
     * @link https://plugins.trac.wordpress.org/browser/wp-media-library-categories/tags/2.0.2/index.php#L360
     * @since 7.8.3
     */
    public function add_bulk_actions() {
        $taxonomy = 'asenha-media-category';
        $terms = get_terms( $taxonomy, 'hide_empty=0' );

        if ( $terms && ! is_wp_error( $terms ) ) {
            echo '<script type="text/javascript">';
            echo 'jQuery(window).on(\'load\',function(){';
            echo 'jQuery(\'<optgroup id="wpmediacategory_optgroup1" label="' .  html_entity_decode( __( 'Categories', 'admin-site-enhancements' ), ENT_QUOTES, 'UTF-8' ) . '">\').appendTo("select[name=\'action\']");';
            echo 'jQuery(\'<optgroup id="wpmediacategory_optgroup2" label="' .  html_entity_decode( __( 'Categories', 'admin-site-enhancements' ), ENT_QUOTES, 'UTF-8' ) . '">\').appendTo("select[name=\'action2\']");';

            // Remove categories
            foreach ( $terms as $term ) {
                $sTxtRemove = esc_js( __( 'Remove', 'admin-site-enhancements' ) . ': ' . $term->name );
                echo "jQuery('<option>').val('wpmediacategory_remove_" . $term->term_taxonomy_id . "').text('" . $sTxtRemove . "').appendTo('#wpmediacategory_optgroup1');";
                echo "jQuery('<option>').val('wpmediacategory_remove_" . $term->term_taxonomy_id . "').text('" . $sTxtRemove . "').appendTo('#wpmediacategory_optgroup2');";
            }

            // Remove all categories
            echo "jQuery('<option>').val('wpmediacategory_remove_0').text('" . esc_js( __( 'Remove all categories', 'admin-site-enhancements' ) ) . "').appendTo('#wpmediacategory_optgroup1');";
            echo "jQuery('<option>').val('wpmediacategory_remove_0').text('" . esc_js( __( 'Remove all categories', 'admin-site-enhancements' ) ) . "').appendTo('#wpmediacategory_optgroup2');";
            echo "});";
            echo "</script>";            
        }
    }

    /**
     * Handle bulk action to remove a category from multiple attachments
     * 
     * @link https://plugins.trac.wordpress.org/browser/wp-media-library-categories/tags/2.0.2/index.php#L397
     * @Link https://developer.wordpress.org/reference/hooks/load-page_hook/
     * @since 7.8.3
     */
    public function handle_bulk_action() {
        global $wpdb;

        if ( ! isset( $_REQUEST['action'] ) ) {
            return;
        }

        // Is it a category?
        $action = ( $_REQUEST['action'] != -1 ) ? $_REQUEST['action'] : $_REQUEST['action2'];
        if ( substr( $action, 0, 16 ) != 'wpmediacategory_' ) {
            return;
        }

        // Cecurity check
        check_admin_referer( 'bulk-media' );

        // Make sure IDs are submitted.  Depending on the resource type, this may be 'media' or 'post'.
        if ( isset( $_REQUEST['media'] ) ) {
            $post_ids = array_map( 'intval', $_REQUEST['media'] );
        }
        // vi( $post_ids );

        if( empty( $post_ids ) ) {
            return;
        }

        $sendback = admin_url( "upload.php?mode=list" );

        // remember pagenumber
        $pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
        $sendback = add_query_arg( 'paged', $pagenum, $sendback );

        // Remember orderby
        if ( isset( $_REQUEST['orderby'] ) ) {
            $orderby = sanitize_text_field( $_REQUEST['orderby'] );
            $sendback = add_query_arg( 'orderby', $orderby, $sendback );
        }
        // Remember order
        if ( isset( $_REQUEST['order'] ) ) {
            $order = sanitize_text_field( $_REQUEST['order'] );
            $sendback = add_query_arg( 'order', $order, $sendback );
        }
        // Remember author
        if ( isset( $_REQUEST['author'] ) ) {
            $author = sanitize_text_field( $_REQUEST['author'] );
            $sendback = add_query_arg( 'author', $author, $sendback );
        }
        // Remember category
        if ( isset( $_REQUEST['asenha-media-category'] ) ) {
            $category = sanitize_text_field( $_REQUEST['asenha-media-category'] );
            $sendback = add_query_arg( 'asenha-media-category', $category, $sendback );
        }
        // vi( $sendback );

        foreach( $post_ids as $post_id ) {

            if ( is_numeric( str_replace( 'wpmediacategory_remove_', '', $action ) ) ) {
                $term_id = str_replace( 'wpmediacategory_remove_', '', $action );

                // Remove all categories
                if ( $term_id == 0 ) {
                    $wpdb->delete( $wpdb->term_relationships,
                        array(
                            'object_id' => $post_id
                        ),
                        array(
                            '%d'
                        )
                    );
                // Remove category
                } else {
                    $wpdb->delete( $wpdb->term_relationships,
                        array(
                            'object_id'        => $post_id,
                            'term_taxonomy_id' => $term_id
                        ),
                        array(
                            '%d',
                            '%d'
                        )
                    );
                }

            }
        }

        $this->update_count_callback();

        // Perform a safe (local) redirect
        wp_safe_redirect( $sendback );
        exit();        
    }

    /**
     * Update term counts for all terms
     * 
     * @param string $taxonomy
     * @link https://plugins.trac.wordpress.org/browser/wp-media-library-categories/tags/2.0.2/index.php#L150
     * @since 7.8.3
     */
    public function update_count_callback( $taxonomy = 'asenha-media-category' ) {
        global $wpdb;

        // Select id & count from taxonomy
        $query = "SELECT term_taxonomy_id, MAX(total) AS total FROM ((
        SELECT tt.term_taxonomy_id, COUNT(*) AS total FROM $wpdb->term_relationships tr, $wpdb->term_taxonomy tt WHERE tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s GROUP BY tt.term_taxonomy_id
        ) UNION ALL (
        SELECT term_taxonomy_id, 0 AS total FROM $wpdb->term_taxonomy WHERE taxonomy = %s
        )) AS unioncount GROUP BY term_taxonomy_id";

        // Get the term count for each term
        $term_counts = $wpdb->get_results( $wpdb->prepare( $query, $taxonomy, $taxonomy ) );
        // vi( $term_counts );

        // Update all count values from taxonomy
        foreach ( $term_counts as $term_count ) {
            $wpdb->update( $wpdb->term_taxonomy, array( 'count' => $term_count->total ), array( 'term_taxonomy_id' => $term_count->term_taxonomy_id ) );
        }
    }
}