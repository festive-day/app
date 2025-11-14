<?php

namespace ElementorAseRepeater\DynamicTags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AseRepeaterImage extends AseRepeaterTagBase {
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    public function get_name() {
        return 'ase-repeater-image';
    }

    public function get_title() {
        return __('ASE Repeater Image', 'admin-site-enhancements');
    }

    public function get_group() {
        return 'ase';
    }

    public function get_categories() {
        return array(
            \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
        );
    }

    public function get_supported_fields() {
        return array( 
            'file',
        );
    }

    public function render() {
        $value = $this->get_value();
        if ( ! empty($value['url'] ) ) {
            echo esc_url( $value['url'] );
        }
    }

    public function get_value( array $options = [] ) {
        try {
            if ( $this->configurator->is_in_widgets_context() || $this->configurator->is_all_processing_disabled() ) {
                return ['id' => null, 'url' => ''];
            }

            $field_name = $this->get_settings( 'repeater_field' );
        
            if ( empty( $field_name ) ) {
                return [ 'id' => null, 'url' => '' ];
            }
        
            $value = $this->get_repeater_value( $field_name, 'raw' );
        
            if ( $value === null ) {
                return [ 'id' => null, 'url' => '' ];
            }
        
            $image_data = [ 'id' => null, 'url' => '' ];

            if ( is_array( $value ) && isset( $value['ID'] ) && isset( $value['url'] ) ) {
                $image_data['id'] = $value['ID'];
                $image_data['url'] = $value['url'];
            } elseif ( is_numeric( $value ) ) {
                $image_data['id'] = $value;
                $image_data['url'] = wp_get_attachment_url( $value );
            } elseif ( is_string( $value ) && filter_var( $value, FILTER_VALIDATE_URL ) ) {
                $image_data['id'] = attachment_url_to_postid( $value );
                $image_data['url'] = $value;
            }
        
            return $image_data;
        } catch ( \Exception $e ) {
            return ['id' => null, 'url' => ''];
        }
    }
}