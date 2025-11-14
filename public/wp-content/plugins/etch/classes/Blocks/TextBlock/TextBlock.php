<?php
/**
 * Text Block
 *
 * Renders text content with dynamic expression support.
 * Resolves dynamic expressions in text content (e.g., {this.title}, {props.value}).
 *
 * @package Etch
 */

namespace Etch\Blocks\TextBlock;

use Etch\Blocks\Types\TextAttributes;
use Etch\Blocks\Global\ScriptRegister;
use Etch\Blocks\Global\ContextProvider;
use Etch\Preprocessor\Utilities\EtchParser;

/**
 * TextBlock class
 *
 * Handles rendering of etch/text blocks with dynamic content resolution.
 * Supports all context types: global (this, site, user), component props, and element attributes.
 */
class TextBlock {

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
			'etch/text',
			array(
				'api_version' => '3',
				'attributes' => array(
					'content' => array(
						'type'     => 'string',
						'source'   => 'html',
						'selector' => 'span',
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
		$attrs = TextAttributes::from_array( $attributes );
		$text_content = $attrs->content;

		ScriptRegister::register_script( $attrs );

		$context = ContextProvider::get_context_for_block( $block );

		if ( ! empty( $context ) ) {
			$text_content = EtchParser::replace_string( $text_content, $context );
		}

		return wp_strip_all_tags( $text_content, true );
	}
}
