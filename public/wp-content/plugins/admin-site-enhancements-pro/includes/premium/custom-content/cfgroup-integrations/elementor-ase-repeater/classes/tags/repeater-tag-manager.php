<?php

namespace ElementorAseRepeater\DynamicTags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ElementorAseRepeater\Configurator;
use ElementorAseRepeater\Controls\DynamicTagControls;

class RepeaterTagManager {
    private static $instance = null;

    private $controls;

    private $configurator;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->configurator = Configurator::instance();
        $this->controls = DynamicTagControls::instance();
        $this->load_tag_classes();
    }

    public function load_tag_classes() {
        require_once plugin_dir_path( __FILE__ ) . 'tag-base-trait.php';

        $tag_classes = self::get_tag_classes_names();
        $class_filename_converter = array(
            'AseRepeaterPostTitle'      => 'ase-repeater-post-title',
            'AseRepeaterText'           => 'ase-repeater-text',
            'AseRepeaterImage'          => 'ase-repeater-image',
            'AseRepeaterUrl'            => 'ase-repeater-url',
            'AseRepeaterFile'           => 'ase-repeater-file',
            'AseRepeaterGallery'        => 'ase-repeater-gallery',
        );

        foreach ( $tag_classes as $class ) {
            $file_path = plugin_dir_path( __FILE__ ) . 'ase-repeater-tags/' . $class_filename_converter[$class] . '.php';

            if ( file_exists( $file_path ) ) {
                require_once $file_path;
                $full_class_name = 'ElementorAseRepeater\\DynamicTags\\' . $class;
                class_exists( $full_class_name );
            }
        }
    }

    public static function get_tag_classes_names() {
        $available_tags = array( 
            'AseRepeaterPostTitle', 
            'AseRepeaterText', 
            'AseRepeaterImage', 
            'AseRepeaterUrl',
            'AseRepeaterFile',
            'AseRepeaterGallery',
        );

        return $available_tags;
    }

    /**
     * Not currently used anywhere, but kept here for reference.
     */
    // private static function get_all_repeater_fields( $types, $post_id ) {
        // Do something here
    // }

}
