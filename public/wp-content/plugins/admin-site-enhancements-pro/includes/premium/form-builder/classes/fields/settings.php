<?php
defined( 'ABSPATH' ) || die();
?>

<div class="fb-fields-settings fb-hidden fb-fields-type-<?php echo esc_attr( $field_type ); ?>" id="fb-fields-settings-<?php echo esc_attr( $field_id ); ?>" data-fid="<?php echo esc_attr( $field_id ); ?>" data-field-key="<?php echo esc_attr( $field_key ); ?>" data-field-type="<?php echo esc_attr( $field_type ); ?>">
    <input type="hidden" name="fb-form-submitted[]" value="<?php echo absint( $field_id ); ?>" />
    <input type="hidden" name="field_options[field_order_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['field_order'] ); ?>" />
    <input type="hidden" name="field_options[grid_id_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['grid_id'] ); ?>" id="fb-grid-class-<?php echo esc_attr( $field_id ); ?>" />

    <div class="fb-field-panel-header">
        <h3><?php echo wp_kses( $type_icon, Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?><span><?php printf( /* translators: %s: field type name */ esc_html__( '%s Field', 'admin-site-enhancements' ), esc_html( $type_name ) ); ?></span></h3>
        <div class="fb-field-panel-id">(ID <?php echo esc_html( $field_id ); ?>)</div>
    </div>

    <div class="fb-form-container fb-grid-container">
        <?php
        if ( $field_type === 'altcha' && !Form_Builder_Field_Altcha::should_show_captcha() ) {
            ?>
            <div class="fb-form-row">
                <?php printf(
                    /* translators: %1$s: opening <a> tag, %2$s: closing </a> tag */
                    esc_html__( 'ALTCHA will not work untill the Secret Key is set up. Add the key in ASE\'s CAPTCHA Protection module %1$shere%2$s.', 'admin-site-enhancements' ), 
                    '<a href="' . get_admin_url( null, '/tools.php?page=admin-site-enhancements#security' ) . '" target="_blank">', 
                    '</a>' ); ?>
            </div>
            <?php
        }
        
        if ( $field_type === 'captcha' && !Form_Builder_Field_Captcha::should_show_captcha() ) {
            ?>
            <div class="fb-form-row">
                <?php printf(
                    /* translators: %1$s: opening <a> tag, %2$s: closing </a> tag */
                    esc_html__( 'reCAPTCHA will not work untill the Site and Secret Keys are set up. Add Keys in ASE\'s CAPTCHA Protection module %1$shere%2$s.', 'admin-site-enhancements' ), 
                    '<a href="' . get_admin_url( null, '/tools.php?page=admin-site-enhancements#security' ) . '" target="_blank">', 
                    '</a>' ); ?>
            </div>
            <?php
        }

        if ( $field_type === 'turnstile' && !Form_Builder_Field_Turnstile::should_show_captcha() ) {
            ?>
            <div class="fb-form-row">
                <?php printf(
                    /* translators: %1$s: opening <a> tag, %2$s: closing </a> tag */
                    esc_html__( 'Turnstile will not work untill the Site and Secret Keys are set up. Add Keys in ASE\'s CAPTCHA Protection module %1$shere%2$s.', 'admin-site-enhancements' ), 
                    '<a href="' . get_admin_url( null, '/tools.php?page=admin-site-enhancements#security' ) . '" target="_blank">', 
                    '</a>' ); ?>
            </div>
            <?php
        }
        if ( $display['label'] ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Label', 'admin-site-enhancements' ); ?> </label>
                <textarea name="field_options[name_<?php echo absint( $field_id ); ?>]" rows="3" data-changeme="fb-editor-field-label-text-<?php echo absint( $field_id ); ?>"><?php echo esc_textarea( $field['name'] ); ?></textarea>
            </div>

            <?php
            if ( ! empty( $field['label_position'] ) ) {
                $label_position = $field['label_position'];
            } else {
                $label_position = isset( $form_options['form_label_position'] ) ? $form_options['form_label_position'] : 'top';
            }
            ?>

            <div class="fb-form-row always-hide"><!-- Add 'fb-grid-3' class to make half-wide-->
                <label><?php esc_html_e( 'Label Position', 'admin-site-enhancements' ); ?></label>
                <select name="field_options[label_position_<?php echo absint( $field_id ); ?>]" class="field-options-label-position">
                    <option value="top" <?php isset( $field['label_position'] ) ? selected( $label_position, 'top' ) : ''; ?>>
                        <?php esc_html_e( 'Top', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="left" <?php isset( $field['label_position'] ) ? selected( $label_position, 'left' ) : ''; ?>>
                        <?php esc_html_e( 'Left', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="right" <?php isset( $field['label_position'] ) ? selected( $label_position, 'right' ) : ''; ?>>
                        <?php esc_html_e( 'Right', 'admin-site-enhancements' ); ?>
                    </option>
                </select>
            </div>

            <?php
            if ( ! empty( $field['label_alignment'] ) ) {
                $label_alignment = $field['label_alignment'];
            } else {
                $label_alignment = isset( $form_options['form_label_alignment'] ) ? $form_options['form_label_alignment'] : 'left';
            }
            ?>

            <div class="fb-form-row always-hide"><!-- Add 'fb-grid-3' class to make half-wide-->
                <label><?php esc_html_e( 'Label Alignment', 'admin-site-enhancements' ); ?></label>
                <select name="field_options[label_alignment_<?php echo absint( $field_id ); ?>]">
                    <option value="left" <?php selected( $label_alignment, 'left' ); ?>>
                        <?php esc_html_e( 'Left', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="right" <?php selected( $label_alignment, 'right' ); ?>>
                        <?php esc_html_e( 'Right', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="center" <?php selected( $label_alignment, 'center' ); ?>>
                        <?php esc_html_e( 'Center', 'admin-site-enhancements' ); ?>
                    </option>
                </select>
            </div>
            <?php
        }

        if ( $display['label'] || $display['required']) {
            ?>
            <div class="fb-form-row fb-hide-label-required-row">
                <?php
                if ( $field_type !== 'hidden' ) {
                    ?>
                <label for="fb-hide-label-field-<?php echo absint( $field_id ); ?>">
                    <input id="fb-hide-label-field-<?php echo absint( $field_id ); ?>" type="checkbox" name="field_options[hide_label_<?php echo absint( $field_id ); ?>]" value="1" <?php checked( ( isset( $field['hide_label'] ) && $field['hide_label'] ), 1 ); ?> data-label-show-hide-checkbox="fb-label-show-hide" />
                    <?php esc_html_e( 'Hide Label', 'admin-site-enhancements' ); ?>
                </label>
                    <?php
                }

                if ( $field_type !== 'altcha' && $field_type !== 'captcha' && $field_type !== 'turnstile' && $field_type !== 'hidden' ) {
                    ?>
                    <label for="fb-req-field-<?php echo absint( $field_id ); ?>">
                        <input type="checkbox" class="fb-form-field-required" id="fb-req-field-<?php echo absint( $field_id ); ?>" name="field_options[required_<?php echo absint( $field_id ); ?>]" value="1" <?php checked( $field['required'], 1 ); ?> />
                        <?php esc_html_e( 'Required', 'admin-site-enhancements' ); ?>
                    </label>
                    <?php
                }
                ?>
            </div>
            <?php
        }

        if ( $field_type === 'heading' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Select Heading', 'admin-site-enhancements' ); ?></label>
                <select name="field_options[heading_type_<?php echo esc_attr( $field_id ); ?>]">
                    <option value="h1" <?php isset( $field['heading_type'] ) ? selected( $field['heading_type'], 'h1' ) : ''; ?>>
                        <?php esc_html_e( 'H1', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="h2" <?php isset( $field['heading_type'] ) ? selected( $field['heading_type'], 'h2' ) : ''; ?>>
                        <?php esc_html_e( 'H2', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="h3" <?php isset( $field['heading_type'] ) ? selected( $field['heading_type'], 'h3' ) : ''; ?>>
                        <?php esc_html_e( 'H3', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="h4" <?php isset( $field['heading_type'] ) ? selected( $field['heading_type'], 'h4' ) : ''; ?>>
                        <?php esc_html_e( 'H4', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="h5" <?php isset( $field['heading_type'] ) ? selected( $field['heading_type'], 'h5' ) : ''; ?>>
                        <?php esc_html_e( 'H5', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="h6" <?php isset( $field['heading_type'] ) ? selected( $field['heading_type'], 'h6' ) : ''; ?>>
                        <?php esc_html_e( 'H6', 'admin-site-enhancements' ); ?>
                    </option>
                </select>
            </div>
            <?php
        }

        if ( $display['content'] ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Content', 'admin-site-enhancements' ); ?></label>
                <div class="fb-form-textarea">
                    <textarea name="field_options[content_<?php echo esc_attr( $field_id ); ?>]" data-changeme="fb-field-<?php echo esc_attr( $field_id ) ?>"><?php echo isset( $field['content'] ) ? esc_textarea( $field['content'] ) : ''; ?></textarea>
                </div>
            </div>

            <div class="fb-form-row">
                <label><?php esc_html_e( 'Text Alignment', 'admin-site-enhancements' ); ?></label>
                <select name="field_options[text_alignment_<?php echo esc_attr( $field_id ); ?>]">
                    <option value="left" <?php isset( $field['text_alignment'] ) ? selected( $field['text_alignment'], 'left' ) : ''; ?>>
                        <?php esc_html_e( 'Left', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="right" <?php isset( $field['text_alignment'] ) ? selected( $field['text_alignment'], 'right' ) : ''; ?>>
                        <?php esc_html_e( 'Right', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="center" <?php isset( $field['text_alignment'] ) ? selected( $field['text_alignment'], 'center' ) : ''; ?>>
                        <?php esc_html_e( 'Center', 'admin-site-enhancements' ); ?>
                    </option>
                </select>
            </div>
            <?php
        }

        if ( $field_type === 'image_select' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Select Type', 'admin-site-enhancements' ); ?></label>
                <select class="fb-select-image-type" name="field_options[select_option_type_<?php echo esc_attr( $field_id ); ?>]" data-is-id="<?php echo esc_attr( $field_id ); ?>">
                    <option value="checkbox" <?php isset( $field['select_option_type'] ) ? selected( $field['select_option_type'], 'checkbox' ) : ''; ?>>
                        <?php esc_html_e( 'Multiple', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="radio" <?php isset( $field['select_option_type'] ) ? selected( $field['select_option_type'], 'radio' ) : ''; ?>>
                        <?php esc_html_e( 'Single', 'admin-site-enhancements' ); ?>
                    </option>
                </select>
            </div>
            <?php
            $columns = array(
                'small' => esc_html__( 'Small', 'admin-site-enhancements' ),
                'medium' => esc_html__( 'Medium', 'admin-site-enhancements' ),
                'large' => esc_html__( 'Large', 'admin-site-enhancements' ),
                'xlarge' => esc_html__( 'Extra Large', 'admin-site-enhancements' ),
            );
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Image Size', 'admin-site-enhancements' ); ?></label>
                <select name="field_options[image_size_<?php echo absint( $field_id ); ?>]">
                    <?php foreach ( $columns as $col => $col_label ) { ?>
                        <option value="<?php echo esc_attr( $col ); ?>" <?php selected( $field['image_size'], $col ); ?>>
                            <?php echo esc_html( $col_label ); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <?php
        }

        if ( $field_type === 'image' ) {
            $image_id = $image = '';
            if ( isset( $field['image_id'] ) ) {
                $image_id = $field['image_id'];
                $image = wp_get_attachment_image_src( $field['image_id'], 'full' );
                $image = isset( $image[0] ) ? $image[0] : '';
            }
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Select Image', 'admin-site-enhancements' ); ?></label>
                <div class="fb-image-preview">
                    <input type="hidden" class="fb-image-id" name="field_options[image_id_<?php echo esc_attr( $field_id ); ?>]" id="fb-field-image-<?php echo absint( $field_id ); ?>" value="<?php echo esc_attr( $image_id ); ?>" />
                    <div class="fb-image-preview-wrap<?php echo ( $image ? '' : ' fb-hidden' ); ?>">
                        <div class="fb-image-preview-box">
                            <img id="fb-image-preview-<?php echo absint( $field_id ); ?>" src="<?php echo esc_url( $image ); ?>" />
                        </div>
                        <button type="button" class="button fb-remove-image">
                            <?php esc_html_e( 'Delete', 'admin-site-enhancements' ); ?>
                        </button>
                    </div>
                    <button type="button" class="button fb-choose-image<?php echo ( $image ? ' fb-hidden' : '' ); ?>">
                        <?php esc_attr_e( 'Upload image', 'admin-site-enhancements' ); ?>
                    </button>
                </div>
            </div>
            <?php
        }

        if ( $field_type === 'spacer' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Height (px )', 'admin-site-enhancements' ); ?></label>
                <input type="number" name="field_options[spacer_height_<?php echo absint( $field_id ); ?>]" value="<?php echo isset( $field['spacer_height'] ) ? esc_attr( $field['spacer_height'] ) : ''; ?>" data-changeheight="field_change_height_<?php echo absint( $field_id ) ?>" />
            </div>
            <?php
        }

        if ( $field_type === 'scale' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Lowest Scale Text', 'admin-site-enhancements' ); ?></label>
                <input type="text" class="max-value-field" name="field_options[lowest_scale_text_<?php echo absint( $field_id ); ?>]" value="<?php echo isset( $field['lowest_scale_text'] ) ? esc_attr( $field['lowest_scale_text'] ) : ''; ?>" data-changeme="fb-scale-text-lowest-<?php echo absint( $field_id ); ?>" />
            </div>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Highest Scale Text', 'admin-site-enhancements' ); ?></label>
                <input type="text" class="min-value-field" name="field_options[highest_scale_text_<?php echo absint( $field_id ); ?>]" value="<?php echo isset( $field['highest_scale_text'] ) ? esc_attr( $field['highest_scale_text'] ) : ''; ?>" data-changeme="fb-scale-text-highest-<?php echo absint( $field_id ); ?>" />
            </div>
            <div class="fb-form-row" style="display:none;">
                <label><?php esc_html_e( 'Highest Scale Point', 'admin-site-enhancements' ); ?></label>
                <input type="number" name="field_options[highest_scale_point_<?php echo absint( $field_id ); ?>]" value="<?php echo isset( $field['highest_scale_point'] ) ? esc_attr( $field['highest_scale_point'] ) : ''; ?>" min="1" data-field-id="<?php echo absint( $field_id ); ?>" />
            </div>
            <div class="fb-form-row" style="display:none;">
                <label><?php esc_html_e( 'Lowest Scale Point', 'admin-site-enhancements' ); ?></label>
                <input type="number" name="field_options[lowest_scale_point_<?php echo absint( $field_id ); ?>]" value="<?php echo isset( $field['lowest_scale_point'] ) ? esc_attr( $field['lowest_scale_point'] ) : ''; ?>" min="1" data-field-id="<?php echo absint( $field_id ); ?>" />
            </div>
            <?php
        }

        if ( $field_type === 'time' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Step', 'admin-site-enhancements' ); ?></label>
                <input type="number" name="field_options[step_<?php echo absint( $field_id ); ?>]" value="<?php echo isset( $field['step'] ) ? esc_attr( $field['step'] ) : ''; ?>" min="1" />
            </div>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Min Time', 'admin-site-enhancements' ); ?></label>
                <input type="text" class="min-value-field" name="field_options[min_time_<?php echo absint( $field_id ); ?>]" value="<?php echo isset( $field['min_time'] ) ? esc_attr( $field['min_time'] ) : ''; ?>" />
            </div>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Max Time', 'admin-site-enhancements' ); ?></label>
                <input type="text" class="max-value-field" name="field_options[max_time_<?php echo absint( $field_id ); ?>]" value="<?php echo isset( $field['max_time'] ) ? esc_attr( $field['max_time'] ) : ''; ?>" />
            </div>
            <?php
        }

        if ( $field_type === 'date' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Date Format', 'admin-site-enhancements' ); ?></label>
                <select name="field_options[date_format_<?php echo esc_attr( $field_id ); ?>]">
                    <option value="MM dd, yy" <?php isset( $field['date_format'] ) ? selected( $field['date_format'], 'MM dd, yy' ) : ''; ?>>
                        <?php esc_html_e( 'September 19, 2023', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="yy-mm-dd" <?php isset( $field['date_format'] ) ? selected( $field['date_format'], 'yy-mm-dd' ) : ''; ?>>
                        <?php esc_html_e( '2023-09-19', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="mm/dd/yy" <?php isset( $field['date_format'] ) ? selected( $field['date_format'], 'mm/dd/yy' ) : ''; ?>>
                        <?php esc_html_e( '09/19/2023', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="dd/mm/yy" <?php isset( $field['date_format'] ) ? selected( $field['date_format'], 'dd/mm/yy' ) : ''; ?>>
                        <?php esc_html_e( '19/09/2023', 'admin-site-enhancements' ); ?>
                    </option>
                </select>
            </div>
            <?php
        }

        if ( $field_type === 'separator' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Divider Type', 'admin-site-enhancements' ); ?></label>
                <select name="field_options[border_style_<?php echo esc_attr( $field_id ); ?>]" data-changebordertype="field_change_style_<?php echo esc_attr( $field_id ) ?>">
                    <option value="solid" <?php isset( $field['border_style'] ) ? selected( $field['border_style'], 'solid' ) : ''; ?>>
                        <?php esc_html_e( 'Solid', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="double" <?php isset( $field['border_style'] ) ? selected( $field['border_style'], 'double' ) : ''; ?>>
                        <?php esc_html_e( 'Double', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="dotted" <?php isset( $field['border_style'] ) ? selected( $field['border_style'], 'dotted' ) : ''; ?>>
                        <?php esc_html_e( 'Dotted', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="dashed" <?php isset( $field['border_style'] ) ? selected( $field['border_style'], 'dashed' ) : ''; ?>>
                        <?php esc_html_e( 'Dashed', 'admin-site-enhancements' ); ?>
                    </option>
                </select>
            </div>

            <div class="fb-form-row">
                <label><?php esc_html_e( 'Divider Height (px )', 'admin-site-enhancements' ); ?></label>
                <input type="number" name="field_options[border_width_<?php echo absint( $field_id ); ?>]" value="<?php echo ( isset( $field['border_width'] ) ? esc_attr( $field['border_width'] ) : '' ); ?>" data-changeborderwidth="field_change_style_<?php echo absint( $field_id ) ?>" />
            </div>
            <?php
        }

        if ( $field_type === 'textarea' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Rows', 'admin-site-enhancements' ); ?></label>
                <input type="number" name="field_options[rows_<?php echo absint( $field_id ); ?>]" value="<?php echo ( isset( $field['rows'] ) ? esc_attr( $field['rows'] ) : '' ); ?>" data-changerows="<?php echo esc_attr( $this->html_id() ); ?>" />
            </div>
            <?php
        }

        if ( $display['range'] ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Number Range', 'admin-site-enhancements' ); ?></label>
                <div class="fb-grid-container">
                    <div class="fb-form-row fb-grid-2">
                        <label><?php esc_html_e( 'From', 'admin-site-enhancements' ); ?></label>
                        <input type="number" name="field_options[minnum_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['minnum'] ); ?>" data-changeme="fb-field-<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="min" <?php echo ( $field_type === 'range_slider' ? 'data-changemin="field_change_min_' . esc_attr( $field['field_key'] ) . '"' : '' ); ?> />
                    </div>

                    <div class="fb-form-row fb-grid-2">
                        <label><?php esc_html_e( 'To', 'admin-site-enhancements' ); ?></label>
                        <input type="number" name="field_options[maxnum_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['maxnum'] ); ?>" data-changeme="fb-field-<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="max" <?php echo ( $field_type === 'range_slider' ? 'data-changemax="field_change_max_' . esc_attr( $field['field_key'] ) . '"' : '' ); ?> />
                    </div>

                    <div class="fb-form-row fb-grid-2">
                        <label><?php esc_html_e( 'Step', 'admin-site-enhancements' ); ?></label>
                        <input type="number" name="field_options[step_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['step'] ); ?>" data-changeatt="step" data-changeme="fb-field-<?php echo esc_attr( $field['field_key'] ); ?>" />
                    </div>
                </div>
            </div>
            <?php
        }

        $this->show_primary_options();

        if ( $field_type === 'upload' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Upload Label', 'admin-site-enhancements' ); ?></label>
                <input type="text" name="field_options[upload_label_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['upload_label'] ); ?>" data-changeme="fb-editor-upload-label-text-<?php echo absint( $field_id ); ?>" />
            </div>

            <div class="fb-form-row">
                <label><?php esc_html_e( 'Extensions', 'admin-site-enhancements' ); ?></label>
                <input type="text" name="field_options[extensions_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['extensions'] ); ?>" />
                <label class="fb-field-desc"><?php esc_html_e( 'The allowed extensions are pdf, doc, docx, xls, xlsx, odt, ppt, pptx, pps, ppsx, jpg, jpeg, png, gif, bmp, mp3, mp4, ogg, wav, mp4, m4v, mov, wmv, avi, mpg, ogv, 3gp, txt, zip, rar, 7z, csv', 'admin-site-enhancements' ); ?></label>
            </div>

            <div class="fb-form-row">
                <label><?php esc_html_e( 'Maximum File Size Allowed to Upload (MB)', 'admin-site-enhancements' ); ?></label>
                <input type="number" name="field_options[max_upload_size_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['max_upload_size'] ); ?>" />
            </div>

            <div class="fb-form-row">
                <label>
                    <input type="hidden" name="field_options[multiple_uploads_<?php echo absint( $field_id ); ?>]" value="off" />
                    <input type="checkbox" name="field_options[multiple_uploads_<?php echo absint( $field_id ); ?>]" value="on" data-condition="toggle" id="fb-multiple-uploads-<?php echo absint( $field_id ); ?>" <?php checked( $field['multiple_uploads'], 'on' ); ?> />
                    <?php esc_html_e( 'Multiple Uploads', 'admin-site-enhancements' ); ?>
                </label>
            </div>

            <div class="fb-form-row" data-condition-toggle="fb-multiple-uploads-<?php echo absint( $field_id ); ?>">
                <label>
                    <?php esc_html_e( 'Multiple Uploads Limit', 'admin-site-enhancements' ); ?>
                    <input type="number" name="field_options[multiple_uploads_limit_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['multiple_uploads_limit'] ); ?>" />
                </label>
            </div>
            <?php
        }

        if ( $field_type === 'select' || $field_type === 'radio' || $field_type === 'checkbox' || $field_type === 'image_select' ) {
            $this->show_field_choices( '', 'default' );
        }

        if ( $field_type === 'likert_matrix_scale' ) {
            $this->show_field_choices( __( 'Rows', 'admin-site-enhancements' ), 'rows' );
            $this->show_field_choices( __( 'Columns', 'admin-site-enhancements' ), 'columns' );
        }

        if ( $field_type === 'matrix_of_dropdowns' ) {
            $this->show_field_choices( __( 'Rows', 'admin-site-enhancements' ), 'rows' );
            $this->show_field_choices( __( 'Columns', 'admin-site-enhancements' ), 'columns' );
            $this->show_field_choices( __( 'Dropdown Options', 'admin-site-enhancements' ), 'dropdowns' );
        }

        if ( $field_type === 'matrix_of_variable_dropdowns_two' ) {
            $this->show_field_choices( __( 'Rows', 'admin-site-enhancements' ), 'rows' );
            $this->show_field_choices( __( 'Columns', 'admin-site-enhancements' ), 'columns' );
            $this->show_field_choices( __( 'First Dropdown Options', 'admin-site-enhancements' ), 'first_dropdown' );
            $this->show_field_choices( __( 'Second Dropdown Options', 'admin-site-enhancements' ), 'second_dropdown' );
        }

        if ( $field_type === 'matrix_of_variable_dropdowns_three' ) {
            $this->show_field_choices( __( 'Rows', 'admin-site-enhancements' ), 'rows' );
            $this->show_field_choices( __( 'Columns', 'admin-site-enhancements' ), 'columns' );
            $this->show_field_choices( __( 'First Dropdown Options', 'admin-site-enhancements' ), 'first_dropdown' );
            $this->show_field_choices( __( 'Second Dropdown Options', 'admin-site-enhancements' ), 'second_dropdown' );
            $this->show_field_choices( __( 'Third Dropdown Options', 'admin-site-enhancements' ), 'third_dropdown' );
        }

        if ( $field_type === 'matrix_of_variable_dropdowns_four' ) {
            $this->show_field_choices( __( 'Rows', 'admin-site-enhancements' ), 'rows' );
            $this->show_field_choices( __( 'Columns', 'admin-site-enhancements' ), 'columns' );
            $this->show_field_choices( __( 'First Dropdown Options', 'admin-site-enhancements' ), 'first_dropdown' );
            $this->show_field_choices( __( 'Second Dropdown Options', 'admin-site-enhancements' ), 'second_dropdown' );
            $this->show_field_choices( __( 'Third Dropdown Options', 'admin-site-enhancements' ), 'third_dropdown' );
            $this->show_field_choices( __( 'Fourth Dropdown Options', 'admin-site-enhancements' ), 'fourth_dropdown' );
        }

        if ( $field_type === 'matrix_of_variable_dropdowns_five' ) {
            $this->show_field_choices( __( 'Rows', 'admin-site-enhancements' ), 'rows' );
            $this->show_field_choices( __( 'Columns', 'admin-site-enhancements' ), 'columns' );
            $this->show_field_choices( __( 'First Dropdown Options', 'admin-site-enhancements' ), 'first_dropdown' );
            $this->show_field_choices( __( 'Second Dropdown Options', 'admin-site-enhancements' ), 'second_dropdown' );
            $this->show_field_choices( __( 'Third Dropdown Options', 'admin-site-enhancements' ), 'third_dropdown' );
            $this->show_field_choices( __( 'Fourth Dropdown Options', 'admin-site-enhancements' ), 'fourth_dropdown' );
            $this->show_field_choices( __( 'Fifth Dropdown Options', 'admin-site-enhancements' ), 'fifth_dropdown' );
        }
        
        if ( $display['auto_width'] ) {
            ?>
            <div class="fb-form-row">
                <label>
                    <input type="hidden" name="field_options[auto_width_<?php echo absint( $field_id ); ?>]" value="off" />
                    <input type="checkbox" name="field_options[auto_width_<?php echo absint( $field_id ); ?>]" value="on" <?php checked( $field['auto_width'], 'on' ); ?> />
                    <?php esc_html_e( 'Automatic Width', 'admin-site-enhancements' ); ?>
                </label>
            </div>
            <?php
        }

        $this->show_after_default();
        
        if ( $display['clear_on_focus'] ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Placeholder', 'admin-site-enhancements' ); ?></label>
                <?php
                if ( $field_type === 'textarea' ) {
                    ?>
                    <textarea id="fb-placeholder-<?php echo absint( $field_id ); ?>" name="field_options[placeholder_<?php echo absint( $field_id ); ?>]" rows="3" data-changeme="fb-field-<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="placeholder"><?php echo esc_textarea( $field['placeholder'] ); ?></textarea>
                    <?php
                } else {
                    if ( $field_type === 'select' ) {
                        if ( empty( $field['placeholder'] ) ) {
                            $field['placeholder'] = __( 'Choose one', 'admin-site-enhancements' );
                        }
                    }
                    ?>
                    <input id="fb-placeholder-<?php echo absint( $field_id ); ?>" type="text" name="field_options[placeholder_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['placeholder'] ); ?>" data-changeme="fb-field-<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="placeholder" />
                    <?php
                }
                ?>
            </div>
            <?php
        }

        if ( $display['required'] ) {
            ?>
            <div class="fb-form-row fb-required-detail-<?php echo esc_attr( $field_id ) . ( $field['required'] ? '' : ' fb-hidden' ); ?> always-hide">
                <label><?php esc_html_e( 'Required Field Indicator', 'admin-site-enhancements' ); ?></label>
                <input type="text" name="field_options[required_indicator_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['required_indicator'] ); ?>" data-changeme="fb-editor-field-required-<?php echo absint( $field_id ); ?>" />
            </div>
            <?php
        }

        if ( $field_type === 'radio' || $field_type === 'checkbox' || $field_type === 'image_select' ) {
            ?>
            <div class="fb-form-row fb-grid-3">
                <label><?php esc_html_e( 'Options Layout', 'admin-site-enhancements' ); ?></label>
                <select name="field_options[options_layout_<?php echo absint( $field_id ); ?>]">
                    <option value="inline" <?php selected( $field['options_layout'], 'inline' ); ?>>
                        <?php esc_html_e( 'Inline', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="1" <?php selected( $field['options_layout'], '1' ); ?>>
                        <?php esc_html_e( '1 Column', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="2" <?php selected( $field['options_layout'], '2' ); ?>>
                        <?php esc_html_e( '2 Columns', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="3" <?php selected( $field['options_layout'], '3' ); ?>>
                        <?php esc_html_e( '3 Columns', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="4" <?php selected( $field['options_layout'], '4' ); ?>>
                        <?php esc_html_e( '4 Columns', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="5" <?php selected( $field['options_layout'], '5' ); ?>>
                        <?php esc_html_e( '5 Columns', 'admin-site-enhancements' ); ?>
                    </option>
                    <option value="6" <?php selected( $field['options_layout'], '6' ); ?>>
                        <?php esc_html_e( '6 Columns', 'admin-site-enhancements' ); ?>
                    </option>
                </select>
            </div>
            <?php
        }

        if ( $display['description'] ) {
            if ( $field_type !== 'altcha' && $field_type !== 'captcha' && $field_type !== 'turnstile' ) {
            ?>
            <div class="fb-form-row">
                <label><?php esc_html_e( 'Description', 'admin-site-enhancements' ); ?></label>
                <textarea name="field_options[description_<?php echo absint( $field_id ); ?>]" data-changeme="fb-field-desc-<?php echo absint( $field_id ); ?>"><?php echo esc_textarea( $field['description'] ); ?></textarea>
            </div>
            <?php
            }
        }
        
        if ( 'user_id' !== $field_type
            && 'hidden' !== $field_type
        ) {
        // Wrap options below in an "Advanced Options" accordion
        ?>
        <div class="accordion advanced-field-options-accordion">
            <div class="accordion__control">Advanced Options<span class="accordion__indicator"></span></div>
            <div class="accordion__panel">
                <div class="fb-grid-container">
        <?php
        }

            if ( $display['default'] ) {
                $field_type_attr_val = 'text';
                if ( $field_type == 'range_slider' || $field_type == 'number' || $field_type == 'spinner' ) {
                    $field_type_attr_val = 'number';
                }

                if ( $field_type == 'email' ) {
                    $field_type_attr_val = 'email';
                }
                ?>
                <div class="fb-form-row">
                    <label><?php esc_html_e( 'Default Value', 'admin-site-enhancements' ); ?></label>
                    <input type="<?php echo esc_attr( $field_type_attr_val ); ?>" name="<?php echo 'default_value_' . absint( $field_id ); ?>" value="<?php echo esc_attr( $field['default_value'] ); ?>" class="fb-default-value-field" data-changeme="fb-field-<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="value" />
                    <?php if ( $field_type == 'hidden' ): ?>
                    <p class="description"><?php echo __( 'Use <strong>#page_title</strong> for the title of the page the form is displayed on.', 'admin-site-enhancements' ); ?>. <br /><?php echo __( 'Use <strong>#page_url</strong> for the URL of the page the form is displayed on.', 'admin-site-enhancements' ); ?></p>
                    <?php endif; ?>
                </div>
                <?php
            }
            
            if ( $display['format'] ) {
                ?>
                <div class="fb-form-row">
                    <label><?php esc_html_e( 'Format', 'admin-site-enhancements' ); ?></label>
                    <input type="text" value="<?php echo esc_attr( $field['format'] ); ?>" name="field_options[format_<?php echo absint( $field_id ); ?>]" data-fid="<?php echo absint( $field_id ); ?>" />
                    <p class="description"><?php esc_html_e( 'Enter a regex format to validate.', 'admin-site-enhancements' ); ?> <a href="https://www.phpliveregex.com" target="_blank"><?php esc_html_e( 'Generate Regex', 'admin-site-enhancements' ); ?></a></p>
                </div>
                <?php
            }
            
            if ( $display['max'] ) {
                ?>
                <div class="fb-form-row fb-grid-3">
                    <label><?php esc_html_e( 'Max Characters', 'admin-site-enhancements' ); ?></label>
                    <input type="number" name="field_options[max_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo esc_attr( $field['max'] ); ?>" size="5" data-fid="<?php echo absint( $field_id ); ?>" />
                </div>
                <?php
            }

            if ( $display['max_width'] ) {
                ?>
                <div class="fb-form-row">
                    <label><?php esc_html_e( 'Max Width', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-form-input-unit">
                        <input type="number" name="field_options[field_max_width_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo ( isset( $field['field_max_width'] ) ? esc_attr( $field['field_max_width'] ) : '' ); ?>" />

                        <select name="field_options[field_max_width_unit_<?php echo esc_attr( $field_id ); ?>]">
                            <option value="%" <?php isset( $field['field_max_width_unit'] ) ? selected( $field['field_max_width_unit'], '%' ) : ''; ?>>
                                <?php esc_html_e( '%', 'admin-site-enhancements' ); ?>
                            </option>
                            <option value="px" <?php isset( $field['field_max_width_unit'] ) ? selected( $field['field_max_width_unit'], 'px' ) : ''; ?>>
                                <?php esc_html_e( 'px', 'admin-site-enhancements' ); ?>
                            </option>
                        </select>
                    </div>
                </div>
                <?php
            }

            if ( $display['image_max_width'] ) {
                ?>
                <div class="fb-form-row">
                    <label><?php esc_html_e( 'Image Max Width', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-form-input-unit">
                        <input type="number" name="field_options[image_max_width_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo ( isset( $field['image_max_width'] ) ? esc_attr( $field['image_max_width'] ) : '' ); ?>" />

                        <select name="field_options[image_max_width_unit_<?php echo esc_attr( $field_id ); ?>]">
                            <option value="%" <?php isset( $field['image_max_width_unit'] ) ? selected( $field['image_max_width_unit'], '%' ) : ''; ?>>
                                <?php esc_html_e( '%', 'admin-site-enhancements' ); ?>
                            </option>
                            <option value="px" <?php isset( $field['image_max_width_unit'] ) ? selected( $field['image_max_width_unit'], 'px' ) : ''; ?>>
                                <?php esc_html_e( 'px', 'admin-site-enhancements' ); ?>
                            </option>
                        </select>
                    </div>
                </div>
                <?php
            }

            if ( $display['field_alignment'] ) {
                $field_alignment = isset( $field['field_alignment'] ) ? esc_attr( $field['field_alignment'] ) : '';
                ?>
                <div class="fb-form-row">
                    <label><?php esc_html_e( 'Alignment', 'admin-site-enhancements' ); ?></label>
                    <select name="field_options[field_alignment_<?php echo esc_attr( $field_id ); ?>]">
                        <option value="left" <?php selected( $field_alignment, 'left' ); ?>>
                            <?php esc_html_e( 'Left', 'admin-site-enhancements' ); ?>
                        </option>
                        <option value="right" <?php selected( $field_alignment, 'right' ); ?>>
                            <?php esc_html_e( 'Right', 'admin-site-enhancements' ); ?>
                        </option>
                        <option value="center" <?php selected( $field_alignment, 'center' ); ?>>
                            <?php esc_html_e( 'Center', 'admin-site-enhancements' ); ?>
                        </option>
                    </select>
                    <label class="fb-field-desc"><?php esc_html_e( 'This option will only work if the Field Max Width is set and width is smaller than container.', 'admin-site-enhancements' ); ?></label>
                </div>
                <?php
            }

            if ( $display['css'] ) {
                ?>
                <div class="fb-form-row">
                    <label><?php esc_html_e( 'CSS Classes', 'admin-site-enhancements' ); ?></label>
                    <input type="text" name="field_options[classes_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['classes'] ); ?>" />
                </div>
                <?php
            }

            $has_validation = ( $display['invalid'] || $display['required'] );
            $has_invalid = $display['invalid'];

            if ( $field_type === 'upload' ) {
                $has_validation = true;
                $has_invalid = true;
            }

            if ( $has_validation ) {
                ?>
                <h4 class="fb-validation-header <?php echo ( $has_invalid ? 'fb-alway-show' : ( $field['required'] ? '' : ' fb-hidden' ) ); ?>"> <?php esc_html_e( 'Validation Messages', 'admin-site-enhancements' ); ?></h4>
                <?php
            }

            if ( $display['required'] ) {
                ?>
                <div class="fb-form-row fb-required-detail-<?php echo esc_attr( $field_id ) . ( $field['required'] ? '' : ' fb-hidden' ); ?>">
                    <label><?php esc_html_e( 'Required', 'admin-site-enhancements' ); ?></label>
                    <input type="text" name="field_options[blank_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo esc_attr( $field['blank'] ); ?>" />
                </div>
                <?php
            }

            if ( $display['invalid'] ) {
                ?>
                <div class="fb-form-row">
                    <label><?php esc_html_e( 'Invalid Format', 'admin-site-enhancements' ); ?></label>
                    <input type="text" name="field_options[invalid_<?php echo esc_attr( $field_id ); ?>]" value="<?php echo esc_attr( $field['invalid'] ); ?>" />
                </div>
                <?php
            }


            if ( $field_type === 'upload' ) {
                ?>
                <div class="fb-form-row">
                    <label><?php esc_html_e( 'Extensions', 'admin-site-enhancements' ); ?></label>
                    <input type="text" name="field_options[extensions_error_message_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['extensions_error_message'] ); ?>" />
                </div>

                <div class="fb-form-row" data-condition-toggle="fb-multiple-uploads-<?php echo absint( $field_id ); ?>">
                    <label><?php esc_html_e( 'Multiple Uploads', 'admin-site-enhancements' ); ?></label>
                    <input type="text" name="field_options[multiple_uploads_error_message_<?php echo absint( $field_id ); ?>]" value="<?php echo esc_attr( $field['multiple_uploads_error_message'] ); ?>" />
                </div>
                <?php
            }

        if ( 'user_id' !== $field_type
            && 'hidden' !== $field_type
        ) {
        ?>
                </div> <!-- .fb-grid-container -->
            </div> <!-- .accordion__panel -->
        </div> <!-- .accordion -->
        <?php
        }
        ?>

    </div>
</div>