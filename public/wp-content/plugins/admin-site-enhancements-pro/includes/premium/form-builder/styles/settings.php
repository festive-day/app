<?php
defined( 'ABSPATH' ) || die();

global $post;
$post_id = $post->ID;
$formbuilder_styles = get_post_meta( $post_id, 'formbuilder_styles', true );

if ( ! $formbuilder_styles ) {
    $formbuilder_styles = Form_Builder_Styles::default_styles();
} else {
    $formbuilder_styles = Form_Builder_Helper::recursive_parse_args( $formbuilder_styles, Form_Builder_Styles::default_styles() );
}

wp_nonce_field( 'fb-styles-nonce', 'formbuilder_styles_nonce' );
?>

<div class="fb-content">
    <div class="fb-body">
        <div class="fb-fields-sidebar fb-style-sidebar">
            <div class="fb-sticky-sidebar">
                <?php include FORMBUILDER_PATH . 'styles/main.php'; ?>
            </div>
        </div>

        <div id="fb-form-panel">
            <div class="fb-form-wrap">
                <?php Form_Builder_Helper::print_message(); ?>
                <?php include FORMBUILDER_PATH . 'styles/demo-preview.php'; ?>
            </div>
        </div>
    </div>

    <?php
    $formbuilder_post_type = htmlspecialchars_decode( Form_Builder_Helper::get_var( 'post_type' ) );
    $formbuilder_post_class = $formbuilder_post_type == 'formbuilder-styles' ? 'postbox' : 'submitbox';
    ?>
    <div class="fb-footer" style="display:none">
        <div id="submitpost" class="<?php echo esc_attr( $formbuilder_post_class ); ?>">
            <div id="major-publishing-actions">
                <div id="publishing-action">
                    <span class="spinner"></span>
                    <?php if ( $formbuilder_post_type == 'formbuilder-styles' ) { ?>
                        <input name="original_publish" type="hidden" id="original_publish" value="Publish">
                        <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php esc_html_e( 'Publish', 'admin-site-enhancements' ); ?>">
                    <?php } else { ?>
                        <input name="original_publish" type="hidden" id="original_publish" value="Update">
                        <input type="submit" name="save" id="publish" class="button button-primary button-large" value="<?php esc_html_e( 'Update', 'admin-site-enhancements' ); ?>">
                    <?php } ?>
                </div>
            </div>
        </div>
<!--         <div class="fb-preview-close">
            <a class="button button-secondary button-large" href="<?php // echo esc_url(admin_url( '/edit.php?post_type=formbuilder-styles' ) ); ?>"><?php // esc_html_e( 'Close', 'admin-site-enhancements' ); ?></a>
        </div>
 -->    </div>
</div>