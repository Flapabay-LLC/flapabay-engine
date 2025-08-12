#!/bin/bash

# Test Docker Build Script
# Usage: ./deploy/test-docker-build.sh [local|production]

BUILD_TYPE=${1:-local}

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

log "Testing Docker build for $BUILD_TYPE environment..."

if [ "$BUILD_TYPE" = "local" ]; then
    log "Building local development image..."
    if docker build -f Dockerfile.local -t flapabay-test-local .; then
        success "Local Docker build successful!"
        
        # Test the image
        log "Testing the built image..."
        if docker run --rm -d --name flapabay-test-container -p 9001:9000 flapabay-test-local; then
            sleep 5
            if docker exec flapabay-test-container php -v > /dev/null 2>&1; then
                success "Container is running and PHP is working!"
            else
                error "Container is running but PHP is not working"
            fi
            
            # Cleanup
            docker stop flapabay-test-container > /dev/null 2>&1
        else
            error "Failed to run the built image"
        fi
        
        # Remove test image
        docker rmi flapabay-test-local > /dev/null 2>&1
        
    else
        error "Local Docker build failed!"
    fi
    
elif [ "$BUILD_TYPE" = "production" ]; then
    log "Building production image..."
    if docker build -f Dockerfile --target production -t flapabay-test-prod .; then
        success "Production Docker build successful!"
        
        # Remove test image
        docker rmi flapabay-test-prod > /dev/null 2>&1
    else
        error "Production Docker build failed!"
    fi
else
    error "Invalid build type. Use 'local' or 'production'"
fi

success "Docker build test completed!"