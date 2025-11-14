<?php
/**
 * Code Snippets Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// use ScssPhp\ScssPhp\Compiler;

/**
 * Code_Snippets_Manager_Admin
 */
class Code_Snippets_Manager_Admin {

	/**
	 * Default options for a new page
	 */
	private $default_options = array(
		'language' 						=> 'css',
		'linking'  						=> 'internal',
		'type'     						=> 'header',
		'side'     						=> 'frontend',
		'execution_method'		=> '',
		'execution_location_type'		=> '',
		'execution_location'			=> '',
		'execution_location_details'	=> '',
		'conditionals'					=> '',
		'priority' 						=> 10,
		'compile_scss' 					=> 'yes',
	);

	/**
	 * Array with the options for a specific code-snippets-manager post
	 */
	private $options = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'remove_admin_menu_for_non_admins' ), 9999999998 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_dequeue_scripts_styles' ), PHP_INT_MAX );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
		add_action( 'admin_init', array( $this, 'create_uploads_directory' ) );
		add_action( 'edit_form_after_title', array( $this, 'codemirror_editor' ) );
		add_action( 'add_meta_boxes_asenha_code_snippet', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'options_save_meta_box_data' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'process_php_snippet' ), 10, 2 );
		add_action( 'wp_restore_post_revision', array( $this, 'restore_snippet_revision_in_file' ), 10, 2 );
		add_action( 'trashed_post', array( $this, 'trash_post' ) );
		add_action( 'untrashed_post', array( $this, 'untrash_post' ) );
		add_action( 'delete_post', array( $this, 'delete_post' ), 10, 2 );
		add_action( 'wp_ajax_csm_disable_safe_mode', array( $this, 'wp_ajax_csm_disable_safe_mode' ) );
		add_action( 'wp_ajax_csm_active_code', array( $this, 'wp_ajax_csm_active_code' ) );
		add_action( 'wp_ajax_csm_permalink', array( $this, 'wp_ajax_csm_permalink' ) );
		add_action( 'wp_ajax_csm_ajax_get_page_type_list', array( $this, 'csm_ajax_get_page_type_list' ) );
		add_action( 'wp_ajax_csm_ajax_get_post_types', array( $this, 'csm_ajax_get_post_types' ) );
		add_action( 'wp_ajax_csm_ajax_get_single_posts', array( $this, 'csm_ajax_get_single_posts' ) );
		add_action( 'wp_ajax_csm_ajax_get_taxonomies_list', array( $this, 'csm_ajax_get_taxonomies_list' ) );
		add_action( 'wp_ajax_csm_ajax_get_taxonomies_terms_list', array( $this, 'csm_ajax_get_taxonomies_terms_list' ) );
		add_action( 'wp_ajax_csm_ajax_get_user_roles', array( $this, 'csm_ajax_get_user_roles' ) );
		add_action( 'wp_ajax_csm_ajax_get_device_type_list', array( $this, 'csm_ajax_get_device_type_list' ) );
		add_action( 'post_submitbox_start', array( $this, 'post_submitbox_start' ) );
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
		add_action( 'load-post.php', array( $this, 'contextual_help' ) );
		add_action( 'load-post-new.php', array( $this, 'contextual_help' ) );
		add_action( 'edit_form_before_permalink', array( $this, 'edit_form_before_permalink' ) );
		add_action( 'before_delete_post', array( $this, 'before_delete_post' ) );

		// Add some custom actions/filters
		add_filter( 'manage_asenha_code_snippet_posts_columns', array( $this, 'manage_custom_posts_columns' ) );
		add_action( 'manage_asenha_code_snippet_posts_custom_column', array( $this, 'manage_posts_columns' ), 10, 2 );
		add_filter( 'manage_edit-asenha_code_snippet_sortable_columns', array( $this, 'manage_edit_posts_sortable_columns' ) );
		add_action( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 2 );
		add_action( 'posts_join_paged', array( $this, 'posts_join_paged' ), 10, 2 );
		add_action( 'posts_where_paged', array( $this, 'posts_where_paged' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
		add_filter( 'parse_query', array( $this, 'parse_query' ), 10 );
		add_filter( 'wp_statuses_get_supported_post_types', array( $this, 'wp_statuses_get_supported_post_types' ), 20 );

		add_action( 'current_screen', array( $this, 'current_screen_2' ), 100 );
	}

	/**
	 * Add submenu pages
	 */
	function admin_menu() {
		$menu_slug    = 'edit.php?post_type=asenha_code_snippet';
		// add_menu_page(
		// 	__( 'Code Snippets', 'admin-site-enhancements' ),
		// 	__( 'Code Snippets', 'admin-site-enhancements' ),
		// 	'publish_code_snippetss',
		// 	$menu_slug,
		// 	'',
		// 	'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGZpbGwtcnVsZT0iZXZlbm9kZCIgZD0iTTYuMjggNS4yMmEuNzUuNzUgMCAwIDEgMCAxLjA2TDIuNTYgMTBsMy43MiAzLjcyYS43NS43NSAwIDAgMS0xLjA2IDEuMDZMLjk3IDEwLjUzYS43NS43NSAwIDAgMSAwLTEuMDZsNC4yNS00LjI1YS43NS43NSAwIDAgMSAxLjA2IDBabTcuNDQgMGEuNzUuNzUgMCAwIDEgMS4wNiAwbDQuMjUgNC4yNWEuNzUuNzUgMCAwIDEgMCAxLjA2bC00LjI1IDQuMjVhLjc1Ljc1IDAgMCAxLTEuMDYtMS4wNkwxNy40NCAxMGwtMy43Mi0zLjcyYS43NS43NSAwIDAgMSAwLTEuMDZabS0yLjM0My0zLjIwOWEuNzUuNzUgMCAwIDEgLjYxMi44NjdsLTIuNSAxNC41YS43NS43NSAwIDAgMS0xLjQ3OC0uMjU1bDIuNS0xNC41YS43NS43NSAwIDAgMSAuODY2LS42MTJaIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=',
		// 	21
		// );

		// Remove default "Add New"
		$submenu_slug = 'post-new.php?post_type=asenha_code_snippet';
		remove_submenu_page( $menu_slug, $submenu_slug );
		
		$title = __( 'Add CSS / SCSS Snippet', 'admin-site-enhancements' );
		add_submenu_page( $menu_slug, $title, $title, 'publish_code_snippetss', $submenu_slug . '&amp;language=css', '', 1 );

		$title = __( 'Add JS Snippet', 'admin-site-enhancements' );
		add_submenu_page( $menu_slug, $title, $title, 'publish_code_snippetss', $submenu_slug . '&amp;language=js', '', 2 );

		$title = __( 'Add HTML Snippet', 'admin-site-enhancements' );
		add_submenu_page( $menu_slug, $title, $title, 'publish_code_snippetss', $submenu_slug . '&amp;language=html', '', 3 );

		$title = __( 'Add PHP Snippet', 'admin-site-enhancements' );
		add_submenu_page( $menu_slug, $title, $title, 'publish_code_snippetss', $submenu_slug . '&amp;language=php', '', 4 );

	}
	
	/**
	 * Make sure the Code Snippets admin menu is not shown to non-administrators
	 */
	public function remove_admin_menu_for_non_admins() {
		global $menu;
		
        $current_user = wp_get_current_user();
        $current_user_roles = (array) $current_user->roles; // single dimensional array of role slugs
        $current_user_roles = array_values( $current_user_roles );
		
		if ( ! in_array( 'administrator', $current_user_roles ) ) {
			foreach ( $menu as $menu_index => $menu_item ) {
				if ( 'edit_code_snippetss' == $menu_item[1] ) {
					unset( $menu[$menu_index] );
				}
			}
		}
	}


	/**
	 * Enqueue the scripts and styles
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		
		$screen = get_current_screen();

		// Only for code-snippets-manager post type
		if ( $screen->post_type != 'asenha_code_snippet' ) {
			return false;
		}

		// We force loading the uncompressed version of TinyMCE. This ensures we load 'wp-tinymce-root' and then 'wp-tinymce', 
		// which prevents issue where the Visual editor for the snippet description is unusable in some scenarios
		$wp_scripts = wp_scripts();
		$wp_scripts->remove( 'wp-tinymce' );
		wp_register_tinymce_scripts( $wp_scripts, true );

		// Some handy variables
		// $a  = plugins_url( '/', CSM_PLUGIN_FILE ) . 'assets';
		$ase_assets = ASENHA_URL . 'assets';
		$ase_csm_assets = ASENHA_URL . 'includes/premium/code-snippets-manager/assets';
		$ase_csm_font = ASENHA_URL . 'includes/premium/code-snippets-manager/assets/font';
		$codemirror = ASENHA_URL . 'includes/premium/code-snippets-manager/assets/codemirror';
		$codemirror_theme = ASENHA_URL . 'includes/premium/code-snippets-manager/assets/codemirror/theme';
		$v  = CSM_VERSION;

		wp_enqueue_script( 'csm-tipsy', $ase_csm_assets . '/jquery.tipsy.js', array( 'jquery' ), $v, false );
		wp_enqueue_style( 'csm-tipsy', $ase_csm_assets . '/tipsy.css', array(), $v );
		wp_enqueue_script( 'csm-cookie', $ase_csm_assets . '/js.cookie.js', array( 'jquery' ), $v, false );
		wp_register_script( 'csm-admin', $ase_csm_assets . '/csm_admin.js', array( 'jquery', 'jquery-ui-resizable' ), $v, false );
		wp_localize_script( 'csm-admin', 'CSM', $this->cm_localize() );
		wp_enqueue_script( 'csm-admin' );
		wp_enqueue_style( 'csm-admin', $ase_csm_assets . '/csm_admin.css', array(), $v );

		// Only for the new/edit Code's page
		if ( $hook_suffix == 'post-new.php' || $hook_suffix == 'post.php' ) {
			wp_deregister_script( 'wp-codemirror' );

			wp_enqueue_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css', array(), $v );
			wp_enqueue_script( 'csm-codemirror', $codemirror . '/lib/codemirror.js', array( 'jquery' ), $v, false );
			wp_enqueue_style( 'csm-codemirror', $codemirror . '/lib/codemirror.css', array(), $v );
			// wp_enqueue_script( 'csm-admin_url_rules', $ase_csm_assets . '/csm_admin-url_rules.js', array( 'jquery' ), $v, false );

			// Font
			wp_enqueue_style( 'csm-font', $ase_csm_font . '/font.css', array(), $v );

			// Themes
			wp_enqueue_style( 'cm-theme-eclipse', $codemirror_theme . '/' . $this->get_codemirror_editor_theme_slug() . '.css', array(), $v );
			
			// Add the language modes
			$cmm = ASENHA_URL . 'includes/premium/code-snippets-manager/assets/codemirror/mode/';
			wp_enqueue_script( 'cm-xml', $cmm . 'xml/xml.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-js', $cmm . 'javascript/javascript.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-css', $cmm . 'css/css.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-htmlmixed', $cmm . 'htmlmixed/htmlmixed.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-clike', $cmm . 'php/clike.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-php', $cmm . 'php/php.js', array( 'csm-codemirror' ), $v, false );

			$cma = ASENHA_URL . 'includes/premium/code-snippets-manager/assets/codemirror/addon/';
			wp_enqueue_script( 'csm-closebrackets', $cma . 'edit/closebrackets.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-matchbrackets', $cma . 'edit/matchbrackets.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-matchtags', $cma . 'edit/matchtags.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-dialog', $cma . 'dialog/dialog.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-search', $cma . 'search/search.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-searchcursor', $cma . 'search/searchcursor.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'cm-jump-to-line', $cma . 'search/jump-to-line.js', array( 'csm-codemirror' ), $v, false );
			// wp_enqueue_script( 'cm-match-highlighter', $cma . 'search/match-highlighter.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-fullscreen', $cma . 'display/fullscreen.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_style( 'cm-dialog', $cma . 'dialog/dialog.css', array(), $v );
			wp_enqueue_script( 'csm-formatting', $codemirror . '/lib/util/formatting.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-comment', $cma . 'comment/comment.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-active-line', $cma . 'selection/active-line.js', array( 'csm-codemirror' ), $v, false );

			// Hint Addons
			wp_enqueue_script( 'csm-hint', $cma . 'hint/show-hint.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-hint-js', $cma . 'hint/javascript-hint.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-hint-xml', $cma . 'hint/xml-hint.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-hint-html', $cma . 'hint/html-hint.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-hint-css', $cma . 'hint/css-hint.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-hint-anyword', $cma . 'hint/anyword-hint.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_style( 'csm-hint', $cma . 'hint/show-hint.css', array(), $v );

			// Fold Addons
			wp_enqueue_script( 'csm-fold-brace', $cma . 'fold/brace-fold.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-fold-comment', $cma . 'fold/comment-fold.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-fold-code', $cma . 'fold/foldcode.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-fold-gutter', $cma . 'fold/foldgutter.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-fold-indent', $cma . 'fold/indent-fold.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-fold-markdown', $cma . 'fold/markdown-fold.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_script( 'csm-fold-xml', $cma . 'fold/xml-fold.js', array( 'csm-codemirror' ), $v, false );
			wp_enqueue_style( 'csm-fold-gutter', $cma . 'fold/foldgutter.css', array(), $v );

			// remove the assets from other plugins so it doesn't interfere with CodeMirror
			// global $wp_scripts;
			// if ( is_array( $wp_scripts->registered ) && count( $wp_scripts->registered ) != 0 ) {
			// 	foreach ( $wp_scripts->registered as $_key => $_value ) {
			// 		if ( ! isset( $_value->src ) ) {
			// 			continue;
			// 		}

			// 		if ( strstr( $_value->src, 'wp-content/plugins' ) !== false
			// 		&& strstr( $_value->src, 'plugins/admin-site-enhancements/' ) === false
			// 		&& strstr( $_value->src, 'plugins/advanced-custom-fields/' ) === false
			// 		&& strstr( $_value->src, 'plugins/wp-jquery-update-test/' ) === false
			// 		&& strstr( $_value->src, 'plugins/enable-jquery-migrate-helper/' ) === false
			// 		&& strstr( $_value->src, 'plugins/tablepress/' ) === false
			// 		&& strstr( $_value->src, 'plugins/advanced-custom-fields-pro/' ) === false ) {
			// 			unset( $wp_scripts->registered[ $_key ] );
			// 		}
			// 	}
			// }
			// remove the CodeMirror library added by the Product Slider for WooCommerce plugin by ShapedPlugin
			// wp_enqueue_style( 'spwps-codemirror', $ase_csm_assets . '/empty.css', '1.0' );
			// wp_enqueue_script( 'spwps-codemirror', $ase_csm_assets . '/empty.js', array(), '1.0', true );
			
			// Conditional logic
			wp_register_script( 'csm-select2', $ase_assets . '/js/select2.full.min.js', array( 'jquery' ), $v, false );
			wp_enqueue_script( 'csm-select2' );
			wp_enqueue_style( 'csm-select2', $ase_assets . '/css/select2.min.css', array(), $v );
			wp_register_script( 'csm-conditional-logic', $ase_csm_assets . '/csm_conditional_logic.js', array( 'jquery', 'csm-select2' ), $v, true );
			wp_enqueue_script( 'csm-conditional-logic' );
			wp_enqueue_style( 'csm-conditional-logic', $ase_csm_assets . '/csm_conditional_logic.css', array( 'csm-select2' ), $v );
		}
	}
	
	/**
	 * Dequeue scripts and styles. Mostly for compatibility fixes with other plugins
	 * 
	 * @since 7.6.8
	 */
	public function admin_dequeue_scripts_styles( $hook_suffix ) {
		$screen = get_current_screen();

		// Only for code-snippets-manager post type
		if ( $screen->post_type != 'asenha_code_snippet' ) {
			return false;
		}

		if ( $hook_suffix == 'post-new.php' || $hook_suffix == 'post.php' ) {
			wp_dequeue_script( 'select2' ); // Secure Custom Fields
			wp_dequeue_style( 'select2' ); // Secure Custom Fields
			wp_dequeue_script( 'learndash-select2-jquery-script' ); // LearnDash
			wp_dequeue_style( 'learndash-select2-jquery-style' ); // LearnDash
			wp_dequeue_script( 'hashform-select2' ); // Hash Form
			wp_dequeue_style( 'hashform-select2' ); // Hash Form
		}
	}
	
	public function get_codemirror_editor_theme_slug() {
        $options = get_option( ASENHA_SLUG_U, array() );
        $theme = isset( $options['code_snippets_editor_theme'] ) ? $options['code_snippets_editor_theme'] : 'dark';
        
        switch ( $theme ) {
        	case 'dark':
        		$editor_theme = 'monokai-mod';
        		break;
        		
        	case 'light':
        		$editor_theme = 'solarized-mod';
        		break;
        }
        
        return $editor_theme;		
	}

	/**
	 * Send variables to the csm_admin.js script
	 */
	public function cm_localize() {
		global $pagenow;

        $extra_options = get_option( ASENHA_SLUG_U . '_extra', array() );
        $settings = isset( $extra_options['code_snippets_manager_settings'] ) ? $extra_options['code_snippets_manager_settings'] : array();

		$vars = array(
			'autocomplete'   		=> isset( $settings['csm_autocomplete'] ) && ! $settings['csm_autocomplete'] ? false : true,
			'active'         		=> __( 'Active', 'admin-site-enhancements' ),
			'inactive'       		=> __( 'Inactive', 'admin-site-enhancements' ),
			'activate'       		=> __( 'Activate', 'admin-site-enhancements' ),
			'deactivate'     		=> __( 'Deactivate', 'admin-site-enhancements' ),
			'active_title'   		=> __( 'The code is active. Click to deactivate it', 'admin-site-enhancements' ),
			'deactive_title' 		=> __( 'The code is inactive. Click to activate it', 'admin-site-enhancements' ),
			'execution_success'		=> __( 'Snippet was successfully executed.', 'admin-site-enhancements' ),
			'execution_error'		=> __( 'Something went wrong. Snippet was not successfully executed.', 'admin-site-enhancements' ),
			'page_now'		 		=> $pagenow,

			/* CodeMirror options */
			'codemirror' => array(
				'theme'			   => $this->get_codemirror_editor_theme_slug(),
				'indentUnit'       => 4,
				'indentWithTabs'   => true,
				'inputStyle'       => 'contenteditable',
				'lineNumbers'      => true,
				'lineWrapping'     => true,
				'styleActiveLine'  => true,
				'continueComments' => true,
				'extraKeys'        => array(
					'Ctrl-Space' => 'autocomplete',
					'Cmd-Space'  => 'autocomplete',
					'Ctrl-/'     => 'toggleComment',
					'Cmd-/'      => 'toggleComment',
					'Alt-F'      => 'findPersistent',
					'Ctrl-F'     => 'findPersistent',
					'Cmd-F'      => 'findPersistent',
					'Ctrl-J'     =>  'toMatchingTag',
				),
				'direction'        => 'ltr', // Code is shown in LTR even in RTL languages.
				'gutters'          => array( 'CodeMirror-lint-markers' ),
				'matchBrackets'    => true,
				'matchTags'        => array( 'bothTags' => true ),
				'autoCloseBrackets' => true,
				'autoCloseTags'    => true,
			)
		);

		return apply_filters( 'csm_code_editor_settings', $vars);
	}

	public function add_meta_boxes() {
		$options = $this->get_options( get_the_ID() );
		
		// Add snippet options meta box
		add_meta_box( 
			'code-snippet-options', 
			__( 'Snippet Options', 'admin-site-enhancements' ), 
			array( $this, 'code_snippet_options_meta_box_callback' ), 
			'asenha_code_snippet', 
			'side', 
			'low' 
		);
		
		// Add WP Editor meta box for snippet description
		add_meta_box( 
			'code-snippet-description', 
			__( 'Description', 'admin-site-enhancements' ), 
			array( $this, 'code_snippet_description_meta_box_callback' ), 
			'asenha_code_snippet', 
			'advanced', 
			'high' 
		);

		remove_meta_box( 'slugdiv', 'asenha_code_snippet', 'normal' );
	}

	/**
	 * Get options for a specific code-snippets-manager post
	 */
	private function get_options( $post_id ) {
		if ( isset( $this->options[ $post_id ] ) ) {
			return $this->options[ $post_id ];
		}

		$options = get_post_meta( $post_id );
		if ( empty( $options ) || ! isset( $options['options'][0] ) ) {
			$this->options[ $post_id ] = $this->default_options;
			return $this->default_options;
		}

		$options                   = @unserialize( $options['options'][0] );
		$this->options[ $post_id ] = $options;
		return $options;
	}


	/**
	 * Reformat the `edit` or the `post` screens
	 */
	function current_screen( $current_screen ) {

		if ( $current_screen->post_type != 'asenha_code_snippet' ) {
			return false;
		}

		// All snippets
		if ( $current_screen->base == 'edit' ) {
			add_action( 'admin_head', array( $this, 'current_screen_edit' ) );
		}

		// Edit snippet
		if ( $current_screen->base == 'post' ) {
			add_action( 'admin_head', array( $this, 'current_screen_post' ) );
		}

		wp_deregister_script( 'autosave' );
	}



	/**
	 * Add the buttons in the `edit` screen
	 */
	function add_new_buttons() {
		$current_screen = get_current_screen();

		if ( ( isset( $current_screen->action ) && $current_screen->action == 'add' ) || $current_screen->post_type != 'asenha_code_snippet' ) {
			return false;
		}
		?>
	<div class="updated buttons">
	<a href="post-new.php?post_type=asenha_code_snippet&language=css" class="custom-btn custom-css-btn"><?php _e( 'Add CSS/SCSS code', 'admin-site-enhancements' ); ?></a>
	<a href="post-new.php?post_type=asenha_code_snippet&language=js" class="custom-btn custom-js-btn"><?php _e( 'Add JS code', 'admin-site-enhancements' ); ?></a>
	<a href="post-new.php?post_type=asenha_code_snippet&language=html" class="custom-btn custom-js-btn"><?php _e( 'Add HTML code', 'admin-site-enhancements' ); ?></a>
		<!-- a href="post-new.php?post_type=asenha_code_snippet&language=php" class="custom-btn custom-php-btn">Add PHP code</a -->
	</div>
		<?php
	}



	/**
	 * Add new columns in the `edit` screen
	 */
	function manage_custom_posts_columns( $columns ) {		
		$columns = array(
			'cb'        	=> '<input type="checkbox" />',
			'active'    	=> __( 'Status', 'admin-site-enhancements' ),
			'type'      	=> __( 'Type', 'admin-site-enhancements' ),
			'title'     	=> __( 'Title' ),
			'csm_options'   => __( 'Snippet Options' ),
			'priority'   	=> __( 'Priority' ),
			'description'   => __( 'Description' ),
			'asenha_code_snippet_category'   => __( 'Categories' ),
			// 'published' 	=> __( 'Published' ),
			'csm_modified'  => __( 'Modified', 'admin-site-enhancements' ),
			'author'    	=> __( 'Author' ),
		);
		
		return $columns;
	}


	/**
	 * Fill the data for the new added columns in the `edit` screen
	 */
	function manage_posts_columns( $column, $post_id ) {
		$options = $this->get_options( $post_id );
		$execution_method = ( isset( $options['execution_method'] ) ) ? $options['execution_method'] : 'on_page_load';
		$secure_url_token = ( isset( $options['secure_url_token'] ) ) ? $options['secure_url_token'] : '';
		$execution_location_type = ( isset( $options['execution_location_type'] ) ) ? $options['execution_location_type'] : 'hook';
		$execution_location = ( isset( $options['execution_location'] ) ) ? $options['execution_location'] : 'plugins_loaded';
		$execution_location_details = ( isset( $options['execution_location_details'] ) ) ? $options['execution_location_details'] : 'everywhere';

		if ( 'type' === $column ) {
			echo '<a href="' . admin_url( 'edit.php?post_status=all&post_type=asenha_code_snippet&language_filter=' . $options['language'] ) . '" class="button button-small snippet-language">' . $options['language'] . '</a>';
		}

		if ( 'csm_options' === $column ) {
			// Load snippet
			$linking = ( isset( $options['linking'] ) ) ? $options['linking'] : '';
			$linking_label = ( ! empty( $linking ) && $linking == 'external' ) ? __( 'As a file', 'admin-site-enhancements' ) : __( 'Inline', 'admin-site-enhancements' );

			// Position on page
			$type = ( isset( $options['type'] ) ) ? $options['type'] : '';
			if ( 'header' === $type ) {
				$type_label = __( 'In &lt;head&gt;', 'admin-site-enhancements' );
			} elseif ( 'body_open' === $type ) {
				$type_label = __( 'After &lt;body&gt;', 'admin-site-enhancements' );				
			} elseif ( 'footer' === $type ) {
				$type_label = __( 'In &lt;footer&gt;', 'admin-site-enhancements' );				
			} else {
				$type_label = '';
			}

			// On which part of the site
			$sides = ( isset( $options['side'] ) ) ? $options['side'] : '';
			$sides = explode( ',', $sides );
			$sides_label = implode( ', ', $sides );
			
			if ( isset( $options['conditionals'] ) && is_array( $options['conditionals'] ) && isset( $options['conditionals'][0][0] ) ) {
				$sides_label = str_replace( 'frontend', __( 'frontend (conditional)', 'admin-site-enhancements' ), $sides_label );
			}
			
			// Combined option labels
			$options_labels = esc_html( $linking_label ) . '<br /> ' . esc_html( $type_label ) . '<br /> ' . esc_html( ucfirst( $sides_label ) );
			
			if ( $options['language'] != 'php' ) {
				echo $options_labels;
			} else {
				switch ( $execution_method ) {
					case 'on_page_load';
						$execution_method_label = __( 'Always (on page load)', 'admin-site-enhancements' );
						break;	

					case 'on_demand';
						$execution_method_label = __( 'Manually (on demand)', 'admin-site-enhancements' );
						break;	

					case 'via_secure_url';
						$execution_method_label = __( 'Via a secure URL', 'admin-site-enhancements' );
						break;	
				}
				
				switch ( $execution_location_details ) {
					case 'everywhere';
						$execution_location_details = __( 'Everywhere / set in code', 'admin-site-enhancements' );
						break;	

					case 'admin';
						$execution_location_details = __( 'Admin', 'admin-site-enhancements' );
						break;	

					case 'frontend';
						$execution_location_details = __( 'Frontend', 'admin-site-enhancements' );
						break;	
				}

				switch ( $execution_method ) {
					case 'on_page_load';
						switch ( $execution_location_type ) {
							case 'hook';
								echo '<div class="php-snippet-options">
										<div class="execution-method">' . $execution_method_label . '</div>
										<div class="execution-location via-hook"><code>' . $execution_location . '</code> hook</div>
										<div class="execution-location-details">' . $execution_location_details .  '</div>
									</div>';
								break;
								
							case 'shortcode';
								echo '<div class="php-snippet-options">
										<div class="execution-method">' . $execution_method_label . '</div>
										<div class="shortcode-wrapper">
											<div class="the-shortcode">[php_snippet id="' . $post_id . '"]</div>
											<span class="copy-shortcode-button" data-clipboard-text="' . $post_id . '"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><g fill="none" stroke="#8C8F94" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"><path d="M16 3H4v13"/><path d="M8 7h12v12a2 2 0 0 1-2 2h-8a2 2 0 0 1-2-2z"/></g></svg></span>
										</div>
									</div>';
								break;
						}
						break;

					case 'on_demand';
						echo '<div class="php-snippet-options">
								<div class="execution-method">' . $execution_method_label . '</div>
							</div>';
						break;

					case 'via_secure_url';
						$secure_url = get_site_url() . '/?codex_token=' . $secure_url_token;

						echo '<div class="php-snippet-options">
								<div class="execution-method">' . $execution_method_label . '</div>
								<div class="secure-url-wrapper">
									<div class="the-secure-url" title="' . $secure_url . '">' . $secure_url . '</div>
									<span class="copy-secure-url-button" data-clipboard-text="' . $secure_url . '"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><g fill="none" stroke="#8C8F94" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"><path d="M16 3H4v13"/><path d="M8 7h12v12a2 2 0 0 1-2 2h-8a2 2 0 0 1-2-2z"/></g></svg></span>
								</div>
							</div>';
						break;
				}
				
			}
		}

		if ( 'description' === $column ) {
			$description = wp_strip_all_tags( get_post_meta( $post_id, 'code_snippet_description', true ) );
			$word_limit = 14;
			$description = implode(" ", array_slice( explode(" ", $description), 0, $word_limit ) );
			if ( ! empty( $description ) ) {
				$description .= '...';
			}
			echo $description;
		}

		if ( 'priority' === $column ) {
			if ( in_array( $options['language'], array( 'css', 'js', 'html' ) ) 
				|| 'on_page_load' == $execution_method 
			) {
				echo $options['priority'];			
			}
		}

		if ( 'asenha_code_snippet_category' === $column ) {
            $terms = get_the_terms( $post_id, $column );
	        $tax_terms = '';

            if ( ! empty( $terms ) ) {
                foreach ( $terms as $term ) {
                    $tax_terms .= '<a href="' . admin_url( 'edit.php?post_type=asenha_code_snippet&asenha_code_snippet_category=' . $term->slug ) . '">' . $term->name . '</a>, ';
                }

                $tax_terms = rtrim( $tax_terms, ', ' );
            }

            echo $tax_terms;
		}

		if ( 'published' === $column ) {
			$post = get_post( $post_id );

			if ( '0000-00-00 00:00:00' === $post->post_date ) {
				$t_time    = __( 'Unpublished' );
				$h_time    = $t_time;
				$time_diff = 0;
			} else {
				$time      = get_post_time( 'U', false, $post );
				$time_diff = time() - $time;

				if ( $time && $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
					/* translators: %s: Human-readable time difference. */
					$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
				} else {
					$h_time = get_the_time( __( 'Y/m/d' ), $post );
				}
			}

			 echo $h_time;
		}

		if ( 'csm_modified' === $column ) {
			$post = get_post( $post_id );

			if ( '0000-00-00 00:00:00' === $post->post_date ) {
				$t_time    = __( 'Unpublished' );
				$modified_time    = $t_time;
			} else {				
				$modified_time = get_the_modified_time( __( 'F j, Y' ), $post ) . ' at ' . get_the_modified_time( __( 'H:i' ), $post );
			}

			 echo $modified_time;
		}

		if ( 'active' === $column ) {
			$options = $this->get_options( $post_id );
			$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=csm_active_code&code_id=' . $post_id ), 'csm-active-code-' . $post_id );
			$spinner = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="#999" d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/><path fill="#999" d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z"><animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg>';
			if ( $this->is_active( $post_id ) ) {
				$active_title = __( 'The code is active. Click to deactivate it', 'admin-site-enhancements' );
				// https://icon-sets.iconify.design/la/toggle-on/
				$status_icon  = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-4.96 0-9 4.035-9 9s4.04 9 9 9h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm14 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7s3.121-7 7-7z"/></svg>';
			} else {
				$active_title = __( 'The code is inactive. Click to activate it', 'admin-site-enhancements' );
				// https://icon-sets.iconify.design/la/toggle-off/
				$status_icon  = '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="#999" d="M9 7c-.621 0-1.227.066-1.813.188a9.238 9.238 0 0 0-.875.218A9.073 9.073 0 0 0 .72 12.5c-.114.27-.227.531-.313.813A8.848 8.848 0 0 0 0 16c0 .93.145 1.813.406 2.656c.004.008-.004.024 0 .032A9.073 9.073 0 0 0 5.5 24.28c.27.114.531.227.813.313A8.83 8.83 0 0 0 9 24.999h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm0 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7c0-.242.008-.484.031-.719A6.985 6.985 0 0 1 9 9zm5.625 0H23c3.879 0 7 3.121 7 7s-3.121 7-7 7h-8.375C16.675 21.348 18 18.828 18 16c0-2.828-1.324-5.348-3.375-7z"/></svg>';
			}
			$error_indicator = '';
			if ( 'php' == $options['language'] ) {
				$has_error = get_post_meta( $post_id, 'php_snippet_has_error', true );
				if ( $has_error ) {
					$error_indicator = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 32 32"><circle cx="16" cy="16" r="8" fill="#d63638"/></svg>';
				} 
			}
			
			echo '<a href="' . esc_url( $url ) . '" class="csm_activate_deactivate snippet-status-icon-link" data-code-id="' . $post_id . '" title="' . $active_title . '"><span class="has-error">' . $error_indicator . '</span><span class="snippet-status">' . $status_icon . '<span class="snippet-status-spinner" style="display:none;">' . $spinner . '</span></span></a>';
		}
	}


	/**
	 * Make the 'Modified' column sortable
	 */
	function manage_edit_posts_sortable_columns( $columns ) {
		$columns['active']    		= 'active';
		$columns['type']      		= 'type';
		$columns['priority']      	= 'priority';
		$columns['csm_modified']  	= 'csm_modified';
		$columns['published'] 		= 'published';
		return $columns;
	}


	/**
	 * List table: Change the query in order to filter by code type
	 */
	function parse_query( $query ) {

		global $wpdb;
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return $query;
		}

		if ( ! isset( $query->query['post_type'] ) ) {
			return $query;
		}

		if ( 'asenha_code_snippet' !== $query->query['post_type'] ) {
			return $query;
		}

		$filter = filter_input( INPUT_GET, 'language_filter' );
		if ( ! is_string( $filter ) || strlen( $filter ) == 0 ) {
			return $query;
		}
		
		switch ( $filter ) {
			case 'php':
			case 'js':
			case 'html':
				$filter = '%' . $wpdb->esc_like( $filter ) . '%';
				$post_id_query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s";
				$post_ids = $wpdb->get_col( $wpdb->prepare( $post_id_query, 'options', $filter ) );
				break;
				
			case 'css':
				$filter = '%' . $wpdb->esc_like( $filter ) . '%';
				$filter_php = '%' . $wpdb->esc_like( 'php' ) . '%';
				$filter_js= '%' . $wpdb->esc_like( 'js' ) . '%';
				$filter_html = '%' . $wpdb->esc_like( 'html' ) . '%';
				$post_id_query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s AND meta_value NOT LIKE %s AND meta_value NOT LIKE %s AND meta_value NOT LIKE %s";
				$post_ids = $wpdb->get_col( $wpdb->prepare( $post_id_query, 'options', $filter, $filter_php, $filter_js, $filter_html ) );
				break;
		}

		if ( ! is_array( $post_ids ) || count( $post_ids ) == 0 ) {
			$post_ids = array( -1 );
		}
		$query->query_vars['post__in'] = $post_ids;

		return $query;
	}


	/**
	 * The "Publish"/"Update" button is missing if the "LH Archived Post Status" plugins is installed.
	 */
	function wp_statuses_get_supported_post_types( $post_types ) {
		unset( $post_types['asenha_code_snippet'] );
		return $post_types;
	}


	/**
	 * List table: add a filter by code type
	 */
	function restrict_manage_posts( $post_type ) {
		if ( 'asenha_code_snippet' !== $post_type ) {
			return;
		}
		
		// Output snippet types dropdown filter

		$languages = array(
			'css'  => __( 'CSS', 'admin-site-enhancements' ),
			'js'   => __( 'JS', 'admin-site-enhancements' ),
			'html' => __( 'HTML', 'admin-site-enhancements' ),
			'php' => __( 'PHP', 'admin-site-enhancements' ),
		);

		echo '<label class="screen-reader-text" for="code-snippets-manager-filter">' . esc_html__( 'Filter Code Type', 'admin-site-enhancements' ) . '</label>';
		echo '<select name="language_filter" id="code-snippets-manager-filter">';
		echo '<option  value="">' . __( 'All Types', 'admin-site-enhancements' ) . '</option>';
		foreach ( $languages as $_lang => $_label ) {
			$selected = selected( filter_input( INPUT_GET, 'language_filter' ), $_lang, false );
			echo '<option ' . $selected . ' value="' . $_lang . '">' . $_label . '</option>';
		}
		echo '</select>';

		// Output snippet categories dropdown filter

		$post_taxonomies = get_object_taxonomies( 'asenha_code_snippet', 'objects' );
		$snippet_category = $post_taxonomies['asenha_code_snippet_category'];

		wp_dropdown_categories( array(
			'show_option_all'	=> 'All Categories',
			'orderby'			=> 'name',
			'order'				=> 'ASC',
			'hide_empty'		=> false,
			'hide_if_empty'		=> true,
			'selected'			=> sanitize_text_field( ( isset( $_GET[$snippet_category->query_var] ) && ! empty( $_GET[$snippet_category->query_var] ) ) ? $_GET[$snippet_category->query_var] : '' ), 
			'hierarchical'		=> true,
			'name'				=> $snippet_category->query_var,
			'taxonomy'			=> $snippet_category->name,
			'value_field'		=> 'slug',
		) );
	}


	/**
	 * Order table by Type and Active columns
	 */
	function posts_orderby( $orderby, $query ) {
		if ( ! is_admin() ) {
			return $orderby;
		}
		global $wpdb;

		if ( 'asenha_code_snippet' === $query->get( 'post_type' ) && 'type' === $query->get( 'orderby' ) ) {
			$orderby = "REGEXP_SUBSTR( {$wpdb->prefix}postmeta.meta_value, 'js|html|css') " . $query->get( 'order' );
		}
		if ( 'asenha_code_snippet' === $query->get( 'post_type' ) && 'active' === $query->get( 'orderby' ) ) {
			$orderby = "coalesce( postmeta1.meta_value, 'p' ) " . $query->get( 'order' );
		}
		if ( 'asenha_code_snippet' === $query->get( 'post_type' ) && 'csm_modified' === $query->get( 'orderby' ) ) {
			$orderby = " {$wpdb->posts}.post_modified " . $query->get( 'order' );
		}
		return $orderby;
	}


	/**
	 * Order table by Type and Active columns
	 */
	function posts_join_paged( $join, $query ) {
		if ( ! is_admin() ) {
			return $join;
		}
		global $wpdb;

		if ( 'asenha_code_snippet' === $query->get( 'post_type' ) && 'type' === $query->get( 'orderby' ) ) {
			$join = "LEFT JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id";
		}

		if ( 'asenha_code_snippet' === $query->get( 'post_type' ) && 'active' === $query->get( 'orderby' ) ) {
			$join = "LEFT JOIN (SELECT post_id AS ID, meta_value FROM {$wpdb->prefix}postmeta WHERE meta_key = '_active' ) as postmeta1 USING( ID )";
		}
		return $join;
	}


	/**
	 * Order table by Type and Active columns
	 */
	function posts_where_paged( $where, $query ) {
		if ( ! is_admin() ) {
			return $where;
		}
		global $wpdb;

		if ( 'asenha_code_snippet' === $query->get( 'post_type' ) && 'type' === $query->get( 'orderby' ) ) {
			$where .= " AND {$wpdb->prefix}postmeta.meta_key = 'options'";
		}
		return $where;
	}

	/**
	 * Disable safe mode
	 */
	function wp_ajax_csm_disable_safe_mode() {
		 if ( isset( $_REQUEST ) && isset( $_REQUEST['code_id'] ) ) {
			$wp_config_options = array(
				'add'       => true, // Add the config if missing.
				'raw'       => true, // Display value in raw format without quotes.
				'normalize' => false, // Normalize config output using WP Coding Standards.
			);

			$wp_config = new ASENHA\Classes\WP_Config_Transformer;
			$success = $wp_config->update( 'constant', 'CSM_SAFE_MODE', 'false', $wp_config_options );
			if ( $success ) {
				sleep(2);
                echo json_encode( array( 
                    'success' => true 
                ) );
			} else {
                echo json_encode( array( 
                    'success' => false 
                ) );				
			}
		 }
	}

	/**
	 * Activate/deactivate a code
	 *
	 * @return void
	 */
	function wp_ajax_csm_active_code() {
		if ( ! isset( $_GET['code_id'] ) ) {
			die();
		}

		$code_id = absint( $_GET['code_id'] );

		$response = 'error';
		if ( check_admin_referer( 'csm-active-code-' . $code_id ) ) {

			if ( 'asenha_code_snippet' === get_post_type( $code_id ) ) {
				$active = get_post_meta( $code_id, '_active', true );
				$active = ( $active !== 'no' ) ? $active = 'yes' : 'no';

				update_post_meta( $code_id, '_active', $active === 'yes' ? 'no' : 'yes' );
				$this->rebuild_snippets_data();
			}
		}
		echo $active;

		die();
	}

	/**
	 * Check if a code is active
	 *
	 * @return bool
	 */
	function is_active( $post_id ) {
		return get_post_meta( $post_id, '_active', true ) !== 'no';
	}

	/**
	 * Reformat the `edit` screen
	 */
	function current_screen_edit() {
		?>
		<script type="text/javascript">
			 /* <![CDATA[ */
			jQuery(window).ready(function($){
				var h1 = '<?php _e( 'Code Snippets', 'admin-site-enhancements' ); ?> ';
				h1 += '<a href="post-new.php?post_type=asenha_code_snippet&language=css" class="page-title-action"><?php _e( 'Add CSS / SCSS Snippet', 'admin-site-enhancements' ); ?></a>';
				h1 += '<a href="post-new.php?post_type=asenha_code_snippet&language=js" class="page-title-action"><?php _e( 'Add JS Snippet', 'admin-site-enhancements' ); ?></a>';
				h1 += '<a href="post-new.php?post_type=asenha_code_snippet&language=html" class="page-title-action"><?php _e( 'Add HTML Snippet', 'admin-site-enhancements' ); ?></a>';
				h1 += '<a href="post-new.php?post_type=asenha_code_snippet&language=php" class="page-title-action"><?php _e( 'Add PHP Snippet', 'admin-site-enhancements' ); ?></a>';
				$("#wpbody-content h1").html(h1);
			});

		</script>
		<?php
	}


	/**
	 * Reformat the `post` screen
	 */
	function current_screen_post() {

		$this->remove_unallowed_metaboxes();

		$strings = array(
			'Add CSS / SCSS Snippet'   	=> __( 'Add CSS / SCSS Snippet', 'admin-site-enhancements' ),
			'Add JS Snippet'    		=> __( 'Add JS Snippet', 'admin-site-enhancements' ),
			'Add HTML Snippet'  		=> __( 'Add HTML Snippet', 'admin-site-enhancements' ),
			'Add PHP Snippet'  			=> __( 'Add PHP Snippet', 'admin-site-enhancements' ),
			'Edit CSS / SCSS Snippet'  	=> __( 'Edit CSS / SCSS Snippet', 'admin-site-enhancements' ),
			'Edit JS Snippet'   		=> __( 'Edit JS Snippet', 'admin-site-enhancements' ),
			'Edit HTML Snippet' 		=> __( 'Edit HTML Snippet', 'admin-site-enhancements' ),
			'Edit PHP Snippet' 			=> __( 'Edit PHP Snippet', 'admin-site-enhancements' ),
		);

		if ( isset( $_GET['post'] ) ) {
			$action  = 'Edit';
			$post_id = esc_attr( $_GET['post'] );
			// $snippet_status_class = ( $this->is_active( $post_id ) ) ? 'active' : 'inactive';
		} else {
			$action  = 'Add';
			$post_id = false;
		}
		$language = $this->get_language( $post_id );
		if ( 'css' == $language ) {
			$language = 'css / scss';
		}

		$title = $action . ' ' . strtoupper( $language ) . ' Snippet';
		$title = ( isset( $strings[ $title ] ) ) ? $strings[ $title ] : $strings['Add CSS / SCSS Snippet'];

		if ( $action == 'Edit' ) {
			// $title .= ' <span class="snippet-status ' . $snippet_status_class . '">' . $snippet_status . '</span>';
			// $title .= ' <a href="post-new.php?post_type=asenha_code_snippet&language=css" class="page-title-action">' . __( 'Add CSS Snippet', 'admin-site-enhancements' ) . '</a> ';
			// $title .= '<a href="post-new.php?post_type=asenha_code_snippet&language=js" class="page-title-action">' . __( 'Add JS Snippet', 'admin-site-enhancements' ) . '</a>';
			// $title .= '<a href="post-new.php?post_type=asenha_code_snippet&language=html" class="page-title-action">' . __( 'Add HTML Snippet', 'admin-site-enhancements' ) . '</a>';
			// $title .= '<a href="post-new.php?post_type=asenha_code_snippet&language=php" class="page-title-action">' . __( 'Add PHP Snippet', 'admin-site-enhancements' ) . '</a>';
		}

		?>
		<style type="text/css">
			#post-body-content, .edit-form-section { position: static !important; }
			#ed_toolbar { display: none; }
			#postdivrich { display: none; }
		</style>
		<script type="text/javascript">
			 /* <![CDATA[ */
			jQuery(window).ready(function($){
				$("#wpbody-content h1").html('<?php echo $title; ?>');
				$("#message.updated.notice").html('<p><?php _e( 'Snippet updated', 'admin-site-enhancements' ); ?></p>');

				var from_top = -$("#normal-sortables").height();
				if ( from_top != 0 ) {
					$(".csm_only_premium-first").css('margin-top', from_top.toString() + 'px' );
				} else {
					$(".csm_only_premium-first").hide();
				}
			});
			/* ]]> */
		</script>
		<?php
	}


	/**
	 * Remove unallowed metaboxes from code-snippets-manager edit page
	 *
	 * Use the code-snippets-manager-meta-boxes filter to add/remove allowed metaboxdes on the page
	 */
	function remove_unallowed_metaboxes() {
		global $wp_meta_boxes;

		// Side boxes
		$allowed = array( 
			'submitdiv', 
			'code-snippet-options', 
			'code-snippet-description', 
			'asenha_code_snippet_categorydiv' 
		);

		$allowed = apply_filters( 'asenha_code_snippet-meta-boxes', $allowed );

		foreach ( $wp_meta_boxes['asenha_code_snippet']['side'] as $_priority => $_boxes ) {
			foreach ( $_boxes as $_key => $_value ) {
				if ( ! in_array( $_key, $allowed ) ) {
					unset( $wp_meta_boxes['asenha_code_snippet']['side'][ $_priority ][ $_key ] );
				}
			}
		}

		// Normal boxes. vsm-post-meta is for Entity Viewer plugin's meta box.
		$allowed = array( 
			'slugdiv', 
			'previewdiv', 
			'url-rules', 
			'revisionsdiv', 
			'vsm-post-meta', 
			'code-snippet-options', 
			'code-snippet-description' 
		);

		$allowed = apply_filters( 'asenha_code_snippet-meta-boxes-normal', $allowed );

		if ( isset( $wp_meta_boxes['asenha_code_snippet']['normal'] ) ) {
			foreach ( $wp_meta_boxes['asenha_code_snippet']['normal'] as $_priority => $_boxes ) {
				foreach ( $_boxes as $_key => $_value ) {
					if ( ! in_array( $_key, $allowed ) ) {
						unset( $wp_meta_boxes['asenha_code_snippet']['normal'][ $_priority ][ $_key ] );
					}
				}
			}
		}

		// Advanced meta boxes.
		$allowed = array( 
			'code-snippet-options', 
			'code-snippet-description' 
		);

		$allowed = apply_filters( 'asenha_code_snippet-meta-boxes-advanced', $allowed );

		if ( isset( $wp_meta_boxes['asenha_code_snippet']['advanced'] ) ) {
			foreach ( $wp_meta_boxes['asenha_code_snippet']['advanced'] as $_priority => $_boxes ) {
				foreach ( $_boxes as $_key => $_value ) {
					if ( ! in_array( $_key, $allowed ) ) {
						unset( $wp_meta_boxes['asenha_code_snippet']['advanced'][ $_priority ][ $_key ] );
					}
				}
			}
		}

	}



	/**
	 * Add the codemirror editor in the `post` screen
	 */
	public function codemirror_editor( $post ) {

		$current_screen = get_current_screen();

		if ( $current_screen->post_type != 'asenha_code_snippet' ) {
			return false;
		}

		if ( empty( $post->post_title ) && empty( $post->post_content ) ) {
			$new_post = true;
			$post_id  = false;
			$options = array();
			$execution_method = '';
			$execution_location_type = '';
		} else {
			$new_post = false;
			if ( ! isset( $_GET['post'] ) ) {
				$_GET['post'] = $post->id;
			}
			$post_id = esc_attr( $_GET['post'] );

			$options = $this->get_options( $post->ID );
			$execution_method = isset( $options['execution_method'] ) ? $options['execution_method'] : 'on_page_load';
			$execution_location_type = isset( $options['execution_location_type'] ) ? $options['execution_location_type'] : 'hook';
		}
		$language = $this->get_language( $post_id );

        $extra_options = get_option( ASENHA_SLUG_U . '_extra', array() );
        $settings = isset( $extra_options['code_snippets_manager_settings'] ) ? $extra_options['code_snippets_manager_settings'] : array();

		// Replace the htmlentities (https://wordpress.org/support/topic/annoying-bug-in-text-editor/), but only selectively
		if ( isset( $settings['csm_htmlentities'] ) && $settings['csm_htmlentities'] == 1 && strstr( $post->post_content, '&' ) ) {

			// First the ampresands
			$post->post_content = str_replace( '&amp', htmlentities( '&amp' ), $post->post_content );

			// Then the rest of the entities
			$html_flags = defined( 'ENT_HTML5' ) ? ENT_QUOTES | ENT_HTML5 : ENT_QUOTES;
			$entities   = get_html_translation_table( HTML_ENTITIES, $html_flags );
			unset( $entities[ array_search( '&amp;', $entities ) ] );
			$regular_expression = str_replace( ';', '', '/(' . implode( '|', $entities ) . ')/i' );
			preg_match_all( $regular_expression, $post->post_content, $matches );
			if ( isset( $matches[0] ) && count( $matches[0] ) > 0 ) {
				foreach ( $matches[0] as $_entity ) {
					$post->post_content = str_replace( $_entity, htmlentities( $_entity ), $post->post_content );
				}
			}
		}

		// if ( isset( $settings['csm_htmlentities2'] ) && $settings['csm_htmlentities2'] == 1 ) {
			// this prevents certain PHP closing tag getting interspersed with / upon saving a snippet
			$post->post_content = str_replace( '?/>', htmlentities( '?>' ), $post->post_content );
			// e.g. this will prevent deletion of code section that starts with </textarea>
			$post->post_content = htmlentities( $post->post_content );
			// this ensures PHP closing tag are shown, saved and executed correctly
			$post->post_content = str_replace( htmlentities( '&gt;' ), '>', $post->post_content );
		// }

		switch ( $language ) {
			case 'js':
				$code_mirror_mode   = 'text/javascript';
				$code_mirror_before = '<script type="text/javascript">';
				$code_mirror_after  = '</script>';
				break;
			case 'html':
				$code_mirror_mode   = 'html';
				$code_mirror_before = '';
				$code_mirror_after  = '';
				break;
			case 'php':
				if ( $new_post ) {
					$post->post_content = '<?php' . PHP_EOL;
				}
				$code_mirror_mode   = 'php';
				$code_mirror_before = '';
				$code_mirror_after  = '';
				
				if ( ! $new_post ) {
					// Check if snippet has an error, or caused an error in the previous execution
					$php_snippet_has_error = get_post_meta( $post_id, 'php_snippet_has_error', true );
					$php_snippet_error_type = get_post_meta( $post_id, 'php_snippet_error_type', true );
					$php_snippet_error_message = get_post_meta( $post_id, 'php_snippet_error_message', true );
					$is_safe_mode_enabled = defined( 'CSM_SAFE_MODE' ) ? CSM_SAFE_MODE : false;

					if ( $php_snippet_has_error ) {
						$error_message_div = '<div class="php-snippet-error">';
										
						if ( 'fatal' == $php_snippet_error_type ) {
							$error_message_div .= '<div class="php-snippet-error-intro">' . __( 'This snippet caused the following <span class="php-error-status fatal">fatal error</span>:', 'admin-site-enhancements' ) . '</div>';
						} else {
							$error_message_div .= '<div class="php-snippet-error-intro">' . __( 'This snippet caused the following <span class="php-error-status non-fatal">non-fatal error</strong>:</span>', 'admin-site-enhancements' ) . '</div>';
						}
						
						$error_message_div .= '<div class="php-snippet-error-message">' . $php_snippet_error_message . '</div>';

						if ( 'fatal' == $php_snippet_error_type ) {
					        if ( 'on_page_load' == $execution_method 
					    		&& 'hook' == $execution_location_type
					    	) {
								$error_message_div .= '<div class="php-snippet-next-action">' . __( 'This has <strong>triggered safe mode</strong> to be enabled and all PHP snippets execution are currently stopped. Please <strong>fix the code</strong> causing the error, <strong>update</strong> the snippet, and only then, <strong>activate</strong> the snippet and <a id="disable-csm-safe-mode-link" href="#"><strong>disable safe mode</strong></a>.', 'admin-site-enhancements' ) . '</div>';
					    	} else {
								$error_message_div .= '<div class="php-snippet-next-action">' . __( 'The snippet has been deactivated. Please <strong>fix the code</strong> causing the error, then <strong>update and activate</strong> the snippet.', 'admin-site-enhancements' ) . '</div>';
					    	}
						} else {
					        if ( 'on_page_load' == $execution_method 
					    		&& 'hook' == $execution_location_type
					    	) {
								$error_message_div .= '<div class="php-snippet-next-action">' . __( 'Please <strong>fix the code</strong> causing the error, and <strong>update</strong> the snippet. If you plan on doing that later, you can <strong>deactivate</strong> the snippet for now so it will stop triggering the error.', 'admin-site-enhancements' ) . '</div>';
					    	} else {
								$error_message_div .= '<div class="php-snippet-next-action">' . __( 'Please <strong>fix the code</strong> causing the error, and then <strong>update and activate</strong> the snippet.', 'admin-site-enhancements' ) . '</div>';
					    	}
						}

						$error_message_div .= '</div>';
					} else {
						$error_message_div = '';
					}
				}

				break;
			default:
				$code_mirror_mode   = 'text/css';
				$code_mirror_before = '<style type="text/css">';
				$code_mirror_after  = '</style>';

		}
		
		?>
				<div class="code-mirror-buttons">
				<div class="button-left" id="csm-fullscreen-button" alt="<?php _e( 'Distraction-free writing mode', 'code-snippets-manager-pro' ); ?>"><span rel="tipsy" original-title="<?php _e( 'Fullscreen', 'code-snippets-manager-pro' ); ?>"><button role="presentation" type="button" tabindex="-1"><i class="csm-i-fullscreen"></i> <span>Go fullscreen</span></button></span></div>
				<div class="button-right"><!--<span rel="tipsy" original-title="<?php // _e( 'Beautify Code', 'code-snippets-manager-pro' ); ?>"><button type="button" tabindex="-1" id="csm-beautifier"><i class="csm-i-beautifier"></i></button></span>--></div>
				<!--<div class="button-left"><span rel="tipsy" original-title="<?php // _e( 'Editor Settings', 'code-snippets-manager-pro' ); ?>"><button type="button" tabindex="-1" id="csm-settings"><i class="csm-i-settings"></i></button></span></div>-->

<input type="hidden" name="fullscreen" id="csm-fullscreen-hidden" value="false" />
<!-- div class="button-right" id="csm-search-button" alt="Search"><button role="presentation" type="button" tabindex="-1"><i class="csm-i-find"></i></button></div -->

				</div>
				<?php 
				if ( isset( $error_message_div ) && 'php' == $language ) {
					echo $error_message_div;
				}

		        $options = get_option( ASENHA_SLUG_U, array() );
		        $editor_theme = isset( $options['code_snippets_editor_theme'] ) ? $options['code_snippets_editor_theme'] : 'dark';
		        switch ( $editor_theme ) {
		        	case 'dark':
		        		$additional_class = '';
		        		$base_style_css = 'color:#272822;background-color:#272822;border-left:1px solid #272822;border-right:1px solid #272822;';
		        		break;

		        	case 'light':
		        		$additional_class = ' light-theme';
		        		$base_style_css = 'color:#fefbf3;background-color:#fefbf3;border-left:1px solid #c3c4c7;border-right:1px solid #c3c4c7;';
		        		break;
		        }
				?>
				<div class="code-mirror-before <?php echo esc_attr( $language ); ?><?php echo esc_attr( $additional_class ); ?>"><div><?php echo htmlentities( $code_mirror_before ); ?></div></div>
				<textarea class="wp-editor-area" id="csm_content" mode="<?php echo htmlentities( $code_mirror_mode ); ?>" name="content" style="width:100%;min-height:500px;margin-top:0;<?php echo esc_attr( $base_style_css ); ?>border-radius:0;"><?php echo $post->post_content; ?></textarea>
				<div class="code-mirror-after <?php echo esc_attr( $language ); ?><?php echo esc_attr( $additional_class ); ?>"><div><?php echo htmlentities( $code_mirror_after ); ?></div></div>

				<table id="post-status-info"><tbody><tr>
					<td class="autosave-info">
					<span class="autosave-message">&nbsp;</span>
				<?php
				if ( 'auto-draft' != $post->post_status ) {
					echo '<span id="last-edit">';
					if ( $last_user = get_userdata( get_post_meta( $post->ID, '_edit_last', true ) ) ) {
						printf( __( 'Last edited by %1$s on %2$s at %3$s', 'code-snippets-manager-pro' ), esc_html( $last_user->display_name ), mysql2date( get_option( 'date_format' ), $post->post_modified ), mysql2date( get_option( 'time_format' ), $post->post_modified ) );
					} else {
						printf( __( 'Last edited on %1$s at %2$s', 'code-snippets-manager-pro' ), mysql2date( get_option( 'date_format' ), $post->post_modified ), mysql2date( get_option( 'time_format' ), $post->post_modified ) );
					}
					echo '</span>';
				}
				?>
					</td>
				</tr></tbody></table>


				<input type="hidden" id="update-post_<?php echo $post->ID; ?>" value="<?php echo wp_create_nonce( 'update-post_' . $post->ID ); ?>" />
		<?php

	}



	/**
	 * Show the options form in the `post` screen
	 * 
	 */
	function code_snippet_options_meta_box_callback( $post ) {

		$options = $this->get_options( $post->ID );
		if ( ! isset( $options['preprocessor'] ) ) {
			$options['preprocessor'] = 'none';
		}

		if ( isset( $_GET['language'] ) ) {
			$options['language'] = $this->get_language();
		}

		if ( ! isset( $options['compile_scss'] ) ) {
			$options['compile_scss'] = 'yes';
		}

		// For CSS and JS snippets
		$meta = $this->get_options_meta();
		if ( $options['language'] == 'css' ) {
			$meta['compile_scss'] = array(
				'title'   => __( 'Compile SCSS?', 'admin-site-enhancements' ),
				'type'    => 'radio',
				'default' => 'yes',
				'values'  => array(
					'yes' => array(
						'title'    => __( 'Yes', 'admin-site-enhancements' ),
						'dashicon' => 'media-code',
					),
					'no' => array(
						'title'    => __( 'No', 'admin-site-enhancements' ),
						'dashicon' => 'editor-alignleft',
					),
				),
			);
		}

		// For HTML snippets
		if ( $options['language'] == 'html' ) {
			$meta = $this->get_options_meta_html();
		}

		// For PHP snippets
		if ( $options['language'] == 'php' ) {
			$options['execution_location_type'] = ( ! isset( $options['execution_location_type'] ) ) ? 'hook' : $options['execution_location_type']; 
			$options['execution_location'] = ( ! isset( $options['execution_location'] ) ) ? 'plugins_loaded' : $options['execution_location']; 
			$options['execution_location_details'] = ( ! isset( $options['execution_location_details'] ) ) ? 'everywhere' : $options['execution_location_details']; 
			$meta = $this->get_options_meta_php();
		}

		$options['multisite'] = false;

		wp_nonce_field( 'options_save_meta_box_data', 'code-snippets-manager_meta_box_nonce' );

		?>
			<div class="options_meta_box <?php echo esc_html( $options['language'] ); ?>">
			<?php

			$output = '';

			foreach ( $meta as $_key => $a ) {
				$close_div = false;

				if ( ( $_key == 'preprocessor' && $options['language'] == 'css' ) 
					|| ( $_key == 'linking' && $options['language'] == 'html' ) 
					|| in_array( $_key, [ 'priority', 'minify', 'multisite' ] ) 
				) {
					$close_div = true;
					$output   .= '<div class="csm_opaque ' . $_key . '-wrapper' . '">';
				}

				// Don't show Pre-processors for JavaScript and PHP Codes
				if ( ( $options['language'] == 'js' && $_key == 'preprocessor' ) 
				|| ( $options['language'] == 'php' && $_key == 'preprocessor' )
				) {
					continue;
				}

				$output .= '<h3 class="' . $options['language'] . ' ' . $_key . '">' . esc_attr( $a['title'] ) . '</h3>' . PHP_EOL;

				$output .= $this->render_input( $_key, $a, $options );

				if ( $close_div ) {
					$output .= '</div>';
				}
			}

			echo $output;

			?>

			<input type="hidden" name="code_snippet_language" value="<?php echo $options['language']; ?>" />

			<div style="clear: both;"></div>

			</div>

			<?php
	}
	
	/**
	 * Add description meta box
	 * 
	 * @since 6.2.0
	 */
	function code_snippet_description_meta_box_callback( $post ) {
		$content = get_post_meta( $post->ID, 'code_snippet_description', true );
		$editor_settings = array(
			'wpautop' 			=> true,
			'media_buttons'		=> false,
			'tinymce'			=> true,
			'quicktags'			=> false,
			'teeny'				=> false, // minimal editor, less buttons/options in TinyMCE
			'drag_drop_upload'	=> false,
			'textarea_rows'		=> 4,
			'tinymce'			=> array(
				'toolbar1'		=> 'bold,italic,underline,strikethrough,forecolor,blockquote,bullist,numlist,link,unlink,indent,outdent,undo,redo,charmap,pastetext,removeformat,code,fullscreen',
				'content_css'	=> 	ASENHA_URL . 'includes/premium/code-snippets-manager/assets/csm_tinymce.css',
			),
		);
		wp_editor( $content, 'code_snippet_description', $editor_settings );
	}

	/**
	 * Get an array with all the information for building the code's options. For CSS / JS snippets.
	 */
	function get_options_meta() {
		$options = array(
			'linking'      => array(
				'title'   => __( 'Load snippet', 'admin-site-enhancements' ),
				'type'    => 'radio',
				'default' => 'internal',
				'values'  => array(
					'external' => array(
						'title'    => __( 'As a file', 'admin-site-enhancements' ),
						'dashicon' => 'media-code',
					),
					'internal' => array(
						'title'    => __( 'Inline', 'admin-site-enhancements' ),
						'dashicon' => 'editor-alignleft',
					),
				),
			),
			'type'         => array(
				'title'   => __( 'Position on page', 'admin-site-enhancements' ),
				'type'    => 'radio',
				'default' => 'header',
				'values'  => array(
					'header' => array(
						'title'    => __( '&lt;head&gt;', 'admin-site-enhancements' ),
						'dashicon' => 'arrow-up-alt2',
					),
					'footer' => array(
						'title'    => __( '&lt;footer&gt;', 'admin-site-enhancements' ),
						'dashicon' => 'arrow-down-alt2',
					),
				),
			),
			'side'         => array(
				'title'   => __( 'On which part of the site?', 'admin-site-enhancements' ),
				'type'    => 'checkbox',
				'default' => 'frontend',
				'values'  => array(
					'frontend' => array(
						'title'    => __( 'Frontend', 'admin-site-enhancements' ),
						'dashicon' => 'tagcloud',
					),
					'admin'    => array(
						'title'    => __( 'Admin', 'admin-site-enhancements' ),
						'dashicon' => 'id',
					),
					'login'    => array(
						'title'    => __( 'Login page', 'admin-site-enhancements' ),
						'dashicon' => 'admin-network',
					),
				),
			),
			'conditionals'		=> array(
				'title'		=> '',
				'type'		=> 'conditionals',
				'default'	=> '',
				'values'	=> array(),
			),
			'priority'		=> array(
				'title'   => __( 'Load priority / order', 'admin-site-enhancements' ),
				'type'    => 'number',
				'default' => 10,
			),
		);

		return $options;
	}


	/**
	 * Get an array with all the information for building the code's options. For HTML snippets.
	 */
	function get_options_meta_html() {
		$options = array(
			'type'         => array(
				'title'   => __( 'Position on page', 'admin-site-enhancements' ),
				'type'    => 'radio',
				'default' => 'header',
				'values'  => array(
					'header' => array(
						'title'    => __( '&lt;head&gt;', 'admin-site-enhancements' ),
						'dashicon' => 'arrow-up-alt2',
					),
					'footer' => array(
						'title'    => __( '&lt;footer&gt;', 'admin-site-enhancements' ),
						'dashicon' => 'arrow-down-alt2',
					),
				),
			),
			'side'     => array(
				'title'   => __( 'On which part of the site?', 'admin-site-enhancements' ),
				'type'    => 'checkbox',
				'default' => 'frontend',
				'values'  => array(
					'frontend' => array(
						'title'    => __( 'Frontend', 'admin-site-enhancements' ),
						'dashicon' => 'tagcloud',
					),
					'admin'    => array(
						'title'    => __( 'Admin', 'admin-site-enhancements' ),
						'dashicon' => 'id',
					),
				),
			),
			'conditionals'		=> array(
				'title'		=> '',
				'type'		=> 'conditionals',
				'default'	=> '',
				'values'	=> array(),
			),
			'priority'		=> array(
				'title'   => __( 'Load priority / order', 'admin-site-enhancements' ),
				'type'    => 'number',
				'default' => 10,
			),
		);

		if ( function_exists( 'wp_body_open' ) ) {
			$tmp = $options['type']['values'];
			unset( $options['type']['values'] );
			$options['type']['values']['header']    = $tmp['header'];
			$options['type']['values']['body_open'] = array(
				'title'    => __( '&lt;body&gt;', 'admin-site-enhancements' ),
				'dashicon' => 'editor-code',
			);
			$options['type']['values']['footer']    = $tmp['footer'];
		}

		return $options;
	}



	/**
	 * Get an array with all the information for building the code's options. For PHP snippets.
	 */
	function get_options_meta_php() {
		$options = array(
			'side'     => array(
				'title'   => __( 'On which part of the site?', 'admin-site-enhancements' ),
				'type'    => 'checkbox',
				'default' => 'sitewide',
				'values'  => array(
					'sitewide'    => array(
						'title'    => __( 'Everywhere', 'admin-site-enhancements' ),
						'dashicon' => 'id',
					),
				),
			),
			'execution_method'         => array(
				'title'   => __( 'How to execute', 'admin-site-enhancements' ),
				'type'    => 'select',
				'default' => 'on_page_load',
				'values'  => array(
					'on_page_load' 			=> __( 'Always (on page load)', 'admin-site-enhancements' ),
					'on_demand' 			=> __( 'Manually (on demand)', 'admin-site-enhancements' ),
					'via_secure_url' 		=> __( 'Via a secure URL', 'admin-site-enhancements' ),
				),
			),
			'on_demand_execution_button'	=> array(
				'title'		=> '',
				'type'		=> 'php_execution_button',
				'default'	=> '',
				'values'	=> array(),
			),
			'secure_url_token'         => array(
				'title'   => '',
				'type'    => 'secure_url',
				'default' => '',
				'values'  => array(),
			),
			'snippet_inactive_notes'     => array(
				'title'   => '',
				'type'    => 'html',
				'default' => '',
				'values'  => array(
					'notes'    => array(
						'title'    => '<div class="warning-note snippet-inactive-notes" style="display: none;">' . __( 'Please activate the snippet first before executing it.', 'admin-site-enhancements' ) . '</div>',
						'dashicon' => 'id',
					),
				),
			),
			'on_demand_execution_notes'     => array(
				'title'   => '',
				'type'    => 'html',
				'default' => '',
				'values'  => array(
					'notes'    => array(
						'title'    => '<div class="warning-note on-demand-execution-notes" style="display: none;">' . __( 'If the spinner keeps spinning, please manually reload the page to see the error that occurred during execution.', 'admin-site-enhancements' ) . '</div>',
						'dashicon' => 'id',
					),
				),
			),
			'execution_location_type'         => array(
				'title'   => __( 'Where to execute / insert', 'admin-site-enhancements' ),
				'type'    => 'radio',
				'default' => 'hook',
				'values'  => array(
					'hook' => array(
						'title'    => __( 'Hook', 'admin-site-enhancements' ),
						'dashicon' => 'arrow-up-alt2',
					),
					'shortcode' => array(
						'title'    => __( 'Shortcode', 'admin-site-enhancements' ),
						'dashicon' => 'arrow-down-alt2',
					),
				),
			),
			'execution_location'         => array(
				'title'   => '',
				'type'    => 'select',
				'default' => 'plugins_loaded',
				'values'  => array(
					'plugins_loaded' 		=> 'plugins_loaded (' . __( 'default', 'admin-site-enhancements' ) . ')',
					'after_setup_theme' 	=> 'after_setup_theme',
					'init' 					=> 'init',
					'wp_loaded' 			=> 'wp_loaded',
					'wp' 					=> 'wp',
				),
			),
			'execution_shortcode'         => array(
				'title'   => '',
				'type'    => 'php_shortcode',
				'default' => '',
				'values'  => array(),
			),
			'execution_location_details'         => array(
				'title'   => __( 'On which part of the site?', 'admin-site-enhancements' ),
				'type'    => 'select',
				'default' => 'everywhere',
				'values'  => array(
					'everywhere' => __( 'Everywhere / set in code', 'admin-site-enhancements' ),
					'admin' => __( 'Admin', 'admin-site-enhancements' ),
					'frontend' => __( 'Frontend', 'admin-site-enhancements' ),
					// 'frontend' => __( 'Some frontend pages', 'admin-site-enhancements' ),
				),
			),
			'priority'		=> array(
				'title'   => __( 'Execution priority / order', 'admin-site-enhancements' ),
				'type'    => 'number',
				'default' => 10,
			),
			// 'notes'     => array(
			// 	'title'   => '',
			// 	'type'    => 'html',
			// 	'default' => 'Some notes here...',
			// 	'values'  => array(
			// 		'notes'    => array(
			// 			'title'    => '<p>Use the proper condition(s) in your code for manual, fine-grained control. e.g. <code>is_admin()</code>, <code>is_single()</code>, etc.</p><p>When fatal error occurs and your site is not accessible, <a href="https://www.wpase.com/documentation/code-snippets-manager/" target="_blank">edit wp-config.php</a> to regain access.</p>',
			// 			'dashicon' => 'id',
			// 		),
			// 	),
			// ),
		);

		return $options;
	}
	
	/**
	 * Render the checkboxes, radios, selects and inputs
	 */
	function render_input( $_key, $a, $options ) {
		$language = $this->get_language();
		$execution_method = isset( $options['execution_method'] ) ? $options['execution_method'] : 'on_page_load';
		$secure_url_token = isset( $options['secure_url_token'] ) ? $options['secure_url_token'] : '';
		$priority = isset( $options['priority'] ) ? $options['priority'] : 10;
		
		$name   = 'code_snippet_' . $_key;
		$output = '';

		// Show radio type options
		if ( $a['type'] === 'radio' ) {
			$output .= '<div class="radio-group ' . $language . ' ' . $_key . '">' . PHP_EOL;
			foreach ( $a['values'] as $__key => $__value ) {
				$output   .= '<div class="radio-item">';
				$id        = $name . '-' . $__key;
				$dashicons = isset( $__value['dashicon'] ) ? 'dashicons-before dashicons-' . $__value['dashicon'] : '';
				$selected  = ( isset( $a['disabled'] ) && $a['disabled'] ) ? ' disabled="disabled"' : '';
				$selected .= ( $__key == $options[ $_key ] ) ? ' checked="checked" ' : '';
				$output   .= '<input type="radio" ' . $selected . 'value="' . $__key . '" name="' . $name . '" id="' . $id . '">' . PHP_EOL;
				$output   .= '<label class="' . $dashicons . '" for="' . $id . '"> ' . esc_attr( $__value['title'] ) . '</label><br />' . PHP_EOL;
				$output   .= '</div>';
			}
			$output .= '</div>' . PHP_EOL;
		}

		// Show checkbox type options
		if ( $a['type'] == 'checkbox' ) {
			$output .= '<div class="radio-group ' . $language . ' ' . $_key . '">' . PHP_EOL;
			if ( isset( $a['values'] ) && count( $a['values'] ) > 0 ) {
				$current_values = explode(',', $options[ $_key ] );
				foreach ( $a['values'] as $__key => $__value ) {
					$id        = $name . '-' . $__key;
					$dashicons = isset( $__value['dashicon'] ) ? 'dashicons-before dashicons-' . $__value['dashicon'] : '';
					$selected  = ( isset( $a['disabled'] ) && $a['disabled'] ) ? ' disabled="disabled"' : '';
					$selected .= ( in_array( $__key, $current_values ) ) ? ' checked="checked" ' : '';
					$output   .= '<input type="checkbox" ' . $selected . ' value="1" name="' . $id . '" id="' . $id . '">' . PHP_EOL;
					$output   .= '<label class="' . $dashicons . '" for="' . $id . '"> ' . esc_attr( $__value['title'] ) . '</label><br />' . PHP_EOL;
				}
			} else {
				$dashicons = isset( $a['dashicon'] ) ? 'dashicons-before dashicons-' . $a['dashicon'] : '';
				$selected  = ( isset( $options[ $_key ] ) && $options[ $_key ] == '1' ) ? ' checked="checked" ' : '';
				$selected .= ( isset( $a['disabled'] ) && $a['disabled'] ) ? ' disabled="disabled"' : '';
				$output   .= '<input type="checkbox" ' . $selected . ' value="1" name="' . $name . '" id="' . $name . '">' . PHP_EOL;
				$output   .= '<label class="' . $dashicons . '" for="' . $name . '"> ' . esc_attr( $a['title'] ) . '</label>' . PHP_EOL;
			}
			$output .= '</div>' . PHP_EOL;
		}

		// Show select type options
		if ( $a['type'] == 'select' ) {
			$output .= '<div class="radio-group ' . $language . ' ' . $_key . '">' . PHP_EOL;
			$output .= '<select name="' . $name . '" id="' . $name . '">' . PHP_EOL;
			foreach ( $a['values'] as $__key => $__value ) {
				$selected = ( isset( $options[ $_key ] ) && $options[ $_key ] == $__key ) ? ' selected="selected"' : '';
				$output  .= '<option value="' . $__key . '"' . $selected . '>' . esc_attr( $__value ) . '</option>' . PHP_EOL;
			}
			$output .= '</select>' . PHP_EOL;
			$output .= '</div>' . PHP_EOL;
		}

		// Show number type options
		if ( $a['type'] == 'number' ) {
			$id = 'code_snippet-' . $_key;
			$output .= '<div class="number ' . $language . ' ' . $_key . '">' . PHP_EOL;
			$output .= '<input type="number" id="' . $id . '" name="' . $name . '" value="' . $priority . '">';
			if ( 'php' == $language ) {
				$output .= '<span class="default faded">' . __( 'Default is 10. Lower number is executed earlier.', 'admin-site-enhancements' ) . '</span>';
			} else {
				$output .= '<span class="default faded">' . __( 'Default is 10. Lower number is loaded earlier.', 'admin-site-enhancements' ) . '</span>';
			}
			$output .= '</div>' . PHP_EOL;
		}

		// Show html
		if ( $a['type'] === 'html' ) {
			$output .= '<div class="html-description ' . $language . ' ' . $_key . '">' . PHP_EOL;
			foreach ( $a['values'] as $__key => $__value ) {
				$id        = $name . '-' . $__key;
				$dashicons = isset( $__value['dashicon'] ) ? 'dashicons-before dashicons-' . $__value['dashicon'] : '';
				$output   .= $__value['title'] . PHP_EOL;
			}
			$output .= '</div>' . PHP_EOL;
		}
		
		// Show execution button for PHP snippet with on_demand method
		if ( $a['type'] === 'php_execution_button' ) {
			if ( 'on_demand' === $execution_method ) {
				$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=execute_php_snippet_on_demand&snippet_id=' . get_the_ID() ), 'csm-execute-php-snippet-' . get_the_ID() );

				$output .= '<div class="php-execution-button-wrapper"><a class="button execute-php-snippet-button" data-php-snippet-id="' . get_the_ID() .'" href="' . esc_url( $url ) . '">' . __( 'Execute now', 'admin-site-enhancements' ) . '</a><span class="spinner"></span></div>';
			}
		}

		// Show execution button for PHP snippet with on_demand method
		if ( $a['type'] === 'secure_url' ) {
			if ( empty( $secure_url_token ) ) {
		        $plain_domain = str_replace( array( ".", "-", "_" ), "", sanitize_text_field( $_SERVER['SERVER_NAME'] ) ); // e.g. wwwgooglecom
		        $raw_token = str_rot13( $plain_domain . '__' . get_the_ID() );
				$secure_url_token = bin2hex( $raw_token );
			}
			$secure_url = get_site_url() . '/?codex_token=' . $secure_url_token;
			
			$id = 'code_snippet-' . $_key;
			
			$output .= '<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . esc_attr( $secure_url_token ) . '">';
			$output .= '<div class="secure-url-wrapper">';
			$output .= '<div class="the-secure-url" title="' . $secure_url . '">' . $secure_url . '</div>';
			$output .= '<a class="button copy-secure-url-button" data-clipboard-text="' . $secure_url . '">' . __( 'Copy', 'admin-site-enhancements' ) . '</a>';
			$output .= '</div>';
		}
		
		// Show shortcode with copy button
		if ( $a['type'] === 'php_shortcode' ) {
			$output .= '<div class="shortcode-wrapper">';
			$output .= '<div class="the-shortcode">[php_snippet id="' . get_the_ID() . '"]</div>';
			$output .= '<a class="button copy-shortcode-button" data-clipboard-text="' . get_the_ID() . '">' . __( 'Copy', 'admin-site-enhancements' ) . '</a>';
			$output .= '</div>';
		}

		// Show conditional logic
		if ( $a['type'] === 'conditionals' ) {
			$post_id = get_the_ID();

			$grouped_filter_params = [
				[
					'id'    => 'location',
					'title' => __( 'Location', 'admin-site-enhancements' ),
					'items' => [
						[
							'id'          => 'location-page-type', // Method is location_post_type()
							'title'       => __( 'Type of page', 'admin-site-enhancements' ),
							'type'        => 'select',
							'values'      => [
								'type'   => 'ajax',
								'action' => 'csm_ajax_get_page_type_list'
							],
							'description' => __( 'List of specific pages.', 'admin-site-enhancements' )
						],
						[
							'id'          => 'location-post-type', // Method is location_post_type()
							'title'       => __( 'Post type', 'admin-site-enhancements' ),
							'type'        => 'select',
							'values'      => [
								'type'   => 'ajax',
								'action' => 'csm_ajax_get_post_types'
							],
							'description' => __( 'A post type of the current page.', 'admin-site-enhancements' )
						],
						[
							'id'          => 'location-single-post', // Method is location_single_post()
							'title'       => __( 'Single page/post/CPT', 'admin-site-enhancements' ),
							'type'        => 'select',
							'values'      => [
								'type'   => 'ajax',
								'action' => 'csm_ajax_get_single_posts'
							],
							'description' => __( 'A particular page/post/CPT.', 'admin-site-enhancements' )
						],
						[
							'id'          => 'location-url', // Method is locatino_url()
							'title'       => __( 'URL', 'admin-site-enhancements' ),
							'type'        => 'text',
							'description' => __( 'An URL of the current page where a user who views your website is located.', 'admin-site-enhancements' )
						],
						[
							'id'          => 'location-taxonomy-type', // Method is location_taxonomy_type()
							'title'       => __( 'Taxonomy', 'admin-site-enhancements' ),
							'type'        => 'select',
							'values'      => [
								'type'   => 'ajax',
								'action' => 'csm_ajax_get_taxonomies_list'
							],
							'description' => __( 'List of taxonomies.', 'admin-site-enhancements' )
						],
						[
							'id'          => 'location-taxonomy-term', // Method is location_taxonomy_type()
							'title'       => __( 'Taxonomy term', 'admin-site-enhancements' ),
							'type'        => 'select',
							'values'      => [
								'type'   => 'ajax',
								'action' => 'csm_ajax_get_taxonomies_terms_list'
							],
							'description' => __( 'List of taxonomy terms.', 'admin-site-enhancements' )
						],
					]
				],
				[
					'id'    => 'user',
					'title' => __( 'User', 'admin-site-enhancements' ),
					'items' => [
						[
							'id'          => 'user-login-status', // Method is user_login_status()
							'title'       => __( 'Logged-in', 'admin-site-enhancements' ),
							'type'        => 'select',
							'values'	  => [
								[
									'value' => 'yes', 
									'title' => __( 'True', 'admin-site-enhancements' ) 
								],
								[ 
									'value' => 'no', 
									'title' => __( 'False', 'admin-site-enhancements' ) 
								],
							],
							'description' => __( 'List of user login status.', 'admin-site-enhancements' )
						],
						[
							'id'          => 'user-role', // Method is user_role()
							'title'       => __( 'User role', 'admin-site-enhancements' ),
							'type'        => 'select',
							'values'	  => [
								'type'   => 'ajax',
								'action' => 'csm_ajax_get_user_roles'
							],
						],
					]
				],
				[
					'id'    => 'technology',
					'title' => __( 'Technology', 'admin-site-enhancements' ),
					'items' => [
						[
							'id'          => 'location-device-type', // Method is location_post_type()
							'title'       => __( 'Device type', 'admin-site-enhancements' ),
							'type'        => 'select',
							'values'      => [
								'type'   => 'ajax',
								'action' => 'csm_ajax_get_device_type_list'
							],
							'description' => __( 'List of device types.', 'admin-site-enhancements' )
						],
					]
				],
			];

			$filterParams = [];

			foreach ( (array) $grouped_filter_params as $filter_group ) {
				$filterParams = array_merge( $filterParams, $filter_group['items'] );
			}

			ob_start()
			?>
	        <div class="csm-advanded-options" style="display:none;">
	            <div class="csm-empty" id="csm-advanced-visibility-options">
	                <script>
						window.csm = window.csm || {};
						window.csm.filtersParams = <?php echo json_encode( $filterParams ); ?>;
						window.csm.placeholderText = '<?php echo __( 'Select or search', 'admin-site-enhancements' ); ?>';
	                </script>

	                <div class="csm-editor-wrap">
	                    <div class="csm-when-empty">
							<?php _e( '<a href="#" class="csm-add-filter">Specify frontend location details &raquo;</a>', 'admin-site-enhancements' ) ?>
	                    </div>
	                    <div class="csm-filters"></div>
	                </div>

	                <div class="csm-filter csm-template">
	                    <div class="csm-head">
	                        <div class="csm-left">
	                        	<div class="csm-filter-heading">On the frontend:</div>
	                            <select class="csm-filter-type">
	                                <option value="showif"><?php _e( 'Load when', 'admin-site-enhancements' ); ?></option>
	                                <option value="hideif"><?php _e( 'Do not load when', 'admin-site-enhancements' ); ?></option>
	                            </select>
	                            <a href="#" class="button button-small btn-remove-filter">x</a>
	                        </div>
	                    </div>
	                    <div class="csm-box">
	                        <div class="csm-when-empty">
								<?php _e( 'No conditions specified. <a href="#" class="csm-link-add">Click here</a> to add one.', 'admin-site-enhancements' ) ?>
	                        </div>
	                        <div class="csm-conditions"></div>
	                    </div>
	                </div>

	                <div class="csm-scope csm-template">
	                    <div class="csm-and"><span><?php _e( 'and', 'admin-site-enhancements' ) ?></span></div>
	                </div>

	                <div class="csm-condition csm-template">
	                    <div class="csm-or"><?php _e( 'or', 'admin-site-enhancements' ) ?></div>
	                    <span class="csm-params">
	                        <select class="csm-param-select">
	                            <?php if ( ! empty( $grouped_filter_params ) ): ?>
		                            <?php foreach ( (array) $grouped_filter_params as $filterParam ) { ?>
	                                    <optgroup label="<?php echo $filterParam['title'] ?>">
	                                    <?php foreach ( $filterParam['items'] as $param ) { ?>
	                                        <option value="<?php echo $param['id'] ?>"<?php echo 'disabled' == $param['type'] ? ' disabled' : '' ?>>
	                                            <?php echo $param['title'] ?>
	                                        </option>
	                                    <?php } ?>
	                                </optgroup>
		                            <?php } ?>
	                            <?php endif; ?>
	                        </select>
<!-- 	                        <i class="csm-hint">
	                            <span class="csm-hint-icon"></span>
	                            <span class="csm-hint-content"></span>
	                        </i>
 -->	                    </span>
	                    <span class="csm-operators">
	                        <select class="csm-operator-select">
	                            <option value="equals"><?php _e( 'is', 'admin-site-enhancements' ) ?></option>
	                            <option value="notequal"><?php _e( 'is not', 'admin-site-enhancements' ) ?></option>
	                            <option value="in"><?php _e( 'is / one of', 'admin-site-enhancements' ) ?></option>
	                            <option value="notin"><?php _e( 'is not / not any of', 'admin-site-enhancements' ) ?></option>
	                            <option value="greater"><?php _e( 'greater than', 'admin-site-enhancements' ) ?></option>
	                            <option value="less"><?php _e( 'less than', 'admin-site-enhancements' ) ?></option>
	                            <option value="older"><?php _e( 'earlier than', 'admin-site-enhancements' ) ?></option>
	                            <option value="younger"><?php _e( 'later than', 'admin-site-enhancements' ) ?></option>
	                            <option value="contains"><?php _e( 'contains', 'admin-site-enhancements' ) ?></option>
	                            <option value="notcontain"><?php _e( 'does not contain', 'admin-site-enhancements' ) ?></option>
	                            <option value="between"><?php _e( 'between', 'admin-site-enhancements' ) ?></option>
	                        </select>
	                    </span>
	                    <span class="csm-value"></span>
	                    <span class="csm-controls">
	                        <div class="button-group">
	                            <a href="#" class="button button-small button-default csm-btn-or"><?php _e( 'OR', 'admin-site-enhancements' ) ?></a>
	                            <a href="#" class="button button-small button-default csm-btn-and"><?php _e( 'AND', 'admin-site-enhancements' ) ?></a>
	                            <a href="#" class="button button-small button-default csm-btn-remove">x</a>
	                        </div>
	                    </span>
	                </div>

					<?php 
						$changed_filters = get_post_meta( $post_id, 'code_snippet_changed_filters', true ); 
						$conditionals = isset( $options['conditionals'] ) ? $options['conditionals'] : array();
					?>
	                <input id="code_snippet_changed_filters" name="code_snippet_changed_filters" value="<?php echo empty( $changed_filters ) ? 0 : 1 ?>" type="hidden"/>
	                <input id="code_snippet_visibility_filters" name="code_snippet_filters" value='<?php echo json_encode( $conditionals ); ?>' type="hidden"/>
					<?php wp_nonce_field( 'code_snippet_' . $post_id . '_conditions_metabox', 'code_snippet_conditions_metabox_nonce' ) ?>
	            </div>
	        </div>
			<?php
			return ob_get_clean();
		}
		
		return $output;

	}

	/**
	 * Save the post and the metadata
	 */
	function options_save_meta_box_data( $post_id ) {

		// The usual checks
		if ( ! isset( $_POST['code-snippets-manager_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['code-snippets-manager_meta_box_nonce'], 'options_save_meta_box_data' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['post_type'] ) && 'asenha_code_snippet' != $_POST['post_type'] ) {
			return;
		}
		
		// Save snippet description
		
		if ( isset( $_POST['code_snippet_description'] ) ) {
			update_post_meta( $post_id, 'code_snippet_description', wp_kses_post( $_POST['code_snippet_description'] ) );
		}

		// Update the post's meta
		$defaults = array(
			'language' 						=> 'css',
			'linking'  						=> 'internal',
			'type'     						=> 'header',
			'side'     						=> '',
			'execution_method'				=> '',
			'secure_url_token'				=> '',
			'execution_location_type'		=> '',
			'execution_location'			=> '',
			'execution_location_details'	=> '',
			'conditionals'					=> '',
			'priority'						=> 10,
			'compile_scss' 					=> 'yes',
		);

		if ( $_POST['code_snippet_language'] == 'html' ) {
			$defaults = array(
				'language' 						=> 'html',
				'linking'  						=> 'both',
				'type'     						=> 'header',
				'side'     						=> '',
				'execution_method'				=> '',
				'secure_url_token'				=> '',
				'execution_location_type'		=> '',
				'execution_location'			=> '',
				'execution_location_details'	=> '',
				'conditionals'					=> '',
				'priority' 						=> 10,
				'compile_scss' 					=> 'no',
			);
		}

		if ( $_POST['code_snippet_language'] == 'php' ) {
			$defaults = array(
				'language' 						=> 'php',
				'linking'  						=> 'external',
				'type'     						=> 'none',
				'side'     						=> 'sitewide',
				'execution_method'				=> 'on_page_load',
				'secure_url_token'				=> '',
				'execution_location_type'		=> 'hook',
				'execution_location'			=> 'plugins_loaded',
				'execution_location_details'	=> 'everywhere',
				'conditionals'					=> '',
				'priority' 						=> 10,
				'compile_scss' 					=> 'no',
			);
		}

		foreach ( $defaults as $_field => $_default ) {
			$options[ $_field ] = isset( $_POST[ 'code_snippet_' . $_field ] ) ? esc_attr( strtolower( $_POST[ 'code_snippet_' . $_field ] ) ) : $_default;
		}

		$options['side'] = [];
		foreach ( ['frontend', 'admin', 'login', 'sitewide'] as $_side ) {
			if ( isset( $_POST[ 'code_snippet_side-' . $_side ] ) && $_POST[ 'code_snippet_side-' . $_side ] == '1' ) {
				$options['side'][] = $_side;
			}
		}
		// Set default for 'side' when neither frontend | admin | login | sitewide is selected
		if ( count( $options['side'] ) === 0 ) {
			if ( $_POST['code_snippet_language'] == 'php' ) {
				$options['side'] = ['sitewide'];			
			} else {
				// $options['side'] = ['frontend'];
				$options['side'] = '';
			}
		}
		if ( is_array( $options['side'] ) ) {
			$options['side'] = implode(',', $options['side'] );		
		} else {
			$options['side'] = '';
		}

		$options['language'] = in_array( $options['language'], array( 'html', 'css', 'js', 'php' ), true ) ? $options['language'] : $defaults['language'];

		// Update each snippet's Frontend Conditional Logic post meta
		// Ref: https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/admin/metaboxes/base-options.php#L881
		if ( isset( $_POST['code_snippet_filters'] ) ) {
			$options['conditionals'] = array( json_decode( stripslashes( $_POST['code_snippet_filters'] ) ) );			
		} else {
			$options['conditionals'] = array();
		}

		// Update each snippet's Options post meta
		update_post_meta( $post_id, 'options', $options );

		if ( $options['language'] == 'html' ) {
			$this->rebuild_snippets_data();
			return;
		}

		if ( $options['language'] == 'js' ) {
			// Replace the default comment
			if ( preg_match( '@/\* Add your JavaScript code here[\s\S]*?End of comment \*/@im', $_POST['content'] ) ) {
				$_POST['content'] = preg_replace( '@/\* Add your JavaScript code here[\s\S]*?End of comment \*/@im', '/* Default comment here */', $_POST['content'] );
			}

			// For other locales remove all the comments
			if ( substr( get_locale(), 0, 3 ) !== 'en_' ) {
				$_POST['content'] = preg_replace( '@/\*[\s\S]*?\*/@', '', $_POST['content'] );
			}
		}

		// Save the Code Snippet in a file in `wp-content/uploads/code-snippets`
		$before = '';
		$after  = '';

		if ( $options['linking'] == 'internal' ) {

			// $before = '<!-- start Code Snippets Manager -->' . PHP_EOL;
			// $before = '';
			// $after  = '<!-- end Code Snippets Manager -->' . PHP_EOL;
			// $after  = '';
			if ( $options['language'] == 'css' ) {
				$before .= '<style type="text/css">' . PHP_EOL;
				$after   = '</style>' . PHP_EOL . $after;
			}
			if ( $options['language'] == 'js' ) {
				if ( ! preg_match( '/<script\b[^>]*>([\s\S]*?)<\/script>/im', $_POST['content'] ) ) {
					$before .= '<script type="text/javascript">' . PHP_EOL;
					$after   = '</script>' . PHP_EOL . $after;
				} else {
					// the content has a <script> tag, then remove the comments so they don't show up on the frontend
					$_POST['content'] = preg_replace( '@/\*[\s\S]*?\*/@', '', $_POST['content'] );
				}
			}
		}

		if ( $options['linking'] == 'external' ) {
			$before = '/******* Do not edit this file *******' . PHP_EOL .
			'Code Snippets Manager' . PHP_EOL .
			'Saved: ' . date( 'M d Y | H:i:s' ) . ' */' . PHP_EOL;
			$after  = '';
		}
		
		if ( $options['language'] == 'php' ) {
			$before = '';
			$after = '';
		}
		// vi( $options );

		// Check if code-snippets directory exists. Create if non-existent.
		if ( ! is_dir( CSM_UPLOAD_DIR ) ) {
			wp_mkdir_p( CSM_UPLOAD_DIR );
		}

		// Check if code-snippets directory is writable.
		if ( wp_is_writable( CSM_UPLOAD_DIR ) ) {
			$file_name    = $post_id . '.' . $options['language'];
			
			if ( $options['language'] == 'css' ) {
				$compile_scss = isset( $options['compile_scss'] ) ? $options['compile_scss'] : 'yes';
				if ( 'yes' == $compile_scss ) {
					// Try to compile SCSS if it's part of the CSS code
					try {
						$code_snippet = $this->scss_compiler( stripslashes( $_POST['content'] ) );
					} catch ( Exception $e ) {
						$code_snippet = stripslashes( $_POST['content'] );
					}
				} else {
					$code_snippet = stripslashes( $_POST['content'] );
				}
			} else {
				$code_snippet = stripslashes( $_POST['content'] );
			}
						
			$file_content = $before . $code_snippet . $after;
			@file_put_contents( CSM_UPLOAD_DIR . '/' . $file_name, $file_content );
		}

		$this->rebuild_snippets_data();
	}
	
	/**
	 * Make sure PHP snippets begin with the PHP opening tag
	 * 
	 * @since 7.8.13
	 */
	public function process_php_snippet( $data, $postarr ) {
		// Let's limit post content modification only for PHP code snippets
		if ( isset( $data['post_type'] )
		&& 'asenha_code_snippet' == $data['post_type'] 
		&& isset( $postarr['code_snippet_language'] ) 
		&& 'php' == $postarr['code_snippet_language'] ) {
			// Remove empty lines at the upper part of the post content
			$post_content = $data['post_content'];
			$post_content = preg_replace( '/^\s*\n/', '', $post_content );

			// Make sure the first five characters is the PHP opening tag
			$first_five_characters = substr( $post_content, 0, 5 );			
			if ( '<?php' !== $first_five_characters ) {
				$post_content = '<?php' . PHP_EOL . $post_content;
			}

			$data['post_content'] = $post_content;
		}

		return $data;
	}
	
	/**
	 * Restore the revision in the code snippet file
	 * 
	 * @since 7.1.5
	 */
	public function restore_snippet_revision_in_file( $post_id, $revision_id ) {
		if ( wp_is_writable( CSM_UPLOAD_DIR ) ) {
			$options = get_post_meta( $post_id, 'options', true );

			if ( isset( $options['language'] ) ) {
				$file_path = CSM_UPLOAD_DIR . '/' . $post_id . '.' . $options['language'];

				if ( is_file( $file_path ) ) {
					$post = get_post( $post_id );
					$post_content = $post->post_content;
					
					if ( 'css' == $options['language'] ) {
						// Try to compile SCSS if it's part of the CSS code
						$code_snippet = $this->scss_compiler( stripslashes( $post_content ) );
					} else {
						$code_snippet = stripslashes( $post_content );
					}
					
					$options = get_post_meta( $post_id, 'options', true );

					if ( $options['linking'] == 'internal' ) {

						// $before = '<!-- start Code Snippets Manager -->' . PHP_EOL;
						$before = '';
						// $after  = '<!-- end Code Snippets Manager -->' . PHP_EOL;
						$after  = '';
						if ( $options['language'] == 'css' ) {
							$before .= '<style type="text/css">' . PHP_EOL;
							$after   = '</style>' . PHP_EOL . $after;
						}
						if ( $options['language'] == 'js' ) {
							if ( ! preg_match( '/<script\b[^>]*>([\s\S]*?)<\/script>/im', $post_content ) ) {
								$before .= '<script type="text/javascript">' . PHP_EOL;
								$after   = '</script>' . PHP_EOL . $after;
							} else {
								// the content has a <script> tag, then remove the comments so they don't show up on the frontend
								$post_content = preg_replace( '@/\*[\s\S]*?\*/@', '', $post_content );
							}
						}
					}

					if ( $options['linking'] == 'external' ) {
						$before = '/******* Do not edit this file *******' . PHP_EOL .
						'Code Snippets Manager' . PHP_EOL .
						'Saved: ' . date( 'M d Y | H:i:s' ) . ' */' . PHP_EOL;
						$after  = '';
					}
					
					if ( $options['language'] == 'php' ) {
						$before = '';
						$after = '';
					}

					$file_content = $before . $code_snippet . $after;
					@file_put_contents( $file_path, $file_content );
				}				
			}
		}

		$this->rebuild_snippets_data();
	}
	
	/**
	 * SCSS Compiler Function
	 * 
	 * @since 6.3.0
	 */
	public function scss_compiler( $scss ) {

		// SCSS compiler
		// $compiler = new Compiler();
		$compiler = new \ScssPhp\ScssPhp\Compiler();
		$compiled_css = $compiler->compileString( $scss )->getCss();
		return $compiled_css;

	}
	

	/**
	 * Create the code-snippets-manager dir in uploads directory
	 *
	 * Show a message if the directory is not writable
	 *
	 * Create an empty index.php file inside
	 */
	function create_uploads_directory() {
		global $pagenow, $typenow;
		
		if ( 'post.php' != $pagenow && 'asenha_code_snippet' != $typenow ) {
			return false;			
		}

		$dir = CSM_UPLOAD_DIR;

		// Create the dir if it doesn't exist
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		// Show a message if it couldn't create the dir
		if ( ! file_exists( $dir ) ) :
			?>
			 <div class="notice notice-error is-dismissible">
			 <p><?php 
			 printf( 
			 	/* translators: %s is directory slug 'code-snippets-manager' */
			 	__( 'The %s directory could not be created', 'admin-site-enhancements' ), 
			 	'<b>code-snippets-manager</b>' 
			 	); 
			 	?>
		 	</p>
			 <p><?php _e( 'Please run the following commands in order to make the directory', 'admin-site-enhancements' ); ?>: <br /><strong>mkdir <?php echo $dir; ?>; </strong><br /><strong>chmod 777 <?php echo $dir; ?>;</strong></p>
			</div>
			<?php
			return;
endif;

		// Show a message if the dir is not writable
		if ( ! wp_is_writable( $dir ) ) :
			?>
			 <div class="notice notice-error is-dismissible">
			 <p><?php 
			 	printf( 
			 		/* translators: %s is the directory path */
			 		__( 'The %s directory is not writable, therefore the code snippet files cannot be saved.', 'admin-site-enhancements' ), 
			 		'<b>' . $dir . '</b>' 
			 		); ?></p>
			 <p><?php _e( 'Please run the following command to make the directory writable', 'admin-site-enhancements' ); ?>:<br /><strong>chmod 777 <?php echo $dir; ?> </strong></p>
			</div>
			<?php
			return;
endif;

		// Write a blank index.php
		if ( ! file_exists( $dir . '/index.php' ) ) {
			$content = '<?php' . PHP_EOL . '// Silence is golden.';
			@file_put_contents( $dir . '/index.php', $content );
		}
	}


	/**
	 * Build an array where you can quickly find the data related to active snippets
	 *
	 * @return void
	 */
	public function rebuild_snippets_data() {

		// Retrieve all the code-snippets-manager codes
		$posts = query_posts( 'post_type=asenha_code_snippet&post_status=publish&nopaging=true' );
		
		$active_snippets = array();

		foreach ( $posts as $_post ) {

			// Skip snippets that are not active
			if ( ! $this->is_active( $_post->ID ) ) {
				continue;
			}

			$options = $this->get_options( $_post->ID );

			// Example:
			// $options = array(
			// 	'language' => 'php', // css | js | html | php
			// 	'linking'  => 'external', // external (as a file) | internal (inline)
			// 	'type'     => 'none', // none | header | body_open | footer
			// 	'side'     => 'sitewide', // sitewide &| frontend &| admin &| login, comma-separated, e.g. frontend,admin
			//  'execution_location'	   => 'plugins_loaded', // which hook to use to execute PHP snippets.
			// 	'priority' => 10, // between 1 to 9999
			// );				

			$language = $options['language'];
			$load_type = $options['linking'];
			$position_on_page = $options['type']; // header | body_open | footer
			$location = explode( ',', $options['side'] ); // e.g. 'frontend,admin' --> array( 'frontend', 'admin' )

			switch ( $language ) {
				case 'css';
				case 'js';
				case 'html';
					$execution_method = '';
					$secure_url_token = '';
					$execution_location_type = '';
					$execution_location = '';
					$execution_location_details = '';
					$conditionals = isset( $options['conditionals'] ) ? $options['conditionals'] : array();
					break;
				
				case 'php';
					$execution_method = isset( $options['execution_method'] ) ? $options['execution_method'] : 'on_page_load';
					$secure_url_token = isset( $options['secure_url_token'] ) ? $options['secure_url_token'] : '';
					$execution_location_type = isset( $options['execution_location_type'] ) ? $options['execution_location_type'] : 'hook';
					$execution_location = isset( $options['execution_location'] ) ? $options['execution_location'] : 'plugins_loaded';
					$execution_location_details = isset( $options['execution_location_details'] ) ? $options['execution_location_details'] : 'everywhere';
					$conditionals = '';
					break;
			}

			$priority = $options['priority'];
			$compile_scss = isset( $options['compile_scss'] ) ? $options['compile_scss'] : 'yes';

			$filename = $_post->ID . '.' . $language; // e.g. 6123.php

			// Add version number to filename. For cache busting.
			if ( $options['linking'] == 'external' 
				&& $options['language'] != 'php' 
			) {
				$filename .= '?v=' . rand( 1, 10000 ); // e.g. 6123.css?v=1234
			}
			
			$active_snippets[$language][$_post->ID]['id'] = $_post->ID;
			$active_snippets[$language][$_post->ID]['filename'] = $filename;
			$active_snippets[$language][$_post->ID]['title'] = get_the_title( $_post->ID );
			$active_snippets[$language][$_post->ID]['load_type'] = $load_type;
			$active_snippets[$language][$_post->ID]['position_on_page'] = $position_on_page;
			$active_snippets[$language][$_post->ID]['location'] = $location;
			$active_snippets[$language][$_post->ID]['execution_method'] = $execution_method;
			$active_snippets[$language][$_post->ID]['secure_url_token'] = $secure_url_token;
			$active_snippets[$language][$_post->ID]['execution_location_type'] = $execution_location_type;
			$active_snippets[$language][$_post->ID]['execution_location'] = $execution_location;
			$active_snippets[$language][$_post->ID]['execution_location_details'] = $execution_location_details;
			$active_snippets[$language][$_post->ID]['conditionals'] = $conditionals;
			$active_snippets[$language][$_post->ID]['priority'] = $priority;
			$active_snippets[$language][$_post->ID]['compile_scss'] = $compile_scss;

			// Mark to enqueue the jQuery library, if necessary
			if ( 'js' === $language ) {
				$_post->post_content = preg_replace( '@/\* Add your JavaScript code here[\s\S]*?End of comment \*/@im', '/* Default comment here */', $_post->post_content );
				if ( preg_match( '/jquery\s*(\(|\.)/i', $_post->post_content ) && ! isset( $active_snippets['jquery'] ) ) {
					$active_snippets['jquery'] = true;
				}
			}

		}
		
		// Let's reorder snippets by priority
		$active_snippets_by_type_and_priority = array();
		$active_snippets_by_type_and_priority_sorted_by_priority = array();
		$active_snippets_sorted_by_priority = array();

		// We get an array of snippet_id => priority for each type
		foreach ( $active_snippets as $type => $snippets ) {
			if ( is_array( $snippets ) ) {
				foreach ( $snippets as $snippet_id => $snippet_info ) {
					$active_snippets_by_type_and_priority[$type][$snippet_id] = $snippet_info['priority'];
				}				
			} else {
				$active_snippets_by_type_and_priority[$type] = $snippets; // jQuery => true | false
			}
		}
		
		// We get an array of snippet_id => priority for each type, ordered by priority
		foreach ( $active_snippets_by_type_and_priority as $type => $active_snippets_by_type ) {
			// only sort the snippets array, not the jquery node
			if ( is_array( $active_snippets_by_type ) ) {
				asort( $active_snippets_by_type );			
			}
			$active_snippets_by_type_and_priority_sorted_by_priority[$type] = $active_snippets_by_type;
		}
		
		// We sort the main active snippets array based on the array above, already ordered by priority
		foreach ( $active_snippets_by_type_and_priority_sorted_by_priority as $snippet_sorted_type => $snippets_sorted ) {
			if ( is_array( $snippets_sorted ) ) {
				foreach ( $snippets_sorted as $snippet_sorted_id => $snippet_priority ) {
					foreach ( $active_snippets as $type => $snippets ) {
						if ( $snippet_sorted_type == $type ) {
							if ( is_array( $snippets ) ) {
								foreach ( $snippets as $snippet_id => $snippet_info ) {
									if ( $snippet_sorted_id == $snippet_id ) {
										$active_snippets_sorted_by_priority[$snippet_sorted_type][$snippet_sorted_id] = $snippet_info;
									}
								}
							}
						}
					}
				}				
			} else {
				$active_snippets_sorted_by_priority[$snippet_sorted_type] = $snippets_sorted; // jQuery => true | false
			}
		}

		// Save the snippets data in the database
		$extra_options = get_option( ASENHA_SLUG_U . '_extra', array() );
		$extra_options['code_snippets'] = $active_snippets_sorted_by_priority;
		update_option( ASENHA_SLUG_U. '_extra', $extra_options, true );
			
	}

	/**
	 * Rebuilt the tree when you trash a custom code
	 */
	function trash_post( $post_id ) {
		update_post_meta( $post_id, '_active', 'no' );
		$this->rebuild_snippets_data();
	}

	/**
	 * Rebuilt the tree when you restore a custom code
	 */
	function untrash_post( $post_id ) {
		$this->rebuild_snippets_data();
	}

	/**
	 * Delete the snippet from the filesystem when deleting the snippet post
	 */
	function delete_post( $post_id, $post ) {		
		if ( wp_is_writable( CSM_UPLOAD_DIR ) ) {
			$file_types = array( 'css', 'js', 'html', 'php' );
			foreach ( $file_types as $file_type ) {
				if ( is_file( CSM_UPLOAD_DIR . '/' . $post_id . '.' . $file_type ) ) {
					wp_delete_file( CSM_UPLOAD_DIR . '/' . $post_id . '.' . $file_type );					
				}
			}

			$this->rebuild_snippets_data();
		}
	}

	/**
	 * Get the language for the current post
	 */
	function get_language( $post_id = false ) {
		if ( $post_id == false ) {
			if ( isset( $_GET['post'] ) ) {
				$post_id = intval( $_GET['post'] );
			}
		}
		if ( $post_id !== false ) {
			$options  = $this->get_options( $post_id );
			$language = $options['language'];
		} else {
			$language = isset( $_GET['language'] ) ? esc_attr( strtolower( $_GET['language'] ) ) : 'css';
		}
		if ( ! in_array( $language, array( 'css', 'js', 'html', 'php' ) ) ) {
			$language = 'css';
		}

		return $language;
	}


	/**
	 * Show the activate/deactivate link in the row's action area
	 */
	function post_row_actions( $actions, $post ) {
		if ( 'asenha_code_snippet' !== $post->post_type ) {
			return $actions;
		}

		$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=csm_active_code&code_id=' . $post->ID ), 'csm-active-code-' . $post->ID );
		if ( $this->is_active( $post->ID ) ) {
			$active_title = __( 'The code is active. Click to deactivate it', 'admin-site-enhancements' );
			$active_text  = __( 'Deactivate', 'admin-site-enhancements' );
		} else {
			$active_title = __( 'The code is inactive. Click to activate it', 'admin-site-enhancements' );
			$active_text  = __( 'Activate', 'admin-site-enhancements' );
		}
		$actions['activate'] = '<a href="' . esc_url( $url ) . '" title="' . $active_title . '" class="csm_activate_deactivate" data-code-id="' . $post->ID . '">' . $active_text . '</a>';

		return $actions;
	}


	/**
	 * Show the activate/deactivate link in admin.
	 */
	public function post_submitbox_start() {
		global $post;

		if ( ! is_object( $post ) ) {
			return;
		}

		if ( 'asenha_code_snippet' !== $post->post_type ) {
			return;
		}

		if ( ! isset( $_GET['post'] ) ) {
			return;
		}

		$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=csm_active_code&code_id=' . $post->ID ), 'csm-active-code-' . $post->ID );

		if ( $this->is_active( $post->ID ) ) {
			// https://icon-sets.iconify.design/zondicons/checkmark/
			$icon   = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20"><path fill="currentColor" d="m0 11l2-2l5 5L18 3l2 2L7 18z"/></svg>';
			$text   = __( 'Active', 'admin-site-enhancements' );
			$action = __( 'Deactivate', 'admin-site-enhancements' );
			$status_class = 'active';
		} else {
			$icon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20"><path fill="currentColor" d="M10 0C4.478 0 0 4.478 0 10s4.478 10 10 10s10-4.478 10-10S15.522 0 10 0Zm0 18.304A8.305 8.305 0 0 1 3.56 4.759l11.681 11.68A8.266 8.266 0 0 1 10 18.305Zm6.44-3.063L4.759 3.561a8.305 8.305 0 0 1 11.68 11.68Z"/></svg>';
			$text   = __( 'Inactive', 'admin-site-enhancements' );
			$action = __( 'Activate', 'admin-site-enhancements' );
			$status_class = 'inactive';
		}
		?>
		<div id="activate-action"><span id="snippet-status" class="snippet-status <?php echo esc_attr( $status_class ); ?>" data-snippet-status="<?php echo esc_attr( $status_class ); ?>" style="font-weight: 600;">
		<?php echo $icon; ?>
		<?php echo $text; ?></span>
		<a class="button button-small csm_activate_deactivate" data-code-id="<?php echo $post->ID; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo $action; ?></a>
		</div>
		<?php
	}


	/**
	 * Show the Permalink edit form
	 */
	public function edit_form_before_permalink( $filename = '', $permalink = '', $filetype = 'css' ) {
		if ( isset( $_GET['language'] ) ) {
			$filetype = strtolower(trim($_GET['language']));
		}

		if ( ! in_array( $filetype, array( 'css', 'js', 'php' ) ) ) {
			return;
		}

		if ( ! is_string( $filename ) ) {
			global $post;
			if ( ! is_object( $post ) ) {
				return;
			}
			if ( 'asenha_code_snippet' !== $post->post_type ) {
				return;
			}

			$post    = $filename;
			$slug    = get_post_meta( $post->ID, '_slug', true );
			$options = get_post_meta( $post->ID, 'options', true );

			if ( is_array( $options ) && isset( $options['language'] ) ) {
				$filetype = $options['language'];
			}
			if ( $filetype === 'html' ||  $filetype === 'php' ) {
				return;
			}
			if ( ! @file_exists( CSM_UPLOAD_DIR . '/' . $slug . '.' . $filetype ) ) {
				$slug = false;
			}
			$filename = ( $slug ) ? $slug : $post->ID;
		}

		if ( empty( $permalink ) ) {
			$permalink = CSM_UPLOAD_URL . '/' . $filename . '.' . $filetype;
		}

		?>
		<div class="inside">
			<?php if ( $filetype === 'css' || $filetype === 'js' ) : ?>
			<div id="edit-slug-box" class="hide-if-no-js">
				<strong>Permalink:</strong>
				<span id="sample-permalink"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( CSM_UPLOAD_URL ) . '/'; ?><span id="editable-post-name"><?php echo esc_html( $filename ); ?></span>.<?php echo esc_html( $filetype ); ?></a></span>
				&lrm;<span id="csm-edit-slug-buttons"><button type="button" class="csm-edit-slug button button-small hide-if-no-js" aria-label="Edit permalink">Edit</button></span>
				<span id="editable-post-name-full" data-filetype="<?php echo $filetype; ?>"><?php echo esc_html( $filename ); ?></span>
			</div>
			<?php endif; ?>
			<?php wp_nonce_field( 'csm-permalink', 'csm-permalink-nonce' ); ?>
		</div>
		<?php
	}


	/**
	 * AJAX save the Permalink slug
	 */
	public function wp_ajax_csm_permalink() {

		if ( ! isset( $_POST['csm_permalink_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['csm_permalink_nonce'], 'csm-permalink' ) ) {
			return;
		}

		$code_id   = isset( $_POST['code_id'] ) ? intval( $_POST['code_id'] ) : 0;
		$permalink = isset( $_POST['permalink'] ) ? $_POST['permalink'] : null;
		$slug      = isset( $_POST['new_slug'] ) ? trim( sanitize_file_name( $_POST['new_slug'] ) ) : null;
		$filetype  = isset( $_POST['filetype'] ) ? $_POST['filetype'] : 'css';
		if ( empty( $slug ) ) {
			$slug = (string) $code_id;
		} else {
			update_post_meta( $code_id, '_slug', $slug );
		}
		$this->edit_form_before_permalink( $slug, $permalink, $filetype );

		wp_die();
	}

	/**
	 * Returns a list of page list values
	 * 
	 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/admin/ajax/ajax.php#L126
	 * @since 7.6.7
	 */
	public function csm_ajax_get_page_type_list() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		$snippet_id = intval( $_REQUEST['snippet_id'] );

		check_admin_referer( 'code_snippet_' . $snippet_id . '_conditions_metabox' );

		$result = [
			'values' => [
				[
					'value' => 'is_front_page',
					'title' => __( 'Homepage', 'admin-site-enhancements' ),
				],
				[
					'value' => 'is_home',
					'title' => __( 'Blog / posts page', 'admin-site-enhancements' ),
				],
				[
					'value' => 'is_singular',
					'title' => __( 'Single post/page/CPT', 'admin-site-enhancements' ),
				],
				[
					'value' => 'is_author',
					'title' => __( 'Author archive', 'admin-site-enhancements' ),
				],
				[
					'value' => 'is_date',
					'title' => __( 'Date archive', 'admin-site-enhancements' ),
				],
				[
					'value' => 'is_archive',
					'title' => __( 'Archive', 'admin-site-enhancements' ),
				],
				[
					'value' => 'is_search',
					'title' => __( 'Search page', 'admin-site-enhancements' ),
				],
				[
					'value' => 'is_404',
					'title' => __( '404 page', 'admin-site-enhancements' ),
				],
			],
		];

		echo json_encode( $result );
		exit;
	}
	
	/**
	 * Returns a list of device type values
	 * 
	 * @since 7.8.10
	 */
	public function csm_ajax_get_device_type_list() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		$snippet_id = intval( $_REQUEST['snippet_id'] );

		check_admin_referer( 'code_snippet_' . $snippet_id . '_conditions_metabox' );

		$result = [
			'values' => [
				[
					'value' => 'desktop',
					'title' => __( 'Desktop', 'admin-site-enhancements' ),
				],
				[
					'value' => 'tablet',
					'title' => __( 'Tablet', 'admin-site-enhancements' ),
				],
				[
					'value' => 'mobile',
					'title' => __( 'Mobile', 'admin-site-enhancements' ),
				],
			],
		];

		echo json_encode( $result );
		exit;
	}

	/**
	 * Returns a list of public post types.
	 * 
	 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/admin/ajax/ajax.php#L57
	 * @since 7.6.7
	 */
	public function csm_ajax_get_post_types() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		$snippet_id = intval( $_REQUEST['snippet_id'] );

		check_admin_referer( 'code_snippet_' . $snippet_id . '_conditions_metabox' );

		$values_simple = [];
		$values     = [];

		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $value ) {
				$values_simple[] = $value->label . ' (' . $value->name . ')';
				$values[] = [
					'value' => $key,
					'title' => $value->label . ' (' . $value->name . ')',
				];
			}
			
			// Let's sort by label
			asort( $values_simple ); // sort by value, ascending
			$values_sorted = [];

			foreach ( $values_simple as $value_simple ) {
				foreach ( $values as $value ) {
					if ( $value_simple == $value['title'] ) {
						$values_sorted[] = [
							'value'	=> $value['value'],
							'title'	=> $value['title'],
						];
					}
				}
			}
		}

		$result = [
			'values' => $values_sorted,
		];

		echo json_encode( $result );
		exit;
	}

	/**
	 * Returns a list of single posts categorized by their post types.
	 * 
	 * @since 7.6.7
	 */
	public function csm_ajax_get_single_posts() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		$snippet_id = intval( $_REQUEST['snippet_id'] );

		check_admin_referer( 'code_snippet_' . $snippet_id . '_conditions_metabox' );

		$result = [
			'values'	=> [
			
			]
		];

		// Add Pages to $results
		$page_post_type_object = get_post_type_object( 'page' );

		$args = array(
			'post_type'			=> 'page',
			'posts_per_page'	=> -1,
		);

		$query = new WP_Query( $args );
		if ( $query->post_count > 0 ) {
			$pages = $query->posts;				
		}
		
		foreach ( $pages as $page ) {
			$result['values'][$page_post_type_object->labels->singular_name . ' (' . $page->post_type . ')'][] = [
				'value'	=> $page->ID . '__' . $page->post_type . '__' . $page->post_title, // e.g. 23__movie__Mission Impossible
				'title'	=> '[' . $page_post_type_object->labels->singular_name . '] ' . $page->post_title,
			];					
		}

		// Get public post types that are publicly queryable
		$post_types = get_post_types( [ 
				'public' 				=> true,
				'publicly_queryable'	=> true,
			], 
			'objects' 
		);

		$excluded_post_types = array(
			'attachment',
		);

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $key => $post_type ) {
				if ( in_array( $post_type->name, $excluded_post_types ) ) {
					continue;	
				}

				if ( property_exists( $post_type, 'labels' ) ) {
					$post_type_labels = $post_type->labels;
					if ( property_exists( $post_type_labels, 'singular_name' ) ) {
						$post_type_label = $post_type_labels->singular_name;
					} else {
						$post_type_label = $post_type->label;
					}
				} else {
					$post_type_label = $post_type->label;					
				}
				
				$args = array(
					'post_type'			=> $post_type->name,
					'posts_per_page'	=> -1,
				);
				
				$query = new WP_Query( $args );
				if ( $query->post_count > 0 ) {
					$posts = $query->posts;				
				}
				
				foreach ( $posts as $post ) {
					$result['values'][$post_type_label . ' (' . $post_type->name . ')'][] = [
						'value'	=> $post->ID . '__' . $post_type->name . '__' . $post->post_title, // e.g. 23__movei__Mission Impossible
						'title'	=> '[' . $post_type_label . '] ' . $post->post_title,
					];					
				}
				
				wp_reset_postdata();
			}
		}

		// Add 'Pages' into $results

		
		echo json_encode( $result );
		exit;
	}

	/**
	 * Returns a list of taxonomies
	 * 
	 * @link https://plugins.trac.wordpress.org/browser/insert-headers-and-footers/tags/2.2.5/includes/conditional-logic/class-wpcode-conditional-page.php#L188
	 * @since 7.6.7
	 */
	public function csm_ajax_get_taxonomies_list() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		$snippet_id = intval( $_REQUEST['snippet_id'] );

		check_admin_referer( 'code_snippet_' . $snippet_id . '_conditions_metabox' );

		$taxonomies = get_taxonomies(
			array(
				'public' => true,
			),
			'objects'
		);

		$values_simple = [];
		$values    = array();

		foreach ( $taxonomies as $taxonomy ) {
			if ( 'post_format' === $taxonomy->name ) {
				continue;
			}
			$values_simple[] = $taxonomy->labels->singular_name . ' (' . $taxonomy->name . ')';
			$values[] = array(
				'value' => $taxonomy->name,
				'title' => $taxonomy->labels->singular_name . ' (' . $taxonomy->name . ')',
			);
		}

		// Let's sort by label
		asort( $values_simple ); // sort by value, ascending
		$values_sorted = [];

		foreach ( $values_simple as $value_simple ) {
			foreach ( $values as $value ) {
				if ( $value_simple == $value['title'] ) {
					$values_sorted[] = [
						'value'	=> $value['value'],
						'title'	=> $value['title'],
					];
				}
			}
		}

		$result = [
			'values' => $values_sorted,
		];

		echo json_encode( $result );
		exit;
	}
	
	/**
	 * Return a list of taxonomy terms, categoriezed by their taxonomies
	 * 
	 * @since 7.6.7
	 */
	public function csm_ajax_get_taxonomies_terms_list() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		$snippet_id = intval( $_REQUEST['snippet_id'] );

		check_admin_referer( 'code_snippet_' . $snippet_id . '_conditions_metabox' );
		
		$result = [
			'values'	=> [
			
			]
		];

		$public_taxonomy_names = get_taxonomies(
			array(
				'public' => true,
			)
		);

		$public_taxonomies = array();
		
		foreach ( $public_taxonomy_names as $taxonomy_name ) {
			$taxonomy_object = get_taxonomy( $taxonomy_name );
			$taxonomy_label = $taxonomy_object->label;
			$public_taxonomies[$taxonomy_name] = $taxonomy_label;
		}
		asort( $public_taxonomies ); // Sort by value (label), ascending
		
		foreach ( $public_taxonomies as $taxonomy_name => $taxonomy_label ) {			
			$terms = get_terms(
				array(
					'taxonomy'		=> $taxonomy_name,
					'orderby'		=> 'name',
					'order'			=> 'ASC',
					'hide_empty'	=> false,
				)
			);

			$all_terms = array();

			foreach ( $terms as $term ) {
				$all_terms[$term->name] = $term->term_id . '__' . $term->slug;
			}
			ksort( $all_terms ); // Sort by key (term name/label), ascending
			
			foreach ( $all_terms as $term_name => $term_id_slug ) {
				$result['values'][$taxonomy_label . ' (' . $taxonomy_name . ')'][] = [
					'value'	=> $term_id_slug, // e.g. 23__politics
					'title'	=> $term_name,
				];
			}
			
		}

		echo json_encode( $result );
		exit;
	}

	/**
	 * Return a list of user roles
	 * 
	 * @link https://plugins.trac.wordpress.org/browser/insert-php/tags/2.4.10/admin/ajax/ajax.php#L18
	 * @since 7.6.7
	 */
	public function csm_ajax_get_user_roles() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		$snippet_id = intval( $_REQUEST['snippet_id'] );

		check_admin_referer( 'code_snippet_' . $snippet_id . '_conditions_metabox' );

		global $wp_roles;
		$roles = $wp_roles->roles;

		$values = [];
		foreach ( $roles as $role_slug => $role ) {
			$values[] = [
				'value' => $role_slug,
				'title' => $role['name'],
			];
		}

		$values[] = [
			'value' => 'guest',
			'title' => __( 'Guest', 'admin-site-enhancements' ),
		];

		$result = [
			'values' => $values,
		];

		echo json_encode( $result );
		exit;
	}

	/**
	 * Show contextual help for the Code Snippet edit page
	 */
	public function contextual_help() {
		$screen = get_current_screen();

		if ( $screen->id != 'asenha_code_snippet' ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'csm-editor_shortcuts',
				'title'   => __( 'Editor Shortcuts', 'code-snippets-manager-pro' ),
				'content' =>
							  '<p><table id="commands">
				            <tr>
				            <td><strong>Find</strong></td>
				            <td> <span class="commands win">Win: <code>Ctrl</code> + <code>F</code></span><span class="commands mac">Mac: <code>Command</code> + <code>F</code></span></td>
				            </tr>
				            <tr>
				            <td><strong>Replace</strong></td>
				            <td> <span class="commands win">Win: <code>Shift</code> + <code>Ctrl</code> + <code>F</code></span><span class="commands mac">Mac: <code>Command</code> + <code>Option</code> + <code>F</code></span></td>
				            </tr>
				            <tr>
				            <td><strong>Save</strong></td>
				            <td> <span class="commands win">Win: <code>Ctrl</code> + <code>S</code></span><span class="commands mac">Mac: <code>Command</code> + <code>S</code></span></td>
				            </tr>
				            <tr>
				            <td><strong>Comment line/block</strong></td>
				            <td> <span class="commands win">Win: <code>Ctrl</code> + <code>/</code></span><span class="commands mac">Mac: <code>Command</code> + <code>/</code></span></td>
				            </tr>
				            <tr>
				            <td><strong>Code folding</strong></td>
				            <td> <span class="commands win">Win: <code>Ctrl</code> + <code>Q</code></span><span class="commands mac">Mac: <code>Ctrl</code> + <code>Q</code></span></td>
				            </tr>
				            <tr>
				            <td><strong>Exit fullscreen</strong></td>
				            <td> <span class="commands win">Win: <code>Esc</code></span><span class="commands mac">Mac: <code>Esc</code></span></td>
				            </tr>
				            </table></p>',
			)
		);

	}


	/**
	 * Remove the JS/CSS/PHP file from the disk when deleting the post
	 */
	function before_delete_post( $postid ) {
		global $post;
		if ( ! is_object( $post ) ) {
			return;
		}
		if ( 'asenha_code_snippet' !== $post->post_type ) {
			return;
		}
		if ( ! wp_is_writable( CSM_UPLOAD_DIR ) ) {
			return;
		}

		$options = get_post_meta( $postid, 'options', true );
		if ( ! is_array( $options ) ) {
			return;
		}

		$options['language'] = ( isset( $options['language'] ) ) ? strtolower( $options['language'] ) : 'css';
		$options['language'] = in_array( $options['language'], array( 'html', 'js', 'css', 'php' ), true ) ? $options['language'] : 'css';

		$slug = get_post_meta( $postid, '_slug', true );
		$slug = sanitize_file_name( $slug );

		$file_name = $postid . '.' . $options['language'];

		@unlink( CSM_UPLOAD_DIR . '/' . $file_name );

		if ( ! empty( $slug ) ) {
			@unlink( CSM_UPLOAD_DIR . '/' . $slug . '.' . $options['language'] );
		}
	}


	/**
	 * Fix for bug: white page Edit Code Snippet for WordPress 5.0 with Classic Editor
	 */
	function current_screen_2() {
		$screen = get_current_screen();

		if ( $screen->post_type != 'asenha_code_snippet' ) {
			return false;
		}

		remove_filter( 'use_block_editor_for_post', array( 'Classic_Editor', 'choose_editor' ), 100, 2 );
		add_filter( 'use_block_editor_for_post', '__return_false', 100 );
		add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );
	}
}

return new Code_Snippets_Manager_Admin();
