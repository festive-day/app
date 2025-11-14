# Quickstart Guide: Cookie Consent Manager

**Feature**: Cookie Consent Manager
**Date**: 2025-11-14
**Purpose**: Testing scenarios and validation checklist

## Prerequisites

- WordPress 6.0+ installed
- Etch theme active
- AutomaticCSS framework loaded
- PHP 8.0+ environment
- PHPUnit test suite configured

---

## Setup Steps

### 1. Plugin Installation

```bash
# Navigate to WordPress plugins directory
cd public/wp-content/plugins/

# Create plugin directory
mkdir cookie-consent-manager
cd cookie-consent-manager

# Initialize plugin structure (done by implementation)
# See plan.md Project Structure section for file layout
```

### 2. Database Migration

```bash
# Run up migration (creates tables)
wp eval-file database/migrations/001-create-tables-up.sql

# Verify tables created
wp db query "SHOW TABLES LIKE 'wp_cookie_consent_%'"
# Expected: 3 tables (categories, cookies, events)
```

### 3. Activate Plugin

```bash
# Activate via WP-CLI
wp plugin activate cookie-consent-manager

# Verify activation
wp plugin list | grep cookie-consent-manager
# Status should be "active"
```

### 4. Configure Admin Settings

```bash
# Access admin dashboard
open http://localhost/wp-admin/options-general.php?page=cookie-consent-settings

# Or via WP-CLI (add test cookies)
wp eval '
$wpdb->insert(
    $wpdb->prefix . "cookie_consent_cookies",
    array(
        "name" => "_ga",
        "category_id" => 3, // analytics
        "provider" => "Google Analytics",
        "purpose" => "Track site usage",
        "expiration" => "2 years",
        "domain" => ".example.com"
    )
);
'
```

---

## Testing Scenarios

### Scenario 1: First-Time Visitor (User Story 1 - P1)

**Goal**: Verify banner displays and blocks non-essential cookies

**Steps**:
1. Clear browser data (cookies + localStorage)
2. Navigate to site homepage
3. **Verify**: Bottom banner appears within 1 second
4. **Verify**: Banner contains "Accept All" and "Reject All" buttons
5. Open DevTools → Application → Cookies
6. **Verify**: No analytics/marketing cookies set (only `wp_consent_status` absent)
7. Open DevTools → Console → Run: `document.cookie`
8. **Verify**: Only essential WordPress cookies present

**Expected Result**:
- Banner visible at bottom, full-width
- Non-essential cookies blocked
- No JavaScript errors in console

**Pass Criteria**: SC-001, SC-006, SC-007 met

---

### Scenario 2: Accept All Cookies

**Goal**: Verify "Accept All" enables all cookies

**Steps**:
1. From Scenario 1 state (banner visible)
2. Click "Accept All" button
3. **Verify**: Banner dismisses immediately
4. **Verify**: `wp_consent_status` cookie set (check DevTools)
5. **Verify**: localStorage key `wp_cookie_consent` exists with:
   ```json
   {
     "acceptedCategories": ["essential", "functional", "analytics", "marketing"]
   }
   ```
6. Reload page
7. **Verify**: Banner does NOT reappear
8. **Verify**: Google Analytics script (if configured) loads with `type="text/javascript"`

**Expected Result**:
- All cookies enabled
- Consent persists across page loads
- Scripts activate dynamically

**Pass Criteria**: SC-002, SC-003 met

---

### Scenario 3: Reject All Cookies

**Goal**: Verify "Reject All" blocks non-essential cookies

**Steps**:
1. Clear browser data, reload page (banner appears)
2. Click "Reject All" button
3. **Verify**: Banner dismisses
4. **Verify**: localStorage shows:
   ```json
   {
     "rejectedCategories": ["functional", "analytics", "marketing"],
     "acceptedCategories": ["essential"]
   }
   ```
5. Navigate site (multiple pages)
6. **Verify**: No analytics/marketing cookies appear
7. **Verify**: Site functionality intact (login, forms work)

**Expected Result**:
- Only essential cookies active
- Site remains functional
- User choice persisted

**Pass Criteria**: SC-007, SC-008 met

---

### Scenario 4: Manage Preferences - Partial Consent (User Story 2 - P2)

**Goal**: Verify granular category control

**Steps**:
1. Clear browser data, reload page (banner appears)
2. Click "Manage Preferences" or "Cookie Details"
3. **Verify**: Modal/expanded view shows 4 categories:
   - Essential (checkbox disabled/checked)
   - Functional (checkbox enabled/unchecked)
   - Analytics (checkbox enabled/unchecked)
   - Marketing (checkbox enabled/unchecked)
4. Check "Functional" and "Analytics", leave "Marketing" unchecked
5. Click "Save Preferences"
6. **Verify**: localStorage shows:
   ```json
   {
     "acceptedCategories": ["essential", "functional", "analytics"],
     "rejectedCategories": ["marketing"]
   }
   ```
7. **Verify**: Google Analytics loads, Facebook Pixel does NOT load

**Expected Result**:
- Granular control per category
- Essential category cannot be disabled
- Scripts load based on selection

**Pass Criteria**: FR-007, SC-004 met

---

### Scenario 5: Cookie Details View

**Goal**: Verify cookie transparency (User Story 2 - P2)

**Steps**:
1. From Scenario 4 preferences modal
2. Expand "Analytics" category
3. **Verify**: Cookie list shows:
   - Cookie name (_ga, _gid)
   - Provider (Google Analytics)
   - Purpose (descriptive text)
   - Expiration (2 years)
4. Expand "Essential" category
5. **Verify**: Cookies listed with descriptions

**Expected Result**:
- All cookies visible with full details
- Descriptions clear and non-technical

**Pass Criteria**: FR-006, SC-005 met

---

### Scenario 6: Modify Existing Consent (User Story 3 - P3)

**Goal**: Verify preference changes without page reload

**Steps**:
1. From Scenario 4 state (analytics accepted)
2. Navigate to site footer
3. Click "Cookie Settings" link
4. **Verify**: Preferences modal opens with current selections shown
5. Uncheck "Analytics", check "Marketing"
6. Click "Save"
7. **Verify**: No page reload occurs
8. **Verify**: localStorage updated immediately
9. Open DevTools → Network tab
10. **Verify**: AJAX call to `ccm_record_consent` with `event_type: "modify"`
11. **Verify**: Google Analytics script removed, Facebook Pixel added

**Expected Result**:
- Changes apply without reload (FR-009)
- Audit event logged
- Scripts reload based on new consent

**Pass Criteria**: SC-004, FR-009 met

---

### Scenario 7: Consent Persistence (12 Months)

**Goal**: Verify consent lasts 12 months (SC-003)

**Steps**:
1. Set consent (Accept All)
2. Note current timestamp
3. Simulate 11 months passing:
   ```javascript
   // DevTools Console
   const consent = JSON.parse(localStorage.getItem('wp_cookie_consent'));
   consent.timestamp = Date.now() / 1000 - (11 * 30 * 24 * 60 * 60); // 11 months ago
   localStorage.setItem('wp_cookie_consent', JSON.stringify(consent));
   ```
4. Reload page
5. **Verify**: Banner does NOT appear (within 12 months)
6. Simulate 13 months:
   ```javascript
   consent.timestamp = Date.now() / 1000 - (13 * 30 * 24 * 60 * 60);
   localStorage.setItem('wp_cookie_consent', JSON.stringify(consent));
   ```
7. Reload page
8. **Verify**: Banner reappears (expired)

**Expected Result**:
- Consent valid for 12 months
- Re-prompt after expiration

**Pass Criteria**: SC-003 met

---

### Scenario 8: Admin Dashboard - Add Cookie

**Goal**: Verify admin interface (FR-011a)

**Steps**:
1. Login as administrator
2. Navigate to Settings → Cookie Consent
3. Click "Cookies" tab
4. Click "Add New Cookie"
5. Fill form:
   - Name: `_fbp`
   - Category: Marketing
   - Provider: Facebook Pixel
   - Purpose: Ad targeting and measurement
   - Expiration: 90 days
   - Domain: `.example.com`
6. Click "Save"
7. **Verify**: Cookie appears in list
8. **Verify**: Database query:
   ```sql
   SELECT * FROM wp_cookie_consent_cookies WHERE name = '_fbp';
   ```
9. Reload frontend, open preferences
10. **Verify**: `_fbp` appears in Marketing category

**Expected Result**:
- Admin CRUD works
- Changes reflect immediately on frontend

**Pass Criteria**: FR-011a met

---

### Scenario 9: Audit Log Verification

**Goal**: Verify 3-year audit trail (FR-013)

**Steps**:
1. Perform consent action (Accept All)
2. Login as admin
3. Navigate to Settings → Cookie Consent → Audit Logs
4. **Verify**: Recent event shows:
   - Visitor ID (hashed)
   - Event type (accept_all)
   - Accepted categories (JSON array)
   - Timestamp
   - IP address (if enabled)
5. Filter by date range (last 7 days)
6. **Verify**: Only recent events shown
7. Click "Export CSV"
8. **Verify**: CSV downloads with all log fields

**Expected Result**:
- All consent events logged
- Admin can view/export for compliance

**Pass Criteria**: FR-013, SC-010 met

---

### Scenario 10: Do Not Track Detection

**Goal**: Verify DNT header honored (FR-012)

**Steps**:
1. Enable "Do Not Track" in browser settings
   - Chrome: Settings → Privacy → Send "Do Not Track"
   - Firefox: Settings → Privacy → Always send "Do Not Track"
2. Clear browser data
3. Navigate to site
4. **Verify**: Console log shows "DNT detected, auto-rejecting"
5. **Verify**: Consent automatically set to "reject_all"
6. **Verify**: Banner shows "Rejected due to Do Not Track" message

**Expected Result**:
- DNT header respected
- Automatic rejection
- User informed

**Pass Criteria**: FR-012 met

---

### Scenario 11: Mobile Responsiveness

**Goal**: Verify mobile display (SC-009)

**Steps**:
1. Open site on mobile device or DevTools → Device Toolbar
2. Set viewport to iPhone 12 (390x844)
3. **Verify**: Banner displays full-width at bottom
4. **Verify**: Buttons touch-friendly (44x44px minimum)
5. Tap "Manage Preferences"
6. **Verify**: Modal/expanded view scrollable
7. **Verify**: Category toggles easy to tap
8. **Verify**: Text readable (16px+ font size)

**Expected Result**:
- Responsive on mobile
- Touch-friendly controls
- No horizontal scroll

**Pass Criteria**: SC-009 met

---

## Integration Tests

### Test 1: Cookie Blocking Integration

**File**: `tests/integration/test-cookie-blocking.php`

```php
class Test_Cookie_Blocking extends WP_UnitTestCase {
    public function test_blocks_analytics_before_consent() {
        // Setup: Register analytics script
        wp_enqueue_script('ga', 'https://www.googletagmanager.com/gtag/js');

        // Action: Render page without consent
        $output = do_shortcode('[cookie_consent_banner]');

        // Assert: Script blocked
        $this->assertStringContainsString('type="text/plain"', $output);
        $this->assertStringContainsString('data-consent-category="analytics"', $output);
    }

    public function test_activates_scripts_after_consent() {
        // Setup: Set consent in cookie
        $_COOKIE['wp_consent_status'] = md5('essential,analytics');

        // Action: Check if category consented
        $this->assertTrue(CCM_Consent::has_consent('analytics'));

        // Action: Render script
        $output = CCM_Blocker::render_script('ga', 'analytics');

        // Assert: Script active
        $this->assertStringContainsString('type="text/javascript"', $output);
    }
}
```

### Test 2: localStorage + Cookie Sync

**File**: `tests/integration/test-storage-sync.php`

```php
class Test_Storage_Sync extends WP_UnitTestCase {
    public function test_cookie_matches_localstorage() {
        // Setup: Simulate frontend storing consent
        $consent = [
            'acceptedCategories' => ['essential', 'analytics'],
            'version' => '1.0.0'
        ];

        // Action: Generate cookie value
        $cookie_value = CCM_Storage::generate_cookie_hash($consent);

        // Assert: Cookie value deterministic
        $this->assertEquals(md5('essential,analytics'), $cookie_value);
    }
}
```

### Test 3: Audit Logging Integration

**File**: `tests/integration/test-consent-logging.php`

```php
class Test_Consent_Logging extends WP_UnitTestCase {
    public function test_logs_accept_all_event() {
        global $wpdb;

        // Setup: Clear events table
        $wpdb->query("DELETE FROM {$wpdb->prefix}cookie_consent_events");

        // Action: Record consent
        CCM_Logger::record_event([
            'event_type' => 'accept_all',
            'accepted_categories' => ['essential', 'functional', 'analytics', 'marketing'],
            'visitor_id' => 'test123'
        ]);

        // Assert: Event logged
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cookie_consent_events");
        $this->assertEquals(1, $count);

        // Assert: Correct data
        $event = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}cookie_consent_events ORDER BY id DESC LIMIT 1");
        $this->assertEquals('accept_all', $event->event_type);
        $this->assertEquals('test123', $event->visitor_id);
    }
}
```

---

## Validation Checklist

### Functional Requirements

- [ ] FR-001: Banner displays before non-essential cookies
- [ ] FR-002: "Accept All" and "Reject All" buttons present
- [ ] FR-003: Script wrapper + interception blocks cookies
- [ ] FR-004: localStorage + cookie persistence works
- [ ] FR-005: 4 categories (Essential, Functional, Analytics, Marketing) exist
- [ ] FR-006: Cookie details show name, provider, purpose, expiration
- [ ] FR-007: Category-level accept/reject works
- [ ] FR-008: Preference manager accessible from all pages
- [ ] FR-009: Preference changes apply without reload
- [ ] FR-010: Rejected cookies cleared on preference change
- [ ] FR-011: Cookie detection (auto or manual)
- [ ] FR-011a: WordPress admin UI for cookie management
- [ ] FR-012: Do Not Track header respected
- [ ] FR-013: Audit logs retained for 3 years
- [ ] FR-015: Bottom banner, full-width, non-blocking

### Success Criteria

- [ ] SC-001: 100% of first-time visitors see banner
- [ ] SC-002: Consent decision <10 seconds
- [ ] SC-003: Consent persists 12+ months
- [ ] SC-004: Preferences accessible within 3 clicks
- [ ] SC-005: 100% of cookies displayed in details
- [ ] SC-006: Banner loads <1 second
- [ ] SC-007: 100% blocking accuracy before consent
- [ ] SC-008: Core functionality works with rejected cookies
- [ ] SC-009: Mobile responsive and touch-friendly
- [ ] SC-010: 100% of events captured in audit log

### Constitution Compliance

- [ ] WordPress coding standards (PHPCS validation)
- [ ] PHP 8.0+ syntax used
- [ ] BEM naming for CSS classes
- [ ] Etch theme integration (no theme conflicts)
- [ ] AutomaticCSS framework used for styling
- [ ] Integration tests written and passing
- [ ] Database migrations (up/down scripts)
- [ ] Version 1.0.0 tagged

---

## Performance Benchmarks

**Banner Load Time**: <1 second (SC-006)
- Measure: DevTools → Network → `consent-banner.js` load time
- Target: <500ms download + <500ms render

**Consent Check Speed**: <50ms (plan.md constraint)
- Measure: Console timing: `console.time('consent'); hasConsent('analytics'); console.timeEnd('consent')`
- Target: <50ms

**Database Query Performance**:
- Category list: <10ms (4 categories, indexed)
- Cookie list for category: <50ms (50-200 cookies, indexed)
- Audit log query (1 day): <100ms (indexed timestamp)

---

## Summary

11 testing scenarios covering all 3 user stories (P1-P3), 15 functional requirements, and 10 success criteria. Integration tests validate cookie blocking, storage sync, and audit logging. Checklist ensures constitution compliance and performance benchmarks met.

**Next Step**: Run `/speckit.tasks` to generate implementation task list.
