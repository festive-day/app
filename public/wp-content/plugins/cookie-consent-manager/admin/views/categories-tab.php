<?php
/**
 * Categories Tab View
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="ccm-tab-content ccm-tab-categories">
    <h2><?php esc_html_e( 'Cookie Categories', 'cookie-consent-manager' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Manage cookie categories that visitors can accept or reject. Essential categories cannot be deleted.', 'cookie-consent-manager' ); ?>
    </p>

    <div class="ccm-categories-section">
        <div class="ccm-section-header">
            <h3><?php esc_html_e( 'Categories', 'cookie-consent-manager' ); ?></h3>
            <button type="button" class="button button-primary ccm-btn-add-category">
                <?php esc_html_e( 'Add Category', 'cookie-consent-manager' ); ?>
            </button>
        </div>

        <div class="ccm-categories-list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="column-name"><?php esc_html_e( 'Name', 'cookie-consent-manager' ); ?></th>
                        <th class="column-slug"><?php esc_html_e( 'Slug', 'cookie-consent-manager' ); ?></th>
                        <th class="column-description"><?php esc_html_e( 'Description', 'cookie-consent-manager' ); ?></th>
                        <th class="column-required"><?php esc_html_e( 'Required', 'cookie-consent-manager' ); ?></th>
                        <th class="column-cookies"><?php esc_html_e( 'Cookies', 'cookie-consent-manager' ); ?></th>
                        <th class="column-actions"><?php esc_html_e( 'Actions', 'cookie-consent-manager' ); ?></th>
                    </tr>
                </thead>
                <tbody id="ccm-categories-tbody">
                    <tr class="ccm-loading">
                        <td colspan="6" class="ccm-loading-message">
                            <?php esc_html_e( 'Loading categories...', 'cookie-consent-manager' ); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div id="ccm-category-modal" class="ccm-modal" style="display: none;">
        <div class="ccm-modal-content">
            <div class="ccm-modal-header">
                <h3 class="ccm-modal-title"><?php esc_html_e( 'Add Category', 'cookie-consent-manager' ); ?></h3>
                <button type="button" class="ccm-modal-close" aria-label="<?php esc_attr_e( 'Close', 'cookie-consent-manager' ); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <form id="ccm-category-form" class="ccm-form">
                <input type="hidden" id="ccm-category-id" name="id" value="">
                
                <div class="ccm-form-field">
                    <label for="ccm-category-slug">
                        <?php esc_html_e( 'Slug', 'cookie-consent-manager' ); ?>
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="ccm-category-slug" 
                        name="slug" 
                        class="regular-text" 
                        required
                        pattern="[a-z0-9-]+"
                        title="<?php esc_attr_e( 'Lowercase letters, numbers, and hyphens only', 'cookie-consent-manager' ); ?>"
                    >
                    <p class="description">
                        <?php esc_html_e( 'Machine-readable identifier (lowercase, alphanumeric, hyphens only)', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label for="ccm-category-name">
                        <?php esc_html_e( 'Name', 'cookie-consent-manager' ); ?>
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="ccm-category-name" 
                        name="name" 
                        class="regular-text" 
                        required
                        maxlength="100"
                    >
                    <p class="description">
                        <?php esc_html_e( 'Human-readable category name shown to visitors', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label for="ccm-category-description">
                        <?php esc_html_e( 'Description', 'cookie-consent-manager' ); ?>
                    </label>
                    <?php
                    wp_editor(
                        '',
                        'ccm-category-description',
                        array(
                            'textarea_name' => 'description',
                            'textarea_rows' => 5,
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => false,
                        )
                    );
                    ?>
                    <p class="description">
                        <?php esc_html_e( 'Description shown to visitors in the cookie consent banner', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label for="ccm-category-display-order">
                        <?php esc_html_e( 'Display Order', 'cookie-consent-manager' ); ?>
                    </label>
                    <input 
                        type="number" 
                        id="ccm-category-display-order" 
                        name="display_order" 
                        class="small-text" 
                        value="10"
                        min="0"
                    >
                    <p class="description">
                        <?php esc_html_e( 'Lower numbers appear first in the banner (default: 10)', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label>
                        <input 
                            type="checkbox" 
                            id="ccm-category-required" 
                            name="is_required" 
                            value="1"
                        >
                        <?php esc_html_e( 'Required (cannot be rejected by visitors)', 'cookie-consent-manager' ); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e( 'Only one category should be marked as required (typically Essential)', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Save Category', 'cookie-consent-manager' ); ?>
                    </button>
                    <button type="button" class="button ccm-modal-cancel">
                        <?php esc_html_e( 'Cancel', 'cookie-consent-manager' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="ccm-modal-overlay" id="ccm-modal-overlay" style="display: none;"></div>
</div>

