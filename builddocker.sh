#!/bin/bash

# Constants
IMAGE_NAME="node_php_redis"
CONTAINER_NAME="nodephpcontainer"
NODE_PORT=3180
PHP_PORT=8180
REDIS_PORT=6379

# Build the Docker image
echo "🔧 Building Docker image: $IMAGE_NAME"
docker build -t "$IMAGE_NAME" .

# Check if the container already exists
if [ "$(docker ps -aq -f name=$CONTAINER_NAME)" ]; then
    echo "🧹 Removing existing container: $CONTAINER_NAME"
    docker rm -f "$CONTAINER_NAME"
fi

# Run the Docker container
echo "🚀 Running container: $CONTAINER_NAME"
docker run -d \
  -p $NODE_PORT:$NODE_PORT \
  -p $PHP_PORT:$PHP_PORT \
  -p $REDIS_PORT:$REDIS_PORT \
  --name "$CONTAINER_NAME" \
  "$IMAGE_NAME"

echo "✅ Container '$CONTAINER_NAME' is running."
