# Host Experience Management API Documentation

## Overview
This API allows hosts to manage their experience listings. All endpoints require JWT authentication and automatically validate that the user has `is_host = true` status on the backend.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints require JWT token in the Authorization header:
```
Authorization: Bearer {jwt_token}
```

## Automatic Backend Validations
- **Authentication**: All endpoints verify JWT token validity
- **Host Status**: Backend automatically checks `user.is_host = true`
- **Ownership**: Users can only access/modify their own experiences
- **Listing Type**: Automatically filters for experience listing type

---

## API Endpoints

### 1. List Host's Experiences
**GET** `/experiences`

**Description**: Retrieve all experiences belonging to the authenticated host with search, filtering, and pagination.

**Query Parameters** (all optional):
```json
{
  "search": "string",           // Search in title, address, location, country
  "status": "draft|published|inactive",  // Filter by status
  "sort_by": "string",         // Default: "created_at"
  "sort_order": "asc|desc",    // Default: "desc"
  "per_page": "integer"        // Default: 10, pagination size
}
```

**Example Request**:
```bash
GET /api/experiences?search=hiking&status=published&per_page=5
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Experiences fetched successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "title": "Mountain Hiking Adventure",
        "description": "Experience the thrill of mountain hiking",
        "price": 150.00,
        "currency": "USD",
        "address": "123 Mountain Trail",
        "location": "Denver",
        "county": "Colorado",
        "country": "USA",
        "status": "published",
        "experience": {
          "duration": "4 hours",
          "activity_type": "adventure",
          "group_size": 8,
          "difficulty_level": "moderate"
        }
      }
    ],
    "per_page": 10,
    "total": 1
  }
}
```

---

### 2. Create New Experience
**POST** `/experiences`

**Description**: Create a new experience listing for the authenticated host.

**Request Body** (JSON):
```json
{
  "title": "string (required, max:255)",
  "description": "string (required, max:5000)",
  "price": "number (required, min:0)",
  "currency": "string (optional, 3 chars, default: USD)",
  "address": "string (required, max:500)",
  "city": "string (required, max:100)",
  "state": "string (optional, max:100)",
  "country": "string (required, max:100)",
  "latitude": "number (optional, -90 to 90)",
  "longitude": "number (optional, -180 to 180)",
  "status": "draft|published|inactive (optional, default: draft)",
  "images": ["array of image URLs (optional)"],
  "amenities": ["array of amenity IDs (optional)"],
  "duration": "string (required, max:100)",
  "activity_type": "outdoor|indoor|cultural|adventure|food_drink|wellness|educational|entertainment (required)",
  "group_size": "integer (required, 1-50)",
  "difficulty_level": "easy|moderate|challenging|expert (required)",
  "included_items": ["array of strings (optional, max:255 each)"],
  "requirements": "string (optional, max:2000)",
  "cancellation_policy": "flexible|moderate|strict (optional)"
}
```

**Example Request**:
```json
{
  "title": "Mountain Hiking Adventure",
  "description": "Experience the thrill of mountain hiking with professional guides",
  "price": 150.00,
  "currency": "USD",
  "address": "123 Mountain Trail",
  "city": "Denver",
  "state": "Colorado",
  "country": "USA",
  "latitude": 39.7392,
  "longitude": -104.9903,
  "status": "published",
  "images": ["https://example.com/image1.jpg"],
  "amenities": [1, 2, 3],
  "duration": "4 hours",
  "activity_type": "adventure",
  "group_size": 8,
  "difficulty_level": "moderate",
  "included_items": ["Professional guide", "Safety equipment", "Water bottle"],
  "requirements": "Basic fitness level required",
  "cancellation_policy": "flexible"
}
```

**Success Response** (201):
```json
{
  "success": true,
  "message": "Experience created successfully",
  "data": {
    "id": 1,
    "title": "Mountain Hiking Adventure",
    "user_id": 123,
    "listing_type_id": 2,
    "experience": {
      "id": 1,
      "listing_id": 1,
      "duration": "4 hours",
      "activity_type": "adventure"
    }
  }
}
```

---

### 3. Get Specific Experience
**GET** `/experiences/{id}`

**Description**: Retrieve details of a specific experience owned by the authenticated host.

**Path Parameters**:
- `id` (integer, required): Experience listing ID

**Example Request**:
```bash
GET /api/experiences/1
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Experience fetched successfully",
  "data": {
    "id": 1,
    "title": "Mountain Hiking Adventure",
    "description": "Experience the thrill of mountain hiking",
    "price": 150.00,
    "currency": "USD",
    "address": "123 Mountain Trail",
    "location": "Denver",
    "county": "Colorado",
    "country": "USA",
    "latitude": 39.7392,
    "longitude": -104.9903,
    "status": "published",
    "images": ["https://example.com/image1.jpg"],
    "amenities": [1, 2, 3],
    "experience": {
      "id": 1,
      "listing_id": 1,
      "duration": "4 hours",
      "activity_type": "adventure",
      "group_size": 8,
      "difficulty_level": "moderate",
      "included_items": ["Professional guide", "Safety equipment"],
      "requirements": "Basic fitness level required",
      "cancellation_policy": "flexible"
    }
  }
}
```

---

### 4. Update Experience
**PUT** `/experiences/{id}`

**Description**: Update an existing experience owned by the authenticated host.

**Path Parameters**:
- `id` (integer, required): Experience listing ID

**Request Body** (JSON) - All fields are optional:
```json
{
  "title": "string (optional, max:255)",
  "description": "string (optional, max:5000)",
  "price": "number (optional, min:0)",
  "currency": "string (optional, 3 chars)",
  "address": "string (optional, max:500)",
  "city": "string (optional, max:100)",
  "state": "string (optional, max:100)",
  "country": "string (optional, max:100)",
  "latitude": "number (optional, -90 to 90)",
  "longitude": "number (optional, -180 to 180)",
  "status": "draft|published|inactive (optional)",
  "images": ["array of image URLs (optional)"],
  "amenities": ["array of amenity IDs (optional)"],
  "duration": "string (optional, max:100)",
  "activity_type": "outdoor|indoor|cultural|adventure|food_drink|wellness|educational|entertainment (optional)",
  "group_size": "integer (optional, 1-50)",
  "difficulty_level": "easy|moderate|challenging|expert (optional)",
  "included_items": ["array of strings (optional, max:255 each)"],
  "requirements": "string (optional, max:2000)",
  "cancellation_policy": "flexible|moderate|strict (optional)"
}
```

**Example Request**:
```json
{
  "title": "Updated Mountain Hiking Adventure",
  "price": 175.00,
  "status": "published",
  "group_size": 10
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Experience updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Mountain Hiking Adventure",
    "price": 175.00,
    "status": "published",
    "experience": {
      "group_size": 10
    }
  }
}
```

---

### 5. Delete Experience
**DELETE** `/experiences/{id}`

**Description**: Delete an experience owned by the authenticated host.

**Path Parameters**:
- `id` (integer, required): Experience listing ID

**Example Request**:
```bash
DELETE /api/experiences/1
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Experience deleted successfully"
}
```

---

## Error Responses

### Authentication Required (401)
```json
{
  "success": false,
  "message": "Authentication required"
}
```

### Not a Host (403)
```json
{
  "success": false,
  "message": "Only hosts can manage experiences"
}
```

### Experience Not Found (404)
```json
{
  "success": false,
  "message": "Experience not found"
}
```

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "price": ["The price must be a number."]
  }
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to create experience",
  "error": "Detailed error message"
}
```

---

## Frontend Integration Notes

### 1. Authentication Flow
```javascript
// Set JWT token in axios defaults
axios.defaults.headers.common['Authorization'] = `Bearer ${jwtToken}`;

// Or for individual requests
const response = await axios.get('/api/experiences', {
  headers: {
    'Authorization': `Bearer ${jwtToken}`,
    'Content-Type': 'application/json'
  }
});
```

### 2. Error Handling
```javascript
try {
  const response = await axios.post('/api/experiences', experienceData);
  // Handle success
} catch (error) {
  if (error.response.status === 403) {
    // User is not a host - redirect or show error
  } else if (error.response.status === 422) {
    // Validation errors - show field-specific errors
    const errors = error.response.data.errors;
  }
}
```

### 3. Host Status Validation
**Important**: The backend automatically validates host status. Frontend should:
- Ensure only authenticated users access these endpoints
- Handle 403 responses gracefully (user not a host)
- No need to check `is_host` on frontend - backend handles this

### 4. Data Mapping
**Note**: The API uses these field mappings:
- `city` in request → `location` in database
- `state` in request → `county` in database

### 5. Activity Types
Valid values for `activity_type`:
- `outdoor`
- `indoor` 
- `cultural`
- `adventure`
- `food_drink`
- `wellness`
- `educational`
- `entertainment`

### 6. Difficulty Levels
Valid values for `difficulty_level`:
- `easy`
- `moderate`
- `challenging`
- `expert`

### 7. Status Values
Valid values for `status`:
- `draft` (default)
- `published`
- `inactive`

---

## Testing
Use the provided test file to verify API functionality:
```bash
php test_experience_api.php
```

For host authorization testing:
```bash
php test_host_authorization.php
```