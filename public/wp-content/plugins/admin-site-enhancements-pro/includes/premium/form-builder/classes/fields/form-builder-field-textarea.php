<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Textarea extends Form_Builder_Field_Type {

    protected $type = 'textarea';

    protected function field_settings_for_type() {
        return array(
            'value' => false,
            'clear_on_focus' => true
        );
    }

    protected function extra_field_default_opts() {
        return array(
            'rows' => '10',
        );
    }

    public function sanitize_value(&$value ) {
        return Form_Builder_Helper::sanitize_value( 'esc_textarea', $value );
    }

    protected function input_html() {
        $field = $this->get_field();
        $value = $this->prepare_esc_value();
        ?>
        <textarea <?php $this->field_attrs(); ?> rows="<?php echo absint( $field['rows'] ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
        <?php
    }

}
