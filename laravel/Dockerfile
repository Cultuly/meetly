FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        curl \
        libzip-dev \
        libonig-dev \
        zip \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh     

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]