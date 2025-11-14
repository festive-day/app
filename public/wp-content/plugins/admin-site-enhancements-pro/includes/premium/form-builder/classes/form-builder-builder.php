<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Builder {

    public function __construct() {

        $this->includes();

        add_action( 'admin_menu', array( $this, 'add_menu' ), 1 );
        add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );

        add_action( 'wp_ajax_formbuilder_update_form', array( $this, 'update_form' ) );
        add_action( 'wp_ajax_formbuilder_create_form', array( $this, 'create_form' ) );
        add_action( 'wp_ajax_formbuilder_save_form_settings', array( $this, 'save_form_settings' ) );
        add_action( 'wp_ajax_formbuilder_save_form_style', array( $this, 'save_form_style' ) );
        add_action( 'wp_ajax_formbuilder_add_more_condition_block', array( $this, 'add_more_condition_block' ) );
        add_action( 'admin_footer', array( $this, 'init_overlay_html' ) );

        add_filter( 'plugin_action_links_' . plugin_basename( FORMBUILDER_FILE ), array( $this, 'add_plugin_action_link' ), 10, 1 );

        add_action( 'wp_ajax_formbuilder_file_upload_action', array( $this, 'file_upload_action' ) );
        add_action( 'wp_ajax_nopriv_formbuilder_file_upload_action', array( $this, 'file_upload_action' ) );

        add_action( 'wp_ajax_formbuilder_file_delete_action', array( $this, 'file_delete_action' ) );
        add_action( 'wp_ajax_nopriv_formbuilder_file_delete_action', array( $this, 'file_delete_action' ) );

        add_action( 'wp_loaded', array( $this, 'admin_notice' ), 20 );
    }

    public function includes() {
        include FORMBUILDER_PATH . 'forms/sanitization.php';
    }

    public function add_menu() {
        global $formbuilder_listing_page;
        add_menu_page(
            esc_html__( 'Forms', 'admin-site-enhancements' ), 
            esc_html__( 'Forms', 'admin-site-enhancements' ), 
            'manage_options', 
            'formbuilder', 
            array( $this, 'route' ), 
            'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0OCIgaGVpZ2h0PSI0OCIgdmlld0JveD0iMCAwIDQ4IDQ4Ij48ZyBmaWxsPSJjdXJyZW50Q29sb3IiPjxwYXRoIGQ9Ik0xOSAyNS41YTQuNSA0LjUgMCAxIDEtOSAwYTQuNSA0LjUgMCAwIDEgOSAwbS0yLjUgMGEyIDIgMCAxIDAtNCAwYTIgMiAwIDAgMCA0IDBNMTAgMTUuMjVjMC0uNjkuNTYtMS4yNSAxLjI1LTEuMjVoMjAuNWExLjI1IDEuMjUgMCAxIDEgMCAyLjVoLTIwLjVjLS42OSAwLTEuMjUtLjU2LTEuMjUtMS4yNW0xMi4yNSA5LjI1YTEuMjUgMS4yNSAwIDEgMCAwIDIuNWg5LjVhMS4yNSAxLjI1IDAgMSAwIDAtMi41eiIvPjxwYXRoIGQ9Ik0xMC43NSA1QTUuNzUgNS43NSAwIDAgMCA1IDEwLjc1djIxLjVBNS43NSA1Ljc1IDAgMCAwIDEwLjc1IDM4aDIxLjVBNS43NSA1Ljc1IDAgMCAwIDM4IDMyLjI1di0yMS41QTUuNzUgNS43NSAwIDAgMCAzMi4yNSA1ek03LjUgMTAuNzVhMy4yNSAzLjI1IDAgMCAxIDMuMjUtMy4yNWgyMS41YTMuMjUgMy4yNSAwIDAgMSAzLjI1IDMuMjV2MjEuNWMwIC40NTYtLjA5NC44OS0uMjY0IDEuMjg1QTMuMjQgMy4yNCAwIDAgMSAzMi4yNSAzNS41aC0yMS41YTMuMjQgMy4yNCAwIDAgMS0yLjk5OS0xLjk5NUEzLjIgMy4yIDAgMCAxIDcuNSAzMi4yNXoiLz48cGF0aCBkPSJNMTUuMjUgNDIuNWE1Ljc0IDUuNzQgMCAwIDEtNC43NDctMi41MDRxLjEyMy4wMDQuMjQ3LjAwNGgyMS41QTcuNzUgNy43NSAwIDAgMCA0MCAzMi4yNXYtMjEuNXEwLS4xMjMtLjAwNC0uMjQ3QTUuNzQgNS43NCAwIDAgMSA0Mi41IDE1LjI1djE3YzAgNS42Ni00LjU5IDEwLjI1LTEwLjI1IDEwLjI1eiIvPjwvZz48L3N2Zz4=', 
            29
        );

        $formbuilder_listing_page = add_submenu_page(
            'formbuilder', 
            esc_html__( 'Forms', 'admin-site-enhancements' ), // Page title
            esc_html__( 'All Forms', 'admin-site-enhancements' ), // Menu title
            'manage_options', 
            'formbuilder', 
            array( $this, 'route' )
        );

        add_action("load-$formbuilder_listing_page", array( $this, 'listing_page_screen_options' ) );
    }

    public function route() {
        /* Gets formbuilder_action value else action value */
        $action = htmlspecialchars_decode( Form_Builder_Helper::get_var( 'formbuilder_action', 'sanitize_text_field', Form_Builder_Helper::get_var( 'action' ) ));

        if ( Form_Builder_Helper::get_var( 'delete_all' ) ) {
            $action = 'delete_all';
        }

        switch ( $action ) {
            case 'edit':
            case 'trash':
            case 'destroy':
            case 'untrash':
            case 'delete_all':
            case 'duplicate':
            case 'settings':
            case 'style':
                return self::$action();

            default:

                if (strpos( $action, 'bulk_' ) === 0 ) {
                    self::bulk_actions();
                    return;
                }

                self::display_forms_list();
                return;
        }
    }

    public static function display_message( $message, $class ) {
        if ( '' !== trim( $message ) ) {
            echo '<div id="message" class="' . esc_attr( $class ) . ' notice is-dismissible">';
            echo '<p>' . wp_kses_post( $message ) . '</p>';
            echo '</div>';
        }
    }

    public static function display_forms_list( $message = '', $class = 'updated' ) {
        ?>
        <div class="fb-content">
            <div class="fb-form-list-wrap wrap">
                <h1 class="wp-heading-inline"><?php echo __( 'Forms', 'admin-site-enhancements' ); ?></h1>
                <div class="fb-add-new-form">
                    <a href="#" class="button fb-trigger-modal"><?php esc_html_e( 'Add New Form', 'admin-site-enhancements' ); ?></a>
                </div>
                <hr class="wp-header-end">

                <?php
                self::display_message( $message, $class );
                $form_table = new Form_Builder_Listing();
                $form_status = Form_Builder_Helper::get_var( 'status', 'sanitize_title', 'published' );
                $form_table->views();
                ?>
                <form id="posts-filter" method="get">
                    <input type="hidden" name="page" value="<?php echo esc_attr( Form_Builder_Helper::get_var( 'page', 'sanitize_title' ) ); ?>" />
                    <input type="hidden" name="status" value="<?php echo esc_attr( $form_status ); ?>" />
                    <?php
                    $form_table->prepare_items();
                    $form_table->search_box( 'Search', 'search' );
                    $form_table->display();
                    ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function create_form() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer( 'formbuilder_ajax', 'nonce' );
        
        // Maybe create Form Builder four tables if they haven't been created
        // e.g. upgraded from previous version of ASE, so, no activation methods are fired
        global $wpdb;
        $table_name = $wpdb->prefix . 'asenha_formbuilder_forms';
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

        if ( $wpdb->get_var( $query ) === $table_name ) {
            // Table already exists, do nothing.
        } else {
            $activation = new ASENHA\CLasses\Activation;
            $activation->create_form_builder_tables();
        }

        $name = Form_Builder_Helper::get_post( 'name' );
        $new_values = array(
            'name' => esc_html( $name ),
            'description' => '',
            'form_key' => sanitize_text_field( $name ),
            'options' => array(
                'submit_value' => esc_html__( 'Submit', 'admin-site-enhancements' ),
                'show_description' => 'on',
                'show_title' => 'on',
            ),
            'settings' => Form_Builder_Helper::get_form_settings_default( $name )
        );
        $form_id = self::create( $new_values );
        $response = array( 'redirect' => admin_url( 'admin.php?page=formbuilder&formbuilder_action=edit&id=' . absint( $form_id ) ));
        echo wp_json_encode( $response );
        wp_die();
    }

    public static function create( $values ) {
        global $wpdb;
        $options = isset( $values['options'] ) && is_array( $values['options'] ) ? $values['options'] : array();
        $options = Form_Builder_Helper::recursive_parse_args( $options, Form_Builder_Helper::get_form_options_default() );
        $options = Form_Builder_Helper::sanitize_array( $options, Form_Builder_Helper::get_form_options_sanitize_rules() );

        $settings = isset( $values['settings'] ) && is_array( $values['settings'] ) ? $values['settings'] : array();
        $settings = Form_Builder_Helper::recursive_parse_args( $settings, Form_Builder_Helper::get_form_settings_default() );
        $settings = Form_Builder_Helper::sanitize_array( $settings, Form_Builder_Helper::get_form_settings_sanitize_rules() );

        $styles = isset( $values['styles'] ) && is_array( $values['styles'] ) ? $values['styles'] : array();
        $styles = Form_Builder_Helper::recursive_parse_args( $styles, array( 'form_style' => 'default-style', 'form_style_template' => '' ) );
        $styles = Form_Builder_Helper::sanitize_array( $styles, Form_Builder_Helper::get_form_styles_sanitize_rules() );

        $new_values = array(
            'form_key' => Form_Builder_Helper::get_unique_key( 'asenha_formbuilder_forms', 'form_key' ),
            'name' => esc_html( $values['name'] ),
            'description' => esc_html( $values['description'] ),
            'status' => isset( $values['status'] ) ? sanitize_text_field( $values['status'] ) : 'published',
            'created_at' => isset( $values['created_at'] ) ? sanitize_text_field( $values['created_at'] ) : current_time( 'mysql' ),
            'options' => serialize( $options ),
            'settings' => serialize( $settings ),
            'styles' => serialize( $styles ),
        );
        $wpdb->insert( $wpdb->prefix . 'asenha_formbuilder_forms', $new_values );
        $id = $wpdb->insert_id;
        return $id;
    }

    public function update_form() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer( 'formbuilder_ajax', 'nonce' );

        $fields_array = $settings_array = array();

        $fields = htmlspecialchars_decode( nl2br( str_replace( '&quot;', '"', Form_Builder_Helper::get_post( 'formbuilder_fields', 'esc_html' ) )) );

        if ( $fields ) {
            $fields_array = Form_Builder_Helper::parse_json_array( $fields );
        }

        $settings = htmlspecialchars_decode( nl2br( str_replace( '&quot;', '"', Form_Builder_Helper::get_post( 'formbuilder_settings', 'esc_html' ) )) );
        if ( $settings ) {
            $settings_array = Form_Builder_Helper::parse_json_array( $settings );
        }

        self::update( $fields_array, $settings_array );
    }

    public static function update( $fields_values, $settings_values ) {
        $id = isset( $fields_values['id'] ) ? absint( $fields_values['id'] ) : ''; // Form ID

        self::update_form_options( $id, $settings_values );
        Form_Builder_Fields::update_form_fields( $id, $fields_values );

        $message = esc_html__( 'Form was successfully updated.', 'admin-site-enhancements' );

        if (defined( 'DOING_AJAX' ) ) {
            wp_die( wp_kses( $message, array( 'a' => array(), 'span' => array() ) ) );
        }
    }

    public static function update_form_options( $id, $args ) {
        global $wpdb;
        $options = Form_Builder_Helper::recursive_parse_args( $args, Form_Builder_Helper::get_form_options_checkbox_settings() );
        $options = Form_Builder_Helper::sanitize_array( $options, Form_Builder_Helper::get_form_options_sanitize_rules() );

        $query_results = $wpdb->update( $wpdb->prefix . 'asenha_formbuilder_forms', array(
            'name' => esc_html( $args['title'] ),
            'description' => esc_html( $args['description'] ),
            'options' => maybe_serialize( $options )
        ), array( 'id' => $id ) );
        return $query_results;
    }

    public static function edit() {
        require( FORMBUILDER_PATH . 'forms/build/edit.php' );
    }

    public static function settings() {
        require FORMBUILDER_PATH . 'forms/settings/settings.php';
    }

    public static function style() {
        require FORMBUILDER_PATH . 'forms/style/style.php';
    }

    public function listing_page_screen_options() {

        global $formbuilder_listing_page;

        $screen = get_current_screen();

        // get out of here if we are not on our settings page
        if ( ! is_object( $screen ) || $screen->id != $formbuilder_listing_page ) {
            return;
        }

        $args = array(
            'label' => esc_html__( 'Forms per page', 'admin-site-enhancements' ),
            'default' => 10,
            'option' => 'forms_per_page'
        );
        add_screen_option( 'per_page', $args );

        new Form_Builder_Listing();
    }

    public function set_screen_option( $status, $option, $value ) {
        if ( 'forms_per_page' == $option )
            return $value;
    }

    public static function trash() {
        self::change_form_status( 'trash' );
    }

    public static function untrash() {
        self::change_form_status( 'untrash' );
    }

    public static function change_form_status( $status ) {
        $available_status = array(
            'untrash' => array( 'new_status' => 'published' ),
            'trash' => array( 'new_status' => 'trash' ),
        );

        if ( ! isset( $available_status[$status] ) ) {
            return;
        }

        $id = Form_Builder_Helper::get_var( 'id', 'absint' );
        check_admin_referer( $status . '_form_' . $id );

        $count = 0;
        if (self::set_status( $id, $available_status[$status]['new_status'] ) ) {
            $count++;
        }

        $available_status['untrash']['message'] = sprintf( 
                /* translators: %1$s: number of forms, %2$s: number of forms */
                _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'admin-site-enhancements' ), 
                $count 
            );
        $available_status['trash']['message'] = sprintf( 
            /* translators: %1$s: number of forms, %2$s: opening <a> tag, %3$s: closing </a> tag */
            _n( '%1$s form moved to the Trash. %2$sUndo%3$s', '%1$s forms moved to the Trash. %2$sUndo%3$s', $count, 'admin-site-enhancements' ), 
            $count, 
            '<a href="' . esc_url( wp_nonce_url( '?page=formbuilder&formbuilder_action=untrash&id=' . absint( $id ), 'untrash_form_' . absint( $id ) ) ) . '">', 
            '</a>' );
        $message = $available_status[$status]['message'];

        self::display_forms_list( $message );
    }

    public static function set_status( $id, $status ) {
        $statuses = array( 'published', 'trash' );
        if ( ! in_array( $status, $statuses ) ) {
            return false;
        }

        global $wpdb;

        if ( is_array( $id ) ) {
            $query = $wpdb->prepare("UPDATE {$wpdb->prefix}asenha_formbuilder_forms SET status=%s WHERE id IN (" . implode( ',', array_fill( 0, count( $id ), '%d' ) ) . ")", $status, ...$id );
            $query_results = $wpdb->query( $query );
        } else {
            $query_results = $wpdb->update( $wpdb->prefix . 'asenha_formbuilder_forms', array( 'status' => $status ), array( 'id' => $id ) );
        }

        return $query_results;
    }

    public static function delete_all() {
        $count = self::delete();
        $message = sprintf( 
            /* translators: %1$s: number of forms */
            _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'admin-site-enhancements' ), 
            $count 
        );
        self::display_forms_list( $message );
    }

    public static function delete() {
        global $wpdb;
        $count = 0;
        $query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}asenha_formbuilder_forms WHERE status=%s", 'trash' );
        $trash_forms = $wpdb->get_results( $query );
        if ( ! $trash_forms ) {
            return 0;
        }

        foreach ( $trash_forms as $form ) {
            self::destroy_form( $form->id );
            $count++;
        }
        return $count;
    }

    public static function destroy() {
        $id = Form_Builder_Helper::get_var( 'id', 'absint' );
        check_admin_referer( 'destroy_form_' . $id );
        $count = 0;
        if (self::destroy_form( $id ) ) {
            $count++;
        }
        $message = sprintf( 
            /* translators: %1$s: number of forms */
            _n( '%1$s form permanently deleted', '%1$s forms permanently deleted', $count, 'admin-site-enhancements' ), 
            $count 
        );
        self::display_forms_list( $message );
    }

    public static function bulk_actions() {
        $message = self::process_bulk_actions();
        self::display_forms_list( $message );
    }

    public static function process_bulk_actions() {
        if ( ! $_REQUEST) {
            return;
        }

        $bulkaction = Form_Builder_Helper::get_var( 'action', 'sanitize_text_field' );


        if ( $bulkaction == -1 ) {
            $bulkaction = Form_Builder_Helper::get_var( 'action2', 'sanitize_title' );
        }

        if ( ! empty( $bulkaction ) && strpos( $bulkaction, 'bulk_' ) === 0 ) {
            $bulkaction = str_replace( 'bulk_', '', $bulkaction );
        }

        $ids = Form_Builder_Helper::get_var( 'form_id', 'sanitize_text_field' );

        if ( empty( $ids ) ) {
            $error = esc_html__( 'No forms were specified', 'admin-site-enhancements' );
            return $error;
        }

        if ( ! is_array( $ids ) ) {
            $ids = explode( ',', $ids );
        }

        switch ( $bulkaction ) {
            case 'delete':
                $message = self::bulk_destroy( $ids );
                break;
            case 'trash':
                $message = self::bulk_trash( $ids );
                break;
            case 'untrash':
                $message = self::bulk_untrash( $ids );
        }

        if ( isset( $message ) && ! empty( $message ) ) {
            return $message;
        }
    }

    public static function bulk_trash( $ids ) {
        $count = self::set_status( $ids, 'trash' );
        if ( ! $count ) {
            return '';
        }

        return sprintf( _n( '%1$s form moved to the Trash. %2$sUndo%3$s', '%1$s forms moved to the Trash. %2$sUndo%3$s', $count, 'admin-site-enhancements' ), $count, '<a href="' . esc_url( wp_nonce_url( '?page=formbuilder&action=bulk_untrash&status=published&form_id=' . implode( ',', $ids ), 'bulk-toplevel_page_formbuilder' ) ) . '">', '</a>' );
    }

    public static function bulk_untrash( $ids ) {
        $count = self::set_status( $ids, 'published' );
        if ( ! $count ) {
            return '';
        }

        return sprintf( _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'admin-site-enhancements' ), $count );
    }

    public static function bulk_destroy( $ids ) {
        $count = 0;
        foreach ( $ids as $id ) {
            $form = self::destroy_form( $id );
            if ( $form ) {
                $count++;
            }
        }

        $message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'admin-site-enhancements' ), $count );
        return $message;
    }

    public static function destroy_form( $id ) {
        global $wpdb;
        $form = self::get_form_vars( $id );
        if ( ! $form ) {
            return false;
        }

        $id = $form->id;
        $query = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}asenha_formbuilder_entries WHERE form_id=%d", $id );
        $entries = $wpdb->get_col( $query );

        foreach ( $entries as $entry_id ) {
            Form_Builder_Entry::destroy_entry( $entry_id );
        }

        $query = $wpdb->prepare( 'DELETE hfi FROM ' . $wpdb->prefix . 'asenha_formbuilder_fields AS hfi LEFT JOIN ' . $wpdb->prefix . 'asenha_formbuilder_forms hfm ON (hfi.form_id = hfm.id ) WHERE hfi.form_id=%d', $id );
        $wpdb->query( $query );

        $query = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'asenha_formbuilder_forms WHERE id=%d', $id );
        $results = $wpdb->query( $query );
        return $results;
    }

    public static function duplicate() {
        global $wpdb;
        $message = '';
        $nonce = Form_Builder_Helper::get_var( '_wpnonce' );

        if ( ! wp_verify_nonce( $nonce ) ) {
            wp_die(esc_html__( 'Error ! Refresh the page and try again.', 'admin-site-enhancements' ) );
        }

        $id = Form_Builder_Helper::get_var( 'id', 'absint' );
        $values = self::get_form_vars( $id );

        if ( ! $values ) {
            return false;
        }

        $options = Form_Builder_Helper::recursive_parse_args( $values->options, Form_Builder_Helper::get_form_options_default() );
        $options = Form_Builder_Helper::sanitize_array( $options, Form_Builder_Helper::get_form_options_sanitize_rules() );

        $settings = Form_Builder_Helper::recursive_parse_args( $values->settings, Form_Builder_Helper::get_form_settings_default() );
        $settings = Form_Builder_Helper::sanitize_array( $settings, Form_Builder_Helper::get_form_settings_sanitize_rules() );

        $styles = Form_Builder_Helper::recursive_parse_args( $values->styles, array( 'form_style' => 'default-style', 'form_style_template' => '' ) );
        $styles = Form_Builder_Helper::sanitize_array( $styles, Form_Builder_Helper::get_form_styles_sanitize_rules() );

        $new_values = array(
            'form_key' => Form_Builder_Helper::get_unique_key( 'asenha_formbuilder_forms', 'form_key' ),
            'name' => esc_html( $values->name ) . ' - ' . esc_html__( 'Copy', 'admin-site-enhancements' ),
            'description' => esc_html( $values->description ),
            'status' => $values->status ? sanitize_text_field( $values->status ) : 'published',
            'created_at' => sanitize_text_field(current_time( 'mysql' ) ),
            'options' => serialize( $options ),
            'settings' => serialize( $settings ),
            'styles' => serialize( $styles ),
        );

        $query_results = $wpdb->insert( $wpdb->prefix . 'asenha_formbuilder_forms', $new_values );

        if ( $query_results ) {
            $form_id = $wpdb->insert_id;
            Form_Builder_Fields::duplicate_fields( $id, $form_id );
        }

        if ( $form_id ) {
            $message = esc_html__( 'Form was successfully copied', 'admin-site-enhancements' );
            $class = 'updated';
        } else {
            $message = esc_html__( 'Error! Form can not be copied', 'admin-site-enhancements' );
            $class = 'error';
        }

        self::display_forms_list( $message, $class );
    }

    public static function get_admin_header( $atts = array() ) {
        $class = isset( $atts['class'] ) ? $atts['class'] : '';
        $form = $atts['form'];
        $form_title = $form->name;
        ?>
        <div id="fb-header" class="<?php echo esc_attr( $class ); ?>">
            <h4 id="fb-form-title"><span class="fb-form-title-span"><?php echo esc_html( $form_title ); ?></span><span class="fb-edit-form-title"><?php echo wp_kses( Form_Builder_Icons::get( 'form_options' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h4>
            <?php self::get_form_nav( $form ); ?>

            <button class="formbuilder-ajax-udpate-button" type="button" id="fb-update-button">
                <?php esc_html_e( 'Update', 'admin-site-enhancements' ); ?>
            </button>

            <div class="fb-preview-button">
                <a href="<?php echo esc_url(admin_url( 'admin-ajax.php?action=formbuilder_preview&form=' . absint( $form->id ) )); ?>" target="_blank" title="<?php echo esc_attr( 'Preview', 'admin-site-enhancements' ); ?>"><?php echo wp_kses( Form_Builder_Icons::get( 'preview' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></a>
            </div>

            <button class="fb-embed-button" type="button" title="<?php echo esc_attr( 'Embed', 'admin-site-enhancements' ); ?>">
                <?php echo '[/]'; ?>
            </button>

            <div class="fb-entries-button">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=formbuilder-entries&form_id=' . absint( $form->id ) ) ); ?>" target="_blank" title="<?php echo esc_attr( 'Entries', 'admin-site-enhancements' ); ?>"><?php echo wp_kses( Form_Builder_Icons::get( 'entries' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></a>
            </div>
            
            <div class="formbuilder-close">
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=formbuilder' ) ); ?>" aria-label="<?php esc_attr_e( 'Close', 'admin-site-enhancements' ); ?>">
                    x
                </a>
            </div>
        </div>
        <?php
    }

    public static function get_form_nav( $form ) {
        if ( ! $form ) {
            return;
        }
        $id = $form->id;
        $nav_items = self::get_form_nav_items( $id );
        ?>
        <ul class="fb-main-nav">
            <?php foreach ( $nav_items as $nav_item ) { ?>
                <li>
                    <a href="<?php echo esc_url( $nav_item['link'] ); ?>" class="<?php echo self::is_current_page( $nav_item['page'], $nav_item['current'] ) ? 'fb-active-nav' : ''; ?>">
                        <?php echo esc_html( $nav_item['label'] ); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
        <?php
    }

    public static function get_form_nav_items( $id ) {
        $nav_items = array(
            array(
                'link' => admin_url( 'admin.php?page=formbuilder&formbuilder_action=edit&id=' . absint( $id ) ),
                'label' => esc_html__( 'Builder', 'admin-site-enhancements' ),
                'current' => array( 'edit', 'new', 'duplicate' ),
                'page' => 'formbuilder'
            ),
            array(
                'link' => admin_url( 'admin.php?page=formbuilder&formbuilder_action=settings&id=' . absint( $id ) ),
                'label' => esc_html__( 'Settings', 'admin-site-enhancements' ),
                'current' => array( 'settings' ),
                'page' => 'formbuilder'
            ),
            array(
                'link' => admin_url( 'admin.php?page=formbuilder&formbuilder_action=style&id=' . absint( $id ) ),
                'label' => esc_html__( 'Style', 'admin-site-enhancements' ),
                'current' => array( 'style' ),
                'page' => 'formbuilder'
            ),
            // array(
            //     'link' => admin_url( 'admin.php?page=formbuilder-entries&form_id=' . absint( $id ) ),
            //     'label' => esc_html__( 'Entries', 'admin-site-enhancements' ),
            //     'current' => array(),
            //     'page' => 'formbuilder-entries'
            // ),
        );
        return $nav_items;
    }

    public static function is_current_page( $page, $action = array() ) {
        $current_page = Form_Builder_Helper::get_var( 'page' );
        $formbuilder_action = Form_Builder_Helper::get_var( 'formbuilder_action' );

        if ( ( $page == $current_page ) && ( ! empty( $formbuilder_action ) && in_array( $formbuilder_action, $action ) )) {
            return true;
        }
        return false;
    }

    public static function get_all_forms() {
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}asenha_formbuilder_forms WHERE id!=%d AND status='published'", 0 );
        $results = $wpdb->get_results( $query );
        return $results;
    }

    public static function get_form_vars( $id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asenha_formbuilder_forms';

        $query = $wpdb->prepare("SELECT * FROM {$table_name} WHERE id=%d", $id );
        $results = $wpdb->get_row( $query );

        if ( ! $results )
            return;

        foreach ( $results as $key => $value ) {
            $results->$key = maybe_unserialize( $value );
        }

        return $results;
    }

    public function init_overlay_html() {
        $plugin_path = FORMBUILDER_PATH;
        $new_form_overlay = apply_filters( 'formbuilder_new_form_overlay_template', $plugin_path . 'forms/new-form-overlay.php' );
        if ( Form_Builder_Helper::is_form_listing_page() ) {
            include $new_form_overlay;
        }
        if ( Form_Builder_Helper::is_form_builder_page() ) {
            include $plugin_path . 'forms/shortcode-overlay.php';
        }
    }

    public function save_form_settings() {
        if ( ! current_user_can( 'manage_options' ) )
            return;

        $json_vars = htmlspecialchars_decode( nl2br( str_replace( '&quot;', '"', Form_Builder_Helper::get_post( 'formbuilder_compact_fields' ) )) );
        $vars = Form_Builder_Helper::parse_json_array( $json_vars );

        $email_to_array = array();
        foreach ( $vars['email_to'] as $row ) {
            $email_to_val = trim( $row );
            if ( $email_to_val ) {
                $email_to_array[] = $email_to_val;
            }
        }
        $vars['email_to'] = implode( ',', $email_to_array );

        $webhook_urls_array = array();
        if ( ! empty( $vars['webhook_urls'] ) ) {
            foreach ( $vars['webhook_urls'] as $row ) {
                $webhook_url_val = trim( $row );
                if ( $webhook_url_val ) {
                    $webhook_urls_array[] = $webhook_url_val;
                }
            }            
        }
        $vars['webhook_urls'] = implode( ',', $webhook_urls_array );

        $id = isset( $vars['id'] ) ? absint( $vars['id'] ) : Form_Builder_Helper::get_var( 'id', 'absint' );
        unset( $vars['id'], $vars['process_form'], $vars['_wp_http_referer'] );

        self::update_settings( $id, $vars );
        $message = esc_html__( 'Form was successfully updated.', 'admin-site-enhancements' );
        wp_die( wp_kses_post( $message ) );
    }

    public function save_form_style() {
        if ( ! current_user_can( 'manage_options' ) )
            return;

        $json_vars = htmlspecialchars_decode( nl2br( str_replace( '&quot;', '"', Form_Builder_Helper::get_post( 'formbuilder_compact_fields' ) )) );
        $vars = Form_Builder_Helper::parse_json_array( $json_vars );
        $id = isset( $vars['id'] ) ? absint( $vars['id'] ) : Form_Builder_Helper::get_var( 'id', 'absint' );

        self::update_style( $id, $vars );
        $message = esc_html__( 'Form was successfully updated.', 'admin-site-enhancements' );
        wp_die( wp_kses_post( $message ) );
    }

    public static function update_settings( $id, $values ) {
        global $wpdb;
        $values = Form_Builder_Helper::recursive_parse_args( $values, Form_Builder_Helper::get_form_settings_checkbox_settings() );
        $values = Form_Builder_Helper::sanitize_array( $values, Form_Builder_Helper::get_form_settings_sanitize_rules() );

        $new_values = array(
            'settings' => serialize( $values )
        );
        if ( ! empty( $new_values ) ) {
            $query_results = $wpdb->update( $wpdb->prefix . 'asenha_formbuilder_forms', $new_values, array( 'id' => $id ) );
        }
        return $query_results;
    }

    public static function update_style( $id, $value ) {
        global $wpdb;
        $new_values = array(
            'styles' => serialize( Form_Builder_Helper::sanitize_array( $value ) )
        );
        if ( ! empty( $new_values ) ) {
            $query_results = $wpdb->update( $wpdb->prefix . 'asenha_formbuilder_forms', $new_values, array( 'id' => $id ) );
        }
        return $query_results;
    }

    public function add_more_condition_block() {
        $form_id = Form_Builder_Helper::get_post( 'form_id', 'absint', 0 );
        $fields = Form_Builder_Fields::get_form_fields( $form_id );
        ?>
        <div class="fb-condition-repeater-block">
            <select name="condition_action[]" required>
                <option value="show"><?php esc_html_e( 'Show', 'admin-site-enhancements' ); ?></option>
                <option value="hide"><?php esc_html_e( 'Hide', 'admin-site-enhancements' ); ?></option>
            </select>

            <select name="compare_from[]" required>
                <option value=""><?php esc_html_e( 'Select field', 'admin-site-enhancements' ); ?></option>
                <?php
                foreach ( $fields as $field ) {
                    if ( ! ( $field->type == 'heading' || $field->type == 'paragraph' || $field->type == 'separator' || $field->type == 'spacer' || $field->type == 'image' || $field->type == 'altcha' || $field->type == 'captcha' || $field->type == 'turnstile' ) ) {
                        ?>
                        <option value="<?php echo esc_attr( $field->id ); ?>"><?php echo esc_html( $field->name ) . ' (ID: ' . esc_attr( $field->id ) . ' )'; ?></option>
                        <?php
                    }
                }
                ?>
            </select>

            <span class="fb-condition-seperator"><?php esc_html_e( 'if', 'admin-site-enhancements' ); ?></span>
            <select name="compare_to[]" required>
                <option value=""><?php esc_html_e( 'Select field', 'admin-site-enhancements' ); ?></option>
                <?php
                foreach ( $fields as $field ) {
                    if ( ! ( $field->type == 'heading' || $field->type == 'paragraph' || $field->type == 'separator' || $field->type == 'spacer' || $field->type == 'image' || $field->type == 'altcha' || $field->type == 'captcha' || $field->type == 'turnstile' || $field->type == 'name' || $field->type == 'address' ) ) {
                        ?>
                        <option value="<?php echo esc_attr( $field->id ); ?>"><?php echo esc_html( $field->name ) . ' (ID: ' . esc_html( $field->id ) . ' )'; ?></option>
                        <?php
                    }
                }
                ?>
            </select>

            <select name="compare_condition[]" required>
                <option value="equal"><?php esc_html_e( 'is', 'admin-site-enhancements' ); ?></option>
                <option value="not_equal"><?php esc_html_e( 'is not', 'admin-site-enhancements' ); ?></option>
                <option value="greater_than"><?php esc_html_e( 'is greater than', 'admin-site-enhancements' ); ?></option>
                <option value="greater_than_or_equal"><?php esc_html_e( 'is greater than or equal to', 'admin-site-enhancements' ); ?></option>
                <option value="less_than"><?php esc_html_e( 'is less than', 'admin-site-enhancements' ); ?></option>
                <option value="less_than_or_equal"><?php esc_html_e( 'is less than or equal to', 'admin-site-enhancements' ); ?></option>
                <option value="is_like"><?php esc_html_e( 'contains', 'admin-site-enhancements' ); ?></option>
                <option value="is_not_like"><?php esc_html_e( 'does not contain', 'admin-site-enhancements' ); ?></option>
            </select>

            <input type="text" name="compare_value[]" required />
            <span class="fb-condition-remove"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
        </div>
        <?php
        die();
    }

    public static function get_show_hide_conditions( $id ) {
        $form = Form_Builder_Builder::get_form_vars( $id );
        $settings = $form->settings ? $form->settings : array();
        $conditions = array();
        if ( isset( $settings['condition_action'] ) && $settings['condition_action'] ) {
            foreach ( $settings['condition_action'] as $key => $row ) {
                $condition = array(
                    'condition_action' => $settings['condition_action'][$key],
                    'compare_from' => $settings['compare_from'][$key],
                    'compare_to' => $settings['compare_to'][$key],
                    'compare_condition' => $settings['compare_condition'][$key],
                    'compare_value' => $settings['compare_value'][$key],
                );
                $conditions[] = $condition;
            }
        }
        return $conditions;
    }

    public function add_plugin_action_link( $links ) {
        $custom['settings'] = sprintf(
            '<a href="%s" aria-label="%s">%s</a>', esc_url(add_query_arg( 'page', 'formbuilder', admin_url( 'admin.php' ) )), esc_attr__( 'Form Builder', 'admin-site-enhancements' ), esc_html__( 'Settings', 'admin-site-enhancements' )
        );

        return array_merge( $custom, (array) $links );
    }

    public function file_upload_action() {
        if ( ! wp_verify_nonce( Form_Builder_Helper::get_var( 'file_uploader_nonce' ), 'formbuilder-upload-ajax-nonce' ) ) {
            die();
        }

        $allowedExtensions = Form_Builder_Helper::get_var( 'allowedExtensions' );
        $sizeLimit = Form_Builder_Helper::get_var( 'sizeLimit' );
        $upload_dir = wp_upload_dir();

        $default_allowed_extenstions = array(
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'odt',
            'ppt',
            'pptx',
            'pps',
            'ppsx',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'bmp',
            'mp3',
            'mp4',
            'ogg',
            'wav',
            'mp4',
            'm4v',
            'mov',
            'wmv',
            'avi',
            'mpg',
            'ogv',
            '3gp',
            'txt',
            'zip',
            'rar',
            '7z',
            'csv'
        );

        if ( $allowedExtensions ) {
            foreach ( $allowedExtensions as $ext ) {
                if ( in_array( $ext, $default_allowed_extenstions ) ) {
                    $filtered_allowed_extenstions[] = $ext;
                }
            }

            $uploader = new Form_Builder_File_Uploader( $filtered_allowed_extenstions, $sizeLimit );
            $result = $uploader->handleUpload( $upload_dir['basedir'] . FORMBUILDER_UPLOAD_DIR, $replaceOldFile = false, $upload_dir['baseurl'] . FORMBUILDER_UPLOAD_DIR);

            echo json_encode( $result );
        }
        die();
    }

    public function file_delete_action() {
        if ( wp_verify_nonce( Form_Builder_Helper::get_post( '_wpnonce' ), 'formbuilder-upload-ajax-nonce' ) ) {
            $path = str_replace( ' ', '+', Form_Builder_Helper::get_post( 'path', 'wp_kses_post' ) );
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . FORMBUILDER_UPLOAD_DIR . '/temp/';
            $check = @unlink( $temp_dir . Form_Builder_Helper::decrypt( $path ) );

            if ( $check ) {
                die( 'success' );
            }
        }
        die( 'error' );
    }

    public static function remove_old_temp_files() {
        $max_file_age = apply_filters( 'formbuilder_temp_file_delete_time', 2 * 3600 );
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . FORMBUILDER_UPLOAD_DIR . '/temp/';

        // Remove old temp files
        if ( is_dir( $temp_dir ) and ( $dir = opendir( $temp_dir ) )) {
            while ( ( $file = readdir( $dir ) ) !== false ) {
                $temp_file_path = $temp_dir . DIRECTORY_SEPARATOR . $file;
                if ( ( filemtime( $temp_file_path ) < time() - $max_file_age ) ) {
                    @unlink( $temp_file_path );
                }
            }
            closedir( $dir );
        }
    }

    public function admin_notice() {
        add_action( 'admin_notices', array( $this, 'admin_notice_content' ) );
    }
    
    public function admin_notice_content() {
        if ( ! $this->is_dismissed( 'review' ) && ! empty(get_option( 'formbuilder_first_activation' ) ) && time() > get_option( 'formbuilder_first_activation' ) + 15 * DAY_IN_SECONDS) {
            $this->review_notice();
        }
    }

    public static function is_dismissed( $notice ) {
        $dismissed = get_option( 'formbuilder_dismissed_notices', array() );

        // Handle legacy user meta
        $dismissed_meta = get_user_meta(get_current_user_id(), 'formbuilder_dismissed_notices', true );
        if ( is_array( $dismissed_meta ) ) {
            if ( array_diff( $dismissed_meta, $dismissed ) ) {
                $dismissed = array_merge( $dismissed, $dismissed_meta );
                update_option( 'formbuilder_dismissed_notices', $dismissed );
            }
            if ( ! is_multisite() ) {
                // Don't delete on multisite to avoid the notices to appear in other sites.
                delete_user_meta(get_current_user_id(), 'formbuilder_dismissed_notices' );
            }
        }

        return in_array( $notice, $dismissed );
    }

    public function review_notice() {
        ?>
        <div class="formbuilder-notice notice notice-info">
            <?php $this->dismiss_button( 'review' ); ?>
            <div class="formbuilder-notice-logo">
                <?php echo wp_kses( Form_Builder_Icons::get( 'notice' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?>
            </div>

            <div class="formbuilder-notice-content">
                <p>
                    <?php
                    printf(
                        /* translators: %1$s is link start tag, %2$s is link end tag. */
                        esc_html__( 'Great to see that you have been using Form Builder for some time. We hope you love it, and we would really appreciate it if you would %1$sgive us a 5 stars rating%2$s and spread your words to the world.', 'admin-site-enhancements' ), '<a target="_blank" href="https://wordpress.org/support/plugin/form-builder/reviews/?filter=5">', '</a>'
                    );
                    ?>
                </p>
                <a target="_blank" class="button button-primary button-large" href="https://wordpress.org/support/plugin/form-builder/reviews/?filter=5"><span class="dashicons dashicons-thumbs-up"></span><?php echo esc_html__( 'Yes, of course', 'admin-site-enhancements' ) ?></a> &nbsp;
                <a class="button button-large" href="<?php echo esc_url( wp_nonce_url(add_query_arg( 'formbuilder-hide-notice', 'review' ), 'review', 'formbuilder_notice_nonce' ) ); ?>"><span class="dashicons dashicons-yes"></span><?php echo esc_html__( 'I have already rated', 'admin-site-enhancements' ) ?></a>
            </div>
        </div>
        <?php
    }

    public function welcome_init() {
        if ( ! get_option( 'formbuilder_first_activation' ) ) {
            update_option( 'formbuilder_first_activation', time() );
        }

        if ( isset( $_GET['formbuilder-hide-notice'], $_GET['formbuilder_notice_nonce'] ) ) {
            $notice = sanitize_key( $_GET['formbuilder-hide-notice'] );
            check_admin_referer( $notice, 'formbuilder_notice_nonce' );
            self::dismiss( $notice );
            wp_safe_redirect(remove_query_arg( array( 'formbuilder-hide-notice', 'formbuilder_notice_nonce' ), wp_get_referer() ));
            exit;
        }
    }

    public function dismiss_button( $name ) {
        printf( '<a class="notice-dismiss" href="%s"><span class="screen-reader-text">%s</span></a>', esc_url( wp_nonce_url(add_query_arg( 'formbuilder-hide-notice', $name ), $name, 'formbuilder_notice_nonce' ) ), esc_html__( 'Dismiss this notice.', 'admin-site-enhancements' ) );
    }

    public static function dismiss( $notice ) {
        $dismissed = get_option( 'formbuilder_dismissed_notices', array() );

        if ( ! in_array( $notice, $dismissed ) ) {
            $dismissed[] = $notice;
            update_option( 'formbuilder_dismissed_notices', array_unique( $dismissed ) );
        }
    }

}

new Form_Builder_Builder();
