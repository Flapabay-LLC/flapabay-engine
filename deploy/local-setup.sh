#!/bin/bash

# Flapabay Local Development Setup Script
# Usage: ./deploy/local-setup.sh

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

# Ensure we're in the project directory
cd "$(dirname "$0")/.."

log "Setting up Flapabay Local Development Environment..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    error "Docker is not running. Please start Docker and try again."
fi

# Check ports and offer to fix conflicts
log "Checking for port conflicts..."
./deploy/check-ports.sh

# Check if Docker Compose is available
if ! command -v docker-compose &> /dev/null; then
    error "Docker Compose is not installed. Please install Docker Compose and try again."
fi

# Setup local environment file
if [ ! -f ".env.local" ]; then
    log "Creating local environment file..."
    cp .env.local.example .env.local
    
    # Generate application key
    log "Generating application key..."
    APP_KEY=$(openssl rand -base64 32)
    sed -i.bak "s/GENERATE_NEW_KEY_HERE/$APP_KEY/" .env.local && rm .env.local.bak
    
    warning "Please edit .env.local with your specific configuration before proceeding."
    warning "Press Enter to continue after editing .env.local, or Ctrl+C to exit..."
    read -r
fi

# Copy environment file for Docker
log "Setting up environment for Docker..."
cp .env.local .env

# Source environment variables
export $(cat .env | grep -v '^#' | xargs)

# Create necessary directories
log "Creating necessary directories..."
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set permissions
log "Setting proper permissions..."
chmod -R 775 storage bootstrap/cache

# Pull Docker images
log "Pulling Docker images..."
docker-compose -f docker-compose.local.yml pull

# Build application image
log "Building application image..."
docker-compose -f docker-compose.local.yml build --no-cache

# Stop any existing containers
log "Stopping existing containers..."
docker-compose -f docker-compose.local.yml down --remove-orphans

# Start services
log "Starting services..."
docker-compose -f docker-compose.local.yml up -d

# Wait for database to be ready
log "Waiting for database to be ready..."
until docker-compose -f docker-compose.local.yml exec -T db mysqladmin ping -h"localhost" --silent; do
    log "Waiting for database connection..."
    sleep 2
done

# Wait for application to be ready
log "Waiting for application to be ready..."
sleep 10

# Install/update Composer dependencies
log "Installing Composer dependencies..."
docker-compose -f docker-compose.local.yml exec -T app composer install

# Install/update NPM dependencies
log "Installing NPM dependencies..."
docker-compose -f docker-compose.local.yml exec -T app npm install

# Generate application key (in case it wasn't generated)
log "Ensuring application key is set..."
docker-compose -f docker-compose.local.yml exec -T app php artisan key:generate --force

# Run database migrations
log "Running database migrations..."
docker-compose -f docker-compose.local.yml exec -T app php artisan migrate --force

# Seed database with test data
log "Seeding database with test data..."
docker-compose -f docker-compose.local.yml exec -T app php artisan db:seed --force

# Clear caches
log "Clearing caches..."
docker-compose -f docker-compose.local.yml exec -T app php artisan config:clear
docker-compose -f docker-compose.local.yml exec -T app php artisan cache:clear
docker-compose -f docker-compose.local.yml exec -T app php artisan view:clear
docker-compose -f docker-compose.local.yml exec -T app php artisan route:clear

# Build frontend assets
log "Building frontend assets..."
docker-compose -f docker-compose.local.yml exec -T app npm run build

# Create storage link
log "Creating storage link..."
docker-compose -f docker-compose.local.yml exec -T app php artisan storage:link || true

# Health check
log "Performing health check..."
sleep 5

if curl -f -s http://localhost/health > /dev/null; then
    success "Application is running successfully!"
else
    error "Health check failed. Check the logs with: docker-compose -f docker-compose.local.yml logs"
fi

# Display running containers and access URLs
log "Setup completed! Running containers:"
docker-compose -f docker-compose.local.yml ps

success "Flapabay local development environment is ready!"
echo
echo "=== ACCESS URLS ==="
echo "🌐 Application: http://localhost"
echo "📧 MailHog (Email testing): http://localhost:8025"
echo "🗄️  phpMyAdmin: http://localhost:8081"
echo "📊 Redis Commander: http://localhost:8082"
echo "🔌 WebSocket (Reverb): ws://localhost:8080"
echo "==================="
echo
log "To monitor logs: docker-compose -f docker-compose.local.yml logs -f"
log "To run artisan commands: docker-compose -f docker-compose.local.yml exec app php artisan [command]"
log "To access bash: docker-compose -f docker-compose.local.yml exec app bash"

# Show some useful commands
echo
echo "=== USEFUL COMMANDS ==="
echo "Stop services: docker-compose -f docker-compose.local.yml down"
echo "Restart services: docker-compose -f docker-compose.local.yml restart"
echo "View logs: docker-compose -f docker-compose.local.yml logs -f [service]"
echo "Run tests: docker-compose -f docker-compose.local.yml exec app php artisan test"
echo "Clear cache: docker-compose -f docker-compose.local.yml exec app php artisan cache:clear"
echo "========================"

success "Local development setup completed successfully!"