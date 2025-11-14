<?php
defined( 'ABSPATH' ) || die();
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

    <head>
        <title><?php bloginfo( 'name' ); ?> | <?php echo esc_html( $form->name ); ?></title>
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <?php wp_head(); ?>
    </head>

    <body class="formbuilder_preview_page">
        <?php
        // Enqueue pre-registered styles in /classes/form-builder-loader.php
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style( 'jquery-timepicker' );
        wp_enqueue_style( 'formbuilder-file-uploader' );
        wp_enqueue_style( 'formbuilder-style' );

        $fonts_url = Form_Builder_Styles::fonts_url();

        if ( $fonts_url ) {
            wp_enqueue_style( 'formbuilder-fonts' );
        }
        
        // Enqueue pre-registered scripts in /classes/form-builder-loader.php
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'jquery-timepicker' );
        wp_enqueue_script( 'formbuilder-file-uploader' );
        wp_enqueue_script( 'moment' );
        wp_enqueue_script( 'frontend' );

        Form_Builder_Preview::show_form( $form->id );
        wp_footer();
        ?>
    </body>

</html>