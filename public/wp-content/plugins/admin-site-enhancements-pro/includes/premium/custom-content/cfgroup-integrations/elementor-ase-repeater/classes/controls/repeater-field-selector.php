<?php
namespace ElementorAseRepeater\Controls;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use ElementorAseRepeater\Configurator;
use Elementor\Controls_Manager;
use ElementorPro\Modules\LoopBuilder\Documents\Loop;

/**
 * This is for Loop Item >> Settings
 */
class RepeaterFieldSelector {
    private static $instance = null;
    private $configurator;
    const SETTINGS_KEY = 'RepeaterFieldSelector';

    private function __construct() {
        $this->configurator = Configurator::instance();
        add_action( 'elementor/documents/register_controls', [ $this, 'register_controls' ] );
        add_action( 'elementor/document/before_save', [ $this, 'save_settings' ], 10, 2 );
    }

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function register_controls($document) {
        if ( ! $document instanceof Loop || ! $document::get_property( 'has_elements' ) ) {
            return;
        }

        $document->start_controls_section(
            'asenha_loop_settings_section',
            [
                'label'     => __( 'ASE Repeater Loop Settings', 'admin-site-enhancements' ),
                'tab'       => Controls_Manager::TAB_SETTINGS,
            ]
        );

        $repeater_fields = $this->get_repeater_fields();
        $saved_repeater_field = $this->get_saved_repeater_field( $document->get_main_id() );

        $document->add_control(
            'asenha_loop_repeater_field',
            [
                'label'         => __( 'ASE Repeater Field for Loop', 'admin-site-enhancements' ),
                'type'          => Controls_Manager::SELECT,
                'options'       => $repeater_fields,
                'default'       => $saved_repeater_field ?: '',
                'description'   => __( 'Select an ASE repeater field to use in this loop template.', 'admin-site-enhancements' ),
            ]
        );

        $document->end_controls_section();
    }

    public function save_settings( $document, $data ) {
        if ( isset( $data['settings']['asenha_loop_repeater_field'] ) ) {
            $selected_field = $data['settings']['asenha_loop_repeater_field'];
            update_post_meta( $document->get_main_id(), 'asenha_loop_repeater_field', $selected_field );
        }
    }
    
    public function get_saved_repeater_field( $document_id ) {
        $field = get_post_meta( $document_id, 'asenha_loop_repeater_field', true );
        return $field;
    }

    private function get_repeater_fields() {
        $repeater_fields = [ '' => __( 'Select...', 'admin-site-enhancements' ) ];

        $fields = find_cf(); // Get all fields in the site

        foreach ( $fields as $field ) {
            if ( 'repeater' === $field['type'] ) {
                $repeater_fields[$field['name']] = $field['label'] . ' (' . $field['name'] . ')';
            }
        }

        return $repeater_fields;
    }
}