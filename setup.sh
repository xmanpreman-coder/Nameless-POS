#!/bin/bash
# setup.sh - Quick Setup Script for Mac/Linux

echo "================================"
echo "Nameless POS - Docker Setup"
echo "================================"
echo ""

# Check Docker
echo "[1/5] Checking Docker..."
if ! command -v docker &> /dev/null; then
    echo "[ERROR] Docker not installed!"
    echo "Install: https://www.docker.com/products/docker-desktop"
    exit 1
fi
echo "[OK] Docker found"

# Check docker-compose
echo "[2/5] Checking docker-compose..."
if ! command -v docker-compose &> /dev/null; then
    echo "[ERROR] docker-compose not installed!"
    exit 1
fi
echo "[OK] docker-compose found"

# Build Docker image
echo "[3/5] Building Docker image..."
docker-compose -f docker-compose.dev.yml build
if [ $? -ne 0 ]; then
    echo "[ERROR] Build failed!"
    exit 1
fi
echo "[OK] Image built successfully"

# Start containers
echo "[4/5] Starting containers..."
docker-compose -f docker-compose.dev.yml up -d
if [ $? -ne 0 ]; then
    echo "[ERROR] Failed to start containers!"
    exit 1
fi
echo "[OK] Containers started"

# Wait for app to be ready
echo "[5/5] Waiting for app to be ready..."
sleep 5

echo ""
echo "================================"
echo "✅ Setup Complete!"
echo "================================"
echo ""
echo "Open browser: http://localhost:8000"
echo ""
echo "Docker commands:"
echo "  docker-compose -f docker-compose.dev.yml ps        # See containers"
echo "  docker-compose -f docker-compose.dev.yml logs -f   # View logs"
echo "  docker-compose -f docker-compose.dev.yml down      # Stop containers"
echo ""
