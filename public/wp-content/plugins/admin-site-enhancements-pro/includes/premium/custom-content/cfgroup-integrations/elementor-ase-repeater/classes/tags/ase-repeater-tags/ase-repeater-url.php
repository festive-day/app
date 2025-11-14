<?php

namespace ElementorAseRepeater\DynamicTags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AseRepeaterUrl extends AseRepeaterTagBase {
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    public function get_name() {
        return 'ase-repeater-url';
    }

    public function get_title() {
        return __('ASE Repeater URL', 'admin-site-enhancements');
    }

    public function get_group() {
        return 'ase';
    }

    public function get_categories() {
        return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
    }

    public function get_supported_fields() {
        return array( 
            'text', 
            'hyperlink',
            'file',
            'relationship',
            'term',
            'user',
        );
    }

    public function get_value( array $options = [] ) {
        $field_name = $this->get_settings('repeater_field');

        if ( ! $field_name ) {
            return '';
        }
        
        $field = find_cf( array( 'field_name' => $field_name ) );
        $field_type = $field[$field_name]['type'];

        $value = $this->get_repeater_value( $field_name, 'raw' );

        if ( ! $value ) {
            return '';
        }

        if ( is_array( $value ) ) {
            if ( isset( $value['url'] ) ) {
                // Hyperlink field
                return $value['url'];
            } 
            elseif ( isset( $value[0] ) && ! empty( $value[0] ) ) {
                // Relationship or Term fields, returning an array[0] of comma-separated post or term IDs
                $values = explode( ',', $value[0] );
                // Force usage of the first ID. We assume relationship / term will be limited to a single selection, but this takes care of cases where multiple values are selected.
                $first_id = intval( $values[0] );

                if ( 'relationship' == $field_type && get_the_permalink( $first_id ) ) {
                    return get_the_permalink( $first_id );                
                } elseif ( 'term' == $field_type && is_string( get_term_link( $first_id ) ) ) {
                    return get_term_link( $first_id );
                } elseif ( 'user' == $field_type && is_string( get_author_posts_url( $first_id ) ) ) {
                    return get_author_posts_url( $first_id );
                }
            }
        } elseif ( is_numeric($value) ) {
            return wp_get_attachment_url( $value );
        } elseif ( filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
            return 'mailto:' . $value;
        } elseif ( filter_var( $value, FILTER_VALIDATE_URL) ) {
            return $value;
        } else {
            return '/';
        }

        return $value;
    }
}