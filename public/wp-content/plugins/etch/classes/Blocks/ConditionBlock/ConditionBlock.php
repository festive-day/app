<?php
/**
 * Condition Block
 *
 * Renders inner blocks conditionally based on evaluated condition.
 * Returns empty string if condition is falsy, otherwise renders children.
 *
 * @package Etch
 */

namespace Etch\Blocks\ConditionBlock;

use Etch\Blocks\Types\ConditionAttributes;
use Etch\Blocks\Global\ScriptRegister;
use Etch\Blocks\Global\ContextProvider;
use Etch\Blocks\ConditionBlock\ConditionEvaluator;

/**
 * ConditionBlock class
 *
 * Handles rendering of etch/condition blocks with conditional content display.
 * Evaluates conditions using dynamic context and renders inner blocks only if condition is truthy.
 */
class ConditionBlock {

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
			'etch/condition',
			array(
				'api_version' => '3',
				'attributes' => array(
					'condition' => array(
						'type' => 'object',
						'default' => null,
					),
					'conditionString' => array(
						'type' => 'string',
						'default' => '',
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
	 * Evaluates the condition and returns inner blocks content if condition is truthy,
	 * otherwise returns empty string.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @param string               $content Block content (inner blocks).
	 * @param \WP_Block|null       $block WP_Block instance (contains context).
	 * @return string Rendered block HTML or empty string.
	 */
	public function render_block( array $attributes, string $content = '', $block = null ): string {
		$attrs = ConditionAttributes::from_array( $attributes );

		ScriptRegister::register_script( $attrs );

		// If no condition, render children (default behavior)
		if ( null === $attrs->condition ) {
			return $content;
		}

		// Get context - this builds global context and component context (via self::$current_component_block)
		// ComponentBlock sets self::$current_component_block before rendering inner blocks
		$context = ContextProvider::get_context_for_block( $block );

		// Evaluate the condition using the helper class (inspired by old Preprocessor approach)
		if ( ! ConditionEvaluator::evaluate_condition( $attrs->condition, $context ) ) {
			return '';
		}

		// Condition is truthy, render children
		return $content;
	}
}
