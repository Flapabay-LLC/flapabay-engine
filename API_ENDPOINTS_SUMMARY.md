# API Endpoints Summary

Quick reference guide for all available API endpoints with HTTP methods, authentication requirements, and descriptions.

## Base URL
```
Production: https://your-domain.com/api/v1
Development: http://localhost:8000/api/v1
```

---

## 🔐 Authentication Endpoints

| Method | Endpoint | Auth Required | Rate Limit | Description |
|--------|----------|---------------|------------|-------------|
| `POST` | `/google/signin` | ❌ | 10/min | Google OAuth sign-in/sign-up |
| `POST` | `/google/logout` | ✅ | - | Logout and invalidate JWT token |
| `POST` | `/auth/refresh` | ✅ | - | Refresh JWT token |

---

## 👤 User Management Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/users/{user_id}` | ✅ | Get user profile |
| `POST` | `/users/{user_id}` | ✅ | Update user profile |
| `POST` | `/users/{user_id}/profile-picture` | ✅ | Update profile picture |
| `POST` | `/users/{user_id}/complete-details` | ✅ | Complete user details |
| `GET` | `/users/{user_id}/reviews` | ✅ | Get user reviews |
| `POST` | `/complete-user-details` | ✅ | Complete user details (alternative) |
| `POST` | `/host/signup` | ✅ | Register as host |

---

## 🏠 Property Management Endpoints

### Public Property Endpoints
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/properties` | ❌ | Get all properties (with filters) |
| `GET` | `/properties/{propertyId}` | ❌ | Get single property details |
| `GET` | `/properties/{propertyId}/description` | ❌ | Get property description |
| `GET` | `/properties/{propertyId}/price-details` | ❌ | Get property pricing |
| `GET` | `/properties/{propertyId}/amenities` | ❌ | Get property amenities |
| `GET` | `/properties/{propertyId}/availability` | ❌ | Get property availability |

### Protected Property Endpoints
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/properties` | ✅ | Create new property |
| `POST` | `/update-properties` | ✅ | Update property details |
| `POST` | `/properties/{propertyId}/availability` | ✅ | Set property availability |

---

## 📋 Listing Management Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/wizard-listings` | ✅ | Create new listing (wizard) |
| `POST` | `/listings/{listingId}` | ✅ | Update host listing |
| `GET` | `/listings/host` | ✅ | Get host's listings |
| `GET` | `/listings/host/drafts` | ✅ | Get host's draft listings |
| `DELETE` | `/listings/{listingId}` | ✅ | Delete host listing |

---

## 📅 Booking & Reservation Endpoints

### Bookings
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/booking` | ✅ | Create new booking |
| `GET` | `/bookings` | ✅ | Get user bookings |
| `GET` | `/booking/{book_id}` | ✅ | Get specific booking |
| `PUT` | `/booking/{book_id}/cancel` | ✅ | Cancel booking |
| `POST` | `/bookings/{booking_id}/invoice` | ✅ | Generate booking invoice |
| `GET` | `/my-trips` | ✅ | Get user's trips |
| `GET` | `/bookings/host` | ✅ | Get host bookings |

### Reservations
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/reserve` | ✅ | Create reservation |
| `GET` | `/reservations` | ✅ | Get user reservations |
| `GET` | `/reservations/{id}` | ✅ | Get specific reservation |
| `POST` | `/reservations/{id}/cancel` | ✅ | Cancel reservation |
| `GET` | `/host/reservations` | ✅ | Get host reservations |

---

## 💬 Chat & Messaging Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/chat/start` | ✅ | Start new chat thread |
| `GET` | `/chat/threads` | ✅ | Get user's chat threads |
| `GET` | `/chat/thread/{id}` | ✅ | Get specific thread |
| `POST` | `/chat/thread/message` | ✅ | Send message to thread |
| `GET` | `/chat/thread/{threadId}/messages` | ✅ | Get thread messages |
| `POST` | `/chat/typing-status` | ✅ | Update typing status |
| `POST` | `/user/presence` | ✅ | Update user presence |
| `POST` | `/chat/send-pre-approval` | ✅ | Send pre-approval (host) |
| `POST` | `/chat/send-special-offer` | ✅ | Send special offer (host) |
| `GET` | `/chat/saved-replies` | ✅ | Get saved replies |
| `POST` | `/chat/saved-replies` | ✅ | Add saved reply |

---

## ⭐ Reviews & Ratings Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/reviews` | ✅ | Get property reviews |
| `POST` | `/create-review` | ✅ | Create property review |
| `POST` | `/update-review` | ✅ | Update property review |

---

## ❤️ Favorites & Wishlist Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/favorites` | ✅ | Get user favorites |
| `GET` | `/favorites/user` | ✅ | Get user's favorite properties |
| `POST` | `/favorites` | ✅ | Add to favorites |
| `DELETE` | `/favorites` | ✅ | Remove from favorites |
| `POST` | `/wishlists` | ❌ | Create wishlist |
| `DELETE` | `/wishlists/{wishlistId}` | ❌ | Delete wishlist |
| `GET` | `/my-wishlists` | ✅ | Get user's wishlists |
| `POST` | `/wishlists/set-default` | ✅ | Set default wishlist |

---

## 💳 Payment & Stripe Endpoints

### Payment Management
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/payments/checkout` | ✅ | Process payment checkout |
| `GET` | `/payments/status` | ✅ | Get payment status |
| `GET` | `/payments/payout-options` | ✅ | Get payout options |
| `POST` | `/payments/create-payout-options` | ✅ | Create payout option |
| `POST` | `/payments/update-payout-options/{id}` | ✅ | Update payout option |
| `GET` | `/payments/user-payment-details` | ✅ | Get user payment details |
| `POST` | `/payments/user-payment-details` | ✅ | Add user payment details |
| `POST` | `/payments/user-payment-details/edit/{id}` | ✅ | Edit user payment details |

### Stripe Integration
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/stripe/authenticate` | ✅ | Stripe authentication |
| `POST` | `/stripe/account` | ✅ | Create connected account |
| `GET` | `/stripe/account/{accountId}` | ✅ | Get connected account |
| `POST` | `/stripe/payout` | ✅ | Create payout |
| `GET` | `/stripe/payout/{payoutId}` | ✅ | Get payout details |
| `GET` | `/stripe/payouts` | ✅ | Get all payouts |
| `POST` | `/stripe/payout/cancel/{payoutId}` | ✅ | Cancel payout |
| `POST` | `/stripe/payout/reverse/{payoutId}` | ✅ | Reverse payout |
| `POST` | `/stripe/payment-intent/create` | ✅ | Create payment intent |
| `POST` | `/stripe/payment-intent/update/{id}` | ✅ | Update payment intent |
| `GET` | `/stripe/payment-intent/retrieve/{id}` | ✅ | Get payment intent |
| `GET` | `/stripe/payment-intents` | ✅ | Get all payment intents |
| `POST` | `/stripe/payment-intent/cancel/{id}` | ✅ | Cancel payment intent |
| `POST` | `/stripe/payment-intent/confirm/{id}` | ✅ | Confirm payment intent |
| `POST` | `/stripe/refund/create` | ✅ | Create refund |
| `GET` | `/stripe/refund/retrieve/{id}` | ✅ | Get refund details |
| `GET` | `/stripe/refunds` | ✅ | Get all refunds |
| `POST` | `/stripe/webhook` | ❌ | Stripe webhook handler |

---

## 🏢 Host Management Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/host-info` | ✅ | Get host information |

---

## 👥 Co-Host Management Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/co-hosts/whitelist` | ✅ | Add property to co-host whitelist |
| `POST` | `/co-hosts/signup` | ✅ | Sign up as co-host |
| `GET` | `/co-hosts/properties` | ✅ | Get co-host managed properties |
| `GET` | `/co-hosts/members` | ✅ | Get host's co-host members |

---

## 🆘 Support Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/support/tickets` | ✅ | Submit support ticket |
| `GET` | `/support/tickets` | ✅ | Get user's support tickets |
| `GET` | `/support/ticket/{ticketId}` | ✅ | Get ticket details |
| `POST` | `/support/ticket/{ticketId}/responses` | ✅ | Add ticket response |
| `GET` | `/support/faqs` | ✅ | Get FAQs |

---

## 🔧 System & Configuration Endpoints

### Categories & Types
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/categories/add` | ❌ | Add category |
| `GET` | `/categories` | ❌ | Get all categories |
| `GET` | `/property-types` | ❌ | Get property types |
| `POST` | `/property-types` | ✅ | Create property type |

### System Data
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/system/amenities` | ❌ | Get system amenities |
| `GET` | `/system/favorites` | ❌ | Get system favorites |
| `GET` | `/system/place-items` | ❌ | Get system place items |
| `GET` | `/system/property-types` | ❌ | Get system property types |
| `POST` | `/system/amenities` | ✅ | Create system amenity |
| `POST` | `/system/favorites` | ✅ | Create system favorite |
| `POST` | `/system/property-types` | ✅ | Create system property type |

### Languages & Currencies
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/supported-lang` | ❌ | Get supported languages |
| `POST` | `/supported-lang` | ✅ | Add supported language |
| `POST` | `/set-user-default-supported-lang` | ✅ | Set user default language |
| `GET` | `/translations` | ✅ | Get translations |
| `GET` | `/get-supported-currencies` | ❌ | Get supported currencies |
| `POST` | `/set-user-currency` | ✅ | Set user currency |

### Locations & Cosmetics
| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `GET` | `/locations` | ✅ | Get locations |
| `POST` | `/location` | ✅ | Create location |
| `GET` | `/icons` | ✅ | Get icons |
| `POST` | `/icons` | ✅ | Create icon |

---

## 🔔 Notification Endpoints

| Method | Endpoint | Auth Required | Description |
|--------|----------|---------------|-------------|
| `POST` | `/create-notification` | ✅ | Create user notification |
| `GET` | `/fetch-user-notifications/{userId}` | ✅ | Get user notifications |
| `DELETE` | `/delete-user-notification/{userId}/{notificationId}` | ✅ | Delete specific notification |
| `DELETE` | `/delete-user-all-notifications/{userId}` | ✅ | Delete all user notifications |

---

## 📱 Social Authentication Endpoints

| Method | Endpoint | Auth Required | Rate Limit | Description |
|--------|----------|---------------|------------|-------------|
| `POST` | `/facebook/signin` | ❌ | - | Facebook OAuth sign-in |

---

## 📊 Quick Stats

- **Total Endpoints**: 100+
- **Authentication Required**: 70+
- **Public Endpoints**: 30+
- **Rate Limited**: 1 (Google sign-in)
- **HTTP Methods Used**: GET, POST, PUT, DELETE

---

## 🔑 Authentication Legend

- ✅ **Auth Required**: Must include `Authorization: Bearer {jwt_token}` header
- ❌ **Public**: No authentication required
- **Rate Limit**: Requests per minute limit

---

## 📝 Usage Notes

1. **Base URL**: All endpoints are prefixed with `/api/v1`
2. **Content-Type**: Use `application/json` for JSON requests
3. **File Uploads**: Use `multipart/form-data` for file uploads
4. **Error Handling**: All endpoints return consistent error format
5. **Pagination**: List endpoints support `page` and `limit` parameters
6. **Filtering**: Many GET endpoints support query parameters for filtering

---

**Last Updated**: January 24, 2025  
**API Version**: v1  
**Total Endpoints**: 100+