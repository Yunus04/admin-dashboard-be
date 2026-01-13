# Admin Dashboard API - Laravel

API Backend for Admin Dashboard with Authentication, Authorization (RBAC), and CRUD Operations features.

## Tech Stack

- **Framework:** Laravel 12.x
- **Database:** SQLite (default) / PostgreSQL
- **Authentication:** Laravel Sanctum (API Tokens)
- **PHP:** 8.2+

## Installation

### 1. Clone and Install Dependencies

```bash
git clone <repository-url>
cd admin-dashboard-be
composer install
```

### 2. Environment Configuration

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Default configuration uses SQLite for simplicity:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/Users/mohamadyunus/Documents/work/admin-dashboard/admin-dashboard-be/database/database.sqlite
```

For PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=admin_dashboard
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Setup Database

```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations and seeders
php artisan migrate:fresh --seed
```

### 4. Run Server

```bash
php artisan serve
```

API will run at: `http://localhost:8000`

---

## Default Accounts for Testing

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@admin.com | password |
| Admin | admin2@admin.com | password |
| Merchant | merchant@merchant.com | password |
| Merchant | janestore@merchant.com | password |
| Merchant | bobrestaurant@merchant.com | password |

---

## API Endpoints

### Base URL

```
http://localhost:8000/api
```

### Authentication

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| POST | `/auth/login` | User login | Public |
| POST | `/auth/logout` | Logout | Authenticated |
| POST | `/auth/refresh` | Refresh token | Authenticated |
| POST | `/auth/forgot-password` | Request password reset | Public |
| POST | `/auth/reset-password` | Reset password | Public |

#### Request Example - Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@admin.com",
    "password": "password"
  }'
```

#### Response Example - Login

```json
{
  "success": true,
  "message": "auth.login_success",
  "data": {
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "admin@admin.com",
      "role": "super_admin"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
  }
}
```

### User Registration

> **Note:** User registration for Admin Dashboard is handled by **Super Admin** through User Management, not self-registration.

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| POST | `/users` | Create new user | Super Admin only |

```bash
curl -X POST http://localhost:8000/api/users \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Admin",
    "email": "newadmin@example.com",
    "password": "password123",
    "role": "admin"
  }'
```

### Dashboard

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/dashboard` | Get dashboard data | Authenticated |

#### Response Example - Dashboard (Super Admin)

```json
{
  "success": true,
  "message": "dashboard.retrieved",
  "data": {
    "summary": {
      "total_users": 5,
      "total_merchants": 3,
      "active_merchants": 3
    },
    "users_by_role": {
      "super_admin": 1,
      "admin": 1,
      "merchant": 3
    },
    "recent_users": [...],
    "recent_merchants": [...]
  }
}
```

#### Response Example - Dashboard (Merchant)

```json
{
  "success": true,
  "message": "dashboard.retrieved",
  "data": {
    "merchant": {
      "id": 1,
      "business_name": "Main Merchant",
      "phone": "081234567890",
      "address": "123 Main Street, New York",
      "user": {
        "id": 4,
        "name": "Merchant User",
        "email": "merchant@merchant.com"
      }
    }
  }
}
```

### User Management (Super Admin Only)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/users` | List all users |
| POST | `/users` | Create new user (Admin or Merchant) |
| GET | `/users/{id}` | User detail |
| PUT | `/users/{id}` | Update user |
| DELETE | `/users/{id}` | Soft delete user |
| POST | `/users/{id}/restore` | Restore user |
| GET | `/users/merchants` | Get merchant users for owner selection (Super Admin, Admin) |

#### Get Merchant Users (Owner Selection)

Used when creating a merchant to select an owner from existing merchant-role users who don't have a merchant profile yet.

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/users/merchants` | List available merchant users | Super Admin, Admin |

```bash
curl -X GET http://localhost:8000/api/users/merchants \
  -H "Authorization: Bearer {token}"
```

#### Response Example - Merchant Users

```json
{
  "success": true,
  "message": "users.retrieved",
  "data": [
    {
      "id": 4,
      "name": "Merchant User",
      "email": "merchant@merchant.com",
      "role": "merchant",
      "created_at": "2026-01-08T14:39:44.000000Z",
      "updated_at": "2026-01-08T14:39:44.000000Z"
    }
  ]
}
```

### Settings

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/settings/profile` | Get user profile | Authenticated |
| PATCH | `/settings/profile` | Update profile | Authenticated |
| POST | `/settings/change-password` | Change password | Authenticated |

### Merchant Management

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/merchants` | List merchants | All roles* |
| POST | `/merchants` | Create merchant | Super Admin, Admin |
| GET | `/merchants/{id}` | Merchant detail | All roles** |
| PUT | `/merchants/{id}` | Update merchant | All roles** |
| DELETE | `/merchants/{id}` | Delete merchant | Super Admin, Admin |

**Notes:**
- * Merchant can only view their own profile
- ** Merchant can only access their own data

---

## Role-Based Access Control (RBAC)

### Available Roles

1. **super_admin** - Full access to all features, including user management
2. **admin** - Can manage merchants, cannot manage other admins
3. **merchant** - Can only access their own data

### Admin Restrictions

- Admin cannot create other Admin accounts
- Admin cannot manage (update/delete/restore) other Admin accounts
- Only Super Admin can manage users

### Middleware Usage Example

```php
// Only Super Admin can access users
Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    Route::apiResource('users', UserController::class);
});

// Super Admin & Admin for merchants
Route::middleware(['auth:sanctum', 'role:super_admin,admin'])->group(function () {
    Route::post('/merchants', [MerchantController::class, 'store']);
});
```

---

## Error Handling

### Error Response Format

```json
{
  "success": false,
  "message": "Error message key",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden (Role-based access denied)
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Internationalization (i18n)

The API supports multiple languages through translation files:

- **English**: `lang/en/messages.php`
- **Indonesian**: `lang/id/messages.php`

Response messages use translation keys:

```json
{
  "success": true,
  "message": "users.created"
}
```

---

## Development Notes

### Implemented Features

- [x] Authentication (Login, Logout, Forgot Password)
- [x] User Registration via User Management (Super Admin only)
- [x] Role-Based Access Control (RBAC)
- [x] User CRUD (Super Admin only)
- [x] Merchant CRUD (with role-based filtering)
- [x] Dashboard with role-specific data
- [x] Settings (Profile & Password Management)
- [x] Soft Deletes with Restore
- [x] API Tokens with Laravel Sanctum
- [x] Rate Limiting for auth endpoints

### Bonus Features Added

- [x] API Documentation (Swagger/L5-Swagger)
- [x] Unit/Integration Testing
- [x] Activity Logging for audit trail
- [x] Repository Pattern for data access
- [x] Comprehensive input validation with Form Requests
- [x] Multi-language support (EN/ID)
- [x] Secure route protection with role middleware

---

## Activity Logging

Audit trail logging system:

| Action | Description |
|--------|-------------|
| `user_created` | New user created |
| `user_updated` | User updated (with old/new values) |
| `user_deleted` | User deleted (soft delete) |
| `user_restored` | User restored |
| `merchant_created` | New merchant created |
| `merchant_updated` | Merchant updated |
| `merchant_deleted` | Merchant deleted |
| `login` | User login |
| `logout` | User logout |

Each log includes:
- User ID performing the action
- Action description
- Model type and ID
- Old and new values (for updates)
- IP address
- User agent

---

## Security Features

- **Authentication**: Laravel Sanctum with API tokens
- **Authorization**: Role-based access control (RBAC)
- **Password**: Hashing with bcrypt
- **Input Validation**: Comprehensive Form Requests
- **Rate Limiting**: 5 login, 3 forgot password attempts per minute
- **Soft Deletes**: Data is not permanently deleted
- **Activity Logging**: Audit trail for all actions

---

## Repository Pattern

Repository Pattern implementation for data access:

```
app/Repositories/
├── Interfaces/
│   ├── UserRepositoryInterface.php
│   ├── MerchantRepositoryInterface.php
│   └── AuthRepositoryInterface.php
└── Implementations/
    ├── UserRepository.php
    ├── MerchantRepository.php
    └── AuthRepository.php
```

---

## Testing

```bash
# Run all tests
php artisan test

# Run tests with details
php artisan test --verbose

# Run specific test
php artisan test --filter=AuthenticationTest
```

---

## API Documentation

Access Swagger UI for interactive API documentation:

```
http://localhost:8000/api/documentation
```

JSON Schema:

```
http://localhost:8000/api/docs/json
```

---

## Docker Deployment

### Using Docker Compose (Recommended for local development)

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f
```

The application will be available at `http://localhost:8000`

### Using Docker

```bash
# Build the image
docker build -t admin-dashboard-api .

# Run the container
docker run -p 8000:8000 \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=your-postgres-host \
  -e DB_PORT=5432 \
  -e DB_DATABASE=admin_dashboard \
  -e DB_USERNAME=postgres \
  -e DB_PASSWORD=postgres \
  admin-dashboard-api
```

---

## CI/CD Pipeline

The CI/CD pipeline runs on every push to `main` branch:

### Jobs:

1. **Quality Assurance**
   - Validate composer.json and composer.lock
   - Install dependencies
   - Run PHPUnit tests
   - SonarQube code analysis

### Configuration File

Located at: `.github/workflows/ci-cd.yml`

---

## License

MIT License

