<?php
defined( 'ABSPATH' ) || die();

$id = Form_Builder_Helper::get_var( 'id', 'absint', 0 );
$form = Form_Builder_Builder::get_form_vars( $id );
$fields = Form_Builder_Fields::get_form_fields( $id );

$subject_tags_excluded_field_types = array(
    'heading',
    'paragraph',
    'separator',
    'spacer',
    'image',
    'altcha',
    'captcha',
    'turnstile',
    'likert_matrix_scale',
    'matrix_of_dropdowns',
    'matrix_of_variable_dropdowns',
    'matrix_of_variable_dropdowns_two',
    'matrix_of_variable_dropdowns_three',
    'matrix_of_variable_dropdowns_four',
    'matrix_of_variable_dropdowns_five',
);
$message_tags_excluded_field_types = array(
    'heading',
    'paragraph',
    'separator',
    'spacer',
    'image',
    'altcha',
    'captcha',
    'turnstile',
);

$settings = $form->settings ? $form->settings : Form_Builder_Helper::get_form_settings_default();

$footer_text = isset( $settings['footer_text'] ) ? $settings['footer_text'] : __( 'This email was sent from #linked_site_name.', 'admin-site-enhancements' );
$footer_text_ar = isset( $settings['footer_text_ar'] ) ? $settings['footer_text_ar'] : __( 'This email was sent from #linked_site_name.', 'admin-site-enhancements' );
?>
<div id="fb-wrap" class="fb-content">
    <?php
    self::get_admin_header(
        array(
            'form' => $form,
            'class' => 'fb-header-nav',
        )
    );

    $sections = array(
        'email-settings' => array(
            'name' => esc_html__( 'Email Notification Settings', 'admin-site-enhancements' ),
        ),
        'auto-responder' => array(
            'name' => esc_html__( 'Auto Responder', 'admin-site-enhancements' ),
        ),
        'form-confirmation' => array(
            'name' => esc_html__( 'Confirmation', 'admin-site-enhancements' ),
        ),
        'entries' => array(
            'name' => esc_html__( 'Entries', 'admin-site-enhancements' ),
        ),
        'webhooks' => array(
            'name' => esc_html__( 'Webhooks', 'admin-site-enhancements' ),
        ),
        // 'conditional-logic' => array(
        //     'name' => esc_html__( 'Conditional Logic', 'admin-site-enhancements' ),
        // ),
        'import-export' => array(
            'name' => esc_html__( 'Import/Export', 'admin-site-enhancements' ),
        ),
    );
    $sections = apply_filters( 'formbuilder_settings_sections', $sections );
    $current = 'email-settings';
    ?>

    <div class="fb-body">
        <div class="fb-fields-sidebar">
            <ul class="fb-settings-tab">
                <?php foreach ( $sections as $key => $section ) { ?>
                    <li class="<?php echo ( $current === $key ? 'fb-active' : '' ); ?>">
                        <a href="#fb-<?php echo esc_attr( $key ); ?>">
                            <!-- <i class="<?php // echo esc_attr( $section['icon'] ) ?>"></i> -->
                            <?php echo esc_html( $section['name'] ); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div id="fb-form-panel">
            <?php Form_Builder_Helper::print_message(); ?>
            <div class="fb-form-wrap">
                <form method="post" id="fb-settings-form">
                    <input type="hidden" name="id" id="form_id" value="<?php echo esc_attr( $id ); ?>" />
                    <?php
                    wp_nonce_field( 'formbuilder_process_form_nonce', 'process_form' );
                    foreach ( $sections as $key => $section ) {
                        ?>
                        <div id="fb-<?php echo esc_attr( $key ); ?>" class="<?php echo ( ( $current === $key ) ? '' : ' fb-hidden' ); ?>">
                            <h2><?php echo esc_html( $section['name'] ); ?></h2>
                            <?php
                            $file_path = FORMBUILDER_PATH . 'forms/settings/';
                            if ( file_exists( $file_path . esc_attr( $key ) . '.php' ) ) {
                                require $file_path . esc_attr( $key ) . '.php';
                            }
                            do_action( 'formbuilder_settings_sections_content', array(
                                'section_key' => $key,
                                'settings' => $settings,
                                'fields' => $fields,
                                'form_id' => $id
                            ) );
                            ?>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</div>