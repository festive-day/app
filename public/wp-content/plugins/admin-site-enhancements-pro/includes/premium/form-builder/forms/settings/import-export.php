<?php
defined( 'ABSPATH' ) || die();
?>

<div class="fb-form-container fb-grid-container">
    <div class="fb-form-row">
        <?php esc_html_e("You can export the settings and then import the form in the same or different website.", "admin-site-enhancements"); ?>
    </div>

    <div class="fb-form-row">
        <h3><?php esc_html_e( 'Export', 'admin-site-enhancements' ); ?></h3>
        <form method="post"></form>
        <form method="post">
            <input type="hidden" name="formbuilder_imex_action" value="export_form" />
            <input type="hidden" name="formbuilder_form_id" value="<?php echo esc_attr( $id ); ?>" />
            <?php wp_nonce_field("formbuilder_imex_export_nonce", "formbuilder_imex_export_nonce"); ?>
            <button class="button button-primary" id="formbuilder_export" name="formbuilder_export"><?php esc_html_e("Export Form", "admin-site-enhancements") ?></button>
        </form>
    </div>

    <div class="fb-form-row"></div>

    <div class="fb-form-row">
        <h3><?php esc_html_e( 'Import', 'admin-site-enhancements' ); ?></h3>
        <form method="post" enctype="multipart/form-data">
            <div class="fb-preview-zone hidden">
                <div class="fb-box fb-box-solid">
                    <div class="fb-box-body"></div>
                    <button type="button" class="button fb-remove-preview">
                        <span class="fb fb-window-close"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                    </button>
                </div>
            </div>
            <div class="fb-dropzone-wrapper">
                <div class="fb-dropzone-desc">
                    <span class="fb fb-file-image-plus-outline"><?php echo wp_kses( Form_Builder_Icons::get( 'file_generic' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                    <p><?php esc_html_e("Choose a JSON file or drag it here", "admin-site-enhancements"); ?></p>
                </div>
                <input type="file" name="formbuilder_import_file" class="fb-dropzone">
            </div>
            <button class="button button-primary" id="formbuilder_import" type="submit" name="formbuilder_import"><i class='icofont-download'></i> <?php esc_html_e("Import", "admin-site-enhancements") ?></button>
            <input type="hidden" name="formbuilder_imex_action" value="import_form" />
            <input type="hidden" name="formbuilder_form_id" value="<?php echo esc_attr( $id ); ?>" />
            <?php wp_nonce_field("formbuilder_imex_import_nonce", "formbuilder_imex_import_nonce"); ?>
        </form>
    </div>
</div>