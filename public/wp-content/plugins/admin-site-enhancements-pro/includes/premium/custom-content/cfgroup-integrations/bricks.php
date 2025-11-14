<?php
namespace Bricks\Integrations\Dynamic_Data\Providers;

class Provider_Ase extends Base {

	public function register_tags() {
		$fields = self::get_fields();
		
		foreach ( $fields as $field ) {
			$this->register_tag( $field );
		}
	}
	
	/**
	 * Get all ASE fields from all published custom field groups
	 * 
	 * @return array $ase_fields an indexed array of ASE custom fields
	 */
	public static function get_fields() {
		$cache_key = 'ase_fields_for_bricks';
		$ase_fields   = wp_cache_get( $cache_key, 'bricks' );

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

			// Get CFG placed on taxonomy terms
			$cfgroup_ids_for_taxonomy_terms = get_cfgroup_ids_by_placement( 'taxonomy-terms' );
			
			// Assemble array of fields with additional Bricks property for marking location/placement
			$ase_fields = array();
			
			if ( ! empty( $cfgroup_ids ) ) {
				foreach ( $cfgroup_ids as $cfgroup_id ) {
					$cfgroup_fields = CFG()->find_fields( array( 'group_id' => $cfgroup_id ) );

					if ( ! is_array( $cfgroup_fields ) ) {
						continue;
					}
					
					$is_for_taxonomy_terms = false;
					if ( in_array( $cfgroup_id, $cfgroup_ids_for_taxonomy_terms ) ) {
						$is_for_taxonomy_terms = true;
						
					}

					foreach ( $cfgroup_fields as $field ) {
						// Let's check the placement of the field / field group
						if ( isset( $field['option_pages'] ) & ! empty( $field['option_pages'] ) ) {
							// Options page
							$field['_bricks_locations'] = array( 'option' );
						} else if ( $is_for_taxonomy_terms ) {
							$field['_bricks_locations'] = array( 'term' );
						} else {
							// A post type
							$field['_bricks_locations'] = array( 'post' );
						}

						$ase_fields[] = $field;
					}
				}
			}

			wp_cache_set( $cache_key, $ase_fields, 'bricks', MINUTE_IN_SECONDS );
		}
		// vi( $ase_fields );
		
		// We need to move a repeater field's sub-fiels into the repeater's data array under 'sub_fields' key
		// Thisi s needed for register_tag() later to work recursively taking into account parent field >> sub-field relationship
		$sub_fields = array();
		foreach ( $ase_fields as $index => $ase_field ) {
			if ( $ase_field['parent_id'] > 0 ) {
				$sub_fields[$index] = $ase_field;
				unset( $ase_fields[$index] );
			}
		}

		// Reindex the array, so the indexes are sequential again
		$ase_fields = array_values( $ase_fields ); // Fields with no parent
		$sub_fields = array_values( $sub_fields ); // Fields with parent-repeater
		// vi( $sub_fields );
		
		// We attach sub fields to their parent 'repeater' fields
		$ase_fields_with_sub_fields = array();
		
		foreach ( $sub_fields as $sub_field_index => $sub_field ) {
			foreach( $ase_fields as $ase_field_index => $ase_field ) {
				if ( 'repeater' == $ase_field['type'] && $sub_field['parent_id'] == $ase_field['id'] ) {
					if ( ! isset( $ase_fields_with_sub_fields[$ase_field_index] ) ) {
						$ase_fields_with_sub_fields[$ase_field_index] = $ase_field;					
					}
					$ase_fields_with_sub_fields[$ase_field_index]['sub_fields'][] = $sub_field;
					// Remove sub-field already assigned to a parent field
					unset($sub_fields[$sub_field_index]);
					// At this point, $sub_fields contains fields with a child-repeater parent
				}
			}
		}
		// vi( $ase_fields_with_sub_fields );
		// vi( $sub_fields, '', 'still detached from parent' );

		// Replace the original parent repeater fields with the ones that contains sub-fields
		foreach ( $ase_fields as $ase_field_index => $ase_field ) {
			foreach ( $ase_fields_with_sub_fields as $index => $ase_field_with_sub_fields ) {
				if ( $ase_field_index == $index ) {
					unset( $ase_fields[$ase_field_index] );
					$ase_fields[$ase_field_index] = $ase_field_with_sub_fields;
				}
			}
		}

		// Add back sub fields still detached from child-repeater parent, so they become available in the list of fields for dynamic data source. This works, but the field tag in Bricks dynamic data source does not include the child-repeater tag
		// if ( ! empty( $sub_fields ) ) {
		// 	$ase_fields = array_merge( $ase_fields, $sub_fields );
		// }

		// Let's add the sub fields with a child-repeater parent, into the main fields array, attached to their child-repeater paretns
		foreach ( $ase_fields as $index => $ase_field ) {
			if ( isset( $ase_field['sub_fields'] ) ) {
				foreach ( $ase_field['sub_fields'] as $main_sub_field_index => $main_sub_field ) {
					if ( 'repeater' == $main_sub_field['type'] ) {
						foreach ( $sub_fields as $parentless_sub_field_index => $parentless_sub_field ) {
							if ( $parentless_sub_field['parent_id'] == $main_sub_field['id'] ) {
								$main_sub_field['sub_fields'][] = $parentless_sub_field;
								unset( $sub_fields[$parentless_sub_field_index] );
							}
						}
						
						$ase_field['sub_fields'][$main_sub_field_index] = $main_sub_field;
					}
				}
			}
			
			unset( $ase_fields[$index] );
			$ase_fields[$index] = $ase_field;
		}

		// Reindex the array, so the indexes are sequential again
		$ase_fields = array_values( $ase_fields );

		// vi( $sub_fields, '', 'final' ); // Should be an empty array
		// vi( $ase_fields, '', 'final' ); // Field will contain nested repeaters and their corresponding sub-fields
		
		return $ase_fields;
	}
	
	/**
	 * This will register ASE custom fields into the dynamic data dropdown menus in Bricks
	 * 
	 * @param  array  $field        THe ASE field to register
	 * @param  array  $parent_field Parent field if it exist
	 * @param  array  $parent_tag   Parent tag if it exist
	 */
	public function register_tag( $field, $parent_field = [], $parent_tag = [] ) {
		$contexts = self::get_fields_by_context();
		
		$type = $field['type'];

		if ( ! isset( $contexts[ $type ] ) ) {
			return;
		}

		foreach ( $contexts[$type] as $context ) {

			// Set field name/label. Add parent field name/label to the field name/label if needed
			$name = ! empty( $parent_field['name'] ) ? 'ase__' . $parent_field['name'] . '__' . $field['name'] : 'ase__' . $field['name'];
			$label = ! empty( $parent_field['label'] ) ? $field['label'] . ' (' . $parent_field['label'] . ') ~~ [' . $type . '] ' : $field['label'] . ' ~~ [' . $type . '] ';

			if ( $context === self::CONTEXT_LOOP ) {
				$label = 'ASE ' . ucfirst( $type ) . ': ' . $label;
			}

			$tag = array(
				'group'    => 'ASE',
				'field'    => $field,
				'provider' => $this->name,
			);

			if ( isset( $parent_field['id'] ) && $parent_field > 0 ) {
				// Add the parent field attributes to the child tag so we could retrieve the value of sub-fields
				$tag['parent'] = [
					'key'  => $parent_field['id'],
					'name' => $parent_field['name'],
					'type' => $parent_field['type'],
				];

				if ( ! empty( $parent_field['_bricks_locations'] ) ) {
					$tag['parent']['_bricks_locations'] = $parent_field['_bricks_locations'];
				}
			}

			// Set the tag name and label
			$tag['name']  = '{' . $name . '}';
			$tag['label'] = $label;

			// Mark duplicate tags
			if ( isset( $this->tags[ $name ] ) ) {
				$tag['duplicate'] = true;
			}

			// Register fields for the Loop context ( for repeater and relationship fields )
			if ( $context === self::CONTEXT_LOOP ) {
				$this->loop_tags[ $name ] = $tag;

				// Check for sub-fields (repeater field)
				if ( ! empty( $field['sub_fields'] ) ) {
					foreach ( $field['sub_fields'] as $sub_field ) {
						$this->register_tag( $sub_field, $field, $tag ); // Recursive
					}
				}

			} 
			
			$this->tags[ $name ] = $tag;
		}
	}
	
	/**
	 * Set the Bricks context for each ASE field type
	 */
	public static function get_fields_by_context() {
		$field_contexts = array(
			// Content
			'text'			=> [ self::CONTEXT_TEXT ],
			'textarea'		=> [ self::CONTEXT_TEXT ],
			'wysiwyg'		=> [ self::CONTEXT_TEXT ],
			'file'			=> [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_IMAGE, self::CONTEXT_VIDEO, self::CONTEXT_MEDIA ],
			'gallery'		=> [ self::CONTEXT_IMAGE ],

			// Choice
			'true_false'	=> [ self::CONTEXT_TEXT ],
			'radio'			=> [ self::CONTEXT_TEXT ],
			'select'		=> [ self::CONTEXT_TEXT ],
			'checkbox'		=> [ self::CONTEXT_TEXT ],

			// Relational
			'relationship'	=> [ self::CONTEXT_TEXT, self::CONTEXT_LINK, self::CONTEXT_LOOP ],
			'term'			=> [ self::CONTEXT_TEXT, self::CONTEXT_LINK ],
			'user'			=> [ self::CONTEXT_TEXT ],

			// Special
			'hyperlink'		=> [ self::CONTEXT_TEXT, self::CONTEXT_LINK ],
			'number'		=> [ self::CONTEXT_TEXT ],
			'date'			=> [ self::CONTEXT_TEXT ],
			'time'			=> [ self::CONTEXT_TEXT ],
			'datetime'		=> [ self::CONTEXT_TEXT ],
			'color'			=> [ self::CONTEXT_TEXT ],
			'repeater'		=> [ self::CONTEXT_LOOP ],		
		);
		
		return $field_contexts;
	}

	/**
	 * Get tag value main function
	 *
	 * @param string  $tag The tag name (e.g. ase_my_field).
	 * @param WP_Post $post The post object.
	 * @param array   $args The dynamic data tag arguments.
	 * @param string  $context E.g. text, link, image.
	 *
	 * @return mixed The tag value.
	 */
	public function get_tag_value( $tag, $post, $args, $context ) {
		$tag_info = $this->tags[$tag];
        $field_info = $tag_info['field'];
        
		// Get the post ID and account for the possibility of field being part of an options page
        if ( isset( $field_info['option_pages'] ) && ! empty( $field_info['option_pages'] ) ) {
            $options_pages_ids = array_keys( $field_info['option_pages'] );
            // Use the first ID for now, which is the most common use case.
            // i.e. a field group is most probably only assigned to a singe option page
            $post_id = $options_pages_ids[0];
        } else if ( 'term' == $field_info['_bricks_locations'][0] ) {
        	// Get term info for $post_id when the taxonomy term archive is viewed on the frontend
	        $queried_object = get_queried_object();
	        // vi( $queried_object );
	        if ( is_a( $queried_object, 'WP_Term' ) ) {
                $taxonomy = $queried_object->taxonomy;
                $term_id = $queried_object->term_id;
                $post_id = $taxonomy . '_' . $term_id;
	        }
	        
        	// Get term info for $post_id when the taxonomy term archive is viewed inside the builder
        	if ( is_object( $post ) ) {
		        if ( property_exists( $post, 'post_type' ) && 'bricks_template' === $post->post_type ) {
		        	$template_settings = get_post_meta( $post->ID, '_bricks_template_settings', true );
		        	$template_preview_term = $template_settings['templatePreviewTerm']; // e.g. 'genre::84'
		        	$template_preview_term = explode( '::', $template_preview_term );
		        	$taxonomy = $template_preview_term[0];
		        	$term_id = $template_preview_term[1];
		        }        		
        	}

            $post_id = $taxonomy . '_' . $term_id;
        } else {
			$post_id = isset( $post->ID ) ? $post->ID : '';
        }
	
		$filters = $this->get_filters_from_args( $args ); // defined in base.php
		
		// Default value
		$value = '';
		
		if ( isset( $this->tags[$tag]['field'] ) ) {
			// Get field info
			$field = $this->tags[$tag]['field'];

			// Check for duplicate field already part of another field group
			if ( isset( $this->tags[ $tag ]['duplicate'] ) ) {
				$actual_field = get_cf_info( $field['name'], $post_id );
				
				$field = $actual_field ? $actual_field : $field;
			}

			// Get raw value of field
			$value = $this->get_raw_value( $tag, $post_id );

			// Get return format if it's defined
			if ( isset( $field['options']['return_value'] ) ) {
				$return_format = $field['options']['return_value'];
			} 
			elseif ( isset( $field['options']['format'] ) ) {
				$return_format = $field['options']['format'];			
			}
			else {
				$return_format = '';
			}

			// @since 1.8 - New array_value filter. Once used, we don't want to process the field type logic
			if ( isset( $filters['array_value'] ) && is_array( $value ) ) {
				// Force context to text
				$context = 'text';
				$value   = $this->return_array_value( $value, $filters ); // defined in base.php
			}

			// Process field type logic
			else {
				switch ( $field['type'] ) {
					case 'file':
						if ( empty( $value ) ) {
							$value = array();
						} else {
							$filters['object_type'] = 'media';
							$filters['separator']   = '';

							// Return value as the attachment ID
							if ( $return_format === 'id' ) {
								$value = $value;
							} elseif ( $return_format === 'url' ) {
								if ( false !== strpos( $value, 'http' ) ) {
									$value = attachment_url_to_postid( $value ); // WP core function								
								} else if ( is_numeric( $value ) ) {
									// In a repeater, when the field's return value is set to 'File URL'
									$value = $value;
								}
							}
						}
						break;
						
					case 'gallery':
						if ( empty( $value ) ) {
							$value = array();
						} else {
							$filters['object_type'] = 'media';
							$filters['separator']   = '';
							
							if ( is_array( $value ) ) {
								// Do nothing, $value is already an array of attachment IDs
							} else {
								// $value is comma-separated attachment IDs
								$attachment_ids = explode( ',', $value );								
								$value = $attachment_ids; // Array of attachment IDs
							}
						}
						break;

					case 'true_false':
						// return ( $value ) ? 'True' : 'False';
		                if ( in_array( $value, array(
		                    1,
		                    'True',
		                    'Yes',
		                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512"><path fill="currentColor" d="m173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69L432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001"/></svg>',
		                    '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-4.96 0-9 4.035-9 9s4.04 9 9 9h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm14 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7s3.121-7 7-7"/></svg>',
		                    0,
		                    'False',
		                    'No',
		                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path fill="currentColor" d="M62 10.571L53.429 2L32 23.429L10.571 2L2 10.571L23.429 32L2 53.429L10.571 62L32 40.571L53.429 62L62 53.429L40.571 32z"/></svg>',
		                    '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-.621 0-1.227.066-1.813.188a9.238 9.238 0 0 0-.875.218A9.073 9.073 0 0 0 .72 12.5c-.114.27-.227.531-.313.813A8.848 8.848 0 0 0 0 16c0 .93.145 1.813.406 2.656c.004.008-.004.024 0 .032A9.073 9.073 0 0 0 5.5 24.28c.27.114.531.227.813.313A8.83 8.83 0 0 0 9 24.999h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm0 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7c0-.242.008-.484.031-.719A6.985 6.985 0 0 1 9 9m5.625 0H23c3.879 0 7 3.121 7 7s-3.121 7-7 7h-8.375C16.675 21.348 18 18.828 18 16c0-2.828-1.324-5.348-3.375-7"/></svg>',
		                ) ) ) {
		                    if ( 1 === $value ) {
		                        return 'True';
		                    } else if ( 0 === $value ) {
		                        return 'False';
		                    } else {
		                        return $value;
		                    }
		                }
						break;

					case 'radio':
					case 'select':
					case 'checkbox':
						if ( isset( $tag_info['parent'] ) && 'repeater' == $tag_info['parent']['type'] ) {
							// This is a repeater sub-field. At this point, $value is indexed array of value(s)
							$new_value = array();
							$choices = $tag_info['field']['options']['choices'];
							if ( ! isset( $filters['value'] ) && is_array( $value ) && ! empty( $value ) ) {
								// If :value filter is NOT set, we reformat $value to be index of value(s) => label(s)
								foreach ( $value as $value_key ) {
									foreach ( $choices as $choice_key => $choice_label ) {
										if ( $value_key == $choice_key ) {
											$new_value[$value_key] = $choice_label;
										}
									}
								}
								$value = $new_value;								
							} else {
								// Do nothing, values will be returned by default
							}
						} else {
							// This is a regular field. At this point, $value is array of value(s) => label(s)
							if ( isset( $filters['value'] ) ) {
								// If :value filter is set
								if ( is_array( $value ) && ! empty( $value ) ) {
									// We convert $value into array of value(s) => value(s) for format_value_for_context() to process.
									foreach ( $value as $key => $item ) {
										$value[$key] = $key;
									}
								}
							}
						}
						break;

					case 'term':
						$filters['object_type'] = 'term';
						$taxonomies = $field['options']['taxonomies'];
						if ( count( $taxonomies ) === 1 ) {
							$filters['taxonomy'] = $taxonomies[0];
						} else {
							$filters['taxonomy'] = ''; // this will result in term ID being output on the frontend
						}

						// NOTE: Undocumented
						$show_as_link = apply_filters( 'bricks/acf/taxonomy/show_as_link', true, $value, $field );
						if ( $show_as_link ) {
							$filters['link'] = true;
						}

						// If $value is an array where the value of the first element is a string of comma-separated term IDs.
			            if ( is_string( $value[0] ) && false !== strpos( $value[0], ',' ) ) {
		                    $value = explode( ',', $value[0] ); // Turns '123,456' into array( 0 => '123', 1 => '234' )
			            }
						
						// At this point, $value is an indexed array of ter, IDs as strings, and the value of the first element is not a string of comma-separated term IDs. Let's convert to integers.
						if ( is_array( $value ) && ! empty( $value ) ) {
							foreach ( $value as $index => $string ) {
								unset( $value[$index] );
								$value[$index] = intval( $string );
							}
							// Re-order the index
							$value = array_values( $value );
						}
						break;
						
					case 'user':
						$filters['object_type'] = 'user';

						// If $value is an array where the value of the first element is a string of comma-separated user IDs.
			            if ( is_string( $value[0] ) && false !== strpos( $value[0], ',' ) ) {
		                    $value = explode( ',', $value[0] ); // Turns '123,456' into array( 0 => '123', 1 => '234' )
			            }
					
						// At this point, $value is an indexed array of user IDs as strings, and the value of the first element is not a string of comma-separated user IDs. Let's convert to integers.
						if ( is_array( $value ) && ! empty( $value ) ) {
							foreach ( $value as $index => $string ) {
								unset( $value[$index] );
								$value[$index] = intval( $string );
							}
							// Re-order the index
							$value = array_values( $value );
						}
						break;
						
					case 'relationship':
						$filters['object_type'] = 'post';
						$filters['link']        = true;

						if ( ! is_null( $value ) ) {
							// If $value is an array where the value of the first element is a string of comma-separated post IDs.
				            if ( is_array( $value ) 
				            	&& isset( $value[0] )
				            	&& is_string( $value[0] ) 
				            	&& false !== strpos( $value[0], ',' ) 
				            ) {
			                    $value = explode( ',', $value[0] ); // Turns '123,456' into array( 0 => '123', 1 => '234' )
				            }

							// At this point, $value is an indexed array of post IDs as strings, and the value of the first element is not a string of comma-separated post IDs. Let's convert the IDs to integers.
							if ( is_array( $value ) 
								&& ! empty( $value ) 
								&& false === strpos( $value[0], ',' ) 
							) {
								foreach ( $value as $index => $string ) {
									unset( $value[$index] );
									$value[$index] = intval( $string );
								}
								// Re-order the index
								$value = array_values( $value );
							}							
						}
						break;

					case 'hyperlink':
						// We need to return the URL here
						if ( isset( $tag_info['parent'] ) && 'repeater' == $tag_info['parent']['type'] ) {
							// This is a repeater sub-field. At this point, $value is an array of url, text and target
							if ( isset( $value['url'] ) ) {
								// if ( 'php' == $return_format ) {
								// 	$value = $value['url'];								
								// }
								// if ( 'html' == $return_format ) {
									$value = '<a href="' . $value['url'] . '" target="' . $value['target'] . '">' . $value['text'] . '</a>';
								// }
							}
						} else {
							// This is a regular field. Let's get raw value as array of url, text and target
							$raw_value = get_cf( $field['name'], 'raw', $post_id ); // array of url, label, target
							if ( isset( $value['url'] ) ) {
								$value = $raw_value['url'];
							}
						}
						break;

					case 'date':
						// We need to return unix time here for format_value_for_context() and then format_value_for_text() in base.php
						if ( ! is_null( $value ) ) {
							$value = strtotime( $value );						
						}
						$filters['object_type'] = 'date';
						break;

					case 'time':
						$frontend_display_format = $field['options']['frontend_display_format'];
						if ( isset( $tag_info['parent'] ) && 'repeater' == $tag_info['parent']['type'] ) {
							$value = get_cf_time( $value, $frontend_display_format );
						} else {
							$value = get_cf( $field['name'], 'default', $post_id ); // Time formatted according to time field options
						}
						break;
				}
			}

		}

		// STEP: Apply context (text, link, image, media)
		$value = $this->format_value_for_context( $value, $tag, $post_id, $filters, $context );

		return $value;
	}

	/**
	 * Get the field raw value
	 *
	 * @param array      $tag The tag name (e.g. ase_my_field).
	 * @param int|string $post_id The post ID.
	 */
	public function get_raw_value( $tag, $post_id ) {
		$tag_object = $this->tags[ $tag ];
		$field      = $tag_object['field'];
		$is_option = false; // Not from an options page, ASE does not currently support it
		
		$is_looping = \Bricks\Query::is_looping();
			
		// STEP: Check if in a Query Loop from Relationship, or Repeater field
		if ( \Bricks\Query::is_looping() ) {
			$query_type = \Bricks\Query::get_query_object_type();

			// Loop belongs to this provider
			if ( array_key_exists( $query_type, $this->loop_tags ) ) {
				// Query Loop tag object
				$loop_tag_object = $this->loop_tags[ $query_type ];

				// Query Loop tag ASE field ID
				$loop_tag_field_name = isset( $loop_tag_object['field']['name'] ) ? $loop_tag_object['field']['name'] : false;

				if ( empty( $loop_tag_field_name ) ) {
					return '';
				}

				/**
				 * Loop created by an ASE Relationship or Repeater field
				 */
				if (
					isset( $loop_tag_object['field']['type'] ) &&
					in_array( $loop_tag_object['field']['type'], array( 'relationship' ) )
				) {
					// The loop already sets the global $post
					$post_id = get_the_ID();

					// Is a regular field
					// return get_cf( $field['name'], 'default', $post_id );
					return CFG()->get( $field['name'], $post_id, array( 'format' => 'api' ) );
				}

				// NOTE: Bricks needs to build a path to get the final value from the $loop_object
				// The iteration starts on the field to which we need to get the value and iterates its parent until it is the query loop field.

				// Store the field names while iterating
				$value_path = [];

				// Get the first parent field object
				$parent_field = isset( $tag_object['parent']['name'] ) ? CFG()->get_field_info( $tag_object['parent']['name'], $post_id ) : false;

				// Check if the parent field is the loop field; if not, iterate up
				while ( isset( $parent_field['name'] ) && $parent_field['name'] !== $loop_tag_field_name ) {
					if ( isset( $parent_field['name'] ) ) {
						$value_path[] = $parent_field['name'];

						// Get the parent field tag object (as registered in Bricks)
						$parent_tag = isset( $this->tags[ $parent_field['name'] ] ) ? $this->tags[ $parent_field['name'] ] : false;
					} else {
						$parent_tag = false;
					}

					// Get the parent of the parent
					$parent_field = isset( $parent_tag['parent']['name'] ) ? CFG()->get_field_info( $parent_tag['parent']['name'], $post_id ) : false;
				}

				// The current loop object (array of values)
				$narrow_values = \Bricks\Query::get_loop_object();

				// GENERATES FATAL ERROR
				$found_value = isset( $narrow_values[ $field['name'] ] ) ? $narrow_values[ $field['name'] ] : '';

				if ( ! $is_option ) {
					return $found_value;
				}
			}
		}

		// STEP: Still here, get the regular (not a sub-field) value for this field
		if ( in_array( $field['type'], array( 'gallery', 'date', 'datetime' ) ) ) {
			// return get_cf( $field['name'], 'raw', $post_id );
			return CFG()->get( $field['name'], $post_id, array( 'format' => 'raw' ) );
		} else {
			// return get_cf( $field['name'], 'default', $post_id );
			return CFG()->get( $field['name'], $post_id, array( 'format' => 'api' ) );
		}
	}

	/**
	 * Set the loop query if exists
	 *
	 * @param array $results
	 * @param Query $query
	 * @return array
	 */
	public function set_loop_query( $results, $query ) {
		if ( ! array_key_exists( $query->object_type, $this->loop_tags ) ) {
			return $results;
		}

		$tag_object = $this->loop_tags[ $query->object_type ];

		$field = $this->loop_tags[ $query->object_type ]['field'];

		$looping_query_id = \Bricks\Query::is_any_looping();

		if ( $looping_query_id ) {
			$loop_query_object_type = \Bricks\Query::get_query_object_type( $looping_query_id );
			$loop_object_type       = \Bricks\Query::get_loop_object_type( $looping_query_id );

			// Maybe it is a nested loop
			if ( array_key_exists( $loop_query_object_type, $this->loop_tags ) ) {
				$loop_object = \Bricks\Query::get_loop_object( $looping_query_id );

				// If this is a nested repeater
				if ( is_array( $loop_object ) && array_key_exists( $field['name'], $loop_object ) ) {
					return $loop_object[ $field['name'] ];
				}

				// If this is a nested relationship
				if ( is_object( $loop_object ) && is_a( $loop_object, 'WP_Post' ) ) {
					$ase_object_id = get_the_ID();
				}
			}

			/**
			 * Check: Is it a post loop?
			 *
			 * @since 1.7: use $loop_object_type instead of $loop_query_object_type so that it works with user custom queries via PHP filters
			 */
			elseif ( $loop_object_type === 'post' ) {
				$ase_object_id = get_the_ID();
			}

		}

		if ( ! isset( $ase_object_id ) ) {
			// Get the $post_id or the template preview ID
			$post_id = \Bricks\Database::$page_data['preview_or_post_id'];

			$ase_object_id = $this->get_object_id( $field, $post_id );
		}

		$results = get_cf( $field['name'], 'raw', $ase_object_id ); // array of IDs as strings

		if ( 'relationship' == $field['type'] ) {
			if ( is_array( $results ) && ! empty( $results ) ) {
				// Convert to array of IDs as integers
				foreach ( $results as $index => $result ) {
					unset( $results[$index] );
					$results[$index] = intval( $result );
				}				
			}
		}

		return ! empty( $results ) ? $results : array();
	}

	/**
	 * Manipulate the loop object
	 *
	 * @param array  $loop_object
	 * @param string $loop_key
	 * @param Query  $query
	 * @return array
	 */
	public function set_loop_object( $loop_object, $loop_key, $query ) {
		if ( ! array_key_exists( $query->object_type, $this->loop_tags ) ) {
			return $loop_object;
		}

		// Check if the ASE field is relationship (list of posts)
		$field = $this->loop_tags[ $query->object_type ]['field'];

		// 'relationship' needs to set the global $post (@since 1.8.6)
		if ( in_array( $field['type'], [ 'relationship' ] ) ) {
			global $post;
			$post = get_post( $loop_object );
			setup_postdata( $post );

			// The $loop_object could be a post ID or a post object, returning the post object (@since 1.5.3)
			return $post;
		}

		return $loop_object;
	}

	/**
	 * Calculate the object ID to be used when fetching the field value
	 *
	 * @param array $field
	 * @param int   $post_id
	 */
	public function get_object_id( $field, $post_id ) {
		$locations = isset( $field['_bricks_locations'] ) ? $field['_bricks_locations'] : array();

		// If this field belongs to a options page, we get the post ID of the option page
		if ( in_array( 'option', $locations )
			&& isset( $field['option_pages'] ) 
			&& ! empty( $field['option_pages'] ) 
		) {
			$post_ids = array_keys( $field['option_pages'] );
			// Assume the field is only attached to one options page
			$post_id = $post_ids[0];
		}

		// In a Query Loop
		if ( \Bricks\Query::is_looping() ) {
			$object_type = \Bricks\Query::get_loop_object_type();
			$object_id   = \Bricks\Query::get_loop_object_id();

			// Terms loop
			// if ( $object_type == 'term' && in_array( $object_type, $locations ) ) {
			// 	$object = \Bricks\Query::get_loop_object();

			// 	return isset( $object->taxonomy ) ? $object->taxonomy . '_' . $object_id : $post_id;
			// }

			// Users loop
			// if ( $object_type == 'user' && in_array( $object_type, $locations ) ) {
			// 	return 'user_' . $object_id;
			// }
		}

		$queried_object = \Bricks\Helpers::get_queried_object( $post_id );

		// if ( in_array( 'term', $locations ) && is_a( $queried_object, 'WP_Term' ) ) {
		// 	if ( isset( $queried_object->taxonomy ) && isset( $queried_object->term_id ) ) {
		// 		return $queried_object->taxonomy . '_' . $queried_object->term_id;
		// 	}
		// }

		// if ( in_array( 'user', $locations ) ) {
		// 	if ( is_a( $queried_object, 'WP_User' ) && isset( $queried_object->ID ) ) {
		// 		return 'user_' . $queried_object->ID;
		// 	}

		// 	if ( count( $locations ) == 1 ) {
		// 		return 'user_' . get_current_user_id();
		// 	}
		// }

		// Default
		return $post_id;
	}

}