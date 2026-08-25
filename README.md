# 🏛️ AngkorVerses Backend API

A comprehensive, production-ready central RESTful API backend for the **AngkorVerses System**, built with Laravel, MySQL, and Laravel Sanctum.

This unified backend powers both the **Admin Management Web (`tourism-frontend`)** and the **Travel/User Client Applications (`tourism-travel` Tourist Web and the Tourist Android Mobile App)**.

---

## 🏛️ Architecture Overview

```text
                               Laravel Backend API
                               tourism-backend-api
                                        │
                 ┌──────────────────────┴──────────────────────┐
                 │                                             │
                 ▼                                             ▼
            Admin API                                      Travel API
           `/api/*`                                     `/api/travel/*`
                 │                                             │
                 ▼                                     ┌───────┴───────┐
          tourism-frontend                             │               │
           Admin Web App                               ▼               ▼
                                                tourism-travel     Android App
                                                  Tourist Web    Tourist Mobile
```

---

## 📋 Table of Contents
- [Tech Stack](#-tech-stack)
- [Roles & Permissions](#-roles--permissions)
- [Travel / User API Reference (`/api/travel/*`)](#-travel--user-api-reference-apitravel)
  - [1. Authentication](#1-authentication)
  - [2. Tourist Destinations (Places)](#2-tourist-destinations-places)
  - [3. Provinces & Locations](#3-provinces--locations)
  - [4. Categories](#4-categories)
  - [5. Events & Festivals](#5-events--festivals)
  - [6. Media Gallery](#6-media-gallery)
  - [7. Reviews & Ratings](#7-reviews--ratings)
  - [8. Favorites & Wishlist](#8-favorites--wishlist)
  - [9. Gamification & Achievements](#9-gamification--achievements)
  - [10. Live Support Chat](#10-live-support-chat)
  - [11. Privacy & Deletion Requests](#11-privacy--deletion-requests)
  - [12. Public App Settings](#12-public-app-settings)
  - [13. AI Assistant & Tourism Intelligence](#13-ai-assistant--tourism-intelligence)
- [Admin Management API Reference (`/api/*`)](#-admin-management-api-reference-api)
- [Standard API Response Format](#-standard-api-response-format)
- [Installation & Setup](#-installation--setup)
- [Testing](#-testing)
- [Default Seeded Accounts](#-default-seeded-accounts)
- [Project Directory Structure](#-project-directory-structure)

---

## 🛠️ Tech Stack

- **Framework:** Laravel 11.x / 12.x (PHP 8.2+)
- **Database:** MySQL 8.0+ / SQLite for testing
- **Authentication:** Laravel Sanctum (Bearer Tokens)
- **OAuth Providers:** Google OAuth 2.0 & Facebook Graph API
- **Architecture:** Form Requests, API Resources, Role Middleware (`admin.role`), Standard `ApiResponse` Trait

---

## 👥 Roles & Permissions

| Role | Target Client | Capabilities |
|---|---|---|
| `User` | Tourist Web & Android Mobile App | Browse destinations, post reviews, save wishlist, live support chat, submit deletion requests |
| `Guide / Editor` | Admin Portal | Manage destination content, event schedules, media gallery |
| `Admin` | Admin Portal | Review moderation, analytics, user management, support moderation |
| `Super Admin` | Admin Portal | Full administrative access, system configuration, user deletion approval |

> **Security Rule:** Normal `User` role accounts are strictly blocked (`HTTP 403 Forbidden`) from accessing Admin management endpoints.

---

## 🚀 Travel / User API Reference (`/api/travel/*`)

All Travel endpoints share the `/api/travel` prefix and are consumed identically by **`tourism-travel`** and the **Android Mobile App**.

### 1. Authentication

#### Register Tourist Account
- **Endpoint:** `POST /api/travel/auth/register`
- **Auth:** Public
- **Request Body:**
  ```json
  {
    "name": "Sophea Traveler",
    "email": "sophea@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+855 12 345 678",
    "location": "Phnom Penh",
    "bio": "Passionate travel photographer."
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Registration successful.",
    "data": {
      "user": {
        "id": 1,
        "name": "Sophea Traveler",
        "email": "sophea@example.com",
        "phone": "+855 12 345 678",
        "role": "User",
        "status": "Active",
        "location": "Phnom Penh",
        "bio": "Passionate travel photographer.",
        "verified": false
      },
      "token": "1|sanctum_token_string...",
      "token_type": "Bearer"
    }
  }
  ```

#### Email & Password Login
- **Endpoint:** `POST /api/travel/auth/login`
- **Auth:** Public
- **Request Body:**
  ```json
  {
    "email": "sophea@example.com",
    "password": "password123"
  }
  ```

#### Google OAuth Login
- **Endpoint:** `POST /api/travel/auth/google`
- **Auth:** Public
- **Request Body:**
  ```json
  {
    "id_token": "google_oauth_id_token...",
    "email": "sophea.traveler@gmail.com",
    "name": "Sophea Traveler",
    "avatar": "https://lh3.googleusercontent.com/..."
  }
  ```

#### Facebook OAuth Login
- **Endpoint:** `POST /api/travel/auth/facebook`
- **Auth:** Public
- **Request Body:**
  ```json
  {
    "access_token": "facebook_access_token...",
    "facebook_id": "1234567890",
    "name": "Sophea Traveler"
  }
  ```

#### Get Current Authenticated Tourist
- **Endpoint:** `GET /api/travel/auth/me`
- **Auth:** `Bearer Token` (`auth:sanctum`)

#### Update Profile
- **Endpoint:** `PUT /api/travel/auth/profile`
- **Auth:** `Bearer Token`
- **Request Body:**
  ```json
  {
    "name": "Sophea Traveler Updated",
    "phone": "+855 88 999 000",
    "location": "Siem Reap",
    "bio": "Heritage enthusiast exploring Angkor."
  }
  ```

#### Change Password
- **Endpoint:** `PUT /api/travel/auth/password`
- **Auth:** `Bearer Token`
- **Request Body:**
  ```json
  {
    "current_password": "password123",
    "password": "newpassword456",
    "password_confirmation": "newpassword456"
  }
  ```

#### Upload Avatar
- **Endpoint:** `POST /api/travel/auth/avatar`
- **Auth:** `Bearer Token`
- **Content-Type:** `multipart/form-data`
- **Body:** `avatar` (Image file: jpeg, png, jpg, webp - Max 5MB)

#### Logout
- **Endpoint:** `POST /api/travel/auth/logout`
- **Auth:** `Bearer Token`

---

### 2. Tourist Destinations (Places)

#### Browse & Search Destinations
- **Endpoint:** `GET /api/travel/places`
- **Auth:** Public
- **Query Parameters:**
  - `search`: Search name, description, address in English & Khmer
  - `province_id` / `province`: Filter by province
  - `category_id` / `category`: Filter by category
  - `min_rating` / `rating`: Filter by minimum rating (e.g., `4.5`)
  - `price`: Filter by price tier (`Free`, `$5`, etc.)
  - `featured`: Filter featured (`true`/`false`)
  - `sort_by`: `popular`, `rating`, `reviews`, `name`, `newest`
  - `per_page`: Number of results (default `12`)
  - `page`: Page number

#### Destination Details
- **Endpoint:** `GET /api/travel/places/{id}`
- **Auth:** Public
- **Returns:** Full destination details with category, province, opening hours, coordinates, entrance fee, approved reviews, and media gallery.

---

### 3. Provinces & Locations

- `GET /api/travel/provinces`: List all active Cambodian provinces with attraction and event counts.
- `GET /api/travel/provinces/{id}`: Detailed view of a province with associated tourist destinations and cultural events.

---

### 4. Categories

- `GET /api/travel/categories`: List tourism categories (Temples, Eco-Tourism, Beaches, Cultural Heritage, Cuisine).
- `GET /api/travel/categories/{id}`: Category details with associated attractions.

---

### 5. Events & Festivals

- `GET /api/travel/events`: List events filterable by `status` (`Upcoming`, `Ongoing`, `Past`), `province_id`, `category`, `search`, and date range.
- `GET /api/travel/events/{id}`: Event details with venue, organizer, dates, and tags.

---

### 6. Media Gallery

- `GET /api/travel/galleries`: Browse photo & video galleries (filterable by `place_id`, `media_type` [`image`, `video`], `tag`, `search`).
- `GET /api/travel/galleries/{id}`: View single gallery media item with view counter.

---

### 7. Reviews & Ratings

- `GET /api/travel/reviews`: List approved destination reviews (filter by `place_id`, `rating`).
- `GET /api/travel/reviews/{id}`: View review details with attached images and guide replies.
- `POST /api/travel/reviews` (`Bearer Token`): Post a review for a destination.
  ```json
  {
    "place_id": 1,
    "rating": 5,
    "cleanliness": 5,
    "value": 4,
    "accessibility": 5,
    "hospitality": 5,
    "title": "Breathtaking experience at sunrise",
    "comment": "Angkor Wat at dawn is unforgettable. Highly recommend hiring an official guide.",
    "images": ["https://example.com/photo1.jpg"]
  }
  ```
- `PUT /api/travel/reviews/{id}` (`Bearer Token`): Edit own review.
- `DELETE /api/travel/reviews/{id}` (`Bearer Token`): Delete own review.

---

### 8. Favorites & Wishlist

- `GET /api/travel/favorites` (`Bearer Token`): Retrieve user's saved wishlist destinations.
- `POST /api/travel/favorites` (`Bearer Token`):
  ```json
  {
    "place_id": 1,
    "visited": false
  }
  ```
- `DELETE /api/travel/favorites/{placeId}` (`Bearer Token`): Remove destination from wishlist.
- `PATCH /api/travel/favorites/{id}/toggle-visited` (`Bearer Token`): Toggle visited status.

---

### 9. Gamification & Achievements

- `GET /api/travel/achievements`: List available badges (*Angkor Explorer*, *Eco Traveler*, *Cultural Enthusiast*, etc.).
- `GET /api/travel/achievements/my` (`Bearer Token`): Retrieve authenticated user's unlocked badges and progress.

---

### 10. Live Support Chat

- `GET /api/travel/chats` (`Bearer Token`): List user's support conversations.
- `POST /api/travel/chats` (`Bearer Token`): Start a new conversation.
  ```json
  {
    "category": "Angkor Pass Inquiry",
    "priority": "medium",
    "message": "Can I purchase the Angkor Pass on mobile with QR payment?"
  }
  ```
- `GET /api/travel/chats/{id}` (`Bearer Token`): Get message history for conversation.
- `POST /api/travel/chats/{id}/messages` (`Bearer Token`): Send message in conversation.
  ```json
  {
    "message": "Thank you for the quick assistance!"
  }
  ```

---

### 11. Privacy & Deletion Requests

- `GET /api/travel/deletion-requests` (`Bearer Token`): View status of submitted deletion requests.
- `POST /api/travel/deletion-requests` (`Bearer Token`): Submit account or item deletion request for admin review.
  ```json
  {
    "request_type": "account",
    "reason": "I have completed my journey and would like to close my account."
  }
  ```

---

### 12. Public App Settings

- `GET /api/travel/settings`: Public application configuration, emergency contacts (Tourist Police, Ambulance, Fire), support emails, and privacy URLs.

---

### 13. AI Assistant & Tourism Intelligence

Powered by the **Angkor Verse AI Microservice** (`https://aichat-backend-pi.vercel.app/`).

#### 🤖 AI Tourism Chat Assistant
- **Endpoint:** `POST /api/travel/ai/chat` (or `POST /api/travel/ai-chat`)
- **Auth:** Public / Bearer Token
- **Request Body:**
  ```json
  {
    "message": "What are the best temples to visit for sunrise in Siem Reap?",
    "session_id": "optional_session_id",
    "province": "Siem Reap",
    "category": "Temples & Heritage",
    "language": "en"
  }
  ```
- **Returns:** Intelligent response powered by Gemini AI with verified Cambodian landmarks, local practical tips, and suggested follow-up questions.

#### 🌟 Smart Recommendations
- **Endpoint:** `POST /api/travel/ai/recommendations` (or `POST /api/travel/recommendations`)
- **Request Body:**
  ```json
  {
    "province": "Siem Reap",
    "category": "Historical",
    "budget": "moderate",
    "travel_style": "cultural",
    "interests": ["temples", "history", "photography"]
  }
  ```

#### 🗓️ Day-by-Day Smart Itinerary Generator
- **Endpoint:** `POST /api/travel/ai/itineraries` (or `POST /api/travel/itineraries`)
- **Request Body:**
  ```json
  {
    "province": "Siem Reap",
    "days": 3,
    "budget": "moderate",
    "travel_style": "cultural"
  }
  ```

#### ☀️ Live Weather & Travel Suitability Advice
- **Endpoint:** `GET /api/travel/ai/weather?province=Siem+Reap&days=3`
- **Returns:** Real-time temperature, precipitation risk, forecast, and tailored travel advice in both English and Khmer.

#### 💱 Currency Reference & Converter
- **Get Rate:** `GET /api/travel/ai/currency`
- **Convert:** `POST /api/travel/ai/currency/convert`
  ```json
  {
    "amount": 25,
    "from_currency": "USD",
    "to_currency": "KHR"
  }
  ```

#### 🚗 Transit & Transport Estimator
- **Endpoint:** `GET /api/travel/ai/transport?origin=Phnom+Penh&destination=Siem+Reap&travelers=2`

#### 🩺 AI System Status
- **Endpoint:** `GET /api/travel/ai/status` (or `GET /api/ai/status`)

---

## 🔒 Admin Management API Reference (`/api/*`)

| Method | Endpoint | Description | Guard / Role |
|---|---|---|---|
| `GET` | `/api/dashboard` | Dashboard statistical summary | `admin.role` |
| `GET` | `/api/users` | List and search all users | `admin.role` |
| `POST` | `/api/users` | Create user with assigned role | `admin.role` |
| `PUT` | `/api/users/{id}/status` | Update user status (`Active`/`Suspended`) | `admin.role` |
| `POST` | `/api/places` | Create new tourist destination | `admin.role` |
| `PUT` | `/api/places/{id}` | Update destination details | `admin.role` |
| `DELETE` | `/api/places/{id}` | Delete destination | `admin.role` |
| `POST` | `/api/events` | Create event schedule | `admin.role` |
| `PUT` | `/api/reviews/{id}/status` | Moderate review (`Approved`/`Rejected`) | `admin.role` |
| `PUT` | `/api/deletion-requests/{id}/status` | Process account deletion request | `admin.role` |
| `PUT` | `/api/settings` | Update system-wide configurations | `admin.role` |

---

## 📦 Standard API Response Format

```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "meta": {
    "total": 50,
    "per_page": 12,
    "current_page": 1,
    "last_page": 5
  }
}
```

Error response:
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## ⚙️ Installation & Setup

### Prerequisites
- **PHP**: ^8.2 or ^8.3 with `pdo`, `mbstring`, `openssl`, `bcmath`, `curl` extensions enabled
- **Composer**: ^2.0+
- **Database**: SQLite (default for development) or MySQL ^8.0 / PostgreSQL

### Step-by-Step Backend Setup

```bash
# 1. Clone the repository
git clone https://github.com/TonBunHeng/tourism-backend-api.git
cd tourism-backend-api

# 2. Install PHP dependencies
composer install

# 3. Create environment file & generate application key
cp .env.example .env
php artisan key:generate

# 4. Create SQLite database file (if using default SQLite connection)
touch database/database.sqlite

# 5. Run database migrations & seed default data (admin user, categories, places, events)
php artisan migrate --seed

# Note: If resetting an existing database and re-seeding default accounts:
php artisan migrate:fresh --seed

# 6. Start the local backend development server
# For local machine only (http://127.0.0.1:8000):
php artisan serve

# For Local LAN access (accessible by mobile devices & other computers using your IP):
php artisan serve --host=0.0.0.0 --port=8000
# or:
composer run serve
```

> **Note:** Serving with `--host=0.0.0.0` allows both **Admin Web App (`tourism-frontend`)** and **Travel Client Apps (`tourism-travel` / Mobile App)** on your Local LAN IP (e.g. `http://192.168.x.x:8000`) to connect with identical data and CORS permissions.

---

## 🧪 Testing

Run the automated PHPUnit test suite covering authentication, destinations, reviews, favorites, support chats, deletion requests, and role authorization:

```bash
php artisan test
```

---

## 🔑 Default Seeded Accounts

| Name | Role | Email | Password |
|---|---|---|---|
| **Ton Bunheng** | **Super Admin** | `admin@tourism.gov.kh` | `password123` |
| **Sophal Sopheaktra** | **Guide / Editor** | `sopheaktra@tourism.gov.kh` | `password123` |
| **VIT Vong** | **Tourist (User)** | `vit.vong@example.com` | `password123` |
| **Ou Sreylin** | **Tourist (User)** | `ou.sreylin@example.com` | `password123` |

---

## 📄 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
