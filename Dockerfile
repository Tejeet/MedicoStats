FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Update and install dependencies
RUN apt update && apt install -y \
    curl \
    wget \
    lsb-release \
    ca-certificates \
    apt-transport-https \
    software-properties-common \
    gnupg2 \
    redis-server \
    apache2 \
    libapache2-mod-php \
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
    supervisor

# Install Node.js v22 LTS
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt install -y nodejs

# Confirm versions
RUN php -v && node -v && npm -v

# Copy Apache + PHP files to /var/www/html
WORKDIR /var/www/html
COPY ./php ./php

# Copy Node.js app to /home
WORKDIR /home
COPY ./node ./node

# Copy Supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose ports
EXPOSE 8180 3110 6379

# Start Supervisor
CMD ["/usr/bin/supervisord", "-n"]
