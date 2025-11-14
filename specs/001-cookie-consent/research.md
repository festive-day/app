# Research: Cookie Consent Manager

**Feature**: Cookie Consent Manager
**Date**: 2025-11-14
**Phase**: 0 (Research & Technology Decisions)

## Research Areas

### 1. WordPress Cookie Blocking Techniques

**Decision**: Script wrapper + `document.cookie` interception

**Rationale**:
- WordPress ecosystem heavily relies on third-party scripts (Google Analytics, Facebook Pixel, etc.)
- Script wrapper delays loading of non-essential scripts until consent given
- `document.cookie` API override catches scripts that slip through wrapper
- Dual approach provides 100% blocking accuracy (SC-007 requirement)
- Compatible with WordPress plugin architecture via `wp_enqueue_script` manipulation

**Alternatives considered**:
- **Content Security Policy (CSP) headers**: Rejected - Too restrictive, breaks legitimate WordPress admin functionality
- **JavaScript-only override**: Rejected - Misses server-set cookies and HTTP-only cookies
- **Network-level blocking (proxy)**: Rejected - Requires infrastructure changes outside WordPress scope

**Implementation approach**:
- Hook into `wp_enqueue_scripts` action (priority 1) to wrap third-party scripts
- Inject blocking script in `wp_head` hook (priority -9999) to run before any other scripts
- Use `data-consent-category` attributes on script tags for categorization

### 2. localStorage + Cookie Hybrid Storage Pattern

**Decision**: Store full consent object in localStorage, use cookie for fast checks

**Rationale**:
- localStorage immune to cookie clearing during browsing sessions
- Cookie allows server-side consent checks (PHP) before page render
- Meets 12-month persistence requirement (SC-003)
- Faster than database queries for high-traffic sites
- Survives WordPress cache purges

**Alternatives considered**:
- **Cookie-only storage**: Rejected - User cookie settings wipe preferences
- **Database-only storage**: Rejected - Requires user identification, excludes anonymous visitors
- **Session storage**: Rejected - Doesn't persist across browser sessions

**Implementation details**:
- localStorage key: `wp_cookie_consent` (JSON object with categories, timestamp, version)
- Cookie: `wp_consent_status` (simple string: "accepted"|"rejected"|"partial"|hash of categories)
- Cookie expiration: 365 days (renewable on consent update)
- Sync mechanism: JavaScript reads localStorage on load, writes cookie for PHP access

### 3. WordPress Admin Dashboard UI Patterns

**Decision**: WordPress Settings API + custom admin page under Settings menu

**Rationale**:
- Native WordPress UI patterns (familiar to admins)
- Settings API handles form validation, sanitization, nonce verification automatically
- Integrates with WordPress permissions system (`manage_options` capability)
- AutomaticCSS utilities work with WordPress admin styles
- No external dependencies required

**Alternatives considered**:
- **Custom React admin interface**: Rejected - Adds complexity, violates Simplicity principle (IV)
- **WordPress Customizer**: Rejected - Not appropriate for data management (designed for theme settings)
- **Gutenberg block editor**: Rejected - Overkill for simple CRUD operations

**Implementation approach**:
- Register settings page: `add_options_page()` + `add_settings_section()`
- Admin page structure:
  - **Cookie Registry tab**: List table (WP_List_Table class) for cookies
  - **Categories tab**: Category management (name, description, required status)
  - **Audit Logs tab**: Read-only log viewer with date filter
  - **Settings tab**: Global settings (banner text, color scheme, retention period)

### 4. Database Schema for 3-Year Audit Retention

**Decision**: Custom tables with automated cleanup cron job

**Rationale**:
- Custom tables avoid polluting `wp_options` or `wp_postmeta`
- Indexed tables provide fast audit log queries
- WordPress cron handles automated 3-year cleanup
- Migration scripts support schema versioning (Principle V)

**Alternatives considered**:
- **wp_options serialized storage**: Rejected - Poor query performance, no indexing
- **Custom post type**: Rejected - Adds unnecessary overhead, wrong abstraction
- **External logging service**: Rejected - Adds dependency, cost, privacy concerns

**Schema design decisions**:
- `wp_cookie_consent_events` table: Time-series data, partition by year for performance
- Indexes: `visitor_id`, `timestamp`, `action_type` for common queries
- Foreign keys: Link to `wp_cookie_consent_categories` for referential integrity
- Cleanup cron: Daily job deletes records older than 1095 days (3 years)

### 5. Script Wrapper Implementation Strategy

**Decision**: Dynamic script tag rewriting with consent check

**Rationale**:
- Works with both inline and external scripts
- Compatible with WordPress plugin ecosystem
- No changes required to third-party plugin code
- Graceful degradation if JavaScript disabled

**Alternatives considered**:
- **Manual script registration**: Rejected - Requires modifying every plugin
- **Reverse proxy interception**: Rejected - Infrastructure dependency
- **Browser extension approach**: Rejected - Can't control visitor browsers

**Implementation pattern**:
```javascript
// Original script tag (set by third-party plugin)
<script src="analytics.js"></script>

// Rewritten by cookie blocker
<script type="text/plain" data-consent-category="analytics" src="analytics.js"></script>

// Activated after consent
// Cookie blocker changes type="text/plain" to type="text/javascript"
```

**WordPress hooks used**:
- `wp_enqueue_scripts` (manipulate script registration)
- `script_loader_tag` filter (rewrite script tags with consent attributes)
- `wp_footer` (inject consent enforcement JavaScript)

### 6. AutomaticCSS Integration for Banner Styling

**Decision**: Use AutomaticCSS utility classes + BEM naming for custom components

**Rationale**:
- Leverages Etch theme's existing AutomaticCSS installation
- Utility classes handle responsive design automatically
- BEM naming prevents style conflicts with theme/plugins
- Meets Constitution Principle II (Etch Theme Integration)

**Alternatives considered**:
- **Inline styles**: Rejected - Not maintainable, violates WordPress standards
- **Separate CSS framework**: Rejected - Conflicts with Etch theme
- **Custom CSS only**: Rejected - Reinvents responsive utilities

**Class structure**:
```css
/* BEM block */
.cookie-banner { /* AutomaticCSS utilities */ }

/* BEM elements */
.cookie-banner__container { /* ACSS grid/flex */ }
.cookie-banner__message { /* ACSS typography */ }
.cookie-banner__button { /* ACSS button utilities */ }

/* BEM modifiers */
.cookie-banner--bottom { /* Position variant */ }
.cookie-banner--visible { /* State modifier */ }
```

### 7. PHPUnit Integration Testing Approach

**Decision**: WordPress test suite + WP_UnitTestCase base classes

**Rationale**:
- WordPress provides test utilities for database, hooks, users
- `WP_UnitTestCase` handles database transactions (rollback after each test)
- Meets Constitution Principle III (Integration Testing requirement)
- CI/CD friendly (GitHub Actions WordPress testing workflow)

**Test coverage plan**:
- **Cookie blocking tests**: Verify scripts blocked/unblocked based on consent
- **Storage integration**: Test localStorage ↔ cookie sync
- **Admin interface**: Test CRUD operations on cookies/categories
- **Audit logging**: Verify events logged with correct data
- **Etch compatibility**: Test banner renders with Etch theme active

**Test structure**:
```php
class Test_Cookie_Blocking extends WP_UnitTestCase {
    public function test_blocks_analytics_before_consent() {
        // Setup: Register analytics script
        // Action: Load page without consent
        // Assert: Script tag has type="text/plain"
    }
}
```

## Summary

All technical decisions resolved. No NEEDS CLARIFICATION markers remain. Ready for Phase 1 (data modeling).

**Key Technologies Confirmed**:
- PHP 8.0+ with WordPress 6.0+
- MySQL (WordPress database) + custom tables
- JavaScript (ES6+) for frontend
- localStorage + cookie hybrid storage
- WordPress Settings API for admin UI
- AutomaticCSS + BEM for styling
- PHPUnit + WordPress test suite

**Next Phase**: Generate data-model.md with database schema, entity relationships, and validation rules.
