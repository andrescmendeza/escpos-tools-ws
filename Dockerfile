# Dockerfile for escpos-tools
FROM php:8.2-cli

# Set working directory
WORKDIR /app

# Copy only composer files first for better cache usage
COPY composer.json composer.lock ./

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y \
        curl \
        libmagickwand-dev \
        libpng-dev \
        libzip-dev \
        unzip \
        zlib1g-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick


# Copy composer.phar and install dependencies
COPY composer.phar /usr/local/bin/composer.phar
RUN php /usr/local/bin/composer.phar --version \
    && php /usr/local/bin/composer.phar install --no-interaction --no-dev

# Copy the rest of the application code
COPY . .

# To use the web service, send a POST request to http://localhost:8080/esc2html_service.php with parameters 'esc' (base64 RAW) and 'width' (optional)

# Generate example HTML at build time (combined in a single RUN)
RUN php esc2html.php receipt-with-logo.bin > output.html

# Expose the web service for esc2html_service.php on port 8080
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080"]
