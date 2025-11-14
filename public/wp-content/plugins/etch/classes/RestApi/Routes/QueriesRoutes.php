<?php
/**
 * QueriesRoutes.php
 *
 * This file contains the QueriesRoutes class which defines REST API routes for handling queries.
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
use Etch\Traits\DynamicData;

/**
 * QueriesRoutes
 *
 * This class defines REST API endpoints for retrieving and updating query options.
 *
 * @package Etch\RestApi\Routes
 */
class QueriesRoutes extends BaseRoute {
	use DynamicData;

	/**
	 * Returns the route definitions for queries endpoints.
	 *
	 * @return array<array{
	 *     route: string,
	 *     methods: string|array<string>,
	 *     callback: callable,
	 *     permission_callback?: callable,
	 *     args?: array<string, array{required?: bool, type?: string}>
	 * }>
	 */
	protected function get_routes(): array {
		return array(
			array(
				'route'               => '/queries',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_queries' ),
				'permission_callback' => '__return_true',
			),
			array(
				'route'               => '/queries',
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_queries' ),
				'permission_callback' => function () {
					return $this->check_permission( 'manage_options' );
				},
			),
			array(
				'route'               => '/queries/wp-query',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_wp_query_results' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'query_args' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			),
			array(
				'route'               => '/queries/wp-terms',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_wp_terms_results' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'query_args' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			),
			array(
				'route'               => '/queries/wp-users',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_wp_users_results' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'query_args' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			),
		);
	}

	/**
	 * Update Etch queries.
	 * This function updates the Etch queries in the database.
	 *
	 * @param WP_REST_Request<array{}> $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_queries( $request ): WP_REST_Response|WP_Error {
		$new_queries = json_decode( $request->get_body(), true );
		if ( ! is_array( $new_queries ) ) {
			return new WP_Error( 'invalid_queries', 'Queries must be provided as an array', array( 'status' => 400 ) );
		}

		$option_name = 'etch_queries';

		$existing_queries = get_option( $option_name, array() );
		$update_result = true;
		if ( $new_queries !== $existing_queries ) {
			$update_result = update_option( $option_name, $new_queries );
		}

		if ( $update_result ) {
			return new WP_REST_Response( array( 'message' => 'Queries updated successfully' ), 200 );
		} else {
			return new WP_Error( 'update_failed', 'Failed to update queries', array( 'status' => 500 ) );
		}
	}

	/**
	 * Get Etch queries.
	 *
	 * @return WP_REST_Response Response object with queries.
	 */
	public function get_queries(): WP_REST_Response {
		$option_name = 'etch_queries';
		$queries = get_option( $option_name, array() );

		// Ensure object is returned even if no queries are set (avoid returning an empty array).
		return new WP_REST_Response( (object) $queries, 200 );
	}

	/**
	 * Get WP query results based on provided arguments.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 *
	 * @phpstan-param WP_REST_Request<array<string,mixed>> $request
	 */
	public function get_wp_query_results( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$query_args = $request->get_param( 'query_args' );

		// Convert to array if it's a JSON string
		if ( is_string( $query_args ) ) {
			$query_args = json_decode( $query_args, true );
		}

		if ( empty( $query_args ) || ! is_array( $query_args ) ) {
			return new WP_Error(
				'invalid_query_args',
				'Query arguments must be provided as an array',
				array( 'status' => 400 )
			);
		}

		// Run the query
		$query = new \WP_Query( $query_args );

		/**
		 * Posts.
		 *
		 * @var array<int,array<string,mixed>>
		 */
		$posts = array();

		// Build the response
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post = get_post();

				if ( ! $post instanceof \WP_Post ) {
					continue;
				}

				$posts[] = $this->get_dynamic_data( $post );
			}
			wp_reset_postdata();
		}

		$response = new WP_REST_Response(
			array(
				'data'  => $posts,
				'total' => $query->found_posts,
				'pages' => $query->max_num_pages,
			),
			200
		);

		// Set caching headers
		$this->set_caching_headers( 30, $response );

		return $response;
	}

	/**
	 * Get WP terms results based on provided arguments.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 *
	 * @phpstan-param WP_REST_Request<array<string,mixed>> $request
	 */
	public function get_wp_terms_results( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$query_args = $request->get_param( 'query_args' );

		// Convert to array if it's a JSON string
		if ( is_string( $query_args ) ) {
			$query_args = json_decode( $query_args, true );
		}

		if ( empty( $query_args ) || ! is_array( $query_args ) ) {
			return new WP_Error(
				'invalid_query_args',
				'Query arguments must be provided as an array',
				array( 'status' => 400 )
			);
		}

		$query = new \WP_Term_Query( $query_args );

		/**
		 * Terms.
		 *
		 * @var array<\WP_Term>
		 */
		$terms = $query->get_terms();

		foreach ( $terms as $index  => $term ) {
			$terms[ $index ] = $this->get_dynamic_term_data( $term );
		}

		$response = new WP_REST_Response(
			array(
				'data'  => $terms,
			),
			200
		);

		// Set caching headers
		$this->set_caching_headers( 30, $response );

		return $response;
	}

	/**
	 * Get WP users results based on provided arguments.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 *
	 * @phpstan-param WP_REST_Request<array<string,mixed>> $request
	 */
	public function get_wp_users_results( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$query_args = $request->get_param( 'query_args' );

		if ( is_string( $query_args ) ) {
			$query_args = json_decode( $query_args, true );
		}

		if ( empty( $query_args ) || ! is_array( $query_args ) ) {
			return new WP_Error(
				'invalid_query_args',
				'Query arguments must be provided as an array',
				array( 'status' => 400 )
			);
		}

		$query = new \WP_User_Query( $query_args );

		$users = $query->get_results();

		foreach ( $users as $index  => $user ) {
			$users[ $index ] = $this->get_dynamic_user_data( $user );
		}

		$response = new WP_REST_Response(
			array(
				'data'  => $users,
			),
			200
		);

		// Set caching headers
		$this->set_caching_headers( 30, $response );

		return $response;
	}
}
