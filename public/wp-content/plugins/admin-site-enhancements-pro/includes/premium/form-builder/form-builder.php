<?php
// Forked from Hash Form v1.2.3 (https://wordpress.org/plugins/hash-form/) by HashThemes (https://hashthemes.com/) licensed under GPL-2.0+

defined( 'ABSPATH' ) || die();

define( 'FORMBUILDER_VERSION', ASENHA_VERSION );
define( 'FORMBUILDER_FILE', __FILE__ );
define( 'FORMBUILDER_PATH', plugin_dir_path( FORMBUILDER_FILE ) );
define( 'FORMBUILDER_URL', plugin_dir_url( FORMBUILDER_FILE ) );
define( 'FORMBUILDER_UPLOAD_DIR', '/form-builder' );

require FORMBUILDER_PATH . 'classes/form-builder-icons.php';
require FORMBUILDER_PATH . 'classes/form-builder-common-methods.php';
require FORMBUILDER_PATH . 'classes/form-builder-serialized-str-parser.php';
require FORMBUILDER_PATH . 'classes/form-builder-str-reader.php';
require FORMBUILDER_PATH . 'classes/form-builder-block.php';
require FORMBUILDER_PATH . 'classes/form-builder-uploader.php';
require FORMBUILDER_PATH . 'classes/form-builder-builder.php';
require FORMBUILDER_PATH . 'classes/form-builder-helper.php';
require FORMBUILDER_PATH . 'classes/form-builder-fields.php';
require FORMBUILDER_PATH . 'classes/form-builder-loader.php';
require FORMBUILDER_PATH . 'classes/form-builder-entry.php';
require FORMBUILDER_PATH . 'classes/form-builder-import-export.php';
require FORMBUILDER_PATH . 'classes/form-builder-listing.php';
require FORMBUILDER_PATH . 'classes/form-builder-entry-listing.php';
require FORMBUILDER_PATH . 'classes/form-builder-validate.php';
require FORMBUILDER_PATH . 'classes/form-builder-preview.php';
require FORMBUILDER_PATH . 'classes/form-builder-shortcode.php';
require FORMBUILDER_PATH . 'classes/form-builder-settings.php';
require FORMBUILDER_PATH . 'classes/form-builder-styles.php';
require FORMBUILDER_PATH . 'classes/form-builder-grid-helper.php';
require FORMBUILDER_PATH . 'classes/form-builder-actions.php';
require FORMBUILDER_PATH . 'classes/form-builder-email.php';

/**
 * Register widget.
 */
// add_action( 'elementor/widgets/register', 'formbuilder_elementor_widget_register' );

// function formbuilder_elementor_widget_register( $widgets_manager ) {
//     require FORMBUILDER_PATH . 'classes/form-builder-element.php';
//     $widgets_manager->register( new \Form_Builder_Element() );
// }