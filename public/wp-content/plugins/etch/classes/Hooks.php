<?php
/**
 * Hooks system for Etch.
 *
 * @package Etch
 * @subpackage Assets
 */

namespace Etch;

use Etch\Helpers\EtchGlobal;

/**
 * Class Hooks
 *
 * Provides hooks for third-party developers and internal use
 */
class Hooks {

	/**
	 * Constructor for the Elements class.
	 *
	 * Adds action hook to register component classes on init.
	 */
	public function __construct() {
		// Register the hook to enqueue the global data
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_etch_global_hook_data' ) );
	}

	/**
	 * Sanitize and validate a path or URL.
	 *
	 * @param array{id: string, url: string} $stylesheet Array containing 'id' and 'url' keys.
	 * @return array{id: string, url: string}|false Sanitized array or false if invalid.
	 */
	private function sanitize_stylesheet( array $stylesheet ): array|false {
		if ( empty( $stylesheet['id'] ) || empty( $stylesheet['url'] ) ) {
			return false;
		}

		// Check if it's an absolute URL
		if ( filter_var( $stylesheet['url'], FILTER_VALIDATE_URL ) ) {
			// Validate that it's a CSS file
			if ( ! preg_match( '/\.css(\?.*)?$/', $stylesheet['url'] ) ) {
				return false;
			}
			$stylesheet['url'] = esc_url_raw( $stylesheet['url'] );
			return $stylesheet;
		}

		// Handle relative url
		// Remove any attempts to navigate up directories
		$stylesheet['url'] = str_replace( '..', '', $stylesheet['url'] );

		// Ensure url starts with forward slash
		$stylesheet['url'] = '/' . ltrim( $stylesheet['url'], '/' );

		// Basic validation for relative url
		if ( ! preg_match( '/^\/[\w\/-]+\.css$/', $stylesheet['url'] ) ) {
			return false;
		}

		return $stylesheet;
	}

	/**
	 * Enqueue the window object containing registered CSS paths.
	 *
	 * @return void
	 */
	public function enqueue_etch_global_hook_data(): void {
		// $additional_stylesheets @var array<array{id: string, url: string}>
		$additional_stylesheets = apply_filters( 'etch/preview/additional_stylesheets', array() );

		// Dedupe the stylesheets
		$additional_stylesheets = array_unique( $additional_stylesheets, SORT_REGULAR );

		// Sanitize and filter the paths
		$additional_stylesheets = array_filter(
			array_map(
				array( $this, 'sanitize_stylesheet' ),
				$additional_stylesheets
			)
		);

		if ( empty( $additional_stylesheets ) ) {
			return;
		}

		// Prepare the data for output
		$data = array(
			'iframe' => array(
				'additionalStylesheets' => $additional_stylesheets,
			),
		);

		EtchGlobal::get_instance()->add_to_etch_global( $data );
	}
}
