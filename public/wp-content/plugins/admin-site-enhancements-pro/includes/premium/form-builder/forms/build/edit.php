<?php
defined( 'ABSPATH' ) || die();

$id = htmlspecialchars_decode( Form_Builder_Helper::get_var( 'id', 'absint' ) );
$form = Form_Builder_Builder::get_form_vars( $id );

if ( ! $form ) {
    ?>
    <h3><?php esc_html_e( 'You are trying to edit a form that does not exist.', 'admin-site-enhancements' ); ?></h3>
    <?php
    return;
}
$fields = Form_Builder_Fields::get_form_fields( $form->id );
$values = Form_Builder_Helper::process_form_array( $form );

$edit_message = esc_html__( 'Form was successfully updated.', 'admin-site-enhancements' );
$has_fields = isset( $fields ) && ! empty( $fields );

if ( ! empty( $fields ) ) {
    $vars = Form_Builder_Helper::get_fields_array( $id );
}

if (defined( 'DOING_AJAX' ) ) {
    wp_die();
} else {
    ?>
    <div id="fb-wrap" class="fb-content">
        <?php
        self::get_admin_header(
            array(
                'form' => $form,
                'class' => 'fb-header-nav',
            )
        );
        ?>
        <div class="fb-body">
            <?php require( FORMBUILDER_PATH . 'forms/build/sidebar.php' ); ?>

            <div id="fb-form-panel">
                <div class="fb-form-wrap">
                    <form method="post">
                        <?php require( FORMBUILDER_PATH . 'forms/build/builder.php' ); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
}