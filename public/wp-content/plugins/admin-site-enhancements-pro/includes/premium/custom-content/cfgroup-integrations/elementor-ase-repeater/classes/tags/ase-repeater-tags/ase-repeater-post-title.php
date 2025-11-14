<?php

namespace ElementorAseRepeater\DynamicTags;

use ElementorAseRepeater\LoopGrid\LoopGridProvider;



if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AseRepeaterPostTitle extends \Elementor\Core\DynamicTags\Data_Tag
{
    public function get_name() {
        return 'ase-repeater-original-title';
    }

    public function get_title() {
        return __( 'ASE Repeater Original Post Title', 'admin-site-enhancements' );
    }

    public function get_group() {
        return 'ase';
    }

    public function get_categories() {
        return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];
    }

    public function get_value( array $options = [] ) {
        $post_id = get_the_ID();
        $loop_grid_provider = LoopGridProvider::instance();
        return $loop_grid_provider->get_original_post_title( $post_id );
    }
}