FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    postgresql-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
    zip \
    pdo \
    pdo_pgsql \
    && apk add --no-cache --repository=https://dl-cdn.alpinelinux.org/alpine/v3.18/community \
    && apk add --no-cache \
    libpq

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Note: APP_KEY should be set via Railway environment variables
# Remove the line below since .env is managed by Railway
# RUN php artisan key:generate --no-interaction

# Change ownership
RUN chown -R nobody:nobody /var/www/html/storage /var/www/html/bootstrap/cache

# Generate APP_KEY if not set
RUN php artisan key:generate --no-interaction || true

# Expose port
EXPOSE 8000

# Start command - explicit format for Railway
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=$PORT"]

