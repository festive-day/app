<?php
defined( 'ABSPATH' ) || die();

$enable_db_entries = isset( $settings['enable_db_entries'] ) ? $settings['enable_db_entries'] : 'on';
$entry_preview_field_id = isset( $settings['entry_preview_field_id'] ) ? $settings['entry_preview_field_id'] : '';
?>

<div class="fb-form-container fb-grid-container">
    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-6">
            <label><?php esc_html_e( 'Save Form Submissions to the Database', 'admin-site-enhancements' ); ?></label>
            <div class="fb-setting-fields fb-toggle-input-field">
                <input type="hidden" name="enable_db_entries" value="off">
                <input type="checkbox" id="enable_db_entries" name="enable_db_entries" value="on" <?php checked( $enable_db_entries, 'on', true ); ?>>
            </div>
        </div>
    </div>

    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Content for the Preview Column', 'admin-site-enhancements' ); ?></label>
            <select name="entry_preview_field_id">
                <option value=""><?php esc_html_e( 'Choose a form field', 'admin-site-enhancements' ); ?></option>
                <?php
                $applicable_field_types = array(
                    'name',
                    'email',
                    'url',
                    'phone',
                    'address',
                    'text',
                    'textarea',
                    'number',
                    'range_slider',
                    'spinner',
                    'star',
                    'scale',
                    'select',
                    'radio',
                    'checkbox',
                    'image_select',
                    'upload',
                    'date',
                    'time',
                    'hidden',
                );
                foreach ( $fields as $field ) {
                    if ( in_array( $field->type, $applicable_field_types ) ) {
                        ?>
                        <option value="<?php echo esc_attr( $field->id ); ?>" <?php selected( $entry_preview_field_id, $field->id ); ?>><?php echo esc_html( $field->name ); ?> (ID: <?php echo esc_html( $field->id ); ?>)</option>
                        <?php
                    }
                }
                ?>
            </select>
            <p class="description"><?php esc_html_e( 'This is for the Entries page', 'admin-site-enhancements' ); ?></p>
        </div>
    </div>
</div>