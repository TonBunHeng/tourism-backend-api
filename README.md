# 🏛️ Smart Tourism Backend API

A comprehensive, production-ready RESTful API backend for a **Smart Tourism Information & Management System**, built with Laravel, MySQL, and Laravel Sanctum.

---

## 📋 Table of Contents
- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Key Features & Modules](#key-features--modules)
- [Database Architecture](#database-architecture)
- [API Endpoints Reference](#api-endpoints-reference)
- [Standard API Response Format](#standard-api-response-format)
- [Installation & Setup](#installation--setup)
- [Default Seeded Accounts](#default-seeded-accounts)
- [Project Directory Structure](#project-directory-structure)

---

## 🌟 Overview

The **Smart Tourism Backend API** powers tourism mobile apps, web portals, and administrative dashboards. It enables tourists to discover destinations, explore upcoming cultural events, read and post verified reviews, curate personal itineraries/favorites, interact via support chat, and track gamified achievements, while providing administrators with rich management tools and statistics.

---

## 🛠️ Tech Stack

- **Framework:** Laravel 11.x / 12.x (PHP 8.2+)
- **Database:** MySQL / MariaDB (Database migrations + Seeders included)
- **Authentication:** Laravel Sanctum (Token-based Bearer Authentication)
- **Architecture:** Controller-Service-Model REST API pattern with standard ApiResponse traits

---

## ✨ Key Features & Modules

1. **Authentication & User Management**
   - User registration & login with secure password hashing (`bcrypt`).
   - Token-based API access via Laravel Sanctum.
   - Profile management, avatar upload support, two-factor status, role-based access (`Super Admin`, `Guide / Editor`, `Tourist`).

2. **Provinces & Locations**
   - Multi-language province directory (Khmer & English names).
   - Coordinates (latitude/longitude), capital city, area, population, and cover images.

3. **Categories & Tagging**
   - Hierarchical categorisation for destinations and attractions (e.g., Temples, Nature & Eco-tourism, Beaches, Cultural Heritage).

4. **Places & Attractions Directory**
   - Full destination information: descriptions, opening hours, entrance fees, contact details, coordinates, ratings, highlights, and tags.

5. **Events & Cultural Festivities**
   - Upcoming and recurring events, date ranges, ticketing status, venue details, and organizer info.

6. **Interactive Reviews & Rating System**
   - 1-to-5 star rating breakdown (overall, cleanliness, value, accessibility, hospitality).
   - Multi-image attachments per review and official reply threads.

7. **Wishlist & Favorites**
   - Bookmark favorite places with "visited" status toggling and personalized visit notes.

8. **Rich Media Gallery**
   - High-resolution tourism photo & video gallery with tagging, view counters, like counts, and licensing metadata.

9. **Live Support & Inquiry Chat**
   - Real-time tourist support conversations, support tickets, and threaded message history.

10. **Data Privacy & Deletion Requests**
    - Compliance/GDPR data management allowing users to request account and data removal with admin approval workflows.

11. **Gamification & Achievements**
    - Badges and travel achievements (e.g., "Angkor Explorer", "Eco Traveler") with unlock tracking and points.

12. **System Settings & App Configuration**
    - Dynamic application settings (maintenance mode, emergency helpline numbers, app versions, terms & policies).

13. **Analytics & Dashboard**
    - Aggregate statistics for admin dashboards (total destinations, reviews, users, events, and monthly metrics).

---

## 🗄️ Database Architecture

The database contains 21 migration tables organized as follows:

| Table | Description |
|---|---|
| `users` | User accounts, roles, subscription tier, verification status |
| `personal_access_tokens` | Sanctum API tokens |
| `provinces` | Geographic province & regional data |
| `categories` | Place and attraction classification categories |
| `places` | Tourist destinations, coordinates, prices, ratings |
| `events` | Festivals, cultural events, exhibitions |
| `event_tags` | Tagging system for events |
| `reviews` | Tourist reviews, sub-ratings, visit dates |
| `review_replies` | Official replies to reviews by guides/administrators |
| `review_images` | Photos uploaded with reviews |
| `favorites` | User saved bookmarks and visited statuses |
| `gallery_media` | Media gallery (images/videos) with metadata |
| `gallery_media_tags` | Tag mappings for gallery media |
| `chats` | Tourist support and inquiry conversation threads |
| `chat_messages` | Individual messages within chat threads |
| `deletion_requests` | GDPR & user account/data deletion requests |
| `deletion_request_items` | Specific sub-items requested for deletion |
| `user_achievements` | Gamified milestones and tourist badges |
| `system_settings` | Key-value application configurations |
| `cache` / `jobs` | Laravel system cache and background queue jobs |

---

## 🚀 API Endpoints Reference

### 1. System & Authentication
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/health` | Healthcheck and service status | No |
| `POST` | `/api/auth/register` | Register new user account | No |
| `POST` | `/api/auth/login` | Login and receive Bearer token | No |
| `GET` | `/api/auth/me` | Get current authenticated user | Yes (`auth:sanctum`) |
| `PUT` | `/api/auth/profile` | Update profile information | Yes (`auth:sanctum`) |
| `POST` | `/api/auth/logout` | Revoke current API token | Yes (`auth:sanctum`) |

### 2. Places & Attractions
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/places` | List places (filter by category, province, query) | No |
| `GET` | `/api/places/{id}` | View single place with gallery & reviews | No |
| `POST` | `/api/places` | Create a new destination | Yes |
| `PUT` | `/api/places/{id}` | Update destination details | Yes |
| `DELETE` | `/api/places/{id}` | Delete a destination | Yes |

### 3. Provinces & Categories
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/provinces` | List all provinces | No |
| `GET` | `/api/provinces/{id}` | View province details & places | No |
| `POST` | `/api/provinces` | Create province entry | Yes |
| `GET` | `/api/categories` | List all tourism categories | No |
| `GET` | `/api/categories/{id}` | View category and associated places | No |
| `POST` | `/api/categories` | Create category | Yes |

### 4. Events & Festivities
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/events` | List upcoming and past events | No |
| `GET` | `/api/events/{id}` | Event details with tags | No |
| `POST` | `/api/events` | Create new event | Yes |
| `PUT` | `/api/events/{id}` | Update event | Yes |
| `DELETE` | `/api/events/{id}` | Delete event | Yes |

### 5. Reviews & Ratings
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/reviews` | List reviews (filterable by place) | No |
| `GET` | `/api/reviews/{id}` | Single review with images & replies | No |
| `POST` | `/api/reviews` | Post a new review with ratings | Yes |
| `PUT` | `/api/reviews/{id}` | Edit an existing review | Yes |
| `DELETE` | `/api/reviews/{id}` | Delete a review | Yes |
| `POST` | `/api/reviews/{id}/replies` | Add official response to review | Yes |

### 6. User Favorites / Wishlist
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/favorites` | List authenticated user's favorites | Yes |
| `POST` | `/api/favorites` | Add place to favorites | Yes |
| `DELETE` | `/api/favorites/{placeId}` | Remove place from favorites | Yes |
| `PATCH` | `/api/favorites/{id}/toggle-visited`| Toggle visited status | Yes |

### 7. Media Gallery
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/galleries` | Explore media gallery items | No |
| `GET` | `/api/galleries/{id}` | Get single media item | No |
| `POST` | `/api/galleries` | Upload/register media | Yes |
| `PUT` | `/api/galleries/{id}` | Update media metadata | Yes |
| `DELETE` | `/api/galleries/{id}` | Remove media item | Yes |

### 8. Support Chats & Inquiries
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/chats` | List user/admin chat conversations | Yes |
| `POST` | `/api/chats` | Start a new support thread | Yes |
| `GET` | `/api/chats/{id}` | Get messages in conversation | Yes |
| `POST` | `/api/chats/{id}/messages` | Send message in conversation | Yes |
| `PUT` | `/api/chats/{id}/status` | Update chat status (`Open`, `Resolved`) | Yes |

### 9. Gamification, Deletion Requests & Settings
| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/achievements` | List all available badges | Yes |
| `GET` | `/api/users/{userId}/achievements`| List achievements unlocked by user | Yes |
| `GET` | `/api/deletion-requests` | List user data deletion requests | Yes |
| `POST` | `/api/deletion-requests` | Submit account/data deletion request | Yes |
| `GET` | `/api/settings` | Get system configurations & policies | Yes |
| `GET` | `/api/dashboard/stats` | Aggregate dashboard metrics | No |

---

## 📦 Standard API Response Format

All endpoints follow a uniform JSON response structure via `App\Traits\ApiResponse`:

### Success Response
```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {
    ...
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation error or resource not found.",
  "errors": {
    "field": ["Detailed error message"]
  }
}
```

---

## ⚙️ Installation & Setup

### 1. Prerequisites
- PHP >= 8.2
- Composer
- MySQL >= 8.0 or MariaDB
- Required PHP Extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`

### 2. Clone and Install Dependencies
```bash
git clone https://github.com/TonBunHeng/tourism-backend-api.git
cd tourism-backend-api

composer install
```

### 3. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` to configure your database connection:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tourism_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations & Seed Sample Data
```bash
php artisan migrate:fresh --seed
```

### 5. Start Local Development Server
```bash
php artisan serve
```
The API is now running at `http://127.0.0.1:8000`.

---

## 🔑 Default Seeded Accounts

After running `php artisan db:seed`, the following sample users are pre-configured:

| Role | Email | Password | Status |
|---|---|---|---|
| **Super Admin** | `admin@tourism.gov.kh` | `password123` | Active / Verified |
| **Guide / Editor** | `sokha@tourism.gov.kh` | `password123` | Active / Verified |

---

## 📁 Project Directory Structure

```text
tourism-backend-api/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/            # REST API Controllers
│   ├── Models/                 # 18 Eloquent Models
│   └── Traits/
│       └── ApiResponse.php     # Standard API Response Trait
├── database/
│   ├── migrations/             # 21 Migration files
│   └── seeders/
│       └── DatabaseSeeder.php  # Comprehensive dummy data seeder
├── routes/
│   └── api.php                 # All RESTful API route definitions
└── tests/                      # Feature and Unit tests
```

---

## 📄 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
