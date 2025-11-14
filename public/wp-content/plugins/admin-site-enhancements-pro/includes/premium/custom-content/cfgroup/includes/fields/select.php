<?php

class cfgroup_select extends cfgroup_field
{
    public $select2_inserted;

    function __construct() {
        $this->name = 'select';
        $this->label = __( 'Select', 'admin-site-enhancements' );
        $this->select2_inserted = false;
    }

    function html( $field ) {
        $multiple = '';
        $field->input_class = empty( $field->input_class ) ? '' : $field->input_class;

        // Multi-select
        if ( isset( $field->options['multiple'] ) && '1' == $field->options['multiple'] ) {
            $multiple = ' multiple';
            $field->input_class .= ' multiple';
        }

        // Select2
        if ( isset( $field->options['select2'] ) && '1' == $field->options['select2'] ) {
            $field->input_class .= ' select2';

            add_action( 'admin_footer', [ $this, 'select2_code' ] );
        }

        // Select boxes should return arrays (unless "force_single" is true)
        if ( '[]' != substr( $field->input_name, -2 ) && empty( $field->options['force_single'] ) ) {
            $field->input_name .= '[]';
        }
    ?>
        <select name="<?php echo esc_attr( $field->input_name ); ?>" class="<?php echo trim( esc_attr( $field->input_class ) ); ?>"<?php echo $multiple; ?>>
        <?php foreach ( $field->options['choices'] as $val => $label ) : ?>
            <?php $val = ( '{empty}' == $val ) ? '' : $val; ?>
            <?php $selected = in_array( $val, (array) $field->value ) ? ' selected' : ''; ?>
            <option value="<?php echo esc_attr( $val ); ?>"<?php echo $selected; ?>><?php echo esc_attr( $label ); ?></option>
        <?php endforeach; ?>
        </select>
    <?php
    }

    function select2_code() {

        // Exit early if the select2 code has already been inserted
        if ( $this->select2_inserted ) {
            return;
        }

        echo '<script src="' . CFG_URL . '/assets/js/select2/select2.min.js"></script>';
        echo '<link rel="stylesheet" type="text/css" href="' . CFG_URL . '/assets/js/select2/select2.css" />';

        // Don't insert select2 code twice
        $this->select2_inserted = true;
    }

    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_select:not(.ready)').init_select();
                });
                $('.cfgroup_select').init_select();
            });

            $.fn.init_select = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    if ( $this.find( 'select' ).hasClass( 'select2' ) ) {
                        $this.find( 'select' ).select2();
                    }
                });
            }
        })(jQuery);
        </script>
    <?php
    }


    function options_html( $key, $field ) {

        // Convert choices to textarea-friendly format
        if ( isset( $field->options['choices'] ) && is_array( $field->options['choices'] ) ) {
            foreach ( $field->options['choices'] as $choice_key => $choice_val ) {
                $field->options['choices'][ $choice_key ] = "$choice_key : $choice_val";
            }

            $field->options['choices'] = implode( "\n", $field->options['choices'] );
        }
        else {
            $field->options['choices'] = '';
        }
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Choices', 'admin-site-enhancements' ); ?></label>
                <p class="description"><?php /* translators: Do not translate {empty}. Keep as is. */ _e( 'One <code>value : label</code> per line, or just <code>label</code>. Use <code>{empty}</code> for an empty value.', 'admin-site-enhancements' ); ?></p>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'textarea',
                        'input_name' => "cfgroup[fields][$key][options][choices]",
                        'value' => $this->get_option( $field, 'choices' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label select-label">
                <label><?php _e( 'Multi-select?', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'true_false',
                        'input_name' => "cfgroup[fields][$key][options][multiple]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option( $field, 'multiple' ),
                        'options' => [ 'message' => __( 'This is a multi-select field', 'admin-site-enhancements' ) ],
                     ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Default Value(s)', 'admin-site-enhancements' ); ?></label>
                <p class="description"><?php _e( 'For multiple values, separate with <code> |</code>, e.g. <code>value1 | value2</code>', 'admin-site-enhancements' ); ?></p>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type'          => 'text',
                        'input_name'    => "cfgroup[fields][$key][options][default_value]",
                        'value'         => $this->get_option( $field, 'default_value' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label select-label">
                <label><?php _e('Select2', 'cfgroup'); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'true_false',
                        'input_name' => "cfgroup[fields][$key][options][select2]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option($field, 'select2'),
                        'options' => [ 'message' => __('Render this field with Select2', 'admin-site-enhancements' ) ],
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Output Format', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][format]",
                        'options' => [
                            'choices' => [
                                'labels'    => __( 'Labels', 'admin-site-enhancements' ),
                                'values'    => __( 'Values', 'admin-site-enhancements' ),
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'format', 'labels' ),
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

    function format_value_for_api( $value, $field = null ) {
        $value_array = [];
        $choices = $field->options['choices'];
        $output_format = isset( $field->options['format'] ) ? $field->options['format'] : 'labels';

        if ( is_array( $value ) ) {
            foreach ( $value as $val ) {
                $value_array[ $val ] = isset( $choices[ $val ] ) ? $choices[ $val ] : $val;
            }

            switch ( $output_format ) {
                case 'labels':
                    $value_array = array_values( $value_array );
                    break;
                    
                case 'values':
                    $value_array = array_keys( $value_array );
                    break;
            }

            if ( count( $value_array ) > 1 ) {
                $value_array = implode( ', ', $value_array );
            } else {
                $value_array = $value_array[0];
            }
        }

        return $value_array;
    }

    function format_value_for_display( $value, $field = null ) {
        return $this->format_value_for_api( $value, $field );
    }

    function prepare_value( $value, $field = null ) {
        return $value;
    }

    function pre_save_field( $field ) {
        $new_choices = [];
        $choices = trim( $field['options']['choices'] );

        if ( ! empty( $choices ) ) {
            $choices = str_replace( "\r\n", "\n", $choices );
            $choices = str_replace( "\r", "\n", $choices );
            $choices = ( false !== strpos( $choices, "\n" ) ) ? explode( "\n", $choices ) : (array) $choices;

            foreach ( $choices as $choice ) {
                $choice = trim( $choice );
                if ( false !== ( $pos = strpos( $choice, ' : ' ) ) ) {
                    $array_key = substr( $choice, 0, $pos );
                    $array_value = substr( $choice, $pos + 3 );
                    $new_choices[ $array_key ] = $array_value;
                }
                else {
                    $new_choices[ $choice ] = $choice;
                }
            }
        }

        $field['options']['choices'] = $new_choices;

        return $field;
    }

    function pre_save( $value, $field = null ) {
        if ( ! empty( $value ) ) {
            // The raw input from a post edit screen's custom field meta box will save a comma-separated string
            if ( is_string( $value ) ) {
                if ( false !== strpos( $value, ',' ) ) {
                    return explode( ',', $value ); // Turns 'first,second' into array( 0 => 'first', 1 => 'second' )
                } else {
                    return (array) $value; // Turns 'first' into array( 0 => 'first' );
                }
            } else {
                // $value is not a string, i.e. an array
                return $value;                
            }
        }

        return array(); // Return empty array
    }
}
