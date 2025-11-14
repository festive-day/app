# Tasks: Cookie Consent Manager

**Branch**: `001-cookie-consent`
**Input**: Design documents from `/specs/001-cookie-consent/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Integration tests ARE REQUIRED per Constitution Principle III (Non-negotiable integration testing)

**Organization**: Tasks grouped by user story for independent implementation and testing

## Format: `- [ ] [ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: User story label (US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: WordPress plugin initialization and basic structure

- [X] T001 Create plugin directory structure at public/wp-content/plugins/cookie-consent-manager/ per plan.md
- [X] T002 Create main plugin file cookie-consent-manager.php with WordPress plugin headers
- [X] T003 [P] Create includes/ directory with class stubs (class-cookie-manager.php, class-consent-logger.php, class-cookie-blocker.php, class-storage-handler.php, class-admin-interface.php)
- [X] T004 [P] Create admin/ directory structure (views/, css/, js/)
- [X] T005 [P] Create public/ directory structure (js/, css/, templates/)
- [X] T006 [P] Create database/ directory with migrations/ subdirectory
- [X] T007 [P] Create tests/ directory with integration/ subdirectory and bootstrap.php
- [X] T008 Setup PHPUnit configuration for WordPress test suite in tests/bootstrap.php
- [X] T009 Register plugin activation/deactivation hooks in cookie-consent-manager.php

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Database schema, core classes, WordPress integration - MUST complete before ANY user story

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T010 Create database schema SQL in database/schema.sql (all 3 tables: categories, cookies, events)
- [X] T011 Create up migration database/migrations/001-create-tables-up.sql per data-model.md
- [X] T012 Create down migration database/migrations/001-create-tables-down.sql per data-model.md
- [X] T013 Implement database migration execution in plugin activation hook (run 001-create-tables-up.sql)
- [X] T014 Insert default cookie categories (essential, functional, analytics, marketing) on plugin activation
- [X] T015 Implement core cookie manager class includes/class-cookie-manager.php (singleton pattern, WordPress hooks registration)
- [X] T016 [P] Implement consent logger class includes/class-consent-logger.php (record_event, generate visitor_id hash)
- [X] T017 [P] Implement storage handler class includes/class-storage-handler.php (localStorage sync, cookie management, generate_cookie_hash)
- [X] T018 Register WordPress AJAX actions for all frontend endpoints (ccm_get_banner_config, ccm_record_consent, ccm_check_dnt)
- [X] T019 Register WordPress AJAX actions for all admin endpoints (ccm_list_categories through ccm_export_logs per admin-api.md)
- [X] T020 Setup WordPress Settings API page at Settings → Cookie Consent in includes/class-admin-interface.php
- [X] T021 Implement nonce verification and capability checks (manage_options) for all admin endpoints
- [X] T022 Setup daily cron job for 3-year audit log retention cleanup (wp_cookie_consent_cleanup hook)

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Basic Consent Banner (Priority: P1) 🎯 MVP

**Goal**: First-time visitors see banner, can accept/reject cookies, choice persists, non-essential cookies blocked until consent

**Independent Test**: Display banner to new visitor, verify cookies blocked until consent given, confirm choice persists across page loads (quickstart.md Scenario 1-3)

### Integration Tests for User Story 1

**NOTE: Write tests FIRST, ensure they FAIL before implementation**

- [ ] T023 [P] [US1] Create test-cookie-blocking.php in tests/integration/ for script wrapper blocking (verify type="text/plain" before consent)
- [ ] T024 [P] [US1] Create test-consent-logging.php in tests/integration/ for audit event recording (verify accept_all/reject_all events logged)
- [ ] T025 [P] [US1] Create test-etch-compatibility.php in tests/integration/ for banner rendering with Etch theme

### Implementation for User Story 1

- [ ] T026 [P] [US1] Create banner HTML template in public/templates/banner-template.php (bottom banner, Accept All, Reject All buttons)
- [ ] T027 [P] [US1] Create banner CSS with BEM naming in public/css/banner.css (use AutomaticCSS utilities, full-width bottom position)
- [ ] T028 [US1] Implement consent-banner.js in public/js/ (display logic, localStorage write, cookie write, AJAX consent recording)
- [ ] T029 [US1] Implement cookie-blocker.js in public/js/ (script tag rewriting type="text/javascript" → type="text/plain", document.cookie interception)
- [ ] T030 [US1] Implement storage-manager.js in public/js/ (localStorage read/write, cookie read/write, sync logic)
- [ ] T031 [US1] Implement script wrapper in includes/class-cookie-blocker.php (hook wp_enqueue_scripts, script_loader_tag filter, add data-consent-category attributes)
- [ ] T032 [US1] Implement GET /ccm_get_banner_config endpoint (return categories, cookies, banner_text, consent_version per frontend-api.md)
- [ ] T033 [US1] Implement POST /ccm_record_consent endpoint (validate event_type, log to wp_cookie_consent_events, return event_id)
- [ ] T034 [US1] Implement GET /ccm_check_dnt endpoint (check DNT header, return dnt_enabled boolean)
- [ ] T035 [US1] Enqueue banner scripts/styles in wp_enqueue_scripts hook (priority -9999 for blocker.js)
- [ ] T036 [US1] Add "Cookie Settings" link to Etch theme footer (wp_footer hook)
- [ ] T037 [US1] Implement banner show/hide logic based on localStorage consent check (consent-banner.js)
- [ ] T038 [US1] Implement cookie clearing for rejected categories when consent changes from accept → reject (storage-manager.js)
- [ ] T039 [US1] Add validation for localStorage consent object (version check, timestamp expiration check 12 months)
- [ ] T040 [US1] Add rate limiting for POST /ccm_record_consent (10 requests/minute per IP, WordPress transient cache)

**Checkpoint**: User Story 1 fully functional - banner displays, cookies blocked, consent persists, audit logs working

---

## Phase 4: User Story 2 - Cookie Details & Categories (Priority: P2)

**Goal**: Users view cookie list organized by category with descriptions before giving consent

**Independent Test**: Access cookie details from banner, view cookie list by category, verify descriptions clear (quickstart.md Scenario 4-5)

### Integration Tests for User Story 2

- [ ] T041 [P] [US2] Add test for category display in test-etch-compatibility.php (verify 4 categories render with descriptions)
- [ ] T042 [P] [US2] Add test for cookie details modal in test-etch-compatibility.php (verify cookies grouped by category)

### Implementation for User Story 2

- [ ] T043 [P] [US2] Create cookie details modal HTML in public/templates/banner-template.php (expand banner template with modal)
- [ ] T044 [P] [US2] Create modal CSS in public/css/banner.css (category accordion, cookie list styles, BEM naming)
- [ ] T045 [US2] Implement "Manage Preferences" button click handler in consent-banner.js (open modal, populate categories)
- [ ] T046 [US2] Implement category accordion in consent-banner.js (expand/collapse categories, show cookie list per category)
- [ ] T047 [US2] Implement category-level checkboxes in modal (essential locked, functional/analytics/marketing toggleable)
- [ ] T048 [US2] Implement "Save Preferences" button handler (collect accepted/rejected categories, call storage-manager.js)
- [ ] T049 [US2] Update GET /ccm_get_banner_config endpoint to return cookies array per category (join wp_cookie_consent_cookies)
- [ ] T050 [US2] Add mobile responsive styles for modal in banner.css (scrollable, touch-friendly controls, 44px min button size)

**Checkpoint**: User Story 2 functional - cookie details accessible, categories displayed, descriptions clear

---

## Phase 5: User Story 3 - Preference Management & Updates (Priority: P3)

**Goal**: Users change cookie preferences at any time after initial choice

**Independent Test**: Locate preference manager in footer, change existing choices, verify new preferences take effect immediately without reload (quickstart.md Scenario 6)

### Integration Tests for User Story 3

- [ ] T051 [P] [US3] Add test for preference modification in test-consent-logging.php (verify modify event logged with correct categories)
- [ ] T052 [P] [US3] Add test for cookie clearing on reject in test-cookie-blocking.php (verify rejected cookies deleted)

### Implementation for User Story 3

- [ ] T053 [US3] Implement CookieConsentManager.openPreferences() JavaScript API in consent-banner.js (open modal, pre-fill current consent)
- [ ] T054 [US3] Implement CookieConsentManager.updateConsent() JavaScript API in storage-manager.js (update localStorage, cookie, fire AJAX, trigger script reload)
- [ ] T055 [US3] Implement CookieConsentManager.revokeConsent() JavaScript API in storage-manager.js (clear localStorage, cookie, non-essential cookies)
- [ ] T056 [US3] Add event handlers for preference changes to POST /ccm_record_consent with event_type="modify"
- [ ] T057 [US3] Implement script reloading logic in cookie-blocker.js (when consent updated, activate/deactivate scripts based on new categories)
- [ ] T058 [US3] Implement re-consent prompt when cookie list changes (check consent_version mismatch, show "Policy Updated" message)
- [ ] T059 [US3] Add WordPress action hooks (cookie_consent_given, cookie_consent_modified, cookie_consent_revoked) in includes/class-consent-logger.php

**Checkpoint**: User Story 3 functional - preferences changeable without reload, "Cookie Settings" link working, re-consent flow operational

---

## Phase 6: Admin Interface (Supporting All User Stories)

**Purpose**: WordPress admin dashboard for cookie/category management (FR-011a)

**Independent Test**: Login as admin, add/edit/delete cookies and categories via Settings → Cookie Consent (quickstart.md Scenario 8)

### Integration Tests for Admin Interface

- [ ] T060 [P] Create test-admin-interface.php in tests/integration/ (test CRUD operations on categories and cookies)

### Implementation for Admin Interface

- [ ] T061 [P] Create category management tab view in admin/views/categories-tab.php (list table, add/edit forms)
- [ ] T062 [P] Create cookie management tab view in admin/views/cookies-tab.php (WP_List_Table for cookies, add/edit forms)
- [ ] T063 [P] Create audit log tab view in admin/views/logs-tab.php (filterable table, export button)
- [ ] T064 [P] Create settings tab view in admin/views/settings-tab.php (banner text, retention period)
- [ ] T065 Create admin CSS in admin/css/admin-styles.css (AutomaticCSS utilities, WordPress admin compatible)
- [ ] T066 Create admin JavaScript in admin/js/admin-scripts.js (AJAX handlers for CRUD, form validation)
- [ ] T067 Implement POST /ccm_create_category endpoint per admin-api.md (validate slug/name, insert to wp_cookie_consent_categories)
- [ ] T068 Implement POST /ccm_update_category endpoint per admin-api.md (validate id, update categories table)
- [ ] T069 Implement POST /ccm_delete_category endpoint per admin-api.md (prevent delete if is_required=1, CASCADE delete cookies)
- [ ] T070 Implement GET /ccm_list_categories endpoint per admin-api.md (return categories with cookie_count join)
- [ ] T071 Implement POST /ccm_create_cookie endpoint per admin-api.md (validate category_id, insert to wp_cookie_consent_cookies)
- [ ] T072 Implement POST /ccm_update_cookie endpoint per admin-api.md (validate id, update cookies table)
- [ ] T073 Implement POST /ccm_delete_cookie endpoint per admin-api.md (delete from wp_cookie_consent_cookies)
- [ ] T074 Implement GET /ccm_list_cookies endpoint per admin-api.md (pagination, category filter, return cookies with category name)
- [ ] T075 Implement GET /ccm_view_logs endpoint per admin-api.md (date range filter, event_type filter, pagination)
- [ ] T076 Implement GET /ccm_export_logs endpoint per admin-api.md (CSV export with all fields, date range filter)
- [ ] T077 Add input validation for all admin endpoints (slug format, required fields, field lengths per data-model.md validation rules)
- [ ] T078 Add rate limiting for admin endpoints (100 requests/minute per user, WordPress transient cache)
- [ ] T079 Enqueue admin scripts/styles only on Cookie Consent settings page (admin_enqueue_scripts hook with page check)

**Checkpoint**: Admin interface functional - categories/cookies manageable, audit logs viewable/exportable

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements affecting multiple user stories, performance optimization, final validation

- [ ] T080 [P] Implement WordPress transient caching for GET /ccm_get_banner_config (1 hour cache, clear on category/cookie update)
- [ ] T081 [P] Add PHPCS validation for WordPress coding standards across all PHP files
- [ ] T082 [P] Optimize database queries with EXPLAIN analysis (categories list, cookies by category, audit log queries)
- [ ] T083 [P] Add database indexes verification per data-model.md (idx_slug, idx_display_order, idx_category, idx_visitor, idx_timestamp)
- [ ] T084 Implement cron cleanup for 3-year retention (DELETE FROM wp_cookie_consent_events WHERE event_timestamp < DATE_SUB(NOW(), INTERVAL 3 YEAR))
- [ ] T085 Add JavaScript console errors handling (try-catch blocks, error logging for AJAX failures)
- [ ] T086 Test Safari ITP compatibility (detect Safari, adjust blocking strategy per frontend-api.md browser compatibility notes)
- [ ] T087 Verify AutomaticCSS classes applied correctly (all banner/modal elements use ACSS utilities)
- [ ] T088 Test mobile responsiveness per quickstart.md Scenario 11 (iPhone 12 viewport, touch-friendly controls)
- [ ] T089 Verify performance benchmarks per quickstart.md (banner load <1s, consent check <50ms, DB queries within targets)
- [ ] T090 Test DNT detection per quickstart.md Scenario 10 (enable DNT in browser, verify auto-reject)
- [ ] T091 Test consent expiration per quickstart.md Scenario 7 (simulate 13 months, verify re-prompt)
- [ ] T092 Run all integration tests from tests/integration/ (ensure 100% pass rate)
- [ ] T093 Complete quickstart.md validation checklist (all 15 FR, 10 SC, constitution compliance)
- [ ] T094 Add plugin version constant (1.0.0) and update mechanism for future schema changes
- [ ] T095 Create uninstall.php for plugin cleanup (drop tables, delete options, clear transients)
- [ ] T096 Security hardening review (XSS protection wp_kses, SQL injection $wpdb->prepare, nonce verification)
- [ ] T097 Documentation: Add inline PHPDoc comments to all public methods
- [ ] T098 Documentation: Update plan.md with final file structure if changes occurred
- [ ] T099 Git commit with message per constitution versioning (version 1.0.0 tag)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - start immediately
- **Foundational (Phase 2)**: Depends on Setup - BLOCKS all user stories
- **User Stories (Phase 3-5)**: All depend on Foundational completion
  - US1, US2, US3 can proceed in parallel if staffed
  - Or sequentially by priority (P1 → P2 → P3)
- **Admin Interface (Phase 6)**: Depends on Foundational - can run parallel to User Stories
- **Polish (Phase 7)**: Depends on all phases complete

### User Story Dependencies

- **User Story 1 (P1)**: Depends ONLY on Foundational (Phase 2) - no other story dependencies
- **User Story 2 (P2)**: Depends ONLY on Foundational (Phase 2) - extends US1 banner but independently testable
- **User Story 3 (P3)**: Depends ONLY on Foundational (Phase 2) - reuses US1/US2 components but independently testable

### Within Each User Story

- Integration tests FIRST (write, verify FAIL)
- HTML/CSS templates before JavaScript
- JavaScript APIs before AJAX endpoints
- Core functionality before edge cases
- Story complete validation before next priority

### Parallel Opportunities

**Phase 1 (Setup)**: T003, T004, T005, T006, T007 all parallel

**Phase 2 (Foundational)**: T016, T017 parallel (logger + storage handler), T018 + T019 parallel (AJAX registration)

**User Story 1**: T023-T025 tests parallel, T026-T027 templates parallel, T028-T030 JavaScript files parallel

**User Story 2**: T041-T042 tests parallel, T043-T044 templates parallel

**User Story 3**: T051-T052 tests parallel

**Admin Interface**: T061-T064 view templates parallel, T067-T076 endpoint implementations parallel (different AJAX actions)

**Polish**: T080-T083 parallel (different concerns), T092-T099 can run after all tests pass

---

## Parallel Example: User Story 1

```bash
# Launch all integration tests together:
Task: T023 - test-cookie-blocking.php
Task: T024 - test-consent-logging.php
Task: T025 - test-etch-compatibility.php

# Launch all templates together:
Task: T026 - banner-template.php
Task: T027 - banner.css

# Launch all JavaScript files together:
Task: T028 - consent-banner.js
Task: T029 - cookie-blocker.js
Task: T030 - storage-manager.js
```

---

## Parallel Example: Admin Interface

```bash
# Launch all view templates together:
Task: T061 - categories-tab.php
Task: T062 - cookies-tab.php
Task: T063 - logs-tab.php
Task: T064 - settings-tab.php

# Launch all category endpoints together:
Task: T067 - ccm_create_category
Task: T068 - ccm_update_category
Task: T069 - ccm_delete_category
Task: T070 - ccm_list_categories

# Launch all cookie endpoints together:
Task: T071 - ccm_create_cookie
Task: T072 - ccm_update_cookie
Task: T073 - ccm_delete_cookie
Task: T074 - ccm_list_cookies
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001-T009)
2. Complete Phase 2: Foundational (T010-T022) - CRITICAL BLOCKER
3. Complete Phase 3: User Story 1 (T023-T040)
4. **STOP and VALIDATE**: Run quickstart.md Scenarios 1-3
5. Deploy/demo if passing

**Result**: Functional cookie consent banner with accept/reject, cookie blocking, persistence

### Incremental Delivery

1. **Foundation** (Phases 1-2) → Database + core classes ready
2. **MVP** (Phase 3 - US1) → Test → Deploy (basic banner working)
3. **Enhanced** (Phase 4 - US2) → Test → Deploy (cookie details added)
4. **Complete** (Phase 5 - US3) → Test → Deploy (preference management added)
5. **Admin** (Phase 6) → Test → Deploy (admin dashboard ready)
6. **Polish** (Phase 7) → Test → Release 1.0.0

Each phase adds value without breaking previous functionality.

### Parallel Team Strategy

With multiple developers after Foundational phase (T022) completes:

- **Developer A**: User Story 1 (T023-T040)
- **Developer B**: User Story 2 (T041-T050)
- **Developer C**: User Story 3 (T051-T059)
- **Developer D**: Admin Interface (T060-T079)

All stories independently testable, minimal merge conflicts (different files).

---

## Task Count Summary

- **Phase 1 (Setup)**: 9 tasks
- **Phase 2 (Foundational)**: 13 tasks (BLOCKING)
- **Phase 3 (User Story 1)**: 18 tasks (3 tests + 15 implementation)
- **Phase 4 (User Story 2)**: 10 tasks (2 tests + 8 implementation)
- **Phase 5 (User Story 3)**: 9 tasks (2 tests + 7 implementation)
- **Phase 6 (Admin Interface)**: 20 tasks (1 test + 19 implementation)
- **Phase 7 (Polish)**: 20 tasks

**Total**: 99 tasks

**Tests**: 8 integration test tasks (T023-T025, T041-T042, T051-T052, T060) per Constitution requirement

**Parallel Opportunities**:
- Phase 1: 5 tasks parallel
- Phase 2: 4 tasks parallel
- User Stories: All 3 stories can run parallel after Foundational (37 tasks across US1-US3)
- Admin: 8 tasks parallel (views + endpoints)
- Polish: 4 tasks parallel

**MVP Scope**: Phases 1-3 only (40 tasks) = Basic banner with accept/reject, cookie blocking, persistence

---

## Notes

- **[P]** = Different files, no blocking dependencies, can run parallel
- **[Story]** = Maps task to user story for traceability (US1, US2, US3)
- **Tests FIRST**: Write integration tests, verify they FAIL, then implement
- Each user story independently completable and testable per spec.md acceptance scenarios
- Commit after each task or logical group (use git per constitution)
- Stop at any checkpoint to validate story independently per quickstart.md scenarios
- All file paths match plan.md project structure (WordPress plugin in public/wp-content/plugins/)
- Constitution compliant: WordPress standards, PHP 8.0+, Etch theme, AutomaticCSS, BEM naming, integration tests required, versioning 1.0.0, database migrations
