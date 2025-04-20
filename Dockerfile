FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Update system and install core tools
RUN apt update && apt install -y \
    curl \
    wget \
    lsb-release \
    ca-certificates \
    apt-transport-https \
    software-properties-common \
    gnupg2 \
    redis-server \
    supervisor \
    nano

# Add PHP 8.3 PPA and install PHP + extensions
RUN add-apt-repository ppa:ondrej/php -y && \
    apt update && \
    apt install -y \
    php8.3 \
    php8.3-cli \
    php8.3-mbstring \
    php8.3-curl \
    php8.3-xml \
    php8.3-mysql \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-gd \
    php8.3-common

# Install Node.js 22.x LTS
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt install -y nodejs

# Confirm versions
RUN php -v && node -v && npm -v && redis-server --version

# Create app directory
WORKDIR /var/www/html

# Copy app files
COPY . .

# Supervisor configuration
RUN mkdir -p /var/log/supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose ports
EXPOSE 3110 8180 6379

# Start supervisor (which starts all services)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
