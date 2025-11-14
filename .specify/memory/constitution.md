<!--
Sync Impact Report
==================
Version Change: NEW → 1.0.0
Change Type: MAJOR (Initial constitution)
Modified Principles: N/A (initial creation)
Added Sections:
  - Core Principles (5 principles)
  - Technology Stack
  - Development Workflow
  - Governance

Templates Status:
  ✅ plan-template.md - Reviewed, no updates required
  ✅ spec-template.md - Reviewed, no updates required
  ✅ tasks-template.md - Reviewed, no updates required
  ✅ Command files - No agent-specific references found

Follow-up TODOs: None
-->

# SpecKit WordPress Development Constitution

## Core Principles

### I. WordPress Block Standards

**MUST adhere to WordPress ecosystem standards:**
- WordPress coding standards for all plugin development
- PHP 8.0+ features and syntax required
- BEM (Block Element Modifier) naming convention for all CSS classes
- HTML/CSS/JS coding standards per WordPress Codex

**Rationale**: Ensures compatibility with WordPress core, maintains code quality, enables seamless integration with the WordPress ecosystem, and provides consistent naming patterns across blocks.

### II. Etch Theme Integration

**MUST use Etch ecosystem exclusively:**
- Etch theme/editor is the only permitted theme framework
- AutomaticCSS framework MUST be implemented for all styling
- NO custom theme development outside Etch ecosystem
- Visual development environment editor is the primary development tool

**Rationale**: Standardizes on a unified development environment, leverages AutomaticCSS framework capabilities, ensures consistent design system, and reduces maintenance complexity.

### III. Integration Testing (NON-NEGOTIABLE)

**MUST test all integrations:**
- Block integration tests required for all custom blocks
- Database interaction tests required for all data operations
- Etch theme compatibility tests required for all UI components
- AutomaticCSS framework integration must be validated

**Focus areas requiring integration tests:**
- New block registration and rendering
- Block attribute schema changes
- Database schema modifications
- Inter-block communication
- Etch theme template integration

**Rationale**: WordPress blocks operate in a complex ecosystem. Integration tests catch compatibility issues, database integrity problems, and theme conflicts before production deployment.

### IV. Simplicity

**MUST start simple:**
- YAGNI (You Aren't Gonna Need It) principles strictly enforced
- NO premature optimization
- NO unnecessary abstractions
- Complexity MUST be justified with documented rationale

**Rationale**: WordPress and Etch provide extensive functionality. Adding unnecessary complexity creates maintenance burden and conflicts with core principles.

### V. Versioning & SQL Database Integrity

**MUST maintain version discipline:**
- Semantic versioning: MAJOR.MINOR.PATCH format
- MAJOR: Breaking changes to block schemas or database structure
- MINOR: New blocks or backward-compatible features
- PATCH: Bug fixes and refinements

**MUST maintain database integrity:**
- Database schema changes require migration scripts
- All migrations MUST be reversible (up/down scripts)
- Data validation required before and after migrations
- NO direct database modifications without version tracking

**Rationale**: WordPress relies heavily on database integrity. Poor versioning or schema management causes data corruption, plugin conflicts, and upgrade failures.

## Technology Stack

**Required Technologies:**
- **WordPress**: Latest stable version
- **PHP**: 8.0+ (minimum)
- **Etch Theme**: Latest version
- **AutomaticCSS Framework**: Latest version
- **Block Editor (Gutenberg)**: WordPress core block editor

**Forbidden Technologies:**
- Custom themes outside Etch ecosystem
- Non-BEM CSS naming conventions
- PHP versions below 8.0
- Direct database access without WordPress APIs

**Rationale**: Ensures compatibility, leverages modern PHP features, maintains ecosystem standards, and prevents technical debt.

## Development Workflow

**Block Development Process:**
1. Define block requirements in feature spec (user-facing functionality)
2. Create block schema with BEM-compliant naming
3. Write integration tests (block registration, rendering, Etch compatibility)
4. Implement block using WordPress standards and Etch editor
5. Apply AutomaticCSS framework for styling
6. Validate database integrity if block persists data
7. Test in Etch theme environment

**Database Change Process:**
1. Document schema change in feature plan
2. Create up/down migration scripts
3. Test migration on copy of production database
4. Validate data integrity before and after
5. Version bump (MAJOR or MINOR as appropriate)
6. Deploy migration with rollback plan

**Code Review Requirements:**
- WordPress coding standards validated (PHPCS)
- BEM naming convention verified
- Integration tests pass
- AutomaticCSS framework properly applied
- Database migrations tested
- Etch theme compatibility confirmed

## Governance

**Constitution Authority:**
- This constitution supersedes all other development practices
- All feature specifications MUST comply with these principles
- All pull requests/reviews MUST verify constitutional compliance
- Complexity MUST be justified against Principle IV (Simplicity)

**Amendment Process:**
- Amendments require documentation of rationale
- Version bump according to Principle V (Versioning)
- All dependent templates MUST be updated
- Migration plan required for breaking governance changes

**Compliance Verification:**
- Constitution Check section in plan-template.md enforces gates
- Violations require explicit justification in Complexity Tracking
- Unjustified violations block feature progression

**Runtime Development Guidance:**
- Use `.specify/memory/agent-context.md` for active technology guidance
- Agent context auto-updated from feature plans
- Manual additions preserved between updates

**Version**: 1.0.0 | **Ratified**: 2025-11-14 | **Last Amended**: 2025-11-14
