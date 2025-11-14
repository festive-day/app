<?php
/**
 * UiRoutes.php
 *
 * This file contains the UiRoutes class which defines REST API routes for handling UI state.
 *
 * PHP version 7.4+
 *
 * @category  Plugin
 * @package   Etch\RestApi\Routes
 */

declare(strict_types=1);
namespace Etch\RestApi\Routes;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * UiRoutes
 *
 * This class defines REST API endpoints for retrieving and updating UI state.
 *
 * @package Etch\RestApi\Routes
 */
class UiRoutes extends BaseRoute {

	/**
	 * Returns the route definitions for UI state endpoints.
	 *
	 * @return array<array{route: string, methods: string|array<string>, callback: callable, permission_callback?: callable, args?: array<string, mixed>}>
	 */
	protected function get_routes(): array {
		return array(
			array(
				'route'               => '/ui/state',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_ui_state' ),
				'permission_callback' => '__return_true',
			),
			array(
				'route'               => '/ui/state',
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_ui_state' ),
				'permission_callback' => function () {
					return $this->check_permission( 'manage_options' );
				},
			),
		);
	}

	/**
	 * Get Etch UI State.
	 *
	 * @return WP_REST_Response Response object with ui state.
	 */
	public function get_ui_state() {
		$option_name = 'etch_ui_state';
		$ui_state = get_option( $option_name, array() );

		return new WP_REST_Response( (object) $ui_state, 200 );
	}

	/**
	 * Update Etch UI state.
	 * This function updates the Etch UI state in the database.
	 *
	 * @param WP_REST_Request<array{}> $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_ui_state( $request ) {

		$new_state = json_decode( $request->get_body(), true );
		if ( ! is_array( $new_state ) ) {
			return new WP_Error( 'invalid_ui_state', 'UI State must be provided as an object', array( 'status' => 400 ) );
		}

		$option_name = 'etch_ui_state';

		$existing_state = get_option( $option_name, array() );

		$update_result = true;
		if ( $new_state !== $existing_state ) {
			// We just overwrite the existing state with the new one
			$update_result = update_option( $option_name, $new_state );
		}

		if ( $update_result ) {
			return new WP_REST_Response( array( 'message' => 'UI State updated successfully' ), 200 );
		} else {
			return new WP_Error( 'update_failed', 'Failed to update UI State', array( 'status' => 500 ) );
		}
	}
}
