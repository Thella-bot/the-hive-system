# Architecture Documentation

## System Overview

The Hive System is a comprehensive educational institution management platform built with Laravel 12, Vue 3, and Inertia.js. It manages students, staff, academics, finance, library, and administrative operations for Honey Bee Culinary Institute.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 + Inertia.js v2 |
| Styling | Tailwind CSS v3 |
| Authentication | Laravel Fortify + Spatie Permission |
| Database | MySQL (production), SQLite (testing) |
| Cache/Queue/Session | Redis |
| PDF Generation | DomPDF |
| Real-time | Laravel Reverb (WebSockets) |
| Testing | PHPUnit 11 |

## Architecture Patterns

### Layered Architecture

```
┌─────────────────────────────────────────┐
│           Frontend (Vue 3)             │
│  Pages/ → Components/ → Composables/   │
└──────────────────┬──────────────────────┘
                   │ Inertia.js
┌──────────────────▼──────────────────────┐
│          Controllers (Hive/)            │
│   - Handle HTTP requests               │
│   - Authorize via Policies             │
│   - Return Inertia responses           │
└──────────────────┬──────────────────────┘
                   │
┌──────────────────▼──────────────────────┐
│          Actions (app/Actions/)         │
│   - Business logic orchestration       │
│   - UpdateStudent, CreateNewStudent    │
└──────────────────┬──────────────────────┘
                   │
┌──────────────────▼──────────────────────┐
│          Services (app/Services/)       │
│   - SignatoryService                   │
│   - ReferenceDataService               │
│   - IdGenerator                        │
└──────────────────┬──────────────────────┘
                   │
┌──────────────────▼──────────────────────┐
│           Models (Eloquent)             │
│   - 50+ models with relationships      │
│   - Policies for authorization          │
└──────────────────┬──────────────────────┘
                   │
┌──────────────────▼──────────────────────┐
│          Database (MySQL)               │
│   - 30+ tables                         │
│   - Polymorphic relationships          │
│   - Soft deletes                       │
└─────────────────────────────────────────┘
```

### Request Lifecycle

1. **Request** → Web middleware (auth, session, CSRF)
2. **Route** → `routes/hive.php` (web) or `routes/api.php` (API)
3. **Middleware** → Role/permission checks, approved user check
4. **Controller** → Validates input, authorizes via policies
5. **Action/Service** → Executes business logic
6. **Model** → Interacts with database
7. **Response** → Inertia render or JSON/PDF

### Authorization Strategy

- **Spatie Permission** for roles and permissions
- **Policies** for model-level authorization (`app/Policies/`)
- **BasePolicy** grants super-admin/it-support full access
- **Route middleware** for role-based access (`role:super-admin|finance`)
- **Inertia shared data** exposes user roles/permissions to frontend

### Frontend Architecture

```
resources/js/
├── Pages/
│   ├── Hive/           # Protected admin/staff pages
│   │   ├── Dashboard.vue
│   │   ├── Students/
│   │   ├── Staff/
│   │   ├── Finance/
│   │   └── ...
│   └── Public/         # Public pages
├── Components/
│   ├── Pagination.vue
│   ├── Modal.vue
│   └── ...
├── Layouts/
│   ├── HiveLayout.vue
│   └── GuestLayout.vue
├── Composables/
│   └── useUser.js
└── app.js              # Entry point
```

- **Inertia.js** for SPA-like navigation without client-side routing
- **Vue 3 Composition API** with `<script setup>`
- **Tailwind CSS** for styling
- **Heroicons** for icons
- **Chart.js** for data visualization
- **FullCalendar** for calendar views

### Key Design Decisions

1. **No Jetstream Teams** — Single institution, not multi-tenant
2. **Dual Profile System** — `profiles` table polymorphic to `users` (student) and `staff_profiles` (staff)
3. **Permission-Driven UI** — Frontend shows/hides elements based on user permissions
4. **Soft Deletes** — Used extensively for audit trails
5. **Polymorphic Relationships** — `profiles` table serves both students and staff
6. **Action Classes** — Business logic isolated from controllers
7. **Service Classes** — Reusable business logic (signatories, reference data, ID generation)
8. **Traits** — Shared controller concerns (`HasFilters`, `GeneratesDocumentPdfs`, `VerifiesUploadedFiles`)

### Database Design

- **30+ tables** with comprehensive foreign key relationships
- **Polymorphic `profiles` table** for student/staff data
- **Pivot tables** for many-to-many (enrollments, module_programme, etc.)
- **JSON columns** for flexible data (dietary_restrictions, grounds)
- **Soft deletes** on most tables
- **40+ performance indexes** on frequently queried columns

### Caching Strategy

- **Redis** for cache, sessions, and queues
- **Reference data caching** — `ReferenceDataService` with `Cache::rememberForever()`
- **Notification counts** — Cached for 60 seconds
- **Signatory lookups** — Cached forever, invalidated via observers
- **PDF generation** — Cache-first strategy with `GeneratesDocumentPdfs` trait

### Queue Architecture

- **Redis** queue driver for real-time processing
- **Queued jobs**: PDF generation, email sending, user imports
- **All notifications** implement `ShouldQueue`
- **Failed job handling** via Laravel's built-in retry mechanism

### Testing Strategy

- **PHPUnit** for feature and unit tests
- **Feature tests** for controller actions
- **Factories** for test data generation
- **Inertia assertions** for page component testing
- **SQLite in-memory** for test database

## Directory Structure

```
app/
├── Actions/           # Business logic (Fortify, Hive, Jetstream)
├── Console/           # Artisan commands
├── Contracts/         # Interfaces
├── Enums/             # PHP enums
├── Events/            # Event classes
├── Exceptions/        # Exception handlers
├── Http/
│   ├── Controllers/   # HTTP controllers
│   │   ├── Hive/      # Admin/staff controllers
│   │   ├── Api/       # API controllers
│   │   └── Admin/     # Admin-specific controllers
│   ├── Middleware/    # HTTP middleware
│   └── Requests/      # Form request validation
├── Jobs/              # Queued jobs
├── Mail/              # Email classes
├── Models/            # Eloquent models (50+)
├── Notifications/     # Notification classes
├── Observers/         # Model observers
├── Policies/          # Authorization policies (28)
├── Providers/         # Service providers
└── Services/          # Business services

database/
├── factories/         # Model factories
├── migrations/        # Database migrations
└── seeders/           # Database seeders

resources/
├── css/               # Stylesheets
├── js/
│   ├── Pages/         # Vue page components
│   ├── Components/    # Reusable Vue components
│   ├── Layouts/       # Vue layouts
│   └── Composables/   # Vue composables
└── markdown/          # Static markdown content

routes/
├── hive.php           # Main web routes
├── api.php            # API routes
├── web.php            # Public web routes
└── hive/              # Feature-specific routes

tests/
├── Feature/           # Feature tests
├── Unit/              # Unit tests
└── TestCase.php       # Base test case
```

## Service Boundaries

| Service | Responsibility |
|---------|---------------|
| **SignatoryService** | Get signatory names for PDF generation |
| **ReferenceDataService** | Cache and retrieve roles, departments, programmes |
| **IdGenerator** | Generate sequential student/employee IDs |
| **NumberToWords** | Convert numbers to words for receipts |
| **Dashboard services** | Build dashboard data for different user types |
| **CreateNewStudent** | Orchestrate student creation |
| **UpdateStudent** | Orchestrate student updates |

## External Integrations

- **Laravel Reverb** — WebSocket server for real-time chat
- **DomPDF** — PDF document generation
- **Laravel Sanctum** — API token authentication
- **Laravel Fortify** — Authentication scaffolding
- **Spatie Permission** — Role-based access control
- **Laravel Reverb** — Real-time broadcasting

## Scalability Considerations

- **Redis** for all cache/session/queue operations
- **Queued jobs** for PDF generation and email sending
- **Reference data caching** reduces DB queries
- **Database indexes** on all frequently queried columns
- **Pagination** on all list endpoints
- **Lazy loading** for images and heavy frontend libraries
- **Vite code splitting** for optimal bundle sizes

## Security Considerations

- **Role-based access control** via Spatie Permission
- **Policy enforcement** on all models
- **Form request validation** on all inputs
- **MIME validation** on all file uploads
- **File content verification** via magic bytes
- **XSS prevention** via HTML sanitization
- **CSRF protection** on all web routes
- **Email verification** enabled
- **Unapproved user blocking** via middleware
- **Session encryption** enabled
- **Sanctum token expiration** configured
