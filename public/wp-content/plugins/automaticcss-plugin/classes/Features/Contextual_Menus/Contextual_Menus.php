<?php
/**
 * Automatic.css Contextual Menus class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Features\Contextual_Menus;

use Automatic_CSS\Features\Base;
use Automatic_CSS\Framework\AssetManager\DefaultAssetEnqueuing;
use Automatic_CSS\Framework\AssetManager\HasAssetsToEnqueueInterface;
use Automatic_CSS\Framework\ContextManager\Context;
use Automatic_CSS\Framework\IntegrationManager\IntegrationInterface;
use Automatic_CSS\Framework\Platforms\Bricks;
use Automatic_CSS\Helpers\CSS_File;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\JS_File;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Helpers\Permissions;
use Automatic_CSS\Model\Database_Settings;

/**
 * Builder Contextual Menus class.
 */
class Contextual_Menus extends Base implements IntegrationInterface, HasAssetsToEnqueueInterface {

	use DefaultAssetEnqueuing; // Adds enqueue_assets() method.

	/**
	 * The permissions.
	 *
	 * @var Permissions
	 */
	private $permissions;

	/**
	 * The JS file.
	 *
	 * @var JS_File
	 */
	private $context_menu_js_file;

	/**
	 * The CSS file.
	 *
	 * @var CSS_File
	 */
	private $context_menu_css_file;

	/**
	 * Initialize the feature.
	 *
	 * @param Permissions   $permissions The permissions.
	 * @param JS_File|null  $context_menu_js_file The JS file.
	 * @param CSS_File|null $context_menu_css_file The CSS file.
	 */
	public function __construct( $permissions = null, $context_menu_js_file = null, $context_menu_css_file = null ) {
		$this->permissions = $permissions ?? new Permissions();
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );
		$this->context_menu_js_file = $context_menu_js_file ?? new JS_File(
			'class-context-menu',
			ACSS_FEATURES_URL . '/Contextual_Menus/js/main.min.js',
			ACSS_FEATURES_DIR . '/Contextual_Menus/js/main.min.js',
			array(),
			true,
			'p_acssSettings_object',
			array(
				'settings' => get_option( 'automatic_css_settings' ),
			)
		);
		$this->context_menu_css_file = $context_menu_css_file ?? new CSS_File(
			'context-menu-css',
			ACSS_FEATURES_URL . '/Contextual_Menus/css/style.css',
			ACSS_FEATURES_DIR . '/Contextual_Menus/css/style.css'
		);
		// Skip the enqueueing of the assets if the new asset management is enabled.
		if ( Flag::is_on( 'ENABLE_NEW_ASSET_MANAGEMENT' ) ) {
			return;
		}
		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/gutenberg/in_builder_context', array( $this, 'enqueue_scripts' ) );
		add_action( 'acss/oxygen/in_builder_context', array( $this, 'enqueue_oxygen_scripts' ) );
		add_action( 'acss/bricks/in_builder_context', array( $this, 'enqueue_bricks_scripts' ) );
		add_action( 'acss/gutenberg/in_builder_context', array( $this, 'enqueue_gutenberg_scripts' ) );
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
			// TODO: load the builder-specific assets too:
			// - balloon.css in Gutenberg only (since we no longer support Oxygen)
			// - bricks-enlarge-inputs.css in Bricks only.
			// BUT CHECK THAT THESE ARE NEEDED FIRST.
			case Context::BUILDER:
				return array(
					$this->context_menu_js_file,
					$this->context_menu_css_file,
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
		return $acss_database->get_var( 'option-contextual-menus-enable' ) === 'on' ?? false;
	}

	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'contextual-menus';
	}

	/**
	 * Enqueue scripts for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! $this->has_user_full_access() ) {
			return;
		}

		$doing_cwicly = doing_action( 'acss/cwicly/in_builder_context' );
		$done_cwicly = did_action( 'acss/cwicly/in_builder_context' );
		Logger::log( sprintf( '%s: doing_cwicly: %s, done_cwicly: %s', __METHOD__, $doing_cwicly, $done_cwicly ) );
		if ( $doing_cwicly || $done_cwicly ) {
			// TODO: remove this check when cwicly is fully supported.
			return;
		}
		$path = '/Contextual_Menus/js';
		$filename = 'main.min.js';
		wp_enqueue_script(
			'class-context-menu',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" ),
			true
		);

		// add acss settings object so acss settings can be checked within js.
		wp_localize_script(
			'class-context-menu',
			'p_acssSettings_object',
			array(
				'settings' => get_option( 'automatic_css_settings' ),
			)
		);

		$path = '/Contextual_Menus/css';
		$filename = 'style.css';
		wp_enqueue_style(
			'plstr-context-menu-style',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);

		// add acss settings object so acss settings can be checked within js.
		wp_localize_script(
			'var-context-menu',
			'p_acssSettings_object',
			array(
				'settings' => get_option( 'automatic_css_settings' ),
			)
		);
	}

	/**
	 * Enqueue oxygen specific scripts and styles for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_oxygen_scripts() {
		if ( ! $this->has_user_full_access() ) {
			return;
		}

		$path = '/Contextual_Menus/css';
		$filename = 'balloon.css';
		wp_enqueue_style(
			'context-menu-balloon-css',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}

	/**
	 * Enqueue gutenberg specific scripts and styles for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_gutenberg_scripts() {
		if ( ! $this->has_user_full_access() ) {
			return;
		}

		$doing_cwicly = doing_action( 'acss/cwicly/in_builder_context' );
		$done_cwicly = did_action( 'acss/cwicly/in_builder_context' );
		Logger::log( sprintf( '%s: doing_cwicly: %s, done_cwicly: %s', __METHOD__, $doing_cwicly, $done_cwicly ) );
		if ( $doing_cwicly || $done_cwicly ) {
			// TODO: remove this check when cwicly is fully supported.
			return;
		}
		$path = '/Contextual_Menus/css';
		$filename = 'balloon.css';
		wp_enqueue_style(
			'context-menu-balloon-css',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}


	/**
	 * Enqueue bricks specific scripts and styles for the contextual menu feature.
	 *
	 * @return void
	 */
	public function enqueue_bricks_scripts() {
		if ( ! $this->has_user_full_access() ) {
			return;
		}

		$path = '/Contextual_Menus/css';
		$filename = 'bricks-enlarge-inputs.css';
		wp_enqueue_style(
			'context-menu-bricks-enlarge-inputs-css',
			ACSS_FEATURES_URL . "{$path}/{$filename}",
			array(),
			filemtime( ACSS_FEATURES_DIR . "{$path}/{$filename}" )
		);
	}

	/**
	 * Adds 'type="module"' to the script tag
	 *
	 * @param string $tag The original script tag.
	 * @param string $handle The script handle.
	 * @param string $src The script source.
	 * @return string
	 */
	public static function add_type_attribute( $tag, $handle, $src ) {
		// if not correct script, do nothing and return original $tag.
		// TODO: remove the first check after removing the ENABLE_NEW_ASSET_MANAGEMENT flag.
		if ( 'class-context-menu' == $handle || 'acss-class-context-menu' == $handle || 'var-context-menu' == $handle ) {
			$load_as_module =
				Flag::is_on( 'LOAD_DASHBOARD_SCRIPTS_AS_MODULE' ) || Flag::is_on( 'LOAD_DASHBOARD_FROM_VITE' ) ?
				' type="module"' :
				'';
			$tag = '<script' . $load_as_module . ' src="' . esc_url( $src ) . '"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		}
		// change the script tag by adding type="module" and return it.

		return $tag;
	}
}
