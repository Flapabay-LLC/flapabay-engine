#!/bin/bash

# Flapabay Staging Deployment Script
# Usage: ./deploy/deploy-staging.sh

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

log "Starting Flapabay Staging Deployment..."

# Check for required environment files
if [ ! -f ".env.staging" ]; then
    error ".env.staging file not found. Please create it with staging configuration."
fi

# Backup current environment
if [ -f ".env" ]; then
    log "Backing up current .env file..."
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
fi

# Copy staging environment
log "Setting up staging environment..."
cp .env.staging .env

# Source environment variables
if [ -f ".env" ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Validate critical environment variables
if [ -z "$DB_PASSWORD" ] || [ -z "$DB_ROOT_PASSWORD" ] || [ -z "$APP_KEY" ]; then
    error "Critical environment variables are missing. Check your .env.staging file."
fi

# Create SSL directory
log "Setting up SSL directory..."
mkdir -p docker/ssl/www

# Pull latest images
log "Pulling latest Docker images..."
docker-compose -f docker-compose.staging.yml pull

# Build application image
log "Building application image..."
docker-compose -f docker-compose.staging.yml build --no-cache

# Stop existing containers
log "Stopping existing containers..."
docker-compose -f docker-compose.staging.yml down --remove-orphans

# Start database and redis first
log "Starting database and Redis..."
docker-compose -f docker-compose.staging.yml up -d db redis

# Wait for database to be ready
log "Waiting for database to be ready..."
until docker-compose -f docker-compose.staging.yml exec -T db mysqladmin ping -h"localhost" --silent; do
    log "Waiting for database connection..."
    sleep 2
done

# Start application
log "Starting application..."
docker-compose -f docker-compose.staging.yml up -d app

# Wait for application to be ready
log "Waiting for application to be ready..."
sleep 10

# Run database migrations
log "Running database migrations..."
docker-compose -f docker-compose.staging.yml exec -T app php artisan migrate --force

# Seed database with test data
log "Seeding database with test data..."
docker-compose -f docker-compose.staging.yml exec -T app php artisan db:seed --force

# Clear and cache configurations
log "Optimizing application..."
docker-compose -f docker-compose.staging.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.staging.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.staging.yml exec -T app php artisan view:cache

# Setup SSL certificates
log "Setting up SSL certificates..."
if [ ! -f "docker/ssl/live/api.staging.flapabay.com/fullchain.pem" ]; then
    log "Obtaining SSL certificate..."
    docker-compose -f docker-compose.staging.yml run --rm certbot
    
    # Copy certificates to nginx ssl directory
    if [ -d "docker/ssl/live/api.staging.flapabay.com" ]; then
        cp docker/ssl/live/api.staging.flapabay.com/fullchain.pem docker/ssl/
        cp docker/ssl/live/api.staging.flapabay.com/privkey.pem docker/ssl/
    else
        warning "SSL certificate not found. Using self-signed certificate for now."
        # Generate self-signed certificate as fallback
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -keyout docker/ssl/privkey.pem \
            -out docker/ssl/fullchain.pem \
            -subj "/C=US/ST=State/L=City/O=Organization/CN=api.staging.flapabay.com"
    fi
else
    log "SSL certificates already exist."
fi

# Start nginx
log "Starting nginx..."
docker-compose -f docker-compose.staging.yml up -d nginx

# Health check
log "Performing health check..."
sleep 5

if curl -f -s http://localhost/health > /dev/null; then
    success "Application is running successfully!"
else
    error "Health check failed. Check the logs with: docker-compose -f docker-compose.staging.yml logs"
fi

# Display running containers
log "Deployment completed! Running containers:"
docker-compose -f docker-compose.staging.yml ps

success "Flapabay staging is now running!"
log "Access your application at: https://api.staging.flapabay.com"
log "Monitor logs with: docker-compose -f docker-compose.staging.yml logs -f"

log "Staging deployment completed successfully!"