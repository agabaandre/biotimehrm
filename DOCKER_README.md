# Docker Setup for Attendance Management System

This document explains how to set up and run the Attendance Management System using Docker.

## Prerequisites

- Docker (version 20.10 or higher)
- Docker Compose (version 2.0 or higher)
- At least 4GB of available RAM
- At least 10GB of available disk space
- MySQL/MariaDB database (local or remote)

## Quick Start

1. **Clone the repository and navigate to the project directory:**
   ```bash
   cd /path/to/attend
   ```

2. **Copy the environment file:**
   ```bash
   cp docker/env.example docker/.env
   ```

3. **Edit the environment file with your database credentials:**
   ```bash
   nano docker/.env
   ```
   
   **Important Database Configuration:**
   - **If database is on the host machine**: Use `DB_HOST=host.docker.internal`
   - **If database is remote**: Use the actual IP/hostname
   - Update `DB_NAME`, `DB_USER`, and `DB_PASS` with your actual credentials

4. **Build and start the containers:**
   ```bash
   docker-compose up -d --build
   ```

5. **Wait for all services to start (this may take a few minutes on first run):**
   ```bash
   docker-compose logs -f
   ```

6. **Access the application:**
   - Web Application: http://localhost:8080
   - Redis: localhost:6379 (if enabled)

## Services Overview

### 1. PHP-FPM Service (`php`)
- **Base**: PHP 8.1 with FPM
- **Port**: 9000 (internal)
- **Extensions**: All required PHP extensions for CodeIgniter
- **Features**: OPcache, Redis, Memcached, APCu
- **Database**: Connects to external MySQL database

### 2. Web Server (`nginx`)
- **Base**: Nginx 1.25 Alpine
- **Port**: 8080 (HTTP), 8443 (HTTPS)
- **Features**: FastCGI, Gzip, Security headers, Rate limiting

### 3. Redis Service (`redis`)
- **Image**: Redis 7 Alpine
- **Port**: 6379
- **Purpose**: Caching and session storage

### 4. Cron Service (`cron`)
- **Base**: PHP 8.1 CLI
- **Purpose**: Background job processing
- **Features**: Supervisor-managed cron daemon
- **Database**: Connects to external MySQL database

## Database Connection

### Host Machine Database
If your MySQL database is running on the same machine as Docker:

```bash
# In docker/.env
DB_HOST=host.docker.internal
DB_NAME=attend
DB_USER=your_db_user
DB_PASS=your_db_password
DB_PORT=3306
```

**Note**: `host.docker.internal` is a special DNS name that Docker provides to resolve to the host machine's IP address.

### Remote Database
If your MySQL database is on a different server:

```bash
# In docker/.env
DB_HOST=192.168.1.100  # or your-db-server.com
DB_NAME=attend
DB_USER=your_db_user
DB_PASS=your_db_password
DB_PORT=3306
```

### Testing Database Connection
To test if Docker containers can reach your database:

```bash
# Test from PHP container
docker-compose exec php mysql -h host.docker.internal -u your_user -p your_database

# Test from cron container
docker-compose exec cron mysql -h host.docker.internal -u your_user -p your_database
```

## Configuration Files

### PHP Configuration
- `docker/php/php.ini` - Main PHP configuration
- `docker/php/php-fpm.conf` - PHP-FPM pool configuration
- `docker/php/opcache.ini` - OPcache optimization settings

### Nginx Configuration
- `docker/nginx/nginx.conf` - Main Nginx configuration
- `docker/nginx/default.conf` - Server block configuration

### Supervisor Configuration
- `docker/supervisor/cron.conf` - Cron service management

## CodeIgniter Configuration

Update your `application/config/database.php` to use environment variables:

```php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => getenv('DB_HOST') ?: 'host.docker.internal',
    'username' => getenv('DB_USER') ?: 'your_db_user',
    'password' => getenv('DB_PASS') ?: 'your_db_password',
    'database' => getenv('DB_NAME') ?: 'attend',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
```

## Cron Jobs

The system includes automated cron jobs for:
- **Duty Roster Summary**: Runs daily at 2:00 AM
- **Custom cron jobs**: Can be added via the cron service

To manually trigger a cron job:
```bash
docker-compose exec cron php index.php cronjobs/DutyRosterSummaryCron/updateDutyRosterSummary
```

## File Permissions

The Docker setup automatically sets proper permissions for:
- `/var/www/html/uploads` - File uploads (777)
- `/var/www/html/logs` - Application logs (777)
- `/var/www/html/cache` - Cache files (777)
- `/var/www/html/temp` - Temporary files (777)

## Monitoring and Logs

### View logs for specific services:
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f cron
```

### Check service status:
```bash
docker-compose ps
```

### Access container shell:
```bash
docker-compose exec php bash
docker-compose exec cron bash
```

## Performance Optimization

### PHP-FPM Settings
- **Max Children**: 50
- **Start Servers**: 5
- **Min Spare Servers**: 5
- **Max Spare Servers**: 35

### OPcache Settings
- **Memory Consumption**: 128MB
- **Max Files**: 4000
- **Revalidation**: Every 2 seconds

### Nginx Settings
- **Worker Connections**: 1024
- **Gzip Compression**: Enabled
- **Static File Caching**: 1 year for assets

## Security Features

- **Rate Limiting**: Login (10 req/min), API (100 req/min)
- **Security Headers**: X-Frame-Options, XSS Protection, etc.
- **File Access Control**: Denied access to sensitive directories
- **PHP Security**: Disabled dangerous functions and features

## Troubleshooting

### Common Issues

1. **Database connection failed**: 
   - Verify `host.docker.internal` resolves correctly
   - Check firewall settings on host machine
   - Ensure MySQL is configured to accept connections from Docker network

2. **Port conflicts**: Ensure ports 8080, 8443, and 6379 are available
3. **Permission errors**: Check file ownership and permissions
4. **Memory issues**: Increase Docker memory allocation

### Database Connection Issues

If containers can't reach the host database:

```bash
# Check if host.docker.internal resolves
docker-compose exec php nslookup host.docker.internal

# Test network connectivity
docker-compose exec php ping host.docker.internal

# Check MySQL is listening on all interfaces
# In your MySQL config, ensure bind-address = 0.0.0.0
```

### Reset Everything
```bash
# Stop and remove all containers
docker-compose down

# Remove all volumes (WARNING: This will delete all data)
docker-compose down -v

# Rebuild and start
docker-compose up -d --build
```

### Health Checks
- **Web Application**: http://localhost:8080/health
- **PHP-FPM**: `docker-compose exec php php-fpm -t`

## Development vs Production

### Development
- Set `APP_DEBUG=true` in environment
- Enable error display
- Lower security restrictions

### Production
- Set `APP_DEBUG=false`
- Disable error display
- Enable all security features
- Use HTTPS
- Configure proper logging

## Backup and Restore

### Database Backup
```bash
# From host machine
mysqldump -h localhost -u your_user -p your_database > backup.sql

# From Docker container
docker-compose exec php mysqldump -h host.docker.internal -u your_user -p your_database > backup.sql
```

### Database Restore
```bash
# From host machine
mysql -h localhost -u your_user -p your_database < backup.sql

# From Docker container
docker-compose exec -T php mysql -h host.docker.internal -u your_user -p your_database < backup.sql
```

### File Backup
```bash
tar -czf uploads_backup.tar.gz uploads/
tar -czf logs_backup.tar.gz logs/
```

## Support

For issues related to:
- **Docker setup**: Check this README and Docker logs
- **Application**: Check application logs in `logs/` directory
- **Database connection**: Verify network connectivity and credentials

## License

This Docker setup is part of the Attendance Management System project.
