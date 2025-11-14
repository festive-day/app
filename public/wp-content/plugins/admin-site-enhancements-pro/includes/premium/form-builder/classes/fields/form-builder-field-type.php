<?php
defined( 'ABSPATH' ) || die();

abstract class Form_Builder_Field_Type {

    protected $field;
    protected $field_id = 0;
    protected $type;

    public function __construct( $field = 0, $type = '' ) {
        $this->field = $field;
        $this->set_type( $type );
        $this->set_field_id();
    }

    public function get_field() {
        return $this->field;
    }

    protected function set_type( $type ) {
        if ( empty( $this->type ) ) {
            $this->type = $this->get_field_column( 'type' );

            if ( empty( $this->type ) && ! empty( $type ) )
                $this->type = $type;
        }
    }

    protected function set_field_id() {
        if ( empty( $this->get_field() ))
            return;

        $field = $this->get_field();

        if ( is_array( $field ) ) {
            $this->field_id = isset( $field['id'] ) ? $field['id'] : 0;
        } else if ( is_object( $field ) && property_exists( $field, 'id' ) ) {
            $this->field_id = $field->id;
        } elseif ( is_numeric( $field ) ) {
            $this->field_id = $field;
        }
    }

    public function get_field_column( $column ) {
        $field_val = '';
        if ( is_object( $this->field ) ) {
            $field_val = $this->field->{$column};
        } elseif ( is_array( $this->field ) && isset( $this->field[$column] ) ) {
            $field_val = $this->field[$column];
        }
        return $field_val;
    }

    /* Form builder FrontEnd each elements */

    public function get_frontend_html() {
        $field = $this->get_field();
        $display = $this->display_field_settings();
        $settings = Form_Builder_Settings::get_settings();
        
        $field_types_with_for_attribute = array(
            // 'name', // Has sub-fields that require special handling in their respective PHP class
            'email',
            'url',
            'phone',
            // 'address', // Has sub-fields that require special handling in their respective PHP class
            'text',
            'textarea',
            'number',
            'range_slider',
            'spinner',
            'star',
            'scale',
            'select', // Dropdown
            'checkbox',
            'radio',
            'image_select',
            'upload',
            'date',
            'time',
            // 'user_id', // Hidden field does not require label with the correct for attribute
            // 'hidden', // Hidden field does not require label with the correct for attribute
            // Matrix fields are excluded for now
        );
        
        if ( $display['label'] && in_array( $field['type'], $field_types_with_for_attribute ) ) {
            $for_attribute = ' for=' . $this->html_id();
        } else {
            $for_attribute = '';
        }
        ?>

        <div class="fb-field-container" style="<?php echo esc_attr( $this->container_inner_style() ); ?>">
            <?php if ( $display['label'] && ! empty( trim( $field['name'] ) ) && ( ! ( $field['type'] == 'captcha' && $settings['re_type'] === 'v3' ) ) && ( ! ( $field['type'] == 'altcha' && $settings['altcha_type'] === 'invisible' ) ) ) { ?>
                <label class="fb-field-label <?php echo ( ! $field['name'] || ( ( isset( $field['hide_label'] ) && $field['hide_label'] ) )) ? 'fb-hidden' : ''; ?>"<?php echo esc_html( $for_attribute ); ?>>
                    <?php echo esc_html( $field['name'] ); ?>
                    <?php if ( ! ! $field['required'] ) { ?>
                        <span class="fb-field-required" aria-hidden="true">
                            <?php echo esc_html( $field['required_indicator'] ); ?>
                        </span>
                    <?php } ?>
                </label>
            <?php } ?>
            <div class="fb-field-content">
                <?php
                $this->input_html();

                if ( isset( $display['description'] ) && $display['description'] && ! empty( trim( $field['description'] ) )) {
                    ?>
                    <div class="fb-field-desc">
                        <?php echo esc_html( $field['description'] ); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    private function container_classes_array() {
        $global_settings = Form_Builder_Settings::get_settings();
        $field = $this->get_field();
        $container_class = array();
        $container_class[] = ( $field['required'] != '0' ) ? 'fb-form-field-required' : '';
        $container_class[] = 'formbuilder-field-type-' . esc_attr( $field['type'] );
        $container_class[] = ( $field['type'] == 'captcha' && $global_settings['re_type'] == 'v3' && ! is_admin() ) ? 'fb-recaptcha-v3 fb-hidden' : '';

        if ( in_array( $field['type'], array( 'heading', 'paragraph' ) )) {
            $text_alignment = isset( $field['text_alignment'] ) && $field['text_alignment'] ? $field['text_alignment'] : 'inline';
            $container_class[] = 'fb-text-alignment-' . trim( $text_alignment );
        }

        if ( in_array( $field['type'], array( 'separator', 'image', 'heading', 'paragraph', 'html' ) )) {
            $field_alignment = isset( $field['field_alignment'] ) ? $field['field_alignment'] : 'left';
            $container_class[] = 'fb-field-alignment-' . esc_attr( $field_alignment );
        }

        if ( ! in_array( $field['type'], array( 'separator', 'image', 'heading', 'paragraph', 'html' ) )) {
            $label_position = isset( $field['label_position'] ) && $field['label_position'] ? $field['label_position'] : 'top';
            $label_alignment = isset( $field['label_alignment'] ) && $field['label_alignment'] ? $field['label_alignment'] : 'left';
            $hide_label = isset( $field['hide_label'] ) && $field['hide_label'] ? $field['hide_label'] : '';
            $container_class[] = 'fb-label-position-' . trim( $label_position );
            $container_class[] = 'fb-label-alignment-' . trim( $label_alignment );
            $container_class[] = $field['classes'] ? esc_attr( $field['classes'] ) : '';

            if ( $field['type'] === 'radio' || $field['type'] === 'checkbox' || $field['type'] === 'image_select' ) {
                $options_layout = isset( $field['options_layout'] ) && $field['options_layout'] ? $field['options_layout'] : 'inline';
                $container_class[] = 'fb-options-layout-' . trim( $options_layout );
            }

            if ( $field['type'] === 'select' ) {
                $container_class[] = isset( $field['auto_width'] ) && $field['auto_width'] == 'on' ? 'fb-auto-width' : '';
            }
        }

        if ( isset( $field['grid_id'] ) && $field['grid_id'] ) {
            $container_class[] = trim( $field['grid_id'] );
        }
        return array_filter( $container_class );
    }

    private function container_inner_style() {
        $field = $this->get_field();
        $field_max_width = isset( $field['field_max_width'] ) ? esc_attr( $field['field_max_width'] ) : '';
        $field_max_width_unit = isset( $field['field_max_width_unit'] ) ? esc_attr( $field['field_max_width_unit'] ) : '%';
        $inline_style = $field_max_width ? ( '--fb-width:' . esc_attr( $field_max_width ) . esc_attr( $field_max_width_unit ) . ';' ) : '';
        if ( $field['type'] == 'image_select' ) {
            $image_max_width = isset( $field['image_max_width'] ) ? esc_attr( $field['image_max_width'] ) : '';
            $image_max_width_unit = isset( $field['image_max_width_unit'] ) ? esc_attr( $field['image_max_width_unit'] ) : '%';
            $inline_style .= $image_max_width ? '--fb-image-width: ' . esc_attr( $image_max_width ) . esc_attr( $image_max_width_unit ) : '';
        }
        return $inline_style;
    }

    protected function input_html() {
        ?>
        [input]
        <?php
    }

    /* Form builder AdminEnd each elements */

    public function load_single_field() {
        $field = $this->get_field();
        $classes = $this->container_classes_array();
        $new_classes = array( 'fb-editor-form-field', 'fb-editor-field-box', 'fb-editor-field-elements', 'ui-state-default', 'widgets-holder-wrap' );
        $classes = array_merge( $new_classes, $classes );
        $classes[] = 'fb-editor-field-type-' . $this->type;

        $field_max_width = isset( $field['field_max_width'] ) ? esc_attr( $field['field_max_width'] ) : '';
        $field_max_width_unit = isset( $field['field_max_width_unit'] ) ? esc_attr( $field['field_max_width_unit'] ) : '%';
        if ( $field['type'] == 'image_select' ) {
            $image_max_width = isset( $field['image_max_width'] ) ? $field['image_max_width'] : '';
            $image_max_width_unit = isset( $field['image_max_width_unit'] ) ? esc_attr( $field['image_max_width_unit'] ) : '%';
        }
        ?>
        <li id="fb-editor-field-id-<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr(implode( ' ', $classes ) ); ?>" data-fid="<?php echo esc_attr( $field['id'] ); ?>" data-formid="<?php echo esc_attr( 'divider' === $field['type'] ? esc_attr( $field['form_select'] ) : esc_attr( $field['form_id'] ) ); ?>" data-type="<?php echo esc_attr( $field['type'] ); ?>">

            <div id="fb-editor-field-container-<?php echo esc_attr( $field['id'] ); ?>" class="fb-editor-field-container" style="<?php echo ( $field_max_width ? ( '--fb-width:' . esc_attr( $field_max_width ) . esc_attr( $field_max_width_unit ) . ';' ) : '' ); ?><?php echo ( ( isset( $image_max_width ) && $image_max_width ) ? '--fb-image-width: ' . esc_attr( $image_max_width ) . esc_attr( $image_max_width_unit ) : '' ); ?>">
                <div class="fb-editor-action-buttons">
                    <a href="#" class="fb-editor-move-action" title="<?php esc_attr_e( 'Move Field', 'admin-site-enhancements' ); ?>" data-container="body" aria-label="<?php esc_attr_e( 'Move Field', 'admin-site-enhancements' ); ?>"><span class="fb-cursor-move"><?php echo wp_kses( Form_Builder_Icons::get( 'cursor_move' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></a>
                    <a href="#" class="fb-editor-delete-action" title="<?php esc_attr_e( 'Delete', 'admin-site-enhancements' ); ?>" data-container="body" aria-label="<?php esc_attr_e( 'Delete', 'admin-site-enhancements' ); ?>" data-deletefield="<?php echo esc_attr( $field['id'] ); ?>"><span class="fb-trash-can-outline"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></a>
                </div>

                <?php $this->get_builder_html(); ?>
            </div>

            <?php
            $this->load_single_field_settings();
            ?>
        </li>
        <?php
    }

    public function get_builder_html() {
        $field = $this->get_field();
        $display = $this->display_field_settings();
        $id = $field['id'];

        if ( $display['label'] ) {
            ?>
            <label class="fb-editor-field-label fb-label-show-hide <?php echo ( ! $field['name'] || ( ( isset( $field['hide_label'] ) && $field['hide_label'] ) )) ? 'fb-hidden' : ''; ?> ">
                <span id="fb-editor-field-label-text-<?php echo esc_attr( $id ); ?>" class="fb-editor-field-label-text">
                    <?php echo esc_html( $field['name'] ); ?>
                </span>

                <span id="fb-editor-field-required-<?php echo esc_attr( $id ); ?>" class="fb-field-required<?php echo ( ! $field['required'] ? ' fb-hidden' : '' ); ?>">
                    <?php echo esc_html( $field['required_indicator'] ); ?>
                </span>
            </label>
        <?php } ?>

        <div class="fb-editor-field-content">
            <div class="fb-editor-field-elements">
                <?php $this->input_html(); ?>
            </div>

            <?php
            if ( isset( $display['description'] ) && $display['description'] ) {
                ?>
                <div class="fb-field-desc" id="fb-field-desc-<?php echo esc_attr( $id ); ?>">
                    <?php echo esc_html( $field['description'] ); ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    protected function html_name( $name = '' ) {
        $prefix = empty( $name ) ? 'item_meta' : $name;
        return $prefix . '[' . $this->get_field_column( 'id' ) . ']';
    }

    protected function html_id( $plus = '' ) {
        return 'fb-field-' . $this->get_field_column( 'field_key' ) . $plus;
    }

    public function display_field_settings() {
        $default_settings = $this->default_display_field_settings();
        $field_type_settings = $this->field_settings_for_type();
        return wp_parse_args( $field_type_settings, $default_settings );
    }

    protected function default_display_field_settings() {
        return array(
            'id' => true,
            'name' => true,
            'value' => true,
            'label' => true,
            'max' => false,
            'invalid' => false,
            'clear_on_focus' => false,
            'classes' => false,
            'range' => false,
            'format' => false,
            'max_width' => true,
            'field_alignment' => false,
            'rows' => false,
            'min_time' => false,
            'max_time' => false,
            'date_format' => false,
            'required' => true,
            'content' => false,
            'css' => true,
            'auto_width' => false,
            'default' => true,
            'description' => true,
            'image_max_width' => false
        );
    }

    protected function field_attrs() {
        $attrs = array();
        $display = $this->display_field_settings();

        if ( isset( $display['id'] ) && $display['id'] ) {
            $default_attrs['id'] = $this->html_id();
        }

        if ( isset( $display['value'] ) && $display['value'] ) {
            $default_attrs['value'] = $this->prepare_esc_value();
        }

        if ( isset( $display['name'] ) && $display['name'] ) {
            $default_attrs['name'] = $this->html_name();
        }

        if ( isset( $display['clear_on_focus'] ) && $display['clear_on_focus'] && $this->get_field_column( 'placeholder' ) ) {
            $default_attrs['placeholder'] = $this->get_field_column( 'placeholder' );
        }

        if ( isset( $display['range'] ) && $display['range'] ) {
            $default_attrs['min'] = is_numeric( $this->get_field_column( 'minnum' ) ) ? $this->get_field_column( 'minnum' ) : 0;
            $default_attrs['max'] = is_numeric( $this->get_field_column( 'maxnum' ) ) ? $this->get_field_column( 'maxnum' ) : 9999999;
            $default_attrs['step'] = is_numeric( $this->get_field_column( 'step' ) ) ? $this->get_field_column( 'step' ) : 1;
        }

        if ( isset( $display['max'] ) && $display['max'] ) {
            $default_attrs['maxlength'] = is_numeric( $this->get_field_column( 'max' ) ) ? $this->get_field_column( 'max' ) : '';
        }

        $default_attrs = array_merge( $default_attrs, $this->extra_field_attrs() );

        foreach ( $default_attrs as $key => $value ) {
            $attrs[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
        }

        echo wp_kses_post(implode( ' ', $attrs ) );
    }

    protected function extra_field_attrs() {
        return array();
    }

    protected function field_settings_for_type() {
        return array();
    }

    protected function load_single_field_settings() {
        $field = $this->get_field();
        $display = $this->display_field_settings();
        $field_type = $field['type'];
        $field_id = $field['id'];
        $field_key = $field['field_key'];
        $all_field_types = Form_Builder_Fields::get_all_fields();
        $type_name = $all_field_types[$field_type]['name'];
        $type_icon = $all_field_types[$field_type]['svg'];

        $form_id = $field['form_id'];
        $form = Form_Builder_Builder::get_form_vars( $form_id );
        $form_options = $form->options;

        include( FORMBUILDER_PATH . 'classes/fields/settings.php' );
    }

    /* Extra Options */

    public function show_primary_options() {

    }

    public function show_field_choices( $section_title = '', $options_id = '' ) {
        $field = $this->get_field();
        $field_key = $field['field_key'];
        $options_id_id_string = ( ! empty( $options_id ) && 'default' != $options_id ) ? '-' . $options_id : '';
        $options_id_class_string = ( ! empty( $options_id ) ) ? ' fb-option-list-type-' . $options_id : '';
        $this->field_choices_heading( $section_title );
        ?>
        <div class="fb-form-row">
            <ul id="fb-field-options-<?php echo esc_attr( $field['id'] ); ?><?php echo esc_attr( $options_id_id_string ); ?>" class="fb-option-list<?php echo esc_attr( $options_id_class_string ); ?>" data-key="<?php echo esc_attr( $field['field_key'] ); ?>" data-options-id="<?php echo esc_attr( $options_id ); ?>" data-field-type="<?php echo esc_attr( $field['type'] ); ?>">
                <?php
                $this->show_single_option( $options_id );
                ?>
            </ul>
            <div class="fb-options-wrap">
                <div class="fb-option-add-list">
                    <a href="javascript:void( 0 );" data-opttype="single" class="button fb-add-option" data-options-id="<?php echo esc_attr( $options_id ); ?>">
                        <?php esc_html_e( 'Add', 'admin-site-enhancements' ); ?>
                    </a>
                </div>
                <?php
                if ( $field['type'] != 'image_select' ) {
                    ?>
                    <span class="fb-bulk-edit-link">
                        <a href="#" class="fb-bulk-edit-link" data-key="<?php echo esc_attr( $field_key ); ?>" data-options-id="<?php echo esc_attr( $options_id ); ?>">
                            <?php esc_html_e( 'Bulk Edit', 'admin-site-enhancements' ); ?>
                        </a>
                    </span>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    public function show_single_option( $original_options_id = '' ) {
        $field = $this->get_field();
        if ( ! is_array( $field['options'] ) )
            return;
        $html_id = $this->html_id();
        $this->hidden_field_option( $original_options_id );
        if ( 'likert_matrix_scale' == $field['type'] 
            || 'matrix_of_dropdowns' == $field['type']
            || false !== strpos( $field['type'], 'matrix_of_variable_dropdowns_' ) 
        ) {
            foreach ( $field['options'] as $options_id => $sub_options ) {
                if ( $original_options_id == $options_id ) {
                    foreach ( $sub_options as $opt_key => $opt ) {
                        $field_val = $opt['label'];
                        $default_value = (array) $field['default_value'];
                        $checked = in_array( $field_val, $default_value ) ? 'checked' : '';

                        $this->output_single_option( $field, $options_id, $html_id, $opt_key, $opt, $field_val, $default_value, $checked );
                    }
                }
            }
        } else {
            foreach ( $field['options'] as $opt_key => $opt ) {
                $field_val = $opt['label'];
                $default_value = (array) $field['default_value'];
                $checked = in_array( $field_val, $default_value ) ? 'checked' : '';

                $this->output_single_option( $field, $original_options_id, $html_id, $opt_key, $opt, $field_val, $default_value, $checked );
            }
        }
    }

    protected function hidden_field_option( $options_id = '' ) {
        $field = $this->get_field();
        $ajax_action = get_post( 'action', 'sanitize_text_field' );
        if ( $ajax_action === 'formbuilder_import_options' )
            return;
        $opt_key = '000';
        $opt = esc_html__( 'New Option', 'admin-site-enhancements' );

        $html_id = $this->html_id();
        $field_val = $opt = esc_html__( 'New Option', 'admin-site-enhancements' );

        $default_value = '';
        $checked = false;

        $this->output_single_option( $field, $options_id, $html_id, $opt_key, $opt, $field_val, $default_value, $checked );
    }
    
    protected function output_single_option( $field = array(), $options_id = '', $html_id = '', $opt_key = '', $opt = '', $field_val = '', $default_value = '', $checked = '' ) {
        if ( $field['type'] == 'image_select' ) {
            $field_type = $field['select_option_type'] ? esc_attr( $field['select_option_type'] ) : 'radio';
            $field_name = 'default_value_' . absint( $field['id'] ) . '[' . esc_attr( $opt_key ) . ']';
            $field_option_additional_node = '';
        } else if ( $field['type'] == 'select' ) {
            $field_type = 'radio';
            $field_name = 'default_value_' . absint( $field['id'] );
            $field_option_additional_node = '';
        } else if ( $field['type'] == 'likert_matrix_scale' 
            || $field['type'] == 'matrix_of_dropdowns' 
            || false !== strpos( $field['type'], 'matrix_of_variable_dropdowns_' ) 
        ) {
            $field_type = 'radio';
            $field_name = 'default_value_' . absint( $field['id'] );    
            $field_option_additional_node = '[' . $options_id . ']';
        } else {
            $field_type = $field['type'];
            $field_name = $field_type == 'radio' ? 'default_value_' . absint( $field['id'] ) : 'default_value_' . esc_attr( $field['id'] ) . '[' . esc_attr( $opt_key ) . ']';
            $field_option_additional_node = '';
        }
        ?>
        <li id="fb-option-list-<?php echo absint( $field['id'] ) . '-' . esc_attr( $opt_key ); ?>" data-options-id="<?php echo esc_attr( $options_id ); ?>" data-optkey="<?php echo esc_attr( $opt_key ); ?>" class="<?php echo ( $opt_key === '000' ? ' fb-hidden fb-option-template' : '' ); ?>">
            <div class="fb-single-option">
                <span class="fb-drag"><?php echo wp_kses( Form_Builder_Icons::get( 'drag_handle' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                <input class="fb-choice-input" type="<?php echo esc_attr( $field_type ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field_val ); ?>" <?php echo wp_kses_post( $checked ); ?> />

                <input class="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>" type="text" name="field_options[options_<?php echo esc_attr( $field['id'] ); ?>]<?php echo esc_attr( $field_option_additional_node ); ?>[<?php echo esc_attr( $opt_key ); ?>][label]" value="<?php echo esc_attr( $field_val ); ?>" />

                <a href="javascript:void( 0 )" class="fb-remove-field" data-fid="<?php echo esc_attr( $field['id'] ); ?>" data-removeid="fb-option-list-<?php echo absint( $field['id'] ) . '-' . esc_attr( $opt_key ); ?>" data-options-id="<?php echo esc_attr( $options_id ); ?>">
                    <span class="fb-trash-can-outline"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                </a>
            </div>
            <?php
            if ( $field['type'] == 'image_select' ) {
                $opt = isset( $field['options'][$opt_key] ) ? $field['options'][$opt_key] : '';
                $image_id = isset( $opt['image_id'] ) ? absint( $opt['image_id'] ) : 0;
                $src = wp_get_attachment_image_src( $image_id, 'full' );
                $url = is_array( $src ) ? $src[0] : '';
                if ( ! $url ) {
                    $url = wp_get_attachment_image_url( $image_id );
                }
                $image = array(
                    'id' => $image_id,
                    'url' => $url ? $url : '',
                );
                ?>
                <div class="fb-is-image-preview field_<?php echo esc_attr( $field['id'] ); ?>_image_id">
                    <input type="hidden" class="fb-image-id" name="field_options[options_<?php echo esc_attr( $field['id'] ); ?>][<?php echo esc_attr( $opt_key ); ?>][image_id]" id="fb-field-image-<?php echo absint( $field['id'] ) . '-' . esc_attr( $opt_key ); ?>" value="<?php echo ( empty( $image['id'] ) ? '' : absint( $image['id'] ) ); ?>" />
                    <div class="fb-is-image-preview-box<?php echo ( empty( $image['url'] ) ? '' : ' fb-image-added' ); ?>">
                        <span class="fb-is-image-holder">
                            <?php
                            if ( ! empty( $image['url'] ) ) {
                                ?>
                                <img id="fb-is-image-preview-<?php echo absint( $field['id'] ) . '-' . esc_attr( $opt_key ); ?>" src="<?php echo esc_url( $image['url'] ); ?>" />
                                <?php
                            }
                            ?>
                        </span>
                        <a class="fb-is-remove-image" href="#"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></a>
                    </div>
                </div>
                <?php
            }
            ?>
        </li>
        <?php        
    }

    protected function field_choices_heading( $section_title = '' ) {
        $field = $this->get_field();
        if ( 'likert_matrix_scale' == $field['type'] 
            || 'matrix_of_dropdowns' == $field['type'] 
            || false !== strpos( $field['type'], 'matrix_of_variable_dropdowns_' ) 
        ) {
            $field_heading = $section_title;
        } else {
            $field_heading = __( 'Options', 'admin-site-enhancements' );
        }
        ?>
        <h4 class="fb-field-heading">
            <?php
            echo esc_html__( $field_heading );
            ?>
        </h4>
        <?php
    }

    /* Combo Options */

    protected function show_after_default() {

    }

    public function get_default_field_options() {
        $opts = array(
            'grid_id' => '',
            'label_position' => '',
            'label_alignment' => '',
            'hide_label' => '',
            'heading_type' => '',
            'text_alignment' => '',
            'content' => '',
            'select_option_type' => 'radio',
            'image_size' => '',
            'image_id' => '',
            'spacer_height' => '50',
            'step' => '1',
            'highest_scale_text' => esc_html__( 'Extremely likely', 'admin-site-enhancements' ),
            'lowest_scale_text' => esc_html__( 'Not at all likely', 'admin-site-enhancements' ),
            'highest_scale_point' => '10',
            'lowest_scale_point' => '1',
            'min_time' => '00:00',
            'max_time' => '23:59',
            'date_format' => 'MM dd, yy',
            'border_style' => 'solid',
            'border_width' => '2',
            'minnum' => '1',
            'maxnum' => '10',
            'classes' => '',
            'auto_width' => 'off',
            'placeholder' => '',
            'format' => '',
            'required_indicator' => '*',
            'options_layout' => 'inline',
            'field_max_width' => '',
            'field_max_width_unit' => '%',
            'image_max_width' => '100',
            'image_max_width_unit' => '%',
            'field_alignment' => 'left',
            'blank' => esc_html__( 'This field is required.', 'admin-site-enhancements' ),
            'invalid' => esc_html__( 'This field is invalid.', 'admin-site-enhancements' ),
            'rows' => '10',
            'max' => '',
            'disable' => array(
                'line1' => '',
                'line2' => '',
                'city' => '',
                'state' => '',
                'zip' => '',
                'country' => ''
            )
        );
        $field_opts = $this->extra_field_default_opts();
        $opts = array_merge( $opts, $field_opts );
        return $opts;
    }

    protected function extra_field_default_opts() {
        return array();
    }

    /* Front End Display */

    public function show_field() {
        $this->load_field_scripts();
        $field = $this->get_field();
        $classes = $this->container_classes_array();
        $classes[] = 'fb-form-field';
        ?>
        <div id="fb-field-container-<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr(implode( ' ', $classes ) ); ?>">
            <?php $this->get_frontend_html(); ?>
        </div>
        <?php
    }

    protected function load_field_scripts() {

    }

    protected function prepare_esc_value() {
        $field = $this->get_field();
        $value = isset( $field['default_value'] ) ? $field['default_value'] : '';
        if ( is_array( $value ) ) {
            $value = implode( ', ', $value );
        }

        if (strpos( $value, '&lt;' ) !== false )
            $value = htmlentities( $value );
        return $value;
    }

    protected function add_min_max() {
        $field = $this->field();
        $min = $field['minnum'];
        $max = $field['maxnum'];
        $step = $field['step'];

        if ( ! is_numeric( $min ) )
            $min = 0;

        if ( ! is_numeric( $max ) )
            $max = 9999999;

        if ( ! is_numeric( $step ) && $step !== 'any' )
            $step = 1;

        $input_html .= ' min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '"';
    }

    public function validate( $args ) {
        return array();
    }

    public function set_value_before_save( $value ) {
        return $value;
    }

    public function sanitize_value(&$value ) {
        return Form_Builder_Helper::sanitize_value( 'sanitize_text_field', $value );
    }

    public function get_new_field_defaults() {
        $defaults = array(
            'name' => $this->get_new_field_name(),
            'description' => '',
            'type' => $this->type,
            'default_value' => '',
            'required' => false,
            'field_options' => $this->get_default_field_options(),
        );
        
        if ( 'likert_matrix_scale' == $this->type ) {
            $defaults['options'] = array(
                'rows' => array(
                    array(
                        'label' => esc_html__( 'Pineapple on pizza is an excellent idea!', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Every human being is entitled to a decent place to live in', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Before any two nations or group of nations decide to go to war, they should consider doing a chess tournament instead to settle their conflict', 'admin-site-enhancements' ),
                    ),
                ),
                'columns' => array(
                    array(
                        'label' => esc_html__( 'Strongly disagree', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Disagree', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Neutral', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Agree', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Strongly agree', 'admin-site-enhancements' ),
                    ),
                ),
            );
        } else if ( 'matrix_of_dropdowns' == $this->type ) {
            $defaults['options'] = array(
                'rows' => array(
                    array(
                        'label' => esc_html__( "I'm given enough opportunities for professional growth", 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'The company culture allows for work-life balance', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( "I'm compensated appropriately for the skills, time and effort I put into my work", 'admin-site-enhancements' ),
                    ),
                ),
                'columns' => array(
                    array(
                        'label' => '2022',
                    ),
                    array(
                        'label' => '2023',
                    ),
                    array(
                        'label' => '2024',
                    ),
                ),
                'dropdowns' => array(
                    array(
                        'label' => esc_html__( 'Strongly disagree', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Disagree', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Neutral', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Agree', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Strongly agree', 'admin-site-enhancements' ),
                    ),
                ),
            );
        } else if ( false !== strpos( $this->type, 'matrix_of_variable_dropdowns' )  ) {
            $defaults['options'] = array(
                'rows' => array(
                    array(
                        'label' => esc_html__( "Free-flow beverages", 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Gym facility', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( "Sleeping pods", 'admin-site-enhancements' ),
                    ),
                ),
                'columns' => array(
                    array(
                        'label' => 'How important is this for you?',
                    ),
                ),
                'first_dropdown' => array(
                    array(
                        'label' => esc_html__( 'Not at all important', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Not important', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Neutral', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Important', 'admin-site-enhancements' ),
                    ),
                    array(
                        'label' => esc_html__( 'Very important', 'admin-site-enhancements' ),
                    ),
                ),
            );
            
            $second_column = array(
                'label' => 'Have you used it in the last 12 months?',
            );

            $third_column = array(
                'label' => 'How often do you use this?',
            );

            $fourth_column = array(
                'label' => 'How long have you been using this?',
            );

            $fifth_column = array(
                'label' => 'How satisfied are you with this?',
            );
            
            $second_dropdown_options = array(
                array(
                    'label' => esc_html__( 'No', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Yes', 'admin-site-enhancements' ),
                ),
            );

            $third_dropdown_options = array(
                array(
                    'label' => esc_html__( 'Never', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Once a year', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Several times a year', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Once a month', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Several times a month', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Once a week', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Several times a week', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Every day', 'admin-site-enhancements' ),
                ),
            );

            $fourth_dropdown_options = array(
                array(
                    'label' => esc_html__( 'Never', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Less than a month', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( '1-6 months', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( '6-12 months', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( '1-3 years', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'More than 3 years', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'More than 10 years', 'admin-site-enhancements' ),
                ),            
            );

            $fifth_dropdown_options = array(
                array(
                    'label' => esc_html__( 'Highly dissatisfied', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Dissatisfied', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Neutral', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Satisfied', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Highly satisfied', 'admin-site-enhancements' ),
                ),
            );

            if ( 'matrix_of_variable_dropdowns_two' == $this->type ) {
                $defaults['options']['columns'][] = $second_column;

                $defaults['options']['second_dropdown'] = $second_dropdown_options;
            }

            if ( 'matrix_of_variable_dropdowns_three' == $this->type ) {
                $defaults['options']['columns'][] = $second_column;
                $defaults['options']['columns'][] = $third_column;

                $defaults['options']['second_dropdown'] = $second_dropdown_options;
                $defaults['options']['third_dropdown'] = $third_dropdown_options;
            }

            if ( 'matrix_of_variable_dropdowns_four' == $this->type ) {
                $defaults['options']['columns'][] = $second_column;
                $defaults['options']['columns'][] = $third_column;
                $defaults['options']['columns'][] = $fourth_column;

                $defaults['options']['second_dropdown'] = $second_dropdown_options;
                $defaults['options']['third_dropdown'] = $third_dropdown_options;
                $defaults['options']['fourth_dropdown'] = $fourth_dropdown_options;
            }

            if ( 'matrix_of_variable_dropdowns_five' == $this->type ) {
                $defaults['options']['columns'][] = $second_column;
                $defaults['options']['columns'][] = $third_column;
                $defaults['options']['columns'][] = $fourth_column;
                $defaults['options']['columns'][] = $fifth_column;

                $defaults['options']['second_dropdown'] = $second_dropdown_options;
                $defaults['options']['third_dropdown'] = $third_dropdown_options;
                $defaults['options']['fourth_dropdown'] = $fourth_dropdown_options;
                $defaults['options']['fifth_dropdown'] = $fifth_dropdown_options;
            }
            // vi( $defaults );
        } else {
            $defaults['options'] = array(
                array(
                    'label' => esc_html__( 'Rock', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Paper', 'admin-site-enhancements' ),
                ),
                array(
                    'label' => esc_html__( 'Scissor', 'admin-site-enhancements' ),
                )
            );
        }
        
        return $defaults;
    }

    protected function get_new_field_name() {
        $name = esc_html__( 'Untitled', 'admin-site-enhancements' );
        $fields = Form_Builder_Fields::get_all_fields();
        if ( isset( $fields[$this->type] ) ) {
            $name = is_array( $fields[$this->type] ) ? $fields[$this->type]['name'] : $fields[$this->type];
        }
        return $name;
    }

}
