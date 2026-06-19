# API Routes & Endpoints Documentation

This document provides a comprehensive overview of all API routes in The Hive System.

---

## Public API Endpoints

### Home
```
GET /
```
Redirects to dashboard if authenticated, otherwise shows public home page.

### Pages
```
GET /about
GET /programmes
GET /contact
GET /apply
GET /short-courses
GET /short-courses/{short_course}/apply
```

### Forms
```
POST /apply                        - Submit programme application
POST /contact                      - Submit contact form
POST /short-courses/{id}/apply     - Submit short course application
```

---

## Authentication Endpoints

Handled by Laravel Jetstream:

```
GET  /login                        - Login page
POST /login                       - Login attempt
POST /logout                      - Logout
GET  /forgot-password             - Password reset request page
POST /forgot-password            - Send password reset link
GET  /reset-password/{token}    - Password reset page
POST /reset-password            - Reset password
```

---

## Hive Internal Endpoints

### Dashboard
```
GET /hive/dashboard               - Main dashboard
```

### Departments (resourceful)
```
GET    /hive/departments
GET    /hive/departments/create
POST   /hive/departments
GET    /hive/departments/{department}
GET    /hive/departments/{department}/edit
PUT    /hive/departments/{department}
DELETE /hive/departments/{department}
```

### Programmes (resourceful)
```
GET    /hive/programmes
GET    /hive/programmes/create
POST   /hive/programmes
GET    /hive/programmes/{programme}
GET    /hive/programmes/{programme}/edit
PUT    /hive/programmes/{programme}
DELETE /hive/programmes/{programme}
```

### Cohorts (resourceful)
```
GET    /hive/cohorts
GET    /hive/cohorts/create
POST   /hive/cohorts
GET    /hive/cohorts/{cohort}
GET    /hive/cohorts/{cohort}/edit
PUT    /hive/cohorts/{cohort}
DELETE /hive/cohorts/{cohort}
```

### Academic Years
```
GET /hive/academic-years
GET /hive/academic-years/create
POST /hive/academic-years
GET /hive/academic-years/{academic_year}/edit
PUT /hive/academic-years/{academic_year}
DELETE /hive/academic-years/{academic_year}
```

### Short Courses
```
GET    /hive/short-courses
GET    /hive/short-courses/create
POST   /hive/short-courses
GET    /hive/short-courses/{short_course}/edit
PUT    /hive/short-courses/{short_course}
DELETE /hive/short-courses/{short_course}
GET    /hive/short-course-applications
POST   /hive/short-course-applications/{application}/review
```

---

## People Management

### Users
```
GET    /hive/users
GET    /hive/users/create
POST   /hive/users
GET    /hive/users/{user}
GET    /hive/users/{user}/edit
PUT    /hive/users/{user}
DELETE /hive/users/{user}
```

### Students
```
GET    /hive/students
GET    /hive/students/create
POST   /hive/students
GET    /hive/students/{student}
GET    /hive/students/{student}/edit
PUT    /hive/students/{student}
DELETE /hive/students/{student}
```

### Staff
```
GET    /hive/staff
GET    /hive/staff/create
POST   /hive/staff
GET    /hive/staff/{staff}
GET    /hive/staff/{staff}/edit
PUT    /hive/staff/{staff}
DELETE /hive/staff/{staff}
```

### Admin
```
GET /hive/admin/approve-users
POST /hive/admin/approve-users/{user}
GET /hive/admin/import-users
POST /hive/admin/import-users
```

---

## Student Management

### Applications
```
GET    /hive/applications
GET    /hive/applications/create
POST   /hive/applications
GET    /hive/applications/{application}
GET    /hive/applications/{application}/edit
PUT    /hive/applications/{application}
POST   /hive/applications/{application}/complete-registration
```

### Registration
```
GET  /hive/registration
POST /hive/registration
PATCH /hive/registration/{application}
GET  /hive/registration/proof
```

### Enrollment
```
GET    /hive/enrollment
POST   /hive/enrollment
DELETE /hive/enrollment/{module}
```

### Transcript
```
GET /hive/transcript
GET /hive/transcript/{student}/download
```

### Grades
```
GET    /hive/grades
POST   /hive/grades
PUT    /hive/grades/{grade}
DELETE /hive/grades/{grade}
```

### Waitlist
```
GET    /hive/waitlist
POST   /hive/waitlist
DELETE /hive/waitlist/{waitlist}
```

---

## Academic

### Modules
```
GET /hive/modules
GET /hive/modules/create
POST /hive/modules
GET /hive/modules/{module}/edit
PUT /hive/modules/{module}
DELETE /hive/modules/{module}
POST /hive/programmes
```

### Submissions
```
GET    /hive/submissions
POST   /hive/submissions
PUT    /hive/submissions/{submission}
DELETE /hive/submissions/{submission}
```

### Gradables
```
GET    /hive/gradables
GET    /hive/gradables/create
POST   /hive/gradables
GET    /hive/gradables/{gradable}
GET    /hive/gradables/{gradable}/edit
PUT    /hive/gradables/{gradable}
DELETE /hive/gradables/{gradable}
```

### Attendance
```
GET  /hive/attendance
GET  /hive/attendance/scan
POST /hive/attendance/checkin
```

---

## Profile & Account

```
GET  /hive/profile
POST /hive/profile
GET  /hive/student-id
GET  /hive/search?q={query}
```

---

## Announcements

```
GET    /hive/announcements
GET    /hive/announcements/create
POST   /hive/announcements
GET    /hive/announcements/{announcement}
GET    /hive/announcements/{announcement}/edit
PUT    /hive/announcements/{announcement}
DELETE /hive/announcements/{announcement}
GET    /hive/announcements/{announcement}/attachments/{attachment}/download
```

---

## Documents

```
GET    /hive/documents
GET    /hive/documents/create
POST   /hive/documents
GET    /hive/documents/{document}
GET    /hive/documents/{document}/edit
PUT    /hive/documents/{document}
DELETE /hive/documents/{document}
GET    /hive/documents/modules
POST   /hive/documents/{document}/versions
GET    /hive/document-versions/{version}/download
POST   /hive/documents/{document}/acknowledge
GET    /hive/documents/{document}/acknowledgements
```

---

## Notifications

```
GET /hive/notifications
POST /hive/notifications/{notification}/read
POST /hive/notifications/read-all
```

---

## Events

```
GET    /hive/events
GET    /hive/events/create
POST   /hive/events
GET    /hive/events/{event}
GET    /hive/events/{event}/edit
PUT    /hive/events/{event}
DELETE /hive/events/{event}
POST   /hive/events/{event}/rsvp
GET    /hive/events/{event}/ical
GET    /hive/events/{event}/qrcode
```

---

## Chat & Messaging

```
GET /hive/chat
GET /hive/chat/channel/{channel}
GET /hive/chat/module/{module}
```

---

## Polls

```
GET    /hive/polls
POST   /hive/polls
DELETE /hive/polls/{poll}
POST   /hive/polls/{poll}/vote
```

---

## Achievements

```
GET    /hive/achievements
POST   /hive/achievements
DELETE /hive/achievements/{achievement}
```

---

## Leave Requests

```
GET    /hive/leave-requests
GET    /hive/leave-requests/create
POST   /hive/leave-requests
PUT    /hive/leave-requests/{leave}
DELETE /hive/leave-requests/{leave}
```

---

## Payslips

```
GET    /hive/payslips
GET    /hive/payslips/create
POST   /hive/payslips
GET    /hive/payslips/{payslip}/edit
PUT    /hive/payslips/{payslip}
DELETE /hive/payslips/{payslip}
POST   /hive/payslips/generate
POST   /hive/payslips/generate-batch
GET    /hive/payslips/{payslip}/download
```

---

## Finance

### Invoices
```
GET    /finance/invoices
POST   /finance/invoices
GET    /finance/invoices/generate
GET    /finance/invoices/{invoice}
PUT    /finance/invoices/{invoice}
DELETE /finance/invoices/{invoice}
```

### Payments
```
GET    /finance/payments
POST   /finance/payments
GET    /finance/payments/{payment}
PUT    /finance/payments/{payment}
DELETE /finance/payments/{payment}
PATCH /finance/payments/{payment}/verify
```

### Expenses
```
GET    /finance/expenses
GET    /finance/expenses/create
POST   /finance/expenses
GET    /finance/expenses/{expense}
PUT    /finance/expenses/{expense}
DELETE /finance/expenses/{expense}
PATCH /finance/expenses/{expense}/approve
PATCH /finance/expenses/{expense}/reject
PATCH /finance/expenses/{expense}/mark-paid
GET    /finance/expenses/categories
POST   /finance/expenses/categories
PATCH /finance/expenses/categories/{category}
DELETE /finance/expenses/categories/{category}
```

### Budgets
```
GET    /finance/budgets
POST   /finance/budgets
GET    /finance/budgets/{budget}
PUT    /finance/budgets/{budget}
DELETE /finance/budgets/{budget}
PATCH /finance/budgets/{budget}/activate
PATCH /finance/budgets/{budget}/close
```

### Convectionary Income
```
GET    /finance/convectionary
GET    /finance/convectionary/create
POST   /finance/convectionary
GET    /finance/convectionary/{convectionary}
PUT    /finance/convectionary/{convectionary}
DELETE /finance/convectionary/{convectionary}
```

### Reports
```
GET /finance/reports/dashboard
GET /finance/reports/income
GET /finance/reports/expenses
GET /finance/reports/age-analysis
GET /finance/reports/student/{user}
```

---

## Library

```
GET    /hive/library
GET    /hive/library/create
POST   /hive/library
GET    /hive/library/{book}
GET    /hive/library/{book}/edit
PUT    /hive/library/{book}
DELETE /hive/library/{book}
POST   /hive/library/{book}/loan
POST   /hive/library/{book}/return
POST   /hive/library/{book}/reserve
```

---

## Facility Management

### Keys
```
GET    /hive/keys
POST   /hive/keys
POST   /hive/keys/{key}/issue
POST   /hive/keys/{key}/return
POST   /hive/keys/{key}/report-lost
```

### Visitor Logs
```
GET    /hive/visitor-logs
POST   /hive/visitor-logs
POST   /hive/visitor-logs/{log}/checkout
```

### Suppliers
```
GET    /hive/suppliers
POST   /hive/suppliers
PUT    /hive/suppliers/{supplier}
DELETE /hive/suppliers/{supplier}
```

### Uniform Requests
```
GET    /hive/uniform-requests
POST   /hive/uniform-requests
POST   /hive/uniform-requests/{request}/review
```

---

## REST API Endpoints

The system also exposes a REST API at `/api/` for mobile apps and external integrations.

### Chat Messages (Module)
```
GET /api/modules/{module}/messages
POST /api/modules/{module}/messages
```

### Chat Messages (Channel)
```
GET /api/channels/{channel}/messages
POST /api/channels/{channel}/messages
```

### Authenticated API Routes
```
GET /api/user                 - Get current user
GET /api/tasks              - List tasks
POST /api/tasks             - Create task
PATCH /api/tasks/{task}     - Update task
DELETE /api/tasks/{task}    - Delete task
```

---

## Middleware & Access Control

| Route Pattern | Required Roles |
|---------------|----------------|
| `/hive/departments/*` | super-admin, academic-director |
| `/hive/programmes/*` | super-admin, academic-director, program-coordinator |
| `/hive/short-courses/*` | super-admin, admissions-officer |
| `/hive/cohorts/*` | super-admin, program-coordinator |
| `/hive/users/*` | super-admin, it-support, hr-manager |
| `/hive/students/*` | super-admin, admissions-officer, registrar, program-coordinator |
| `/hive/staff/*` | super-admin, hr-manager |
| `/finance/*` | super-admin, finance, hr-manager |

---

## Route Naming Convention

All Hive routes are prefixed with `hive.`:

- `hive.dashboard`
- `hive.departments.index`
- `hive.departments.create`
- `hive.applications.complete-registration`
- `finance.invoices.generate`
- etc.

---

## HTTP Methods

| Method | Usage |
|--------|-------|
| GET | Retrieve resources, show forms |
| POST | Create new resources, actions |
| PUT/PATCH | Update resources |
| DELETE | Remove resources |

---

## Response Format

All endpoints return Inertia pages (server-rendered Vue):

```php
return Inertia::render('Page/Component', $data);
```

API endpoints return JSON:

```php
return response()->json(['data' => $resource]);
```