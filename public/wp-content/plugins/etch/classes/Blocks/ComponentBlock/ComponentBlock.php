<?php
/**
 * Component Block
 *
 * Renders reusable component patterns (wp_block post type) with dynamic context support.
 * Allows passing props to component instances and resolves dynamic expressions in
 * component attributes before rendering pattern blocks.
 *
 * @package Etch
 */

namespace Etch\Blocks\ComponentBlock;

use Etch\Blocks\Types\ComponentAttributes;
use Etch\Blocks\Types\SlotContentAttributes;
use Etch\Blocks\Global\ScriptRegister;
use Etch\Blocks\Global\ContextProvider;
use Etch\Blocks\Global\ComponentSlotContextProvider;
use Etch\Blocks\Utilities\EtchTypeAsserter;
use Etch\Preprocessor\Utilities\EtchParser;

/**
 * ComponentBlock class
 *
 * Handles rendering of etch/component blocks which reference pattern posts.
 * Resolves dynamic expressions in component attributes and provides component
 * props context to child blocks via ContextProvider.
 */
class ComponentBlock {

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
			'etch/component',
			array(
				'api_version' => '3',
				'attributes' => array(
					'ref' => array(
						'type' => 'number',
						'default' => null,
					),
					'attributes' => array(
						'type' => 'object',
						'default' => array(),
					),
				),
				'supports' => array(
					'html' => false,
					'className' => false,
					'customClassName' => false,
					'innerBlocks' => true,
				),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render the block
	 *
	 * Fetches the referenced pattern post (wp_block) and renders its blocks.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content Block content (inner blocks).
	 * @param \WP_Block|null       $block WP_Block instance.
	 * @return string Rendered block HTML.
	 */
	public function render_block( $attributes, $content, $block = null ) {
		// return '<p>TESTING COMPONENT BLOCK</p>';
		$attrs = ComponentAttributes::from_array( $attributes );

		ScriptRegister::register_script( $attrs );

		if ( null === $attrs->ref ) {
			return '';
		}

		if ( ! ( $block instanceof \WP_Block ) ) {
			return '';
		}

		$context = ContextProvider::get_context_for_block( $block );

		$resolved_attributes = $attrs->attributes ?? array();
		if ( ! empty( $context ) ) {
			foreach ( $resolved_attributes as $key => $value ) {
				// Resolve dynamic expressions but preserve types (don't force to string)
				$resolved_value = EtchParser::type_safe_replacement( $value, $context );
				$resolved_attributes[ $key ] = $resolved_value;
			}
			$attrs->attributes = $resolved_attributes;
			$attributes['attributes'] = $resolved_attributes;
		}

		$pattern_post = get_post( $attrs->ref );

		if ( ! $pattern_post || 'wp_block' !== $pattern_post->post_type ) {
			return '';
		}

		$pattern_blocks = parse_blocks( $pattern_post->post_content );

		// Extract slot contents from component instance inner blocks
		// Use parsed_block from WP_Block instances to avoid manual conversion
		$slots_map = $this->extract_slot_contents( $block );

		// Capture parent context BEFORE setting current component block
		// Slot content should use parent context (from before component processing), not component context
		$parent_context = ContextProvider::get_context_for_block( $block );

		// Update block with resolved attributes for context tracking
		$block->attributes = $attributes;

		// Save the previous component block to restore it after rendering
		// This preserves parent component context when rendering nested components
		$previous_component_block = ContextProvider::get_current_component_block();
		ContextProvider::set_current_component_block( $block );

		// Push slot context onto stack for lazy placeholder rendering
		ComponentSlotContextProvider::push( $slots_map, $parent_context, $block );

		// Render pattern blocks as-is - placeholders will render their slots on-demand
		$rendered = '';
		foreach ( $pattern_blocks as $pattern_block ) {
			$rendered .= render_block( $pattern_block );
		}

		// Pop slot context from stack
		ComponentSlotContextProvider::pop();

		// Restore the previous component block (could be parent component or null)
		// This ensures parent component context is preserved for subsequent blocks
		ContextProvider::set_current_component_block( $previous_component_block );

		return $rendered;
	}

	/**
	 * Extract slot contents from component instance inner blocks.
	 * Only extracts direct slot children to avoid scope bleeding between nested components.
	 * Uses parsed_block from WP_Block instances to avoid manual conversion.
	 *
	 * @param \WP_Block $block The component block instance.
	 * @return array<string, array<int, array<string, mixed>>> Array of slot name => array of parsed block data.
	 */
	private function extract_slot_contents( \WP_Block $block ): array {
		$slots_map = array();

		// Get inner blocks from the component instance
		$inner_blocks = $block->inner_blocks;
		foreach ( $inner_blocks as $inner_block ) {
			if ( ! ( $inner_block instanceof \WP_Block ) ) {
				continue;
			}

			// Check if this is a slot-content block
			if ( 'etch/slot-content' !== $inner_block->name ) {
				continue;
			}

			$slot_attrs = SlotContentAttributes::from_array( $inner_block->attributes ?? array() );
			$slot_name = $slot_attrs->name;

			if ( empty( $slot_name ) ) {
				continue;
			}

			// Only use the first slot-content block for each slot name
			// If a slot name already exists in the map, skip this one
			if ( isset( $slots_map[ $slot_name ] ) ) {
				continue;
			}

			$slot_inner_blocks = $inner_block->inner_blocks;
			$slot_parsed_blocks = array();
			foreach ( $slot_inner_blocks as $slot_inner_block ) {
				if ( $slot_inner_block instanceof \WP_Block ) {
					$parsed_block = $slot_inner_block->parsed_block;
					if ( is_array( $parsed_block ) ) {
						$slot_parsed_blocks[] = $parsed_block;
					}
				}
			}
			$slots_map[ $slot_name ] = $slot_parsed_blocks;
		}

		return $slots_map;
	}
}
