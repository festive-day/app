<?php

class Ase_Breakdance_Integration {

	// private $options;
	
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}
	
	public function init() {
		global $post;

		require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/breakdance-fields/ase-field.php';
		
		$breakdance_return_types = array(
			'string',
			'url',
			'image',
			'gallery',
			'video',
			'repeater',
		);

		foreach ( $breakdance_return_types as $return_type ) {
			require_once ASENHA_PATH . 'includes/premium/custom-content/cfgroup-integrations/breakdance-fields/ase-' . $return_type . '-field.php';
		}

		// Get all fields

		$cache_key = 'ase_fields_for_breakdance';
		$ase_fields   = wp_cache_get( $cache_key, 'breakdance' );

		if ( false === $ase_fields ) {
			// Get all published custom field groups

			$cfgroup_ids = array();

			$args = array(
			    'post_type' => 'asenha_cfgroup',
			    'post_status' => 'publish',
			    'numberposts' => -1,
			);

			$cfgroups = get_posts( $args );

			if ( ! empty( $cfgroups ) ) {
				foreach ( $cfgroups as $cfgroup ) {
					$cfgroup_ids[] = $cfgroup->ID;
				}
			}
						
			// Assemble array of fields with additional Breakdance properties

			$ase_fields = array();
			
			if ( ! empty( $cfgroup_ids ) ) {
				foreach ( $cfgroup_ids as $cfgroup_id ) {
					$cfgroup = get_post( $cfgroup_id );
					$cfgroup_name = $cfgroup->post_title;
					$cfgroup_rules = get_post_meta( $cfgroup_id, 'cfgroup_rules', true );
					
					// Get the placement of custom field group
					$cfgroup_placement = 'posts'; // default
					
					if ( isset( $cfgroup_rules['placement'] ) ) {
						if ( isset( $cfgroup_rules['placement']['operator'] ) && '==' == $cfgroup_rules['placement']['operator'] ) {
							$cfgroup_placement = ! empty( $cfgroup_rules['placement']['values'] ) ? $cfgroup_rules['placement']['values'] : 'posts';
						}
					}
					
					if ( 'posts' == $cfgroup_placement && isset( $cfgroup_rules['post_types'] ) ) {
						// Get indexed array of post type slugs
						$available_for_post_types = array();
						switch ( $cfgroup_rules['post_types']['operator'] ) {
							case '==':
								$available_for_post_types = $cfgroup_rules['post_types']['values']; 
								break;
							case '!=':
								$all_post_types = array_values( get_post_types( array(), 'names' ) );
								$available_for_post_types = array_values( array_diff( $all_post_types, $cfgroup_rules['post_types']['values'] ) );
								break;
						}
						// Add Breakdance post types, e.g. global block, header, footer, pop up
						$available_for_post_types = array_merge( $available_for_post_types, (array) BREAKDANCE_DYNAMIC_DATA_PREVIEW_POST_TYPES );

						$cfgroup_fields = CFG()->find_fields( array( 'group_id' => $cfgroup_id ) );

						if ( ! is_array( $cfgroup_fields ) ) {
							continue;
						}

						foreach ( $cfgroup_fields as $field ) {
							if ( 'tab' != $field['type'] ) {
								$field['field_group'] = $cfgroup_name;
								$field['for_post_types'] = $available_for_post_types;
								$ase_fields[] = $field;							
							}
						}						
					} 
					
					if ( 'options-pages' == $cfgroup_placement && isset( $cfgroup_rules['options_pages'] ) ) {
						$is_for_an_option_page = false;
						// $available_for_options_pages = array();
						if ( ! empty( $cfgroup_rules['options_pages']['values'] ) ) {
							$is_for_an_option_page = true;
						}

						$cfgroup_fields = CFG()->find_fields( array( 'group_id' => $cfgroup_id ) );

						if ( ! is_array( $cfgroup_fields ) ) {
							continue;
						}

						foreach ( $cfgroup_fields as $field ) {
							if ( 'tab' != $field['type'] ) {
								$field['field_group'] = $cfgroup_name;
								$field['is_for_an_option_page'] = $is_for_an_option_page;
								$ase_fields[] = $field;							
							}
						}
					}
					
					if ( 'taxonomy-terms' == $cfgroup_placement && isset( $cfgroup_rules['taxonomies'] ) ) {
						$cfgroup_fields = CFG()->find_fields( array( 'group_id' => $cfgroup_id ) );

						if ( ! is_array( $cfgroup_fields ) ) {
							continue;
						}

						foreach ( $cfgroup_fields as $field ) {
							if ( 'tab' != $field['type'] ) {
								$field['field_group'] = $cfgroup_name;
								$field['is_for_taxonomy_terms'] = true;
								$ase_fields[] = $field;
							}
						}
					}
				}
			}

			// Determine if a field is a sub-field of a repeater field and mark it

			foreach( $ase_fields as $ase_field_index => $ase_field ) {
				if ( intval( $ase_field['parent_id'] ) > 0 ) {
					foreach ( $ase_fields as $ase_field_tmp ) {
						if ( 'repeater' == $ase_field_tmp['type'] && $ase_field['parent_id'] == $ase_field_tmp['id'] ) {
							$ase_field['is_repeater_sub_field'] = true;
							$ase_field['parent_repeater'] = $ase_field_tmp['label'];					
							$ase_field['parent_type'] = 'repeater';
							$ase_field['parent_name'] = $ase_field_tmp['name'];
						}
					}
				} else {
					$ase_field['is_repeater_sub_field'] = false;
					$ase_field['parent_repeater'] = '';			
					$ase_field['parent_type'] = '';
					$ase_field['parent_name'] = '';
				}

				// Update field data with additional parameters
				$ase_fields[$ase_field_index] = $ase_field;
			}

			wp_cache_set( $cache_key, $ase_fields, 'bricks', MINUTE_IN_SECONDS );
		}
		// vi( $ase_fields, '', 'for Breakdance' );
		
		// Register fields that will return string value in Breakdance
		$string_return_field_types = array(
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

		foreach ( $ase_fields as $field ) {
			if ( in_array( $field['type'], $string_return_field_types ) ) {
				\Breakdance\DynamicData\registerField( new Ase_String( $field ) );
			}
		}

		// Register fields that will return URL string value in Breakdance
		$url_return_field_types = array(
			'text',
			'hyperlink',
			'file',
			'relationship', // only returns first post's URL
		);

		foreach ( $ase_fields as $field ) {
			if ( in_array( $field['type'], $url_return_field_types ) ) {
				\Breakdance\DynamicData\registerField( new Ase_Url( $field ) );
			}
		}

		// Register fields that will return Image / Image URL value in Breakdance
		$image_return_field_types = array(
			'file',
		);

		foreach ( $ase_fields as $field ) {
			if ( in_array( $field['type'], $image_return_field_types ) 
				&& 'image' == $field['options']['file_type']
			) {
				\Breakdance\DynamicData\registerField( new Ase_Image( $field ) );
			}
		}

		// Register fields that will return gallery value in Breakdance
		$gallery_return_field_types = array(
			'gallery',
		);

		foreach ( $ase_fields as $field ) {
			if ( in_array( $field['type'], $gallery_return_field_types ) ) {
				\Breakdance\DynamicData\registerField( new Ase_Gallery( $field ) );
			}
		}



		// Register fields that will return Video in Breakdance
		$video_return_field_types = array(
			'text',
			'hyperlink',
			'file',
		);

		foreach ( $ase_fields as $field ) {
			if ( in_array( $field['type'], $video_return_field_types ) ) {
				switch ( $field['type'] ) {
					case 'text':
						if ( isset( $field['options']['text_type'] ) ) {
							if ( 'url' == $field['options']['text_type'] 
								|| 'oembed' == $field['options']['text_type'] 
							) {
								\Breakdance\DynamicData\registerField( new Ase_Video( $field ) );
							}
						}
						break;
						
					case 'hyperlink':
						\Breakdance\DynamicData\registerField( new Ase_Video( $field ) );
						break;						

					case 'file':
						if ( 'video' == $field['options']['file_type'] ) {
							\Breakdance\DynamicData\registerField( new Ase_Video( $field ) );
						}
						break;
				}
			}
		}

		// Register fields that will return Repeater data in Breakdance
		$repeater_return_field_types = array(
			'repeater',
		);

		foreach ( $ase_fields as $field ) {
			if ( in_array( $field['type'], $repeater_return_field_types ) ) {
				\Breakdance\DynamicData\registerField( new Ase_Repeater( $field ) );
			}
		}

	}
	
	/**
	 * Maybe get the value of repeater's sub-field
	 */
	public function get_ase_repeater_sub_field_value() {
		return 'Sample response';
	}

}

new Ase_Breakdance_Integration();