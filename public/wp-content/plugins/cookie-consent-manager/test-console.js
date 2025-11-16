/**
 * Cookie Consent Manager - Browser Console Test
 *
 * Copy/paste this into browser console (F12) on the homepage to debug
 */

console.log('=== CCM Diagnostic Test ===');

// Test 1: Check global objects
console.log('\n1. Global Objects:');
console.log('   CookieConsentStorage:', typeof window.CookieConsentStorage);
console.log('   CookieConsentManager:', typeof window.CookieConsentManager);
console.log('   CCM_AJAX_URL:', window.CCM_AJAX_URL);
console.log('   CCM_VERSION:', window.CCM_VERSION);

// Test 2: Check banner element
console.log('\n2. Banner Element:');
const banner = document.getElementById('ccm-banner');
console.log('   Banner found:', !!banner);
if (banner) {
    console.log('   Banner classes:', banner.className);
    console.log('   Banner visible:', banner.offsetHeight > 0);
    console.log('   Computed display:', getComputedStyle(banner).display);
    console.log('   Computed visibility:', getComputedStyle(banner).visibility);
    console.log('   Computed opacity:', getComputedStyle(banner).opacity);
    console.log('   Computed transform:', getComputedStyle(banner).transform);
}

// Test 3: Check scripts loaded
console.log('\n3. Scripts Loaded:');
const scripts = Array.from(document.querySelectorAll('script')).map(s => s.src).filter(s => s.includes('cookie'));
console.log('   CCM scripts:', scripts);

// Test 4: Check styles loaded
console.log('\n4. Styles Loaded:');
const styles = Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map(s => s.href).filter(s => s.includes('cookie'));
console.log('   CCM styles:', styles);

// Test 5: Check localStorage
console.log('\n5. LocalStorage:');
try {
    const consent = localStorage.getItem('cookie_consent');
    console.log('   cookie_consent:', consent ? JSON.parse(consent) : 'NOT SET (banner should show)');
} catch (e) {
    console.error('   Error reading localStorage:', e);
}

// Test 6: Check CookieConsentManager state
console.log('\n6. CookieConsentManager State:');
if (window.CookieConsentManager) {
    console.log('   Config loaded:', !!window.CookieConsentManager.config);
    if (window.CookieConsentManager.config) {
        console.log('   Categories:', window.CookieConsentManager.config.categories);
        console.log('   Cookies:', window.CookieConsentManager.config.cookies);
    }
}

// Test 7: Test AJAX endpoint
console.log('\n7. Testing AJAX Endpoint:');
if (window.CCM_AJAX_URL) {
    fetch(window.CCM_AJAX_URL + '?action=ccm_get_banner_config')
        .then(r => r.json())
        .then(data => {
            console.log('   GET /ccm_get_banner_config:', data);
        })
        .catch(err => {
            console.error('   AJAX Error:', err);
        });
}

// Test 8: Manual banner show (if needed)
console.log('\n8. Manual Banner Control:');
console.log('   To manually show banner, run: showBanner()');
console.log('   To manually hide banner, run: hideBanner()');

window.showBanner = function() {
    const banner = document.getElementById('ccm-banner');
    if (banner) {
        banner.classList.remove('ccm-banner--hidden');
        banner.classList.add('ccm-banner--show');
        console.log('✓ Banner shown');
    } else {
        console.error('✗ Banner element not found');
    }
};

window.hideBanner = function() {
    const banner = document.getElementById('ccm-banner');
    if (banner) {
        banner.classList.add('ccm-banner--hidden');
        banner.classList.remove('ccm-banner--show');
        console.log('✓ Banner hidden');
    }
};

window.clearConsent = function() {
    localStorage.removeItem('cookie_consent');
    document.cookie = 'cookie_consent=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    console.log('✓ Consent cleared - reload page to see banner');
};

window.testBannerClick = function() {
    console.log('Testing Accept All button...');
    const btn = document.getElementById('ccm-accept-all');
    if (btn) {
        btn.click();
        console.log('✓ Accept All clicked');
    } else {
        console.error('✗ Accept All button not found');
    }
};

console.log('\n=== Diagnostic Complete ===');
console.log('Available commands:');
console.log('  showBanner()     - Manually show the banner');
console.log('  hideBanner()     - Manually hide the banner');
console.log('  clearConsent()   - Clear consent and reload');
console.log('  testBannerClick() - Test Accept All button');
