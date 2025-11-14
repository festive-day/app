<?php
/**
 * Element Block
 *
 * Renders HTML elements with dynamic attributes and context support.
 * Resolves dynamic expressions in element attributes and provides element
 * attributes context to child blocks.
 *
 * @package Etch
 */

namespace Etch\Blocks\ElementBlock;

use Etch\Blocks\Types\ElementAttributes;
use Etch\Blocks\Global\StylesRegister;
use Etch\Blocks\Global\ScriptRegister;
use Etch\Blocks\Global\ContextProvider;
use Etch\Blocks\Utilities\EtchTypeAsserter;
use Etch\Preprocessor\Utilities\EtchParser;
use WP_HTML_Tag_Processor;

/**
 * ElementBlock class
 *
 * Handles rendering of etch/element blocks with customizable HTML tags and attributes.
 * Supports dynamic expression resolution in attributes (e.g., {this.title}, {props.value}).
 */
class ElementBlock {

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
			'etch/element',
			array(
				'api_version' => '3',
				'attributes' => array(
					'tag' => array(
						'type' => 'string',
						'default' => 'div',
					),
					'attributes' => array(
						'type' => 'object',
						'default' => array(),
					),
					'styles' => array(
						'type' => 'array',
						'default' => array(),
						'items' => array(
							'type' => 'string',
						),
					),
				),
				'supports' => array(
					'html' => false,
					'className' => false,
					'customClassName' => false,
					// '__experimentalNoWrapper' => true,
					'innerBlocks' => true,
				),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render the block
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content Block content.
	 * @param \WP_Block|null       $block WP_Block instance (contains context).
	 * @return string Rendered block HTML.
	 */
	public function render_block( $attributes, $content, $block = null ) {
		$attrs = ElementAttributes::from_array( $attributes );
		$tag = $attrs->tag;

		ScriptRegister::register_script( $attrs );

		$context = ContextProvider::get_context_for_block( $block );

		$resolved_attributes = $attrs->attributes;

		if ( ! empty( $context ) ) {
			foreach ( $resolved_attributes as $name => $value ) {
				$resolved_attributes[ $name ] = EtchParser::type_safe_replacement( $value, $context );
			}
		}

		// Register styles (original + dynamic) after EtchParser processing
		StylesRegister::register_block_styles( $attrs->styles ?? array(), $attrs->attributes, $resolved_attributes );

		$attribute_string = '';
		foreach ( $resolved_attributes as $name => $value ) {
			$attribute_string .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( EtchTypeAsserter::to_string( $value ) ) );
		}

		return sprintf(
			'<%1$s%2$s>%3$s</%1$s>',
			esc_html( $tag ),
			$attribute_string,
			$content
		);
	}
}
