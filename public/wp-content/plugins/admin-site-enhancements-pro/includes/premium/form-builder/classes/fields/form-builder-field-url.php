<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Url extends Form_Builder_Field_Type {

    protected $type = 'url';

    protected function field_settings_for_type() {
        return array(
            'clear_on_focus' => true,
            'invalid' => true,
        );
    }

    public function validate( $args ) {
        $errors = array();

        $value = $args['value'];
        // vi( $value, '', 'before' );
        if ( trim( $value ) == 'http://' || trim( $value ) == 'https://' || empty( $value ) ) {
            $value = '';
        } else {
            $value = esc_url_raw( $value );
            $value = preg_match( '/^(https?|ftps?|mailto|news|feed|telnet ):/is', $value ) ? $value : 'https://' . $value;
        }
        // vi( $value, '', 'after' );

        // if ( ! empty( $value ) && !preg_match( '/^http(s )?:\/\/(?:localhost|(?:[\da-z\.-]+\.[\da-z\.-]+))/i', $value ) ) {
        if ( ! empty( $value ) && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
            $errors['field' . $args['id']] = Form_Builder_Fields::get_error_msg( $this->field, 'invalid' );
        }

        return $errors;
    }

    public function sanitize_value(&$value ) {
        return Form_Builder_Helper::sanitize_value( 'esc_url_raw', $value );
    }

    protected function input_html() {
        $field_type = $this->type;
        ?>
        <input type="<?php echo esc_attr( $field_type ); ?>" <?php $this->field_attrs(); ?> />
        <?php
    }

}
