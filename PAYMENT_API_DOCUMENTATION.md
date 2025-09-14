# Payment API Documentation

This document provides comprehensive documentation for the enhanced payment-related APIs in the Flapabay backend system.

## Overview

The payment system has been enhanced with the following new features:
- Balance and earnings tracking
- Payout methods management
- Withdrawal/payout requests with OTP verification
- Comprehensive payment history with filtering and export

## Database Changes

### New Tables

#### `withdrawals` Table
- `id` - Primary key
- `user_id` - Foreign key to users table
- `payment_method_id` - Foreign key to payment_methods table
- `amount` - Withdrawal amount (decimal)
- `currency` - Currency code
- `status` - Withdrawal status (pending, processing, completed, failed, cancelled)
- `reference_id` - External reference ID
- `notes` - Additional notes
- `metadata` - JSON metadata
- `requested_at` - When withdrawal was requested
- `processed_at` - When withdrawal was processed
- `completed_at` - When withdrawal was completed
- `created_at`, `updated_at` - Timestamps

### Enhanced Tables

#### `users` Table (New Fields)
- `balance` - Current user balance (decimal)
- `pending_earnings` - Earnings not yet available for withdrawal (decimal)
- `total_earnings` - Lifetime total earnings (decimal)
- `total_withdrawn` - Total amount withdrawn (decimal)
- `last_payout_at` - Last payout timestamp

## API Endpoints

### Balance and Earnings APIs

#### Get Current Balance
```
GET /api/v1/earnings/balance
```

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "current_balance": "1250.75",
    "pending_earnings": "320.50",
    "available_for_withdrawal": "1250.75",
    "currency": "USD",
    "last_updated": "2025-01-15T10:30:00Z"
  }
}
```

#### Get Pending Earnings
```
GET /api/v1/earnings/pending
```

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "pending_earnings": "320.50",
    "currency": "USD",
    "pending_bookings": [
      {
        "booking_id": 123,
        "guest_name": "John Doe",
        "listing_title": "Cozy Apartment",
        "amount": "150.25",
        "expected_release_date": "2025-01-20T00:00:00Z"
      }
    ]
  }
}
```

#### Get Total Earnings Summary
```
GET /api/v1/earnings/total
```

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "total_earnings": "5420.30",
    "total_withdrawn": "4169.55",
    "current_balance": "1250.75",
    "currency": "USD",
    "earnings_this_year": "2100.50",
    "earnings_this_month": "450.75"
  }
}
```

#### Get Monthly Earnings Breakdown
```
GET /api/v1/earnings/monthly
```

**Query Parameters:**
- `year` (optional) - Year to filter by (default: current year)
- `months` (optional) - Number of months to include (default: 12)

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "year": 2025,
    "currency": "USD",
    "monthly_breakdown": [
      {
        "month": "2025-01",
        "month_name": "January",
        "gross_earnings": "530.00",
        "platform_fees": "79.50",
        "net_earnings": "450.50",
        "bookings_count": 8
      }
    ],
    "total_year_earnings": "2100.50"
  }
}
```

### Payout Methods Management APIs

#### Get Supported Payout Methods
```
GET /api/v1/payout-methods/supported
```

**Query Parameters:**
- `country` (optional) - Country code to filter methods

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "supported_methods": [
      {
        "id": "bank_transfer",
        "name": "Bank Transfer",
        "description": "Direct bank account transfer",
        "icon": "bank-icon.svg",
        "processing_time": "1-3 business days",
        "min_amount": "10.00",
        "max_amount": "10000.00",
        "fees": "0.00",
        "supported_countries": ["US", "CA", "GB"],
        "required_fields": [
          {
            "name": "account_number",
            "label": "Account Number",
            "type": "text",
            "required": true
          },
          {
            "name": "routing_number",
            "label": "Routing Number",
            "type": "text",
            "required": true
          }
        ]
      }
    ]
  }
}
```

#### Get User Payout Methods
```
GET /api/v1/payout-methods
```

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "payout_methods": [
      {
        "id": 1,
        "type": "bank_transfer",
        "payment_method": "Bank Transfer",
        "account_number_masked": "****1234",
        "currency": "USD",
        "country_code": "US",
        "is_default": true,
        "created_at": "2025-01-10T10:00:00Z"
      }
    ]
  }
}
```

#### Create Payout Method
```
POST /api/v1/payout-methods
```

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "type": "bank_transfer",
  "payment_method": "Bank Transfer",
  "account_number": "1234567890",
  "routing_number": "021000021",
  "country_code": "US",
  "currency": "USD"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payout method created successfully",
  "data": {
    "id": 2,
    "type": "bank_transfer",
    "payment_method": "Bank Transfer",
    "account_number_masked": "****7890",
    "currency": "USD",
    "country_code": "US"
  }
}
```

#### Update Payout Method
```
PUT /api/v1/payout-methods/{id}
```

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "account_number": "9876543210",
  "routing_number": "021000021"
}
```

#### Delete Payout Method
```
DELETE /api/v1/payout-methods/{id}
```

**Headers:**
- `Authorization: Bearer {token}`

### Withdrawal/Payout Request APIs

#### Request Withdrawal
```
POST /api/v1/withdrawals/request
```

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "payment_method_id": 1,
  "amount": "500.00",
  "currency": "USD"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent to your registered email/phone",
  "data": {
    "withdrawal_id": 123,
    "amount": "500.00",
    "currency": "USD",
    "status": "pending_otp",
    "otp_expires_at": "2025-01-15T10:40:00Z"
  }
}
```

#### Verify Withdrawal OTP
```
POST /api/v1/withdrawals/verify-otp
```

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
  "withdrawal_id": 123,
  "otp": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Withdrawal request submitted successfully",
  "data": {
    "withdrawal_id": 123,
    "amount": "500.00",
    "currency": "USD",
    "status": "pending",
    "reference_id": "WD-00000123",
    "estimated_completion": "2025-01-18T00:00:00Z"
  }
}
```

#### Get Withdrawal History
```
GET /api/v1/withdrawals
```

**Query Parameters:**
- `status` (optional) - Filter by status
- `per_page` (optional) - Items per page (default: 20)
- `page` (optional) - Page number

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "withdrawals": [
      {
        "id": 123,
        "amount": "500.00",
        "currency": "USD",
        "status": "completed",
        "payment_method": "Bank Transfer",
        "account_number_masked": "****1234",
        "reference_id": "WD-00000123",
        "requested_at": "2025-01-15T10:30:00Z",
        "completed_at": "2025-01-18T14:20:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 15,
      "last_page": 1
    }
  }
}
```

#### Cancel Withdrawal
```
POST /api/v1/withdrawals/{id}/cancel
```

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "message": "Withdrawal cancelled successfully",
  "data": {
    "withdrawal_id": 123,
    "status": "cancelled",
    "refunded_amount": "500.00"
  }
}
```

### Payment History APIs

#### Get Payment History
```
GET /api/v1/payment-history
```

**Query Parameters:**
- `type` (optional) - Filter by type: `all`, `earnings`, `payments`, `withdrawals`
- `status` (optional) - Filter by status: `pending`, `completed`, `failed`, `cancelled`
- `date_from` (optional) - Start date (YYYY-MM-DD)
- `date_to` (optional) - End date (YYYY-MM-DD)
- `per_page` (optional) - Items per page (default: 20, max: 100)
- `page` (optional) - Page number

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": "earning_123",
        "type": "earning",
        "description": "Booking payment from John Doe",
        "amount": 127.50,
        "currency": "USD",
        "status": "completed",
        "date": "2025-01-15T10:30:00Z",
        "reference_id": "BK-00000123",
        "details": {
          "listing_title": "Cozy Apartment",
          "guest_name": "John Doe",
          "booking_dates": "2025-01-20 to 2025-01-22"
        }
      },
      {
        "id": "withdrawal_456",
        "type": "withdrawal",
        "description": "Withdrawal to Bank Transfer",
        "amount": -500.00,
        "currency": "USD",
        "status": "completed",
        "date": "2025-01-14T15:20:00Z",
        "reference_id": "WD-00000456",
        "details": {
          "payment_method": "Bank Transfer",
          "account_number": "****1234"
        }
      }
    ],
    "summary": {
      "total_earnings": "2150.75",
      "total_payments": "0.00",
      "total_withdrawals": "1500.00",
      "pending_withdrawals": "0.00",
      "current_balance": "650.75",
      "currency": "USD"
    },
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 45,
      "last_page": 3
    }
  }
}
```

#### Export Payment History
```
GET /api/v1/payment-history/export
```

**Query Parameters:**
- `type` (optional) - Filter by type: `all`, `earnings`, `payments`, `withdrawals`
- `date_from` (optional) - Start date (YYYY-MM-DD)
- `date_to` (optional) - End date (YYYY-MM-DD)
- `format` (optional) - Export format: `csv` (default), `pdf` (future)

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
- CSV file download with payment history data

## Error Responses

All APIs return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

**Common HTTP Status Codes:**
- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Security Features

### OTP Verification
- All withdrawal requests require OTP verification
- OTP is sent to user's registered email/phone
- OTP expires after 10 minutes
- Maximum 3 OTP attempts allowed

### Data Protection
- Account numbers are masked in API responses
- Sensitive payment data is encrypted
- All API endpoints require authentication
- Rate limiting applied to prevent abuse

### Audit Trail
- All financial transactions are logged
- Withdrawal status changes are tracked
- User actions are recorded with timestamps

## Integration Notes

### Platform Fees
- 15% platform fee is automatically deducted from host earnings
- Fees are calculated and displayed in earnings breakdown
- Net earnings are what hosts receive after fees

### Currency Support
- Multi-currency support for international users
- Currency conversion handled at payment processing level
- All amounts returned as formatted strings with 2 decimal places

### Payout Processing
- Withdrawals are processed within 1-3 business days
- Minimum withdrawal amount varies by payment method
- Maximum daily withdrawal limits apply

## Testing

All APIs have been tested and are ready for integration. The system includes:
- Comprehensive validation
- Error handling
- Security measures
- Performance optimization
- Database integrity constraints

For testing purposes, ensure you have:
1. Valid JWT authentication token
2. User with host privileges (for earnings/withdrawal APIs)
3. Proper payment method setup
4. Sufficient balance for withdrawal testing

## Support

For technical support or questions about these APIs, please refer to the main API documentation or contact the development team.