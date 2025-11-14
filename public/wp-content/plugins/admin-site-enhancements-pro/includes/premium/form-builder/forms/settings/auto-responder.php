<?php
defined( 'ABSPATH' ) || die();
?>

<div class="fb-form-container fb-grid-container">
    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Enable Auto Responder', 'admin-site-enhancements' ); ?></label>
            <div class="fb-setting-fields fb-toggle-input-field">
                <input type="hidden" name="enable_ar" value="off">
                <input type="checkbox" name="enable_ar" value="on" <?php checked( $settings['enable_ar'], 'on', true ); ?>>
            </div>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'From Email', 'admin-site-enhancements' ); ?></label>
            <input type="text" name="from_ar" value="<?php echo esc_attr( $settings['from_ar'] ) ?>" />
            <p class="description"><?php esc_html_e( 'Use [admin_email] for the site administrator\'s email', 'admin-site-enhancements' ); ?></p>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'From Name', 'admin-site-enhancements' ); ?></label>
            <input type="text" name="from_ar_name" value="<?php echo esc_attr( $settings['from_ar_name'] ) ?>" />
            <p class="description"><?php esc_html_e( 'Use #site_name for the site name', 'admin-site-enhancements' ); ?>: <?php echo esc_html__( get_bloginfo( 'name' ) ); ?></p>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Send To Email', 'admin-site-enhancements' ); ?></label>
            <select name="send_to_ar">
                <option value=""><?php esc_html_e( 'Choose a form field', 'admin-site-enhancements' ); ?></option>
                <?php
                foreach ( $fields as $field ) {
                    if ( $field->type == 'email' ) {
                        ?>
                        <option value="<?php echo esc_attr( $field->id ); ?>" <?php selected( $settings['send_to_ar'], $field->id ); ?>><?php echo esc_html( $field->name ); ?> (ID: <?php echo esc_html( $field->id ); ?>)</option>
                        <?php
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <div class="fb-form-row">
        <label class="fb-label-with-attr">
            <?php esc_html_e( 'Subject', 'admin-site-enhancements' ); ?>
            <div class="fb-attr-field">
                <div class="fb-attr-field-tags">
                    <span class="fb fb-tag-multiple"><?php echo wp_kses( Form_Builder_Icons::get( 'tags' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>Tags
                </div>
                <ul class="fb-add-field-attr-to-form">
                    <?php
                    foreach ( $fields as $field ) {
                        if ( ! in_array( $field->type, $subject_tags_excluded_field_types ) ) {
                            ?>
                            <li data-value="#field_id_<?php echo esc_attr( $field->id ); ?>">
                                <?php echo esc_html( $field->name ); ?><span>#field_id_<?php echo esc_html( $field->id ); ?></span>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        </label>
        
        <input type="text" name="email_subject_ar" value="<?php echo esc_attr( $settings['email_subject_ar'] ) ?>" />
        <p class="description"><?php esc_html_e( 'Use #form_title for the form title', 'admin-site-enhancements' ); ?>: <?php echo esc_html__( $form->name ); ?></p>
    </div>

    <div class="fb-form-row">
        <label class="fb-label-with-attr">
            <?php esc_html_e( 'Message', 'admin-site-enhancements' ); ?>
            <div class="fb-attr-field">
                <div class="fb-attr-field-tags">
                    <span class="fb fb-tag-multiple"><?php echo wp_kses( Form_Builder_Icons::get( 'tags' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>Tags
                </div>
                <ul class="fb-add-field-attr-to-form">
                    <?php
                    foreach ( $fields as $field ) {
                        if ( ! in_array( $field->type, $message_tags_excluded_field_types ) ) {
                            ?>
                            <li data-value="#field_id_<?php echo esc_attr( $field->id ); ?>">
                                <?php echo esc_html( $field->name ); ?><span>#field_id_<?php echo esc_html( $field->id ); ?></span>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        </label>
        
        <textarea name="email_message_ar" cols="50" rows="5"><?php echo ( $settings['email_message_ar'] ? esc_textarea( $settings['email_message_ar'] ) : '' ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Use #form_details for including all form data', 'admin-site-enhancements' ); ?></p>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-6">
            <label><?php esc_html_e( 'Footer Text', 'admin-site-enhancements' ); ?></label>
            <input type="text" name="footer_text_ar" value="<?php echo esc_attr( $footer_text_ar ); ?>" />
            <p class="description"><?php esc_html_e( 'Use #linked_site_name for the site name linked to the site URL', 'admin-site-enhancements' ); ?>: <a href="<?php echo esc_attr( get_site_url() ); ?>"><?php esc_html_e( get_bloginfo( 'name' ) ); ?></a></p>
        </div>
    </div>
</div>