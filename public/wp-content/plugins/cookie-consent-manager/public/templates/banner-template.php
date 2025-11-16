<?php
/**
 * Cookie Consent Banner Template
 *
 * Bottom banner with Accept All, Reject All, and Manage Preferences options
 * Uses BEM naming convention and AutomaticCSS utilities
 * Etch theme compatible
 *
 * @package Cookie_Consent_Manager
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the consent banner
 */
function ccm_render_banner_template() {
    if ( function_exists( 'ccm_is_etch_builder_request' ) && ccm_is_etch_builder_request() ) {
        return;
    }
    ?>
    <div id="ccm-banner" class="ccm-banner ccm-banner--hidden" role="dialog" aria-modal="false" aria-labelledby="ccm-banner__heading" aria-describedby="ccm-banner__message">
        <div data-etch-element="section" class="ccm-banner__section">
            <div data-etch-element="container" class="ccm-banner__container">
                <div class="ccm-banner__content">
                    <h2 id="ccm-banner__heading" class="ccm-banner__heading">We use cookies</h2>
                    <p id="ccm-banner__message" class="ccm-banner__message">
                        This site uses cookies to enhance your experience and analyze site usage.
                        You can manage your cookie preferences at any time.
                    </p>
                </div>

                <div class="ccm-banner__actions">
                    <button
                        type="button"
                        class="ccm-banner__btn ccm-banner__btn--accept"
                        id="ccm-accept-all"
                        aria-label="Accept all cookies">
                        Accept All
                    </button>

                    <button
                        type="button"
                        class="ccm-banner__btn ccm-banner__btn--reject"
                        id="ccm-reject-all"
                        aria-label="Reject all non-essential cookies">
                        Reject All
                    </button>

                    <button
                        type="button"
                        class="ccm-banner__btn ccm-banner__btn--manage"
                        id="ccm-manage-preferences"
                        aria-label="Manage cookie preferences">
                        Manage Preferences
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preferences Modal (for Manage Preferences) -->
    <div id="ccm-modal" class="ccm-modal ccm-modal--hidden" role="dialog" aria-modal="true" aria-labelledby="ccm-modal__heading">
        <div class="ccm-modal__overlay" id="ccm-modal-overlay"></div>
        <div class="ccm-modal__container">
            <div class="ccm-modal__header">
                <h2 id="ccm-modal__heading" class="ccm-modal__title">Cookie Preferences</h2>
                <button
                    type="button"
                    class="ccm-modal__close"
                    id="ccm-modal-close"
                    aria-label="Close preferences">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="ccm-modal__body" id="ccm-modal-body">
                <!-- Category list will be populated by JavaScript -->
                <p class="ccm-modal__loading">Loading cookie preferences...</p>
                <!-- Category accordion structure (populated by JavaScript) -->
                <div class="ccm-categories" id="ccm-categories-list" style="display: none;">
                    <!-- Categories will be inserted here -->
                </div>
            </div>

            <div class="ccm-modal__footer">
                <button
                    type="button"
                    class="ccm-modal__btn ccm-modal__btn--cancel"
                    id="ccm-cancel-preferences">
                    Cancel
                </button>
                <button
                    type="button"
                    class="ccm-modal__btn ccm-modal__btn--save"
                    id="ccm-save-preferences">
                    Save Preferences
                </button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render the footer "Cookie Settings" link
 */
function ccm_render_footer_link() {
    if ( function_exists( 'ccm_is_etch_builder_request' ) && ccm_is_etch_builder_request() ) {
        return;
    }
    ?>
    <div data-etch-element="section" class="ccm-footer-link__section">
        <div data-etch-element="container" class="ccm-footer-link__container">
            <a href="#" class="ccm-open-preferences" id="ccm-footer-settings" aria-label="Open cookie settings">
                Cookie Settings
            </a>
        </div>
    </div>
    <?php
}

// Render banner on wp_footer (only when needed - controlled by JavaScript)
add_action( 'wp_footer', 'ccm_render_banner_template', 999 );

// Render footer link
add_action( 'wp_footer', 'ccm_render_footer_link', 1000 );
