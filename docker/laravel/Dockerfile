# Imagem base PHP 8.3 FPM
FROM php:8.3-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    supervisor \
    libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Criar usuário com mesmo UID/GID do host
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN groupadd -g ${GROUP_ID} appuser && \
    useradd -u ${USER_ID} -g appuser -m -s /bin/bash appuser

# Definir diretório de trabalho
WORKDIR /var/www

# Dar permissões ao usuário appuser
RUN chown -R appuser:appuser /var/www

# Copiar scripts de inicialização
COPY docker/laravel/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expor porta do PHP-FPM
EXPOSE 9000

USER appuser
CMD ["/usr/local/bin/start.sh"]
