# Guest Review API Documentation

This document outlines the Guest Review API endpoints for the frontend integration. All endpoints require JWT authentication.

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All endpoints require a valid JWT token in the Authorization header:
```
Authorization: Bearer {jwt_token}
```

## Endpoints

### 1. Get User's Reviews
**GET** `/reviews/guest`

Retrieve all reviews submitted by the authenticated user.

**Headers:**
```json
{
  "Authorization": "Bearer {jwt_token}",
  "Accept": "application/json",
  "Content-Type": "application/json"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "listing_id": 456,
      "trip_id": 789,
      "rating": 5,
      "review": "Amazing stay! The host was very welcoming.",
      "status": "published",
      "host_response_comment": "Thank you for the wonderful review!",
      "host_response_created_at": "2025-01-15T10:30:00Z",
      "created_at": "2025-01-14T15:20:00Z",
      "updated_at": "2025-01-15T10:30:00Z",
      "listing": {
        "id": 456,
        "title": "Beautiful Beach House",
        "location": "Miami, FL"
      },
      "booking": {
        "id": 789,
        "start_date": "2025-01-10",
        "end_date": "2025-01-12",
        "booking_status": "completed"
      }
    }
  ],
  "message": "Reviews retrieved successfully"
}
```

**Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### 2. Get Pending Reviews
**GET** `/reviews/pending`

Retrieve all completed trips that are eligible for review (within 30 days of completion).

**Headers:**
```json
{
  "Authorization": "Bearer {jwt_token}",
  "Accept": "application/json",
  "Content-Type": "application/json"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 789,
      "listing_id": 456,
      "user_id": 123,
      "start_date": "2025-01-10",
      "end_date": "2025-01-12",
      "booking_status": "completed",
      "created_at": "2025-01-08T12:00:00Z",
      "listing": {
        "id": 456,
        "title": "Beautiful Beach House",
        "location": "Miami, FL",
        "images": [
          {
            "url": "https://example.com/image1.jpg",
            "alt": "Beach house exterior"
          }
        ]
      },
      "days_remaining": 25
    }
  ],
  "message": "Pending reviews retrieved successfully"
}
```

### 3. Submit New Review
**POST** `/reviews`

Submit a new review for a completed booking.

**Headers:**
```json
{
  "Authorization": "Bearer {jwt_token}",
  "Accept": "application/json",
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "trip_id": 789,
  "rating": 5,
  "review": "Amazing stay! The host was very welcoming and the place was exactly as described.",
  "status": "draft"
}
```

**Field Validation:**
- `trip_id`: Required, integer, must be a valid booking ID owned by the user
- `rating`: Required, integer, between 1 and 5
- `review`: Optional, string, maximum 1000 characters
- `status`: Required, string, either "draft" or "published"

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "user_id": 123,
    "listing_id": 456,
    "trip_id": 789,
    "rating": 5,
    "review": "Amazing stay! The host was very welcoming and the place was exactly as described.",
    "status": "draft",
    "host_response_comment": null,
    "host_response_created_at": null,
    "created_at": "2025-01-14T15:20:00Z",
    "updated_at": "2025-01-14T15:20:00Z"
  },
  "message": "Review created successfully"
}
```

**Response (422 Validation Error):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rating": ["The rating field is required."],
    "trip_id": ["The selected trip id is invalid."]
  }
}
```

**Response (403 Forbidden):**
```json
{
  "success": false,
  "message": "You can only review completed bookings within 30 days of completion."
}
```

### 4. Update Review
**PUT** `/reviews/{reviewId}`

Update an existing review (only draft reviews can be updated).

**Headers:**
```json
{
  "Authorization": "Bearer {jwt_token}",
  "Accept": "application/json",
  "Content-Type": "application/json"
}
```

**Request Body:**
```json
{
  "rating": 4,
  "review": "Updated review content with more details about the stay.",
  "status": "published"
}
```

**Field Validation:**
- `rating`: Optional, integer, between 1 and 5
- `review`: Optional, string, maximum 1000 characters
- `status`: Optional, string, either "draft" or "published"

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "user_id": 123,
    "listing_id": 456,
    "trip_id": 789,
    "rating": 4,
    "review": "Updated review content with more details about the stay.",
    "status": "published",
    "host_response_comment": null,
    "host_response_created_at": null,
    "created_at": "2025-01-14T15:20:00Z",
    "updated_at": "2025-01-14T16:45:00Z"
  },
  "message": "Review updated successfully"
}
```

**Response (403 Forbidden):**
```json
{
  "success": false,
  "message": "You can only update draft reviews."
}
```

**Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Review not found"
}
```

### 5. Delete Review
**DELETE** `/reviews/{reviewId}`

Delete a review (only draft reviews can be deleted).

**Headers:**
```json
{
  "Authorization": "Bearer {jwt_token}",
  "Accept": "application/json"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Review deleted successfully"
}
```

**Response (403 Forbidden):**
```json
{
  "success": false,
  "message": "You can only delete draft reviews."
}
```

**Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Review not found"
}
```

### 6. Publish Review
**PATCH** `/reviews/{reviewId}/publish`

Publish a draft review (makes it visible to hosts and other users).

**Headers:**
```json
{
  "Authorization": "Bearer {jwt_token}",
  "Accept": "application/json",
  "Content-Type": "application/json"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "user_id": 123,
    "listing_id": 456,
    "trip_id": 789,
    "rating": 4,
    "review": "Great stay with excellent amenities.",
    "status": "published",
    "host_response_comment": null,
    "host_response_created_at": null,
    "created_at": "2025-01-14T15:20:00Z",
    "updated_at": "2025-01-14T17:00:00Z"
  },
  "message": "Review published successfully"
}
```

**Response (403 Forbidden):**
```json
{
  "success": false,
  "message": "You can only publish draft reviews."
}
```

## Error Responses

### Common Error Codes
- **400 Bad Request**: Invalid request format or missing required fields
- **401 Unauthorized**: Missing or invalid JWT token
- **403 Forbidden**: User doesn't have permission to perform the action
- **404 Not Found**: Resource not found
- **422 Unprocessable Entity**: Validation errors
- **500 Internal Server Error**: Server error

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Specific validation error message"]
  }
}
```

## Review Status Values
- **draft**: Review is saved but not visible to hosts or other users
- **published**: Review is visible to hosts and other users

## Business Rules
1. Users can only review bookings they made
2. Reviews can only be submitted for completed bookings
3. Reviews must be submitted within 30 days of booking completion
4. Only one review per booking is allowed
5. Only draft reviews can be updated or deleted
6. Published reviews cannot be modified by guests
7. Hosts can respond to published reviews (handled by separate host endpoints)

## Database Schema

### Migrations

The Guest Review API uses the following database tables:

#### 1. user_reviews Table
**Migration:** `2025_01_13_131556_create_user_reviews_table.php`
**Additional Fields:** `2025_09_12_093110_add_guest_review_fields_to_user_reviews_table.php`

```sql
CREATE TABLE user_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    listing_id BIGINT UNSIGNED NULL,
    trip_id BIGINT UNSIGNED NULL,
    rating TINYINT NOT NULL,
    review TEXT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    host_response_comment TEXT NULL,
    host_response_created_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (trip_id) REFERENCES bookings(id) ON DELETE CASCADE
);
```

#### 2. listing_reviews Table
**Migration:** `2025_01_20_110842_create_listing_reviews_table.php`

```sql
CREATE TABLE listing_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    listing_id BIGINT UNSIGNED NOT NULL,
    rating INTEGER NOT NULL,
    review TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);
```

### Seeders

#### 1. UserReviewSeeder
**File:** `database/seeders/UserReviewSeeder.php`

Seeds the `user_reviews` table with comprehensive sample data including:
- Various rating levels (1-5 stars)
- Both draft and published reviews
- Host responses (some with responses, some without)
- Different timestamps to simulate real usage patterns
- Proper foreign key relationships to users, listings, and bookings

**Sample Data:**
- 7 sample reviews with different statuses
- Mix of published and draft reviews
- Host responses with timestamps
- Ratings from 2-5 stars
- Realistic review content

#### 2. listingReviewSeeder
**File:** `database/seeders/listingReviewSeeder.php`

Seeds the `listing_reviews` table with sample data including:
- 6 sample listing reviews
- Various ratings (3-5 stars)
- Different users reviewing different listings
- Realistic review content and timestamps

### Running Migrations and Seeders

```bash
# Run migrations
php artisan migrate

# Run specific seeders
php artisan db:seed --class=UserReviewSeeder
php artisan db:seed --class=listingReviewSeeder

# Or run all seeders
php artisan db:seed
```

### Database Relationships

- `user_reviews.user_id` → `users.id`
- `user_reviews.listing_id` → `listings.id`
- `user_reviews.trip_id` → `bookings.id`
- `listing_reviews.user_id` → `users.id`
- `listing_reviews.listing_id` → `listings.id`

## Frontend Integration Notes

### Authentication
- Store JWT token securely (localStorage/sessionStorage)
- Include token in all API requests
- Handle token expiration (401 responses)
- Redirect to login on authentication failures

### Error Handling
- Display validation errors next to form fields
- Show user-friendly error messages for API failures
- Handle network errors gracefully

### UI Considerations
- Show review deadline countdown for pending reviews
- Distinguish between draft and published reviews in UI
- Disable editing for published reviews
- Show host responses when available
- Implement rating component (1-5 stars)
- Add character counter for review text (max 1000 chars)

### State Management
- Cache review data to minimize API calls
- Update local state after successful API operations
- Handle optimistic updates for better UX