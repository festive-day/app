<?php
/**
 * Forked from Media Library Organizer v1.6.0 by Optimole
 * 
 * @link https://wordpress.org/plugins/media-library-organizer/
 */

// Bail if Media Categories Module is alread loaded.
if ( class_exists( 'Media_Categories_Module' ) ) {
	return;
}

// Define Plugin paths.
define( 'MEDIA_CATEGORIES_MODULE_URL', plugin_dir_url( __FILE__ ) );
define( 'MEDIA_CATEGORIES_MODULE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Define the autoloader for this Plugin
 *
 * @since   1.0.0
 *
 * @param   string $class_name     The class to load.
 */
function media_categories_module_autoloader( $class_name ) {

	// Define the required start of the class name.
	$class_start_name = 'Media_Categories_Module';

	// Get the number of parts the class start name has.
	$class_parts_count = count( explode( '_', $class_start_name ) );

	// Break the class name into an array.
	$class_path = explode( '_', $class_name );

	// Bail if it's not a minimum length (i.e. doesn't potentially have Media_Categories_Module).
	if ( count( $class_path ) < $class_parts_count ) {
		return;
	}

	// Build the base class path for this class.
	$base_class_path = '';
	for ( $i = 0; $i < $class_parts_count; $i++ ) {
		$base_class_path .= $class_path[ $i ] . '_';
	}
	$base_class_path = trim( $base_class_path, '_' );

	// Bail if the first parts don't match what we expect.
	if ( $base_class_path !== $class_start_name ) {
		return;
	}

	// Define the file name.
	$file_name = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . '.php';

	// Define the paths to search for the file.
	$include_paths = array(
		MEDIA_CATEGORIES_MODULE_PATH . 'includes/admin/',
		MEDIA_CATEGORIES_MODULE_PATH . 'includes/global/',
	);

	// Iterate through the include paths to find the file.
	foreach ( $include_paths as $path ) {
		if ( file_exists( $path . '/' . $file_name ) ) {
			require_once $path . '/' . $file_name;
			return;
		}
	}

}
spl_autoload_register( 'media_categories_module_autoloader' );

/**
 * Main function to return Plugin instance.
 *
 * @since   1.0.5
 */
function Media_Categories_Module() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

	return Media_Categories_Module::get_instance();

}
// Finally, initialize the Plugin.
require_once MEDIA_CATEGORIES_MODULE_PATH . 'includes/class-media-categories-module.php';
$media_categories_module = Media_Categories_Module();
