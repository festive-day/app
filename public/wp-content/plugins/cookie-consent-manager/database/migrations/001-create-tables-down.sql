-- Migration Down: Drop cookie consent tables
-- Version: 1.0.0

-- Drop tables in reverse dependency order
DROP TABLE IF EXISTS wp_cookie_consent_events;
DROP TABLE IF EXISTS wp_cookie_consent_cookies;
DROP TABLE IF EXISTS wp_cookie_consent_categories;

-- Remove plugin version option
DELETE FROM wp_options WHERE option_name = 'ccm_db_version';
