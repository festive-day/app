<?php
/**
 * Automatic.css Keyboard Nav Hover Preview class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Keyboard_Nav_Hover_Preview;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Framework\AssetManager\DefaultAssetEnqueuing;
use Automatic_CSS\Framework\AssetManager\HasAssetsToEnqueueInterface;
use Automatic_CSS\Framework\ContextManager\Context;
use Automatic_CSS\Framework\IntegrationManager\IntegrationInterface;
use Automatic_CSS\Framework\Integrations\Bricks;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\CSS_File;
use Automatic_CSS\Helpers\JS_File;
use Automatic_CSS\Helpers\Permissions;
use Automatic_CSS\Model\Database_Settings;

/**
 * Builder Keyboard_Nav_Hover_Preview class.
 */
class Keyboard_Nav_Hover_Preview extends Base implements IntegrationInterface, HasAssetsToEnqueueInterface {

	use DefaultAssetEnqueuing; // Adds enqueue_assets() method.

	/**
	 * The permissions.
	 *
	 * @var Permissions
	 */
	private $permissions;

	/**
	 * JS File for builder.
	 *
	 * @var JS_File
	 */
	private $builder_js;

	/**
	 * CSS File for builder.
	 *
	 * @var CSS_File
	 */
	private $builder_css;

	/**
	 * Initialize the feature.
	 *
	 * @param Permissions|null                     $permissions The permissions.
	 * @param array<string, CSS_File|JS_File|null> $options Array with keys: builder_js, builder_css.
	 */
	public function __construct( $permissions = null, array $options = array() ) {
		$this->permissions = $permissions ?? new Permissions();
		$this->builder_js = $options['builder_js'] ?? new JS_File(
			'keyboard-nav-hover-preview-script',
			ACSS_FEATURES_URL . '/Keyboard_Nav_Hover_Preview/js/keyboard-nav-hover-preview.min.js',
			ACSS_FEATURES_DIR . '/Keyboard_Nav_Hover_Preview/js/keyboard-nav-hover-preview.min.js',
		);
		$this->builder_css = $options['builder_css'] ?? new CSS_File(
			'keyboard-nav-hover-preview-style',
			ACSS_FEATURES_URL . '/Keyboard_Nav_Hover_Preview/css/style.css',
			ACSS_FEATURES_DIR . '/Keyboard_Nav_Hover_Preview/css/style.css',
		);

		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );

		if ( Flag::is_on( 'ENABLE_NEW_ASSET_MANAGEMENT' ) ) {
			return;
		}

		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$path = '/Keyboard_Nav_Hover_Preview/js';
		$filename = 'keyboard-nav-hover-preview.min.js';
		wp_enqueue_script(
			'keyboard-nav-hover-preview-script',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" ),
			true
		);

		$path = '/Keyboard_Nav_Hover_Preview/css';
		$filename = 'style.css';
		wp_enqueue_style(
			'keyboard-nav-hover-preview-style',
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
		// TODO: remove the first check after removing the ENABLE_NEW_ASSET_MANAGEMENT flag.
		if ( 'keyboard-nav-hover-preview-script' == $handle || 'acss-keyboard-nav-hover-preview-script' == $handle ) {
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
		if ( ! $this->permissions->current_user_has_full_access() ) {
			return array();
		}
		switch ( $context->get_context() ) {
			case Context::BUILDER:
				$is_bricks_context = in_array( Bricks::class, $context->get_determiners() );
				if ( ! $is_bricks_context ) {
					return array();
				}
				return array(
					$this->builder_css,
					$this->builder_js,
				);
			case Context::PREVIEW:
			case Context::FRONTEND:
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
		$acss_database = Database_Settings::get_instance();
		return $acss_database->get_var( 'option-keyboard-nav-hover-preview-enable' ) === 'on' ?? false;
	}

	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'keyboard-nav-hover-preview';
	}
}
