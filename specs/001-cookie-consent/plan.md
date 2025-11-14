# Implementation Plan: Cookie Consent Manager

**Branch**: `001-cookie-consent` | **Date**: 2025-11-14 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/001-cookie-consent/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

WordPress plugin for GDPR/CCPA-compliant cookie consent management. Displays bottom banner for first-time visitors, blocks non-essential cookies until consent, provides granular category-based control, and maintains 3-year audit logs. Uses browser localStorage + cookie identifier for persistence, WordPress admin dashboard for cookie management, and script wrapper + API interception for blocking.

## Technical Context

**Language/Version**: PHP 8.0+
**Primary Dependencies**: WordPress latest stable, Etch Theme, AutomaticCSS framework
**Storage**: WordPress MySQL database (wp_options for plugin settings, custom tables for consent logs), browser localStorage (consent preferences), browser cookies (consent identifier)
**Testing**: PHPUnit (integration tests), WordPress test suite
**Target Platform**: WordPress 6.0+ with Etch theme
**Project Type**: WordPress plugin (single project)
**Performance Goals**: Banner loads <1 second, consent check <50ms, handles 10k+ concurrent visitors
**Constraints**: No page reload on preference changes, 100% cookie blocking accuracy, 3-year log retention
**Scale/Scope**: Single WordPress installation, unlimited visitors, 4 cookie categories minimum, admin dashboard UI

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### I. WordPress Block Standards ✅ PASS

- **WordPress coding standards**: Plugin follows WordPress Codex standards
- **PHP 8.0+**: Confirmed in Technical Context
- **BEM naming convention**: CSS classes use BEM for banner components
- **HTML/CSS/JS standards**: Frontend follows WordPress/Etch standards

**Status**: Compliant

### II. Etch Theme Integration ✅ PASS

- **Etch theme only**: Banner integrates with Etch theme templates
- **AutomaticCSS framework**: All styling uses AutomaticCSS utilities
- **No custom themes**: Plugin works within Etch ecosystem
- **Visual editor**: Admin UI uses WordPress/Etch visual patterns

**Status**: Compliant

### III. Integration Testing (NON-NEGOTIABLE) ✅ PASS

**Required tests**:
- Cookie blocking integration tests (verify 100% block rate before consent)
- localStorage + cookie identifier integration tests
- WordPress admin dashboard integration tests
- Script wrapper integration with third-party scripts (Google Analytics, Facebook Pixel)
- Database audit log integration tests (write/read/retention)
- Etch theme compatibility tests (banner rendering, AutomaticCSS application)

**Status**: Compliant - All integration points identified

### IV. Simplicity ✅ PASS

- **YAGNI**: No premature features (multi-language, geo-detection, subdomain sync all out of scope)
- **No abstractions**: Direct WordPress plugin structure, no custom frameworks
- **Justified complexity**: Script wrapper + interception justified by 100% blocking requirement

**Status**: Compliant - Minimal viable implementation

### V. Versioning & SQL Database Integrity ✅ PASS

**Versioning**:
- Initial version: 1.0.0
- MAJOR bumps: Schema changes to consent_events or consent_preferences tables
- MINOR bumps: New cookie categories or features
- PATCH bumps: Bug fixes

**Database integrity**:
- Custom tables: `wp_cookie_consent_events` (audit logs), `wp_cookie_consent_categories` (categories), `wp_cookie_consent_cookies` (cookie registry)
- Migration scripts required: Initial schema creation (1.0.0)
- Reversible migrations: DROP TABLE scripts for rollback
- Data validation: Foreign key constraints, NOT NULL enforcement

**Status**: Compliant - Migration plan required in Phase 1

### Final Gate Status: ✅ ALL GATES PASSED

No violations. Proceed to Phase 0.

## Project Structure

### Documentation (this feature)

```text
specs/001-cookie-consent/
├── plan.md              # This file (/speckit.plan command output)
├── spec.md              # Feature specification (completed)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (WordPress plugin structure)

```text
public/wp-content/plugins/cookie-consent-manager/
├── cookie-consent-manager.php          # Main plugin file
├── includes/
│   ├── class-cookie-manager.php        # Core plugin class
│   ├── class-consent-logger.php        # Audit logging
│   ├── class-cookie-blocker.php        # Script wrapper + interception
│   ├── class-storage-handler.php       # localStorage + cookie management
│   └── class-admin-interface.php       # WordPress admin dashboard
├── admin/
│   ├── views/                          # Admin UI templates
│   ├── css/                            # Admin styles (AutomaticCSS)
│   └── js/                             # Admin JavaScript
├── public/
│   ├── js/
│   │   ├── consent-banner.js           # Banner UI logic
│   │   ├── cookie-blocker.js           # Frontend blocking
│   │   └── storage-manager.js          # localStorage interface
│   ├── css/
│   │   └── banner.css                  # Banner styles (BEM + AutomaticCSS)
│   └── templates/
│       └── banner-template.php         # Banner HTML (Etch-compatible)
├── database/
│   ├── migrations/
│   │   ├── 001-create-tables-up.sql    # Initial schema
│   │   └── 001-create-tables-down.sql  # Rollback script
│   └── schema.sql                      # Full schema reference
└── tests/
    ├── integration/
    │   ├── test-cookie-blocking.php
    │   ├── test-consent-logging.php
    │   ├── test-admin-interface.php
    │   └── test-etch-compatibility.php
    └── bootstrap.php                   # Test setup
```

**Structure Decision**: WordPress plugin structure (single project). Plugin installed in standard WordPress plugins directory, integrates with Etch theme via WordPress hooks and AutomaticCSS classes. No custom post types or Gutenberg blocks required - purely frontend banner + admin interface.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

No violations. This section is empty.
