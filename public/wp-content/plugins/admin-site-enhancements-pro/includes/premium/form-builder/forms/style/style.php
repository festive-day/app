<?php
defined( 'ABSPATH' ) || die();

$id = htmlspecialchars_decode( Form_Builder_Helper::get_var( 'id', 'absint' ) );
$form = Form_Builder_Builder::get_form_vars( $id );

if ( ! $form ) {
    echo '<h3>' . esc_html__( 'You are trying to edit a form that does not exist.', 'admin-site-enhancements' ) . '</h3>';
    return;
}

$fields = Form_Builder_Fields::get_form_fields( $id );
$styles = $form->styles ? $form->styles : array();
$form_style = isset( $styles['form_style'] ) ? $styles['form_style'] : 'default-style';
$form_style_template = isset( $styles['form_style_template'] ) ? $styles['form_style_template'] : '';
?>
<div id="fb-wrap" class="fb-content fb-form-style-template">
    <?php
    self::get_admin_header(
        array(
            'form' => $form,
            'class' => 'fb-header-nav',
        )
    );
    ?>
    <div class="fb-body">
        <div class="fb-fields-sidebar">
            <form class="fb-fields-panel" method="post" id="fb-style-form">
                <input type="hidden" name="id" id="fb-form-id" value="<?php echo absint( $id ); ?>" />
                <div class="fb-form-container fb-grid-container">
                    <div class="fb-form-row">
                        <label><?php esc_html_e( 'Form Style', 'admin-site-enhancements' ); ?></label>
                        <select name="form_style" id="fb-form-style-select" data-condition="toggle">
                            <option value="no-style" <?php isset( $form_style ) ? selected( 'no-style', $form_style ) : ''; ?>><?php esc_html_e( 'No Style', 'admin-site-enhancements' ); ?></option>
                            <option value="default-style" <?php isset( $form_style ) ? selected( 'default-style', $form_style ) : ''; ?>><?php esc_html_e( 'Default Style', 'admin-site-enhancements' ); ?></option>
                            <option value="custom-style" <?php isset( $form_style ) ? selected( 'custom-style', $form_style ) : ''; ?>><?php esc_html_e( 'Custom Style', 'admin-site-enhancements' ); ?></option>
                        </select>
                    </div>

                    <div class="fb-form-row" data-condition-toggle="fb-form-style-select" data-condition-val="no-style">
                        <?php esc_html_e( 'Choose this to use the theme\'s style.', 'admin-site-enhancements' ); ?>
                        <br><br>
                        <?php esc_html_e( 'The preview seen here will use the Form Builder default style. Please preview on the frontend to see the theme\'s style applied.', 'admin-site-enhancements' ); ?>
                    </div>

                    <div class="fb-form-row" data-condition-toggle="fb-form-style-select" data-condition-val="default-style">
                        <?php esc_html_e( 'Choose this when you want to use the Form Builder default, minimalist style.', 'admin-site-enhancements' ); ?>
                    </div>

                    <div class="fb-form-row" data-condition-toggle="fb-form-style-select" data-condition-val="custom-style">
                        <?php esc_html_e( 'Choose this when you want to implement your own style.', 'admin-site-enhancements' ); ?> <?php printf(/* translators: %1$s: opening <a> tag, %2$s: closing </a> tag */esc_html__( 'To create a new Custom Style, go to the %1$sStyles%2$s page.', 'admin-site-enhancements' ), '<a href="' . esc_url(admin_url( 'edit.php?post_type=formbuilder-styles' ) ) . '" target="_blank">', '</a>' ); ?>
                    </div>

                    <div class="fb-form-row" data-condition-toggle="fb-form-style-select" data-condition-val="custom-style">
                        <label><?php esc_html_e( 'Choose Style Template', 'admin-site-enhancements' ); ?></label>
                        <select name="form_style_template" id="fb-form-style-template">
                            <option value="">-- <?php esc_html_e( 'Choose one', 'admin-site-enhancements' ); ?> --</option>
                            <?php
                            $args = array(
                                'post_type' => 'formbuilder-styles',
                                'posts_per_page' => -1,
                                'post_status' => 'publish'
                            );
                            $query = new WP_Query( $args );
                            $posts = $query->posts;
                            foreach ( $posts as $post ) {
                                $formbuilder_styles = get_post_meta( $post->ID, 'formbuilder_styles', true );

                                if ( ! $formbuilder_styles ) {
                                    $formbuilder_styles = Form_Builder_Styles::default_styles();
                                } else {
                                    $formbuilder_styles = Form_Builder_Helper::recursive_parse_args( $formbuilder_styles, Form_Builder_Styles::default_styles() );
                                }
                                ob_start();
                                echo '#fb-container-' . absint( $id ) . '{';
                                Form_Builder_Styles::get_style_vars( $formbuilder_styles, '' );
                                echo '}';
                                $tmpl_css_style = ob_get_clean();
                                ?>
                                <option value="<?php echo esc_attr( $post->ID); ?>" data-style="<?php echo esc_attr( $tmpl_css_style ); ?>" <?php selected( $post->ID, $form_style_template ); ?>><?php echo esc_html( $post->post_title ); ?></option>
                                <?php
                            }
                            wp_reset_postdata();
                            ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div id="fb-form-panel">
            <div class="fb-form-wrap">
                <?php Form_Builder_Preview::show_form( $form->id ); ?>
            </div>
        </div>
    </div>
</div>