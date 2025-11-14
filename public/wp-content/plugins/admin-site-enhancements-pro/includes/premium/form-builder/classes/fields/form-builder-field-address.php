<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Address extends Form_Builder_Field_Type {

    protected $type = 'address';

    protected function field_settings_for_type() {
        return array(
            'default' => false
        );
    }

    protected function sub_fields() {
        return array(
            'line1' => array(
                'type' => 'text',
                'label' => esc_html__( 'Line 1', 'admin-site-enhancements' )
            ),
            'line2' => array(
                'type' => 'text',
                'label' => esc_html__( 'Line 2', 'admin-site-enhancements' )
            ),
            'city' => array(
                'type' => 'text',
                'label' => esc_html__( 'City', 'admin-site-enhancements' )
            ),
            'state' => array(
                'type' => 'text',
                'label' => esc_html__( 'State/Province', 'admin-site-enhancements' )
            ),
            'zip' => array(
                // 'type' => 'number',
                'type' => 'text',
                'label' => esc_html__( 'Zip/Postal', 'admin-site-enhancements' )
            ),
            'country' => array(
                'type' => 'select',
                'label' => esc_html__( 'Country', 'admin-site-enhancements' )
            ) );
    }

    protected function show_after_default() {
        $sub_fields = $this->sub_fields();
        foreach ( $sub_fields as $name => $sub_field ) {
            $this->single_field( $name, $sub_field );
        }
    }

    protected function single_field( $name, $sub_field ) {
        $field = $this->get_field();
        $field_id = $field['id'];
        $field_key = $field['field_key'];
        $label = $sub_field['label'];
        $type = $sub_field['type'];
        $desc = $field['desc'][$name];
        $placeholder = isset( $field['placeholder'][$name] ) ? $field['placeholder'][$name] : '';
        $value = isset( $field['default_value'][$name] ) ? $field['default_value'][$name] : '';
        $disable = isset( $field['disable'][$name] ) ? $field['disable'][$name] : 'on';
        $country_grid_class = $name !== 'country' ? ' fb-grid-2' : '';
        ?>
        <div class="fb-form-row fb-sub-field-<?php echo esc_attr( $name ); ?>" data-sub-field-name="<?php echo esc_attr( $name ); ?>" data-field-id="<?php echo esc_attr( $field_id ); ?>">
            <div class="fb-sub-field-label">
                <?php echo esc_html( $label ); ?>
                <label class="fb-field-show-hide">
                    <input type="hidden" name="field_options[disable_<?php echo esc_attr( $field_id ); ?>][<?php echo esc_attr( $name ); ?>]" value="on">
                    <input type="checkbox" name="field_options[disable_<?php echo esc_attr( $field_id ); ?>][<?php echo esc_attr( $name ); ?>]" id="fb-disable-<?php echo esc_attr( $name ); ?>-<?php echo esc_attr( $field_id ); ?>" data-changeme="fb-subfield-disable-<?php echo esc_attr( $name ); ?>-<?php echo esc_attr( $field_id ); ?>" value="off" data-disablefield="fb-subfield-container-<?php echo esc_attr( $name ); ?>-<?php echo esc_attr( $field_id ); ?>" <?php checked( ( $disable == 'off' ), true ) ?>>
                    <label for="fb-disable-<?php echo esc_attr( $name ); ?>-<?php echo esc_attr( $field_id ); ?>"></label>
                </label>
            </div>

            <div class="fb-grid-container">
                <?php if ( $name !== 'country' ) { ?>
                    <div class="fb-form-row fb-grid-2">
                        <input type="<?php echo esc_attr( $type ); ?>" name="default_value_<?php echo esc_attr( $field_id ); ?>[<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-changeme="fb-field-<?php echo esc_attr( $field_key ); ?>-<?php echo esc_attr( $name ); ?>" data-changeatt="value">
                        <label class="fb-field-desc"><?php esc_html_e( 'Default Value', 'admin-site-enhancements' ); ?></label>
                    </div>
                    <div class="fb-form-row fb-grid-2">
                        <input type="text" name="field_options[placeholder_<?php echo esc_attr( $field_id ); ?>][<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_attr( $placeholder ); ?>" data-changeme="fb-field-<?php echo esc_attr( $field_key ); ?>-<?php echo esc_attr( $name ); ?>" data-changeatt="placeholder">
                        <label class="fb-field-desc"><?php esc_html_e( 'Placeholder', 'admin-site-enhancements' ); ?></label>
                    </div>
                <?php } ?>
                <div class="fb-form-row<?php echo esc_attr( $country_grid_class ); ?>">
                    <input type="text" name="field_options[desc_<?php echo esc_attr( $field_id ); ?>][<?php echo esc_attr( $name ); ?>]" value="<?php echo esc_html( $desc ); ?>" data-changeme="fb-subfield-desc-<?php echo esc_attr( $name ); ?>-<?php echo esc_attr( $field_id ); ?>">
                    <label class="fb-field-desc"><?php esc_html_e( 'Description', 'admin-site-enhancements' ); ?></label>
                </div>
            </div>
        </div>
        <?php
    }

    protected function extra_field_default_opts() {
        $sub_fields = $this->sub_fields();
        $field_options = array();
        foreach ( $sub_fields as $name => $fields ) {
            $field_options['desc'][$name] = $fields['label'];
        }
        return $field_options;
    }

    public function validate( $args ) {
        $errors = isset( $args['errors'] ) ? $args['errors'] : array();
        $field = $this->get_field();

        if ( $field->required == '1' ) {
            $sub_fields = $this->sub_fields();
            unset( $sub_fields['line2'] );

            foreach ( $sub_fields as $name => $sub_field ) {
                if ( isset( $args['value'][$name] ) && empty( $args['value'][$name] ) ) {
                    $errors['field' . $args['id']] = Form_Builder_Fields::get_error_msg( $this->field, 'blank' );
                }
            }
        }
        return $errors;
    }

    protected function input_html() {
        $field = $this->get_field();
        // vi( $field, '', 'address' );
        $field_id = $field['id'];
        $field_key = $field['field_key'];
        ?>
        <div class="fb-grouped-field" id="fb-grouped-field-<?php echo esc_attr( $field_id ); ?>">
            <?php
            $sub_fields = $this->sub_fields();
            foreach ( $sub_fields as $name => $sub_field ) {
                $value = isset( $field['default_value'][$name] ) ? $field['default_value'][$name] : '';
                $placeholder = isset( $field['placeholder'][$name] ) ? $field['placeholder'][$name] : '';
                $disable = isset( $field['disable'][$name] ) ? $field['disable'][$name] : 'on';
                $class = $disable == 'off' ? ' fb-hidden' : ' ';

                $label = isset( $field['desc'][$name] ) ? $field['desc'][$name] : '';
                $type = $sub_field['type'];

                if ( is_admin() || $disable == 'on' ) {
                    ?>
                    <div id="fb-subfield-container-<?php echo esc_attr( $name ) . '-' . esc_attr( $field_id ); ?>" class="fb-subfield-element fb-subfield-element-<?php echo esc_attr( $name ); ?> fb-grid-6 <?php echo esc_attr( $class ); ?>" data-sub-field-name="<?php echo esc_attr( $name ); ?>">
                        <?php
                        if ( $type !== 'select' ) {
                            ?>
                            <input type="<?php echo esc_attr( $type ); ?>" id="fb-field-<?php echo esc_attr( $field_key ); ?>-<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $this->html_name() ) . '[' . esc_attr( $name ) . ']'; ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
                            <?php
                        } else {
                            $this->get_country_select( Form_Builder_Helper::get_countries() );
                        }
                        ?>
                        <div class="fb-field-desc <?php echo esc_attr( $name ); ?>" id="fb-subfield-desc-<?php echo esc_attr( $name ); ?>-<?php echo esc_attr( $field_id ); ?>">
                            <?php echo esc_attr( $label ); ?>
                        </div>
                        <label class="fb-hidden" for="fb-field-<?php echo esc_attr( $field_key ) . '-' . esc_attr( $name ); ?>"><?php echo esc_attr( $sub_fields[$name]['label'] ); ?></label>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <?php
    }

    protected function get_country_select( $args ) {
        $field = $this->get_field();
        $field_key = $field['field_key'];
        ?>
        <select id="<?php echo 'fb-field-' . esc_attr( $field_key ) . '-country'; ?>" name="<?php echo esc_attr( $this->html_name() ) . '[country]'; ?>">
            <option value=""><?php echo esc_html__( 'Choose one', 'admin-site-enhancements' ); ?></option>
            <?php
            foreach ( $args as $arg ) {
                ?>
                <option value="<?php echo esc_html( $arg ); ?>"><?php echo esc_html( $arg ); ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }

}
