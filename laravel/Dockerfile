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

COPY --from=node:22-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-slim /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /var/www/html

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh     

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]