<?php
defined( 'ABSPATH' ) || die();

$form_id = Form_Builder_Helper::get_var( 'id', 'absint' );
?>
<div id="fb-shortcode-form-modal">
    <div class="fb-shortcode-modal-wrap">
        <form id="fb-add-template" method="post">
            <h3><?php esc_attr_e( 'Shortcode to embed this form', 'admin-site-enhancements' ); ?></h3>

            <div class="fb-form-row">
                <input type="text" value="<?php echo esc_attr( '[formbuilder id="' . absint( $form_id ) . '"]' ) ?>" disabled />
                <span id="fb-copy-shortcode" class="fb fb-content-copy"><?php echo wp_kses( Form_Builder_Icons::get( 'copy' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
            </div>

            <div class="fb-copied"><?php esc_attr_e( 'Copied!', 'admin-site-enhancements' ); ?></div>

            <div class="fb-shortcode-footer">
                <a href="#" class="button button-large formbuilder-close-form-modal"><?php esc_html_e( 'Close', 'admin-site-enhancements' ); ?></a>
            </div>
        </form>
    </div>
</div>