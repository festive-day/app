<?php
defined( 'ABSPATH' ) || die();

class Form_Builder_Preview {

    public function __construct() {
        add_action( 'wp_ajax_formbuilder_preview', array( $this, 'preview' ) );
        add_action( 'wp_ajax_nopriv_formbuilder_preview', array( $this, 'preview' ) );
    }

    public static function preview() {
        header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
        $id = htmlspecialchars_decode( Form_Builder_Helper::get_var( 'form', 'absint' ) );
        $form = Form_Builder_Builder::get_form_vars( $id );
        require( FORMBUILDER_PATH . 'forms/preview/preview.php' );
        wp_die();
    }

    public static function show_form( $id ) {
        $form = Form_Builder_Builder::get_form_vars( $id );
        if ( ! $form || $form->status === 'trash' )
            return esc_html__( 'Please select a valid form', 'admin-site-enhancements' );

        self::get_form_contents( $id );
    }

    public static function get_form_contents( $id ) {
        $form = Form_Builder_Builder::get_form_vars( $id );
        $values = Form_Builder_Helper::get_fields_array( $id );

        $styles = $form->styles ? $form->styles : '';

        $form_class = array( 'formbuilder-form' );
        $form_class[] = isset( $form->options['form_css_class'] ) ? $form->options['form_css_class'] : '';
        $form_class[] = $styles && isset( $styles['form_style'] ) ? 'fb-form-' . esc_attr( $styles['form_style'] ) : 'fb-form-default-style';
        $form_class = apply_filters( 'formbuilder_form_classes', $form_class );
        ?>

        <div class="fb-form-tempate">
            <form enctype="multipart/form-data" method="post" class="<?php echo esc_attr(implode( ' ', array_filter( $form_class ) )); ?>" id="fb-form-id-<?php echo esc_attr( $form->form_key ); ?>" novalidate>
                <?php
                require FORMBUILDER_PATH . 'forms/style/form.php';
                $form_msg = Form_Builder_Helper::get_var( 'hf_success' );
                if( $form_msg == 'true' ) {
                    ?>
                    <span class="fb-success-msg"><?php echo esc_html( $form->settings['confirmation_message'] ); ?></span>
                    <?php
                }
                ?>
            </form>
        </div>
        <?php
    }

}

new Form_Builder_Preview();