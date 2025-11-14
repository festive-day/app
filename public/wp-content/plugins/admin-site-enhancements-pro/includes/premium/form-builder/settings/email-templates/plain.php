<?php
defined( 'ABSPATH' ) || die();

$options = get_option( ASENHA_SLUG_U, array() );
$form_builder_email_custom_css = isset( $options['form_builder_email_custom_css'] ) ? $options['form_builder_email_custom_css'] : '';
?>

<!doctype html>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo esc_html(get_bloginfo( 'name' ) ); ?></title>
        <style type="text/css">
            #outlook a {
                padding: 0;
            }

            body {
                width: 100% !important;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
                margin: 0;
                padding: 0;
            }

            .ExternalClass {
                width: 100%;
            }

            .ExternalClass,
            .ExternalClass p,
            .ExternalClass span,
            .ExternalClass font,
            .ExternalClass td,
            .ExternalClass div {
                line-height: 100%;
            }

            #bodyTable {
                height: 100% !important;
                margin: 0;
                padding: 0;
                width: 100% !important;
            }

            .apple-link a {
                color: inherit !important;
                font-family: inherit !important;
                font-size: inherit !important;
                font-weight: inherit !important;
                line-height: inherit !important;
                text-decoration: none !important;
            }

            #MessageViewBody a {
                color: inherit;
                text-decoration: none;
                font-size: inherit;
                font-family: inherit;
                font-weight: inherit;
                line-height: inherit;
            }

            img {
                outline: none;
                text-decoration: none;
                -ms-interpolation-mode: bicubic;
            }

            a img {
                border: none;
            }

            p {
                margin: 1em 0 0 0 !important;
            }

            a {
                color: #000;
            }

            .content p {
                margin: 0 0 20px 0;
            }
            
            <?php echo wp_strip_all_tags( $form_builder_email_custom_css ); ?>
        </style>
    </head>

    <body style="background-color: #fff; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
        <div class="content" style="box-sizing: border-box; display: block; max-width: 600px; margin: 0 0 20px 0; background: #FFFFFF; line-height:1.6">
            <?php if ( ! is_null( $email_message ) ) { echo wp_kses_post( htmlspecialchars_decode( $email_message ) ); } ?>
        </div>
        <div class="footer-text" style="font-family: sans-serif;font-size: 12px;box-sizing: border-box;text-align: left;padding: 0;color: #999;" valign="top">
            <?php echo wp_kses_post(htmlspecialchars_decode( $footer_text ) ); ?>
        </div>
    </body>

</html>