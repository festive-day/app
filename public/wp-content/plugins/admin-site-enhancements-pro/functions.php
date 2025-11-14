<?php

/**
 * Get default WordPress avatar URL by user email
 *
 * @link https://plugins.trac.wordpress.org/browser/simple-user-avatar/tags/4.3/admin/class-sua-admin.php
 * @since  6.2.0
 */
function get_default_avatar_url_by_email__premium_only( $user_email = '', $size = 96 ) {
	// Check the email provided
	if ( empty( $user_email ) || ! filter_var( $user_email, FILTER_VALIDATE_EMAIL ) ) {
		return null;
	}

	// Sanitize email and get md5
	$user_email     = sanitize_email( $user_email );
	$md5_user_email = md5( $user_email );

	// SSL Gravatar URL
	$url = 'https://secure.gravatar.com/avatar/' . $md5_user_email;

	// Add query args
	$url = add_query_arg( 's', $size, $url );
	$url = add_query_arg( 'd', 'mm', $url );
	$url = add_query_arg( 'r', 'g', $url );

	return esc_url( $url );
}

/**
 * Get kses ruleset extended to allow svg
 * 
 * @since 6.9.5
 */
function get_kses_with_svg_ruleset() {
	$kses_defaults = wp_kses_allowed_html( 'post' );

	$svg_args = array(
	    'svg'   => array(
	        'class'				=> true,
	        'aria-hidden'		=> true,
	        'aria-labelledby'	=> true,
	        'role'				=> true,
	        'xmlns'				=> true,
	        'width'				=> true,
	        'height'			=> true,
	        'viewbox'			=> true,
	        'viewBox'			=> true,
	    ),
	    'g'     => array( 
	    	'fill' 				=> true,
	    	'fill-rule' 		=> true,
	        'stroke'			=> true,
	        'stroke-linejoin'	=> true,
	        'stroke-width'		=> true,
	        'stroke-linecap'	=> true,
	    ),
	    'title' => array( 'title' => true ),
	    'path'  => array( 
	        'd'					=> true,
	        'fill'				=> true,
	        'stroke'			=> true,
	        'stroke-linejoin'	=> true,
	        'stroke-width'		=> true,
	        'stroke-linecap'	=> true,
	    ),
	    'rect'	=> array(
	    	'width'				=> true,
	    	'height'			=> true,
	    	'x'					=> true,
	    	'y'					=> true,
	    	'rx'				=> true,
	    	'ry'				=> true,
	    ),
	    'circle' => array(
	    	'cx'				=> true,
	    	'cy'				=> true,
	    	'r'				=> true,
	    ),
	);

	return array_merge( $kses_defaults, $svg_args );
	// Example usage: wp_kses( $the_svg_icon, get_kses_with_svg_ruleset() );	
}

/**
 * Get kses ruleset extended to allow style and script tags
 * 
 * @since 6.9.5
 */
function get_kses_with_style_src_ruleset() {
    $kses_defaults = wp_kses_allowed_html( 'post' );

    $style_script_args = array(
    	'link'		=> array(
    		'rel'			=> true,
    		'href'			=> true,
    		'sizes'			=> true,
    		'crossorigin'	=> true,
    	),
    	'style'		=> true,
    	'script'	=> array(
    		'src'	=> true,
    	),
    );
    
    return array_merge( $kses_defaults, $style_script_args );
	// Example usage: wp_kses( $the_html, get_kses_with_style_src_ruleset() );	
}

/**
 * Get kses ruleset extended to allow style and script tags
 * 
 * @since 6.9.5
 */
function get_kses_with_style_src_svg_ruleset() {
    $kses_defaults = wp_kses_allowed_html( 'post' );

    $style_script_svg_args = array(
    	'input'	=> array(
    		'type'	=> true,
    		'id'	=> true,
    		'class'	=> true,
    		'name'	=> true,
    		'value'	=> true,
    		'style'	=> true,
    	),
    	'style'		=> true,
    	'script'	=> array(
    		'src'	=> true,
    	),
    	'iframe' => array(
    		'title'				=> true,
    		'name'				=> true,
    		'wdith'				=> true,
    		'height'			=> true,
    		'src'				=> true,
    		'srcdoc'			=> true,
    		'align'				=> true, // deprecated
    		'frameborder'		=> true, // deprecated
    		'scrolling'			=> true, // deprecated
    		'allow'				=> true,
    		'referrerpolicy'	=> true,
    		'allowfullscreen'	=> true,
    		'loading'			=> true,
    		'sandbox'			=> true,
    	),
	    'svg'   => array(
	        'class'				=> true,
	        'aria-hidden'		=> true,
	        'aria-labelledby'	=> true,
	        'role'				=> true,
	        'xmlns'				=> true,
	        'width'				=> true,
	        'height'			=> true,
	        'viewbox'			=> true,
	        'viewBox'			=> true,
	    ),
	    'g'     => array( 
	    	'fill' 				=> true,
	    	'fill-rule' 		=> true,
	        'stroke'			=> true,
	        'stroke-linejoin'	=> true,
	        'stroke-width'		=> true,
	        'stroke-linecap'	=> true,
	    ),
	    'title' => array( 'title' => true ),
	    'path'  => array( 
	        'd'					=> true,
	        'fill'				=> true,
	        'stroke'			=> true,
	        'stroke-linejoin'	=> true,
	        'stroke-width'		=> true,
	        'stroke-linecap'	=> true,
	    ),
	    'rect'	=> array(
	    	'width'				=> true,
	    	'height'			=> true,
	    	'x'					=> true,
	    	'y'					=> true,
	    	'rx'				=> true,
	    	'ry'				=> true,
	    ),
	    'circle' => array(
	    	'cx'				=> true,
	    	'cy'				=> true,
	    	'r'				=> true,
	    ),
    );
    
    return array_merge( $kses_defaults, $style_script_svg_args );
	// Example usage: wp_kses( $the_html, get_kses_with_style_src_svg_ruleset() );	
}

/**
 * Get kses ruleset extended to allow input tags
 * 
 * @since 6.9.5
 */
function get_kses_with_custom_html_ruleset() {
    $kses_defaults = wp_kses_allowed_html( 'post' );

    $custom_html_args = array(
    	'input'	=> array(
    		'type'	=> true,
    		'id'	=> true,
    		'class'	=> true,
    		'name'	=> true,
    		'value'	=> true,
    		'style'	=> true,
    	)
    );
    
    return array_merge( $kses_defaults, $custom_html_args );
	// Example usage: wp_kses( $the_html, get_kses_with_custom_html_ruleset() );	
}

/**
 * Export ASE's settings
 * 
 */
function asenha_settings_export__premium_only() {

	if ( empty( $_POST['asenha_export_action'] ) || 'export_settings' != $_POST['asenha_export_action'] ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['asenha_export_nonce'], 'asenha_export_nonce' ) ) {
		wp_die( 'Invalid nonce. Please try again.', 'Error', array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Permission denied. Please contact your site administrator to run the export process.', 'admin-site-enhancements' ), __( 'Error', 'admin-site-enhancements' ), array( 'response' => 403 ) );
	}
	
	$asenha_settings = get_option( ASENHA_SLUG_U, array() );
	$asenha_settings_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
	$admin_menu_settings = isset( $asenha_settings_extra['admin_menu'] ) ? $asenha_settings_extra['admin_menu'] : array();
	
	// Prevent auto-check of "Discourage search engine" when the new site is a live site with a different URL
	$asenha_settings['live_site_url'] = '';
	$asenha_settings['admin_menu'] = $admin_menu_settings;
	
	ignore_user_abort( true );
	
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=admin-site-enhancements-ase-settings-' . date('Y-m-d-Hi') . '.json' );
	header( 'expires: 0' );
	
	echo json_encode( $asenha_settings );
	exit;
	
}

/**
 * Import ASE's settings
 * 
 */
function asenha_settings_import__premium_only() {
	if ( isset( $_FILES['imported-settings'] ) ) {
		$imported_settings = asenha_get_import_content( 'imported-settings' );
		
		if ( $imported_settings ) {
			// Quick check to see if JSON file does indeed contain ASE settings
			if ( array_key_exists( 'enable_duplication', $imported_settings ) ) {
				// We make sure rewrite rules are flushed on the new site
				$imported_settings['custom_content_types_flush_rewrite_rules_needed'] = true;
				$imported_settings['code_snippets_manager_flush_rewrite_rules_needed'] = true;
				// Create new, random secret key for CAPTCHA Protection >> ALTCHA
				$imported_settings['altcha_secret_key'] = bin2hex( random_bytes( 12 ) );

				// Admin Menu Organizer
				if ( isset( $imported_settings['admin_menu'] ) ) {
					$imported_admin_menu_settings = $imported_settings['admin_menu'];
					unset( $imported_settings['admin_menu'] );
				} else {
					$imported_admin_menu_settings = array();
				}

				$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
				$options_extra['admin_menu'] = $imported_admin_menu_settings;

				$import_success = update_option( ASENHA_SLUG_U, $imported_settings, true );
				$import_extra_success = update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );
				
				if ( $import_success && $import_extra_success ) {
					// Reload the ASE settings page via JS after import success
					wp_safe_redirect( admin_url( 'tools.php?page=admin-site-enhancements&import=success' ) );
					exit;
				}

			}
		}		
	}
}

/**
 * Export code snippets created with Code Snippets Manager
 * 
 * @since 7.8.8
 */
function asenha_snippets_export__premium_only() {

	if ( empty( $_POST['asenha_export_action'] ) || 'export_snippets' != $_POST['asenha_export_action'] ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['asenha_export_nonce'], 'asenha_export_nonce' ) ) {
		wp_die( 'Invalid nonce. Please try again.', 'Error', array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Permission denied. Please contact your site administrator to run the export process.', 'admin-site-enhancements' ), __( 'Error', 'admin-site-enhancements' ), array( 'response' => 403 ) );
	}
	
	$export = array();
	
	$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );

	// Export snippets tree
	$export['snippets_tree'] = isset( $options_extra['code_snippets'] ) ? $options_extra['code_snippets'] : array();

	// Export ID of last edited PHP snippet
	$export['last_edited_csm_php_snippet'] = isset( $options_extra['last_edited_csm_php_snippet'] ) ? $options_extra['last_edited_csm_php_snippet'] : '';

	// Export snippet categories
	$tax_query_args = array(
		'taxonomy'		=> 'asenha_code_snippet_category',
		'hide_empty'	=> false,
	);
	$raw_snippet_categories = get_terms( $tax_query_args );
	
	$snippet_categories = array();
	if ( ! empty( $raw_snippet_categories ) ) {
		foreach ( $raw_snippet_categories as $raw_snippet_category ) {
			if ( $raw_snippet_category->parent > 0 ) {
				$parent_category = get_term( $raw_snippet_category->parent, 'asenha_code_snippet_category' );
				$parent_slug = $parent_category->slug;
			} else {
				$parent_slug = '';
			}
			
			$snippet_categories[$raw_snippet_category->slug] = array(
				'term_id'		=> $raw_snippet_category->term_id,
				'slug'			=> $raw_snippet_category->slug,
				'name'			=> $raw_snippet_category->name,
				'description'	=> $raw_snippet_category->description,
				'parent_id'		=> $raw_snippet_category->parent, // integer
				'parent_slug'	=> $parent_slug,
			);
		}		
	}
	$export['snippet_categories'] = $snippet_categories;

	// Export snippets
	$query_args = array(
		'post_type'		=> 'asenha_code_snippet',
		'numberposts'	=> -1,
		'nopaging'		=> true,
	);
	$snippets = get_posts( $query_args );	

	foreach ( $snippets as $snippet ) {
		$export['snippets'][$snippet->ID]['post_id'] = $snippet->ID;
		$export['snippets'][$snippet->ID]['post_title'] = $snippet->post_title;
		$export['snippets'][$snippet->ID]['post_content'] = $snippet->post_content;
		$export['snippets'][$snippet->ID]['post_status'] = $snippet->post_status;
		$export['snippets'][$snippet->ID]['post_author'] = $snippet->post_author;
		$export['snippets'][$snippet->ID]['post_type'] = $snippet->post_type;
		$export['snippets'][$snippet->ID]['menu_order'] = $snippet->menu_order;
		$export['snippets'][$snippet->ID]['code_snippet_description'] = get_post_meta( $snippet->ID, 'code_snippet_description', true );
		$export['snippets'][$snippet->ID]['options'] = get_post_meta( $snippet->ID, 'options', true );
		if ( get_post_meta( $snippet->ID, '_active', true ) ) {
			$export['snippets'][$snippet->ID]['_active'] = get_post_meta( $snippet->ID, '_active', true );
		} else {
			$export['snippets'][$snippet->ID]['_active'] = 'yes';
		}
		
		$raw_snippet_categories = get_the_terms( $snippet->ID, 'asenha_code_snippet_category' );
		$snippet_categories = array();
		if ( ! is_wp_error( $raw_snippet_categories ) && ! empty( $raw_snippet_categories ) ) {
			foreach ( $raw_snippet_categories as $raw_snippet_category ) {
				if ( $raw_snippet_category->parent > 0 ) {
					$parent_category = get_term( $raw_snippet_category->parent, 'asenha_code_snippet_category' );
					$parent_slug = $parent_category->slug;
				} else {
					$parent_slug = '';
				}

				$snippet_categories[$raw_snippet_category->slug] = array(
					'term_id'		=> $raw_snippet_category->term_id,
					'slug'			=> $raw_snippet_category->slug,
					'name'			=> $raw_snippet_category->name,
					'description'	=> $raw_snippet_category->description,
					'parent_id'		=> $raw_snippet_category->parent, // integer
					'parent_slug'	=> $parent_slug,
				);
			}
		}
		$export['snippets'][$snippet->ID]['snippet_categories'] = $snippet_categories;
	}
	
	ignore_user_abort( true );
	
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=admin-site-enhancements-ase-code-snippets-' . date('Y-m-d-Hi') . '.json' );
	header( 'expires: 0' );
	
	echo json_encode( $export );
	exit;
	
}

/**
 * Import code snippets
 * 
 * @since 7.8.8
 */
function asenha_snippets_import__premium_only() {
	if ( isset( $_FILES['imported-code-snippets'] ) && current_user_can( 'manage_options' ) ) {
		$imported_code_snippets = asenha_get_import_content( 'imported-code-snippets' );
		
		if ( $imported_code_snippets ) {
			// Quick check to see if JSON file does indeed contain code snippets data
			if ( array_key_exists( 'snippets_tree', $imported_code_snippets ) ) {
				// Import snippet categories
				$snippet_categories = $imported_code_snippets['snippet_categories'];

				// Import the snippet parent categories first
				foreach ( $snippet_categories as $snippet_category ) {
					if ( $snippet_category['parent_id'] === 0 ) {
						wp_insert_term(
							$snippet_category['name'],
							'asenha_code_snippet_category',
							array(
								'description'	=> $snippet_category['description'],
								'slug'			=> $snippet_category['slug'],
							)
						);
					}
				}

				// Import the snippet child categories
				foreach ( $snippet_categories as $snippet_category ) {
					if ( $snippet_category['parent_id'] > 0 ) {
						$parent_category = get_term_by( 'slug', $snippet_category['parent_slug'], 'asenha_code_snippet_category' );
						wp_insert_term(
							$snippet_category['name'],
							'asenha_code_snippet_category',
							array(
								'description'	=> $snippet_category['description'],
								'slug'			=> $snippet_category['slug'],
								'parent'		=> $parent_category->term_id,
							)
						);
					}
				}

				// Import the snippets
				if ( ! empty( $imported_code_snippets['snippets'] ) ) {
					$snippets_tree = $imported_code_snippets['snippets_tree'];
					$new_snippets_tree = array();
					
					foreach ( $imported_code_snippets['snippets'] as $snippet_id => $snippet ) {
						$postarr = array(
							'post_title'		=> $snippet['post_title'],
							// We prevent backslashes (\) removal from the code in post_content by adding slashes here
							// This is because wp_insert_post applies wp_unslash() to post_content
							'post_content'		=> wp_slash( $snippet['post_content'] ),
							'post_status'		=> $snippet['post_status'],
							'post_author'		=> $snippet['post_author'],
							'post_type'			=> $snippet['post_type'],
							'menu_order'		=> $snippet['menu_order'],
							'import_id'			=> $snippet['post_id'],
							'comment_status'	=> 'closed',
							'ping_status'		=> 'closed',
						);
						
						$post_id = wp_insert_post( $postarr );
						
						if ( $post_id ) {
							// Let's replace the original snippet/post ID with the new one in $snippets_tree
							foreach ( $snippets_tree as $type => $code_snippets ) {
								if ( 'jquery' != $type ) {
									foreach ( $code_snippets as $code_snippet_id => $code_snippet ) {
										if ( $snippet_id == $code_snippet_id ) {
											$code_snippet['id'] = $post_id;
											$code_snippet['filename'] = str_replace( $code_snippet_id, $post_id, $code_snippet['filename'] );
											$new_snippets_tree[$type][$post_id]	= $code_snippet; 
										}
									}									
								}
							}
							
							update_post_meta( $post_id, 'code_snippet_description', $snippet['code_snippet_description'] );
							update_post_meta( $post_id, 'options', $snippet['options'] );
							update_post_meta( $post_id, '_active', $snippet['_active'] );
							update_post_meta( $post_id, 'is_imported_snippet', 'yes' );
							update_post_meta( $post_id, 'original_snippet_id', $snippet['post_id'] );

							// Let's assign the snippet categories
							$snippet_categories = $snippet['snippet_categories'];
							$snippet_category_slugs = array();
							if ( ! empty( $snippet_categories ) ) {
								foreach ( $snippet_categories as $snippet_category ) {
									$snippet_category_slugs[] = $snippet_category['slug'];
								}
							}
							
							if ( ! empty( $snippet_category_slugs ) ) {
								wp_set_object_terms( $post_id, $snippet_category_slugs, 'asenha_code_snippet_category' );
							}

							asenha_save_snippet_to_file__premium_only( $post_id, $snippet );
						}
					}
				}

				// Import the snippets tree and last edited PHP snippet ID
				$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
				$options_extra['snippets_tree'] = $new_snippets_tree; // Use the new snippet tree with new post IDs
				$options_extra['last_edited_csm_php_snippet'] = $imported_code_snippets['last_edited_csm_php_snippet'];
				$import_extra_success = update_option( ASENHA_SLUG_U . '_extra', $options_extra, true );

				$csm_admin = new Code_Snippets_Manager_Admin;
				$csm_admin->rebuild_snippets_data();

				if ( $import_extra_success ) {
					// Reload the ASE settings page via JS after import success
					wp_safe_redirect( admin_url( 'tools.php?page=admin-site-enhancements&import=success' ) );
					exit;
				}
			}
		}		
	}
}

/**
 * Save code snippet to a corresponding file on disk during import
 * 
 * @since 7.8.8
 */
function asenha_save_snippet_to_file__premium_only( $post_id, $snippet ) {
	// Save the Code Snippet in a file in `wp-content/uploads/code-snippets`
	// This is taken and slightly modified from /includes/premium/code-snippets-manager/includes/admin-screens.php 
	// around line 2044 (v7.8.8)
	$before = '';
	$after  = '';

	if ( $snippet['options']['linking'] == 'internal' ) {
		if ( $snippet['options']['language'] == 'css' ) {
			$before .= '<style type="text/css">' . PHP_EOL;
			$after   = '</style>' . PHP_EOL . $after;
		}

		if ( $snippet['options']['language'] == 'js' ) {
			if ( ! preg_match( '/<script\b[^>]*>([\s\S]*?)<\/script>/im', $snippet['post_content'] ) ) {
				$before .= '<script type="text/javascript">' . PHP_EOL;
				$after   = '</script>' . PHP_EOL . $after;
			} else {
				// the content has a <script> tag, then remove the comments so they don't show up on the frontend
				$snippet['post_content'] = preg_replace( '@/\*[\s\S]*?\*/@', '', $snippet['post_content'] );
			}
		}
	}

	if ( $snippet['options']['linking'] == 'external' ) {
		$before = '/******* Do not edit this file *******' . PHP_EOL .
		'Code Snippets Manager' . PHP_EOL .
		'Saved: ' . date( 'M d Y | H:i:s' ) . ' */' . PHP_EOL;
		$after  = '';
	}
	
	if ( $snippet['options']['language'] == 'php' ) {
		$before = '';
		$after = '';
	}
	
	// Check if code-snippets directory exists. Create if non-existent.
	if ( ! is_dir( CSM_UPLOAD_DIR ) ) {
		wp_mkdir_p( CSM_UPLOAD_DIR );
	}

	// Check if code-snippets directory is writable.
	if ( wp_is_writable( CSM_UPLOAD_DIR ) ) {
		$file_name = $post_id . '.' . $snippet['options']['language'];

		// We do not apply stripslashes() to $snippet['post_content'] as it will remove single backslashes (/) from the code
		if ( $snippet['options']['language'] == 'css' ) {
			$compile_scss = isset( $snippet['options']['compile_scss'] ) ? $snippet['options']['compile_scss'] : 'yes';
			if ( 'yes' == $compile_scss ) {
				// Try to compile SCSS if it's part of the CSS code
				try {
					$csm_admin = new Code_Snippets_Manager_Admin;
					$code_snippet = $csm_admin->scss_compiler( $snippet['post_content'] );
				} catch ( Exception $e ) {
					$code_snippet = $snippet['post_content'];
				}
			} else {
				$code_snippet = $snippet['post_content'];
			}
		} else {
			$code_snippet = $snippet['post_content'];
		}
					
		$file_content = $before . $code_snippet . $after;
		@file_put_contents( CSM_UPLOAD_DIR . '/' . $file_name, $file_content );
	}
}

/**
 * Return an array (json_decode-d) of imported file
 * 
 * @since 7.8.8
 */
function asenha_get_import_content( $name ) {
	$file_extension = pathinfo( $_FILES[$name]['name'], PATHINFO_EXTENSION );
	$file_size = $_FILES[$name]['size'];
	
	// Only process JSON file that do not exceed max upload size
	if ( $file_extension === 'json' && $file_size < wp_max_upload_size() ) {
		$file_name = sanitize_file_name($_FILES[$name]['name']);
		$temp_file_path = $_FILES[$name]['tmp_name'];
		
		if ( is_uploaded_file( $temp_file_path ) ) {
			$file_contents = file_get_contents( $temp_file_path );
			$imported_settings = json_decode( $file_contents, true );
			// vi( $imported_settings );
		
			return $imported_settings;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/**
 * Custom wp_die handler for when Code Snippets Manager is activated
 * Modified from _default_wp_die_handler() in WP v6.3.1
 * 
 * @since 5.8.0
 */
function _custom_wp_die_handler__premium_only( $message, $title = '', $args = [] ) {
	
	if ( is_object( $message ) && property_exists( $message, 'error_data' ) ) {
		
		if ( isset( $message->error_data['internal_server_error'] ) ) {
			$filepath_with_error = $message->error_data['internal_server_error']['error']['file'];
		} else {
			$filepath_with_error = '';
		}

		$is_error_from_csm_snippet = ( false !== strpos( $filepath_with_error, '/premium/code-snippets-manager/' ) ) ? true : false;
		
		if ( $is_error_from_csm_snippet 
			&& is_user_logged_in() 
			&& current_user_can( 'manage_options' ) 
			) {

			$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
			$php_snippet_post_id = isset( $options_extra['last_edited_csm_php_snippet'] ) ? absint( $options_extra['last_edited_csm_php_snippet'] ) : '';

			$snippet_info = isset( $options_extra['code_snippets']['php'][$php_snippet_post_id] ) ? $options_extra['code_snippets']['php'][$php_snippet_post_id] : array();
			
			$execution_method = ( isset( $snippet_info['execution_method'] ) ) ? $snippet_info['execution_method'] : 'on_page_load';
			$execution_location_type = ( isset( $snippet_info['execution_location_type'] ) ) ? $snippet_info['execution_location_type'] : 'hook';

			$active_php_snippets = isset( $options_extra['code_snippets']['php'] ) ? array_keys( $options_extra['code_snippets']['php'] ) : array();
		    $snippet_edit_url = get_edit_post_link( $php_snippet_post_id );
		    
		    // Get error data
	        // Error type and codes. 
	        // Ref: https://www.php.net/manual/en/errorfunc.constants.php#109430
	        // Ref: https://logtivity.io/fatal-errors-wordpress/
	        // E_ERROR - 1
	        // E_WARNING - 2
	        // E_PARSE - 4
	        // E_NOTICE - 8
	        // E_CORE_ERROR - 16
	        // E_CORE_WARNING - 32
	        // E_COMPILE_ERROR - 64
	        // E_COMPILE_WARNING - 128
	        // E_USER_ERROR - 256
	        // E_USER_WARNING - 512
	        // E_USER_NOTICE - 1024
	        // E_STRICT - 2048
	        // E_RECOVERABLE_ERROR - 4096
	        // E_DEPRECATED - 8192
	        // E_USER_DEPRECATED - 16384 

		    if ( is_numeric( $php_snippet_post_id ) 
				&& in_array( $php_snippet_post_id, $active_php_snippets )
			) {
			    $code = $message->error_data['internal_server_error']['error']['type']; 
			    $fatal_error_codes = array( 1, 16, 256 );
			    if ( in_array( intval( $code ), $fatal_error_codes ) ) {
			    	$type = 'fatal';
			    } else {
			    	$type = 'non-fatal';
			    }
			    
			    $file 			= $message->error_data['internal_server_error']['error']['file'];
			    $line 			= $message->error_data['internal_server_error']['error']['line'];
			    $message_full 	= $message->error_data['internal_server_error']['error']['message']; // includes stack trace
			    $message_parts 	= explode( ' in /', $message_full );
			    $message 		= $message_parts[0];
				$error_message 	= $message . ' on line ' . $line;

			    $message_parts 	= explode( 'Stack trace:', $message_full );
			    $stack_trace = $message_parts[1];

			    // Record error info in PHP snippet post meta
				update_post_meta( $php_snippet_post_id, 'php_snippet_has_error', true );
				update_post_meta( $php_snippet_post_id, 'php_snippet_error_type', $type );
				update_post_meta( $php_snippet_post_id, 'php_snippet_error_code', $code );
				update_post_meta( $php_snippet_post_id, 'php_snippet_error_message', '<span class="error-message">' . $error_message . '</span><span class="stack-trace">Stack trace:</span>' . ltrim( nl2br( str_replace( ABSPATH, '/', $stack_trace ) ), '<br />' ) );
				update_post_meta( $php_snippet_post_id, 'php_snippet_error_via', 'wp_die_handler' );
				update_post_meta( $php_snippet_post_id, 'safe_mode_activation_via', 'wp_die_handler' );

			    // Deactivate PHP snippet
			    update_post_meta( $php_snippet_post_id, '_active', 'no' );		    	

			    // We have a fatal error making the site inaccessible, let's enable safe mode, halt PHP snippets execution, and make the site accessible again. This is only for snippets that are executed on_page_load via a hook.
		        if ( 'on_page_load' == $execution_method 
		    		&& 'hook' == $execution_location_type
		    	) {

				    // Enable Safe Mode to stop PHP snippets execution
					$wp_config = new ASENHA\Classes\WP_Config_Transformer;
					$wp_config_options = array(
						'add'       => true, // Add the config if missing.
						'raw'       => true, // Display value in raw format without quotes.
						'normalize' => false, // Normalize config output using WP Coding Standards.
					);
					$is_safe_mode_enabled = $wp_config->update( 'constant', 'CSM_SAFE_MODE', 'true', $wp_config_options );
		        	
		    	}

		    }

		    $redirect_delay_in_seconds = 30;
								
			$message = '<div class="wp-die-message">
							<p>A fatal error has just occurred due to the last edit you performed on the <strong>' . get_the_title( $php_snippet_post_id ) . '</strong> PHP snippet.</p>
							<p>Don\'t worry. Your site should remain accessible and functional. Safe Mode has been enabled and all PHP snippets execution has been stopped to prevent further errors.</p>
							<p>You will be automatically redirected to the edit screen of the offending PHP snippet with some info on the error to help you fix the code.</p>
							<p class="redirection-counter">Redirecting in <span id="countdown">' . $redirect_delay_in_seconds . '</span> seconds.</p>
						</div>
						<div class="admin-only">This message is only shown to site administrators.</div>';

		    // JS redirect script
		    $delayed_js_redirect_script = '<script type="text/javascript">

		    // Redirection countdown script: https://codepen.io/a55555a4444a333/pen/VVzKMO
		    // Total seconds
		    var seconds = ' . $redirect_delay_in_seconds . ';
		    
		    function countdown() {
		        seconds = seconds - 1;
		        if (seconds < 0) {
		            // Redirect link here
		            window.location = "' . $snippet_edit_url .'";
		        } else {
		            // Update remaining seconds
		            document.getElementById("countdown").innerHTML = seconds;
		            // Countdown with JS
		            window.setTimeout("countdown()", 1000);
		        }
		    }
		    
		    // Run countdown function
		    countdown();
			</script>';
			
		} else {

			list( $message, $title, $parsed_args ) = _wp_die_process_input( $message, $title, $args );

			if ( is_string( $message ) ) {
				if ( ! empty( $parsed_args['additional_errors'] ) ) {
					$message = array_merge(
						array( $message ),
						wp_list_pluck( $parsed_args['additional_errors'], 'message' )
					);
					$message = "<ul>\n\t\t<li>" . implode( "</li>\n\t\t<li>", $message ) . "</li>\n\t</ul>";
				}

				$message = sprintf(
					'<div class="wp-die-message">%s</div>',
					$message
				);
			}
			
		}

		$have_gettext = function_exists( '__' );

		if ( ! empty( $parsed_args['link_url'] ) && ! empty( $parsed_args['link_text'] ) ) {
			$link_url = $parsed_args['link_url'];
			if ( function_exists( 'esc_url' ) ) {
				$link_url = esc_url( $link_url );
			}
			$link_text = $parsed_args['link_text'];
			$message  .= "\n<p><a href='{$link_url}'>{$link_text}</a></p>";
		}

		if ( isset( $parsed_args['back_link'] ) && $parsed_args['back_link'] ) {
			$back_text = $have_gettext ? __( '&laquo; Back' ) : '&laquo; Back';
			$message  .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
		}

	if ( ! did_action( 'admin_head' ) ) :
		if ( ! headers_sent() ) {
			header( "Content-Type: text/html; charset={$parsed_args['charset']}" );
			status_header( $parsed_args['response'] );
			nocache_headers();
		}

		$text_direction = $parsed_args['text_direction'];
		$dir_attr       = "dir='$text_direction'";

		/*
		 * If `text_direction` was not explicitly passed,
		 * use get_language_attributes() if available.
		 */
		if ( empty( $args['text_direction'] )
			&& function_exists( 'language_attributes' ) && function_exists( 'is_rtl' )
		) {
			$dir_attr = get_language_attributes();
		}
		?>
<!DOCTYPE html>
<html <?php echo esc_attr( $dir_attr ); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo esc_attr( $parsed_args['charset'] ); ?>" />
	<meta name="viewport" content="width=device-width">
		<?php
		if ( function_exists( 'wp_robots' ) && function_exists( 'wp_robots_no_robots' ) && function_exists( 'add_filter' ) ) {
			add_filter( 'wp_robots', 'wp_robots_no_robots' );
			wp_robots();
		}
		?>
	<title><?php echo esc_html( $title ); ?></title>
	<style type="text/css">
		html {
			background: #f1f1f1;
		}
		body {
			position: relative;
			background: #fff;
			border: 1px solid #ccd0d4;
			color: #444;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			margin: 2em auto;
			padding: 1em 2em;
			max-width: 700px;
			-webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
			box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
		}
		h1 {
			border-bottom: 1px solid #dadada;
			clear: both;
			color: #666;
			font-size: 24px;
			margin: 30px 0 0 0;
			padding: 0;
			padding-bottom: 7px;
		}
		#error-page {
			margin-top: 50px;
		}
		#error-page p,
		#error-page .wp-die-message {
			font-size: 14px;
			line-height: 1.5;
			margin: 25px 0 20px;
		}
		#error-page code {
			font-family: Consolas, Monaco, monospace;
		}
		<?php
		if ( $is_error_from_csm_snippet 
			&& is_user_logged_in() 
			&& current_user_can( 'manage_options' )
			) {
		?>
		#error-page p.redirection-counter {
			font-size: 1.25em;
			text-align: center;
			font-weight: bold;
		}
		#countdown {
			color: #fa7e1e;
		}
		.admin-only {
			border-top: 1px solid #ccc;
			padding-top: 8px;
			display: block;
			width: 100%;
			font-size: 13px;
			color: #999;
			text-align: center;
		}
		<?php
		}
		?>
		ul li {
			margin-bottom: 10px;
			font-size: 14px ;
		}
		a {
			color: #0073aa;
		}
		a:hover,
		a:active {
			color: #006799;
		}
		a:focus {
			color: #124964;
			-webkit-box-shadow:
				0 0 0 1px #5b9dd9,
				0 0 2px 1px rgba(30, 140, 190, 0.8);
			box-shadow:
				0 0 0 1px #5b9dd9,
				0 0 2px 1px rgba(30, 140, 190, 0.8);
			outline: none;
		}
		.button {
			background: #f3f5f6;
			border: 1px solid #016087;
			color: #016087;
			display: inline-block;
			text-decoration: none;
			font-size: 13px;
			line-height: 2;
			height: 28px;
			margin: 0;
			padding: 0 10px 1px;
			cursor: pointer;
			-webkit-border-radius: 3px;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			-webkit-box-sizing: border-box;
			-moz-box-sizing:    border-box;
			box-sizing:         border-box;

			vertical-align: top;
		}

		.button.button-large {
			line-height: 2.30769231;
			min-height: 32px;
			padding: 0 12px;
		}

		.button:hover,
		.button:focus {
			background: #f1f1f1;
		}

		.button:focus {
			background: #f3f5f6;
			border-color: #007cba;
			-webkit-box-shadow: 0 0 0 1px #007cba;
			box-shadow: 0 0 0 1px #007cba;
			color: #016087;
			outline: 2px solid transparent;
			outline-offset: 0;
		}

		.button:active {
			background: #f3f5f6;
			border-color: #7e8993;
			-webkit-box-shadow: none;
			box-shadow: none;
		}

		<?php
		if ( 'rtl' === $text_direction ) {
			echo 'body { font-family: Tahoma, Arial; }';
		}
		?>
	</style>
</head>
<body id="error-page">
<?php endif; // ! did_action( 'admin_head' ) ?>
	<?php echo wp_kses_post( $message ); ?>
	<?php
	if ( $is_error_from_csm_snippet 
		&& is_user_logged_in() 
		&& current_user_can( 'manage_options' )
		) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $delayed_js_redirect_script;
	}
	?>
</body>
</html>	
	<?php
	if ( $parsed_args['exit'] ) {
		die();
	}

	} else {
	// =========================================================================================================
	// ========= If the error is not triggered by Code Snippets Manager, and $message is not an object =========
	// ========= Copy from _default_wp_die_handler() in /wp-includes/functions.php =============================
	// =========================================================================================================
	list( $message, $title, $parsed_args ) = _wp_die_process_input( $message, $title, $args );

	if ( is_string( $message ) ) {
		if ( ! empty( $parsed_args['additional_errors'] ) ) {
			$message = array_merge(
				array( $message ),
				wp_list_pluck( $parsed_args['additional_errors'], 'message' )
			);
			$message = "<ul>\n\t\t<li>" . implode( "</li>\n\t\t<li>", $message ) . "</li>\n\t</ul>";
		}

		$message = sprintf(
			'<div class="wp-die-message">%s</div>',
			$message
		);
	}

	$have_gettext = function_exists( '__' );

	if ( ! empty( $parsed_args['link_url'] ) && ! empty( $parsed_args['link_text'] ) ) {
		$link_url = $parsed_args['link_url'];
		if ( function_exists( 'esc_url' ) ) {
			$link_url = esc_url( $link_url );
		}
		$link_text = $parsed_args['link_text'];
		$message  .= "\n<p><a href='{$link_url}'>{$link_text}</a></p>";
	}

	if ( isset( $parsed_args['back_link'] ) && $parsed_args['back_link'] ) {
		$back_text = $have_gettext ? __( '&laquo; Back' ) : '&laquo; Back';
		$message  .= "\n<p><a href='javascript:history.back()'>$back_text</a></p>";
	}

	if ( ! did_action( 'admin_head' ) ) :
		if ( ! headers_sent() ) {
			header( "Content-Type: text/html; charset={$parsed_args['charset']}" );
			status_header( $parsed_args['response'] );
			nocache_headers();
		}

		$text_direction = $parsed_args['text_direction'];
		$dir_attr       = "dir='$text_direction'";

		/*
		 * If `text_direction` was not explicitly passed,
		 * use get_language_attributes() if available.
		 */
		if ( empty( $args['text_direction'] )
			&& function_exists( 'language_attributes' ) && function_exists( 'is_rtl' )
		) {
			$dir_attr = get_language_attributes();
		}
		?>
<!DOCTYPE html>
<html <?php echo $dir_attr; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $parsed_args['charset']; ?>" />
	<meta name="viewport" content="width=device-width">
		<?php
		if ( function_exists( 'wp_robots' ) && function_exists( 'wp_robots_no_robots' ) && function_exists( 'add_filter' ) ) {
			add_filter( 'wp_robots', 'wp_robots_no_robots' );
			wp_robots();
		}
		?>
	<title><?php echo $title; ?></title>
	<style type="text/css">
		html {
			background: #f1f1f1;
		}
		body {
			background: #fff;
			border: 1px solid #ccd0d4;
			color: #444;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			margin: 2em auto;
			padding: 1em 2em;
			max-width: 700px;
			-webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
			box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
		}
		h1 {
			border-bottom: 1px solid #dadada;
			clear: both;
			color: #666;
			font-size: 24px;
			margin: 30px 0 0 0;
			padding: 0;
			padding-bottom: 7px;
		}
		#error-page {
			margin-top: 50px;
		}
		#error-page p,
		#error-page .wp-die-message {
			font-size: 14px;
			line-height: 1.5;
			margin: 25px 0 20px;
		}
		#error-page code {
			font-family: Consolas, Monaco, monospace;
		}
		ul li {
			margin-bottom: 10px;
			font-size: 14px ;
		}
		a {
			color: #2271b1;
		}
		a:hover,
		a:active {
			color: #135e96;
		}
		a:focus {
			color: #043959;
			box-shadow: 0 0 0 2px #2271b1;
			outline: 2px solid transparent;
		}
		.button {
			background: #f3f5f6;
			border: 1px solid #016087;
			color: #016087;
			display: inline-block;
			text-decoration: none;
			font-size: 13px;
			line-height: 2;
			height: 28px;
			margin: 0;
			padding: 0 10px 1px;
			cursor: pointer;
			-webkit-border-radius: 3px;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			-webkit-box-sizing: border-box;
			-moz-box-sizing:    border-box;
			box-sizing:         border-box;

			vertical-align: top;
		}

		.button.button-large {
			line-height: 2.30769231;
			min-height: 32px;
			padding: 0 12px;
		}

		.button:hover,
		.button:focus {
			background: #f1f1f1;
		}

		.button:focus {
			background: #f3f5f6;
			border-color: #007cba;
			-webkit-box-shadow: 0 0 0 1px #007cba;
			box-shadow: 0 0 0 1px #007cba;
			color: #016087;
			outline: 2px solid transparent;
			outline-offset: 0;
		}

		.button:active {
			background: #f3f5f6;
			border-color: #7e8993;
			-webkit-box-shadow: none;
			box-shadow: none;
		}

		<?php
		if ( 'rtl' === $text_direction ) {
			echo 'body { font-family: Tahoma, Arial; }';
		}
		?>
	</style>
</head>
<body id="error-page">
<?php endif; // ! did_action( 'admin_head' ) ?>
	<?php echo $message; ?>
</body>
</html>
	<?php
	if ( $parsed_args['exit'] ) {
		die();
	}
			
	}
}

/**
 * Enqueue ALTCHA scripts and styles on login, registration and password reset forms/pages
 * 
 * @since 7.7.0
 */
function asenha_login_altcha_scripts__premium_only() {
	$options = get_option( 'admin_site_enhancements', array() );
	$captcha_wp_locations = ( array_key_exists( 'captcha_wp_locations', $options ) ) ? $options['captcha_wp_locations'] : array();

    if ( in_array( 'wp_login_form', $captcha_wp_locations ) 
		|| in_array( 'wp_password_reset_form', $captcha_wp_locations ) 
		|| in_array( 'wp_registration_form', $captcha_wp_locations ) 
	) {
		asenha_register_altcha_assets__premium_only();
		asenha_enqueue_altcha_assets__premium_only();
    }
}

/**
 * Enqueue ALTCHA scripts and styles on the frontend, e.g. on posts with commenting enabled
 * 
 * @since 7.7.0
 */
function asenha_frontend_altcha_scripts__premium_only() {
	global $post;
	$disable_comments = new ASENHA\Classes\Disable_Comments;

	$options = get_option( 'admin_site_enhancements', array() );
	$captcha_wp_locations = ( array_key_exists( 'captcha_wp_locations', $options ) ) ? $options['captcha_wp_locations'] : array();

	if ( is_object( $post ) && property_exists( $post, 'comment_status' ) ) {

		if ( property_exists( $post, 'post_type' ) ) {
			$is_commenting_disabled_for_post_type = $disable_comments->is_commenting_disabled_for_post_type( $post->post_type );
		} else {
			$is_commenting_disabled_for_post_type = false;	
		}

		// Enqueue on posts with commenting enabled
		if ( 'open' == $post->comment_status 
			&& ! $is_commenting_disabled_for_post_type
			&&  in_array( 'wp_comment_form', $captcha_wp_locations )
		) {
			asenha_register_altcha_assets__premium_only();
			asenha_enqueue_altcha_assets__premium_only();
		}
	}
	
    // When WooCommerce is active
	if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
		$captcha_woo_locations = ( array_key_exists( 'captcha_woo_locations', $options ) ) ? $options['captcha_woo_locations'] : array();

		// When in Account page, including when logged-out, showing login / registration / lost password forms.
		if ( is_account_page() ) {
		    if ( in_array( 'woo_login_form', $captcha_woo_locations ) 
				|| in_array( 'woo_password_reset_form', $captcha_woo_locations ) 
				|| in_array( 'woo_registration_form', $captcha_woo_locations ) 
			) {
				asenha_register_altcha_assets__premium_only();
				asenha_enqueue_altcha_assets__premium_only();
			}
		}

		// When in checkout page
		if ( is_checkout() ) {
		    if ( in_array( 'woo_login_form', $captcha_woo_locations ) ) {
				asenha_register_altcha_assets__premium_only();
				asenha_enqueue_altcha_assets__premium_only();
			}
		}
	}
}

/**
 * Register ALTCHA scripts and styles
 * 
 * @since 7.7.0
 */
function asenha_register_altcha_assets__premium_only() {
	wp_register_style( 'asenha-altcha-main', ASENHA_URL . 'assets/premium/css/captcha/altcha/altcha.css', array(), ASENHA_VERSION );
	// wp_enqueue_script( 'asenha-altcha-main', ASENHA_URL . 'assets/premium/js/captcha/altcha/altcha.js', array(), ASENHA_VERSION, false  );
	wp_register_script( 'asenha-altcha-main', ASENHA_URL . 'assets/premium/js/captcha/altcha/altcha.min.js', array(), ASENHA_VERSION, false  );
	wp_register_script( 'asenha-altcha-scripts', ASENHA_URL . 'assets/premium/js/captcha/altcha/script.js', array(), ASENHA_VERSION, false  );
}

/**
 * Enqueue ALTCHA scripts and styles
 * 
 * @since 7.7.0
 */
function asenha_enqueue_altcha_assets__premium_only() {
	wp_enqueue_style( 'asenha-altcha-main' );
	// wp_enqueue_script( 'asenha-altcha-main' );
	wp_enqueue_script( 'asenha-altcha-main' );
	wp_enqueue_script( 'asenha-altcha-scripts' );
}

/**
 * Enqueue Google reCAPTCHA scripts and styles on login, registration and password reset forms/pages
 * 
 * @since 7.7.0
 */
function asenha_login_recaptcha_scripts__premium_only() {
	$options = get_option( 'admin_site_enhancements', array() );
	$captcha_wp_locations = ( array_key_exists( 'captcha_wp_locations', $options ) ) ? $options['captcha_wp_locations'] : array();
    $recaptcha_type = isset( $options['recaptcha_types'] ) ? $options['recaptcha_types'] : 'v2_checkbox';

    if ( in_array( 'wp_login_form', $captcha_wp_locations ) 
		|| in_array( 'wp_password_reset_form', $captcha_wp_locations ) 
		|| in_array( 'wp_registration_form', $captcha_wp_locations ) 
	) {
		// Enqueue scripts and styles for reCAPTCHA v2 "I'm not a robot" checbox
		// v3 scripts/styles is inserted inline via CAPTCHA_Protection_reCAPTCHA->get_recaptcha_html()
		if ( in_array( $recaptcha_type, array( 'v2_checkbox' ) ) ) {
			asenha_register_recaptcha_assets__premium_only();
			asenha_enqueue_recaptcha_assets__premium_only();		
		}
    }
}

/**
 * Enqueue Google reCAPTCHA scripts and styles on the frontend, e.g. on posts with commenting enabled
 * 
 * @since 7.7.0
 */
function asenha_frontend_recaptcha_scripts__premium_only() {
	global $post;
	$disable_comments = new ASENHA\Classes\Disable_Comments;

	$options = get_option( 'admin_site_enhancements', array() );
	$captcha_wp_locations = ( array_key_exists( 'captcha_wp_locations', $options ) ) ? $options['captcha_wp_locations'] : array();
    $recaptcha_type = isset( $options['recaptcha_types'] ) ? $options['recaptcha_types'] : 'v2_checkbox';

	if ( is_object( $post ) && property_exists( $post, 'comment_status' ) ) {
		if ( property_exists( $post, 'post_type' ) ) {
			$is_commenting_disabled_for_post_type = $disable_comments->is_commenting_disabled_for_post_type( $post->post_type );
		} else {
			$is_commenting_disabled_for_post_type = false;	
		}

		// Enqueue on posts with commenting enabled
		if ( 'open' == $post->comment_status 
			&& ! $is_commenting_disabled_for_post_type
			&&  in_array( 'wp_comment_form', $captcha_wp_locations )
		) {
			// Enqueue scripts and styles for reCAPTCHA v2 "I'm not a robot" checbox
			// v3 scripts/styles is inserted inline via CAPTCHA_Protection_reCAPTCHA->get_recaptcha_html()
			if ( in_array( $recaptcha_type, array( 'v2_checkbox' ) ) ) {
				asenha_register_recaptcha_assets__premium_only();
				asenha_enqueue_recaptcha_assets__premium_only();		
			}
		}		
	}

    // When WooCommerce is active
	if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
		$captcha_woo_locations = ( array_key_exists( 'captcha_woo_locations', $options ) ) ? $options['captcha_woo_locations'] : array();

		// When in Account page, including when logged-out, showing login / registration / lost password forms.
		if ( is_account_page()) {
		    if ( in_array( 'woo_login_form', $captcha_woo_locations ) 
				|| in_array( 'woo_password_reset_form', $captcha_woo_locations ) 
				|| in_array( 'woo_registration_form', $captcha_woo_locations ) 
			) {
				asenha_register_recaptcha_assets__premium_only();
				asenha_enqueue_recaptcha_assets__premium_only();
			}
		}

		// When in checkout page
		if ( is_checkout() ) {
		    if ( in_array( 'woo_login_form', $captcha_woo_locations ) ) {
				asenha_register_recaptcha_assets__premium_only();
				asenha_enqueue_recaptcha_assets__premium_only();
			}
		}
	}
}

/**
 * Register Google reCAPTCHA scripts and styles
 * 
 * @since 7.7.0
 */
function asenha_register_recaptcha_assets__premium_only() {
	$src = 'https://www.google.com/recaptcha/api.js';
    wp_enqueue_script( 'asenha-recaptcha', $src, array(), ASENHA_VERSION, true );
	// wp_enqueue_script( 'asenha-recaptcha-helper', ASENHA_URL . 'assets/premium/js/captcha/recaptcha/helper.js', array(), ASENHA_VERSION, false );
    wp_enqueue_style( 'asenha-recaptcha', ASENHA_URL . 'assets/premium/css/captcha/recaptcha/recaptcha.css', array(), ASENHA_VERSION );
}

/**
 * Enqueue Google reCAPTCHA scripts and styles
 * 
 * @since 7.7.0
 */
function asenha_enqueue_recaptcha_assets__premium_only() {
	$src = 'https://www.google.com/recaptcha/api.js';
    wp_enqueue_script( 'asenha-recaptcha' );
	// wp_enqueue_script( 'asenha-recaptcha-helper' );
    wp_enqueue_style( 'asenha-recaptcha' );
}
/**
 * Enqueue Cloudflare Turnstile scripts and styles on login, registration and password reset forms/pages
 * 
 * @since 7.7.0
 */
function asenha_login_turnstile_scripts__premium_only() {
	$options = get_option( 'admin_site_enhancements', array() );
	$captcha_wp_locations = ( array_key_exists( 'captcha_wp_locations', $options ) ) ? $options['captcha_wp_locations'] : array();
    $recaptcha_type = isset( $options['recaptcha_types'] ) ? $options['recaptcha_types'] : 'v2_checkbox';

    if ( in_array( 'wp_login_form', $captcha_wp_locations ) 
		|| in_array( 'wp_password_reset_form', $captcha_wp_locations ) 
		|| in_array( 'wp_registration_form', $captcha_wp_locations ) 
	) {
		asenha_register_turnstile_assets__premium_only();
		asenha_enqueue_turnstile_assets__premium_only();		
    }	
}

/**
 * Enqueue Cloudflare Turnstile scripts and styles on the frontend, e.g. on posts with commenting enabled
 * 
 * @since 7.7.0
 */
function asenha_frontend_turnstile_scripts__premium_only() {
	global $post;
	$disable_comments = new ASENHA\Classes\Disable_Comments;

	$options = get_option( 'admin_site_enhancements', array() );
	$captcha_wp_locations = ( array_key_exists( 'captcha_wp_locations', $options ) ) ? $options['captcha_wp_locations'] : array();
    $recaptcha_type = isset( $options['recaptcha_types'] ) ? $options['recaptcha_types'] : 'v2_checkbox';

	if ( is_object( $post ) && property_exists( $post, 'comment_status' ) ) {
		if ( property_exists( $post, 'post_type' ) ) {
			$is_commenting_disabled_for_post_type = $disable_comments->is_commenting_disabled_for_post_type( $post->post_type );
		} else {
			$is_commenting_disabled_for_post_type = false;	
		}

		// Enqueue on posts with commenting enabled
		if ( 'open' == $post->comment_status 
			&& ! $is_commenting_disabled_for_post_type
			&&  in_array( 'wp_comment_form', $captcha_wp_locations )
		) {
			asenha_register_turnstile_assets__premium_only();
			asenha_enqueue_turnstile_assets__premium_only();		
		}		
	}

    // When WooCommerce is active
	if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins', array() ) ) ) {
		$captcha_woo_locations = ( array_key_exists( 'captcha_woo_locations', $options ) ) ? $options['captcha_woo_locations'] : array();

		// When in Account page, including when logged-out, showing login / registration / lost password forms.
		if ( is_account_page() ) {
		    if ( in_array( 'woo_login_form', $captcha_woo_locations ) 
				|| in_array( 'woo_password_reset_form', $captcha_woo_locations ) 
				|| in_array( 'woo_registration_form', $captcha_woo_locations ) 
			) {
				asenha_register_turnstile_assets__premium_only();
				asenha_enqueue_turnstile_assets__premium_only();
			}
		}

		// When in checkout page
		if ( is_checkout() ) {
		    if ( in_array( 'woo_login_form', $captcha_woo_locations ) ) {
		    	asenha_register_turnstile_assets__premium_only();
				asenha_enqueue_turnstile_assets__premium_only();
			}
		}
	}
}

/**
 * Register Cloudflare Turnstile scripts and styles
 * 
 * @since 7.7.0
 */
function asenha_register_turnstile_assets__premium_only() {
	wp_enqueue_style( 'asenha-turnstile-main', ASENHA_URL . 'assets/premium/css/captcha/turnstile/turnstile.css', array(), ASENHA_VERSION );
	$defer = array( 'strategy' => 'defer' );
	wp_enqueue_script( 'asenha-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit', array(), null, $defer );
	/* Disable Submit Button */
	wp_enqueue_script( 'asenha-turnstile-disable-submit', ASENHA_URL . 'assets/premium/js/captcha/turnstile/disable-submit.js', '', ASENHA_VERSION, $defer);
}

/**
 * Enqueue Cloudflare Turnstile scripts and styles
 * 
 * @since 7.7.0
 */
function asenha_enqueue_turnstile_assets__premium_only() {
	wp_enqueue_style( 'asenha-turnstile-main' );
	wp_enqueue_script( 'asenha-turnstile' );
	/* Disable Submit Button */
	wp_enqueue_script( 'asenha-turnstile-disable-submit' );
}