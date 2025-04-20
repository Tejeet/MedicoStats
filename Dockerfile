FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Step 1: Install essential tools and dependencies
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
    nano \
    git

# Step 2: Install PHP 8.3 and required extensions
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

# Step 3: Install Node.js 22.x
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
    apt install -y nodejs

# Step 4: Confirm installed versions
RUN php -v && node -v && npm -v && redis-server --version

# Step 5: Copy PHP app to /var/www/html
WORKDIR /var/www/html
COPY ./php/ /var/www/html/

# Step 6: Set Node.js working directory to /home/node
WORKDIR /home/node
COPY ./node/package*.json ./
RUN npm install
COPY ./node/ .

# Step 7: Configure Supervisor
RUN mkdir -p /var/log/supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Step 8: Expose required ports
EXPOSE 3110 8180 6379

# Step 9: Start all services via Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
