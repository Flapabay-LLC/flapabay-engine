#!/bin/bash

# Database and Application Backup Script
# Usage: ./deploy/backup.sh [production|staging]

set -e

ENVIRONMENT=${1:-production}
BACKUP_DIR="/backups/flapabay"
DATE=$(date +%Y%m%d_%H%M%S)

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
    DB_CONTAINER="flapabay_db_prod"
    DB_NAME="flapabay_prod"
elif [ "$ENVIRONMENT" = "staging" ]; then
    COMPOSE_FILE="docker-compose.staging.yml"
    DB_CONTAINER="flapabay_db_staging"
    DB_NAME="flapabay_staging"
else
    error "Invalid environment. Use 'production' or 'staging'"
fi

# Create backup directory
mkdir -p $BACKUP_DIR/$ENVIRONMENT

log "Starting backup for $ENVIRONMENT environment..."

# Source environment variables
if [ -f ".env.$ENVIRONMENT" ]; then
    export $(cat .env.$ENVIRONMENT | grep -v '^#' | xargs)
fi

# Database backup
log "Backing up database..."
docker-compose -f $COMPOSE_FILE exec -T db mysqldump \
    -u $DB_USERNAME \
    -p$DB_PASSWORD \
    $DB_NAME > $BACKUP_DIR/$ENVIRONMENT/database_${DATE}.sql

# Compress database backup
gzip $BACKUP_DIR/$ENVIRONMENT/database_${DATE}.sql

# Application files backup (storage directory)
log "Backing up application files..."
tar -czf $BACKUP_DIR/$ENVIRONMENT/storage_${DATE}.tar.gz \
    storage/app \
    storage/logs

# Environment files backup
log "Backing up environment configuration..."
cp .env.$ENVIRONMENT $BACKUP_DIR/$ENVIRONMENT/env_${DATE}

# SSL certificates backup
if [ -d "docker/ssl" ]; then
    log "Backing up SSL certificates..."
    tar -czf $BACKUP_DIR/$ENVIRONMENT/ssl_${DATE}.tar.gz docker/ssl
fi

# Clean old backups (keep last 7 days)
log "Cleaning old backups..."
find $BACKUP_DIR/$ENVIRONMENT -name "*.sql.gz" -mtime +7 -delete
find $BACKUP_DIR/$ENVIRONMENT -name "*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR/$ENVIRONMENT -name "env_*" -mtime +7 -delete

success "Backup completed successfully!"
log "Backup files stored in: $BACKUP_DIR/$ENVIRONMENT"
ls -la $BACKUP_DIR/$ENVIRONMENT/*${DATE}*