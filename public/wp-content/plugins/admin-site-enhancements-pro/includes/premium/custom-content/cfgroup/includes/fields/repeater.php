<?php

class cfgroup_repeater extends cfgroup_field
{
    public $values;

    function __construct() {
        $this->name = 'repeater';
        $this->label = __( 'Repeater', 'admin-site-enhancements' );
        $this->values = [];
    }


    /*
    ================================================================
        html
    ================================================================
    */
    function html( $field ) {
        global $post;
        
        if ( is_object( $post ) && property_exists( $post, 'ID' ) ) {
            // for posts and options pages - 'post' $object_type
            $this->values = CFG()->api->get_fields( $post->ID, [ 'format' => 'input' ], 'post' );
        } else if ( isset( $_GET['tag_ID'] ) ) {
            // for taxonomy terms - 'term' $object_type
            $term_id = $_GET['tag_ID'];
            $this->values = CFG()->api->get_fields( $term_id, [ 'format' => 'input' ], 'term' );
        } else {
            // TODO: to handle when in a taxonomy term add/create screens
            $this->values = array();
        }

        $this->recursive_clone( $field->group_id, $field->id );
        $this->recursive_html( $field->group_id, $field->id );
    }


    /*
    ================================================================
        options_html
    ================================================================
    */
    function options_html( $key, $field ) {
    ?>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label repeater-label">
                <label><?php _e( 'Row Display', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'true_false',
                        'input_name' => "cfgroup[fields][$key][options][row_display]",
                        'input_class' => 'true_false',
                        'value' => $this->get_option( $field, 'row_display' ),
                        'options' => [ 'message' => __( 'Show the values by default', 'admin-site-enhancements' ) ],
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Row Label', 'admin-site-enhancements' ); ?></label>
                <div class="cfgroup_tooltip">?
                    <div class="tooltip_inner"><?php _e( 'Override the "Repeater Row” header text. You can also dynamically populate the row label with field values. If you have a text field named “first_name”, enter {first_name} to use the field value as the row label.', 'admin-site-enhancements' ); ?></div>
                </div>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'text',
                        'input_name' => "cfgroup[fields][$key][options][row_label]",
                        'value' => $this->get_option( $field, 'row_label', __( 'Repeater Row', 'admin-site-enhancements' ) ),
                    ] );
                ?>
            </td>
        </tr>
        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Button Label', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <?php
                    CFG()->create_field( [
                        'type' => 'text',
                        'input_name' => "cfgroup[fields][$key][options][button_label]",
                        'value' => $this->get_option( $field, 'button_label', __( 'Add Row', 'admin-site-enhancements' ) ),
                    ] );
                ?>
            </td>
        </tr>

        <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
                <label><?php _e( 'Limits', 'admin-site-enhancements' ); ?></label>
            </td>
            <td>
                <input type="text" name="cfgroup[fields][<?php echo $key; ?>][options][limit_min]" value="<?php echo $this->get_option( $field, 'limit_min' ); ?>" placeholder="min" style="width:60px" />
                <input type="text" name="cfgroup[fields][<?php echo $key; ?>][options][limit_max]" value="<?php echo $this->get_option( $field, 'limit_max' ); ?>" placeholder="max" style="width:60px" />
            </td>
        </tr>

    <?php
    }


    /*
    ================================================================
        recursive_clone
    ================================================================
    */
    function recursive_clone( $group_id, $field_id ) {
        $repeater_field_ids = [];

        // Get repeater field
        $repeater_field = CFG()->api->get_input_fields( [
            'field_id' => $field_id
        ] );

        // Get sub-fields
        $results = CFG()->api->get_input_fields( [
            'group_id' => $group_id,
            'parent_id' => $field_id
        ] );

        $row_label = $this->dynamic_label(
            $this->get_option( $repeater_field[ $field_id ], 'row_label', __( 'Repeater Row', 'admin-site-enhancements' ) )
        );

        ob_start();
    ?>
        <div class="repeater_wrapper">
            <div class="cfgroup_repeater_head open">
                <a class="cfgroup_delete_field" href="javascript:;"></a>
                <a class="cfgroup_toggle_field" href="javascript:;"></a>
                <a class="cfgroup_insert_field" href="javascript:;"></a>
                <span class="label"><?php echo esc_attr( $row_label ); ?></span>
            </div>
            <div class="cfgroup_repeater_body open">
                <div class="fields-wrapper">
                    <?php foreach ( $results as $field ) : ?>
                        <div class="field-column-<?php echo $field->column_width; ?>">
                            <label><?php echo esc_html( $field->label ); ?></label>

                            <?php if ( ! empty( $field->notes ) ) : ?>
                            <p class="notes"><?php echo esc_html( $field->notes ); ?></p>
                            <?php endif; ?>

                            <div class="field field-<?php echo esc_attr( $field->name ); ?> cfgroup_<?php echo esc_attr( $field->type ); ?>">
                            <?php
                            if ( 'repeater' == $field->type ) :
                                $repeater_field_ids[] = $field->id;
                            ?>
                                <div class="table_footer">
                                    <input type="button" class="button-secondary cfgroup_add_field" value="<?php echo esc_attr( $this->get_option( $field, 'button_label', __( 'Add Row', 'admin-site-enhancements' ) ) ); ?>" data-repeater-tag="[clone][<?php echo $field->id; ?>]" data-rows="0" />
                                </div>
                            <?php else : ?>
                            <?php
                                CFG()->create_field( [
                                    'type' => $field->type,
                                    'input_name' => "cfgroup[input][clone][$field->id][value][]",
                                    'input_class' => $field->type,
                                    'options' => $field->options,
                                    'value' => $this->get_option( $field, 'default_value' ),
                                ] );
                            ?>
                            <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php
        $buffer = ob_get_clean();
    ?>

        <script>
        // In frontend CFG forms, this prevents CFG is undfined error
        if ( typeof CFG === 'undefined' ) {
            var CFG = CFG || {};
            CFG['get_field_value'] = {};
            CFG['repeater_buffer'] = [];            
        }
        
        CFG.repeater_buffer[<?php echo $field_id; ?>] = <?php echo json_encode( $buffer ); ?>;
        </script>

    <?php
        foreach ( $repeater_field_ids as $repeater_field_id ) {
            $this->recursive_clone( $group_id, $repeater_field_id );
        }
    }


    /*
    ================================================================
        recursive_html
    ================================================================
    */
    function recursive_html( $group_id, $field_id, $parent_tag = '', $parent_weight = 0 ) {

        // Get repeater field
        $repeater_field = CFG()->api->get_input_fields( [
            'field_id' => $field_id
        ] );

        // Get sub-fields
        $results = CFG()->api->get_input_fields( [
            'group_id' => $group_id,
            'parent_id' => $field_id
        ] );

        // Dynamically build the $values array
        $parent_tag = empty( $parent_tag ) ? "[$field_id]" : $parent_tag;
        eval( "\$values = isset(\$this->values{$parent_tag} ) ? \$this->values{$parent_tag} : false;" );

        // Row options
        $row_display = $this->get_option( $repeater_field[ $field_id ], 'row_display', 0 );
        $row_label = $this->get_option( $repeater_field[ $field_id ], 'row_label', __( 'Repeater Row', 'admin-site-enhancements' ) );
        $button_label = $this->get_option( $repeater_field[ $field_id ], 'button_label', __( 'Add Row', 'admin-site-enhancements' ) );
        $css_class = ( 0 < (int) $row_display ) ? ' open' : '';

        // Do the dirty work
        $row_offset = -1;

        if ( $values ) :
            foreach ( $values as $i => $value ) :
                $row_offset = max( $i, $row_offset );
    ?>
        <div class="repeater_wrapper">
            <div class="cfgroup_repeater_head<?php echo $css_class; ?>">
                <a class="cfgroup_delete_field" href="javascript:;"></a>
                <a class="cfgroup_toggle_field" href="javascript:;"></a>
                <a class="cfgroup_insert_field" href="javascript:;"></a>
                <span class="label"><?php echo esc_attr( $this->dynamic_label( $row_label, $results, $values[ $i ] ) ); ?>&nbsp;</span>
            </div>
            <div class="cfgroup_repeater_body<?php echo $css_class; ?>">
                <div class="fields-wrapper">
                <?php foreach ( $results as $field ) : ?>
                    <div class="field-column-<?php echo $field->column_width; ?>">
                        <?php if ( 'line_break' != $field->type ): ?>
                        <label><?php echo esc_html( $field->label ); ?></label>
                        <?php endif; ?>

                        <?php if ( ! empty( $field->notes ) ) : ?>
                        <p class="notes"><?php echo esc_html( $field->notes ); ?></p>
                        <?php endif; ?>

                        <div class="field field-<?php echo esc_attr( $field->name ); ?> cfgroup_<?php echo esc_attr( $field->type ); ?>">
                        <?php if ( 'repeater' == $field->type ) : ?>
                            <?php $this->recursive_html( $group_id, $field->id, "{$parent_tag}[$i][$field->id]", $i ); ?>
                        <?php else : ?>
                        <?php
                            $args = [
                                'type' => $field->type,
                                'input_name' => "cfgroup[input]{$parent_tag}[$i][$field->id][value][]",
                                'input_class' => $field->type,
                                'options' => $field->options,
                            ];

                            if ( isset( $values[ $i ][ $field->id ] ) ) {
                                $args['value'] = $values[ $i ][ $field->id ];
                            }
                            elseif ( isset( $field->options['default_value'] ) ) {
                                $args['value'] = $field->options['default_value'];
                            }

                            CFG()->create_field( $args );
                        ?>
                        <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php endforeach; endif; ?>

        <div class="table_footer">
            <input type="button" class="button-secondary cfgroup_add_field" value="<?php echo esc_attr( $button_label ); ?>" data-repeater-tag="<?php echo esc_attr( $parent_tag ); ?>" data-rows="<?php echo ( $row_offset + 1 ); ?>" />
        </div>
    <?php
    }


    /*---------------------------------------------------------------------------------------------
        input_head
    ---------------------------------------------------------------------------------------------*/

    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            $(function() {
                $(document).on('click', '.cfgroup_add_field', function() {
                    var num_rows = $(this).attr('data-rows');
                    var repeater_tag = $(this).attr('data-repeater-tag');
                    var repeater_id = repeater_tag.match(/.*\[(.*?)\]/)[1];
                    var html = CFG.repeater_buffer[repeater_id].replace(/\[clone\]/g, repeater_tag + '[' + num_rows + ']');
                    $(this).attr('data-rows', parseInt(num_rows)+1);
                    $(html).insertBefore( $(this).closest('.table_footer') ).addClass('repeater_wrapper_new');
                    $(this).trigger('cfgroup/ready');
                });

                $(document).on('click', '.cfgroup_insert_field', function(event) {
                    event.stopPropagation();
                    var $add_field = $(this).parents('.cfgroup_repeater').find('.cfgroup_add_field');
                    var num_rows = $add_field.attr('data-rows');
                    var repeater_tag = $add_field.attr('data-repeater-tag');
                    var repeater_id = repeater_tag.match(/.*\[(.*?)\]/)[1];
                    var html = CFG.repeater_buffer[repeater_id].replace(/\[clone\]/g, repeater_tag + '[' + num_rows + ']');
                    $add_field.attr('data-rows', parseInt(num_rows)+1);
                    $(html).insertAfter( $(this).closest('.repeater_wrapper') ).addClass('repeater_wrapper_new');
                    $add_field.trigger('cfgroup/ready');
                });

                $(document).on('click', '.cfgroup_delete_field', function(event) {
                    if (confirm('Remove this row?')) {
                        $(this).closest('.repeater_wrapper').remove();
                    }
                    event.stopPropagation();
                });

                $(document).on('click', '.cfgroup_repeater_head', function() {
                    $(this).toggleClass('open');
                    $(this).siblings('.cfgroup_repeater_body').toggleClass('open');
                });

                // Hide or show all rows
                // The HTML is located in includes/form.php
                $(document).on('click', '.cfgroup_repeater_toggle', function() {
                    $(this).closest('.field').find('.cfgroup_repeater_head').toggleClass('open');
                    $(this).closest('.field').find('.cfgroup_repeater_body').toggleClass('open');
                });

                $('.cfgroup_repeater').sortable({
                    axis: 'y',
                    containment: 'parent',
                    items: '.repeater_wrapper',
                    handle: '.cfgroup_repeater_head',
                    update: function(event, ui) {

                        // To re-order field names:
                        // 1. Get the depth of the dragged element
                        // 2. Repeater through each input field within the dragged element
                        // 3. Reset the array index within the name attribute
                        var $container = ui.item.closest('.field');
                        var depth = $container.closest('.cfgroup_repeater').parents('.cfgroup_repeater').length;
                        var array_element = 3 + (depth * 2);

                        var counter = -1;
                        var last_index = -1;
                        $container.find('[name^="cfgroup[input]"]').each(function() {
                            var name_attr = $(this).attr('name').split('[');
                            var current_index = parseInt( name_attr[array_element] );
                            if (current_index != last_index) {
                                counter += 1;
                            }
                            name_attr[array_element] = counter + ']';
                            last_index = current_index;
                            $(this).attr('name', name_attr.join('['));
                        });
                    }
                });
            });
        })(jQuery);
        </script>
    <?php
    }


    /*
    ================================================================
        dynamic_label
    ================================================================
    */
    function dynamic_label( $row_label, $fields = [], $values = [] ) {

        // Exit stage left
        if ( '{' != substr( $row_label, 0, 1 ) || '}' != substr( $row_label, -1 ) ) {
            return $row_label;
        }

        $field = false;
        $fallback = false;
        $field_name = substr( $row_label, 1, -1 );

        // Check for fallback value
        if ( false !== strpos( $field_name, ':' ) ) {
            list( $field_name, $fallback ) = explode( ':', $field_name );
        }

        // Get all field names and IDs
        foreach ( $fields as $f ) {
            if ( $field_name == $f->name ) {
                $field = $f;
                break;
            }
        }

        if ( ! empty( $field ) && isset( $values[ $field->id ] ) ) {
            if ( 'select' == $field->type ) {
                $select_key = reset( $values[ $field->id ] );
                $row_label = $field->options['choices'][ $select_key ];
            }
            else {
                $row_label = $values[ $field->id ];
            }
        }
        elseif ( false !== $fallback ) {
             $row_label = $fallback;
        }

        return $row_label;
    }


    /*
    ================================================================
        prepare_value
    ================================================================
    */
    function prepare_value( $value, $field = null ) {
        return $value;
    }
}
