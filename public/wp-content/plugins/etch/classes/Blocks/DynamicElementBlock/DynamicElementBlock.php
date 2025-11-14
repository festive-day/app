<?php
/**
 * Dynamic Element Block
 *
 * Renders HTML elements with dynamic attributes and context support.
 * Similar to ElementBlock but registered as etch/dynamic-element.
 * Resolves dynamic expressions in element attributes and provides element
 * attributes context to child blocks.
 *
 * @package Etch
 */

namespace Etch\Blocks\DynamicElementBlock;

use Etch\Blocks\Types\ElementAttributes;
use Etch\Blocks\Global\StylesRegister;
use Etch\Blocks\Global\ScriptRegister;
use Etch\Blocks\Global\ContextProvider;
use Etch\Blocks\Utilities\EtchTypeAsserter;
use Etch\Preprocessor\Utilities\EtchParser;

/**
 * DynamicElementBlock class
 *
 * Handles rendering of etch/dynamic-element blocks with customizable HTML tags and attributes.
 * Supports dynamic expression resolution in attributes (e.g., {this.title}, {props.value}).
 */
class DynamicElementBlock {

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
			'etch/dynamic-element',
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

		$tag = 'div';
		if ( isset( $resolved_attributes['tag'] ) && is_string( $resolved_attributes['tag'] ) ) {
			$tag = $resolved_attributes['tag'];
			// Remove 'tag' from attributes so it doesn't get rendered as an HTML attribute
			unset( $resolved_attributes['tag'] );
		} elseif ( ! empty( $attrs->tag ) ) {
			$tag = $attrs->tag;
		}

		// Sanitize the tag name
		$tag = ElementAttributes::is_valid_tag_format( $tag ) ? strtolower( trim( $tag ) ) : 'div';

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
