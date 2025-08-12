#!/bin/bash

# Port Checker and Cleaner Script
# Usage: ./deploy/check-ports.sh

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

# Ports used by the local development environment
PORTS=(80 33060 6379 1025 8025 8081 8082)

log "Checking ports used by Flapabay local development environment..."

for port in "${PORTS[@]}"; do
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        PID=$(lsof -Pi :$port -sTCP:LISTEN -t)
        PROCESS=$(ps -p $PID -o comm= 2>/dev/null || echo "unknown")
        warning "Port $port is in use by process $PROCESS (PID: $PID)"
        
        # Check if it's a Docker container
        if docker ps --format "table {{.Names}}\t{{.Ports}}" | grep ":$port->" >/dev/null 2>&1; then
            CONTAINER=$(docker ps --format "table {{.Names}}\t{{.Ports}}" | grep ":$port->" | awk '{print $1}' | head -1)
            log "Port $port is used by Docker container: $CONTAINER"
        else
            echo -n "Would you like to kill process $PID using port $port? (y/N): "
            read -r response
            if [[ "$response" =~ ^[Yy]$ ]]; then
                if kill $PID 2>/dev/null; then
                    success "Killed process $PID on port $port"
                else
                    error "Failed to kill process $PID on port $port"
                fi
            fi
        fi
    else
        success "Port $port is available"
    fi
done

# Check for MySQL specifically
log "Checking for local MySQL installations..."
if pgrep -f mysqld >/dev/null 2>&1; then
    warning "Local MySQL server is running. This might conflict with the Docker MySQL container."
    echo "You can stop it with: brew services stop mysql (if installed via Homebrew)"
    echo "Or: sudo launchctl unload -w /Library/LaunchDaemons/com.oracle.oss.mysql.mysqld.plist"
fi

# Check for Redis specifically
log "Checking for local Redis installations..."
if pgrep -f redis-server >/dev/null 2>&1; then
    warning "Local Redis server is running. This might conflict with the Docker Redis container."
    echo "You can stop it with: brew services stop redis (if installed via Homebrew)"
fi

# Check for Nginx specifically
log "Checking for local Nginx installations..."
if pgrep -f nginx >/dev/null 2>&1; then
    warning "Local Nginx server is running. This might conflict with the Docker Nginx container."
    echo "You can stop it with: brew services stop nginx (if installed via Homebrew)"
    echo "Or: sudo nginx -s stop"
fi

# Show Docker containers that might be using these ports
log "Checking for existing Docker containers..."
EXISTING_CONTAINERS=$(docker ps -a --filter "name=flapabay" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}")
if [ ! -z "$EXISTING_CONTAINERS" ]; then
    echo "$EXISTING_CONTAINERS"
    echo
    echo "To stop existing Flapabay containers:"
    echo "docker-compose -f docker-compose.local.yml down"
fi

success "Port check completed!"

# Offer to show current port usage
echo
read -p "Would you like to see all ports currently in use? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    log "Current port usage:"
    netstat -tuln | grep LISTEN || lsof -nP -iTCP -sTCP:LISTEN
fi