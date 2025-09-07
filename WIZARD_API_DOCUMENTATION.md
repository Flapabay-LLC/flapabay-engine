# Wizard Listing API Documentation

This document provides comprehensive documentation for the wizard listing endpoints that enable step-by-step property creation and management.

## Overview

The Wizard Listing API provides a streamlined interface for hosts to create and manage property listings through a guided, multi-step process. It includes features like:

- **Draft Management**: Create and update listings in draft state
- **Validation**: Step-by-step and full validation
- **State Management**: Server-driven state transitions
- **Concurrency Control**: ETag-based optimistic locking
- **Media Uploads**: Presigned URL flow for images and videos
- **Metadata**: Dynamic property types, categories, and constraints

## Authentication

All endpoints require authentication via Bearer token:
```
Authorization: Bearer <your_jwt_token>
```

The `host_id` is automatically derived from the authenticated user token.

## Base URL
```
POST /api/v1/wizard-listings
```

## Endpoints

### 1. Create New Listing

**POST** `/api/v1/wizard-listings`

Creates a new property listing in draft state or finalizes it immediately.

#### Request Body
```json
{
  "title": "Beautiful Oceanview Villa",
  "description": "Stunning 3-bedroom villa with panoramic ocean views",
  "property_type_id": 1,
  "category_id": 2,
  "address": "123 Ocean Drive",
  "city": "Miami Beach",
  "state": "FL",
  "country": "USA",
  "latitude": 25.7617,
  "longitude": -80.1918,
  "price_per_night": 299.99,
  "currency": "USD",
  "num_of_bedrooms": 3,
  "num_of_bathrooms": 2,
  "maximum_guests": 6,
  "amenities": ["wifi", "pool", "parking"],
  "images": ["image1.jpg", "image2.jpg"],
  "finalize": false
}
```

#### Response (Draft)
```json
{
  "draft_id": 123,
  "property": {
    "id": 123,
    "title": "Beautiful Oceanview Villa",
    "is_draft": true,
    "version": 1,
    "completion_percentage": 75,
    "images": ["image1.jpg", "image2.jpg"],
    "state_management": {
      "current_state": "draft",
      "completion_percentage": 75,
      "allowed_transitions": [
        {
          "action": "update",
          "method": "PATCH",
          "endpoint": "/api/v1/wizard-listings/123",
          "description": "Update draft property"
        },
        {
          "action": "validate",
          "method": "POST",
          "endpoint": "/api/v1/wizard-listings/123/validate",
          "description": "Validate property completeness"
        }
      ]
    }
  }
}
```

**Headers:**
- `ETag`: `"abc123def456"` - Version identifier for concurrency control

### 2. Update Wizard Listing

**PATCH** `/api/v1/wizard-listings/{id}`

Partially updates a draft listing with new field values.

#### Headers
- `If-Match`: `"abc123def456"` (optional) - ETag for concurrency control

#### Request Body
```json
{
  "title": "Updated Villa Title",
  "price_per_night": 349.99,
  "amenities": ["wifi", "pool", "parking", "gym"]
}
```

#### Response
```json
{
  "code": "SUCCESS",
  "message": "Property updated successfully",
  "property": {
    "id": 123,
    "title": "Updated Villa Title",
    "version": 2,
    "completion_percentage": 80,
    "state_management": {
      "current_state": "draft",
      "allowed_transitions": [...]
    }
  }
}
```

**Headers:**
- `ETag`: `"def456ghi789"` - Updated version identifier

#### Error Responses
- `412 Precondition Failed` - ETag mismatch (concurrent modification)
- `404 Not Found` - Property not found
- `400 Bad Request` - Property not in draft state

### 3. Validate Listing

**POST** `/api/v1/wizard-listings/{id}/validate`

Validates listing completeness for specific steps or full validation.

#### Request Body
```json
{
  "step": "basic_info",  // optional: specific step validation
  "fields": ["title", "description", "price_per_night"]  // optional: specific fields
}
```

#### Response (Success)
```json
{
  "code": "VALIDATION_SUCCESS",
  "message": "All validations passed",
  "completion_percentage": 85,
  "missing_fields": [],
  "step": "basic_info"
}
```

#### Response (Validation Errors)
```json
{
  "code": "VALIDATION_FAILED",
  "message": "Validation failed for some fields",
  "field_errors": {
    "title": ["Title is required"],
    "price_per_night": ["Price must be greater than 0"]
  },
  "completion_percentage": 60,
  "step": "basic_info"
}
```

### 4. Finalize Listing

**POST** `/api/v1/wizard-listings/{id}/finalize`

Finalizes a draft listing, making it live and bookable.

#### Headers
- `If-Match`: `"abc123def456"` (optional) - ETag for concurrency control

#### Request Body
```json
{
  "validate_only": false  // optional: true to validate without finalizing
}
```

#### Response (Success)
```json
{
  "code": "SUCCESS",
  "message": "Listing finalized successfully",
  "property": {
    "id": 123,
    "is_draft": false,
    "version": 3,
    "completion_percentage": 100,
    "state_management": {
      "current_state": "published",
      "allowed_transitions": [
        {
          "action": "update",
          "method": "POST",
          "endpoint": "/api/v1/listings/123",
          "description": "Update published property details"
        },
        {
          "action": "deactivate",
          "method": "POST",
          "endpoint": "/api/v1/listings/123/deactivate",
          "description": "Temporarily deactivate property"
        }
      ]
    }
  }
}
```

#### Response (Validation Only)
```json
{
  "code": "VALIDATION_SUCCESS",
  "message": "Listing is ready for finalization",
  "property": {
    "completion_percentage": 100,
    "state_management": {...}
  }
}
```

### 5. Get Wizard Metadata

**GET** `/api/v1/wizard-listings/meta`

Retrieve metadata for property creation including types, categories, amenities, and validation constraints.

#### Response
```json
{
  "code": "SUCCESS",
  "message": "Metadata retrieved successfully",
  "data": {
    "property_types": [
      {"id": 1, "name": "House", "description": "Entire house"},
      {"id": 2, "name": "Apartment", "description": "Entire apartment"}
    ],
    "categories": [
      {"id": 1, "name": "Beachfront", "description": "Properties near beach"},
      {"id": 2, "name": "City Center", "description": "Urban properties"}
    ],
    "amenities": [
      {"id": "wifi", "name": "WiFi", "category": "connectivity"},
      {"id": "pool", "name": "Swimming Pool", "category": "recreation"}
    ],
    "constraints": {
      "max_guests": {"min": 1, "max": 20},
      "bedrooms": {"min": 0, "max": 10},
      "bathrooms": {"min": 1, "max": 10},
      "price_per_night": {"min": 10, "max": 10000}
    },
    "validation_rules": {
      "required_fields": [
        "title", "description", "address", "city", "country",
        "price_per_night", "currency", "num_of_bedrooms",
        "num_of_bathrooms", "maximum_guests", "property_type_id"
      ],
      "field_formats": {
        "currency": "3-letter ISO code (USD, EUR, etc.)",
        "latitude": "Decimal degrees (-90 to 90)",
        "longitude": "Decimal degrees (-180 to 180)"
      }
    }
  }
}
```

### 6. Fetch Host Draft Listings

**GET** `/api/v1/wizard-listings/drafts`

Retrieve paginated list of host's draft listings with search and sorting.

#### Query Parameters
- `search` (string): Search in title, address, city, country
- `sort_by` (string): Field to sort by (default: `created_at`)
- `sort_order` (string): `asc` or `desc` (default: `desc`)
- `per_page` (integer): Items per page (default: 10)
- `page` (integer): Page number (default: 1)

#### Response
```json
{
  "code": "SUCCESS",
  "message": "Draft listings retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 123,
        "title": "Beautiful Villa",
        "completion_percentage": 75,
        "etag": "\"abc123def456\"",
        "state_management": {
          "current_state": "draft",
          "allowed_transitions": [...]
        },
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-15T14:20:00Z"
      }
    ],
    "total": 25,
    "per_page": 10,
    "last_page": 3
  }
}
```

## Media Upload Flow

### 1. Initialize Upload

**POST** `/api/v1/media/uploads/init`

#### Request Body
```json
{
  "filename": "villa-photo.jpg",
  "content_type": "image/jpeg",
  "file_size": 2048576
}
```

#### Response
```json
{
  "code": "SUCCESS",
  "message": "Upload initialized successfully",
  "data": {
    "upload_id": "upload_abc123",
    "presigned_url": "https://s3.amazonaws.com/bucket/path?signature=...",
    "expires_at": "2024-01-15T11:30:00Z",
    "max_file_size": 10485760,
    "allowed_types": ["image/jpeg", "image/png", "image/webp"]
  }
}
```

### 2. Upload File

Upload directly to the presigned URL using PUT request with the file binary data.

### 3. Attach Media

**POST** `/api/v1/wizard-listings/{id}/media`

#### Request Body
```json
{
  "upload_id": "upload_abc123",
  "media_type": "image",
  "caption": "Main bedroom view",
  "is_primary": true
}
```

## Error Handling

All endpoints use standardized error responses:

### Error Response Format
```json
{
  "code": "ERROR_CODE",
  "message": "Human-readable error message",
  "field_errors": {  // Only for validation errors
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

### Common Error Codes
- `UNAUTHORIZED` (401): Authentication required
- `FORBIDDEN` (403): Insufficient permissions
- `PROPERTY_NOT_FOUND` (404): Property doesn't exist
- `DRAFT_NOT_FOUND` (404): Draft listing not found
- `PROPERTY_NOT_DRAFT` (400): Operation only allowed on drafts
- `VALIDATION_FAILED` (422): Field validation errors
- `PRECONDITION_FAILED` (412): ETag mismatch
- `SERVER_ERROR` (500): Internal server error

## Concurrency Control

The API uses ETags for optimistic concurrency control:

1. **ETag Generation**: Each property has a version-based ETag
2. **If-Match Header**: Include current ETag in update requests
3. **Conflict Detection**: 412 error if ETag doesn't match
4. **Version Increment**: Version increases on each modification

### Example Flow
```bash
# 1. Get current property with ETag
GET /api/v1/wizard-listings/123
Response: ETag: "v1-abc123"

# 2. Update with If-Match header
PATCH /api/v1/wizard-listings/123
If-Match: "v1-abc123"

# 3. Successful update returns new ETag
Response: ETag: "v2-def456"

# 4. Concurrent update with old ETag fails
PATCH /api/v1/wizard-listings/123
If-Match: "v1-abc123"
Response: 412 Precondition Failed
```

## State Management

Each property response includes `state_management` with:
- `current_state`: Current property state (draft/published)
- `completion_percentage`: Completeness score (0-100)
- `allowed_transitions`: Available actions with endpoints

### State Transitions

**Draft State:**
- `update`: Modify property details
- `validate`: Check completeness
- `finalize`: Publish property (requires 80%+ completion)
- `delete`: Remove draft

**Published State:**
- `update`: Modify published property
- `deactivate`: Temporarily disable
- `delete`: Permanently remove

## Rate Limiting

- **General endpoints**: 100 requests per minute
- **Media uploads**: 20 uploads per minute
- **Validation**: 50 requests per minute

## Best Practices

1. **Always include If-Match headers** for update operations
2. **Handle 412 errors** by refetching current state
3. **Use validation endpoint** before finalization
4. **Check completion_percentage** before allowing finalization
5. **Follow allowed_transitions** for UI state management
6. **Implement proper error handling** for all error codes
7. **Cache metadata** to reduce API calls
8. **Use presigned URLs** for efficient media uploads

## SDK Examples

### JavaScript/TypeScript
```javascript
// Create draft listing
const response = await fetch('/api/v1/wizard-listings', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'My Property',
    price_per_night: 100,
    finalize: false
  })
});

const data = await response.json();
const etag = response.headers.get('ETag');

// Update with concurrency control
const updateResponse = await fetch(`/api/v1/wizard-listings/${data.draft_id}`, {
  method: 'PATCH',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'If-Match': etag
  },
  body: JSON.stringify({
    title: 'Updated Property Title'
  })
});
```

### PHP
```php
// Create draft listing
$response = Http::withToken($token)
    ->post('/api/v1/wizard-listings', [
        'title' => 'My Property',
        'price_per_night' => 100,
        'finalize' => false
    ]);

$data = $response->json();
$etag = $response->header('ETag');

// Update with concurrency control
$updateResponse = Http::withToken($token)
    ->withHeaders(['If-Match' => $etag])
    ->patch("/api/v1/wizard-listings/{$data['draft_id']}", [
        'title' => 'Updated Property Title'
    ]);
```

This completes the comprehensive API documentation for the Wizard Listing endpoints.