# Deployment Guide

## Prerequisites

- PHP 8.2+
- Composer 2+
- Node.js 20+
- MySQL 8.0+
- Redis 7+
- Nginx or Apache
- SSL certificate (Let's Encrypt recommended)
- Supervisor (for queue workers)

## Server Setup

### 1. Install Dependencies

```bash
# System packages
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd \
    php8.2-bcmath php8.2-intl nginx mysql-server redis-server supervisor
```

### 2. Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/your-org/the-hive-system.git
cd the-hive-system
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 3. Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Install Node Dependencies and Build

```bash
npm install
npm run build
```

### 5. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hbci.ac.ls

LOG_CHANNEL=errorlog
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hbci
DB_USERNAME=hbci_user
DB_PASSWORD=secure_password

SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis

BROADCAST_CONNECTION=reverb

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="noreply@hbci.ac.ls"
MAIL_FROM_NAME="${APP_NAME}"

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Reverb config
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 6. Database Setup

```bash
# Create database and user
sudo mysql -u root -p
CREATE DATABASE hbci CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hbci_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON hbci.* TO 'hbci_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force
```

### 7. Storage Linking

```bash
php artisan storage:link
```

### 8. Cache Optimization

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 9. Set Permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Queue Workers

### Configure Supervisor

Create `/etc/supervisor/conf.d/hive-worker.conf`:

```ini
[program:hive-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/the-hive-system/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=4
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/hive-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hive-worker:*
```

### Alternative: Systemd Service

Create `/etc/systemd/system/hive-queue.service`:

```ini
[Unit]
Description=Hive Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/the-hive-system/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable hive-queue
sudo systemctl start hive-queue
```

## Web Server Configuration

### Nginx

Create `/etc/nginx/sites-available/hive`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name hbci.ac.ls;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name hbci.ac.ls;

    root /var/www/the-hive-system/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/hbci.ac.ls/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/hbci.ac.ls/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/hive /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d hbci.ac.ls
sudo certbot renew --dry-run
```

## Scheduled Tasks (Cron)

Add to crontab (`crontab -e`):

```cron
* * * * * cd /var/www/the-hive-system && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled tasks in `app/Console/Kernel.php`:
- Send anniversary notifications
- Update cohort statuses
- Generate recurring invoices

## Reverb WebSocket Server

### Option 1: Supervisor (Recommended)

Create `/etc/supervisor/conf.d/hive-reverb.conf`:

```ini
[program:hive-reverb]
process_name=%(program_name)s
command=php /var/www/the-hive-system/artisan reverb:serve --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/hive-reverb.log
```

### Option 2: Systemd Service

Create `/etc/systemd/system/hive-reverb.service`:

```ini
[Unit]
Description=Hive Reverb Server
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/the-hive-system/artisan reverb:serve --host=0.0.0.0 --port=8080

[Install]
WantedBy=multi-user.target
```

## Database Backups

### Automated Daily Backup Script

Create `/var/www/the-hive-system/scripts/backup.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/hive"
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u hbci_user -p secure_password hbci | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/the-hive-system/storage/app

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete
```

Add to crontab:

```cron
0 2 * * * /var/www/the-hive-system/scripts/backup.sh
```

## Monitoring

### Application Monitoring

- **Laravel Telescope** (local only, disable in production)
- **Laravel Pulse** (optional, for production monitoring)
- **Uptime monitoring** — UptimeRobot, Pingdom
- **Error tracking** — Sentry, Bugsnag

### Server Monitoring

- **Log monitoring** — `storage/logs/laravel.log`, `/var/log/nginx/`
- **Queue monitoring** — Supervisor status, Horizon (if using Laravel Horizon)
- **Database monitoring** — MySQL slow query log
- **Redis monitoring** — `redis-cli info`

## Scaling Considerations

### Horizontal Scaling

1. **Load Balancer** — nginx or HAProxy in front of multiple app servers
2. **Shared Storage** — S3 or shared NFS for file uploads
3. **Centralized Cache** — Redis cluster
4. **Database Read Replicas** — MySQL replication for read-heavy workloads
5. **Queue Workers** — Scale horizontally with more worker processes

### Vertical Scaling

1. **Database** — Increase RAM, use SSD storage
2. **Redis** — Increase max memory, configure eviction policy
3. **PHP-FPM** — Increase `pm.max_children`

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| 500 Error | Check `storage/logs/laravel.log`, verify `.env` values |
| Queue not processing | Check Supervisor status, verify Redis connection |
| File uploads failing | Check disk permissions, verify `FILESYSTEM_DISK` |
| Emails not sending | Check mail configuration, verify SMTP credentials |
| WebSocket not connecting | Check Reverb server status, verify firewall ports |
| Slow dashboard | Check Redis cache, verify database indexes |

### Debug Commands

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check queue status
sudo supervisorctl status

# Check Redis connection
redis-cli ping

# Clear all caches
php artisan optimize:clear

# Check config
php artisan config:show

# Check routes
php artisan route:list
```

## Zero-Downtime Deployment

### Using Laravel Envoy

Create `Envoy.blade.php`:

```blade
@servers(['web' => 'user@server_ip'])

@task('deploy')
    cd /var/www/the-hive-system
    git pull origin main
    composer install --no-dev --optimize-autoloader
    npm install
    npm run build
    php artisan migrate --force
    php artisan optimize
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    sudo supervisorctl restart hive-worker:*
@endtask
```

```bash
envoy run deploy
```

### Manual Zero-Downtime

```bash
# Put site in maintenance mode
php artisan down --render="errors::maintenance" --retry=60

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan optimize

# Restart queue workers
sudo supervisorctl restart hive-worker:*

# Restart Reverb
sudo supervisorctl restart hive-reverb

# Bring site back up
php artisan up
```

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] SSL certificate installed and HTTPS enforced
- [ ] Database credentials secured
- [ ] File permissions set correctly (755 for directories, 644 for files)
- [ ] Storage directories writable by web server
- [ ] `.env` not accessible via web server
- [ ] Queue workers running under `www-data` (not root)
- [ ] Regular backups configured
- [ ] Fail2ban configured for SSH
- [ ] Firewall configured (UFW or iptables)
- [ ] Regular security updates applied
