# Contributing to The Hive System

Thank you for your interest in contributing to The Hive System. This document provides guidelines and instructions for contributing.

## Code of Conduct

Please be respectful and constructive in all interactions. We are committed to providing a welcoming and inclusive environment for everyone.

## Getting Started

1. Fork the repository
2. Clone your fork locally
3. Install dependencies: `composer install && npm install`
4. Copy `.env.example` to `.env` and configure it
5. Run `php artisan key:generate`
6. Run `php artisan migrate --seed`
7. Start the development server: `npm run dev` in one terminal, `php artisan serve` in another

## Coding Standards

### PHP
- Follow **PSR-12** coding style
- All PHP files must start with `declare(strict_types=1);`
- Use **Laravel Pint** for formatting: `vendor/bin/pint`
- Use **PHPStan** for static analysis at level 8: `vendor/bin/phpstan analyse`
- Add `: void` to all empty constructors
- Use meaningful variable and method names
- Add PHPDoc to all public methods

### JavaScript/Vue
- Use **Prettier** for formatting
- Use Vue 3 Composition API with `<script setup>`
- Use Tailwind CSS for styling
- Follow the component structure in `resources/js/`

### Commit Messages
- Use clear, descriptive commit messages
- Reference issue numbers when applicable
- Format: `type(scope): description`

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

## Directory Structure

- `app/Actions/` — Business logic actions (CreateNewStudent, UpdateUser, etc.)
- `app/Controllers/` — HTTP controllers
- `app/Models/` — Eloquent models
- `app/Services/` — Service layer for complex logic (dashboard data, ID generation, etc.)
- `app/Policies/` — Authorization policies
- `app/Requests/` — Form request validation classes
- `app/Notifications/` — Notification classes
- `app/Events/` — Event classes
- `app/Jobs/` — Queued jobs
- `app/Enums/` — PHP 8.1+ enums
- `app/Http/Middleware/` — HTTP middleware
- `resources/js/Pages/` — Vue.js Inertia pages
- `resources/js/Components/` — Vue.js reusable components
- `resources/js/Layouts/` — Vue.js layouts
- `resources/js/composables/` — Vue.js composables

## Pull Request Process

1. Create a feature branch from `main`
2. Write or update tests for your changes
3. Ensure all tests pass: `php artisan test`
4. Ensure PHPStan passes: `vendor/bin/phpstan analyse`
5. Ensure code is formatted: `vendor/bin/pint`
6. Commit with a clear, descriptive message
7. Open a pull request with a detailed description

## Testing

Run the full test suite:
```bash
php artisan test
```

Run with coverage:
```bash
php artisan test --coverage
```

Run a specific test file:
```bash
php artisan test tests/Feature/ExampleTest.php
```

Run with stop on failure:
```bash
php artisan test --stop-on-failure
```

## Architecture

See [ARCHITECTURE.md](./ARCHITECTURE.md) for system design, data flow, and service boundaries.

## Frontend

See [FRONTEND.md](./FRONTEND.md) for Vue component hierarchy, composables, and Inertia patterns.

## Testing Guide

See [TESTING.md](./TESTING.md) for test structure, factories, and coverage expectations.

## Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md) for production setup, SSL, queues, and backups.

## Security

See [SECURITY.md](./SECURITY.md) for security policy, auth flow, and vulnerability reporting.

## API

See [API.md](./API.md) for API endpoints, request/response schemas, and authentication.

## Code Style Tools

We use the following tools to maintain code quality:

| Tool | Purpose | Command |
|------|---------|---------|
| Laravel Pint | Code formatting | `vendor/bin/pint` |
| PHPStan | Static analysis | `vendor/bin/phpstan analyse` |
| PHPUnit | Testing | `php artisan test` |
| Prettier | JS/Vue formatting | `npx prettier --write resources/js/` |

## EditorConfig

We use `.editorconfig` to maintain consistent coding styles. Ensure your editor supports EditorConfig.

## Questions?

Feel free to open an issue or reach out to the development team.
