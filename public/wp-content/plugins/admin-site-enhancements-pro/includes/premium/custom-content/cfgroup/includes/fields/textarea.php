<?php

class cfgroup_textarea extends cfgroup_field
{

    function __construct() {
        $this->name = 'textarea';
        $this->label = __( 'Text Area', 'admin-site-enhancements' );
    }


    function html( $field ) {
    ?>
        <textarea name="<?php echo esc_attr( $field->input_name ); ?>" class="<?php echo esc_attr( $field->input_class ); ?>" rows="4"><?php echo $field->value; ?></textarea>
    <?php
    }


    function options_html($key, $field) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Default Value', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'textarea',
                        'input_name' => "cfgroup[fields][$key][options][default_value]",
                        'value' => $this->get_option( $field, 'default_value' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Formatting', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][formatting]",
                        'options' => [
                            'choices' => [
                                'none' => __( 'None', 'admin-site-enhancements' ),
                                'auto_br' => __( 'Convert newlines to <br />', 'admin-site-enhancements' )
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'formatting', 'auto_br' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label validation-label">
                <label><?php _e( 'Validation', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'true_false',
                        'input_name' => "cfgroup[fields][$key][options][required]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option( $field, 'required' ),
                        'options' => [ 'message' => __( 'This is a required field', 'admin-site-enhancements' ) ],
                    ] );
                ?>
            </td>
        </tr>
    <?php
    }


    function format_value_for_input( $value, $field = null ) {
        return htmlspecialchars( $value, ENT_QUOTES );
    }


    function format_value_for_api( $value, $field = null ) {
        $formatting = $this->get_option( $field, 'formatting', 'none' );
        return ( 'none' == $formatting ) ? $value : nl2br( $value );
    }

    function format_value_for_display( $value, $field = null ) {
        return $this->format_value_for_api( $value, $field );
    }
}
