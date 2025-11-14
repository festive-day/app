<?php

class cfgroup_true_false extends cfgroup_field
{

    function __construct() {
        $this->name = 'true_false';
        $this->label = __('True / False', 'cfgroup');
    }

    function html( $field ) {
        $field->value = ( 0 < (int) $field->value ) ? 1 : 0;
    ?>
		<label>
			<input type="checkbox" <?php echo $field->value ? ' checked' : ''; ?>>
			<span><?php echo $field->options['message']; ?></span>
			<input type="hidden" name="<?php echo esc_attr( $field->input_name ); ?>" class="<?php echo esc_attr( $field->input_class ); ?>" value="<?php echo esc_attr( $field->value ); ?>" />
		</label>
    <?php
    }

    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Message', 'admin-site-enhancements' ); ?></label>
                <p class="description"><?php _e( 'The text beside the checkbox', 'admin-site-enhancements' ); ?></p>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'text',
                        'input_name' => "cfgroup[fields][$key][options][message]",
                        'value' => $this->get_option( $field, 'message' ),
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
                                'default'           => __( 'Integer: 1 or 0', 'admin-site-enhancements' ) . ' (' . __( 'default', 'admin-site-enhancements' ) . ')',
                                'true_false'        => __( 'String: True or False', 'admin-site-enhancements' ),
                                'yes_no'            => __( 'String: Yes or No', 'admin-site-enhancements' ),
                                'check_cross'       => __( 'Icon: check or cross', 'admin-site-enhancements' ),
                                'toggle_on_off'     => __( 'Icon: toggled on or off', 'admin-site-enhancements' ),
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'format', 'one_zero' ),
                    ] );
                ?>
            </td>
        </tr>
    <?php
    }

    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_true_false:not(.ready)').init_true_false();
                });
                $('.cfgroup_true_false').init_true_false();
            });

            $.fn.init_true_false = function() {
                this.each(function() {
                    var $this = $(this);
                    $this.addClass('ready');

                    // handle click
                    $this.find('input[type="checkbox"]').on('change click', function() {
                        var val = $(this).prop('checked') ? 1 : 0;
                        $(this).siblings('.true_false').val(val);
                    });
                });
            }
        })(jQuery);
        </script>
    <?php
    }

    function format_value_for_api( $value, $field = null ) {
        $output_format = isset( $field->options['format'] ) ? $field->options['format'] : 'default';
        
        switch ( $output_format ) {
            
            case 'default':
                return ( 0 < (int) $value ) ? 1 : 0;
                break;

            case 'true_false':
                return ( 1 == $value ) ? 'True' : 'False';
                break;

            case 'yes_no':
                return ( 1 == $value ) ? 'Yes' : 'No';
                break;

            case 'check_cross':
                // True: https://icon-sets.iconify.design/fa-solid/check/
                // False: https://icon-sets.iconify.design/emojione-monotone/cross-mark/
                return ( 1 == $value ) ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 512 512"><path fill="currentColor" d="m173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69L432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 64 64"><path fill="currentColor" d="M62 10.571L53.429 2L32 23.429L10.571 2L2 10.571L23.429 32L2 53.429L10.571 62L32 40.571L53.429 62L62 53.429L40.571 32z"/></svg>';
                break;

            case 'toggle_on_off':
                // True: https://icon-sets.iconify.design/la/toggle-on/
                // False: https://icon-sets.iconify.design/la/toggle-off/
                return ( 1 == $value ) ? '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-4.96 0-9 4.035-9 9s4.04 9 9 9h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm14 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7s3.121-7 7-7"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M9 7c-.621 0-1.227.066-1.813.188a9.238 9.238 0 0 0-.875.218A9.073 9.073 0 0 0 .72 12.5c-.114.27-.227.531-.313.813A8.848 8.848 0 0 0 0 16c0 .93.145 1.813.406 2.656c.004.008-.004.024 0 .032A9.073 9.073 0 0 0 5.5 24.28c.27.114.531.227.813.313A8.83 8.83 0 0 0 9 24.999h14c4.957 0 9-4.043 9-9s-4.043-9-9-9zm0 2c3.879 0 7 3.121 7 7s-3.121 7-7 7s-7-3.121-7-7c0-.242.008-.484.031-.719A6.985 6.985 0 0 1 9 9m5.625 0H23c3.879 0 7 3.121 7 7s-3.121 7-7 7h-8.375C16.675 21.348 18 18.828 18 16c0-2.828-1.324-5.348-3.375-7"/></svg>';
                break;            
        }
    }

    function format_value_for_display( $value, $field = null ) {
        return ( 1 == $value ) ? 'Yes' : 'No';
    }    
}