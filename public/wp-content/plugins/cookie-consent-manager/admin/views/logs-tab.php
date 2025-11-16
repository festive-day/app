<?php
/**
 * Audit Logs Tab View
 *
 * @package Cookie_Consent_Manager
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="ccm-tab-content ccm-tab-logs">
    <h2><?php esc_html_e( 'Consent Audit Logs', 'cookie-consent-manager' ); ?></h2>
    <p class="description">
        <?php esc_html_e( 'View all consent events recorded for compliance purposes. Logs are retained for 3 years.', 'cookie-consent-manager' ); ?>
    </p>

    <div class="ccm-logs-section">
        <div class="ccm-section-header">
            <h3><?php esc_html_e( 'Consent Events', 'cookie-consent-manager' ); ?></h3>
            <button type="button" class="button ccm-btn-export-logs">
                <?php esc_html_e( 'Export CSV', 'cookie-consent-manager' ); ?>
            </button>
        </div>

        <div class="ccm-filters">
            <div class="ccm-filter-group">
                <label for="ccm-filter-start-date">
                    <?php esc_html_e( 'Start Date:', 'cookie-consent-manager' ); ?>
                </label>
                <input 
                    type="date" 
                    id="ccm-filter-start-date" 
                    class="ccm-filter-input"
                >
            </div>

            <div class="ccm-filter-group">
                <label for="ccm-filter-end-date">
                    <?php esc_html_e( 'End Date:', 'cookie-consent-manager' ); ?>
                </label>
                <input 
                    type="date" 
                    id="ccm-filter-end-date" 
                    class="ccm-filter-input"
                >
            </div>

            <div class="ccm-filter-group">
                <label for="ccm-filter-event-type">
                    <?php esc_html_e( 'Event Type:', 'cookie-consent-manager' ); ?>
                </label>
                <select id="ccm-filter-event-type" class="ccm-filter-select">
                    <option value=""><?php esc_html_e( 'All Types', 'cookie-consent-manager' ); ?></option>
                    <option value="accept_all"><?php esc_html_e( 'Accept All', 'cookie-consent-manager' ); ?></option>
                    <option value="reject_all"><?php esc_html_e( 'Reject All', 'cookie-consent-manager' ); ?></option>
                    <option value="accept_partial"><?php esc_html_e( 'Accept Partial', 'cookie-consent-manager' ); ?></option>
                    <option value="modify"><?php esc_html_e( 'Modify', 'cookie-consent-manager' ); ?></option>
                    <option value="revoke"><?php esc_html_e( 'Revoke', 'cookie-consent-manager' ); ?></option>
                </select>
            </div>

            <div class="ccm-filter-group">
                <button type="button" class="button ccm-btn-apply-filters">
                    <?php esc_html_e( 'Apply Filters', 'cookie-consent-manager' ); ?>
                </button>
                <button type="button" class="button ccm-btn-clear-filters">
                    <?php esc_html_e( 'Clear', 'cookie-consent-manager' ); ?>
                </button>
            </div>
        </div>

        <div class="ccm-logs-list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th class="column-id"><?php esc_html_e( 'ID', 'cookie-consent-manager' ); ?></th>
                        <th class="column-timestamp"><?php esc_html_e( 'Timestamp', 'cookie-consent-manager' ); ?></th>
                        <th class="column-event-type"><?php esc_html_e( 'Event Type', 'cookie-consent-manager' ); ?></th>
                        <th class="column-accepted"><?php esc_html_e( 'Accepted Categories', 'cookie-consent-manager' ); ?></th>
                        <th class="column-rejected"><?php esc_html_e( 'Rejected Categories', 'cookie-consent-manager' ); ?></th>
                        <th class="column-version"><?php esc_html_e( 'Version', 'cookie-consent-manager' ); ?></th>
                        <th class="column-ip"><?php esc_html_e( 'IP Address', 'cookie-consent-manager' ); ?></th>
                    </tr>
                </thead>
                <tbody id="ccm-logs-tbody">
                    <tr class="ccm-loading">
                        <td colspan="7" class="ccm-loading-message">
                            <?php esc_html_e( 'Loading logs...', 'cookie-consent-manager' ); ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="ccm-pagination" id="ccm-logs-pagination" style="display: none;">
                <!-- Pagination will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

