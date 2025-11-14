<?php
/**
 * Bricks builder class.
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
 * Bricks builder class.
 */
class Bricks implements IntegrationInterface, HasAssetsToEnqueueInterface, DeterminesContextInterface {

	use DefaultAssetEnqueuing; // Adds enqueue_assets() method.

	/**
	 * The CSS file.
	 *
	 * @var CSS_File
	 */
	private $css_file;

	/**
	 * The CSS file for the builder.
	 *
	 * @var CSS_File
	 */
	private $in_builder_css_file;

	/**
	 * Constructor.
	 *
	 * @param CSS_File|null $css_file The CSS file.
	 * @param CSS_File|null $in_builder_css_file The CSS file for the builder.
	 */
	public function __construct( $css_file = null, $in_builder_css_file = null ) {
		$this->css_file = $css_file ?? new CSS_File(
			'automaticcss-bricks',
			'automatic-bricks.css',
			array(
				'source_file' => 'platforms/bricks/automatic-bricks.scss',
				'imports_folder' => 'platforms/bricks',
			),
			array(
				'deps' => apply_filters( 'automaticcss_bricks_deps', array( 'automaticcss-core' ) ),
			)
		);
		$this->in_builder_css_file = $in_builder_css_file ?? new CSS_File(
			'automaticcss-bricks-in-builder',
			'automatic-bricks-in-builder.css',
			array(
				'source_file' => 'platforms/bricks/bricks-in-builder.scss',
				'imports_folder' => 'platforms/bricks',
			)
		);
	}

	/**
	 * Whether the builder is active.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		$theme = wp_get_theme(); // gets the current theme.
		return 'Bricks' === $theme->name || 'Bricks' === $theme->parent_theme;
	}

	/**
	 * Whether the context is the builder context.
	 *
	 * @return boolean
	 */
	public function is_builder_context() {
		$is_builder = isset( $_GET['bricks'] ) && 'run' === sanitize_text_field( wp_unslash( $_GET['bricks'] ) );
		$is_preview = $this->is_preview_context();
		return $is_builder && ! $is_preview;
	}

	/**
	 * Whether the context is the preview context.
	 *
	 * @return boolean
	 */
	public function is_preview_context() {
		return isset( $_GET['brickspreview'] );
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
			case 'builder':
				if ( ! $is_my_context ) {
					return array();
				}
				return array(
					$this->in_builder_css_file,
				);
			case 'preview':
				if ( ! $is_my_context ) {
					return array();
				}
				return array(
					$this->css_file,
				);
			case 'frontend':
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
		return 'bricks';
	}
}
