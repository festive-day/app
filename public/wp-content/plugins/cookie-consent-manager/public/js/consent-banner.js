/**
 * Consent Banner
 *
 * Handles banner display, user interactions, and consent recording
 *
 * @package Cookie_Consent_Manager
 */

(function(window, document) {
    'use strict';

    /**
     * Cookie Consent Manager object
     */
    window.CookieConsentManager = {

        config: null,
        callbacks: {
            onConsentGiven: null,
            onConsentChanged: null
        },

        /**
         * Initialize the consent manager
         *
         * @param {Object} options Configuration options
         */
        init: function(options) {
            options = options || {};

            // Set callbacks
            this.callbacks.onConsentGiven = options.onConsentGiven || null;
            this.callbacks.onConsentChanged = options.onConsentChanged || null;

            // Load banner configuration
            this.loadConfig().then(() => {
                this.setupEventListeners();
                this.checkAndShowBanner();
            }).catch(error => {
                console.error('CCM: Failed to initialize:', error);
            });
        },

        /**
         * Load banner configuration from server
         *
         * @returns {Promise} Configuration data
         */
        loadConfig: function() {
            return fetch(window.CCM_AJAX_URL + '?action=ccm_get_banner_config', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Failed to load configuration');
                }
                this.config = data.data;
                return this.config;
            });
        },

        /**
         * Setup event listeners for banner interactions
         */
        setupEventListeners: function() {
            // Accept All button
            const acceptBtn = document.getElementById('ccm-accept-all');
            if (acceptBtn) {
                acceptBtn.addEventListener('click', this.handleAcceptAll.bind(this));
            }

            // Reject All button
            const rejectBtn = document.getElementById('ccm-reject-all');
            if (rejectBtn) {
                rejectBtn.addEventListener('click', this.handleRejectAll.bind(this));
            }

            // Manage Preferences button
            const manageBtn = document.getElementById('ccm-manage-preferences');
            if (manageBtn) {
                manageBtn.addEventListener('click', this.openPreferences.bind(this));
            }

            // Footer "Cookie Settings" link
            const footerLink = document.getElementById('ccm-footer-settings');
            if (footerLink) {
                footerLink.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.openPreferences();
                });
            }

            // Modal close button
            const closeBtn = document.getElementById('ccm-modal-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', this.closeModal.bind(this));
            }

            // Modal cancel button
            const cancelBtn = document.getElementById('ccm-cancel-preferences');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', this.closeModal.bind(this));
            }

            // Modal save button
            const saveBtn = document.getElementById('ccm-save-preferences');
            if (saveBtn) {
                saveBtn.addEventListener('click', this.handleSavePreferences.bind(this));
            }

            // Modal overlay click
            const overlay = document.getElementById('ccm-modal-overlay');
            if (overlay) {
                overlay.addEventListener('click', this.closeModal.bind(this));
            }

            // Check DNT header
            this.checkDoNotTrack();
        },

        /**
         * Check if banner should be shown
         */
        checkAndShowBanner: function() {
            const consent = window.CookieConsentStorage.getConsent();

            // No consent - show banner
            if (!consent) {
                this.showBanner();
                return;
            }

            // Consent exists but version mismatch - re-prompt
            if (!window.CookieConsentStorage.isVersionCurrent(consent)) {
                console.info('CCM: Cookie policy updated, re-prompting...');
                this.showBanner(true); // true = policy updated
                return;
            }

            // Consent exists and valid - banner stays hidden
            this.hideBanner();
        },

        /**
         * Show banner
         *
         * @param {boolean} policyUpdated Show "policy updated" message
         */
        showBanner: function(policyUpdated) {
            const banner = document.getElementById('ccm-banner');
            if (!banner) {
                return;
            }

            // Update message if policy updated
            if (policyUpdated) {
                const message = banner.querySelector('.ccm-banner__message');
                if (message) {
                    message.textContent = 'Our cookie policy has been updated. Please review your preferences.';
                }
            }

            // Show banner with animation
            banner.classList.remove('ccm-banner--hidden');
            banner.classList.add('ccm-banner--show');
        },

        /**
         * Hide banner
         */
        hideBanner: function() {
            const banner = document.getElementById('ccm-banner');
            if (!banner) {
                return;
            }

            banner.classList.add('ccm-banner--hide');

            // After animation, add hidden class
            setTimeout(() => {
                banner.classList.add('ccm-banner--hidden');
                banner.classList.remove('ccm-banner--show', 'ccm-banner--hide');
            }, 300);
        },

        /**
         * Handle "Accept All" button click
         */
        handleAcceptAll: function() {
            if (!this.config) {
                console.error('CCM: Configuration not loaded');
                return;
            }

            // Get all category slugs
            const allCategories = this.config.categories.map(cat => cat.slug);

            // Create consent object
            const consent = {
                consentGiven: true,
                acceptedCategories: allCategories,
                rejectedCategories: [],
                version: this.config.consent_version
            };

            // Save consent
            this.saveConsent(consent, 'accept_all');
        },

        /**
         * Handle "Reject All" button click
         */
        handleRejectAll: function() {
            if (!this.config) {
                console.error('CCM: Configuration not loaded');
                return;
            }

            // Get required categories only (essential)
            const requiredCategories = this.config.categories
                .filter(cat => cat.is_required)
                .map(cat => cat.slug);

            // Get optional categories
            const optionalCategories = this.config.categories
                .filter(cat => !cat.is_required)
                .map(cat => cat.slug);

            // Create consent object
            const consent = {
                consentGiven: true,
                acceptedCategories: requiredCategories,
                rejectedCategories: optionalCategories,
                version: this.config.consent_version
            };

            // Save consent
            this.saveConsent(consent, 'reject_all');
        },

        /**
         * Open preferences modal
         */
        openPreferences: function() {
            const modal = document.getElementById('ccm-modal');
            if (!modal) {
                return;
            }

            // Populate modal with categories
            this.populateModal();

            // Show modal with animation
            modal.classList.remove('ccm-modal--hidden');
            modal.classList.add('ccm-modal--show');
        },

        /**
         * Close preferences modal
         */
        closeModal: function() {
            const modal = document.getElementById('ccm-modal');
            if (!modal) {
                return;
            }

            modal.classList.add('ccm-modal--hidden');
            modal.classList.remove('ccm-modal--show');
        },

        /**
         * Populate modal with category checkboxes
         */
        populateModal: function() {
            if (!this.config) {
                return;
            }

            const modalBody = document.getElementById('ccm-modal-body');
            if (!modalBody) {
                return;
            }

            // Get current consent
            const consent = window.CookieConsentStorage.getConsent();

            // Build HTML
            let html = '<div class="ccm-categories">';

            this.config.categories.forEach(category => {
                const isChecked = consent && consent.acceptedCategories && consent.acceptedCategories.includes(category.slug);
                const isRequired = category.is_required;

                html += '<div class="ccm-category">';
                html += '<div class="ccm-category__header">';
                html += '<label class="ccm-category__label">';
                html += '<input type="checkbox" ';
                html += 'name="category[]" ';
                html += 'value="' + category.slug + '" ';
                html += isChecked ? 'checked ' : '';
                html += isRequired ? 'disabled ' : '';
                html += 'class="ccm-category__checkbox">';
                html += '<span class="ccm-category__name">' + category.name + '</span>';
                if (isRequired) {
                    html += ' <span class="ccm-category__required">(Required)</span>';
                }
                html += '</label>';
                html += '</div>';
                html += '<p class="ccm-category__description">' + category.description + '</p>';

                // Show cookies in category
                if (category.cookies && category.cookies.length > 0) {
                    html += '<div class="ccm-category__cookies">';
                    html += '<strong>Cookies:</strong> ';
                    html += category.cookies.map(c => c.name).join(', ');
                    html += '</div>';
                }

                html += '</div>';
            });

            html += '</div>';

            modalBody.innerHTML = html;
        },

        /**
         * Handle "Save Preferences" button click
         */
        handleSavePreferences: function() {
            // Get selected categories
            const checkboxes = document.querySelectorAll('.ccm-category__checkbox');
            const acceptedCategories = [];
            const rejectedCategories = [];

            checkboxes.forEach(checkbox => {
                if (checkbox.checked || checkbox.disabled) {
                    acceptedCategories.push(checkbox.value);
                } else {
                    rejectedCategories.push(checkbox.value);
                }
            });

            // Create consent object
            const consent = {
                consentGiven: true,
                acceptedCategories: acceptedCategories,
                rejectedCategories: rejectedCategories,
                version: this.config.consent_version
            };

            // Determine event type
            const existingConsent = window.CookieConsentStorage.getConsent();
            const eventType = existingConsent ? 'modify' : 'accept_partial';

            // Save consent
            this.saveConsent(consent, eventType);

            // Close modal
            this.closeModal();
        },

        /**
         * Save consent to storage and log event
         *
         * @param {Object} consent Consent object
         * @param {string} eventType Event type (accept_all, reject_all, etc.)
         */
        saveConsent: function(consent, eventType) {
            // Save to localStorage and cookie
            const saved = window.CookieConsentStorage.setConsent(consent);

            if (!saved) {
                console.error('CCM: Failed to save consent');
                return;
            }

            // Log event to server
            this.recordConsentEvent(eventType, consent);

            // Hide banner
            this.hideBanner();

            // Fire callback
            if (eventType === 'modify' && this.callbacks.onConsentChanged) {
                this.callbacks.onConsentChanged(consent);
            } else if (this.callbacks.onConsentGiven) {
                this.callbacks.onConsentGiven(consent);
            }

            // Dispatch custom event for other scripts
            const event = new CustomEvent('ccm-consent-changed', {
                detail: consent
            });
            window.dispatchEvent(event);

            // Reload page if needed (to activate scripts)
            // Optional: Only reload if new categories were accepted
            // window.location.reload();
        },

        /**
         * Record consent event via AJAX
         *
         * @param {string} eventType Event type
         * @param {Object} consent Consent object
         */
        recordConsentEvent: function(eventType, consent) {
            const data = {
                action: 'ccm_record_consent',
                event_type: eventType,
                accepted_categories: consent.acceptedCategories || [],
                rejected_categories: consent.rejectedCategories || [],
                consent_version: consent.version
            };

            fetch(window.CCM_AJAX_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                credentials: 'same-origin',
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.info('CCM: Consent recorded, event ID:', data.data.event_id);
                } else {
                    console.error('CCM: Failed to record consent:', data.error);
                }
            })
            .catch(error => {
                console.error('CCM: AJAX error recording consent:', error);
            });
        },

        /**
         * Check Do Not Track header
         */
        checkDoNotTrack: function() {
            fetch(window.CCM_AJAX_URL + '?action=ccm_check_dnt', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.dnt_enabled && data.data.auto_reject) {
                    console.info('CCM: Do Not Track detected, auto-rejecting cookies');
                    this.handleRejectAll();
                }
            })
            .catch(error => {
                console.error('CCM: Failed to check DNT:', error);
            });
        },

        /**
         * Get current consent status
         *
         * @returns {Object|null} Consent object
         */
        getConsent: function() {
            return window.CookieConsentStorage.getConsent();
        },

        /**
         * Check if specific category is consented
         *
         * @param {string} category Category slug
         * @returns {boolean} Consented or not
         */
        hasConsent: function(category) {
            return window.CookieConsentStorage.hasConsent(category);
        },

        /**
         * Update existing consent
         *
         * @param {Object} updates Partial consent object
         */
        updateConsent: function(updates) {
            const saved = window.CookieConsentStorage.updateConsent(updates);

            if (saved) {
                const consent = window.CookieConsentStorage.getConsent();
                this.recordConsentEvent('modify', consent);

                // Fire callback
                if (this.callbacks.onConsentChanged) {
                    this.callbacks.onConsentChanged(consent);
                }

                // Dispatch event
                const event = new CustomEvent('ccm-consent-changed', {
                    detail: consent
                });
                window.dispatchEvent(event);
            }
        },

        /**
         * Revoke all consent
         */
        revokeConsent: function() {
            const consent = window.CookieConsentStorage.getConsent();

            if (consent) {
                this.recordConsentEvent('revoke', consent);
            }

            window.CookieConsentStorage.clearConsent();

            // Show banner again
            this.showBanner();

            // Reload page to disable scripts
            window.location.reload();
        }
    };

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            window.CookieConsentManager.init();
        });
    } else {
        window.CookieConsentManager.init();
    }

})(window, document);
