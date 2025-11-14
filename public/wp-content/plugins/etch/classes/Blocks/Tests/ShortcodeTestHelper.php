<?php
/**
 * Shortcode Test Helper Trait
 *
 * Provides helper methods for testing shortcode resolution in Etch blocks.
 * Registers test-only shortcodes and provides integration testing via the_content filter.
 *
 * @package Etch\Blocks\Tests
 */

declare(strict_types=1);

namespace Etch\Blocks\Tests;

/**
 * Trait ShortcodeTestHelper
 *
 * Helper methods for shortcode testing in block tests.
 */
trait ShortcodeTestHelper {

	/**
	 * Register test shortcode in setUp
	 *
	 * @return void
	 */
	protected function register_test_shortcode(): void {
		add_shortcode(
			'etch_test_hello',
			function ( $atts ) {
				$atts = shortcode_atts(
					array(
						'name' => 'World',
					),
					$atts,
					'etch_test_hello'
				);

				return 'Hello ' . esc_html( $atts['name'] ) . '!';
			}
		);
	}

	/**
	 * Remove test shortcode in tearDown
	 *
	 * @return void
	 */
	protected function remove_test_shortcode(): void {
		remove_shortcode( 'etch_test_hello' );
	}

	/**
	 * Render content through WordPress the_content filter pipeline
	 * This simulates the full WordPress rendering pipeline including do_shortcode
	 *
	 * @param string $content Block content to render.
	 * @return string Rendered content with shortcodes processed.
	 */
	protected function render_through_content_filter( string $content ): string {
		return apply_filters( 'the_content', $content );
	}

	/**
	 * Create a post with block content and render through the_content filter
	 * Also explicitly processes shortcodes to ensure they work in attributes
	 *
	 * @param array<int|string, array<string, mixed>> $blocks Array of block data.
	 * @return string Rendered HTML content.
	 */
	protected function render_blocks_through_content_filter( array $blocks ): string {
		$serialized = serialize_blocks( $blocks );
		$post_id = $this->factory()->post->create(
			array(
				'post_content' => $serialized,
			)
		);

		$post = get_post( $post_id );
		$rendered = apply_filters( 'the_content', $post->post_content );

		// Decode Unicode entities that might have been encoded during JSON serialization
		// This handles cases where quotes become u0022, etc.
		$rendered = preg_replace_callback(
			'/\\\\u([0-9a-fA-F]{4})/',
			function ( $matches ) {
				return mb_convert_encoding( pack( 'H*', $matches[1] ), 'UTF-8', 'UCS-2BE' );
			},
			$rendered
		);

		// Explicitly process shortcodes to ensure they work in HTML attributes
		// the_content filter should handle this, but we ensure it's done
		$rendered = do_shortcode( $rendered );

		// Also process shortcodes in HTML attributes (do_shortcode doesn't process attributes by default)
		// Match attributes with shortcodes: attr="[shortcode]" or attr='[shortcode]'
		$rendered = preg_replace_callback(
			'/(\w+)=["\']([^"\']*\[[^\]]+\][^"\']*)["\']/',
			function ( $matches ) {
				$attr_name = $matches[1];
				$attr_value = $matches[2];
				$processed_value = do_shortcode( $attr_value );
				return $attr_name . '="' . esc_attr( $processed_value ) . '"';
			},
			$rendered
		);

		return $rendered;
	}
}
