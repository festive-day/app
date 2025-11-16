<?php
/**
 * Settings Tab View
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings
$banner_heading = get_option( 'ccm_banner_heading', __( 'We use cookies', 'cookie-consent-manager' ) );
$banner_message = get_option( 'ccm_banner_message', __( 'This site uses cookies to enhance your experience. By continuing to use this site, you consent to our use of cookies.', 'cookie-consent-manager' ) );
$accept_all_label = get_option( 'ccm_accept_all_label', __( 'Accept All', 'cookie-consent-manager' ) );
$reject_all_label = get_option( 'ccm_reject_all_label', __( 'Reject All', 'cookie-consent-manager' ) );
$manage_label = get_option( 'ccm_manage_label', __( 'Manage Preferences', 'cookie-consent-manager' ) );
$retention_period = get_option( 'ccm_retention_period', 1095 ); // 3 years in days
?>

<div class="ccm-tab-content ccm-tab-settings">
    <h2><?php esc_html_e( 'Settings', 'cookie-consent-manager' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'Configure global settings for the cookie consent banner and audit log retention.', 'cookie-consent-manager' ); ?>
    </p>

    <form id="ccm-settings-form" class="ccm-form">
        <div class="ccm-settings-section">
            <h3><?php esc_html_e( 'Banner Text', 'cookie-consent-manager' ); ?></h3>
            <p class="description">
                <?php esc_html_e( 'Customize the text shown in the cookie consent banner.', 'cookie-consent-manager' ); ?>
            </p>

            <div class="ccm-form-field">
                <label for="ccm-banner-heading">
                    <?php esc_html_e( 'Heading', 'cookie-consent-manager' ); ?>
                </label>
                <input 
                    type="text" 
                    id="ccm-banner-heading" 
                    name="banner_heading" 
                    class="regular-text" 
                    value="<?php echo esc_attr( $banner_heading ); ?>"
                    maxlength="200"
                >
                <p class="description">
                    <?php esc_html_e( 'Main heading displayed in the banner', 'cookie-consent-manager' ); ?>
                </p>
            </div>

            <div class="ccm-form-field">
                <label for="ccm-banner-message">
                    <?php esc_html_e( 'Message', 'cookie-consent-manager' ); ?>
                </label>
                <?php
                wp_editor(
                    $banner_message,
                    'ccm-banner-message',
                    array(
                        'textarea_name' => 'banner_message',
                        'textarea_rows' => 5,
                        'media_buttons' => false,
                        'teeny'         => true,
                        'quicktags'     => false,
                    )
                );
                ?>
                <p class="description">
                    <?php esc_html_e( 'Main message displayed in the banner (HTML allowed)', 'cookie-consent-manager' ); ?>
                </p>
            </div>

            <div class="ccm-form-field">
                <label for="ccm-accept-all-label">
                    <?php esc_html_e( 'Accept All Button Label', 'cookie-consent-manager' ); ?>
                </label>
                <input 
                    type="text" 
                    id="ccm-accept-all-label" 
                    name="accept_all_label" 
                    class="regular-text" 
                    value="<?php echo esc_attr( $accept_all_label ); ?>"
                    maxlength="50"
                >
            </div>

            <div class="ccm-form-field">
                <label for="ccm-reject-all-label">
                    <?php esc_html_e( 'Reject All Button Label', 'cookie-consent-manager' ); ?>
                </label>
                <input 
                    type="text" 
                    id="ccm-reject-all-label" 
                    name="reject_all_label" 
                    class="regular-text" 
                    value="<?php echo esc_attr( $reject_all_label ); ?>"
                    maxlength="50"
                >
            </div>

            <div class="ccm-form-field">
                <label for="ccm-manage-label">
                    <?php esc_html_e( 'Manage Preferences Button Label', 'cookie-consent-manager' ); ?>
                </label>
                <input 
                    type="text" 
                    id="ccm-manage-label" 
                    name="manage_label" 
                    class="regular-text" 
                    value="<?php echo esc_attr( $manage_label ); ?>"
                    maxlength="50"
                >
            </div>
        </div>

        <div class="ccm-settings-section">
            <h3><?php esc_html_e( 'Audit Log Retention', 'cookie-consent-manager' ); ?></h3>
            <p class="description">
                <?php esc_html_e( 'Configure how long consent events are retained in the audit log for compliance purposes.', 'cookie-consent-manager' ); ?>
            </p>

            <div class="ccm-form-field">
                <label for="ccm-retention-period">
                    <?php esc_html_e( 'Retention Period (days)', 'cookie-consent-manager' ); ?>
                </label>
                <input 
                    type="number" 
                    id="ccm-retention-period" 
                    name="retention_period" 
                    class="small-text" 
                    value="<?php echo esc_attr( $retention_period ); ?>"
                    min="365"
                    max="3650"
                    step="1"
                >
                <p class="description">
                    <?php esc_html_e( 'Number of days to retain consent events. Default: 1095 (3 years). Minimum: 365 days.', 'cookie-consent-manager' ); ?>
                </p>
            </div>
        </div>

        <div class="ccm-form-actions">
            <button type="submit" class="button button-primary">
                <?php esc_html_e( 'Save Settings', 'cookie-consent-manager' ); ?>
            </button>
            <span class="ccm-save-message" id="ccm-settings-save-message" style="display: none;"></span>
        </div>
    </form>
</div>

