<?php

defined( 'ABSPATH' ) || die();

class Form_Builder_Fields {

    public function __construct() {
        self::include_field_class();
        add_action( 'wp_ajax_formbuilder_insert_field', array( $this, 'create' ) );
        add_action( 'wp_ajax_formbuilder_delete_field', array( $this, 'destroy' ) );
        add_action( 'wp_ajax_formbuilder_import_options', array( $this, 'import_options' ) );
        //add_action( 'wp_ajax_formbuilder_duplicate_field', array( $this, 'duplicate' ) );
    }

    public static function get_form_fields( $form_id ) {
        global $wpdb;
        $form_id = absint( $form_id );
        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}asenha_formbuilder_fields WHERE form_id=%d ORDER BY field_order", $form_id );
        $results = $wpdb->get_results( $query );
        foreach ( $results as $value ) {
            foreach ( $value as $key => $val ) {
                $value->$key = maybe_unserialize( $val );
            }
        }
        return $results;
    }

    public static function create() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer( 'formbuilder_ajax', 'nonce' );
        $field_type = Form_Builder_Helper::get_post( 'field_type', 'sanitize_text_field' );
        $form_id = Form_Builder_Helper::get_post( 'form_id', 'absint', 0 );
        self::include_new_field( $field_type, $form_id );
        wp_die();
    }

    public static function destroy() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer( 'formbuilder_ajax', 'nonce' );
        $field_id = Form_Builder_Helper::get_post( 'field_id', 'absint', 0 );
        self::destroy_row( $field_id );
        wp_die();
    }

    public static function include_new_field( $field_type, $form_id ) {
        $field_values = self::setup_new_field_vars( $field_type, $form_id );
        $field_id = Form_Builder_Fields::create_row( $field_values );
        if ( ! $field_id ) {
            return false;
        }
        $field = self::get_field_vars( $field_id );
        $field_array = self::covert_field_obj_to_array( $field );
        $field_obj = Form_Builder_Fields::get_field_class( $field_array['type'], $field_array );
        $field_obj->load_single_field();
    }

    public static function setup_new_field_vars( $type = '', $form_id = '' ) {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT field_order FROM {$wpdb->prefix}asenha_formbuilder_fields WHERE form_id=%d ORDER BY field_order DESC", $form_id );
        $field_count = $wpdb->get_var( $sql );
        $values = self::get_default_field( $type );
        $values['field_key'] = Form_Builder_Helper::get_unique_key( 'asenha_formbuilder_fields', 'field_key' );
        $values['form_id'] = $form_id;
        $values['field_order'] = $field_count + 1;
        return $values;
    }

    public static function covert_field_obj_to_array( $field ) {
        $field_array = json_decode( wp_json_encode( $field ), true );
        $field_options = $field_array['field_options'];
        unset( $field_array['field_options'] );
        return array_merge( $field_array, $field_options );
    }

    public static function get_default_field( $type ) {
        $field_obj = Form_Builder_Fields::get_field_class( $type );
        return $field_obj->get_new_field_defaults();
    }

    public static function import_options() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $field_id = Form_Builder_Helper::get_post( 'field_id', 'absint' );
        $field_type = Form_Builder_Helper::get_post( 'field_type', 'sanitize_text_field' );
        $options_id = Form_Builder_Helper::get_post( 'options_id', 'sanitize_text_field' );
        $field = self::get_field_vars( $field_id );
        if ( ! in_array( $field->type, array( 
            'radio', 
            'checkbox', 
            'select', 
            'likert_matrix_scale', 
            'matrix_of_dropdowns',
            'matrix_of_variable_dropdowns_two',
            'matrix_of_variable_dropdowns_three',
            'matrix_of_variable_dropdowns_four',
            'matrix_of_variable_dropdowns_five',
        ) ) ) {
            return;
        }

        $field_array = self::covert_field_obj_to_array( $field );
        $field_array['type'] = $field->type;
        $field_array['value'] = $field->default_value;

        $opts = htmlspecialchars_decode( Form_Builder_Helper::get_post( 'opts', 'esc_html' ) );
        $opts = explode("\n", rtrim( $opts, "\n") );
        $opts = array_map( 'trim', $opts );

        foreach ( $opts as $opt_key => $opt ) {
            $opts[$opt_key] = array(
                'label' => $opt
            );
        }

        if ( 'default' == $options_id ) {
            // e.g. radio, checkbox, select
            $field_array['options'] = $opts;            
        } else {
            // e.g. likert_matrix_scale field
            $field_array['options'][$options_id] = $opts;            
        }
        // vi( $field_array );

        $field_obj = Form_Builder_Fields::get_field_class( $field_array['type'], $field_array );
        $field_obj->show_single_option( $options_id );
        wp_die();
    }

    public static function field_selection() {
        $formbuilder_fields = array(
            'identity-fields' => array(
                'label'     => __( 'Identity', 'admin-site-enhancements' ),
                'fields'    => array(
                    'name' => array(
                        'name' => esc_html__( 'Name', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 32 32"><path fill="currentColor" d="M18 13a1 1 0 0 1 1-1h6a1 1 0 0 1 0 2h-6a1 1 0 0 1-1-1m1 4a1 1 0 1 0 0 2h6a1 1 0 0 0 0-2zm-6-4a2 2 0 1 1-4 0a2 2 0 0 1 4 0m-6 4.5A1.5 1.5 0 0 1 8.5 16h5a1.5 1.5 0 0 1 1.5 1.5s0 3.5-4 3.5s-4-3.5-4-3.5M2 7.25A3.25 3.25 0 0 1 5.25 4h21.5A3.25 3.25 0 0 1 30 7.25v17.5A3.25 3.25 0 0 1 26.75 28H5.25A3.25 3.25 0 0 1 2 24.75zM5.25 6C4.56 6 4 6.56 4 7.25v17.5c0 .69.56 1.25 1.25 1.25h21.5c.69 0 1.25-.56 1.25-1.25V7.25C28 6.56 27.44 6 26.75 6z"/></svg>',
                    ),
                    'email' => array(
                        'name' => esc_html__( 'Email', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 32 32"><path fill="currentColor" d="M28 6H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h24a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2m-2.2 2L16 14.78L6.2 8ZM4 24V8.91l11.43 7.91a1 1 0 0 0 1.14 0L28 8.91V24Z"/></svg>',
                    ),
                    'url' => array(
                        'name' => esc_html__( 'Website / URL', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 14 14"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13.5a6.5 6.5 0 1 0 0-13a6.5 6.5 0 0 0 0 13M.5 7h13"/><path d="M9.5 7A11.22 11.22 0 0 1 7 13.5A11.22 11.22 0 0 1 4.5 7A11.22 11.22 0 0 1 7 .5A11.22 11.22 0 0 1 9.5 7"/></g></svg>',
                    ),
                    'phone' => array(
                        'name' => esc_html__( 'Phone', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 0 1-2.18 2a19.8 19.8 0 0 1-8.63-3.07a19.5 19.5 0 0 1-6-6a19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72a12.8 12.8 0 0 0 .7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45a12.8 12.8 0 0 0 2.81.7A2 2 0 0 1 22 16.92"/></svg>',
                    ),
                    'address' => array(
                        'name' => esc_html__( 'Address', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><path fill="currentColor" d="m12.596 11.596l-3.535 3.536a1.5 1.5 0 0 1-2.122 0l-3.535-3.536a6.5 6.5 0 1 1 9.192-9.193a6.5 6.5 0 0 1 0 9.193m-1.06-8.132a5 5 0 1 0-7.072 7.072L8 14.07l3.536-3.534a5 5 0 0 0 0-7.072M8 9a2 2 0 1 1-.001-3.999A2 2 0 0 1 8 9"/></svg>',
                    ),
                )
            ),
            'text-fields' => array(
                'label'     => __( 'Text', 'admin-site-enhancements' ),
                'fields'    => array(
                    'text' => array(
                        'name' => esc_html__( 'Text', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 100 100"><path fill="currentColor" d="M8.5 22.5A3.5 3.5 0 0 0 5 26v48a3.5 3.5 0 0 0 3.5 3.5h83A3.5 3.5 0 0 0 95 74V26a3.5 3.5 0 0 0-3.5-3.5zm3.5 7h76v41H12z" color="currentColor"/></svg>',
                    ),
                    'textarea' => array(
                        'name' => esc_html__( 'Text Area', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 56 56"><path fill="currentColor" d="M7.715 49.574h40.57c4.899 0 7.36-2.437 7.36-7.265V13.69c0-4.828-2.461-7.265-7.36-7.265H7.715C2.84 6.426.355 8.84.355 13.69v28.62c0 4.851 2.485 7.265 7.36 7.265m.07-3.773c-2.344 0-3.656-1.242-3.656-3.68V13.88c0-2.438 1.312-3.68 3.656-3.68h40.43c2.32 0 3.656 1.242 3.656 3.68v28.24c0 2.438-1.336 3.68-3.656 3.68Z"/></svg>',
                    ),
                ),
            ),
            'numeric-fields' => array(
                'label'     => __( 'Numeric', 'admin-site-enhancements' ),
                'fields'    => array(
                    'number' => array(
                        'name' => esc_html__( 'Number', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><path fill="currentColor" d="M9 4.75A.75.75 0 0 1 9.75 4h4a.75.75 0 0 1 .53 1.28l-1.89 1.892c.312.076.604.18.867.319c.742.391 1.244 1.063 1.244 2.005c0 .653-.231 1.208-.629 1.627c-.386.408-.894.653-1.408.777c-1.01.243-2.225.063-3.124-.527a.751.751 0 0 1 .822-1.254c.534.35 1.32.474 1.951.322c.306-.073.53-.201.67-.349c.129-.136.218-.32.218-.596c0-.308-.123-.509-.444-.678c-.373-.197-.98-.318-1.806-.318a.75.75 0 0 1-.53-1.28l1.72-1.72H9.75A.75.75 0 0 1 9 4.75m-3.587 5.763c-.35-.05-.77.113-.983.572a.75.75 0 1 1-1.36-.632c.508-1.094 1.589-1.565 2.558-1.425c1 .145 1.872.945 1.872 2.222c0 1.433-1.088 2.192-1.79 2.681c-.308.216-.571.397-.772.573H7a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75c0-.69.3-1.211.67-1.61c.348-.372.8-.676 1.15-.92c.8-.56 1.18-.904 1.18-1.474c0-.473-.267-.69-.587-.737M5.604.089A.75.75 0 0 1 6 .75v4.77h.711a.75.75 0 0 1 0 1.5H3.759a.75.75 0 0 1 0-1.5H4.5V2.15l-.334.223a.75.75 0 0 1-.832-1.248l1.5-1a.75.75 0 0 1 .77-.037Z"/></svg>',
                    ),
                    'range_slider' => array(
                        'name' => esc_html__( 'Range Slider', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 15 15"><path fill="currentColor" fill-rule="evenodd" d="M10.3 7.5a1.8 1.8 0 1 1-3.6 0a1.8 1.8 0 0 1 3.6 0m.905.5a2.751 2.751 0 0 1-5.41 0H.5a.5.5 0 0 1 0-1h5.295a2.751 2.751 0 0 1 5.41 0H14.5a.5.5 0 0 1 0 1z" clip-rule="evenodd"/></svg>',
                    ),
                    'spinner' => array(
                        'name' => esc_html__( 'Spinner', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M11.943 1.25h.114c2.309 0 4.118 0 5.53.19c1.444.194 2.584.6 3.479 1.494c.895.895 1.3 2.035 1.494 3.48c.19 1.411.19 3.22.19 5.529v.114c0 2.309 0 4.118-.19 5.53c-.194 1.444-.6 2.584-1.494 3.479c-.895.895-2.035 1.3-3.48 1.494c-1.411.19-3.22.19-5.529.19h-.114c-2.309 0-4.118 0-5.53-.19c-1.444-.194-2.584-.6-3.479-1.494c-.895-.895-1.3-2.035-1.494-3.48c-.19-1.411-.19-3.22-.19-5.529v-.114c0-2.309 0-4.118.19-5.53c.194-1.444.6-2.584 1.494-3.479c.895-.895 2.035-1.3 3.48-1.494c1.411-.19 3.22-.19 5.529-.19m-5.33 1.676c-1.278.172-2.049.5-2.618 1.069c-.57.57-.897 1.34-1.069 2.619c-.174 1.3-.176 3.008-.176 5.386s.002 4.086.176 5.386c.119.882.311 1.522.606 2.021L19.407 3.532c-.499-.295-1.139-.487-2.02-.606c-1.3-.174-3.009-.176-5.387-.176s-4.086.002-5.386.176m13.855 1.667L4.593 20.468c.499.295 1.139.487 2.02.606c1.3.174 3.009.176 5.387.176s4.086-.002 5.386-.176c1.279-.172 2.05-.5 2.62-1.069c.569-.57.896-1.34 1.068-2.619c.174-1.3.176-3.008.176-5.386s-.002-4.086-.176-5.386c-.119-.882-.311-1.522-.606-2.021M8 4.75a.75.75 0 0 1 .75.75v1.75h1.75a.75.75 0 0 1 0 1.5H8.75v1.75a.75.75 0 0 1-1.5 0V8.75H5.5a.75.75 0 0 1 0-1.5h1.75V5.5A.75.75 0 0 1 8 4.75M12.25 17a.75.75 0 0 1 .75-.75h5a.75.75 0 0 1 0 1.5h-5a.75.75 0 0 1-.75-.75" clip-rule="evenodd"/></svg>',
                    ),
                    'star' => array(
                        'name' => esc_html__( 'Star', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.5" d="m12 2l3.104 6.728l7.358.873l-5.44 5.03l1.444 7.268L12 18.28L5.534 21.9l1.444-7.268L1.538 9.6l7.359-.873z"/></svg>',
                    ),
                    'scale' => array(
                        'name' => esc_html__( 'NPS / Numerical Scale', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 256 256"><path fill="currentColor" d="m235.32 73.37l-52.69-52.68a16 16 0 0 0-22.63 0L20.68 160a16 16 0 0 0 0 22.63l52.69 52.68a16 16 0 0 0 22.63 0L235.32 96a16 16 0 0 0 0-22.63M84.68 224L32 171.31l32-32l26.34 26.35a8 8 0 0 0 11.32-11.32L75.31 128L96 107.31l26.34 26.35a8 8 0 0 0 11.32-11.32L107.31 96L128 75.31l26.34 26.35a8 8 0 0 0 11.32-11.32L139.31 64l32-32L224 84.69Z"/></svg>',
                    ),
                ),
            ),
            'choice-fields' => array(
                'label'     => __( 'Choice', 'admin-site-enhancements' ),
                'fields'    => array(
                    'select' => array(
                        'name' => esc_html__( 'Dropdown', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="m7.854 10.854l3.792 3.792a.5.5 0 0 0 .708 0l3.793-3.792a.5.5 0 0 0-.354-.854H8.207a.5.5 0 0 0-.353.854"/><path fill="currentColor" d="M2 3.75C2 2.784 2.784 2 3.75 2h16.5c.966 0 1.75.784 1.75 1.75v16.5A1.75 1.75 0 0 1 20.25 22H3.75A1.75 1.75 0 0 1 2 20.25Zm1.75-.25a.25.25 0 0 0-.25.25v16.5c0 .138.112.25.25.25h16.5a.25.25 0 0 0 .25-.25V3.75a.25.25 0 0 0-.25-.25Z"/></svg>',
                    ),
                    'checkbox' => array(
                        'name' => esc_html__( 'Checkboxes', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 512 512"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M352 176L217.6 336L160 272"/><rect width="384" height="384" x="64" y="64" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" rx="48" ry="48"/></svg>',
                    ),
                    'radio' => array(
                        'name' => esc_html__( 'Radio Buttons', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><g fill="none" stroke="currentColor"><path d="M14.5 8a6.5 6.5 0 1 1-13 0a6.5 6.5 0 0 1 13 0Z"/><path d="M6 8a2 2 0 1 1 4 0a2 2 0 0 1-4 0Z"/><path d="M5.5 8a2.5 2.5 0 1 1 5 0a2.5 2.5 0 0 1-5 0Z"/><path d="M7 8a1 1 0 1 1 2 0a1 1 0 0 1-2 0Z"/><path d="M7.5 8a.5.5 0 1 1 1 0a.5.5 0 0 1-1 0Z"/></g></svg>',
                    ),
                    'image_select' => array(
                        'name' => esc_html__( 'Image Select', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" color="currentColor"><path d="M11.5 3C7.022 3 4.782 3 3.391 4.391S2 8.021 2 12.501c0 4.478 0 6.717 1.391 8.108S7.021 22 11.5 22c4.478 0 6.718 0 8.109-1.391S21 16.979 21 12.5c0-1.36 0-2.514-.039-3.5"/><path d="M4.5 21.5c4.372-5.225 9.274-12.116 16.498-7.457M14 6s1 0 2 2c0 0 3.177-5 6-6"/></g></svg>',
                    ),
                    'upload' => array(
                        'name' => esc_html__( 'Upload', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5"><path stroke-miterlimit="10" d="M12 3.212v12.026"/><path stroke-linejoin="round" d="M16.625 7.456L12.66 3.49a.937.937 0 0 0-1.318 0L7.375 7.456M2.75 13.85v4.625a2.31 2.31 0 0 0 2.313 2.313h13.875a2.31 2.31 0 0 0 2.312-2.313V13.85"/></g></svg>',
                    ),
                ),
            ),
            'matrix-fields' => array(
                'label'     => __( 'Matrix', 'admin-site-enhancements' ),
                'fields'    => array(
                    'likert_matrix_scale' => array(
                        'name' => esc_html__( 'Likert / Matrix Scale', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><rect width="16.5" height="16.5" x="3.75" y="3.75" rx="3"/><path d="M3.75 9.25h16.5m-16.5 5.5h16.5m-11-11v16.5m5.5-16.5v16.5"/></g></svg>',
                    ),
                    'matrix_of_dropdowns' => array(
                        'name' => esc_html__( 'Matrix of Uniform Dropdowns', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M5 13.75c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4C5.56 17 5 16.44 5 15.75zm1.5.25v1.5H10V14zm7.25-1.5c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2c0-.69-.56-1.25-1.25-1.25zm.25 3V14h3.5v1.5zM5 8.25C5 7.56 5.56 7 6.25 7h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4c-.69 0-1.25-.56-1.25-1.25zm1.5.25V10H10V8.5zM13.75 7c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2C19 7.56 18.44 7 17.75 7zm.25 3V8.5h3.5V10zM2 6.75A2.75 2.75 0 0 1 4.75 4h14.5A2.75 2.75 0 0 1 22 6.75v10.5A2.75 2.75 0 0 1 19.25 20H4.75A2.75 2.75 0 0 1 2 17.25zM4.75 5.5c-.69 0-1.25.56-1.25 1.25v10.5c0 .69.56 1.25 1.25 1.25h14.5c.69 0 1.25-.56 1.25-1.25V6.75c0-.69-.56-1.25-1.25-1.25z"/></svg>',
                    ),
                    'matrix_of_variable_dropdowns_two' => array(
                        'name' => esc_html__( 'Matrix of Two Variable Dropdowns', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M5 13.75c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4C5.56 17 5 16.44 5 15.75zm1.5.25v1.5H10V14zm7.25-1.5c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2c0-.69-.56-1.25-1.25-1.25zm.25 3V14h3.5v1.5zM5 8.25C5 7.56 5.56 7 6.25 7h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4c-.69 0-1.25-.56-1.25-1.25zm1.5.25V10H10V8.5zM13.75 7c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2C19 7.56 18.44 7 17.75 7zm.25 3V8.5h3.5V10zM2 6.75A2.75 2.75 0 0 1 4.75 4h14.5A2.75 2.75 0 0 1 22 6.75v10.5A2.75 2.75 0 0 1 19.25 20H4.75A2.75 2.75 0 0 1 2 17.25zM4.75 5.5c-.69 0-1.25.56-1.25 1.25v10.5c0 .69.56 1.25 1.25 1.25h14.5c.69 0 1.25-.56 1.25-1.25V6.75c0-.69-.56-1.25-1.25-1.25z"/></svg>',
                    ),
                    'matrix_of_variable_dropdowns_three' => array(
                        'name' => esc_html__( 'Matrix of Three Variable Dropdowns', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M5 13.75c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4C5.56 17 5 16.44 5 15.75zm1.5.25v1.5H10V14zm7.25-1.5c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2c0-.69-.56-1.25-1.25-1.25zm.25 3V14h3.5v1.5zM5 8.25C5 7.56 5.56 7 6.25 7h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4c-.69 0-1.25-.56-1.25-1.25zm1.5.25V10H10V8.5zM13.75 7c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2C19 7.56 18.44 7 17.75 7zm.25 3V8.5h3.5V10zM2 6.75A2.75 2.75 0 0 1 4.75 4h14.5A2.75 2.75 0 0 1 22 6.75v10.5A2.75 2.75 0 0 1 19.25 20H4.75A2.75 2.75 0 0 1 2 17.25zM4.75 5.5c-.69 0-1.25.56-1.25 1.25v10.5c0 .69.56 1.25 1.25 1.25h14.5c.69 0 1.25-.56 1.25-1.25V6.75c0-.69-.56-1.25-1.25-1.25z"/></svg>',
                    ),
                    'matrix_of_variable_dropdowns_four' => array(
                        'name' => esc_html__( 'Matrix of Four Variable Dropdowns', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M5 13.75c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4C5.56 17 5 16.44 5 15.75zm1.5.25v1.5H10V14zm7.25-1.5c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2c0-.69-.56-1.25-1.25-1.25zm.25 3V14h3.5v1.5zM5 8.25C5 7.56 5.56 7 6.25 7h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4c-.69 0-1.25-.56-1.25-1.25zm1.5.25V10H10V8.5zM13.75 7c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2C19 7.56 18.44 7 17.75 7zm.25 3V8.5h3.5V10zM2 6.75A2.75 2.75 0 0 1 4.75 4h14.5A2.75 2.75 0 0 1 22 6.75v10.5A2.75 2.75 0 0 1 19.25 20H4.75A2.75 2.75 0 0 1 2 17.25zM4.75 5.5c-.69 0-1.25.56-1.25 1.25v10.5c0 .69.56 1.25 1.25 1.25h14.5c.69 0 1.25-.56 1.25-1.25V6.75c0-.69-.56-1.25-1.25-1.25z"/></svg>',
                    ),
                    'matrix_of_variable_dropdowns_five' => array(
                        'name' => esc_html__( 'Matrix of Five Variable Dropdowns', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M5 13.75c0-.69.56-1.25 1.25-1.25h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4C5.56 17 5 16.44 5 15.75zm1.5.25v1.5H10V14zm7.25-1.5c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2c0-.69-.56-1.25-1.25-1.25zm.25 3V14h3.5v1.5zM5 8.25C5 7.56 5.56 7 6.25 7h4c.69 0 1.25.56 1.25 1.25v2c0 .69-.56 1.25-1.25 1.25h-4c-.69 0-1.25-.56-1.25-1.25zm1.5.25V10H10V8.5zM13.75 7c-.69 0-1.25.56-1.25 1.25v2c0 .69.56 1.25 1.25 1.25h4c.69 0 1.25-.56 1.25-1.25v-2C19 7.56 18.44 7 17.75 7zm.25 3V8.5h3.5V10zM2 6.75A2.75 2.75 0 0 1 4.75 4h14.5A2.75 2.75 0 0 1 22 6.75v10.5A2.75 2.75 0 0 1 19.25 20H4.75A2.75 2.75 0 0 1 2 17.25zM4.75 5.5c-.69 0-1.25.56-1.25 1.25v10.5c0 .69.56 1.25 1.25 1.25h14.5c.69 0 1.25-.56 1.25-1.25V6.75c0-.69-.56-1.25-1.25-1.25z"/></svg>',
                    ),
                ),
            ),
            'date-time-fields' => array(
                'label'     => __( 'Date Time', 'admin-site-enhancements' ),
                'fields'    => array(
                    'date' => array(
                        'name' => esc_html__( 'Date', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 512 512"><rect width="416" height="384" x="48" y="80" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" rx="48"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M128 48v32m256-32v32m80 80H48m256 100l43.42-32H352v168m-160.13-89.37c9.11 0 25.79-4.28 36.72-15.47a37.9 37.9 0 0 0 11.13-27.26c0-26.12-22.59-39.9-47.89-39.9c-21.4 0-33.52 11.61-37.85 18.93M149 374.16c4.88 8.27 19.71 25.84 43.88 25.84c28.59 0 52.12-15.94 52.12-43.82c0-12.62-3.66-24-11.58-32.07c-12.36-12.64-31.25-17.48-41.55-17.48"/></svg>',
                    ),
                    'time' => array(
                        'name' => esc_html__( 'Time', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><path fill="currentColor" d="M8 15c-3.86 0-7-3.14-7-7s3.14-7 7-7s7 3.14 7 7s-3.14 7-7 7M8 2C4.69 2 2 4.69 2 8s2.69 6 6 6s6-2.69 6-6s-2.69-6-6-6"/><path fill="currentColor" d="M10 10.5c-.09 0-.18-.02-.26-.07l-2.5-1.5A.5.5 0 0 1 7 8.5v-4c0-.28.22-.5.5-.5s.5.22.5.5v3.72l2.26 1.35a.502.502 0 0 1-.26.93"/></svg>',
                    ),
                )
            ),
            'display-fields' => array(
                'label'     => __( 'Display', 'admin-site-enhancements' ),
                'fields'    => array(
                    'heading' => array(
                        'name' => esc_html__( 'Heading', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M8 17V7m0 5h8m0 5V7"/></g></svg>',
                    ),
                    'paragraph' => array(
                        'name' => esc_html__( 'Paragraph / Shortcode', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 15 15"><path fill="none" stroke="currentColor" d="M13 1.5H6.5a4 4 0 1 0 0 8h1m3 4.5V1.5M7.5 14V1.5"/></svg>',
                    ),
                    'html' => array(
                        'name' => esc_html__( 'Content / HTML', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="none"><path d="M0 0h24v24H0z"/><path fill="currentColor" d="M14.486 3.143a1 1 0 0 1 .692 1.233l-4.43 15.788a1 1 0 0 1-1.926-.54l4.43-15.788a1 1 0 0 1 1.234-.693M7.207 7.05a1 1 0 0 1 0 1.414L3.672 12l3.535 3.535a1 1 0 1 1-1.414 1.415L1.55 12.707a1 1 0 0 1 0-1.414L5.793 7.05a1 1 0 0 1 1.414 0m9.586 1.414a1 1 0 1 1 1.414-1.414l4.243 4.243a1 1 0 0 1 0 1.414l-4.243 4.243a1 1 0 0 1-1.414-1.415L20.328 12z"/></g></svg>',
                    ),
                    'image' => array(
                        'name' => esc_html__( 'Image', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><g fill="none" stroke="currentColor"><rect width="15" height="13" x=".5" y="1.5" rx=".5" ry=".5"/><path d="m.5 14l5.15-5.15a.5.5 0 0 1 .71 0l3.29 3.29a.5.5 0 0 0 .71 0l1.29-1.29a.5.5 0 0 1 .71 0L15.5 14"/></g><circle cx="11.5" cy="5.5" r="1.5" fill="currentColor"/></svg>',
                    ),
                    'separator' => array(
                        'name' => esc_html__( 'Separator', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 512 512"><path fill="currentColor" fill-rule="evenodd" d="M469.333 277.333H42.666v-42.666h426.667z" clip-rule="evenodd"/></svg>',
                    ),
                    'spacer' => array(
                        'name' => esc_html__( 'Spacer', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 21h9m7 0h-3M4 3h16m-8 2.5l3 3m-3-3l-3 3m3-3v13m0 0l3-3m-3 3l-3-3"/></svg>',
                    ),
                ),
            ),
            'hidden-fields' => array(
                'label'     => __( 'Hidden', 'admin-site-enhancements' ),
                'fields'    => array(
                    'user_id' => array(
                        'name' => esc_html__( 'User ID', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="9" r="3"/><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M17.97 20c-.16-2.892-1.045-5-5.97-5s-5.81 2.108-5.97 5"/></g></svg>',
                    ),
                    'hidden' => array(
                        'name' => esc_html__( 'Hidden', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16"><g fill="currentColor"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299l.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/><path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884l-12-12l.708-.708l12 12z"/></g></svg>',
                    ),
                ),
            ),
            'spam-protection-fields' => array(
                'label'     => __( 'Spam Protection', 'admin-site-enhancements' ),
                'fields'    => array(
                    'altcha' => array(
                        'name' => esc_html__( 'ALTCHA', 'admin-site-enhancements' ),
                        'svg'  => wp_kses( Form_Builder_Icons::get( 'altcha' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ),
                    ),
                    'captcha' => array(
                        'name' => esc_html__( 'reCAPTCHA', 'admin-site-enhancements' ),
                        'svg'  => '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M9.216 20.51q-2.697-.873-4.447-3.193t-1.75-5.33q0-.785.13-1.554t.415-1.504l-2.17 1.252l-.48-.86l3.89-2.25l2.25 3.885l-.865.519l-1.504-2.6q-.333.733-.49 1.525t-.157 1.606q0 2.694 1.604 4.787t4.093 2.832zm5.996-12.53v-1h2.975q-1.131-1.424-2.756-2.192T12 4.019q-1.586 0-2.966.57q-1.378.569-2.442 1.575l-.519-.904q1.193-1.048 2.696-1.644q1.504-.597 3.212-.597q1.956 0 3.698.786t3.033 2.262V3.481h1v4.5zm.138 14.905l-3.89-2.27l2.25-3.865l.86.5l-1.56 2.662q2.969-.387 4.96-2.637T19.962 12q0-.525-.07-1.032q-.07-.506-.217-.987h1.044q.122.48.182.978q.06.497.06 1.022q0 3.26-2.05 5.73q-2.051 2.47-5.219 3.068l2.158 1.246z"/></svg>',
                    ),
                    'turnstile' => array(
                        'name' => esc_html__( 'Turnstile', 'admin-site-enhancements' ),
                        'svg'  => '<svg width="54" height="54" fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 54 54"><path d="M27.315 7.261a19.45 19.45 0 0 0-13.518 4.917l1.23-6.743-3.193-.582-2.162 11.836 11.84 2.16.582-3.193-6.08-1.11a16.173 16.173 0 1 1-4.982 8.064l-3.142-.824A19.478 19.478 0 1 0 27.315 7.261z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M38.847 21.919 35.928 19 24.477 30.452 19.923 25.9 17 28.822l7.483 7.484 2.923-2.923-.011-.012L38.847 21.92z" fill="currentColor"/></svg>',
                    ),
                ),
            ),    
        );
        return apply_filters( 'formbuilder_field_selection', $formbuilder_fields );
    }

    public static function get_all_fields() {
        $fields_info = Form_Builder_Fields::field_selection();
        $all_fields = array();

        foreach ( $fields_info as $category_slug => $category_details ) {
            foreach ( $category_details['fields'] as $field_key => $field_info ) {
                $all_fields[$field_key] = $field_info;
            }
        }
        
        return $all_fields;
    }
    
    public static function create_row( $values, $return = true ) {
        global $wpdb, $formbuilder_duplicate_ids;

        $new_values = array();
        $key = isset( $values['field_key'] ) ? sanitize_text_field( $values['field_key'] ) : sanitize_text_field( $values['name'] );

        $new_values['field_key'] = sanitize_text_field( Form_Builder_Helper::get_unique_key( 'asenha_formbuilder_fields', 'field_key' ) );
        $new_values['name'] = sanitize_text_field( $values['name'] );
        $new_values['description'] = sanitize_text_field( $values['description'] );
        $new_values['type'] = sanitize_text_field( $values['type'] );
        $new_values['field_order'] = isset( $values['field_order'] ) ? absint( $values['field_order'] ) : '';
        $new_values['required'] = $values['required'] ? true : false;
        $new_values['form_id'] = isset( $values['form_id'] ) ? absint( $values['form_id'] ) : '';
        $new_values['created_at'] = sanitize_text_field(current_time( 'mysql' ) );

        $new_values['options'] = is_array( $values['options'] ) ? Form_Builder_Helper::sanitize_array( $values['options'] ) : sanitize_text_field( $values['options'] );

        $new_values['field_options'] = Form_Builder_Helper::sanitize_array( $values['field_options'], Form_Builder_Helper::get_field_options_sanitize_rules() );

        if ( isset( $values['default_value'] ) ) {
            $field_obj = Form_Builder_Fields::get_field_class( $new_values['type'] );
            $new_values['default_value'] = $field_obj->sanitize_value( $new_values['default_value'] );
        }

        self::preserve_format_option_backslashes( $new_values );

        foreach ( $new_values as $key => $val ) {
            if ( is_array( $val ) ) {
                $new_values[$key] = serialize( $val );
            }
        }

        $query_results = $wpdb->insert( $wpdb->prefix . 'asenha_formbuilder_fields', $new_values );
        $new_id = 0;
        if ( $query_results ) {
            $new_id = $wpdb->insert_id;
        }

        if ( ! $return ) {
            return false;
        }

        if ( $query_results ) {
            if ( isset( $values['id'] ) ) {
                $formbuilder_duplicate_ids[$values['id']] = $new_id;
            }
            return $new_id;
        } else {
            return false;
        }
    }

    public static function update_form_fields( $id, $values ) {
        global $wpdb;
        $all_fields = self::get_form_fields( $id );

        foreach ( $all_fields as $fid ) {
            $field_id = absint( $fid->id );
            if ( $field_id && ( isset( $values['fb-form-submitted'] ) && in_array( $field_id, $values['fb-form-submitted'] ) )) {
                $values['edited'][] = $field_id;
            }

            $field_array[$field_id] = $fid;
        }

        if ( isset( $values['edited'] ) ) {
            foreach ( $values['edited'] as $field_id ) {
                $default_field_cols = Form_Builder_Helper::get_form_fields_default();

                if ( isset( $field_array[$field_id] ) ) {
                    $field = $field_array[$field_id];
                } else {
                    $field = self::get_field_vars( $field_id );
                }

                if ( ! $field ) {
                    continue;
                }

                //updating the fields
                $field_obj = self::get_field_object( $field );
                $update_options = $field_obj->get_default_field_options();
                foreach ( $update_options as $opt => $default ) {
                    $field->field_options[$opt] = isset( $values['field_options'][$opt . '_' . absint( $field_id )] ) ? $values['field_options'][$opt . '_' . absint( $field_id )] : $default;
                }

                $new_field = array(
                    'field_options' => $field->field_options,
                    'default_value' => isset( $values['default_value_' . absint( $field_id )] ) ? $values['default_value_' . absint( $field_id )] : '',
                );

                foreach ( $default_field_cols as $col => $default ) {
                    $default = ( $default === '' ) ? $field->{$col} : $default;
                    $new_field[$col] = isset( $values['field_options'][$col . '_' . absint( $field->id )] ) ? $values['field_options'][$col . '_' . absint( $field->id )] : $default;
                }

                if ( 'likert_matrix_scale' == $field->type || 'matrix_of_dropdowns' == $field->type ) {
                    if ( is_array( $new_field['options'] ) && isset( $new_field['options']['rows']['000'] ) ) {
                        unset( $new_field['options']['rows']['000'] );
                    }
                    if ( is_array( $new_field['options'] ) && isset( $new_field['options']['columns']['000'] ) ) {
                        unset( $new_field['options']['columns']['000'] );
                    }
                } else {
                    if ( is_array( $new_field['options'] ) && isset( $new_field['options']['000'] ) ) {
                        unset( $new_field['options']['000'] );
                    }                    
                }

                self::update_fields( $field_id, $new_field );
            }
        }
    }

    public static function update_fields( $id, $values ) {
        global $wpdb;

        $values['required'] = $values['required'] ? true : false;

        $values['options'] = serialize( is_array( $values['options'] ) ? Form_Builder_Helper::sanitize_array( $values['options'] ) : sanitize_text_field( $values['options'] ) );

        $values['field_options'] = serialize( Form_Builder_Helper::sanitize_array( $values['field_options'], Form_Builder_Helper::get_field_options_sanitize_rules() ));

        if ( isset( $values['default_value'] ) ) {
            $field_obj = Form_Builder_Fields::get_field_class( $values['type'] );
            $values['default_value'] = serialize( $field_obj->sanitize_value( $values['default_value'] ) );
        }

        $query_results = $wpdb->update( $wpdb->prefix . 'asenha_formbuilder_fields', $values, array( 'id' => $id ) );
        return $query_results;
    }

    public static function duplicate_fields( $old_form_id, $form_id ) {
        global $wpdb;

        $query = $wpdb->prepare("SELECT hfi.*, hfm.name AS form_name 
            FROM {$wpdb->prefix}asenha_formbuilder_fields hfi 
            LEFT OUTER JOIN {$wpdb->prefix}asenha_formbuilder_forms hfm 
            ON hfi.form_id = hfm.id 
            WHERE hfi.form_id=%d 
            ORDER BY 'field_order'", $old_form_id
        );
        $fields = $wpdb->get_results( $query );

        foreach ( (array) $fields as $field ) {
            $values = array();
            self::fill_field( $values, $field, $form_id );
            self::create_row( $values );
        }
    }

    public static function fill_field(&$values, $field, $form_id ) {
        global $wpdb;
        $values['field_key'] = Form_Builder_Helper::get_unique_key( 'asenha_formbuilder_fields', 'field_key' );
        $values['form_id'] = $form_id;
        $cols_array = array( 'name', 'description', 'type', 'field_order', 'field_options', 'options', 'default_value', 'required' );
        foreach ( $cols_array as $col ) {
            $values[$col] = maybe_unserialize( $field->{$col});
        }
    }

    private static function preserve_format_option_backslashes(&$values ) {
        if ( isset( $values['field_options']['format'] ) ) {
            $values['field_options']['format'] = self::preserve_backslashes( $values['field_options']['format'] );
        }
    }

    public static function preserve_backslashes( $value ) {
        // If backslashes have already been added, don't add them again
        if (strpos( $value, '\\\\' ) === false ) {
            $value = addslashes( $value );
        }

        return $value;
    }

    public static function destroy_row( $field_id ) {
        global $wpdb;
        $field = self::get_field_vars( $field_id );
        if ( ! $field ) {
            return false;
        }

        $query = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'asenha_formbuilder_entry_meta WHERE field_id=%d', absint( $field_id ) );
        $wpdb->query( $query );

        $query = $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'asenha_formbuilder_fields WHERE id=%d', absint( $field_id ) );
        return $wpdb->query( $query );
    }

    public static function get_field_vars( $field_id ) {
        if ( empty( $field_id ) )
            return;
        global $wpdb;
        $query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'asenha_formbuilder_fields WHERE id=%d', absint( $field_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = $wpdb->get_row( $query );
        if ( empty( $results ) ) {
            return $results;
        }

        self::prepare_options( $results );
        return wp_unslash( $results );
    }

    private static function prepare_options(&$results ) {
        $results->field_options = maybe_unserialize( $results->field_options );
        $results->options = maybe_unserialize( $results->options );
        $results->default_value = maybe_unserialize( $results->default_value );
    }

    public static function get_option( $field, $option ) {
        return is_array( $field ) ? self::get_option_in_array( $field, $option ) : self::get_option_in_object( $field, $option );
    }

    public static function get_option_in_array( $field, $option ) {
        if ( isset( $field[$option] ) ) {
            $this_option = $field[$option];
        } elseif ( isset( $field['field_options'] ) && is_array( $field['field_options'] ) && isset( $field['field_options'][$option] ) ) {
            $this_option = $field['field_options'][$option];
        } else {
            $this_option = '';
        }
        return $this_option;
    }

    public static function get_option_in_object( $field, $option ) {
        return isset( $field->field_options[$option] ) ? $field->field_options[$option] : '';
    }

    public static function get_error_msg( $field, $error ) {
        $field_name = $field->name ? $field->name : '';
        $max_length = intval( Form_Builder_Fields::get_option( $field, 'max' ) );

        $defaults = array(
            'invalid' => sprintf(
                /* translators: %s: field name */
                esc_html__( '%s is invalid.', 'admin-site-enhancements' ), 
                $field_name 
            ),
            'blank' => sprintf(
                /* translators: %s: field name */
                esc_html__( '%s is required.', 'admin-site-enhancements' ), 
                $field_name 
            ),
            'max_char' => sprintf(
                /* translators: %s: max characters length */
                esc_html__( '%s characters only allowed.', 'admin-site-enhancements' ), 
                $max_length 
            ),
        );
        $msg = Form_Builder_Fields::get_option( $field, $error );
        $msg = empty( $msg ) ? $defaults[$error] : $msg;
        return $msg;
    }

    public static function get_field_object( $field ) {
        if ( ! is_object( $field ) ) {
            $field = self::get_field_vars( $field );
        }
        return self::get_field_class( $field->type, $field );
    }

    public static function get_field_class( $field_type, $field = 0 ) {
        $class = self::get_field_type_class( $field_type );
        $field_obj = new $class( $field, $field_type );
        return $field_obj;
    }

    private static function get_field_type_class( $field_type = '' ) {
        $type_classes = apply_filters( 'formbuilder_field_type_class', array(
            'text' => 'Form_Builder_Field_Text',
            'textarea' => 'Form_Builder_Field_Textarea',
            'select' => 'Form_Builder_Field_Select',
            'radio' => 'Form_Builder_Field_Radio',
            'checkbox' => 'Form_Builder_Field_Checkbox',
            'image_select' => 'Form_Builder_Field_Image_Select',
            'number' => 'Form_Builder_Field_Number',
            'phone' => 'Form_Builder_Field_Phone',
            'url' => 'Form_Builder_Field_Url',
            'email' => 'Form_Builder_Field_Email',
            'user_id' => 'Form_Builder_Field_User_ID',
            'html' => 'Form_Builder_Field_HTML',
            'hidden' => 'Form_Builder_Field_Hidden',
            'name' => 'Form_Builder_Field_Name',
            'heading' => 'Form_Builder_Field_Heading',
            'paragraph' => 'Form_Builder_Field_Paragraph',
            'image' => 'Form_Builder_Field_Image',
            'spacer' => 'Form_Builder_Field_Spacer',
            'range_slider' => 'Form_Builder_Field_Range_Slider',
            'address' => 'Form_Builder_Field_Address',
            'scale' => 'Form_Builder_Field_Scale',
            'star' => 'Form_Builder_Field_Star',
            'likert_matrix_scale' => 'Form_Builder_Field_Likert_Matrix_Scale',
            'matrix_of_dropdowns' => 'Form_Builder_Field_Matrix_Of_Dropdowns',
            'matrix_of_variable_dropdowns' => 'Form_Builder_Field_Matrix_Of_Variable_Dropdowns',
            'matrix_of_variable_dropdowns_two' => 'Form_Builder_Field_Matrix_Of_Two_Variable_Dropdowns',
            'matrix_of_variable_dropdowns_three' => 'Form_Builder_Field_Matrix_Of_Three_Variable_Dropdowns',
            'matrix_of_variable_dropdowns_four' => 'Form_Builder_Field_Matrix_Of_Four_Variable_Dropdowns',
            'matrix_of_variable_dropdowns_five' => 'Form_Builder_Field_Matrix_Of_Five_Variable_Dropdowns',
            'separator' => 'Form_Builder_Field_Separator',
            'spinner' => 'Form_Builder_Field_Spinner',
            'date' => 'Form_Builder_Field_Date',
            'time' => 'Form_Builder_Field_Time',
            'upload' => 'Form_Builder_Field_Upload',
            'altcha' => 'Form_Builder_Field_Altcha',
            'captcha' => 'Form_Builder_Field_Captcha',
            'turnstile' => 'Form_Builder_Field_Turnstile',
        ) );
        if ( $field_type ) {
            return isset( $type_classes[$field_type] ) ? $type_classes[$field_type] : 'Form_Builder_Field_Text';
        } else {
            return $type_classes;
        }
    }

    public static function include_field_class() {
        $classes = self::get_field_type_class();
        include FORMBUILDER_PATH . 'classes/fields/form-builder-field-type.php';
        foreach ( $classes as $class ) {
            $class = str_replace( '_', '-', strtolower( $class ) );
            if ( file_exists( FORMBUILDER_PATH . 'classes/fields/' . $class . '.php' ) ) {
                include FORMBUILDER_PATH . 'classes/fields/' . $class . '.php';
            }
        }
        do_action( 'formbuilder_include_field_class' );
    }

    public static function show_fields( $fields ) {
        foreach ( $fields as $field ) {
            $field_obj = Form_Builder_Fields::get_field_class( $field['type'], $field );
            $field_obj->show_field();
        }
    }

}

new Form_Builder_Fields();
