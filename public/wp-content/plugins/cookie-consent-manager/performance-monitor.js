/**
 * Performance Monitoring Script
 * 
 * T089: Verify performance benchmarks per quickstart.md
 * - Banner load <1s
 * - Consent check <50ms
 * - DB queries within targets
 * 
 * Usage: Include in page and check console for performance metrics
 */

(function(window) {
    'use strict';

    /**
     * Performance Monitor
     */
    window.CCMPerformanceMonitor = {
        metrics: {
            bannerLoadTime: null,
            consentCheckTime: null,
            configLoadTime: null,
            ajaxResponseTime: null
        },

        /**
         * Initialize performance monitoring
         */
        init: function() {
            // Monitor banner load time
            this.monitorBannerLoad();
            
            // Monitor consent check performance
            this.monitorConsentCheck();
            
            // Monitor AJAX response times
            this.monitorAJAX();
        },

        /**
         * Monitor banner load time (target: <1s)
         */
        monitorBannerLoad: function() {
            const startTime = performance.now();
            
            // Wait for banner to be visible
            const checkBanner = setInterval(() => {
                const banner = document.getElementById('ccm-banner');
                if (banner && !banner.classList.contains('ccm-banner--hidden')) {
                    const loadTime = performance.now() - startTime;
                    this.metrics.bannerLoadTime = loadTime;
                    
                    clearInterval(checkBanner);
                    
                    if (loadTime > 1000) {
                        console.warn('CCM Performance: Banner load time exceeds 1s:', loadTime + 'ms');
                    } else {
                        console.info('CCM Performance: Banner loaded in', loadTime.toFixed(2) + 'ms');
                    }
                }
            }, 100);

            // Timeout after 5 seconds
            setTimeout(() => {
                clearInterval(checkBanner);
                if (!this.metrics.bannerLoadTime) {
                    console.warn('CCM Performance: Banner load timeout (>5s)');
                }
            }, 5000);
        },

        /**
         * Monitor consent check performance (target: <50ms)
         */
        monitorConsentCheck: function() {
            if (!window.CookieConsentStorage) {
                return;
            }

            // Measure consent check time
            const startTime = performance.now();
            
            try {
                const consent = window.CookieConsentStorage.getConsent();
                const hasAnalytics = window.CookieConsentStorage.hasConsent('analytics');
                
                const checkTime = performance.now() - startTime;
                this.metrics.consentCheckTime = checkTime;
                
                if (checkTime > 50) {
                    console.warn('CCM Performance: Consent check exceeds 50ms:', checkTime.toFixed(2) + 'ms');
                } else {
                    console.info('CCM Performance: Consent check completed in', checkTime.toFixed(2) + 'ms');
                }
            } catch (error) {
                console.error('CCM Performance: Error measuring consent check:', error);
            }
        },

        /**
         * Monitor AJAX response times
         */
        monitorAJAX: function() {
            const self = this;
            
            // Intercept fetch calls
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                const url = args[0];
                const startTime = performance.now();
                
                // Check if it's a CCM AJAX call
                if (typeof url === 'string' && url.includes('ccm_')) {
                    return originalFetch.apply(this, args)
                        .then(response => {
                            const responseTime = performance.now() - startTime;
                            
                            if (url.includes('ccm_get_banner_config')) {
                                self.metrics.configLoadTime = responseTime;
                                if (responseTime > 500) {
                                    console.warn('CCM Performance: Config load exceeds 500ms:', responseTime.toFixed(2) + 'ms');
                                }
                            }
                            
                            if (url.includes('ccm_record_consent')) {
                                self.metrics.ajaxResponseTime = responseTime;
                                if (responseTime > 1000) {
                                    console.warn('CCM Performance: Consent recording exceeds 1s:', responseTime.toFixed(2) + 'ms');
                                }
                            }
                            
                            return response;
                        })
                        .catch(error => {
                            const responseTime = performance.now() - startTime;
                            console.error('CCM Performance: AJAX error after', responseTime.toFixed(2) + 'ms:', error);
                            throw error;
                        });
                }
                
                return originalFetch.apply(this, args);
            };
        },

        /**
         * Get performance report
         */
        getReport: function() {
            const report = {
                bannerLoadTime: this.metrics.bannerLoadTime,
                consentCheckTime: this.metrics.consentCheckTime,
                configLoadTime: this.metrics.configLoadTime,
                ajaxResponseTime: this.metrics.ajaxResponseTime,
                benchmarks: {
                    bannerLoad: {
                        target: '<1000ms',
                        actual: this.metrics.bannerLoadTime ? this.metrics.bannerLoadTime.toFixed(2) + 'ms' : 'N/A',
                        passed: this.metrics.bannerLoadTime ? this.metrics.bannerLoadTime < 1000 : null
                    },
                    consentCheck: {
                        target: '<50ms',
                        actual: this.metrics.consentCheckTime ? this.metrics.consentCheckTime.toFixed(2) + 'ms' : 'N/A',
                        passed: this.metrics.consentCheckTime ? this.metrics.consentCheckTime < 50 : null
                    },
                    configLoad: {
                        target: '<500ms',
                        actual: this.metrics.configLoadTime ? this.metrics.configLoadTime.toFixed(2) + 'ms' : 'N/A',
                        passed: this.metrics.configLoadTime ? this.metrics.configLoadTime < 500 : null
                    }
                }
            };
            
            return report;
        },

        /**
         * Log performance report
         */
        logReport: function() {
            const report = this.getReport();
            console.group('CCM Performance Report');
            console.table(report.benchmarks);
            console.log('Full metrics:', report);
            console.groupEnd();
        }
    };

    // Auto-initialize if debug mode is enabled
    if (window.CCM_DEBUG || window.location.search.includes('ccm_perf=1')) {
        // Wait for DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                window.CCMPerformanceMonitor.init();
            });
        } else {
            window.CCMPerformanceMonitor.init();
        }
        
        // Log report after page load
        window.addEventListener('load', function() {
            setTimeout(() => {
                window.CCMPerformanceMonitor.logReport();
            }, 2000);
        });
    }

})(window);

