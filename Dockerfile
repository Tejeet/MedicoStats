FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Update and install all required packages
RUN apt update && apt install -y \
    curl \
    software-properties-common \
    gnupg2 \
    lsb-release \
    ca-certificates \
    redis-server \
    php8.3 php8.3-cli php8.3-mbstring php8.3-curl php8.3-xml php8.3-mysql \
    php8.3-zip php8.3-bcmath php8.3-gd php8.3-common \
    nodejs npm \
    supervisor

# Make necessary directories
RUN mkdir -p /app /var/www/html /var/log/supervisor

# Copy files
COPY app/app.js /app/app.js
COPY php/index.php /var/www/html/index.php
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set workdir for node app
WORKDIR /app

# Install Fastify
RUN npm install fastify

# Expose required ports
EXPOSE 6379 3110 8180

# Start all services using Supervisor
CMD ["/usr/bin/supervisord", "-n"]
