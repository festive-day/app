<?php
/**
 * Editor class.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

/**
 * Provides an action to output buttons above the TinyMCE editor.
 *
 * @since   1.0.7
 */
class Media_Categories_Module_Editor {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.0.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.0.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_filter( 'media_buttons', array( $this, 'output_media_buttons' ) );

	}

	/**
	 * Outputs buttons above TinyMCE Editor instances.
	 *
	 * @since   1.0.7
	 */
	public function output_media_buttons() {

		/**
		 * Outputs buttons above TinyMCE Editor instances.
		 *
		 * @since   1.0.7
		 */
		do_action( 'media_categories_module_editor_output_media_buttons' );

	}

}
