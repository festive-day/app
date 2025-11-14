<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Settings {

    public function __construct() {
        add_action( 'wp_ajax_formbuilder_test_email_template', array( $this, 'send_test_email' ), 10, 0 );
    }

    public static function default_settings_values() {
        return array(
            'altcha_type'           => 'checkbox', // checkbox | invisible
            'altcha_secret_key'     => '',
            're_type'               => 'v2', // v2 | v3
            'pubkey_v2'             => '',
            'privkey_v2'            => '',
            'pubkey_v3'             => '',
            'privkey_v3'            => '',
            're_lang'               => 'en',
            're_threshold'          => '0.2',
            'turnstile_theme'       => 'light', // light | dark | auto
            'turnstile_site_key'    => '',
            'turnstile_secret_key'  => '',
            'header_image'          => '',
            'email_template'        => 'boxed-plain',
        );
    }
    
    public static function get_settings() {
        $options = get_option( ASENHA_SLUG_U, array() );

        // ALTCHA
        $altcha_widget = isset( $options['altcha_widget'] ) ? $options['altcha_widget'] : 'checkbox';
        $altcha_secret_key = isset( $options['form_builder_altcha_secret_key'] ) ? $options['form_builder_altcha_secret_key'] : '';

        // Google reCAPTCHA
        $recaptcha_widget_version_raw = isset( $options['recaptcha_widget'] ) ? $options['recaptcha_widget'] : 'v2_checkbox';
        $recaptcha_widget_version_parts = explode( '_', $recaptcha_widget_version_raw );
        $recaptcha_widget_version = $recaptcha_widget_version_parts[0]; // v2 | v3

        $site_key_v2 = '';
        $secret_key_v2 = '';
        $site_key_v3 = '';
        $secret_key_v3 = '';

        switch ( $recaptcha_widget_version_raw ) {
            case 'v2_checkbox';
                $site_key_v2 = isset( $options['form_builder_recaptcha_site_key_v2_checkbox'] ) ? $options['form_builder_recaptcha_site_key_v2_checkbox'] : '';
                $secret_key_v2 = isset( $options['form_builder_recaptcha_secret_key_v2_checkbox'] ) ? $options['form_builder_recaptcha_secret_key_v2_checkbox'] : '';
                break;

            case 'v3_invisible';
                $site_key_v3 = isset( $options['form_builder_recaptcha_site_key_v3_invisible'] ) ? $options['form_builder_recaptcha_site_key_v3_invisible'] : '';
                $secret_key_v3 = isset( $options['form_builder_recaptcha_secret_key_v3_invisible'] ) ? $options['form_builder_recaptcha_secret_key_v3_invisible'] : '';
                break;
        }

        // Cloudflare Turnstile
        $turnstile_widget_theme = isset( $options['turnstile_widget_theme'] ) ? $options['turnstile_widget_theme'] : 'light';
        $turnstile_site_key = isset( $options['form_builder_turnstile_site_key'] ) ? $options['form_builder_turnstile_site_key'] : '';
        $turnstile_secret_key = isset( $options['form_builder_turnstile_secret_key'] ) ? $options['form_builder_turnstile_secret_key'] : '';
        
        $email_template = ( array_key_exists( 'form_builder_email_template', $options ) ) ? $options['form_builder_email_template'] : 'boxed-plain';
        $email_header_image_attachment_id = ( array_key_exists( 'form_builder_email_header_image_attachment_id', $options ) ) ? $options['form_builder_email_header_image_attachment_id'] : '';

        $settings = array(
            'altcha_secret_key'     => $altcha_secret_key,
            'altcha_type'           => $altcha_widget,
            're_type'               => $recaptcha_widget_version, // v2 | v3
            'pubkey_v2'             => $site_key_v2,
            'privkey_v2'            => $secret_key_v2,
            'pubkey_v3'             => $site_key_v3,
            'privkey_v3'            => $secret_key_v3,
            're_lang'               => 'en',
            're_threshold'          => '0.2',
            'turnstile_theme'       => $turnstile_widget_theme,
            'turnstile_site_key'    => $turnstile_site_key,
            'turnstile_secret_key'  => $turnstile_secret_key,
            'header_image'          => $email_header_image_attachment_id,
            'email_template'        => $email_template,
        );

        $settings = wp_parse_args( $settings, self::default_settings_values() );

        return $settings;
    }

    public function send_test_email() {
        if ( ! current_user_can( 'manage_options' ) )
            return;

        $settings = self::get_settings();

        $header_image = $settings['header_image'];

        $email_template = Form_Builder_Helper::get_post( 'email_template' );
        $email_template_underscored = str_replace( '-', '_', $email_template );
        $test_email = Form_Builder_Helper::get_post( 'test_email' );
        $email_subject = esc_html__( 'Test Email', 'admin-site-enhancements' );
        $count = 0;

        $contents = array(
            0 => array(
                'title' => 'Name',
                'value' => 'John Doe'
            ),
            1 => array(
                'title' => 'Email',
                'value' => 'noreply@gmail.com'
            ),
            2 => array(
                'title' => 'Subject',
                'value' => 'Exciting Updates and Important Information Inside!'
            ),
            3 => array(
                'title' => 'Message',
                'value' => '<p>I hope this email finds you well. We are thrilled to share some exciting updates and important information that we believe you will find valuable.</p><p>Your satisfaction is our priority, and we are committed to delivering the best possible experience.</p>'
            )
        );

        $email_message = '<p style="margin-bottom:20px">';
        $email_message .= esc_html__( 'Hello, this is a test email.', 'admin-site-enhancements' );
        $email_message .= '</p>';
        foreach ( $contents as $content ) {
            $count++;
            $email_message .= call_user_func( 'Form_Builder_Email::' . $email_template_underscored . '_row_template', $content['title'], $content['value'], $count );
        }
        
        $footer_text = __( 'This email was sent from #linked_site_name.', 'admin-site-enhancements' );
        $linked_site_name = '<a href="' . get_site_url() . '">' . get_bloginfo( 'name' ) . '</a>';
        $footer_text = str_replace( '#linked_site_name', $linked_site_name, $footer_text );
        
        ob_start();
        include( FORMBUILDER_PATH . 'settings/email-templates/' . $email_template . '.php' );
        $form_html = ob_get_clean();

        $admin_email = get_option( 'admin_email' );
        $site_name = get_bloginfo( 'name' );
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . esc_attr( $site_name ) . ' <' . esc_attr( $admin_email ) . '>';
        $mail = wp_mail( $test_email, $email_subject, $form_html, $headers );
        if ( $mail ) {
            die( wp_json_encode(
                array(
                    'success' => true,
                    'message' => esc_html__( 'Email sent successfully', 'admin-site-enhancements' )
                )
            ) );
        }
        die( wp_json_encode(
            array(
                'success' => false,
                'message' => esc_html__( 'Failed to send email', 'admin-site-enhancements' )
            )
        ) );
    }

}

new Form_Builder_Settings();
