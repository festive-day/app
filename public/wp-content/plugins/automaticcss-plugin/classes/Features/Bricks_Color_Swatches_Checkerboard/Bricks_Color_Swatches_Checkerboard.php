<?php
/**
 * Automatic.css Bricks_Color_Swatches_Checkerboard class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Bricks_Color_Swatches_Checkerboard;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Framework\AssetManager\DefaultAssetEnqueuing;
use Automatic_CSS\Framework\AssetManager\HasAssetsToEnqueueInterface;
use Automatic_CSS\Framework\ContextManager\Context;
use Automatic_CSS\Framework\IntegrationManager\IntegrationInterface;
use Automatic_CSS\Framework\Integrations\Bricks;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\CSS_File;
use Automatic_CSS\Model\Database_Settings;

/**
 * Builder Bricks_Color_Swatches_Checkerboard class.
 */
class Bricks_Color_Swatches_Checkerboard extends Base implements IntegrationInterface, HasAssetsToEnqueueInterface {

	use DefaultAssetEnqueuing; // Adds enqueue_assets() method.

	/**
	 * CSS File for builder.
	 *
	 * @var CSS_File
	 */
	private $builder_css;

	/**
	 * Initialize the feature.
	 *
	 * @param array<string, CSS_File|JS_File|null> $options Array with keys: builder_js.
	 */
	public function __construct( array $options = array() ) {
		$this->builder_css = $options['builder_css'] ?? new CSS_File(
			'bricks-color-swatches-checkerboard',
			ACSS_FEATURES_URL . '/Bricks_Color_Swatches_Checkerboard/css/checkerboard.css',
			ACSS_FEATURES_DIR . '/Bricks_Color_Swatches_Checkerboard/css/checkerboard.css',
		);

		if ( Flag::is_on( 'ENABLE_NEW_ASSET_MANAGEMENT' ) ) {
			return;
		}

		// add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_scripts' ) ); // commented out.
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_scripts' ) );
		// add_filter('script_loader_tag', array($this,'add_type_attribute') , 10, 3); // commented out.
	}

	/**
	 * Enqueue scripts for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		$path = '/Bricks_Color_Swatches_Checkerboard/css';
		$filename = 'checkerboard.css';
		wp_enqueue_style(
			'bricks-color-swatches-checkerboard',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}

	/**
	 * Adds 'type="module"' to the script tag
	 *
	 * @param string $tag    The original script tag.
	 * @param string $handle The script handle.
	 * @param string $src    The script source.
	 * @return string
	 */
	public static function add_type_attribute( $tag, $handle, $src ) {
		// if not correct script, do nothing and return original $tag.
		if ( 'keyboard-nav-hover-preview-script' === $handle ) {
			$load_as_module =
			Flag::is_on( 'LOAD_DASHBOARD_SCRIPTS_AS_MODULE' ) || Flag::is_on( 'LOAD_DASHBOARD_FROM_VITE' ) ?
			' type="module"' :
			'';
			$tag = '<script' . $load_as_module . ' src="' . esc_url( $src ) . '"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		}
		// change the script tag by adding type="module" and return it.

		return $tag;
	}

	/**
	 * Enqueue the assets
	 *
	 * @param Context $context The context in which the assets are being enqueued.
	 */
	public function get_assets_to_enqueue( Context $context ): array {
		switch ( $context->get_context() ) {
			case Context::BUILDER:
				$is_bricks_context = in_array( Bricks::class, $context->get_determiners() );
				if ( ! $is_bricks_context ) {
					return array();
				}
				return array(
					$this->builder_css,
				);
			case Context::PREVIEW:
			case Context::FRONTEND:
			default:
				return array();
		}

		return array();
	}

	/**
	 * Whether the integration is active.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		$acss_database = Database_Settings::get_instance();
		return $acss_database->get_var( 'option-bricks-color-swatches-checkerboard-enable' ) === 'on' ?? false;
	}

	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'bricks-color-swatches-checkerboard';
	}
}
