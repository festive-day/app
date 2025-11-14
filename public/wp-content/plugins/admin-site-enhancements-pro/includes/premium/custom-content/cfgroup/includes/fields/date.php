<?php

class cfgroup_date extends cfgroup_field
{

    function __construct() {
        $this->name = 'date';
        $this->label = __( 'Date', 'admin-site-enhancements' );
    }

    function options_html( $key, $field ) {
    ?>
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
                                'F j, Y'        => 'F j, Y - ' . wp_date( 'F j, Y', time() ),
                                'F jS, Y'       => 'F jS, Y - ' . wp_date( 'F jS, Y', time() ),
                                'l, F jS, Y'    => 'l, F jS, Y - ' . wp_date( 'l, F jS, Y', time() ),
                                'M j, Y'        => 'M j, Y - ' . wp_date( 'M j, Y', time() ),
                                'D, M j, Y'     => 'D, M j, Y - ' . wp_date( 'D, M j, Y', time() ),
                                'j F, Y'        => 'j F, Y - ' . wp_date( 'j F, Y', time() ),
                                'l, j F, Y'     => 'l, j F, Y - ' . wp_date( 'l, j F, Y', time() ),
                                'j M, Y'        => 'j M, Y - ' . wp_date( 'j M, Y', time() ),
                                'D, j M, Y'     => 'D, j M, Y - ' . wp_date( 'D, j M, Y', time() ),
                                'F j'           => 'F j - ' . wp_date( 'F j', time() ),
                                'F jS'          => 'F jS - ' . wp_date( 'F jS', time() ),
                                'l, F jS'       => 'l, F jS - ' . wp_date( 'l, F jS', time() ),
                                'M j'           => 'M j - ' . wp_date( 'M j', time() ),
                                'D, M j'        => 'D, M j - ' . wp_date( 'D, M j', time() ),
                                'j F'           => 'j F - ' . wp_date( 'j F', time() ),
                                'l, j F'     => 'l, j F - ' . wp_date( 'l, j F', time() ),
                                'j M'        => 'j M - ' . wp_date( 'j M', time() ),
                                'D, j M'     => 'D, j M - ' . wp_date( 'D, j M', time() ),
                                'Y-m-d'         => 'Y-m-d - ' . wp_date( 'Y-m-d', time() ),
                                'Y/m/d'         => 'Y/m/d - ' . wp_date( 'Y/m/d', time() ),
                                'm-d-Y'         => 'm-d-Y - ' . wp_date( 'm-d-Y', time() ),
                                'm/d/Y'         => 'm/d/Y - ' . wp_date( 'm/d/Y', time() ),
                                'n/j/y'         => 'n/j/y - ' . wp_date( 'n/j/y', time() ),
                                'd-m-Y'         => 'd-m-Y - ' . wp_date( 'd-m-Y', time() ),
                                'd/m/Y'         => 'd/m/Y - ' . wp_date( 'd/m/Y', time() ),
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'frontend_display_format', 'F j, Y' ),
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
    ?>
        <script>
        (function($) {
            $(function() {
                $(document).on('cfgroup/ready', '.cfgroup_add_field', function() {
                    $('.cfgroup_date:not(.ready)').init_date();
                });
                $('.cfgroup_date').init_date();
            });

            $.fn.init_date = function() {
                this.each(function() {
                    $(this).find('input.date').flatpickr({
                        // Ref: https://flatpickr.js.org/options/
                        // 'showMonths': 1, // How many months the calendar should show
                        'dateFormat': 'Y-m-d',
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
        $output_format = isset( $field->options['frontend_display_format'] ) ? $field->options['frontend_display_format'] : 'F j, Y';
        $timezone_object = new DateTimeZone( 'UTC' );
        return wp_date( $output_format, strtotime( $value ), $timezone_object );
    }

    function format_value_for_display( $value, $field = null ) {
        return $this->format_value_for_api( $value, $field );
    }
}
