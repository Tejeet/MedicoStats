# Start with Ubuntu 22.04
FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Update and install dependencies
RUN apt update && apt install -y \
    curl \
    software-properties-common \
    gnupg2 \
    lsb-release \
    ca-certificates \
    redis-server \
    php8.3 php8.3-cli php8.3-fpm php8.3-mbstring php8.3-curl php8.3-xml php8.3-mysql \
    php8.3-zip php8.3-bcmath php8.3-gd php8.3-common \
    supervisor

# Install Node.js (latest LTS from NodeSource)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt install -y nodejs

# Create Supervisor directory for configs
RUN mkdir -p /var/log/supervisor

# Copy supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose Redis, PHP-FPM, and Node app ports
EXPOSE 6379 9000 3000

# Start supervisor to manage services
CMD ["/usr/bin/supervisord", "-n"]
