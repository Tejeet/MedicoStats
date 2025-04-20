#!/bin/bash

# Constants
IMAGE_NAME="php-node-refis-app"
CONTAINER_NAME="php-node-refis-container"
NODE_PORT=3110
PHP_PORT=8210
REDIS_PORT=6379

MEDIA_DIR="$(pwd)/media"

# Create local persistent media folder if it doesn't exist
mkdir -p "$MEDIA_DIR"

# Build the Docker image
echo "🔧 Building Docker image: $IMAGE_NAME"
docker build -t "$IMAGE_NAME" .

# Remove existing container if it exists
if [ "$(docker ps -aq -f name=$CONTAINER_NAME)" ]; then
    echo "🧹 Removing existing container: $CONTAINER_NAME"
    docker rm -f "$CONTAINER_NAME"
fi

# Run the Docker container with media persistence and auto-restart
echo "🚀 Running container: $CONTAINER_NAME with persistent /media folder and restart=always"
docker run -d \
  --restart=always \
  -p $NODE_PORT:$NODE_PORT \
  -p $PHP_PORT:$PHP_PORT \
  -p $REDIS_PORT:$REDIS_PORT \
  -v "$MEDIA_DIR":/var/www/html/media \
  --name "$CONTAINER_NAME" \
  "$IMAGE_NAME"

echo "✅ Container '$CONTAINER_NAME' is running with persistent media folder and auto-restart."
