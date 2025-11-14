<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Matrix_Of_Dropdowns extends Form_Builder_Field_Type {

    protected $type = 'matrix_of_dropdowns';

    protected function field_settings_for_type() {
        return array(
            'default' => false,
        );
    }

    protected function input_html() {
        $field = $this->get_field();
        $field_id = $field['id'];
        $field_key = $field['field_key'];
        $options = $field['options'] ? $field['options'] : array();
        $columns = $options['columns'];
        $rows = $options['rows'];
        $dropdowns = $options['dropdowns'];
        unset( $dropdowns['000'] );
        $default_option_value = '';
        $default_option_label = __( 'Choose one', 'admin-site-enhancements' );
        ?>

        <div class="fb-choice-container fb-matrix-of-dropdowns-container">
            <div id="fb-matrix-header-row-<?php echo esc_attr( $field_key ); ?>" class="fb-matrix-header-row">
                <div class="fb-matrix-row-title">
                </div>
                <div class="fb-columns">
                <?php
                foreach ( $columns as $column_option_key => $option ) {
                    ?>
                    <div id="<?php echo esc_attr( $this->html_id( '-columns-' . $column_option_key ) ); ?>" class="fb-column-item fb-column-item-<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $option['label'] ); ?></div>
                    <?php
                }
                ?>
                </div>
            </div>
            <?php
            foreach ( $rows as $row_option_key => $row_option ) {
                ?>
                <div id="fb-matrix-choice-row-<?php echo esc_attr( $field_key ); ?>-<?php echo esc_attr( $row_option_key ); ?>" class="fb-matrix-choice-row">
                    <div id="<?php echo esc_attr( $this->html_id( '-rows-' . $row_option_key ) ); ?>" class="fb-matrix-row-title"><?php echo esc_html( $row_option['label'] ); ?>
                    </div>
                    <div class="fb-row-choices">
                        <?php
                        foreach ( $columns as $column_option_key => $column_option ) {
                            $label = isset( $column_option['label'] ) ? $row_option['label'] . ' -- ' . $column_option['label'] : '';
                            $label_for_mobile = isset( $column_option['label'] ) ? $column_option['label'] : '';
                            ?>
                            <label class="fb-hidden fb-show-on-mobile" for="<?php echo esc_attr( $this->html_id( '-' . $row_option_key . '-' . $column_option_key ) ); ?>"><span class="always-hide"><?php echo esc_html( $label ); ?></span><span class="show-on-mobile"><?php echo esc_html( $label_for_mobile ); ?></span></label>
                            <select id="<?php echo esc_attr( $this->html_id() . '-' . $row_option_key . '-' . $column_option_key ); ?>" name="<?php echo esc_attr( $this->html_name() ) . '[dropdown_matrix_rows][' . $row_option_key . '][]'; ?>">
                                <option value="<?php echo esc_attr( $default_option_value ); ?>"><?php echo esc_html( $default_option_label ); ?></option>
                                <?php
                                foreach ( $dropdowns as $dropdown_option_key => $dropdown_option ) {
                                    $option_value = $row_option['label'] . '||' . $column_option['label'] . '||' . $dropdown_option['label'];
                                    ?>
                                    <option value="<?php echo esc_attr( $option_value ); ?>"><?php echo esc_html( $dropdown_option['label'] ); ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <?php                
            }
            ?>
        </div>
        <?php
    }

}
