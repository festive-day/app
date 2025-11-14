<?php
/**
 * Dependency Injection Container.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Helpers;

/**
 * Simple DI Container for managing dependencies.
 */
class Container {
	/**
	 * The singleton instance.
	 *
	 * @var Container|null
	 */
	private static $instance = null;

	/**
	 * The container's bindings.
	 *
	 * @var array
	 */
	private $bindings = array();

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {}

	/**
	 * Get the singleton instance.
	 *
	 * @return Container
	 */
	public static function get_instance(): Container {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Bind a value to the container.
	 *
	 * @param string $id The identifier.
	 * @param mixed  $value The value to bind.
	 * @return void
	 */
	public function bind( string $id, $value ): void {
		$this->bindings[ $id ] = $value;
	}

	/**
	 * Get a value from the container.
	 *
	 * @param string $id The identifier.
	 * @return mixed
	 * @throws \Exception If the binding doesn't exist.
	 */
	public function get( string $id ) {
		if ( ! isset( $this->bindings[ $id ] ) ) {
			throw new \Exception( esc_html( "No binding found for {$id}" ) );
		}
		return $this->bindings[ $id ];
	}

	/**
	 * Check if a binding exists.
	 *
	 * @param string $id The identifier.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->bindings[ $id ] );
	}
}
