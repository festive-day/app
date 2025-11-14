<?php
/**
 * This class is forked from CFS Options Screens v1.2.7 by Jonathan Christopher
 * 
 * @link http://wordpress.org/plugins/cfs-options-screens/
 * @link https://plugins.trac.wordpress.org/browser/cfs-options-screens/tags/1.2.7/cfs-options-screens.php
 * @since 7.0.0
 */

use ASENHA\Classes\Common_Methods;

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CFG_Options_Screens {

	/**
	 * @var CFG_Options_Screens Singleton
	 */
	private static $instance;

	/**
	 * @var array Options screens to create and utilize
	 */
	public $screens = array();

	/**
	 * @var string Post Type that powers options screens
	 */
	public $post_type = 'asenha_options_page';

	/**
	 * @var string Meta key used to store options screen name
	 */
	public $meta_key = '_cfgroup_options_screen_name';

	/**
	 * @var bool Whether we are applicable on this page load
	 */
	private $applicable = false;

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_inline_css_script' ) );

		add_action( 'cfgroup_matching_groups', array( $this, 'cfgroup_rule_override' ), 10, 3 );
		add_action( 'cfgroup_form_before_fields', array( $this, 'output_override_note' ) );
	}

	/**
	 * Output some markup above all Field Group fields in override cases, explaining
	 * that editing these fields will override the defaults
	 *
	 * @since 1.2
	 *
	 * @param $params
	 *
	 * @return void
	 */
	function output_override_note( $params ) {

		if ( empty( $params['field_groups'] ) ) {
			return;
		}

		$screens = $this->get_screens_from_field_group_id( $params['field_groups'] );

		foreach ( $screens as $screen ) {
			foreach ( $screen['field_groups'] as $field_group ) {
				if (
					is_array( $field_group )
					&& array_key_exists( 'id', $field_group )
					&& $params['field_groups'] == $field_group['id']
					&& array_key_exists( 'has_overrides', $field_group )
					&& ! empty( $field_group['has_overrides'] ) ) {

					// set up the note when editing the default
					$editing_default_note =  '<strong>Note:</strong> These defaults can be overridden when editing the applicable page.';
					$editing_default_note = apply_filters( 'cfgroup_options_screens_override_note_default',
						'<div class="cfg-options-screens-note field" style="font-size:1.2em;"><p class="notes" style="padding-top:0.85em;">' . $editing_default_note . '</p></div>',
						$screen
					);

					// set up the note when editing the override
					/** @noinspection HtmlUnknownTarget */
					// $editing_override_note = sprintf(
					// 	__( '<strong>Optional:</strong> Editing these fields will <em>override</em> <a href="%s">%s</a> which will be used if these fields are left empty',
					// 		'admin-site-enhancements' ),
					// 	esc_url( admin_url() . $this->get_options_screen_edit_slug( $screen['id'] ) ),
					// 	esc_html( $screen['menu_title'] )
					// );
					$editing_override_note = '<strong>Optional:</strong> Editing these fields will <em>override</em> <a href="' . esc_url( admin_url() . $this->get_options_screen_edit_slug( $screen['id'] ) ) . '">' . esc_html( $screen['menu_title'] ) . '</a> which will be used if these fields are left empty';
					$editing_override_note = apply_filters( 'cfgroup_options_screens_override_note_override',
						'<div class="cfg-options-screens-note field" style="font-size:1.2em;"><p class="notes" style="padding-top:0.85em;">' . $editing_override_note . '</p></div>',
						$screen
					);

					$note = $this->applicable ? $editing_default_note : $editing_override_note;

					echo wp_kses_post(
						apply_filters( 'cfgroup_options_screens_override_note',
							$note,
							$screen,
							$this->applicable // indicates whether editing the default (when true) or the override (when false)
						)
					);

					break;
				}
			}
		}
	}

	/**
	 * Retrieve an array of Options Screens models that utilize a Field Group ID
	 *
	 * @since 1.2
	 * @param $field_group_id
	 *
	 * @return array
	 */
	function get_screens_from_field_group_id( $field_group_id ) {

		$field_group_id = absint( $field_group_id );
		$screens = array();

		foreach ( $this->screens as $screen ) {

			$screen_field_groups = $this->get_field_group_ids( $screen['field_groups'] );

			if ( in_array( $field_group_id, $screen_field_groups ) ) {
				$screens[] = $screen;
			}
		}

		return $screens;
	}

	/**
	 * Singleton
	 *
	 * @return CFG_Options_Screens
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CFG_Options_Screens ) ) {
			self::$instance = new CFG_Options_Screens;
		}
		return self::$instance;
	}

	/**
	 * Initialize everything
	 */
	function init() {		
		// hide the 'Edit Post' title, 'Add New' button, and 'updated' notification when editing all options screens
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );

		// reinstate the 'updated' notification
		add_action( 'admin_notices', array( $this, 'maybe_updated_notice' ) );

		// let developers customize the post type used
		$this->post_type = apply_filters( 'cfgroup_options_screens_post_type', $this->post_type );

		// register our custom post type
		$this->register_cpts();

		add_action( 'add_meta_boxes', [ $this, 'add_meta_box_for_options_page_config' ] );
		add_action( 'add_meta_boxes', [ $this, 'remove_meta_box_from_options_page' ] );

		// allow registration of options screens
		// $this->screens = apply_filters( 'cfgroup_options_screens', $this->screens );
		$this->register_options_pages();		
		
		// make sure our posts exist
		$this->init_screens();

		// add menus
		add_action( 'admin_menu', array( $this, 'maybe_add_menus' ) );
	}
	
	/**
	 * Maybe enqueue our stylesheet
	 * @param $hook
	 */
	function assets( $hook ) {
		global $post;

		if ( ( 'post-new.php' == $hook || 'post.php' == $hook ) && $this->post_type == $post->post_type ) {
			wp_enqueue_style( 'asenha-options-screens', ASENHA_URL . 'includes/premium/custom-content/options-pages/assets/css/options-pages.css', array(), ASENHA_VERSION );
			wp_enqueue_script( 'asenha-options-screens', ASENHA_URL . 'includes/premium/custom-content/options-pages/assets/js/options-pages.js', array( 'jquery' ), ASENHA_VERSION, false );
			$this->applicable = true;
		}

		return;
	}

	/**
	 * Output CSS in the footer that will customize the page title
	 */
	function admin_inline_css_script() {
		global $post, $pagenow, $typenow;

		$heading = 'Options';

		// determine which screen we're on
		if ( isset( $this->screens ) && is_object( $post ) ) {
			foreach ( $this->screens as $screen ) {
				if ( $post->ID == $screen['id'] ) {
					$heading = $screen['page_title'];
				}
			}
		}

		wp_localize_script( 
			'asenha-options-screens', 
			'optionsPage',
			array(
				'heading' 	=> $heading,
			)
		);
		
		if ( 'post.php' == $pagenow && 'asenha_options_page' == $typenow ) {
		?>
		<style>
			h1.wp-heading-inline {
				visibility: hidden;
			}
		</style>
		<?php			
		}
	}
	
	/**
	 * Since the 'updated' message references saving a post and adding a new one we hid it and this adds our own
	 */
	function maybe_updated_notice() {
		global $post;

		$screen = get_current_screen();

		// bail out if this isn't a proper edit screen
		if ( ! isset( $screen->post_type ) || $this->post_type !== $screen->post_type ) {
			return;
		}

		if ( isset( $_GET['message'] ) && $this->post_type == $post->post_type ) {
			?>
			<div class="updated"><p><?php esc_html_e( 'Changes have been saved.', 'admin-site-enhancements' ); ?></p></div>
			<?php
		}
	}
	
	/**
	 * Activate options pages that has been added
	 */
	function register_options_pages() {
		// This function breaks admin pages of certain plugins, so we prevent execution there
		$request_uri = $_SERVER['REQUEST_URI'];
		$should_execute = true;

		// WP Grid Builder v2x
		if ( false !== strpos( $request_uri, 'wp-grid-builder' ) ) {
			$should_execute = false;
		}
		
		if ( $should_execute ) {
			$field_groups_rules = array();

			$args = array(
			    'post_type'         => 'asenha_cfgroup',
			    'post_status'       => 'publish',
			    'numberposts'    	=> -1,
			    'orderby'           => 'title',
			    'order'             => 'ASC',
			);

			$cfgroups = get_posts( $args );
			
			if ( ! empty( $cfgroups ) ) {
				foreach( $cfgroups as $cfgroup ) {
					$rules = (array) get_post_meta( $cfgroup->ID, 'cfgroup_rules', true );

					if ( isset( $rules['placement'] ) 
						&& ! empty( $rules['placement']['values'] ) 
						&& isset( $rules['options_pages']['values'] ) 
						&& ! empty( $rules['options_pages']['values'] ) 
					) {
					 $field_groups_rules[$cfgroup->ID] = $rules;			 	
					}					
				}
			}
			
			$screens = $this->screens;

			// Get screens info from published options pages

			$args = array(
			    'post_type'         => 'options_page_config',
			    'post_status'       => 'publish',
			    'numberposts'    => -1,
			    'orderby'           => 'title',
			    'order'             => 'ASC',
			);
		
			$opconfigs = get_posts( $args );
			
			if ( ! empty( $opconfigs ) ) {
				foreach( $opconfigs as $opconfig ) {
			        $name = get_post_meta( $opconfig->ID, 'options_page_menu_slug', true );
			        $field_group_ids_for_options_page = array();
			        
			        foreach ( $field_groups_rules as $field_group_id => $rules ) {
			        	if ( isset( $rules['placement']['values'] ) 
			        		&& 'options-pages' == $rules['placement']['values']
			        	) {
				        	if ( '==' == $rules['options_pages']['operator'] && 
				        		in_array( $name, $rules['options_pages']['values'] ) 
				        	) {
				        		$field_group_ids_for_options_page[] = $field_group_id;
				        	}
				        	
				        	if ( '!=' == $rules['options_pages']['operator'] && 
				        		! in_array( $name, $rules['options_pages']['values'] ) 
				        	) {
				        		$field_group_ids_for_options_page[] = $field_group_id;
				        	}
			        	}
			        }
			        
			        $screens[] = array(
			        	'name'				=> $name,
			        	'menu_title'		=> get_post_meta( $opconfig->ID, 'options_page_menu_title', true ),
			        	'page_title'		=> get_post_meta( $opconfig->ID, 'options_page_title', true ),
			        	'icon'				=> get_post_meta( $opconfig->ID, 'options_page_menu_icon', true ),
			        	'parent'			=> get_post_meta( $opconfig->ID, 'options_page_parent_menu', true ),
			        	'field_groups'		=> $field_group_ids_for_options_page,
			        	'capability'		=> get_post_meta( $opconfig->ID, 'options_page_capability', true ),
			        	'custom_capability'	=> get_post_meta( $opconfig->ID, 'options_page_capability_custom', true ),
			        );
				}
			}

			$this->screens = $screens;				
		}
	}

	/**
	 * Retrieves post IDs for all screens, creates new posts in CPT if nonexistent
	 */
	function init_screens() {
		global $options_pages_screens;

		if ( ! empty( $this->screens ) ) {

			foreach ( $this->screens as $screen_key => $screen_meta ) {
				$this->screens[ $screen_key ]['name']           = isset( $screen_meta['name'] )          ? $screen_meta['name'] : 'custom_site_options';
				$this->screens[ $screen_key ]['page_title']     = isset( $screen_meta['page_title'] )    ? $screen_meta['page_title'] : ucfirst( (string) $this->screens[ $screen_key ]['name'] );
				$this->screens[ $screen_key ]['menu_title']     = isset( $screen_meta['menu_title'] )    ? $screen_meta['menu_title'] : ucfirst( (string) $this->screens[ $screen_key ]['name'] );
				$this->screens[ $screen_key ]['menu_icon']      = isset( $screen_meta['icon'] )          ? $screen_meta['icon'] : 'dashicons-admin-generic';
				$this->screens[ $screen_key ]['menu_position']  = isset( $screen_meta['menu_position'] ) ? $screen_meta['menu_position'] : 100;
				$this->screens[ $screen_key ]['field_groups']   = isset( $screen_meta['field_groups'] )  ? $screen_meta['field_groups'] : array();
				
				if ( 'custom' != $screen_meta['capability'] ) {
					$this->screens[ $screen_key ]['capability'] = isset( $screen_meta['capability'] ) ? $screen_meta['capability'] : 'manage_options';
				} else {
					$this->screens[ $screen_key ]['capability'] = isset( $screen_meta['custom_capability'] ) ? $screen_meta['custom_capability'] : 'manage_options';					
				}

				// check to see if the post for this screen exists
				// $option_screen = get_page_by_title( $this->screens[ $screen_key ]['name'], 'OBJECT', $this->post_type );
				
				$posts = get_posts(
					array(
						'post_type'              => 'asenha_options_page',
						'title'                  => $this->screens[ $screen_key ]['page_title'],
						'post_status'            => 'all',
						'numberposts'            => 1,
						'update_post_term_cache' => false,
						'update_post_meta_cache' => false,           
						'orderby'                => 'post_date ID',
						'order'                  => 'ASC',
						)
				);

				$option_screen = null;

				if ( ! empty( $posts ) ) {
					$option_screen = $posts[0];
				} 

				if ( empty( $option_screen ) ) {
					// post doesn't exist, create and flag it
					$this->screens[ $screen_key ]['id'] = wp_insert_post(
						array(
							// 'post_title' => sanitize_text_field( $this->screens[ $screen_key ]['name'] ),
							'post_title' => sanitize_text_field( $this->screens[ $screen_key ]['page_title'] ),
							'post_type'  => sanitize_text_field( $this->post_type )
						)
					);
				} else {
					$this->screens[ $screen_key ]['id'] = absint( $option_screen->ID );
				}
			}
			
			$options_pages_screens = $this->screens;
		}
	}

	/**
	 * Registers the CPT that powers everything
	 */
	function register_cpts() {
		
		// Option 
		$args = array(
			'label'                 => __( 'Options Page', 'admin-site-enhancements' ),
			'public'                => false,
			'publicly_queryable'    => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => false,
			'show_in_menu'          => false,
			'query_var'             => false,
			'rewrite'               => false,
			'supports'              => false,
		);

		$args = apply_filters( 'cfgroup_options_screens_post_type_args', $args );

		register_post_type( $this->post_type, $args );

		// Options Pages Config
		register_post_type(
			'options_page_config',
			array(
				'labels'				=> array(
					'name'					=> __( 'Options Pages', 'admin-site-enhancements' ),
					'singular_name'			=> __( 'Options Page', 'admin-site-enhancements' ),
					'add_new'				=> __( 'Add New', 'admin-site-enhancements' ),
					'add_new_item'			=> __( 'Add New Options Page', 'admin-site-enhancements' ),
					'new_item'				=> __( 'New Options Page', 'admin-site-enhancements' ),
					'edit_item'				=> __( 'Edit Options Page', 'admin-site-enhancements' ),
					'update_item'			=> __( 'Update Options Page', 'admin-site-enhancements' ),
					'view_item'				=> __( 'View Options Page', 'admin-site-enhancements' ),
					'search_items'			=> __( 'Search Options Page', 'admin-site-enhancements' ),
					'not_found'				=> __( 'No Custom Options Pages found', 'admin-site-enhancements' ),
					'not_found_in_trash'	=> __( 'No Custom Options Pages found in Trash', 'admin-site-enhancements' ),
				),
				'public'			=> false,
				'show_ui'			=> true,
				'show_in_menu'		=> 'options-general.php',
				'hierarchical'		=> false,
				// Hide 'title' and 'editor'
				'supports'			=> false,
				'rewrite'			=> false,
				'query_var'			=> false,
				'can_export'		=> true,
				// Allow administrator to edit/read/delete without adding custom capabilites
				'map_meta_cap'		=> true,
				// Limit CRUD operations to administrators
				'capabilities'		=> array(
					// Meta capabilities.
					'edit_post'              => 'edit_asenha_options_page_settings',
					'read_post'              => 'read_asenha_options_page_settings',
					'delete_post'            => 'delete_asenha_options_page_settings',

					// Primitive capabilities used outside of map_meta_cap():
					'edit_posts'             => 'manage_options',
					'edit_others_posts'      => 'manage_options',
					'publish_posts'          => 'manage_options',
					'read_private_posts'     => 'manage_options',

					// Primitive capabilities used within map_meta_cap():
					'read'                   => 'read',
					'delete_posts'           => 'manage_options',
					'delete_private_posts'   => 'manage_options',
					'delete_published_posts' => 'manage_options',
					'delete_others_posts'    => 'manage_options',
					'edit_private_posts'     => 'manage_options',
					'edit_published_posts'   => 'manage_options',
					'create_posts'           => 'manage_options',				
				),
			)
		);
	}
	
	function add_meta_box_for_options_page_config() {
		add_meta_box(
			'asenha_optionp_meta_box',
			__( 'Page Settings', 'admin-site-enhancements' ),
			[ $this, 'render_options_page_meta_box' ],
			'options_page_config',
			'normal',
			'core'
		);

        add_meta_box( 
            'asenha_optionp_tips_meta_box', 
            __( 'Tips', 'admin-site-enhancements' ), 
            [ $this, 'render_optionsp_tips_meta_box' ], 
            'options_page_config', 
            'side', 
            'default'
        );
	}
	
	function remove_meta_box_from_options_page() {
		 remove_meta_box( 'slugdiv', 'asenha_options_page', 'normal' );		
	}
	
	function render_options_page_meta_box() {
		$common_methods = new Common_Methods;

		// Get or set the values of custom taxonomy fields
		require_once ASENHA_PATH . 'includes/premium/custom-content/options-pages/options-page-fields-values.php';

		// Render metabox to input / update custom taxonomy fields
		require_once ASENHA_PATH . 'includes/premium/custom-content/options-pages/options-page-form-fields.php';
	}

    /**
     * Render tips meta box for custom field group
     */
    public function render_optionsp_tips_meta_box() {
        require_once ASENHA_PATH . 'includes/premium/custom-content/options-pages/opconfig_meta_box_tips.php';        
    }


	/**
	 * Retrieve the slug for an Options Screen edit URL
	 *
	 * @since 1.2
	 *
	 * @param $screen_id
	 *
	 * @return mixed
	 */
	function get_options_screen_edit_slug( $screen_id ) {
		$url = add_query_arg(
			array(
				'post'      => absint( $screen_id ),
				'action'    => 'edit',
			),
			admin_url( 'post.php' )
		);

		// $url = esc_url( $url ); // Commented out as this is causing JS error with Admin Menu Organizer

		return str_replace( admin_url(), '', $url );
	}

	/**
	 * Add applicable Admin menus
	 */
	function maybe_add_menus() {

		if ( empty( $this->screens ) ) {
			return;
		}

		// screens were registered during init so the ID is already prepped and the post exists
		foreach ( $this->screens as $screen ) {
			$edit_link = $this->get_options_screen_edit_slug( $screen['id'] );

			// if this screen doesn't have a parent, it IS a parent
			if ( 'none' == $screen['parent'] ) {
				add_menu_page(
					$screen['page_title'],
					$screen['menu_title'],
					$screen['capability'],
					$edit_link,
					'',
					$screen['menu_icon'],
					$screen['menu_position']
				);
			} else {
				// it's a sub-menu, so add it to the parent
				$parent_slug = $screen['parent'];

				add_submenu_page(
					$parent_slug,
					$screen['page_title'],
					$screen['menu_title'],
					$screen['capability'],
					$edit_link,
					''
				);
			}
		}
	}

	/**
	 * By default CFG Options Screens only supports Field Groups with no placement rules
	 * 'Overrides' are for the cases where you want to use a Field Group to facilitate setting
	 * defaults on an Options Screen, but also allow for an override for that field data
	 * on specific edit screens that follow the placement rules of CFG itself
	 *
	 * @param $matches
	 * @param $params
	 * @param $rule_types
	 * @param $options_screen
	 *
	 * @return mixed
	 */
	function maybe_has_overrides( $matches, /** @noinspection PhpUnusedParameterInspection */ $params, /** @noinspection PhpUnusedParameterInspection */ $rule_types, $options_screen ) {
		// didn't find an options screen?
		if ( empty( $options_screen ) ) {
			return $matches;
		}

		// segment defaults
		if ( empty( $options_screen['field_groups'] ) ) {
			return $matches;
		}

		// move over all of these Field Groups into $matches
		foreach ( $options_screen['field_groups'] as $field_group ) {
			$key = $this->get_field_group_id( $field_group );

			if ( ! array_key_exists( $key, $matches ) ) {
				$matches[ $key ] = get_the_title( $key );
			}
		}

		return $matches;
	}

	/**
	 * Version 1.2 introduced overrides, so we need back compat
	 *
	 * @since 1.2
	 *
	 * @param $field_group
	 *
	 * @return int
	 */
	function get_field_group_id( $field_group ) {

		if ( is_string( $field_group ) ) {

			$field_group = $this->get_field_group_id_from_title( $field_group );

		} elseif ( is_array( $field_group ) ) {

			if ( array_key_exists( 'id', $field_group ) ) {

				$field_group = $field_group['id'];

			} elseif ( array_key_exists( 'title', $field_group ) ) {

				$field_group = $this->get_field_group_id_from_title( $field_group['title'] );

			}

		}

		return absint( $field_group );
	}

	/**
	 * Retrieve CFG Field Group ID from title
	 *
	 * @param string $title
	 *
	 * @return int
	 */
	function get_field_group_id_from_title( $title = '' ) {
		$id = 0;

		$field_group_obj = get_page_by_title( $title, 'OBJECT', 'cfg' );

		if ( $field_group_obj instanceof WP_Post ) {
			$id = $field_group_obj->ID;
		}

		return $id;
	}

	/**
	 * Retrieve the CFG Options Screen model from its post ID
	 *
	 * @param $post_id
	 *
	 * @return bool|array
	 */
	function get_options_screen_from_post_id( $post_id ) {
		$options_screen = false;

		// we need to validate that this post ID is actually a registered options screen
		if ( ! empty( $this->screens ) ) {
			foreach ( $this->screens as $screen_key => $screen_meta ) {
				if ( isset( $screen_meta['id'] ) && $post_id == $screen_meta['id'] ) {
					$options_screen = $screen_meta;
					break;
				}
			}
		}

		return $options_screen;
	}

	/**
	 * Retrieve an array of Field Group IDs from a field_groups definition, takes into consideration
	 * whether you want to include overrides or not
	 *
	 * @since 1.2
	 * @param $field_groups
	 * @param bool $include_overrides
	 *
	 * @return array
	 */
	function get_field_group_ids( $field_groups, $include_overrides = true ) {

		$field_group_ids = array();

		foreach ( $field_groups as $field_group ) {

			if (
				( $include_overrides || is_numeric( $field_group ) ) // include by intention or by legacy field_groups value (pre 1.2)
				|| (
					! $include_overrides // don't include overrides
					&& is_array( $field_group ) // 1.2 or later
					&& array_key_exists( 'has_overrides', $field_group ) && empty( $field_group['has_overrides'] ) // does not have overrides
				) ) {
				// include it in the list
				$field_group_ids[] = $this->get_field_group_id( $field_group );
			} elseif ( is_string( $field_group ) ) {
				$field_group = $this->get_field_group_id_from_title( $field_group );
				if ( ! empty( $field_group ) ) {
					$field_group_ids[] = absint( $field_group );
				}
			}
		}

		return $field_group_ids;
	}


	/**
	 * Custom Field Group doesn't support single post IDs for placement rules, so we're going to inject our own.
	 *
	 * @param $matches
	 * @param $params
	 * @param $rule_types
	 *
	 * @return mixed
	 */
	function cfgroup_rule_override( $matches, $params, $rule_types ) {

		if ( is_array( $params ) || ! is_numeric( $params ) ) {
			return $matches;
		}

		$post_id = absint( $params );
		$options_screen = $this->get_options_screen_from_post_id( $post_id );

		$matches = $this->maybe_has_overrides( $matches, $params, $rule_types, $options_screen );

		// let developers override the matches and parameters
		// maybe_has_overrides() use case: setting up 'defaults' for a Field Group - you want the
		// same Field Group to appear both in it's defined locations (which when defined omit it
		// from $matches from the start) and in the options screen to act as the defaults
		$matches = apply_filters( 'cfgroup_options_screens_rule_matches', $matches, $params, $rule_types, $this );

		if ( $options_screen && is_array( $matches ) && ! empty( $matches ) ) {

			// we need to strip out the Field Groups that are not registered with this options screen
			$field_group_ids = $this->get_field_group_ids( $options_screen['field_groups'] );

			foreach ( $matches as $match_key => $match_title ) {
				if ( ! in_array( $match_key, $field_group_ids ) ) {
					unset( $matches[ $match_key ] );
				}
			}

		} else {

			// we need to strip out all Field Groups related to Options Screens else they'll show up where we don't want them
			$options_screens_field_groups = array();

			foreach ( $this->screens as $screen_meta ) {
				$field_group_ids = $this->get_field_group_ids( $screen_meta['field_groups'], false );
				$options_screens_field_groups = array_merge( $options_screens_field_groups, $field_group_ids );
			}

			foreach ( $matches as $match_key => $match_title ) {
				if ( in_array( $match_key, $options_screens_field_groups ) ) {
					unset( $matches[ $match_key ] );
				}
			}
		}

		return $matches;
	}

}

/**
 * Retrieve an option from a settings screen
 * Usage: $value = get_cf_option( 'name_of_option_page', 'my_field' );
 *
 * @param string $screen The options screen name
 * @param string $field  The field name
 *
 * @return bool|mixed The field value as returned by the CFG API
 */
if ( ! function_exists( 'get_op_option' ) ) {
	function get_op_option( $screen = 'custom_site_options', $field = '', $post_id = 0 ) {
		$value = false;

		if ( ! function_exists( 'CFG' ) ) {
			return false;
		}

		$cfgroup_options_screens = CFG_Options_Screens::instance();

		if ( ! empty( $cfgroup_options_screens->screens ) ) {
			foreach ( $cfgroup_options_screens->screens as $screen_meta ) {
				if ( $screen == $screen_meta['name'] ) {

					// support the overrides concept by first checking for the override
					// and automatically falling back to the Options Screen value if needed
					$post_id = empty( $post_id ) ? get_queried_object_id() : absint( $post_id );

					if ( ! empty( $post_id ) ) {
						$value = CFG()->get( $field, absint( $post_id ) );
					}

					if ( empty( $value ) ) {
						$value = CFG()->get( $field, $screen_meta['id'] );
					}

					break;
				}
			}
		}

		return $value;
	}
}

/**
 * Retrieve all option from a settings screen
 * Usage: $value = get_cf_options( 'name_of_option_page' );
 *
 * @param string $screen The options screen name
 *
 * @return bool|mixed The field value as returned by the CFG API
 */
if ( ! function_exists( 'get_op_options' ) ) {
	function get_op_options( $screen = 'custom_site_options' ) {
		$value = false;

		if ( ! function_exists( 'CFG' ) ) {
			return false;
		}

		$cfgroup_options_screens = CFG_Options_Screens::instance();

		if ( ! empty( $cfgroup_options_screens->screens ) ) {
			foreach ( $cfgroup_options_screens->screens as $screen_meta ) {
				if ( $screen == $screen_meta['name'] ) {
					$value = CFG()->get( false, $screen_meta['id'] );
				}
			}
		}

		return $value;
	}
}

/**
 * Initializer
 *
 * @return CFG_Options_Screens
 */
if ( ! function_exists( 'cfgroup_options_screens_init' ) ) {
	function cfgroup_options_screens_init() {
		$cfgroup_options_screens = CFG_Options_Screens::instance();
		return $cfgroup_options_screens;
	}
}

// kickoff
cfgroup_options_screens_init();
