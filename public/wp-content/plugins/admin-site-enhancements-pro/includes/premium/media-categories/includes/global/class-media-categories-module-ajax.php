<?php
/**
 * AJAX class.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

/**
 * Registers AJAX actions for Term management, Attachment editing and Search.
 *
 * @since   1.0.9
 */
class Media_Categories_Module_AJAX {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.0.9
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.0.9
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_action( 'wp_ajax_media_categories_module_add_term', array( $this, 'add_term' ) );
		add_action( 'wp_ajax_media_categories_module_edit_term', array( $this, 'edit_term' ) );
		add_action( 'wp_ajax_media_categories_module_delete_term', array( $this, 'delete_term' ) );
		add_action( 'wp_ajax_media_categories_module_categorize_attachments', array( $this, 'categorize_attachments' ) );
		add_action( 'wp_ajax_media_categories_module_search_authors', array( $this, 'search_authors' ) );
		add_action( 'wp_ajax_media_categories_module_search_taxonomy_terms', array( $this, 'search_taxonomy_terms' ) );
		add_action( 'wp_ajax_media_categories_module_get_taxonomies_terms', array( $this, 'get_taxonomies_terms' ) );
		add_action( 'wp_ajax_media_categories_module_get_taxonomy_terms', array( $this, 'get_taxonomy_terms' ) );

	}

	/**
	 * Adds a Term
	 *
	 * @since   1.1.1
	 */
	public function add_term() {

		// Check nonce.
		check_ajax_referer( 'media_categories_module_add_term', 'nonce' );

		// Get vars.
		$taxonomy_name  = sanitize_text_field( $_REQUEST['taxonomy_name'] );
		$term_name      = sanitize_text_field( $_REQUEST['term_name'] );
		$term_parent_id = sanitize_text_field( $_REQUEST['term_parent_id'] );
		$term_id        = $this->base->get_class( 'taxonomies' )->create_term( $taxonomy_name, $term_name, $term_parent_id );

		// Bail if Term ID is a WP_Error.
		if ( is_wp_error( $term_id ) ) {
			wp_send_json_error( $term_id->get_error_message() );
		}

		// Get Taxonomy and Term.
		$taxonomy = $this->base->get_class( 'taxonomies' )->get_taxonomy( $taxonomy_name );
		$term     = get_term_by( 'id', $term_id, $taxonomy_name );

		// Return success with created Term, List View compatible dropdown filter and Grid View Edit Attachment checkbox reflecting changes.
		wp_send_json_success(
			array(
				// The Created Term.
				'term'            => $term,
				// The List View <select> dropdown filter, reflecting the changes i.e. the new Term.
				'dropdown_filter' => $this->base->get_class( 'media' )->get_list_table_category_filter( $taxonomy_name, $taxonomy->label ),
				// The Grid View Edit Attachment <li> checkbox, which can be injected into the Edit Attachment Backbone modal.
				'checkbox'        => $this->base->get_class( 'media' )->get_grid_edit_attachment_checkbox( $taxonomy_name, $term ),
				// The Taxonomy.
				'taxonomy'        => $taxonomy,
				// All Terms.
				'terms'           => $this->base->get_class( 'common' )->get_terms_hierarchical( $taxonomy_name ),
			)
		);

	}

	/**
	 * Edit a Term
	 *
	 * @since   1.1.1
	 */
	public function edit_term() {

		// Check nonce.
		check_ajax_referer( 'media_categories_module_edit_term', 'nonce' );

		// Get vars.
		$taxonomy_name = sanitize_text_field( $_REQUEST['taxonomy_name'] );
		$term_id       = absint( $_REQUEST['term_id'] );
		$term_name     = sanitize_text_field( $_REQUEST['term_name'] );

		// Get what will become the Old Term.
		$old_term = get_term_by( 'id', $term_id, $taxonomy_name );

		// Bail if the (Old) Term doesn't exist.
		if ( ! $old_term ) {
			wp_send_json_error( __( 'Category does not exist, so cannot be deleted', 'admin-site-enhancements' ) );
		}

		// Update Term.
		$result = $this->base->get_class( 'taxonomies' )->update_term( $taxonomy_name, $term_id, $term_name );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Get Taxonomy.
		$taxonomy = $this->base->get_class( 'taxonomies' )->get_taxonomy( $taxonomy_name );

		// Return success with old term, edited Term and List View compatible dropdown filter reflecting changes.
		wp_send_json_success(
			array(
				// Old Term.
				'old_term'        => $old_term,
				// New (Edited) Term.
				'term'            => get_term_by( 'id', $term_id, $taxonomy_name ),
				// The List View <select> dropdown filter, reflecting the changes i.e. the edited Term.
				'dropdown_filter' => $this->base->get_class( 'media' )->get_list_table_category_filter( $taxonomy_name, $taxonomy->label ),
				// The Taxonomy.
				'taxonomy'        => $taxonomy,
				// All Terms.
				'terms'           => $this->base->get_class( 'common' )->get_terms_hierarchical( $taxonomy_name ),
			)
		);

	}

	/**
	 * Delete a Term
	 *
	 * @since   1.1.1
	 */
	public function delete_term() {

		// Check nonce.
		check_ajax_referer( 'media_categories_module_delete_term', 'nonce' );

		// Get vars.
		$taxonomy_name = sanitize_text_field( $_REQUEST['taxonomy_name'] );
		$term_id       = absint( $_REQUEST['term_id'] );

		// Get Term.
		$term = get_term_by( 'id', $term_id, $taxonomy_name );

		// Bail if the Term doesn't exist.
		if ( ! $term ) {
			wp_send_json_error( __( 'Term does not exist, so cannot be deleted', 'admin-site-enhancements' ) );
		}

		// Delete Term.
		$result = $this->base->get_class( 'taxonomies' )->delete_term( $taxonomy_name, $term_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Get Taxonomy.
		$taxonomy = $this->base->get_class( 'taxonomies' )->get_taxonomy( $taxonomy_name );

		// Return success with deleted Term and List View compatible dropdown filter reflecting changes.
		wp_send_json_success(
			array(
				// Deleted Term.
				'term'            => $term,
				// The List View <select> dropdown filter, reflecting the changes i.e. the deleted Term.
				'dropdown_filter' => $this->base->get_class( 'media' )->get_list_table_category_filter( $taxonomy_name, $taxonomy->label ),
				// The Taxonomy.
				'taxonomy'        => $taxonomy,
				// All Terms.
				'terms'           => $this->base->get_class( 'common' )->get_terms_hierarchical( $taxonomy_name ),
			)
		);

	}

	/**
	 * Categorizes the given Attachment IDs with the given Term ID
	 *
	 * @since   1.1.1
	 */
	public function categorize_attachments() {

		// Check nonce.
		check_ajax_referer( 'media_categories_module_categorize_attachments', 'nonce' );

		// Get vars.
		$taxonomy_name  = sanitize_text_field( $_REQUEST['taxonomy_name'] );
		$term_id        = (int) sanitize_text_field( $_REQUEST['term_id'] );
		$attachment_ids = $_REQUEST['attachment_ids'];

		$attachments = array();
		$parent_terms = [];
		foreach ( $attachment_ids as $attachment_id ) {
			// Get attachment.
			$attachment = new Media_Categories_Module_Attachment( absint( $attachment_id ) );

			// If the Term ID is -1, remove Terms.
			// Otherwise append them.
			if ( $term_id === -1 ) {
				$attachment->remove_terms( $taxonomy_name );
			} else {
				$attachment->append_terms( $taxonomy_name, array( $term_id ) );
				$term = get_term( $term_id, $taxonomy_name );
		        if ( ! is_wp_error( $term ) && $term->parent > 0 ) {
		            $parent_terms[] = $term->parent;
		            // We detect and include parent, grandparent and grand grandparent terms
		            $parent_term_id = $term->parent;
		            $parent_term = get_term( $parent_term_id, $taxonomy_name );
		            if ( ! is_wp_error( $parent_term ) && $parent_term->parent > 0 ) {
		            	$parent_terms[] = $parent_term->parent;
			            $grandparent_term_id = $parent_term->parent;
			            $grandparent_term = get_term( $grandparent_term_id, $taxonomy_name );
			            if ( ! is_wp_error( $grandparent_term ) && $grandparent_term->parent > 0 ) {
			            	$parent_terms[] = $grandparent_term->parent;
				            $grandgrandparent_term_id = $grandparent_term->parent;
				            $grandgrandparent_term = get_term( $grandgrandparent_term_id, $taxonomy_name );
				            if ( ! is_wp_error( $grandgrandparent_term ) && $grandgrandparent_term->parent > 0 ) {
				            	$parent_terms[] = $grandgrandparent_term->parent;
				            }
			            }
		            }
		        }
			}

			// Update the Attachment.
			$result = $attachment->update();
			
			// Append parent categories
		    $append = true;
		    wp_set_object_terms( $attachment_id, $parent_terms, $taxonomy_name, $append );

			// Bail if an error occured.
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( $result->get_error_message() );
			}

			// Add to return data.
			$attachments[] = array(
				'id'    => $attachment_id,
				'terms' => wp_get_post_terms( $attachment_id, $taxonomy_name ),
			);

			// Destroy the class.
			unset( $attachment );
		}

		// Get Taxonomy.
		$taxonomy = $this->base->get_class( 'taxonomies' )->get_taxonomy( $taxonomy_name );

		// Return the Attachment IDs and their Categories.
		wp_send_json_success(
			array(
				// Attachments updated, with Terms.
				'attachments'     => $attachments,
				// Term Assigned to Attachments.
				'term'            => get_term_by( 'id', $term_id, $taxonomy_name ),
				// The List View <select> dropdown filter, reflecting the changes i.e. the edited Term.
				'dropdown_filter' => $this->base->get_class( 'media' )->get_list_table_category_filter( $taxonomy_name, $taxonomy->label ),
				// The Taxonomy.
				'taxonomy'        => $taxonomy,
				// All Terms.
				'terms'           => $this->base->get_class( 'common' )->get_terms_hierarchical( $taxonomy_name ),
			)
		);

	}

	/**
	 * Searches for Authors for the given freeform text
	 *
	 * @since   1.0.9
	 */
	public function search_authors() {

		// Check nonce.
		check_ajax_referer( 'media_categories_module_search_authors', 'nonce' );

		// Get vars.
		$query = sanitize_text_field( $_REQUEST['query'] );

		// Get results.
		$users = new WP_User_Query(
			array(
				'search' => '*' . $query . '*',
			)
		);

		// Build array.
		$users_array = array();
		$results     = $users->get_results();
		if ( ! empty( $results ) ) {
			foreach ( $results as $user ) {
				$users_array[] = array(
					'id'         => $user->ID,
					'user_login' => $user->user_login,
				);
			}
		}

		// Done.
		wp_send_json_success( $users_array );

	}

	/**
	 * Searches Categories for the given freeform text
	 *
	 * @since   1.0.9
	 */
	public function search_taxonomy_terms() {

		// Check nonce.
		check_ajax_referer( 'media_categories_module_search_taxonomy_terms', 'nonce' );

		// Get vars.
		$taxonomy_name = false;
		if ( isset( $_REQUEST['taxonomy_name'] ) ) {
			$taxonomy_name = sanitize_text_field( $_REQUEST['taxonomy_name'] );
		} elseif ( isset( $_REQUEST['args'] ) && isset( $_REQUEST['args']['taxonomy_name'] ) ) {
			$taxonomy_name = sanitize_text_field( $_REQUEST['args']['taxonomy_name'] );
		}
		$query = sanitize_text_field( $_REQUEST['query'] );

		// Bail if no Taxonomy Name specified.
		if ( ! $taxonomy_name ) {
			return wp_send_json_error( __( 'The taxonomy_name or args[taxonomy_name] parameter must be included in the request.', 'admin-site-enhancements' ) );
		}

		// Get results.
		$terms = new WP_Term_Query(
			array(
				'taxonomy'   => $taxonomy_name,
				'search'     => $query,
				'hide_empty' => false,
			)
		);

		// Build array.
		$terms_array = array();
		if ( ! empty( $terms->terms ) ) {
			foreach ( $terms->terms as $term ) {
				$terms_array[] = array(
					'id'   => $term->term_id,
					'term' => $term->name,
					'slug' => $term->slug,
				);
			}
		}

		// Done.
		wp_send_json_success( $terms_array );

	}

	/**
	 * Returns all Terms for all Taxonomies
	 *
	 * @since   1.3.3
	 */
	public function get_taxonomies_terms() {

		// Check nonce.
		check_ajax_referer( 'media_categories_module_get_taxonomies_terms', 'nonce' );

		// Iterate through Taxonomies.
		$response = array();
		foreach ( $this->base->get_class( 'taxonomies' )->get_taxonomies() as $taxonomy_name => $taxonomy ) {
			$response[ $taxonomy_name ] = array(
				'taxonomy' => $this->base->get_class( 'taxonomies' )->get_taxonomy( $taxonomy_name ),
				'terms'    => $this->base->get_class( 'common' )->get_terms_hierarchical( $taxonomy_name ),
			);
		}

		// Return success with Taxonomies and Terms.
		wp_send_json_success( $response );

	}

	/**
	 * Returns all Terms for the given Taxonomy
	 *
	 * @since   1.3.3
	 */
	public function get_taxonomy_terms() {

		// Check nonce.
		check_ajax_referer( 'media_categories_module_get_taxonomy_terms', 'nonce' );

		// Get vars.
		$taxonomy_name = sanitize_text_field( $_REQUEST['taxonomy_name'] );

		// Return success with Taxonomy and Terms.
		wp_send_json_success(
			array(
				'taxonomy' => $this->base->get_class( 'taxonomies' )->get_taxonomy( $taxonomy_name ),
				'terms'    => $this->base->get_class( 'common' )->get_terms_hierarchical( $taxonomy_name ),
			)
		);

	}

}
