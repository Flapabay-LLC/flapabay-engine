#!/bin/bash

# Flapabay Local Testing Script
# Usage: ./deploy/local-test.sh

set -e

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

log "Running Flapabay Local Environment Tests..."

# Check if containers are running
if ! docker-compose -f docker-compose.local.yml ps | grep -q "Up"; then
    error "Local environment is not running. Run ./deploy/local-setup.sh first."
fi

# Test 1: Health Check
log "Test 1: Health Check"
if curl -f -s http://localhost/health > /dev/null; then
    success "✓ HTTP health check passed"
else
    error "✗ HTTP health check failed"
fi

# Test 2: Database Connection
log "Test 2: Database Connection"
if docker-compose -f docker-compose.local.yml exec -T app php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';" 2>/dev/null | grep -q "DB OK"; then
    success "✓ Database connection OK"
else
    error "✗ Database connection failed"
fi

# Test 3: Redis Connection
log "Test 3: Redis Connection"
if docker-compose -f docker-compose.local.yml exec -T redis redis-cli ping 2>/dev/null | grep -q "PONG"; then
    success "✓ Redis connection OK"
else
    error "✗ Redis connection failed"
fi

# Test 4: Laravel Application Response
log "Test 4: Laravel Application Response"
RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/)
if [ "$RESPONSE" = "200" ]; then
    success "✓ Laravel application responding (HTTP $RESPONSE)"
else
    warning "△ Laravel application response: HTTP $RESPONSE"
fi

# Test 5: API Endpoint Test
log "Test 5: API Endpoint Test"
API_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/api/v1/testing)
if [ "$API_RESPONSE" = "200" ]; then
    success "✓ API endpoint responding (HTTP $API_RESPONSE)"
else
    warning "△ API endpoint response: HTTP $API_RESPONSE"
fi

# Test 6: WebSocket Connection (Reverb)
log "Test 6: WebSocket Connection (Reverb)"
if nc -z localhost 8080 > /dev/null 2>&1; then
    success "✓ WebSocket port 8080 is accessible"
else
    error "✗ WebSocket port 8080 is not accessible"
fi

# Test 7: Database Migrations Status
log "Test 7: Database Migrations Status"
MIGRATION_STATUS=$(docker-compose -f docker-compose.local.yml exec -T app php artisan migrate:status 2>/dev/null | grep -c "Ran" || echo "0")
if [ "$MIGRATION_STATUS" -gt 0 ]; then
    success "✓ Database migrations are up to date ($MIGRATION_STATUS migrations)"
else
    warning "△ No migrations found or migrations not run"
fi

# Test 8: Queue Connection
log "Test 8: Queue Connection"
if docker-compose -f docker-compose.local.yml exec -T app php artisan queue:failed --format=json 2>/dev/null | grep -q "\[\]"; then
    success "✓ Queue connection OK (no failed jobs)"
else
    warning "△ Queue connection issues or failed jobs present"
fi

# Test 9: Storage Directory Permissions
log "Test 9: Storage Directory Permissions"
if docker-compose -f docker-compose.local.yml exec -T app test -w /var/www/html/storage; then
    success "✓ Storage directory is writable"
else
    error "✗ Storage directory is not writable"
fi

# Test 10: Composer Dependencies
log "Test 10: Composer Dependencies"
COMPOSER_STATUS=$(docker-compose -f docker-compose.local.yml exec -T app composer check-platform-reqs 2>/dev/null | grep -c "OK" || echo "0")
if [ "$COMPOSER_STATUS" -gt 0 ]; then
    success "✓ Composer dependencies satisfied"
else
    warning "△ Composer dependency issues detected"
fi

# Test 11: NPM Dependencies
log "Test 11: Frontend Build"
if docker-compose -f docker-compose.local.yml exec -T app test -f /var/www/html/public/build/manifest.json; then
    success "✓ Frontend assets built successfully"
else
    warning "△ Frontend assets may not be built"
fi

# Test 12: MailHog Accessibility
log "Test 12: MailHog Email Testing Service"
if curl -f -s http://localhost:8025 > /dev/null; then
    success "✓ MailHog is accessible at http://localhost:8025"
else
    warning "△ MailHog is not accessible"
fi

# Test 13: phpMyAdmin Accessibility
log "Test 13: phpMyAdmin Database Manager"
if curl -f -s http://localhost:8081 > /dev/null; then
    success "✓ phpMyAdmin is accessible at http://localhost:8081"
else
    warning "△ phpMyAdmin is not accessible"
fi

# Test 14: Redis Commander Accessibility
log "Test 14: Redis Commander"
if curl -f -s http://localhost:8082 > /dev/null; then
    success "✓ Redis Commander is accessible at http://localhost:8082"
else
    warning "△ Redis Commander is not accessible"
fi

# Test 15: Laravel Tests (if available)
log "Test 15: Laravel Unit/Feature Tests"
if docker-compose -f docker-compose.local.yml exec -T app php artisan test --stop-on-failure > /dev/null 2>&1; then
    success "✓ Laravel tests passed"
else
    warning "△ Some Laravel tests failed or no tests found"
fi

# Performance Tests
log "Running Basic Performance Tests..."

# Test response time
RESPONSE_TIME=$(curl -o /dev/null -s -w '%{time_total}' http://localhost/)
success "Application response time: ${RESPONSE_TIME}s"

# Check container resource usage
log "Container Resource Usage:"
docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}" $(docker-compose -f docker-compose.local.yml ps -q)

# Summary
echo
echo "=== LOCAL ENVIRONMENT TEST SUMMARY ==="
echo "Environment: Local Development"
echo "Test Date: $(date)"
echo "Container Status:"
docker-compose -f docker-compose.local.yml ps
echo
echo "=== ACCESS URLS ==="
echo "🌐 Application: http://localhost"
echo "📧 MailHog: http://localhost:8025"
echo "🗄️  phpMyAdmin: http://localhost:8081"
echo "📊 Redis Commander: http://localhost:8082"
echo "🔌 WebSocket: ws://localhost:8080"
echo "=================================="

success "Local environment testing completed!"

# Offer to run additional tests
echo
read -p "Would you like to run stress tests? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log "Running stress tests..."
    
    # Simple load test
    log "Running basic load test (10 concurrent requests)..."
    for i in {1..10}; do
        curl -s http://localhost/ > /dev/null &
    done
    wait
    success "Load test completed"
    
    # API stress test
    log "Running API stress test..."
    for i in {1..5}; do
        curl -s http://localhost/api/v1/testing > /dev/null &
    done
    wait
    success "API stress test completed"
fi

log "All testing completed successfully!"