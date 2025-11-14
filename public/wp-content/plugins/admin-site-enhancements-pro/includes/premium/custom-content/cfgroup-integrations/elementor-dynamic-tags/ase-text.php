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
class Ase_Text extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get dynamic tag name.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return string Dynamic tag name.
	 */
	public function get_name() {
		return 'ase-text';
	}

	/**
	 * Get dynamic tag title.
	 *
	 * @since 6.8.3
	 * @access public
	 * @return string Dynamic tag title.
	 */
	public function get_title() {
		return 'ASE Field';
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
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
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
			'textarea',
			'wysiwyg',
			'color',
			'date',
			'time',
			'datetime',
			'hyperlink',
			'number',
			'true_false',
			'radio',
			'select',
			'checkbox',
			'file',
			'gallery',
			'relationship',
			'term',
			'user',
		);

		$ase_elementor_integration = new Ase_Elementor_Integration;
		$cf_groups = $ase_elementor_integration->get_control_options( 'text', $applicable_field_types );

		// Field selection
		$this->add_control(
			'key',
			[
				'label' => 'Field Name',
				'type' => \Elementor\Controls_Manager::SELECT,
				'groups' => $cf_groups,
			]
		);

		// Text field -- output type selection
		$this->add_control(
			'text_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'plain' 	=> 'Plain',
					'link' 		=> 'Linked URL, email or phone number',
					'oembed' 	=> 'oEmbed',
					'shortcode'	=> 'Shortcode',
				],
				'default'	=> 'plain',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__text__',
						]
					],
				],
			]
		);

		// True False field -- output type selection
		$this->add_control(
			'true_false_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'true_false' 	=> 'True or False',
					'yes_no' 		=> 'Yes or No',
					'check_cross' 	=> 'Check or Cross mark',
					'toggle_on_off' => 'Toggle On or Off icon',
				],
				'default'	=> 'true_false',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__true_false__',
						]
					],
				],
			]
		);

		// Date field -- output type selection
		$this->add_control(
			'date_output',
			[
				'label' => 'Date Format',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default'	=> 'F j, Y',
				'placeholder'	=> 'e.g. F j, Y',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__date__',
						]
					],
				],
			]
		);

		// Datetime field -- output type selection
		$this->add_control(
			'datetime_output',
			[
				'label' => 'Date Time Format',
				'type' => \Elementor\Controls_Manager::TEXT,
				'default'	=> 'F j, Y - H:i',
				'placeholder'	=> 'e.g. F j, Y - H:i',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__datetime__',
						]
					],
				],
			]
		);
		
		// Hyperlink field -- output type selection
		$this->add_control(
			'hyperlink_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'link' 		=> 'Link',
					'url' 		=> 'URL',
				],
				'default'	=> 'link',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__hyperlink__',
						]
					],
				],
			]
		);

		// File (image) field -- output type selection
		$this->add_control(
			'file_image_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'thumbnail' 	=> 'Thumbnail',
					'medium' 		=> 'Medium',
					'medium_large' 	=> 'Medium Large',
					'large' 		=> 'Large',
					'full' 			=> 'Full size',
					'file_link' 			=> 'Linked filename',
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

		// File (video / audio) field -- output type selection
		$this->add_control(
			'file_av_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'url' 			=> 'Player',
					'file_link' 	=> 'Linked filename',
				],
				'default'	=> 'url',
				'conditions' => [
					'relation'	=> 'or',
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__file__video',
						],
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__file__audio',
						]
					],
				],
			]
		);
		
		// File (pdf) field -- output type selection
		$this->add_control(
			'file_pdf_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'pdf_viewer' 	=> 'PDF Viewer',
					'file_link' 	=> 'Linked filename',
				],
				'default'	=> 'pdf_viewer',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__file__pdf',
						]
					],
				],
			]
		);

		// Text field -- output type selection
		$this->add_control(
			'gallery_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'justified' => 'Justified Grid',
					'masonry' 	=> 'Masonry Grid',
				],
				'default'	=> 'justified',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__gallery__',
						]
					],
				],
			]
		);		

		// Relationship field -- output type selection
		$this->add_control(
			'relationship_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'titles_only_c' 	=> 'Linked title. Comma separated.',
					'titles_only_v' 	=> 'Linked title as a list.',
					'image_titles_v' 	=> 'Linked image and title as a list.',
					'image_titles_h' 	=> 'Linked image and title, horizontally listed',
				],
				'default'	=> 'titles_only_c',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__relationship__',
						]
					],
				],
			]
		);

		// Term field -- output type selection
		$this->add_control(
			'term_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'names' 				=> 'Term labels',
					'names_archive_links' 	=> 'Term labels linked to archive pages',
					'names_edit_links' 		=> 'Term labels linked to edit screens',
				],
				'default'	=> 'names_archive_links',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__term__',
						]
					],
				],
			]
		);

		// User field -- output type selection
		$this->add_control(
			'user_output',
			[
				'label' => 'Output',
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'first_names' 		=> 'First names',
					'last_names' 		=> 'Last names',
					'display_names' 	=> 'Display names',
					'usernames' 		=> 'Usernames',
				],
				'default'	=> 'display_names',
				'conditions' => [
					'terms'	=> [
						[
							'name'		=> 'key',
							'operator'	=> 'contains',
							'value' 	=> '__user__',
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

		$field_name = isset( $field_key_parts[0] ) ? $field_key_parts[0] : '';
		$field_type = isset( $field_key_parts[1] ) ? $field_key_parts[1] : '';
		$field_subtype = isset( $field_key_parts[2] ) ? $field_key_parts[2] : '';

		$output_format = 'default';

		if ( ! empty( $field_name ) && ! empty( $field_type ) ) {

			switch ( $field_type ) {
				case 'text':
					$output_format = $this->get_settings( 'text_output' );
					$output_format = ! empty( $output_format ) ? $output_format : 'plain';
					
					if ( 'link' == $output_format ) {
						switch ( $field_subtype ) {
							case 'url':
								$output_format = 'link';
								break;
							case 'email':
								$output_format = 'email';
								break;
							case 'shortcode':
								$output_format = 'shortcode';
								break;
							case 'phone':
								$output_format = 'phone';
								break;
							case 'any':
								$output_format = 'default';
								break;
						}					
					} elseif ( 'oembed' == $output_format ) {
						switch ( $field_subtype ) {
							case 'url':
							case 'any':
								$output_format = 'oembed';
								break;
							case 'email':
								$output_format = 'email';
								break;
							case 'shortcode':
								$output_format = 'shortcode';
								break;
							case 'phone':
								$output_format = 'phone';
								break;
						}
						break;
					} elseif ( 'shortcode' == $output_format ) {
						switch ( $field_subtype ) {
							case 'url':
								$output_format = 'link';
								break;
							case 'email':
								$output_format = 'email';
								break;
							case 'any':
							case 'shortcode':
								$output_format = 'shortcode';
								break;
							case 'phone':
								$output_format = 'phone';
								break;
						}
						break;
					} else {
						// $output_format is 'plain'
						$output_format = 'default';
					}
					break;

				case 'true_false':
					$output_format = $this->get_settings( 'true_false_output' );
					$output_format = ! empty( $output_format ) ? $output_format : 'true_false';
					break;

				case 'date':
					$output_format = $this->get_settings( 'date_output' );
					$output_format = ! empty( $output_format ) ? $output_format : 'F j, Y';
					break;

				case 'time':
					$output_format = 'default';
					break;

				case 'datetime':
					$output_format = $this->get_settings( 'datetime_output' );
					$output_format = ! empty( $output_format ) ? $output_format : 'F j, Y - H:i';
					break;
					
				case 'radio':
				case 'select':
				case 'checkbox':
					$output_format = 'values_c';
					break;

				case 'hyperlink':
					$output_format = $this->get_settings( 'hyperlink_output' );
					$output_format = ! empty( $output_format ) ? $output_format : 'link';
					break;
					
				case 'file':
					switch ( $field_subtype ) {
						case 'image':
							$output_format = $this->get_settings( 'file_image_output' );
							$output_format = ! empty( $output_format ) ? 'image_view__' . $output_format : 'image_view__medium_large';
							break;

						case 'video':
						case 'audio':
							$output_format = $this->get_settings( 'file_av_output' );
							if ( ! empty( $output_format ) ) {
								$output_format = $output_format;
							} else {
								$output_format = 'url';
							}
							break;

						case 'pdf':
							$output_format = $this->get_settings( 'file_pdf_output' );
							$output_format = ! empty( $output_format ) ? $output_format : 'pdf_viewer';
							break;

						case 'any':
							$output_format = 'file_link';
							break;
					}
					break;
				
				case 'gallery' :
					$output_format = $this->get_settings( 'gallery_output' );
					$output_format = ! empty( $output_format ) ? 'gallery_' . $output_format . '__medium' : 'gallery_justified__medium';
					break;

				case 'relationship':
					$output_format = $this->get_settings( 'relationship_output' );
					$output_format = ! empty( $output_format ) ? $output_format : 'titles_only_c';
					break;

				case 'term':
					$output_format = $this->get_settings( 'term_output' );
					$output_format = ! empty( $output_format ) ? $output_format : 'names_archive_links';
					break;

				case 'user':
					$output_format = $this->get_settings( 'user_output' );
					$output_format = ! empty( $output_format ) ? $output_format : 'display_names';
					break;

			}
			
			if ( 'relationship' != $field_type ) {
				$field_value = get_cf( $field_name, $output_format );
			} else {
				$field_value = get_cf_related_to( $field_name, $output_format );
			}
			
			// For troubleshooting
			// echo $field_value;
			// echo '';
			
			echo wp_kses( $field_value, get_kses_with_style_src_svg_ruleset() );
		} else {
			echo '';
		}
		
	}

}