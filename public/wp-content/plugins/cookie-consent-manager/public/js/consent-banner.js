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
                try {
                    this.setupEventListeners();
                    this.checkAndShowBanner();
                } catch (error) {
                    console.error('CCM: Error during initialization:', error);
                    if (window.CCM_DEBUG) {
                        console.error('CCM: Initialization error details:', {
                            message: error.message,
                            stack: error.stack
                        });
                    }
                }
            }).catch(error => {
                console.error('CCM: Failed to initialize:', error);
                // Show fallback banner even if config fails to load
                try {
                    this.showFallbackBanner();
                } catch (fallbackError) {
                    console.error('CCM: Failed to show fallback banner:', fallbackError);
                }
            });
        },

        /**
         * Load banner configuration from server
         *
         * @returns {Promise} Configuration data
         */
        loadConfig: function() {
            try {
                return fetch(window.CCM_AJAX_URL + '?action=ccm_get_banner_config', {
                    method: 'GET',
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.data?.message || data.error || 'Failed to load configuration');
                    }
                    this.config = data.data;
                    return this.config;
                })
                .catch(error => {
                    console.error('CCM: Error loading banner configuration:', error);
                    // Log error details for debugging
                    if (window.CCM_DEBUG) {
                        console.error('CCM: AJAX URL:', window.CCM_AJAX_URL);
                        console.error('CCM: Error details:', {
                            message: error.message,
                            stack: error.stack
                        });
                    }
                    throw error;
                });
            } catch (error) {
                console.error('CCM: Fatal error in loadConfig:', error);
                return Promise.reject(error);
            }
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
         *
         * T058: Check consent_version mismatch, show "Policy Updated" message
         */
        checkAndShowBanner: function() {
            const consent = window.CookieConsentStorage.getConsent();

            // No consent - show banner
            if (!consent) {
                this.showBanner();
                return;
            }

            // Consent exists but version mismatch - re-prompt with policy updated message
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
         * T058: Show "Policy Updated" message when cookie list changes
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
                    // T058: Show "Policy Updated" message
                    message.textContent = 'Our cookie policy has been updated. Please review your preferences.';
                    
                    // Add visual indicator for policy update
                    banner.classList.add('ccm-banner--policy-updated');
                    
                    // Add announcement for screen readers
                    const announcement = document.createElement('div');
                    announcement.setAttribute('role', 'alert');
                    announcement.setAttribute('aria-live', 'polite');
                    announcement.className = 'ccm-banner__announcement';
                    announcement.textContent = 'Cookie policy updated. Please review your preferences.';
                    announcement.style.position = 'absolute';
                    announcement.style.left = '-9999px';
                    banner.appendChild(announcement);
                    
                    // Remove announcement after screen reader reads it
                    setTimeout(() => {
                        if (announcement.parentNode) {
                            announcement.parentNode.removeChild(announcement);
                        }
                    }, 1000);
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
         *
         * T053: Open modal, pre-fill current consent
         */
        openPreferences: function() {
            const modal = document.getElementById('ccm-modal');
            if (!modal) {
                console.error('CCM: Modal element not found');
                return;
            }

            // Ensure config is loaded before populating
            if (!this.config) {
                this.loadConfig().then(() => {
                    this.populateModal();
                    // Show modal
                    modal.classList.remove('ccm-modal--hidden');
                    modal.classList.add('ccm-modal--show');
                    // Set aria attributes
                    modal.setAttribute('aria-modal', 'true');
                    document.body.style.overflow = 'hidden'; // Prevent background scrolling
                }).catch(error => {
                    console.error('CCM: Failed to load configuration:', error);
                });
                return;
            }

            // Populate modal with categories
            this.populateModal();

            // Show modal with animation
            modal.classList.remove('ccm-modal--hidden');
            modal.classList.add('ccm-modal--show');
            // Set aria attributes
            modal.setAttribute('aria-modal', 'true');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
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
            // Set aria attributes
            modal.setAttribute('aria-modal', 'false');
            document.body.style.overflow = ''; // Restore scrolling
        },

        /**
         * Populate modal with category accordion
         */
        populateModal: function() {
            if (!this.config) {
                return;
            }

            const modalBody = document.getElementById('ccm-modal-body');
            const categoriesList = document.getElementById('ccm-categories-list');
            const loadingMsg = modalBody.querySelector('.ccm-modal__loading');

            if (!modalBody || !categoriesList) {
                return;
            }

            // Get current consent
            const consent = window.CookieConsentStorage.getConsent();

            // Build HTML with accordion structure
            let html = '';

            this.config.categories.forEach((category, index) => {
                const isChecked = consent && consent.acceptedCategories && consent.acceptedCategories.includes(category.slug);
                const isRequired = category.is_required;
                const isExpanded = index === 0; // Expand first category by default

                html += '<div class="ccm-category' + (isExpanded ? ' ccm-category--expanded' : '') + '" data-category-slug="' + category.slug + '">';
                
                // Header with checkbox and toggle
                html += '<div class="ccm-category__header" role="button" tabindex="0" aria-expanded="' + isExpanded + '">';
                html += '<label class="ccm-category__label" onclick="event.stopPropagation();">';
                html += '<input type="checkbox" ';
                html += 'name="category[]" ';
                html += 'value="' + category.slug + '" ';
                html += 'data-category="' + category.slug + '" ';
                html += isChecked ? 'checked ' : '';
                html += isRequired ? 'disabled ' : '';
                html += 'class="ccm-category__checkbox">';
                html += '<span class="ccm-category__name">' + this.escapeHtml(category.name) + '</span>';
                if (isRequired) {
                    html += ' <span class="ccm-category__required">(Required)</span>';
                }
                html += '</label>';
                html += '<span class="ccm-category__toggle" aria-hidden="true">▼</span>';
                html += '</div>';

                // Collapsible content
                html += '<div class="ccm-category__content">';
                html += '<div class="ccm-category__inner">';
                html += '<p class="ccm-category__description">' + this.escapeHtml(category.description || '') + '</p>';

                // Show cookies in category
                if (category.cookies && category.cookies.length > 0) {
                    html += '<div class="ccm-category__cookies">';
                    html += '<div class="ccm-category__cookies-title">Cookies in this category</div>';
                    html += '<ul class="ccm-cookie-list">';
                    
                    category.cookies.forEach(cookie => {
                        html += '<li class="ccm-cookie-item">';
                        html += '<div class="ccm-cookie-item__name">' + this.escapeHtml(cookie.name || '') + '</div>';
                        html += '<div class="ccm-cookie-item__details">';
                        
                        if (cookie.provider) {
                            html += '<div class="ccm-cookie-item__detail">';
                            html += '<span class="ccm-cookie-item__detail-label">Provider:</span>';
                            html += '<span class="ccm-cookie-item__detail-value">' + this.escapeHtml(cookie.provider) + '</span>';
                            html += '</div>';
                        }
                        
                        if (cookie.purpose) {
                            html += '<div class="ccm-cookie-item__detail">';
                            html += '<span class="ccm-cookie-item__detail-label">Purpose:</span>';
                            html += '<span class="ccm-cookie-item__detail-value">' + this.escapeHtml(cookie.purpose) + '</span>';
                            html += '</div>';
                        }
                        
                        if (cookie.expiration) {
                            html += '<div class="ccm-cookie-item__detail">';
                            html += '<span class="ccm-cookie-item__detail-label">Expiration:</span>';
                            html += '<span class="ccm-cookie-item__detail-value">' + this.escapeHtml(cookie.expiration) + '</span>';
                            html += '</div>';
                        }
                        
                        html += '</div>';
                        html += '</li>';
                    });
                    
                    html += '</ul>';
                    html += '</div>';
                } else {
                    html += '<div class="ccm-category__cookies">';
                    html += '<p style="color: var(--text-2, #4a4a4a); font-size: var(--text-s, 0.875rem);">No cookies registered in this category.</p>';
                    html += '</div>';
                }

                html += '</div>';
                html += '</div>';
                html += '</div>';
            });

            categoriesList.innerHTML = html;

            // Hide loading, show categories
            if (loadingMsg) {
                loadingMsg.style.display = 'none';
            }
            categoriesList.style.display = 'block';

            // Setup accordion toggle handlers
            this.setupAccordionHandlers();
        },

        /**
         * Setup accordion expand/collapse handlers
         */
        setupAccordionHandlers: function() {
            const categoryHeaders = document.querySelectorAll('.ccm-category__header');
            
            categoryHeaders.forEach(header => {
                // Remove existing listeners (if any)
                const newHeader = header.cloneNode(true);
                header.parentNode.replaceChild(newHeader, header);

                // Add click handler
                newHeader.addEventListener('click', function(e) {
                    // Don't toggle if clicking on checkbox
                    if (e.target.type === 'checkbox' || e.target.closest('label')) {
                        return;
                    }

                    const category = this.closest('.ccm-category');
                    const isExpanded = category.classList.contains('ccm-category--expanded');

                    if (isExpanded) {
                        category.classList.remove('ccm-category--expanded');
                        this.setAttribute('aria-expanded', 'false');
                    } else {
                        category.classList.add('ccm-category--expanded');
                        this.setAttribute('aria-expanded', 'true');
                    }
                });

                // Keyboard support
                newHeader.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });
        },

        /**
         * Escape HTML to prevent XSS
         *
         * @param {string} text Text to escape
         * @returns {string} Escaped text
         */
        escapeHtml: function(text) {
            if (!text) {
                return '';
            }
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        /**
         * Handle "Save Preferences" button click
         */
        handleSavePreferences: function() {
            if (!this.config) {
                console.error('CCM: Configuration not loaded');
                return;
            }

            // Get selected categories
            const checkboxes = document.querySelectorAll('.ccm-category__checkbox');
            const acceptedCategories = [];
            const rejectedCategories = [];

            checkboxes.forEach(checkbox => {
                // Required categories are always accepted (even if disabled checkbox)
                if (checkbox.checked || checkbox.disabled) {
                    acceptedCategories.push(checkbox.value);
                } else {
                    rejectedCategories.push(checkbox.value);
                }
            });

            // Ensure at least essential categories are included
            const essentialCategory = this.config.categories.find(cat => cat.is_required);
            if (essentialCategory && !acceptedCategories.includes(essentialCategory.slug)) {
                acceptedCategories.push(essentialCategory.slug);
                const index = rejectedCategories.indexOf(essentialCategory.slug);
                if (index > -1) {
                    rejectedCategories.splice(index, 1);
                }
            }

            // Create consent object
            const consent = {
                consentGiven: true,
                acceptedCategories: acceptedCategories,
                rejectedCategories: rejectedCategories,
                version: this.config.consent_version
            };

            // Get existing consent to determine if we need to clear cookies
            const existingConsent = window.CookieConsentStorage.getConsent();
            
            // Determine event type
            const eventType = existingConsent ? 'modify' : 'accept_partial';

            // If modifying consent and categories changed from accept to reject, clear those cookies
            if (existingConsent && eventType === 'modify') {
                const categoriesToClear = rejectedCategories.filter(slug => {
                    return existingConsent.acceptedCategories && existingConsent.acceptedCategories.includes(slug);
                });

                if (categoriesToClear.length > 0) {
                    // Clear cookies for categories that were previously accepted but are now rejected
                    window.CookieConsentStorage.clearRejectedCookies(categoriesToClear);
                }
            }

            // Save consent using storage manager
            const saved = window.CookieConsentStorage.setConsent(consent);

            if (!saved) {
                console.error('CCM: Failed to save consent preferences');
                alert('Failed to save preferences. Please try again.');
                return;
            }

            // Log event to server
            this.recordConsentEvent(eventType, consent);

            // Hide banner if it's visible
            this.hideBanner();

            // Close modal
            this.closeModal();

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

            // Reload page to activate/deactivate scripts based on new consent
            // This ensures scripts are properly enabled/disabled
            try {
                window.location.reload();
            } catch (error) {
                console.error('CCM: Error reloading page:', error);
                // Fallback: trigger script reload manually
                const event = new CustomEvent('ccm-consent-changed', {
                    detail: consent
                });
                window.dispatchEvent(event);
            }
        },

        /**
         * Show fallback banner when config fails to load
         */
        showFallbackBanner: function() {
            const banner = document.getElementById('ccm-banner');
            if (banner) {
                const message = banner.querySelector('.ccm-banner__message');
                if (message) {
                    message.textContent = 'This site uses cookies. Please enable JavaScript to manage your preferences.';
                }
                this.showBanner();
            }
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
            try {
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.info('CCM: Consent recorded, event ID:', data.data.event_id);
                    } else {
                        console.error('CCM: Failed to record consent:', data.data?.message || data.error || 'Unknown error');
                        // Log additional details in debug mode
                        if (window.CCM_DEBUG) {
                            console.error('CCM: Response data:', data);
                        }
                    }
                })
                .catch(error => {
                    console.error('CCM: AJAX error recording consent:', error);
                    // Log error details for debugging
                    if (window.CCM_DEBUG) {
                        console.error('CCM: Error details:', {
                            message: error.message,
                            stack: error.stack,
                            eventType: eventType,
                            consent: consent
                        });
                    }
                    // Don't throw - consent recording failure shouldn't break user experience
                });
            } catch (error) {
                console.error('CCM: Fatal error in recordConsentEvent:', error);
                // Don't throw - allow user to continue even if logging fails
            }
        },

        /**
         * Check Do Not Track header
         */
        checkDoNotTrack: function() {
            try {
                fetch(window.CCM_AJAX_URL + '?action=ccm_check_dnt', {
                    method: 'GET',
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data.dnt_enabled && data.data.auto_reject) {
                        console.info('CCM: Do Not Track detected, auto-rejecting cookies');
                        this.handleRejectAll();
                    }
                })
                .catch(error => {
                    console.error('CCM: Failed to check DNT:', error);
                    // Log error details in debug mode
                    if (window.CCM_DEBUG) {
                        console.error('CCM: DNT check error details:', {
                            message: error.message,
                            stack: error.stack
                        });
                    }
                    // Don't throw - DNT check failure shouldn't break user experience
                });
            } catch (error) {
                console.error('CCM: Fatal error in checkDoNotTrack:', error);
                // Don't throw - allow user to continue even if DNT check fails
            }
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
         * T054: Update localStorage, cookie, fire AJAX, trigger script reload
         *
         * @param {Object} updates Partial consent object with updates
         * @returns {boolean} Success status
         */
        updateConsent: function(updates) {
            const existingConsent = window.CookieConsentStorage.getConsent();
            
            if (!existingConsent) {
                console.warn('CCM: No existing consent to update');
                return false;
            }

            // Merge updates with existing consent
            const updatedConsent = Object.assign({}, existingConsent, updates);
            
            // Ensure version is set
            if (!updatedConsent.version && this.config) {
                updatedConsent.version = this.config.consent_version;
            }

            // Determine which categories changed from accept to reject
            const categoriesToClear = [];
            if (updatedConsent.rejectedCategories && existingConsent.acceptedCategories) {
                categoriesToClear = updatedConsent.rejectedCategories.filter(slug => {
                    return existingConsent.acceptedCategories.includes(slug);
                });
            }

            // Clear cookies for categories that changed from accept to reject
            if (categoriesToClear.length > 0) {
                window.CookieConsentStorage.clearRejectedCookies(categoriesToClear);
            }

            // Save updated consent to localStorage and cookie
            const saved = window.CookieConsentStorage.setConsent(updatedConsent);

            if (!saved) {
                console.error('CCM: Failed to update consent');
                return false;
            }

            // Get final consent object
            const consent = window.CookieConsentStorage.getConsent();

            // Fire AJAX to record modify event
            this.recordConsentEvent('modify', consent);

            // Fire callback
            if (this.callbacks.onConsentChanged) {
                this.callbacks.onConsentChanged(consent);
            }

            // Dispatch custom event for script reloading
            const event = new CustomEvent('ccm-consent-changed', {
                detail: consent
            });
            window.dispatchEvent(event);

            // Trigger script reload (cookie-blocker.js will handle this)
            // Reload page to ensure scripts are properly activated/deactivated
            window.location.reload();

            return true;
        },

        /**
         * Revoke all consent
         *
         * T055: Clear localStorage, cookie, non-essential cookies
         */
        revokeConsent: function() {
            const consent = window.CookieConsentStorage.getConsent();

            // Record revoke event before clearing
            if (consent) {
                this.recordConsentEvent('revoke', consent);
            }

            // Get all non-essential categories that were accepted
            const nonEssentialCategories = consent && consent.acceptedCategories 
                ? consent.acceptedCategories.filter(cat => cat !== 'essential')
                : [];

            // Clear non-essential cookies
            if (nonEssentialCategories.length > 0) {
                window.CookieConsentStorage.clearRejectedCookies(nonEssentialCategories);
            }

            // Clear consent from localStorage and cookie
            window.CookieConsentStorage.clearConsent();

            // Fire callback
            if (this.callbacks.onConsentChanged) {
                this.callbacks.onConsentChanged(null);
            }

            // Dispatch event for script blocking
            const event = new CustomEvent('ccm-consent-changed', {
                detail: null
            });
            window.dispatchEvent(event);

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
