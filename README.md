
# The Hive System

**The Hive** is a web-based intranet and management system for the Honey Bee Culinary Insitute (HBCI).

## Features

- User management for students, instructors, and staff
- Leave request and approval workflow
- Payslip management and secure document storage
- Assignment creation, submission, and grading
- Announcements and notifications
- Role-based access control
- Modular, extensible architecture

## Technology Stack
- [Laravel](https://laravel.com/) (PHP framework)
- Inertia.js & Vue.js (frontend)
- MySQL or compatible database
- Tailwind CSS

## Getting Started

1. **Clone the repository:**
	```bash
	git clone <repo-url>
	cd the-hive-system
	```
2. **Install dependencies:**
	```bash
	composer install
	npm install
	```
3. **Copy and configure environment:**
	```bash
	cp .env.example .env
	# Edit .env as needed
	```
4. **Generate application key:**
	```bash
	php artisan key:generate
	```
5. **Run migrations and seeders:**
	```bash
	php artisan migrate --seed
	```
6. **Build frontend assets:**
	```bash
	npm run build
	```
7. **Start the development server:**
	```bash
	php artisan serve
	```

## Contributing

Contributions are welcome! Please open issues or submit pull requests for improvements or bug fixes.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).