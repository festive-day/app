<?php
/**
 * Slot Placeholder Block
 *
 * Registers the etch/slot-placeholder block. This block is used in component
 * definitions to mark where slot content should be inserted. The actual
 * replacement logic is handled by ComponentBlock during rendering.
 *
 * @package Etch
 */

namespace Etch\Blocks\SlotPlaceholderBlock;

use Etch\Blocks\Types\SlotPlaceholderAttributes;
use Etch\Blocks\Global\ScriptRegister;
use Etch\Blocks\Global\ComponentSlotContextProvider;
use Etch\Blocks\Global\ContextProvider;
use Etch\Blocks\Utilities\EtchTypeAsserter;

/**
 * SlotPlaceholderBlock class
 *
 * Handles registration of etch/slot-placeholder blocks.
 * The actual slot replacement logic is handled by ComponentBlock.
 */
class SlotPlaceholderBlock {

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
			'etch/slot-placeholder',
			array(
				'api_version' => '3',
				'attributes' => array(
					'name' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
				'supports' => array(
					'html' => false,
					'className' => false,
					'customClassName' => false,
					'innerBlocks' => false,
				),
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Render the block
	 *
	 * Looks up slot content from ComponentSlotContextProvider and renders it.
	 * Slot content uses parent context (not component context) to avoid props leakage.
	 * If no slot content is found, renders nothing.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content Block content.
	 * @param \WP_Block|null       $block WP_Block instance.
	 * @return string Rendered slot content or empty string.
	 */
	public function render_block( $attributes, $content, $block = null ) {
		$attrs = SlotPlaceholderAttributes::from_array( $attributes );

		ScriptRegister::register_script( $attrs );

		$slot_name = $attrs->name;
		if ( empty( $slot_name ) ) {
			return '';
		}

		// Get slot content from the provider
		$slots_map = ComponentSlotContextProvider::current_slots();
		if ( ! isset( $slots_map[ $slot_name ] ) || empty( $slots_map[ $slot_name ] ) ) {
			return '';
		}

		$slot_blocks = $slots_map[ $slot_name ];

		// Get the current component block to restore it later
		$current_component_block = ComponentSlotContextProvider::current_component_block();

		// Temporarily unset the component block so slot content uses parent context
		// This prevents component props from leaking into slot content
		ContextProvider::set_current_component_block( null );

		// Render each slot block
		$rendered = '';
		foreach ( $slot_blocks as $slot_block ) {
			// Ensure slot_block is in the correct format for render_block
			if ( ! is_array( $slot_block ) || ! isset( $slot_block['blockName'] ) ) {
				continue;
			}

			// Build the block array in the format expected by render_block
			$block_name = EtchTypeAsserter::to_string( $slot_block['blockName'] );
			if ( empty( $block_name ) ) {
				continue;
			}

			$parsed_block = array(
				'blockName' => $block_name,
			);

			if ( isset( $slot_block['attrs'] ) && is_array( $slot_block['attrs'] ) ) {
				$parsed_block['attrs'] = $slot_block['attrs'];
			}

			if ( isset( $slot_block['innerBlocks'] ) && is_array( $slot_block['innerBlocks'] ) ) {
				$parsed_block['innerBlocks'] = $slot_block['innerBlocks'];
			}

			if ( isset( $slot_block['innerHTML'] ) && is_string( $slot_block['innerHTML'] ) ) {
				$parsed_block['innerHTML'] = $slot_block['innerHTML'];
			}

			if ( isset( $slot_block['innerContent'] ) && is_array( $slot_block['innerContent'] ) ) {
				$parsed_block['innerContent'] = $slot_block['innerContent'];
			}

			// render_block accepts parsed block arrays
			// phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags
			$rendered .= render_block( $parsed_block );
		}

		// Restore the component block
		if ( $current_component_block instanceof \WP_Block ) {
			ContextProvider::set_current_component_block( $current_component_block );
		}

		return $rendered;
	}
}
