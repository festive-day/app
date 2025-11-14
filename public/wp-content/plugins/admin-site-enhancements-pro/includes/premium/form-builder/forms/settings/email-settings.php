<?php
defined( 'ABSPATH' ) || die();
?>
<div class="fb-form-container fb-grid-container">
    <div class="fb-form-row fb-multiple-rows fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Send To', 'admin-site-enhancements' ); ?></label>
            <div class="fb-multiple-email">
                <?php
                $email_to_array = explode( ',', $settings['email_to'] );
                foreach ( $email_to_array as $row ) {
                    ?>
                    <div class="fb-email-row">
                        <input type="email" name="email_to[]" value="<?php echo esc_attr( $row ); ?>" />
                        <span class="fb fb-trash-can-outline fb-delete-email-row"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                    </div>
                <?php } ?>
            </div>
            <button type="button" class="button button-primary fb-add-email"><?php esc_html_e( 'Add More Email', 'admin-site-enhancements' ); ?></button>
            <p></p>
            <p class="description"><?php esc_html_e( 'Use [admin_email] for the site administrator\'s email', 'admin-site-enhancements' ); ?></p>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label class="fb-label-with-attr">
                <?php esc_html_e( 'Reply To', 'admin-site-enhancements' ); ?>
                <div class="fb-attr-field">
                    <div class="fb-attr-field-tags">
                        <span class="fb fb-tag-multiple"><?php echo wp_kses( Form_Builder_Icons::get( 'tags' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>Tags
                    </div>
                    <ul class="fb-add-field-attr-to-form">
                        <?php
                        foreach ( $fields as $field ) {
                            if ( $field->type == 'email' ) {
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
            <input type="text" name="reply_to_email" value="<?php echo esc_attr( $settings['reply_to_email'] ); ?>" />
            <p class="description"><?php esc_html_e( 'Choose the email field by clicking on the TAGS above', 'admin-site-enhancements' ); ?></p>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'From Email', 'admin-site-enhancements' ); ?></label>
            <input type="text" name="email_from" value="<?php echo esc_attr( $settings['email_from'] ); ?>" />
            <p class="description"><?php esc_html_e( 'Use [admin_email] for the site administrator\'s email', 'admin-site-enhancements' ); ?></p>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'From Name', 'admin-site-enhancements' ); ?></label>
            <input type="text" name="email_from_name" value="<?php echo esc_attr( $settings['email_from_name'] ); ?>" />
            <p class="description"><?php esc_html_e( 'Use #site_name for the site name', 'admin-site-enhancements' ); ?>: <?php esc_html_e( get_bloginfo( 'name' ) ); ?></p>
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

        <input type="text" name="email_subject" value="<?php echo esc_attr( $settings['email_subject'] ); ?>" />
        <p class="description"><?php esc_html_e( 'Use #form_title for the form title', 'admin-site-enhancements' ); ?>: <?php esc_html_e( $form->name ); ?></p>
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

        <textarea name="email_message" rows="5"><?php echo esc_textarea( $settings['email_message'] ); ?></textarea>
        <p class="description"><?php esc_html_e( 'Use #form_details for including all form data', 'admin-site-enhancements' ); ?></p>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-6">
            <label><?php esc_html_e( 'Footer Text', 'admin-site-enhancements' ); ?></label>
            <input type="text" name="footer_text" value="<?php echo esc_attr( $footer_text ); ?>" />
            <p class="description"><?php esc_html_e( 'Use #linked_site_name for the site name linked to the site URL', 'admin-site-enhancements' ); ?>: <a href="<?php echo esc_attr( get_site_url() ); ?>"><?php esc_html_e( get_bloginfo( 'name' ) ); ?></a></p>
        </div>
    </div>
</div>