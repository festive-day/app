<?php

class cfgroup_number extends cfgroup_field
{

    function __construct() {
        $this->name = 'number';
        $this->label = __( 'Number', 'admin-site-enhancements' );
    }

    /**
     * Generate the field HTML
     * @param object $field 
     * @since 1.0.5
     */
    function html( $field ) {
        $step = isset( $field->options['step'] ) ? $field->options['step'] : '1';
    ?>
        <input type="number" step="<?php echo $step; ?>" name="<?php echo $field->input_name; ?>" class="<?php echo $field->input_class; ?>" value="<?php echo $field->value; ?>" />
    <?php
    }

    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Step', 'admin-site-enhancements' ); ?></label>
                <p class="description"><?php _e( 'Specify the granularity (increment or decrement) of the value.', 'admin-site-enhancements' ); ?></p>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][step]",
                        'options' => [
                            'choices' => [
                                '0.000001'      => __( '0.000001 (6 decimal places)', 'admin-site-enhancements' ),
                                '0.00001'       => __( '0.00001 (5 decimal places)', 'admin-site-enhancements' ),
                                '0.0001'        => __( '0.0001 (4 decimal places)', 'admin-site-enhancements' ),
                                '0.001'         => __( '0.001 (3 decimal places)', 'admin-site-enhancements' ),
                                '0.01'          => __( '0.01 (2 decimal places)', 'admin-site-enhancements' ),
                                '0.1'           => __( '0.1 (1 decimal place)', 'admin-site-enhancements' ),
                                '1'             => 1,
                                '10'            => 10,
                                '100'           => 100,
                                '1000'          => __( '1000 (1 thousand)', 'admin-site-enhancements' ),
                                '10000'         => __( '10000 (10 thousands)', 'admin-site-enhancements' ),
                                '100000'        => __( '100000 (100 thousands)', 'admin-site-enhancements' ),
                                '1000000'       => __( '1000000 (1 million)', 'admin-site-enhancements' ),
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'step', '1' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Default Value', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type'          => 'number',
                        'input_name'    => "cfgroup[fields][$key][options][default_value]",
                        'value'         => $this->get_option( $field, 'default_value' ),
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

    /**
     * Format the value for use with $cfgroup->get
     * @param mixed $value 
     * @param mixed $field The field object (optional)
     * @return mixed
     * @since 1.0.5
     */
    function format_value_for_api( $value, $field = null ) {
        return (float) $value;
    }

    function format_value_for_display( $value, $field = null ) {
        return $this->format_value_for_api( $value, $field );
    }
}
