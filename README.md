# Music Catalogue REST API

A RESTful Music Catalogue API built with Laravel and Laravel Sanctum. The API allows authenticated users to manage their own music tracks while preventing users from accessing or modifying tracks belonging to other users.

## Features

* User registration and login
* Token-based authentication using Laravel Sanctum
* Authenticated user profile endpoint
* Logout and token revocation
* Create, view, update, and delete tracks
* Track ownership authorization
* Paginated track listing
* Filter tracks by genre
* Filter tracks by publication status
* Search tracks by title or artist name
* Form Request validation
* API Resource responses
* Automated feature tests
* Database indexes for commonly queried track fields

## Tech Stack

* PHP 8.4
* Laravel 13.32.0
* Laravel Sanctum
* MySQL
* SQLite for automated testing
* PHPUnit/Pest
* Postman/Thunder Client for API testing


## Requirements

Before running the project, make sure you have:

* PHP 8.4+
* Composer
* MySQL
* Git

## Installation

Clone the repository:

```bash
git clone https://github.com/Edacynthia/music-catalogue-api.git
```

Enter the project directory:

```bash
cd music-catalogue-rest-api
```

Install PHP dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

## Database Configuration

Create a MySQL database, then update the database settings in `.env`.

Example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=music_catalogue_rest_api
DB_USERNAME=root
DB_PASSWORD=
```

Run the migrations:

```bash
php artisan migrate
```

## Running the Application

Start the Laravel development server:

```bash
php artisan serve
```

The API will normally be available at:

```text
http://127.0.0.1:8000
```

## Authentication

The API uses Laravel Sanctum personal access tokens.

After registration or login, the API returns a token.

For protected endpoints, send the token as a Bearer token:

```text
Authorization: Bearer YOUR_TOKEN
```

## API Endpoints

### Authentication

| Method | Endpoint        | Authentication | Description                          |
| ------ | --------------- | -------------- | ------------------------------------ |
| POST   | `/api/register` | No             | Register a new user                  |
| POST   | `/api/login`    | No             | Login and receive an API token       |
| POST   | `/api/logout`   | Yes            | Revoke the current token             |
| GET    | `/api/user`     | Yes            | Get the authenticated user's profile |

### Tracks

| Method | Endpoint              | Authentication | Description                          |
| ------ | --------------------- | -------------- | ------------------------------------ |
| GET    | `/api/tracks`         | Yes            | List the authenticated user's tracks |
| POST   | `/api/tracks`         | Yes            | Create a track                       |
| GET    | `/api/tracks/{track}` | Yes            | View one of the user's tracks        |
| PUT    | `/api/tracks/{track}` | Yes            | Update one of the user's tracks      |
| DELETE | `/api/tracks/{track}` | Yes            | Delete one of the user's tracks      |

## Track Fields

A track contains:

| Field                | Type    | Description                       |
| -------------------- | ------- | --------------------------------- |
| `title`              | string  | Track title                       |
| `artist_name`        | string  | Artist name                       |
| `genre`              | string  | Music genre                       |
| `duration`           | integer | Duration in seconds               |
| `release_date`       | date    | Track release date                |
| `publication_status` | string  | `draft` or `published`            |
| `user_id`            | integer | ID of the user who owns the track |

`user_id` is assigned automatically from the authenticated user and cannot be supplied to create ownership of another user's track.

## Creating a Track

Request:

```http
POST /api/tracks
```

Example JSON:

```json
{
    "title": "African Queen",
    "artist_name": "2Baba",
    "genre": "Afrobeats",
    "duration": 240,
    "release_date": "2025-05-10",
    "publication_status": "published"
}
```

Successful creation returns HTTP `201 Created`.

## Listing, Filtering, Searching and Pagination

The track listing endpoint supports pagination.

```http
GET /api/tracks
```

Pagination:

```http
GET /api/tracks?page=2
```

Filter by genre:

```http
GET /api/tracks?genre=Afrobeats
```

Filter by publication status:

```http
GET /api/tracks?publication_status=published
```

Search by title or artist:

```http
GET /api/tracks?search=Queen
```

Filters can also be combined:

```http
GET /api/tracks?genre=Afrobeats&publication_status=published&search=Queen
```

The listing response includes the track data together with pagination metadata and links.

## Authorization and Track Ownership

Each track belongs to the user who created it.

Track ownership is enforced through a Laravel Policy.

A user can:

* View their own tracks
* Update their own tracks
* Delete their own tracks

A user cannot:

* View another user's track
* Update another user's track
* Delete another user's track

Unauthorized access to another user's track returns HTTP `403 Forbidden`.

The track creation endpoint also uses the authenticated user's relationship:

```php
$request->user()->tracks()->create(...)
```

This prevents clients from choosing another user as the owner of a newly created track.

## Validation

Track validation is handled using dedicated Laravel Form Requests:

* `StoreTrackRequest`
* `UpdateTrackRequest`

Examples of validation rules include:

* Required title, artist, genre, duration and release date when creating a track
* Duration must be a positive integer
* Publication status must be either `draft` or `published`
* Update fields are optional, but supplied values must still satisfy their validation rules

Validation failures return HTTP `422 Unprocessable Entity`.

## Database Design

The main relationships are:

```text
User
  |
  | hasMany
  |
  v
Track
  |
  | belongsTo
  |
  v
User
```

The `tracks` table contains a foreign key to `users.id` with cascading deletion.

Indexes are also applied to frequently queried fields:

* `user_id`
* `genre`
* `publication_status`

## Testing

The application includes feature tests covering authentication and track management.

Run the complete test suite with:

```bash
php artisan test
```

The tests cover:

### Authentication

* User registration
* Duplicate email validation
* Successful login
* Invalid login credentials
* Authenticated user profile
* Logout
* Guest access to protected endpoints

### Tracks

* Successful track creation
* Track validation failures
* Viewing own tracks
* Preventing access to another user's tracks
* Updating own tracks
* Preventing unauthorized updates
* Deleting own tracks
* Preventing unauthorized deletion
* Filtering by genre
* Searching by title or artist
* Pagination

## API Response Format

Successful endpoints generally use a consistent structure such as:

```json
{
    "success": true,
    "message": "Track created successfully.",
    "data": {}
}
```

Paginated track listings use Laravel's resource collection pagination structure and include `data`, `links`, and `meta`.

## HTTP Status Codes

The API uses appropriate HTTP status codes, including:

| Status | Meaning                                           |
| ------ | ------------------------------------------------- |
| `200`  | Successful request                                |
| `201`  | Resource successfully created                     |
| `401`  | Authentication required or invalid authentication |
| `403`  | Authenticated user is not authorized              |
| `404`  | Resource not found                                |
| `422`  | Validation failure                                |

## Project Structure

Important application components include:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   └── TrackController.php
│   ├── Requests/
│   │   ├── StoreTrackRequest.php
│   │   └── UpdateTrackRequest.php
│   └── Resources/
│       └── TrackResource.php
├── Models/
│   ├── Track.php
│   └── User.php
└── Policies/
    └── TrackPolicy.php

database/
├── factories/
│   └── TrackFactory.php
└── migrations/

tests/
└── Feature/
    ├── AuthTest.php
    └── TrackTest.php
```

## Assumptions and Limitations

* A track belongs to one user.
* Users can only manage tracks they own.
* Track duration is stored as seconds.
* Publication status is limited to `draft` and `published`.
* The API currently does not include playlists.
* The API currently does not include payments or subscriptions.
* Real-time notifications are not included.
* Search uses title and artist name matching.
* The implementation uses Laravel 13.32.0, although the original assessment specifies Laravel 11/12.

## AI Assistance Disclosure

AI-assisted development tools were used during the implementation for development guidance, debugging assistance, code review suggestions, and explanations of Laravel concepts.

The final implementation was reviewed, tested, and adapted manually. The developer is able to explain the authentication flow, database relationships, validation, authorization policies, API resources, pagination, filtering, search, and automated tests used in this project.

## Git History

The project was developed using incremental Git commits to separate major features and refactoring steps.

Examples include:

```text
feat: add track model and database relationships
feat: add track filtering search and pagination
feat: add track ownership authorization
refactor: move track validation to form requests
refactor: add track API resource
perf: add indexes for track queries
test: add authentication and track feature coverage
```