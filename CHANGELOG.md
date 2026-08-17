# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Security audit fixes (plaintext passwords removed, email verification enabled, unapproved user blocking)
- Performance optimizations (Redis for cache/session/queue, dashboard query optimization, reference data caching)
- Placements and Disciplinary route families
- PDF caching trait for document generation
- File content verification for uploads
- 40+ database performance indexes
- `EnsureUserIsApproved` middleware
- `ReferenceDataService` for cached reference data
- `VerifiesUploadedFiles` trait
- `GeneratesDocumentPdfs` trait

### Changed
- Switched cache/session/queue drivers from database to Redis
- Removed dashboard computation from global middleware
- Updated all PDF generation to use caching trait
- Updated all notifications to implement `ShouldQueue`

### Fixed
- Paginator-as-array bug in Modules and Waitlists pages
- Student edit not saving due to wrong user context check
- `department.programme` relationship mismatch
- Tailwind v4 syntax causing PostCSS errors
- Department permission denied for admins
- Missing `hive.modules.show` route
- Vue component missing `computed` import

### Security
- Removed plaintext passwords from welcome emails
- Added MIME validation to all file upload endpoints
- Sanitized announcement body_html (client + server side)
- Added role middleware to 16+ routes
- Added `authorizeResource` to 5 controllers
- Created 3 new policies (Invoice, Expense, Budget)
- Fixed 7 policy methods checking non-existent permissions
- Moved sensitive files to private disk
- Added file content verification (magic bytes)

## [1.0.0] - 2025-01-15

### Added
- Initial release of The Hive System
- Student management (registration, enrollment, grades)
- Staff management (HR, payroll, leave)
- Finance module (invoices, payments, budgets, expenses)
- Academic management (modules, cohorts, programmes, departments)
- Assessment system (gradables, submissions, online assessments)
- Library management (books, loans, reservations)
- Document management (versioning, acknowledgements)
- Announcements system with attachments
- Events calendar with RSVP
- Polls and surveys
- Chat system with WebSocket support
- Notifications system
- Search functionality
- Student ID cards
- PDF document generation
- Role-based access control with Spatie Permission
- Inertia.js + Vue 3 frontend
- Tailwind CSS styling
- Comprehensive test suite
