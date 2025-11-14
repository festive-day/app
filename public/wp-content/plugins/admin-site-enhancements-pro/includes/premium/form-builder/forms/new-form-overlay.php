<?php
defined( 'ABSPATH' ) || die();
?>

<div id="fb-add-form-modal">
    <div class="fb-add-form-modal-wrap">
        <form id="fb-add-template" method="post">
            <h3><?php esc_attr_e( 'Create New Form', 'admin-site-enhancements' ); ?></h3>

            <div class="fb-form-row">
                <label for="fb-form-name"><?php esc_html_e( 'Form Name', 'admin-site-enhancements' ); ?></label>
                <input type="text" name="template_name" id="fb-form-name" />
            </div>

            <div class="fb-add-form-footer">
                <a href="#" class="button button-large formbuilder-close-form-modal"><?php esc_html_e( 'Cancel', 'admin-site-enhancements' ); ?></a>
                <button type="submit"><?php esc_html_e( 'Create', 'admin-site-enhancements' ); ?></button>
            </div>
        </form>
    </div>
</div>