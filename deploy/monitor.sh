#!/bin/bash

# Application Monitoring Script
# Usage: ./deploy/monitor.sh [production|staging]

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

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Ensure we're in the project directory
cd "$(dirname "$0")/.."

if [ "$ENVIRONMENT" = "production" ]; then
    COMPOSE_FILE="docker-compose.production.yml"
    DOMAIN="api.flapabay.com"
elif [ "$ENVIRONMENT" = "staging" ]; then
    COMPOSE_FILE="docker-compose.staging.yml"
    DOMAIN="api.staging.flapabay.com"
else
    error "Invalid environment. Use 'production' or 'staging'"
fi

log "Monitoring Flapabay $ENVIRONMENT environment..."

# Check container status
log "Checking container status..."
CONTAINERS=$(docker-compose -f $COMPOSE_FILE ps -q)
RUNNING_CONTAINERS=$(docker-compose -f $COMPOSE_FILE ps --services --filter "status=running")

echo "Running containers:"
for service in $RUNNING_CONTAINERS; do
    success "✓ $service"
done

# Check for stopped containers
STOPPED_CONTAINERS=$(docker-compose -f $COMPOSE_FILE ps --services --filter "status=exited")
if [ ! -z "$STOPPED_CONTAINERS" ]; then
    for service in $STOPPED_CONTAINERS; do
        error "✗ $service (stopped)"
    done
fi

# Health checks
log "Performing health checks..."

# HTTP health check
if curl -f -s -k https://$DOMAIN/health > /dev/null; then
    success "✓ HTTP health check passed"
else
    error "✗ HTTP health check failed"
fi

# Database health check
if docker-compose -f $COMPOSE_FILE exec -T app php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';" 2>/dev/null | grep -q "DB OK"; then
    success "✓ Database connection OK"
else
    error "✗ Database connection failed"
fi

# Redis health check
if docker-compose -f $COMPOSE_FILE exec -T redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
    success "✓ Redis connection OK"
else
    error "✗ Redis connection failed"
fi

# Disk usage check
log "Checking disk usage..."
DISK_USAGE=$(df -h / | awk 'NR==2{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    warning "Disk usage is high: ${DISK_USAGE}%"
else
    success "✓ Disk usage OK: ${DISK_USAGE}%"
fi

# Memory usage check
log "Checking memory usage..."
MEMORY_USAGE=$(free | grep Mem | awk '{printf("%.2f", $3/$2 * 100.0)}')
MEMORY_INT=${MEMORY_USAGE%.*}
if [ $MEMORY_INT -gt 80 ]; then
    warning "Memory usage is high: ${MEMORY_USAGE}%"
else
    success "✓ Memory usage OK: ${MEMORY_USAGE}%"
fi

# SSL certificate expiry check
log "Checking SSL certificate expiry..."
if [ -f "docker/ssl/fullchain.pem" ]; then
    EXPIRY_DATE=$(openssl x509 -enddate -noout -in docker/ssl/fullchain.pem | cut -d= -f2)
    EXPIRY_EPOCH=$(date -d "$EXPIRY_DATE" +%s)
    CURRENT_EPOCH=$(date +%s)
    DAYS_LEFT=$(( ($EXPIRY_EPOCH - $CURRENT_EPOCH) / 86400 ))
    
    if [ $DAYS_LEFT -lt 30 ]; then
        warning "SSL certificate expires in $DAYS_LEFT days"
    else
        success "✓ SSL certificate valid for $DAYS_LEFT days"
    fi
else
    warning "SSL certificate file not found"
fi

# Queue status check
log "Checking queue status..."
FAILED_JOBS=$(docker-compose -f $COMPOSE_FILE exec -T app php artisan queue:failed --format=json 2>/dev/null | jq length 2>/dev/null || echo "0")
if [ "$FAILED_JOBS" -gt 0 ]; then
    warning "$FAILED_JOBS failed jobs in queue"
else
    success "✓ No failed jobs in queue"
fi

# Log file sizes check
log "Checking log file sizes..."
LOG_SIZE=$(docker-compose -f $COMPOSE_FILE exec -T app du -sh storage/logs 2>/dev/null | cut -f1 || echo "0K")
success "Log directory size: $LOG_SIZE"

# Recent errors check
log "Checking for recent errors..."
ERROR_COUNT=$(docker-compose -f $COMPOSE_FILE exec -T app grep -c "ERROR\|CRITICAL" storage/logs/laravel.log 2>/dev/null | tail -1 || echo "0")
if [ "$ERROR_COUNT" -gt 10 ]; then
    warning "$ERROR_COUNT errors found in recent logs"
else
    success "✓ Error count acceptable: $ERROR_COUNT"
fi

log "Monitoring completed for $ENVIRONMENT environment"

# Summary
echo
echo "=== MONITORING SUMMARY ==="
echo "Environment: $ENVIRONMENT"
echo "Domain: $DOMAIN"
echo "Disk Usage: ${DISK_USAGE}%"
echo "Memory Usage: ${MEMORY_USAGE}%"
echo "Failed Jobs: $FAILED_JOBS"
echo "Log Size: $LOG_SIZE"
echo "Recent Errors: $ERROR_COUNT"
echo "=========================="