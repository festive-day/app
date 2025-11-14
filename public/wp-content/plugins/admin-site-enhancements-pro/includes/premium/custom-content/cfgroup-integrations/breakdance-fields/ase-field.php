<?php

namespace Ase\Integrations\Breakdance;

use Breakdance\DynamicData\DynamicDataController;
use Breakdance\DynamicData\LoopController;
use function Breakdance\isRequestFromBuilderDynamicDataGet;
use DateTimeZone;

class Breakdance_Ase_Field {

    /**
     * @var Post ID
     */
    public int $post_id;

    /**
     * @var ASE Field
     */
    public array $field;

    /**
     * @var Output type in Breakdance: string, url, image, video
     */
    public string $bd_output_type;

    /**
     * @param AseString $field
     */
    public function __construct( $post_id, $field, $bd_output_type ) {
        $this->post_id = $post_id;
        $this->field = $field;
        $this->bd_output_type = $bd_output_type;
    }
    
    /**
     * Get value for regular fields and repeater sub-fields
     */
    public function get_field_value() {
    	
    	// Let's layout the foundation here. 
    	// Check if there's a parent field and whether the returned value should be a regular field, or a repeater's sub-field
    	
    	if ( ! empty( $this->field['parent_name'] ) ) {
    		// Field is a sub-field
    		$should_return_sub_field_value = true;
	        $parent_field = $this->field['parent_name'];
	        $parent_field_data = CFG()->get_field_info( $parent_field );

	        $parent_field_value = '';
	        $sub_field_value = '';
	        $sub_field_value_placeholder = 'Placeholder for <span style="font-weight:600;">' . $this->field['label'] . '</span> field.';

	        if ( $parent_field_data ) {
	            $breakdanceLoop = \Breakdance\DynamicData\LoopController::getInstance( $parent_field_data['id'] . '_' . $parent_field_data['name'] );
	            // vis( $breakdanceLoop, 'breakdanceLoop' ); // We use vis to get the correct variable name shown in the inspector dashboard

	            if ( isset( $breakdanceLoop->field['field'] ) and isset( $breakdanceLoop->field['index'] ) ) {
	                $loopField = $breakdanceLoop->field['field'];
	                $loopIndex = $breakdanceLoop->field['index'];

	                if ( $parent_field == $loopField['name'] ) {
	                    $parent_field_value = get_cf( $this->field['parent_name'], 'raw' );
	                    // vis( $parent_field_value, 'parent_field_value', '', 'for ' . $this->field['name'] . ' - Loop field: ' . $loopField['name'] );
	                }
	            }
	        }

	        if ( isRequestFromBuilderDynamicDataGet() ) {
	        	// This results in error in Breakdance builder's Global Block edit screen. Kept here for future reference.
	        	// global $post;
	        	// $post_id = $post->ID;
	        	// $parent_field_value = get_cf( $this->field['parent_name'], 'raw', $post_id );

	        	// We set this as empty. In Global Blocks editor preview, get_cf() for parent field will return empty anyway
	        	$parent_field_value = '';
	        }
    	} else {
    		// Field is a regular field
    		$should_return_sub_field_value = false;
	    	$raw_value = get_cf( $this->field['name'], 'raw' );
	    	$value = get_cf( $this->field['name'] );
    	}
    	
    	// Common variables
    	$class_name = str_replace( '_', '-', $this->field['name'] );

    	// ============================================================
    	// For fields with string return type in Breakdance
    	// ============================================================

    	if ( 'string' == $this->bd_output_type ) {
    		$field_options = $this->field['options'];

    		switch ( $this->field['type'] ) {

	        	case 'text':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field = CFG()->get_field_info( $this->field['name'] );
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
		        			if ( ! empty( $sub_field_value ) ) {
				        		switch ( $sub_field['options']['text_type'] ) {
				        			case 'url':
				        				return (string) wp_oembed_get( $sub_field_value );
				        				break;

				        			case 'email':
				        				return (string) '<a href="mailto:' . $sub_field_value . '">' . $sub_field_value . '</a>';
				        				break;

				        			case 'phone':
				        				return (string) '<a href="tel:' . $sub_field_value . '">' . $sub_field_value . '</a>';
				        				break;

				        			case 'shortcode':
				        				return (string) do_shortcode( $sub_field_value );
				        				break;
				        				
				        			default:
				        				return (string) $sub_field_value;
			        			}
		        			} else {
		        				return (string) '';
		        			}
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
		        		switch ( $this->field['options']['text_type'] ) {
		        			case 'url':
		        				return (string) wp_oembed_get( $value );
		        				break;

		        			case 'email':
		        				return (string) '<a href="mailto:' . $value . '">' . $value . '</a>';
		        				break;

		        			case 'phone':
		        				return (string) '<a href="tel:' . $value . '">' . $value . '</a>';
		        				break;

		        			case 'shortcode':
		        				return (string) do_shortcode( $value );
		        				break;

		        			default:
		        				return (string) $value;
		        		}
	        		}	        	
	        		break;
	        		
	        	case 'textarea':
	        	case 'wysiwyg':
	        	case 'number':
	        	case 'color':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			if ( 'wysiwyg' == $this->field['type'] ) {
			                    $sub_field_value = wpautop( $parent_field_value[$loopIndex][$this->field['name']] );
		        			} else {
			                    $sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
		        			}
		                    return (string) ( ! empty( $sub_field_value ) ) ? $sub_field_value : '';
		    				break;
		        		} else {
				            return (string) $sub_field_value_placeholder;		        				
		        		}	        			
	        		} else {
	        			if ( 'wysiwyg' == $this->field['type'] ) {
	        				if ( is_string( $value ) ) {
			        			return (string) wpautop( $value );
	        				} else {
	        					return (string) '';
	        				}
	        			} else {
		        			return (string) $value;
	        			}
	        		}
	        		break;

	        	case 'date':
	        		$frontend_display_format = isset( $field_options['frontend_display_format'] ) ? $field_options['frontend_display_format'] : 'F j, Y';
	        		
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
			                    $sub_field_value = date( $frontend_display_format, strtotime( (string) $parent_field_value[$loopIndex][$this->field['name']] ) );
			                    return (string) ( ! empty( $sub_field_value ) ) ? $sub_field_value : '';
			        		} else {
					            return (string) $sub_field_value_placeholder;	        			
			        		}
	        		} else {
	        			if ( ! empty( $value ) ) {
		                    return (string) date( $frontend_display_format, strtotime( (string) $value ) );        				
	        			} else {
	        				return (string) '';	
	        			}
	        		}
		        	break;

	        	case 'time':
	        		if ( $should_return_sub_field_value ) {
		        		$frontend_display_format = isset( $this->field['options']['frontend_display_format'] ) ? $this->field['options']['frontend_display_format'] : 'G:i';
		        		if ( ! empty( $parent_field_value ) ) {
		        				$raw_sub_field_value = (string) $parent_field_value[$loopIndex][$this->field['name']];
		        				$sub_field_value = get_cf_time( $raw_sub_field_value, $frontend_display_format );
			                    return (string) ( ! empty( $sub_field_value ) ) ? $sub_field_value : '';
			        		} else {
					            return (string) $sub_field_value_placeholder;
			        		}
	        		} else {
	        			if ( ! empty( $value ) ) {
		                    return (string) $value; // Already formatted
	        			} else {
	        				return (string) '';	
	        			}
	        		}
		        	break;

	        	case 'datetime':
		            $datetime_output_format = get_cf_datetime_output_format( $field_options );
		            $timezone_object = new DateTimeZone( 'UTC' );
	        		
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
			                    $sub_field_value = wp_date( $datetime_output_format, strtotime( (string) $parent_field_value[$loopIndex][$this->field['name']] ), $timezone_object );
			                    return (string) ( ! empty( $sub_field_value ) ) ? $sub_field_value : '';
			        		} else {
					            return (string) $sub_field_value_placeholder;	        			
			        		}
	        		} else {
	        			// Let's use the raw cf_value to ensure it works with strtotime()
	        			if ( ! empty( $raw_value ) ) {
		                    return (string) wp_date( $datetime_output_format, strtotime( (string) $raw_value ), $timezone_object );
	        			} else {
	        				return (string) '';	
	        			}
	        		}
		        	break;
		        	
        		case 'hyperlink':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']]; // array of url, text, target
		        			return (string) get_cf_hyperlink( $sub_field_value );
		        		} else {
				            return (string) $sub_field_value_placeholder;	        			
		        		}	        			
	        			break;
    	    		} else {
		        		if ( isset( $this->field['options']['format'] ) ) {
		        			switch ( $this->field['options']['format'] ) {
		        				case 'html':
				        			return (string) $value;
				        			break;
				        			
				        		case 'php':
				        			return (string) get_cf_hyperlink( $value );
				        			break;
		        			}
		        		}
	        		}
        		break;

        		case 'true_false':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];

			        		if ( '1' === $sub_field_value ) {
			        			return (string) 'Yes';
			        		} else {
			        			return (string) 'No';
			        		}
		        		} else {
				            return (string) $sub_field_value_placeholder;	        			
		        		}
    	    		} else {
		        		if ( true === $value ) {
		        			return (string) 'Yes';
		        		} else {
		        			return (string) 'No';
		        		}
	        		}
        		break;


        		case 'radio':
        		case 'select':
        		case 'checkbox':
	        		$new_value = array();
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
							$sub_field = CFG()->get_field_info( $this->field['name'] );
							$sub_field_choices = $sub_field['options']['choices'];
							$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']]; // indexed array of values

			        		if ( is_array( $sub_field_value ) && ! empty( $sub_field_value ) ) {
			        			foreach ( $sub_field_value as $value ) {
			        				foreach ( $sub_field_choices as $choice_value => $choice_label ) {
			        					if ( $choice_value == $value ) {
					        				$new_value[] = $choice_label;		        					
			        					}
			        				}
			        			}
			        			return (string) implode( ', ', $new_value );
			        		} else {
			        			return (string) '';
			        		}
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
		        		if ( is_array( $value ) && ! empty( $value ) ) {
		        			foreach ( $value as $key => $val ) {
		        				$new_value[] = $val;
		        			}
		        			return (string) implode( ', ', $new_value );
		        		} else {
		        			return (string) $value;
		        		}
	        		}
        		break;

        		case 'file':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field = CFG()->get_field_info( $this->field['name'] );
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
		        			if ( ! empty( $sub_field_value ) && is_numeric( $sub_field_value ) ) {
		        				$file_url = wp_get_attachment_url( $sub_field_value );
				        		switch ( $sub_field['options']['file_type'] ) {
				        			case 'image':
				        				return (string) get_cf_image_view( $sub_field_value, 'medium_large', $class_name );
				        				break;
				        			case 'video':
				        				return (string) get_cf_video_player( $file_url );
				        				break;
				        			case 'audio':
				        				return (string) get_cf_audio_player( $file_url );
					                    // return (string) do_shortcode( '[audio src="' . $file_url . '"]' );
				        				break;
				        			case 'pdf':
				        				return (string) get_cf_pdf_viewer( $file_url );
				        				break;
				        			case 'file': // File type is 'Any'
				        				return (string) get_cf_file_link( $file_url );
				        				break;
			        			}
		        			} else {
		        				return (string) '';
		        			}
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
		        		switch ( $this->field['options']['file_type'] ) {
		        			case 'image':
					        	return (string) get_cf( $this->field['name'], 'image_view__medium_large' );
		        				break;
		        			case 'video':
					        	return (string) get_cf( $this->field['name'], 'video_player' );
		        				break;
		        			case 'audio':
					        	return (string) get_cf( $this->field['name'], 'audio_player' );
		        				break;
		        			case 'pdf':
					        	return (string) get_cf( $this->field['name'], 'pdf_viewer' );
		        				break;
		        			case 'file': // File type is 'Any'
		        				return (string) get_cf( $this->field['name'], 'file_link' );
		        				break;
		        		}
	        		}
        		break;
        		
        		case 'gallery':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']]; // Comma-separated attachment IDs
		        			$sub_field_value = explode( ',', $sub_field_value );
		        			return (string) get_cf_gallery_justified( $sub_field_value, 'medium' );
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}	        			
	        		} else {
	        			return (string) get_cf( $this->field['name'], 'gallery_justified__medium' );
	        		}
        			break;

        		case 'relationship':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
		        			return (string) cf_titles_only_v( $class_name, 'cf_titles_only_v__ul', $sub_field_value );
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
						return (string) get_cf_related_to( $this->field['name'], 'cf_titles_only_v__ul' );
	        		}
        		break;

        		case 'term':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
		        			return (string) get_cf_terms( $sub_field_value, 'names_archive_links' );
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
    	    			return (string) get_cf( $this->field['name'], 'names_archive_links' );
	        		}
        		break;

        		case 'user':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
		        			return get_cf_users( $sub_field_value, 'display_names' );
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
    	    			return (string) get_cf( $this->field['name'], 'display_names' );
	        		}
        		break;

    		}
    	}

    	// ============================================================
    	// For fields with URL return type in Breakdance
    	// ============================================================

    	if ( 'url' == $this->bd_output_type ) {
    		switch ( $this->field['type'] ) {

	        	case 'text':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
							if ( filter_var( $sub_field_value, FILTER_VALIDATE_URL ) && false !== strpos( $sub_field_value, 'http' ) ) {
			        			return (string) $sub_field_value;
			        		} else {
			        			return (string) '?';
			        		}
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
			    		$raw_value = get_cf( $this->field['name'], 'raw' );
						if ( filter_var( $raw_value, FILTER_VALIDATE_URL ) && false !== strpos( $raw_value, 'http' ) ) {
							return (string) $raw_value;
						} else {
							return (string) get_the_permalink();
						}
	        		}
	        		break;

	        	case 'hyperlink':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
			        		if ( is_array( $sub_field_value ) && ! empty( $sub_field_value ) && isset( $sub_field_value['url'] ) ) {
			        			return (string) $sub_field_value['url'];
			        		}
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
			    		$raw_value = get_cf( $this->field['name'], 'raw' );
		        		if ( is_array( $raw_value ) && ! empty( $raw_value ) && isset( $raw_value['url'] ) ) {
		        			return (string) $raw_value['url'];
		        		}
	        		}
	        		break;

	        	case 'file':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
			        		return (string) wp_get_attachment_url( $sub_field_value );
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
			    		$raw_value = get_cf( $this->field['name'], 'raw' );
		        		if ( ! empty( $raw_value ) && is_numeric( $raw_value ) ) {
			        		return (string) wp_get_attachment_url( $raw_value );
		        		}
	        		}
	        		break;

	        	case 'relationship':
	        		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
							if ( is_array( $sub_field_value ) && count( $sub_field_value ) > 0 ) {
								$first_related_item_id = $sub_field_value[0];
								return (string) get_the_permalink( $first_related_item_id );
							}
		        		} else {
				            return (string) $sub_field_value_placeholder;
		        		}
    	    		} else {
			    		$raw_value = get_cf( $this->field['name'], 'raw' );
						if ( is_array( $raw_value ) && count( $raw_value ) > 0 ) {
							$first_related_item_id = $raw_value[0];
							return (string) get_the_permalink( $first_related_item_id );
						}
	        		}
	        		break;
	        		
	        }
	    }

    	// ============================================================
    	// For fields with image return type in Breakdance
    	// ============================================================

    	if ( 'image' == $this->bd_output_type ) {
			// Return attachment ID
    		if ( $should_return_sub_field_value ) {
        		if ( ! empty( $parent_field_value ) ) {
        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
        			return $sub_field_value;
        		} else {
        			return (string) 0;
        		}
    		} else {
    			return get_cf( $this->field['name'], 'raw' );
    		}
	    }

    	// ============================================================
    	// For fields with gallery return type in Breakdance
    	// ============================================================

    	if ( 'gallery' == $this->bd_output_type ) {
			// Return attachment ID
    		if ( $should_return_sub_field_value ) {
        		if ( ! empty( $parent_field_value ) ) {
        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']];
        			return $sub_field_value;
        		} else {
        			return (string) 0;
        		}
    		} else {
    			return get_cf( $this->field['name'], 'raw' );
    		}
	    }

    	// ============================================================
    	// For fields with video return type in Breakdance
    	// ============================================================

    	if ( 'video' == $this->bd_output_type ) {
    		// Return URL to video online or local file
    		switch ( $this->field['type'] ) {

	        	case 'text':
		    		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']]; // URL
		        			return (string) $sub_field_value;
		        		} else {
		        			return (string) '';
		        		}
		        	} else {
		    			return (string) get_cf( $this->field['name'], 'raw' );
		        	}
	        		break;

	        	case 'hyperlink':
		    		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']]; // array of url, text, target
		        			if ( is_array( $sub_field_value ) && ! empty( $sub_field_value ) && isset( $sub_field_value['url'] ) ) {
		        				return (string) $sub_field_value['url'];
		        			}
		        		} else {
		        			return (string) '';
		        		}
		        	} else {
		    			$raw_value = get_cf( $this->field['name'], 'raw' );
		    			if ( isset( $raw_value['url'] ) 
		    				&& filter_var( $raw_value['url'], FILTER_VALIDATE_URL ) 
		    				&& false !== strpos( $raw_value['url'], 'http' ) 
		    			) {
		    				return (string) $raw_value['url'];
		    			}
		        	}
	        		break;

	        	case 'file':
		    		if ( $should_return_sub_field_value ) {
		        		if ( ! empty( $parent_field_value ) ) {
		        			$sub_field_value = $parent_field_value[$loopIndex][$this->field['name']]; // attachment ID
		        			return wp_get_attachment_url( $sub_field_value );
		        		} else {
		        			return (string) '';
		        		}
		        	} else {
				    	$attachment_id = get_cf( $this->field['name'], 'raw' );
				    	$attachment_data = wp_prepare_attachment_for_js( $attachment_id );
				    	if ( isset( $attachment_data['url'] ) ) {
				    		$url = $attachment_data['url'];
					    	return (string) $url;
				    	} else {
				    		return (string) '';
				    	}
		        	}
	        		break;
	        		
	        }
	    }

    }

}