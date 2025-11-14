<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Captcha extends Form_Builder_Field_Type {

    protected $type = 'captcha';

    protected function field_settings_for_type() {
        return array(
            'required' => false,
            'invalid' => true,
            'captcha_size' => true,
            'default' => false,
            'max_width' => false
        );
    }

    public static function get_captcha_image_name() {
        $settings = Form_Builder_Settings::get_settings();
        if ( $settings['re_type'] === 'v3' ) {
            $image_name = 'recaptcha_v3';
        } else {
            $image_name = 'recaptcha';
        }
        return $image_name;
    }

    protected function new_field_settings() {
        $settings = Form_Builder_Settings::get_settings();
        return array(
            'invalid' => $settings['re_msg'],
        );
    }

    protected function extra_field_default_opts() {
        return array(
            'label' => 'none',
            'captcha_size' => 'normal',
            'captcha_theme' => 'light',
        );
    }

    public function front_field_input() {
        $settings = Form_Builder_Settings::get_settings();
        if ( ! self::should_show_captcha() ) {
            ?>
            <div class="howto">
                <?php esc_html_e( 'This field is not set up yet.', 'admin-site-enhancements' ); ?>
            </div>
            <?php            
        } else {
            ?>
            <div id="<?php echo esc_attr( $this->html_id() ); ?>" class="g-recaptcha" data-sitekey="<?php echo ( $settings['re_type'] == 'v3' ? esc_attr( $settings['pubkey_v3'] ) : esc_attr( $settings['pubkey_v2'] ) ); ?>" data-size="<?php echo esc_attr( $this->captcha_size( $settings ) ); ?>" data-theme="<?php echo esc_attr( $this->field['captcha_theme'] ); ?>"></div>
            <?php            
        }
    }

    protected function load_field_scripts() {
        $api_js_url = $this->api_url();
        wp_enqueue_script( 'captcha-api', $api_js_url, array(), FORMBUILDER_VERSION, true );
    }

    protected function api_url() {
        $formbuilder_settings = Form_Builder_Settings::get_settings();
        return $this->recaptcha_api_url( $formbuilder_settings );
    }

    protected function recaptcha_api_url( $settings ) {
        $api_js_url = 'https://www.google.com/recaptcha/api.js?';
        $api_js_url .= $settings['re_type'] == 'v3' ? 'render=' . $settings['pubkey_v3'] : '';
        $api_js_url .= empty( $lang ) ? '' : '&hl=' . $settings['re_lang'];
        return $api_js_url;
    }

    protected function captcha_size( $settings ) {
        if ( $settings['re_type'] == 'v3' ) {
            return 'invisible';
        }
        return $this->field['captcha_size'] === 'default' ? 'normal' : $this->field['captcha_size'];
    }

    protected function validate_against_api( $args ) {
        $errors = array();
        $settings = Form_Builder_Settings::get_settings();
        $resp = $this->send_api_check( $args );
        $response = json_decode( wp_remote_retrieve_body( $resp ), true );
        // vi( $response );

        if ( is_wp_error( $resp ) ) {
            $error_string = $resp->get_error_message();
            $errors['field' . $args['id']] = esc_html__( 'There was a problem verifying your captcha', 'admin-site-enhancements' );
            $errors['field' . $args['id']] .= ' ' . $error_string;
            return $errors;
        }

        if ( ! is_array( $response ) ) {
            $errors['field' . $args['id']] = esc_html__( 'There was a problem verifying your captcha', 'admin-site-enhancements' );
            return $errors;
        }

        if ( 'v3' === $settings['re_type'] && array_key_exists( 'score', $response ) ) {
            $threshold = floatval( $settings['re_threshold'] );
            $score = floatval( $response['score'] );
            if ( $score < $threshold ) {
                $response['success'] = false;
            }
        }

        if ( isset( $response['success'] ) && ! $response['success'] ) {
            $invalid_message = Form_Builder_Fields::get_option( $this->field, 'invalid' );
            if ( $invalid_message === esc_html__( 'The reCAPTCHA was not entered correctly', 'admin-site-enhancements' ) ) {
                $invalid_message = '';
            }
            $errors['field' . $args['id']] = ( $invalid_message === '' ? $settings['re_msg '] : $invalid_message );
        }

        return $errors;
    }

    public function validate( $args ) {
        // vi( $args );
        $errors = array();
        if ( ! self::should_show_captcha() ) {
            $errors['field' . $args['id']] = esc_html__( 'The reCAPTCHA keys are not entered.', 'admin-site-enhancements' );
            return $errors;
        } else {
            return $this->validate_against_api( $args );
        }
    }

    public static function should_show_captcha() {
        $settings = Form_Builder_Settings::get_settings();
        $site_key = $settings['re_type'] == 'v3' ? $settings['pubkey_v3'] : $settings['pubkey_v2'];
        return ! empty( $site_key );
    }

    protected function send_api_check( $args ) {
        $settings = Form_Builder_Settings::get_settings();
        $arg_array = array(
            'body' => array(
                'secret' => $settings['re_type'] == 'v3' ? $settings['privkey_v3'] : $settings['privkey_v2'],
                'response' => $args['value'],
                'remoteip' => Form_Builder_Helper::get_ip_address(),
                'token_field' => 'g-recaptcha-response',
            ),
        );
        return wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', $arg_array );
    }

    protected function input_html() {
        $html = '';
        if ( is_admin() ) {
            if ( ! Form_Builder_Field_Captcha::should_show_captcha() ) {
                ?>
                <div class="howto">
                    <?php esc_html_e( 'This field is not set up yet.', 'admin-site-enhancements' ); ?>
                </div>
                <?php
            } else {
                $image_name = Form_Builder_Field_Captcha::get_captcha_image_name();
                ?>
                <img src="<?php echo esc_url( FORMBUILDER_URL . 'assets/img/' . $image_name . '.png' ); ?>" style="width: 304px;" />
                <input type="hidden" name="<?php echo esc_attr( $this->html_name() ); ?>" value="1" />
                <?php
            }
        } else {
            $html = self::front_field_input();
        }

        return $html;
    }

}
