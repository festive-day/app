<?php

namespace ElementorAseRepeater\DynamicTags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AseRepeaterFile extends AseRepeaterTagBase {
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    public function get_name() {
        return 'ase-repeater-file';
    }

    public function get_title() {
        return __('ASE Repeater File', 'admin-site-enhancements');
    }

    public function get_group() {
        return 'ase';
    }

    public function get_categories() {
        return array(
            \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY
        );
    }

    public function get_supported_fields() {
        return array( 'file' );
    }

    public function get_value( array $options = [] ) {
        $field_name = $this->get_settings( 'repeater_field' );

        if ( empty( $field_name ) ) {
            return '';
        }

        $value = $this->get_repeater_value( $field_name, 'raw' );

        if ( ! $value ) {
            return '';
        }

        // For MEDIA category (used in video widget, etc.)
        $url = wp_get_attachment_url( intval( $value ) ) ?: '';

        if ( in_array( \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY, $this->get_categories() ) ) {
            return array(
                'id' => $value,
                'url' => $url,
            );
        }

        // For URL category (used in links, buttons, etc.)
        if ( in_array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY, $this->get_categories() ) ) {
            return wp_get_attachment_url( intval( $value ) ) ?: '';
        }
    }
}