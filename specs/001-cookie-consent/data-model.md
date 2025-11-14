# Data Model: Cookie Consent Manager

**Feature**: Cookie Consent Manager
**Date**: 2025-11-14
**Phase**: 1 (Data Modeling)

## Entity Overview

Four primary entities with clear separation of concerns:

1. **Cookie Category** - Groupings for cookies (Essential, Functional, Analytics, Marketing)
2. **Cookie** - Individual cookies registered in the system
3. **Consent Event** - Audit log of all consent actions (3-year retention)
4. **Consent Preference** - Current visitor consent state (client-side storage, not database)

## Database Schema

### Table: `wp_cookie_consent_categories`

Stores cookie category definitions managed by administrators.

```sql
CREATE TABLE wp_cookie_consent_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_required TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields**:
- `id`: Primary key
- `slug`: Machine-readable identifier (e.g., "analytics", "marketing")
- `name`: Human-readable name (e.g., "Analytics Cookies")
- `description`: Purpose explanation shown to visitors
- `is_required`: Boolean - if 1, category cannot be rejected (e.g., Essential)
- `display_order`: Sort order in banner UI (lower = shown first)
- `created_at`, `updated_at`: Timestamps for auditing

**Validation Rules**:
- `slug`: Lowercase alphanumeric + hyphens only, max 50 chars
- `name`: Required, max 100 chars
- `is_required`: Only one category should have is_required=1 (Essential)
- `display_order`: Default increment by 10 to allow reordering

**Default Records** (inserted on plugin activation):
```sql
INSERT INTO wp_cookie_consent_categories (slug, name, description, is_required, display_order) VALUES
('essential', 'Essential', 'Required for site functionality', 1, 10),
('functional', 'Functional', 'Enhance site features', 0, 20),
('analytics', 'Analytics', 'Help understand site usage', 0, 30),
('marketing', 'Marketing', 'Personalize ads and content', 0, 40);
```

---

### Table: `wp_cookie_consent_cookies`

Registry of all cookies tracked by the system.

```sql
CREATE TABLE wp_cookie_consent_cookies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(255),
    purpose TEXT,
    expiration VARCHAR(100),
    domain VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES wp_cookie_consent_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields**:
- `id`: Primary key
- `name`: Cookie name (e.g., "_ga", "wordpress_logged_in")
- `category_id`: Foreign key to categories table
- `provider`: Who sets the cookie (e.g., "Google Analytics", "WordPress Core")
- `purpose`: Why the cookie exists (shown in cookie details view)
- `expiration`: How long cookie lasts (e.g., "Session", "1 year", "2 days")
- `domain`: Cookie domain scope (e.g., ".example.com", "example.com")
- `created_at`, `updated_at`: Timestamps

**Validation Rules**:
- `name`: Required, max 255 chars
- `category_id`: Must reference valid category
- `provider`: Optional, max 255 chars
- `purpose`: Required for transparency (GDPR compliance)
- `expiration`: Required, freeform text for flexibility
- `domain`: Optional, validates as domain pattern if provided

**Relationships**:
- Belongs to one Category (many-to-one)
- CASCADE DELETE: If category deleted, cookies also deleted

---

### Table: `wp_cookie_consent_events`

Audit log of all consent actions for compliance (3-year retention).

```sql
CREATE TABLE wp_cookie_consent_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visitor_id VARCHAR(64) NOT NULL,
    event_type ENUM('accept_all', 'reject_all', 'accept_partial', 'modify', 'revoke') NOT NULL,
    accepted_categories TEXT,
    rejected_categories TEXT,
    consent_version VARCHAR(20),
    ip_address VARCHAR(45),
    user_agent TEXT,
    event_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_visitor (visitor_id),
    INDEX idx_timestamp (event_timestamp),
    INDEX idx_event_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Fields**:
- `id`: Primary key
- `visitor_id`: Anonymous identifier (SHA256 hash of IP + User-Agent, not PII)
- `event_type`: Action taken (accept all, reject all, partial accept, modify existing, revoke)
- `accepted_categories`: JSON array of category slugs accepted (e.g., `["essential","analytics"]`)
- `rejected_categories`: JSON array of category slugs rejected
- `consent_version`: Plugin version when consent given (for tracking consent under different policies)
- `ip_address`: Visitor IP (optional, for geographic compliance tracking)
- `user_agent`: Browser identifier (helps distinguish devices)
- `event_timestamp`: When action occurred

**Validation Rules**:
- `visitor_id`: Required, generated via `hash('sha256', $ip . $user_agent . $salt)`
- `event_type`: Must be one of defined enum values
- `accepted_categories`, `rejected_categories`: Valid JSON arrays of category slugs
- `consent_version`: Matches plugin version format (e.g., "1.0.0")
- `ip_address`: Optional IPv4/IPv6 format
- `event_timestamp`: Auto-set, indexed for retention queries

**Retention Policy**:
- Records older than 1095 days (3 years) deleted by daily cron job
- Cron hook: `wp_cookie_consent_cleanup`
- Cleanup query: `DELETE FROM wp_cookie_consent_events WHERE event_timestamp < DATE_SUB(NOW(), INTERVAL 3 YEAR)`

**Privacy Considerations**:
- `visitor_id` is hashed, not reversible to individual
- `ip_address` stored for compliance only (can be disabled in settings)
- No linkage to WordPress user accounts (anonymous consent tracking)

---

## Client-Side Storage (Not Database)

### localStorage: `wp_cookie_consent`

Stores full consent object on visitor's browser.

**Structure** (JSON):
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

**Fields**:
- `version`: Plugin version when consent recorded
- `timestamp`: Unix timestamp of initial consent
- `consentGiven`: Boolean (true if any action taken)
- `acceptedCategories`: Array of category slugs
- `rejectedCategories`: Array of category slugs
- `lastModified`: Unix timestamp of last modification

**Validation**:
- JavaScript validates structure before save
- Version mismatch triggers re-consent prompt
- Timestamp validates 12-month expiration (SC-003)

---

### Cookie: `wp_consent_status`

Small identifier cookie for fast PHP-side consent checks.

**Value**: Hash of accepted categories (e.g., `md5(implode(',', $accepted))`)

**Attributes**:
- Path: `/` (site-wide)
- Expiration: 365 days
- HttpOnly: `false` (JavaScript needs read access)
- Secure: `true` (HTTPS only, recommended)
- SameSite: `Lax`

**Purpose**: Allows PHP to check consent status before page render (optimization for script blocking).

---

## Entity Relationships

```
Cookie Category (1) ──< (many) Cookie
                │
                │ (referenced by JSON array)
                │
                └──────< Consent Event (many)

Consent Preference (client-side) ──references──> Cookie Category (by slug)
```

**Relationship Rules**:
1. Each Cookie belongs to exactly one Category
2. Categories can have zero or many Cookies
3. Consent Events reference Categories by slug (JSON array), not foreign key (allows category deletion without breaking audit trail)
4. Consent Preference (client-side) mirrors database category slugs

---

## State Transitions

### Consent Lifecycle States

1. **No Consent** (initial state)
   - `wp_consent_status` cookie absent
   - `wp_cookie_consent` localStorage absent
   - Banner displays on page load

2. **Consent Given** (terminal state for session)
   - User clicks "Accept All", "Reject All", or saves partial preferences
   - localStorage written with full consent object
   - Cookie written with hash
   - Consent Event logged to database
   - Banner dismissed

3. **Consent Modified** (from Consent Given)
   - User opens "Cookie Settings" and changes preferences
   - localStorage updated
   - Cookie updated
   - New Consent Event logged (type: `modify`)
   - No page reload required (FR-009)

4. **Consent Revoked** (from Consent Given → back to No Consent)
   - User clears browser data, or
   - 12 months pass since last consent (SC-003)
   - localStorage/cookie cleared
   - Banner reappears on next visit
   - Consent Event logged (type: `revoke`) if explicit action

5. **Consent Expired - Re-prompt** (from Consent Given)
   - Cookie list or policy version changes (FR-014)
   - Existing consent remains active
   - Banner appears with "Updated" message
   - User must review and re-consent
   - New Consent Event logged with new version

---

## Validation Rules Summary

### Category Validation
- Slug: lowercase, alphanumeric, hyphens, unique, max 50 chars
- Name: required, max 100 chars
- Only one required category permitted

### Cookie Validation
- Name: required, max 255 chars
- Category: must exist in categories table
- Purpose: required for transparency

### Consent Event Validation
- Visitor ID: SHA256 hash, 64 chars
- Event type: enum validation
- Categories: valid JSON, slugs exist in categories table
- Timestamp: within reasonable range (not future, not pre-plugin-activation)

### Client Storage Validation
- localStorage JSON structure validated on read/write
- Cookie expiration enforced (365 days from set date)
- Version mismatch triggers re-consent flow

---

## Indexes & Performance

**Query Patterns**:
1. Find all cookies in category: `SELECT * FROM cookies WHERE category_id = ?` (indexed: category_id)
2. Audit log for visitor: `SELECT * FROM events WHERE visitor_id = ?` (indexed: visitor_id)
3. Recent events: `SELECT * FROM events WHERE event_timestamp > ?` (indexed: event_timestamp)
4. Category lookup by slug: `SELECT * FROM categories WHERE slug = ?` (indexed: slug, unique)
5. Retention cleanup: `DELETE FROM events WHERE event_timestamp < ?` (indexed: event_timestamp)

**Expected Data Volume**:
- Categories: <20 records (admin-managed, low volume)
- Cookies: 50-200 records (depends on site integrations)
- Consent Events: 10k-1M+ records over 3 years (high volume, time-series)

**Optimization Strategy**:
- Consent Events table candidates for partitioning by year (if volume exceeds 1M records)
- Consider archiving events older than 1 year to separate table if query performance degrades
- Cache category list in WordPress transient (rarely changes, frequently queried)

---

## Migration Scripts

### Up Migration (001-create-tables-up.sql)

```sql
-- Create categories table
CREATE TABLE IF NOT EXISTS wp_cookie_consent_categories ( /* schema above */ );

-- Create cookies table
CREATE TABLE IF NOT EXISTS wp_cookie_consent_cookies ( /* schema above */ );

-- Create events table
CREATE TABLE IF NOT EXISTS wp_cookie_consent_events ( /* schema above */ );

-- Insert default categories
INSERT INTO wp_cookie_consent_categories /* default records above */;

-- Set plugin version option
INSERT INTO wp_options (option_name, option_value, autoload)
VALUES ('cookie_consent_db_version', '1.0.0', 'yes')
ON DUPLICATE KEY UPDATE option_value = '1.0.0';
```

### Down Migration (001-create-tables-down.sql)

```sql
-- Drop tables in reverse dependency order
DROP TABLE IF EXISTS wp_cookie_consent_events;
DROP TABLE IF EXISTS wp_cookie_consent_cookies;
DROP TABLE IF EXISTS wp_cookie_consent_categories;

-- Remove plugin version option
DELETE FROM wp_options WHERE option_name = 'cookie_consent_db_version';
```

---

## Summary

Data model complete. Three custom tables with clear separation:
- **Categories**: Admin-managed groupings (low volume)
- **Cookies**: Cookie registry (medium volume)
- **Events**: Audit trail (high volume, time-series)

Client-side storage (localStorage + cookie) provides fast consent checks without database queries on every page load.

Meets all Constitution requirements:
- ✅ Database integrity (foreign keys, migrations, validation)
- ✅ Versioning (schema versioned, migration scripts)
- ✅ Simplicity (no unnecessary abstractions, direct WordPress patterns)

**Next Phase**: Generate API contracts (admin endpoints for CRUD operations).
