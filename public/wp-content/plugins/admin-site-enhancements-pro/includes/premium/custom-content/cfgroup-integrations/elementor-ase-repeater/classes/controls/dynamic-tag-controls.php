<?php
namespace ElementorAseRepeater\Controls;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use ElementorAseRepeater\Configurator;
use ElementorAseRepeater\Data\RepeaterDataTrait;
use Elementor\Controls_Manager;

require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/data/repeater-data-trait.php';

class DynamicTagControls {
    use RepeaterDataTrait;
    
    private static $instance = null;
    private $configurator;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->configurator = Configurator::instance();
    }


    public function register_tags( $dynamic_tags ) {
        try {
            if ( $this->configurator->is_in_widgets_context() || $this->configurator->is_all_processing_disabled() ) {
                return; // Silently return if in widgets context or all processing is disabled
            }
        
            $tag_classes = \ElementorAseRepeater\DynamicTags\RepeaterTagManager::get_tag_classes_names();
            
            foreach ( $tag_classes as $class ) {
                $full_class_name = 'ElementorAseRepeater\\DynamicTags\\' . $class;
                if ( class_exists( $full_class_name ) ) {
                    $tag = new $full_class_name();

                    if ( $tag->get_name() !== 'ase-repeater-original-title' ) {
                        $this->register_controls( $tag );
                    }

                    $dynamic_tags->register( $tag );
                }
                // Silently continue if the class doesn't exist
            }
        } catch ( \Exception $e ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                // For admin users only
                if ( current_user_can( 'manage_options' ) ) {
                    add_action( 'admin_notices', function() use ( $e ) {
                        printf(
                            '<div class="notice notice-error"><p>%s</p></div>',
                            esc_html(sprintf(
                                /* translators: %s: Error message */
                                __( 'Elementor ASE Repeater: Error registering tags - %s', 'admin-site-enhancements' ),
                                $e->getMessage()
                            ))
                        );
                    });
                }
            }
        }
    }
    
        /**
     * Registers the initial control structure for ASE Repeater dynamic tags.
     * 
     * This method is called during the initial registration of dynamic tags with Elementor.
     * It sets up the basic structure of the controls that will appear in the Elementor editor
     * for each ASE Repeater dynamic tag.
     * 
     * Note: This method works in conjunction with Configurator::get_updated_dynamic_tag_controls().
     * While this method sets up the initial structure, get_updated_dynamic_tag_controls() 
     * provides dynamic updates to the control options based on user interactions.
     * 
     * @param \Elementor\Core\DynamicTags\Tag $tag The tag instance.
     * @param string|null $selected_repeater The initially selected repeater field, if any.
     */
    public function register_controls( $tag, $selected_repeater = null ) {
        try {
            if ($this->configurator->is_in_widgets_context()) {
                return;
            }
            
            $supported_fields = method_exists( $tag, 'get_supported_fields' ) ? $tag->get_supported_fields() : [];
            $control_options = $this->get_control_options( $supported_fields, $selected_repeater, $tag );

            if ( empty( $control_options ) ) {
                return;
            }

            $tag->start_controls_section(
                'asenha_section',
                [
                    'label' => __( 'Available Fields', 'admin-site-enhancements' ),
                ]
            );

            $tag->add_control(
                'repeater_field',
                [
                    'label'   => esc_html__( 'Choose a field', 'admin-site-enhancements' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'groups'  => $control_options,
                    'classes' => 'asenha-premium-fields',
                    'frontend_available' => true,
                ]
            );

            $tag->end_controls_section();
        } catch ( \Exception $e ) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // For admin users only
                if ( current_user_can( 'manage_options' ) ) {
                    add_action( 'admin_notices', function() use ( $e ) {
                        printf(
                            '<div class="notice notice-error"><p>%s</p></div>',
                            esc_html(sprintf(
                                /* translators: %s: Error message */
                                __( 'Elementor ASE Repeater: Error registering controls - %s', 'admin-site-enhancements' ),
                                $e->getMessage()
                            ))
                        );
                    });
                }
            }
        }
    }

    public function get_control_options( $supported_fields, $selected_repeater = null, $tag = null, $post_id = null ) {        
        if ( $this->configurator->is_in_widgets_context() ) {
            return [];
        }

        // If no repeater is selected, try to get the saved one
        if ( empty( $selected_repeater ) ) {
            $settings = RepeaterFieldSelector::instance();
            $selected_repeater = $settings->get_saved_repeater_field( $post_id );
        }

        if ( ! empty( $selected_repeater ) ) {
            $repeater_fields = [];

            $field = find_cf( array( 'field_name' => $selected_repeater ) );
            
            if ( 'repeater' === $field[$selected_repeater]['type'] ) {
                $options = [];
                $all_supported_fields = $supported_fields;

                if ( $tag !== null && method_exists( $tag, 'get_supported_fields' ) ) {
                    $all_supported_fields = $tag->get_supported_fields();
                }
                
                $sub_fields = find_cf( array( 'parent_id' => $field[$selected_repeater]['id'] ) );

                foreach ( $sub_fields as $sub_field_name => $sub_field_info ) {
                    $label = $sub_field_info['label'];
                    $type = $sub_field_info['type'];
                    $name = $sub_field_info['name'];
                    
                    // Always include 'hyperlink' type, regardless of the tag
                    if ( in_array( $type, $all_supported_fields ) || $type === 'hyperlink' ) {
                        // if ( in_array( $type, ['text', 'textarea', 'file', 'hyperlink'] ) ) {
                            $options[$name] = $label . ' (' . $name . ') | ' . $type;
                        // } 
                    } 
                }

                if ( ! empty( $options ) ) {
                    $repeater_fields[] = [
                        'label' => $field[$selected_repeater]['label'] . ' (' . $selected_repeater . ')',
                        'options' => $options,
                    ];
                }
            }
            
            return $repeater_fields;            
        } else {
            return [
                [
                    'label' => __( 'Select Repeater Field in Page Settings', 'admin-site-enhancements' ),
                    'options' => [ '' => __( 'No Repeater Field Selected', 'admin-site-enhancements' ) ],
                ]
            ];
        }
    }
    
}