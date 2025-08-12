# Flapabay Engine - Local Development with Docker

This guide provides comprehensive instructions for setting up and testing the Flapabay Engine locally using Docker containers before deploying to staging or production.

## Prerequisites

- Docker Desktop installed and running
- Docker Compose (usually included with Docker Desktop)
- Git
- At least 4GB of available RAM
- At least 10GB of available disk space

## Quick Start

1. **Clone and navigate to the project**
   ```bash
   git clone <repository-url>
   cd flapabay-engine
   ```

2. **Run the local setup script**
   ```bash
   ./deploy/local-setup.sh
   ```

3. **Access your application**
   - Application: http://localhost
   - MailHog (Email testing): http://localhost:8025
   - phpMyAdmin: http://localhost:8081
   - Redis Commander: http://localhost:8082
   - MySQL Database: localhost:33060

## Manual Setup (Alternative)

If you prefer to set up manually or need to customize the process:

### 1. Environment Configuration

```bash
# Copy the local environment template
cp .env.local.example .env.local

# Edit the configuration (optional, defaults work for most cases)
nano .env.local

# Copy for Docker
cp .env.local .env
```

### 2. Build and Start Services

```bash
# Build the application image
docker-compose -f docker-compose.local.yml build

# Start all services
docker-compose -f docker-compose.local.yml up -d

# Wait for services to be ready, then run setup commands
docker-compose -f docker-compose.local.yml exec app php artisan migrate --seed
docker-compose -f docker-compose.local.yml exec app npm run build
```

## Local Development Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Nginx       │    │   Laravel App   │    │     MySQL       │
│  localhost:80   │────│   (Port 9000)   │────│ localhost:33060 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
        │                       │                       
┌─────────────────┐    ┌─────────────────┐              
│     Redis       │    │    MailHog      │              
│ localhost:6379  │    │ localhost:8025  │              
└─────────────────┘    └─────────────────┘              
        │                       
┌─────────────────┐    ┌─────────────────┐    
│  Redis Comm.    │    │   phpMyAdmin    │    
│ localhost:8082  │    │ localhost:8081  │    
└─────────────────┘    └─────────────────┘    
```

## Services Overview

### Application Container (`flapabay_app_local`)
- **PHP 8.2-FPM** with Laravel
- **Supervisor** managing PHP-FPM, Queue workers, and Reverb
- **Live code reloading** via volume mounts
- **Port**: 9000 (internal)

### Web Server (`flapabay_nginx_local`)
- **Nginx** with optimized configuration for development
- **No SSL** (HTTP only for local development)
- **Static file serving** without caching
- **Port**: 80

### Database (`flapabay_db_local`)
- **MySQL 8.0** with UTF8MB4 support
- **Test database** automatically created
- **Persistent data** via Docker volumes
- **Port**: 33060 (mapped from internal 3306 to avoid conflicts)

### Cache (`flapabay_redis_local`)
- **Redis 7** for sessions, cache, and queues
- **No password** for local development
- **Persistent data** via Docker volumes
- **Port**: 6379

### Development Tools

#### MailHog (`flapabay_mailhog_local`)
- **Email testing** - captures all outgoing emails
- **Web interface**: http://localhost:8025
- **SMTP**: localhost:1025

#### phpMyAdmin (`flapabay_phpmyadmin_local`)
- **Database management** interface
- **Web interface**: http://localhost:8081
- **Auto-login** with configured credentials

#### Redis Commander (`flapabay_redis_commander_local`)
- **Redis management** interface
- **Web interface**: http://localhost:8082
- **View/edit** Redis keys and values

## Development Workflow

### Daily Development

```bash
# Start the environment
docker-compose -f docker-compose.local.yml up -d

# View logs (optional)
docker-compose -f docker-compose.local.yml logs -f

# Stop the environment
docker-compose -f docker-compose.local.yml down
```

### Running Artisan Commands

```bash
# Any Laravel artisan command
docker-compose -f docker-compose.local.yml exec app php artisan [command]

# Examples:
docker-compose -f docker-compose.local.yml exec app php artisan migrate
docker-compose -f docker-compose.local.yml exec app php artisan tinker
docker-compose -f docker-compose.local.yml exec app php artisan queue:work
```

### Running Tests

```bash
# Run all tests
docker-compose -f docker-compose.local.yml exec app php artisan test

# Run specific test file
docker-compose -f docker-compose.local.yml exec app php artisan test tests/Feature/ExampleTest.php

# Run with coverage
docker-compose -f docker-compose.local.yml exec app php artisan test --coverage
```

### Managing Dependencies

```bash
# Install new Composer package
docker-compose -f docker-compose.local.yml exec app composer require vendor/package

# Install new NPM package
docker-compose -f docker-compose.local.yml exec app npm install package-name

# Update dependencies
docker-compose -f docker-compose.local.yml exec app composer update
docker-compose -f docker-compose.local.yml exec app npm update
```

### Frontend Development

```bash
# Build assets for development
docker-compose -f docker-compose.local.yml exec app npm run dev

# Build assets for production testing
docker-compose -f docker-compose.local.yml exec app npm run build

# Watch for changes (if supported)
docker-compose -f docker-compose.local.yml exec app npm run watch
```

## Testing Scripts

### Comprehensive Testing

```bash
# Run all local environment tests
./deploy/local-test.sh
```

This script tests:
- HTTP health endpoints
- Database connectivity
- Redis connectivity
- WebSocket functionality
- Queue system
- File permissions
- Email system (MailHog)
- All management interfaces

### Custom Testing

```bash
# Test specific API endpoints
curl http://localhost/api/v1/testing

# Test WebSocket connection
wscat -c ws://localhost:8080/app/local-reverb-key

# Test email functionality (check MailHog interface)
docker-compose -f docker-compose.local.yml exec app php artisan tinker
>>> Mail::raw('Test email', function($m) { $m->to('test@example.com')->subject('Test'); })
```

## Debugging and Troubleshooting

### Viewing Logs

```bash
# All services logs
docker-compose -f docker-compose.local.yml logs -f

# Specific service logs
docker-compose -f docker-compose.local.yml logs -f app
docker-compose -f docker-compose.local.yml logs -f nginx
docker-compose -f docker-compose.local.yml logs -f db

# Laravel application logs
docker-compose -f docker-compose.local.yml exec app tail -f storage/logs/laravel.log
```

### Accessing Containers

```bash
# Access application container
docker-compose -f docker-compose.local.yml exec app bash

# Access database container
docker-compose -f docker-compose.local.yml exec db mysql -u flapabay_user -p flapabay_local

# Access Redis container
docker-compose -f docker-compose.local.yml exec redis redis-cli
```

### Common Issues and Solutions

#### 1. Port Already in Use
```bash
# Run the port checker script
./deploy/check-ports.sh

# Manual check for specific ports
lsof -i :80
lsof -i :33060

# Stop conflicting services
brew services stop mysql  # If you have local MySQL
brew services stop redis  # If you have local Redis
brew services stop nginx  # If you have local Nginx
```

#### 2. Permission Issues
```bash
# Fix storage permissions
docker-compose -f docker-compose.local.yml exec app chmod -R 775 storage bootstrap/cache
```

#### 3. Database Connection Issues
```bash
# Reset database
docker-compose -f docker-compose.local.yml down -v
docker-compose -f docker-compose.local.yml up -d db
# Wait for database to start, then run migrations
```

#### 4. Asset Build Issues
```bash
# Clear node modules and reinstall
docker-compose -f docker-compose.local.yml exec app rm -rf node_modules package-lock.json
docker-compose -f docker-compose.local.yml exec app npm install
```

#### 5. Apple Silicon (ARM64) Platform Warnings
If you see platform warnings on Apple Silicon Macs, they're usually harmless but you can fix them:

```bash
# Option 1: Set platform in Docker Compose (already done in our config)
# The docker-compose.local.yml already includes platform: linux/amd64

# Option 2: Set Docker default platform (if you want to avoid warnings globally)
export DOCKER_DEFAULT_PLATFORM=linux/amd64

# Option 3: Use ARM64 compatible alternatives (future improvement)
# Some images have ARM64 versions available
```

**Note**: The warnings don't affect functionality, but the containers may run slightly slower on Apple Silicon due to emulation.

### Performance Monitoring

```bash
# Monitor container resources
docker stats

# Monitor specific containers
docker stats $(docker-compose -f docker-compose.local.yml ps -q)

# Check disk usage
docker system df
```

## Environment Configuration Details

### Key Environment Variables

| Variable | Local Value | Description |
|----------|-------------|-------------|
| `APP_ENV` | `local` | Application environment |
| `APP_DEBUG` | `true` | Enable debug mode |
| `DB_HOST` | `db` | Database host (container name) |
| `REDIS_HOST` | `redis` | Redis host (container name) |
| `MAIL_HOST` | `mailhog` | Email host for testing |
| `BROADCAST_CONNECTION` | `reverb` | WebSocket driver |

### Development Features Enabled

- **Debug mode** with detailed error messages
- **Query logging** for database optimization
- **Hot reloading** for code changes
- **Email interception** via MailHog
- **Redis inspection** via Redis Commander
- **Database management** via phpMyAdmin

## Data Management

### Backup Local Data

```bash
# Export database
docker-compose -f docker-compose.local.yml exec db mysqldump -u flapabay_user -pflapabay_local flapabay_local > backup.sql

# Export Redis data
docker-compose -f docker-compose.local.yml exec redis redis-cli BGSAVE
```

### Reset Environment

```bash
# Soft reset (keeps images)
docker-compose -f docker-compose.local.yml down -v
./deploy/local-setup.sh

# Full reset (removes everything)
./deploy/local-clean.sh --force
./deploy/local-setup.sh
```

## Integration with Production Deployment

### Testing Production-like Features

1. **SSL Testing**: Use ngrok or similar to test HTTPS locally
2. **Queue Testing**: Ensure queue workers process jobs correctly
3. **WebSocket Testing**: Test real-time features work properly
4. **Email Testing**: Verify email templates render correctly in MailHog

### Pre-deployment Checklist

- [ ] All tests pass (`./deploy/local-test.sh`)
- [ ] Database migrations run without errors
- [ ] Queue jobs process correctly
- [ ] WebSocket connections work
- [ ] Frontend assets build successfully
- [ ] API endpoints respond correctly
- [ ] Email functionality works (check MailHog)

## Cleanup

### Regular Cleanup

```bash
# Stop services and remove containers
docker-compose -f docker-compose.local.yml down

# Remove unused Docker resources
docker system prune
```

### Complete Cleanup

```bash
# Remove everything including data
./deploy/local-clean.sh

# Force cleanup (removes dependencies too)
./deploy/local-clean.sh --force
```

## Tips for Efficient Local Development

1. **Use volume mounts**: Code changes reflect immediately without rebuilding
2. **Monitor logs**: Keep log windows open for real-time debugging
3. **Use MailHog**: Test email functionality without sending real emails
4. **Database GUI**: Use phpMyAdmin for quick database inspection
5. **Redis monitoring**: Use Redis Commander to debug cache/session issues
6. **Health checks**: Regularly run `./deploy/local-test.sh` to catch issues early

## Support

If you encounter issues with the local development setup:

1. **Check logs**: `docker-compose -f docker-compose.local.yml logs -f`
2. **Run tests**: `./deploy/local-test.sh`
3. **Clean restart**: `./deploy/local-clean.sh && ./deploy/local-setup.sh`
4. **Check resources**: Ensure Docker has enough RAM/disk space allocated
5. **Update Docker**: Make sure you're running a recent version of Docker Desktop