<?php

/**
 * @since      1.0.0
 * @package   Tax_Terms_Order
 * @subpackage Tax_Terms_Order/includes
 */
class Tax_Terms_Order {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		$this->version = TAX_TERMS_ORDER_VERSION;
		$this->plugin_name = 'tax-terms-order';

		$this->load_dependencies();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies.
	 *
	 * - Terms_Order_Loader. Orchestrates the hooks of the plugin.
	 * - Terms_Order_Admin. Defines all hooks for the admin area.
	 *
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-tax-terms-order-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tax-terms-order-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-tax-terms-order-walker.php';

		$this->loader = new Tax_Terms_Order_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Tax_Terms_Order_Admin( $this->get_plugin_name(), $this->get_version() );

		// Only load assets on taxonomy terms ordering page

		$request_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] ); // e.g. /wp-admin/index.php?page=page-slug

		if ( false !== strpos( $request_uri, '-terms-order' ) ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );			
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->add_term_order_column_in_terms_table();
		$this->loader->run();
	}
	
	function add_term_order_column_in_terms_table() {
        global $wpdb;
        $query = "SHOW COLUMNS FROM $wpdb->terms LIKE 'term_order'";
        $result = $wpdb->query( $query );
        if ( 0 == $result ){
            $query = "ALTER TABLE $wpdb->terms ADD `term_order` INT( 4 ) NULL DEFAULT '0'";
            $result = $wpdb->query( $query ); 
        }

    }
	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Terms_Order_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
