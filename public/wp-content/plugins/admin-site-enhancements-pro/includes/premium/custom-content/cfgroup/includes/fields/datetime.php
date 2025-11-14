<?php

class cfgroup_datetime extends cfgroup_field
{

    function __construct() {
        $this->name = 'datetime';
        $this->label = __( 'Date Time', 'admin-site-enhancements' );
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
                                'Y-m-d H:i'     => 'Y-m-d H:i -- ' . wp_date( 'Y-m-d', time() ) . ' 19:45',
                                'Y-m-d G:i K'   => 'Y-m-d G:i K -- ' . wp_date( 'Y-m-d', time() ) . ' 07:45 PM',
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'input_format', 'Y-m-d H:i' ),
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
                            'force_single'  => true,
                        ],
                        'value' => $this->get_option( $field, 'step', '5' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Date Output Format', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][date_output_format]",
                        'options' => [
                            'choices' => [
                                'F j, Y'        => 'F j, Y -- ' . wp_date( 'F j, Y', time() ),
                                'F jS, Y'       => 'F jS, Y -- ' . wp_date( 'F jS, Y', time() ),
                                'l, F jS, Y'    => 'l, F jS, Y -- ' . wp_date( 'l, F jS, Y', time() ),
                                'M j, Y'        => 'M j, Y -- ' . wp_date( 'M j, Y', time() ),
                                'D, M j, Y'     => 'D, M j, Y -- ' . wp_date( 'D, M j, Y', time() ),
                                'j F, Y'        => 'j F, Y -- ' . wp_date( 'j F, Y', time() ),
                                'l, j F, Y'     => 'l, j F, Y -- ' . wp_date( 'l, j F, Y', time() ),
                                'j M, Y'        => 'j M, Y -- ' . wp_date( 'j M, Y', time() ),
                                'D, j M, Y'     => 'D, j M, Y -- ' . wp_date( 'D, j M, Y', time() ),
                                'F j'           => 'F j -- ' . wp_date( 'F j', time() ),
                                'F jS'          => 'F jS -- ' . wp_date( 'F jS', time() ),
                                'l, F jS'       => 'l, F jS -- ' . wp_date( 'l, F jS', time() ),
                                'M j'           => 'M j -- ' . wp_date( 'M j', time() ),
                                'D, M j'        => 'D, M j -- ' . wp_date( 'D, M j', time() ),
                                'j F'           => 'j F -- ' . wp_date( 'j F', time() ),
                                'l, j F'        => 'l, j F -- ' . wp_date( 'l, j F', time() ),
                                'j M'           => 'j M -- ' . wp_date( 'j M', time() ),
                                'D, j M'        => 'D, j M -- ' . wp_date( 'D, j M', time() ),
                                'Y-m-d'         => 'Y-m-d -- ' . wp_date( 'Y-m-d', time() ),
                                'Y/m/d'         => 'Y/m/d -- ' . wp_date( 'Y/m/d', time() ),
                                'm-d-Y'         => 'm-d-Y -- ' . wp_date( 'm-d-Y', time() ),
                                'm/d/Y'         => 'm/d/Y -- ' . wp_date( 'm/d/Y', time() ),
                                'n/j/y'         => 'n/j/y -- ' . wp_date( 'n/j/y', time() ),
                                'd-m-Y'         => 'd-m-Y -- ' . wp_date( 'd-m-Y', time() ),
                                'd/m/Y'         => 'd/m/Y -- ' . wp_date( 'd/m/Y', time() ),
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'date_output_format', 'F j, Y' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Time Output Format', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][time_output_format]",
                        'options' => [
                            'choices' => [
                                'G:i'       => 'G:i -- 9:30, 19:45',
                                'H:i'       => 'H:i -- 09:30, 19:45',
                                'g:i a'     => 'g:i a -- 9:30 am, 7:45 pm',
                                'g:i A'     => 'g:i A -- 9:30 AM, 7:45 PM',
                                'h:i a'     => 'h:i a -- 09:30 am, 07:45 pm',
                                'h:i A'     => 'h:i A -- 09:30 AM, 07:45 PM',
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'time_output_format', 'H:i' ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Date Time Output Separator', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][date_time_output_separator]",
                        'options' => [
                            'choices' => [
                                'space'     => __( 'Space', 'admin-site-enhancements' )  . ' -- ' . wp_date( 'F j, Y H:i', time() ),
                                'comma'     => __( 'Comma', 'admin-site-enhancements' ) . ' -- ' . wp_date( 'F j, Y, H:i', time() ),
                                'hyphen'    => __( 'Hyphen', 'admin-site-enhancements' ) . ' -- ' . wp_date( 'F j, Y - H:i', time() ),
                                'pipe'      => __( 'Pipe', 'admin-site-enhancements' ) . ' -- ' . wp_date( 'F j, Y | H:i', time() ),
                                'slash'     => __( 'Slash', 'admin-site-enhancements' ) . ' -- ' . wp_date( 'F j, Y / H:i', time() ),
                                'at_symbol' => '@' . ' -- ' . wp_date( 'F j, Y @ H:i', time() ),
                                'at_text'   => 'at' . ' -- ' . wp_date( 'F j, Y \a\t H:i', time() ),
                            ],
                            'force_single'  => true,
                        ],
                        'value' => $this->get_option( $field, 'date_time_output_separator', 'hyphen' ),
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
        $minute_increment = intval( $this->get_option( $field, 'step', 5 ) );
        $date_format = $this->get_option( $field, 'input_format', 'Y-m-d H:i' );
        switch ( $date_format ) {
            case 'Y-m-d H:i':
            $time_24hr = "'time_24hr': true,";
                break;

            case 'Y-m-d G:i K':
            $time_24hr = "'time_24hr': false,";
                break;
        }
    ?>
        <script>
        (function($) {
            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_datetime:not(.ready)').init_datetime();
                });
                $('.cfgroup_datetime').init_datetime();
            });

            $.fn.init_datetime = function() {
                this.each(function() {
                    $(this).find('input.datetime').flatpickr({
                        // Ref: https://flatpickr.js.org/options/
                        // 'noCalendar': true,
                        'enableTime': true,
                        // 'enableSeconds': true,
                        <?php echo stripslashes( esc_js( $time_24hr ) ); ?> // true or false
                        'defaultHour': 12, // 0 to 23
                        'minuteIncrement': <?php echo esc_js( $minute_increment ); ?>,
                        // 'showMonths': 1, // How many months the calendar should show
                        // 'dateFormat': 'Y-m-d H:i', // 'Y-m-d H:i:S' (24 hours, leading zero) | 'Y-m-d G:i K' (12 hours, leading zero, AM/PM)
                        'dateFormat': '<?php echo esc_js( $date_format ); ?>',
                        // 'dateFormat': 'H:i', // 24 hour
                        // 'dateFormat': 'h:i K', // 12 hour with AM/PM, e.g. 7:30 AM
                        // 'minTime': '06:00',
                        // 'maxTime': '22:00',
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
        $datetime_output_format = get_cf_datetime_output_format( $field->options );
        $timezone_object = new DateTimeZone( 'UTC' );

        return wp_date( $datetime_output_format, strtotime( $value ), $timezone_object );
    }

    function format_value_for_display( $value, $field = null ) {
        return $this->format_value_for_api( $value, $field );
    }
}
