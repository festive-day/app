<?php
/**
 * Cookies Tab View
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="ccm-tab-content ccm-tab-cookies">
    <h2><?php esc_html_e( 'Cookie Registry', 'cookie-consent-manager' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Manage cookies that are tracked and displayed to visitors. Cookies are organized by category.', 'cookie-consent-manager' ); ?>
    </p>

    <div class="ccm-cookies-section">
        <div class="ccm-section-header">
            <h3><?php esc_html_e( 'Cookies', 'cookie-consent-manager' ); ?></h3>
            <button type="button" class="button button-primary ccm-btn-add-cookie">
                <?php esc_html_e( 'Add Cookie', 'cookie-consent-manager' ); ?>
            </button>
        </div>

        <div class="ccm-filters">
            <label for="ccm-filter-category">
                <?php esc_html_e( 'Filter by Category:', 'cookie-consent-manager' ); ?>
            </label>
            <select id="ccm-filter-category" class="ccm-filter-select">
                <option value=""><?php esc_html_e( 'All Categories', 'cookie-consent-manager' ); ?></option>
            </select>
        </div>

        <div class="ccm-cookies-list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="column-name"><?php esc_html_e( 'Cookie Name', 'cookie-consent-manager' ); ?></th>
                        <th class="column-category"><?php esc_html_e( 'Category', 'cookie-consent-manager' ); ?></th>
                        <th class="column-provider"><?php esc_html_e( 'Provider', 'cookie-consent-manager' ); ?></th>
                        <th class="column-purpose"><?php esc_html_e( 'Purpose', 'cookie-consent-manager' ); ?></th>
                        <th class="column-expiration"><?php esc_html_e( 'Expiration', 'cookie-consent-manager' ); ?></th>
                        <th class="column-actions"><?php esc_html_e( 'Actions', 'cookie-consent-manager' ); ?></th>
                    </tr>
                </thead>
                <tbody id="ccm-cookies-tbody">
                    <tr class="ccm-loading">
                        <td colspan="6" class="ccm-loading-message">
                            <?php esc_html_e( 'Loading cookies...', 'cookie-consent-manager' ); ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="ccm-pagination" id="ccm-cookies-pagination" style="display: none;">
                <!-- Pagination will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add/Edit Cookie Modal -->
    <div id="ccm-cookie-modal" class="ccm-modal" style="display: none;">
        <div class="ccm-modal-content">
            <div class="ccm-modal-header">
                <h3 class="ccm-modal-title"><?php esc_html_e( 'Add Cookie', 'cookie-consent-manager' ); ?></h3>
                <button type="button" class="ccm-modal-close" aria-label="<?php esc_attr_e( 'Close', 'cookie-consent-manager' ); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <form id="ccm-cookie-form" class="ccm-form">
                <input type="hidden" id="ccm-cookie-id" name="id" value="">
                
                <div class="ccm-form-field">
                    <label for="ccm-cookie-name">
                        <?php esc_html_e( 'Cookie Name', 'cookie-consent-manager' ); ?>
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="ccm-cookie-name" 
                        name="name" 
                        class="regular-text" 
                        required
                        maxlength="255"
                        placeholder="e.g., _ga, _fbp"
                    >
                    <p class="description">
                        <?php esc_html_e( 'The exact name of the cookie as it appears in the browser', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label for="ccm-cookie-category">
                        <?php esc_html_e( 'Category', 'cookie-consent-manager' ); ?>
                        <span class="required">*</span>
                    </label>
                    <select id="ccm-cookie-category" name="category_id" class="regular-text" required>
                        <option value=""><?php esc_html_e( 'Select a category', 'cookie-consent-manager' ); ?></option>
                    </select>
                    <p class="description">
                        <?php esc_html_e( 'The category this cookie belongs to', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label for="ccm-cookie-provider">
                        <?php esc_html_e( 'Provider', 'cookie-consent-manager' ); ?>
                    </label>
                    <input 
                        type="text" 
                        id="ccm-cookie-provider" 
                        name="provider" 
                        class="regular-text" 
                        maxlength="255"
                        placeholder="e.g., Google Analytics, Facebook Pixel"
                    >
                    <p class="description">
                        <?php esc_html_e( 'Who sets this cookie (optional)', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label for="ccm-cookie-purpose">
                        <?php esc_html_e( 'Purpose', 'cookie-consent-manager' ); ?>
                        <span class="required">*</span>
                    </label>
                    <textarea 
                        id="ccm-cookie-purpose" 
                        name="purpose" 
                        class="large-text" 
                        rows="4"
                        required
                        maxlength="500"
                        placeholder="<?php esc_attr_e( 'Describe what this cookie is used for...', 'cookie-consent-manager' ); ?>"
                    ></textarea>
                    <p class="description">
                        <?php esc_html_e( 'Explain why this cookie exists and what it does (required for GDPR compliance)', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label for="ccm-cookie-expiration">
                        <?php esc_html_e( 'Expiration', 'cookie-consent-manager' ); ?>
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="ccm-cookie-expiration" 
                        name="expiration" 
                        class="regular-text" 
                        required
                        maxlength="100"
                        placeholder="e.g., Session, 1 year, 90 days"
                    >
                    <p class="description">
                        <?php esc_html_e( 'How long the cookie lasts (freeform text)', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-field">
                    <label for="ccm-cookie-domain">
                        <?php esc_html_e( 'Domain', 'cookie-consent-manager' ); ?>
                    </label>
                    <input 
                        type="text" 
                        id="ccm-cookie-domain" 
                        name="domain" 
                        class="regular-text" 
                        maxlength="255"
                        placeholder="e.g., .example.com, example.com"
                    >
                    <p class="description">
                        <?php esc_html_e( 'Cookie domain scope (optional)', 'cookie-consent-manager' ); ?>
                    </p>
                </div>

                <div class="ccm-form-actions">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Save Cookie', 'cookie-consent-manager' ); ?>
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

