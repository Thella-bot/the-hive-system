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

### ✅ Core Foundation
User management, role-based access control, department structure, academic year scheduling, and cohort management.

### ✅ Student Management
Student profiles, applications, enrollment, grading, transcripts, and attendance tracking.

### ✅ Bursar / Finance
Invoices, payments, expenses, budgets, and financial reporting.

### ✅ Library Management
Book catalog, loans, reservations, and categories.

### 🔧 School Management (in progress)
Timetabling, kitchen/lab resource allocation, academic calendar, and equipment inventory.

### 🔧 Learning & Teaching (in progress)
Course and lesson management, structured recipe content, practical assessment rubrics.

### 🔧 Engagement (in progress)
Announcements, notice board listings, posts, recipe sharing, and guest chef event management.

---

## Roles

| Role | Description |
|---|---|
| `super-admin` | Full system access |
| `school-admin` | Manages school structure, users, and content |
| `academic-director` | Manages academic programmes and structure |
| `program-coordinator` | Manages specific programmes and cohorts |
| `admissions-officer` | Handles student admissions and applications |
| `registrar` | Manages student records and enrollment |
| `finance` | Access to bursar and financial features |
| `hr-manager` | Manages staff and HR functions |
| `chef-instructor` | Teaches courses, grades students, manages attendance |
| `student` | Views courses and content, participates in engagement |
| `it-support` | Technical support and system administration |

---

## Quick Reference

### Documentation Files

- [Database Schema](docs/database.md) - Complete database schema documentation
- [Controllers](docs/controllers.md) - All controllers and their methods
- [API Routes](docs/api-routes.md) - All endpoints and routes

### Key Models

| Model | Description |
|-------|-------------|
| `User` | Authentication and user accounts |
| `Profile` | Polymorphic staff/student profiles |
| `Department` | Academic departments |
| `Programme` | Academic programmes offered |
| `Module` | Course modules |
| `Cohort` | Student cohorts by year |
| `AcademicYear` | Academic year configuration |
| `Application` | Student programme applications |
| `Enrollment` | Student module enrollments |
| `StudentGrade` | Student grades |
| `Attendance` | Attendance records |
| `Invoice` | Student tuition invoices |
| `Payment` | Student payments |
| `Expense` | Operational expenses |
| `Budget` | Annual budgets |
| `Payslip` | Staff salary slips |
| `LeaveRequest` | Staff leave requests |
| `LibraryBook` | Library book inventory |
| `BookLoan` | Book loans |
| `Announcement` | System announcements |
| `Event` | Calendar events |
| `Document` | Policy documents |
| `Notification` | In-app notifications |

### Key Relationships

```
User
├─�� Profile (polymorphic: StaffProfile/StudentProfile)
├── Enrollments (many)
├── StudentGrades (many)
├── Attendances (many)
├── Applications (many)
├── Notifications (many)
└── Messages (many)

Department
└── Programmes (many)
    └── Cohorts (many)
        └── Enrollments (many)
            └── Users (many)

Programme
├── Modules (many-to-many)
└── Cohorts (many)

Module
├── Submissions (many)
├── Gradables (many)
└── Attendances (many)

Invoice
├── User (belongsTo)
└── Items (many)

Payment
├── User (belongsTo)
└── Invoice (belongsTo)
```

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
php artisan queue:work

# Or start multiple workers with a single command
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
│   ├── Controllers/
│   │   ├── Api/                    # REST API controllers
│   │   ├── Concerns/               # Controller concerns/traits
│   │   ├── Hive/                   # Main app controllers
│   │   │   ├── Admin/              # Admin-only controllers
│   │   │   ├── Bursar/             # Finance controllers
│   │   │   └── ...                 # Other controllers
│   │   └── *.php                   # Public controllers
│   └── Middleware/
│       ├── HandleInertiaRequests.php
│       └── BypassAuthInLocal.php
├── Models/
│   ├── User.php
│   ├── Department.php
│   ├── Programme.php
│   ├── Module.php
│   ├── Cohort.php
│   ├── AcademicYear.php
│   ├── Application.php
│   ├── Enrollment.php
│   ├── StudentGrade.php
│   ├── Attendance.php
│   ├── Invoice.php
│   ├── Payment.php
│   ├── Expense.php
│   ├── Budget.php
│   ├── Payslip.php
│   ├── LeaveRequest.php
│   ├── LibraryBook.php
│   ├── BookLoan.php
│   ├── Announcement.php
│   ├── Event.php
│   ├── Document.php
│   ├── Notification.php
│   ├── ChatChannel.php
│   ├── Message.php
│   ├── Poll.php
│   ├── Achievement.php
│   ├── ShortCourse.php
│   ├── Key.php                     # Includes KeyAssignment
│   └── VisitorLog.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── JetstreamServiceProvider.php
└── Actions/

database/
├── migrations/                     # Timestamped migrations
└── seeders/
    ├── RolePermissionSeeder.php
    └── DatabaseSeeder.php

docs/
├── database.md                     # Database schema
├── controllers.md                  # Controllers reference
└── api-routes.md                   # Routes reference

routes/
├── web.php                         # Public routes
├── api.php                        # REST API routes
├── hive.php                       # Main app routes
├── channels.php                  # WebSocket channels
├── console.php                   # Console commands
└── hive/
    ├── people.php                # User management
    ├── academic.php              # Academic modules
    ├── assessments.php           # Assessments
    ├── bursar.php                # Finance
    ├── library.php               # Library
    └── registrar.php             # Registrar

resources/
├── js/
│   ├── Layouts/
│   │   └── AppLayout.vue
│   ├── Pages/
│   │   ├── Dashboard.vue
│   │   ├── Departments/
│   │   ├── Programmes/
│   │   ├── Modules/
│   │   ├── Cohorts/
│   │   ├── Users/
│   │   ├── Students/
│   │   ├── Staff/
│   │   ├── Bursar/
│   │   ├── Library/
│   │   └── *.vue
│   └── Components/
│       ├── NavItem.vue
│       ├── StatCard.vue
│       ├── Badge.vue
│       └── Pagination.vue
└── css/
    └── app.css
```

---

## Key Design Decisions

**No Jetstream Teams** — Jetstream is used for authentication only. Department and cohort structure is managed through custom models rather than Jetstream's team system, giving full control over the school hierarchy.

**Dual profile system** — Users have either a `StaffProfile` or a `StudentProfile` depending on their role. This keeps role-specific fields clean and separate without overloading the `users` table.

**Permission-driven UI** — The sidebar, action buttons, and page access are all driven by Spatie permissions shared via `HandleInertiaRequests`. Adding a permission to a role automatically surfaces the relevant UI to that role.

**Soft deletes** — Departments, programmes, cohorts, and student profiles use soft deletes so historical records are preserved even after removal.

**Polymorphic relationships** — Used for profiles, notifications, bookmarks, and document-acknowledgements for flexibility.

**Scopes on all models** — All Eloquent models include query scopes for common filters (active, pending, etc.) with proper return type hints.

---

## API Reference

### Public Endpoints
- `GET /` - Landing page
- `GET /about` - About page
- `GET /programmes` - Programme listing
- `GET /contact` - Contact page
- `GET /apply` - Application form
- `POST /apply` - Submit application
- `POST /contact` - Submit contact

### REST API
- `GET /api/user` - Get authenticated user
- `GET /api/tasks` - Student task list
- `POST /api/tasks` - Create task
- `PATCH /api/tasks/{task}` - Update task
- `DELETE /api/tasks/{task}` - Delete task
- `GET /api/modules/{id}/messages` - Module chat
- `POST /api/modules/{id}/messages` - Send message

### Hive Internal
All routes prefixed with `/hive/` and `hive.`
- Dashboard, departments, programmes, cohorts
- Student management, applications, enrollment
- Bursar, library, documents, events
- Chat, polls, achievements

---

## Common Commands

```bash
# Development
php artisan serve              # Start server
npm run dev                    # Vite dev server
npm run build                  # Production build

# Database
php artisan migrate          # Run migrations
php artisan migrate:rollback # Rollback
php artisan db:seed           # Seed database
php artisan migrate:fresh     # Fresh install

# Queue workers
php artisan queue:work        # Start worker
php artisan queue:flush       # Clear failed jobs

# Code quality
./vendor/bin/pint             # Format code
php artisan test              # Run tests
php artisan route:list        # List routes

# Cache
php artisan cache:clear        # Clear cache
php artisan config:clear     # Clear config
php artisan view:clear       # Clear views
```

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

---

## Further Reading

- [Database Schema](docs/database.md) - Complete schema documentation
- [Controllers](docs/controllers.md) - All controllers and methods
- [API Routes](docs/api-routes.md) - All endpoint documentation