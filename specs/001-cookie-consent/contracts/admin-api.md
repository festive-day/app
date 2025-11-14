# Admin API Contract: Cookie Consent Manager

**Feature**: Cookie Consent Manager
**Date**: 2025-11-14
**Type**: WordPress Admin AJAX/REST Endpoints

## Overview

WordPress admin endpoints for managing cookie categories, cookie registry, and viewing audit logs. All endpoints require `manage_options` capability (administrator role).

---

## Authentication

**Method**: WordPress nonce verification
**Capability Required**: `manage_options`
**Nonce Action**: `cookie_consent_admin_action`

All requests must include:
- Cookie: `wordpress_logged_in_*` (WordPress session)
- Header: `X-WP-Nonce` or POST field `_wpnonce`

---

## Endpoints

### 1. List Cookie Categories

**Endpoint**: `GET /wp-admin/admin-ajax.php?action=ccm_list_categories`

**Purpose**: Retrieve all cookie categories for admin display

**Request**:
```http
GET /wp-admin/admin-ajax.php?action=ccm_list_categories HTTP/1.1
X-WP-Nonce: abc123xyz789
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "slug": "essential",
      "name": "Essential",
      "description": "Required for site functionality",
      "is_required": true,
      "display_order": 10,
      "cookie_count": 5
    },
    {
      "id": 2,
      "slug": "analytics",
      "name": "Analytics",
      "description": "Help understand site usage",
      "is_required": false,
      "display_order": 30,
      "cookie_count": 12
    }
  ]
}
```

**Error Response** (403 Forbidden):
```json
{
  "success": false,
  "error": "Insufficient permissions"
}
```

---

### 2. Create Cookie Category

**Endpoint**: `POST /wp-admin/admin-ajax.php?action=ccm_create_category`

**Purpose**: Add new cookie category

**Request**:
```http
POST /wp-admin/admin-ajax.php?action=ccm_create_category HTTP/1.1
Content-Type: application/x-www-form-urlencoded
X-WP-Nonce: abc123xyz789

slug=social-media&name=Social+Media&description=Social+sharing+widgets&is_required=0&display_order=50
```

**Response** (201 Created):
```json
{
  "success": true,
  "data": {
    "id": 5,
    "slug": "social-media",
    "name": "Social Media",
    "description": "Social sharing widgets",
    "is_required": false,
    "display_order": 50,
    "created_at": "2025-11-14 10:30:00"
  }
}
```

**Validation Errors** (400 Bad Request):
```json
{
  "success": false,
  "errors": {
    "slug": "Slug must be lowercase alphanumeric with hyphens only",
    "name": "Name is required"
  }
}
```

---

### 3. Update Cookie Category

**Endpoint**: `POST /wp-admin/admin-ajax.php?action=ccm_update_category`

**Purpose**: Modify existing category

**Request**:
```http
POST /wp-admin/admin-ajax.php?action=ccm_update_category HTTP/1.1
Content-Type: application/x-www-form-urlencoded
X-WP-Nonce: abc123xyz789

id=2&name=Analytics+and+Performance&description=Updated+description
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 2,
    "slug": "analytics",
    "name": "Analytics and Performance",
    "description": "Updated description",
    "is_required": false,
    "display_order": 30,
    "updated_at": "2025-11-14 11:00:00"
  }
}
```

**Error Response** (404 Not Found):
```json
{
  "success": false,
  "error": "Category not found"
}
```

---

### 4. Delete Cookie Category

**Endpoint**: `POST /wp-admin/admin-ajax.php?action=ccm_delete_category`

**Purpose**: Remove category (CASCADE deletes associated cookies)

**Request**:
```http
POST /wp-admin/admin-ajax.php?action=ccm_delete_category HTTP/1.1
Content-Type: application/x-www-form-urlencoded
X-WP-Nonce: abc123xyz789

id=5
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Category deleted successfully",
  "deleted_cookies": 3
}
```

**Error Response** (409 Conflict):
```json
{
  "success": false,
  "error": "Cannot delete required category"
}
```

---

### 5. List Cookies

**Endpoint**: `GET /wp-admin/admin-ajax.php?action=ccm_list_cookies`

**Purpose**: Retrieve all cookies with pagination/filtering

**Request**:
```http
GET /wp-admin/admin-ajax.php?action=ccm_list_cookies&category_id=2&page=1&per_page=20 HTTP/1.1
X-WP-Nonce: abc123xyz789
```

**Query Parameters**:
- `category_id` (optional): Filter by category
- `page` (default: 1): Page number
- `per_page` (default: 20): Items per page

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "name": "_ga",
      "category_id": 2,
      "category_name": "Analytics",
      "provider": "Google Analytics",
      "purpose": "Tracks visitor behavior",
      "expiration": "2 years",
      "domain": ".example.com",
      "created_at": "2025-11-14 09:00:00"
    }
  ],
  "pagination": {
    "total": 45,
    "page": 1,
    "per_page": 20,
    "total_pages": 3
  }
}
```

---

### 6. Create Cookie

**Endpoint**: `POST /wp-admin/admin-ajax.php?action=ccm_create_cookie`

**Purpose**: Add new cookie to registry

**Request**:
```http
POST /wp-admin/admin-ajax.php?action=ccm_create_cookie HTTP/1.1
Content-Type: application/x-www-form-urlencoded
X-WP-Nonce: abc123xyz789

name=_fbp&category_id=4&provider=Facebook+Pixel&purpose=Ad+targeting&expiration=90+days&domain=.example.com
```

**Response** (201 Created):
```json
{
  "success": true,
  "data": {
    "id": 50,
    "name": "_fbp",
    "category_id": 4,
    "provider": "Facebook Pixel",
    "purpose": "Ad targeting",
    "expiration": "90 days",
    "domain": ".example.com",
    "created_at": "2025-11-14 12:00:00"
  }
}
```

**Validation Errors** (400 Bad Request):
```json
{
  "success": false,
  "errors": {
    "category_id": "Invalid category",
    "purpose": "Purpose is required"
  }
}
```

---

### 7. Update Cookie

**Endpoint**: `POST /wp-admin/admin-ajax.php?action=ccm_update_cookie`

**Purpose**: Modify existing cookie details

**Request**:
```http
POST /wp-admin/admin-ajax.php?action=ccm_update_cookie HTTP/1.1
Content-Type: application/x-www-form-urlencoded
X-WP-Nonce: abc123xyz789

id=50&purpose=Updated+purpose+text&expiration=120+days
```

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "id": 50,
    "name": "_fbp",
    "category_id": 4,
    "purpose": "Updated purpose text",
    "expiration": "120 days",
    "updated_at": "2025-11-14 13:00:00"
  }
}
```

---

### 8. Delete Cookie

**Endpoint**: `POST /wp-admin/admin-ajax.php?action=ccm_delete_cookie`

**Purpose**: Remove cookie from registry

**Request**:
```http
POST /wp-admin/admin-ajax.php?action=ccm_delete_cookie HTTP/1.1
Content-Type: application/x-www-form-urlencoded
X-WP-Nonce: abc123xyz789

id=50
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Cookie deleted successfully"
}
```

---

### 9. View Audit Logs

**Endpoint**: `GET /wp-admin/admin-ajax.php?action=ccm_view_logs`

**Purpose**: Retrieve consent event logs with filtering

**Request**:
```http
GET /wp-admin/admin-ajax.php?action=ccm_view_logs&start_date=2025-11-01&end_date=2025-11-14&event_type=accept_all&page=1&per_page=50 HTTP/1.1
X-WP-Nonce: abc123xyz789
```

**Query Parameters**:
- `start_date` (optional): Filter from date (YYYY-MM-DD)
- `end_date` (optional): Filter to date (YYYY-MM-DD)
- `event_type` (optional): Filter by event type
- `page` (default: 1): Page number
- `per_page` (default: 50): Items per page

**Response** (200 OK):
```json
{
  "success": true,
  "data": [
    {
      "id": 12345,
      "visitor_id": "a1b2c3d4...",
      "event_type": "accept_all",
      "accepted_categories": ["essential", "functional", "analytics", "marketing"],
      "rejected_categories": [],
      "consent_version": "1.0.0",
      "ip_address": "192.0.2.1",
      "user_agent": "Mozilla/5.0...",
      "event_timestamp": "2025-11-14 14:30:00"
    }
  ],
  "pagination": {
    "total": 2341,
    "page": 1,
    "per_page": 50,
    "total_pages": 47
  }
}
```

---

### 10. Export Audit Logs

**Endpoint**: `GET /wp-admin/admin-ajax.php?action=ccm_export_logs`

**Purpose**: Download audit logs as CSV for compliance reporting

**Request**:
```http
GET /wp-admin/admin-ajax.php?action=ccm_export_logs&start_date=2025-01-01&end_date=2025-11-14 HTTP/1.1
X-WP-Nonce: abc123xyz789
```

**Response** (200 OK):
```http
Content-Type: text/csv
Content-Disposition: attachment; filename="consent-logs-2025-11-14.csv"

id,visitor_id,event_type,accepted_categories,rejected_categories,consent_version,ip_address,event_timestamp
12345,a1b2c3d4...,accept_all,"[\"essential\",\"analytics\"]",[],1.0.0,192.0.2.1,2025-11-14 14:30:00
```

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 400 | Bad Request | Validation errors, malformed data |
| 403 | Forbidden | Insufficient permissions, nonce failure |
| 404 | Not Found | Resource doesn't exist |
| 409 | Conflict | Action violates constraint (e.g., delete required category) |
| 500 | Internal Server Error | Database error, unexpected failure |

---

## Rate Limiting

**Limit**: 100 requests per minute per admin user
**Implementation**: WordPress transient caching per user ID
**Response** (429 Too Many Requests):
```json
{
  "success": false,
  "error": "Rate limit exceeded. Try again in 30 seconds."
}
```

---

## Validation Rules

### Category Fields
- `slug`: Required, 2-50 chars, lowercase alphanumeric + hyphens, unique
- `name`: Required, 3-100 chars
- `description`: Optional, max 500 chars
- `is_required`: Boolean (0 or 1), only one category can have is_required=1
- `display_order`: Integer, default auto-increment by 10

### Cookie Fields
- `name`: Required, 1-255 chars
- `category_id`: Required, must exist
- `provider`: Optional, max 255 chars
- `purpose`: Required, 10-500 chars
- `expiration`: Required, freeform text
- `domain`: Optional, valid domain format

### Log Query Filters
- `start_date`, `end_date`: Valid date format (YYYY-MM-DD)
- `event_type`: Must be valid enum value
- `page`: Positive integer
- `per_page`: 1-100

---

## Security Notes

1. **Nonce verification** on all write operations
2. **Capability check** (`manage_options`) on all endpoints
3. **SQL injection protection** via WordPress $wpdb->prepare()
4. **XSS protection** via wp_kses() on text inputs
5. **CSRF protection** via WordPress nonce system
6. **Rate limiting** to prevent abuse

---

## Summary

10 admin endpoints covering full CRUD for categories and cookies, plus audit log viewing/export. All endpoints use WordPress AJAX conventions with proper security (nonce + capability checks).

**Next**: Generate frontend API contract (consent recording, preference retrieval).
