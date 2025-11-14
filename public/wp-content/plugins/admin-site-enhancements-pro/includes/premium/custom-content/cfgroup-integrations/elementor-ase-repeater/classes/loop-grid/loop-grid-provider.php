<?php

namespace ElementorAseRepeater\LoopGrid;

use ElementorAseRepeater\Controls\LoopGridControlsBase;
use ElementorAseRepeater\Configurator;

class LoopGridProvider {
    protected static $instance = null;

    protected $configurator;

    protected $controls;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct() {
        $this->configurator = \ElementorAseRepeater\Configurator::instance();
        $this->init_controls();
        $this->register_controls();
        // Add filter for virtual post classes
        add_filter( 'post_class', [ $this, 'add_virtual_post_classes' ], 10, 3 );
        // Add filter to clean up WHERE clause
        add_filter( 'posts_where', [ $this, 'clean_posts_where' ], 10, 2 );
    }

    protected function init_controls() {
        $this->controls = new \ElementorAseRepeater\Controls\LoopGridControlsBase( $this->configurator, $this );
    }

    protected function register_controls() {
        add_action(
            'elementor/element/loop-grid/section_query/after_section_start',
            [ $this->controls, 'register_query_controls' ],
            10,
            2
        );
    }

    // Create an array that consists of repeater field row data
    // $posts contains an array of posts data relevant to the repeater field in question, e.g. movie_scenes repeater will contain movie posts
    public function add_virtual_posts( $posts, $query ) {
        // Only run this filter for our specific post type
        if ( ! isset( $query->query_vars['asenha_virtual_posts'] ) || $query->query_vars['asenha_virtual_posts'] !== true ) {
            return $posts;
        }

        $repeater_field = $query->get( 'ase_repeater_field' ); // e.g. 'movie_scenes'

        if ( ! $repeater_field ) {
            return $posts;
        }

        $virtual_posts = [];

        foreach ( $posts as $post ) {
            $repeater_data = get_cf( $repeater_field, 'raw', $post->ID );

            if ( ! $repeater_data || is_null( $repeater_data ) || ! is_array( $repeater_data ) ) {
                continue;
            }

            foreach ( $repeater_data as $index => $row ) {
                $virtual_post = new \stdClass();
                $virtual_post->ID = -1 * ($post->ID . $this->configurator::VIRTUAL_POST_ID_SEPARATOR . $index);
                $virtual_post->post_parent = $post->ID;
                $virtual_post->post_title = $post->post_title . ' - ' . $repeater_field . ' ' . ($index + 1);
                $virtual_post->post_status = 'publish';
                $virtual_post->post_type = $post->post_type;
                // Use parent's post type instead of creating new one
                $virtual_post->filter = 'raw';
                // Add our custom data
                $virtual_post->ase_repeater_data = $row;
                $virtual_post->asenha_loop_index = $index;
                $virtual_posts[] = $virtual_post;
            }
        }

        return $virtual_posts;
    }

    public function filter_elementor_query_args( $query_args, $widget ) {
        $settings = $widget->get_settings();
        if ( isset( $settings['use_ase_repeater'] ) && $settings['use_ase_repeater'] === 'yes' ) {
            // Add our virtual posts flags
            $query_args['asenha_virtual_posts'] = true;
            $query_args['ase_repeater_field'] = $settings['ase_repeater_field'];
            $query_args['query_current_post_only'] = $settings['query_current_post_only'];
            // Only modify query if we want current post
            if ( $settings['query_current_post_only'] === 'yes' ) {
                $query_args['post__in'] = [ get_the_ID() ];
            }
        }
        return $query_args;
    }

    public function get_ase_repeater_fields() {
        $repeater_fields = [];

        try {
            $repeater_fields_raw = find_cf( array( 'field_type' => 'repeater' ) );
            if ( ! empty( $repeater_fields_raw ) && is_array( $repeater_fields_raw ) ) {
                foreach ( $repeater_fields_raw as $field_name => $field_info ) {
                    $repeater_fields[$field_name] = $field_info['label'] . ' (' . $field_name . ')';
                }
            }
        } catch ( \Exception $e ) {
            // Silently handle any exceptions
        }

        return $repeater_fields;
    }

    // private function process_fields( $fields, &$result, $parent = '' ) {
    //     foreach ( $fields as $field ) {
    //         if ( $field['type'] === 'repeater' ) {
    //             $key = ( $parent ? $parent . '_' . $field['name'] : $field['name'] );
    //             $result[$key] = $field['label'];
    //         } elseif ( $field['type'] === 'group' && !empty( $field['sub_fields'] ) ) {
    //             $this->process_fields( $field['sub_fields'], $result, $field['name'] );
    //         }
    //     }
    // }

    public function get_original_post_title( $post_id ) {
        if ( $post_id < 0 ) {
            // This is a virtual post
            $original_post_id = abs( $post_id );
            $original_post_id = explode( $this->configurator::VIRTUAL_POST_ID_SEPARATOR, $original_post_id )[0];
            $post = get_post( $original_post_id );
        } else {
            $post = get_post( $post_id );
        }
        if ( !$post ) {
            return '';
        }
        return get_the_title( $post->ID );
    }

    public function add_virtual_post_classes( $classes, $class, $post_id ) {
        if ( is_string( $post_id ) && strpos( $post_id, '-' ) === 0 ) {
            // Add standard WordPress classes
            $classes[] = 'post-' . abs( $post_id );
            $classes[] = 'type-repeater-field-post';
            $classes[] = 'status-publish';
            $classes[] = 'hentry';
        }
        return $classes;
    }

    public function clean_posts_where( $where, $query ) {
        // Only clean WHERE clause if we're not in current post only mode
        if ( ! isset( $query->query_vars['query_current_post_only'] ) || $query->query_vars['query_current_post_only'] !== 'yes' ) {
            // Remove any NOT IN clauses
            $where = preg_replace( '/AND\\s+wp_posts\\.ID\\s+NOT\\s+IN\\s*\\([^)]+\\)/', '', $where );
        }
        return $where;
    }

}
