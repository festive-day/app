<?php

defined( 'ABSPATH' ) || die();

class Form_Builder_Loader {

    public function __construct() {
        add_filter( 'admin_body_class', array( $this, 'add_admin_class' ), 999 );
        add_filter( 'screen_options_show_screen', array( $this, 'remove_screen_options' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_init' ), 11 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 11 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'elementor_editor_styles' ) );
    }

    /**
     * Add body class to make form builder full screen
     */
    public static function add_admin_class( $classes ) {
        if ( Form_Builder_Helper::is_form_builder_page() ) {
            // $full_screen_on = self::get_full_screen_setting();
            // if ( $full_screen_on ) {
            //     $classes .= ' is-fullscreen-mode';
            //     wp_enqueue_style( 'wp-edit-post' ); // Load the CSS for .is-fullscreen-mode.
            // }
            $formbuilder_action = isset( $_GET['formbuilder_action'] ) ? ' ' . $_GET['formbuilder_action'] : '';
            $classes .= ' is-form-builder' . $formbuilder_action;
        }
        return $classes;
    }
    
    /**
     * Remove Screen Options button
     */
    public function remove_screen_options() {
        if ( Form_Builder_Helper::is_form_builder_page() ) {
            return false;
        } else {
            return true;
        }
    }

    private static function get_full_screen_setting() {
        global $wpdb;
        $meta_key = $wpdb->get_blog_prefix() . 'persisted_preferences';
        $prefs = get_user_meta(get_current_user_id(), $meta_key, true );
        if ( $prefs && isset( $prefs['core/edit-post']['fullscreenMode'] ) )
            return $prefs['core/edit-post']['fullscreenMode'];
        return true;
    }

    public static function admin_init() {
        global $pagenow, $typenow, $hook_suffix;
        $current_screen = get_current_screen();
        
        $page = Form_Builder_Helper::get_var( 'page', 'sanitize_title' );
        if ( strpos( $page, 'formbuilder' ) === 0 ) {
            wp_enqueue_media();
            wp_enqueue_script( 'formbuilder-builder', FORMBUILDER_URL . 'assets/js/builder.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'wp-i18n', 'wp-hooks', 'jquery-ui-dialog', 'formbuilder-select2' ), FORMBUILDER_VERSION, true );
            wp_enqueue_script( 'formbuilder-backend', FORMBUILDER_URL . 'assets/js/backend.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'wp-i18n', 'wp-hooks', 'jquery-ui-dialog', 'jquery-ui-datepicker', 'wp-mediaelement' ), FORMBUILDER_VERSION, true );

            wp_localize_script( 'formbuilder-backend', 'formbuilder_backend_js', array(
                'nonce' => wp_create_nonce( 'formbuilder_ajax' ),
                'matrix_select_placeholder' => __( 'Choose one', 'admin-site-enhancements' ),
            ) );
        }

        if ( strpos( $page, 'formbuilder' ) === 0 
            || 'formbuilder-styles' === $typenow
        ) {
            wp_enqueue_script( 'formbuilder-chosen', FORMBUILDER_URL . 'assets/js/chosen.jquery.js', array( 'jquery' ), FORMBUILDER_VERSION, true );
            wp_enqueue_script( 'formbuilder-select2', FORMBUILDER_URL . 'assets/js/select2.min.js', array( 'jquery' ), FORMBUILDER_VERSION, true );
            wp_enqueue_script( 'jquery-condition', FORMBUILDER_URL . 'assets/js/jquery-condition.js', array( 'jquery' ), FORMBUILDER_VERSION, true );
            wp_enqueue_script( 'wp-color-picker-alpha', FORMBUILDER_URL . 'assets/js/wp-color-picker-alpha.js', array( 'wp-color-picker' ), FORMBUILDER_VERSION, true );
            wp_enqueue_script( 'formbuilder-admin-settings', FORMBUILDER_URL . 'assets/js/admin-settings.js', array( 'jquery' ), FORMBUILDER_VERSION, true );

            wp_localize_script( 'formbuilder-admin-settings', 'formbuilder_admin_js_obj', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce( 'formbuilder-ajax-nonce' ),
                'error' => esc_html__( 'Error! Reload the page and try again.', 'admin-site-enhancements' ),
            ) );

            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_style( 'formbuilder-chosen', FORMBUILDER_URL . 'assets/css/chosen.css', array(), FORMBUILDER_VERSION);
            wp_enqueue_style( 'formbuilder-select2', FORMBUILDER_URL . 'assets/css/select2.min.css', array(), FORMBUILDER_VERSION);
            wp_enqueue_style( 'formbuilder-admin', FORMBUILDER_URL . 'assets/css/admin-style.css', array(), FORMBUILDER_VERSION);
            wp_enqueue_style( 'formbuilder-file-uploader', FORMBUILDER_URL . 'assets/css/file-uploader.css', array(), FORMBUILDER_VERSION);
            wp_enqueue_style( 'formbuilder-admin-settings', FORMBUILDER_URL . 'assets/css/admin-settings.css', array(), FORMBUILDER_VERSION);
            wp_enqueue_style( 'formbuilder-style', FORMBUILDER_URL . 'assets/css/style.css', array(), FORMBUILDER_VERSION);            
        }
        
        // Form listing page
        if ( 'admin.php' == $pagenow && 'toplevel_page_formbuilder' == $hook_suffix ) {
            wp_enqueue_script( 'formbuilder-popper', FORMBUILDER_URL . 'assets/js/popper.min.js', array() );
            wp_enqueue_script( 'formbuilder-tippy', FORMBUILDER_URL . 'assets/js/tippy-bundle.umd.min.js', array( 'formbuilder-popper' ) );
            wp_enqueue_script( 'formbuilder-clipboard-js', FORMBUILDER_URL . 'assets/js/clipboard.min.js', array( 'jquery', 'formbuilder-tippy' ) );
        }

        $fonts_url = Form_Builder_Styles::fonts_url();

        // Load Fonts if necessary.
        if ( $fonts_url ) {
            wp_enqueue_style( 'formbuilder-fonts', $fonts_url, array(), false );
        }
    }

    public static function elementor_editor_styles() {
        // wp_enqueue_style( 'formbuilder-icons', FORMBUILDER_URL . 'assets/fonts/fb-icons.css', array(), FORMBUILDER_VERSION);
    }

    public static function enqueue_styles() {
        // wp_enqueue_style( 'dashicons' );
        wp_register_style( 'jquery-timepicker', FORMBUILDER_URL . 'assets/css/jquery.timepicker.css', array(), FORMBUILDER_VERSION);
        wp_register_style( 'formbuilder-file-uploader', FORMBUILDER_URL . 'assets/css/file-uploader.css', array(), FORMBUILDER_VERSION);
        wp_register_style( 'formbuilder-style', FORMBUILDER_URL . 'assets/css/style.css', array(), FORMBUILDER_VERSION);

        $fonts_url = Form_Builder_Styles::fonts_url();

        if ( $fonts_url ) {
            wp_register_style( 'formbuilder-fonts', $fonts_url, array(), false );
        }

        // Styles are enqueued in /classes/form-builder-shortcode.php
        // Styles are enqueued in /classes/form-builder-block.php
        // Styles are enqueued in /forms/preview/preview.php
    }

    public static function enqueue_scripts() {
        // wp_enqueue_script( 'jquery-ui-slider' );
        wp_register_script( 'jquery-timepicker', FORMBUILDER_URL . 'assets/js/jquery.timepicker.min.js', array( 'jquery' ), FORMBUILDER_VERSION, true );
        wp_register_script( 'formbuilder-file-uploader', FORMBUILDER_URL . 'assets/js/file-uploader.js', array(), FORMBUILDER_VERSION, true );
        wp_localize_script( 'formbuilder-file-uploader', 'formbuilder_file_vars', array(
            'remove_txt' => esc_html( 'Remove', 'admin-site-enhancements' )
        ) );
        wp_register_script( 'moment', FORMBUILDER_URL . 'assets/js/moment.js', array(), FORMBUILDER_VERSION, true );
        wp_register_script( 'frontend', FORMBUILDER_URL . 'assets/js/frontend.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-timepicker', 'formbuilder-file-uploader', 'formbuilder-file-uploader' ), FORMBUILDER_VERSION, true );
        wp_localize_script( 'frontend', 'formbuilder_vars', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'ajax_nounce' => wp_create_nonce( 'formbuilder-upload-ajax-nonce' ),
            'preview_img' => '',
        ) );
        
        // Scripts are enqueued in /classes/form-builder-shortcode.php
        // Scripts are enqueued in /classes/form-builder-block.php
        // Scripts are enqueued in /forms/preview/preview.php
    }

}

new Form_Builder_Loader();
