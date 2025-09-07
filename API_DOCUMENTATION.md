# Authentication API Documentation

This document provides comprehensive API documentation for frontend integration, including all authentication endpoints with sample requests and responses.

## Base URL
```
Production: https://your-domain.com/api/v1
Development: http://localhost:8000/api/v1
```

## Authentication Headers

For protected endpoints, include the JWT token in the Authorization header:
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

---

## 🔐 Authentication Endpoints

### 1. Google Sign-In/Sign-Up

**Endpoint:** `POST /google/signin`  
**Rate Limit:** 10 requests per minute  
**Authentication:** None required  

#### Request
```json
{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjdkYzAyYjg1M2Y3ZTQwNzEifQ.eyJpc3MiOiJhY2NvdW50cy5nb29nbGUuY29tIiwiYXVkIjoieW91ci1nb29nbGUtY2xpZW50LWlkLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsInN1YiI6IjEwMjM0NTY3ODkwIiwiZW1haWwiOiJqb2huLmRvZUBleGFtcGxlLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJuYW1lIjoiSm9obiBEb2UiLCJwaWN0dXJlIjoiaHR0cHM6Ly9saDMuZ29vZ2xldXNlcmNvbnRlbnQuY29tL2EtL0FPaDE0R2hUX1E9czk2LWMiLCJnaXZlbl9uYW1lIjoiSm9obiIsImZhbWlseV9uYW1lIjoiRG9lIiwibG9jYWxlIjoiZW4iLCJpYXQiOjE2NDI2ODQ4MDAsImV4cCI6MTY0MjY4ODQwMH0.example_signature"
}
```

#### Success Response (200)
```json
{
  "success": true,
  "user": {
    "id": 123,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "created_at": "2025-01-24T10:30:00.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3YxL2dvb2dsZS9zaWduaW4iLCJpYXQiOjE2NDI2ODQ4MDAsImV4cCI6MTY0MjY4ODQwMCwibmJmIjoxNjQyNjg0ODAwLCJqdGkiOiJhYmMxMjMiLCJzdWIiOiIxMjMiLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.example_jwt_signature",
  "message": "Google authentication successful"
}
```

#### Error Responses

**Invalid Token (401)**
```json
{
  "success": false,
  "message": "Invalid or expired token"
}
```

**Unverified Email (400)**
```json
{
  "success": false,
  "message": "Google email not verified"
}
```

**Rate Limit Exceeded (429)**
```json
{
  "success": false,
  "message": "Too Many Attempts."
}
```

**Validation Error (400)**
```json
{
  "success": false,
  "message": "Token is missing",
  "errors": {
    "id_token": ["The id token field is required."]
  }
}
```

---

### 2. Google Logout

**Endpoint:** `POST /google/logout`  
**Authentication:** Required (JWT Token)  

#### Request
```json
{}
```

#### Headers
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Content-Type: application/json
```

#### Success Response (200)
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

#### Error Responses

**No Token Provided (400)**
```json
{
  "success": false,
  "message": "Token not provided"
}
```

**Invalid Token (401)**
```json
{
  "success": false,
  "message": "Token is invalid"
}
```

---

### 3. JWT Token Refresh

**Endpoint:** `POST /auth/refresh`  
**Authentication:** Required (JWT Token)  

#### Request
```json
{}
```

#### Headers
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
Content-Type: application/json
```

#### Success Response (200)
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.new_token_payload.new_signature",
  "token_type": "bearer",
  "expires_in": 3600
}
```

#### Error Response (401)
```json
{
  "success": false,
  "message": "Token could not be refreshed"
}
```

---

## 👤 User Management Endpoints

### 4. Get User Profile

**Endpoint:** `GET /users/{user_id}`  
**Authentication:** Required (JWT Token)  

#### Request
```
GET /api/v1/users/123
```

#### Success Response (200)
```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "profile_picture": "https://lh3.googleusercontent.com/a-/AOh14GhT_Q=s96-c",
    "google_id": "102345678901234567890",
    "email_verified_at": "2025-01-24T10:30:00.000000Z",
    "created_at": "2025-01-24T10:30:00.000000Z",
    "updated_at": "2025-01-24T10:30:00.000000Z",
    "profile_complete": true,
    "currency": "USD",
    "language": "en"
  }
}
```

### 5. Update User Profile

**Endpoint:** `POST /users/{user_id}`  
**Authentication:** Required (JWT Token)  

#### Request
```json
{
  "name": "John Smith",
  "phone": "+1234567890",
  "bio": "Travel enthusiast and host",
  "location": "New York, NY"
}
```

#### Success Response (200)
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 123,
    "name": "John Smith",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "bio": "Travel enthusiast and host",
    "location": "New York, NY",
    "updated_at": "2025-01-24T11:00:00.000000Z"
  }
}
```

### 6. Update Profile Picture

**Endpoint:** `POST /users/{user_id}/profile-picture`  
**Authentication:** Required (JWT Token)  
**Content-Type:** `multipart/form-data`  

#### Request
```
POST /api/v1/users/123/profile-picture
Content-Type: multipart/form-data

profile_picture: [file]
```

#### Success Response (200)
```json
{
  "success": true,
  "message": "Profile picture updated successfully",
  "data": {
    "profile_picture_url": "https://your-domain.com/storage/profile-pictures/123/profile.jpg"
  }
}
```

### 7. Complete User Details

**Endpoint:** `POST /users/{user_id}/complete-details`  
**Authentication:** Required (JWT Token)  

#### Request
```json
{
  "phone": "+1234567890",
  "date_of_birth": "1990-05-15",
  "gender": "male",
  "address": "123 Main St, New York, NY 10001",
  "emergency_contact_name": "Jane Doe",
  "emergency_contact_phone": "+1234567891",
  "government_id_type": "passport",
  "government_id_number": "A12345678"
}
```

#### Success Response (200)
```json
{
  "success": true,
  "message": "User details completed successfully",
  "data": {
    "profile_complete": true,
    "user_details": {
      "phone": "+1234567890",
      "date_of_birth": "1990-05-15",
      "gender": "male",
      "address": "123 Main St, New York, NY 10001",
      "emergency_contact_name": "Jane Doe",
      "emergency_contact_phone": "+1234567891",
      "government_id_type": "passport",
      "government_id_number": "A12345678"
    }
  }
}
```

---

## 🏠 Property Management Endpoints

### 8. Get Properties (Public)

**Endpoint:** `GET /properties`  
**Authentication:** None required  

#### Query Parameters
```
?location=New York&check_in=2025-02-01&check_out=2025-02-05&guests=2&page=1&limit=10
```

#### Success Response (200)
```json
{
  "success": true,
  "data": {
    "properties": [
      {
        "id": 1,
        "title": "Cozy Downtown Apartment",
        "description": "Beautiful apartment in the heart of the city",
        "price_per_night": 120.00,
        "currency": "USD",
        "location": "New York, NY",
        "latitude": 40.7128,
        "longitude": -74.0060,
        "max_guests": 4,
        "bedrooms": 2,
        "bathrooms": 1,
        "property_type": "Apartment",
        "images": [
          {
            "id": 1,
            "url": "https://your-domain.com/storage/properties/1/image1.jpg",
            "is_primary": true
          }
        ],
        "amenities": [
          {
            "id": 1,
            "name": "WiFi",
            "icon": "wifi"
          },
          {
            "id": 2,
            "name": "Kitchen",
            "icon": "kitchen"
          }
        ],
        "host": {
          "id": 456,
          "name": "Jane Smith",
          "profile_picture": "https://your-domain.com/storage/profiles/456/profile.jpg",
          "response_rate": 95,
          "response_time": "within an hour"
        },
        "rating": 4.8,
        "review_count": 24,
        "available": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 150,
      "last_page": 15,
      "has_more": true
    }
  }
}
```

### 9. Get Single Property

**Endpoint:** `GET /properties/{propertyId}`  
**Authentication:** None required  

#### Success Response (200)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Cozy Downtown Apartment",
    "description": "Beautiful apartment in the heart of the city with modern amenities and great location.",
    "price_per_night": 120.00,
    "currency": "USD",
    "location": "New York, NY",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "max_guests": 4,
    "bedrooms": 2,
    "bathrooms": 1,
    "property_type": "Apartment",
    "check_in_time": "15:00",
    "check_out_time": "11:00",
    "minimum_nights": 2,
    "maximum_nights": 30,
    "images": [
      {
        "id": 1,
        "url": "https://your-domain.com/storage/properties/1/image1.jpg",
        "is_primary": true,
        "caption": "Living room"
      },
      {
        "id": 2,
        "url": "https://your-domain.com/storage/properties/1/image2.jpg",
        "is_primary": false,
        "caption": "Bedroom"
      }
    ],
    "amenities": [
      {
        "id": 1,
        "name": "WiFi",
        "icon": "wifi",
        "category": "connectivity"
      },
      {
        "id": 2,
        "name": "Kitchen",
        "icon": "kitchen",
        "category": "cooking"
      }
    ],
    "house_rules": [
      "No smoking",
      "No pets",
      "No parties or events"
    ],
    "host": {
      "id": 456,
      "name": "Jane Smith",
      "profile_picture": "https://your-domain.com/storage/profiles/456/profile.jpg",
      "bio": "Experienced host with 5 years of hosting",
      "response_rate": 95,
      "response_time": "within an hour",
      "joined_date": "2020-03-15",
      "verified": true
    },
    "rating": 4.8,
    "review_count": 24,
    "reviews": [
      {
        "id": 1,
        "guest_name": "Mike Johnson",
        "guest_avatar": "https://your-domain.com/storage/profiles/789/profile.jpg",
        "rating": 5,
        "comment": "Amazing place! Very clean and comfortable.",
        "created_at": "2025-01-20T14:30:00.000000Z"
      }
    ],
    "availability": {
      "available_dates": [
        "2025-02-01",
        "2025-02-02",
        "2025-02-03"
      ],
      "blocked_dates": [
        "2025-02-04",
        "2025-02-05"
      ]
    }
  }
}
```

### 10. Create Property (Host)

**Endpoint:** `POST /properties`  
**Authentication:** Required (JWT Token)  

#### Request
```json
{
  "title": "Modern City Loft",
  "description": "Spacious loft in downtown area with city views",
  "property_type_id": 1,
  "price_per_night": 150.00,
  "currency": "USD",
  "location": "San Francisco, CA",
  "latitude": 37.7749,
  "longitude": -122.4194,
  "max_guests": 6,
  "bedrooms": 3,
  "bathrooms": 2,
  "check_in_time": "16:00",
  "check_out_time": "10:00",
  "minimum_nights": 1,
  "maximum_nights": 14,
  "amenity_ids": [1, 2, 3, 5, 8],
  "house_rules": [
    "No smoking",
    "Pets allowed with fee",
    "Quiet hours after 10 PM"
  ],
  "images": [
    {
      "url": "https://your-domain.com/temp/upload1.jpg",
      "is_primary": true,
      "caption": "Main living area"
    }
  ]
}
```

#### Success Response (201)
```json
{
  "success": true,
  "message": "Property created successfully",
  "data": {
    "id": 25,
    "title": "Modern City Loft",
    "status": "pending_review",
    "created_at": "2025-01-24T12:00:00.000000Z"
  }
}
```

---

## 📅 Booking Management Endpoints

### 11. Create Booking

**Endpoint:** `POST /booking`  
**Authentication:** Required (JWT Token)  

#### Request
```json
{
  "property_id": 1,
  "check_in_date": "2025-02-15",
  "check_out_date": "2025-02-18",
  "guests": 2,
  "guest_details": {
    "adults": 2,
    "children": 0,
    "infants": 0
  },
  "special_requests": "Early check-in if possible",
  "payment_method": "stripe",
  "payment_intent_id": "pi_1234567890"
}
```

#### Success Response (201)
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "booking_id": "BK-2025-001234",
    "property": {
      "id": 1,
      "title": "Cozy Downtown Apartment",
      "location": "New York, NY"
    },
    "dates": {
      "check_in": "2025-02-15",
      "check_out": "2025-02-18",
      "nights": 3
    },
    "guests": {
      "total": 2,
      "adults": 2,
      "children": 0,
      "infants": 0
    },
    "pricing": {
      "base_price": 120.00,
      "nights": 3,
      "subtotal": 360.00,
      "service_fee": 36.00,
      "taxes": 28.80,
      "total": 424.80,
      "currency": "USD"
    },
    "status": "confirmed",
    "confirmation_code": "ABC123XYZ",
    "created_at": "2025-01-24T12:30:00.000000Z"
  }
}
```

### 12. Get User Bookings

**Endpoint:** `GET /bookings`  
**Authentication:** Required (JWT Token)  

#### Query Parameters
```
?status=confirmed&page=1&limit=10
```

#### Success Response (200)
```json
{
  "success": true,
  "data": {
    "bookings": [
      {
        "id": "BK-2025-001234",
        "property": {
          "id": 1,
          "title": "Cozy Downtown Apartment",
          "location": "New York, NY",
          "image": "https://your-domain.com/storage/properties/1/image1.jpg"
        },
        "dates": {
          "check_in": "2025-02-15",
          "check_out": "2025-02-18",
          "nights": 3
        },
        "guests": 2,
        "total_amount": 424.80,
        "currency": "USD",
        "status": "confirmed",
        "confirmation_code": "ABC123XYZ",
        "created_at": "2025-01-24T12:30:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 5,
      "last_page": 1
    }
  }
}
```

---

## 💬 Chat/Messaging Endpoints

### 13. Start Chat Thread

**Endpoint:** `POST /chat/start`  
**Authentication:** Required (JWT Token)  

#### Request
```json
{
  "property_id": 1,
  "recipient_id": 456,
  "message": "Hi! I'm interested in booking your property for next weekend."
}
```

#### Success Response (201)
```json
{
  "success": true,
  "data": {
    "thread_id": "thread_abc123",
    "participants": [
      {
        "id": 123,
        "name": "John Doe",
        "role": "guest"
      },
      {
        "id": 456,
        "name": "Jane Smith",
        "role": "host"
      }
    ],
    "property": {
      "id": 1,
      "title": "Cozy Downtown Apartment"
    },
    "last_message": {
      "id": "msg_xyz789",
      "content": "Hi! I'm interested in booking your property for next weekend.",
      "sender_id": 123,
      "created_at": "2025-01-24T13:00:00.000000Z"
    },
    "created_at": "2025-01-24T13:00:00.000000Z"
  }
}
```

### 14. Get Chat Threads

**Endpoint:** `GET /chat/threads`  
**Authentication:** Required (JWT Token)  

#### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "thread_id": "thread_abc123",
      "participants": [
        {
          "id": 456,
          "name": "Jane Smith",
          "profile_picture": "https://your-domain.com/storage/profiles/456/profile.jpg",
          "role": "host"
        }
      ],
      "property": {
        "id": 1,
        "title": "Cozy Downtown Apartment",
        "image": "https://your-domain.com/storage/properties/1/image1.jpg"
      },
      "last_message": {
        "content": "That sounds perfect! Let me check availability.",
        "sender_name": "Jane Smith",
        "created_at": "2025-01-24T13:15:00.000000Z"
      },
      "unread_count": 2,
      "updated_at": "2025-01-24T13:15:00.000000Z"
    }
  ]
}
```

---

## 🔧 System Endpoints

### 15. Get System Amenities

**Endpoint:** `GET /system/amenities`  
**Authentication:** None required  

#### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "WiFi",
      "icon": "wifi",
      "category": "connectivity",
      "description": "High-speed internet access"
    },
    {
      "id": 2,
      "name": "Kitchen",
      "icon": "kitchen",
      "category": "cooking",
      "description": "Full kitchen with appliances"
    },
    {
      "id": 3,
      "name": "Air Conditioning",
      "icon": "ac",
      "category": "climate",
      "description": "Climate control system"
    }
  ]
}
```

### 16. Get Property Types

**Endpoint:** `GET /property-types`  
**Authentication:** None required  

#### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Apartment",
      "icon": "apartment",
      "description": "Private apartment in a building"
    },
    {
      "id": 2,
      "name": "House",
      "icon": "house",
      "description": "Entire house"
    },
    {
      "id": 3,
      "name": "Villa",
      "icon": "villa",
      "description": "Luxury villa with amenities"
    }
  ]
}
```

### 17. Get Supported Currencies

**Endpoint:** `GET /get-supported-currencies`  
**Authentication:** None required  

#### Success Response (200)
```json
{
  "success": true,
  "data": [
    {
      "code": "USD",
      "name": "US Dollar",
      "symbol": "$",
      "rate": 1.0
    },
    {
      "code": "EUR",
      "name": "Euro",
      "symbol": "€",
      "rate": 0.85
    },
    {
      "code": "GBP",
      "name": "British Pound",
      "symbol": "£",
      "rate": 0.73
    }
  ]
}
```

---

## 📱 Frontend Integration Examples

### JavaScript/React Example

```javascript
// Authentication Service
class AuthService {
  constructor() {
    this.baseURL = 'http://localhost:8000/api/v1';
    this.token = localStorage.getItem('auth_token');
  }

  // Google Sign-In
  async googleSignIn(idToken) {
    try {
      const response = await fetch(`${this.baseURL}/google/signin`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id_token: idToken })
      });
      
      const data = await response.json();
      
      if (data.success) {
        this.token = data.token;
        localStorage.setItem('auth_token', data.token);
        return { success: true, user: data.user };
      }
      
      return { success: false, message: data.message };
    } catch (error) {
      return { success: false, message: 'Network error' };
    }
  }

  // Logout
  async logout() {
    try {
      const response = await fetch(`${this.baseURL}/google/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json',
        }
      });
      
      const data = await response.json();
      
      if (data.success) {
        this.token = null;
        localStorage.removeItem('auth_token');
        return true;
      }
      
      return false;
    } catch (error) {
      return false;
    }
  }

  // Get authenticated headers
  getAuthHeaders() {
    return {
      'Authorization': `Bearer ${this.token}`,
      'Content-Type': 'application/json',
    };
  }

  // Check if user is authenticated
  isAuthenticated() {
    return !!this.token;
  }
}

// Usage Example
const authService = new AuthService();

// Google Sign-In with Google Identity Services
function handleGoogleSignIn(response) {
  authService.googleSignIn(response.credential)
    .then(result => {
      if (result.success) {
        console.log('User signed in:', result.user);
        // Redirect to dashboard or update UI
        window.location.href = '/dashboard';
      } else {
        console.error('Sign-in failed:', result.message);
        // Show error message to user
      }
    });
}

// Logout
function handleLogout() {
  authService.logout().then(success => {
    if (success) {
      window.location.href = '/login';
    }
  });
}

// API calls with authentication
async function fetchUserProfile(userId) {
  const response = await fetch(`${authService.baseURL}/users/${userId}`, {
    headers: authService.getAuthHeaders()
  });
  
  return await response.json();
}
```

---

## 🔒 Security Notes

1. **HTTPS Only**: All API calls must use HTTPS in production
2. **JWT Tokens**: Include in Authorization header for protected endpoints
3. **Rate Limiting**: Google sign-in limited to 10 requests per minute
4. **Token Expiration**: JWT tokens expire after 1 hour, use refresh endpoint
5. **Error Handling**: Always check response status and handle errors gracefully
6. **CORS**: Configure CORS settings for your frontend domain

---

## 📊 HTTP Status Codes

- **200**: Success
- **201**: Created (for POST requests)
- **400**: Bad Request (validation errors)
- **401**: Unauthorized (invalid/missing token)
- **403**: Forbidden (insufficient permissions)
- **404**: Not Found
- **422**: Unprocessable Entity (validation errors)
- **429**: Too Many Requests (rate limiting)
- **500**: Internal Server Error

---

**Last Updated**: January 24, 2025  
**API Version**: v1  
**Documentation Status**: Complete and Ready for Frontend Integration