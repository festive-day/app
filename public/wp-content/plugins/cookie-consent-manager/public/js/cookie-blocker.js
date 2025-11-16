/**
 * Cookie Blocker
 *
 * Blocks non-essential scripts and cookies until user consent
 * Rewrites script tags from type="text/javascript" to type="text/plain"
 *
 * CRITICAL: Must load BEFORE any other scripts (priority -9999)
 *
 * @package Cookie_Consent_Manager
 */

(function(window, document) {
    'use strict';

    /**
     * Cookie Blocker object
     */
    window.CookieConsentBlocker = {

        /**
         * Initialize blocker
         * Must run as early as possible
         */
        init: function() {
            // Intercept document.cookie writes
            this.interceptCookieAPI();

            // Block scripts on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', this.blockScripts.bind(this));
            } else {
                this.blockScripts();
            }

            // Listen for consent changes
            window.addEventListener('ccm-consent-changed', this.handleConsentChange.bind(this));
        },

        /**
         * Block scripts that require consent
         */
        blockScripts: function() {
            const consent = window.CookieConsentStorage ? window.CookieConsentStorage.getConsent() : null;

            // Get all script tags
            const scripts = document.querySelectorAll('script[data-consent-category]');

            scripts.forEach(script => {
                const category = script.getAttribute('data-consent-category');

                if (!category) {
                    return;
                }

                // Essential scripts are never blocked
                if (category === 'essential') {
                    return;
                }

                // Check if user has consented to this category
                const hasConsent = consent && consent.acceptedCategories && consent.acceptedCategories.includes(category);

                if (!hasConsent) {
                    // Block script by changing type
                    this.blockScript(script);
                } else {
                    // Activate script if consent given
                    this.activateScript(script);
                }
            });
        },

        /**
         * Block a script tag
         *
         * @param {Element} script Script element
         */
        blockScript: function(script) {
            const currentType = script.getAttribute('type') || 'text/javascript';

            // Already blocked?
            if (currentType === 'text/plain') {
                return;
            }

            // Save original type
            script.setAttribute('data-original-type', currentType);

            // Change type to text/plain (prevents execution)
            script.setAttribute('type', 'text/plain');
        },

        /**
         * Activate a blocked script
         *
         * @param {Element} script Script element
         */
        activateScript: function(script) {
            const currentType = script.getAttribute('type');

            // Already active?
            if (currentType === 'text/javascript' || currentType === 'application/javascript') {
                return;
            }

            // Get original type
            const originalType = script.getAttribute('data-original-type') || 'text/javascript';

            // Change type back to allow execution
            script.setAttribute('type', originalType);

            // For inline scripts, need to re-execute
            if (!script.src && script.textContent) {
                this.executeInlineScript(script);
            }

            // For external scripts, reload
            if (script.src) {
                this.reloadExternalScript(script);
            }
        },

        /**
         * Execute inline script content
         *
         * @param {Element} script Script element
         */
        executeInlineScript: function(script) {
            try {
                // Create new script element
                const newScript = document.createElement('script');
                newScript.textContent = script.textContent;
                newScript.type = 'text/javascript';

                // Copy attributes
                Array.from(script.attributes).forEach(attr => {
                    if (attr.name !== 'type') {
                        newScript.setAttribute(attr.name, attr.value);
                    }
                });

                // Replace old script with new
                script.parentNode.replaceChild(newScript, script);
            } catch (error) {
                console.error('CCM: Error executing inline script:', error);
            }
        },

        /**
         * Reload external script
         *
         * @param {Element} script Script element
         */
        reloadExternalScript: function(script) {
            try {
                // Create new script element
                const newScript = document.createElement('script');
                newScript.src = script.src;
                newScript.type = 'text/javascript';

                // Copy attributes
                Array.from(script.attributes).forEach(attr => {
                    if (attr.name !== 'type' && attr.name !== 'src') {
                        newScript.setAttribute(attr.name, attr.value);
                    }
                });

                // Add to DOM
                script.parentNode.insertBefore(newScript, script.nextSibling);

                // Remove old script (optional - keeps it as placeholder)
                // script.remove();
            } catch (error) {
                console.error('CCM: Error reloading external script:', error);
            }
        },

        /**
         * Handle consent change event
         *
         * T057: When consent updated, activate/deactivate scripts based on new categories
         *
         * @param {Event} event Custom event
         */
        handleConsentChange: function(event) {
            const consent = event.detail;

            // If consent is null (revoked), block all non-essential scripts
            if (!consent) {
                this.blockAllNonEssentialScripts();
                return;
            }

            // Get all script tags with consent categories
            const scripts = document.querySelectorAll('script[data-consent-category]');

            scripts.forEach(script => {
                const category = script.getAttribute('data-consent-category');

                if (!category) {
                    return;
                }

                // Essential scripts are always allowed
                if (category === 'essential') {
                    this.activateScript(script);
                    return;
                }

                // Check if user has consented to this category
                const hasConsent = consent.acceptedCategories && consent.acceptedCategories.includes(category);

                if (hasConsent) {
                    // Activate script - reload if needed
                    this.activateScript(script);
                } else {
                    // Block script
                    this.blockScript(script);
                }
            });

            // Clear cookies for rejected categories
            if (consent.rejectedCategories && window.CookieConsentStorage) {
                window.CookieConsentStorage.clearRejectedCookies(consent.rejectedCategories);
            }
        },

        /**
         * Block all non-essential scripts
         *
         * Used when consent is revoked
         */
        blockAllNonEssentialScripts: function() {
            const scripts = document.querySelectorAll('script[data-consent-category]');

            scripts.forEach(script => {
                const category = script.getAttribute('data-consent-category');

                if (category && category !== 'essential') {
                    this.blockScript(script);
                }
            });
        },

        /**
         * Intercept document.cookie API
         */
        interceptCookieAPI: function() {
            const self = this;
            const originalCookieDescriptor = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie');

            if (!originalCookieDescriptor) {
                return;
            }

            Object.defineProperty(document, 'cookie', {
                get: function() {
                    return originalCookieDescriptor.get.call(document);
                },
                set: function(value) {
                    // Parse cookie being set
                    const cookieName = value.split('=')[0].trim();

                    // Allow essential cookies
                    if (self.isEssentialCookie(cookieName)) {
                        return originalCookieDescriptor.set.call(document, value);
                    }

                    // Check consent
                    const consent = window.CookieConsentStorage ? window.CookieConsentStorage.getConsent() : null;

                    // Block if no consent
                    if (!consent) {
                        console.warn('CCM: Blocked cookie (no consent):', cookieName);
                        return;
                    }

                    // Determine category (simplified - in production, check against registry)
                    const category = self.getCookieCategory(cookieName);

                    // Check if category is consented
                    if (consent.acceptedCategories && consent.acceptedCategories.includes(category)) {
                        return originalCookieDescriptor.set.call(document, value);
                    } else {
                        console.warn('CCM: Blocked cookie (category not consented):', cookieName, category);
                    }
                }
            });
        },

        /**
         * Check if cookie is essential (always allowed)
         *
         * @param {string} cookieName Cookie name
         * @returns {boolean} Is essential
         */
        isEssentialCookie: function(cookieName) {
            const essentialPrefixes = [
                'wp-',
                'wordpress_',
                'wp_cookie_consent',
                'PHPSESSID',
                'comment_'
            ];

            return essentialPrefixes.some(prefix => cookieName.startsWith(prefix));
        },

        /**
         * Get cookie category (simplified heuristic)
         *
         * @param {string} cookieName Cookie name
         * @returns {string} Category slug
         */
        getCookieCategory: function(cookieName) {
            // Analytics cookies
            if (cookieName.startsWith('_ga') || cookieName.startsWith('_gid')) {
                return 'analytics';
            }

            // Marketing cookies
            if (cookieName.startsWith('_fb') || cookieName.startsWith('fr')) {
                return 'marketing';
            }

            // Default to functional
            return 'functional';
        }
    };

    // Auto-initialize blocker ASAP
    window.CookieConsentBlocker.init();

})(window, document);
