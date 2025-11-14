<?php

namespace ElementorAseRepeater;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ElementorAseRepeater\Controls\DynamicTagControls;
use ElementorAseRepeater\LoopGrid\LoopGridProvider;
use ElementorAseRepeater\Data\RestHandler;
use ElementorAseRepeater\DynamicTags\RepeaterTagManager;
use ElementorAseRepeater\Controls\RepeaterFieldSelector;

class Configurator {
    private static $_instance = null;

    private $repeater_data = null;

    private $processing_complete = false;

    private $in_widgets_context = false;

    private $disable_all_processing = false;

    private static $all_processing_disabled = false;

    private static $is_initialized = false;

    private $repeater_provider = null;

    private $rest;

    const VIRTUAL_POST_ID_SEPARATOR = 9990999;

    private function __construct() {
    }

    public static function instance() {
        if ( self::$_instance === null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    // Initialization and configurator setup
    public function initialize() {
        // Check if already initialized
        if ( self::$is_initialized ) {
            return;
        }

        $this->load_dependencies();

        // Check if requirements are met
        if ( !$this->check_requirements() ) {
            return;
        }

        $this->repeater_provider = LoopGridProvider::instance();
        $this->register_hooks();

        add_filter( 'the_posts', array( $this->repeater_provider, 'add_virtual_posts' ), 10, 2 );

        $this->rest = new RestHandler();
        add_action( 'rest_api_init', [$this->rest, 'register_rest_routes'] );
        do_action( 'elementor_ase_repeater_initialized' );
        self::$is_initialized = true;
    }

    private function load_dependencies() {
        $this->load_elementor_dependencies();
    }

    private function load_elementor_dependencies() {
        require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/controls/dynamic-tag-controls.php';
        require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/controls/asenha-switcher-control.php';
        require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/controls/loop-grid-controls-base.php';
        require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/controls/repeater-field-selector.php';
        require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/loop-grid/loop-grid-provider.php';
        require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/tags/repeater-tag-manager.php';
        require_once ELEMENTOR_ASE_REPEATER_PATH . 'classes/data/rest-handler.php';
    }

    private function check_requirements() {
        if ( ! did_action( 'elementor/loaded' ) ) {
            return false;
        }

        if ( !version_compare( ELEMENTOR_VERSION, ELEMENTOR_ASE_REPEATER_MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            return false;
        }

        if ( version_compare( PHP_VERSION, ELEMENTOR_ASE_REPEATER_MINIMUM_PHP_VERSION, '<' ) ) {
            return false;
        }

        return true;
    }

    // State management
    public function set_widgets_context( $value ) {
        $this->in_widgets_context = $value;
    }

    public function is_in_widgets_context() {
        return $this->in_widgets_context;
    }

    public function set_processing_complete( $value ) {
        $this->processing_complete = $value;
    }

    public function is_processing_complete() {
        return $this->processing_complete;
    }

    public function disable_all_processing() {
        self::$all_processing_disabled = true;
    }

    public function is_all_processing_disabled() {
        return self::$all_processing_disabled;
    }

    // Hook registration
    private function register_hooks() {
        $this->register_elementor_hooks();
    }

    private function register_elementor_hooks() {
        $this->register_dynamic_tags_hooks();
        $this->register_repeater_provider_hooks();

        add_filter( 'elementor/query/query_args', [$this->repeater_provider, 'filter_elementor_query_args'], 10, 2 );
        add_action( 'elementor/dynamic_tags/before_render', [ $this, 'refresh_dynamic_tag_controls' ] );
    }

    private function register_dynamic_tags_hooks() {
        add_action( 'elementor/editor/before_enqueue_scripts', [ $this->get_dynamic_tags(), 'load_tag_classes' ] );
        add_action( 'wp_ajax_elementor_ajax', [ $this, 'load_tag_classes_before_ajax' ], 5 );

        if ( ! $this->is_in_widgets_context() ) {
            add_action( 'elementor/dynamic_tags/register', [ DynamicTagControls::instance(), 'register_tags' ] );
        }
    }

    private function register_repeater_provider_hooks() {
        $provider = LoopGridProvider::instance();
    }

    // ASE Repeater specific functionality
    public function load_tag_classes_before_ajax() {
        $this->get_dynamic_tags()->load_tag_classes();
    }

    public function get_dynamic_tags() {
        return RepeaterTagManager::instance();
    }

    public function refresh_dynamic_tag_controls( $selected_repeater = null ) {
        $controls = DynamicTagControls::instance();
        $dynamic_tags = $this->get_dynamic_tags();
        $tag_classes = $dynamic_tags->get_tag_classes_names();

        foreach ( $tag_classes as $tag_class ) {
            if ( is_subclass_of( $tag_class, 'AseRepeaterTagBase' ) ) {
                $tag = new $tag_class();
                $controls->register_controls( $tag, $selected_repeater );
            }
        }
    }

    // Utility functions
    public function is_edit_mode( $post_id ) {
        return \Elementor\Plugin::$instance->editor->is_edit_mode( $post_id );
    }

    /**
     * Updates the dynamic tag controls based on the current state and user selections.
     * 
     * This method is called in response to AJAX requests or other dynamic events in the Elementor editor.
     * It provides updated control options for ASE Repeater dynamic tags, particularly when the user
     * changes the selected repeater field or when other relevant changes occur.
     * 
     * Note: This method works in conjunction with DynamicTagControls::register_controls().
     * While register_controls() sets up the initial control structure, this method provides
     * the updated data to populate that structure based on the current state.
     * 
     * @param int $post_id The ID of the post being edited.
     * @param string $selected_repeater The currently selected repeater field.
     * @param array $tags An array of tag configurations to update.
     * @param mixed $request Optional request data.
     * @return array Updated tag controls data.
     */
    public function get_updated_dynamic_tag_controls( $post_id, $selected_repeater, $tags, $request = null ) {
        $is_edit_mode = $this->is_edit_mode( $post_id );

        // If no repeater is selected, try to get the saved one
        if ( empty( $selected_repeater ) ) {
            $settings = new RepeaterFieldSelector();
            $selected_repeater = $settings->get_saved_repeater_field( $post_id );
        }

        $updated_tags = [];
        $controls = DynamicTagControls::instance();

        foreach ( $tags as $tag_name => $tag_config ) {
            $tag_class = $this->get_tag_class_from_name( $tag_name );
            if ( $tag_class ) {
                $tag_instance = new $tag_class();
                $supported_fields = $tag_instance->get_supported_fields();
                $groups = $controls->get_control_options( $supported_fields, $selected_repeater, $tag_instance, $post_id );
                $updated_tags[$tag_name] = [
                    'controls' => [
                        'repeater_field' => [
                            'type'    => \Elementor\Controls_Manager::SELECT,
                            'label'   => __( 'Choose a field', 'admin-site-enhancements' ),
                            'default' => $selected_repeater,
                            'groups'  => $groups,
                            'global'  => [
                                'active' => false,
                            ],
                        ],
                    ],
                ];
            }
        }

        return $updated_tags;
    }

    private function get_tag_class_from_name( $tag_name ) {
        $tag_classes = RepeaterTagManager::get_tag_classes_names();
        $class_map = array_combine( array_map( function ( $class ) {
            return 'ase-repeater-' . strtolower( str_replace( 'AseRepeater', '', $class ) );
        }, $tag_classes ), $tag_classes );

        return ( isset( $class_map[$tag_name] ) ? 'ElementorAseRepeater\\DynamicTags\\' . $class_map[$tag_name] : null );
    }
}