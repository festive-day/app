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
        <style>
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

            .image_fix {
                display: block;
            }

            p {
                margin: 1em 0 0 0 !important;
            }

            table td {
                border-collapse: collapse;
            }

            table {
                border-collapse: collapse;
                mso-table-lspace: 0pt;
                mso-table-rspace: 0pt;
            }

            a {
                color: #000;
            }

            .content table:last-child {
                margin-bottom: 0;
            }
            
            .field-row-with-top-border {
                border-top: 5px solid #4183D7;
            }
            
            .field-row-no-top-border {
                border-top: none;
            }
            
            .field-row-highlight-color {
                background: #f9f9ff;
            }
            
            .field-row-regular-color {
                background: #FFFFFF;
            }
            
            <?php echo wp_strip_all_tags( $form_builder_email_custom_css ); ?>
        </style>
    </head>

    <body style="background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f6f6f6; width: 100%; padding: 30px;" width="100%" bgcolor="#f6f6f6">
            <tbody>
                <?php
                if ( $header_image ) {
                    $image = wp_get_attachment_image_src( $header_image, 'full' );
                    $image_alt = get_post_meta( $header_image, '_wp_attachment_image_alt', TRUE );
                    if ( $image ) {
                        ?>
                        <tr class="header-image-tr">
                            <td class="header-image-td" align="center" valign="middle" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; border-collapse: collapse !important; vertical-align: top; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; font-weight: normal; margin: 0; Margin: 0; font-size: 14px; mso-line-height-rule: exactly; line-height: 140%; text-align: center; padding: 30px 30px 22px 30px;">
                                <img src="<?php echo esc_url( $image[0] ); ?>" width="250" alt="<?php echo esc_html( $image_alt ); ?>" style="outline: none; text-decoration: none; max-width: 100%; clear: both; -ms-interpolation-mode: bicubic; display: inline-block !important; width: 250px;" />
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                <tr class="container-tr">
                    <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box;" valign="top">
                        <div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px; line-height:1.6">
                            <?php if ( ! is_null( $email_message ) ) { echo wp_kses_post( htmlspecialchars_decode( $email_message ) ); } ?>
                        </div>
                    </td>
                </tr>
                <tr class="footer-text-tr">
                    <td class="footer-text" style="font-family: sans-serif;font-size: 12px;vertical-align: top;box-sizing: border-box;text-align: center;padding: 20px;color: #555555;" valign="top">
                        <?php echo wp_kses_post(htmlspecialchars_decode( $footer_text ) ); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </body>

</html>