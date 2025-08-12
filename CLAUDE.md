# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
Flapabay Engine is a Laravel-based property booking platform (similar to Airbnb) with Vue.js frontend using Inertia.js. The application includes real-time chat functionality using Laravel Reverb, property management, booking system, payments via Stripe, and user authentication.

## Development Commands

### Backend (Laravel)
- **Start development server**: `composer dev` (runs server, queue, logs, and frontend concurrently)
- **Individual services**:
  - `php artisan serve` - Laravel server
  - `php artisan queue:listen --tries=1` - Queue worker
  - `php artisan pail --timeout=0` - Log viewer
  - `php artisan reverb:start` - WebSocket server for real-time features
- **Database**: `php artisan migrate --seed`
- **Tests**: `php artisan test` or `phpunit`
- **Code formatting**: `php artisan pint` (Laravel Pint)

### Frontend (Vue.js + Vite)
- **Development**: `npm run dev`
- **Build**: `npm run build`

## Architecture Overview

### Backend Structure
- **Framework**: Laravel 11 with PHP 8.2+
- **Authentication**: JWT tokens via tymon/jwt-auth, Laravel Passport, and Sanctum
- **Real-time**: Laravel Reverb for WebSocket connections
- **Payment**: Stripe integration for property bookings
- **SMS**: Infobip and Twilio for OTP verification
- **Social Auth**: Google OAuth integration

### Key Models & Relationships
- **User** → Has UserDetail, can be host/guest
- **Property** → Belongs to host, has amenities, images, reviews
- **Booking/Reservation** → Links guest, property, payment
- **Thread/Message** → Chat system between hosts and guests
- **Payment** → Handles Stripe transactions and payouts

### API Structure
- **Base URL**: `/api/v1/`
- **Chat API**: Thread-based messaging system with real-time updates
- **Property API**: CRUD operations for listings and bookings
- **Auth API**: Multi-step authentication with OTP verification

### Frontend Structure
- **Framework**: Vue.js 3 with Inertia.js for SPA behavior
- **Styling**: Tailwind CSS with custom components
- **State**: Vue composition API
- **Real-time**: Laravel Echo with Pusher/Reverb for WebSocket connections

## Key Features
1. **Property Management**: Hosts can create/manage listings with images, amenities, availability
2. **Booking System**: Guests can search, reserve, and pay for properties
3. **Real-time Chat**: Thread-based messaging between hosts and guests
4. **Payment Processing**: Stripe integration for bookings and host payouts
5. **Multi-auth**: Email/phone OTP, social login, JWT tokens
6. **Reviews & Ratings**: Bidirectional reviews between hosts and guests

## Environment Setup
- Copy `.env.example` to `.env`
- Configure database, Stripe keys, broadcasting (Reverb), SMS providers
- Run `php artisan key:generate`
- Set `BROADCAST_CONNECTION=reverb` for real-time features

## Testing
- Feature tests cover authentication, chat, and booking flows
- Run tests with `php artisan test`
- Test real-time features by starting Reverb server and using `/test-reverb` endpoint

## Important Notes
- The project uses thread-based chat system (not direct messaging)
- All API endpoints require authentication
- Properties support dynamic categories and filtering
- Real-time features require Reverb WebSocket server to be running
- Payment flow integrates with Stripe webhooks for transaction updates