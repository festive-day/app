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
class Ase_Color extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get dynamic tag name.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return string Dynamic tag name.
	 */
	public function get_name() {
		return 'ase-color';
	}

	/**
	 * Get dynamic tag title.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return string Dynamic tag title.
	 */
	public function get_title() {
		return 'ASE Color Field';
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
			\Elementor\Modules\DynamicTags\Module::COLOR_CATEGORY,
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
			'color',
		);

		$ase_elementor_integration = new Ase_Elementor_Integration;
		$cf_groups = $ase_elementor_integration->get_control_options( 'color', $applicable_field_types );

		// Field selection
		$this->add_control(
			'key',
			[
				'label' => 'Field Name',
				'type' => \Elementor\Controls_Manager::SELECT,
				'groups' => $cf_groups,
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
		// $field_type = $field_key_parts[1];
		// $field_subtype = $field_key_parts[2];
		$field_value = ! empty( get_cf( $field_name, 'default' ) ) ? get_cf( $field_name, 'default' ) : '#ffffff';

		echo wp_kses_post( $field_value );
	}

}