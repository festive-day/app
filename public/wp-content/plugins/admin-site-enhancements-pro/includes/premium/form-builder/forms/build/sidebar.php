<?php
defined( 'ABSPATH' ) || die();
?>

<div class="fb-fields-sidebar">
    <div class="fb-fields-container">
        <ul id="fb-fields-tabs" class="fb-fields-tabs">
            <li class="fb-active-tab"><a href="#fb-add-fields-panel" id="fb-add-fields-tab"><?php echo wp_kses( Form_Builder_Icons::get( 'add_field' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?><span><?php esc_html_e( 'Add', 'admin-site-enhancements' ); ?></span></a></li>
            <li><a href="#fb-options-panel" id="fb-options-tab"><?php echo wp_kses( Form_Builder_Icons::get( 'edit_field' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?><span><?php esc_html_e( 'Edit', 'admin-site-enhancements' ); ?></span></a></li>
            <li><a href="#fb-meta-panel" id="fb-design-tab"><?php echo wp_kses( Form_Builder_Icons::get( 'form_options' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?><span><?php esc_html_e( 'Form', 'admin-site-enhancements' ); ?></span></a></li>
        </ul>

        <div class="fb-fields-panels">
            <div id="fb-add-fields-panel" class="fb-fields-panel">
                <?php
                Form_Builder_Helper::show_search_box( array(
                    'input_id' => 'field-list',
                    'placeholder' => esc_html__( 'Search Fields', 'admin-site-enhancements' ),
                    'tosearch' => 'fb-field-box',
                ) );

                $registered_fields = Form_Builder_Fields::field_selection();
                foreach ( $registered_fields as $category_slug => $category_details ) {
                ?>
                <div class="accordion fields-list-accordion <?php echo esc_attr( $category_slug ); ?>">
                    <div class="accordion__control"><?php echo esc_html( $category_details['label'] ); ?><span class="accordion__indicator"></span></div>
                    <div class="accordion__panel">
                        <ul class="fb-fields-list">
                            <?php
                                foreach ( $category_details['fields'] as $field_key => $field_type ) {
                                    ?>
                                    <li class="fb-field-box formbuilder_<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" data-field-name="<?php echo esc_attr( $field_type['name'] ); ?>">
                                        <a href="#" class="fb-add-field" title="<?php echo esc_html( $field_type['name'] ); ?>">
                                            <!-- <i class="<?php // echo esc_attr( $field_type['icon'] ); ?>"></i> -->
                                            <?php echo wp_kses( $field_type['svg'], Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?>
                                            <span><?php echo esc_html( $field_type['name'] ); ?></span>
                                        </a>
                                    </li>
                                    <?php
                                }
                            ?>
                        </ul>
                    </div>
                </div>
                <?php
                }
                ?>
            </div>

            <div id="fb-options-panel" class="fb-fields-panel">
                <div class="fb-fields-settings">
                    <div class="fb-no-field-placeholder">
                        <div class="fb-no-field-msg"><?php esc_html_e( 'Select a field to see the options', 'admin-site-enhancements' ); ?></div>
                    </div>
                </div>

                <form method="post" id="fb-fields-form">
                    <input type="hidden" name="id" id="fb-form-id" value="<?php echo esc_attr( $values['id'] ); ?>" />
                    <?php wp_nonce_field( 'formbuilder_save_form_nonce', 'formbuilder_save_form' ); ?>
                    <input type="hidden" id="fb-end-form-marker" />
                </form>
            </div>

            <div id="fb-meta-panel" class="fb-fields-panel">
                <form method="post" id="fb-meta-form">
                    <div class="fb-form-container fb-grid-container">
                        <div class="fb-form-row">
                            <label><?php esc_html_e( 'Title', 'admin-site-enhancements' ); ?></label>
                            <input type="text" name="title" value="<?php echo esc_attr( $values['name'] ); ?>">
                        </div>

                        <div class="fb-form-row">
                            <label>
                                <input type="checkbox" name="show_title" value="on" <?php isset( $values['show_title'] ) ? checked( $values['show_title'], 'on' ) : ''; ?> />
                                <?php esc_html_e( 'Show the form title', 'admin-site-enhancements' ); ?>
                            </label>
                        </div>

                        <div class="fb-form-row fb-grid-3">
                            <label><?php esc_html_e( 'Label Position', 'admin-site-enhancements' ); ?></label>
                            <select name="form_label_position">
                                <option value="top" <?php isset( $values['form_label_position'] ) ? selected( $values['form_label_position'], 'top' ) : ''; ?>>
                                    <?php esc_html_e( 'Top', 'admin-site-enhancements' ); ?>
                                </option>
                                <option value="left" <?php isset( $values['form_label_position'] ) ? selected( $values['form_label_position'], 'left' ) : ''; ?>>
                                    <?php esc_html_e( 'Left', 'admin-site-enhancements' ); ?>
                                </option>
                                <option value="right" <?php isset( $values['form_label_position'] ) ? selected( $values['form_label_position'], 'right' ) : ''; ?>>
                                    <?php esc_html_e( 'Right', 'admin-site-enhancements' ); ?>
                                </option>
                            </select>
                        </div>

                        <div class="fb-form-row fb-grid-3">
                            <label><?php esc_html_e( 'Label Alignment', 'admin-site-enhancements' ); ?></label>
                            <select name="form_label_alignment">
                                <option value="left" <?php isset( $values['form_label_alignment'] ) ? selected( $values['form_label_alignment'], 'left' ) : ''; ?>>
                                    <?php esc_html_e( 'Left', 'admin-site-enhancements' ); ?>
                                </option>
                                <option value="center" <?php isset( $values['form_label_alignment'] ) ? selected( $values['form_label_alignment'], 'center' ) : ''; ?>>
                                    <?php esc_html_e( 'Center', 'admin-site-enhancements' ); ?>
                                </option>
                                <option value="right" <?php isset( $values['form_label_alignment'] ) ? selected( $values['form_label_alignment'], 'right' ) : ''; ?>>
                                    <?php esc_html_e( 'Right', 'admin-site-enhancements' ); ?>
                                </option>
                            </select>
                        </div>
                        
                        <div class="fb-form-row">
                            <label><?php esc_html_e( 'Submit Button Text', 'admin-site-enhancements' ); ?></label>
                            <input type="text" name="submit_value" value="<?php echo isset( $values['submit_value'] ) ? esc_attr( $values['submit_value'] ) : ''; ?>" data-changeme="fb-editor-submit-button">
                        </div>

                        <div class="fb-form-row">
                            <label><?php esc_html_e( 'Submit Button Alignment', 'admin-site-enhancements' ); ?></label>
                            <select name="submit_btn_alignment">
                                <option value="left" <?php isset( $values['submit_btn_alignment'] ) ? selected( $values['submit_btn_alignment'], 'left' ) : ''; ?>>
                                    <?php esc_html_e( 'Left', 'admin-site-enhancements' ); ?>
                                </option>
                                <option value="right" <?php isset( $values['submit_btn_alignment'] ) ? selected( $values['submit_btn_alignment'], 'right' ) : ''; ?>>
                                    <?php esc_html_e( 'Right', 'admin-site-enhancements' ); ?>
                                </option>
                                <option value="center" <?php isset( $values['submit_btn_alignment'] ) ? selected( $values['submit_btn_alignment'], 'center' ) : ''; ?>>
                                    <?php esc_html_e( 'Center', 'admin-site-enhancements' ); ?>
                                </option>
                                <option value="stretch" <?php isset( $values['submit_btn_alignment'] ) ? selected( $values['submit_btn_alignment'], 'center' ) : ''; ?>>
                                    <?php esc_html_e( 'Stretch', 'admin-site-enhancements' ); ?>
                                </option>
                            </select>
                        </div>

                        <div class="accordion form-options-accordion">
                            <div class="accordion__control"><?php echo __( 'Advanced Options', 'admin-site-enhancements' ); ?><span class="accordion__indicator"></span></div>
                            <div class="accordion__panel">
                                <div class="fb-grid-container">
                                    <div class="fb-form-row">
                                        <label><?php esc_html_e( 'Description', 'admin-site-enhancements' ); ?></label>
                                        <textarea name="description"><?php echo esc_textarea( $values['description'] ); ?></textarea>
                                    </div>

                                    <div class="fb-form-row">
                                        <label>
                                            <input type="checkbox" name="show_description" value="on" <?php isset( $values['show_description'] ) ? checked( $values['show_description'], 'on' ) : ''; ?> />
                                            <?php esc_html_e( 'Show the form description', 'admin-site-enhancements' ); ?>
                                        </label>
                                    </div>
                                    
                                    <div class="fb-form-row">
                                        <label><?php esc_html_e( 'Required Field Indicator', 'admin-site-enhancements' ); ?></label>
                                        <input type="text" name="required_field_indicator" value="<?php echo isset( $values['required_field_indicator'] ) ? esc_attr( $values['required_field_indicator'] ) : ''; ?>" data-changeme="fb-field-required">
                                    </div>

                                    <div class="fb-form-row">
                                        <label><?php esc_html_e( 'CSS Class', 'admin-site-enhancements' ); ?></label>
                                        <input type="text" name="form_css_class" value="<?php echo isset( $values['form_css_class'] ) ? esc_attr( $values['form_css_class'] ) : ''; ?>">
                                    </div>

                                    <div class="fb-form-row">
                                        <label><?php esc_html_e( 'Submit Button CSS Class', 'admin-site-enhancements' ); ?></label>
                                        <input type="text" name="submit_btn_css_class" value="<?php echo isset( $values['submit_btn_css_class'] ) ? esc_attr( $values['submit_btn_css_class'] ) : ''; ?>">
                                    </div>
                                </div><!-- .fb-grid-container -->
                            </div><!-- .accordion__panel -->
                        </div><!-- .accordion -->


                    </div>
                </form>

                <div class="fb-hidden">
                    <?php wp_editor( '', 'fb-init-tinymce' ); ?>
                </div>
            </div>
        </div>
    </div>
</div>