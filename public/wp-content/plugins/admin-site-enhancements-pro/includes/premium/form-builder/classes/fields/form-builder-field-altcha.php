<?php

defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Altcha extends Form_Builder_Field_Type {

    protected $type = 'altcha';

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
    //         'invalid' => $settings['altcha_msg'],
    //     );
    // }

    protected function extra_field_default_opts() {
        return array(
            'altcha_type' => 'checkbox',
        );
    }

    protected function load_field_scripts() {
    	asenha_register_altcha_assets__premium_only();
    	asenha_enqueue_altcha_assets__premium_only();
    }

    public static function should_show_captcha() {
        $settings = Form_Builder_Settings::get_settings();
        return ( ! empty( $settings['altcha_secret_key'] ) ) ? true : false;
    }

    protected function input_html() {
        $settings = Form_Builder_Settings::get_settings();
        $altcha_type = $settings['altcha_type'];
        $html = '';

        if ( is_admin() ) {
            if ( ! self::should_show_captcha() ) {
                ?>
                <div class="howto">
                    <?php esc_html_e( 'This field is not set up yet.', 'admin-site-enhancements' ); ?>
                </div>
                <?php
            } else {
            	switch ( $altcha_type ) {
            		case 'checkbox';
		                ?>
		                <div class="altcha-builder-preview">
		                	<div class="altcha-checkbox-preview">
			                	<input type="checkbox" id="" required="" class="" name="">
			                	<label for="" class="">I'm not a robot</label>
			                </div>
			                <div class="altcha-svg-preview">
			                	<?php echo wp_kses( Form_Builder_Icons::get( 'altcha' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?>
			                </div>
		                </div>
		                <?php            		
            			break;
            			
            		case 'invisible';
	            		?>
		                <div class="altcha-builder-preview altcha-invisible-preview">
			                <div class="altcha-svg-preview">
			                	<?php echo wp_kses( Form_Builder_Icons::get( 'altcha' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?>
			                </div>
		                	<div class="altcha-title-preview">
			                	ALTCHA
			                </div>
		                </div>
	            		<?php            		
            			break;
            	}
            	?>
                <input type="hidden" name="<?php echo esc_attr( $this->html_name() ); ?>" value="1" />
                <?php
            }
        } else {
        	$altcha = new ASENHA\Classes\CAPTCHA_Protection_ALTCHA;
            $html = $altcha->altcha_wordpress_render_widget( 'captcha' );
        }

        return $html;
    }

    public function validate( $args ) {
    	// vi( $args );

    	$altcha_payload = $args['value'];

    	$altcha = new ASENHA\Classes\CAPTCHA_Protection_ALTCHA;
        $errors = array();

        if ( false === $altcha->verify( $altcha_payload ) ) {
            $errors['field' . $args['id']] = esc_html__( 'Something went wrong.', 'admin-site-enhancements' );
        }

        return $errors;
    }

}