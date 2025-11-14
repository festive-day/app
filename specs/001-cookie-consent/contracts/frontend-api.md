# Frontend API Contract: Cookie Consent Manager

**Feature**: Cookie Consent Manager
**Date**: 2025-11-14
**Type**: Public AJAX Endpoints + JavaScript API

## Overview

Public-facing endpoints for recording consent, retrieving preferences, and loading banner configuration. No authentication required (anonymous visitors).

---

## Endpoints

### 1. Get Banner Configuration

**Endpoint**: `GET /wp-admin/admin-ajax.php?action=ccm_get_banner_config`

**Purpose**: Load category list and banner text for initial display

**Request**:
```http
GET /wp-admin/admin-ajax.php?action=ccm_get_banner_config HTTP/1.1
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "slug": "essential",
        "name": "Essential",
        "description": "Required for site functionality",
        "is_required": true,
        "cookies": [
          {
            "name": "wordpress_logged_in",
            "provider": "WordPress Core",
            "purpose": "Session management",
            "expiration": "Session"
          }
        ]
      },
      {
        "slug": "analytics",
        "name": "Analytics",
        "description": "Help understand site usage",
        "is_required": false,
        "cookies": [
          {
            "name": "_ga",
            "provider": "Google Analytics",
            "purpose": "Tracks visitor behavior",
            "expiration": "2 years"
          }
        ]
      }
    ],
    "banner_text": {
      "heading": "We use cookies",
      "message": "This site uses cookies to enhance your experience...",
      "accept_all_label": "Accept All",
      "reject_all_label": "Reject All",
      "manage_label": "Manage Preferences"
    },
    "consent_version": "1.0.0"
  }
}
```

**Cache**: 1 hour (categories/cookies rarely change)

---

### 2. Record Consent

**Endpoint**: `POST /wp-admin/admin-ajax.php?action=ccm_record_consent`

**Purpose**: Log consent event to database (audit trail)

**Request**:
```http
POST /wp-admin/admin-ajax.php?action=ccm_record_consent HTTP/1.1
Content-Type: application/json

{
  "event_type": "accept_partial",
  "accepted_categories": ["essential", "functional", "analytics"],
  "rejected_categories": ["marketing"],
  "consent_version": "1.0.0"
}
```

**Request Fields**:
- `event_type`: "accept_all" | "reject_all" | "accept_partial" | "modify" | "revoke"
- `accepted_categories`: Array of category slugs
- `rejected_categories`: Array of category slugs
- `consent_version`: Plugin version (from banner config)

**Response** (201 Created):
```json
{
  "success": true,
  "data": {
    "event_id": 12345,
    "visitor_id": "a1b2c3d4e5f6...",
    "timestamp": "2025-11-14 15:00:00"
  }
}
```

**Error Response** (400 Bad Request):
```json
{
  "success": false,
  "error": "Invalid event_type"
}
```

**Rate Limiting**: 10 requests per minute per visitor (by IP)

---

### 3. Check Do Not Track

**Endpoint**: `GET /wp-admin/admin-ajax.php?action=ccm_check_dnt`

**Purpose**: Server-side DNT header check (FR-012)

**Request**:
```http
GET /wp-admin/admin-ajax.php?action=ccm_check_dnt HTTP/1.1
DNT: 1
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "dnt_enabled": true,
    "auto_reject": true
  }
}
```

**Logic**:
- If `DNT: 1` header present → `auto_reject: true`
- Frontend automatically sets consent to "reject_all"

---

## JavaScript API

The plugin exposes a global `CookieConsentManager` object for programmatic control.

### Initialize Banner

```javascript
CookieConsentManager.init({
  onConsentGiven: function(consent) {
    console.log('Consent given:', consent);
  },
  onConsentChanged: function(consent) {
    console.log('Consent modified:', consent);
  }
});
```

**Parameters**:
- `onConsentGiven`: Callback fired when initial consent recorded
- `onConsentChanged`: Callback fired when existing consent modified

---

### Check Consent Status

```javascript
const consent = CookieConsentManager.getConsent();
// Returns:
// {
//   version: "1.0.0",
//   timestamp: 1699900800,
//   consentGiven: true,
//   acceptedCategories: ["essential", "analytics"],
//   rejectedCategories: ["marketing"]
// }
```

**Returns**: Consent object from localStorage or `null` if no consent

---

### Check Category Consent

```javascript
if (CookieConsentManager.hasConsent('analytics')) {
  // Load analytics scripts
}
```

**Parameters**:
- `category`: Category slug to check

**Returns**: `true` if category accepted, `false` otherwise

---

### Update Consent

```javascript
CookieConsentManager.updateConsent({
  acceptedCategories: ["essential", "functional"],
  rejectedCategories: ["analytics", "marketing"]
});
```

**Parameters**:
- `acceptedCategories`: Array of category slugs
- `rejectedCategories`: Array of category slugs

**Effect**:
- Updates localStorage
- Updates cookie
- Logs event via AJAX
- Fires `onConsentChanged` callback
- Reloads scripts based on new consent (if needed)

---

### Revoke Consent

```javascript
CookieConsentManager.revokeConsent();
```

**Effect**:
- Clears localStorage
- Clears consent cookie
- Logs revoke event
- Clears all non-essential cookies
- Shows banner again

---

### Open Preference Manager

```javascript
CookieConsentManager.openPreferences();
```

**Effect**: Opens cookie preferences modal (used for "Cookie Settings" link in footer)

---

## LocalStorage Schema

**Key**: `wp_cookie_consent`

**Value** (JSON):
```json
{
  "version": "1.0.0",
  "timestamp": 1699900800,
  "consentGiven": true,
  "acceptedCategories": ["essential", "functional", "analytics"],
  "rejectedCategories": ["marketing"],
  "lastModified": 1699900800
}
```

**Validation**:
- JavaScript validates schema before read/write
- Corrupted data triggers re-consent flow
- Version mismatch triggers re-consent with "Policy Updated" message

---

## Cookie Schema

**Name**: `wp_consent_status`

**Value**: MD5 hash of accepted categories (e.g., `md5("essential,analytics")`)

**Attributes**:
```
Path=/
Max-Age=31536000 (365 days)
SameSite=Lax
Secure (if HTTPS)
```

**Purpose**: Fast server-side consent check before page render

---

## Script Blocking Implementation

### Before Consent

Third-party scripts rewritten with `type="text/plain"`:

```html
<!-- Original -->
<script src="https://www.googletagmanager.com/gtag/js?id=GA-12345"></script>

<!-- Blocked (rewritten by plugin) -->
<script type="text/plain" data-consent-category="analytics" src="https://www.googletagmanager.com/gtag/js?id=GA-12345"></script>
```

### After Consent

Plugin changes `type="text/plain"` to `type="text/javascript"`:

```html
<script type="text/javascript" data-consent-category="analytics" src="https://www.googletagmanager.com/gtag/js?id=GA-12345"></script>
```

**Implementation**:
1. Plugin hooks into `script_loader_tag` filter
2. Checks script src against cookie registry
3. If cookie category not consented → rewrites type
4. Frontend JS monitors consent changes and activates scripts

---

## Event Hooks (WordPress Actions)

Plugin fires WordPress actions for third-party integration:

```php
// Fired after consent recorded
do_action('cookie_consent_given', $consent_data);

// Fired when consent modified
do_action('cookie_consent_modified', $old_consent, $new_consent);

// Fired when consent revoked
do_action('cookie_consent_revoked', $visitor_id);
```

**Use case**: Other plugins can react to consent changes (e.g., clear analytics data when consent revoked).

---

## Performance Considerations

1. **Banner config endpoint cached** (1 hour) to reduce database queries
2. **Consent check via cookie** (not localStorage) for initial page render (PHP can read cookie, not localStorage)
3. **Rate limiting** prevents DoS via consent recording
4. **Async AJAX calls** for consent logging (doesn't block page load)
5. **localStorage primary storage** (faster than cookie for complex data)

---

## Security Notes

1. **No authentication required** (public endpoints for anonymous visitors)
2. **Input validation** on event_type and category slugs
3. **Rate limiting** by IP address to prevent abuse
4. **No PII stored** (visitor_id is hashed, not reversible)
5. **XSS protection** on all user-facing text (banner messages, cookie descriptions)

---

## Browser Compatibility

**Supported Browsers**:
- Chrome 90+ (localStorage, cookie API)
- Firefox 88+ (localStorage, cookie API)
- Safari 14+ (localStorage, ITP restrictions noted)
- Edge 90+ (localStorage, cookie API)

**Safari Note**: Intelligent Tracking Prevention (ITP) may limit third-party cookie blocking. Plugin detects Safari and adjusts blocking strategy (blocks first-party wrapper scripts instead).

---

## Summary

3 public AJAX endpoints + JavaScript API covering:
- Banner configuration loading
- Consent recording (audit logs)
- Do Not Track header detection
- Programmatic consent management

JavaScript API provides developer-friendly interface for checking consent status, updating preferences, and integrating with third-party scripts.

**Next**: Generate quickstart.md with testing scenarios.
