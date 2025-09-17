# Dockerfile for escpos-tools
FROM php:8.2-cli

# Copy source code
WORKDIR /app
COPY . /app

# Install Composer and required dependencies
RUN apt-get update \
    && apt-get install -y \
        curl \
        git \
        libmagickwand-dev \
        libpng-dev \
        libzip-dev \
        unzip \
        zlib1g-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install zip gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \

    && composer install --no-interaction --no-dev

# To use the web service, send a POST request to http://localhost:8080/esc2html_service.php with parameters 'esc' (base64 RAW) and 'width' (optional)

# Generate example HTML at build time (combined in a single RUN)
RUN php esc2html.php receipt-with-logo.bin > output.html

# Expose the web service for esc2html_service.php on port 8080
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080"]
