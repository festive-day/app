<?php
defined( 'ABSPATH' ) || die();

$hide_form_after_submission = isset( $settings['hide_form_after_submission'] ) ? $settings['hide_form_after_submission'] : 'off';
?>

<div class="fb-form-container fb-grid-container">
    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Confirmation Type', 'admin-site-enhancements' ); ?></label>
            <select name="confirmation_type" data-condition="toggle" id="fb-form-conformation-type">
                <option value="show_message" <?php selected( $settings['confirmation_type'], 'show_message' ); ?>><?php esc_html_e( 'Success Message', 'admin-site-enhancements' ); ?></option>
                <option value="show_page" <?php selected( $settings['confirmation_type'], 'show_page' ); ?>><?php esc_html_e( 'Show Page', 'admin-site-enhancements' ); ?></option>
                <option value="redirect_url" <?php selected( $settings['confirmation_type'], 'redirect_url' ); ?>><?php esc_html_e( 'Redirect URL', 'admin-site-enhancements' ); ?></option>
            </select>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container" data-condition-toggle="fb-form-conformation-type" data-condition-val="show_message">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Success Message', 'admin-site-enhancements' ); ?></label>
            <textarea name="confirmation_message"><?php echo esc_html( $settings['confirmation_message'] ) ?></textarea>
        </div>

        <div class="fb-grid-6">
            <label><?php esc_html_e( 'Hide Form After Successful Submission', 'admin-site-enhancements' ); ?></label>
            <div class="fb-setting-fields fb-toggle-input-field">
                <input type="hidden" name="hide_form_after_submission" value="off">
                <input type="checkbox" id="hide_form_after_submission" name="hide_form_after_submission" value="on" <?php checked( $hide_form_after_submission, 'on', true ); ?>>
            </div>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container" data-condition-toggle="fb-form-conformation-type" data-condition-val="show_page">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Show Page', 'admin-site-enhancements' ); ?></label>
            <select name="show_page_id">
                <?php foreach (get_pages() as $page ) { ?>
                    <option value="<?php echo esc_attr( $page->ID); ?>" <?php selected( $settings['show_page_id'], $page->ID); ?>><?php echo esc_html( $page->post_title ); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container" data-condition-toggle="fb-form-conformation-type" data-condition-val="redirect_url">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Redirect URL', 'admin-site-enhancements' ); ?></label>
            <input type="text" name="redirect_url_page" value="<?php echo esc_attr( $settings['redirect_url_page'] ) ?>" />
        </div>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Error Message', 'admin-site-enhancements' ); ?></label>
            <textarea name="error_message"><?php echo esc_textarea( $settings['error_message'] ) ?></textarea>
        </div>
    </div>
</div>