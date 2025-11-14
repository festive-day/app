<?php
/**
 * MediaRoutes.php
 *
 * This file contains the MediaRoutes class which defines REST API routes for handling media.
 *
 * @package Etch
 * @gplv2
 */

declare(strict_types=1);
namespace Etch\RestApi\Routes;

use Etch\Helpers\SvgLoader;
use WP_REST_Response;
use WP_Error;
use WP_REST_Request;

/**
 * MediaRoutes
 *
 * This class defines REST API endpoints for retrieving site information.
 *
 * @package Etch\RestApi\Routes
 */
class MediaRoutes extends BaseRoute {

	/**
	 * Get the routes for the media.
	 *
	 * @return array<array{
	 *     route: string,
	 *     methods: string,
	 *     callback: callable,
	 *     permission_callback: callable
	 * }> The routes for the media.
	 */
	protected function get_routes(): array {
		return array(
			array(
				'route'               => '/youtube/(?P<video_id>[a-zA-Z0-9_-]+)',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_youtube_video_info' ),
				'permission_callback' => '__return_true',
			),
			array(
				'route'               => '/attachment-sizes/(?P<attachment_id>[a-zA-Z0-9_-]+)',
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_attachment_sizes' ),
				'permission_callback' => '__return_true',
			),
			array(
				'route'               => '/svg',
				'methods'             => 'GET',
				'callback'            => array( $this, 'fetch_svg' ),
				'permission_callback' => '__return_true',
			),
		);
	}


	/**
	 * Get YouTube video information.
	 *
	 * @param WP_REST_Request<array{video_id: string}> $request The REST request object.
	 * @return WP_REST_Response|WP_Error The YouTube video information response or an error.
	 */
	public function get_youtube_video_info( $request ) {
		$video_id = (string) $request->get_param( 'video_id' );

		if ( empty( $video_id ) ) {
			return new WP_Error( 'invalid_video_id', __( 'Invalid video ID.', 'etch' ), array( 'status' => 400 ) );
		}

		// Construct the YouTube URL
		$youtube_url = 'https://www.youtube.com/watch?v=' . urlencode( $video_id );

		// Build the oEmbed URL
		$oembed_url = 'https://www.youtube.com/oembed?url=' . urlencode( $youtube_url ) . '&format=json';

		$response = file_get_contents( $oembed_url );

		if ( ! $response ) {
			return new WP_Error( 'youtube_api_error', __( 'Failed to retrieve video information.', 'etch' ), array( 'status' => 500 ) );
		}

		$video_info = json_decode( $response, true );
		return new WP_REST_Response( $video_info, 200 );
	}

	/**
	 * Get all images sizes for an attachment based on the url
	 *
	 * @param WP_REST_Request<array{attachment_id: string}> $request The REST request object.
	 * @return WP_REST_Response|WP_Error Array with all available sizes for an image or an error.
	 */
	public function get_attachment_sizes( $request ) {
		$attachment_id = (int) $request->get_param( 'attachment_id' );

		if ( empty( $attachment_id ) ) {
			return new WP_Error( 'invalid_atachment_id', __( 'Invalid attachment ID.', 'etch' ), array( 'status' => 400 ) );
		}

		$sizes = array();
		$images_sizes = array( 'full' );
		$image_metadata = wp_get_attachment_metadata( $attachment_id );

		if ( ! $image_metadata || empty( $image_metadata['sizes'] ) ) {
			return new WP_REST_Response( $sizes, 200 );
		}

		foreach ( $image_metadata['sizes'] as $available_image_size_name => $available_image_size ) {
			$images_sizes[] = $available_image_size_name;
		}

		foreach ( $images_sizes as $size_name ) {
			$image = wp_get_attachment_image_src( $attachment_id, $size_name );

			if ( ! $image ) {
				continue;
			}

			$srcset_attr = wp_get_attachment_image_srcset( $attachment_id, $size_name );
			$sizes_attr = wp_get_attachment_image_sizes( $attachment_id, $size_name );

			$sizes[] = array(
				'name' => $size_name,
				'width' => $image[1],
				'height' => $image[2],
				'url' => $image[0],
				'sizes' => (string) $sizes_attr,
				'srcset' => (string) $srcset_attr,
			);

		}
		return new WP_REST_Response( $sizes, 200 );
	}

	/**
	 * Fetch an SVG from a URL.
	 *
	 * @param WP_REST_Request<array{url: string}> $request The REST request object.
	 * @return WP_REST_Response|WP_Error The SVG markup response or an error.
	 */
	public function fetch_svg( $request ) {
		$url = (string) $request->get_param( 'url' );

		if ( empty( $url ) ) {
			return new WP_Error( 'invalid_url', __( 'Invalid URL.', 'etch' ), array( 'status' => 400 ) );
		}

		$svg = SvgLoader::get_remote_svg( $url );

		if ( is_wp_error( $svg ) ) {
			return $svg;
		}

		return new WP_REST_Response( $svg, 200 );
	}
}
