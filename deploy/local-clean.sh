#!/bin/bash

# Flapabay Local Environment Cleanup Script
# Usage: ./deploy/local-clean.sh [--force]

FORCE_CLEAN=false

# Check for force flag
if [ "$1" = "--force" ]; then
    FORCE_CLEAN=true
fi

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

log "Cleaning up Flapabay Local Development Environment..."

if [ "$FORCE_CLEAN" = false ]; then
    warning "This will remove all local Docker containers, volumes, and data."
    warning "Your source code will NOT be affected."
    echo
    read -p "Are you sure you want to continue? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log "Cleanup cancelled."
        exit 0
    fi
fi

# Stop and remove containers
log "Stopping and removing containers..."
docker-compose -f docker-compose.local.yml down --remove-orphans

# Remove volumes (this will delete all data)
log "Removing Docker volumes (this will delete all database data)..."
docker-compose -f docker-compose.local.yml down --volumes

# Remove Docker images
log "Removing Docker images..."
docker-compose -f docker-compose.local.yml down --rmi all

# Clean up any orphaned containers
log "Cleaning up orphaned containers..."
docker container prune -f

# Clean up any orphaned volumes
log "Cleaning up orphaned volumes..."
docker volume prune -f

# Clean up any orphaned networks
log "Cleaning up orphaned networks..."
docker network prune -f

# Clean up any orphaned images
log "Cleaning up orphaned images..."
docker image prune -f

# Clean Laravel caches and temporary files
log "Cleaning application caches..."
if [ -f ".env" ]; then
    rm .env
fi

if [ -d "storage/logs" ]; then
    rm -rf storage/logs/*
fi

if [ -d "bootstrap/cache" ]; then
    rm -rf bootstrap/cache/*
fi

if [ -d "storage/framework/cache" ]; then
    rm -rf storage/framework/cache/*
fi

if [ -d "storage/framework/sessions" ]; then
    rm -rf storage/framework/sessions/*
fi

if [ -d "storage/framework/views" ]; then
    rm -rf storage/framework/views/*
fi

# Clean node_modules and vendor if they exist (optional)
if [ "$FORCE_CLEAN" = true ]; then
    log "Force clean: removing node_modules and vendor directories..."
    if [ -d "node_modules" ]; then
        rm -rf node_modules
    fi
    
    if [ -d "vendor" ]; then
        rm -rf vendor
    fi
    
    if [ -d "public/build" ]; then
        rm -rf public/build
    fi
    
    if [ -f "package-lock.json" ]; then
        rm package-lock.json
    fi
    
    if [ -f "composer.lock" ]; then
        rm composer.lock
    fi
fi

# Show remaining Docker resources
log "Remaining Docker resources:"
echo "Images:"
docker images | grep flapabay || echo "No Flapabay images found"
echo
echo "Volumes:"
docker volume ls | grep flapabay || echo "No Flapabay volumes found"
echo
echo "Networks:"
docker network ls | grep flapabay || echo "No Flapabay networks found"

success "Local environment cleanup completed!"

if [ "$FORCE_CLEAN" = true ]; then
    warning "Complete cleanup performed. You'll need to run npm install and composer install next time."
else
    log "To set up the environment again, run: ./deploy/local-setup.sh"
fi

# Show disk space freed up
FREED_SPACE=$(docker system df | grep "Reclaimable" | awk '{print $4}' | head -1)
if [ ! -z "$FREED_SPACE" ]; then
    success "Disk space potentially freed: $FREED_SPACE"
fi

# Optional: Run docker system prune for deeper cleanup
echo
read -p "Would you like to run a deep Docker system cleanup? This will remove ALL unused Docker data. (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log "Running deep Docker system cleanup..."
    docker system prune -a --volumes -f
    success "Deep cleanup completed!"
fi

log "Cleanup process finished."