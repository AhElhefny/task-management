# Task Manager API

A powerful RESTful API built with Laravel 11+ for managing tasks, supporting features like filtering, sorting, pagination, notifications, and rate limiting.

---

## 🚀 Features

- Task CRUD operations (create, list, update status, soft delete)
- Filtering by:
  - Status (1=Pending, 2=In Progress, 3=Completed, 4=Overdue)
  - Due date range (`start_date`, `end_date`)
  - Text search (`title`, `description`)
- Sorting by `created_at`, `due_date`, `priority`
- Pagination with metadata
- **Email notifications** sent 24 hours before task due
- **Rate limiting** on task creation endpoint
- Laravel Queues & Artisan Command usage
- OpenAPI (Swagger) Documentation

---

## 🛠 Setup Instructions

1. Clone the repository:

```bash
git clone https://github.com/AhElhefny/task-manager-api.git
cd task-manager-api

Install dependencies:
    composer install
    cp .env.example .env
    php artisan key:generate

Set up database in .env then run:
    php artisan migrate --seed

Start the server:
    php artisan serve

🔐 Rate Limiting
The API limits how many tasks a user can create in a short time to prevent abuse.

Configured using Laravel’s built-in ThrottleRequests middleware.

Limit: 5 requests per minute on /api/tasks POST.

Returns 429 Too Many Requests if exceeded.

📬 Task Notification System
We notify users via email 24 hours before a task’s due date.

Artisan command: php artisan tasks:notify-for-coming

Scheduled to run hourly via Laravel Scheduler

Uses queue system to send notifications asynchronously

Job Class: SendTaskReminderEmail

Custom notification logic is in app/Notifications/NotifyForComingTasksNotification.php

📅 Task Status & Priority Enums
    Used consistently across validation, database, and API resources.

Status Code	Meaning
    1	Pending
    2	In Progress
    3	Completed
    4	Overdue

Priority Code	Meaning
    1	Low
    2	Medium
    3	High

📄 API Documentation
Full Swagger documentation is available in swagger.yaml. You can visualize it using:

Swagger Editor

Or Postman with the OpenAPI import

Also, a ready Postman Collection is available in postman/collection.json.

🧠 Design Decisions
Service Layer: Encapsulates business logic outside controllers.

Request Classes: For clean validation and type safety.

Enum Integration: Centralized status/priority control.

Queue System: For non-blocking email delivery.

Rate Limiting: Security and abuse prevention.

OpenAPI: Makes the API self-documented and testable.

✍️ Author
Ahmed Ahmed Elhefny
GitHub: AhElhefny
Project: Task Manager API
