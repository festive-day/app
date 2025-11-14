<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Scale extends Form_Builder_Field_Type {

    protected $type = 'scale';
    protected $array_allowed = false;

    protected function field_settings_for_type() {
        return array(
            'default' => false,
        );
    }

    public function sanitize_value(&$value ) {
        return Form_Builder_Helper::sanitize_value( 'intval', $value );
    }

    protected function input_html() {
        $field = $this->get_field();
        $min = isset( $field['lowest_scale_point'] ) ? $field['lowest_scale_point'] : 1;
        $max = isset( $field['highest_scale_point'] ) ? $field['highest_scale_point'] : 10;
        $field['options'] = range( $min, $max );
        ?>

        <div class="fb-scale-container">
            <div class="fb-scale-header">
                <div id="fb-scale-text-lowest-<?php echo esc_attr( $field['id'] ) ?>" class="fb-scale-text fb-scale-lowest">
                    <?php echo esc_html( $field['lowest_scale_text'] ); ?>
                </div>
                <div id="fb-scale-text-highest-<?php echo esc_attr( $field['id'] ) ?>" class="fb-scale-text fb-scale-highest">
                    <?php echo esc_html( $field['highest_scale_text'] ); ?>
                </div>
            </div>
            <div class="fb-scale-body">
            <?php
            foreach ( $field['options'] as $opt_key => $opt ) {
                ?>
                <div class="fb-scale-item">
                    <input type="radio" id="<?php echo esc_attr( $this->html_name() ) . '_' . esc_attr( $opt ); ?>" name="<?php echo esc_attr( $this->html_name() ); ?>" value="<?php echo esc_attr( $opt ); ?>" />
                    <label for="<?php echo esc_attr( $this->html_name() ) . '_' . esc_attr( $opt ); ?>" class="fb-scale">
                        <?php echo esc_html( $opt ); ?>
                    </label>
                </div>
                <?php
            }
            ?>
            </div>
        </div>
        <?php
    }

}
