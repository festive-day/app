<?php

/**
 * Forked from Simple Custom CSS and JS v3.44 by SilkyPress.com
 * @link https://wordpress.org/plugins/custom-css-js/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// MobileDetect v4.8.x
use Detection\Exception\MobileDetectException;
use Detection\MobileDetectStandalone;

if ( version_compare( PHP_VERSION, '8.0.0', '>=' ) ) {
	if ( ! class_exists( 'MobileDetect' ) ) {
		// MobileDetect v4.8.x
		require_once ASENHA_PATH . 'vendor/serbanghita/mobile-detect/4.8.x/standalone/autoloader.php';
		require_once ASENHA_PATH . 'vendor/serbanghita/mobile-detect/4.8.x/src/MobileDetectStandalone.php';
	}
} else {
	// MobileDetect v3.74.x
	require_once ASENHA_PATH . 'vendor/serbanghita/mobile-detect/3.74.x/MobileDetect.php';
}

if ( ! class_exists( 'Code_Snippets_Manager' ) ) :
	/**
	 * Main Code_Snippets_Manager Class
	 *
	 * @class Code_Snippets_Manager
	 */
	final class Code_Snippets_Manager {

		// public $search_tree         = false; // Legacy - used up to ASE Pro v7.6.3. No longer referenced anywhere on this file.
		public $snippets_tree       = false; // Current - since ASE Pro v7.6.4
		protected static $_instance = null;
		private $settings           = array();

		/**
		 * Main Code_Snippets_Manager Instance
		 *
		 * Ensures only one instance of Code_Snippets_Manager is loaded or can be loaded
		 *
		 * @static
		 * @return Code_Snippets_Manager - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'An error has occurred. Please reload the page and try again.' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'An error has occurred. Please reload the page and try again.' ), '1.0' );
		}

		/**
		 * Code_Snippets_Manager Constructor
		 *
		 * @access public
		 */
		public function __construct() {

			$options = get_option( ASENHA_SLUG_U, array() );
			$extra_options = get_option( ASENHA_SLUG_U . '_extra', array() );

			include_once 'includes/admin-install.php';
			// register_activation_hook( __FILE__, array( 'Code_Snippets_Manager_Install', 'install' ) );
			add_action( 'admin_init', array( 'Code_Snippets_Manager_Install', 'create_roles' ), PHP_INT_MAX );
			add_action( 'init', array( 'Code_Snippets_Manager_Install', 'register_post_type' ), 1 );
			add_action( 'init', array( 'Code_Snippets_Manager_Install', 'register_category' ), 1 );
			add_action( 'init', array( $this, 'register_php_snippet_shortcode' ) );
			add_action( 'wp_ajax_execute_php_snippet_on_demand', array( $this, 'execute_php_snippet_on_demand' ) );

			// Make sure we only flush rewrite rules once as it's an expensive operation
			$flush_rewrite_rules_needed = $options['code_snippets_manager_flush_rewrite_rules_needed'];

			if ( $flush_rewrite_rules_needed ) {
				flush_rewrite_rules();
				$options['code_snippets_manager_flush_rewrite_rules_needed'] = false;
				update_option( ASENHA_SLUG_U, $options, true );
			}

			$this->set_constants();

			if ( is_admin() ) {
				include_once 'includes/admin-screens.php';
				include_once 'includes/admin-config.php';
			}
			
			// Upgrade to or use new snippets data store introduced in ASE Pro v7.6.4
			if ( ! isset( $extra_options['code_snippets'] ) ) {
				$this->upgrade_snippets_data_store( $extra_options );
			} else {
				$this->snippets_tree = $extra_options['code_snippets'];
			}

			$this->settings = isset( $extra_options['code_snippets_manager_settings'] ) ? $extra_options['code_snippets_manager_settings'] : array();

			if ( ! isset( $this->settings['remove_comments'] ) ) {
				$this->settings['remove_comments'] = false;
			}

			if ( ! $this->snippets_tree || count( $this->snippets_tree ) == 0 ) {
				return false;
			}

			if ( is_null( self::$_instance ) ) {

				// Execute CSS, JS, HTML snippets
				$this->print_code_actions();
				
				// Execute PHP snippets.
				$php_snippets = ( isset( $this->snippets_tree['php'] ) ) ? $this->snippets_tree['php'] : array();

				if ( ! empty( $php_snippets ) ) {
					foreach ( $this->snippets_tree['php'] as $snippet_id => $snippet_info ) {
						$execution_method = isset( $snippet_info['execution_method'] ) ? $snippet_info['execution_method'] : 'on_page_load';
						$execution_location_type = isset( $snippet_info['execution_location_type'] ) ? $snippet_info['execution_location_type'] : 'hook';
						$execution_location = isset( $snippet_info['execution_location'] ) ? $snippet_info['execution_location'] : 'plugins_loaded';
						$execution_location_details = isset( $snippet_info['execution_location_details'] ) ? $snippet_info['execution_location_details'] : 'everywhere';
						
						$should_execute = false;
						
						switch ( $execution_method ) {
							case 'on_page_load';
								switch ( $execution_location_type ) {
									case 'hook';
										switch ( $execution_location_details ) {
											case 'everywhere';
												if ( is_admin() ) {
													$should_execute = true;
												} else {
													$should_execute = true;
												}
												break;
											
											case 'admin';
												if ( is_admin() ) {
													$should_execute = true;
												} else {
													$should_execute = false;
												}
												break;
											
											case 'frontend';
												if ( is_admin() ) {
													$should_execute = false;
												} else {
													$should_execute = true;
												}
												break;
										}
										break;
										
									case 'shortcode';
										// Do nothing here, as the PHP snippet will be executed via the [php_snippet] shortcode on the page it is placed on
										break;
								}							
								break;
								
							case 'on_demand';
								// Do nothing here, as the PHP snippet will be executed on demand
								break;

							case 'via_secure_url';
								$secure_token_in_url = isset( $_GET['codex_token'] ) ? sanitize_text_field( $_GET['codex_token'] ) : '';
								if ( $secure_token_in_url === $snippet_info['secure_url_token'] ) {
									$should_execute = true;
								}
								break;
						}
						
						if ( $should_execute ) {
							// Use a closure here so we can pass an argument. Ref: https://stackoverflow.com/a/34168439
							add_action( $execution_location, function() use ( $snippet_info ) { $this->execute_php_snippet( $snippet_info ); } );
						}
					}					
				}

				if ( isset ( $this->snippets_tree['jquery'] ) && true === $this->snippets_tree['jquery'] ) {
					add_action( 'wp_enqueue_scripts', 'Code_Snippets_Manager::wp_enqueue_scripts' );
				}

			}
			
		}

	    /**
	     * Register the shortcode to execute PHP snippet
	     * 
	     * @since 7.6.5
	     */
	    public function register_php_snippet_shortcode() {
	        add_shortcode( 'php_snippet', array( $this, 'php_snippet_shortcode_handler' ) );
	    }
	    
	    /**
	     * Execute the PHP snippet
	     * 
	     * @since 7.6.6
	     */
	    public function php_snippet_shortcode_handler( $atts ) {
	        $atts = shortcode_atts( array(
	            'id'    => '',
	        ), $atts );
	        
			$post_id = absint( $atts['id'] );

			$filename = $post_id . '.php';
			$upload_dir = wp_upload_dir();
			$file_path = $upload_dir['basedir'] . '/code-snippets/' . $filename;

			// Check if safe mode is enabled
			$is_safe_mode_is_enabled = $this->is_safe_mode_enabled();

			// Check if snippet is active
			$is_snippet_active = $this->is_snippet_active( $post_id );
	        
			// Safe mode is not enabled
			if ( ! $is_safe_mode_is_enabled ) {
				// PHP snippet is active
				if ( $is_snippet_active ) {
					$snippet_info = $this->snippets_tree['php'][$post_id];
					
					return $this->execute_php_snippet( $snippet_info );
				}
			}

	    }

		/**
		 * Activate/deactivate a code
		 *
		 * @return void
		 */
		public function execute_php_snippet_on_demand() {
			if ( ! isset( $_GET['snippet_id'] ) ) {
				die();
			}

			$snippet_id = absint( $_GET['snippet_id'] );
			$response = 'error';
			
			if ( check_admin_referer( 'csm-execute-php-snippet-' . $snippet_id ) ) {

				if ( 'asenha_code_snippet' === get_post_type( $snippet_id ) ) {

					// Check if safe mode is enabled
					$is_safe_mode_is_enabled = $this->is_safe_mode_enabled();

					// Check if snippet is active
					$is_snippet_active = $this->is_snippet_active( $snippet_id );
			        
					// Safe mode is not enabled
					if ( ! $is_safe_mode_is_enabled ) {
						// PHP snippet is active
						if ( $is_snippet_active ) {
							$snippet_info = $this->snippets_tree['php'][$snippet_id];
							
							$this->execute_php_snippet( $snippet_info );
						}
					}

					$response = 'success';
				}
			}
			echo $response;

			die();
		}
				
		/**
		 * Upgrade snippets data store to new option node at 
		 * from admin_site_enhancements_extra['code_snippets_manager_tree'] in version up to v7.6.3
		 * to admin_site_enhancements_extra['code_snippets'] in v7.6.4 onward
		 * 
		 * @since 7.6.4
		 */
		public function upgrade_snippets_data_store( $extra_options ) {
			$legacy_code_snippets = isset( $extra_options['code_snippets_manager_tree'] ) ? $extra_options['code_snippets_manager_tree'] : array();
			$current_code_snippets = array();
			
			foreach ( $legacy_code_snippets as $snippet_args => $snippet_filenames ) {

				// CSS snippet
				if ( false !== strpos( $snippet_args, '-css-' ) ) {
					$snippet_args_parts = explode( '-', $snippet_args );
					$load_type = $snippet_args_parts[3]; // external | internal
					$position_on_page = $snippet_args_parts[2]; // header | footer
					$location = $snippet_args_parts[0]; // array of frontend &| admin &| login
					$priority = 10;
					$execution_location = '';

					foreach ( $snippet_filenames as $snippet_filename ) {
						$snippet_filename_parts = explode( '.', $snippet_filename );

						$snippet_id = $snippet_filename_parts[0]; // equals to post ID, e.g. 6123
						$snippet_title = get_the_title( $snippet_id );
						
						$current_code_snippets['css'][$snippet_id]['id'] = $snippet_id; // e.g. 6123
						$current_code_snippets['css'][$snippet_id]['filename'] = $snippet_filename; // e.g. 6123.css or 6123.css?v=1234
						$current_code_snippets['css'][$snippet_id]['title'] = $snippet_title; // e.g. Custom colors for wp-admin
						$current_code_snippets['css'][$snippet_id]['load_type'] = $load_type; // external (as a file) | internal (inline)
						$current_code_snippets['css'][$snippet_id]['position_on_page'] = $position_on_page; // header | footer
						$current_code_snippets['css'][$snippet_id]['location'][] = $location; // array of frontend &| admin &| login
						// $current_code_snippets['css'][$snippet_id]['location_specifics'] = $location_specifics;
						$current_code_snippets['css'][$snippet_id]['execution_location'] = $execution_location; // between 1 to 9999
						$current_code_snippets['css'][$snippet_id]['priority'] = $priority; // between 1 to 9999
						// $current_code_snippets['css'][$snippet_id]['device'] = $device; // all | desktop | mobile
						// $current_code_snippets['css'][$snippet_id]['is_scheduled'] = $is_scheduled; // boolean true | false
						// $current_code_snippets['css'][$snippet_id]['schedule_start'] = $schedule_start;
						// $current_code_snippets['css'][$snippet_id]['schedule_end'] = $schedule_end;
					}
				}

				// JS snippet
				if ( false !== strpos( $snippet_args, '-js-' ) ) {
					$snippet_args_parts = explode( '-', $snippet_args );
					$load_type = $snippet_args_parts[3]; // external | internal
					$position_on_page = $snippet_args_parts[2]; // header | footer
					$location = $snippet_args_parts[0]; // array of frontend &| admin &| login
					$priority = 10;
					$execution_location = '';

					foreach ( $snippet_filenames as $snippet_filename ) {
						$snippet_filename_parts = explode( '.', $snippet_filename );
						$snippet_id = $snippet_filename_parts[0]; // equals to post ID, e.g. 6123
						$snippet_title = get_the_title( $snippet_id );

						$current_code_snippets['js'][$snippet_id]['id'] = $snippet_id; // e.g. 6123
						$current_code_snippets['js'][$snippet_id]['filename'] = $snippet_filename; // e.g. 6123.js
						$current_code_snippets['js'][$snippet_id]['title'] = $snippet_title; // e.g. Add custom element in admin bar
						$current_code_snippets['js'][$snippet_id]['load_type'] = $load_type; // external (as a file) | internal (inline)
						$current_code_snippets['js'][$snippet_id]['position_on_page'] = $position_on_page; // header | footer
						$current_code_snippets['js'][$snippet_id]['location'][] = $location; // array of frontend &| admin &| login
						// $current_code_snippets['js'][$snippet_id]['location_specifics'] = $location_specifics;
						$current_code_snippets['js'][$snippet_id]['execution_location'] = $execution_location; // between 1 to 9999
						$current_code_snippets['js'][$snippet_id]['priority'] = $priority; // between 1 to 9999
						// $current_code_snippets['js'][$snippet_id]['device'] = $device; // all | desktop | mobile
						// $current_code_snippets['js'][$snippet_id]['is_scheduled'] = $is_scheduled; // boolean true | false
						// $current_code_snippets['js'][$snippet_id]['schedule_start'] = $schedule_start;
						// $current_code_snippets['js'][$snippet_id]['schedule_end'] = $schedule_end;
					}
				}

				// HTML snippet
				if ( false !== strpos( $snippet_args, '-html-' ) ) {
					$snippet_args_parts = explode( '-', $snippet_args );
					$position_on_page = $snippet_args_parts[2]; // header | body_open | footer
					$location = $snippet_args_parts[0]; // array of frontend &| admin
					$priority = 10;
					$execution_location = '';

					foreach ( $snippet_filenames as $snippet_filename ) {
						$snippet_filename_parts = explode( '.', $snippet_filename );
						$snippet_id = $snippet_filename_parts[0]; // equals to post ID, e.g. 6123
						$snippet_title = get_the_title( $snippet_id );

						$current_code_snippets['html'][$snippet_id]['id'] = $snippet_id; // e.g. 6123
						$current_code_snippets['html'][$snippet_id]['filename'] = $snippet_filename; // e.g. 6123.html
						$current_code_snippets['html'][$snippet_id]['title'] = $snippet_title; // e.g. Add custom element in admin bar
						// load_type is 'both' by default for HTMl snippet
						$current_code_snippets['html'][$snippet_id]['position_on_page'] = $position_on_page; // header | body_open | footer
						$current_code_snippets['html'][$snippet_id]['location'][] = $location; // array of frontend &| admin
						// $current_code_snippets['html'][$snippet_id]['location_specifics'] = $location_specifics;
						$current_code_snippets['html'][$snippet_id]['execution_location'] = $execution_location; // between 1 to 9999
						$current_code_snippets['html'][$snippet_id]['priority'] = $priority; // between 1 to 9999
						// $current_code_snippets['html'][$snippet_id]['device'] = $device; // all | desktop | mobile
						// $current_code_snippets['html'][$snippet_id]['is_scheduled'] = $is_scheduled; // boolean true | false
						// $current_code_snippets['html'][$snippet_id]['schedule_start'] = $schedule_start;
						// $current_code_snippets['html'][$snippet_id]['schedule_end'] = $schedule_end;
					}
				}
				
				// PHP snippet
				if ( false !== strpos( $snippet_args, '-php-' ) ) {
					$location_type = 'global';
					$location = 'sitewide';
					$execution_location = 'plugins_loaded';
					$priority = 10;
					
					foreach ( $snippet_filenames as $snippet_filename ) {
						$snippet_filename_parts = explode( '.', $snippet_filename );
						$snippet_id = $snippet_filename_parts[0]; // equals to post ID, e.g. 6123
						$snippet_title = get_the_title( $snippet_id );

						$current_code_snippets['php'][$snippet_id]['id'] = $snippet_id; // e.g. 6123
						$current_code_snippets['php'][$snippet_id]['filename'] = $snippet_filename; // e.g. 6123.php
						$current_code_snippets['php'][$snippet_id]['title'] = $snippet_title; // e.g. Add custom element in admin bar
						$current_code_snippets['php'][$snippet_id]['location_type'] = $location_type; // global | page-specific
						$current_code_snippets['php'][$snippet_id]['location'][] = $location; // sitewide | admin | frontend
						// $current_code_snippets['php'][$snippet_id]['location_specifics'] = $location_specifics;
						// $current_code_snippets['php'][$snippet_id]['execution_method'] = $execution_method; // on_page_load | on_demand
						$current_code_snippets['php'][$snippet_id]['execution_location'] = $execution_location; // plugins_loaded | after_setup_theme | etc
						$current_code_snippets['php'][$snippet_id]['priority'] = $priority; // between 1 to 9999
						// $current_code_snippets['php'][$snippet_id]['device'] = $device; // all | desktop | mobile
						// $current_code_snippets['php'][$snippet_id]['is_scheduled'] = $is_scheduled; // boolean true | false
						// $current_code_snippets['php'][$snippet_id]['schedule_start'] = $schedule_start;
						// $current_code_snippets['php'][$snippet_id]['schedule_end'] = $schedule_end;
					}
				}

				// CSS snippet
				if ( false !== strpos( $snippet_args, 'jquery' ) ) {
					$use_jquery = $snippet_filenames; // true or false;
					$current_code_snippets['jquery'] = $use_jquery;
				}
				
			}
			$extra_options['code_snippets'] = $current_code_snippets;
			update_option( ASENHA_SLUG_U . '_extra', $extra_options );
			
			sleep( 2 );

			unset( $extra_options['code_snippets_manager_tree'] );
			update_option( ASENHA_SLUG_U . '_extra', $extra_options );

			$this->snippets_tree = $current_code_snippets;			
		}

		/**
		 * Add the appropriate wp actions
		 */
		public function print_code_actions() {

			// Current - since ASE Pro 7.6.4
			foreach ( $this->snippets_tree as $snippet_type => $snippets ) {
				if ( in_array( $snippet_type, array( 'css', 'js', 'html' ) ) ) {
					foreach ( $snippets as $snippet ) {

						foreach( $snippet['location'] as $location ) {
							$action = '';
							
							switch ( $location ) {
								case 'frontend';
									$action = 'wp_';
									break;

								case 'admin';
									$action = 'admin_';
									break;

								case 'login';
									$action = 'login_';
									break;
							}

							switch ( $snippet['position_on_page'] ) {
								case 'header';
									$action .= 'head';
									break;

								case 'body_open';
									$action .= 'body_open';
									break;

								case 'footer';
									$action .= 'footer';
									break;
							}
							
							$priority = ( 'wp_footer' === $action ) ? 40 : 10;
							
							add_action( $action, array( $this, 'print_' . $snippet_type . '_' . $snippet['id'] ), $priority );
						}

					}
				}
			}
				
		}

		/**
		 * Print the custom code.
		 */
		public function __call( $function, $args ) {

			if ( strpos( $function, 'print_' ) === false ) {
				return false;
			}

			$function = str_replace( 'print_', '', $function );
				
			// Current - since ASE Pro 7.6.4
			$function_parts = explode( '_', $function );
			$snippet_type = $function_parts[0]; // css | js | html | php
			$snippet_id = $function_parts[1]; // e.g. 6123
			
			$snippets_tree = $this->snippets_tree;

			if ( ! isset( $snippets_tree[$snippet_type][$snippet_id] ) ) {
				return false;
			}

			$snippet_options = $snippets_tree[$snippet_type][$snippet_id];
			// if ( '9750' == $snippet_id ) {
			// 	vi( $snippet_options );				
			// }

			// Get current URL
			
			// Calling Common_Methods here may trigger fatal error in certain PHP snippets when loaded in the frontend
			// $common_methods = new ASENHA\Classes\Common_Methods;
			// $current_url = $common_methods->get_current_url(); // e.g. https://www.site.com/some-page

			// Let's use the raw method here directly
			$url = ( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field( $_SERVER['HTTP_HOST'] ) . sanitize_text_field( $_SERVER['REQUEST_URI'] );
			$url_parts = explode( '?', $url, 2 ); // limit to max of 2 elements with last element containing the rest of the string
			if ( isset( $url_parts[0] ) ) {
				$output = trim( $url_parts[0], '/' );
			}

			$current_url = $output ? urldecode( $output ) : '/';
			
			// Check for frontend conditionals. Skip admin pages and the login page.
			if ( ! is_admin() && false === strpos( $current_url, 'wp-login.php' ) ) {
				if ( ! $this->is_conditionals_fulfilled( $snippet_id, $snippet_type, $snippet_options ) ) {
					return false; // Do not proceed / do not load CSS/JS/HTML snippet when conditionals are not met
				}				
			}
			
			$where = isset( $snippets_tree[$snippet_type][$snippet_id]['load_type'] ) ? $snippets_tree[$snippet_type][$snippet_id]['load_type'] : 'internal'; // external | internal
			$type = $snippet_type; // css | js | html
			$tag   = array( 'css' => 'style', 'js' => 'script' );

			$type_attr = ( 'js' === $type && ! current_theme_supports( 'html5', 'script' ) ) ? ' type="text/javascript"' : '';
			$type_attr = ( 'css' === $type && ! current_theme_supports( 'html5', 'style' ) ) ? ' type="text/css"' : $type_attr;

			$upload_url = str_replace( array( 'https://', 'http://' ), '//', CSM_UPLOAD_URL ) . '/';

			if ( 'internal' === $where ) {

				$before = $this->settings['remove_comments'] ? '' : '<!-- start Code Snippets Manager -->' . PHP_EOL;
				$after  = $this->settings['remove_comments'] ? '' : '<!-- end Code Snippets Manager -->' . PHP_EOL;

				if ( 'css' === $type || 'js' === $type ) {
					$before .= '<' . $tag[ $type ] . ' ' . $type_attr . '>' . PHP_EOL;
					$after   = '</' . $tag[ $type ] . '>' . PHP_EOL . $after;
				}

			}

			$_filename = $snippets_tree[$snippet_type][$snippet_id]['filename'];

			if ( 'internal' ===  $where && ( strstr( $_filename, 'css' ) || strstr( $_filename, 'js' ) ) ) {
				if ( $this->settings['remove_comments'] || empty( $type_attr ) ) {
					$code_snippet = @file_get_contents( CSM_UPLOAD_DIR . '/' . $_filename );
					if ( $this->settings['remove_comments'] ) {
							$code_snippet = str_replace( array( 
									'<!-- start Code Snippets Manager -->' . PHP_EOL, 
									'<!-- end Code Snippets Manager -->' . PHP_EOL 
							), '', $code_snippet );
					}
					if ( empty( $type_attr ) ) {
						$code_snippet = str_replace( array( ' type="text/javascript"', ' type="text/css"' ), '', $code_snippet );
					}
					echo $code_snippet;
				} else {
					echo @file_get_contents( CSM_UPLOAD_DIR . '/' . $_filename );
				}
			}

			if ( 'internal' === $where && ! strstr( $_filename, 'css' ) && ! strstr( $_filename, 'js' ) ) {
				$post = get_post( $_filename );
				echo $before . $post->post_content . $after;
			}

			if ( 'external' === $where && 'js' === $type ) {
				echo PHP_EOL . "<script{$type_attr} src='{$upload_url}{$_filename}'></script>" . PHP_EOL;
			}

			if ( 'external' === $where && 'css' === $type ) {
				$shortfilename = preg_replace( '@\.css\?v=.*$@', '', $_filename );
				echo PHP_EOL . "<link rel='stylesheet' id='{$shortfilename}-css' href='{$upload_url}{$_filename}'{$type_attr} media='all' />" . PHP_EOL;
			}

			if ( 'html' === $type ) {
				$post_id = str_replace( '.html', '', $_filename );
				$post      = get_post( $post_id );
				echo $post->post_content . PHP_EOL;
			}
				
		}
		
		/**
		 * Frontend conditionals check
		 * 
		 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/includes/class.execute.snippet.php#L672
		 * @since 7.6.7

		 * @return boolean
		 */
		public function is_conditionals_fulfilled( $snippet_id, $snippet_type, $snippet_options ) {
			// if ( '9750' == $snippet_id ) {
			// 	vi( $snippet_options );
			// }
			$result = true; // By default, return true, i.e. conditionals is fulfilled
			$conditionals = isset( $snippet_options['conditionals'] ) ? $snippet_options['conditionals'] : array();
			// if ( '9750' == $snippet_id ) {
			// 	vi( $conditionals );
			// }

			// Check if we have conditionals to work with
			if ( ! ( empty( $conditionals ) || isset( $conditionals[0] ) && empty( $conditionals[0] ) ) ) {
				foreach ( $conditionals[0] as $filter ) {
					// if ( '9750' == $snippet_id ) {
					// 	vi( $filter );
					// }

					// $filter here has 'conditions' and 'type' (showif | hideif)
					// There is only one $filter to iterate through

					$filter_conditions = $this->get_item_property( $filter, 'conditions' );
					// if ( '9750' == $snippet_id ) {
					// 	vi( $filter_conditions );
					// }

					if ( empty( $filter_conditions ) ) {
						continue; // skip this iteration, continue to the next one
					}
					
					// Set initial state for AND conditionals
					$and_conditions = null;
					
					// Let's go through AND scopes, i.e. groups of OR conditionals
					foreach ( $filter_conditions as $and_scope ) {
						$and_scope_conditionals = $this->get_item_property( $and_scope, 'conditions' );
						// if ( '9750' == $snippet_id ) {
						// 	vi( $and_scope_conditionals );
						// }

						if ( empty( $and_scope_conditionals ) ) {
							continue; // skip this iteration, continue to the next one								
						}

						// Set initial state for OR conditionals within this iteraation of the AND scopes/groups
						$or_conditions = null;

						// Let's go through OR conditionals
						foreach( $and_scope_conditionals as $or_conditional ) {
							$method_name = str_replace( '-', '_', $this->get_item_property( $or_conditional, 'param' ) ); // e.g. location-post-type becomse location_post_type
							$operator    = $this->get_item_property( $or_conditional, 'operator' );
							$value       = $this->get_item_property( $or_conditional, 'value' );
							
							// Get the result of the OR condition
							if ( is_null( $or_conditions ) ) {
								$or_conditions = $this->call_method( $method_name, $operator, $value );
							} else {
								$or_conditions = $or_conditions || $this->call_method( $method_name, $operator, $value ); // Returns true if either $or_conditions or $this->call_method is true. Returns false if both are false.
							}
						}

						// Get the current result of AND condition
						if ( is_null( $and_conditions ) ) {
							$and_conditions = $or_conditions; // First $and_scope, so $and_conditions is still null. Set value as $or_conditions
						} else {
							$and_conditions = $and_conditions && $or_conditions; // Returns true only if both $and_conditions and $or_conditions are true. Otherwise, will return false.
						}
						
						$and_conditions = is_null( $and_conditions ) ? $or_conditions : $and_conditions && $or_conditions;  // For the later option, returns true only if both $and_conditions and $or_conditions are true. Otherwise, will return false.
					}
					// if ( '9750' == $snippet_id ) {
					// 	vi( $and_conditions, '', 'for page' );
					// 	vi( $or_conditions, '', 'for page'  );
					// }

					// Get the final result of the conditionals.
					$result = ( 'showif' == $this->get_item_property( $filter, 'type' ) ) ? $and_conditions : ! $and_conditions;	
				}
			}
						
			return $result;
		}

		/**
		 * Get property of an item
		 * 
		 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/includes/class.execute.snippet.php#L655
		 * @since 7.6.7
		 */
		public function get_item_property( $item, $property ) {
			if ( is_object( $item ) ) {
				return $item->$property ?? null; // return null if there is no such property
			} elseif ( isset( $item[$property] ) ) {
				return $item[$property];
			}
		}

		/**
		* Call specified method
		*
		* @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/includes/class.execute.snippet.php#L724
		* @since 7.6.7

		* @param $method_name
		* @param $operator
		* @param $value
		*
		* @return bool
		*/
		private function call_method( $method_name, $operator, $value ) {
			if ( method_exists( $this, $method_name ) ) {
				return $this->$method_name( $operator, $value );
			} else {
				return false;
			}
		}

		/**
		 * A some selected page
		 *
		 * @since 7.6.7
		 * @param $operator
		 * @param $value
		 *
		 * @return boolean
		 */
		private function location_page_type( $operator, $conditional_page_type ) {
			if ( is_front_page() ) {
				$actual_page_type = 'is_front_page';
			} elseif ( is_home() ) {
				$actual_page_type = 'is_home';
			} elseif ( is_singular() ) {
				$actual_page_type = 'is_singular';
			} elseif ( is_author() ) {
				$actual_page_type = 'is_author';
			} elseif ( is_date() ) {
				$actual_page_type = 'is_date';
			} elseif ( is_archive() ) {
				$actual_page_type = 'is_archive';
			} elseif ( is_search() ) {
				$actual_page_type = 'is_search';
			} elseif ( is_404() ) {
				$actual_page_type = 'is_404';
			} else {
				$actual_page_type = '';
			}

			return $this->check_by_operator( $operator, $actual_page_type, $conditional_page_type );
		}

		/**
		 * A post type of the current page viewed by the user
		 *
		 * @since 7.6.7
		 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/includes/class.execute.snippet.php#L1139
		 * 
		 * @param $operator
		 * @param $conditional_post_type
		 *
		 * @return boolean
		 */
		private function location_post_type( $operator, $conditional_post_type ) {
			if ( is_singular() ) {
				$actual_post_type = get_post_type();
				return $this->check_by_operator( $operator, $conditional_post_type, $actual_post_type );
			}

			return false;
		}
		
		/**
		 * A single page/post/CPT viewed by the user
		 */
		private function location_single_post( $operator, $conditional_single_posts ) {			
			if ( is_singular() ) {
				$conditional_single_post_ids = array();

				if ( ! empty( $conditional_single_posts ) ) {
					foreach ( $conditional_single_posts as $post ) {
						$post_parts = explode( '__', $post ); // e.g. 5920__movie__Mission Impossible
						$conditional_single_post_ids[] = $post_parts[0];
					}
				}
				
				return $this->check_by_operator( $operator, $conditional_single_post_ids, get_the_ID() );
			}
		}

		/**
		 * A taxonomy archive page viewed by the user
		 *
		 * @since 7.6.7
		 * @link https://plugins.trac.wordpress.org/browser/insert-headers-and-footers/tags/2.2.5/includes/conditional-logic/class-wpcode-conditional-page.php#L299
		 * 
		 * @param $operator
		 * @param $conditional_taxonomy_type
		 *
		 * @return boolean
		 */
		private function location_taxonomy_type( $operator, $conditional_taxonomy_type ) {
			global $wp_query;

			if ( is_null( $wp_query ) ) {
				return '';
			}

			$queried_object = get_queried_object();

			$actual_taxonomy_type = isset( $queried_object->taxonomy ) ? $queried_object->taxonomy : ''; // the taxonomy slug

			return $this->check_by_operator( $operator, $conditional_taxonomy_type, $actual_taxonomy_type );
		}

		/**
		 * A taxonomy archive page for a term, viewed by the user
		 *
		 * @since 7.6.7
		 * @link https://plugins.trac.wordpress.org/browser/insert-headers-and-footers/tags/2.2.5/includes/conditional-logic/class-wpcode-conditional-page.php#L314
		 * 
		 * @param $operator
		 * @param $conditional_taxonomy_term
		 *
		 * @return boolean
		 */
		private function location_taxonomy_term( $operator, $conditional_taxonomy_term ) {
			if ( is_string( $conditional_taxonomy_term ) ) {
				$conditional_taxonomy_term_parts = explode( '__', $conditional_taxonomy_term );
				$conditional_taxonomy_term_id = intval( $conditional_taxonomy_term_parts[0] ); // an integer
				$conditional_taxonomy_term_slug = $conditional_taxonomy_term_parts[1];				
			}
			
			if ( is_array( $conditional_taxonomy_term ) ) {
				if ( count( $conditional_taxonomy_term ) > 1 ) {
					// Two or more terms were selected
					$term_ids = array();
					foreach( $conditional_taxonomy_term as $term ) {
						$term_parts = explode( '__', $term );
						$term_ids[] = intval( $term_parts[0] );
					}					
					$conditional_taxonomy_term_id = $term_ids; // an array of integers
				} else {
					// Only one term were selected
					$term_parts = explode( '__', $conditional_taxonomy_term[0] );
					$conditional_taxonomy_term_id = intval( $term_parts[0] ); // an integer
				}
			}
			// vi( $conditional_taxonomy_term_id );
			
			global $wp_query;

			if ( is_null( $wp_query ) ) {
				return array();
			}
			
			$actual_taxonomy_term_ids = '';

			if ( is_tax() || is_category() || is_tag() ) {
				$queried_object = get_queried_object();

				$actual_taxonomy_term_ids = isset( $queried_object->term_id ) ? $queried_object->term_id : '';
			}

			if ( is_singular() ) {
				$actual_taxonomy_term_ids = get_terms(
					array(
						'object_ids' => array( get_the_ID() ),
						'fields'     => 'ids',
					)
				);
			}
			// vi( $actual_taxonomy_term_ids );

			return $this->check_by_operator( $operator, $conditional_taxonomy_term_id, $actual_taxonomy_term_ids );
		}

		/**
		 * A URL of the current page viewed by the user
		 *
		 * @since 7.6.7
		 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/includes/class.execute.snippet.php#L1111
		 * 
		 * @param $operator
		 * @param $conditional_url
		 *
		 * @return boolean
		 */
		private function location_url( $operator, $conditional_url ) {
			$common_methods = new ASENHA\Classes\Common_Methods;
			$current_url = $common_methods->get_current_url(); // e.g. https://www.site.com/some-page

			return $current_url ? $this->check_by_operator( $operator, trim( $current_url, '/' ), trim( trim( $conditional_url ), '/' ) ) : false;
		}
		
		/**
		 * User login status
		 * 
		 * @since 7.6.7
		 * 
		 * @return bool
		 */
		public function user_login_status( $operator, $conditional_login_status ) {
			switch ( $conditional_login_status ) {
				case 'yes';
					$conditional_login_status = true;
					break;
					
				case 'no';
					$conditional_login_status = false;
					break;
			}
			
			return $this->check_by_operator( $operator, $conditional_login_status, is_user_logged_in() );
		}

		/**
		 * A role of the user who is viewing your website. The role "guest" is applied for unregistered users.
		 *
		 * @since 7.6.7
		 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/includes/class.execute.snippet.php#L869
		 *
		 * @param string $operator
		 * @param string $conditional_user_role
		 *
		 * @return boolean
		 */
		private function user_role( $operator, $conditional_user_role ) {
			if ( ! is_user_logged_in() ) {
				return $this->check_by_operator( $operator, $conditional_user_role, 'guest' );
			} else {
				$current_user = wp_get_current_user();
				if ( ! ( $current_user instanceof WP_User ) ) {
					return false;
				}
				$current_user_roles = array_values( $current_user->roles );

				return $this->check_by_operator( $operator, $conditional_user_role, $current_user_roles );
			}
		}

		/**
		 * Device type check
		 *
		 * @since 7.8.10
		 * @param $operator
		 * @param $value
		 *
		 * @return boolean
		 */
		private function location_device_type( $operator, $conditional_device_type ) {
			if ( version_compare( PHP_VERSION, '8.0.0', '>=' ) ) {
				// MobileDetect v4.8.x
				$detection = new MobileDetectStandalone();
			} else {
				// MobileDetect v3.74.x
				$detection = new \Detection\AsenhaMobileDetect;
			}

			// $detection->setUserAgent('iPad'); // For testing
			// $detection->setUserAgent('iPhone'); // For testing
			// $user_agent = $detection->getUserAgent(); // For testing
			
			$actual_device_type = 'unknown';
			$is_desktop = false;
			$is_tablet = false;
			$is_mobile = false;
			

			if ( version_compare( PHP_VERSION, '8.0.0', '>=' ) ) {
				// MobileDetect v4.8.x
				try {
					$is_tablet = $detection->isTablet();
					$is_mobile = $detection->isMobile();
				} catch ( \Detection\Exception\MobileDetectException $e ) {
					// Handle errors here...
				}
			} else {
				// MobileDetect v3.74.x
				$is_tablet = $detection->isTablet();
				$is_mobile = $detection->isMobile();
			}
			
			if ( ! $is_tablet && ! $is_mobile ) {
				$is_desktop = true;
			}
						
			if ( $is_desktop ) {
				$actual_device_type = 'desktop';
			}
			
			if ( ! $is_desktop && $is_tablet && $is_mobile ) {
				$actual_device_type = 'tablet';				
			}

			if ( ! $is_desktop && ! $is_tablet && $is_mobile ) {
				$actual_device_type = 'mobile';				
			}

			return $this->check_by_operator( $operator, $actual_device_type, $conditional_device_type );
		}

		/**
		 * Check whether a conditional is fulfilled by supplying the operator and values
		 *
		 * @since 7.6.6
		 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/includes/class.execute.snippet.php#L829
		 * 
		 * @param $operation
		 * @param $first
		 * @param $second
		 * @param $third
		 *
		 * @return bool
		 */
		public function check_by_operator( $operation, $first, $second, $third = false ) {
			switch ( $operation ) {
				case 'equals':
				case 'in':
					if ( ! is_array( $first ) && is_array( $second ) ) {
						return in_array( $first, $second );
					} elseif ( is_array( $first ) && ! is_array( $second ) ) {
						return in_array( $second, $first );						
					} elseif ( is_array( $first ) && is_array( $second ) ) {
						$intersect = array_intersect( $first, $second );
						if ( count( $intersect ) > 0 ) {
							return true;
						} else {
							return false;
						}
					} else {
						return $first === $second;
					}
				case 'notequal':
				case 'notin':
					if ( is_array( $second ) ) {
						return ! in_array( $first, $second );
					} elseif ( is_array( $first ) ) {
						return ! in_array( $second, $first );						
					} else {
						return $first !== $second;
					}
				case 'less':
				case 'older':
					return $first > $second;
				case 'greater':
				case 'younger':
					return $first < $second;
				case 'contains':
					return strpos( $first, $second ) !== false;
				case 'notcontain':
					return strpos( $first, $second ) === false;
				case 'between':
					return $first < $second && $second < $third;

				default:
					return $first === $second;
			}
		}

		/**
		 * Enqueue the jQuery library, if necessary
		 */
		public static function wp_enqueue_scripts() {
			wp_enqueue_script( 'jquery' );
		}

		
		/**
		 * Execute PHP code
		 */
		public function execute_php_snippet( $snippet_info ) {
			$post_id = absint( $snippet_info['id'] );

			$upload_dir = wp_upload_dir();
			$file_path = $upload_dir['basedir'] . '/code-snippets/' . $snippet_info['filename'];
			
			$execution_method = ( isset( $snippet_info['execution_method'] ) ) ? $snippet_info['execution_method'] : 'on_page_load';
			$execution_location_type = ( isset( $snippet_info['execution_location_type'] ) ) ? $snippet_info['execution_location_type'] : 'hook';

			// Check if safe mode is enabled
			$is_safe_mode_is_enabled = $this->is_safe_mode_enabled();

			// Check if snippet is active
			$is_snippet_active = $this->is_snippet_active( $post_id );
			
			$validation_result = 'No validation has been performed yet';
			$execution_result = 'No execution has been performed yet';

			// Safe mode is not enabled
			if ( ! $is_safe_mode_is_enabled ) {
				// PHP snippet is active
				if ( $is_snippet_active ) {

					// Get PHP code
					$php_code = $this->get_php_code( $file_path );

					$wp_config = new ASENHA\Classes\WP_Config_Transformer;

					// We're mainly using the custom wp_die_handler to handle fatal errors during PHP snippet editing
					// However, some fatal error does not trigger the wp_die screen, so, we catch it with
					// a custom shutdown function. This ensures we deactivate the PHP snippet and enable Safe Mode
					$args = array(
						'origin'					=> 'ase_csm', // ASE Code Snippets Manager (csm)
						'post_id'					=> $post_id,
						'execution_method' 			=> $execution_method,
						'execution_location_type' 	=> $execution_location_type,
						'wp_config'					=> $wp_config,
					);
					register_shutdown_function( array( $this, 'csm_shutdown_handler' ), $args );

				    // Basic validation for code's PHP syntax
					$validator = new ASENHA\Classes\PHP_Validator( $php_code );
					$validation_result = $validator->validate();

					if ( false === $validation_result ) {
						// No validation error were returned, code looks fine. Let's try to execute.
						// If fatal error occurs, it will be handled above. Safe mode will be enabled.
						// ob_start();
						try {
							$execution_result = eval( $php_code );
						} catch ( ParseError $parse_error ) {
							$execution_result = $parse_error;
						} catch ( Error $error ) {
							$execution_result = $error;
						} catch ( Exception $exception_error ) {
							$execution_result = $exception_error;
						} catch ( Throwable $throwable_error ) {
							$execution_result = $throwable_error;
						}
						// Ref: https://blog.airbrake.io/blog/php-exception-handling/php-parseerror
						// Ref: https://blog.eleven-labs.com/en/php7-throwable-error-exception/
						// All PHP errors implement the Throwable interface, or are extended from another inherited class therein.
						// Error implements the Throwable interface.
						// ParseError extends the Error class.
						// Exception implements the Throwable interface.
						// ob_end_clean();
					} else {
						// Do not execute the code
						$execution_result = 'Code was not executed due to validation error.';
					}

				} else {							
					$execution_result = 'PHP snippet is inactive, so code is not executed.';
				}							
			} else {
				$execution_result = 'Code was not executed due to safe mode being enabled.';
			}
			// if ( $post_id == 1234 ) {
			// 	vi( $execution_result );				
			// }

			// Code has gone through validation and/or execution. Let's check if we have valid error.
			if ( false !== $validation_result || null !== $execution_result ) {

				$error_message = '';

				if ( is_array( $validation_result ) ) {

					update_post_meta( $post_id, 'php_snippet_has_error', true );
					update_post_meta( $post_id, 'php_snippet_error_type', 'non-fatal' );
					update_post_meta( $post_id, 'php_snippet_error_code', 'unknown' );
					
					$message = rtrim( $validation_result['message'], '.' );
					$line = intval( $validation_result['line'] ) - 1;
					$error_message = $message . ' on line ' . $line;

					update_post_meta( $post_id, 'php_snippet_error_message', '<span class="error-message">' . $error_message . '</span>' );
					update_post_meta( $post_id, 'php_snippet_error_via', 'execute_php_snippet() - $validation_result' );
					update_post_meta( $post_id, 'safe_mode_activation_via', '' );
					
				}
				
				if ( is_object( $execution_result ) ) {

					update_post_meta( $post_id, 'php_snippet_has_error', true );
					update_post_meta( $post_id, 'php_snippet_error_type', 'non-fatal' );
					update_post_meta( $post_id, 'php_snippet_error_code', 'unknown' );

					$message = $execution_result->getMessage();
					$line = $execution_result->getLine();
					$error_message = $message . ' on line ' . $line;

					update_post_meta( $post_id, 'php_snippet_error_message', '<span class="error-message">' . $error_message . '</span>' );
					update_post_meta( $post_id, 'php_snippet_error_via', 'execute_php_snippet() - $execution_result is_object' );
					update_post_meta( $post_id, 'safe_mode_activation_via', '' );
						
				} 
				
		    } else if ( false === $validation_result || null === $execution_result ) {
				
				if ( get_post_meta( $post_id, 'php_snippet_has_error', true ) ) {
					// Let's clean up lingering error message recorded and shown in the snippet							
					update_post_meta( $post_id, 'php_snippet_has_error', false );
					update_post_meta( $post_id, 'php_snippet_error_type', '' );
					update_post_meta( $post_id, 'php_snippet_error_code', '' );
					update_post_meta( $post_id, 'php_snippet_error_message', '' );
					update_post_meta( $post_id, 'php_snippet_error_via', '' );
					update_post_meta( $post_id, 'safe_mode_activation_via', '' );					
				}

			} else {}

			if ( 'on_page_load' == $execution_method 
				&& 'shortcode' == $execution_location_type
			) {
				if ( is_a( $execution_result, 'ParseError' ) ) {
					return '';
				} else {
			        return $execution_result;
				}
			}
			
		}

	    /**
	     * Check if safe mode is enabled for PHP snippets
	     * 
	     * @since 7.6.5
	     */
	    public function is_safe_mode_enabled() {
			$safe_mode_via_constant = defined( 'CSM_SAFE_MODE' ) ? CSM_SAFE_MODE : false;

			if ( ( isset( $_GET['safemode'] ) && sanitize_text_field( $_GET['safemode'] ) == '1' ) 
				|| ( $safe_mode_via_constant )
				) {
				$safe_mode_is_enabled = true;
			} else {
				$safe_mode_is_enabled = false;							
			}

			return $safe_mode_is_enabled;
	    }
	    
	    /**
	     * Check if snippet is active
	     * 
	     * @since 7.6.5
	     */
	    public function is_snippet_active( $post_id ) {
			$is_snippet_active = ( 'no' != get_post_meta( $post_id, '_active', true ) ) ? true : false;
			
			return $is_snippet_active;
	    }
	    
	    /**
	     * Get PHP code that's been cleaned up
	     * 
	     * @since 7.6.5
	     */
	    public function get_php_code( $file_path ) {
			// Get code and parse it as string
			$php_code = file_get_contents( $file_path );

		    // Clean up, so code is in proper form for eval(), i.e. without opening and closing php tags
		    $php_code = trim( $php_code );
		    $php_code = ltrim( $php_code, '<?php' );
		    $php_code = rtrim( $php_code, '?>' );

		    return $php_code;
	    }
		
		/**
		 * Handle fatal error caused by faulty PHP snippets
		 */
		public function csm_shutdown_handler( $args ) {			
		    $error_raw = error_get_last();

			$origin = isset( $args['origin'] ) ? $args['origin'] : '';
			$post_id = isset( $args['post_id'] ) ? intval( $args['post_id'] ) : '';
			$execution_method = isset( $args['execution_method'] ) ? $args['execution_method'] : 'on_page_load';
			$execution_location_type = isset( $args['execution_location_type'] ) ? $args['execution_location_type'] : 'hook';
			$wp_config = isset( $args['wp_config'] ) ? $args['wp_config'] : '';

			$options_extra = get_option( ASENHA_SLUG_U . '_extra', array() );
			$last_edited_csm_php_snippet = isset( $options_extra['last_edited_csm_php_snippet'] ) ? intval( $options_extra['last_edited_csm_php_snippet'] ) : '';
			// $active_php_snippets = isset( $options_extra['code_snippets']['php'] ) ? array_keys( $options_extra['code_snippets']['php'] ) : array();

		    // Only process if there's an actual error, and origin is 
		    // from PHP code snippets handled by ASE Code Snippets Manager
		    if ( $error_raw !== NULL && isset( $args['origin'] ) && 'ase_csm' == $origin ) {

		        $file 			= $error_raw["file"];			    
				$is_error_from_csm_snippet = ( false !== strpos( $file, '/premium/code-snippets-manager/' ) ) ? true : false;
			    
			    if ( $is_error_from_csm_snippet ) {

			        $code 			= $error_raw["type"]; // Ref: https://www.php.net/manual/en/errorfunc.constants.php#109430
			        $fatal_error_codes = array( 1, 16, 256 );
			        if ( in_array( intval( $code ), $fatal_error_codes ) ) {
			        	$type = 'fatal';
			        } else {
			        	$type = 'non-fatal';
			        }
				    
			        $line 			= $error_raw["line"];
			        $message_full 	= $error_raw["message"]; // includes stack trace
			        $message_parts 	= explode( ' in /', $message_full );
			        $message 		= $message_parts[0];
					$error_message = $message . ' on line ' . $line;
			        		        
			        if ( 'fatal' == $type ) {
					    // if ( in_array( $post_id, $active_php_snippets )
					    // 	&& $post_id == $last_edited_csm_php_snippet 
						// ) {

					    if ( $post_id == $last_edited_csm_php_snippet 
						) {

					        $message_parts 	= explode( 'Stack trace:', $message_full );
					        $message_stack_trace = $message_parts[1];
					        $snippet_edit_url = get_edit_post_link( $post_id );

					        // Record error info in PHP snippet post meta
							update_post_meta( $post_id, 'php_snippet_has_error', true );
							update_post_meta( $post_id, 'php_snippet_error_type', $type );
							update_post_meta( $post_id, 'php_snippet_error_code', $code );
							update_post_meta( $post_id, 'php_snippet_error_message', '<span class="error-message">' . $error_message . '</span><span class="stack-trace">Stack trace:</span>' . ltrim( nl2br( str_replace( ABSPATH, '/', $message_stack_trace ) ), '<br />' ) );
							update_post_meta( $post_id, 'php_snippet_error_via', 'shutdown' );
							update_post_meta( $post_id, 'safe_mode_activation_via', 'shutdown' );

					        // Deactivate PHP snippet
					        update_post_meta( $post_id, '_active', 'no' );

						    // We have a fatal error making the site inaccessible, let's enable safe mode, halt PHP snippets execution, and make the site accessible again. This is only for snippets that are executed on_page_load via a hook.

					        if ( 'on_page_load' == $execution_method 
					    		&& 'hook' == $execution_location_type
					    	) {

								$wp_config_options = array(
									'add'       => true, // Add the config if missing.
									'raw'       => true, // Display value in raw format without quotes.
									'normalize' => false, // Normalize config output using WP Coding Standards.
								);

								$update_success = $wp_config->update( 'constant', 'CSM_SAFE_MODE', 'true', $wp_config_options );
								
								if ( $update_success ) {
									// Prevent showing fatal error screen by redirecting back to snippet edit screen
									// wp_safe_redirect( get_edit_post_link( $post_id ) );
									// exit;
								}
					        	
					        }
								
						}
			        }
			     
			    }

		    } else {

		    	// There is no error
				if ( 'on_page_load' == $execution_method  && 'shortcode' == $execution_location_type ) {
					// Probably a snippet that returns a string value. Most likely a snippet executed via shortcode.
					if ( $post_id == $last_edited_csm_php_snippet ) {
						if ( get_post_meta( $post_id, 'php_snippet_has_error', true ) ) {
							// Let's clean up lingering error message recorded and shown in the snippet							
							update_post_meta( $post_id, 'php_snippet_has_error', false );
							update_post_meta( $post_id, 'php_snippet_error_type', '' );
							update_post_meta( $post_id, 'php_snippet_error_code', '' );
							update_post_meta( $post_id, 'php_snippet_error_message', '' );
							update_post_meta( $post_id, 'php_snippet_error_via', '' );
							update_post_meta( $post_id, 'safe_mode_activation_via', '' );							
						}
					}
				}
		    	
		    }

		}

		/**
		 * Set constants for later use
		 */
		public function set_constants() {
			$dir       = wp_upload_dir();
			$constants = array(
				'CSM_VERSION'     => ASENHA_VERSION,
				'CSM_UPLOAD_DIR'  => $dir['basedir'] . '/code-snippets',
				'CSM_UPLOAD_URL'  => $dir['baseurl'] . '/code-snippets',
				'CSM_PLUGIN_FILE' => __FILE__,
			);
			foreach ( $constants as $_key => $_value ) {
				if ( ! defined( $_key ) ) {
					define( $_key, $_value );
				}
			}
		}

	}

endif;

if ( ! function_exists( 'Code_Snippets_Manager' ) ) {
	/**
	 * Returns the main instance of Code_Snippets_Manager
	 *
	 * @return Code_Snippets_Manager
	 */
	function Code_Snippets_Manager() {
		return Code_Snippets_Manager::instance();
	}

	Code_Snippets_Manager();
}