<?php
/**
 * Oxygen builder class.
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
 * Oxygen builder class.
 */
class Oxygen implements IntegrationInterface, HasAssetsToEnqueueInterface, DeterminesContextInterface {

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
			'automaticcss-oxygen',
			'automatic-oxygen.css',
			array(
				'source_file' => 'platforms/oxygen/automatic-oxygen.scss',
				'imports_folder' => 'platforms/oxygen',
			)
		);
	}

	/**
	 * Whether the builder is active.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		// I checked with class_exists( 'CT_Component' ), but it doesn't work here.
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'oxygen/functions.php' );
	}

	/**
	 * Whether the context is the builder context.
	 *
	 * @return boolean
	 */
	public function is_builder_context() {
		$is_builder = isset( $_GET['ct_builder'] );
		$is_preview = $this->is_preview_context();
		return $is_builder && ! $is_preview;
	}

	/**
	 * Whether the context is the preview context.
	 *
	 * @return boolean
	 */
	public function is_preview_context() {
		return isset( $_GET['oxygen_iframe'] );
	}

	/**
	 * Whether the context is the frontend context.
	 *
	 * @return boolean
	 */
	public function is_frontend_context() {
		return ! $this->is_builder_context() && ! $this->is_preview_context() && WordPress::is_wp_frontend();
	}

	/**
	 * Enqueue the appropriate assets based on context.
	 *
	 * @param Context $context The context.
	 * @return array<CSS_File> The assets to enqueue for the specified context.
	 */
	public function get_assets_to_enqueue( Context $context ): array {
		$is_my_context = in_array( self::class, $context->get_determiners() );
		switch ( $context->get_context() ) {
			case Context::BUILDER:
				return array();
			case Context::PREVIEW:
				if ( ! $is_my_context ) {
					return array();
				}
				return array(
					$this->css_file,
				);
			case Context::FRONTEND:
				return array(
					$this->css_file,
				);
			default:
				// In unknown context, don't enqueue anything.
				return array();
		}
	}

	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'oxygen';
	}
}
