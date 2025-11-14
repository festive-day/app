<?php
defined( 'ABSPATH' ) || die();
$id = get_the_ID();
?>

<div class="fb-settings-row fb-form-row">
    <label class="fb-setting-label"><?php esc_html_e( 'Choose form to preview', 'admin-site-enhancements' ); ?></label>
    <select id="fb-template-preview-form-id">
        <?php
        $forms = Form_Builder_Builder::get_all_forms();
        ?>
        <option value=""><?php esc_html_e( 'Default Demo Form', 'admin-site-enhancements' ); ?></option>
        <?php
        foreach ( $forms as $form ) {
            ?>
            <option value="<?php echo esc_attr( $form->id ); ?>"><?php echo esc_html( $form->name ); ?></option>
        <?php } ?>
    </select>
</div>

<h2 class="fb-settings-heading"><?php esc_html_e( 'Form', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>

<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Column Gap', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-form-column-gap" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[form][column_gap]" value="<?php echo is_numeric( $formbuilder_styles['form']['column_gap'] ) ? intval( $formbuilder_styles['form']['column_gap'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Row Gap', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-form-row-gap" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[form][row_gap]" value="<?php echo is_numeric( $formbuilder_styles['form']['row_gap'] ) ? intval( $formbuilder_styles['form']['row_gap'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Background Color', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-form-bg-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[form][bg_color]" value="<?php echo esc_attr( $formbuilder_styles['form']['bg_color'] ); ?>">
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Box Shadow', 'admin-site-enhancements' ) ?></label>
        <div class="fb-setting-fields">
            <ul class="fb-shadow-fields">
                <li class="fb-shadow-settings-field">
                    <input id="fb-form-shadow-x" data-unit="px" type="number" name="formbuilder_styles[form][shadow][x]" value="<?php echo esc_attr( $formbuilder_styles['form']['shadow']['x'] ); ?>">
                    <label><?php esc_html_e( 'H', 'admin-site-enhancements' ) ?></label>
                </li>
                <li class="fb-shadow-settings-field">
                    <input id="fb-form-shadow-y" data-unit="px" type="number" name="formbuilder_styles[form][shadow][y]" value="<?php echo esc_attr( $formbuilder_styles['form']['shadow']['y'] ); ?>">
                    <label><?php esc_html_e( 'V', 'admin-site-enhancements' ) ?></label>
                </li>
                <li class="fb-shadow-settings-field">
                    <input id="fb-form-shadow-blur" data-unit="px" type="number" name="formbuilder_styles[form][shadow][blur]" value="<?php echo esc_attr( $formbuilder_styles['form']['shadow']['blur'] ); ?>">
                    <label><?php esc_html_e( 'Blur', 'admin-site-enhancements' ) ?></label>
                </li>
                <li class="fb-shadow-settings-field">
                    <input id="fb-form-shadow-spread" data-unit="px" type="number" name="formbuilder_styles[form][shadow][spread]" value="<?php echo esc_attr( $formbuilder_styles['form']['shadow']['spread'] ); ?>">
                    <label><?php esc_html_e( 'Spread', 'admin-site-enhancements' ) ?></label>
                </li>
            </ul>
            <div class="fb-shadow-settings-field">
                <div class="fb-color-input-field">
                    <input id="fb-form-shadow-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[form][shadow][color]" value="<?php echo esc_attr( $formbuilder_styles['form']['shadow']['color'] ); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Border Color', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-form-border-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[form][border_color]" value="<?php echo esc_attr( $formbuilder_styles['form']['border_color'] ); ?>">
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Border Width', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-form-border-top" data-unit="px" type="number" name="formbuilder_styles[form][border][top]" value="<?php echo esc_attr( $formbuilder_styles['form']['border']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-border-right" data-unit="px" type="number" name="formbuilder_styles[form][border][right]" value="<?php echo esc_attr( $formbuilder_styles['form']['border']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-border-bottom" data-unit="px" type="number" name="formbuilder_styles[form][border][bottom]" value="<?php echo esc_attr( $formbuilder_styles['form']['border']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-border-left" data-unit="px" type="number" name="formbuilder_styles[form][border][left]" value="<?php echo esc_attr( $formbuilder_styles['form']['border']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Border Radius', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-form-border-radius-top" data-unit="px" type="number" name="formbuilder_styles[form][border_radius][top]" value="<?php echo esc_attr( $formbuilder_styles['form']['border_radius']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-border-radius-right" data-unit="px" type="number" name="formbuilder_styles[form][border_radius][right]" value="<?php echo esc_attr( $formbuilder_styles['form']['border_radius']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-border-radius-bottom" data-unit="px" type="number" name="formbuilder_styles[form][border_radius][bottom]" value="<?php echo esc_attr( $formbuilder_styles['form']['border_radius']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-border-radius-left" data-unit="px" type="number" name="formbuilder_styles[form][border_radius][left]" value="<?php echo esc_attr( $formbuilder_styles['form']['border_radius']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Padding', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-form-padding-top" data-unit="px" type="number" name="formbuilder_styles[form][padding][top]" value="<?php echo esc_attr( $formbuilder_styles['form']['padding']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-padding-right" data-unit="px" type="number" name="formbuilder_styles[form][padding][right]" value="<?php echo esc_attr( $formbuilder_styles['form']['padding']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-padding-bottom" data-unit="px" type="number" name="formbuilder_styles[form][padding][bottom]" value="<?php echo esc_attr( $formbuilder_styles['form']['padding']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-form-padding-left" data-unit="px" type="number" name="formbuilder_styles[form][padding][left]" value="<?php echo esc_attr( $formbuilder_styles['form']['padding']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>
</div>

<h2 class="fb-settings-heading"><?php esc_html_e( 'Labels', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>

<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'label' ); ?>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Bottom Spacing', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-label-spacing" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[label][spacing]" value="<?php echo is_numeric( $formbuilder_styles['label']['spacing'] ) ? intval( $formbuilder_styles['label']['spacing'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>
    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Required Text Color', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-label-required-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[label][required_color]" value="<?php echo esc_attr( $formbuilder_styles['label']['required_color'] ); ?>">
        </div>
    </div>
</div>


<h2 class="fb-settings-heading"><?php esc_html_e( 'Description', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'desc' ); ?>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Top Spacing', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-desc-spacing" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[desc][spacing]" value="<?php echo is_numeric( $formbuilder_styles['desc']['spacing'] ) ? intval( $formbuilder_styles['desc']['spacing'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>
</div>


<h2 class="fb-settings-heading"><?php esc_html_e( 'Fields', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>

<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'field', array( 'color' ) ); ?>
    </div>

    <div class="fb-tab-container">
        <ul class="fb-setting-tab">
            <li data-tab="fb-tab-normal" class="fb-tab-active"><?php esc_html_e( 'Normal', 'admin-site-enhancements' ); ?></li>
            <li data-tab="fb-tab-focus"><?php esc_html_e( 'Focus', 'admin-site-enhancements' ); ?></li>
        </ul>

        <div class="fb-setting-tab-panel">
            <div class="fb-tab-normal fb-tab-content">
                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-field-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[field][color_normal]" value="<?php echo esc_attr( $formbuilder_styles['field']['color_normal'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Background Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-field-bg-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[field][bg_color_normal]" value="<?php echo esc_attr( $formbuilder_styles['field']['bg_color_normal'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label"><?php esc_html_e( 'Box Shadow', 'admin-site-enhancements' ) ?></label>
                    <div class="fb-setting-fields">
                        <ul class="fb-shadow-fields">
                            <li class="fb-shadow-settings-field">
                                <input id="fb-field-shadow-normal-x" data-unit="px" type="number" name="formbuilder_styles[field][shadow_normal][x]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_normal']['x'] ); ?>">
                                <label><?php esc_html_e( 'H', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-field-shadow-normal-y" data-unit="px" type="number" name="formbuilder_styles[field][shadow_normal][y]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_normal']['y'] ); ?>">
                                <label><?php esc_html_e( 'V', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-field-shadow-normal-blur" data-unit="px" type="number" name="formbuilder_styles[field][shadow_normal][blur]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_normal']['blur'] ); ?>">
                                <label><?php esc_html_e( 'Blur', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-field-shadow-normal-spread" data-unit="px" type="number" name="formbuilder_styles[field][shadow_normal][spread]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_normal']['spread'] ); ?>">
                                <label><?php esc_html_e( 'Spread', 'admin-site-enhancements' ) ?></label>
                            </li>
                        </ul>
                        <div class="fb-shadow-settings-field">
                            <div class="fb-color-input-field">
                                <input id="fb-field-shadow-normal-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[field][shadow_normal][color]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_normal']['color'] ); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Border Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-field-border-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[field][border_color_normal]" value="<?php echo esc_attr( $formbuilder_styles['field']['border_color_normal'] ); ?>">
                    </div>
                </div>
            </div>

            <div class="fb-tab-focus fb-tab-content" style="display: none;">
                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color ( Focus )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-field-color-focus" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[field][color_focus]" value="<?php echo esc_attr( $formbuilder_styles['field']['color_focus'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Background Color ( Focus )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-field-bg-color-focus" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[field][bg_color_focus]" value="<?php echo esc_attr( $formbuilder_styles['field']['bg_color_focus'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label"><?php esc_html_e( 'Box Shadow ( Focus )', 'admin-site-enhancements' ) ?></label>
                    <div class="fb-setting-fields">
                        <ul class="fb-shadow-fields">
                            <li class="fb-shadow-settings-field">
                                <input id="fb-field-shadow-focus-x" data-unit="px" type="number" name="formbuilder_styles[field][shadow_focus][x]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_focus']['x'] ); ?>">
                                <label><?php esc_html_e( 'H', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-field-shadow-focus-y" data-unit="px" type="number" name="formbuilder_styles[field][shadow_focus][y]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_focus']['y'] ); ?>">
                                <label><?php esc_html_e( 'V', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-field-shadow-focus-blur" data-unit="px" type="number" name="formbuilder_styles[field][shadow_focus][blur]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_focus']['blur'] ); ?>">
                                <label><?php esc_html_e( 'Blur', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-field-shadow-focus-spread" data-unit="px" type="number" name="formbuilder_styles[field][shadow_focus][spread]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_focus']['spread'] ); ?>">
                                <label><?php esc_html_e( 'Spread', 'admin-site-enhancements' ) ?></label>
                            </li>
                        </ul>

                        <div class="fb-shadow-settings-field">
                            <div class="fb-color-input-field">
                                <input id="fb-field-shadow-focus-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[field][shadow_focus][color]" value="<?php echo esc_attr( $formbuilder_styles['field']['shadow_focus']['color'] ); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Border Color ( Focus )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-field-border-color-focus" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[field][border_color_focus]" value="<?php echo esc_attr( $formbuilder_styles['field']['border_color_focus'] ); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Border Width', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-field-border-top" data-unit="px" type="number" name="formbuilder_styles[field][border][top]" value="<?php echo esc_attr( $formbuilder_styles['field']['border']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-border-right" data-unit="px" type="number" name="formbuilder_styles[field][border][right]" value="<?php echo esc_attr( $formbuilder_styles['field']['border']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-border-bottom" data-unit="px" type="number" name="formbuilder_styles[field][border][bottom]" value="<?php echo esc_attr( $formbuilder_styles['field']['border']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-border-left" data-unit="px" type="number" name="formbuilder_styles[field][border][left]" value="<?php echo esc_attr( $formbuilder_styles['field']['border']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Border Radius', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-field-border-radius-top" data-unit="px" type="number" name="formbuilder_styles[field][border_radius][top]" value="<?php echo esc_attr( $formbuilder_styles['field']['border_radius']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-border-radius-right" data-unit="px" type="number" name="formbuilder_styles[field][border_radius][right]" value="<?php echo esc_attr( $formbuilder_styles['field']['border_radius']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-border-radius-bottom" data-unit="px" type="number" name="formbuilder_styles[field][border_radius][bottom]" value="<?php echo esc_attr( $formbuilder_styles['field']['border_radius']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-border-radius-left" data-unit="px" type="number" name="formbuilder_styles[field][border_radius][left]" value="<?php echo esc_attr( $formbuilder_styles['field']['border_radius']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Padding', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-field-padding-top" data-unit="px" type="number" name="formbuilder_styles[field][padding][top]" value="<?php echo esc_attr( $formbuilder_styles['field']['padding']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-padding-right" data-unit="px" type="number" name="formbuilder_styles[field][padding][right]" value="<?php echo esc_attr( $formbuilder_styles['field']['padding']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-padding-bottom" data-unit="px" type="number" name="formbuilder_styles[field][padding][bottom]" value="<?php echo esc_attr( $formbuilder_styles['field']['padding']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-field-padding-left" data-unit="px" type="number" name="formbuilder_styles[field][padding][left]" value="<?php echo esc_attr( $formbuilder_styles['field']['padding']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>
</div>

<h2 class="fb-settings-heading"><?php esc_html_e( 'Upload Button', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-tab-container">
        <ul class="fb-setting-tab">
            <li data-tab="fb-tab-normal" class="fb-tab-active"><?php esc_html_e( 'Normal', 'admin-site-enhancements' ); ?></li>
            <li data-tab="fb-tab-hover"><?php esc_html_e( 'Hover', 'admin-site-enhancements' ); ?></li>
        </ul>

        <div class="fb-setting-tab-panel">
            <div class="fb-tab-normal fb-tab-content">
                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-upload-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[upload][color_normal]" value="<?php echo esc_attr( $formbuilder_styles['upload']['color_normal'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Background Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-upload-bg-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[upload][bg_color_normal]" value="<?php echo esc_attr( $formbuilder_styles['upload']['bg_color_normal'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label"><?php esc_html_e( 'Box Shadow', 'admin-site-enhancements' ) ?></label>
                    <div class="fb-setting-fields">
                        <ul class="fb-shadow-fields">
                            <li class="fb-shadow-settings-field">
                                <input id="fb-upload-shadow-normal-x" data-unit="px" type="number" name="formbuilder_styles[upload][shadow_normal][x]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_normal']['x'] ); ?>">
                                <label><?php esc_html_e( 'H', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-upload-shadow-normal-y" data-unit="px" type="number" name="formbuilder_styles[upload][shadow_normal][y]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_normal']['y'] ); ?>">
                                <label><?php esc_html_e( 'V', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-upload-shadow-normal-blur" data-unit="px" type="number" name="formbuilder_styles[upload][shadow_normal][blur]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_normal']['blur'] ); ?>">
                                <label><?php esc_html_e( 'Blur', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-upload-shadow-normal-spread" data-unit="px" type="number" name="formbuilder_styles[upload][shadow_normal][spread]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_normal']['spread'] ); ?>">
                                <label><?php esc_html_e( 'Spread', 'admin-site-enhancements' ) ?></label>
                            </li>
                        </ul>
                        <div class="fb-shadow-settings-field">
                            <div class="fb-color-input-field">
                                <input id="fb-upload-shadow-normal-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[upload][shadow_normal][color]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_normal']['color'] ); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Border Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-upload-border-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[upload][border_color_normal]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border_color_normal'] ); ?>">
                    </div>
                </div>
            </div>

            <div class="fb-tab-hover fb-tab-content" style="display: none;">
                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color (Hover )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-upload-color-hover" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[upload][color_hover]" value="<?php echo esc_attr( $formbuilder_styles['upload']['color_hover'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Background Color (Hover )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-upload-bg-color-hover" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[upload][bg_color_hover]" value="<?php echo esc_attr( $formbuilder_styles['upload']['bg_color_hover'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label"><?php esc_html_e( 'Box Shadow (Hover )', 'admin-site-enhancements' ) ?></label>
                    <div class="fb-setting-fields">
                        <ul class="fb-shadow-fields">
                            <li class="fb-shadow-settings-field">
                                <input id="fb-upload-shadow-hover-x" data-unit="px" type="number" name="formbuilder_styles[upload][shadow_hover][x]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_hover']['x'] ); ?>">
                                <label><?php esc_html_e( 'H', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-upload-shadow-hover-y" data-unit="px" type="number" name="formbuilder_styles[upload][shadow_hover][y]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_hover']['y'] ); ?>">
                                <label><?php esc_html_e( 'V', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-upload-shadow-hover-blur" data-unit="px" type="number" name="formbuilder_styles[upload][shadow_hover][blur]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_hover']['blur'] ); ?>">
                                <label><?php esc_html_e( 'Blur', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-upload-shadow-hover-spread" data-unit="px" type="number" name="formbuilder_styles[upload][shadow_hover][spread]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_hover']['spread'] ); ?>">
                                <label><?php esc_html_e( 'Spread', 'admin-site-enhancements' ) ?></label>
                            </li>
                        </ul>
                        <div class="fb-shadow-settings-field">
                            <div class="fb-color-input-field">
                                <input id="fb-upload-shadow-hover-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[upload][shadow_hover][color]" value="<?php echo esc_attr( $formbuilder_styles['upload']['shadow_hover']['color'] ); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Border Color (Hover )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-upload-border-color-hover" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[upload][border_color_hover]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border_color_hover'] ); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Border Width', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-upload-border-top" data-unit="px" type="number" name="formbuilder_styles[upload][border][top]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-border-right" data-unit="px" type="number" name="formbuilder_styles[upload][border][right]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-border-bottom" data-unit="px" type="number" name="formbuilder_styles[upload][border][bottom]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-border-left" data-unit="px" type="number" name="formbuilder_styles[upload][border][left]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Border Radius', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-upload-border-radius-top" data-unit="px" type="number" name="formbuilder_styles[upload][border_radius][top]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border_radius']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-border-radius-right" data-unit="px" type="number" name="formbuilder_styles[upload][border_radius][right]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border_radius']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-border-radius-bottom" data-unit="px" type="number" name="formbuilder_styles[upload][border_radius][bottom]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border_radius']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-border-radius-left" data-unit="px" type="number" name="formbuilder_styles[upload][border_radius][left]" value="<?php echo esc_attr( $formbuilder_styles['upload']['border_radius']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Padding', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-upload-padding-top" data-unit="px" type="number" name="formbuilder_styles[upload][padding][top]" value="<?php echo esc_attr( $formbuilder_styles['upload']['padding']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-padding-right" data-unit="px" type="number" name="formbuilder_styles[upload][padding][right]" value="<?php echo esc_attr( $formbuilder_styles['upload']['padding']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-padding-bottom" data-unit="px" type="number" name="formbuilder_styles[upload][padding][bottom]" value="<?php echo esc_attr( $formbuilder_styles['upload']['padding']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-upload-padding-left" data-unit="px" type="number" name="formbuilder_styles[upload][padding][left]" value="<?php echo esc_attr( $formbuilder_styles['upload']['padding']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>
</div>

<h2 class="fb-settings-heading"><?php esc_html_e( 'Submit Button', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'button', array( 'color' ) ); ?>
    </div>

    <div class="fb-tab-container">
        <ul class="fb-setting-tab">
            <li data-tab="fb-tab-normal" class="fb-tab-active"><?php esc_html_e( 'Normal', 'admin-site-enhancements' ); ?></li>
            <li data-tab="fb-tab-hover"><?php esc_html_e( 'Hover', 'admin-site-enhancements' ); ?></li>
        </ul>

        <div class="fb-setting-tab-panel">
            <div class="fb-tab-normal fb-tab-content">
                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-button-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[button][color_normal]" value="<?php echo esc_attr( $formbuilder_styles['button']['color_normal'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Background Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-button-bg-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[button][bg_color_normal]" value="<?php echo esc_attr( $formbuilder_styles['button']['bg_color_normal'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label"><?php esc_html_e( 'Box Shadow', 'admin-site-enhancements' ) ?></label>
                    <div class="fb-setting-fields">
                        <ul class="fb-shadow-fields">
                            <li class="fb-shadow-settings-field">
                                <input id="fb-button-shadow-normal-x" data-unit="px" type="number" name="formbuilder_styles[button][shadow_normal][x]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_normal']['x'] ); ?>">
                                <label><?php esc_html_e( 'H', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-button-shadow-normal-y" data-unit="px" type="number" name="formbuilder_styles[button][shadow_normal][y]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_normal']['y'] ); ?>">
                                <label><?php esc_html_e( 'V', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-button-shadow-normal-blur" data-unit="px" type="number" name="formbuilder_styles[button][shadow_normal][blur]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_normal']['blur'] ); ?>">
                                <label><?php esc_html_e( 'Blur', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-button-shadow-normal-spread" data-unit="px" type="number" name="formbuilder_styles[button][shadow_normal][spread]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_normal']['spread'] ); ?>">
                                <label><?php esc_html_e( 'Spread', 'admin-site-enhancements' ) ?></label>
                            </li>
                        </ul>
                        <div class="fb-shadow-settings-field">
                            <div class="fb-color-input-field">
                                <input id="fb-button-shadow-normal-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[button][shadow_normal][color]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_normal']['color'] ); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Border Color', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-button-border-color-normal" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[button][border_color_normal]" value="<?php echo esc_attr( $formbuilder_styles['button']['border_color_normal'] ); ?>">
                    </div>
                </div>
            </div>

            <div class="fb-tab-hover fb-tab-content" style="display: none;">
                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color (Hover )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-button-color-hover" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[button][color_hover]" value="<?php echo esc_attr( $formbuilder_styles['button']['color_hover'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Background Color (Hover )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-button-bg-color-hover" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[button][bg_color_hover]" value="<?php echo esc_attr( $formbuilder_styles['button']['bg_color_hover'] ); ?>">
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label"><?php esc_html_e( 'Box Shadow (Hover )', 'admin-site-enhancements' ) ?></label>
                    <div class="fb-setting-fields">
                        <ul class="fb-shadow-fields">
                            <li class="fb-shadow-settings-field">
                                <input id="fb-button-shadow-hover-x" data-unit="px" type="number" name="formbuilder_styles[button][shadow_hover][x]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_hover']['x'] ); ?>">
                                <label><?php esc_html_e( 'H', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-button-shadow-hover-y" data-unit="px" type="number" name="formbuilder_styles[button][shadow_hover][y]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_hover']['y'] ); ?>">
                                <label><?php esc_html_e( 'V', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-button-shadow-hover-blur" data-unit="px" type="number" name="formbuilder_styles[button][shadow_hover][blur]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_hover']['blur'] ); ?>">
                                <label><?php esc_html_e( 'Blur', 'admin-site-enhancements' ) ?></label>
                            </li>
                            <li class="fb-shadow-settings-field">
                                <input id="fb-button-shadow-hover-spread" data-unit="px" type="number" name="formbuilder_styles[button][shadow_hover][spread]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_hover']['spread'] ); ?>">
                                <label><?php esc_html_e( 'Spread', 'admin-site-enhancements' ) ?></label>
                            </li>
                        </ul>
                        <div class="fb-shadow-settings-field">
                            <div class="fb-color-input-field">
                                <input id="fb-button-shadow-hover-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[button][shadow_hover][color]" value="<?php echo esc_attr( $formbuilder_styles['button']['shadow_hover']['color'] ); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fb-settings-row">
                    <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Border Color (Hover )', 'admin-site-enhancements' ); ?></label>
                    <div class="fb-setting-fields fb-color-input-field">
                        <input id="fb-button-border-color-hover" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[button][border_color_hover]" value="<?php echo esc_attr( $formbuilder_styles['button']['border_color_hover'] ); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Border Width', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-button-border-top" data-unit="px" type="number" name="formbuilder_styles[button][border][top]" value="<?php echo esc_attr( $formbuilder_styles['button']['border']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-border-right" data-unit="px" type="number" name="formbuilder_styles[button][border][right]" value="<?php echo esc_attr( $formbuilder_styles['button']['border']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-border-bottom" data-unit="px" type="number" name="formbuilder_styles[button][border][bottom]" value="<?php echo esc_attr( $formbuilder_styles['button']['border']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-border-left" data-unit="px" type="number" name="formbuilder_styles[button][border][left]" value="<?php echo esc_attr( $formbuilder_styles['button']['border']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Border Radius', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-button-border-radius-top" data-unit="px" type="number" name="formbuilder_styles[button][border_radius][top]" value="<?php echo esc_attr( $formbuilder_styles['button']['border_radius']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-border-radius-right" data-unit="px" type="number" name="formbuilder_styles[button][border_radius][right]" value="<?php echo esc_attr( $formbuilder_styles['button']['border_radius']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-border-radius-bottom" data-unit="px" type="number" name="formbuilder_styles[button][border_radius][bottom]" value="<?php echo esc_attr( $formbuilder_styles['button']['border_radius']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-border-radius-left" data-unit="px" type="number" name="formbuilder_styles[button][border_radius][left]" value="<?php echo esc_attr( $formbuilder_styles['button']['border_radius']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Padding', 'admin-site-enhancements' ) ?></label>
        <ul class="fb-unit-fields">
            <li class="fb-unit-settings-field">
                <input id="fb-button-padding-top" data-unit="px" type="number" name="formbuilder_styles[button][padding][top]" value="<?php echo esc_attr( $formbuilder_styles['button']['padding']['top'] ); ?>" min="0">
                <label><?php esc_html_e( 'Top', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-padding-right" data-unit="px" type="number" name="formbuilder_styles[button][padding][right]" value="<?php echo esc_attr( $formbuilder_styles['button']['padding']['right'] ); ?>" min="0">
                <label><?php esc_html_e( 'Right', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-padding-bottom" data-unit="px" type="number" name="formbuilder_styles[button][padding][bottom]" value="<?php echo esc_attr( $formbuilder_styles['button']['padding']['bottom'] ); ?>" min="0">
                <label><?php esc_html_e( 'Bottom', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <input id="fb-button-padding-left" data-unit="px" type="number" name="formbuilder_styles[button][padding][left]" value="<?php echo esc_attr( $formbuilder_styles['button']['padding']['left'] ); ?>" min="0">
                <label><?php esc_html_e( 'Left', 'admin-site-enhancements' ) ?></label>
            </li>
            <li class="fb-unit-settings-field">
                <div class="fb-link-button">
                    <span class="dashicons dashicons-admin-links fb-linked"></span>
                    <span class="dashicons dashicons-editor-unlink fb-unlinked"></span>
                </div>
            </li>
        </ul>
    </div>
</div>


<h2 class="fb-settings-heading"><?php esc_html_e( 'Validation', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'validation' ); ?>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Top Spacing', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-validation-spacing" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[validation][spacing]" value="<?php echo is_numeric( $formbuilder_styles['validation']['spacing'] ) ? intval( $formbuilder_styles['validation']['spacing'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Text Align', 'admin-site-enhancements' ) ?></label>
        <select id="fb-validation-textalign" name="formbuilder_styles[validation][textalign]">
            <option value="left" <?php selected( $formbuilder_styles['validation']['textalign'], 'left' ); ?>><?php esc_html_e( 'Left', 'admin-site-enhancements' ); ?></option>
            <option value="center" <?php selected( $formbuilder_styles['validation']['textalign'], 'center' ); ?>><?php esc_html_e( 'Center', 'admin-site-enhancements' ); ?></option>
            <option value="right" <?php selected( $formbuilder_styles['validation']['textalign'], 'right' ); ?>><?php esc_html_e( 'Right', 'admin-site-enhancements' ); ?></option>
        </select>
    </div>
</div>


<h2 class="fb-settings-heading"><?php esc_html_e( 'Form Title', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'form_title' ); ?>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Bottom Spacing', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-form-title-spacing" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[form_title][spacing]" value="<?php echo is_numeric( $formbuilder_styles['form_title']['spacing'] ) ? intval( $formbuilder_styles['form_title']['spacing'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>
</div>


<h2 class="fb-settings-heading"><?php esc_html_e( 'Form Description', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'form_desc' ); ?>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Bottom Spacing', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-form-desc-spacing" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[form_desc][spacing]" value="<?php echo is_numeric( $formbuilder_styles['form_desc']['spacing'] ) ? intval( $formbuilder_styles['form_desc']['spacing'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>
</div>


<h2 class="fb-settings-heading"><?php esc_html_e( 'Heading', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'heading' ); ?>
    </div>
</div>

<h2 class="fb-settings-heading"><?php esc_html_e( 'Paragraph', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Typography', 'admin-site-enhancements' ); ?></label>
        <?php self::get_typography_fields( 'formbuilder_styles', $formbuilder_styles, 'paragraph' ); ?>
    </div>
</div>

<h2 class="fb-settings-heading"><?php esc_html_e( 'Divider', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-divider-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[divider_color]" value="<?php echo esc_attr( $formbuilder_styles['divider_color'] ); ?>">
        </div>
    </div>
</div>

<h2 class="fb-settings-heading"><?php esc_html_e( 'Star', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Size', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-star-size" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[star][size]" value="<?php echo is_numeric( $formbuilder_styles['star']['size'] ) ? intval( $formbuilder_styles['star']['size'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>
    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-star-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[star][color]" value="<?php echo esc_attr( $formbuilder_styles['star']['color'] ); ?>">
        </div>
    </div>
    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Color (Active )', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-star-color-active" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[star][color_active]" value="<?php echo esc_attr( $formbuilder_styles['star']['color_active'] ); ?>">
        </div>
    </div>
</div>

<h2 class="fb-settings-heading"><?php esc_html_e( 'Range Slider', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Height', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-range-height" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[range][height]" value="<?php echo is_numeric( $formbuilder_styles['range']['height'] ) ? intval( $formbuilder_styles['range']['height'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>
    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Handle Size', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields formbuilder-range-slider-wrap">
            <div class="formbuilder-range-slider"></div>
            <input data-unit="px" id="fb-range-handle-size" class="formbuilder-range-input-selector" type="number" name="formbuilder_styles[range][handle_size]" value="<?php echo is_numeric( $formbuilder_styles['range']['handle_size'] ) ? intval( $formbuilder_styles['range']['handle_size'] ) : ''; ?>" min="0" max="100" step="1"> px
        </div>
    </div>
    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Bar Color', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-range-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[range][color]" value="<?php echo esc_attr( $formbuilder_styles['range']['color'] ); ?>">
        </div>
    </div>
    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Bar Color (Active )', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-range-color-active" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[range][color_active]" value="<?php echo esc_attr( $formbuilder_styles['range']['color_active'] ); ?>">
        </div>
    </div>
    <div class="fb-settings-row">
        <label class="fb-setting-label fb-color-input-label"><?php esc_html_e( 'Handle Color', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields fb-color-input-field">
            <input id="fb-range-handle-color" type="text" class="color-picker fb-color-picker" data-alpha-enabled="true" data-alpha-custom-width="30px" data-alpha-color-type="hex" name="formbuilder_styles[range][handle_color]" value="<?php echo esc_attr( $formbuilder_styles['range']['handle_color'] ); ?>">
        </div>
    </div>
</div>

<?php do_action( 'formbuilder_styles_settings', $formbuilder_styles ); ?>


<h2 class="fb-settings-heading"><?php esc_html_e( 'Import/Export', 'admin-site-enhancements' ); ?><span class="fb fb-triangle-small-down"><?php echo wp_kses( Form_Builder_Icons::get( 'triangle_down' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></h2>
<div class="fb-form-settings">
    <p>
        <?php esc_html_e("You can export the form styles and then import the form styles in the same or different website.", "admin-site-enhancements"); ?>
    </p>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Export', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields">
            <form method="post"></form>
            <form method="post">
                <input type="hidden" name="formbuilder_imex_action" value="export_style" />
                <input type="hidden" name="formbuilder_style_id" value="<?php echo esc_attr( $id ); ?>" />
                <?php wp_nonce_field("formbuilder_imex_export_nonce", "formbuilder_imex_export_nonce"); ?>
                <button class="button button-primary" id="formbuilder_export" name="formbuilder_export"><?php esc_html_e("Export Style", "admin-site-enhancements") ?></button>
            </form>
        </div>
    </div>

    <div class="fb-settings-row">
        <label class="fb-setting-label"><?php esc_html_e( 'Import', 'admin-site-enhancements' ); ?></label>
        <div class="fb-setting-fields">
            <form method="post" enctype="multipart/form-data">
                <div class="fb-preview-zone hidden">
                    <div class="fb-box fb-box-solid">
                        <div class="fb-box-body"></div>
                        <button type="button" class="button fb-remove-preview">
                            <span class="fb fb-window-close"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                        </button>
                    </div>
                </div>
                <div class="fb-dropzone-wrapper">
                    <div class="fb-dropzone-desc">
                        <span class="fb fb-file-image-plus-outline"><?php echo wp_kses( Form_Builder_Icons::get( 'file_generic' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                        <p><?php esc_html_e("Choose a JSON file or drag it here", "admin-site-enhancements"); ?></p>
                    </div>
                    <input type="file" name="formbuilder_import_file" class="fb-dropzone">
                </div>
                <button class="button button-primary" id="formbuilder_import" type="submit" name="formbuilder_import"><i class='icofont-download'></i> <?php esc_html_e("Import", "admin-site-enhancements") ?></button>
                <input type="hidden" name="formbuilder_imex_action" value="import_style" />
                <input type="hidden" name="formbuilder_style_id" value="<?php echo esc_attr( $id ); ?>" />
                <?php wp_nonce_field("formbuilder_imex_import_nonce", "formbuilder_imex_import_nonce"); ?>
            </form>
        </div>
    </div>
</div>