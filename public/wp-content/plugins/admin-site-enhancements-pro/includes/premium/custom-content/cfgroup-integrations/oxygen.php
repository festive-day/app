<?php

use ASENHA\Classes\Common_Methods;

class Ase_Oxygen_Integration {

	// private $options;
	
	public function __construct() {
		add_action( 'init', [ $this, 'init' ], 0 );
	}
	
	public function init() {
		add_filter( 'oxygen_custom_dynamic_data', [ $this, 'init_ase_dynamic_data' ], 10, 1 );		
	}
	
	public function init_ase_dynamic_data( $dynamic_data ) {
		global $post;
		$queried_object = get_queried_object();
		$common_methods = new Common_Methods;

		/**
		 * Check if we are editing an Oxygen template. Oxygen templates can be applied to
		 * several post_types, so we have to check which post types it is configured to render
		 * and grab the fields related to each of them.
		 */
		$post_type = isset( $post->post_type ) ? $post->post_type : false;
		if ( function_exists( 'oxy_get_post_meta' ) ) {
			$template_type = isset( $post->ID ) ? oxy_get_post_meta( $post->ID, 'ct_template_type', true ) : false;
		} else {
			$template_type = isset( $post->ID ) ? get_post_meta( $post->ID, 'ct_template_type', true ) : false;
		}

		if ( 'ct_template' == $post_type && 'reusable_part' != $template_type ) {
			// An Oxygen template that's not for a reusable part
			$fields = array();

			// Detect which post types the template is applied for
			if ( function_exists( 'oxy_get_post_meta' ) ) {
				$post_types = oxy_get_post_meta( $post->ID, 'ct_template_post_types', true );
			} else {
				$post_types = get_post_meta( $post->ID, 'ct_template_post_types', true );
			}

			// We want to include relationship fields related to the current post type
			// This is useful to get source posts a post is related from, 
			// since the relationship field is part of the source post (type) and not the current post (type)
			// $relationship_fields = CFG()->find_fields( array( 'field_type' => 'relationship' ) );

			if ( is_array( $post_types ) ) {
				if ( ! empty( $post_types ) ) {
					foreach( $post_types as $applicable_post_type ) {
						$most_recent_post_id = $common_methods->get_most_recent_post_id( $applicable_post_type );
						$fields = array_merge( $fields, CFG()->find_fields( array( 'post_id' => $most_recent_post_id ) ) );

						// Include relationship field if related to the post type the Oxygen template is applicable to
						// $fields_count = count( $fields );
						// if ( is_array( $relationship_fields ) && ! empty( $relationship_fields ) ) {
						// 	foreach ( $relationship_fields as $relationship_field ) {
						// 		$relationship_field_to_add = array();
						// 		$relationship_post_types = $relationship_field['options']['post_types'];
						// 		if ( is_array( $relationship_post_types ) 
						// 			&& ! empty( $relationship_post_types ) 
						// 			&& in_array( $applicable_post_type, $relationship_post_types ) 
						// 		) {
						// 			$fields[$fields_count] = $relationship_field;
						// 			$fields_count++;
						// 		}
						// 	}
						// }

					}
				}
			}

			// Add fields coming from options pages
	        $options_pages_ids = get_options_pages_ids(); // indexed array of post IDs
	        if ( ! empty( $options_pages_ids ) ) {
				foreach( $options_pages_ids as $options_page_id ) {
					$fields = array_merge( $fields, CFG()->find_fields( array( 'post_id' => $options_page_id ) ) );
				}			        	
	        }

			// Check if the template is applied for a taxonomy archive
			$is_for_taxonomy_archive = false;
			if ( function_exists( 'oxy_get_post_meta' ) ) {
				$is_for_taxonomy_archive = oxy_get_post_meta( $post->ID, 'ct_template_apply_if_archive_among_taxonomies', true );
			} else {
				$is_for_taxonomy_archive = get_post_meta( $post->ID, 'ct_template_apply_if_archive_among_taxonomies', true );
			}

			// Add fields coming from taxonomy terms
			if ( $is_for_taxonomy_archive ) {
		        $cfgroup_ids = get_cfgroup_ids_by_placement( 'taxonomy-terms' );
		        $fields = array_merge( $fields, CFG()->find_fields( array( 'group_id' => $cfgroup_ids ) ) );
			}
		} else {
			// Not an oxygen template
			$current_post_id = isset( $post->ID ) ? $post->ID : 0;
			$fields = CFG()->find_fields( array( 'post_id' => $current_post_id ) );
		}
		
		$fields = array_unique( $fields, SORT_REGULAR );
		// vi( $fields, '', 'for post type ' . $post_type . ' and template type ' . $template_type );
		
		// Generate the Oxygen settings for each field type
		// Available modes: content, image, link, custom-field, image-id
		// Available positions: 'Post', 'Author', 'User', 'Featured Image', 'Current User', 'Archive' 'Blog Info'

        // Mode: content. Return: various types of content.

		$options_for_content = array_reduce( $fields, array( $this, "add_button" ), array() );
		
		if ( count( $options_for_content ) > 0 ) {

			// Prepend to the beginning of $options_for_content array
		    array_unshift( 
		    	$options_for_content, 
		    	array( 
		    		'name' => 'Select the ASE field', 
		    		'type' => 'heading' 
		    	) 
		    );

            $ase_content = array(
                'name'      	=> 'ASE Field',
                'mode'       	=> 'content',
                'position'   	=> 'Post',
                'data'       	=> 'ase_content',
                'handler'    	=> array( $this, 'ase_content_handler' ),
                'properties' 	=> $options_for_content
            );
            $dynamic_data[]   = $ase_content;
        }

        // Mode: image, link, custom-field. Return: URL.

		$options_for_url = array_reduce( $fields, array( $this, "add_url_button" ), array() );

        if ( count( $options_for_url ) > 0 ) {
            // Dynamic Data modal modes "custom-field", "link" and "image" are expected to return an URL

            $ase_image = array(
                'name' 			=> 'ASE Field',
                'mode' 			=> 'image',
                'position' 		=> 'Post', 
                'data' 			=> 'ase_image',
                'handler' 		=> array( $this, 'ase_url_handler' ),
                'properties' 	=> $options_for_url
            );
            $dynamic_data[] = $ase_image;

            $ase_link = array(
                'name' 			=> 'ASE Field',
                'mode' 			=> 'link',
                'position' 		=> 'Post',
                'data' 			=> 'ase_link',
                'handler' 		=> array( $this, 'ase_url_handler' ),
                'properties' 	=> $options_for_url
            );
            $dynamic_data[] = $ase_link;

            $ase_custom_field = array(
                'name' 			=> 'ASE Field',
                'mode' 			=> 'custom-field',
                'position' 		=> 'Post',
                'data' 			=> 'ase_custom_field',
                'handler' 		=> array( $this, 'ase_url_handler' ),
                'properties' 	=> $options_for_url
            );
            $dynamic_data[] = $ase_custom_field;
        }
        
        // Mode: image-id. Return: attachment/image ID
        
		$options_for_image_id = array_reduce( $fields, array( $this, "add_image_id_button" ), array() ); 
		
        if ( count( $options_for_image_id ) > 0 ) { 
            $ase_image_id_field = array(
                'name' 			=> 'ASE Field',
                'mode' 			=> 'image-id',
                'position' 		=> 'Post',
                'data' 			=> 'ase_image_id',
                'handler' 		=> array( $this, 'ase_image_id_handler' ),
                'properties' 	=> $options_for_image_id
            );
            $dynamic_data[] = $ase_image_id_field;
        }
        
        // vi( $dynamic_data );		
		return $dynamic_data;
	}

    function add_button( $result, $field_settings ) {
    	// All fields except 'repeater' and 'tab'
        $applicable_field_types = array( 'text', 'textarea', 'wysiwyg', 'color', 'true_false', 'radio', 'select', 'checkbox', 'hyperlink', 'number', 'date', 'time', 'datetime', 'term', 'user', 'relationship', 'file', 'gallery' );
        $properties = [];

        switch ( $field_settings['type'] ) {
            case 'text':
				$properties[] = array(
				    'name'     	=> 'Please select the output type',
				    'data'      => 'output_type',
				    'type'      => 'select',
				    'options'   => array(
				    	'Plain'			=> 'plain',
				        'URL Link' 		=> 'url_link',
				        'Mailto Link' 	=> 'mailto_link',
				        'oEmbed' 		=> 'oembed',
				        'Shortcode' 	=> 'shortcode',
				    ),
				    'nullval'   => 'plain'
				);
				$properties[] = array(
				    'name'     	=> 'Please select the label type',
				    'data'      => 'label_type',
				    'type'      => 'select',
				    'options'   => array(
				    	'Field value'		=> 'field_value',
				        'Custom text' 		=> 'custom_text',
				    ),
				    'nullval'   => 'field_value',
				    'show_condition' 	=> "dynamicDataModel.output_type == 'url_link' || dynamicDataModel.output_type == 'mailto_link'"
				);
				$properties[] = array(
				    'name' 				=> 'Label',
				    'data' 				=> 'link_label',
				    'type' 				=> 'text',
				    'show_condition' 	=> "dynamicDataModel.label_type == 'custom_text'"
				);
            	break;
            case 'textarea':
            case 'wysiwyg':
            case 'color':
            	$properties[] = array();
            	break;
            case 'true_false':
				$properties[] = array(
				    'name'     	=> 'Please select the output type',
				    'data'      => 'output_type',
				    'type'      => 'select',
				    'options'   => array(
				        'True or False' 		=> 'true_false',
				        'Yes or No' 			=> 'yes_no',
				        'Check or Cross mark' 	=> 'check_cross',
				        'Toggle On or Off icon'	=> 'toggle_on_off',
				        'Custom Text'			=> 'custom_text',
				    ),
				    'nullval'   => 'yes_no'
				);
				$properties[] = array(
				    'name' 				=> 'Content when true',
				    'data' 				=> 'content_true',
				    'type' 				=> 'text',
				    'show_condition' 	=> "dynamicDataModel.output_type == 'custom_text'"
				);
				$properties[] = array(
				    'name' 				=> 'Content when false',
				    'data' 				=> 'content_false',
				    'type' 				=> 'text',
				    'show_condition' 	=> "dynamicDataModel.output_type == 'custom_text'"
				);
                break;
            case 'select':
            	$properties[] = array();
                break;
            case 'hyperlink':
                $properties[] = array(
                    'name' 	=> 'Link',
                    'data' 	=> 'url_link',
                    'type' 	=> 'checkbox',
                    'value' => 'yes'
                );
                $properties[] = array(
                    'name' 				=> 'Custom link text',
                    'data' 				=> 'link_text',
                    'type' 				=> 'text',
                    'show_condition' 	=> "dynamicDataModel.url_link == 'yes'"
                );
                $properties[] = array(
                    'name' 				=> 'Open in new tab',
                    'data' 				=> 'new_tab',
                    'type' 				=> 'checkbox',
                    'value' 			=> 'yes',
                    'show_condition' 	=> "dynamicDataModel.url_link == 'yes'"
                );
                break;
            case 'date':
                $properties[] = array(
                    'name' 	=> 'PHP Date Format. Defaults to Y-m-d',
                    'data' 	=> 'date_format',
                    'type' 	=> 'text'
                );
                break;
            case 'time':
            	$properties[] = array();
            	break;
            case 'datetime':
                $properties[] = array(
                    'name' 	=> 'PHP Date Time Format. Defaults to F j, Y - H:i',
                    'data' 	=> 'datetime_format',
                    'type' 	=> 'text'
                );
                break;
            case 'term':
				$properties[] = array(
				    'name'     	=> 'Please select the output type',
				    'data'      => 'output_type',
				    'type'      => 'select',
				    'options'   => array(
				        'Term names' 							=> 'names',
				        'Term names linked to archive pages' 	=> 'names_archive_links',
				        'Term names linked to edit screens' 	=> 'names_edit_links'
				    ),
				    'nullval'   => 'names'
				);
                break;
            case 'user':
				$properties[] = array(
				    'name'     	=> 'Please select the output type',
				    'data'      => 'output_type',
				    'type'      => 'select',
				    'options'   => array(
				        'User display names' 	=> 'display_names',
				        'Usernames'			 	=> 'usernames',
				        'User first names' 		=> 'first_names',
				        'User last names' 		=> 'last_names'
				    ),
				    'nullval'   => 'display_names'
				);
                break;
            case 'relationship':
				$properties[] = array(
				    'name'     	=> 'Please select the relationship direction',
				    'data'      => 'relationship_direction',
				    'type'      => 'select',
				    'options'   => array(
				        'Related to - get target entities' 		=> 'related_to',
				        'Related from - get source entities'	=> 'related_from',
				    ),
				    'nullval'   => 'related_to'
				);
				$properties[] = array(
				    'name'     	=> 'Please select the output type',
				    'data'      => 'output_type',
				    'type'      => 'select',
				    'options'   => array(
				        'Linked titles - comma separated' 				=> 'titles_only_c',
				        'Linked titles - vertical list'					=> 'titles_only_v',
				        'Linked images with titles - vertical list' 	=> 'image_titles_v',
				        'Linked images with titles - horizontal list'	=> 'image_titles_h',
				    ),
				    'nullval'   => 'titles_only_c'
				);
                break;
            case 'file':
		        if ( 'image' == $field_settings['options']['file_type'] ) {
					$properties[] = array(
					    'name'     	=> 'Please select what you want to insert',
					    'data'      => 'insert_type',
					    'type'      => 'select',
					    'options'   => array(
					        'Image element' => 'image_element',
					        'Image URL' 	=> 'image_url',
					        'Image Title' 	=> 'image_title',
					        'Image Caption' => 'image_caption'
					    ),
					    'nullval'   => 'image_element'
					);
					$properties[] = array(
					    'name'					=> 'Size',
					    'data'					=> 'size',
					    'type'					=> 'select',
					    'options'				=> array(
		                    'Thumbnail' 	=> 'thumbnail',
		                    'Medium' 		=> 'medium',
		                    'Medium Large' 	=> 'medium_large',
		                    'Large' 		=> 'large',
		                    'Original' 		=> 'full'
					    ),
					    'nullval' 				=> 'medium',
					    'change'				=> 'scope.dynamicDataModel.width = ""; scope.dynamicDataModel.height = ""',
					    'show_condition' 		=> "dynamicDataModel.insert_type == 'image_element' || dynamicDataModel.insert_type == 'image_url'"
					);
					$properties[] = array(
					    'name' 				=> 'or',
					    'type' 				=> 'label',
					    'show_condition'	=> "dynamicDataModel.insert_type == 'image_element'"
					);
					$properties[] = array(
					    'name' 				=> 'Width',
					    'data' 				=> 'width',
					    'type' 				=> 'text',
					    'helper'			=> true,
					    'change' 			=> "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height",
					    'show_condition' 	=> "dynamicDataModel.insert_type == 'image_element'"
					);
					$properties[] = array(
					    'name' 				=> 'Height',
					    'data' 				=> 'height',
					    'type' 				=> 'text',
					    'helper' 			=> true,
					    'change' 			=> "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height",
					    'show_condition' 	=> "dynamicDataModel.insert_type == 'image_element'"
					);
		        } else {
		        	switch ( $field_settings['options']['file_type'] ) {
		        		case 'video' :
		        			$output_options = array(
		        				'Video Player'	=> 'viewer',
						        'URL' 			=> 'url',
						        'Attachment ID' => 'attachment_id'
		        			);
			        		break;
		        		case 'audio' :
		        			$output_options = array(
		        				'Audio Player'	=> 'viewer',
						        'URL' 			=> 'url',
						        'Attachment ID' => 'attachment_id'
		        			);
			        		break;
		        		case 'pdf' :
		        			$output_options = array(
		        				'PDF Viewer'	=> 'viewer',
						        'URL' 			=> 'url',
						        'Attachment ID' => 'attachment_id'
		        			);
			        		break;
		        		case 'any' :
		        		case 'file' :
		        			$output_options = array(
						        'URL' 			=> 'url',
						        'Attachment ID' => 'attachment_id'
		        			);
			        		break;
		        	}
					$properties[] = array(
					    'name' 		=> 'Output type',
					    'data' 		=> 'output_type',
					    'type' 		=> 'select',
					    'options'	=> $output_options,
					    'nullval' 	=> 'url'
					);
					$properties[] = array(
					    'name' 				=> 'Link',
					    'data' 				=> 'file_link',
					    'type' 				=> 'checkbox',
					    'value' 			=> 'yes',
					    'show_condition' 	=> "dynamicDataModel.output_type == 'url'"
					);
					$properties[] = array(
					    'name' 				=> 'Link text (file name will be used if left empty)',
					    'data' 				=> 'link_text',
					    'type' 				=> 'text',
					    'show_condition' 	=> "dynamicDataModel.file_link == 'yes'"
					);
		        }
				break;

            case 'gallery':
				$properties[] = array(
				    'name'     	=> 'Please select gallery type',
				    'data'      => 'gallery_type',
				    'type'      => 'select',
				    'options'   => array(
				        'Grid' 		=> 'grid',
				        'Justified' => 'justified',
				        'Masonry' 	=> 'masonry',
				    ),
				    'nullval'   => 'justified'
				);
				$properties[] = array(
				    'name'     	=> 'Please select image size to use',
				    'data'      => 'gallery_image_size',
				    'type'      => 'select',
				    'options'   => array(
				        'Thumbnail' 	=> 'thumbnail',
				        'Medium' 		=> 'medium',
				        'Medium Large' 	=> 'medium_large',
				    ),
				    'nullval'   => 'medium'
				);
				$properties[] = array(
				    'name'     	=> 'How many images per row?',
				    'data'      => 'gallery_grid_columns',
				    'type'      => 'select',
				    'options'   => array(
				        '2' 	=> '2',
				        '3' 	=> '3',
				        '4' 	=> '4',
				        '5' 	=> '5',
				        '6' 	=> '6',
				        '7' 	=> '7',
				        '8' 	=> '8',
				        '9' 	=> '9',
				        '10' 	=> '10',
				    ),
				    'nullval'   => '6',
				    'show_condition' 	=> "dynamicDataModel.gallery_type == 'grid'"
				);
                break;
        }

        if ( ! empty( $field_settings['label'] ) 
        	&& isset( $field_settings['type'] ) 
        	&& in_array( $field_settings['type'], $applicable_field_types ) 
        	&& isset( $field_settings['parent_id'] )
        	&& 0 == $field_settings['parent_id'] // Not a sub-field
        ) {
            $result[] = array(
                'name' 			=> $field_settings['label'] . ' [' . $field_settings['type'] . ']',
                'data' 			=> $field_settings['name'],
                'type' 			=> 'button',
                'properties' 	=> $properties,
                'settings_page' => false,
            );
        }

        return $result;
	}

    function add_url_button( $result, $field_settings ) {
	    $applicable_field_types = array( 'file', 'text', 'hyperlink', 'relationship', 'term' );
	    $properties = array();

        if ( 'file' == $field_settings['type'] && 'image' == $field_settings['options']['file_type'] ) {
            $properties[] = array(
                'name' 		=> 'Size',
                'data' 		=> 'size',
                'type' 		=> 'select',
                'options' 	=> array(
                    'Thumbnail' 	=> 'thumbnail',
                    'Medium' 		=> 'medium',
                    'Medium Large' 	=> 'medium_large',
                    'Large' 		=> 'large',
                    'Original' 		=> 'full'
                ),
                'nullval' 	=> 'medium',
                'change' 	=> 'scope.dynamicDataModel.width = ""; scope.dynamicDataModel.height = ""'
            );
            $properties[] = array(
                'name' 	=> 'or',
                'type' 	=> 'label'
            );
            $properties[] = array(
                'name' 		=> 'Width',
                'data' 		=> 'width',
                'type' 		=> 'text',
                'helper' 	=> true,
                'change' 	=> "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height"
            );
            $properties[] = array(
                'name' 		=> 'Height',
                'data' 		=> 'height',
                'type' 		=> 'text',
                'helper' 	=> true,
                'change' 	=> "scope.dynamicDataModel.size = scope.dynamicDataModel.width+'x'+scope.dynamicDataModel.height"
            );
        }

        if ( ! empty( $field_settings['label'] ) 
        	&& isset( $field_settings['type'] ) 
        	&& in_array( $field_settings['type'], $applicable_field_types ) 
        	&& isset( $field_settings['parent_id'] )
        	&& 0 == $field_settings['parent_id'] // Not a sub-field
        ) {
            $result[] = array(
                'name' 			=> $field_settings['label'] . ' [' . $field_settings['type'] . ']',
                'data' 			=> $field_settings['name'],
                'type' 			=> 'button',
                'properties' 	=> $properties,
                'settings_page' => false,
            );
        }
        
        return $result;
    }

    public function add_image_id_button( $result, $field_settings ) {
	    $applicable_field_types = array( 'file');

        if ( ! isset( $field_settings['type'] ) ) {
            return array();
        }

        if( ! empty( $field_settings['name'] ) 
        	&& isset( $field_settings['type'] ) 
        	&& in_array( $field_settings['type'], $applicable_field_types ) 
        	&& isset( $field_settings['parent_id'] )
        	&& 0 == $field_settings['parent_id'] // Not a sub-field
        	&& isset( $field_settings['options']['file_type'] ) 
        	&& 'image' == $field_settings['options']['file_type'] ) { // Only image file
            $result[] = array(
                'name' => $field_settings['label'] . ' [' . $field_settings['type'] . ']',
                'data' => $field_settings['name'],
                'type' => 'button',
                'properties' => [],
                'settings_page' => false,
            );
        }
        
        return $result;
    }
    
    /**
     * Handler for fields that should return some form of content.
     */
    public function ase_content_handler( $atts ) {
    	 global $wpdb, $post;
    	 $queried_object = get_queried_object();

        $field_name = $atts['settings_path'];
        $settings_page = $atts['settings_page'];
        $field = $this->get_ase_field_data( $field_name, $settings_page );
        
        $output = '';
        
        if ( empty( $field ) ) {
        	return $output;
        }
        
        if ( isset( $field['type'] ) ) {
			switch ( $field['type'] ) {
				case 'text':
	            	$output_type = isset( $atts['output_type'] ) ? $atts['output_type'] : 'plain';
	            	$label_type = isset( $atts['label_type'] ) ? $atts['label_type'] : 'field_value';
	            	
	            	switch ( $label_type ) {
	            		case 'field_value';
	            			$link_label = $field['value'];
	            			break;
	            		case 'custom_text';
			            	$link_label = isset( $atts['link_label'] ) ? $atts['link_label'] : $field['value'];
			            	break;        		
	            	}
	            	
	            	switch ( $output_type ) {
	            		case 'plain':
	        				$output = $field['value'];
	            			break;
	            		case 'url_link':
            				$output = '<a href="' . $field['value'] . '">' . $link_label . '</a>';
	            			break;
	            		case 'mailto_link':
				            $output = '<a href="mailto:' . $field['value'] . '">' . $link_label . '</a>';
	            			break;
	            		case 'oembed':
	            			$output = wp_oembed_get( $field['value'] );
	            			break;
	            		case 'shortcode':
	            			$output = do_shortcode( $field['value'] );
	            			break;
	            	}
					break;

				case 'textarea':
				case 'wysiwyg':
				case 'number':
	            case 'color':
	            	if ( 'wysiwyg' == $field['type'] ) {
						$output = wpautop( $field['value'] );
	            	} else {
						$output = $field['value'];
	            	}
					break;

	            case 'true_false':
	            	$output_type = isset( $atts['output_type'] ) ? $atts['output_type'] : 'yes_no';
	            	
	            	switch ( $output_type ) {
	            		case 'true_false':
	        				$output = ( true == $field['value'] ) ? 'True' : 'False';
	            			break;
	            		case 'yes_no':
	        				$output = ( true == $field['value'] ) ? 'Yes' : 'No';
	            			break;
	            		case 'check_cross':
	            			// True: https://icon-sets.iconify.design/fa-solid/check/
	            			// False: https://icon-sets.iconify.design/emojione-monotone/cross-mark/
	        				$output = ( true == $field['value'] ) ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512"><path fill="currentColor" d="m173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69L432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path fill="currentColor" d="M62 10.571L53.429 2L32 23.429L10.571 2L2 10.571L23.429 32L2 53.429L10.571 62L32 40.571L53.429 62L62 53.429L40.571 32z"/></svg>';
	            			break;
	            		case 'toggle_on_off':
	            			// True: https://icon-sets.iconify.design/la/toggle-on/
	            			// False: https://icon-sets.iconify.design/la/toggle-off/
	        				$output = ( true == $field['value'] ) ? '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-4.96 0-9 4.035-9 9s4.04 9 9 9h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm14 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7s3.121-7 7-7"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-.621 0-1.227.066-1.813.188a9.238 9.238 0 0 0-.875.218A9.073 9.073 0 0 0 .72 12.5c-.114.27-.227.531-.313.813A8.848 8.848 0 0 0 0 16c0 .93.145 1.813.406 2.656c.004.008-.004.024 0 .032A9.073 9.073 0 0 0 5.5 24.28c.27.114.531.227.813.313A8.83 8.83 0 0 0 9 24.999h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm0 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7c0-.242.008-.484.031-.719A6.985 6.985 0 0 1 9 9m5.625 0H23c3.879 0 7 3.121 7 7s-3.121 7-7 7h-8.375C16.675 21.348 18 18.828 18 16c0-2.828-1.324-5.348-3.375-7"/></svg>';
	            			break;
	            		case 'custom_text':
	            			$content_true = ( isset( $atts['content_true'] ) && ! empty( $atts['content_true'] ) ) ? $atts['content_true'] : 'True';
	            			$content_false = ( isset( $atts['content_false'] ) && ! empty( $atts['content_false'] ) ) ? $atts['content_false'] : 'False';
	            			$output = ( true == $field['value'] ) ? $content_true : $content_false;
	            			break;
	            	}
	            	break;

	            case 'radio':
	            case 'select':
	            case 'checkbox':
	            	$output = CFG()->get( $field_name, false, array( 'format' => 'api' ) );
					break;

				case 'hyperlink':
					if ( empty( $atts['url_link'] ) ) {
						$output = $field['value']['url'];
					} else {
						$link_text = ( ! empty( $atts['link_text'] ) ) ? $atts['link_text'] : $field['value']['text'];
						$target = ( ! empty( $atts['new_tab'] ) ) ? 'target="_blank"' : 'target="' . $field['value']['target'] . '"';
						$output = '<a href="' . $field['value']['url'] . '" ' . $target . '>' . $link_text . '</a>';
					}
					break;

	            case 'date':
	            	$date_format = isset( $atts['date_format'] ) ? $atts['date_format'] : 'Y-m-d';
		            $timezone_object = new DateTimeZone( 'UTC' );
	            	$output = wp_date( $date_format, strtotime( $field['value'] ), $timezone_object );
	            	break;

	            case 'time':
	            	// $output = get_cf( $field_name );
	            	$output = CFG()->get( $field_name, false, array( 'format' => 'api' ) );
	            	break;

	            case 'datetime':
	            	$date_format = isset( $atts['datetime_format'] ) ? $atts['datetime_format'] : 'F j, Y - H:i';
		            $timezone_object = new DateTimeZone( 'UTC' );
	            	$output = wp_date( $date_format, strtotime( $field['value'] ), $timezone_object );
	            	break;

				case 'term':
	            	$output_type = isset( $atts['output_type'] ) ? $atts['output_type'] : 'names';
	            	$output = get_cf( $field_name, $output_type );
					break;

				case 'user':
	            	$output_type = isset( $atts['output_type'] ) ? $atts['output_type'] : 'display_names';
	            	$output = get_cf( $field_name, $output_type );
					break;

	            case 'relationship':
	            	$relationship_direction = isset( $atts['relationship_direction'] ) ? $atts['relationship_direction'] : 'related_to';
	            	// vi( $relationship_direction, '', $field_name. ' - ' . $relationship_direction );
	            	$output_type = isset( $atts['output_type'] ) ? $atts['output_type'] : 'titles_only_c';
	            	// vi( $output_type, '', $field_name . ' - ' . $output_type );

	            	if ( is_a( $queried_object, 'WP_Term' ) ) {
		                // This field is part of a taxonomy term CFG
		                $taxonomy = $queried_object->taxonomy;
		                $term_id = $queried_object->term_id;
		                $post_id = $taxonomy . '_' . $term_id;
	            	}
	            	
	            	switch ( $relationship_direction ) {
	            		case 'related_to':
			            	if ( is_a( $queried_object, 'WP_Term' ) ) {
				                // This field is part of a taxonomy term CFG
				                $output = get_cf_related_to( $field_name, $output_type, 'api', $post_id );
			            	} else {
			            		// This field is part of post or options page CFG
				            	$output = get_cf_related_to( $field_name, $output_type );
				            	// vi( $output, '', $field_name . ' - ' . $relationship_direction. ' - ' . $output_type );
			            	}
	            			break;
	            		case 'related_from':
			            	if ( is_a( $queried_object, 'WP_Term' ) ) {
				                // This field is part of a taxonomy term CFG
				                $output = get_cf_related_from( $field_name, $output_type, false, false, 'relationship', $post_id );
			            	} else {
				            	$output = get_cf_related_from( $field_name, $output_type );
				            	// $output = get_cf_related_from( $field_name, $output_type, false, 'publish', 'relationship', $post->ID );
				            	// vi( $output, '', $field_name . ' - ' . $relationship_direction. ' - ' . $output_type );
			            	}
	            			break;
	            	}
					break;

	            case 'file':
			        if ( 'image' == $field['options']['file_type'] ) {
		                $image_id = null;
		                $image_url = null;
		                $image_attachment = null;
		                
		                if ( isset( $field['value'] ) && is_numeric( $field['value'] ) ) {
		                    $image_id = $field['value'];
		                    $image_attachment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE ID='%d';", $image_id ), ARRAY_A );

			                if ( isset( $atts['size'] ) ) {
				                $image_size = $this->get_image_size( $atts['size'] );
			                    $image_url = wp_get_attachment_image_src( $image_id, $image_size )[0];
			                } else {
			                    $image_url = wp_get_attachment_image_src( $image_id, 'full' )[0];
			                }	                	
		                }
		                
		                switch ( $atts['insert_type'] ) {
		                    case 'image_element':
		                    	// vi( $output, '', 'image_element' . $image_id );
		                        $output = '<img src="' . $image_url . '"/>';
		                        break;
		                    case 'image_url':
		                    	// vi( $output, '', 'image_url' . $image_id );
		                        $output = $image_url;
		                        break;
		                    case 'image_title':
		                    	// vi( $output, '', 'image_title' . $image_id );
		                        $output = $image_attachment['post_title'];
		                        break;
		                    case 'image_caption':
		                    	// vi( $output, '', 'image_caption' . $image_id );
		                        $output = $image_attachment['post_excerpt'];
		                        break;
		                }
						break;
			        } else {
		                $attachment_id = null;
		                $attachment_url = null;
			        	
		                if ( isset( $field['value'] ) && is_numeric( $field['value'] ) ) {
		                	$attachment_id = $field['value'];
		                	$attachment_url = wp_get_attachment_url( $attachment_id );

			                if ( ! isset( $atts['output_type'] ) || empty( $atts['output_type'] ) ) {
			                	$atts['output_type'] = 'url';
			                }

			                switch ( $atts['output_type'] ) {
			                	case 'viewer';
			                		switch ( $field['options']['file_type'] ) {
			                			case 'video';
					                		$output = do_shortcode( '[video src="' . $attachment_url . '"]' ) . 
							                '<style>
							                .ct-span .wp-video {
							                	width: 640px !important;
							                    height: 100% !important;
							                }
							                .ct-span video.wp-video-shortcode, 
							                .ct-span .mejs-container.mejs-video, 
							                .ct-span .mejs-overlay.load {
							                    width: 100% !important;
							                    height: 100% !important;
							                }
							                .ct-span .mejs-container.mejs-video {
							                    padding-top: 56.25%;
							                }
							                .ct-span .wp-video, 
							                .ct-span video.wp-video-shortcode {
							                    max-width: 100% !important;
							                }
							                .ct-span video.wp-video-shortcode {
							                    position: relative;
							                }
							                .ct-span .mejs-video .mejs-mediaelement {
							                    position: absolute;
							                    top: 0;
							                    right: 0;
							                    bottom: 0;
							                    left: 0;
							                }
							                .ct-span .mejs-video .mejs-controls {
							                    /* display: none; */
							                }
							                .ct-span .mejs-video .mejs-overlay-play {
							                    top: 0;
							                    right: 0;
							                    bottom: 0;
							                    left: 0;
							                    width: auto !important;
							                    height: auto !important;
							                }
							                @media (max-width: 640px) {
								                .ct-span .wp-video {
								                	width: 280px !important;
							                	}
							                }
							                </style>';
			                				break;
			                			case 'audio':
								    		$output = do_shortcode( '[audio src="' . $attachment_url . '"]' ) . 
							                '<style>
							                .ct-span .wp-audio-shortcode {
							                	width: 480px !important;
							                }
							                @media (max-width: 640px) {
								                .ct-span .wp-audio-shortcode {
								                	width: 280px !important;
							                	}
							                }
							                </style>';
			                				break;
			                			case 'pdf':
								    		$random_number = rand(1,1000);
								    		$output = '<div id="pdf-viewer-'. $random_number .'" class="pdfobject-viewer"></div>
								    				<style>
													.pdfobject-container { width: 48rem; height: 32rem; border: 1rem solid rgba(0,0,0,.1); }
							                        @media screen and (max-width: 768px) {
							                            .pdfobject-container { width: 100%; height: 32rem; }
							                        }
													</style>
								    				<script src="' . ASENHA_URL . 'assets/premium/js/pdfobject.js"></script>
								    				<script>PDFObject.embed("' . $attachment_url . '", "#pdf-viewer-'. $random_number .'");</script>';
								    		break;
			                		}
					                break;
			                	case 'attachment_id':
			                		$output = $attachment_id;
			                		break;
			                	case 'url':
			                		if ( isset( $atts['file_link'] ) && 'yes' == $atts['file_link'] ) {
				                		if ( isset( $atts['link_text'] ) && ! empty( $atts['link_text'] ) ) {
				                			$output = '<a href="' . $attachment_url . '">' . $atts['link_text'] . '</a>';
				                		} else {
				                			$output = '<a href="' . $attachment_url . '">' . $attachment_url . '</a>';
				                		}
			                		} else {
				                		$output = $attachment_url;	                			
			                		}
			                		break;
			                }
		                }
			        }
			        break;
			        
			    case 'gallery':
			    	$attachment_ids = $field['value'];
			    	
			    	// Convert attachment IDs to array for justified and masonry galleris
			    	if ( 'justified' == $atts['gallery_type'] 
			    		|| 'masonry' == $atts['gallery_type'] 
			    	) {
				    	$attachment_ids = explode( ',', $attachment_ids );		    		
			    	}

	                if ( ! isset( $atts['gallery_type'] ) || empty( $atts['gallery_type'] ) ) {
	                	$atts['gallery_type'] = 'justified';
	                }

	                $image_size = isset( $atts['gallery_image_size'] ) && ! empty( $atts['gallery_image_size'] ) ? $atts['gallery_image_size'] : 'medium';

	                switch ( $atts['gallery_type'] ) {
	                	case 'grid':
	                		$columns = isset( $atts['gallery_grid_columns'] ) ? intval( $atts['gallery_grid_columns'] ) : 5;
	                		$output = get_cf_gallery_grid( $attachment_ids, $image_size, $columns );
	                		break;
	                	case 'justified':
	                		$output = get_cf_gallery_justified( $attachment_ids, $image_size );
	                		break;
	                	case 'masonry':
	                		$output = get_cf_gallery_masonry( $attachment_ids, $image_size );	                	
	                		break;
	                }
			    
			    	break;
			}
        }

    	return $output;
    }

    /**
     * Handler for fields that should return a URL.
     */
	function ase_url_handler( $atts ){

        $field_name = $atts['settings_path'];
        $settings_page = $atts['settings_page'];
        $field = $this->get_ase_field_data( $field_name, $settings_page );

		$url = '';

		if ( empty( $field ) ) {
			return $url;	
		}

        if ( isset( $field['type'] ) ) {
			switch ( $field['type'] ) {
				case 'file':			
					if ( 'image' == $field['options']['file_type'] ) {
		                $image_id = null;
		                $image_url = null;

		                $image_size = $this->get_image_size( $atts['size'] );
		                
		                if ( isset( $field['value'] ) && is_numeric( $field['value'] ) ) {
		                    $image_id = $field['value'];
		                    $image_url = wp_get_attachment_image_src( $image_id, $image_size )[0];
		                }

		                $url = $image_url;
					} else {
		                if ( isset( $field['value'] ) && is_numeric( $field['value'] ) ) {
							$attachment_url = wp_get_attachment_url( $field['value'] );                	
		                }
		
		                $url = $attachment_url;
					}
					break;
	            case 'text':
	            	if ( ! empty( $field['value'] ) ) {
		            	$url = $field['value'];
	            	}
	            	break;
	            case 'hyperlink':
	            	// Field value is an array of URL, text, target
	            	if ( is_array( $field['value'] ) ) {
	            		$url = $field['value']['url']; // the URL
	            	}
					break;
	            case 'relationship':
	            	// Only output URL if the relationship field has one related entity, not less, not more.
	            	if ( is_array( $field['value'] ) && 1 === count( $field['value'] ) ) {
	            		$url = get_the_permalink( $field['value'][0] );
	            	}
					break;
	            case 'term':
	            	// Only output URL if the term field has one related term, not less, not more.
	            	if ( is_array( $field['value'] ) && 1 === count( $field['value'] ) ) {
	            		$url = get_term_link( intval( $field['value'][0] ) );
	            	}
			}
        }

		return esc_url_raw( $url );
	}

    /**
     * Handler for image fields that should return the attachment ID.
     */
    public function ase_image_id_handler( $atts ) {
        $field_name = $atts['settings_path'];
        $settings_page = $atts['settings_page'];
        
        $field = $this->get_ase_field_data( $field_name, $settings_page );

		$image_id = '';
		
		if ( isset( $field['value'] ) && is_numeric( $field['value'] ) ) {
			$image_id = $field['value']; // attachment ID
		}

		return $image_id;
    }

    /**
     * Common helper to get complete field data
     */
    public function get_ase_field_data( $field_name, $settings_page = false ) {
		global $post;
		$queried_object = get_queried_object();
		
		$field_info = get_cf_info( $field_name );

        if ( isset( $field_info['option_pages'] ) && ! empty( $field_info['option_pages'] ) ) {
            $options_pages_ids = array_keys( $field_info['option_pages'] );
            // Use the first ID for now, which is the most common use case.
            // i.e. a field group is most probably only assigned to a singe option page
            $post_id = $options_pages_ids[0];
        }  else if ( is_a( $queried_object, 'WP_Term' ) ) {
            $taxonomy = $queried_object->taxonomy;
            $term_id = $queried_object->term_id;
            $post_id = $taxonomy . '_' . $term_id;
        } else {
        	$post_id = $post->ID;
        }

        $field = get_cf_info( $field_name, $post_id );
        $field['value'] = CFG()->get( $field_name, $post_id, array( 'format' => 'raw' ) );

        return $field;
    }
    
    /**
     * Get the image size
     */
    public function get_image_size( $size_attribute = '' ) {

        $image_size = empty( $size_attribute ) ? '' : strtolower( $size_attribute ); // either size slug, e.g. medium, or size as width x height, e.g. 1920x1080

        if ( ! empty( $image_size ) ) {
            $image_size = explode( 'x', $image_size );

            if ( 1 == count( $image_size ) ) {
                $image_size = $image_size[0]; // thumbnail, medium, medium_large, large or full
            } else {
                $image_size = array_map( 'intval', $image_size ); // e.g. 1920x1080
            }                	
        } else {
        	$image_size = 'medium';	                	
        }
        
        return $image_size;
    }


}

new Ase_Oxygen_Integration();