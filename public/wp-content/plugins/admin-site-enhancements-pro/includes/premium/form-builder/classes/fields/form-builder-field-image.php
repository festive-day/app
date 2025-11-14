<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Image extends Form_Builder_Field_Type {

    protected $type = 'image';

    public function field_settings_for_type() {
        return array(
            'label' => false,
            'default' => false,
            'description' => false,
            'required' => false,
            'image' => true,
            'field_alignment' => true,
        );
    }

    protected function extra_field_default_opts() {
        return array(
            'image_id' => '',
            'field_alignment' => 'left',
        );
    }

    protected function input_html() {
        $field = $this->get_field();
        $image = '';
        if ( isset( $field['image_id'] ) ) {
            $image = wp_get_attachment_image_src( $field['image_id'], 'full' );
            $image = isset( $image[0] ) ? $image[0] : '';
        }
        $image_class = $image ? 'fb-hidden' : '';
        ?>

        <div class="fb-image-preview-front fb-field-image-<?php echo esc_attr( $field['id'] ); ?>">
            <div class="fb-no-image-field <?php echo esc_attr( $image_class ); ?>">
                <?php esc_html_e( 'Image Field - No Image', 'admin-site-enhancements' ); ?>
            </div>
            <?php
            if ( $image ) {
                ?>
                <img src="<?php echo esc_url( $image ); ?>" />
                <?php
            }
            ?>
        </div>
        <?php
    }

}
