<?php

namespace ElementorAseRepeater\DynamicTags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AseRepeaterGallery extends AseRepeaterTagBase {
    public function __construct( $data = [] ) {
        parent::__construct( $data );
    }

    public function get_name() {
        return 'ase-repeater-gallery';
    }

    public function get_title() {
        return __('ASE Repeater Gallery', 'admin-site-enhancements');
    }

    public function get_group() {
        return 'ase';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY];
    }

    public function get_supported_fields() {
        return array( 'gallery' );
    }

    public function get_value( array $options = [] ) {
        $images = array();
        $repeater_field = $this->get_settings( 'repeater_field' );
  
        if ( empty( $repeater_field ) ) {
            return $images;
        }
    
        $gallery_images = $this->get_repeater_value( $repeater_field, 'raw' );
        $gallery_images = explode( ',', $gallery_images );
    
        if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
            foreach ( $gallery_images as $attachment_id ) {
                $images[] = array( 'id' => $attachment_id );
            }
        }
    
        return $images;
    }
}