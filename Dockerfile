FROM php:8.4-fpm-alpine

LABEL maintainer="Benjamin Romero <programador.ben@gmail.com>"

# Variables
ARG UID=1000
ARG GID=1000
WORKDIR /var/www/html

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apk add --no-cache \
    git curl zip unzip bash libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev libxml2-dev oniguruma-dev icu-dev postgresql-dev postgresql-client \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        gd \
        zip \
        intl \
        bcmath \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del --no-cache libpng-dev libjpeg-turbo-dev freetype-dev postgresql-dev $PHPIZE_DEPS

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Crear usuario sin privilegios
RUN addgroup -g ${GID} sail \
    && adduser -D -G sail -u ${UID} sail

# Copiar código fuente
COPY . /var/www/html

# Dar permisos
RUN chown -R sail:sail /var/www/html

USER sail

# Exponer puerto 80 (para Artisan serve)
EXPOSE 80

# Healthcheck opcional
HEALTHCHECK --interval=30s --timeout=5s CMD curl -f http://localhost:80/ || exit 1

# Comando por defecto
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
