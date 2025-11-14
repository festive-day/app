<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_HTML extends Form_Builder_Field_Type {

    protected $type = 'html';

    public function field_settings_for_type() {
        return array(
            'default' => false,
            'required' => false,
            'label' => false,
            'description' => false,
            'field_alignment' => true,
        );
    }

    protected function extra_field_default_opts() {
        return array(
            'field_alignment' => 'left',
        );
    }

    public function show_primary_options() {
        $field = $this->get_field();
        ?>
        <div class="fb-form-row">
            <!-- <label><?php // esc_html_e( 'Content', 'admin-site-enhancements' ); ?></label> -->
            <div class="fb-form-text-editor">
                <?php
                $args = array(
                    'textarea_name' => 'field_options[description_' . absint( $field['id'] ) . ']',
                    'textarea_rows' => 8,
                );
                $html_id = 'fb-field-desc_' . absint( $field['id'] );
                wp_editor( $field['description'], $html_id, $args );
                ?>
            </div>
        </div>
        <?php
    }

    public function input_html() {
        $field = $this->get_field();
        ?>
        <div class="fb-custom-html-field">
            <?php
            if ( is_admin() && !Form_Builder_Helper::is_preview_page() ) {
                ?>
                <div class="fb-custom-html-preview">
                    <?php esc_html_e( 'Content / HTML - No Preview Available', 'admin-site-enhancements' ); ?>
                </div>
                <?php
            } else {
                echo do_shortcode( wp_kses_post( $field['description'] ) );
            }
            ?>
        </div>
        <?php
    }

}
