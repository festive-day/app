<?php

namespace ElementorAseRepeater\DynamicTags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AseRepeaterText extends AseRepeaterTagBase {
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    public function get_name() {
        return 'ase-repeater-text';
    }

    public function get_title() {
        return __('ASE Repeater Text', 'admin-site-enhancements');
    }

    public function get_group() {
        return 'ase';
    }

    public function get_categories() {
        return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
    }

    public function get_supported_fields() {
        return array( 
            'text', 
            'textarea',
            'wysiwyg',
            'color',
            'date',
            'time',
            'datetime',
            'hyperlink',
            'number',
            'true_false',
            'radio',
            'select',
            'checkbox',
            'file',
            'gallery',
            'relationship',
            'term',
            'user',
        );
    }

    public function get_value( array $options = [] ) {
        $field_name = $this->get_settings( 'repeater_field' );

        if ( empty( $field_name ) ) {
            return '';
        }

        $value = $this->get_repeater_value( $field_name, 'display' );

        if ( $value === null ) {
            return '';
        }

        $field = find_cf( array( 'field_name' => $field_name ) );
        $field_type = $field[$field_name]['type'];

        if ( $field_type === null ) {
            return '';
        }

        if ( ! in_array( $field_type, $this->get_supported_fields() ) ) {
            return '';
        }

        return (string) $value;
    }
}