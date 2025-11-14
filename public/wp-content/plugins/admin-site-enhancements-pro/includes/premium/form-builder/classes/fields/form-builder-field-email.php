<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Email extends Form_Builder_Field_Type {

    protected $type = 'email';

    protected function field_settings_for_type() {
        return array(
            'clear_on_focus' => true,
            'invalid' => true,
        );
    }

    public function validate( $args ) {
        $errors = isset( $args['errors'] ) ? $args['errors'] : array();
        if ( $args['value'] != '' && ! is_email( $args['value'] ) ) {
            $errors['field' . $args['id']] = Form_Builder_Fields::get_error_msg( $this->field, 'invalid' );
        }
        return $errors;
    }

    public function sanitize_value(&$value ) {
        return Form_Builder_Helper::sanitize_value( 'sanitize_email', $value );
    }

    public function input_html() {
        ?>
        <input type="email" <?php $this->field_attrs(); ?> />
        <?php
    }

}
