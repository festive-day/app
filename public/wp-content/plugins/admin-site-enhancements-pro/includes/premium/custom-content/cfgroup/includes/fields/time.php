<?php

class cfgroup_time extends cfgroup_field
{

    function __construct() {
        $this->name = 'time';
        $this->label = __( 'Time', 'admin-site-enhancements' );
    }

    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Input Format', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][input_format]",
                        'options' => [
                            'choices' => [
                                'H:i'     => 'H:i -- 19:45',
                                'G:i K'   => 'G:i K -- 07:45 PM',
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'input_format', 'H:i' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Minute Increment', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][step]",
                        'options' => [
                            'choices' => [
                                '1'  => __( '1 minute', 'admin-site-enhancements' ),
                                '5'  => __( '5 minutes', 'admin-site-enhancements' ),
                                '10'  => __( '10 minutes', 'admin-site-enhancements' ),
                                '15'  => __( '15 minutes', 'admin-site-enhancements' ),
                                '20'  => __( '20 minutes', 'admin-site-enhancements' ),
                                '30'  => __( '30 minutes', 'admin-site-enhancements' ),
                                '60'  => __( '60 minutes', 'admin-site-enhancements' ),
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'step', '5' ),
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
                        'input_name' => "cfgroup[fields][$key][options][frontend_display_format]",
                        'options' => [
                            'choices' => [
                                'G:i'  => 'G:i - 9:30, 19:45',
                                'H:i'  => 'H:i - 09:30, 19:45',
                                'g:i a'  => 'g:i a - 9:30 am, 7:45 pm',
                                'g:i A'  => 'g:i A - 9:30 AM, 7:45 PM',
                                'h:i a'  => 'h:i a - 09:30 am, 07:45 pm',
                                'h:i A'  => 'h:i A - 09:30 AM, 07:45 PM',
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'frontend_display_format', 'G:i' ),
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

    function input_head( $field = null ) {
        $this->load_assets();

        $date_format = $this->get_option( $field, 'input_format', 'H:i' );
        switch ( $date_format ) {
            case 'H:i':
            $time_24hr = "'time_24hr': true,";
            $default_hour = 12;
                break;

            case 'G:i K':
            $time_24hr = "'time_24hr': false,";
            $default_hour = 6;
                break;
        }
        $step = intval( $this->get_option( $field, 'step', 5 ) );
    ?>
        <script>
        (function($) {
            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_time:not(.ready)').init_time();
                });
                $('.cfgroup_time').init_time();
            });

            $.fn.init_time = function() {
                this.each(function() {
                    $(this).find('input.time').flatpickr({
                        // Ref: https://flatpickr.js.org/options/
                        'noCalendar': true,
                        'enableTime': true,
                        // 'enableSeconds': true,
                        <?php echo stripslashes( esc_js( $time_24hr ) ); ?>
                        'defaultHour': <?php echo esc_js( $default_hour ); ?>,
                        'minuteIncrement': <?php echo esc_js( $step ); ?>,
                        'dateFormat': '<?php echo esc_js( $date_format ); ?>',
                        // 'defaultDate': '13:45',
                    });
                    $(this).addClass('ready');
                });
            };
        })(jQuery);
        </script>
    <?php
    }

    function load_assets() {
        // wp_register_script( 'asenha-jquery-ui-timepicker', CFG_URL . '/includes/fields/time/jquery.timepicker.js', [ 'jquery', 'jquery-ui-core' ] );
        // wp_enqueue_script( 'asenha-jquery-ui-timepicker' );        

        // https://www.jsdelivr.com/package/npm/flatpickr?tab=files&path=dist
        // Source: https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css v4.6.13
        // wp_register_style( 'asenha-flatpickr-default', CFG_URL . '/assets/css/flatpickr/flatpickr.min.css', array(), ASENHA_VERSION );
        // wp_enqueue_style( 'asenha-flatpickr-default' );
        wp_register_style( 'asenha-flatpickr-dark', CFG_URL . '/assets/css/flatpickr/dark.css', array(), ASENHA_VERSION );
        wp_enqueue_style( 'asenha-flatpickr-dark' );
        // Source: https://cdn.jsdelivr.net/npm/flatpickr v4.6.13
        wp_register_script( 'asenha-flatpickr', CFG_URL . '/assets/js/flatpickr/flatpickr.min.js', [ 'jquery', 'jquery-ui-core' ], ASENHA_VERSION );
        wp_enqueue_script( 'asenha-flatpickr' );
    }

    function format_value_for_api( $value, $field = null ) {
        $output_format = isset( $field->options['frontend_display_format'] ) ? $field->options['frontend_display_format'] : 'G:i';
        return get_cf_time( $value, $output_format );
    }

    function format_value_for_display( $value, $field = null ) {
        return $this->format_value_for_api( $value, $field );
    }
}
