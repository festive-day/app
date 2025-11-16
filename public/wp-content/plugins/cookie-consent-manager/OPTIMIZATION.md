# Database Optimization & Code Quality Tools

This document describes the optimization and verification tools for the Cookie Consent Manager plugin.

## T081: PHPCS Validation

### Setup

1. Install PHPCS and WordPress Coding Standards:
```bash
composer global require squizlabs/php_codesniffer wp-coding-standards/wpcs
export PATH=$PATH:~/.composer/vendor/bin
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs
```

Or use Composer in the plugin directory:
```bash
cd public/wp-content/plugins/cookie-consent-manager
composer install
```

### Usage

Run PHPCS validation:
```bash
./phpcs-check.sh
```

Or manually:
```bash
phpcs --standard=.phpcs.xml .
```

Auto-fix issues:
```bash
phpcbf --standard=.phpcs.xml .
```

### Configuration

The `.phpcs.xml` file configures:
- WordPress Coding Standards
- PHP 8.0+ compatibility
- Excludes test files and vendor directories
- Allows short array syntax and short ternary operators

## T082: Database Query Optimization

### Usage

Analyze database queries with EXPLAIN:
```bash
wp eval-file optimize-queries.php
```

Or standalone (requires wp-load.php):
```bash
php optimize-queries.php
```

### What It Does

The script analyzes these key queries:
1. Categories list (ordered by display_order)
2. Cookies by category
3. Categories with cookie count (JOIN)
4. Cookies list with category name (JOIN)
5. Audit log with timestamp filter
6. Retention cleanup query

For each query, it:
- Shows the SQL statement
- Runs EXPLAIN to show execution plan
- Identifies full table scans
- Checks if indexes are being used
- Provides optimization recommendations

### Expected Results

All queries should:
- Use indexes (not full table scans)
- Have reasonable row counts
- Use appropriate JOIN strategies

## T083: Database Index Verification

### Usage

Verify all required indexes exist:
```bash
wp eval-file verify-indexes.php
```

Or standalone:
```bash
php verify-indexes.php
```

### Required Indexes

Per `data-model.md`, these indexes must exist:

**Categories Table:**
- `idx_slug` on `slug` column
- `idx_display_order` on `display_order` column

**Cookies Table:**
- `idx_category` on `category_id` column
- `idx_name` on `name` column

**Events Table:**
- `idx_visitor` on `visitor_id` column
- `idx_timestamp` on `event_timestamp` column
- `idx_event_type` on `event_type` column

### Expected Output

The script will:
- Check each table exists
- Verify each required index exists
- List all indexes on each table
- Provide SQL to create missing indexes if any

### Exit Code

- `0` = All indexes present
- `1` = Missing indexes detected

## Integration with CI/CD

These scripts can be integrated into CI/CD pipelines:

```bash
# PHPCS check
./phpcs-check.sh || exit 1

# Index verification
wp eval-file verify-indexes.php || exit 1

# Query optimization (informational)
wp eval-file optimize-queries.php
```

## Notes

- All scripts require WordPress to be loaded
- Database queries use `$wpdb->prefix` for table names
- Indexes are defined in `database/schema.sql` and `database/migrations/001-create-tables-up.sql`
- PHPCS configuration follows WordPress Coding Standards v3.0+

