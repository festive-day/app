<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Star extends Form_Builder_Field_Type {

    protected $type = 'star';
    protected $array_allowed = false;

    public function field_settings_for_type() {
        return array(
            'default' => false,
            'max_width' => false,
        );
    }

    public function show_primary_options() {
        $field = $this->get_field();
        ?>
        <div class="fb-form-row">
            <label>
                <?php esc_html_e( 'Maximum Rating', 'admin-site-enhancements' ); ?>
            </label>
            <input type="number" name="field_options[maxnum_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['maxnum'] ); ?>" min="1" max="50" step="1" data-changestars="fb-field-star-<?php echo esc_attr( $field['id'] ); ?>" />
            <input type="hidden" name="field_options[minnum_<?php echo esc_attr( $field['id'] ); ?>]" />
        </div>
        <?php
    }

    public function sanitize_value(&$value ) {
        return Form_Builder_Helper::sanitize_value( 'intval', $value );
    }

    protected function input_html() {
        $field = $this->get_field();
        $max = isset( $field['maxnum'] ) ? $field['maxnum'] : 5;
        $field['options'] = range( 1, $max );
        $registered_fields = Form_Builder_Fields::get_all_fields();
        ?>

        <div class="formbuilder-star-group" id="fb-field-star-<?php echo esc_attr( $field['id'] ); ?>">
            <?php
            foreach ( $field['options'] as $opt_key => $opt ) {
                ?>
                <label class="fb-star-rating">
                    <input type="radio" name="<?php echo esc_attr( $this->html_name() ); ?>" value="<?php echo esc_attr( $opt ); ?>" />
                    <span class="fb-star-outline"><?php echo wp_kses( $registered_fields['star']['svg'], Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                </label>
                <?php
            }
            ?>
        </div>
        <?php
    }

}
