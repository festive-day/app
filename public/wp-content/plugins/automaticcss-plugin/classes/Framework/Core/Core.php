<?php
/**
 * Automatic.css Framework's Context Core file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Core;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Framework\AssetManager\HasAssetsToEnqueueInterface;
use Automatic_CSS\Framework\ContextManager\Context;
use Automatic_CSS\Framework\ContextManager\DeterminesContextInterface;
use Automatic_CSS\Framework\Integrations\Gutenberg;
use Automatic_CSS\Helpers\WordPress;
use Automatic_CSS\Model\Database_Settings;

/**
 * Automatic.css Framework's Context Core class.
 */
class Core implements DeterminesContextInterface, HasAssetsToEnqueueInterface {

	/**
	 * Instance of the core CSS file
	 *
	 * @var CSS_File
	 */
	private $core_css_file;

	/**
	 * Instance of the vars CSS file
	 *
	 * @var CSS_File
	 */
	private $vars_css_file;

	/**
	 * Instance of the custom CSS file
	 *
	 * @var CSS_File
	 */
	private $custom_css_file;

	/**
	 * Instance of the tokens CSS file
	 *
	 * @var CSS_File
	 */
	private $tokens_css_file;

	/**
	 * Whether the option-inline-tokens is on.
	 *
	 * @var boolean
	 */
	private $is_option_inline_tokens_on;

	/**
	 * Constructor
	 *
	 * @param CSS_File|null $core_css_file The core CSS file.
	 * @param CSS_File|null $vars_css_file The variables CSS file.
	 * @param CSS_File|null $custom_css_file The custom CSS file.
	 * @param CSS_File|null $tokens_css_file The tokens CSS file.
	 */
	public function __construct( $core_css_file = null, $vars_css_file = null, $custom_css_file = null, $tokens_css_file = null ) {
		$this->core_css_file = $core_css_file ?? new CSS_File( 'automaticcss-core', 'automatic.css', 'automatic.scss' );
		$this->vars_css_file = $vars_css_file ?? new CSS_File( 'automaticcss-variables', 'automatic-variables.css', 'automatic-variables.scss' );
		$this->custom_css_file = $custom_css_file ?? new CSS_File( 'automaticcss-custom', 'automatic-custom-css.css', 'automatic-custom-css.scss' );
		$this->tokens_css_file = $tokens_css_file ?? new CSS_File( 'automaticcss-tokens', 'automatic-tokens.css', 'automatic-tokens.scss' );
		// TODO: dependency inject and test cover this.
		$this->is_option_inline_tokens_on = Database_Settings::get_instance()->get_var( 'option-inline-tokens' ) === 'on' ?? false;
		// Hooks.
		add_action( 'acss/gutenberg/builder_context', array( $this, 'enqueue_gutenberg_builder_assets' ) );
	}

	/**
	 * Whether the builder is active.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return true; // Core is always active.
	}

	/**
	 * Whether the context is the builder context.
	 *
	 * @return boolean
	 */
	public function is_builder_context() {
		return false; // Core is not a builder.
	}

	/**
	 * Whether the context is the preview context.
	 *
	 * @return boolean
	 */
	public function is_preview_context() {
		return false; // Core is not a builder, so it can't be in preview mode.
	}

	/**
	 * Whether the context is the frontend context.
	 *
	 * @return boolean
	 */
	public function is_frontend_context() {
		return WordPress::is_wp_frontend();
	}

	/**
	 * Get the frontend and preview stylesheets.
	 *
	 * @param Context $context The context to get stylesheets for.
	 * @return array<CSS_File> The stylesheets for the given context.
	 */
	public function get_assets_to_enqueue( Context $context ): array {
		switch ( $context->get_context() ) {
			case Context::BUILDER:
				return array(
					$this->vars_css_file,
				);
			case Context::PREVIEW:
				if ( $this->is_gutenberg_active( $context->get_determiners() ) ) {
					return array();
				}
				return array(
					$this->tokens_css_file,
					$this->core_css_file,
					$this->custom_css_file,
				);
			case Context::FRONTEND:
				return array(
					$this->tokens_css_file,
					$this->core_css_file,
					$this->custom_css_file,
				);
			default:
				return array();
		}
	}

	/**
	 * Enqueue the appropriate assets based on context.
	 *
	 * @param Context $context The context.
	 * @return void
	 */
	public function enqueue_assets( Context $context ): void {
		$assets = $this->get_assets_to_enqueue( $context );
		foreach ( $assets as $asset ) {
			switch ( $asset->handle ) {
				case $this->core_css_file->handle:
				case $this->vars_css_file->handle:
					$asset->enqueue_stylesheet();
					break;
				case $this->tokens_css_file->handle:
					// TODO: test cover this.
					if ( $this->is_option_inline_tokens_on ) {
						$asset->enqueue_inline();
					} else {
						$asset->enqueue_stylesheet();
					}
					break;
				case $this->custom_css_file->handle:
					$asset->enqueue_inline();
					break;
			}
		}
	}

	/**
	 * Enqueue the VARS stylesheet in the Gutenberg builder context.
	 * For some reason, without special handling, the VARS stylesheet bleeds into the Gutenberg preview context.
	 *
	 * @return void
	 */
	public function enqueue_gutenberg_builder_assets() {
		$this->vars_css_file->enqueue_stylesheet();
	}

	/**
	 * Whether Gutenberg is active.
	 *
	 * @param array<string> $determiners The class names of the determiners that are active for this context.
	 * @return boolean
	 */
	private function is_gutenberg_active( $determiners ) {
		return in_array( Gutenberg::class, $determiners );
	}

	/**
	 * Get the name of the context determiner.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'core';
	}
}
