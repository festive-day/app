<div class="field">
    <div class="field_meta">
        <table class="widefat">
            <tr>
                <td class="field_order">

                </td>
                <td class="field_label">
                    <a class="cfgroup_edit_field row-title"><?php echo esc_html( $field->label ); ?></a>
                    <?php if ( in_array( $field->type, array( 'tab', 'heading', 'line_break' ) ) ) : ?>
                        <span class="field-type-flag"><?php echo esc_html( $field->type ); ?></span>
                    <?php endif; ?>
                </td>
                <td class="field_name">
                    <?php echo esc_html( $field->name ); ?>
                </td>
                <td class="field_type">
                    <?php 
                    if ( $field->type == 'file' ) {
                        $file_type = $field->options['file_type'];
                        if ( 'file' == $file_type ) {
                            $file_type = 'any';
                        }
                        echo esc_html( $field->type . ' (' . $file_type . ')' );
                    } elseif ( $field->type == 'text' ) {
                        $text_type = isset( $field->options['text_type'] ) ? $field->options['text_type'] : 'any';
                        echo esc_html( $field->type . ' (' . $text_type . ')' );
                    } else {
                        echo esc_html( $field->type );                        
                    }
                    ?>
                </td>
                <td class="field_width">
                    <?php echo esc_html( $field->column_width ); ?>
                </td>
                <td class="field_toggle">
                    <a class="cfgroup_edit_field"><svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 256 256"><path fill="currentColor" d="m216.49 104.49l-80 80a12 12 0 0 1-17 0l-80-80a12 12 0 0 1 17-17L128 159l71.51-71.52a12 12 0 0 1 17 17Z"/></svg></a>
                </td>
            </tr>
        </table>
    </div>

    <div class="field_form">
        <table class="widefat">
            <tbody>
                <tr class="field_basics">
                    <td colspan="2">
                        <table>
                            <tr>
                                <td class="field_label">
                                    <label>
                                        <?php _e( 'Field Label', 'admin-site-enhancements' ); ?>
                                        <div class="cfgroup_tooltip">?
                                            <div class="tooltip_inner"><?php _e( 'The field label that editors will see.', 'admin-site-enhancements' ); ?></div>
                                        </div>
                                    </label>
                                    <input type="text" name="cfgroup[fields][<?php echo esc_attr( $field->weight ); ?>][label]" value="<?php echo empty( $field->id ) ? '' : esc_attr( $field->label ); ?>" />
                                </td>
                                <td class="field_name">
                                    <label>
                                        <?php _e( 'Field Name', 'admin-site-enhancements' ); ?>
                                        <div class="cfgroup_tooltip">?
                                            <div class="tooltip_inner">
                                                <?php _e( 'The field name is passed into get_cf() to retrieve values. Use only lowercase letters, numbers, and underscores.', 'admin-site-enhancements' ); ?>
                                            </div>
                                        </div>
                                    </label>
                                    <input type="text" name="cfgroup[fields][<?php echo esc_attr( $field->weight ); ?>][name]" value="<?php echo empty( $field->id ) ? '' : esc_attr( $field->name ); ?>" />
                                </td>
                                <td class="field_type">
                                    <label><?php _e( 'Field Type', 'admin-site-enhancements' ); ?></label>
                                    <?php
                                        $content_fields = array( 'text', 'textarea', 'wysiwyg', 'file', 'gallery' );
                                        $choice_fields = array( 'true_false', 'radio', 'select', 'checkbox' );
                                        $extra_fields = array( 'hyperlink', 'number', 'date', 'time', 'datetime', 'color' );
                                        $relational_fields = array( 'relationship', 'term', 'user' );
                                        $special_fields = array( 'repeater' );
                                        $layout_fields = array( 'tab', 'heading', 'line_break' );
                                    ?>
                                    <select name="cfgroup[fields][<?php echo esc_attr( $field->weight ); ?>][type]">
                                        <optgroup label="Content">
                                            <?php foreach ( CFG()->fields as $type ) : ?>
                                            <?php $selected = ($type->name == $field->type) ? ' selected' : ''; ?>
                                            <?php if ( in_array( $type->name, $content_fields ) ) { ?>
                                            <option value="<?php echo esc_attr( $type->name ); ?>"<?php echo $selected; ?>><?php echo esc_html( $type->label ); ?></option>
                                            <?php } ?>
                                            <?php endforeach; ?>
                                        <optgroup>
                                        <optgroup label="Choice">
                                            <?php foreach ( CFG()->fields as $type ) : ?>
                                            <?php $selected = ($type->name == $field->type) ? ' selected' : ''; ?>
                                            <?php if ( in_array( $type->name, $choice_fields ) ) { ?>
                                            <option value="<?php echo esc_attr( $type->name ); ?>"<?php echo $selected; ?>><?php echo esc_html( $type->label ); ?></option>
                                            <?php } ?>
                                            <?php endforeach; ?>
                                        <optgroup>
                                        <optgroup label="Extra">
                                            <?php foreach ( CFG()->fields as $type ) : ?>
                                            <?php $selected = ($type->name == $field->type) ? ' selected' : ''; ?>
                                            <?php if ( in_array( $type->name, $extra_fields ) ) { ?>
                                            <option value="<?php echo esc_attr( $type->name ); ?>"<?php echo $selected; ?>><?php echo esc_html( $type->label ); ?></option>
                                            <?php } ?>
                                            <?php endforeach; ?>
                                        <optgroup>
                                        <optgroup label="Relational">
                                            <?php foreach ( CFG()->fields as $type ) : ?>
                                            <?php $selected = ($type->name == $field->type) ? ' selected' : ''; ?>
                                            <?php if ( in_array( $type->name, $relational_fields ) ) { ?>
                                            <option value="<?php echo esc_attr( $type->name ); ?>"<?php echo $selected; ?>><?php echo esc_html( $type->label ); ?></option>
                                            <?php } ?>
                                            <?php endforeach; ?>
                                        <optgroup>
                                        <optgroup label="Special">
                                            <?php foreach ( CFG()->fields as $type ) : ?>
                                            <?php $selected = ($type->name == $field->type) ? ' selected' : ''; ?>
                                            <?php if ( in_array( $type->name, $special_fields ) ) { ?>
                                            <option value="<?php echo esc_attr( $type->name ); ?>"<?php echo $selected; ?>><?php echo esc_html( $type->label ); ?></option>
                                            <?php } ?>
                                            <?php endforeach; ?>
                                        <optgroup>
                                        <optgroup label="Layout">
                                            <?php foreach ( CFG()->fields as $type ) : ?>
                                            <?php $selected = ($type->name == $field->type) ? ' selected' : ''; ?>
                                            <?php if ( in_array( $type->name, $layout_fields ) ) { ?>
                                            <option value="<?php echo esc_attr( $type->name ); ?>"<?php echo $selected; ?>><?php echo esc_html( $type->label ); ?></option>
                                            <?php } ?>
                                            <?php endforeach; ?>
                                        <optgroup>
                                    </select>
                                </td>
                                <td class="field_column_width">
                                    <label>
                                        <?php _e( 'Column Width', 'admin-site-enhancements' ); ?>
                                        <div class="cfgroup_tooltip">?
                                            <div class="tooltip_inner"><?php _e( 'Set column width to allow for more than one field in a row. For example: Three "third" fields will be shown in a single row. ' ); ?></div>
                                        </div>
                                    </label>
                                    <select name="cfgroup[fields][<?php echo esc_attr( $field->weight ); ?>][column_width]">
                                        <option value="full" <?php selected( $field->column_width, 'full' ); ?>>
                                            Full
                                        </option>
                                        <option value="three-quarters" <?php selected( $field->column_width, 'three-quarters' ); ?>>
                                            Three quarters
                                        </option>
                                        <option value="two-thirds" <?php selected( $field->column_width, 'two-thirds' ); ?>>
                                            Two thirds
                                        </option>
                                        <option value="half" <?php selected( $field->column_width, 'half' ); ?>>
                                            Half
                                        </option>
                                        <option value="third" <?php selected( $field->column_width, 'third' ); ?>>
                                            Third
                                        </option>
                                        <option value="quarter" <?php selected( $field->column_width, 'quarter' ); ?>>
                                            Quarter
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <?php CFG()->fields[ $field->type ]->options_html( $field->weight, $field ); ?>

                <tr class="field_notes">
                    <td class="label">
                        <label>
                            <?php _e( 'Instructions', 'admin-site-enhancements' ); ?>
                            <div class="cfgroup_tooltip">?
                                <div class="tooltip_inner"><?php _e( 'Instructions for editors. Shown when submitting data.', 'admin-site-enhancements' ); ?></div>
                            </div>
                        </label>
                    </td>
                    <td>
                        <textarea name="cfgroup[fields][<?php echo esc_attr( $field->weight ); ?>][notes]"><?php echo esc_textarea( $field->notes ); ?></textarea>
                    </td>
                </tr>
                <tr class="field_actions">
                    <td class="label"></td>
                    <td style="vertical-align:middle">
                        <input type="hidden" name="cfgroup[fields][<?php echo esc_attr( $field->weight ); ?>][id]" class="field_id" value="<?php echo esc_attr( $field->id ); ?>" />
                        <input type="hidden" name="cfgroup[fields][<?php echo esc_attr( $field->weight ); ?>][parent_id]" class="parent_id" value="<?php echo esc_attr( $field->parent_id ); ?>" />
                        <span class="cfgroup_delete_field"><?php _e( 'Delete', 'admin-site-enhancements' ); ?></span><input type="button" value="<?php _e( 'Close', 'admin-site-enhancements' ); ?>" class="button-secondary cfgroup_edit_field" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>