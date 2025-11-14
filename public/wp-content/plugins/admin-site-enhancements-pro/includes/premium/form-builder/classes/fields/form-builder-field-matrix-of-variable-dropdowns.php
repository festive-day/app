<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Matrix_Of_Variable_Dropdowns extends Form_Builder_Field_Type {

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
        $rows = $options['rows'];
        $columns = $options['columns'];
        $dropdowns_options = array();

        // This reorganize the options for the variable dropdowns
        // Also felxible to accommodate 2 to 5 variable dropdowns fields
        foreach ( $options as $options_key => $options_value ) {
            // For $options['first_dropdown'], $options['second_dropdown'], etc.
            if ( false !== strpos( $options_key, '_dropdown' ) ) {
                $dropdown_options = array();
                foreach ( $options_value as $key => $value ) {
                    $dropdown_options[] = $value['label'];
                }
                
                $dropdowns_options[] = $dropdown_options;
            }
        }

        unset( $rows['000'] );
        unset( $columns['000'] );
        $default_option_value = '';
        $default_option_label = __( 'Choose one', 'admin-site-enhancements' );
        ?>

        <div class="fb-choice-container fb-matrix-of-three-variable-dropdowns-container">
            <div id="fb-matrix-header-row-<?php echo esc_attr( $field_key ); ?>" class="fb-matrix-header-row">
                <div class="fb-matrix-row-title">
                </div>
                <div class="fb-columns">
                <?php
                foreach ( $columns as $column_option_key => $column_option ) {
                    ?>
                    <div id="<?php echo esc_attr( $this->html_id( '-columns-' . $column_option_key ) ); ?>" class="fb-column-item fb-column-item-<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $column_option['label'] ); ?></div>
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
                            foreach ( $dropdowns_options as $dropdown_key => $dropdown_option ) {
                                if ( $column_option_key == $dropdown_key ) {
                                    $label = isset( $column_option['label'] ) ? $row_option['label'] . ' -- ' . $column_option['label'] : '';
                                    $label_for_mobile = isset( $column_option['label'] ) ? $column_option['label'] : '';
                                    ?>
                                    <label class="fb-hidden fb-show-on-mobile" for="<?php echo esc_attr( $this->html_id( '-' . $row_option_key . '-' . $column_option_key ) ); ?>"><span class="always-hide"><?php echo esc_html( $label ); ?></span><span class="show-on-mobile"><?php echo esc_html( $label_for_mobile ); ?></span></label>
                                    <select id="<?php echo esc_attr( $this->html_id() . '-' . $row_option_key . '-' . $column_option_key ); ?>" name="<?php echo esc_attr( $this->html_name() ) . '[dropdowns_matrix_rows][' . $row_option_key . '][]'; ?>">
                                    <?php
                                    foreach ( $dropdowns_options[$dropdown_key] as $key => $option ) {
                                        if ( '000' != $key ) {
                                            $option_value = $row_option['label'] . '||' . $column_option['label'] . '||' . $option;
                                            ?>
                                            <option value="<?php echo esc_attr( $option_value ); ?>"><?php echo esc_html( $option ); ?></option>
                                        <?php
                                        } else {
                                            ?>
                                            <option value="<?php echo esc_attr( $default_option_value ); ?>"><?php echo esc_html( $default_option_label ); ?></option>
                                        <?php                                            
                                        }
                                    }
                                    ?>
                                    </select>
                                    <?php
                                }
                            }
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
