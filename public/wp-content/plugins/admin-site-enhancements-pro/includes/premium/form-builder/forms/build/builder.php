<?php
defined( 'ABSPATH' ) || die();
?>

<div id="fb-editor-wrap" class="<?php echo ( $has_fields ? 'fb-editor-has-fields' : '' ); ?>">
    <?php do_action( 'formbuilder_before_form_builder_editor_fields' ); ?>
    <ul id="fb-editor-fields" class="fb-editor-sorting inside">
        <?php
        if ( ! empty( $vars['fields'] ) ) {
            $grid_helper = new Form_Builder_Grid_Helper();
            $vars['count'] = 0;
            foreach ( $vars['fields'] as $field ) {
                $vars['count']++;
                $grid_helper->set_field( $field );
                $grid_helper->maybe_begin_field_wrapper();
                $field_obj = Form_Builder_Fields::get_field_class( $field['type'], $field );
                $field_obj->load_single_field();
                $grid_helper->sync_list_size();
                unset( $field );
            }
            $grid_helper->force_close_field_wrapper();
            unset( $grid_helper );
        }
        ?>
    </ul>

    <div class="fb-editor-submit-button-wrap fb-submit-btn-align-<?php echo ( isset( $form->options['submit_btn_alignment'] ) ? esc_attr( $form->options['submit_btn_alignment'] ) : 'left' ); ?>">
        <button id="fb-editor-submit-button" class="fb-editor-submit-button" disabled="disabled">
            <?php echo ( isset( $form->options['submit_value'] ) ? esc_html( $form->options['submit_value'] ) : esc_html__( 'Submit', 'admin-site-enhancements' ) ); ?>
        </button>
    </div>

    <div class="fb-no-fields">
        <span>
            <h3><?php esc_html_e( 'Add Fields Here', 'admin-site-enhancements' ); ?></h3>
            <p><?php esc_html_e( 'Click or drag a field from the sidebar to add it to your form', 'admin-site-enhancements' ); ?></p>
        </span>
    </div>
</div>

<div id="fb-bulk-edit-modal">
    <div class="postbox">
        <div class="fb-bulk-edit-modal-header">
            <h2>
                <?php esc_html_e( 'Bulk Edit Options', 'admin-site-enhancements' ); ?>
            </h2>
            <a class="dismiss" title="<?php esc_attr_e( 'Close', 'admin-site-enhancements' ); ?>"><span class="fb fb-window-close"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span></a>
        </div>

        <div class="fb-bulk-edit-body fb-editor-grid-container">
            <div class="fb-grid-8 fb-bulk-edit-content">
                <p>
                    <?php esc_html_e( 'Edit or add field options (one per line )', 'admin-site-enhancements' ); ?>
                </p>
                <textarea name="formbuilder_bulk_options" id="fb-bulk-options"></textarea>
                <input type="hidden" value="" id="bulk-field-id" />
                <input type="hidden" value="" id="bulk-option-type" />
            </div>
            <div class="fb-grid-4 fb-bulk-edit-sidebar">
                <h3>
                    <?php esc_html_e( 'Insert Presets', 'admin-site-enhancements' ); ?>
                </h3>
                <ul class="fb-default-opts">
                    <?php
                    $preset_options = Form_Builder_Helper::get_options_presets();
                    foreach ( $preset_options as $class => $option ) {
                        ?>
                        <li class="<?php echo esc_attr( $class ); ?>">
                            <a href="#" class="fb-insert-preset" data-opts="<?php echo esc_attr( wp_json_encode( $option['options'] ) ); ?>">
                                <?php echo esc_html( $option['label'] ); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="fb-bulk-edit-modal-footer">
            <button class="button" id="fb-update-bulk-options">
                <?php esc_attr_e( 'Update', 'admin-site-enhancements' ); ?>
            </button>
        </div>
    </div>
</div>