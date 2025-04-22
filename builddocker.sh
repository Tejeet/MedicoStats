#!/bin/bash

# Constants
IMAGE_NAME="php_node_refis_app"
CONTAINER_NAME="php_node_refis_container"
NODE_PORT=3110
PHP_PORT=8210
REDIS_PORT=6379
MQTT_PORT=1884
# Media directory outside the repo (in the user's home directory)
MEDIA_DIR="/home/pi/docker_media/$CONTAINER_NAME"

# Create the media directory if it doesn't exist
mkdir -p "$MEDIA_DIR"

# Build the Docker image
echo "🔧 Building Docker image: $IMAGE_NAME"
docker build -t "$IMAGE_NAME" .

# Remove existing container if it exists
if [ "$(docker ps -aq -f name=$CONTAINER_NAME)" ]; then
    echo "🧹 Removing existing container: $CONTAINER_NAME"
    docker rm -f "$CONTAINER_NAME"
fi

# Run the Docker container with persistent media folder and auto-restart
echo "🚀 Running container: $CONTAINER_NAME with persistent /media folder and restart=always"
docker run -d \
  --restart=always \
  -p $NODE_PORT:$NODE_PORT \
  -p $PHP_PORT:$PHP_PORT \
  -p $REDIS_PORT:$REDIS_PORT \
  -p $MQTT_PORT:$MQTT_PORT \
  -v "$MEDIA_DIR":/var/www/html/media \
  --name "$CONTAINER_NAME" \
  "$IMAGE_NAME"

echo "✅ Container '$CONTAINER_NAME' is running with persistent media folder at $MEDIA_DIR and auto-restart."
