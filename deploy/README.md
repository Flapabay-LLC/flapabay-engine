# Flapabay Engine Deployment Guide

This guide provides comprehensive instructions for deploying the Flapabay Engine using Docker in both staging and production environments.

## Prerequisites

- Docker and Docker Compose installed
- Domain names configured (api.flapabay.com for production, api.staging.flapabay.com for staging)
- SSH access to your server
- Valid SSL certificates or ability to generate them

## Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd flapabay-engine
   ```

2. **Set up environment files**
   ```bash
   # For production
   cp .env.production.example .env.production
   # For staging
   cp .env.staging.example .env.staging
   ```

3. **Configure environment variables**
   Edit the environment files with your actual configuration values.

4. **Deploy**
   ```bash
   # Production deployment
   ./deploy/deploy-production.sh
   
   # Staging deployment
   ./deploy/deploy-staging.sh
   ```

## Environment Configuration

### Critical Environment Variables

Ensure the following variables are properly configured in your `.env.production` and `.env.staging` files:

- `APP_KEY`: Generate with `php artisan key:generate`
- `DB_PASSWORD` and `DB_ROOT_PASSWORD`: Strong database passwords
- `REDIS_PASSWORD`: Strong Redis password
- `JWT_SECRET`: Strong JWT secret
- `STRIPE_SECRET`: Your Stripe API keys (live for production, test for staging)
- `REVERB_APP_KEY` and `REVERB_APP_SECRET`: Generate unique keys

### Domain Configuration

- **Production**: `https://api.flapabay.com`
- **Staging**: `https://api.staging.flapabay.com`

## Deployment Scripts

### Production Deployment
```bash
./deploy/deploy-production.sh
```

This script will:
- Set up production environment
- Build and deploy Docker containers
- Configure SSL certificates
- Run database migrations
- Optimize Laravel caches
- Perform health checks

### Staging Deployment
```bash
./deploy/deploy-staging.sh
```

Similar to production but includes:
- Test data seeding
- Debug mode enabled
- Less strict security headers

### SSL Certificate Renewal
```bash
./deploy/ssl-renew.sh production
./deploy/ssl-renew.sh staging
```

### Backup System
```bash
./deploy/backup.sh production
./deploy/backup.sh staging
```

Creates backups of:
- Database
- Storage directories
- Environment files
- SSL certificates

### Monitoring
```bash
./deploy/monitor.sh production
./deploy/monitor.sh staging
```

Checks:
- Container health
- HTTP endpoints
- Database connectivity
- SSL certificate expiry
- System resources
- Queue status

## Architecture Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Nginx       │    │   Laravel App   │    │     MySQL       │
│   (Port 80/443) │────│   (Port 9000)   │────│   (Port 3306)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                               │
                       ┌─────────────────┐
                       │     Redis       │
                       │   (Port 6379)   │
                       └─────────────────┘
```

## Services

### Application (Laravel)
- **Container**: `flapabay_app_[env]`
- **Services**: PHP-FPM, Queue Workers, Laravel Reverb
- **Port**: 9000 (internal)
- **Health**: `/health` endpoint

### Web Server (Nginx)
- **Container**: `flapabay_nginx_[env]`
- **Ports**: 80 (HTTP), 443 (HTTPS)
- **SSL**: Let's Encrypt certificates
- **Features**: Gzip compression, static file caching, WebSocket proxy

### Database (MySQL)
- **Container**: `flapabay_db_[env]`
- **Port**: 3306 (production), 3307 (staging)
- **Storage**: Persistent volumes
- **Backups**: Automated daily backups

### Cache (Redis)
- **Container**: `flapabay_redis_[env]`
- **Port**: 6379 (internal)
- **Usage**: Sessions, cache, queues
- **Persistence**: AOF enabled

## Real-time Features

Laravel Reverb provides WebSocket functionality:
- **Internal Port**: 8080
- **External Access**: WSS via nginx proxy
- **Endpoint**: `/app/` on your domain

## Monitoring and Maintenance

### Log Management
- Application logs: `storage/logs/`
- Nginx logs: `/var/log/nginx/`
- Container logs: `docker-compose logs`

### Health Checks
- HTTP: `curl https://api.flapabay.com/health`
- Database: Built into deployment scripts
- SSL: Certificate expiry monitoring

### Backup Strategy
- **Frequency**: Daily automated backups
- **Retention**: 7 days
- **Location**: `/backups/flapabay/`
- **Contents**: Database, storage, configs, SSL

### SSL Certificate Management
- **Provider**: Let's Encrypt
- **Renewal**: Automated via cron
- **Fallback**: Self-signed certificates
- **Monitoring**: Expiry alerts

## Troubleshooting

### Common Issues

1. **SSL Certificate Errors**
   ```bash
   # Check certificate status
   openssl x509 -text -noout -in docker/ssl/fullchain.pem
   
   # Renew certificates
   ./deploy/ssl-renew.sh production
   ```

2. **Database Connection Issues**
   ```bash
   # Check database container
   docker-compose -f docker-compose.production.yml logs db
   
   # Test connection
   docker-compose -f docker-compose.production.yml exec app php artisan tinker
   ```

3. **Queue Not Processing**
   ```bash
   # Check queue workers
   docker-compose -f docker-compose.production.yml logs app
   
   # Restart queue workers
   docker-compose -f docker-compose.production.yml restart app
   ```

### Performance Optimization

1. **Database Optimization**
   - Configure MySQL buffer pools
   - Index optimization
   - Query monitoring

2. **Cache Strategy**
   - Redis for sessions and cache
   - OpCache for PHP
   - Nginx static file caching

3. **WebSocket Performance**
   - Reverb connection limits
   - Load balancing considerations

## Security Considerations

- Strong passwords for all services
- SSL/TLS encryption
- Security headers in nginx
- Regular security updates
- Database access restrictions
- Environment variable protection

## Scaling Considerations

For high-traffic scenarios:
- Load balancer for multiple app instances
- Database read replicas
- Redis clustering
- CDN for static assets
- Container orchestration (Kubernetes)

## Support

For deployment issues:
1. Check logs: `docker-compose logs`
2. Run monitoring: `./deploy/monitor.sh`
3. Verify configuration files
4. Check domain DNS settings
5. Validate SSL certificates