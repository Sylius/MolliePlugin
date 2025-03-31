# Use Ubuntu 22.04 as the base image
FROM ubuntu:22.04

# Set build arguments for PHP and Node.js versions
ARG PHP_VERSION=8.1

# Set environment variables
ENV NODE_VERSION=18
ENV PROJECT_DIR=/var/www
ENV ROOT_DIR=/var/www/tests/Application/public
ENV DEBIAN_FRONTEND=noninteractive

# Set working directory to the project root
WORKDIR ${PROJECT_DIR}

### 1️⃣ Install System Tools ###
RUN apt-get update && apt-get install -y --no-install-recommends \
    software-properties-common \
    gpg-agent \
    curl \
    unzip \
    git \
    supervisor \
    nginx \
    mariadb-server \
    gettext \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

### 2️⃣ Install PHP & Extensions ###
RUN add-apt-repository ppa:ondrej/php -y && apt-get update && apt-get install -y --no-install-recommends \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

### 3️⃣ Install Node.js ###
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - && \
    apt-get install -y --no-install-recommends \
    nodejs && \
    npm install -g yarn && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

### 4️⃣ Install MailHog ###
RUN curl -L -o /usr/local/bin/mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_linux_amd64 \
    && chmod +x /usr/local/bin/mailhog

### 5️⃣ Install wkhtmltopdf ###
RUN apt-get update && apt-get install -y --no-install-recommends wkhtmltopdf && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy project files (ensuring it's copying from the correct location)
COPY . .

# Verify that the project files are correctly copied
RUN ls -la ${PROJECT_DIR}

# Copy and replace `ROOT_DIR` and `PHP_VERSION` in Nginx config
COPY .docker/config/nginx/default.conf /etc/nginx/sites-available/default.template
RUN envsubst '$ROOT_DIR $PHP_VERSION' < /etc/nginx/sites-available/default.template > /etc/nginx/sites-available/default \
    && rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copy custom MySQL config
COPY .docker/config/mysql/my.cnf /etc/mysql/my.cnf

# Configure MariaDB: Set up Sylius database with password authentication
RUN service mariadb start && \
    mysqladmin ping --silent && \
    mysql -e "CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY '';" && \
    mysql -e "ALTER USER 'root'@'127.0.0.1' IDENTIFIED BY '';" && \
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '';" && \
    mysql -e "FLUSH PRIVILEGES;"

# Copy and set up the entrypoint script
COPY .docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose only the web server port
EXPOSE 80 8025

LABEL org.opencontainers.image.description="Demo Docker image for showcasing the Sylius Mollie Plugin integration"

# Start all services using Supervisor
ENTRYPOINT ["/entrypoint.sh"]
