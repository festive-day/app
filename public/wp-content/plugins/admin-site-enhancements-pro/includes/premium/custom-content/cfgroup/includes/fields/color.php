<?php

class cfgroup_color extends cfgroup_field
{

    function __construct() {
        $this->name = 'color';
        $this->label = __( 'Color', 'admin-site-enhancements' );
    }


    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Default Value', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'text',
                        'input_name' => "cfgroup[fields][$key][options][default_value]",
                        'value' => $this->get_option( $field, 'default_value' ),
                    ] );
                ?>
            </td>
        </tr>
    <?php
    }

    /**
     * Generate the field HTML
     * @param object $field 
     * @since 7.8.2
     */
    function html( $field ) {
    ?> 
        <div class="cfgroup-color-preview"></div>
        <input type="text" name="<?php echo esc_attr( $field->input_name ); ?>" class="<?php echo esc_attr( $field->input_class ); ?>" value="<?php echo esc_attr( $field->value ); ?>" />
    <?php
    }

    function input_head( $field = null ) {
        $this->load_assets();
    ?>
        <script>
        (function($) {
            $(document).on('focus', '.cfgroup_color input.color', function() {
                if (!$(this).hasClass('ready')) {
                    $(this).addClass('ready').colorPicker({
                        animationSpeed: 0
                    });
                }
            });

            $(function() {
                $('.cfgroup_color input.color').addClass('ready').colorPicker({
                    animationSpeed: 0
                });
            });
        })(jQuery);
        </script>
    <?php
    }

    function load_assets() {
        // Color picker: Modified from https://github.com/PitPik/tinyColorPicker v1.1.1 (Jul 15, 2016)
        // [TODO] Maybe use https://github.com/simonwep/pickr in the future
        wp_register_script( 'jqcolors', CFG_URL . '/assets/js/color-picker/colors.js' );
        wp_enqueue_script( 'jqcolors' );
        // wp_register_script( 'jqcolorpicker', CFG_URL . '/assets/js/color-picker/jqColorPicker.js' );
        // wp_register_script( 'jqcolorpicker', CFG_URL . '/assets/js/color-picker/jqColorPicker.min.js' );
        wp_register_script( 'jqcolorpicker', CFG_URL . '/assets/js/color-picker/jqColorPicker_mod.js' );
        wp_enqueue_script( 'jqcolorpicker' );
    }
}
