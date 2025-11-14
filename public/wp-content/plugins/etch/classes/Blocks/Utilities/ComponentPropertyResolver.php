<?php
/**
 * Component Property Resolver
 *
 * Utility class for resolving component properties from definitions and instance attributes.
 *
 * @package Etch\Blocks\Utilities
 */

namespace Etch\Blocks\Utilities;

use Etch\Blocks\Types\ComponentProperty;
use Etch\Blocks\Utilities\EtchTypeAsserter;
use Etch\Preprocessor\Utilities\EtchParser;
use Etch\Preprocessor\Utilities\LoopHandlerManager;

/**
 * ComponentPropertyResolver class
 *
 * Handles resolution of component properties by merging defaults with instance attributes.
 * Supports specialized types (e.g., loop arrays) and dynamic expression evaluation.
 */
class ComponentPropertyResolver {

	/**
	 * Resolve component properties from property definitions and instance attributes.
	 *
	 * @param array<int|string, mixed> $property_definitions Array of property definitions from pattern.
	 * @param array<string, mixed>     $instance_attributes  Instance attributes from component block.
	 * @param array<string, mixed>     $context              Context for dynamic expression evaluation.
	 * @return array<string, mixed> Resolved properties array.
	 */
	public static function resolve_properties( array $property_definitions, array $instance_attributes, array $context = array() ): array {
		$resolved_props = array();

		// First, build a map of ComponentProperty instances by key
		$property_map = array();
		foreach ( $property_definitions as $prop_data ) {
			// Ensure prop_data is an array before passing to from_array
			if ( ! is_array( $prop_data ) ) {
				continue;
			}
			$property = ComponentProperty::from_array( $prop_data );
			if ( null !== $property ) {
				$property_map[ $property->key ] = $property;
			}
		}

		// Start with defaults from property definitions
		foreach ( $property_map as $key => $property ) {
			$default_value = $property->default;
			$primitive = $property->get_primitive();

			// Guard against recursion: early return empty value if default contains {props.}
			if ( is_string( $default_value ) && strpos( $default_value, '{props.' ) !== false ) {
				$resolved_props[ $key ] = self::get_empty_value_for_type( $primitive );
				continue;
			}

			// Evaluate dynamic expressions in default value if context is available
			if ( ! empty( $context ) && is_string( $default_value ) ) {
				$default_value = EtchParser::type_safe_replacement( $default_value, $context );
			}

			// Handle specialized array type (loop props)
			if ( $property->is_specialized_array() ) {
				$resolved_props[ $key ] = self::resolve_array_property_value( $default_value, $context );
			} else {
				// Cast default to appropriate type
				$resolved_props[ $key ] = self::cast_to_type( $default_value, $primitive, $context );
			}
		}

		// Override with instance attributes, applying type casting
		foreach ( $instance_attributes as $key => $value ) {
			// Only process attributes that have property definitions
			if ( ! isset( $property_map[ $key ] ) ) {
				continue;
			}

			$property = $property_map[ $key ];
			$primitive = $property->get_primitive();

			// Evaluate dynamic expressions in instance value if context is available
			if ( ! empty( $context ) ) {
				$value = EtchParser::type_safe_replacement( $value, $context );
			}

			// Handle specialized array type (loop props)
			if ( $property->is_specialized_array() ) {
				$resolved_props[ $key ] = self::resolve_array_property_value( $value, $context );
			} else {
				$resolved_props[ $key ] = self::cast_to_type( $value, $primitive, $context );
			}
		}

		return $resolved_props;
	}

	/**
	 * Resolve array property value from global loops or context expressions.
	 * Handles specialized "array" type (loop props) by checking loop presets and context.
	 *
	 * @param mixed                $value   The property value to resolve.
	 * @param array<string, mixed> $context Context for dynamic expression evaluation.
	 * @return array<mixed> The resolved array data.
	 */
	private static function resolve_array_property_value( $value, array $context ): array {
		// If already an array, return as is
		if ( is_array( $value ) ) {
			return $value;
		}

		// Convert to string for processing
		$string_value = EtchTypeAsserter::to_string( $value );

		// If empty, return empty array
		if ( empty( $string_value ) ) {
			return array();
		}

		// Check if it's a global loop key (by database key first)
		$loop_presets = LoopHandlerManager::get_loop_presets();
		if ( isset( $loop_presets[ $string_value ] ) ) {
			return LoopHandlerManager::get_loop_preset_data( $string_value, array() );
		}

		// Check if it matches any loop by key property
		$found_loop_id = LoopHandlerManager::find_loop_by_key( $string_value );
		if ( $found_loop_id ) {
			return LoopHandlerManager::get_loop_preset_data( $found_loop_id, array() );
		}

		// Try to parse as context expression using EtchParser
		if ( ! empty( $context ) ) {
			$parsed = EtchParser::type_safe_replacement( $string_value, $context );
			if ( is_array( $parsed ) ) {
				return $parsed;
			}
		}

		// Fall back to JSON decode or comma-separated parsing
		return EtchTypeAsserter::to_array( $string_value );
	}

	/**
	 * Get empty value for a given primitive type.
	 *
	 * @param string $primitive The primitive type.
	 * @return mixed Empty value for the type.
	 */
	private static function get_empty_value_for_type( string $primitive ) {
		switch ( $primitive ) {
			case 'string':
				return '';
			case 'number':
				return 0;
			case 'boolean':
				return false;
			case 'array':
			case 'object':
				return array();
			default:
				return '';
		}
	}

	/**
	 * Cast a value to the correct primitive type.
	 *
	 * @param mixed                $value     The value to cast.
	 * @param string               $primitive  The primitive type (string, number, boolean, object, array).
	 * @param array<string, mixed> $context   Context for dynamic expression evaluation (optional).
	 * @return mixed The cast value.
	 */
	private static function cast_to_type( $value, string $primitive, array $context = array() ) {
		// Handle null/empty defaults
		if ( null === $value || '' === $value ) {
			return self::get_empty_value_for_type( $primitive );
		}

		switch ( $primitive ) {
			case 'string':
				return EtchTypeAsserter::to_string( $value );

			case 'number':
				return EtchTypeAsserter::to_number( $value );

			case 'boolean':
				return EtchTypeAsserter::to_bool( $value );

			case 'array':
			case 'object':
				return EtchTypeAsserter::to_array( $value );

			default:
				return $value;
		}
	}
}
