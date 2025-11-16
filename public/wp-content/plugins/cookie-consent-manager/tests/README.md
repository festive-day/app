# Cookie Consent Manager – PHPUnit Setup

This project now ships with a self-contained WordPress test harness so you can
run `phpunit` anywhere without re-downloading WordPress core every time.

## Directory layout

```
tests/
├── wordpress/                # Downloaded copy of WordPress core (6.8.3)
├── wordpress-tests-lib/      # WP test suite + wp-tests-config.php
│   └── wp-tests-config.php   # Points at the local_tests database
└── integration/…             # Plugin integration tests
tools/
├── bin/phpunit               # PHPUnit 9.6 phar
└── phpunit-polyfills/src/    # Yoast PHPUnit Polyfills autoloader
```

## One-time prerequisites

1. Ensure the LocalWP MySQL socket is running. Credentials come from `wp-config.php`
   (`root` / `root`, socket path set to `DB_HOST`).
2. Create the dedicated database (already done on this machine):

   ```bash
   mysql -u root -proot \
     --socket="/Users/david_atlarge/Library/Application Support/Local/run/7ycJ_xFru/mysql/mysqld.sock" \
     -e "CREATE DATABASE IF NOT EXISTS local_tests CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

## Running the test suite

1. Export the WordPress test library path so the bootstrap can find it:

   ```bash
   export WP_TESTS_DIR="/Users/david_atlarge/Local Sites/speckit-eval/app/public/wp-content/plugins/cookie-consent-manager/tests/wordpress-tests-lib"
   ```

2. Execute PHPUnit from the repository root (or plugin root):

   ```bash
   cd "/Users/david_atlarge/Local Sites/speckit-eval/app/public/wp-content/plugins/cookie-consent-manager"
   "../../../../tools/bin/phpunit" --bootstrap tests/bootstrap.php tests/integration
   ```

   > **Note:** PHPUnit emits deprecation warnings from `phpspec/prophecy` under PHP 8.3.
   > They do not block execution but will appear until Prophecy ships PHP 8.3-compatible
   > signatures.

## Updating WordPress or the test suite

- WordPress core (`tests/wordpress`) currently matches 6.8.3. Download a newer tarball
  and extract over this directory when upgrading WordPress.
- The WP test library (`tests/wordpress-tests-lib`) was copied from
  `wordpress-develop` tag `6.8.3`. Replace it with the matching tag when bumping
  WordPress.
- PHPUnit binary lives at `tools/bin/phpunit`. Update by downloading a new phar
  and replacing the file.

Everything under `tools/` and the downloaded WordPress directories are git-ignored
so local changes won’t pollute commits.
