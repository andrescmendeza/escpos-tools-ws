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

# Generate example HTML at build time (combined in a single RUN)
RUN php esc2html.php emulator_test.raw > salida.html

# Expose a simple web server to view the generated HTML
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080"]
