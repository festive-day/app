<?php
/**
 * Tree View Media class.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

/**
 * Outputs the Tree View in the Media Library Footer
 *
 * @version   1.1.1
 */
class Media_Categories_Module_Tree_View_Media {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.1.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.1.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Enqueue JS and CSS for Tree View.
		add_action( 'media_categories_module_admin_scripts_js_media', array( $this, 'enqueue_js' ), 10, 4 );
		add_action( 'media_categories_module_admin_scripts_css_media', array( $this, 'enqueue_css' ), 10, 0 );

		// Output Move Column in List View.
		add_filter( 'media_categories_module_media_define_list_view_columns', array( $this, 'define_list_view_columns' ), 10, 1 );
		add_filter( 'media_categories_module_media_define_list_view_columns_output_tree-view-move', array( $this, 'define_list_view_columns_output_tree_view_move' ), 10, 1 );

		// Output HTML in the Upload List and Grid Views.
		add_action( 'media_categories_module_media_media_library_footer', array( $this, 'media_library_footer' ) );

	}

	/**
	 * Enqueue JS for the WordPress Admin > Media screens
	 *
	 * @since   1.1.1
	 *
	 * @param   WP_Screen $screen     Current WordPress Screen.
	 * @param   array     $screens    Plugin Registered Screens.
	 * @param   string    $mode       View Mode (list|grid).
	 * @param   string    $ext        If defined, loads minified JS.
	 */
	public function enqueue_js( $screen, $screens, $mode, $ext ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if Tree View isn't enabled.
		if ( ! Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'enabled' ) ) {
			return;
		}

		// WP Zinc.
		wp_enqueue_script( 'wpzinc-admin-notification' );

		// jQuery UI.
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-menu' );
		wp_enqueue_script( 'jquery-ui-widget' );

		wp_enqueue_script( $this->base->plugin->name . '-jstree', $this->base->plugin->url . 'assets/js/' . ( $ext ? $ext . '/' : '' ) . 'jstree' . ( $ext ? '-' . $ext : '' ) . '.js', array( 'jquery' ), Media_Categories_Module()->plugin->version, true );
		wp_enqueue_script( $this->base->plugin->name . '-resize-sensor', $this->base->plugin->url . 'assets/js/' . ( $ext ? $ext . '/' : '' ) . 'resize-sensor' . ( $ext ? '-' . $ext : '' ) . '.js', array(), Media_Categories_Module()->plugin->version, true );
		wp_enqueue_script( $this->base->plugin->name . '-sticky-sidebar', $this->base->plugin->url . 'assets/js/' . ( $ext ? $ext . '/' : '' ) . 'sticky-sidebar' . ( $ext ? '-' . $ext : '' ) . '.js', array(), Media_Categories_Module()->plugin->version, true );
		wp_enqueue_script( $this->base->plugin->name . '-jquery-ui-contextmenu', $this->base->plugin->url . 'assets/js/' . ( $ext ? $ext . '/' : '' ) . 'jquery.ui-contextmenu' . ( $ext ? '-' . $ext : '' ) . '.js', array( 'jquery' ), Media_Categories_Module()->plugin->version, true );
		// wp_enqueue_script( $this->base->plugin->name . '-media', $this->base->plugin->url . 'assets/js/' . ( $ext ? $ext . '/' : '' ) . 'media' . ( $ext ? '-' . $ext : '' ) . '.js', array( 'jquery' ), Media_Categories_Module()->plugin->version, true );
		wp_enqueue_script( $this->base->plugin->name . '-media', $this->base->plugin->url . 'assets/js/media.js', array( 'jquery' ), Media_Categories_Module()->plugin->version, true );

		// Get Tree View Taxonomy.
		$taxonomy = $this->get_tree_view_taxonomy();

		// Add Context Menu to Add, Edit and Delete Categories if the User's Role permits this.
		$context_menu = false;
		if ( current_user_can( 'manage_categories' ) ) {
			$context_menu = array(
				array(
					'title' => __( 'Add Child', 'admin-site-enhancements' ),
					'cmd'   => 'create_term',
				),
				array(
					'title' => __( 'Edit', 'admin-site-enhancements' ),
					'cmd'   => 'edit_term',
				),
				array(
					'title' => __( 'Delete', 'admin-site-enhancements' ),
					'cmd'   => 'delete_term',
				),
			);
		}

		/**
		 * Defines the menu items for the Tree View's Context Menu, triggered when a user
		 * right clicks on a Category in the Tree View.
		 *
		 * @since   1.3.9
		 *
		 * @param   mixed   $context_menu   Context Menu (false: none, array).
		 */
		$context_menu = apply_filters( 'media_categories_module_tree_view_media_context_menu', $context_menu );

		// Define the AJAX actions supported by Tree View.
		$actions = array(
			'create_term'            => array(
				'action' => 'media_categories_module_add_term',
				'nonce'  => wp_create_nonce( 'media_categories_module_add_term' ),
				// 'prompt' => sprintf(
				// 	/* translators: Taxonomy Name, Singular */
				// 	__( 'Enter a %s Name', 'admin-site-enhancements' ),
				// 	$taxonomy->labels->singular_name
				// ),
				'prompt' => 'Enter a ' . $taxonomy->labels->singular_name . ' Name',
			),
			'edit_term'              => array(
				'action'       => 'media_categories_module_edit_term',
				'nonce'        => wp_create_nonce( 'media_categories_module_edit_term' ),
				// 'prompt'       => sprintf(
				// 	/* translators: Taxonomy Name, Singular */
				// 	__( 'Edit %s Name', 'admin-site-enhancements' ),
				// 	$taxonomy->labels->singular_name
				// ),
				// 'no_selection' => sprintf(
				// 	/* translators: Taxonomy Name, Singular */
				// 	__( 'You must select a %s first', 'admin-site-enhancements' ),
				// 	$taxonomy->labels->singular_name
				// ),
				'prompt'       => 'Edit ' . $taxonomy->labels->singular_name . ' Name',
				'no_selection' => 'You must select a ' . $taxonomy->labels->singular_name . ' first',
			),
			'delete_term'            => array(
				'action'       => 'media_categories_module_delete_term',
				'nonce'        => wp_create_nonce( 'media_categories_module_delete_term' ),
				// 'prompt'       => sprintf(
				// 	/* translators: Taxonomy Name, Singular */
				// 	__( 'Delete %s?', 'admin-site-enhancements' ),
				// 	$taxonomy->labels->singular_name
				// ),
				// 'no_selection' => sprintf(
				// 	/* translators: Taxonomy Name, Singular */
				// 	__( 'You must select a %s first', 'admin-site-enhancements' ),
				// 	$taxonomy->labels->singular_name
				// ),
				'prompt'       => 'Delete ' . $taxonomy->labels->singular_name . '?',
				'no_selection' => 'You must select a ' . $taxonomy->labels->singular_name . ' first',
			),
			'categorize_attachments' => array(
				'action' => 'media_categories_module_categorize_attachments',
				'nonce'  => wp_create_nonce( 'media_categories_module_categorize_attachments' ),
			),
			'get_tree_view'          => array(
				'action' => 'media_categories_module_tree_view_get_tree_view',
				'nonce'  => wp_create_nonce( 'media_categories_module_tree_view_get_tree_view' ),
			),
		);

		/**
		 * Defines the AJAX actions supported by the Tree View. Any context menu items should have
		 * a corresponding action defined here.
		 *
		 * @since   1.3.9
		 *
		 * @param   array   $actions    Actions.
		 */
		$actions = apply_filters( 'media_categories_module_tree_view_media_actions', $actions );

		// Define Media Settings.
		$media_settings = array(
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'actions'          => $actions,
			'context_menu'     => $context_menu,
			'taxonomy'         => $taxonomy,
			'selected_term'    => Media_Categories_Module()->get_class( 'media' )->get_selected_terms_slugs( $taxonomy->name ),
			'selected_term_id' => Media_Categories_Module()->get_class( 'media' )->get_selected_terms_ids( $taxonomy->name ),
			'media_view'       => Media_Categories_Module()->get_class( 'common' )->get_media_view(),
			'jstree'           => Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'expand_collapse' ),
			'labels'           => array(
				/* translators: Number of attachments */
				'categorized_attachments' => __( 'Categorized %s items', 'admin-site-enhancements' ),
				/* translators: Number of attachments */
				'categorize_attachments'  => __( 'Categorize %s items', 'admin-site-enhancements' ),
				'categorize_attachment'   => __( 'Categorize 1 item', 'admin-site-enhancements' ),
			),
		);

		// Localize Media script.
		wp_localize_script( $this->base->plugin->name . '-media', 'media_categories_module_tree_view', $media_settings );

	}

	/**
	 * Enqueue JS for the WordPress Admin > Media screens
	 *
	 * @since   1.1.1
	 */
	public function enqueue_css() {

		// Bail if Tree View isn't enabled.
		if ( ! Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'enabled' ) ) {
			return;
		}

		// CSS.
		wp_enqueue_style( $this->base->plugin->name . '-media', $this->base->plugin->url . '/assets/css/media.css', array(), Media_Categories_Module()->plugin->version );

	}

	/**
	 * Defines the Columns to display in the List View WP_List_Table
	 *
	 * @since   1.1.4
	 *
	 * @param   array $columns        Columns.
	 * @return  array                   Columns
	 */
	public function define_list_view_columns( $columns ) {

		// Bail if Tree View isn't enabled.
		if ( ! Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'enabled' ) ) {
			return $columns;
		}

		// Inject Move Column after the checkbox.
		return Media_Categories_Module()->get_class( 'common' )->array_insert_after(
			$columns,
			'cb',
			array(
				'tree-view-move' => '<span class="dashicons dashicons-move"></span>',
			)
		);

	}

	/**
	 * Defines the data to display in the List View WP_List_Table Move Column
	 *
	 * @since   1.1.4
	 *
	 * @param   string $output         Output.
	 * @return  string                  Output
	 */
	public function define_list_view_columns_output_tree_view_move( $output ) {

		// Bail if Tree View isn't enabled.
		if ( ! Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'enabled' ) ) {
			return $output;
		}

		return '<span class="dashicons dashicons-move"></span>';

	}

	/**
	 * Outputs the Tree View markup on the Media Library screens
	 *
	 * @since   1.1.1
	 */
	public function media_library_footer() {

		// Bail if Tree View isn't enabled.
		if ( ! Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'enabled' ) ) {
			return;
		}

		// Get Taxonomy.
		$taxonomy = $this->get_tree_view_taxonomy();

		// Get Tree View.
		$output = $this->get_tree_view( $taxonomy->name, Media_Categories_Module()->get_class( 'media' )->get_selected_terms_ids( $taxonomy->name ) );

		// Check is JSTree enabled.
		$jstree_enabled = Media_Categories_Module()->get_class( 'settings' )->get_setting( 'tree-view', 'jstree_enabled' );

		// Output.
		require_once $this->base->plugin->folder . '/views/admin/media.php';

		// Output Notification.
		require_once Media_Categories_Module()->plugin->folder . '/_modules/dashboard/views/notification.php';

	}

	/**
	 * Returns the Taxonomy object to be used in the Tree View.
	 *
	 * @since   1.3.2
	 *
	 * @return  WP_Taxonomy     Taxonomy
	 */
	public function get_tree_view_taxonomy() {

		/**
		 * Defines the Taxonomy to display Terms for in the Tree View.
		 *
		 * @since   1.3.2
		 *
		 * @param   string  $taxonomy_name      Taxonomy Name.
		 */
		$taxonomy_name = apply_filters( 'media_categories_module_tree_view_media_get_tree_view_taxonomy', 'asenha-media-category' );

		// Return taxonomy object.
		return Media_Categories_Module()->get_class( 'taxonomies' )->get_taxonomy( $taxonomy_name );

	}

	/**
	 * Gets the Tree View markup.
	 *
	 * @since   1.1.1
	 *
	 * @param   string         $taxonomy_name          Taxonomy Name.
	 * @param   bool|int|array $selected_term_ids      Selected Term ID(s) (false | int | array of integers).
	 */
	public function get_tree_view( $taxonomy_name, $selected_term_ids = false ) {

		// Define walker class to use for this Tree View.
		$walker = new Media_Categories_Module_Tree_View_Taxonomy_Walker();

		// Build args.
		$args = array(
			'echo'       => 0,
			'hide_empty' => 0,
			// 'exclude' => get_option('default_term_' . $taxonomy_name),
			'show_count' => 1,
			'taxonomy'   => $taxonomy_name,
			'title_li'   => 0,
			'walker'     => $walker,
		);

		$user = wp_get_current_user();
		$user_roles = $user->roles;

		// If logged in as an Administrator, prevent PublishPress Permissions from attempting to filter Term counts,
		// otherwise they will display as zero for Administrators (other User Roles are unaffected).
		if ( is_user_logged_in() && in_array( 'administrator', array_values( $user_roles ) ) ) {
			$args['pp_no_filter'] = true;
		}

		// If a current Term ID is specified, add it to the arguments now.
		if ( $selected_term_ids !== false ) {
			// Cast integers.
			if ( is_array( $selected_term_ids ) ) {
				foreach ( $selected_term_ids as $index => $selected_term_id ) {
					$selected_term_ids[ $index ] = absint( $selected_term_id );
				}
			} else {
				$selected_term_ids = absint( $selected_term_ids );
			}

			$args['current_category'] = $selected_term_ids;
		}

		// Output.
		$output = '<ul>
            <li class="cat-item-all">
                <a href="' . $this->get_all_terms_link() . '">' . __( 'All Media', 'admin-site-enhancements' ) . '</a>
            </li>
            <li class="cat-item-unassigned">
                <a href="' . $this->get_unassigned_term_link( $taxonomy_name ) . '">' . __( 'Uncategorized', 'admin-site-enhancements' ) . '</a>
            </li>' .
			wp_list_categories( $args ) /* @phpstan-ignore-line */ . '
        </ul>';

		// Return.
		return $output;

	}

	/**
	 * Returns the All Categories contextual link, depending on the screen
	 * we're on.
	 *
	 * @since   1.1.1
	 *
	 * @return  string  URL
	 */
	public function get_all_terms_link() {

		return add_query_arg( $this->get_filters(), 'upload.php' );

	}

	/**
	 * Returns the Uncategorized contextual link, depending on the screen
	 * we're on.
	 *
	 * @since   1.1.1
	 *
	 * @param   string $taxonomy_name  Taxonomy Name.
	 * @return  string                  URL
	 */
	public function get_unassigned_term_link( $taxonomy_name ) {

		return add_query_arg(
			array_merge(
				$this->get_filters(),
				array(
					$taxonomy_name => -1,
					// $taxonomy_name => 'uncategorized',
				)
			),
			'upload.php'
		);

	}

	/**
	 * Returns an array of filters that the user might have applied to the Media Library view
	 *
	 * @since   1.1.1
	 *
	 * @return  array   Filters
	 */
	private function get_filters() {

		$args       = array(
			'mode' => Media_Categories_Module()->get_class( 'common' )->get_media_view(),
		);
		$conditions = array(
			'attachment-filter',
			'm',
			'orderby',
			'order',
			'paged',
		);
		foreach ( $conditions as $condition ) {
			if ( ! isset( $_REQUEST[ $condition ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				continue;
			}

			if ( empty( $_REQUEST[ $condition ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				continue;
			}

			$args[ $condition ] = sanitize_text_field( $_REQUEST[ $condition ] ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		return $args;

	}

}
