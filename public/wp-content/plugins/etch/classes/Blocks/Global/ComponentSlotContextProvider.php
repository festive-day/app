<?php
/**
 * Component Slot Context Provider
 *
 * Manages slot content maps and parent context for component rendering.
 * Uses a stack-based approach to handle nested components with slots.
 *
 * @package Etch\Blocks\Global
 */

namespace Etch\Blocks\Global;

/**
 * ComponentSlotContextProvider class
 *
 * Handles slot content mapping and parent context for component blocks.
 * This is separate from ContextProvider to keep responsibilities clear:
 * - ContextProvider: dynamic/global context (props, site, user, etc.)
 * - ComponentSlotContextProvider: slot content maps and parent context for slots
 */
class ComponentSlotContextProvider {

	/**
	 * Stack of component slot contexts.
	 * Each entry contains: ['slots' => array, 'parent_context' => array, 'component_block' => WP_Block]
	 *
	 * @var array<int, array{slots: array<string, array<int, array<string, mixed>>>, parent_context: array<string, mixed>, component_block: \WP_Block}>
	 */
	private static array $context_stack = array();

	/**
	 * Push a new component slot context onto the stack.
	 *
	 * @param array<string, array<int, array<string, mixed>>> $slots_map Slot name => array of parsed block data.
	 * @param array<string, mixed>                            $parent_context Parent context (from before component processing).
	 * @param \WP_Block                                       $component_block The component block instance.
	 * @return void
	 */
	public static function push( array $slots_map, array $parent_context, \WP_Block $component_block ): void {
		self::$context_stack[] = array(
			'slots'           => $slots_map,
			'parent_context'  => $parent_context,
			'component_block' => $component_block,
		);
	}

	/**
	 * Pop the most recent component slot context from the stack.
	 *
	 * @return void
	 */
	public static function pop(): void {
		array_pop( self::$context_stack );
	}

	/**
	 * Get the current slots map (from the top of the stack).
	 *
	 * @return array<string, array<int, array<string, mixed>>> Slot name => array of parsed block data.
	 */
	public static function current_slots(): array {
		if ( empty( self::$context_stack ) ) {
			return array();
		}

		$top_index = count( self::$context_stack ) - 1;
		$top = self::$context_stack[ $top_index ];

		/**
		 * Type assertion for stack top element.
		 *
		 * @var array{slots: array<string, array<int, array<string, mixed>>>, parent_context: array<string, mixed>, component_block: \WP_Block} $top
		 */
		return $top['slots'];
	}

	/**
	 * Get the current parent context (from the top of the stack).
	 *
	 * @return array<string, mixed> Parent context array.
	 */
	public static function current_parent_context(): array {
		if ( empty( self::$context_stack ) ) {
			return array();
		}

		$top_index = count( self::$context_stack ) - 1;
		$top = self::$context_stack[ $top_index ];

		/**
		 * Type assertion for stack top element.
		 *
		 * @var array{slots: array<string, array<int, array<string, mixed>>>, parent_context: array<string, mixed>, component_block: \WP_Block} $top
		 */
		return $top['parent_context'];
	}

	/**
	 * Get the current component block (from the top of the stack).
	 *
	 * @return \WP_Block|null The component block instance, or null if stack is empty.
	 */
	public static function current_component_block(): ?\WP_Block {
		if ( empty( self::$context_stack ) ) {
			return null;
		}

		$top_index = count( self::$context_stack ) - 1;
		$top = self::$context_stack[ $top_index ];

		/**
		 * Type assertion for stack top element.
		 *
		 * @var array{slots: array<string, array<int, array<string, mixed>>>, parent_context: array<string, mixed>, component_block: \WP_Block} $top
		 */
		return $top['component_block'];
	}
}
