/**
 * Storage Manager
 *
 * Handles localStorage and cookie read/write operations for consent data
 *
 * @package Cookie_Consent_Manager
 */

(function(window) {
    'use strict';

    const STORAGE_KEY = 'wp_cookie_consent';
    const COOKIE_NAME = 'wp_consent_status';
    const CONSENT_DURATION_DAYS = 365;
    const CONSENT_VERSION_EXPIRY_MONTHS = 12;

    /**
     * Storage Manager object
     */
    window.CookieConsentStorage = {

        /**
         * Get consent data from localStorage
         *
         * @returns {Object|null} Consent object or null if not found/invalid
         */
        getConsent: function() {
            try {
                const data = localStorage.getItem(STORAGE_KEY);
                if (!data) {
                    return null;
                }

                const consent = JSON.parse(data);

                // Validate consent object structure
                if (!this.validateConsent(consent)) {
                    console.warn('CCM: Invalid consent object in localStorage, clearing...');
                    this.clearConsent();
                    return null;
                }

                // Check if consent has expired (12 months)
                if (this.isExpired(consent)) {
                    console.info('CCM: Consent expired, clearing...');
                    this.clearConsent();
                    return null;
                }

                return consent;
            } catch (error) {
                console.error('CCM: Error reading consent from localStorage:', error);
                return null;
            }
        },

        /**
         * Save consent data to localStorage and cookie
         *
         * @param {Object} consent Consent object to save
         * @returns {boolean} Success status
         */
        setConsent: function(consent) {
            try {
                // Add metadata
                const now = Math.floor(Date.now() / 1000);
                consent.timestamp = consent.timestamp || now;
                consent.lastModified = now;
                consent.version = consent.version || window.CCM_VERSION || '1.0.0';

                // Validate before saving
                if (!this.validateConsent(consent)) {
                    console.error('CCM: Invalid consent object, cannot save');
                    return false;
                }

                // Save to localStorage
                localStorage.setItem(STORAGE_KEY, JSON.stringify(consent));

                // Save identifier cookie
                this.setConsentCookie(consent.acceptedCategories || []);

                return true;
            } catch (error) {
                console.error('CCM: Error saving consent to localStorage:', error);
                return false;
            }
        },

        /**
         * Update existing consent
         *
         * @param {Object} updates Partial consent object with updates
         * @returns {boolean} Success status
         */
        updateConsent: function(updates) {
            const existing = this.getConsent() || {};
            const updated = Object.assign({}, existing, updates);
            return this.setConsent(updated);
        },

        /**
         * Clear all consent data
         */
        clearConsent: function() {
            try {
                localStorage.removeItem(STORAGE_KEY);
                this.deleteConsentCookie();
            } catch (error) {
                console.error('CCM: Error clearing consent:', error);
            }
        },

        /**
         * Validate consent object structure
         *
         * @param {Object} consent Consent object to validate
         * @returns {boolean} Valid or not
         */
        validateConsent: function(consent) {
            if (!consent || typeof consent !== 'object') {
                return false;
            }

            // Required fields
            if (!consent.version || !consent.timestamp) {
                return false;
            }

            // Categories must be arrays
            if (!Array.isArray(consent.acceptedCategories)) {
                return false;
            }

            if (consent.rejectedCategories && !Array.isArray(consent.rejectedCategories)) {
                return false;
            }

            // Version format check (X.X.X)
            if (!/^\d+\.\d+\.\d+$/.test(consent.version)) {
                return false;
            }

            // Timestamp must be number
            if (typeof consent.timestamp !== 'number') {
                return false;
            }

            return true;
        },

        /**
         * Check if consent has expired (12 months)
         *
         * @param {Object} consent Consent object
         * @returns {boolean} Expired or not
         */
        isExpired: function(consent) {
            if (!consent.timestamp) {
                return true;
            }

            const now = Math.floor(Date.now() / 1000);
            const ageInMonths = (now - consent.timestamp) / (30 * 24 * 60 * 60);

            return ageInMonths > CONSENT_VERSION_EXPIRY_MONTHS;
        },

        /**
         * Check if consent version matches current plugin version
         *
         * @param {Object} consent Consent object
         * @returns {boolean} Version matches
         */
        isVersionCurrent: function(consent) {
            if (!consent.version) {
                return false;
            }

            const currentVersion = window.CCM_VERSION || '1.0.0';
            return consent.version === currentVersion;
        },

        /**
         * Set consent identifier cookie
         *
         * @param {Array} acceptedCategories Array of accepted category slugs
         */
        setConsentCookie: function(acceptedCategories) {
            const hash = this.generateCookieHash(acceptedCategories);
            const expires = new Date();
            expires.setDate(expires.getDate() + CONSENT_DURATION_DAYS);

            document.cookie = COOKIE_NAME + '=' + hash +
                '; expires=' + expires.toUTCString() +
                '; path=/' +
                '; SameSite=Lax' +
                (window.location.protocol === 'https:' ? '; Secure' : '');
        },

        /**
         * Delete consent cookie
         */
        deleteConsentCookie: function() {
            document.cookie = COOKIE_NAME + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        },

        /**
         * Generate MD5 hash of accepted categories
         *
         * @param {Array} categories Category slugs
         * @returns {string} MD5 hash
         */
        generateCookieHash: function(categories) {
            const sorted = categories.slice().sort();
            const str = sorted.join(',');
            return this.md5(str);
        },

        /**
         * Simple MD5 implementation
         *
         * @param {string} str String to hash
         * @returns {string} MD5 hash
         */
        md5: function(str) {
            // Simple hash for demo - in production use crypto library
            let hash = 0;
            if (str.length === 0) return hash.toString(16);

            for (let i = 0; i < str.length; i++) {
                const char = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash;
            }

            return Math.abs(hash).toString(16).padStart(32, '0').substring(0, 32);
        },

        /**
         * Clear cookies for rejected categories
         *
         * @param {Array} rejectedCategories Category slugs that were rejected
         */
        clearRejectedCookies: function(rejectedCategories) {
            if (!rejectedCategories || rejectedCategories.length === 0) {
                return;
            }

            // Get all cookies
            const cookies = document.cookie.split(';');

            // Clear each cookie (best effort - some may be HttpOnly or from different domains)
            cookies.forEach(cookie => {
                const cookieName = cookie.split('=')[0].trim();

                // Don't clear essential cookies or our consent cookie
                if (cookieName === COOKIE_NAME || cookieName.startsWith('wordpress_') || cookieName.startsWith('wp-')) {
                    return;
                }

                // Delete cookie
                document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
                document.cookie = cookieName + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + window.location.hostname + ';';
            });
        },

        /**
         * Check if a specific category is consented
         *
         * @param {string} category Category slug
         * @returns {boolean} Consented or not
         */
        hasConsent: function(category) {
            const consent = this.getConsent();
            if (!consent) {
                return false;
            }

            return consent.acceptedCategories && consent.acceptedCategories.includes(category);
        }
    };

})(window);
