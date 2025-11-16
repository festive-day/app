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
         * Detect Safari browser (including iOS Safari)
         *
         * @returns {boolean} True if Safari detected
         */
        isSafari: function() {
            const ua = navigator.userAgent.toLowerCase();
            // Safari detection: Safari but not Chrome/Edge
            const isSafari = /safari/.test(ua) && !/chrome/.test(ua) && !/chromium/.test(ua) && !/edg/.test(ua);
            // iOS Safari
            const isIOS = /iphone|ipad|ipod/.test(ua);
            return isSafari || isIOS;
        },

        /**
         * Check if Safari ITP (Intelligent Tracking Prevention) is active
         * ITP limits third-party cookie blocking, so we need to adjust strategy
         *
         * @returns {boolean} True if Safari ITP detected
         */
        isSafariITP: function() {
            if (!this.isSafari()) {
                return false;
            }

            // Safari 14+ has ITP 2.0+ which is more restrictive
            // Check Safari version from user agent
            const ua = navigator.userAgent;
            const safariMatch = ua.match(/Version\/(\d+)/);
            if (safariMatch && parseInt(safariMatch[1]) >= 14) {
                return true;
            }

            // iOS Safari 14+ also has ITP
            const iosMatch = ua.match(/OS (\d+)_(\d+)/);
            if (iosMatch) {
                const majorVersion = parseInt(iosMatch[1]);
                if (majorVersion >= 14) {
                    return true;
                }
            }

            return false;
        },

        /**
         * Initialize blocker
         * Must run as early as possible
         */
        init: function() {
            // Detect Safari ITP
            const isITP = this.isSafariITP();
            if (isITP) {
                console.info('CCM: Safari ITP detected - using first-party blocking strategy');
                // Set flag for other parts of the code
                window.CCM_SAFARI_ITP = true;
            }

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
         * T086: Adjusts blocking strategy for Safari ITP
         */
        blockScripts: function() {
            const consent = window.CookieConsentStorage ? window.CookieConsentStorage.getConsent() : null;
            const isITP = window.CCM_SAFARI_ITP || this.isSafariITP();

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

                    // Safari ITP: Also remove script from DOM if it's third-party
                    // ITP limits third-party cookie blocking, so we need more aggressive blocking
                    if (isITP && this.isThirdPartyScript(script)) {
                        // For Safari ITP, we block more aggressively by removing the script
                        // This prevents ITP from allowing the script despite our blocking
                        script.style.display = 'none';
                        script.setAttribute('data-ccm-blocked', 'true');
                    }
                } else {
                    // Activate script if consent given
                    this.activateScript(script);
                    // Restore script if it was hidden for ITP
                    if (script.hasAttribute('data-ccm-blocked')) {
                        script.style.display = '';
                        script.removeAttribute('data-ccm-blocked');
                    }
                }
            });
        },

        /**
         * Check if script is third-party (external domain)
         *
         * @param {Element} script Script element
         * @returns {boolean} True if third-party
         */
        isThirdPartyScript: function(script) {
            if (!script.src) {
                return false; // Inline scripts are first-party
            }

            try {
                const scriptUrl = new URL(script.src, window.location.href);
                const currentDomain = window.location.hostname;
                const scriptDomain = scriptUrl.hostname;

                // Check if domains match (including subdomains)
                return scriptDomain !== currentDomain && !scriptDomain.endsWith('.' + currentDomain);
            } catch (error) {
                // If URL parsing fails, assume first-party
                return false;
            }
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
                // Log error details in debug mode
                if (window.CCM_DEBUG) {
                    console.error('CCM: Script execution error details:', {
                        message: error.message,
                        stack: error.stack,
                        scriptSrc: script.src || 'inline',
                        scriptCategory: script.getAttribute('data-consent-category')
                    });
                }
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

                // Add error handler for script loading
                newScript.onerror = function() {
                    console.error('CCM: Failed to load external script:', script.src);
                };

                // Add to DOM
                script.parentNode.insertBefore(newScript, script.nextSibling);

                // Remove old script (optional - keeps it as placeholder)
                // script.remove();
            } catch (error) {
                console.error('CCM: Error reloading external script:', error);
                // Log error details in debug mode
                if (window.CCM_DEBUG) {
                    console.error('CCM: Script reload error details:', {
                        message: error.message,
                        stack: error.stack,
                        scriptSrc: script.src,
                        scriptCategory: script.getAttribute('data-consent-category')
                    });
                }
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
            if (!cookieName) {
                return false;
            }

            const essentialPrefixes = [
                'wp-',
                'wordpress_',
                'wp_cookie_consent',
                'phpsessid',
                'comment_',
                'etch_',
                'etcbuilder_',
                'builder_',
                'acf_'
            ];
            const normalizedName = cookieName.toLowerCase();

            return essentialPrefixes.some(prefix => normalizedName.startsWith(prefix));
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
