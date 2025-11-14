<?php
/**
 * StylesRoutes.php
 *
 * This file contains the StylesRoutes class which defines REST API routes for handling styles.
 *
 * PHP version 7.4+
 *
 * @category  Plugin
 * @package   Etch\RestApi\Routes
 */

declare(strict_types=1);
namespace Etch\RestApi\Routes;

use Etch\Services\StylesheetService;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
/**
 * StylesRoutes
 *
 * This class defines REST API endpoints for retrieving and updating styles.
 *
 * @package Etch\RestApi\Routes
 */
class StylesRoutes extends BaseRoute {

	/**
	 * Option name where styles are stored.
	 *
	 * @var string
	 */
	private $styles_option_name = 'etch_styles';


	/**
	 * Instance of the StylesheetService.
	 *
	 * @var StylesheetService
	 */
	private $stylesheet_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->stylesheet_service = StylesheetService::get_instance();
	}


	/**
	 * Returns the route definitions for styles endpoints.
	 *
	 * @return array<array{route: string, methods: string|array<string>, callback: callable, permission_callback?: callable, args?: array<string, mixed>}>
	 */
	protected function get_routes(): array {
		return array(
			array(
				'route'               => '/styles',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_styles' ),
				'permission_callback' => '__return_true',
			),
			array(
				'route'               => '/styles',
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_styles' ),
				'permission_callback' => function () {
					return $this->check_permission( 'manage_options' );
				},
			),
			// Stylesheet routes.
			array(
				'route'               => '/stylesheets',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_stylesheets' ),
				'permission_callback' => function () {
					return $this->check_permission( 'manage_options' );
				},
			),
			array(
				'route'               => '/stylesheets',
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_stylesheet' ),
				'permission_callback' => function () {
					return $this->check_permission( 'manage_options' );
				},
			),
			array(
				'route'               => '/stylesheets',
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_stylesheets' ),
				'permission_callback' => function () {
					return $this->check_permission( 'manage_options' );
				},
			),
			array(
				'route'               => '/stylesheets/(?P<id>[\w-]+)',
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_stylesheet' ),
				'permission_callback' => function () {
					return $this->check_permission( 'manage_options' );
				},
			),
			array(
				'route'               => '/stylesheets/(?P<id>[\w-]+)',
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_stylesheet' ),
				'permission_callback' => function () {
					return $this->check_permission( 'manage_options' );
				},
			),
		);
	}

	/**
	 * Update Etch styles.
	 * This function updates the Etch styles in the database.
	 *
	 * @param WP_REST_Request<array{}> $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_styles( $request ) {

		$new_styles = json_decode( $request->get_body(), true );
		if ( ! is_array( $new_styles ) ) {
			return new WP_Error( 'invalid_styles', 'Styles must be provided as an array', array( 'status' => 400 ) );
		}
		$existing_styles = get_option( $this->styles_option_name, array() );
		$update_result = true;
		if ( $new_styles !== $existing_styles ) {
			$update_result = update_option( $this->styles_option_name, $new_styles );
		}

		if ( $update_result ) {
			return new WP_REST_Response( array( 'message' => 'Styles updated successfully' ), 200 );
		} else {
			return new WP_Error( 'update_failed', 'Failed to update styles', array( 'status' => 500 ) );
		}
	}

	/**
	 * Get Etch styles.
	 *
	 * @return WP_REST_Response Response object with styles.
	 */
	public function get_styles() {
		$styles = get_option( $this->styles_option_name, array() );

		// Ensure object is returned even if no styles are set (avoid returning an empty array).
		// TODO - maybe discuss this stuff with Matteo if there is a better way to handle this. And if there may be any issues when handling it this way.
		return new WP_REST_Response( (object) $styles, 200 );
	}


	// Global Stylesheet Routes

	/**
	 * Get all global style sheets.
	 *
	 * @return WP_REST_Response Response object with global style sheets.
	 */
	public function get_stylesheets() {
		$stylesheets = $this->stylesheet_service->get_stylesheets();
		return new WP_REST_Response( (object) $stylesheets, 200 );
	}

	/**
	 * Update multiple global style sheets.
	 *
	 * @param WP_REST_Request<array{}> $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_stylesheets( $request ) {
		$new_styles = json_decode( $request->get_body(), true );
		if ( ! is_array( $new_styles ) ) {
			return new WP_Error( 'invalid_styles', 'Styles must be provided as an array', array( 'status' => 400 ) );
		}

		$this->stylesheet_service->update_stylesheets( $new_styles );
		return new WP_REST_Response( array( 'message' => 'Stylesheets updated successfully' ), 200 );
	}

	/**
	 * Create a new global style sheet.
	 *
	 * @param WP_REST_Request<array{}> $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_stylesheet( $request ) {
		$body = json_decode( $request->get_body(), true );
		if ( ! is_array( $body ) || ! is_string( $body['name'] ) || ! is_string( $body['css'] ) ) {
			return new WP_Error( 'invalid_data', 'Invalid data provided', array( 'status' => 400 ) );
		}

		$id = $this->stylesheet_service->create_stylesheet( $body );

		if ( is_wp_error( $id ) ) {
			return $id; // Return the WP_Error directly if creation failed.
		}

		return new WP_REST_Response(
			array(
				'id' => $id,
				'message' => 'Stylesheet created successfully',
			),
			201
		);
	}

	/**
	 * Update a specific global style sheet by ID.
	 *
	 * @param WP_REST_Request<array{id: string}> $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_stylesheet( $request ) {
		$id = $request->get_param( 'id' );

		if ( ! $id || ! is_string( $id ) ) {
			return new WP_Error( 'invalid_id', 'No valid stylesheet id provided', array( 'status' => 400 ) );
		}

		$body = json_decode( $request->get_body(), true );

		if ( ! is_array( $body ) || ! is_string( $body['name'] ) || ! is_string( $body['css'] ) ) {
			return new WP_Error( 'invalid_data', 'Invalid data provided', array( 'status' => 400 ) );
		}

		$result = $this->stylesheet_service->update_stylesheet( $id, $body );
		if ( is_wp_error( $result ) ) {
			return $result; // Return the WP_Error directly if update failed.
		}

		return new WP_REST_Response( array( 'message' => 'Stylesheet updated successfully' ), 200 );
	}

	/**
	 * Delete a specific global style sheet by ID.
	 *
	 * @param WP_REST_Request<array{id: string}> $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_stylesheet( $request ) {
		$id = $request->get_param( 'id' );

		if ( ! $id || ! is_string( $id ) ) {
			return new WP_Error( 'invalid_id', 'No valid stylesheet id provided', array( 'status' => 400 ) );
		}

		$result = $this->stylesheet_service->delete_stylesheet( $id );

		if ( is_wp_error( $result ) ) {
			return $result; // Return the WP_Error directly if deletion failed.
		}

		return new WP_REST_Response( array( 'message' => 'Stylesheet deleted successfully' ), 200 );
	}
}
