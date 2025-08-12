#!/bin/bash

# SSL Certificate Renewal Script
# Usage: ./deploy/ssl-renew.sh [production|staging]

set -e

ENVIRONMENT=${1:-production}

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Ensure we're in the project directory
cd "$(dirname "$0")/.."

if [ "$ENVIRONMENT" = "production" ]; then
    COMPOSE_FILE="docker-compose.production.yml"
    NGINX_CONTAINER="flapabay_nginx_prod"
    DOMAIN="api.flapabay.com"
elif [ "$ENVIRONMENT" = "staging" ]; then
    COMPOSE_FILE="docker-compose.staging.yml"
    NGINX_CONTAINER="flapabay_nginx_staging"
    DOMAIN="api.staging.flapabay.com"
else
    error "Invalid environment. Use 'production' or 'staging'"
fi

log "Renewing SSL certificate for $ENVIRONMENT environment..."

# Renew certificate
log "Running certbot renewal..."
docker-compose -f $COMPOSE_FILE run --rm certbot renew

# Check if renewal was successful
if [ -f "docker/ssl/live/$DOMAIN/fullchain.pem" ]; then
    log "Copying renewed certificates..."
    cp docker/ssl/live/$DOMAIN/fullchain.pem docker/ssl/
    cp docker/ssl/live/$DOMAIN/privkey.pem docker/ssl/
    
    log "Reloading nginx configuration..."
    docker exec $NGINX_CONTAINER nginx -s reload
    
    success "SSL certificate renewed successfully for $DOMAIN"
else
    error "SSL certificate renewal failed"
fi

log "SSL renewal process completed."