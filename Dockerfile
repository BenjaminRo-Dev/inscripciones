FROM php:8.4-fpm

LABEL maintainer="Benjamin Romero <programador.ben@gmail.com>"

WORKDIR /var/www/html

# Instalar dependencias de sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl zip unzip bash libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libxml2-dev libonig-dev libicu-dev libpq-dev supervisor \
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

# Copiar código y dar permisos
COPY . /var/www/html

# Preparar carpetas para supervisord
RUN mkdir -p /var/run/supervisor /var/log/supervisor

EXPOSE 80

#Nota: esta linea no se esta ejecutando, se ejecuta elcommand del docker-compose
# Arrancar supervisord al iniciar el contenedor
# CMD ["supervisord", "-c", "/var/www/html/mi_config/supervisord.conf"]
