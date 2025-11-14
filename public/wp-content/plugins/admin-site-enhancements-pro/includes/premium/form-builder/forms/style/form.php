<?php
defined( 'ABSPATH' ) || die();

$styles = $form->styles ? $form->styles : array();
$style_id = isset( $styles['form_style_template'] ) ? $styles['form_style_template'] : '';
$formbuilder_styles = get_post_meta( $style_id, 'formbuilder_styles', true );
$submit_class = isset( $form->options['submit_btn_alignment'] ) ? 'fb-submit-btn-align-' . esc_html( $form->options['submit_btn_alignment'] ) : 'fb-submit-btn-align-left';
$submit = isset( $form->options['submit_value'] ) ? esc_html( $form->options['submit_value'] ) : esc_html__( 'Submit', 'admin-site-enhancements' );
$button_id = 'fb-submit-button';
$button_class = array( 'fb-submit-button' );
if ( isset( $form->options['submit_btn_css_class'] ) ) {
    $button_class[] = esc_attr( $form->options['submit_btn_css_class'] );
}

$form_title = esc_html( $form->name );
$form_description = esc_html( $form->description );
$show_title = isset( $form->options['show_title'] ) ? esc_html( $form->options['show_title'] ) : 'on';
$show_description = isset( $form->options['show_description'] ) ? esc_html( $form->options['show_description'] ) : 'off';
$formbuilder_action = htmlspecialchars_decode( Form_Builder_Helper::get_var( 'action' ) );

if ( ! $formbuilder_styles ) {
    $formbuilder_styles = Form_Builder_Styles::default_styles();
} else {
    $formbuilder_styles = Form_Builder_Helper::recursive_parse_args( $formbuilder_styles, Form_Builder_Styles::default_styles() );
}
?>

<div class="fb-form-preview" id="fb-container-<?php echo esc_attr( $form->id ); ?>">
    <?php
    if ( empty( $values ) || !isset( $values['fields'] ) || empty( $values['fields'] ) ) {
        ?>
        <div class="fb-form-error">
            <strong><?php esc_html_e( 'Oops!', 'admin-site-enhancements' ); ?></strong>
            <?php printf(
                /* translators: %1$s: <a> tag opening, %2$s: </a> tag closing */
                esc_html__( 'You did not add any fields to your form. %1$sGo back%2$s and add some.', 'admin-site-enhancements' ), 
                '<a href="' . esc_url(admin_url( 'admin.php?page=formbuilder&formbuilder_action=edit&id=' . absint( $id ) )) . '">', 
                '</a>' 
            ); ?>
        </div>
        <?php
        return;
    }

    if ( $show_title == 'on' && $form_title ) {
        ?>
        <h3 class="fb-form-title"><?php echo esc_html( $form_title ); ?></h3>
        <?php
    }

    if ( $show_description == 'on' && $form_description ) {
        ?>
        <div class="fb-form-description"><?php echo esc_html( $form_description ); ?></div>
        <?php
    }
    ?>
    <div class="fb-container">
        <input type="hidden" name="formbuilder_action" value="create" />
        <input type="hidden" name="form_id" value="<?php echo absint( $form->id ); ?>" />
        <input type="hidden" name="form_key" value="<?php echo esc_attr( $form->form_key ); ?>" />
        <input type="hidden" class="formbuilder-form-conditions" value="<?php echo esc_attr(htmlspecialchars( wp_json_encode( Form_Builder_Builder::get_show_hide_conditions(absint( $form->id ) )), ENT_QUOTES, 'UTF-8' ) ); ?>" />
        <?php
        wp_nonce_field( 'formbuilder_submit_entry_nonce', 'formbuilder_submit_entry_' . absint( $form->id ) );

        if ( $values['fields'] ) {
            Form_Builder_Fields::show_fields( $values['fields'] );
        }
        ?>
        <div class="fb-submit-wrap <?php echo esc_attr( $submit_class ); ?>">
            <button id="<?php echo esc_attr( $button_id ); ?>" class="<?php echo esc_attr(implode( ' ', $button_class ) ) ?>" type="submit" <?php disabled( $formbuilder_action, 'formbuilder_preview' ); ?>><?php echo esc_html( $submit ); ?></button>
        </div>
    </div>
    <?php
    $form_style = apply_filters( 'formbuilder_enable_style', '__return_true' );
    if ( $form_style ) {
        echo '<style class="fb-style-content">';
        echo '#fb-container-' . absint( $form->id ) . '{';
        Form_Builder_Styles::get_style_vars( $formbuilder_styles, '' );
        echo '}';
        echo '</style>';
    }
    ?>
</div>