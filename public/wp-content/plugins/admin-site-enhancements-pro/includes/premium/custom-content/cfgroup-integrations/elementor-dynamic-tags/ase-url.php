<?php
namespace ASENHA\Integrations\Elementor;

use ASENHA\Classes\Common_Methods;
use ASENHA\Integrations\Elementor\Ase_Elementor_Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Dynamic Tag - ASE fields with text/HTML return values
 *
 * @since 6.8.3
 */
class Ase_Url extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get dynamic tag name.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return string Dynamic tag name.
	 */
	public function get_name() {
		return 'ase-url';
	}

	/**
	 * Get dynamic tag title.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return string Dynamic tag title.
	 */
	public function get_title() {
		return 'ASE URL Field';
	}

	/**
	 * Get dynamic tag groups.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return array Dynamic tag groups.
	 */
	public function get_group() {
		return [ 'ase' ];
	}

	/**
	 * Get dynamic tag categories.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return array Dynamic tag categories.
	 */
	public function get_categories() {
		return [ 
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];
	}

	/**
	 * Register dynamic tag controls.
	 *
	 * Add input fields to allow the user to choose which ASE field to render.
	 *
	 * @since 6.8.3
	 * @access protected
	 * @return void
	 */
	protected function register_controls() {
		$applicable_field_types = array(
			'text',
			'hyperlink',
			'file',
			'relationship',
		);			

		$ase_elementor_integration = new Ase_Elementor_Integration;
		$cf_groups = $ase_elementor_integration->get_control_options( 'url', $applicable_field_types );

		// Field selection
		$this->add_control(
			'key',
			[
				'label' => 'Field Name',
				'type' => \Elementor\Controls_Manager::SELECT,
				'groups' => $cf_groups,
			]
		);

		// File (image) field -- output type selection
		$this->add_control(
			'file_image_size',
			[
				'label' => 'Image size',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'thumbnail' 	=> 'Thumbnail',
					'medium' 		=> 'Medium',
					'medium_large' 	=> 'Medium Large',
					'large' 		=> 'Large',
					'full' 			=> 'Full size',
				],
				'default'	=> 'medium_large',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__file__image',
						]
					],
				],
			]
		);
	}

	/**
	 * Render tag output on the frontend.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return void
	 */
	public function render() {
		$field_key = $this->get_settings( 'key' );
		$field_key_parts = explode( '__', $field_key );

		$field_name = $field_key_parts[0];
		$field_type = $field_key_parts[1];
		$field_subtype = $field_key_parts[2];

		$custom_fallback = $this->get_settings( 'true_false_output' );
		
		// Set the fallback value
		if ( ! empty( $custom_fallback ) ) {
			$field_value = $custom_fallback;
		} else {
			$field_value = get_the_permalink();
		}
				
		$raw_value = get_cf( $field_name, 'raw' );

		switch ( $field_type ) {
			case 'text':
				if ( filter_var( $raw_value, FILTER_VALIDATE_URL ) && false !== strpos( $raw_value, 'http' ) ) {
					$field_value = $raw_value;
				}
				break;

			case 'hyperlink':
        		if ( is_array( $raw_value ) && ! empty( $raw_value ) && isset( $raw_value['url'] ) ) {
        			$field_value = $raw_value['url'];
        		}
        		break;

			case 'file':
				if ( ! empty( $raw_value ) ) {
					switch ( $field_subtype ) {
						case 'image':
							$image_size = $this->get_settings( 'file_image_size' );
							$image_size = ! empty( $image_size ) ? $image_size : 'medium_large';
							$field_value = wp_get_attachment_image_url( $raw_value, $image_size );
							break;
						case 'video':
						case 'audio':
						case 'pdf':
						case 'any':
			        		$field_value = wp_get_attachment_url( $raw_value );
							break;
					}					
				}
				break;

			case 'relationship':
				if ( is_array( $raw_value ) && count( $raw_value ) > 0 ) {
					$first_related_item_id = $raw_value[0];
					$field_value = get_the_permalink( $first_related_item_id );
				}
				break;
		}

		echo wp_kses_post( $field_value );
	}

}