<?php
// Forked from Dynamic Elementor ACF Repeater v1.0.0 (https://wordpress.org/plugins/dynamic-elementor-acf-repeater/) by Calculabs (https://calculabs.com)

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ElementorAseRepeater\Configurator;
use ElementorAseRepeater\Controls\RepeaterFieldSelector;

define( 'ELEMENTOR_ASE_REPEATER_VERSION', '2.0.0' );
define( 'ELEMENTOR_ASE_REPEATER_MINIMUM_ELEMENTOR_VERSION', '3.5.0' );
define( 'ELEMENTOR_ASE_REPEATER_MINIMUM_PHP_VERSION', '7.4' );
define( 'ELEMENTOR_ASE_REPEATER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ELEMENTOR_ASE_REPEATER_FILE', __FILE__ );

require_once __DIR__ . '/classes/configurator.php';

class Elementor_ASE_Repeater {
    private static $_instance = null;

    private $configurator;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function plugin_name() {
        return 'Elementor ASE Repeater';
    }

    private function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
        $this->configurator = Configurator::instance();
    }

    public function init_plugin() {
        if ( $this->check_requirements() ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999 );
            add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999 );
            add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999 );
            add_action( 'elementor_pro/init', [ $this, 'init_elementor_dependent_features' ], 20 );
        }
    }

    public function enqueue_scripts() {
        $is_edit_mode = Elementor\Plugin::$instance->editor->is_edit_mode();
        $is_preview_mode = Elementor\Plugin::$instance->preview->is_preview_mode();

        wp_enqueue_style(
            'asenha-elementor-ase-repeater',
            plugins_url( 'assets/css/elementor-ase-repeater.css', __FILE__ ),
            [],
            ELEMENTOR_ASE_REPEATER_VERSION
        );

        if ( $is_edit_mode || $is_preview_mode ) {
            wp_enqueue_script(
                'asenha-control-updater',
                plugins_url( 'assets/js/control-updater.js', __FILE__ ),
                [ 'elementor-editor', 'jquery' ],
                ELEMENTOR_ASE_REPEATER_VERSION,
                true
            );
            wp_enqueue_script(
                'asenha-tag-change-detector',
                plugins_url( 'assets/js/tag-change-detector.js', __FILE__ ),
                [ 'jquery', 'elementor-editor' ],
                ELEMENTOR_ASE_REPEATER_VERSION,
                true
            );
        }
    }

    private function check_requirements() {
        if ( ! class_exists( 'Custom_Field_Group' ) || ! did_action( 'elementor/loaded' ) ) {
            return false;
        }

        if ( ! version_compare( ELEMENTOR_VERSION, ELEMENTOR_ASE_REPEATER_MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            return false;
        }

        if ( version_compare( PHP_VERSION, ELEMENTOR_ASE_REPEATER_MINIMUM_PHP_VERSION, '<' ) ) {
            return false;
        }

        return true;
    }

    public function init_elementor_dependent_features() {
        if ( did_action( 'elementor/loaded' ) ) {
            $this->configurator->initialize();
            RepeaterFieldSelector::instance();
        }
    }

}

// Initialize the plugin
Elementor_ASE_Repeater::instance();