# 🍽️ The Hive System

A culinary institute Hive built with Laravel, Inertia.js, and Vue 3. The Hive is an all-in-one internal platform for managing the academic, administrative, and social life of a culinary school — from student enrolment and cohort management to course delivery, assessments, and staff engagement.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 |
| Frontend | Vue 3 + Inertia.js v2 |
| Styling | Tailwind CSS v3 |
| Auth | Laravel Jetstream (without Teams) |
| Roles & Permissions | Spatie Laravel Permission |
| API Auth | Laravel Sanctum |
| PDF Generation | barryvdh/laravel-dompdf |
| Build Tool | Vite |
| Database | MySQL (SQLite for local dev) |

---

## Modules

### ✅ Core Foundation *(complete)*
User management, role-based access control, department structure, academic year scheduling, and cohort management.

### 🔧 School Management *(in progress)*
Timetabling, kitchen/lab resource allocation, academic calendar, and equipment inventory.

### 🔧 Learning & Teaching *(in progress)*
Course and lesson management, structured recipe content, practical assessment rubrics, and attendance tracking.

### 🔧 Student Management *(in progress)*
Student profiles, progress tracking, transcript and certificate generation via PDF.

### 🔧 Engagement *(in progress)*
Announcements, notice board listings, posts, recipe sharing, and guest chef event management.

---

## Roles

| Role | Description |
|---|---|
| `super-admin` | Full system access |
| `school-admin` | Manages school structure, users, and content |
| `department-head` | Manages their department's cohorts, students, and courses |
| `chef-instructor` | Teaches courses, grades students, manages attendance |
| `student` | Views courses and content, participates in engagement |

---

## Getting Started

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+ (or SQLite for local)

### Installation

```bash
# Clone the repository
git clone https://github.com/Thella-bot/the-hive-system.git
cd the-hive-system

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file and configure it
cp .env.example .env
php artisan key:generate
```

### Configure your `.env`

```env
APP_NAME="HBCI"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hbci
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

### Database Setup

```bash
# Run migrations
php artisan migrate

# Seed roles, permissions, and the default admin account
php artisan db:seed
```

### Run the app

```bash
# In one terminal — Laravel dev server
php artisan serve

# In another terminal — Vite (JS/CSS)
npm run dev

# In a third terminal — Queue workers (required for background jobs)
# Default starts a single worker; increase concurrency by running multiple workers.
php artisan queue:work

# Or start multiple workers with a single command (database queue by default)
# Example: 4 worker processes
php artisan queue:workers --workers=4

```

Visit `http://localhost:8000` and log in with the seeded admin account:

```
Email:    admin@hbci.ac.ls
Password: password
```

> **Change this password immediately after first login.**

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/         # One controller per resource
│   └── Middleware/          # HandleInertiaRequests (shares auth + flash globally)
├── Models/
│   ├── User.php
│   ├── Department.php
│   ├── AcademicYear.php
│   ├── Cohort.php
│   ├── StaffProfile.php
│   └── StudentProfile.php

database/
├── migrations/              # Ordered, timestamped migrations
└── seeders/
    └── RolePermissionSeeder.php

resources/js/
├── Layouts/
│   └── AppLayout.vue        # Main sidebar layout
├── Components/
│   ├── NavItem.vue
│   ├── StatCard.vue
│   ├── Badge.vue
│   └── Pagination.vue
└── Pages/
    ├── Dashboard.vue
    ├── Departments/         # Index, Create, Edit, Show
    ├── AcademicYears/       # Index, Create, Edit
    ├── Cohorts/             # Index, Create, Edit, Show
    └── Users/               # Index, Create, Edit, Show

routes/
└── web.php
```

---

## Key Design Decisions

**No Jetstream Teams** — Jetstream is used for authentication only. Department and cohort structure is managed through custom models rather than Jetstream's team system, giving us full control over the school hierarchy.

**Dual profile system** — Users have either a `StaffProfile` or a `StudentProfile` depending on their role. This keeps role-specific fields clean and separate without overloading the `users` table.

**Permission-driven UI** — The sidebar, action buttons, and page access are all driven by Spatie permissions shared via `HandleInertiaRequests`. Adding a permission to a role in the seeder automatically surfaces the relevant UI to that role.

**Soft deletes on key models** — Departments, cohorts, and student profiles use soft deletes so historical records are preserved even after removal.

---

## Running Tests

```bash
php artisan test
```

---

## Contributing

1. Create a feature branch from `main`
2. Follow PSR-12 for PHP and the Vue 3 Composition API (`<script setup>`) throughout
3. Run `./vendor/bin/pint` before committing to enforce code style
4. Open a pull request with a clear description of what was added or changed

---

## Licence

Private — culinary institute internal use only.