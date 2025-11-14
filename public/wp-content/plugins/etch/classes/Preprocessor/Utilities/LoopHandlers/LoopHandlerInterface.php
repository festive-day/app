<?php
/**
 * Interface for loop handlers in Etch.
 *
 * @package Etch
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Etch\Preprocessor\Utilities\LoopHandlers;

/**
 * Interface that all loop handlers must implement.
 */
abstract class LoopHandlerInterface {

	/**
	 * Get loop data for the specified query/preset name.
	 *
	 * @param string               $query_name The name of the query/loop preset.
	 * @param array<string, mixed> $loop_params The loop parameters.
	 * @return array<int, array<string, mixed>> Array of data items for the loop.
	 */
	abstract public function get_loop_data( string $query_name, array $loop_params = array() ): array;

	/**
	 * Replace loop parameters in a value.
	 *
	 * @param mixed                $value The value to replace loop parameters in.
	 * @param array<string, mixed> $loop_params The loop parameters.
	 * @return mixed The value with loop parameters replaced.
	 */
	protected function replace_loop_params_in_array( mixed $value, array $loop_params ): mixed {
		if ( ! is_array( $loop_params ) || empty( $loop_params ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			// On direct match, return the value from loopParams
			if ( array_key_exists( $value, $loop_params ) ) {
				return $loop_params[ $value ];
			}

			$result = $value;

			$sortedKeys = array_keys( $loop_params );
			usort(
				$sortedKeys,
				function ( $a, $b ) {
					return strlen( $b ) - strlen( $a );
				}
			);

			foreach ( $sortedKeys as $key ) {
				if ( strpos( $result, $key ) !== false ) {
					$arg_value = $loop_params[ $key ];
					if ( null === $arg_value || '' === $arg_value ) {
						$replacement = '';
					} elseif ( is_array( $arg_value ) || is_object( $arg_value ) ) {
						$encoded = json_encode( $arg_value );
						$replacement = false !== $encoded ? $encoded : '';
					} elseif ( is_scalar( $arg_value ) ) {
						$replacement = (string) $arg_value;
					} else {
						$replacement = '';
					}

					$result = str_replace( $key, $replacement, $result );
				}
			}
			return $result;
		}

		if ( is_array( $value ) ) {
			$result = array();
			foreach ( $value as $key => $val ) {
				$result[ $key ] = $this->replace_loop_params_in_array( $val, $loop_params );
			}
			return $result;
		}

		return $value; // If not a string, return as is
	}

	/**
	 * Get query arguments for the specified query name.
	 *
	 * @param string               $query_name The name of the query preset.
	 * @param array<string, mixed> $loop_params Additional parameters for the loop.
	 * @return array<string, mixed> Query arguments array.
	 */
	protected function get_query_args( string $query_name, array $loop_params ): array {
		$loop_presets = get_option( 'etch_loops', array() );

		if ( ! is_array( $loop_presets ) || ! isset( $loop_presets[ $query_name ]['config']['args'] ) ) {
			return array();
		}

		$query_args = $loop_presets[ $query_name ]['config']['args'];

		if ( ! is_array( $query_args ) ) {
			return array();
		}

		$replaced_args = $this->replace_loop_params_in_array( $query_args, $loop_params );
		return is_array( $replaced_args ) ? $replaced_args : array();
	}
}
