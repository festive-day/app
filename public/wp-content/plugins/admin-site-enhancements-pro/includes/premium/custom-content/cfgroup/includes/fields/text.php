<?php

class cfgroup_text extends cfgroup_field
{

    function __construct() {
        $this->name = 'text';
        $this->label = __( 'Text', 'admin-site-enhancements' );
    }


    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Text Type', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'select',
                        'input_name' => "cfgroup[fields][$key][options][text_type]",
                        'options' => [
                            'choices' => [
                                'any'       => __( 'Any', 'admin-site-enhancements' ),
                                'url'       => __( 'URL / oEmbed', 'admin-site-enhancements' ),
                                'email'     => __( 'Email', 'admin-site-enhancements' ),
                                'phone'     => __( 'Phone', 'admin-site-enhancements' ),
                                'shortcode' => __( 'Shortcode', 'admin-site-enhancements' ),
                            ],
                            'force_single' => true,
                        ],
                        'value' => $this->get_option( $field, 'text_type', 'any' ),
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
                        'type'          => 'text',
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

    function format_value_for_input( $value, $field = null ) {
        return htmlspecialchars( $value, ENT_QUOTES );
    }
}
