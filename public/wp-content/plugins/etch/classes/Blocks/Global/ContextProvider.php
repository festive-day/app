<?php
/**
 * Context Provider for Etch Blocks
 *
 * Provides dynamic context (component props, global contexts like site, this, term, etc.)
 * to all etch/ custom blocks.
 *
 * @package Etch\Blocks\Global
 */

namespace Etch\Blocks\Global;

use Etch\Blocks\Types\ComponentAttributes;
use Etch\Blocks\Utilities\ComponentPropertyResolver;
use Etch\Traits\DynamicData;
use WP_Term;

/**
 * ContextProvider class
 *
 * Handles providing dynamic context to etch/ blocks, including:
 * - Component properties from ComponentBlock instances
 * - Global contexts: site, url, options, user
 * - Current post context: this
 * - Taxonomy contexts: term, taxonomy (when on archive pages)
 */
class ContextProvider {
	use DynamicData;

	/**
	 * Current ComponentBlock being rendered (for context passing)
	 *
	 * @var \WP_Block|null
	 */
	private static $current_component_block = null;

	/**
	 * Stack of loop contexts pushed by LoopBlock during rendering.
	 * Each item is an associative array of context keys => values.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private static $loop_context_stack = array();

	/**
	 * Cached global context (built once per request)
	 *
	 * @var array<string, mixed>|null
	 */
	private static $cached_global_context = null;

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Build component context (props) if inside a ComponentBlock.
	 *
	 * @param \WP_Block|null       $parent_block The parent block (if any).
	 * @param array<string, mixed> $existing_context Existing context to merge props with.
	 * @return array<string, mixed> Component context array (contains 'props' key if applicable).
	 */
	private static function build_component_context( ?\WP_Block $parent_block, array $existing_context ): array {
		$component_context = array();

		// Get component block from parent or current static
		$component_block = null;
		if ( $parent_block instanceof \WP_Block && 'etch/component' === $parent_block->name ) {
			$component_block = $parent_block;
		} elseif ( self::$current_component_block instanceof \WP_Block ) {
			$component_block = self::$current_component_block;
		}

		if ( ! $component_block instanceof \WP_Block ) {
			return $component_context;
		}

		$component_attrs = $component_block->attributes ?? array();
		$attrs = ComponentAttributes::from_array( $component_attrs );

		if ( null === $attrs->ref ) {
			return $component_context;
		}

		$pattern_post = get_post( $attrs->ref );

		if ( ! $pattern_post || 'wp_block' !== $pattern_post->post_type ) {
			return $component_context;
		}

		$property_definitions = get_post_meta( $pattern_post->ID, 'etch_component_properties', true );
		if ( ! is_array( $property_definitions ) ) {
			$property_definitions = array();
		}

		$instance_attributes = $attrs->attributes ?? array();
		// Pass existing context to resolver for dynamic expression evaluation and loop resolution
		$resolved_props = ComponentPropertyResolver::resolve_properties( $property_definitions, $instance_attributes, $existing_context );

		$existing_props = is_array( $existing_context['props'] ?? null ) ? $existing_context['props'] : array();
		$component_context['props'] = array_merge( $existing_props, $resolved_props );

		return $component_context;
	}

	/**
	 * Build global context (site, url, options, user, this, term, taxonomy).
	 *
	 * Context is cached after first build to avoid repeated work.
	 *
	 * @return array<string, mixed> Global context array.
	 */
	private static function build_global_context(): array {
		if ( null !== self::$cached_global_context ) {
			return self::$cached_global_context;
		}

		$instance = new self();
		$global_context = array();

		$post = get_post();
		if ( null !== $post ) {
			$global_context['this'] = $instance->get_dynamic_data( $post );
		}

		$current_user = wp_get_current_user();
		if ( $current_user->exists() ) {
			$global_context['user'] = $instance->get_dynamic_user_data( $current_user );
		}

		$global_context['site'] = $instance->get_dynamic_site_data();
		$global_context['url'] = $instance->get_dynamic_url_data();
		$global_context['options'] = $instance->get_dynamic_option_pages_data();

		if ( is_tax() || is_category() || is_tag() ) {
			$queried_object = get_queried_object();
			if ( $queried_object instanceof WP_Term ) {
				$global_context['term'] = $instance->get_dynamic_term_data( $queried_object );
				$global_context['taxonomy'] = $instance->get_dynamic_tax_data( $queried_object->taxonomy );
			}
		}

		self::$cached_global_context = $global_context;

		return $global_context;
	}

	/**
	 * Push a loop context onto the stack.
	 *
	 * @param array<string, mixed> $context Context to push (e.g., ['item' => ..., 'index' => 0]).
	 * @return void
	 */
	public static function push_loop_context( array $context ): void {
		if ( empty( $context ) ) {
			return;
		}

		self::$loop_context_stack[] = $context;
	}

	/**
	 * Pop the most recent loop context from the stack.
	 *
	 * @return void
	 */
	public static function pop_loop_context(): void {
		array_pop( self::$loop_context_stack );
	}

	/**
	 * Combine all loop contexts in order (outer to inner) so inner values override outer ones.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_combined_loop_context(): array {
		if ( empty( self::$loop_context_stack ) ) {
			return array();
		}

		$combined = array();
		foreach ( self::$loop_context_stack as $ctx ) {
			if ( is_array( $ctx ) ) {
				$combined = array_merge( $combined, $ctx );
			}
		}

		return $combined;
	}

	/**
	 * Set the current ComponentBlock being rendered.
	 *
	 * This is used to track ComponentBlock instances when rendering pattern blocks,
	 * since render_block() doesn't preserve parent relationships.
	 *
	 * @param \WP_Block|null $block The ComponentBlock WP_Block instance.
	 * @return void
	 */
	public static function set_current_component_block( ?\WP_Block $block ): void {
		self::$current_component_block = $block;
	}

	/**
	 * Get the current ComponentBlock being rendered.
	 *
	 * @return \WP_Block|null
	 */
	public static function get_current_component_block(): ?\WP_Block {
		return self::$current_component_block;
	}

	/**
	 * Get context for a block in its render callback.
	 *
	 * This method can be called from any etch/ block's render callback to get
	 * the proper context. It checks if context can be passed from parent,
	 * otherwise generates it from scratch (using cached global context).
	 *
	 * Performance: Efficient because:
	 * - Global context is cached and built once per request
	 * - Parent context is reused when available (no duplicate work)
	 * - Component context is only built when inside a ComponentBlock
	 *
	 * @param \WP_Block|null $block The current block instance.
	 * @return array<string, mixed> The context array with global and component contexts.
	 */
	public static function get_context_for_block( ?\WP_Block $block ): array {
		$parent_block = null;
		if ( $block instanceof \WP_Block ) {
			$parent_block = $block->parent ?? null;
		}

		$initial_context = array();
		if ( $block instanceof \WP_Block ) {
			if ( $parent_block instanceof \WP_Block && is_array( $parent_block->context ) && ! empty( $parent_block->context ) ) {
				$initial_context = $parent_block->context;
			} elseif ( is_array( $block->context ) && ! empty( $block->context ) ) {
				$initial_context = $block->context;
			}
		}

		$has_global_context = isset( $initial_context['this'] ) || isset( $initial_context['site'] ) || isset( $initial_context['user'] );

		$global_context = array();
		if ( ! $has_global_context ) {
			$global_context = self::build_global_context();
		}

		$component_context = self::build_component_context( $parent_block, array_merge( $global_context, $initial_context ) );

		$context = array_merge( $global_context, $initial_context, $component_context );

		// Merge loop context last so the most recent loop values take precedence
		$loop_context = self::get_combined_loop_context();
		if ( ! empty( $loop_context ) ) {
			$context = array_merge( $context, $loop_context );
		}

		return $context;
	}
}
