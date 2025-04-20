#!/bin/bash

# Constants
IMAGE_NAME="php-node-refis-app"
CONTAINER_NAME="php-node-refis-container"
NODE_PORT=3110
PHP_PORT=8210
REDIS_PORT=6379

# Build the Docker image
echo "🔧 Building Docker image: $IMAGE_NAME"
docker build -t "$IMAGE_NAME" .

# Check if the container already exists
if [ "$(docker ps -aq -f name=$CONTAINER_NAME)" ]; then
    echo "🧹 Removing existing container: $CONTAINER_NAME"
    docker rm -f "$CONTAINER_NAME"
fi

# Run the Docker container with auto-restart enabled
echo "🚀 Running container: $CONTAINER_NAME with restart=always"
docker run -d \
  --restart=always \
  -p $NODE_PORT:$NODE_PORT \
  -p $PHP_PORT:$PHP_PORT \
  -p $REDIS_PORT:$REDIS_PORT \
  --name "$CONTAINER_NAME" \
  "$IMAGE_NAME"

echo "✅ Container '$CONTAINER_NAME' is running and set to auto-restart on boot."
