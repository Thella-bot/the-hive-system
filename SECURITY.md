# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability, please report it privately via email to **security@hbci.ac.ls**.

**Do not** open a public GitHub issue for security vulnerabilities.

We will acknowledge receipt within 48 hours and provide a detailed response within 7 days.

## Security Features

### Authentication

- **Laravel Fortify** — Authentication scaffolding with 2FA support
- **Email verification** — Users must verify email before accessing the system
- **Password hashing** — Bcrypt with configurable rounds (default: 12)
- **Session encryption** — All sessions encrypted at rest
- **Remember tokens** — Secure "remember me" functionality

### Authorization

- **Spatie Permission** — Role-based access control with granular permissions
- **Policies** — Model-level authorization for all resources
- **Middleware** — Route-level role checks
- **BasePolicy** — Super-admin and IT support have full access

### Data Protection

- **Soft deletes** — No permanent data loss
- **Input sanitization** — All user inputs validated via FormRequests
- **XSS prevention** — HTML sanitized in announcements and documents
- **SQL injection prevention** — Eloquent ORM with parameterized queries
- **CSRF protection** — All web routes protected by CSRF middleware

### File Upload Security

- **MIME validation** — File type verified by magic bytes, not extension
- **Size limits** — Configurable max upload sizes
- **Private storage** — Sensitive documents stored on private disk
- **Path traversal prevention** — Filenames sanitized
- **Virus scanning** — Recommended for production (ClamAV)

### Audit Logging

- **Model observers** — Track created/updated/deleted records
- **IP logging** — Request IPs logged for sensitive actions
- **User agent** — Browser/client info logged

## Security Configuration

### Environment Variables

```env
# Force HTTPS
APP_URL=https://hbci.ac.ls

# Disable debug in production
APP_DEBUG=false

# Strong encryption key
APP_KEY=base64:...

# Session security
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Trusted proxies (for load balancers)
TRUSTED_PROXIES=10.0.0.0/8,172.16.0.0/12,192.168.0.0/16
```

### Headers

```php
// app/Http/Middleware/TrustProxies.php
protected $proxies = '*';
protected $headers = Request::HEADER_X_FORWARDED_ALL;
```

## Security Best Practices

### For Developers

1. **Never commit secrets** — Use `.env` for all sensitive data
2. **Validate all inputs** — Use FormRequest classes
3. **Authorize all actions** — Use Policies, never skip authorization
4. **Sanitize output** — Use `{{ }}` (escaped) not `{!! !!}` (unescaped)
5. **Use parameterized queries** — Never concatenate SQL
6. **Limit file uploads** — Validate MIME type and size
7. **Hash passwords** — Never store plaintext passwords
8. **Use HTTPS** — Never transmit sensitive data over HTTP
9. **Keep dependencies updated** — Run `composer update` regularly
10. **Review logs** — Check `storage/logs/laravel.log` regularly

### For Users

1. **Strong passwords** — Minimum 8 characters, mixed case, numbers, symbols
2. **Enable 2FA** — Two-factor authentication available
3. **Log out** — Always log out on shared computers
4. **Report suspicious activity** — Contact IT support immediately
5. **Don't share credentials** — Each user has unique login

## Incident Response

### If a Breach is Suspected

1. **Contain** — Disable affected accounts, revoke tokens
2. **Assess** — Review logs, identify scope of breach
3. **Notify** — Inform affected users, regulatory bodies if required
4. **Remediate** — Fix vulnerabilities, update credentials
5. **Review** — Update security policies to prevent recurrence

### Emergency Contacts

| Role | Contact |
|------|---------|
| IT Security | it-support@hbci.ac.ls |
| System Admin | sysadmin@hbci.ac.ls |
| Management | management@hbci.ac.ls |

## Compliance

- **Data Protection** — Student and staff data handled per data protection regulations
- **Retention** — Data retention policies enforced via model observers
- **Access logs** — All sensitive actions logged with user, timestamp, IP
- **Backups** — Daily encrypted backups stored offsite

## Security Updates

Subscribe to security advisories:

- **GitHub Security Advisories** — Watch the repository
- **Laravel Security Advisories** — laravel.com/docs/security
- **PHP Security Advisories** — php.net/security

## Previous Security Issues

| Date | Issue | Severity | Resolution |
|------|-------|----------|------------|
| 2025-06 | Plaintext passwords in welcome emails | High | Removed passwords, added password reset link |
| 2025-06 | Missing MIME validation on uploads | High | Added magic bytes verification |
| 2025-06 | XSS in announcement HTML | Medium | Added HTML sanitization |
| 2025-06 | Unauthorized API access | Medium | Added role middleware to 16+ routes |
