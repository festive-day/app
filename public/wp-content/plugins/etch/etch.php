<?php
/**
 * Etch
 *
 * @package           Etch
 * @author            Digital Gravy
 * @copyright         2024 Digital Gravy
 * @gplv2
 *
 * @wordpress-plugin
 * Plugin Name:       Etch
 * Plugin URI:        https://etchwp.com
 * Description:       Your unified development environment for WordPress.
 * Version:           1.0.0-alpha-10
 * Requires at least: 5.9
 * Requires PHP:      8.1
 * Author:            Digital Gravy
 * Author URI:        https://digitalgravy.co
 * Text Domain:       etch
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

declare(strict_types=1);

use Etch\Helpers\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define plugin directories and urls.
 */
define( 'ETCH_PLUGIN_FILE', __FILE__ );
define( 'ETCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ETCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Initialize the plugin.
 */
require_once ETCH_PLUGIN_DIR . '/vendor/autoload.php';
\Etch\Plugin::get_instance()->init();

/**
 * Run the plugin.
 *
 * @return void
 */
function etch_run_plugin() {
	\Etch\Plugin::get_instance()->run();
}
add_action( 'plugins_loaded', 'etch_run_plugin' );

/**
 * Initialize default loop presets for Etch plugin.
 *
 * @return void
 */
function init_etch_loop_presets() {
	$default_presets = array(
		'etch1r1' => array(
			'name' => 'Basic Nav',
			'key' => 'basicNav',
			'global' => true,
			'config' => array(
				'type' => 'json',
				'data' => array(
					array(
						'label' => 'Home',
						'url' => '/',
					),
					array(
						'label' => 'Item 2',
						'children' => array(
							array(
								'label' => 'Item 2.1',
								'url' => '/dropdown1',
							),
							array(
								'label' => 'Item 2.2',
								'url' => '/dropdown2',
							),
							array(
								'label' => 'Item 2.3',
								'url' => '/dropdown3',
							),
						),
					),
					array(
						'label' => 'Item 3',
						'url' => '/page',
						'children' => array(
							array(
								'label' => 'Item 3.1',
								'url' => '/dropdown1',
							),
							array(
								'label' => 'Item 3.2',
								'url' => '/dropdown2',
							),
							array(
								'label' => 'Item 3.3',
								'url' => '/dropdown3',
							),
							array(
								'label' => 'Item 3.4',
								'url' => '/dropdown4',
							),
						),
					),
					array(
						'label' => 'Item 4',
						'url' => '/about',
					),
					array(
						'label' => 'Item 5',
						'url' => '/contact-us',
					),
				),
			),
		),
		'k7mrbkq' => array(
			'name' => 'Posts',
			'key' => 'posts',
			'global' => true,
			'config' => array(
				'type' => 'wp-query',
				'args' => array(
					'post_type' => 'post',
					'posts_per_page' => 10,
					'orderby' => 'date',
					'order' => 'DESC',
					'post_status' => 'publish',
				),
			),
		),
		'k5rb2t1' => array(
			'name' => 'Simple JSON',
			'key' => 'simple_json',
			'global' => true,
			'config' => array(
				'type' => 'json',
				'data' => array(
					array(
						'title' => 'Post 1',
						'content' => 'This is the content of post 1',
					),
					array(
						'title' => 'Post 2',
						'content' => 'This is the content of post 2',
					),
				),
			),
		),
	);

	$option_name = 'etch_loops';

	$existing_loops = get_option( $option_name, array() );
	if ( ! is_array( $existing_loops ) ) {
		$existing_loops = array();
	}

	$merged_presets = array_merge( $default_presets, $existing_loops );

	update_option( 'etch_loops', $merged_presets );
}


/**
 * Migrates old Etch components to WordPress block patterns (wp_block post type).
 *
 * @return void
 */
function migrate_old_components_to_patterns() {
	$existing_components = get_option( 'etch_components', array() );

	// early return on empty
	if ( empty( $existing_components ) || ! is_array( $existing_components ) ) {
		return;
	}

	// create pattern for every existing component
	foreach ( $existing_components as $component_id => $component_data ) {
		$existing_pattern_query = new WP_Query(
			array(
				'post_type'      => 'wp_block',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => 'etch_component_legacy_id',
						'value'   => $component_id,
						'compare' => '=',
					),
				),
				'fields' => 'ids',
			)
		);

		if ( $existing_pattern_query->have_posts() ) {
			Logger::log( 'Pattern for component ID ' . $component_id . ' already exists, skipping.' );
			continue;
		}

		$post_data = array(
			'post_type'    => 'wp_block',
			'post_title'   => sanitize_text_field( $component_data['name'] ?? 'New Pattern' ),
			'post_content' => wp_slash( serialize_blocks( $component_data['blocks'] ?? array() ) ),
			'post_excerpt' => sanitize_text_field( $component_data['description'] ?? '' ),
			'post_status'  => 'publish',
		);

		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			Logger::log( 'Failed to create pattern for component ID ' . $component_id );
			continue;
		}

		if ( isset( $component_data['properties'] ) ) {
			update_post_meta( $post_id, 'etch_component_properties', $component_data['properties'] );
		}

		if ( isset( $component_data['key'] ) ) {
			update_post_meta( $post_id, 'etch_component_html_key', $component_data['key'] );
		}

				update_post_meta( $post_id, 'etch_component_legacy_id', $component_id );

	}

		// delete old components option
	delete_option( 'etch_components' );
}

// function reset_custom_block_migration_flag() {
// $settings = get_option( 'etch_settings', array() );
// if ( ! is_array( $settings ) ) {
// $settings = array();
// }
// $settings['custom_block_migration_completed'] = false;
// update_option( 'etch_settings', $settings );
// }
// add_action( 'init', 'reset_custom_block_migration_flag', 11 );

add_action( 'init', 'init_etch_loop_presets', 9 );
add_action( 'init', 'migrate_old_components_to_patterns', 10 );
