<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Field_Hidden extends Form_Builder_Field_Type {

    protected $type = 'hidden';

    public function field_settings_for_type() {
        return array(
            'max_width' => false,
            'css' => false,
            'description' => false,
            'required' => false,
            'label' => true
        );
    }

    public function set_value_before_save( $value ) {
        // Perform additional modification here as needed.
        return $value;
    }

    protected function input_html() {
        $field = $this->get_field();
        $default_value = $field['default_value'];

        $tags = array(
            '#page_title',
            '#page_url',  
        );
        
        $replacements = array(
            get_the_title(), // #page_title
            get_the_permalink(), // #page_url
        );
        
        $value = str_replace( $tags, $replacements, $default_value );
        
        if ( is_admin() && !Form_Builder_Helper::is_preview_page() ) {
            ?>
            <input type="text" <?php $this->field_attrs(); ?> />
            <p class="howto">
                <?php esc_html_e( 'Note: This field will not be shown in the form. Enter the value to be hidden.', 'admin-site-enhancements' ); ?>
            </p>
            <?php
        } else {
            ?>
            <input type="hidden" id="<?php echo esc_attr( $this->html_id() ); ?>" value="<?php echo esc_attr( $value ); ?>" name="item_meta[<?php echo esc_attr( $field['id'] ); ?>]" />
            <?php
        }
    }

}
