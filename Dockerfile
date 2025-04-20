# Use official Ubuntu 22.04 as the base image
FROM ubuntu:22.04

# Set environment variables to avoid user prompts during install
ENV DEBIAN_FRONTEND=noninteractive

# Update, install dependencies, and PHP 8.3 via PPA
RUN apt update && apt install -y \
    software-properties-common && \
    add-apt-repository ppa:ondrej/php -y && \
    apt update && apt install -y \
    curl \
    wget \
    lsb-release \
    ca-certificates \
    apt-transport-https \
    gnupg2 \
    redis-server \
    apache2 \
    libapache2-mod-php8.3 \
    php8.3 \
    php8.3-cli \
    php8.3-mbstring \
    php8.3-curl \
    php8.3-xml \
    php8.3-mysql \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-gd \
    php8.3-common \
    supervisor \
    nodejs \
    npm && \
    apt clean && rm -rf /var/lib/apt/lists/*

# Set PHP 8.3 as default
RUN update-alternatives --set php /usr/bin/php8.3

# Enable Apache mods (optional based on your app needs)
RUN a2enmod rewrite

# Create /home/node and /home/php directories
RUN mkdir -p /home/node /home/php

# Copy Node.js app code to /home/node
COPY ./node /home/node/

# Copy PHP app code to /home/php
COPY ./php /home/php/

# Set correct permissions
RUN chown -R www-data:www-data /home/php /home/node

# Expose necessary ports
EXPOSE 8180 3110 6379

# Copy Supervisor config (optional if needed for managing both services)
# COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Start Supervisor to manage services like Apache, Redis, and Node.js
CMD ["/usr/bin/supervisord", "-n"]
