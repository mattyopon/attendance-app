FROM php:8.2-fpm

# システム依存パッケージ
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# PHP設定
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# ユーザー設定（権限問題回避）
RUN groupadd -g 1000 appuser && \
    useradd -u 1000 -g appuser -m appuser

USER appuser

EXPOSE 9000
CMD ["php-fpm"]
