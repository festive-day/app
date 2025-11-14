<?php
/**
 * Media Categories Module class.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

/**
 * Main Media Categories Module class, used to load the Plugin.
 *
 * @package   Media_Categories_Module
 * @author    WP Media Library
 * @version   1.0.0
 */
class Media_Categories_Module {

	/**
	 * Holds the class object.
	 *
	 * @since   1.0.0
	 *
	 * @var     object
	 */
	public static $instance;

	/**
	 * Plugin
	 *
	 * @since   1.0.0
	 *
	 * @var     object
	 */
	public $plugin;

	/**
	 * Dashboard
	 *
	 * @since   1.0.5
	 *
	 * @var     object
	 */
	public $dashboard;

	/**
	 * Classes
	 *
	 * @since   1.0.5
	 *
	 * @var     object
	 */
	public $classes;

	/**
	 * Constructor. Acts as a bootstrap to load the rest of the plugin
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		// Plugin Details.
		$this->plugin                    = new stdClass();
		$this->plugin->name              = 'media-categories-module';
		$this->plugin->displayName       = 'Media Categories Module';
		$this->plugin->author_name       = 'Media Categories Module';
		$this->plugin->version           = ASENHA_VERSION;
		$this->plugin->requires          = '5.0';
		$this->plugin->tested            = '5.9.3';
		$this->plugin->folder            = MEDIA_CATEGORIES_MODULE_PATH;
		$this->plugin->url               = MEDIA_CATEGORIES_MODULE_URL;
		$this->plugin->documentation_url = '#';
		$this->plugin->support_url       = '#';
		$this->plugin->upgrade_url       = '#';
		$this->plugin->review_name       = 'media-categories-module';
		// $this->plugin->review_notice     = sprintf(
		// 	/* translators: Plugin Name */
		// 	__( 'Thanks for using %s to organize your Media Library!', 'admin-site-enhancements' ),
		// 	$this->plugin->displayName
		// );
		$this->plugin->review_notice     = 'Thanks for using ' . $this->plugin->displayName . ' to organize your Media Library!';
		// Dashboard Submodule.
		if ( ! class_exists( 'WPZincDashboardWidget' ) ) {
			require_once $this->plugin->folder . '_modules/dashboard/class-wpzincdashboardwidget.php';
		}
		$this->dashboard = new WPZincDashboardWidget( $this->plugin );

		// Initialize Free Addons.
		$this->initialize_free_addons();

		// Defer loading of Plugin Classes.
		add_action( 'init', array( $this, 'initialize' ), 1 );
		add_action( 'init', array( $this, 'upgrade' ), 2 );

	}

	/**
	 * Initialize Free Addons
	 *
	 * @since   1.1.4
	 */
	private function initialize_free_addons() {

		// Define Addons Directory.
		$addons_dir = MEDIA_CATEGORIES_MODULE_PATH . '/addons/';

		// Iterate through Addons Directory.
		$files_dirs = scandir( $addons_dir );
		foreach ( $files_dirs as $file_dir ) {
			// Skip dot folders.
			if ( $file_dir === '.' || $file_dir === '..' ) {
				continue;
			}

			// Skip if Addon directory doesn't exist.
			if ( ! is_dir( $addons_dir . $file_dir ) ) {
				continue;
			}

			// Skip if Addon bootstrap file doesn't exist.
			$file = $addons_dir . $file_dir . '/class-media-categories-module-' . $file_dir . '.php';
			if ( ! file_exists( $file ) ) {
				continue;
			}

			// Load Addon.
			require_once $file;
		}

	}

	/**
	 * Initializes classes and Free Addons.
	 *
	 * @since   1.0.5
	 */
	public function initialize() {

		$this->classes = new stdClass();

		$this->initialize_admin();
		$this->initialize_frontend();
		$this->initialize_admin_or_frontend_editor();
		// $this->initialize_cli();
		$this->initialize_global();

	}

	/**
	 * Initialize classes for the WordPress Administration interface
	 *
	 * @since   1.0.9
	 */
	private function initialize_admin() {

		// Bail if this request isn't for the WordPress Administration interface.
		if ( ! is_admin() ) {
			return;
		}

		$this->classes->admin      = new Media_Categories_Module_Admin( self::$instance );
		$this->classes->admin_ajax = new Media_Categories_Module_Admin_AJAX( self::$instance );
		// $this->classes->export     = new Media_Categories_Module_Export( self::$instance );
		// $this->classes->import     = new Media_Categories_Module_Import( self::$instance );

	}

	/**
	 * Initialize classes for the frontend web site
	 *
	 * @since   1.0.9
	 */
	private function initialize_frontend() {

		// Bail if this request isn't for the frontend web site.
		if ( is_admin() ) {
			return;
		}

		$this->classes->frontend = new Media_Categories_Module_Frontend( self::$instance );

	}

	/**
	 * Initialize classes for WP-CLI
	 *
	 * @since   1.0.9
	 */
	private function initialize_cli() {

		// Bail if WP-CLI isn't installed on the server.
		// if ( ! class_exists( 'WP_CLI' ) ) {
		// 	return;
		// }

		// In CLI mode, is_admin() is not called, so we need to require the classes that
		// the CLI commands may use.
		// $this->classes->cli = new Media_Categories_Module_CLI( self::$instance );

	}

	/**
	 * Initialize classes for the WordPress Administration interface or a frontend Page Builder
	 *
	 * @since   1.0.9
	 */
	private function initialize_admin_or_frontend_editor() {

		// Bail if this request isn't for the WordPress Administration interface and isn't for a frontend Page Builder.
		if ( ! $this->is_admin_or_frontend_editor() ) {
			return;
		}

		$this->classes->ajax          = new Media_Categories_Module_AJAX( self::$instance );
		$this->classes->editor        = new Media_Categories_Module_Editor( self::$instance );
		$this->classes->page_builders = new Media_Categories_Module_Page_Builders( self::$instance );
		$this->classes->tinymce       = new Media_Categories_Module_TinyMCE( self::$instance );

	}

	/**
	 * Initialize classes used everywhere
	 *
	 * @since   1.0.9
	 */
	private function initialize_global() {

		$this->classes->common       = new Media_Categories_Module_Common( self::$instance );
		// $this->classes->dynamic_tags = new Media_Categories_Module_Dynamic_Tags( self::$instance );
		$this->classes->filesystem   = new Media_Categories_Module_Filesystem( self::$instance );
		$this->classes->install      = new Media_Categories_Module_Install( self::$instance );
		$this->classes->media        = new Media_Categories_Module_Media( self::$instance );
		$this->classes->mime         = new Media_Categories_Module_MIME( self::$instance );
		$this->classes->notices      = new Media_Categories_Module_Notices( self::$instance );
		$this->classes->settings     = new Media_Categories_Module_Settings( self::$instance );
		$this->classes->shortcode    = new Media_Categories_Module_Shortcode( self::$instance );
		$this->classes->taxonomies   = new Media_Categories_Module_Taxonomies( self::$instance );
		$this->classes->upload       = new Media_Categories_Module_Upload( self::$instance );
		$this->classes->user_option  = new Media_Categories_Module_User_Option( self::$instance );

	}

	/**
	 * Improved version of WordPress' is_admin(), which includes whether we're
	 * editing on the frontend using a Page Builder, or a developer / Addon
	 * wants to load Editor, Media Management and Upload classes on the frontend
	 * of the site.
	 *
	 * @since   1.0.7
	 *
	 * @return  bool    Is Admin or Frontend Editor Request
	 */
	public function is_admin_or_frontend_editor() {

		// If we're in the wp-admin, return true.
		if ( is_admin() ) {
			return true;
		}

		// Pro.
		// if ( ! empty( $_SERVER ) ) {
		// 	if ( strpos( sanitize_text_field( $_SERVER['REQUEST_URI'] ), '/pro/' ) !== false ) {
		// 		return true;
		// 	}
		// 	if ( strpos( sanitize_text_field( $_SERVER['REQUEST_URI'] ), '/x/' ) !== false ) {
		// 		return true;
		// 	}
		// 	if ( strpos( sanitize_text_field( $_SERVER['REQUEST_URI'] ), 'cornerstone-endpoint' ) !== false ) {
		// 		return true;
		// 	}
		// }

		// If the request global exists, check for specific request keys which tell us
		// that we're using a frontend editor.
		if ( ! empty( $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			// Sanitize request.
			$request = map_deep( $_REQUEST, 'sanitize_text_field' ); // phpcs:ignore WordPress.Security.NonceVerification

			// Beaver Builder.
			if ( array_key_exists( 'fl_builder', $request ) ) {
				return true;
			}

			// Cornerstone (AJAX).
			if ( array_key_exists( '_cs_nonce', $request ) ) {
				return true;
			}

			// Divi.
			if ( array_key_exists( 'et_fb', $request ) ) {
				return true;
			}

			// Elementor.
			if ( array_key_exists( 'action', $request ) && $request['action'] === 'elementor' ) {
				return true;
			}

			// Kallyas.
			if ( array_key_exists( 'zn_pb_edit', $request ) ) {
				return true;
			}

			// Oxygen.
			if ( array_key_exists( 'ct_builder', $request ) ) {
				return true;
			}

			// Themify Builder.
			if ( array_key_exists( 'tb-preview', $request ) && array_key_exists( 'tb-id', $request ) ) {
				return true;
			}

			// Thrive Architect.
			if ( array_key_exists( 'tve', $request ) ) {
				return true;
			}

			// Visual Composer.
			if ( array_key_exists( 'vcv-editable', $request ) ) {
				return true;
			}

			// WPBakery Page Builder.
			if ( array_key_exists( 'vc_editable', $request ) ) {
				return true;
			}
		} else {
			$request = false;
		}

		// Assume we're not in the Administration interface.
		$is_admin_or_frontend_editor = false;

		/**
		 * Filters whether the current request is a WordPress Administration / Frontend Editor request or not.
		 *
		 * Page Builders can set this to true to allow Media Categories Module and its Addons to load its
		 * functionality.
		 *
		 * @since   1.0.7
		 *
		 * @param   bool    $is_admin_or_frontend_editor    Is WordPress Administration / Frontend Editor request.
		 * @param   array   $request                        Sanitized request data.
		 */
		$is_admin_or_frontend_editor = apply_filters( 'media_categories_module_is_admin_or_frontend_editor', $is_admin_or_frontend_editor, $request );

		// Return filtered result.
		return $is_admin_or_frontend_editor;

	}

	/**
	 * Runs the upgrade routine once the plugin has loaded
	 *
	 * @since   1.0.5
	 */
	public function upgrade() {

		// Bail if we're not in the WordPress Admin.
		if ( ! is_admin() ) {
			return;
		}

		// Run upgrade routine.
		$this->get_class( 'install' )->upgrade();

	}

	/**
	 * Returns the given class
	 *
	 * @since   1.0.5
	 *
	 * @param   string $name   Class Name.
	 */
	public function get_class( $name ) {

		// If the class hasn't been loaded, throw a WordPress die screen
		// to avoid a PHP fatal error.
		if ( ! isset( $this->classes->{ $name } ) ) {
			// Define the error.
			$error = new WP_Error(
				'media_categories_module_get_class',
				// sprintf(
				// 	/* translators: %1$s: Plugin Name, %2$s: PHP class name */
				// 	__( '%1$s: Error: Could not load Plugin class %2$s', 'admin-site-enhancements' ),
				// 	$this->plugin->displayName,
				// 	$name
				// )
				$this->plugin->displayName . ': Error: Could not load Plugin class ' . $name
			);

			// Depending on the request, return or display an error.
			// Admin UI.
			if ( is_admin() ) {
				wp_die(
					esc_html( $error->get_error_message() ),
					esc_html( $this->plugin->displayName . ': Error' ),
					array(
						'back_link' => true,
					)
				);
			}

			// Cron / CLI.
			return $error;
		}

		// Return the class object.
		return $this->classes->{ $name };

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since   1.0.0
	 *
	 * @return  object Class.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) { // @phpstan-ignore-line.
			self::$instance = new self();
		}

		return self::$instance;

	}

}
