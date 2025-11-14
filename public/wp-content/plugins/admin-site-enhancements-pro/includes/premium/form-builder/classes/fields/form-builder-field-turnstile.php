<?php

defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Turnstile extends Form_Builder_Field_Type {

    protected $type = 'turnstile';

    protected function field_settings_for_type() {
        return array(
            'required' => true,
            'invalid' => false,
            'default' => false,
            'max_width' => false,
        );
    }

    // protected function new_field_settings() {
    //     $settings = Form_Builder_Settings::get_settings();
    //     return array(
    //         'invalid' => $settings['turnstile_msg'],
    //     );
    // }

    protected function extra_field_default_opts() {
        return array(
            'turnstile_theme' => 'light',
        );
    }

    protected function load_field_scripts() {
        asenha_register_turnstile_assets__premium_only();
        asenha_enqueue_turnstile_assets__premium_only();
    }

    public static function should_show_captcha() {
        $settings = Form_Builder_Settings::get_settings();
        return ( ! empty( $settings['turnstile_site_key'] ) && ! empty( $settings['turnstile_secret_key'] ) ) ? true : false;
    }

    protected function input_html() {
        $settings = Form_Builder_Settings::get_settings();
        $turnstile_theme = $settings['turnstile_theme'];
        $html = '';

        if ( is_admin() ) {
            if ( ! self::should_show_captcha() ) {
                ?>
                <div class="howto">
                    <?php esc_html_e( 'This field is not set up yet.', 'admin-site-enhancements' ); ?>
                </div>
                <?php
            } else {
                ?>
                <img src="<?php echo esc_url( FORMBUILDER_URL . 'assets/img/turnstile.webp' ); ?>" class="turnstile-builder-preview" />
                <input type="hidden" name="<?php echo esc_attr( $this->html_name() ); ?>" value="1" />
                <?php
            }
        } else {
            $turnstile = new ASENHA\Classes\CAPTCHA_Protection_Turnstile;
            $html = $turnstile->show_turnstile_widget( '#fb-submit-button ', 'turnstileWPCallback', 'form-builder-form', '-' . wp_rand(), 'form-builder', 'normal', false );
        }

        return $html;
    }

    public function validate( $args ) {
        // vi( $args );

        $errors = $args['errors']; // empty array

        if ( empty( $args['value'] ) ) {
            $errors['field' . $args['id']] = esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' );
        } else {
            $turnstile = new ASENHA\Classes\CAPTCHA_Protection_Turnstile;

            // Check Turnstile
            $check = $turnstile->turnstile_check( $args['value'] );
            $success = $check['success'];

            if( $success != true ) {
                $errors['field' . $args['id']] = esc_html__( 'Please verify that you are human.', 'admin-site-enhancements' );
            }            
        }

        return $errors;
    }
}