<?php

namespace ASENHA\Classes;

/**
 * Class for Show Custom Taxonomy Filters module
 *
 * @since 6.9.5
 */
class Show_Custom_Taxonomy_Filters {
        
    // Excluded taxonomies
    private $inapplicable_taxonomies = array( 
            'category', // Post Categories
            'product_cat', // WooCommerce Product Categories
            'asenha_code_snippet_category', // ASE Code Snippets Categories
            'asenha-media-category', // ASE Media Categories
        );

    /**
     * Show custom (hierarchical) taxonomy filter(s) for all post types.
     *
     * @since 1.0.0
     */
    public function show_custom_taxonomy_filters( $post_type ) {
        $object_taxonomies = get_object_taxonomies( $post_type, 'objects' );
        
        array_walk( $object_taxonomies, [ $this, 'output_taxonomy_filter' ] );
    }

    /**
     * Output filter on the post type's list table for a taxonomy
     *
     * @since 1.0.0
     */
    public function output_taxonomy_filter( $taxonomy ) {

        // Show filter if taxonomy is hierarchical
        if ( true === $taxonomy->hierarchical && ! in_array( $taxonomy->name, $this->inapplicable_taxonomies ) ) {
            $this->render_additional_filter( $taxonomy );
        }

        if ( bwasenha_fs()->can_use_premium_code__premium_only() ) {
            $options = get_option( ASENHA_SLUG_U );
            $show_custom_taxonomy_filters_non_hierarchical = isset( $options['show_custom_taxonomy_filters_non_hierarchical'] ) ? $options['show_custom_taxonomy_filters_non_hierarchical'] : false;            

            if ( $show_custom_taxonomy_filters_non_hierarchical ) {
                // Show filter if taxonomy is non-hierarchical
                if ( false === $taxonomy->hierarchical && ! in_array( $taxonomy->name, $this->inapplicable_taxonomies ) ) {
                    $this->render_additional_filter( $taxonomy );
                }                
            }
        }

    }
    
    /**
     * Render additional filter
     * 
     * @since 6.9.7
     */
    public function render_additional_filter( $taxonomy ) {
        $show_option_all_label = sprintf( 'All %s', ucwords( $taxonomy->label ) );

        if ( property_exists( $taxonomy, 'labels' ) ) {
            $taxonomy_labels = $taxonomy->labels;
            if ( property_exists( $taxonomy_labels, 'all_items' ) && ! empty( $taxonomy_labels->all_items ) ) {
                $show_option_all_label = $taxonomy->labels->all_items;
            }
        }
        
        wp_dropdown_categories( array(
            'show_option_all'   => $show_option_all_label,
            'orderby'           => 'name',
            'order'             => 'ASC',
            'hide_empty'        => false,
            'hide_if_empty'     => true,
            'selected'          => sanitize_text_field( ( isset( $_GET[$taxonomy->query_var] ) && ! empty( $_GET[$taxonomy->query_var] ) ) ? sanitize_text_field( $_GET[$taxonomy->query_var] ) : '' ), 
            'hierarchical'      => true,
            'name'              => $taxonomy->query_var,
            'taxonomy'          => $taxonomy->name,
            'value_field'       => 'slug',
        ) );
        
    }
    
    /**
     * Remove malformed string inside the $where clause that is breaking search for posts.
     * This malformed string appears when filter(s) for non-hierarchical taxonomies are shown/enabled.
     * 
     * @since 7.8.7
     */
    public function filter_where_clause__premium_only( $where, $query ) {
        if ( is_admin() ) {
            // do_action( 'inspect', [ 'where', $where ] );        
            $where = str_replace( ' AND ( ' . PHP_EOL . '  0 = 1' . PHP_EOL . ')', '', $where );
            // do_action( 'inspect', [ 'where', $where ] );
        }
        
        return $where;
    }
    
}