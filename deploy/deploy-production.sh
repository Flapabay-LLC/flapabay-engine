#!/bin/bash

# Flapabay Production Deployment Script
# Usage: ./deploy/deploy-production.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then
  error "Please do not run this script as root"
fi

# Ensure we're in the project directory
cd "$(dirname "$0")/.."

log "Starting Flapabay Production Deployment..."

# Check for required environment files
if [ ! -f ".env.production" ]; then
    error ".env.production file not found. Please create it with production configuration."
fi

# Backup current environment
if [ -f ".env" ]; then
    log "Backing up current .env file..."
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
fi

# Copy production environment
log "Setting up production environment..."
cp .env.production .env

# Source environment variables
if [ -f ".env" ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Validate critical environment variables
if [ -z "$DB_PASSWORD" ] || [ -z "$DB_ROOT_PASSWORD" ] || [ -z "$APP_KEY" ]; then
    error "Critical environment variables are missing. Check your .env.production file."
fi

# Create SSL directory
log "Setting up SSL directory..."
mkdir -p docker/ssl/www

# Pull latest images
log "Pulling latest Docker images..."
docker-compose -f docker-compose.production.yml pull

# Build application image
log "Building application image..."
docker-compose -f docker-compose.production.yml build --no-cache

# Stop existing containers
log "Stopping existing containers..."
docker-compose -f docker-compose.production.yml down --remove-orphans

# Start database and redis first
log "Starting database and Redis..."
docker-compose -f docker-compose.production.yml up -d db redis

# Wait for database to be ready
log "Waiting for database to be ready..."
until docker-compose -f docker-compose.production.yml exec -T db mysqladmin ping -h"localhost" --silent; do
    log "Waiting for database connection..."
    sleep 2
done

# Start application
log "Starting application..."
docker-compose -f docker-compose.production.yml up -d app

# Wait for application to be ready
log "Waiting for application to be ready..."
sleep 10

# Run database migrations
log "Running database migrations..."
docker-compose -f docker-compose.production.yml exec -T app php artisan migrate --force

# Seed database if needed (uncomment if you want to seed in production)
# log "Seeding database..."
# docker-compose -f docker-compose.production.yml exec -T app php artisan db:seed --force

# Clear and cache configurations
log "Optimizing application..."
docker-compose -f docker-compose.production.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.production.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.production.yml exec -T app php artisan view:cache

# Setup SSL certificates
log "Setting up SSL certificates..."
if [ ! -f "docker/ssl/live/api.flapabay.com/fullchain.pem" ]; then
    log "Obtaining SSL certificate..."
    docker-compose -f docker-compose.production.yml run --rm certbot
    
    # Copy certificates to nginx ssl directory
    if [ -d "docker/ssl/live/api.flapabay.com" ]; then
        cp docker/ssl/live/api.flapabay.com/fullchain.pem docker/ssl/
        cp docker/ssl/live/api.flapabay.com/privkey.pem docker/ssl/
    else
        warning "SSL certificate not found. Using self-signed certificate for now."
        # Generate self-signed certificate as fallback
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -keyout docker/ssl/privkey.pem \
            -out docker/ssl/fullchain.pem \
            -subj "/C=US/ST=State/L=City/O=Organization/CN=api.flapabay.com"
    fi
else
    log "SSL certificates already exist."
fi

# Start nginx
log "Starting nginx..."
docker-compose -f docker-compose.production.yml up -d nginx

# Health check
log "Performing health check..."
sleep 5

if curl -f -s http://localhost/health > /dev/null; then
    success "Application is running successfully!"
else
    error "Health check failed. Check the logs with: docker-compose -f docker-compose.production.yml logs"
fi

# Display running containers
log "Deployment completed! Running containers:"
docker-compose -f docker-compose.production.yml ps

success "Flapabay is now running in production mode!"
log "Access your application at: https://api.flapabay.com"
log "Monitor logs with: docker-compose -f docker-compose.production.yml logs -f"

# Setup log rotation (optional)
log "Setting up log rotation..."
cat > /tmp/flapabay-logrotate << EOF
/var/log/nginx/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        docker kill -s USR1 flapabay_nginx_prod 2>/dev/null || true
    endscript
}
EOF

if [ -w "/etc/logrotate.d" ]; then
    sudo mv /tmp/flapabay-logrotate /etc/logrotate.d/flapabay
    log "Log rotation configured."
else
    warning "Could not configure log rotation. Manual setup required."
fi

log "Production deployment completed successfully!"