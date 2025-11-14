<?php

namespace ElementorAseRepeater\Controls;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use ElementorAseRepeater\LoopGrid\LoopGridProvider;
use ElementorAseRepeater\DynamicTags\RepeaterTagManager;
use Elementor\Controls_Manager;
use ElementorAseRepeater\Controls\AsenhaSwitcherControl;

class LoopGridControlsBase {
    protected $configurator;
    protected $provider;

    public function __construct( $configurator, $provider ) {
        $this->configurator = $configurator;
        $this->provider = $provider;
        $this->register_asenha_switcher_control();
    }

    protected function register_asenha_switcher_control() {
        add_action( 'elementor/controls/register', function( $controls_manager ) {
            $controls_manager->register( new AsenhaSwitcherControl() );
        });
    }

    public function register_query_controls( $element, $args ) {
        
        $element->add_control(
            'use_ase_repeater',
            [
                'label'         => __( 'Use ASE Repeater', 'admin-site-enhancements' ),
                'type'          => Controls_Manager::SWITCHER,
                'default'       => '',
                'label_on'      => __( 'Yes', 'admin-site-enhancements' ),
                'label_off'     => __( 'No', 'admin-site-enhancements' ),
                'description'   => __( 'To see your repeater content, select a preview post with repeater data in Elementor settings. Make sure it matches post type of source chosen below.', 'admin-site-enhancements' ),
            ]
        );

        $repeater_fields = $this->provider->get_ase_repeater_fields();
        
        if ( ! $element->get_controls( 'ase_repeater_field' ) ) {
            $element->add_control(
                'ase_repeater_field',
                [
                    'label'         => __( 'ASE Repeater Field', 'admin-site-enhancements' ),
                    'type'          => Controls_Manager::SELECT,
                    'options'       => $repeater_fields,
                    'condition'     => [
                        'use_ase_repeater' => 'yes',
                    ],
                ]
            );
        }

        $element->add_control(
            'query_current_post_only',
            [
                'label'         => __('Query Current Post Only', 'admin-site-enhancements'),
                'type'          => Controls_Manager::SWITCHER,
                'default'       => 'yes',
                'label_on'      => __('Yes', 'admin-site-enhancements'),
                'label_off'     => __('No', 'admin-site-enhancements'),
                'description'   => __('When enabled, only repeater fields from the current post will be used. When disabled, repeater fields from all posts of the selected source will be used.', 'admin-site-enhancements'),
                'condition'     => [
                    'use_ase_repeater' => 'yes',
                ],
            ]
        );
    }
   
}