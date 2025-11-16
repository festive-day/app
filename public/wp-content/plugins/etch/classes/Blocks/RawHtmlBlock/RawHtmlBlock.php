<?php
/**
 * Raw Html Block
 *
 * Renders raw html content with dynamic expression support.
 * Resolves dynamic expressions content (e.g., {this.title}, {props.myHtml}).
 *
 * @package Etch
 */

namespace Etch\Blocks\RawHtmlBlock;

use Etch\Blocks\Types\RawHtmlAttributes;
use Etch\Blocks\Global\ScriptRegister;
use Etch\Blocks\Global\ContextProvider;
use Etch\Blocks\Utilities\ShortcodeProcessor;
use Etch\Preprocessor\Utilities\EtchParser;

/**
 * RawHtmlBlock class
 *
 * Handles rendering of etch/Raw Html Blocks with dynamic content resolution.
 * Supports all context types: global (this, site, user), component props, and element attributes.
 */
class RawHtmlBlock {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the block
	 *
	 * @return void
	 */
	public function register_block() {
		register_block_type(
			'etch/raw-html',
			array(
				'api_version' => '3',
				'attributes' => array(
					'content' => array(
						'type'     => 'string',
						'default'  => '',
					),
					'unsafe' => array(
						'type'     => 'string',
						'default'  => '',
					),
				),
				'supports' => array(
					'html' => false,
					'className' => false,
					'customClassName' => false,
				),
				'render_callback' => array( $this, 'render_callback' ),
			)
		);
	}

	/**
	 * Render callback for the block
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content    Block content.
	 * @param \WP_Block|null       $block      WP_Block instance (contains context).
	 * @return string
	 */
	public function render_callback( array $attributes, string $content = '', $block = null ): string {
		$attrs = RawHtmlAttributes::from_array( $attributes );
		$html_content = $attrs->content;

		ScriptRegister::register_script( $attrs );

		$context = ContextProvider::get_context_for_block( $block );

		$unsafe = false;

		if ( ! empty( $context ) ) {
			$html_content = EtchParser::replace_string( $html_content, $context );
			$resolvedUnsafe = EtchParser::replace_string( $attrs->unsafe, $context );
			if ( ! empty( $resolvedUnsafe ) ) {
				$unsafe = in_array( $resolvedUnsafe, array( 'true', '1', 'yes', 'on' ), true );
			}
		}

		// Process shortcodes after dynamic data resolution
		$html_content = ShortcodeProcessor::process( $html_content, 'etch/raw-html' );

		// On unsafe, return raw content
		if ( $unsafe ) {
			return $html_content;
		}

		return $this->sanitize_html( $html_content );
	}

	/**
	 * Sanitize HTML content to prevent XSS
	 *
	 * @param string $html_content HTML content to sanitize.
	 * @return string Sanitized HTML content.
	 */
	public function sanitize_html( string $html_content ): string {
		$allowed_html = wp_kses_allowed_html( 'post' );
		$allowed_html['*']['data-*'] = true;
		$allowed_html['*']['style']  = true;
		return wp_kses( $html_content, $allowed_html );
	}
}
