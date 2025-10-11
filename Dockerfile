FROM php:8.4-fpm

LABEL maintainer="Benjamin Romero <programador.ben@gmail.com>"

ARG UID=1000
ARG GID=1000
WORKDIR /var/www/html

# Instalar dependencias de sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl zip unzip bash libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libxml2-dev libonig-dev libicu-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        gd \
        zip \
        intl \
        bcmath \
        opcache \
        pcntl \
        sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurar git safe directory
RUN git config --global --add safe.directory /var/www/html

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crear usuario sin privilegios
RUN groupadd -g ${GID} sail \
    && useradd -m -u ${UID} -g sail sail

# Copiar código y dar permisos
COPY . /var/www/html
RUN chown -R sail:sail /var/www/html

USER sail

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s CMD curl -f http://localhost:80/ || exit 1

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
