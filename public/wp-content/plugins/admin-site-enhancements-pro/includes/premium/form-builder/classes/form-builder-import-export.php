<?php

defined( 'ABSPATH' ) || die();

class Form_Builder_Import_Export {

    public function __construct() {
        // Process a settings export that generates a .json file of the form settings
        add_action( 'admin_init', array( $this, 'process_settings_export' ) );
        // Process a settings export that generates a .json file of the form style
        add_action( 'admin_init', array( $this, 'process_style_export' ) );
        // Process a settings import from a json file
        add_action( 'admin_init', array( $this, 'process_settings_import' ) );
        // Process a style import from a json file
        add_action( 'admin_init', array( $this, 'process_style_import' ) );
    }

    public function process_settings_export() {
        $id = Form_Builder_Helper::get_post( 'formbuilder_form_id', 'absint' );

        if ( 'export_form' != Form_Builder_Helper::get_post( 'formbuilder_imex_action' ) || ! $id ) {
            return;
        }

        if ( ! wp_verify_nonce( Form_Builder_Helper::get_post( 'formbuilder_imex_export_nonce' ), 'formbuilder_imex_export_nonce' ) ) {
            return;
        }

        global $wpdb;

        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}asenha_formbuilder_forms WHERE id=%d", $id );
        $forms = $wpdb->get_results( $query );

        foreach ( $forms as $form ) {
            $form_styles = $form->styles ? unserialize( $form->styles ) : [];
            $exdat['form_key'] = $form->form_key ? $form->form_key : '';
            $exdat['options'] = $form->options ? unserialize( $form->options ) : [];
            $exdat['status'] = $form->status ? $form->status : 'published';
            $exdat['settings'] = $form->settings ? unserialize( $form->settings ) : [];
            $exdat['styles'] = $form_styles;
            $exdat['created_at'] = $form->created_at ? $form->created_at : '';
            $fields = Form_Builder_Fields::get_form_fields( $form->id );
            $exfield = array();
            foreach ( $fields as $field ) {
                $efield = array();
                $efield['name'] = $field->name;
                $efield['description'] = $field->description;
                $efield['type'] = $field->type;
                $efield['default_value'] = $field->default_value;
                $efield['options'] = $field->options;
                $efield['field_order'] = absint( $field->field_order );
                $efield['required'] = absint( $field->required );
                $efield['field_options'] = $field->field_options;
                $exfield[] = $efield;
            }
            $exdat['field'] = $exfield;

            $form_style = isset( $form_styles['form_style'] ) && $form_styles['form_style'] ? $form_styles['form_style'] : 'default-style';

            if ( $form_style == 'custom-style' ) {
                $form_style_id = $form_styles['form_style_template'];
                $formbuilder_styles = get_post_meta( $form_style_id, 'formbuilder_styles', true );
                $formbuilder_styles = Form_Builder_Helper::sanitize_array( $formbuilder_styles, Form_Builder_Styles::get_styles_sanitize_array() );
                if ( $formbuilder_styles ) {
                    $exdat['style'] = $formbuilder_styles;
                }
            }

            ignore_user_abort( true );

            nocache_headers();
            header( 'Content-Type: application/json; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=fb-' . $id . '-' . date( 'm-d-Y' ) . '.json' );
            header("Expires: 0");

            echo wp_json_encode( $exdat );
            exit;
        }
    }

    public function process_style_export() {
        $id = Form_Builder_Helper::get_post( 'formbuilder_style_id', 'absint' );

        if ( 'export_style' != Form_Builder_Helper::get_post( 'formbuilder_imex_action' ) || ! $id ) {
            return;
        }

        if ( ! wp_verify_nonce( Form_Builder_Helper::get_post( 'formbuilder_imex_export_nonce' ), 'formbuilder_imex_export_nonce' ) ) {
            return;
        }

        global $wpdb;

        $formbuilder_styles = get_post_meta( $id, 'formbuilder_styles', true );
        $formbuilder_styles = Form_Builder_Helper::sanitize_array( $formbuilder_styles, Form_Builder_Styles::get_styles_sanitize_array() );

        if ( $formbuilder_styles ) {

            ignore_user_abort( true );

            nocache_headers();
            header( 'Content-Type: application/json; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=fb-style-' . $id . '-' . date( 'm-d-Y' ) . '.json' );
            header("Expires: 0");

            echo wp_json_encode( $formbuilder_styles );
            exit;
        }
    }

    public function process_settings_import() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $form_id = Form_Builder_Helper::get_post( 'formbuilder_form_id', 'absint' );

        if ( 'import_form' != Form_Builder_Helper::get_post( 'formbuilder_imex_action' ) || ! $form_id ) {
            return;
        }

        if ( ! wp_verify_nonce( Form_Builder_Helper::get_post( 'formbuilder_imex_import_nonce' ), 'formbuilder_imex_import_nonce' ) ) {
            return;
        }

        global $wpdb;

        $filename = sanitize_text_field( wp_unslash( $_FILES['formbuilder_import_file']['name'] ) );
        $extension = explode( '.', $filename );
        $extension = end( $extension );

        if ( $extension != 'json' ) {
            wp_die(esc_html__( 'Please upload a valid .json file' ) );
        }

        $formbuilder_import_file = sanitize_text_field( $_FILES['formbuilder_import_file']['tmp_name'] );

        if ( empty( $formbuilder_import_file ) ) {
            wp_die(esc_html__( 'Please upload a file to import' ) );
        }

        // Retrieve the settings from the file and convert the json object to an array.
        $imdat = json_decode( file_get_contents( $formbuilder_import_file ), true );

        if ( ! ( isset( $imdat['options'] ) && isset( $imdat['settings'] ) && isset( $imdat['styles'] ) )) {
            wp_die(esc_html__( 'Please upload a valid file to import' ) );
        }

        $options = Form_Builder_Helper::recursive_parse_args( $imdat['options'], Form_Builder_Helper::get_form_options_default() );
        $options = Form_Builder_Helper::sanitize_array( $options, Form_Builder_Helper::get_form_options_sanitize_rules() );

        $settings = Form_Builder_Helper::recursive_parse_args( $imdat['settings'], Form_Builder_Helper::get_form_settings_default() );
        $settings = Form_Builder_Helper::sanitize_array( $settings, Form_Builder_Helper::get_form_settings_sanitize_rules() );

        $styles = Form_Builder_Helper::recursive_parse_args( $imdat['styles'], array( 'form_style' => 'default-style', 'form_style_template' => '' ) );
        $styles = Form_Builder_Helper::sanitize_array( $styles, Form_Builder_Helper::get_form_styles_sanitize_rules() );

        if ( isset( $imdat['style'] ) ) {
            $new_post = array(
                'post_type' => 'formbuilder-styles',
                'post_title' => 'formbuilder-style-' . $form_id,
                'post_status' => 'publish',
            );
            $style_id = wp_insert_post( $new_post );
            $formbuilder_styles = Form_Builder_Helper::recursive_parse_args( $imdat['style'], Form_Builder_Styles::default_styles() );
            $formbuilder_styles = Form_Builder_Helper::sanitize_array( $formbuilder_styles, Form_Builder_Styles::get_styles_sanitize_array() );
            update_post_meta( $style_id, 'formbuilder_styles', $formbuilder_styles );
            $styles['form_style_template'] = $style_id;
        }

        $form = array(
            'options' => serialize( $options ),
            'status' => esc_html( $imdat['status'] ),
            'settings' => serialize( $settings ),
            'styles' => serialize( $styles ),
            'created_at' => gmdate( 'Y-m-d H:i:s', strtotime(esc_html( $imdat['created_at'] ) )),
        );

        if ( empty( $imdat['created_at'] ) ) {
            $form['created_at'] = current_time( 'mysql' );
        }

        $wpdb->update( $wpdb->prefix . 'asenha_formbuilder_forms', $form, array( 'id' => $form_id ) );
        $query = $wpdb->prepare("DELETE FROM {$wpdb->prefix}asenha_formbuilder_fields WHERE form_id=%d", $form_id );
        $wpdb->query( $query );

        if ( isset( $imdat['field'] ) && is_array( $imdat['field'] ) && ! empty( $imdat['field'] ) ) {
            foreach ( $imdat['field'] as $field ) {
                Form_Builder_Fields::create_row( array(
                    'name' => isset( $field['name'] ) ? $field['name'] : '',
                    'description' => isset( $field['description'] ) ? $field['description'] : '',
                    'type' => isset( $field['type'] ) ? $field['type'] : 'text',
                    'default_value' => isset( $field['default_value'] ) ? $field['default_value'] : '',
                    'options' => isset( $field['options'] ) ? $field['options'] : '',
                    'field_order' => isset( $field['field_order'] ) ? $field['field_order'] : '',
                    'form_id' => absint( $form_id ),
                    'required' => isset( $field['required'] ) ? $field['required'] : false,
                    'field_options' => isset( $field['field_options'] ) ? $field['field_options'] : array()
                ) );
            }
        }

        $_SESSION['formbuilder_message'] = esc_html__( 'Settings imported successfully', 'admin-site-enhancements' );
    }

    public function process_style_import() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $style_id = Form_Builder_Helper::get_post( 'formbuilder_style_id', 'absint' );

        if ( 'import_style' != Form_Builder_Helper::get_post( 'formbuilder_imex_action' ) || ! $style_id ) {
            return;
        }

        if ( ! wp_verify_nonce( Form_Builder_Helper::get_post( 'formbuilder_imex_import_nonce' ), 'formbuilder_imex_import_nonce' ) ) {
            return;
        }

        global $wpdb;

        $filename = sanitize_text_field( wp_unslash( $_FILES['formbuilder_import_file']['name'] ) );
        $extension = explode( '.', $filename );
        $extension = end( $extension );

        if ( $extension != 'json' ) {
            wp_die(esc_html__( 'Please upload a valid .json file' ) );
        }

        $formbuilder_import_file = sanitize_text_field( $_FILES['formbuilder_import_file']['tmp_name'] );

        if ( empty( $formbuilder_import_file ) ) {
            wp_die(esc_html__( 'Please upload a file to import' ) );
        }

        // Retrieve the settings from the file and convert the json object to an array.
        $imdat = json_decode( file_get_contents( $formbuilder_import_file ), true );
        $formbuilder_styles = Form_Builder_Helper::recursive_parse_args( $imdat, Form_Builder_Styles::default_styles() );
        $formbuilder_styles = Form_Builder_Helper::sanitize_array( $formbuilder_styles, Form_Builder_Styles::get_styles_sanitize_array() );
        update_post_meta( $style_id, 'formbuilder_styles', $formbuilder_styles );

        $_SESSION['formbuilder_message'] = esc_html__( 'Form style imported successfully', 'admin-site-enhancements' );
    }

}

new Form_Builder_Import_Export();
