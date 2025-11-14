<?php
/**
 * Etch integration.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Integrations;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\AssetManager\DefaultAssetEnqueuing;
use Automatic_CSS\Framework\AssetManager\HasAssetsToEnqueueInterface;
use Automatic_CSS\Framework\ContextManager\Context;
use Automatic_CSS\Framework\ContextManager\DeterminesContextInterface;
use Automatic_CSS\Framework\IntegrationManager\IntegrationInterface;
use Automatic_CSS\Helpers\WordPress;
use Automatic_CSS\Plugin;

/**
 * Etch integration.
 */
class Etch implements IntegrationInterface, HasAssetsToEnqueueInterface, DeterminesContextInterface {

	use DefaultAssetEnqueuing; // Adds enqueue_assets() method.

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'etch/preview/additional_stylesheets', array( $this, 'enqueue_preview_assets' ) );
	}

	/**
	 * Is the Etch platform active?
	 *
	 * @return boolean
	 */
	public static function is_active() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'etch/etch.php' );
	}

	/**
	 * Is the builder context?
	 *
	 * @return boolean
	 */
	public function is_builder_context() {
		$is_builder = (bool) filter_input( INPUT_GET, 'etch' );
		$is_preview = $this->is_preview_context();
		return $is_builder && ! $is_preview;
	}

	/**
	 * Is the preview context?
	 *
	 * @return boolean
	 */
	public function is_preview_context() {
		return false;
	}

	/**
	 * Is the frontend context?
	 *
	 * @return boolean
	 */
	public function is_frontend_context() {
		return ! $this->is_builder_context() && ! $this->is_preview_context() && WordPress::is_wp_frontend();
	}

	/**
	 * Enqueue assets for the given context.
	 *
	 * @param Context $context The context to enqueue assets for.
	 * @return array<CSS_File> The assets to enqueue for the specified context.
	 */
	public function get_assets_to_enqueue( Context $context ): array {
		return array();
	}

	/**
	 * Enqueue the preview assets.
	 *
	 * @param array<array{id: string, url: string}> $additional_stylesheets The stylesheets to add to Etch's preview.
	 * @return array The (possibly modified) stylesheets to add to Etch's preview.
	 */
	public function enqueue_preview_assets( $additional_stylesheets = array() ) {
		// No self::is_preview_context() because it doesn't work with Etch.
		// We trust that the action is only called when we're in the preview.
		$asset_manager = Plugin::get_instance()->asset_manager;
		$context = new Context( Context::PREVIEW, true, array( self::class ) );
		// @var array<CSS_File> $stylesheets
		$assets = $asset_manager->get_assets_to_enqueue( $context );
		$stylesheets = array_filter(
			$assets,
			function ( $asset ) {
				return $asset instanceof CSS_File;
			}
		);
		foreach ( $stylesheets as $stylesheet ) {
			$additional_stylesheets[] = array(
				'id' => $stylesheet->handle,
				'url' => $stylesheet->file_url,
			);
		}
		return apply_filters( 'acss/etch/additional_stylesheets', $additional_stylesheets );
	}

	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'etch';
	}
}
