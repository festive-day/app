<?php

function get_options_pages_ids() {
    $options_page_ids = array();

    // We use get_posts() here instaed of WP_Query to get a static loop and not interfere with the main loop
    // If we use WP_Query, it will for example break the output of Query Loop block in the block editor
    // Ref: https://developer.wordpress.org/reference/functions/get_posts/
    // Ref: https://kinsta.com/blog/wordpress-get_posts/
    // Ref: https://digwp.com/2011/05/loops/
    $args = array(
        'post_type'         => 'asenha_options_page',
        'post_status'       => 'publish',
        'numberposts'       => -1, // use this instead of posts_per_page
        'orderby'           => 'title',
        'order'             => 'ASC',
    );
    
    $options_pages = get_posts( $args );
    
    if ( ! empty( $options_pages ) ) {
        foreach ( $options_pages as $options_page ) {
            $options_page_ids[] = $options_page->ID;
        }
    }
    
    return $options_page_ids;    
}

/**
 * Get info on custom fields that are part of custom field groups with placement for options pages
 * 
 */
function get_options_pages_cf() {
    $options_pages_fields = array();
    $options_page_ids = get_options_pages_ids();
    
    if ( ! empty( $options_page_ids ) ) {
        foreach ( $options_page_ids as $id ) {
            $fields = CFG()->find_fields( array( 'post_id' => $id ) );
            foreach ( $fields as $field ) {
                $options_pages_fields[$field['name']] = $id; // array of field_name => options_page_post_id pairs
            }
        }
    }
    
    return $options_pages_fields;
}

/**
 * Get info on custom fields that are part of custom field groups with placement for taxonomy terms
 * 
 * @since 7.8.12
 */
function get_taxonomy_terms_cf( $type = 'full' ) {
    $cfgroup_ids = get_cfgroup_ids_by_placement( 'taxonomy-terms' );
    
    $args = array(
        'group_id' => $cfgroup_ids,
    );
    $taxonomy_terms_cf = find_cf( $args ); // array of custom field slug => custom field info array

    switch ( $type ) {
        case 'full':
            return $taxonomy_terms_cf;
            break;
            
        case 'names':
            return array_keys( $taxonomy_terms_cf );
            break;
            
        case 'info':
            return array_values( $taxonomy_terms_cf );        
            break;
    }
}

/**
 * Get an array of custom field info which can be part of a CFG for posts, options pages or taxonomy terms
 */
function get_cf_info( $field_name = false, $post_id = false ) {
    
    // Normalizing for getting all field values
    if ( false == $field_name || 'all' == $field_name ) {
        $field_name = false; 
    }
    
    // Post ID is false, or contains an underscore, e.g. for a term it could be genre_84,
    // where 'genre' is the taxonomy and '84' is the term ID
    if ( false === $post_id || false !== strpos( $post_id, '_' ) ) {
        
        // Array of field_name => asenha_options_page post ID pairs
        $options_pages_fields = get_options_pages_cf();
        $options_pages_field_names = array_keys( $options_pages_fields );
        
        // Get info on fields placed inside CFGS for taxonomy terms
        $taxonomy_terms_fields = get_taxonomy_terms_cf( 'full' );
        $taxonomy_terms_field_names = get_taxonomy_terms_cf( 'names' );
        
        if ( in_array( $field_name, $options_pages_field_names ) ) {
            // We assign the post ID of the options page
            $post_id = $options_pages_fields[$field_name];
            return CFG()->get_field_info( $field_name, $post_id );
        } else if ( in_array( $field_name, $taxonomy_terms_field_names ) ) {
            return $taxonomy_terms_fields[$field_name];
        } else {
            // This is a request for a value of a field that's part of a page / post / custom post
            global $post;
            $post_id = ( ! empty( $post->ID ) ) ? $post->ID : get_the_ID();        
            return CFG()->get_field_info( $field_name, $post_id );
        }
    } else {
        return CFG()->get_field_info( $field_name, $post_id );
    }
}

function get_the_correct_cf_post_id( $field_name, $output_format, $post_id = false ) {
    if ( false === $post_id ) {
        if ( 'option' == $output_format || false !== strpos( $output_format, 'option__' ) ) {
            // This is a request for a value of a field that's part of an options page. 
            // Let's try to get the post ID of that page.

            // Array of field_name => asenha_options_page post ID pairs
            $options_pages_fields = get_options_pages_cf();
            $options_pages_field_names = array_keys( $options_pages_fields );
            
            // We assign the post ID of the options page
            if ( in_array( $field_name, $options_pages_field_names ) ) {
                $post_id = $options_pages_fields[$field_name];
            }
        } else {
            global $post;
            $queried_object = get_queried_object();

            $field_info = get_cf_info( $field_name );
            $cfgroup_rules = get_cf_cfgroup_rules( $field_name );
            $cfgroup_placement = isset( $cfgroup_rules['placement']['values'] ) ? $cfgroup_rules['placement']['values'] : 'posts'; 
            // posts | options-pages | taxonomy-terms
            
            switch ( $cfgroup_placement ) {
                case 'posts':
                    $post_id = ( ! empty( $post->ID ) ) ? $post->ID : get_the_ID();
                    break;

                case 'options-pages':
                    // Array of field_name => asenha_options_page post ID pairs
                    $options_pages_fields = get_options_pages_cf();
                    $options_pages_field_names = array_keys( $options_pages_fields );
                    
                    // We assign the post ID of the options page
                    if ( in_array( $field_name, $options_pages_field_names ) ) {
                        $post_id = $options_pages_fields[$field_name];
                    }
                    break;
                    
                case 'taxonomy-terms';
                    if ( is_a( $queried_object, 'WP_Term' ) ) {
                        $taxonomy = $queried_object->taxonomy;
                        $term_id = $queried_object->term_id;
                        $post_id = $taxonomy . '_' . $term_id;
                    } else {
                        // fallback for when the custom field value for a taxonomy term is being called outside of a taxonomy term archive
                        $post_id = false;
                    }
                    break;
            }
        }
    }
    
    return $post_id;
}

function get_cf( $field_name = false, $output_format = 'default', $post_id = false ) {
    
    // Normalizing for getting all field values
    if ( false == $field_name || 'all' == $field_name ) {
        $field_name = false; 
    }
    
    // For CSS class naming in custom field output
    $field_name_slug = str_replace( '_', '-', $field_name );
    
    $post_id = get_the_correct_cf_post_id( $field_name, $output_format, $post_id );
    
    // Get custom field info
    $cf_info = get_cf_info( $field_name, $post_id );
    $cf_type = isset( $cf_info['type'] ) ? $cf_info['type'] : 'text';

    // Set the base format when getting CFG()->get() return value
    if ( 'default' == $output_format ) {
        $base_format = 'api';
    } elseif ( 'display' == $output_format ) {
        $base_format = 'display';
    } elseif ( 'raw' == $output_format) {
        $base_format = 'raw';
    } elseif ( 'option' == $output_format || false !== strpos( $output_format, 'option__' ) ) { 
        // For getting the value of a field that's part of an options page
        $base_format = 'api';
    } else {
        if ( 'radio' == $cf_type || 'select' == $cf_type || 'checkbox' == $cf_type ) {
            // So we get the option values and labels intact and not as an indexed array
            $base_format = 'api';       
        } else {
            $base_format = 'raw';
        }
    }
        
    // Get the exact format from a request for field value in an options page
    // e.g. from 'option__link' into 'link'
    if ( false !== strpos( $output_format, 'option__' ) ) {
        $output_format = str_replace( 'option__', '',$output_format );
    }

    // Get the raw value of custom field
    $options = array( 'format' => 'raw' );
    $raw_cf_value = CFG()->get( $field_name, $post_id, $options );
    
    // Get the value of custom field
    $options = array( 'format' => $base_format );
    $cf_value = CFG()->get( $field_name, $post_id, $options );
    
    // Process custom field value further
            
    if ( 'text' == $cf_type || 'textarea' == $cf_type || 'wysiwyg' == $cf_type || 'color' == $cf_type ) {

    	if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
            if ( 'wysiwyg' == $cf_type ) {
                if ( ! is_null( $cf_value ) & is_string( $cf_value ) ) {
                    return wpautop( $cf_value );                    
                } else {
                    return;
                }
            } else {
                return $cf_value;
            }
    	}
        
        if ( 'link' == $output_format ) {
            return '<a href="' . $cf_value . '">' . $cf_value . '</a>';
        }

        if ( 'email' == $output_format ) {
            return '<a href="mailto:' . $cf_value . '">' . $cf_value . '</a>';
        }

        if ( 'phone' == $output_format ) {
            return '<a href="tel:' . $cf_value . '">' . $cf_value . '</a>';
        }

        if ( 'oembed' == $output_format ) {
            if ( false !== strpos( $cf_value, 'x.com' ) ) {
                $cf_value = str_replace( 'x.com', 'twitter.com', $cf_value );
            }
            return wp_oembed_get( $cf_value );
        }

        if ( 'shortcode' == $output_format ) {
            return do_shortcode( $cf_value );
        }
        
    } elseif ( 'number' == $cf_type ) {

        if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
            return $cf_value;       
        }

        if ( false !== strpos( $output_format, 'format__' ) ) {
            $output_parts = explode( '__', $output_format );
            $locale = ! empty( $output_parts[1] ) ? $output_parts[1] : 'en_US'; // e.g. en_US
            
            switch ( $locale ) {
                case 'comma':
                    $locale = 'en_US';
                    break;

                case 'dot':
                    $locale = 'de_DE';
                    break;

                case 'space':
                    $locale = 'fr_FR';
                    break;
            }

            if ( class_exists( 'NumberFormatter' ) ) {
                $formatter = new NumberFormatter( $locale, NumberFormatter::DECIMAL );
                return $formatter->format( $cf_value );             
            } else {
                return $cf_value;
            }

        }
                
    } elseif ( 'true_false' == $cf_type ) {
        if ( isset( $cf_info['options']['format'] ) ) {
            $output_format == $cf_info['options']['format'];
        }
        
        switch ( $output_format ) {
            case 'default':
            case 'option':
                return ( 1 == $cf_value ) ? true : false;
                break;

            case 'raw':
                return $cf_value;
                break;

            case 'true_false':
                return ( 1 == $cf_value ) ? 'True' : 'False';
                break;

            case 'yes_no':
                return ( 1 == $cf_value ) ? 'Yes' : 'No';
                break;

            case 'check_cross':
                // True: https://icon-sets.iconify.design/fa-solid/check/
                // False: https://icon-sets.iconify.design/emojione-monotone/cross-mark/
                return ( 1 == $cf_value ) ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512"><path fill="currentColor" d="m173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69L432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path fill="currentColor" d="M62 10.571L53.429 2L32 23.429L10.571 2L2 10.571L23.429 32L2 53.429L10.571 62L32 40.571L53.429 62L62 53.429L40.571 32z"/></svg>';
                break;

            case 'toggle_on_off':
                // True: https://icon-sets.iconify.design/la/toggle-on/
                // False: https://icon-sets.iconify.design/la/toggle-off/
                return ( 1 == $cf_value ) ? '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-4.96 0-9 4.035-9 9s4.04 9 9 9h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm14 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7s3.121-7 7-7"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-.621 0-1.227.066-1.813.188a9.238 9.238 0 0 0-.875.218A9.073 9.073 0 0 0 .72 12.5c-.114.27-.227.531-.313.813A8.848 8.848 0 0 0 0 16c0 .93.145 1.813.406 2.656c.004.008-.004.024 0 .032A9.073 9.073 0 0 0 5.5 24.28c.27.114.531.227.813.313A8.83 8.83 0 0 0 9 24.999h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm0 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7c0-.242.008-.484.031-.719A6.985 6.985 0 0 1 9 9m5.625 0H23c3.879 0 7 3.121 7 7s-3.121 7-7 7h-8.375C16.675 21.348 18 18.828 18 16c0-2.828-1.324-5.348-3.375-7"/></svg>';
                break;
        }
		
    } elseif ( 'radio' == $cf_type || 'select' == $cf_type || 'checkbox' == $cf_type ) {
    	if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
    		return $cf_value;
    	}

        $choices = $cf_info['options']['choices'];
        $choice_labels = array_values( $choices );
        $choice_values = array_keys( $choices );

        // If $value is an array where the value of the first element is a string of comma-separated user IDs.
        if ( is_string( $cf_value ) && false !== strpos( $cf_value, ',' ) ) {
            $cf_value = explode( ', ', $cf_value ); // Turns '123,456' into array( 0 => '123', 1 => '234' )
        } else {
            $cf_value = array( $cf_value ); // Turns '789' into array( 0 => '789' );
        }

        $output = '';

    	// Return comma separated labels
    	if ( 'label' == $output_format || 'labels' == $output_format || 'values_c' == $output_format ) {
            if ( is_array( $cf_value ) && ! empty( $cf_value ) ) {
                if ( in_array( $cf_value[0], $choice_labels ) ) {
                    return implode( ', ', $cf_value );
                }
                
                if ( in_array( $cf_value[0], $choice_values ) ) {
                    foreach ( $choices as $choice_value => $choice_label ) {
                        foreach ( $cf_value as $value ) {
                            if ( $choice_value == $value ) {
                                $output .= $choice_label . ', ';
                            }
                        }
                    }
                    
                    return trim( $output, ', ' );
                }
            } else {
                return;
            }
    	}

        // Return comma separated values
        else if ( 'value' == $output_format || 'values' == $output_format || 'keys_c' == $output_format ) {
            if ( is_array( $cf_value ) && ! empty( $cf_value ) ) {
                if ( in_array( $cf_value[0], $choice_values ) ) {
                    return implode( ', ', $cf_value );
                }

                if ( in_array( $cf_value[0], $choice_labels ) ) {
                    foreach ( $choices as $choice_value => $choice_label ) {
                        foreach ( $cf_value as $value ) {
                            if ( $choice_label == $value ) {
                                $output .= $choice_value . ', ';
                            }
                        }
                    }
                    
                    return trim( $output, ', ' );
                }
            } else {
                return;
            }
        }

    } elseif ( 'date' == $cf_type ) {
    	
    	if ( 'raw' == $output_format || 'option' == $output_format ) {
    		return $cf_value;
    	} else if ( 'default' == $output_format ) {
            // We set the timezone to be UTC, as the unixtime we obtain from strtotime( $cf_value ) is a GMT/UTC time at 00:00 of the same date
            // This is to prevent issue where there is a one-day difference for timezones like Chicago/America GMT/UTC-6. e.g. it's already January 10, 2025 00:00 in GMT, but still January 9, 2025 18:00 in Chicago.
            // Ref: https://developer.wordpress.org/reference/functions/wp_date/
            // Ref: https://developer.wordpress.org/reference/functions/wp_timezone/
            // Ref: https://developer.wordpress.org/reference/functions/wp_timezone_string/
            $frontend_display_format = isset( $cf_info['options']['frontend_display_format'] ) ? $cf_info['options']['frontend_display_format'] : 'F j, Y';
            $timezone_object = new DateTimeZone( 'UTC' );
            return wp_date( $frontend_display_format, strtotime( $cf_value ), $timezone_object );
        } else {
            $timezone_object = new DateTimeZone( 'UTC' );
            return wp_date( $output_format, strtotime( $cf_value ), $timezone_object );
    	}
    	
    } elseif ( 'time' == $cf_type ) {
            
        if ( 'raw' == $output_format ) {
            return $cf_value;
        }
        
        if ( 'default' == $output_format || 'option' == $output_format ) {
            $frontend_display_format = isset( $cf_info['options']['frontend_display_format'] ) ? $cf_info['options']['frontend_display_format'] : 'G:i';
            return get_cf_time( $cf_value, $frontend_display_format );
        }
        
    } elseif ( 'datetime' == $cf_type ) {
        $timezone_object = new DateTimeZone( 'UTC' );

        if ( 'raw' == $output_format ) {
            return $cf_value;
        } else if ( 'default' == $output_format || 'option' == $output_format ) {
            $datetime_output_format = get_cf_datetime_output_format( $cf_info['options'] );
            // Let's use the raw cf_value to ensure it works with strtotime()
            return wp_date( $datetime_output_format, strtotime( $raw_cf_value ), $timezone_object );
        } else {
            // Custom format set in $output_format
            // Let's use the raw cf_value to ensure it works with strtotime()
            return wp_date( $output_format, strtotime( $raw_cf_value ), $timezone_object );
        }
        
    } elseif ( 'hyperlink' == $cf_type ) {
    	
    	if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
	    	return $cf_value;		
    	}
        
        if ( 'link' == $output_format ) {
            return get_cf_hyperlink( $cf_value );
        }

        if ( 'url' == $output_format ) {
            return $cf_value['url'];
        }
    	
    } elseif ( 'file' === $cf_type ) {
    	
    	$file_type = $cf_info['options']['file_type'];
    	$return_value = $cf_info['options']['return_value'];

        // For all file types
        if ( 'url' == $output_format ) {
            return wp_get_attachment_url( $cf_value );
        }

        // For all file types
        if ( 'file_link' == $output_format ) {
            $attachment_url = wp_get_attachment_url( $cf_value );
            return get_cf_file_link( $attachment_url );
        }

    	if ( 'image' === $file_type ) {
    		
	     	// Output attachment ID
	    	if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
	    		return $cf_value;
	    	}
            
            if ( ! empty( $cf_value ) ) {
                // Output image URL
                if ( false !== strpos( $output_format, 'image_url' ) ) {
                    $image_size = str_replace( 'image_url__', '', $output_format );
                    return wp_get_attachment_image_url( $cf_value, $image_size );
                }

                // Output actual image
                if ( false !== strpos( $output_format, 'image_view' ) ) {
                    $image_size = str_replace( 'image_view__', '', $output_format );
                    return get_cf_image_view( $cf_value, $image_size, $field_name_slug );
                }                
            } else {
                return;
            }

    	}

    	if ( 'video' === $file_type ) {
	     	// Output attachment ID
	    	if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
	    		return $cf_value;
	    	}

	     	// Output video player. $cf_value will be the attachment ID with a custom output of 'video_player'.
	    	if ( 'video_player' == $output_format ) {
	    		$file_url = wp_get_attachment_url( $cf_value );
                return get_cf_video_player( $file_url );
	    	}       		    		
    	}

    	if ( 'audio' === $file_type ) {
	     	// Output attachment ID
	    	if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
	    		return $cf_value;
	    	}

	     	// Output audio player. $cf_value will be the attachment ID with a custom output of 'audio_player'.
	    	if ( 'audio_player' == $output_format ) {
	    		$file_url = wp_get_attachment_url( $cf_value );
                return get_cf_audio_player( $file_url );
	    	}
	    }

    	if ( 'pdf' === $file_type ) {
	     	// Output attachment ID
	    	if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
	    		return $cf_value;
	    	}

	     	// Output PDF viewer. $cf_value will be the attachment ID with a custom output of 'audio_player'.
	    	if ( 'pdf_viewer' == $output_format ) {
	    		$file_url = wp_get_attachment_url( $cf_value );
                return (string) get_cf_pdf_viewer( $file_url );
	    	}
    	}

    	if ( 'any' === $file_type || 'file' === $file_type ) {
	     	// Output attachment ID
	    	if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
	    		return $cf_value;
	    	}
	    }

    } elseif ( 'gallery' === $cf_type ) {

        if ( 'raw' == $output_format || 'default' == $output_format || 'option' == $output_format ) {
            // raw: comma separated attachment IDs
            // default: indexed array of attachment IDs
            return $cf_value;
        }

        elseif ( false !== strpos( $output_format, 'gallery_grid' ) ) {
            $attachment_ids = $cf_value; // comma-separated attachment IDs

            // $output_format example: gallery_grid__medium__columns_3
            $output_parameters = explode( '__', $output_format );
            
            if ( isset( $output_parameters[1] ) && ! empty( $output_parameters[1] ) ) {
                $image_size = $output_parameters[1];
            } else {
                $image_size = 'medium';
            }

            if ( isset( $output_parameters[2] ) && ! empty( $output_parameters[2] ) ) {
                $columns_number = str_replace( 'columns_', '', $output_parameters[2] );
            } else {
                $columns_number = '4';
            }

            return get_cf_gallery_grid( $attachment_ids, $image_size, $columns_number );
        }

        elseif ( false !== strpos( $output_format, 'gallery_justified' ) ) {
            $attachment_ids = explode( ',', $cf_value );

            $image_size = str_replace( 'gallery_justified__', '', $output_format );
            $image_size = ! empty( $image_size ) ? $image_size : 'medium';
                        
            return get_cf_gallery_justified( $attachment_ids, $image_size );
        }

        elseif ( false !== strpos( $output_format, 'gallery_masonry' ) ) {
            $attachment_ids = explode( ',', $cf_value );

            $image_size = str_replace( 'gallery_masonry__', '', $output_format );
            $image_size = ! empty( $image_size ) ? $image_size : 'medium';

            return get_cf_gallery_masonry( $attachment_ids, $image_size );
        }
        
    } elseif ( 'term' == $cf_type ) {
        
        if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
            return $cf_value;       
        } else {
            return get_cf_terms( $cf_value, $output_format );            
        }
        
    } elseif ( 'user' == $cf_type ) {
        
        if ( 'default' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
            return $cf_value;       
        } else {
            return get_cf_users( $cf_value, $output_format );
        }
        
    } else {

    	if ( 'default' == $output_format || 'display' == $output_format || 'raw' == $output_format || 'option' == $output_format ) {
	        return $cf_value;
    	}

    }

}

function the_cf( $field_name = false, $output_format = 'default', $post_id = false ) {
	
	$cf_value = get_cf( $field_name, $output_format, $post_id );
	
	if ( ! is_array( $cf_value ) ) {
		echo $cf_value;
	} else {
		echo var_dump( $cf_value );		
	}

}

function cf_shortcode_cb( $shortcode_atts ) {
    
    $default_atts = array(
        'name'      => '',
        'output'    => 'default',
        'post_id'   => false,
    );
    
    $atts = shortcode_atts( $default_atts, $shortcode_atts );
    
    return get_cf( $atts['name'], $atts['output'], $atts['post_id'] );
    
}
add_shortcode( 'cf', 'cf_shortcode_cb' );

function get_cf_time( $raw_time, $frontend_display_format ) {
    if ( ! empty( $raw_time ) ) {
        $timezone_object = new DateTimeZone( 'UTC' );
        // We use 'Y-m-d' number-based format, not something like 'F j, Y' which can come out differently in a different language.
        $raw_datetime = wp_date( 'Y-m-d', time(), $timezone_object ) . ' ' . $raw_time;
        
        return wp_date( $frontend_display_format, strtotime( $raw_datetime ), $timezone_object );
    } else {
        return '';
    }
}

function get_cf_datetime_output_format( $cf_info_options ) {
    $date_output_format = isset( $cf_info_options['date_output_format'] ) ? $cf_info_options['date_output_format'] : 'F j, Y';
    $time_output_format = isset( $cf_info_options['time_output_format'] ) ? $cf_info_options['time_output_format'] : 'H:i';
    $date_time_output_separator = isset( $cf_info_options['date_time_output_separator'] ) ? $cf_info_options['date_time_output_separator'] : 'hyphen';
    
    switch ( $date_time_output_separator ) {
        case 'space':
            $separator = ' ';
            break;

        case 'comma':
            $separator = ', ';
            break;

        case 'hyphen':
            $separator = ' - ';
            break;

        case 'pipe':
            $separator = ' | ';
            break;

        case 'slash':
            $separator = ' / ';
            break;

        case 'at_symbol':
            $separator = ' @ ';
            break;

        case 'at_text':
            $separator = ' \a\t ';
            break;
    }
    
    $datetime_output_format = $date_output_format . $separator . $time_output_format;
    
    return $datetime_output_format;
}

function get_cf_hyperlink( $hyperlink_array ) {
    $url = isset( $hyperlink_array['url'] ) ? $hyperlink_array['url'] : '/';
    $text = isset( $hyperlink_array['text'] ) ? $hyperlink_array['text'] : 'Link';
    $target = isset( $hyperlink_array['target'] ) ? $hyperlink_array['target'] : '_blank';

    return (string) '<a href="' . $url . '" target="' . $target . '">' . $text . '</a>';
}

function get_cf_file_link( $attachment_url ) {
    if ( ! empty( $attachment_url ) ) {
        $url_parts = explode( '/', $attachment_url );                   
    } else {
        $url_parts = array();
    }

    if ( is_array( $url_parts ) && ! empty( $url_parts ) ) {
        $file_name = $url_parts[count($url_parts)-1];               
    } else {
        $file_name = '';
    }
    return '<a href="' . $attachment_url . '" class="custom-field-file-link" target="_blank">' . $file_name . '</a>';    
}

function get_cf_gallery_grid( $attachment_ids = '', $image_size = 'medium', $columns_number = 5 ) {
    $output = '';
    if ( ! empty( $attachment_ids ) ) {
        $wp_gallery = do_shortcode( '[gallery ids="' . $attachment_ids . '" size="' . $image_size . '" columns="' . $columns_number . '" link="file"]' );
        $output .= '<div class="ase-gallery ase-gallery-grid">';
        $output .= $wp_gallery;
        $output .= '</div>';

        // Reference: https://codepen.io/svelts/pen/ogboNV by Sven Lötscher
        $output .= '<style>
                    .ase-gallery-grid {
                        margin-bottom: 1em;
                    }
                    .ase-gallery-grid .gallery {
                        display: flex;
                        flex-wrap: wrap;
                        margin: -0.25em;
                        grid-gap: 0;
                    }
                    ';
        switch ( $columns_number ) {
            case '2';
                $output .= '.ase-gallery-grid .gallery-columns-2 .gallery-item {
                        max-width: 50%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-2 .gallery-item {
                        max-width: 48%;
                    }';
                break;

            case '3';
                $output .= '.ase-gallery-grid .gallery-columns-3 .gallery-item {
                        max-width: 33.3333%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-3 .gallery-item {
                        max-width: 32%;
                    }';
                break;

            case '4';
                $output .= '.ase-gallery-grid .gallery-columns-4 .gallery-item {
                        max-width: 25%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-4 .gallery-item {
                        max-width: 23%;
                    }';
                break;

            case '5';
                $output .= '.ase-gallery-grid .gallery-columns-5 .gallery-item {
                        max-width: 20%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-5 .gallery-item {
                        max-width: 18.5%;
                    }';
                break;

            case '6';
                $output .= '.ase-gallery-grid .gallery-columns-6 .gallery-item {
                        max-width: 16.6666%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-6 .gallery-item {
                        max-width: 15%;
                    }';
                break;

            case '7';
                $output .= '.ase-gallery-grid .gallery-columns-7 .gallery-item {
                        max-width: 14.2857%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-7 .gallery-item {
                        max-width: 13%;
                    }';
                break;

            case '8';
                $output .= '.ase-gallery-grid .gallery-columns-8 .gallery-item {
                        max-width: 12.5%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-8 .gallery-item {
                        max-width: 11%;
                    }';
                break;

            case '9';
                $output .= '.ase-gallery-grid .gallery-columns-9 .gallery-item {
                        max-width: 11.1111%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-9 .gallery-item {
                        max-width: 9.5%;
                    }';
                break;

            case '10';
                $output .= '.ase-gallery-grid .gallery-columns-10 .gallery-item {
                        max-width: 10%;
                    }
                    .block-editor-page .swp-rnk-preview .ase-gallery-grid .gallery-columns-10 .gallery-item {
                        max-width: 8.5%;
                    }';
                break;
        }
        $output .= '
            .ase-gallery-grid figure {
                margin-bottom: 0;
            }
            .ase-gallery-grid .gallery-item {
                box-sizing: border-box;
                display: inline-block;
                padding: 0.25em;
            }
            .ase-gallery-grid .gallery-item img {
                max-width: 100%;
                height: 100%;
            }
            .ase-gallery-grid .gallery-icon {
                height: 100%;
                padding: 0;
            }
            .ase-gallery-grid .gallery-icon img {
                height: 100%;
                object-fit: cover;
            }
            </style>';
    }

    return $output;    
}

function get_cf_gallery_justified( $attachment_ids = array(), $image_size = 'medium' ) {
    $output = '';
    if ( is_array( $attachment_ids ) && ! empty( $attachment_ids ) ) {
        // Reference: https://codepen.io/w3work/pen/bGGWNBQ by Sven
        $output .= '<div class="ase-gallery ase-gallery-justified">';
        foreach ( $attachment_ids as $attachment_id ) {
            $image_full_url = wp_get_attachment_image_url( $attachment_id, 'full' );
            $image_thumbnail_url = wp_get_attachment_image_url( $attachment_id, $image_size );
            $output .= '<a href="' .$image_full_url. '"><img src="' . $image_thumbnail_url . '" /></a>';
        }                
        $output .= '</div>';

        $output .= '
            <style>
                :root {
                  --gallery-row-height: 80px;
                  --gallery-gap: .625em;
                }

                .ase-gallery-justified {
                  display: flex;
                  width: 100%;
                  overflow: hidden;
                  flex-wrap: wrap;
                  gap: 0.5em;
                  margin-bottom: 1em;
                  /* margin-bottom: calc(-1 * var(--gallery-gap, 1em)); */
                  /* margin-left: calc(-1 * var(--gallery-gap, 1em)); */
                }
                .ase-gallery-justified:after {
                  content: "";
                  flex-grow: 999999999;
                  min-width: var(--gallery-row-height);
                  height: 0;
                }
                .ase-gallery-justified a {
                  display: block;
                  height: var(--gallery-row-height);
                  flex-grow: 1;
                  /* margin-bottom: var(--gallery-gap, 1em);
                  margin-left: var(--gallery-gap, 1em);
                    overflow: hidden; */
                }
                .ase-gallery-justified a img {
                  height: var(--gallery-row-height);
                  object-fit: cover;
                  max-width: 100%;
                  min-width: 100%;
                  vertical-align: bottom;
                  transition: .375s;
                }
                .ase-gallery-justified a img:hover {
                    transform: scale(1.05);
                }

                @media only screen and (min-width: 768px) {
                  :root {
                    --gallery-row-height: 120px;
                  }
                }
                @media only screen and (min-width: 1280px) {
                  :root {
                    --gallery-row-height: 150px;
                  }
                }
            </style>';
    }
    
    return $output;
}

function get_cf_gallery_masonry( $attachment_ids = array(), $image_size = 'medium' ) {
    $output = '';
    if ( is_array( $attachment_ids ) && ! empty( $attachment_ids ) ) {
        // Reference: https://codepen.io/svelts/pen/ogboNV by Sven Lötscher
        $output .= '<div class="ase-gallery ase-gallery-masonry">';
        foreach ( $attachment_ids as $attachment_id ) {
            $image_full_url = wp_get_attachment_image_url( $attachment_id, 'full' );
            $image_thumbnail_url = wp_get_attachment_image_url( $attachment_id, $image_size );
            $output .= '<a href="' .$image_full_url. '"><img src="' . $image_thumbnail_url . '" /></a>';
        }                
        $output .= '</div>';

        $output .= '
            <style>
            .ase-gallery-masonry {
                display: block;
                width: 100%;
                column-width: 180px;
                column-gap: .5em;
                margin-bottom: 1em;
            }
            .ase-gallery-masonry a {
                display: block;
                width: 100%;
                margin-bottom: .5em;
                overflow: hidden;
            }
            .ase-gallery-masonry a img {
                display: block;
                width: 100%;
                height: auto;
                transition: .375s;
            }
            .ase-gallery-masonry a img:hover {
                transform: scale(1.025);
            }
            </style>';
    }

    return $output;    
}

function get_cf_image_view( $attachment_id, $image_size, $field_name_slug ) {
    return '<img src="' . wp_get_attachment_image_url( $attachment_id, $image_size ) . '" class="' . esc_attr( $field_name_slug ) . ' ' . esc_attr( $image_size ) . '"/>';                    
    
}

function get_cf_video_player( $file_url ) {
    if ( filter_var( $file_url, FILTER_VALIDATE_URL ) && false !== strpos( $file_url, 'http' ) ) {
        $output = do_shortcode( '[video src="' . $file_url . '"]' );
        if ( function_exists( 'bricks_is_frontend' ) && bricks_is_frontend() ) {
            // Rendered by Bricks builder, do not add inline CSS, video player already displays fine
        } else {
            $output .= '<style>';
            if ( is_plugin_active( 'breakdance/plugin.php' ) || function_exists( '\Breakdance\DynamicData\registerField' ) || class_exists( '\Breakdance\DynamicData\Field' ) ) {
                // With Bricks builder, no need to define width or height. Player already looks fine. So, add/do nothing here.
            } else {
                // Let's add width and height so video player looks and works well in most themes.
                $output .= '.wp-video, video.wp-video-shortcode, .mejs-container.mejs-video, .mejs-overlay.load {
                        width: 100% !important;
                        height: 100% !important;
                    }';                        
            }
            // Let's output the rest of the inline CSS.
            $output .= '.mejs-container.mejs-video {
                    padding-top: 56.25%;
                }
                .wp-video, video.wp-video-shortcode {
                    max-width: 100% !important;
                }
                video.wp-video-shortcode {
                    position: relative;
                }
                .mejs-video .mejs-mediaelement {
                    position: absolute;
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                }
                .mejs-video .mejs-controls {
                    /* display: none; */
                }
                .mejs-video .mejs-overlay-play {
                    top: 0;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    width: auto !important;
                    height: auto !important;
                }
                </style>';
        }
        return $output;
    } else {
        return;
    }
}

function get_cf_audio_player( $file_url ) {
    if ( filter_var( $file_url, FILTER_VALIDATE_URL ) && false !== strpos( $file_url, 'http' ) ) {
        return do_shortcode( '[audio src="' . $file_url . '"]' );
    } else {
        return;
    }
}

function get_cf_pdf_viewer( $file_url ) {
    if ( filter_var( $file_url, FILTER_VALIDATE_URL ) && false !== strpos( $file_url, 'http' ) ) {
        $random_number = rand(1,1000);
        if ( function_exists( 'bricks_is_frontend' ) && bricks_is_frontend() ) {
            $width = '100%';
            $height = '640px';
        } else {
            $width = '48rem';
            $height = '32rem';
        }
        return '<div id="pdf-viewer-'. $random_number .'" class="pdfobject-viewer"></div>
                <style>
                .pdfobject-container { width: ' . $width . '; height: ' . $height . '; border: 1rem solid rgba(0,0,0,.1); }
                @media screen and (max-width: 768px) {
                    .pdfobject-container { width: 100%; height: 32rem; }
                }
                </style>
                <script src="' . ASENHA_URL . 'assets/premium/js/pdfobject.js"></script>
                <script>PDFObject.embed("' . $file_url . '", "#pdf-viewer-'. $random_number .'");</script>';
    } else {
        return;
    }
}

function get_cf_terms( $cf_value, $output_format ) {
    if ( is_array( $cf_value ) && count( $cf_value ) > 0 ) {
        switch ( $output_format ) {
            case 'names':
                $names = array();
                foreach( $cf_value as $term_id ) {
                    $term_id = (int) $term_id;
                    $term = get_term( $term_id );
                    $names[] = $term->name;
                }
                $names = implode(', ', $names );
                return $names;
                break;
                
            case 'names_archive_links':
                $names_archive_links = array();
                foreach( $cf_value as $term_id ) {
                    $term_id = (int) $term_id;
                    $term = get_term( $term_id );
                    $names_archive_links[] = '<a href="' . get_term_link( $term_id ) . '">' . $term->name . '</a>';
                }
                $names_archive_links = implode(', ', $names_archive_links );
                return $names_archive_links;
                break;

            case 'names_edit_links':
                $names_edit_links = array();
                foreach( $cf_value as $term_id ) {
                    $term_id = (int) $term_id;
                    $term = get_term( $term_id );
                    $names_edit_links[] = '<a href="' . get_edit_term_link( $term_id ) . '">' . $term->name . '</a>';
                }
                $names_edit_links = implode(', ', $names_edit_links );
                return $names_edit_links;
                break;
        }
    } else {
        return '';
    }
    
}

function get_cf_users( $cf_value, $output_format ) {
    if ( is_array( $cf_value ) && count( $cf_value ) > 0 ) {
        switch ( $output_format ) {
            case 'first_names':
                $first_names = array();
                foreach( $cf_value as $user_id ) {
                    $user_id = (int) $user_id;
                    $user = get_user_by( 'id', $user_id );
                    $first_names[] = $user->user_firstname;
                }
                $first_names = implode(', ', $first_names );
                return $first_names;
                break;

            case 'last_names':
                $last_names = array();
                foreach( $cf_value as $user_id ) {
                    $user_id = (int) $user_id;
                    $user = get_user_by( 'id', $user_id );
                    $last_names[] = $user->user_lastname;
                }
                $last_names = implode(', ', $last_names );
                return $last_names;
                break;

            case 'full_names':
                $full_names = array();
                foreach( $cf_value as $user_id ) {
                    $user_id = (int) $user_id;
                    $user = get_user_by( 'id', $user_id );
                    $full_names[] = $user->user_firstname . ' ' . $user->user_lastname;
                }
                $full_names = implode(', ', $full_names );
                return $full_names;
                break;
                
            case 'display_names':
                $display_names = array();
                foreach( $cf_value as $user_id ) {
                    $user_id = (int) $user_id;
                    $user = get_user_by( 'id', $user_id );
                    if ( is_object( $user ) ) {
                        if ( property_exists( $user, 'display_name' ) ) {
                            $display_names[] = $user->display_name;
                        } else if ( property_exists( $user, 'user_firstname' ) && property_exists( $user, 'user_lastname' ) ) {
                            $display_names[] = $user->user_firstname . ' ' . $user->user_lastname;
                        } else {
                            $display_names[] = $user->user_nicename;
                        }                        
                    } else {
                        $display_names[] = '';
                    }
                }
                $display_names = implode(', ', $display_names );
                return $display_names;
                break;

            case 'usernames':
                $usernames = array();
                foreach( $cf_value as $user_id ) {
                    $user_id = (int) $user_id;
                    $user = get_user_by( 'id', $user_id );
                    $usernames[] = $user->user_login;
                }
                $usernames = implode(', ', $usernames );
                return $usernames;
                break;
        }
    } else {
        return '';
    }
}

function get_cf_related_to( $field_name = false, $output_format = 'default', $base_format = 'raw', $post_id = false ) {

    $field_name_slug = str_replace( '_', '-', $field_name );

    $post_id = get_the_correct_cf_post_id( $field_name, $output_format, $post_id );

    if ( in_array( $base_format, array( 'raw', 'api', 'input' ) ) ) {
        $options = array( 'format' => $base_format );
    } else {
        $options = array( 'format' => 'raw' );
    }

    $related_to = CFG()->get( $field_name, $post_id, $options );

    if ( 'default' === $output_format ) {
        return $related_to;        
    }

    if ( false !== strpos( $output_format, 'titles_only_c' ) ) {
    	return cf_titles_only_c( $field_name_slug, $related_to );
    }
    
    if ( false !== strpos( $output_format, 'titles_only_v' ) ) {
    	return cf_titles_only_v( $field_name_slug, $output_format, $related_to );
    }

    if ( false !== strpos( $output_format, 'image_titles_v' ) ) {
    	return cf_image_titles_v( $field_name_slug, $related_to, $output_format );
    }

    if ( false !== strpos( $output_format, 'image_titles_h' ) ) {
    	return cf_image_titles_h( $field_name_slug, $related_to, $output_format );
    }

}

function the_cf_related_to( $field_name = false, $output_format = 'default', $base_format = 'raw', $post_id = false) {
	
	$related_to = get_cf_related_to( $field_name, $output_format, $base_format, $post_id );
	
	if ( ! is_array( $related_to ) ) {
		echo $related_to;
	} else {
		echo var_dump( $related_to );		
	}

}

function cf_related_to_shortcode_cb( $shortcode_atts ) {
    
    $default_atts = array(
        'name'      => '',
        'output'    => 'default',
        'base'      => 'raw',
        'post_id'   => false,
    );
    
    $atts = shortcode_atts( $default_atts, $shortcode_atts );
    
    return get_cf_related_to( $atts['name'], $atts['output'], $atts['base'], $atts['post_id'] );
    
}
add_shortcode( 'cf_related_to', 'cf_related_to_shortcode_cb' );

function get_cf_related_from( $field_name = false, $output_format = 'default', $related_from_post_type = false, $related_from_post_status = 'publish', $field_type = 'relationship', $post_id = false ) {

    $field_name_slug = str_replace( '_', '-', $field_name );

    $post_id = get_the_correct_cf_post_id( $field_name, $output_format, $post_id );
    
    $options = array( 
        'field_type'    => $field_type,
    );

    if ( false !== $field_name ) {
        $options['field_name'] = $field_name;            
    }

    if ( false !== $related_from_post_type ) {
        $options['post_type'] = $related_from_post_type;            
    }

    if ( false !== $related_from_post_status ) {
        $options['post_status'] = $related_from_post_status;            
    }

    $related_from = CFG()->get_reverse_related( $post_id, $options );
            
    if ( 'default' == $output_format ) {
        return $related_from;
    }
    
    if ( false !== strpos( $output_format, 'titles_only_c' ) ) {
    	return cf_titles_only_c( $field_name_slug, $related_from );
    }

    if ( false !== strpos( $output_format, 'titles_only_v' ) ) {
    	return cf_titles_only_v( $field_name_slug, $output_format, $related_from );
    }

    if ( false !== strpos( $output_format, 'image_titles_v' ) ) {
        return cf_image_titles_v( $field_name_slug, $related_from, $output_format );
    }

    if ( false !== strpos( $output_format, 'image_titles_h' ) ) {
    	return cf_image_titles_h( $field_name_slug, $related_from, $output_format );
    }

}

function the_cf_related_from( $field_name = false, $output_format = 'default', $related_from_post_type = false, $related_from_post_status = 'publish', $field_type = 'relationship', $post_id = false ) {
	
	$related_from = get_cf_related_from( $field_name, $output_format, $related_from_post_type, $related_from_post_status, $field_type, $post_id );
	
	if ( ! is_array( $related_from ) ) {
		echo $related_from;
	} else {
		echo var_dump( $related_from );		
	}

}

function cf_related_from_shortcode_cb( $shortcode_atts ) {
    
    $default_atts = array(
        'name'          => '',
        'output'        => 'default',
        'post_type'     => false,
        'post_status'   => 'publish',
        'field_type'    => 'relationship',
        'post_id'       => false,
    );
    
    $atts = shortcode_atts( $default_atts, $shortcode_atts );
    
    return get_cf_related_from( $atts['name'], $atts['output'], $atts['post_type'], $atts['post_status'], $atts['field_type'], $atts['post_id'] );
    
}
add_shortcode( 'cf_related_from', 'cf_related_from_shortcode_cb' );

/**
 * Output comma separated, linked titles of the related posts/objects
 * @param  string 	$field_name_slug   	slugified $field_name to use for class names
 * @param  array 	$related_ids_array 	array of related post IDs
 */
function cf_titles_only_c( $field_name_slug, $related_ids_array ) {
    $output = '<div class="related related-' . esc_attr( $field_name_slug ) . ' titles-c">';
    if ( is_array( $related_ids_array ) && count( $related_ids_array ) > 0 ) {

        $count = count( $related_ids_array );
        $i = 1;

        foreach ( $related_ids_array as $object_id ) {

            $post = get_post( $object_id );

            if ( is_object( $post ) ) {
                $output .= '<a class="related-item" href="' . get_the_permalink( $object_id ) . '">' . trim( $post->post_title ) . '</a>';

                if ( $i < $count ) { 
                    $output .= ', '; 
                }
            }

            $i++;

        }

    }

    $output .= '</div>';
    
    return $output;
}

/**
 * Output list of linked titles of the related posts/objects
 * @param  string 	$field_name_slug   	slugified $field_name to use for class names
 * @param  array 	$related_ids_array 	array of related post IDs
 */
function cf_titles_only_v( $field_name_slug, $output_format, $related_ids_array ) {
	$output_format = explode( '__', $output_format );
	$list_type = isset( $output_format[1] ) ? $output_format[1] : 'div';
	
	if ( 'div' == $list_type ) {
		$parent_element = 'div';
		$child_element = 'div';
	}
	
	if ( 'ol' == $list_type ) {
		$parent_element = 'ol';
		$child_element = 'li';
	}

	if ( 'ul' == $list_type ) {
		$parent_element = 'ul';
		$child_element = 'li';
	}
	
    $output = '<' . $parent_element . ' class="related related-' . $field_name_slug . ' titles-v">';

    if ( is_array( $related_ids_array ) && count( $related_ids_array ) > 0 ) {

        foreach ( $related_ids_array as $object_id ) {

            $post = get_post( $object_id );

            if ( is_object( $post ) ) {
                $output .= '<' . $child_element . ' class="related-item">
                	<a href="' . get_the_permalink( $object_id ) . '">' . $post->post_title . '</a>
                </' . $child_element . '>';
            }

        }

    }

    $output .= '</' . $parent_element . '>';
    
    return $output;
    
}

/**
 * Output list of linked image-titles of the related posts/objects
 * @param  string 	$field_name_slug   	slugified $field_name to use for class names
 * @param  array 	$related_ids_array 	array of related post IDs
 */
function cf_image_titles_v( $field_name_slug, $related_ids_array, $output_format ) {

    $output = '<div class="related related-' . $field_name_slug . ' image-titles-v" style="display:flex;flex-direction:column;gap:16px;flex-wrap:wrap;">';

    if ( is_array( $related_ids_array ) && count( $related_ids_array ) > 0 ) {

        $output_format_parts = explode( '__', $output_format );
        $image_size = isset( $output_format_parts[1] ) ? $output_format_parts[1] : 'thumbnail';

        foreach ( $related_ids_array as $object_id ) {

            $post = get_post( $object_id );

            if ( is_object( $post ) ) {
                $output .= '<a class="related-item" href="' . get_the_permalink( $post->ID ) . '">
                    <div class="related-item-div" style="display:flex;flex-direction:row;">
                        <img src="' . get_the_post_thumbnail_url( $post->ID, $image_size ) . '" class="custom-field-file-image-as-id" style="width:50px;height:50px;margin-right:8px;">
                        <div class="related-item-title">' .
                            $post->post_title .
                        '</div>
                </div>
                </a>';
            }

        }

    }

    $output .= '</div>';

    return $output;
}

/**
 * Output horizontal grid of linked image-titles of the related posts/objects
 * @param  string 	$field_name_slug   	slugified $field_name to use for class names
 * @param  array 	$related_ids_array 	array of related post IDs
 */
function cf_image_titles_h( $field_name_slug, $related_ids_array, $output_format ) {

    $output = '<div class="related related-' . $field_name_slug . ' image-titles-h" style="display:flex;gap:16px;flex-wrap:wrap;">';

    if ( is_array( $related_ids_array ) && count( $related_ids_array ) > 0 ) {

        $output_format_parts = explode( '__', $output_format );
        $image_size = isset( $output_format_parts[1] ) ? $output_format_parts[1] : 'thumbnail';

        foreach ( $related_ids_array as $object_id ) {

            $post = get_post( $object_id );
            $featured_image_id = get_post_thumbnail_id( $object_id );
            $image_info = wp_get_attachment_image_src( $featured_image_id, $image_size );

            if ( $image_info ) {
            	$image_width = $image_info[1];
            } else {
            	$image_width = get_option( $image_size . '_size_w', '150' );
            }

            if ( is_object( $post ) ) {

                $output .= '<a class="related-item" href="' . get_the_permalink( $post->ID ) . '">
                    <div class="related-item-div" style="max-width:' . $image_width . 'px">
                        ' . get_the_post_thumbnail( $post->ID, $image_size ) . '
                        <div class="related-item-title">
                            ' . $post->post_title . '
                        </div>
                </div>
                </a>';

            }

        }

    }

    $output .= '</div>';
    
    return $output;

}

/**
 * Update the value of a custom field
 */
function update_cf( $post_id = false, $fields_data = '', $single_new_value = '' ) {
    if ( ! is_numeric( $post_id ) ) {
        $post_id = get_the_ID();
    }
    
    if ( is_numeric( $post_id ) ) {
        $all_cf_info = get_cf_info( false, $post_id );

        // Prepare $field_data, $post_date and $options 
        // for /includes/premium/custom-content/cfgroup/includes/form.php >> init() >> CFG()->save()
        $cf_raw_values = get_cf( false, 'raw', $post_id );

        $common_methods = new ASENHA\Classes\Common_Methods;
        $field_data = $common_methods->convert_ase_cf_raw_values_to_cfg_save_format__premium_only( $cf_raw_values, $all_cf_info );
        // vi( $field_data, '', 'processed to conform with format for CFG->save' );
    } else {
        return;
    }

    // $fields_data is the name of a single field
    if ( is_string( $fields_data ) ) {
        $field_name = $fields_data;
        $field = $all_cf_info[$field_name];
        $field_id = $all_cf_info[$field_name]['id'];
        $field_type = $all_cf_info[$field_name]['type'];
        
        // Maybe normalize the format of $single_new_value

        if ( in_array( $field_type, array( 'file', 'gallery' ) ) ) {
            $single_new_value = cfg_normalize_media_value( $single_new_value, $field );
        }

        if ( 'true_false' == $field_type ) {
            $single_new_value = cfg_normalize_truefalse_value( $single_new_value, $field );
        }

        if ( in_array( $field_type, array( 'radio', 'select', 'checkbox' ) ) ) {
            $single_new_value = cfg_normalize_choice_value( $single_new_value, $field );
        }

        if ( 'hyperlink' == $field_type ) {
            $single_new_value = cfg_normalize_hyperlink_value( $single_new_value, $field );
        }

        if ( 'date' == $field_type ) {
            $single_new_value = cfg_normalize_date_value( $single_new_value, $field );
        }

        if ( 'time' == $field_type ) {
            $single_new_value = cfg_normalize_time_value( $single_new_value, $field );
        }

        if ( 'relationship' == $field_type ) {
            $single_new_value = cfg_normalize_relationship_value( $single_new_value, $field );
        }

        if ( 'term' == $field_type ) {
            $single_new_value = cfg_normalize_term_value( $single_new_value, $field );
        }

        if ( 'user' == $field_type ) {
            $single_new_value = cfg_normalize_user_value( $single_new_value, $field );
        }
        
        // Let's update the $field_name field with the $single_new_value
        $field_data[$field_id]['value'] = $single_new_value;
        // vi( $field_data, '', 'processed a single value for updating a field via CFG->save' );
    }
    
    // $fields_data is an associative array of field names and their new values
    if ( is_array( $fields_data ) && ! empty( $fields_data ) ) {
        foreach ( $fields_data as $field_name => $new_value ) {
            $field = $all_cf_info[$field_name];
            $field_id = $all_cf_info[$field_name]['id'];
            $field_type = $all_cf_info[$field_name]['type'];
            
            // Maybe normalize the format of $new_value for certain field types

            if ( in_array( $field_type, array( 'file', 'gallery' ) ) ) {
                $new_value = cfg_normalize_media_value( $new_value, $field );
            }

            if ( 'true_false' == $field_type ) {
                $new_value = cfg_normalize_truefalse_value( $new_value, $field );
            }

            if ( in_array( $field_type, array( 'radio', 'select', 'checkbox' ) ) ) {
                $new_value = cfg_normalize_choice_value( $new_value, $field );
            }

            if ( 'hyperlink' == $field_type ) {
                $new_value = cfg_normalize_hyperlink_value( $new_value, $field );
            }

            if ( 'date' == $field_type ) {
                $new_value = cfg_normalize_date_value( $new_value, $field );
            }

            if ( 'time' == $field_type ) {
                $new_value = cfg_normalize_time_value( $new_value, $field );
            }

            if ( 'relationship' == $field_type ) {
                $new_value = cfg_normalize_relationship_value( $new_value, $field );
            }

            if ( 'term' == $field_type ) {
                $new_value = cfg_normalize_term_value( $new_value, $field );
            }

            if ( 'user' == $field_type ) {
                $new_value = cfg_normalize_user_value( $new_value, $field );
            }
            
            if ( 'repeater' == $field_type ) {
                $new_value = cfg_normalize_repeater_value( $new_value, $field, $post_id );
            }
            
            // Let's update the $field_name field with the $new_value
            if ( 'repeater' != $field_type ) {
                $field_data[$field_id]['value'] = $new_value;
            } else {
                $field_data[$field_id] = $new_value;
            }
        }
        // vi( $field_data, '', 'processed multiple new values for updating multiple fields via CFG->save' );
    }

    $post_data = array(
        'ID'    => $post_id,
    );
    // vi( $post_data, '', 'for duplicating ASE post meta' );
    
    // Get Custom Field Group IDs from the original post
    $cfgroup_ids = array();
    foreach ( $all_cf_info as $cf_name => $cf_info ) {
        if ( is_array( $cf_info ) && isset( $cf_info['group_id'] ) && ! in_array( $cf_info['group_id'], $cfgroup_ids ) ) {
            $cfgroup_ids[] = $cf_info['group_id'];
        }
    }
    
    $options = array(
        'format'        => 'input',
        'field_groups'  => $cfgroup_ids,
    );
    // vi( $options, '', 'for duplicating ASE post meta' );

    $result = CFG()->save(
        $field_data,
        $post_data,
        $options
    );
    // vi( $result, '', 'of ASE field data duplication during content duplication' );
    
    return $result; // The ID of the post being updated
}

/**
 * Maybe convert file / gallery value to the appropriate format, i.e. attachment ID or comma-separated attachment IDs
 * 
 * @since 7.8.4
 */
function cfg_normalize_media_value( $value, $field ) {
    // Single number, e.g. 723
    if ( is_numeric( $value ) ) {
        return intval( $value );
    }
    
    // e.g. array( 723 ) | array( 980, 723 ) | array( 'https://www.example.com/some-image.jpg', 'https://www.example.com/another-image.png'  )
    if ( is_array( $value ) && ! empty( $value ) ) {
        $value = array_values( $value );
        $value = implode( ',', $value );
    }

    // String
    if ( is_string( $value ) && ! empty( $value ) ) {
        $value = trim( $value ); // Remove space at the beginning and end of string
        $value = str_replace( ' ', '', $value ); // Remove space in between elements
        
        $attachment_ids = array();
        
        if ( false !== strpos( $value, ',' ) ) {
            $value_array = explode( ',', $value );

            foreach ( $value_array as $val ) {
                if ( is_numeric( $val ) ) {
                    $attachment_ids[] = intval( $val );
                } else {
                    if ( filter_var( $val, FILTER_VALIDATE_URL )  ) {
                        // Let's try to sideload the image from the URL
                        $result = media_sideload_image( $val, '', null, 'id' );
                        
                        if ( ! is_wp_error( $result ) && is_numeric( $result ) ) {
                            $attachment_ids[] = intval( $result );
                        }
                    }                    
                }
            }

            $value = implode( ',', $attachment_ids );
        } 
        // String of single element without comma
        else {
            if ( is_numeric( $value ) ) {
                $attachment_ids[] = intval( $value );
            } else {
                if ( filter_var( $value, FILTER_VALIDATE_URL )  ) {
                    // Let's try to sideload the image from the URL
                    $result = media_sideload_image( $value, null, null, 'id' );
                    
                    if ( ! is_wp_error( $result ) && is_numeric( $result ) ) {
                        $attachment_ids[] = intval( $result );
                    }
                }
            }
            
            $value = implode( ',', $attachment_ids );
        }
    }
    // vi( $value, '', 'final' );
    
    return $value;
}

/**
 * Make sure boolean is returned for True/False field value
 * 
 * @since 7.8.3
 */
function cfg_normalize_truefalse_value( $value, $field ) {
    if ( is_string( $value ) ) {
        if ( in_array( $value, array( '0', '1' ) ) ) {
            return $value;
        }
        if ( 'true' === $value ) {
            return true;
        }
        if ( 'false' === $value ) {
            return false;
        }
    } elseif ( is_integer( $value ) ) {
        if ( 1 === $value ) {
            return true;
        }
        if ( 0 === $value ) {
            return false;
        }
    } elseif ( is_bool( $value ) ) {
        return $value;
    }
}

/**
 * Maybe convert select / checkbox choices to the appropriate format, i.e. string of choice value or string of comma-separated values
 * e.g. 'rock', or 'soccer,basketball,swimming'
 * 
 * @since 7.8.4
 */
function cfg_normalize_choice_value( $value, $field ) {
    $choices = $field['options']['choices'];
    $new_value = array();

    if ( is_array( $value ) && ! empty( $value ) ) {
        $value = array_values( $value );
        $value = implode( ',', $value );
    }

    if ( is_string( $value ) && ! empty( $value ) ) {
        // String of comma-separated values
        if ( false !== strpos( $value, ',' ) ) {
            $value_array = explode( ',', $value );
            foreach ( $value_array as $val ) {
                foreach ( $choices as $choice_val => $choice_label ) {
                    if ( trim( $val ) == trim( $choice_val ) || trim( $val ) == trim( $choice_label ) ) {
                        $new_value[] = $choice_val;
                    }
                }
            }
            
            $value = implode( ',', $new_value );
        } 
        // String of single element without comma
        else {
            foreach ( $choices as $choice_val => $choice_label ) {
                if ( trim( $value ) == trim( $choice_val ) || trim( $value ) == trim( $choice_label ) ) {
                    $value = $choice_val;
                }
            }
        }
    }
    // vi( $value, '', 'final' );
    
    return $value;
}

/**
 * Maybe convert string value to array of 'url', 'text' and 'target' for Hyperlink field value
 * 
 * @since 7.8.3
 */
function cfg_normalize_hyperlink_value( $value, $field ) {
    if ( is_string( $value ) && ! empty( $value ) ) {
        if ( false !== strpos( $value, ',' ) ) {
            $val_array = explode( ',', $value );
            $url = isset( $val_array[0] ) ? $val_array[0] : '';
            $text = isset( $val_array[1] ) ? $val_array[1] : '';
            $target = isset( $val_array[2] ) ? $val_array[2] : '';
        } else {
            if ( false !== filter_var( $value, FILTER_VALIDATE_URL ) ) {
                $url = $value;
                $text = $value;
                $target = 'none';
            }
        }
        
        return array(
            'url'       => $url,
            'text'      => $text,
            'target'    => $target,
        );
    } else if ( is_array( $value ) && isset( $value['url'] ) && isset( $value['text'] ) && isset( $value['target'] ) ) {
        return $value;
    } else {
        return array();
    }
}

/**
 * Maybe convert date value to the appropriate format
 * 
 * @since 7.8.4
 */
function cfg_normalize_date_value( $value, $field ) {
    if ( is_string( $value ) && ! empty( $value ) ) {
        $timezone_object = new DateTimeZone( 'UTC' );
        $value = wp_date( 'Y-m-d', strtotime( $value ), $timezone_object );        
    }

    return $value;
}

/**
 * Maybe convert date value to the appropriate format, G:i, e.g. 9:30 | 19:45
 * 
 * @since 7.8.4
 */
function cfg_normalize_time_value( $value, $field ) {
    $value = trim( $value );
    
    if ( ! empty( $value ) && false !== strpos( $value, ':' ) ) {
        $value = strtolower( $value ); // Convert AM/PM to am/pm, if present
        $value = str_replace( ' ', '', $value ); // Remove space between number, e.g. 07:09 and AM/PM, if present
        
        // If am or pm is present
        if ( false !== strpos( $value, 'am' ) || false !== strpos( $value, 'pm' ) ) {
            if ( false !== strpos( $value, 'am' ) ) {
                $ampm = 'am';
                $value_raw = str_replace( 'am', '', $value ); // 7:09 or 07:09
            }
            
            if ( false !== strpos( $value, 'pm' ) ) {
                $ampm = 'pm';
                $value_raw = str_replace( 'pm', '', $value ); // 7:09 or 07:09
            }

        } else {
            $ampm = '';
            $value_raw = $value;
        }

        $value_raw = ltrim( $value_raw, '0' ); // Remove preceding 0, so at this point, value is, e.g., 7:09
        $value_raw_array = explode( ':', $value_raw );

        $hour_raw = intval( $value_raw_array[0] ); // e.g. 7
        $minutes_raw = $value_raw_array[1]; // e.g. 09

        if ( 'pm' == $ampm ) {
            // $hour_raw is between 0 to 11, so we add 12 to it.
            $hour_raw = $hour_raw + 12;
        }
        $hour_raw = (string) $hour_raw;
        
        $value = $hour_raw . ':' . $minutes_raw;
    }

    return $value;
}

/**
 * Maybe convert relationship field value to the appropriate format, which is the post ID or string of comma-separated post IDs
 * Accepted values include post ID or path, comma-separated post IDs / paths, array of post IDs / paths
 * 
 * @link https://developer.wordpress.org/reference/functions/get_post/
 * @link https://developer.wordpress.org/reference/functions/get_page_by_path/
 * @since 7.8.4
 */
function cfg_normalize_relationship_value ( $value, $field ) {
    // Single number, e.g. 723
    if ( is_numeric( $value ) ) {
        return intval( $value );
    }
    
    // e.g. array( 723 ) | array( 980, 723 ) | array( 'species-going-extinct', 'stopping-runaway-climate-change'  )
    if ( is_array( $value ) && ! empty( $value ) ) {
        $value = array_values( $value );
        $value = implode( ',', $value );
    }

    // String
    if ( is_string( $value ) && ! empty( $value ) ) {
        $value = trim( $value ); // Remove space at the beginning and end of string
        $value = str_replace( ' ', '', $value ); // Remove space in between elements

        $post_ids = array();
        $post_types = get_post_types( array( 'builtin' => false ), 'names' );
        $post_types['page'] = 'page';
        $post_types['post'] = 'post';
        $post_types = array_keys( $post_types );

        // String of comma-separated values
        if ( false !== strpos( $value, ',' ) ) {
            $value_array = explode( ',', $value );

            foreach ( $value_array as $val ) {
                if ( is_numeric( $val ) ) {
                    $post = get_post( intval( $val ), OBJECT );
                    $post_ids[] = intval( $post->ID );
                } else {
                    $val = trim( $val, '/' ); // removing opening and traling slash, e.g. '/post-name-slug/'' => 'post-name-slug'
                    foreach ( $post_types as $post_type ) {
                        $post = get_page_by_path( $val, OBJECT, $post_type );
                        if ( is_object( $post ) ) {
                            if ( property_exists( $post, 'ID' ) ) {
                                $post_ids[] = intval( $post->ID );
                                break;
                            }
                        }
                    }
                }
            }

            $value = implode( ',', $post_ids );
        } 
        // String of single element without comma
        else {
            if ( is_numeric( $value ) ) {
                $post = get_post( intval( $value ), OBJECT );
                $post_ids[] = intval( $post->ID );
            } else {
                $value = trim( $value, '/' ); // removing opening and traling slash, e.g. '/post-name-slug/'' => 'post-name-slug'
                foreach ( $post_types as $post_type ) {
                    $post = get_page_by_path( $value, OBJECT, $post_type );
                    if ( is_object( $post ) ) {
                        if ( property_exists( $post, 'ID' ) ) {
                            $value = intval( $post->ID );
                        }
                        break;
                    }
                }
            }
        }
    }
    // vi( $value, '', 'final' );
    
    return $value;
}

/**
 * Maybe conver term field value to the appropriate format, which is the term ID or string of comma-separated term ID
 * 
 * @since 7.8.4
 */
function cfg_normalize_term_value( $value, $field ) {
    // Single number, e.g. 723
    if ( is_numeric( $value ) ) {
        return intval( $value );
    }

    // e.g. array( 723 ) | array( 980, 723 ) | array( 'science', 'politics'  )
    if ( is_array( $value ) && ! empty( $value ) ) {
        $value = array_values( $value );
        $value = implode( ',', $value );
    }

    // String
    if ( is_string( $value ) && ! empty( $value ) ) {
        $value = trim( $value ); // Remove space at the beginning and end of string
        $value = str_replace( ' ', '', $value ); // Remove space in between elements

        $term_ids = array();
        $taxonomies = get_taxonomies( array( '_builtin' => false ), 'names' );
        $taxonomies['category'] = 'category';
        $taxonomies['post_tag'] = 'post_tag';
        $taxonomies = array_keys( $taxonomies );

        // String of comma-separated values
         if ( false !== strpos( $value, ',' ) ) {
            $value_array = explode( ',', $value );

            foreach ( $value_array as $val ) {
                if ( is_numeric( $val ) ) {
                    $term_ids[] = intval( $val );
                } else {
                    $val = trim( $val );
                    foreach ( $taxonomies as $taxonomy ) {
                        $term = get_term_by( 'name', $val, $taxonomy, OBJECT );
                        if ( is_object( $term ) ) {
                            if ( property_exists( $term, 'term_id' ) ) {
                                $term_ids[] = intval( $term->term_id );
                                break;
                            }
                        }

                        $term = get_term_by( 'slug', $val, $taxonomy, OBJECT );
                        if ( is_object( $term ) ) {
                            if ( property_exists( $term, 'term_id' ) ) {
                                $term_ids[] = intval( $term->term_id );
                                break;
                            }
                        }
                    }
                }
            }

            $value = implode( ',', $term_ids );
        } 
        // String of single element without comma
        else {
            if ( is_numeric( $value ) ) {
                $term_ids[] = intval( $value );
            } else {
                $value = trim( $value ); // removing opening and traling slash, e.g. '/post-name-slug/'' => 'post-name-slug'
                foreach ( $taxonomies as $taxonomy ) {
                    $term = get_term_by( 'name', $value, $taxonomy, OBJECT );
                    if ( is_object( $term ) ) {
                        if ( property_exists( $term, 'term_id' ) ) {
                            $value = intval( $term->term_id );
                            break;
                        }
                    }

                    $term = get_term_by( 'slug', $value, $taxonomy, OBJECT );
                    if ( is_object( $term ) ) {
                        if ( property_exists( $term, 'term_id' ) ) {
                            $value = intval( $term->term_id );
                            break;
                        }
                    }
                }
            }
        }
    }
    // vi( $value, '', 'final' );

    return $value;
}

/**
 * Maybe convert user field value to the appropriate format, which is the user ID or string of comma-separated post IDs
 * 
 * @since 7.8.4
 */
function cfg_normalize_user_value( $value, $field ) {    
    // Single number, e.g. 11
    if ( is_numeric( $value ) ) {
        $value = array_values( $value );
        return intval( $value );
    }
    
    // e.g. array( 'john@example.com' ) | array( 11, 2 ) | array( 'adminuser', 'dailycontributor'  )
    if ( is_array( $value ) && ! empty( $value ) ) {
        $value = implode( ',', $value );
    }
    
    // String
    if ( is_string( $value ) && ! empty( $value ) ) {
        $value = trim( $value ); // Remove space at the beginning and end of string
        $value = str_replace( ' ', '', $value ); // Remove space in between elements

        // String of comma-separated values
        if ( false !== strpos( $value, ',' ) ) {
            $value_array = explode( ',', $value );
            $user_ids = array();
            foreach ( $value_array as $val ) {
                if ( is_numeric( $val ) ) {
                    $user_ids[] = intval( $val );
                } else if ( filter_var( $val, FILTER_VALIDATE_EMAIL ) ) {
                    $user = get_user_by( 'email', $val );
                    if ( is_object( $user ) ) {
                        if ( property_exists( $user, 'ID' ) ) {
                            $user_ids[] = intval( $user->ID );
                        }
                    }
                } else {
                    $user = get_user_by( 'login', $val );
                    if ( is_object( $user ) ) {
                        if ( property_exists( $user, 'ID' ) ) {
                            $user_ids[] = intval( $user->ID );
                        }
                    }
                }
            }

            $value = implode( ',', $user_ids );
        } 
        // String of single element without comma
        else {
            if ( is_numeric( $value ) ) {
                // Do nothing, $value is already a potential user ID
            } else if ( filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
                $user = get_user_by( 'email', $value );
                if ( is_object( $user ) ) {
                    if ( property_exists( $user, 'ID' ) ) {
                        $value = intval( $user->ID );
                    }
                }                
            } else {
                $user = get_user_by( 'login', $value );
                if ( is_object( $user ) ) {
                    if ( property_exists( $user, 'ID' ) ) {
                        $value = intval( $user->ID );
                    }
                }
            }
        }
    }
    
    return $value;
}

/**
 * Normalize value for repeater fields. Cna work with nested (parent-child) repeater, i.e. a repeater field with a child repeater in it.
 * 
 * @since 7.8.4
 */
function cfg_normalize_repeater_value( $value, $field, $post_id ) {
    if ( is_array( $value ) && ! empty( $value ) ) {
        $i = 0;
        $new_value = array();
        foreach ( $value as $sub_field_values ) {
            foreach ( $sub_field_values as $sub_field_name => $sub_field_value ) {
                $sub_field = get_cf_info( $sub_field_name, $post_id );
                
                // Contains sub-field value only
                if ( is_string( $sub_field_value ) || is_integer( $sub_field_value ) || is_bool( $sub_field_value ) ) {
                    $new_value[$i][$sub_field['id']]['value'][0] = $sub_field_value;
                }
                
                // Contains sub-sub-field values
                if ( is_array( $sub_field_value ) ) {
                    $k = 0;
                    foreach ( $sub_field_value as $subsub_field_values ) {
                        foreach ( $subsub_field_values as $subsub_field_name => $subsub_field_value ) {
                            $subsub_field = get_cf_info( $subsub_field_name, $post_id );
                            $new_value[$i][$sub_field['id']][$k][$subsub_field['id']]['value'][0] = $subsub_field_value;
                        }
                        $k++;
                    }
                }
            }
            $i++;
        }

        return $new_value;
    } else {
        return $value;
    }
}

/** 
 * Turn comma-separated values into an indexed array of values for update_cf() --> CFG()->save()
 * 
 * @since 7.8.3
 */
// function cfg_transform_to_array_for_cf_update( $value, $field_type = '' ) {
//     if ( is_string( $value ) ) {
//         if ( false !== strpos( $value, ',' ) ) {
//             return explode( ',', $value ); // Turns '123,456' into array( 0 => '123', 1 => '234' )
//         } else {
//             return (array) $value; // Turns '1234' into array( 0 => '1234' );
//         }        
//     } else {
//         // $value is not a string, i.e. an array
//         if ( is_array( $value ) && isset( $value[0] ) ) {
//             // $value is already an indexed array
//             return $value;        
//         }
//     }
// }

/**
 * Find fields based on post ID, group ID, field type, etc.
 * 
 * @since 7.8.4
 */
function find_cf( $args = array() ) {
    if ( empty( $args ) ) {
        $args = array(
            'post_id'       => false,   // (int) single post ID or false (bool) for fields from all posts
            'group_id'      => array(), // (int) group ID, or (array) group IDs
            'field_id'      => array(), // (int) field ID, or (array) field IDs
            'field_name'    => array(), // (string) field name, or (array) field names
            'field_type'    => array(), // (string) field type, or (array) field types
            'parent_id'     => array(), // (int) parent field ID, or (array) parent field IDs
        );
    }
    
    $fields = CFG()->find_fields( $args );
    $output = array();
    
    foreach ( $fields as $field ) {
        $output[$field['name']] = $field;
    }
    
    return $output;
}

/**
 * Get field info of a sub-field's parent repeater
 * 
 * @since 7.8.6
 */
function get_parent_repeater_cf( $subfield_name ) {
    $subfield = find_cf( array( 'field_name' => $subfield_name ) );
    $parent_repeater_id = $subfield[$subfield_name]['parent_id'];
    
    $all_fields = find_cf();
    foreach ( $all_fields as $field_name => $field ) {
        if ( $parent_repeater_id == $field['id'] && 'repeater' == $field['type'] ) {
            $parent_repeater = find_cf( array( 'field_name' => $field['name'] ) );
            return $parent_repeater[$field['name']];
        }
    }
}

/**
 * Create a new post and save custom field data for it
 * 
 * @since 7.8.4
 */
function insert_post_cf( $post_data, $fields_data = array() ) {
    
    // We replace the CFG()->save( $field_data, $post_data ) method with wp_insert_post() and update_cf()
    // This will allow for a wider variety of acceptable data formats for the custom fields facilitated by update_cf()

    $post_defaults = [
        'post_title'                => 'New Post',
        'post_content'              => '',
        'post_author'               => '',
        'post_status'               => 'draft',
    ];
    $post_data = array_merge( $post_defaults, $post_data );

    // Create the post
    $post_id = wp_insert_post( $post_data );
    
    // Update custom fields data for the post
    $post_id = update_cf( $post_id, $fields_data );
    
    return intval( $post_id );
}

/**
 * Get all custom field group IDs
 * 
 * @since 7.8.10
 */
function get_all_cfgroup_ids() {
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
    
    return $cfgroup_ids;
}

/**
 * Get custom field group IDs by placement
 * 
 * @since 7.8.10
 * @param  string $placement    'posts' | 'options-pages' | 'taxonomy-terms'
 * @return array                array of custom field group IDs
 */
function get_cfgroup_ids_by_placement( $placement = 'posts' ) {
    $cfgroup_ids = get_all_cfgroup_ids();
    $cfgroup_ids_by_placement = array();
    
    foreach ( $cfgroup_ids as $cfgroup_id ) {
        $rules = get_post_meta( $cfgroup_id, 'cfgroup_rules', true );
        
        if ( isset( $rules['placement'] ) && $placement == $rules['placement']['values'] ) {
            $cfgroup_ids_by_placement[] = $cfgroup_id;
        }
    }
    
    return $cfgroup_ids_by_placement;
}

/**
 * Retrieves the taxonomy name associated on the specified $term_id. 
 *
 * @link https://tommcfarlin.com/taxonomy-by-term-id/
 * @param  int    $term_id  The term ID from which to retrieve the taxonomy name.
 * @return string $taxonomy The name of the taxaonomy associated with the term ID.
 */
function get_taxonomy_by_term_id( $term_id ) {
    
    // We can't get a term if we don't have a term ID.
    if ( 0 === $term_id || null === $term_id ) {
        return;
    }
    
    // Grab the term using the ID then read the name from the associated taxonomy.
    $taxonomy = '';
    $term = get_term( $term_id );
    if ( false !== $term ) {
        $taxonomy = $term->taxonomy;
    }

    return trim( $taxonomy );
}

/**
 * Retrieve the custom field group rules for a custom field
 * 
 * @since 7.8.16
 */
function get_cf_cfgroup_rules( $field_name ) {
    $field_info = get_cf_info( $field_name );
    $cfgroup_id = isset( $field_info['group_id'] ) ? intval( $field_info['group_id'] ) : 0;
    
    if ( $cfgroup_id > 0 ) {
        $cfgroup_rules = get_post_meta( $cfgroup_id, 'cfgroup_rules', true );
    } else {
        $cfgroup_rules = array();
    }

    return $cfgroup_rules;
}
