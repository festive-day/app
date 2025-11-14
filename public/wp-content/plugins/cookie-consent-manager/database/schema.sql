-- Cookie Consent Manager Database Schema
-- Version: 1.0.0

-- Table: wp_cookie_consent_categories
CREATE TABLE IF NOT EXISTS wp_cookie_consent_categories (
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

-- Table: wp_cookie_consent_cookies
CREATE TABLE IF NOT EXISTS wp_cookie_consent_cookies (
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

-- Table: wp_cookie_consent_events
CREATE TABLE IF NOT EXISTS wp_cookie_consent_events (
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
