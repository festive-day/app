<?php
/**
 * Dashboard class
 *
 * @package Automatic_CSS\Framework\Dashboard
 */

namespace Automatic_CSS\Framework\Dashboard;

use Automatic_CSS\Framework\AssetManager\DefaultAssetEnqueuing;
use Automatic_CSS\Framework\AssetManager\HasAssetsToEnqueueInterface;
use Automatic_CSS\Framework\ContextManager\Context;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\JS_File;
use Automatic_CSS\Helpers\Permissions;
use Automatic_CSS\Model\Config\UI;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Plugin;

/**
 * Dashboard class
 */
class Dashboard implements HasAssetsToEnqueueInterface {

	use DefaultAssetEnqueuing; // Adds enqueue_assets() method.

	/**
	 * Dashboard JS file
	 *
	 * @var JS_File
	 */
	private $dashboard_js_file;

	/**
	 * Hot reload JS file
	 *
	 * @var JS_File
	 */
	private $hot_reload_js_file;

	/**
	 * Permissions
	 *
	 * @var Permissions
	 */
	private $permissions;

	/**
	 * Constructor
	 *
	 * @param Permissions $permissions Permissions.
	 * @param JS_File     $dashboard_js_file Dashboard JS file.
	 * @param JS_File     $hot_reload_js_file Hot reload JS file.
	 */
	public function __construct( $permissions, $dashboard_js_file = null, $hot_reload_js_file = null ) {
		$this->permissions = $permissions;
		$path = '/UI/Dashboard/js';
		$load_from_vite = Flag::is_on( 'LOAD_DASHBOARD_FROM_VITE' ) ?? false;
		$load_in_footer = Flag::is_on( 'LOAD_DASHBOARD_SCRIPTS_IN_FOOTER' ) ?? false;
		$this->dashboard_js_file = $dashboard_js_file ?? self::setup_dashboard_js_file( $path, $load_from_vite, $load_in_footer );
		$this->hot_reload_js_file = $hot_reload_js_file ?? self::setup_hot_reload_js_file( $path, $load_in_footer );
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );
	}

	/**
	 * Setup the dashboard JS file.
	 *
	 * @param string $path The path to the dashboard JS file.
	 * @param bool   $load_from_vite Whether to load the dashboard from Vite.
	 * @param bool   $load_in_footer Whether to load the dashboard in the footer.
	 * @return JS_File
	 */
	private static function setup_dashboard_js_file( $path, $load_from_vite, $load_in_footer ) {
		$filename = 'dashboard.min.js';
		$dashboard_file_url = $load_from_vite
			? 'http://localhost:5173/features/Dashboard/main.js'
			: ACSS_CLASSES_URL . "{$path}/{$filename}";
		$dashboard_file_path = $load_from_vite
			? null
			: ACSS_CLASSES_DIR . "{$path}/{$filename}";
		return new JS_File(
			'dashboard',
			$dashboard_file_url,
			$dashboard_file_path,
			array(),
			$load_in_footer
		);
	}

	/**
	 * Setup the hot reload JS file.
	 *
	 * @param string $path The path to the hot reload JS file.
	 * @param bool   $load_in_footer Whether to load the hot reload in the footer.
	 * @return JS_File
	 */
	private static function setup_hot_reload_js_file( $path, $load_in_footer ) {
		$filename = 'acss-hot-reload.js';
		$file_url = ACSS_CLASSES_URL . "{$path}/{$filename}";
		$file_path = ACSS_CLASSES_DIR . "{$path}/{$filename}";
		return new JS_File(
			'acss-hot-reload',
			$file_url,
			$file_path,
			array(),
			$load_in_footer
		);
	}

	/**
	 * Enqueue the assets
	 *
	 * @param Context $context The context in which the assets are being enqueued.
	 */
	public function get_assets_to_enqueue( Context $context ): array {
		if ( ! $this->permissions->has_acss_access() ) {
			return array();
		}
		switch ( $context->get_context() ) {
			case Context::PREVIEW:
				return array(
					$this->hot_reload_js_file,
				);
			case Context::BUILDER:
				$this->localize_dashboard( $context );
				return array(
					$this->dashboard_js_file,
					$this->hot_reload_js_file,
				);
			case Context::FRONTEND:
				$this->localize_dashboard( $context );
				return array(
					$this->dashboard_js_file,
				);
			default:
				return array();
		}
	}

	/**
	 * Enqueue the dashboard.
	 *
	 * @param Context $context The context in which the dashboard is being enqueued.
	 */
	private function localize_dashboard( $context ) {
		$context_name = $context->get_context();
		$builder = $this->get_builder_name( $context->get_determiners() );
		$localize_name = 'automatic_css_settings';
		$localized_data = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'automatic_css_save_settings' ),
			'database_settings' => ( Database_Settings::get_instance() )->get_vars(),
			'ui_settings' => ( new UI() )->load(),
			'version' => Plugin::get_plugin_version(),
			'loading_context' => array(
				'is_frontend' => Context::FRONTEND === $context_name,
				'is_preview' => Context::PREVIEW === $context_name,
				'is_builder' => Context::BUILDER === $context_name,
				'builder' => $builder,
				'active_plugins' => $this->get_active_plugins(),
			),
		);
		$this->dashboard_js_file->set_localize( $localize_name, $localized_data );
	}

	/**
	 * Get the builder name.
	 *
	 * @param array $determiners The determiners.
	 * @return string
	 */
	private function get_builder_name( $determiners ) {
		if ( count( $determiners ) === 0 ) {
			return '';
		}
		// $determiners[0] is the name of a class implementing DeterminesContextInterface.
		return $determiners[0]::get_name();
	}

	/**
	 * Get active plugins.
	 *
	 * @return array
	 */
	private function get_active_plugins() {
		$active_plugins = wp_get_active_and_valid_plugins();
		$plugin_filenames = array_map(
			function ( $path ) {
				$filename = basename( $path );
				switch ( $filename ) {
					case 'frames-plugin.php':
						$filename = 'frames';
						break;
				}
				$pos = strrpos( $filename, '.' );
				return ( false === $pos ) ? $filename : substr( $filename, 0, $pos );
			},
			$active_plugins
		);
		return $plugin_filenames;
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
		$scripts_to_change = array( 'acss-dashboard', 'acss-hot-reload' );
		// Return early if not one of the scripts to change.
		if ( ! in_array( $handle, $scripts_to_change, true ) ) {
			return $tag;
		}
		$defer = Flag::is_on( 'DEFER_DASHBOARD_SCRIPTS' ) ? ' defer' : '';
		$module_and_crossorigin =
			Flag::is_on( 'LOAD_DASHBOARD_SCRIPTS_AS_MODULE' ) || Flag::is_on( 'LOAD_DASHBOARD_FROM_VITE' ) ?
			' type="module" crossorigin' :
			'';
		$tag = sprintf( '<script%s src="%s"%s></script>', $module_and_crossorigin, esc_url( $src ), $defer ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		return $tag;
	}
}
