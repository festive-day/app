<?php
/**
 * Breakdance integration.
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

/**
 * Breakdance integration.
 */
class Breakdance implements IntegrationInterface, HasAssetsToEnqueueInterface, DeterminesContextInterface {

	use DefaultAssetEnqueuing; // Adds enqueue_assets() method.

	/**
	 * The CSS file.
	 *
	 * @var CSS_File
	 */
	private $css_file;

	/**
	 * Constructor.
	 *
	 * @param CSS_File|null $css_file The CSS file.
	 */
	public function __construct( $css_file = null ) {
		$this->css_file = $css_file ?? new CSS_File(
			'automaticcss-breakdance',
			'automatic-breakdance.css',
			array(
				'source_file' => 'platforms/breakdance/automatic-breakdance.scss',
				'imports_folder' => 'platforms/breakdance',
			)
		);
		// Hook into the template_include filter to enqueue the builder's assets.
		// add_filter( 'template_include', array( $this, 'enqueue_builder_assets' ) ); // TODO: do we need this?
	}

	/**
	 * Get the assets to enqueue.
	 *
	 * @param Context $context The context.
	 * @return array The assets to enqueue.
	 */
	public function get_assets_to_enqueue( Context $context ): array {
		switch ( $context->get_context() ) {
			case Context::BUILDER:
				return array();
			case Context::PREVIEW:
			case Context::FRONTEND:
				return array( $this->css_file );
			default:
				return array();
		}
	}

	/**
	 * Check if the plugin is installed and activated.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'breakdance/plugin.php' );
	}

	/**
	 * Are we in Breakdance's builder context?
	 * That means we're in the builder, but not in the preview's iframe.
	 *
	 * @return bool
	 */
	public function is_builder_context() {
		$is_builder = isset( $_GET['breakdance'] ) && 'builder' === sanitize_text_field( wp_unslash( $_GET['breakdance'] ) );
		$is_preview = $this->is_preview_context();
		return $is_builder && ! $is_preview;
	}

	/**
	 * Are we in Breakdance's iframe context?
	 * That means we're in NOT in the builder, just in the preview's iframe.
	 *
	 * @return bool
	 */
	public function is_preview_context() {
		$is_preview = isset( $_GET['breakdance_iframe'] );
		return $is_preview;
	}

	/**
	 * Are we in Breakdance's frontend context?
	 * That means we're in neither in the builder nor in the preview's iframe.
	 *
	 * @return bool
	 */
	public function is_frontend_context() {
		return ! $this->is_builder_context() && ! $this->is_preview_context() && WordPress::is_wp_frontend();
	}

	/**
	 * Get the name of the integration.
	 *
	 * @return string The name of the integration.
	 */
	public static function get_name() {
		return 'breakdance';
	}
}
