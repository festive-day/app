<?php
/**
 * Loop Block
 *
 * Renders its inner blocks for each item of a resolved collection.
 * Supports loop presets via LoopHandlerManager and arbitrary targets resolved
 * through EtchParser expressions with modifiers. Loop params can be dynamic.
 *
 * @package Etch\Blocks\LoopBlock
 */

namespace Etch\Blocks\LoopBlock;

use Etch\Blocks\Types\LoopAttributes;
use Etch\Blocks\Global\ScriptRegister;
use Etch\Blocks\Global\ContextProvider;
use Etch\Preprocessor\Utilities\EtchParser;
use Etch\Preprocessor\Utilities\LoopHandlerManager;

/**
 * LoopBlock class
 */
class LoopBlock {

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
			'etch/loop',
			array(
				'api_version' => '3',
				'attributes' => array(
					'target' => array(
						'type' => 'string',
						'default' => '',
					),
					'itemId' => array(
						'type' => 'string',
						'default' => '',
					),
					'indexId' => array(
						'type' => 'string',
						'default' => '',
					),
					'loopId' => array(
						'type' => 'string',
						'default' => null,
					),
					'loopParams' => array(
						'type' => 'object',
						'default' => null,
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
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content Block content (unused - we render children manually).
	 * @param \WP_Block|null       $block WP_Block instance (provides access to inner blocks and parent).
	 * @return string Rendered HTML for all loop iterations.
	 */
	public function render_block( array $attributes, string $content = '', $block = null ): string {
		$attrs = LoopAttributes::from_array( $attributes );

		ScriptRegister::register_script( $attrs );

		// Resolve the base context for this block
		$base_context = ContextProvider::get_context_for_block( $block );

		// Resolve loop params against current context (support dynamic values and modifiers)
		$resolved_loop_params = $this->resolve_loop_params( $attrs->loopParams, $base_context );

		// Determine loop target and prepare context for resolution
		$resolve_target = $attrs->target ?? '';

		if ( null !== $attrs->loopId && LoopHandlerManager::is_valid_loop_id( $attrs->loopId ) ) {
			$loop_id = LoopHandlerManager::strip_loop_params_from_string( $attrs->loopId );
			$loop_data = LoopHandlerManager::get_loop_preset_data( $loop_id, $resolved_loop_params );

			// Generate a unique id for the loop source resolving to avoid conflicts
			// We are unable to use the loopId here directly due to some issues with legacy loops
			// (10+ month old ids, but apperantly our own sites have a ton of those)
			$resolve_id = 'loop_' . substr( uniqid(), -7 );
			$base_context[ $resolve_id ] = $loop_data;

			// Adjust the target to point to the loop item
			if ( '' === $resolve_target ) {
				$resolve_target = $resolve_id;
			} else {
				$resolve_target = $resolve_id . '.' . $resolve_target;
			}
		}

		// Resolve the target into an array of items
		$resolved_items = array();
		if ( '' !== $resolve_target ) {
			$resolved = EtchParser::process_expression( $resolve_target, $base_context );
			if ( is_array( $resolved ) ) {
				$resolved_items = $resolved;
			}
		}

		if ( empty( $resolved_items ) ) {
			return '';
		}

		// Prepare item and index keys
		$item_key = null !== $attrs->itemId && '' !== $attrs->itemId ? $attrs->itemId : 'item';
		$index_key = null !== $attrs->indexId && '' !== $attrs->indexId ? $attrs->indexId : null;

		// Render inner blocks for each item
		$rendered = '';
		$inner_blocks = array();
		if ( $block instanceof \WP_Block && isset( $block->parsed_block['innerBlocks'] ) && is_array( $block->parsed_block['innerBlocks'] ) ) {
			$inner_blocks = $block->parsed_block['innerBlocks'];
		}

		foreach ( $resolved_items as $index => $item ) {
			$loop_context = array( $item_key => $item );
			if ( null !== $index_key ) {
				$loop_context[ $index_key ] = $index;
			}

			ContextProvider::push_loop_context( $loop_context );
			foreach ( $inner_blocks as $child ) {
				$rendered .= render_block( $child );
			}
			ContextProvider::pop_loop_context();
		}

		return $rendered;
	}

	/**
	 * Resolve loop parameters (process string expressions against context)
	 *
	 * @param array<string, mixed>|null $params Loop params.
	 * @param array<string, mixed>      $context Context for expression resolution.
	 * @return array<string, mixed>
	 */
	private function resolve_loop_params( ?array $params, array $context ): array {
		if ( empty( $params ) ) {
			return array();
		}

		$resolved = array();
		foreach ( $params as $key => $value ) {
			if ( is_string( $value ) ) {
				$resolved[ $key ] = EtchParser::process_expression( $value, $context );
			} else {
				$resolved[ $key ] = $value;
			}
		}

		return $resolved;
	}
}
