# Task Manager API

## 🧾 Brief Documentation on Setup & Running the Application

### Prerequisites

- PHP >= 8.1
- Composer
- Laravel 11+
- MySQL or compatible DB

### Setup Steps

```bash
# 1. Clone the project
git clone https://github.com/AhElhefny/task-management.git
cd task-manager-api

# 2. Install PHP dependencies
composer install

# 3. Create environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Set DB credentials in .env
DB_DATABASE=task_db
DB_USERNAME=root
DB_PASSWORD=your_password

# 6. Run migrations and seeders
php artisan migrate --seed

# 7. Serve the app
php artisan serve


```

## 📦 Project Structure Overview

```
├── app/
│   ├── Http/
│   │   ├── Controllers/  # Task, Auth, User controllers
│   │   ├── Middleware/   # Role middleware
│   ├── Models/           # Task, User, Dependency
│   ├── Services/         # Business logic
├── routes/
│   └── api.php           # All API routes
├── database/
│   ├── migrations/
│   └── seeders/
└── tests/                # Feature & unit tests
```

---

## 🔐 Authentication

- **Login Endpoint:** `POST /api/login`
- Uses token-based auth via Laravel Sanctum/Passport
- Seeded actors:
  - Manager: `manager@example.com`
  - User: `user@example.com`

---

## ✅ Tasks API Endpoints

### Create Task *(Manager Only)*

```http
POST /api/tasks
```

Body:

```json
{
  "title": "New Task",
  "description": "Optional",
  "due_date": "2025-08-10 15:00"
}
```

### Get Tasks with Filters *(Manager + Assigned User)*

```http
GET /api/tasks?status=1&user_id=5&start_date=2025-07-01&end_date=2025-08-01
```

### Get Task Details *(Assigned User / Manager)*

```http
GET /api/tasks/{id}
```

### Assign Task to User *(Manager Only)*

```http
PATCH /api/tasks/{id}/assign-user
```

### Add Dependencies *(Manager Only)*

```http
POST /api/tasks/{id}/add-dependencies
```

Body:

```json
{
  "dependency_ids": [3, 6, 9]
}
```

### Update Task Details *(Manager Only)*

```http
PUT /api/tasks/{id}
```

### Update Task Status *(Assigned User Only)*

```http
PATCH /api/tasks/{id}/update-status
```

Body:

```json
{
  "status": 2  // 1 = Pending, 2 = Completed, 3 = Cancelled
}
```

---

## 🔐 Role-Based Authorization

| Action                   | Manager | User |
| ------------------------ | ------- | ---- |
| Create task              | ✅       | ❌    |
| Update task details      | ✅       | ❌    |
| Assign task to user      | ✅       | ❌    |
| View assigned tasks      | ✅       | ✅    |
| Update status (assigned) | ❌       | ✅    |

---

## ⚙️ Tech Stack

- Laravel 10
- Sanctum / Passport (Token Auth)
- MySQL
- PHPUnit (Testing)
- RESTful standards

---

## 🧪 Testing

```bash
php artisan test
```

Includes tests for:

- Auth
- Task creation & filtering
- Role authorization
- Status update

---

## 📌 Notes

- Tasks cannot be marked complete if dependencies are incomplete
- Queue jobs & listeners used for background processes (optional)
- Full-text search supported on title + description

---

## 📄 License

MIT

