<?php
/**
 * Frames integration.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Integrations;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\AssetManager\DefaultAssetEnqueuing;
use Automatic_CSS\Framework\AssetManager\HasAssetsToEnqueueInterface;
use Automatic_CSS\Framework\ContextManager\Context;
use Automatic_CSS\Framework\IntegrationManager\IntegrationInterface;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Traits\Disableable;

/**
 * Frames integration.
 */
class Frames implements IntegrationInterface, HasAssetsToEnqueueInterface {

	use DefaultAssetEnqueuing; // Adds enqueue_assets() method.

	/**
	 * Allow the Frames integration to be disabled while running.
	 */
	use Disableable;

	/**
	 * Instance of the CSS file
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
			'automaticcss-frames',
			'automatic-frames.css',
			array(
				'source_file' => 'platforms/frames/automatic-frames.scss',
				'imports_folder' => 'platforms/frames',
			),
		);
		if ( is_admin() ) {
			// Update the module's status before generating the framework's CSS.
			add_action( 'automaticcss_before_generate_framework_css', array( $this, 'update_status' ) );
		}
		// TODO: make the following dependency injectable and test covered.
		$this->update_status( Database_Settings::get_instance()->get_vars() );
	}

	/**
	 * Get the assets to enqueue.
	 *
	 * @param Context $context The context.
	 * @return array<CSS_File> The assets to enqueue.
	 */
	public function get_assets_to_enqueue( Context $context ): array {
		switch ( $context->get_context() ) {
			case Context::BUILDER:
				return array();
			case Context::PREVIEW:
			case Context::FRONTEND:
				if ( ! in_array( Bricks::class, $context->get_determiners() ) ) {
					return array(); // Frames is only for Bricks.
				}
				return array( $this->css_file );
			default:
				return array();
		}
	}

	/**
	 * Whether the integration is active.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'frames-plugin/frames-plugin.php' );
	}

	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'frames';
	}

	/**
	 * Update the enabled / disabled status of the WooCommerce module
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return void
	 */
	public function update_status( $variables ) {
		$enabled = isset( $variables['option-frames'] ) && 'on' === $variables['option-frames'] ? true : false;
		Logger::now( sprintf( '%s: setting the Frames module to %s', __METHOD__, $variables['option-frames'] ) );
		$this->set_enabled( $enabled );
		$this->css_file->set_enabled( $enabled );
	}
}
