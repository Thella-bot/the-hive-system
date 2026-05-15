# TODO - Fix blank page on http://127.0.0.1:8000/

- [ ] Create/confirm diagnosis: homepage route returns Inertia 'Welcome' but the page expects authenticated props/layout; verify actual root page component and missing routes.
- [ ] Fix: ensure `/` uses the public landing page component (resources/js/Pages/Public/Home.vue) or adjust Welcome.vue to render without authenticated user props.
- [ ] Fix: update `routes/web.php` to render the correct Inertia component for `/`.
- [ ] Run `npm run dev` / `php artisan serve` checks and verify page loads (no blank screen).
- [ ] Run basic build/lint (optional): `npm run build`, `php artisan route:list`.
